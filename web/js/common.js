var DEBUG = true;

function console_log() {
    if (DEBUG) {
        if (console) {
            console.log.apply(console, arguments);
        }
    }
}

$(window).load(function () {
    //Disable html5 validation
    $("form").each(function () {
        $(this).attr('novalidate', 'novalidate');
    });
});

$(function () {
    // menu javascript
    $('.menu-sub-tabs li a').on("click", function (e) {
        e.preventDefault();
        var selectTabs = $(this).attr('data-id');

        $("ul.menu-sub-tabs li").removeClass("active");
        $(this).parent().addClass("active");

        if (selectTabs == 'offerer' || selectTabs == 'asker') {
            Cookies.set('userType', selectTabs, {'path': '/'});
            $('ul.display-sub-tabs').find('li.dropdown').removeClass('open');
        }
        $('ul.display-sub-tabs').find('li[data-id=' + selectTabs + '].dropdown').addClass('open');
        return false;
    });
    // end of menu javascript

    $('.display-tab .dropdown-menu li a').on("click", function (e) {
        var selectTabs = $(this).parents('.display-tab').attr('data-id');
        if (selectTabs == 'offerer' || selectTabs == 'asker') {
            Cookies.set('userType', selectTabs, {'path': '/'});
        }
    });

    $('.numbers-only').keyup(function (e) {
        $(this).val($(this).val().replace(/[^0-9\.,]/g, ''));
    });

    // Favourites click event
    $('#main').on('click', 'a.favourit', function (evt) {
        var cookieList = $.fn.cookieList("favourite");
        var idString = $(this).attr('id');
        var ids = idString.split('-');
        var id = (ids[1]) ? ids[1] : null;
        // toggle the active class when clicked
        if ($(this).hasClass('active')) {
            $(this).removeClass('active');
            cookieList.remove(id);
        } else {
            $(this).addClass('active');
            cookieList.add(id);
        }
        if (cookieList.items().length > 0) {
            $('#fav-count').html("(" + cookieList.items().length + ")");
        } else {
            $('#fav-count').html(" ");
        }
    });

    // Rating
    $("input[name=radio-rating-switcher]:radio").change(function () {
        window.location = $(this).val();
    });

    // Rating to radio buttons
    var $userRatings = $('#user-rating-make li');
    $userRatings.on('click', 'a.a-star-rating', function (evt) {
        var id = $(this).attr('data-value');
        var cnt = 0;
        $userRatings.each(function () {
            if (id >= cnt) {
                $(this).addClass('active');
            } else {
                $(this).removeClass('active');
            }
            cnt++;
        });
        $('#rating_' + id).attr('checked', 'checked');
    });

    var radioValue = $("input[name='rating']:checked").val();
    if (radioValue) {
        var cntStar = 0;
        $userRatings.each(function () {
            if (radioValue > cntStar) {
                $(this).addClass('active');
                cntStar++;
            }
        });
    }

    //Payin dashboard switcher
    $("input[name=radio-payin-switcher]:radio").change(function () {
        window.location = $(this).val();
    });

    // Facebook unwanted has characters
    cleanHash();

    fixIEMobile10();
    // Clearable input types
    $('input.clearable, .clearable input[type=text]').clearSearch({
        callback: function () {
        }
    });
    
    handleButtonClick();
});

/**
 * Simulate user click on a button type submit to make SF isClicked working if the click is done programmatically
 */
function handleButtonClick() {
    $("button[type=submit]").on('click', function (e) {
        var btnName = $(this).attr('name');
        var btnId = $(this).attr('id');
        if (btnId && btnName) {
            $(this).closest('form').find("input[type=hidden]#" + btnId).remove();
            $(this).closest('form').append('<input id="' + btnId + '" name="' + btnName + '" type="hidden" value="1">');
        }
    });
}

/**
 * Fix IE mobile
 */
function fixIEMobile10() {
    if (navigator.userAgent.match(/IEMobile\/10\.0/)) {
        var msViewportStyle = document.createElement('style');
        msViewportStyle.appendChild(
            document.createTextNode(
                '@-ms-viewport{width:auto!important}'
            )
        );
        document.querySelector('head').appendChild(msViewportStyle)
    }
}

/**
 * Init Multi Select Box
 * See fields.html > listing_category_widget_options_tree for indentation management
 *
 * @param elt
 * @param allSelectedText
 * @param width
 */
function initMultiSelect(elt, allSelectedText, noneSelectedText, numSelectedText, width) {
    jcf.destroy(elt);
    jcf.refresh(elt);

    width = typeof width !== 'undefined' ? width : '180px';

    //Replace 160 by 'nbsp'
    $(elt).find('option').each(function (index) {
        $(this).html($(this).text().replace(/&#160;&#160;&#160;/g, "&nbsp;&nbsp;&nbsp;"));
    });

    $(elt).multiselect({
        //buttonWidth: width,
        allSelectedText: allSelectedText,
        nonSelectedText: noneSelectedText,
        nSelectedText: numSelectedText,
        numberDisplayed: 1,
        enableClickableOptGroups: true,
        enableFiltering: true,
        enableCaseInsensitiveFiltering: true,
        buttonText: function (options, select) {
            //Replace nbsp by ''
            if (options.length === 0) {
                return this.nonSelectedText;
            }
            else if (this.allSelectedText
                && options.length === $('option', $(select)).length
                && $('option', $(select)).length !== 1
                && this.multiple) {

                if (this.selectAllNumber) {
                    return this.allSelectedText + ' (' + options.length + ')';
                }
                else {
                    return this.allSelectedText;
                }
            }
            else if (options.length > this.numberDisplayed) {
                return options.length + ' ' + this.nSelectedText;
            }
            else {
                var selected = '';
                var delimiter = this.delimiterText;

                options.each(function () {
                    var label = ($(this).attr('label') !== undefined) ?
                        $(this).attr('label').replace(/&nbsp;&nbsp;&nbsp;/g, '').replace(' - ', '') :
                        $(this).html().replace(/&nbsp;&nbsp;&nbsp;/g, '').replace(' - ', '');

                    selected += label + delimiter;
                });

                return selected.substr(0, selected.length - 2);
            }
        },
        buttonTitle: function (options, select) {
            if (options.length === 0) {
                return this.nonSelectedText;
            }
            else {
                var selected = '';
                var delimiter = this.delimiterText;

                options.each(function () {
                    var label = ($(this).attr('label') !== undefined) ?
                        $(this).attr('label').replace(/&nbsp;&nbsp;&nbsp;/g, '').replace(' - ', '') :
                        $(this).html().replace(/&nbsp;&nbsp;&nbsp;/g, '').replace(' - ', '');
                    selected += $.trim(label) + delimiter;
                });
                return selected.substr(0, selected.length - 2);
            }
        }
    });

    $(elt).next().find('.multiselect-group label').each(function (index) {
        $(this).html($(this).text().replace(/&#160;&#160;&#160;/g, "&nbsp;&nbsp;&nbsp;"));
    });
}


/**
 * Facebook unwanted has characters
 */
function cleanHash() {
    if (window.location.hash == '#_=_') {
        // Check if the browser supports history.replaceState.
        if (history.replaceState) {
            // Keep the exact URL up to the hash.
            var cleanHref = window.location.href.split('#')[0];
            // Replace the URL in the address bar without messing with the back button.
            history.replaceState(null, null, cleanHref);
        } else {
            // Well, you're on an old browser, we can get rid of the _=_ but not the #.
            window.location.hash = '';
        }
    }
}

/**
 * setFavourite class function
 */
function setDefaultFavourites() {
    var cookieList = $.fn.cookieList("favourite");
    $.each(cookieList.items(), function (index, value) {
        var $favorite = $('#favourite-' + value);
        if (!$favorite.hasClass('active')) {
            $favorite.addClass('active');
        }
    });
    if (cookieList.items().length > 0) {
        $('#fav-count').html("(" + cookieList.items().length + ")");
    } else {
        $('#fav-count').html(" ");
    }
}

/**
 * Currencies
 */
var currencies;
$.getJSON("/json/currencies.json", function (data) {
    currencies = data;
});

/**
 * Jquery currencies conversion management
 *
 * @param amount_one_elt First amount
 * @param amount_two_elt Second amount
 * @param currency_two_elt Currency of the second amount
 */
function currencyConversionHandler(amount_one_elt, amount_two_elt, currency_two_elt) {

    $(amount_two_elt).attr("data-currency", $(currency_two_elt).val());
    $(currency_two_elt).change(function () {
        $(amount_two_elt).attr("data-currency", $(this).val());

        $(amount_one_elt).val(
            convertCurrency(
                $(amount_two_elt).val(),
                $(amount_two_elt).attr("data-currency"),
                $(amount_one_elt).attr("data-currency")
            )
        );
    });

    $(amount_one_elt + "," + amount_two_elt).keyup(function (e) {
        var other = amount_one_elt;

        if ("#" + $(this).attr("id") == amount_one_elt) {
            other = amount_two_elt;
        }

        $(other).val(
            convertCurrency(
                $(this).val(),
                $(this).attr("data-currency"),
                $(other).attr("data-currency")
            )
        );
    });

}

/**
 * Convert currency
 *
 * @param  amount
 * @param  from
 * @param   to
 *
 * @returns number|string
 */
function convertCurrency(amount, from, to) {
    //console.log(amount, from, to);
    if (!to || !from || !amount) {
        return '';
    }

    var fromRate = currencies[from];
    var toRate = currencies[to];

    amount = amount.replace(/[^\d.,]/g, '');
    amount = parseInt(amount, 10);

    if (amount && fromRate && toRate) {
        //console_log(Math.round((amount / fromRate) * toRate));
        return Math.round((amount / fromRate) * toRate);// + toCurrencySymbol;
    }

    return '';
}


/**
 * Add form to collection function
 *
 * @param collection
 * @param item
 * @param callbackSuccess
 */
$.fn.addFormToCollection = function (collection, item, callbackSuccess) {
    var $container = this;
    var $addLink = $container.find("a.add");
    var $collectionHolder = $container.find(collection);
    $collectionHolder.data('index', $collectionHolder.find(item).length);

    $addLink.on('click', function (e) {
        e.preventDefault();
        addForm($collectionHolder);
    });

    function addForm($collectionHolder) {
        var prototype = $collectionHolder.parent('div').not(".errors").data('prototype');
        var index = $collectionHolder.data('index');

        var newForm = prototype.replace(/__name__/g, index);
        $collectionHolder.data('index', index + 1);
        $collectionHolder.append(newForm);
        jcf.replaceAll($collectionHolder);
        if (callbackSuccess !== undefined) {
            callbackSuccess();
        }
    }
};

/**
 * Submit ajax form function
 *
 * @param callbackSuccess
 */
$.fn.submitAjaxForm = function (callbackSuccess) {
    var $container = this;
    $container.find("form").submit(function (e) {
        e.preventDefault();

        $.ajax({
            type: $(this).attr('method'),
            url: $(this).attr('action'),
            data: $(this).serialize(),
            beforeSend: function (xhr) {
                $container.find(".flashes").hide();
            },
            success: function (response, status, xhr) {
                $container.replaceWith(response);
                callbackSuccess();
            }
        });

        return false;
    });
};

 /**
 * Bind profile switch change event.
 * Submit form on change.
 */
$('input[name="profileSwitch[profile]"]').on("change", function () {
    $('form[name="profileSwitch"]').submit();
});


/**
 * Get Nb unread messages
 */
function getNbUnReadMessages(url) {
    $.ajax({
        type: 'GET',
        url: url,
        success: function (result) {
            if (result.total > 0) {
                $('#nb-unread-msg').html(" (" + result.total + ")");
            }
            if (result.asker > 0) {
                $('#askerMsg').html(" (" + result.asker + ")");
                $('#nb-unread-asker').html(" (" + result.asker + ")");
            }
            if (result.offerer > 0) {
                $('#offererMsg').html(" (" + result.offerer + ")");
                $('#nb-unread-offerer').html(" (" + result.offerer + ")");
            }
        }
    });
}

/**
 * centerModal centers the modal box when window resized
 * @return void
 */
function centerModal() {
    $(this).css('display', 'block');
    var $dialog = $(this).find(".modal-dialog");
    var offset = ($(window).height() - $dialog.height()) / 2;
    // Center modal vertically in window
    $dialog.css("margin-top", offset);
}


$.fn.extend({
    /**
     *
     * @param width
     * @param {function} [callbackClose]
     * @returns {*|jQuery}
     */
    initDialogForm: function (width, callbackClose) {
        return $(this).dialog({
            autoOpen: false,
            modal: true,
            resizable: false,
            width: width,
            open: function () {

            },
            close: function () {
                $(this).empty();
                if (callbackClose !== undefined) {
                    callbackClose();
                }
            }
        });
    },
    /**
     *
     * @param url
     * @param title
     * @param callbackLoad
     */
    openDialog: function (url, title, callbackLoad) {
        var $dialog = $(this);
        $dialog.dialog("close");
        $dialog.dialog("option", "title", title);

        $.ajax({
            type: 'GET',
            url: url,
            //cache: false,
            success: function (html, status, xhr) {
                $dialog.dialog("open");
                $dialog.dialog("moveToTop");
                $dialog.html(html);
                if (callbackLoad !== undefined) {
                    callbackLoad();
                }
                //To close dialog on outside click
                $(".ui-widget-overlay").on("click", function () {
                    $dialog.dialog("close");
                });
            }
        });
    }
});

/**
 * Refresh modal on load
 */
$('body').on('hidden.bs.modal', '.modal', function () {
    $(this).removeData('bs.modal');
    $(this).find(".modal-content").html('');
});

/**
 * Handle Unauthorised Ajax Access
 *
 * @param loginUrl
 */
function handleUnauthorisedAjaxAccess(loginUrl) {
    $(document).ajaxError(function (event, xhr) {
        if (403 === xhr.status) {
            location.href = loginUrl;
        }
    });
}


// plugin for the cookies add/remove
(function ($) {
    $.fn.extend({
        cookieList: function (cookieName) {
            var cookie = Cookies.get(cookieName);
            var items = cookie ? cookie.split(',') : [];
            return {
                add: function (val) {
                    if (val) {
                        var index = items.indexOf(val);

                        // Note: Add only unique values.
                        if (index == -1) {
                            if (Math.floor(val) == val && $.isNumeric(val)) {
                                items.push(val);
                                Cookies.set(cookieName, items.join(','), {expires: 365, path: '/'});
                            }
                        }
                    }
                },
                remove: function (val) {
                    if (val) {
                        var index = items.indexOf(val);
                        if (index > -1) {
                            items.splice(index, 1);
                            Cookies.set(cookieName, items.join(','), {expires: 365, path: '/'});
                        }
                    }
                },
                indexOf: function (val) {
                    return items.indexOf(val);
                },
                items: function () {
                    return items;
                },
                join: function (separator) {
                    return items.join(separator);
                }
            };
        }
    });
})(jQuery);


function toggleCompanyNameInput(input) {
    if (!$(input).is(':checked')) {
        return;
    }
    if ($(input).val() == 2) {
        $('.target-company-name').show();
        $('.target-company-name input').addClass('required');
    } else {
        $('.target-company-name').hide();
        $('.target-company-name input').removeClass('required');
    }
}

(function ($) {
    $('.trigger-company-name input').each(function (k, el) {
        toggleCompanyNameInput(el);
        $(el).on('click', function () {
            toggleCompanyNameInput(el);
        });

    });
})(jQuery);
