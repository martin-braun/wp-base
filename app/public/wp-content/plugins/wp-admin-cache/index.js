function wp_admin_cache_prefetch(urls) {
    if (urls.length == 0) return;

    var dbI = urls.indexOf("index.php");
    if (dbI > 0) {// priority for dashboard
        urls.splice(0, 0, urls.splice(dbI, 1)[0]);
    }

    var urlIndex = 0;
    var expiration = 0;
    var refresh = 0;

    function exec() {
        if (urls.length == 0) return;
        if (urlIndex == urls.length) {
            urlIndex = 0;
            var delay = expiration - 10000;
            if (delay < 2000) delay = 2000;
            expiration = 0;
            setTimeout(exec, delay);
            refresh = 1;
            return;
        }
        jQuery.ajax({
            url: urls[urlIndex],
            method: 'POST',
            data: {'wp_admin_cache_prefetch': 1, 'wp_admin_cache_refresh': refresh}
        }).done(function (data) {
            if (data.indexOf("prefetched") == 0 || data.indexOf("prefetching") == 0) {
                var exp = data.split(':')[1] * 1000;
                if (exp < expiration || expiration == 0) expiration = exp;
				
            }
            urlIndex++;
            setTimeout(exec, 10);
        }).fail(function (jqXHR, textStatus, errorThrown) {
            urlIndex++;
            setTimeout(exec, 10);
        });


    }
    jQuery(exec);

}

jQuery(function ($) {
    $(".wp-admin-cache-selectAll").on("click", function () {
        var check = this;
        $(this).closest("form").find(".wp-admin-cache-pageList input[type=checkbox]").each(function () {
            this.checked = check.checked;
        });
    });

});