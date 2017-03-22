$(document).ready(function () {
    $.fn.searchbar = function (options) {
        // Defaults
        var defaults = {
            data: {},
            loginurl: '/',
            objekturl: '/',
            objektlisturl: '/',
            hausurl: '/',
            hauslisturl: '/',
            einheiturl: '/',
            einheitlisturl: '/',
            personurl: '/',
            personlisturl: '/',
            partnerurl: '/',
            partnerlisturl: '/'
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
                objektlisturl = options.objektlisturl,
                hausurl = options.hausurl,
                hauslisturl = options.hauslisturl,
                einheiturl = options.einheiturl,
                einheitlisturl = options.einheitlisturl,
                personurl = options.personurl,
                personlisturl = options.personlisturl,
                partnerurl = options.partnerurl,
                partnerlisturl = options.partnerlisturl;

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

            var wait = function (time) {
                var deferred = $.Deferred();
                var timeout = setTimeout(function () {
                    deferred.resolve();
                }, time);
                var promise = {
                    abort: function () {
                        deferred.reject();
                        clearTimeout(timeout);
                    }
                };
                return deferred.promise(promise);
            };

            var $items = [];
            var focusItem = 1;

            var search = function (query) {
                return $.getJSON("/api/v1/search?q=" + query).done(function (data) {
                    $autocomplete.empty();
                    $items = [];
                    query = $.map(query.split(' '), function (item) {
                        return '"' + item + '"';
                    }).join(' ');
                    if ($.isArray(data['objekte']) && !$.isEmptyObject(data['objekte'])) {
                        $items.push($("<li class='grey accent-3'><a tabindex='-1' class='white-text active-alternative' href='" + objektlisturl + query + "'>Objekte<span class='new badge' data-badge-caption=''>" + data['objekte'].length + "</span></a></li>"));
                        $.each(data['objekte'], function (key, val) {
                            $items.push($("<li id='" + val['OBJEKT_ID'] + "'><a tabindex='-1' href='" + objekturl + val['OBJEKT_ID'] + "'>" + val['OBJEKT_KURZNAME'] + "</a></li>"));
                        });
                    }
                    if ($.isArray(data['haeuser']) && !$.isEmptyObject(data['haeuser'])) {
                        $items.push($("<li class='grey accent-3'><a tabindex='-1' class='white-text active-alternative' href='" + hauslisturl + query + "'>Häuser<span class='new badge' data-badge-caption=''>" + data['haeuser'].length + "</span></a></li>"));
                        $.each(data['haeuser'], function (key, val) {
                            $items.push($("<li id='" + val['HAUS_ID'] + "'><a tabindex='-1' href='" + hausurl + val['HAUS_ID'] + "'>" + val['HAUS_STRASSE'] + " " + val['HAUS_NUMMER'] + "</a></li>"));
                        });
                    }
                    if ($.isArray(data['einheiten']) && !$.isEmptyObject(data['einheiten'])) {
                        $items.push($("<li class='grey accent-3'><a tabindex='-1' class='white-text active-alternative' href='" + einheitlisturl + query + "'>Einheiten<span class='new badge' data-badge-caption=''>" + data['einheiten'].length + "</span></a></li>"));
                        $.each(data['einheiten'], function (key, val) {
                            $items.push($("<li id='" + val['EINHEIT_ID'] + "'><a tabindex='-1' href='" + einheiturl + val['EINHEIT_ID'] + "'>" + val['EINHEIT_KURZNAME'] + "</a></li>"));
                        });
                    }
                    if ($.isArray(data['personen']) && !$.isEmptyObject(data['personen'])) {
                        $items.push($("<li class='grey accent-3'><a tabindex='-1' class='white-text active-alternative' href='" + personlisturl + query + "'>Personen<span class='new badge' data-badge-caption=''>" + data['personen'].length + "</span></a></li>"));
                        $.each(data['personen'], function (key, val) {
                            $items.push($("<li id='" + val['PERSON_ID'] + "'><a tabindex='-1' href='" + personurl + val['PERSON_ID'] + "'>" + val['PERSON_NACHNAME'] + ", " + val['PERSON_VORNAME'] + "</a></li>"));
                        });
                    }
                    if ($.isArray(data['partner']) && !$.isEmptyObject(data['partner'])) {
                        $items.push($("<li class='grey accent-3'><a tabindex='-1' class='white-text active-alternative' href='" + partnerlisturl + query + "'>Partner<span class='new badge' data-badge-caption=''>" + data['partner'].length + "</span></a></li>"));
                        $.each(data['partner'], function (key, val) {
                            $items.push($("<li id='" + val['PARTNER_ID'] + "'><a tabindex='-1' href='" + partnerurl + val['PARTNER_ID'] + "'>" + val['PARTNER_NAME'] + "</a></li>"));
                        });
                    }
                    $.each($items, function (index, $item) {
                        $item.hover(function () {
                            $items[focusItem].find('a').first().removeClass('active');
                            focusItem = index;
                        }, function () {
                            $item.find('a').first().addClass('active')
                        });
                    });
                    $autocomplete.append($items);
                    $indicator.hide();
                    $close.show();
                    if(!$.isEmptyObject($items)) {
                        $items[focusItem].find('a').first().addClass('active');
                    }
                    focusItem = 1;
                });
            };

            var timeout = null;
            var query = '';
            var when = null;

            $input.on('keydown', function (e) {
                if (e.which === KeyCode.KEY_RETURN
                    || e.which === KeyCode.KEY_ENTER
                    || e.which === KeyCode.KEY_UP
                    || e.which === KeyCode.KEY_DOWN
                ) {
                    e.preventDefault();
                }
            });

            // Perform search
            $input.on('keyup', function (e) {
                if (e.which === KeyCode.KEY_RETURN || e.which === KeyCode.KEY_ENTER) {
                    if (when) {
                        when.then(function () {
                            if (!$.isEmptyObject($items)) {
                                var a = $items[focusItem].find('a').first();
                                if (a) {
                                     $(a)[0].click()
                                }
                            }
                        });
                    }
                    return;
                }

                if (e.which === KeyCode.KEY_UP) {
                    if (!$.isEmptyObject($items)) {
                        $items[focusItem].find('a').first().removeClass('active');
                        if (focusItem === 0) {
                            focusItem = $items.length - 1;
                        } else {
                            focusItem--;
                        }
                        $autocomplete.animate({
                            scrollTop: $autocomplete.scrollTop() - 200 + $items[focusItem].position().top
                        }, 100);
                        $items[focusItem].find('a').first().addClass('active');
                    }
                    return;
                }

                if (e.which === KeyCode.KEY_DOWN) {
                    if (!$.isEmptyObject($items)) {
                        $items[focusItem].find('a').first().removeClass('active');
                        if (focusItem === $items.length - 1) {
                            focusItem = 0;
                        } else {
                            focusItem++;
                        }
                        $autocomplete.animate({
                            scrollTop: $autocomplete.scrollTop() - 200 + $items[focusItem].position().top
                        }, 100);
                        $items[focusItem].find('a').first().addClass('active');
                    }
                    return;
                }

                var val = $input.val().toLowerCase().trim();
                if (val !== query) {
                    query = val;
                    $indicator.show();
                    $close.hide();
                    if (timeout && timeout.state() === "pending") {
                        timeout.abort();
                    }
                    if (val !== '') {
                        timeout = wait(300);
                        when = timeout.then(function () {
                            return search(val);
                        });
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
                var $related = $(event.relatedTarget);
                if ($.contains($autocomplete[0], $related[0])) {
                    $related[0].click();
                }
                $autocomplete.hide();
            });
            $input.on('focusin', function () {
                $autocomplete.show();
            });
        });
    };
});