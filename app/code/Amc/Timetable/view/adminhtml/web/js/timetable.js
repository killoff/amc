define([
    "jquery",
    "moment",
    "fullcalendar",
    "fullcalendarScheduler",
    "jquery/ui"
], function($, moment){
    "use strict";

    $.widget("mage.timetable", {
        options: {
            resources: {
                url: '',
                type: 'GET',
                data: {}
            },
            events: {
                url: '',
                type: 'GET',
                data: {}
            },
            sync_url: false, // url for sending events changes immediately == server instant updates
            init_registry_url: false, // url to initialize registry on load (used on order view)
            defaultDate: '',
            businessHours: {
                start: '9:00',
                end: '19:00',
                dow: [ 1, 2, 3, 4, 5 ]
            },
            minTime: '09:00',
            maxTime: '19:00',
            resourceLabelText: '',
            slotDuration: '00:15',
            registry_json_field_name: 'registry_json'
        },

        _create: function() {
            console.log('calendar initialized');
            this.orderItemsState = ''; // concatenated order item IDs to track order items changes
            this.initFullcalendar();
            this.registry.subscribe(this.onRegistryChange, this);
            $(document).on("orderLoadArea", function(e) {
                if (this.areOrderItemsChanged()) {
                    this.updateOrderItemsState();
                    this.render();
                }
            }.bind(this));
        },

        render: function() {
            console.log('re-render calendar start');
            this.element.fullCalendar('refetchResources');
            window.setTimeout(function () {this.element.fullCalendar('refetchEvents');}.bind(this), 1000);
            this.registry.dispatchChange();
            console.log('re-render calendar stop');
        },

        initFullcalendar: function() {
            this.element.fullCalendar({
                resources: this.options.resources,
                events: this.options.events,
                defaultDate: this.options.defaultDate,
                editable: true,
                contentHeight: 'auto',
                scrollTime: '00:00',
                header: {
                    left: '',
                    center: 'title',
                    right: 'today prev,next'
                },
                defaultView: 'timelineDay',
                businessHours: this.options.businessHours,
                minTime: this.options.minTime,
                maxTime: this.options.maxTime,
                slotLabelFormat: 'HH:mm',
                eventOverlap: true,
                resourceAreaWidth: '25%',
                resourceLabelText: this.options.resourceLabelText,
                selectable: true,
                selectHelper: true,
                slotDuration: this.options.slotDuration,
                slotWidth: 15,

                eventResize: function(event, delta, revertFunc) {
                    // do not allow to resize out of slot range
                    if (this.isEventOutOfSlot(event) || this.isEventOverlap(event)) {
                        revertFunc();
                        return false;
                    }
                    this.registry.update(event.uuid, event);
                }.bind(this),

                eventDrop: function(event, delta, revertFunc) {
                    // do not allow to drop out of slot range
                    if (this.isEventOutOfSlot(event) || this.isEventOverlap(event)) {
                        revertFunc();
                        return false;
                    }
                    this.registry.update(event.uuid, event);
                }.bind(this),

                eventClick: function(event) {
                    if (window.confirm('Discard time range?')) {
                        event.deleted = 1;
                        this.registry.delete(event);
                        this.element.fullCalendar('removeEvents', event.id);
                    }
                }.bind(this),

                select: function(start, end, jsEvent, calendar, resource)
                {
                    this.element.fullCalendar('unselect');

                    if (resource.id && 'user' === resource.type) {

                        // select product duration if just click happened
                        var productDuration = parseInt(resource.parent.duration);
                        if (productDuration > 0 && Math.ceil(productDuration / 15) >= 1) { // product duration at least 15 minutes
                            var selectedDuration = moment.duration(end.diff(start));
                            if (15 == selectedDuration.asMinutes()) { // most probably user just clicked on time slot, no selection
                                end = start.clone().add(Math.ceil(productDuration / 15) * 15, 'minutes');
                            }
                        }

                        var slot = this.getResourceTimeSlot(resource, start, end);
                        if (!slot)
                            return false;

                        // correct start and end for selected interval in case of out of slot range
                        if (start.isBefore(slot.start))
                            start = slot.start;

                        if (end.isAfter(slot.end))
                            end = slot.end;

                        var uuid = this.generateUuid();
                        var eventData = {
                            id: uuid,
                            title: '', // time start - time end?
                            start: start,
                            end: end,
                            resourceId: resource.id,
                            uuid: uuid,
                            type: 'order',
                            user_id: slot.user_id,
                            sales_item_id: slot.sales_item_id,
                            room_id: slot.room_id,
                            slot: slot,
                            constraint: {
                                start: slot.start,
                                end: slot.end
                            }
                        };

                        if (this.isEventOverlap(eventData))
                            return false;

                        this.element.fullCalendar('renderEvent', eventData, true); // stick? = true
                        this.registry.add(uuid, eventData);
                    }
                }.bind(this),

                selectOverlap: function(event) {
                    console.log(event);
                    return event.rendering === 'background';
                }.bind(this)
            });
        },

        isEventOutOfSlot: function(event) {
            var slot = this.getEventTimeSlot(event);
            if (!slot)
                return false;

            // out of slot == event does not belong to resource or time range violation
            return event.resourceId !== slot.resourceId
                || event.start.isBefore(slot.start)
                || event.end.isAfter(slot.end);
        },

        // check if event overlaps with other events in this resource
        isEventOverlap: function(event) {
            // use event.resourceId since event might be not added to calendar yet
            var resourceEvents = this.element.fullCalendar('getResourceEvents', event.resourceId);
            var overlapEvents = resourceEvents.filter(function(resourceEvent) {
                return event.id != resourceEvent.id
                    && resourceEvent.type === 'order'
                    && this.timeRangesOverlap(event.start, event.end, resourceEvent.start, resourceEvent.end);
            }.bind(this));
            return overlapEvents.length > 0;
        },

        // return "background" event which event belongs to
        getEventTimeSlot: function(event) {
            if (event.slot) {
                return event.slot;
            }
            var resource = this.element.fullCalendar('getEventResource', event);
            return this.getResourceTimeSlot(resource, event.start, event.end);
        },

        // return "background" event for given time range (start, end) and resource
        getResourceTimeSlot: function(resource, start, end) {
            var resourceSlots = this.element.fullCalendar('getResourceEvents', resource);
            if (resourceSlots.length == 0) {
                return false;
            }
            // pick up only time slots that intersect with selected period
            var fitSlots = resourceSlots.filter(function(resourceEvent) {
                return 'background' === resourceEvent.rendering
                    && this.timeRangesOverlap(start, end, resourceEvent.start, resourceEvent.end);
            }.bind(this));
            // no intersections - no selection
            if (fitSlots.length == 0) {
                return false;
            }
            // pick up only first intersection, ignore multiple selection
            return fitSlots[0];
        },

        // check if (startA .. endA) intersects (startB .. endB)
        timeRangesOverlap: function(startA, endA, startB, endB) {
            return startA.isBefore(endB) && endA.isAfter(startB);
        },

        gotoEvent: function(event) {
            var targetEventUuid = $(event.target).attr('data-event-id');
            var targetEvent = this.registry.event(targetEventUuid);
            if (targetEvent.uuid) {
                this.element.fullCalendar( 'gotoDate', targetEvent.start);
            }
        },

        renderStateOnItemsGrid: function (events) {

            var aggregated = {},
                salesItemHtml,
                timeStart,
                i;

            // aggregate events, users and products
            $.each(events, function(uuid, data) {
                if ( ! aggregated.hasOwnProperty(data.product_unique_id)) {
                    aggregated[data.product_unique_id] = {
                        sales_item_id: data.sales_item_id,
                        product_id: data.product_id,
                        users: {}
                    };
                }
                if ( ! aggregated[data.product_unique_id]['users'].hasOwnProperty(data.user_unique_id)) {
                    aggregated[data.product_unique_id]['users'][data.user_unique_id] = {
                        user_id: data.user_id,
                        title: data.user_name,
                        events: []
                    };
                }
                aggregated[data.product_unique_id]['users'][data.user_unique_id]['events'].push(data);
            }.bind(this));

            /**
             * render events under correspondent order items
             */
            // first remove all timetable for order items
            $('.timetable-state').remove();
            $.each(aggregated, function(key, salesItem) {
                console.log(salesItem);
                salesItemHtml = '';
                $.each(salesItem.users, function(uKey, user) {
                    salesItemHtml += '<div class="child">'
                        + '<span class="who">' + user.title + '</span>';
                    events = user.events;
                    salesItemHtml += '<span class="times">';
                    for(i = 0; i < events.length; i++) {
                        timeStart = moment(events[i].start);
                        salesItemHtml += '<span class="time" data-event-id="' + events[i].uuid + '">' + timeStart.format("ddd D.MM, HH:mm") + '</span>';
                    }
                    salesItemHtml.replace(/;$/g, '');
                    salesItemHtml += '</span>';
                    salesItemHtml += '</div>';
                }.bind(this));
                $('<div id="timetable-state-' + salesItem.sales_item_id + '" class="timetable-state">' + salesItemHtml + '</div>')
                    .insertAfter('#order_item_' + salesItem.sales_item_id + '_title');

            }.bind(this));
            // attach 'click' event to all events under order items
            $('.timetable-state').find('[data-event-id]').click(this.gotoEvent.bind(this));
        },

        onRegistryChange: function(events, contextEvent) {


            if (this.options.init_registry_url) {
                $.getJSON( this.options.init_registry_url, {}, function(response) {
                    this.renderStateOnItemsGrid(response);
                }.bind(this))
                .fail(function(exception) {
                    this._handleFail(exception);
                }.bind(this));

            } else {
                var eventsForState = [];
                $.each(events, function(uuid, event) {
                    var userResource = this.element.fullCalendar('getResourceById', event.resourceId);
                    var productResource = userResource.parent;
                    event.sales_item_id = productResource.sales_item_id;
                    event.product_id = productResource.product_id;
                    event.user_name = userResource.title;
                    event.user_id = userResource.user_id;
                    event.product_unique_id = productResource.id;
                    event.user_unique_id = userResource.id;
                    eventsForState.push(event);
                }.bind(this));
                this.renderStateOnItemsGrid(eventsForState);
            }

            // write all the changes to hidden field for backend processing
            var changedEvents = [];
            $.each(this.registry.events(), function(uuid, event) {
                changedEvents.push({
                    user_id: event.user_id,
                    room_id: event.room_id,
                    sales_item_id: event.sales_item_id,
                    start_at: event.start.format('YYYY-MM-DD HH:mm:00'),
                    end_at: event.end.format('YYYY-MM-DD HH:mm:00'),
                    uuid: uuid,
                    deleted: event.deleted
                });
            });
            var hiddenFieldId = 'registry_json' + this.uuid;
            var hiddenField = $('#' + hiddenFieldId);
            if (hiddenField.length == 0) {
                hiddenField = $('<input id="' + hiddenFieldId + '" type="hidden" name="' + this.options.registry_json_field_name + '">');
                hiddenField.insertAfter(this.element);
            }
            hiddenField.val(JSON.stringify(changedEvents));
        },

        generateUuid: function () {
            var i, random;
            var uuid = '';

            for (i = 0; i < 32; i++) {
                random = Math.random() * 16 | 0;
                if (i === 8 || i === 12 || i === 16 || i === 20) {
                    uuid += '-';
                }
                uuid += (i === 12 ? 4 : (i === 16 ? (random & 3 | 8) : random))
                    .toString(16);
            }

            return uuid;
        },

        registry: {
            _events: {},
            _listeners: [],
            add: function(uuid, data) {
                this._events[uuid] = data;
                this.dispatchChange(data);
            },
            update: function(uuid, data) {
                this._events[uuid] = data;
                this.dispatchChange(data);
            },
            delete: function(event) {
                if (event.uuid) {
                    delete this._events[ event.uuid ];
                }
                this.dispatchChange(event);
            },
            event: function(uuid) {
                return this._events.hasOwnProperty(uuid) ? this._events[uuid] : {};
            },
            events: function() {
                return this._events;
            },
            setEvents: function(events) {
                this._events = events;
                this.dispatchChange({});
            },
            subscribe: function(handler, context) {
                this._listeners.push(handler.bind(context));
            },
            dispatchChange: function(contextEvent) {
                //console.log('events in registry:')
                //console.log(this._events);
                $.each(this._listeners, function(i, listener) {
                    listener(this._events, contextEvent);
                }.bind(this));
            }
        },

        getOrderItemsState: function() {
            var state = '';
            $('[data-order-item]').each(function() {
                state += $(this).attr('data-order-item') + '-';
            });
            return state;
        },

        areOrderItemsChanged: function() {
            console.log('state: '+this.orderItemsState + ' <> '+this.getOrderItemsState())
            return this.orderItemsState !== this.getOrderItemsState();
        },

        updateOrderItemsState: function() {
            this.orderItemsState = this.getOrderItemsState();
        }

    });

    return $.mage.timetable;
});
