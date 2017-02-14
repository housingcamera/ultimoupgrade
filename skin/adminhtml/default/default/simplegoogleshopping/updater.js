var updater = {
    init: function() {
        data = new Array();
        $$('.updater').each(function(u) {
            feed = [u.id.replace("feed_", ""), u.readAttribute('cron')];
            data.push(feed);
        })

        new Ajax.Request(updater_url, {
            method: 'post',
            parameters: {data: Object.toJSON(data)},
            loaderArea: false,
            onSuccess: function(response) {
                resp = (response.responseText.evalJSON());
                resp.each(function(r) {
                    $("feed_" + r.id).replace(r.content)
                })
                setTimeout(function() {
                    updater.init()
                }, 1000)
            }
        });

    }
}

document.observe('dom:loaded', function() {
    updater.init();
})
