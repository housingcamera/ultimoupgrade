Validation.creditCartTypes = Validation.creditCartTypes.merge({
    'ESELECTPLUS_DINERSCARD': [false, new RegExp('^([0-9]{3}|[0-9]{4})?$'), false],
});