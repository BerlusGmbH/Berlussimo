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
                $close = $inputDiv.children('.material-icons'),
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

            // Perform search
            $input.on('keyup', function (e) {
                var val = $input.val().toLowerCase();
                if (val !== '') {
                    $.getJSON("api/v1/search?q=" + val, function (data) {
                        $autocomplete.empty();
                        var items = [];
                        if ($.isArray(data['objekte']) && !$.isEmptyObject(data['objekte'])) {
                            items.push("<li><span class='grey accent-3 white-text'>Objekte<span class='new badge' data-badge-caption=''>" + data['objekte'].length + "</span></span></li>");
                            $.each(data['objekte'], function (key, val) {
                                items.push("<li id='" + val['OBJEKT_ID'] + "'><span>" + val['OBJEKT_KURZNAME'] + "</span></li>");
                            });
                        }
                        if ($.isArray(data['haeuser']) && !$.isEmptyObject(data['haeuser'])) {
                            items.push("<li><span class='grey accent-3 white-text'>Häuser<span class='new badge' data-badge-caption=''>" + data['haeuser'].length + "</span></span></li>");
                            $.each(data['haeuser'], function (key, val) {
                                items.push("<li id='" + val['HAUS_ID'] + "'><span>" + val['HAUS_STRASSE'] + " " + val['HAUS_NUMMER'] + "</span></li>");
                            });
                        }
                        if ($.isArray(data['einheiten']) && !$.isEmptyObject(data['einheiten'])) {
                            items.push("<li><span class='grey accent-3 white-text'>Einheiten<span class='new badge' data-badge-caption=''>" + data['einheiten'].length + "</span></span></li>");
                            $.each(data['einheiten'], function (key, val) {
                                items.push("<li id='" + val['EINHEIT_ID'] + "'><span>" + val['EINHEIT_KURZNAME'] + "</span></li>");
                            });
                        }
                        if ($.isArray(data['personen']) && !$.isEmptyObject(data['personen'])) {
                            items.push("<li><span class='grey accent-3 white-text'>Personen<span class='new badge' data-badge-caption=''>" + data['personen'].length + "</span></span></li>");
                            $.each(data['personen'], function (key, val) {
                                items.push("<li id='" + val['PERSON_ID'] + "'><span>" + val['PERSON_NACHNAME'] + ", " + val['PERSON_VORNAME'] + "</span></li>");
                            });
                        }
                        if ($.isArray(data['partner']) && !$.isEmptyObject(data['partner'])) {
                            items.push("<li><span class='grey accent-3 white-text'>Partner<span class='new badge' data-badge-caption=''>" + data['partner'].length + "</span></span></li>");
                            $.each(data['partner'], function (key, val) {
                                items.push("<li id='" + val['PARTNER_ID'] + "'><span>" + val['PARTNER_NAME'] + "</span></li>");
                            });
                        }
                        $autocomplete.append(items.join(""));
                    });
                } else {
                    $autocomplete.empty();
                }
                // Check if the input isn't empty

                // for (var key in data) {
                //     if (data.hasOwnProperty(key) &&
                //         key.toLowerCase().indexOf(val) !== -1 &&
                //         key.toLowerCase() !== val) {
                //         var autocompleteOption = $('<li></li>');
                //         if (!!data[key]) {
                //             autocompleteOption.append('<img src="' + data[key] + '" class="right circle"><span>' + key + '</span>');
                //         } else {
                //             autocompleteOption.append('<span>' + key + '</span>');
                //         }
                //         $autocomplete.append(autocompleteOption);
                //
                //         highlight(val, autocompleteOption);
                //     }
                // }
            });

            $close.on('click', function () {
                $input.val('');
                $autocomplete.empty();
            });
            $input.on('focusout', function () {
                $autocomplete.empty();
            });
            $input.on('focusin', function () {
               if($input.val().length !== 0) {
                   $input.trigger('keyup');
               }
            });
        });
    };
});