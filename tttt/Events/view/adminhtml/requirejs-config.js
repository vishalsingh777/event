var config = {
    paths: {
        'vishal-events/js/form/element/multidate-picker': 'Vishal_Events/js/form/element/multidate-picker',
        'vishal-events/js/form/date-time-fieldset': 'Vishal_Events/js/form/date-time-fieldset'
    },
    map: {
        '*': {
            'Vishal_Events/js/form/element/multidate-picker': 'vishal-events/js/form/element/multidate-picker',
            'Vishal_Events/js/form/date-time-fieldset': 'vishal-events/js/form/date-time-fieldset'
        }
    },
    shim: {
        'Vishal_Events/js/form/date-time-fieldset': {
            deps: ['moment']
        }
    }
};