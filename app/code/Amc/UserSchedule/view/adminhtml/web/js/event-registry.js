define(function() {
    return {
        registry: {
            events: {},
            registerEvent: function(event) {
                this.events[event._id] = event;
            },
            delete: function(event) {
                event.deleted = 1;
                this.events[event._id] = event;
            },
            getEvents: function () {
                return this.events;
            },
            pushRegistry: function(key, value) {
                this[key] = value;
            },
            popRegistry: function(key) {
                var result = this[key];
                delete this[key];
                return result;
            },
            reset: function() {
                this.events = {};
            }
        }
    }
});
