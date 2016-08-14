define(function() {
    return {
        registry: {
            events: {},
            registerEvent: function(event) {
                if (!this.events[event._id]) {
                    this.events[event._id] = event;
                }
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
