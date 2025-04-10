var config = {
    paths: {
        'vishal-events/js/form/element/multidate-picker': 'Vishal_Events/js/form/element/multidate-picker',
        'vishal-events/js/form/date-time-fieldset': 'Vishal_Events/js/form/date-time-fieldset',
        'vishal-events/js/form/element/time-mode-tabs': 'Vishal_Events/js/form/element/time-mode-tabs',
        'vishal-events/js/form/element/time-slots-dynamic': 'Vishal_Events/js/form/element/time-slots-dynamic',
         'vishal-events/js/form/element/datetime-button': 'Vishal_Events/js/form/element/datetime-button',
         'vishal-events/js/event/datetime-modal': 'Vishal_Events/js/event/datetime-modal'
    },
    map: {
        '*': {
            'Vishal_Events/js/form/element/multidate-picker': 'vishal-events/js/form/element/multidate-picker',
            'Vishal_Events/js/form/date-time-fieldset': 'vishal-events/js/form/date-time-fieldset',
            'Vishal_Events/js/form/element/time-mode-tabs': 'vishal-events/js/form/element/time-mode-tabs',
            'Vishal_Events/js/form/element/time-slots-dynamic': 'vishal-events/js/form/element/time-slots-dynamic',
            'Vishal_Events/js/form/element/datetime-button': 'vishal-events/js/form/element/datetime-button',
            'Vishal_Events/js/event/datetime-modal': 'vishal-events/js/event/datetime-modal',
             'Vishal_Events/js/time-validation': 'vishal-events/js/time-validation'
        }
    },
    shim: {
        'Vishal_Events/js/form/date-time-fieldset': {
            deps: ['moment']
        },
        'Vishal_Events/js/form/loading': {
            deps: ['jquery', 'Vishal_Events/js/event/datetime-modal']
        }
    }

};