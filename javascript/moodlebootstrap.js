
require(['core/first'], function() {
    require(['theme_paper/bootstrap','core/log'], function(b, log) {
        log.debug('Bootstrap JavaScript initialised');
    });
});
