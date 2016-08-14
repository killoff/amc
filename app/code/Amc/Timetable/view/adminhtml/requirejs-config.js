var config = {
    map: {
        '*': {
            fullcalendar: 'fullcalendar-scheduler-1.0.2/lib/fullcalendar.min',
            fullcalendarScheduler: 'fullcalendar-scheduler-1.0.2/scheduler'
        }
    },
    shim: {
        "fullcalendar-scheduler-1.0.2/scheduler": [ "fullcalendar"  ]
    }
};
