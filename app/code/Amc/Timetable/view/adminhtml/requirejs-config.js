var config = {
    map: {
        '*': {
            "Magento_Sales/order/create/form": "Amc_Timetable/order/create/form",
            "Magento_Sales/order/create/scripts": "Amc_Timetable/order/create/scripts",
            fullcalendar: 'fullcalendar-scheduler-1.0.2/lib/fullcalendar.min',
            fullcalendarScheduler: 'fullcalendar-scheduler-1.0.2/scheduler'
        }
    },
    shim: {
        "fullcalendar-scheduler-1.0.2/scheduler": [ "fullcalendar"  ]
    }
};
