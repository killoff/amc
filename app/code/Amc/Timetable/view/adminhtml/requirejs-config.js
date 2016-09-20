var config = {
    map: {
        '*': {
            fullcalendar: 'fullcalendar-scheduler/lib/fullcalendar.min',
            fullcalendarScheduler: 'fullcalendar-scheduler/scheduler',
            moment: 'fullcalendar-scheduler/lib/moment.min'
        }
    },
    shim: {
        "fullcalendar-scheduler/scheduler": [ "fullcalendar"  ]
    }
};
