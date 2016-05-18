$(document).ready(function () {

    /**************************
     * Auto complete plugin  *
     *************************/
    $.fn.materialize_autocomplete = function (options) {
        // Defaults
        var defaults = {
            data: {}
        };

        options = $.extend(defaults, options);

        return this.each(function () {
            var $input = $(this);
            var data = options.data,
                $inputDiv = $input.closest('.input-field'); // Div to append on

            // Check if data isn't empty
            if (!$.isEmptyObject(data)) {
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
                    // Capture Enter
                    if (e.which === 13) {
                        $autocomplete.find('li').first().click();
                        return;
                    }

                    var val = $input.val().toLowerCase();
                    $autocomplete.empty();

                    // Check if the input isn't empty
                    if (val !== '') {
                        for (var key in data) {
                            if (data.hasOwnProperty(key) &&
                                key.toLowerCase().indexOf(val) !== -1 &&
                                key.toLowerCase() !== val) {
                                var autocompleteOption = $('<li></li>');
                                autocompleteOption.data('key', key);
                                autocompleteOption.append('<span>' + key + '</span>');
                                highlight(val, autocompleteOption);
                                var autocompleteOptionContent = autocompleteOption.find('span').first();

                                if (!!data[key].pretag) {
                                    autocompleteOptionContent.prepend(data[key].pretag + ' ');
                                }

                                if (!!data[key].posttag) {
                                    autocompleteOptionContent.append(' ' + data[key].posttag);
                                }

                                if (!!data[key].icons) {
                                    var iconCount = data[key].icons.length;
                                    for (var i = 0; i < iconCount; i++) {
                                        if(!!data[key].icons[i].link) {
                                            var link = $('<a style="float: right" target="_blank" href="' + data[key].icons[i].link + '"><i class="material-icons">' + data[key].icons[i].icon + '</i></a>');
                                            link.on('click', function (e) {
                                                e.stopPropagation();
                                            });
                                            autocompleteOptionContent.append(link);
                                        } else {
                                            autocompleteOptionContent.append('<i style="float: right" class="material-icons">' + data[key].icons[i].icon + '</i>');
                                        }
                                    }
                                }

                                $autocomplete.append(autocompleteOption);
                            }
                        }
                    }
                });

                // Set input value
                $autocomplete.on('click', 'li', function () {
                    var key = $(this).data('key');
                    $input.val(key);
                    $autocomplete.empty();
                    $autocomplete.trigger('autocomplete.selected', [key, data]);
                });
            }
        });
    };

});