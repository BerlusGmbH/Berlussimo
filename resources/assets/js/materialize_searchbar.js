$(document).ready(function () {
    $.fn.searchbar = function (options) {
        // Defaults
        var defaults = {
            data: {},
            loginurl: '/',
            objekturl: '/',
            hausurl: '/',
            einheiturl: '/',
            personurl: '/',
            partnerurl: '/'
        };

        options = $.extend(defaults, options);

        return this.each(function () {
            var $input = $(this);
            var data = options.data,
                $inputDiv = $input.closest('.input-field'),
                $close = $('#searchbarClose'),
                $indicator = $('#searchbarIndicator'),
                loginurl = options.loginurl,
                objekturl = options.objekturl,
                hausurl = options.hausurl,
                einheiturl = options.einheiturl,
                personurl = options.personurl,
                partnerurl = options.partnerurl;

            // Create autocomplete element
            var $autocomplete = $('<ul class="autocomplete-content dropdown-content"></ul>');

            // Append autocomplete element
            if ($inputDiv.length) {
                $inputDiv.append($autocomplete); // Set ul in body
            } else {
                $input.after($autocomplete);
            }

            var highlight = function (string, $el) {
                var img = $el.find('img');
                var matchStart = $el.text().toLowerCase().indexOf("" + string.toLowerCase() + ""),
                    matchEnd = matchStart + string.length - 1,
                    beforeMatch = $el.text().slice(0, matchStart),
                    matchText = $el.text().slice(matchStart, matchEnd + 1),
                    afterMatch = $el.text().slice(matchEnd + 1);
                $el.html("<span>" + beforeMatch + "<span class='highlight'>" + matchText + "</span>" + afterMatch + "</span>");
                if (img.length) {
                    $el.prepend(img);
                }
            };

            var search = function (query) {
                $.getJSON("/api/v1/search?q=" + query, function (data) {
                    $autocomplete.empty();
                    var items = [];
                    if ($.isArray(data['objekte']) && !$.isEmptyObject(data['objekte'])) {
                        items.push("<li><span class='grey accent-3 white-text'>Objekte<span class='new badge' data-badge-caption=''>" + data['objekte'].length + "</span></span></li>");
                        $.each(data['objekte'], function (key, val) {
                            items.push("<li id='" + val['OBJEKT_ID'] + "'><a href='" + objekturl + val['OBJEKT_ID'] + "'><span>" + val['OBJEKT_KURZNAME'] + "</span></a></li>");
                        });
                    }
                    if ($.isArray(data['haeuser']) && !$.isEmptyObject(data['haeuser'])) {
                        items.push("<li><span class='grey accent-3 white-text'>Häuser<span class='new badge' data-badge-caption=''>" + data['haeuser'].length + "</span></span></li>");
                        $.each(data['haeuser'], function (key, val) {
                            items.push("<li id='" + val['HAUS_ID'] + "'><a href='" + hausurl + val['HAUS_ID'] + "'><span>" + val['HAUS_STRASSE'] + " " + val['HAUS_NUMMER'] + "</span></a></li>");
                        });
                    }
                    if ($.isArray(data['einheiten']) && !$.isEmptyObject(data['einheiten'])) {
                        items.push("<li><span class='grey accent-3 white-text'>Einheiten<span class='new badge' data-badge-caption=''>" + data['einheiten'].length + "</span></span></li>");
                        $.each(data['einheiten'], function (key, val) {
                            items.push("<li id='" + val['EINHEIT_ID'] + "'><a href='" + einheiturl + val['EINHEIT_ID'] + "'><span>" + val['EINHEIT_KURZNAME'] + "</span></a></li>");
                        });
                    }
                    if ($.isArray(data['personen']) && !$.isEmptyObject(data['personen'])) {
                        items.push("<li><span class='grey accent-3 white-text'>Personen<span class='new badge' data-badge-caption=''>" + data['personen'].length + "</span></span></li>");
                        $.each(data['personen'], function (key, val) {
                            items.push("<li id='" + val['PERSON_ID'] + "'><a href='" + personurl + val['PERSON_ID'] + "'><span>" + val['PERSON_NACHNAME'] + ", " + val['PERSON_VORNAME'] + "</span></a></li>");
                        });
                    }
                    if ($.isArray(data['partner']) && !$.isEmptyObject(data['partner'])) {
                        items.push("<li><span class='grey accent-3 white-text'>Partner<span class='new badge' data-badge-caption=''>" + data['partner'].length + "</span></span></li>");
                        $.each(data['partner'], function (key, val) {
                            items.push("<li id='" + val['PARTNER_ID'] + "'><a href='" + partnerurl + val['PARTNER_ID'] + "'><span>" + val['PARTNER_NAME'] + "</span></a></li>");
                        });
                    }
                    $autocomplete.append(items.join(""));
                    $indicator.hide();
                    $close.show();
                });
            };

            var queryTimeout = null;
            var query = '';

            // Perform search
            $input.on('keyup', function (e) {
                var val = $input.val().toLowerCase().trim();
                if (val !== query) {
                    query = val;
                    $indicator.show();
                    $close.hide();
                    if (queryTimeout !== null) {
                        clearTimeout(queryTimeout);
                    }
                    if (val !== '') {
                        queryTimeout = setTimeout(function () {
                            search(val);
                        }, 300);
                    } else {
                        $indicator.hide();
                        $close.show();
                        $autocomplete.empty();
                    }
                }
            });

            $close.on('click', function () {
                $input.val('');
                $autocomplete.empty();
            });
            $input.on('focusout', function (event) {
                if (event.relatedTarget) {
                    $(event.relatedTarget)[0].click();
                }
                $autocomplete.empty();
            });
            $input.on('focusin', function () {
                if ($input.val().length !== 0) {
                    search($input.val());
                }
            });
        });
    };
});