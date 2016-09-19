var config = {
    map: {
        '*': {
            fullcalendar: 'fullcalendar-scheduler/lib/fullcalendar.min',
            fullcalendarScheduler: 'fullcalendar-scheduler/scheduler'
        }
    },
    shim: {
        "fullcalendar-scheduler/scheduler": [ "fullcalendar"  ]
    }
};
