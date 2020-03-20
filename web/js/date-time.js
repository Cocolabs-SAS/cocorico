/**
 * All date and time managements on client side
 */


/**
 * datepicker init
 * Same than initDatePickerAjax in common.js without ajax mode
 */
function initDatepicker() {
    var today = new Date();

    jQuery.datepicker.setDefaults($.datepicker.regional[$("html").attr("lang")]);//abe++

    jQuery('.datepicker-holder').each(function () {
        var holder = jQuery(this);
        var inputs = holder.find('input:text, input:hidden');
        var from = inputs.filter('input.from');
        var to = inputs.filter('input.to');
        var nbDays = holder.parent().find('#date_range_nb_days');

        inputs.each(function () {
            var input = jQuery(this);

            input.closest('.col').find('.add-on').on('click', function (e) {
                e.preventDefault();
                input.focus();
            });

            if (input.hasClass('no-min-date')) {
                today = null;
            }
        });

        inputs.datepicker({
            //dateFormat: "dd  /  mm  /  y",
            dateFormat: "dd/mm/yy",//abe++
            minDate: today,
            onSelect: function (selectedDate) {
                var input = jQuery(this);
                var option = input.is(from) ? 'minDate' : 'maxDate';
                var instance = input.data('datepicker');
                var date = jQuery.datepicker.parseDate(instance.settings.dateFormat || jQuery.datepicker._defaults.dateFormat, selectedDate, instance.settings);

                inputs.not(input).filter('input:text').datepicker('option', option, date);

                if (input.is(from)) {
                    //abe++
                    if (to.attr('type') === 'text') {//Days are displayed range mode (cocorico.days_display_mode: range)
                        setTimeout(function () {
                            to.focus();
                        }, 100);
                    } else if (to.attr('type') === 'hidden') {//Day are displayed in duration mode
                        setEndDay(input, to, nbDays);
                    }
                }
            }
        });

        //abe++
        nbDays.on('change', function () {
            setEndDay(from, to, $(this));
        });
    });

    initTimePicker('.timepicker-holder');
}

/**
 * Init timePicker fields.
 *
 * @param parentTimesElt string
 */
function initTimePicker(parentTimesElt) {
    var timePickerCompatible = true;
    if (/Edge|Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
        timePickerCompatible = false;
    }

    $(parentTimesElt).each(function () {
        var holder = $(this);
        //Time Pickers
        var pickers = ['start', 'end'];
        pickers.forEach(function (picker) {
            var $picker = holder.find("[id$=_" + picker + "_picker]").first();

            if ($picker.length) {
                if (!timePickerCompatible) $picker.attr('type', 'text');
                $picker.prev('.add-on').find('.icon-clock').on('click', function () {
                    $picker.focus();
                });
                $picker.next('.add-on').find('.icon-clock').on('click', function () {
                    $picker.focus();
                });
                var $hour = holder.find("[id$=_" + picker + "_hour]").first();
                var $minute = holder.find("[id$=_" + picker + "_minute]").first();

                var defaultTime = '';
                if ($hour.val() !== '' && $minute.val() !== '') {
                    defaultTime = moment($hour.val() + ":" + $minute.val(), 'HH:mm');
                }

                $picker.datetimepicker({
                    format: 'HH:mm',
                    stepping: 15,
                    defaultDate: defaultTime,
                    useCurrent: false,
                    enabledHours: hoursAvailable
                    //,debug: true
                }).on('dp.hide', function (e) {
                    var date = e.date;
                    if (date && $picker.val()) {
                        $hour.val(date.format("H"));
                        $minute.val(date.format("m")).change();
                    } else {
                        $hour.val('');
                        $minute.val('').change();
                    }
                }).on('dp.show', function () {
                    //Fix lib error about defaultDate not taking into account of hoursAvailable
                    //Lib vs 4.17.43 fix this above issue but create a new one with useCurrent :/
                    if (!$(this).data('DateTimePicker').date()) {
                        $(this).data('DateTimePicker').defaultDate(moment(hoursAvailable[0] + ":" + "0", 'HH:mm'));
                        $picker.val('');
                    }
                });
            }
        });
    });

    //Sync time fields in duration mode. Set end time.
    syncTimeFields(parentTimesElt);
}


/**
 * Sync time fields if exist. Sync times in duration mode for now.
 *
 * @param  parentTimesElt string
 */
function syncTimeFields(parentTimesElt) {
    $(parentTimesElt).each(function () {
        var holder = $(this);

        //Times are displayed in duration mode (cocorico.times_display_mode: duration)
        if (holder.find("#time_range_nb_minutes").length) {
            var $fromHour = holder.find("#time_range_start_hour");
            var $fromMinute = holder.find("#time_range_start_minute");
            var $toHour = holder.find("#time_range_end_hour");
            var $toMinute = holder.find("#time_range_end_minute");
            var $nbMinutes = holder.find("#time_range_nb_minutes");

            $fromHour.add($fromMinute).add($nbMinutes).on("change", function () {
                setEndTime($fromHour, $fromMinute, $toHour, $toMinute, $nbMinutes);
            });

            setEndTime($fromHour, $fromMinute, $toHour, $toMinute, $nbMinutes);
        }
    });
}

/**
 * Init datePicker in Ajax form.
 * Same than initDatepicker in jquery.main.js with ajax mode
 *
 * @param callbackSuccess function
 * @param parentDatesElt string|null Optional parent element of dates and time fields.
 * Used when many date fields are on the same page
 */
function initDatePickerAjax(callbackSuccess, parentDatesElt) {
    parentDatesElt = (typeof parentDatesElt === 'undefined') ? '' : parentDatesElt + ' ';
    var today = new Date();

    $(parentDatesElt + '.datepicker-holder-ajax').each(function () {
        var holder = $(this);
        var inputs = holder.find('input:text, input:hidden');
        var from = inputs.filter('input.from');
        var to = inputs.filter('input.to');
        var nbDays = holder.parent().find('#date_range_nb_days');

        inputs.each(function () {
            var input = $(this);

            input.closest('.col').find('.add-on').on('click', function (e) {
                e.preventDefault();
                input.focus();
            });
        });

        inputs.datepicker({
            dateFormat: "dd/mm/yy",
            minDate: today,
            onSelect: function (selectedDate) {
                var input = $(this);
                var option = input.is(from) ? 'minDate' : 'maxDate';
                var instance = input.data('datepicker');
                var date = jQuery.datepicker.parseDate(instance.settings.dateFormat || jQuery.datepicker._defaults.dateFormat, selectedDate, instance.settings);

                inputs.not(input).filter('input:text').datepicker('option', option, date);

                if (input.is(from)) {
                    if (to.attr('type') === 'text') {//Days are displayed range mode (cocorico.days_display_mode: range)
                        setTimeout(function () {
                            to.focus();
                        }, 100);
                    } else if (to.attr('type') === 'hidden') {//Day are displayed in duration mode
                        setEndDay(input, to, nbDays);
                        submitDatePickerAjaxForm(callbackSuccess, parentDatesElt);
                    }
                }

                if (from.val() && to.val() && input.is(to) && !input.is(":focus")) {
                    submitDatePickerAjaxForm(callbackSuccess, parentDatesElt);
                }
            },
            onClose: function () {
                //Handle end date not manually selected in range mode
                var input = $(this);
                if (input.is(to) && to.attr('type') === 'text') {
                    if (from.val() && to.val() && to.is(":focus")) {
                        submitDatePickerAjaxForm(callbackSuccess, parentDatesElt);
                    }
                }
            }
        });

        nbDays.on('change', function () {
            setEndDay(from, to, $(this));
            submitDatePickerAjaxForm(callbackSuccess, parentDatesElt);
        });
    });

    //Time picker
    initTimePicker(parentDatesElt + '.timepicker-holder-ajax');

    $(parentDatesElt + '.timepicker-holder-ajax').each(function () {
        var holder = $(this);

        //Handle times select field change
        var timeSelects = holder.find('select');
        timeSelects.each(function () {
            var $timeSelect = $(this);

            $timeSelect.on('change', function () {
                submitDatePickerAjaxForm(callbackSuccess, parentDatesElt);
            });
        });
    });

}


/**
 * Set end day from start day and nb days field
 */
function setEndDay($from, $to, $nbDays) {
    var dateStart = $from.datepicker('getDate');
    var nbDaysVal = parseInt($nbDays.val());
    if (endDayIncluded) {//Global var. Defined in base.html.twig
        nbDaysVal -= 1;
    }
    dateStart.setDate(dateStart.getDate() + nbDaysVal);
    $to.datepicker('setDate', dateStart);
}


/**
 *
 * Set end time from start time and nb minutes field
 *
 * @param $fromHour
 * @param $fromMinute
 * @param $toHour
 * @param $toMinute
 * @param $nbMinutes
 */
function setEndTime($fromHour, $fromMinute, $toHour, $toMinute, $nbMinutes) {
    if ($fromHour.val() !== '' && $fromMinute.val() !== '' && $nbMinutes.val() !== '') {
        var startTime = moment($fromHour.val() + ":" + $fromMinute.val(), "HH:mm");
        startTime = startTime.add($nbMinutes.val(), "minute");
        $toHour.val(startTime.format("H"));
        $toMinute.val(startTime.format("m"));
    } else {
        $toHour.add($toMinute).val('');
    }
}


/**
 * Check times values if exist
 *
 * @param startHour
 * @param endHour
 * @param startMinute
 * @param endMinute
 * @returns {boolean}
 */
function timesAreValid(startHour, endHour, startMinute, endMinute) {
    if (startHour.length && endHour.length) {
        if (!$.isNumeric(startHour.val()) || !$.isNumeric(endHour.val()) ||
            !$.isNumeric(startMinute.val()) || !$.isNumeric(endMinute.val())) {

            return false;
        }
    }

    return true;
}


/**
 * Submit form with date picker and time fields
 *
 * @param callbackSuccess
 * @param parentDatesElt
 */
function submitDatePickerAjaxForm(callbackSuccess, parentDatesElt) {
    parentDatesElt = (typeof parentDatesElt === 'undefined') ? '' : parentDatesElt + ' ';

    //console_log('submitDatePickerAjaxForm');
    $(parentDatesElt + '.datepicker-holder-ajax').each(function () {
        var holder = $(this);
        var inputs = holder.find('input:text, input:hidden');
        var from = inputs.filter('input.from');
        var to = inputs.filter('input.to');

        //var holderTimes = $(parentDatesElt + ".time-fields");
        var holderTimes = $(parentDatesElt + ".ajax-container .time-fields");
        var startHour = holderTimes.find("[id$=_start_hour]").first();
        var endHour = holderTimes.find("[id$=_end_hour]").first();
        var startMinute = holderTimes.find("[id$=_start_minute]").first();
        var endMinute = holderTimes.find("[id$=_end_minute]").first();

        if (from.val() && to.val()) {
            if (timesAreValid(startHour, endHour, startMinute, endMinute)) {
                var container = from.closest('.ajax-container');
                container.submitAjaxForm(callbackSuccess);
                container.find("form").submit();
            }
        }
    });
}

