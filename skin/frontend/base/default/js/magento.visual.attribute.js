/*!
 * jQuery Visual Attribute for Magento
 * Use skinnable HTML for attributes instead of form field
 *
 * Copyright 2011, Arne Stulp
 */

(function($) {
    $.fn.visualAttribute = function(options) {
        var defaults = {
            useTitle: false,
            attrClicked : function(data) {
                return true;
            },
            attrUpdated : function(data) {
            }
        };
        var settings = $.extend(defaults, options);

        //loop all attributes
        var selectbox_counter = 0;
        return this.each(function() {
            //use counter for a unique class for each wrapper
            selectbox_counter++;

            //store reference to attribute selectbox
            var selectbox = $(this);

            //hide the default dropdown (but keep it in dom for posting the required values)
            $(this).css('position', 'absolute').css('left', '-100000px').show();

            //insert wrapper for options
            var wrapper = $('<ul />')
                    .attr("class", "va_wrapper")
                    .attr("id", "va_wrapper_" + selectbox_counter)
                    .appendTo($(this).parent());

            $(this).parent().append('<div style="clear:both"></div>');

            if (selectbox.attr("id") != "") {
                wrapper.attr("rel", selectbox.attr("id"));
            }

            //store all values of the dropdown in an array
            var options = [];
            var option_counter = 0;
            var description = '';

            selectbox.children('option').each(function() {
                option_counter++;

                if (option_counter == 1) {
                    //first option contains the description, e.g. 'please select size'
                    description = $(this).text();
                }

                //only use option if has a value
                var value = $(this).val();
                if (value != '') {
                    options[value] = ({value : value, text : $(this).text()});
                }
            });

            //loop all stored options and create custom html
            var pos = 0;
            if (options.length) {
                for (var index in options) {
                    if (!isNaN(index)) {
                        pos++;
                        var value = index;
                        var text = options[index].text;
                        options[index].position = pos;
                        if (!settings.useTitle) {
                            description = '';
                        }
                        wrapper.append('<li title="' + description + '" class="opt_' + value + '"><a href="#' + value + '">' + text + '</a></li>');
                    }
                }
            }

            //set custom options to same value as current selectbox value (only needed in rare cases)
            var current_value = selectbox.val();
            if (current_value > 0) {
                $("#va_wrapper_" + selectbox_counter + ' li.opt_' + current_value + ' a').addClass('selected');
            }

            //event handler for catching a clicked attribute
            $("#va_wrapper_" + selectbox_counter + ' li a').click(function() {

                var value = $(this).attr("href").split('#')[1];

                //use callback
                if (!settings.attrClicked(options[value])) {
                    return false;
                }

                //set value and class
                selectbox.val(value);
                $("#va_wrapper_" + selectbox_counter + ' .selected').removeClass('selected');
                $(this).addClass('selected');

                //use callback
                settings.attrUpdated(options[value]);

                return false;
            });
        });
    };
})(jQuery);