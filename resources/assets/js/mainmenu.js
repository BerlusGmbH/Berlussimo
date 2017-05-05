$('document').ready(function () {
    $(".mainmenu .collapsible li").on('mouseenter', function (e) {
        var $target = $(e.target);
        var $header;
        if ($target.hasClass('collapsible-header')) {
            $header = $target;
        } else {
            $header = $target.parents('li').first().find('.collapsible-header').first();
        }
        var $collapsible = $target.parents('.collapsible');
        if ($header.text() === "Tools") {
            $collapsible.collapsible('open', 1);
        } else {
            $collapsible.collapsible('open', 0);
        }
    }).on('mouseleave', function (e) {
        var $target = $(e.target);
        var $header;
        if ($target.hasClass('collapsible-header')) {
            $header = $target;
        } else {
            $header = $target.parents('li').first().find('.collapsible-header').first();
        }
        var $collapsible = $target.parents('.collapsible');
        if ($header.text() === "Tools") {
            $collapsible.collapsible('close', 1);
        } else {
            $collapsible.collapsible('close', 0);
        }
    });
});