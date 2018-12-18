/**
 * see http://www.psd2html.com/js-custom-forms/
 */

// page init
jQuery(function () {
    initPopups();
    initCustomForms();
    initAddClasses();
    initUiSlider();
    initRowRemove();
    initCategories();
    initValidation();
    initDatepicker();
    initRating();
    initSelectLanguage();
    jQuery('input, textarea').placeholder();
    jQuery('[data-toggle="tooltip"]').tooltip({
        container: 'body'
    });
});

jQuery(window).load(function () {
    initTooltipFix();
    //abe-- initCalendar();
    initSlideShow();
    initSameHeight();
    initCarousel();
    initCheckedClasses();
    //abe-- initFileUpload();
    //abe-- initDraggable();
    //abe-- initMap();
    setTimeout(function () {
        jQuery('body').removeClass('loading');
    }, 500);
});

//abe ++
function addLanguageForm($collectionHolder, selectedValue, item) {
    // Get the data-prototype explained earlier
    var prototype = $collectionHolder.data('prototype');
    // get the new index
    var index = $collectionHolder.data('index');
    // Replace '__name__' in the prototype's HTML to
    // instead be a number based on how many items we have
    var newForm = prototype.replace(/__name__/g, index);
    // increase the index with one for the next item
    $collectionHolder.data('index', index + 1);
    // Display the form in the page in an li, before the "Add a tag" link li
    $(item).append(newForm);
    $('#user_languages_' + index + '_code').val(selectedValue);
}

// Select Language init
function initSelectLanguage() {
    //abe ++
    // Get the ul that holds the collection of tags
    var $collectionHolder = $('ul.languages');

    // add the "add a tag" anchor and li to the tags ul
    $collectionHolder.data('index', $collectionHolder.find('li').length);
    var $userLanguages = $('#user_languages');
    if ($userLanguages) {
        $userLanguages.parent().remove();
    }
    //end abe++

    jQuery('.languages-block').each(function () {
        var holder = jQuery(this);
        var select = holder.find('select');
        var selectOptions = select.find('option');
        var itemsList = holder.find('.btns-list');
        var btnAdd = holder.find('.btn-add');

        function onSubmit() {
            var activeOption = selectOptions.eq(select.prop('selectedIndex'));
            var tags = itemsList.find('a.btn.btn-default');
            var itemText = activeOption.text();
            var stateFlag = false;

            tags.each(function () {
                if (jQuery(this).text() === itemText) stateFlag = true;
            });

            if (activeOption.index() == 0) {
                stateFlag = true;
            }

            if (stateFlag) return;

            //abe ++
            var item = jQuery('<li>' +
            '<a href="#" id="' + itemText + '" class="close" data-dismiss="alert" aria-label="Close"><i class="icon-cancel"></i><span class="hidden">close</span></a>' +
            '<a href="#" class="btn btn-default">' + itemText + '</a>' +
            '</li>').appendTo(itemsList);

            jQuery(window).trigger('refreshHeight');
            //abe ++
            addLanguageForm($collectionHolder, activeOption.val(), item);
        }

        btnAdd.on('click', function (e) {
            e.preventDefault();
            onSubmit();
        });
    });
}

// initialize custom form elements
function initCustomForms() {
    jcf.setOptions('Select', {
        wrapNative: false,
        wrapNativeOnMobile: false
    });
    jcf.replaceAll();
}

// star rating init
function initRating() {
    lib.each(lib.queryElementsBySelector('.star-rating'), function () {
        new StarRating({
            element: this,
            onselect: function (num) {
                // rating setted event
            }
        });
    });
}

// dragable init
function initDraggable() {
    return false;//abe++
    var readyClass = 'js-upload-ready';
    var previewWidth = 150;
    var previewHeight = 120;

    jQuery('.file-selection').each(function () {
        var container = jQuery(this);
        var uploaderHolder = container.find('.uploader');
        var images = container.find('.files-list .img-thumbnail');
        var imagesHolder = container.find('.files-list');
        var jsFileuploadInput = container.find('input[type="file"]');

        ResponsiveHelper.addRange({
            '..767': {
                on: function () {
                    previewWidth = 108;
                    previewHeight = 87;
                    jsFileuploadInput.fileupload();
                }
            },
            '768..': {
                on: function () {
                    previewWidth = 148;
                    previewHeight = 120;
                }
            }
        });

        jsFileuploadInput.fileupload({
            url: uploaderHolder.data('url'),
            dataType: 'json',
            autoUpload: true,
            acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
            maxFileSize: 10000000, // 10 MB
            previewMaxWidth: previewWidth,
            previewMaxHeight: previewHeight,
            previewCrop: true,
            dropZone: uploaderHolder,
            done: function (e, data) {
                var preview = jQuery('<div class="img-thumbnail"><a href="#" class="close" data-dismiss="alert" aria-label="Close"><span class="hidden">Close</span><i class="icon-cancel"></i></a><div class="image"></div></div>');
                preview.prependTo(imagesHolder);
                var imgHold = preview.find('.image');
                imgHold.html(data.files[0].preview || data.files[0].name);
            }
        });

        images.each(function () {
            var image = jQuery(this);
            var btnClose = image.find('a.close');

            btnClose.on('click', function (e) {
                e.preventDefault();
                image.remove();
            });
        });

        imagesHolder.sortable({
            revert: true
        });
    });

    jQuery('.js-drag-holder').sortable({
        revert: true
    });
}

// remove row init
function initRowRemove() {
    jQuery('.js-removed-row').each(function () {
        var holder = jQuery(this);
        var btnRemove = holder.find('.close');

        btnRemove.on('click', function (e) {
            e.preventDefault();
            holder.remove();
        });
    });
}

// tooltip fix init
function initTooltipFix() {
    var win = jQuery(window);
    var body = jQuery('body');
    var tooltipButtons = jQuery('[data-toggle="tooltip"]');
    var tooltipWidth = 311;

    ResponsiveHelper.addRange({
        '..767': {
            on: function () {
                tooltipWidth = 150;
            }
        },
        '..1699': {
            on: function () {
                tooltipWidth = 194;
            }
        },
        '1700..': {
            on: function () {
                tooltipWidth = 311;
            }
        }
    });

    function resizeHandler() {
        tooltipButtons.each(function () {
            var item = jQuery(this);

            if (item.offset().left + item.outerWidth(true) + tooltipWidth > body.width()) {
                item.attr('data-placement', 'left');

                item.on('mouseenter click', function () {
                    jQuery('.tooltip').removeClass('right').addClass('left').css({
                        left: item.offset().left - tooltipWidth - 20
                    });
                });
            } else {
                item.attr('data-placement', 'right');
                item.on('mouseenter click', function () {
                    jQuery('.tooltip').removeClass('left').addClass('right');
                });
            }
        });
    }

    resizeHandler();
    win.on('resize orientationchange', resizeHandler);
}

// categories init
function initCategories() {
    var activeClass = 'jcf-hover';
    var animSpeed = 350;

    jQuery('.selection-area').each(function () {
        var holder = jQuery(this);
        var block = holder.find('.col ul');
        var links = block.find('a[href^="#"]');

        links.each(function () {
            var link = jQuery(this);

            link.on('click', function (e) {
                var src = link.attr('href');
                var box = jQuery(src);
                var column = link.closest('.col');
                var nextCol = link.closest('.col').next();
                var nextColumns = column.nextAll();
                var nextBoxes = nextColumns.find('.tab');
                var scrollBoxes = nextColumns.find('.jcf-scrollable');

                link.closest('li').siblings().find('a').removeClass(activeClass);
                scrollBoxes.find('a').removeClass(activeClass);
                link.addClass(activeClass);
                nextColumns.hide();
                nextBoxes.hide();
                nextCol.show();
                box.fadeIn(animSpeed);

                scrollBoxes.each(function () {
                    jQuery(this).data('jcfInstance').refresh();
                    setTimeout(function () {
                        jQuery(window).trigger('resize');
                    }, 100);
                });

                e.preventDefault();
            });
        });
    });
}

// calendar init
function initCalendar() {
    return;//abe++
    jQuery('.calendar-block').each(function () {
        var calendar = jQuery(this);
        var dataEvents = calendar.data('events');
        var lang = calendar.data('lang');

        calendar.fullCalendar({
            header: {
                left: 'prev',
                center: 'title',
                right: 'next'
            },
            lang: lang,
            firstDay: 0,
            views: {
                month: { // name of view
                    titleFormat: 'MMMM - YYYY'
                }
            },
            editable: false,
            eventLimit: false, // allow "more" link when too many events
            events: {
                url: dataEvents ? dataEvents : ' ',
            },
            loading: function (bool) {
                var self = jQuery(this);
                var title = self.find('.fc-center h2');

                if (!bool) {
                    var string = title.html();
                    var newString = '';

                    string = string.split(' ');
                    for (i = 0; i < string.length; i++) {
                        if (i !== 2) {
                            newString = newString + string[i] + ' ';
                        } else {
                            newString = newString + ' <span>' + string[i] + '</span> ';
                        }
                    }
                    title.html(newString);
                }
            }
        });
    });

    jQuery('.calendar-box').each(function () {
        var sheduleCalendar = jQuery(this);
        var defaultDate = sheduleCalendar.data('date');
        var dataSheduleEvents = sheduleCalendar.data('events');
        var lang = sheduleCalendar.data('lang');

        sheduleCalendar.fullCalendar({
            header: {
                left: '',
                center: 'title',
                right: ''
            },
            lang: lang,
            firstDay: 0,
            defaultDate: defaultDate,
            views: {
                month: { // name of view
                    titleFormat: 'MMMM YYYY'
                }
            },
            editable: true,
            eventLimit: false, // allow "more" link when too many events
            events: {
                url: dataSheduleEvents,
            }
        });
    });
}

// file upload init
function initFileUpload() {
    return; //abe++
    var doneClass = 'js-image-loaded';
    var readyClass = 'js-upload-ready';

    jQuery('.image-upload-holder').not('.' + readyClass).each(function () {
        var holder = jQuery(this).addClass(readyClass);
        var jsFileuploadInput = holder.find('input[type="file"]');
        var preview = holder.find('.img-holder');

        preview.on('click', function () {
            jsFileuploadInput.trigger('click');
        });

        jsFileuploadInput.fileupload({
            url: holder.data('url'),
            dataType: 'json',
            autoUpload: true,
            acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
            maxFileSize: 10000000, // 10 MB
            previewMaxWidth: preview.width(),
            previewMaxHeight: preview.height(),
            previewCrop: true,
            dropZone: holder,
            done: function (e, data) {
                preview.html(data.files[0].preview || data.files[0].name);
                holder.addClass(doneClass);
            }
        });
    });
}

// iu slider init
function initUiSlider() {
    jQuery('.range-box').each(function () {
        var holder = jQuery(this);
        var slider = holder.find('.ui-slider');
        var valueFieldMin = holder.find('.value-min');
        var valueFieldMax = holder.find('.value-max');
        var sliderMin = parseInt(slider.data('min'), 10) || 0;
        var sliderMax = parseInt(slider.data('max'), 10) || 100;
        var sliderValue = parseInt(slider.data('value'), 10) || 0;
        var sliderStep = parseInt(slider.data('step'), 10) || 1;
        var valueLeft = slider.find('.value-left .text');
        var valueRight = slider.find('.value-right .text');
        //abe++
        var valueMin = valueFieldMin.val();
        var valueMax = valueFieldMax.val();

        slider.slider({
            range: true,
            values: [valueMin, valueMax],
            min: sliderMin,
            max: sliderMax,
            step: sliderStep,
            slide: function (event, ui) {//abe++
                //abe++
                valueMin = ui.values[0];
                valueMax = ui.values[1];
                //abe--
                //sliderMin = ui.values[0];
                //sliderValue = ui.values[1];
                refreshText();
            },
            change: function (event, ui) {//abe++
                valueMin = ui.values[0];
                valueMax = ui.values[1];
                refreshText();
            }
        });

        function refreshText() {
            //abe++
            valueFieldMin.val(valueMin);
            valueFieldMax.val(valueMax);
            valueLeft.text(valueMin);
            valueRight.text(valueMax);
        }

        refreshText();
    });
}

// scroll gallery init
function initCarousel() {
    jQuery('.gallery-slider').scrollGallery({
        mask: '.vertical-slider',
        slider: '.vertical-slideset',
        slides: '.vertical-slide',
        btnPrev: 'a.btn-prev',
        btnNext: 'a.btn-next',
        pagerLinks: '.pagination li',
        vertical: true,
        autoRotation: false,
        switchTime: 3000,
        animSpeed: 500,
        step: 1
    });
}

// fade galleries init
function initSlideShow() {
    jQuery('.slideshow').fadeGallery({
        slides: '.slide',
        btnPrev: 'a.btn-prev',
        btnNext: 'a.btn-next',
        pagerLinks: '.pagination li',
        event: 'click',
        useSwipe: true,
        autoRotation: false,//abe++
        autoHeight: false,
        switchTime: 3000,
        animSpeed: 500
    });
    jQuery('.gallery-slider').fadeGallery({
        slides: '.slide',
        btnPrev: 'a.btn-prev',
        btnNext: 'a.btn-next',
        pagerLinks: '.vertical-slideset .vertical-slide',
        event: 'click',
        useSwipe: true,
        autoRotation: false,
        autoHeight: false,//abe++
        switchTime: 3000,
        animSpeed: 500
    });
}

// popups init
function initPopups() {
    jQuery('.popup-holder').contentPopup({
        mode: 'click'
    });
}

// align blocks height
function initSameHeight() {
    jQuery('.listing-holder').sameHeight({
        elements: '.listing-box',
        skipClass: 'listing-post',
        flexible: true,
        multiLine: true
    });
    jQuery('.about-info, .payment-info, .contact-info').sameHeight({
        elements: '.column',
        flexible: true,
        multiLine: true
    });
    jQuery('.location-holder').sameHeight({
        elements: '.contact-info, address',
        flexible: true,
        multiLine: true
    });
}

// add class on click
function initAddClasses() {
    jQuery('.tabset-holder .opener').clickClass({
        classAdd: 'active',
        addToParent: 'tabset-holder'
    });
    jQuery('.rating-opener').clickClass({
        classAdd: 'active',
        addToParent: 'user-rating'
    });
}

// checked classes when element active
function initCheckedClasses() {
    var checkedClass = 'input-checked', parentCheckedClass = 'input-checked-parent';
    var pairs = [];
    jQuery('label[for]').each(function (index, label) {
        var input = jQuery('#' + label.htmlFor);
        label = jQuery(label);

        // click handler
        if (input.length) {
            pairs.push({input: input, label: label});
            input.bind('click change', function () {
                if (input.is(':radio')) {
                    jQuery.each(pairs, function (index, pair) {
                        refreshState(pair.input, pair.label);
                    });
                } else {
                    refreshState(input, label);
                }
            });
            refreshState(input, label);
        }
    });

    // refresh classes
    function refreshState(input, label) {
        if (input.is(':checked')) {
            input.parent().addClass(parentCheckedClass);
            label.addClass(checkedClass);
        } else {
            input.parent().removeClass(parentCheckedClass);
            label.removeClass(checkedClass);
        }
    }
}

// map init
function initMap() {
    return;//abe++
    var win = jQuery(window);
    var zoom = 14;

    jQuery('.map-canvas').each(function () {
        var currMap = jQuery(this);
        var mapOptions = {
            zoom: zoom,
            center: new google.maps.LatLng(0, 0),
            mapTypeControlOptions: {
                mapTypeIds: [google.maps.MapTypeId.ROADMAP]
            }
        };
        var map = new google.maps.Map(this, mapOptions);

        jQuery.getJSON(currMap.attr('data-markers'), function (data) {
            setMarkers(map, data.markers);
        });

        function setMarkers(map, markerData) {
            var bounds = new google.maps.LatLngBounds();
            var image = {
                url: 'images/pin.png',
                size: new google.maps.Size(20, 32),
                origin: new google.maps.Point(0, 0),
                anchor: new google.maps.Point(0, 32)
            };

            for (var i = 0; i < markerData.length; i++) {
                var popup = markerData[i][2];
                var myLatLng = new google.maps.LatLng(markerData[i][0], markerData[i][1]);
                var marker = new google.maps.Marker({
                    position: myLatLng,
                    map: map,
                    icon: image
                });

                if (popup) {
                    var infobox = new InfoBox({
                        content: document.getElementById(popup),
                        disableAutoPan: false,
                        maxWidth: 150,
                        pixelOffset: new google.maps.Size(-140, 0),
                        zIndex: null,
                        boxStyle: {
                            width: "280px"
                        },
                        closeBoxURL: "",
                        infoBoxClearance: new google.maps.Size(1, 1)
                    });

                    infobox.open(map, marker);

                    jQuery('.map-holder .close').on('click', function (e) {
                        e.preventDefault();
                        infobox.close();
                    });

                    google.maps.event.addListener(marker, 'click', function () {
                        infobox.open(map, this);
                        map.panTo(myLatLng);
                    });
                }

                bounds.extend(myLatLng);
            }

            map.fitBounds(bounds);
            currMap.data('bounds', bounds);
        }

        function setZoom() {
            var listener = google.maps.event.addListener(map, "idle", function () {
                if (map.getZoom() > zoom) map.setZoom(zoom);
                google.maps.event.removeListener(listener);
            });
        }

        function refreshMap() {
            google.maps.event.trigger(map, 'resize');
            map.fitBounds(currMap.data('bounds'));
            setZoom();
        }

        if (currMap.closest('.tab-content').length) {
            var tabLinks = currMap.closest('.tab-content').siblings('.tabset-holder').find('a[data-toggle="tab"]');

            tabLinks.on('shown.bs.tab', refreshMap);
        }

        setZoom();
        win.on('resize orientationchange', refreshMap);
    });
}

// form validation function
function initValidation() {
    var errorClass = 'error';
    var successClass = 'success';
    var regEmail = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
    var regPhone = /^[0-9]+$/;

    jQuery('form.validate-form').each(function () {
        var form = jQuery(this).attr('novalidate', 'novalidate');
        var successFlag = true;
        var inputs = form.find('input, textarea, select');

        // form validation function
        function validateForm(e) {
            successFlag = true;

            inputs.each(checkField);

            jQuery('.confirm-row').each(function () {
                var hold = jQuery(this);
                var field = hold.find('input');


                field.each(function () {
                    if (field.eq(0).val() !== field.eq(1).val()) {
                        hold.find('.validate-row').removeClass(errorClass);
                        successFlag = false;
                        hold.find('.validate-row').removeClass(successClass).addClass(errorClass);
                    }
                });
            });

            if (!successFlag) {
                e.preventDefault();
            }
        }

        // check field
        function checkField(i, obj) {
            var currentObject = jQuery(obj);
            var currentParent = currentObject.closest('.validate-row');

            // not empty fields
            if (currentObject.hasClass('required')) {
                setState(currentParent, currentObject, !currentObject.val().length || currentObject.val() === currentObject.prop('defaultValue'));
            }
            // correct email fields
            if (currentObject.hasClass('required-email')) {
                setState(currentParent, currentObject, !regEmail.test(currentObject.val()));
            }
            // correct number fields
            if (currentObject.hasClass('required-number')) {
                setState(currentParent, currentObject, !regPhone.test(currentObject.val()));
            }
            // something selected
            if (currentObject.hasClass('required-select')) {
                setState(currentParent, currentObject, currentObject.get(0).selectedIndex === 0);
            }
            // checkbox field
            if (currentObject.hasClass('required-checkbox')) {
                setState(currentParent, currentObject, !currentObject.is(':checked'));
            }
        }

        // set state
        function setState(hold, field, error) {
            hold.removeClass(errorClass).removeClass(successClass);
            if (error) {
                hold.addClass(errorClass);
                field.one('focus', function () {
                    hold.removeClass(errorClass).removeClass(successClass);
                });
                successFlag = false;
            } else {
                hold.addClass(successClass);
            }
        }

        // form event handlers
        form.submit(validateForm);
    });
}

/*
 * jQuery Carousel plugin
 */
(function ($) {
    function ScrollGallery(options) {
        this.options = $.extend({
            mask: 'div.mask',
            slider: '>*',
            slides: '>*',
            activeClass: 'active',
            disabledClass: 'disabled',
            btnPrev: 'a.btn-prev',
            btnNext: 'a.btn-next',
            generatePagination: false,
            pagerList: '<ul>',
            pagerListItem: '<li><a href="#"></a></li>',
            pagerListItemText: 'a',
            pagerLinks: '.pagination li',
            currentNumber: 'span.current-num',
            totalNumber: 'span.total-num',
            btnPlay: '.btn-play',
            btnPause: '.btn-pause',
            btnPlayPause: '.btn-play-pause',
            galleryReadyClass: 'gallery-js-ready',
            autorotationActiveClass: 'autorotation-active',
            autorotationDisabledClass: 'autorotation-disabled',
            stretchSlideToMask: false,
            circularRotation: true,
            disableWhileAnimating: false,
            autoRotation: false,
            pauseOnHover: isTouchDevice ? false : true,
            maskAutoSize: false,
            switchTime: 4000,
            animSpeed: 600,
            event: 'click',
            swipeThreshold: 15,
            handleTouch: true,
            vertical: false,
            useTranslate3D: false,
            step: false
        }, options);
        this.init();
    }

    ScrollGallery.prototype = {
        init: function () {
            if (this.options.holder) {
                this.findElements();
                this.attachEvents();
                this.refreshPosition();
                this.refreshState(true);
                this.resumeRotation();
                this.makeCallback('onInit', this);
            }
        },
        findElements: function () {
            // define dimensions proporties
            this.fullSizeFunction = this.options.vertical ? 'outerHeight' : 'outerWidth';
            this.innerSizeFunction = this.options.vertical ? 'height' : 'width';
            this.slideSizeFunction = 'outerHeight';
            this.maskSizeProperty = 'height';
            this.animProperty = this.options.vertical ? 'marginTop' : 'marginLeft';

            // control elements
            this.gallery = $(this.options.holder).addClass(this.options.galleryReadyClass);
            this.mask = this.gallery.find(this.options.mask);
            this.slider = this.mask.find(this.options.slider);
            this.slides = this.slider.find(this.options.slides);
            this.btnPrev = this.gallery.find(this.options.btnPrev);
            this.btnNext = this.gallery.find(this.options.btnNext);
            this.currentStep = 0;
            this.stepsCount = 0;

            // get start index
            if (this.options.step === false) {
                var activeSlide = this.slides.filter('.' + this.options.activeClass);
                if (activeSlide.length) {
                    this.currentStep = this.slides.index(activeSlide);
                }
            }

            // calculate offsets
            this.calculateOffsets();

            // create gallery pagination
            if (typeof this.options.generatePagination === 'string') {
                this.pagerLinks = $();
                this.buildPagination();
            } else {
                this.pagerLinks = this.gallery.find(this.options.pagerLinks);
                this.attachPaginationEvents();
            }

            // autorotation control buttons
            this.btnPlay = this.gallery.find(this.options.btnPlay);
            this.btnPause = this.gallery.find(this.options.btnPause);
            this.btnPlayPause = this.gallery.find(this.options.btnPlayPause);

            // misc elements
            this.curNum = this.gallery.find(this.options.currentNumber);
            this.allNum = this.gallery.find(this.options.totalNumber);
        },
        attachEvents: function () {
            // bind handlers scope
            var self = this;
            this.bindHandlers(['onWindowResize']);
            $(window).bind('load resize orientationchange', this.onWindowResize);

            // previous and next button handlers
            if (this.btnPrev.length) {
                this.prevSlideHandler = function (e) {
                    e.preventDefault();
                    self.prevSlide();
                };
                this.btnPrev.bind(this.options.event, this.prevSlideHandler);
            }
            if (this.btnNext.length) {
                this.nextSlideHandler = function (e) {
                    e.preventDefault();
                    self.nextSlide();
                };
                this.btnNext.bind(this.options.event, this.nextSlideHandler);
            }

            // pause on hover handling
            if (this.options.pauseOnHover && !isTouchDevice) {
                this.hoverHandler = function () {
                    if (self.options.autoRotation) {
                        self.galleryHover = true;
                        self.pauseRotation();
                    }
                };
                this.leaveHandler = function () {
                    if (self.options.autoRotation) {
                        self.galleryHover = false;
                        self.resumeRotation();
                    }
                };
                this.gallery.bind({mouseenter: this.hoverHandler, mouseleave: this.leaveHandler});
            }

            // autorotation buttons handler
            if (this.btnPlay.length) {
                this.btnPlayHandler = function (e) {
                    e.preventDefault();
                    self.startRotation();
                };
                this.btnPlay.bind(this.options.event, this.btnPlayHandler);
            }
            if (this.btnPause.length) {
                this.btnPauseHandler = function (e) {
                    e.preventDefault();
                    self.stopRotation();
                };
                this.btnPause.bind(this.options.event, this.btnPauseHandler);
            }
            if (this.btnPlayPause.length) {
                this.btnPlayPauseHandler = function (e) {
                    e.preventDefault();
                    if (!self.gallery.hasClass(self.options.autorotationActiveClass)) {
                        self.startRotation();
                    } else {
                        self.stopRotation();
                    }
                };
                this.btnPlayPause.bind(this.options.event, this.btnPlayPauseHandler);
            }

            // enable hardware acceleration
            if (isTouchDevice && this.options.useTranslate3D) {
                this.slider.css({'-webkit-transform': 'translate3d(0px, 0px, 0px)'});
            }

            // swipe event handling
            if (isTouchDevice && this.options.handleTouch && window.Hammer && this.mask.length) {
                this.swipeHandler = new Hammer.Manager(this.mask[0]);
                this.swipeHandler.add(new Hammer.Pan({
                    direction: self.options.vertical ? Hammer.DIRECTION_VERTICAL : Hammer.DIRECTION_HORIZONTAL,
                    threshold: self.options.swipeThreshold
                }));

                this.swipeHandler.on('panstart', function () {
                    if (self.galleryAnimating) {
                        self.swipeHandler.stop();
                    } else {
                        self.pauseRotation();
                        self.originalOffset = parseFloat(self.slider.css(self.animProperty));
                    }
                }).on('panmove', function (e) {
                    var tmpOffset = self.originalOffset + e[self.options.vertical ? 'deltaY' : 'deltaX'];
                    tmpOffset = Math.max(Math.min(0, tmpOffset), self.maxOffset);
                    self.slider.css(self.animProperty, tmpOffset);
                }).on('panend', function (e) {
                    self.resumeRotation();
                    if (e.distance > self.options.swipeThreshold) {
                        if (e.offsetDirection === Hammer.DIRECTION_RIGHT || e.offsetDirection === Hammer.DIRECTION_DOWN) {
                            self.nextSlide();
                        } else {
                            self.prevSlide();
                        }
                    } else {
                        self.switchSlide();
                    }
                });
            }
        },
        onWindowResize: function () {
            if (!this.galleryAnimating) {
                this.calculateOffsets();
                this.refreshPosition();
                this.buildPagination();
                this.refreshState();
                this.resizeQueue = false;
            } else {
                this.resizeQueue = true;
            }
        },
        refreshPosition: function () {
            this.currentStep = Math.min(this.currentStep, this.stepsCount - 1);
            this.tmpProps = {};
            this.tmpProps[this.animProperty] = this.getStepOffset();
            this.slider.stop().css(this.tmpProps);
        },
        calculateOffsets: function () {
            var self = this, tmpOffset, tmpStep;
            if (this.options.stretchSlideToMask) {
                var tmpObj = {};
                tmpObj[this.innerSizeFunction] = this.mask[this.innerSizeFunction]();
                this.slides.css(tmpObj);
            }

            this.maskSize = this.mask[this.innerSizeFunction]();
            this.sumSize = this.getSumSize();
            this.maxOffset = this.maskSize - this.sumSize;

            // vertical gallery with single size step custom behavior
            if (this.options.vertical && this.options.maskAutoSize) {
                this.options.step = 1;
                this.stepsCount = this.slides.length;
                this.stepOffsets = [0];
                tmpOffset = 0;
                for (var i = 0; i < this.slides.length; i++) {
                    tmpOffset -= $(this.slides[i])[this.fullSizeFunction](true);
                    this.stepOffsets.push(tmpOffset);
                }
                this.maxOffset = tmpOffset;
                return;
            }

            // scroll by slide size
            if (typeof this.options.step === 'number' && this.options.step > 0) {
                this.slideDimensions = [];
                this.slides.each($.proxy(function (ind, obj) {
                    self.slideDimensions.push($(obj)[self.fullSizeFunction](true));
                }, this));

                // calculate steps count
                this.stepOffsets = [0];
                this.stepsCount = 1;
                tmpOffset = tmpStep = 0;
                while (tmpOffset > this.maxOffset) {
                    tmpOffset -= this.getSlideSize(tmpStep, tmpStep + this.options.step);
                    tmpStep += this.options.step;
                    this.stepOffsets.push(Math.max(tmpOffset, this.maxOffset));
                    this.stepsCount++;
                }
            }
            // scroll by mask size
            else {
                // define step size
                this.stepSize = this.maskSize;

                // calculate steps count
                this.stepsCount = 1;
                tmpOffset = 0;
                while (tmpOffset > this.maxOffset) {
                    tmpOffset -= this.stepSize;
                    this.stepsCount++;
                }
            }
        },
        getSumSize: function () {
            var sum = 0;
            this.slides.each($.proxy(function (ind, obj) {
                sum += $(obj)[this.fullSizeFunction](true);
            }, this));
            this.slider.css(this.innerSizeFunction, sum);
            return sum;
        },
        getStepOffset: function (step) {
            step = step || this.currentStep;
            if (typeof this.options.step === 'number') {
                return this.stepOffsets[this.currentStep];
            } else {
                return Math.min(0, Math.max(-this.currentStep * this.stepSize, this.maxOffset));
            }
        },
        getSlideSize: function (i1, i2) {
            var sum = 0;
            for (var i = i1; i < Math.min(i2, this.slideDimensions.length); i++) {
                sum += this.slideDimensions[i];
            }
            return sum;
        },
        buildPagination: function () {
            if (typeof this.options.generatePagination === 'string') {
                if (!this.pagerHolder) {
                    this.pagerHolder = this.gallery.find(this.options.generatePagination);
                }
                if (this.pagerHolder.length && this.oldStepsCount != this.stepsCount) {
                    this.oldStepsCount = this.stepsCount;
                    this.pagerHolder.empty();
                    this.pagerList = $(this.options.pagerList).appendTo(this.pagerHolder);
                    for (var i = 0; i < this.stepsCount; i++) {
                        $(this.options.pagerListItem).appendTo(this.pagerList).find(this.options.pagerListItemText).text(i + 1);
                    }
                    this.pagerLinks = this.pagerList.children();
                    this.attachPaginationEvents();
                }
            }
        },
        attachPaginationEvents: function () {
            var self = this;
            this.pagerLinksHandler = function (e) {
                e.preventDefault();
                self.numSlide(self.pagerLinks.index(e.currentTarget));
            };
            this.pagerLinks.bind(this.options.event, this.pagerLinksHandler);
        },
        prevSlide: function () {
            if (!(this.options.disableWhileAnimating && this.galleryAnimating)) {
                if (this.currentStep > 0) {
                    this.currentStep--;
                    this.switchSlide();
                } else if (this.options.circularRotation) {
                    this.currentStep = this.stepsCount - 1;
                    this.switchSlide();
                }
            }
        },
        nextSlide: function (fromAutoRotation) {
            if (!(this.options.disableWhileAnimating && this.galleryAnimating)) {
                if (this.currentStep < this.stepsCount - 1) {
                    this.currentStep++;
                    this.switchSlide();
                } else if (this.options.circularRotation || fromAutoRotation === true) {
                    this.currentStep = 0;
                    this.switchSlide();
                }
            }
        },
        numSlide: function (c) {
            if (this.currentStep != c) {
                this.currentStep = c;
                this.switchSlide();
            }
        },
        switchSlide: function () {
            var self = this;
            this.galleryAnimating = true;
            this.tmpProps = {};
            this.tmpProps[this.animProperty] = this.getStepOffset();
            this.slider.stop().animate(this.tmpProps, {
                duration: this.options.animSpeed, complete: function () {
                    // animation complete
                    self.galleryAnimating = false;
                    if (self.resizeQueue) {
                        self.onWindowResize();
                    }

                    // onchange callback
                    self.makeCallback('onChange', self);
                    self.autoRotate();
                }
            });
            this.refreshState();

            // onchange callback
            this.makeCallback('onBeforeChange', this);
        },
        refreshState: function (initial) {
            if (this.options.step === 1 || this.stepsCount === this.slides.length) {
                this.slides.removeClass(this.options.activeClass).eq(this.currentStep).addClass(this.options.activeClass);
            }
            this.pagerLinks.removeClass(this.options.activeClass).eq(this.currentStep).addClass(this.options.activeClass);
            this.curNum.html(this.currentStep + 1);
            this.allNum.html(this.stepsCount);

            // initial refresh
            if (this.options.maskAutoSize && typeof this.options.step === 'number') {
                this.tmpProps = {};
                this.tmpProps[this.maskSizeProperty] = this.slides.eq(Math.min(this.currentStep, this.slides.length - 1))[this.slideSizeFunction](true);
                this.mask.stop()[initial ? 'css' : 'animate'](this.tmpProps);
            }

            // disabled state
            if (!this.options.circularRotation) {
                this.btnPrev.add(this.btnNext).removeClass(this.options.disabledClass);
                if (this.currentStep === 0) this.btnPrev.addClass(this.options.disabledClass);
                if (this.currentStep === this.stepsCount - 1) this.btnNext.addClass(this.options.disabledClass);
            }

            // add class if not enough slides
            this.gallery.toggleClass('not-enough-slides', this.sumSize <= this.maskSize);
        },
        startRotation: function () {
            this.options.autoRotation = true;
            this.galleryHover = false;
            this.autoRotationStopped = false;
            this.resumeRotation();
        },
        stopRotation: function () {
            this.galleryHover = true;
            this.autoRotationStopped = true;
            this.pauseRotation();
        },
        pauseRotation: function () {
            this.gallery.addClass(this.options.autorotationDisabledClass);
            this.gallery.removeClass(this.options.autorotationActiveClass);
            clearTimeout(this.timer);
        },
        resumeRotation: function () {
            if (!this.autoRotationStopped) {
                this.gallery.addClass(this.options.autorotationActiveClass);
                this.gallery.removeClass(this.options.autorotationDisabledClass);
                this.autoRotate();
            }
        },
        autoRotate: function () {
            var self = this;
            clearTimeout(this.timer);
            if (this.options.autoRotation && !this.galleryHover && !this.autoRotationStopped) {
                this.timer = setTimeout(function () {
                    self.nextSlide(true);
                }, this.options.switchTime);
            } else {
                this.pauseRotation();
            }
        },
        bindHandlers: function (handlersList) {
            var self = this;
            $.each(handlersList, function (index, handler) {
                var origHandler = self[handler];
                self[handler] = function () {
                    return origHandler.apply(self, arguments);
                };
            });
        },
        makeCallback: function (name) {
            if (typeof this.options[name] === 'function') {
                var args = Array.prototype.slice.call(arguments);
                args.shift();
                this.options[name].apply(this, args);
            }
        },
        destroy: function () {
            // destroy handler
            $(window).unbind('load resize orientationchange', this.onWindowResize);
            this.btnPrev.unbind(this.options.event, this.prevSlideHandler);
            this.btnNext.unbind(this.options.event, this.nextSlideHandler);
            this.pagerLinks.unbind(this.options.event, this.pagerLinksHandler);
            this.gallery.unbind('mouseenter', this.hoverHandler);
            this.gallery.unbind('mouseleave', this.leaveHandler);

            // autorotation buttons handlers
            this.stopRotation();
            this.btnPlay.unbind(this.options.event, this.btnPlayHandler);
            this.btnPause.unbind(this.options.event, this.btnPauseHandler);
            this.btnPlayPause.unbind(this.options.event, this.btnPlayPauseHandler);

            // destroy swipe handler
            if (this.swipeHandler) {
                this.swipeHandler.destroy();
            }

            // remove inline styles, classes and pagination
            var unneededClasses = [this.options.galleryReadyClass, this.options.autorotationActiveClass, this.options.autorotationDisabledClass];
            this.gallery.removeClass(unneededClasses.join(' '));
            this.slider.add(this.slides).removeAttr('style');
            if (typeof this.options.generatePagination === 'string') {
                this.pagerHolder.empty();
            }
        }
    };

    // detect device type
    var isTouchDevice = /Windows Phone/.test(navigator.userAgent) || ('ontouchstart' in window) || window.DocumentTouch && document instanceof DocumentTouch;

    // jquery plugin
    $.fn.scrollGallery = function (opt) {
        return this.each(function () {
            $(this).data('ScrollGallery', new ScrollGallery($.extend(opt, {holder: this})));
        });
    };
}(jQuery));

/*
 * jQuery SlideShow plugin
 */
(function ($) {
    function FadeGallery(options) {
        this.options = $.extend({
            slides: 'ul.slideset > li',
            activeClass: 'active',
            disabledClass: 'disabled',
            btnPrev: 'a.btn-prev',
            btnNext: 'a.btn-next',
            generatePagination: false,
            pagerList: '<ul>',
            pagerListItem: '<li><a href="#"></a></li>',
            pagerListItemText: 'a',
            pagerLinks: '.pagination li',
            currentNumber: 'span.current-num',
            totalNumber: 'span.total-num',
            btnPlay: '.btn-play',
            btnPause: '.btn-pause',
            btnPlayPause: '.btn-play-pause',
            galleryReadyClass: 'gallery-js-ready',
            autorotationActiveClass: 'autorotation-active',
            autorotationDisabledClass: 'autorotation-disabled',
            autorotationStopAfterClick: false,
            circularRotation: true,
            switchSimultaneously: true,
            disableWhileAnimating: false,
            disableFadeIE: false,
            autoRotation: false,
            pauseOnHover: true,
            autoHeight: false,
            useSwipe: false,
            swipeThreshold: 15,
            switchTime: 4000,
            animSpeed: 600,
            event: 'click'
        }, options);
        this.init();
    }

    FadeGallery.prototype = {
        init: function () {
            if (this.options.holder) {
                this.findElements();
                this.attachEvents();
                this.refreshState(true);
                this.autoRotate();
                this.makeCallback('onInit', this);
            }
        },
        findElements: function () {
            // control elements
            this.gallery = $(this.options.holder).addClass(this.options.galleryReadyClass);
            this.slides = this.gallery.find(this.options.slides);
            this.slidesHolder = this.slides.eq(0).parent();
            this.stepsCount = this.slides.length;
            this.btnPrev = this.gallery.find(this.options.btnPrev);
            this.btnNext = this.gallery.find(this.options.btnNext);
            this.currentIndex = 0;

            // disable fade effect in old IE
            if (this.options.disableFadeIE && !$.support.opacity) {
                this.options.animSpeed = 0;
            }

            // create gallery pagination
            if (typeof this.options.generatePagination === 'string') {
                this.pagerHolder = this.gallery.find(this.options.generatePagination).empty();
                this.pagerList = $(this.options.pagerList).appendTo(this.pagerHolder);
                for (var i = 0; i < this.stepsCount; i++) {
                    $(this.options.pagerListItem).appendTo(this.pagerList).find(this.options.pagerListItemText).text(i + 1);
                }
                this.pagerLinks = this.pagerList.children();
            } else {
                this.pagerLinks = this.gallery.find(this.options.pagerLinks);
            }

            // get start index
            var activeSlide = this.slides.filter('.' + this.options.activeClass);
            if (activeSlide.length) {
                this.currentIndex = this.slides.index(activeSlide);
            }
            this.prevIndex = this.currentIndex;

            // autorotation control buttons
            this.btnPlay = this.gallery.find(this.options.btnPlay);
            this.btnPause = this.gallery.find(this.options.btnPause);
            this.btnPlayPause = this.gallery.find(this.options.btnPlayPause);

            // misc elements
            this.curNum = this.gallery.find(this.options.currentNumber);
            this.allNum = this.gallery.find(this.options.totalNumber);

            // handle flexible layout
            this.slides.css({display: 'block', opacity: 0}).eq(this.currentIndex).css({
                opacity: ''
            });
        },
        attachEvents: function () {
            var self = this;

            // flexible layout handler
            this.resizeHandler = function () {
                self.onWindowResize();
            };
            $(window).bind('load resize orientationchange', this.resizeHandler);

            if (this.btnPrev.length) {
                this.btnPrevHandler = function (e) {
                    e.preventDefault();
                    self.prevSlide();
                    if (self.options.autorotationStopAfterClick) {
                        self.stopRotation();
                    }
                };
                this.btnPrev.bind(this.options.event, this.btnPrevHandler);
            }
            if (this.btnNext.length) {
                this.btnNextHandler = function (e) {
                    e.preventDefault();
                    self.nextSlide();
                    if (self.options.autorotationStopAfterClick) {
                        self.stopRotation();
                    }
                };
                this.btnNext.bind(this.options.event, this.btnNextHandler);
            }
            if (this.pagerLinks.length) {
                this.pagerLinksHandler = function (e) {
                    e.preventDefault();
                    self.numSlide(self.pagerLinks.index(e.currentTarget));
                    if (self.options.autorotationStopAfterClick) {
                        self.stopRotation();
                    }
                };
                this.pagerLinks.bind(self.options.event, this.pagerLinksHandler);
            }

            // autorotation buttons handler
            if (this.btnPlay.length) {
                this.btnPlayHandler = function (e) {
                    e.preventDefault();
                    self.startRotation();
                };
                this.btnPlay.bind(this.options.event, this.btnPlayHandler);
            }
            if (this.btnPause.length) {
                this.btnPauseHandler = function (e) {
                    e.preventDefault();
                    self.stopRotation();
                };
                this.btnPause.bind(this.options.event, this.btnPauseHandler);
            }
            if (this.btnPlayPause.length) {
                this.btnPlayPauseHandler = function (e) {
                    e.preventDefault();
                    if (!self.gallery.hasClass(self.options.autorotationActiveClass)) {
                        self.startRotation();
                    } else {
                        self.stopRotation();
                    }
                };
                this.btnPlayPause.bind(this.options.event, this.btnPlayPauseHandler);
            }

            // swipe gestures handler
            if (this.options.useSwipe && window.Hammer && isTouchDevice) {
                this.swipeHandler = new Hammer.Manager(this.gallery[0]);
                this.swipeHandler.add(new Hammer.Swipe({
                    direction: Hammer.DIRECTION_HORIZONTAL,
                    threshold: self.options.swipeThreshold
                }));
                this.swipeHandler.on('swipeleft', function () {
                    self.nextSlide();
                }).on('swiperight', function () {
                    self.prevSlide();
                });
            }

            // pause on hover handling
            if (this.options.pauseOnHover) {
                this.hoverHandler = function () {
                    if (self.options.autoRotation) {
                        self.galleryHover = true;
                        self.pauseRotation();
                    }
                };
                this.leaveHandler = function () {
                    if (self.options.autoRotation) {
                        self.galleryHover = false;
                        self.resumeRotation();
                    }
                };
                this.gallery.bind({mouseenter: this.hoverHandler, mouseleave: this.leaveHandler});
            }
        },
        onWindowResize: function () {
            if (this.options.autoHeight) {
                this.slidesHolder.css({height: this.slides.eq(this.currentIndex).outerHeight(true)});
            }
        },
        prevSlide: function () {
            if (!(this.options.disableWhileAnimating && this.galleryAnimating)) {
                this.prevIndex = this.currentIndex;
                if (this.currentIndex > 0) {
                    this.currentIndex--;
                    this.switchSlide();
                } else if (this.options.circularRotation) {
                    this.currentIndex = this.stepsCount - 1;
                    this.switchSlide();
                }
            }
        },
        nextSlide: function (fromAutoRotation) {
            if (!(this.options.disableWhileAnimating && this.galleryAnimating)) {
                this.prevIndex = this.currentIndex;
                if (this.currentIndex < this.stepsCount - 1) {
                    this.currentIndex++;
                    this.switchSlide();
                } else if (this.options.circularRotation || fromAutoRotation === true) {
                    this.currentIndex = 0;
                    this.switchSlide();
                }
            }
        },
        numSlide: function (c) {
            if (this.currentIndex != c) {
                this.prevIndex = this.currentIndex;
                this.currentIndex = c;
                this.switchSlide();
            }
        },
        switchSlide: function () {
            var self = this;
            if (this.slides.length > 1) {
                this.galleryAnimating = true;
                if (!this.options.animSpeed) {
                    this.slides.eq(this.prevIndex).css({opacity: 0});
                } else {
                    this.slides.eq(this.prevIndex).stop().animate({opacity: 0}, {duration: this.options.animSpeed});
                }

                this.switchNext = function () {
                    if (!self.options.animSpeed) {
                        self.slides.eq(self.currentIndex).css({opacity: ''});
                    } else {
                        self.slides.eq(self.currentIndex).stop().animate({opacity: 1}, {duration: self.options.animSpeed});
                    }
                    clearTimeout(this.nextTimer);
                    this.nextTimer = setTimeout(function () {
                        self.slides.eq(self.currentIndex).css({opacity: ''});
                        self.galleryAnimating = false;
                        self.autoRotate();

                        // onchange callback
                        self.makeCallback('onChange', self);
                    }, self.options.animSpeed);
                };

                if (this.options.switchSimultaneously) {
                    self.switchNext();
                } else {
                    clearTimeout(this.switchTimer);
                    this.switchTimer = setTimeout(function () {
                        self.switchNext();
                    }, this.options.animSpeed);
                }
                this.refreshState();

                // onchange callback
                this.makeCallback('onBeforeChange', this);
            }
        },
        refreshState: function (initial) {
            this.slides.removeClass(this.options.activeClass).eq(this.currentIndex).addClass(this.options.activeClass);
            this.pagerLinks.removeClass(this.options.activeClass).eq(this.currentIndex).addClass(this.options.activeClass);
            this.curNum.html(this.currentIndex + 1);
            this.allNum.html(this.stepsCount);

            // initial refresh
            if (this.options.autoHeight) {
                if (initial) {
                    this.slidesHolder.css({height: this.slides.eq(this.currentIndex).outerHeight(true)});
                } else {
                    this.slidesHolder.stop().animate({height: this.slides.eq(this.currentIndex).outerHeight(true)}, {duration: this.options.animSpeed});
                }
            }

            // disabled state
            if (!this.options.circularRotation) {
                this.btnPrev.add(this.btnNext).removeClass(this.options.disabledClass);
                if (this.currentIndex === 0) this.btnPrev.addClass(this.options.disabledClass);
                if (this.currentIndex === this.stepsCount - 1) this.btnNext.addClass(this.options.disabledClass);
            }

            // add class if not enough slides
            this.gallery.toggleClass('not-enough-slides', this.stepsCount === 1);
        },
        startRotation: function () {
            this.options.autoRotation = true;
            this.galleryHover = false;
            this.autoRotationStopped = false;
            this.resumeRotation();
        },
        stopRotation: function () {
            this.galleryHover = true;
            this.autoRotationStopped = true;
            this.pauseRotation();
        },
        pauseRotation: function () {
            this.gallery.addClass(this.options.autorotationDisabledClass);
            this.gallery.removeClass(this.options.autorotationActiveClass);
            clearTimeout(this.timer);
        },
        resumeRotation: function () {
            if (!this.autoRotationStopped) {
                this.gallery.addClass(this.options.autorotationActiveClass);
                this.gallery.removeClass(this.options.autorotationDisabledClass);
                this.autoRotate();
            }
        },
        autoRotate: function () {
            var self = this;
            clearTimeout(this.timer);
            if (this.options.autoRotation && !this.galleryHover && !this.autoRotationStopped) {
                this.gallery.addClass(this.options.autorotationActiveClass);
                this.timer = setTimeout(function () {
                    self.nextSlide(true);
                }, this.options.switchTime);
            } else {
                this.pauseRotation();
            }
        },
        makeCallback: function (name) {
            if (typeof this.options[name] === 'function') {
                var args = Array.prototype.slice.call(arguments);
                args.shift();
                this.options[name].apply(this, args);
            }
        },
        destroy: function () {
            // navigation buttons handler
            this.btnPrev.unbind(this.options.event, this.btnPrevHandler);
            this.btnNext.unbind(this.options.event, this.btnNextHandler);
            this.pagerLinks.unbind(this.options.event, this.pagerLinksHandler);
            $(window).unbind('load resize orientationchange', this.resizeHandler);

            // remove autorotation handlers
            this.stopRotation();
            this.btnPlay.unbind(this.options.event, this.btnPlayHandler);
            this.btnPause.unbind(this.options.event, this.btnPauseHandler);
            this.btnPlayPause.unbind(this.options.event, this.btnPlayPauseHandler);
            this.gallery.unbind('mouseenter', this.hoverHandler);
            this.gallery.unbind('mouseleave', this.leaveHandler);

            // remove swipe handler if used
            if (this.swipeHandler) {
                this.swipeHandler.destroy();
            }
            if (typeof this.options.generatePagination === 'string') {
                this.pagerHolder.empty();
            }

            // remove unneeded classes and styles
            var unneededClasses = [this.options.galleryReadyClass, this.options.autorotationActiveClass, this.options.autorotationDisabledClass];
            this.gallery.removeClass(unneededClasses.join(' '));
            this.slidesHolder.add(this.slides).removeAttr('style');
        }
    };

    // detect device type
    var isTouchDevice = /Windows Phone/.test(navigator.userAgent) || ('ontouchstart' in window) || window.DocumentTouch && document instanceof DocumentTouch;

    // jquery plugin
    $.fn.fadeGallery = function (opt) {
        return this.each(function () {
            $(this).data('FadeGallery', new FadeGallery($.extend(opt, {holder: this})));
        });
    };
}(jQuery));

/*
 * Popups plugin
 */
(function ($) {
    function ContentPopup(opt) {
        this.options = $.extend({
            holder: null,
            popup: '.popup',
            btnOpen: '.open',
            btnClose: '.close',
            openClass: 'popup-active',
            clickEvent: 'click',
            mode: 'click',
            hideOnClickLink: true,
            hideOnClickOutside: true,
            delay: 50
        }, opt);
        if (this.options.holder) {
            this.holder = $(this.options.holder);
            this.init();
        }
    }

    ContentPopup.prototype = {
        init: function () {
            this.findElements();
            this.attachEvents();
        },
        findElements: function () {
            this.popup = this.holder.find(this.options.popup);
            this.btnOpen = this.holder.find(this.options.btnOpen);
            this.btnClose = this.holder.find(this.options.btnClose);
        },
        attachEvents: function () {
            // handle popup openers
            var self = this;
            this.clickMode = isTouchDevice || (self.options.mode === self.options.clickEvent);

            if (this.clickMode) {
                // handle click mode
                this.btnOpen.bind(self.options.clickEvent, function (e) {
                    if (self.holder.hasClass(self.options.openClass)) {
                        if (self.options.hideOnClickLink) {
                            self.hidePopup();
                        }
                    } else {
                        self.showPopup();
                    }
                    e.preventDefault();
                });

                // prepare outside click handler
                this.outsideClickHandler = this.bind(this.outsideClickHandler, this);
            } else {
                // handle hover mode
                var timer, delayedFunc = function (func) {
                    clearTimeout(timer);
                    timer = setTimeout(function () {
                        func.call(self);
                    }, self.options.delay);
                };
                this.btnOpen.bind('mouseover', function () {
                    delayedFunc(self.showPopup);
                }).bind('mouseout', function () {
                    delayedFunc(self.hidePopup);
                });
                this.popup.bind('mouseover', function () {
                    delayedFunc(self.showPopup);
                }).bind('mouseout', function () {
                    delayedFunc(self.hidePopup);
                });
            }

            // handle close buttons
            this.btnClose.bind(self.options.clickEvent, function (e) {
                self.hidePopup();
                e.preventDefault();
            });
        },
        outsideClickHandler: function (e) {
            // hide popup if clicked outside
            var targetNode = $((e.changedTouches ? e.changedTouches[0] : e).target);
            if (!targetNode.closest(this.popup).length && !targetNode.closest(this.btnOpen).length) {
                this.hidePopup();
            }
        },
        showPopup: function () {
            // reveal popup
            this.holder.addClass(this.options.openClass);
            this.popup.css({display: 'block'});

            // outside click handler
            if (this.clickMode && this.options.hideOnClickOutside && !this.outsideHandlerActive) {
                this.outsideHandlerActive = true;
                $(document).bind('click touchstart', this.outsideClickHandler);
            }
        },
        hidePopup: function () {
            // hide popup
            this.holder.removeClass(this.options.openClass);
            this.popup.css({display: 'none'});

            // outside click handler
            if (this.clickMode && this.options.hideOnClickOutside && this.outsideHandlerActive) {
                this.outsideHandlerActive = false;
                $(document).unbind('click touchstart', this.outsideClickHandler);
            }
        },
        bind: function (f, scope, forceArgs) {
            return function () {
                return f.apply(scope, forceArgs ? [forceArgs] : arguments);
            };
        }
    };

    // detect touch devices
    var isTouchDevice = /Windows Phone/.test(navigator.userAgent) || ('ontouchstart' in window) || window.DocumentTouch && document instanceof DocumentTouch;

    // jQuery plugin interface
    $.fn.contentPopup = function (opt) {
        return this.each(function () {
            new ContentPopup($.extend(opt, {holder: this}));
        });
    };
}(jQuery));

/*
 * jQuery SameHeight plugin
 */
(function ($) {
    $.fn.sameHeight = function (opt) {
        var options = $.extend({
            skipClass: 'same-height-ignore',
            leftEdgeClass: 'same-height-left',
            rightEdgeClass: 'same-height-right',
            elements: '>*',
            flexible: false,
            multiLine: false,
            useMinHeight: false,
            biggestHeight: false
        }, opt);
        return this.each(function () {
            var holder = $(this), postResizeTimer, ignoreResize;
            var elements = holder.find(options.elements).not('.' + options.skipClass);
            if (!elements.length) return;

            // resize handler
            function doResize() {
                elements.css(options.useMinHeight && supportMinHeight ? 'minHeight' : 'height', '');
                if (options.multiLine) {
                    // resize elements row by row
                    resizeElementsByRows(elements, options);
                } else {
                    // resize elements by holder
                    resizeElements(elements, holder, options);
                }
            }

            doResize();

            // handle flexible layout / font resize
            var delayedResizeHandler = function () {
                if (!ignoreResize) {
                    ignoreResize = true;
                    doResize();
                    clearTimeout(postResizeTimer);
                    postResizeTimer = setTimeout(function () {
                        doResize();
                        setTimeout(function () {
                            ignoreResize = false;
                        }, 10);
                    }, 100);
                }
            };

            // handle flexible/responsive layout
            if (options.flexible) {
                $(window).bind('resize orientationchange fontresize refreshHeight', delayedResizeHandler);
            }

            // handle complete page load including images and fonts
            $(window).bind('load', delayedResizeHandler);
        });
    };

    // detect css min-height support
    var supportMinHeight = typeof document.documentElement.style.maxHeight !== 'undefined';

    // get elements by rows
    function resizeElementsByRows(boxes, options) {
        var currentRow = $(), maxHeight, maxCalcHeight = 0, firstOffset = boxes.eq(0).offset().top;
        boxes.each(function (ind) {
            var curItem = $(this);
            if (curItem.offset().top === firstOffset) {
                currentRow = currentRow.add(this);
            } else {
                maxHeight = getMaxHeight(currentRow);
                maxCalcHeight = Math.max(maxCalcHeight, resizeElements(currentRow, maxHeight, options));
                currentRow = curItem;
                firstOffset = curItem.offset().top;
            }
        });
        if (currentRow.length) {
            maxHeight = getMaxHeight(currentRow);
            maxCalcHeight = Math.max(maxCalcHeight, resizeElements(currentRow, maxHeight, options));
        }
        if (options.biggestHeight) {
            boxes.css(options.useMinHeight && supportMinHeight ? 'minHeight' : 'height', maxCalcHeight);
        }
    }

    // calculate max element height
    function getMaxHeight(boxes) {
        var maxHeight = 0;
        boxes.each(function () {
            maxHeight = Math.max(maxHeight, $(this).outerHeight());
        });
        return maxHeight;
    }

    // resize helper function
    function resizeElements(boxes, parent, options) {
        var calcHeight;
        var parentHeight = typeof parent === 'number' ? parent : parent.height();
        boxes.removeClass(options.leftEdgeClass).removeClass(options.rightEdgeClass).each(function (i) {
            var element = $(this);
            var depthDiffHeight = 0;
            var isBorderBox = element.css('boxSizing') === 'border-box' || element.css('-moz-box-sizing') === 'border-box' || element.css('-webkit-box-sizing') === 'border-box';

            if (typeof parent !== 'number') {
                element.parents().each(function () {
                    var tmpParent = $(this);
                    if (parent.is(this)) {
                        return false;
                    } else {
                        depthDiffHeight += tmpParent.outerHeight() - tmpParent.height();
                    }
                });
            }
            calcHeight = parentHeight - depthDiffHeight;
            calcHeight -= isBorderBox ? 0 : element.outerHeight() - element.height();

            if (calcHeight > 0) {
                element.css(options.useMinHeight && supportMinHeight ? 'minHeight' : 'height', calcHeight);
            }
        });
        boxes.filter(':first').addClass(options.leftEdgeClass);
        boxes.filter(':last').addClass(options.rightEdgeClass);
        return calcHeight;
    }
}(jQuery));

/*
 * Add class plugin
 */
jQuery.fn.clickClass = function (opt) {
    var options = jQuery.extend({
        classAdd: 'add-class',
        addToParent: false,
        event: 'click'
    }, opt);

    return this.each(function () {
        var classItem = jQuery(this);
        if (options.addToParent) {
            if (typeof options.addToParent === 'boolean') {
                classItem = classItem.parent();
            } else {
                classItem = classItem.parents('.' + options.addToParent);
            }
        }
        jQuery(this).bind(options.event, function (e) {
            classItem.toggleClass(options.classAdd);
            e.preventDefault();
        });
    });
};

/*
 * jQuery FontResize Event
 */
jQuery.onFontResize = (function ($) {
    $(function () {
        var randomID = 'font-resize-frame-' + Math.floor(Math.random() * 1000);
        var resizeFrame = $('<iframe>').attr('id', randomID).addClass('font-resize-helper');

        // required styles
        resizeFrame.css({
            width: '100em',
            height: '10px',
            position: 'absolute',
            borderWidth: 0,
            top: '-9999px',
            left: '-9999px'
        }).appendTo('body');

        // use native IE resize event if possible
        if (window.attachEvent && !window.addEventListener) {
            resizeFrame.bind('resize', function () {
                $.onFontResize.trigger(resizeFrame[0].offsetWidth / 100);
            });
        }
        // use script inside the iframe to detect resize for other browsers
        else {
            var doc = resizeFrame[0].contentWindow.document;
            doc.open();
            doc.write('<scri' + 'pt>window.onload = function(){var em = parent.jQuery("#' + randomID + '")[0];window.onresize = function(){if(parent.jQuery.onFontResize){parent.jQuery.onFontResize.trigger(em.offsetWidth / 100);}}};</scri' + 'pt>');
            doc.close();
        }
        jQuery.onFontResize.initialSize = resizeFrame[0].offsetWidth / 100;
    });
    return {
        // public method, so it can be called from within the iframe
        trigger: function (em) {
            $(window).trigger("fontresize", [em]);
        }
    };
}(jQuery));

/*! http://mths.be/placeholder v2.0.7 by @mathias */
(function (window, document, $) {

    // Opera Mini v7 doesn’t support placeholder although its DOM seems to indicate so
    var isOperaMini = Object.prototype.toString.call(window.operamini) == '[object OperaMini]';
    var isInputSupported = 'placeholder' in document.createElement('input') && !isOperaMini;
    var isTextareaSupported = 'placeholder' in document.createElement('textarea') && !isOperaMini;
    var prototype = $.fn;
    var valHooks = $.valHooks;
    var propHooks = $.propHooks;
    var hooks;
    var placeholder;

    if (isInputSupported && isTextareaSupported) {

        placeholder = prototype.placeholder = function () {
            return this;
        };

        placeholder.input = placeholder.textarea = true;

    } else {

        placeholder = prototype.placeholder = function () {
            var $this = this;
            $this
                .filter((isInputSupported ? 'textarea' : ':input') + '[placeholder]')
                .not('.placeholder')
                .bind({
                    'focus.placeholder': clearPlaceholder,
                    'blur.placeholder': setPlaceholder
                })
                .data('placeholder-enabled', true)
                .trigger('blur.placeholder');
            return $this;
        };

        placeholder.input = isInputSupported;
        placeholder.textarea = isTextareaSupported;

        hooks = {
            'get': function (element) {
                var $element = $(element);

                var $passwordInput = $element.data('placeholder-password');
                if ($passwordInput) {
                    return $passwordInput[0].value;
                }

                return $element.data('placeholder-enabled') && $element.hasClass('placeholder') ? '' : element.value;
            },
            'set': function (element, value) {
                var $element = $(element);

                var $passwordInput = $element.data('placeholder-password');
                if ($passwordInput) {
                    return $passwordInput[0].value = value;
                }

                if (!$element.data('placeholder-enabled')) {
                    return element.value = value;
                }
                if (value == '') {
                    element.value = value;
                    // Issue #56: Setting the placeholder causes problems if the element continues to have focus.
                    if (element != safeActiveElement()) {
                        // We can't use `triggerHandler` here because of dummy text/password inputs :(
                        setPlaceholder.call(element);
                    }
                } else if ($element.hasClass('placeholder')) {
                    clearPlaceholder.call(element, true, value) || (element.value = value);
                } else {
                    element.value = value;
                }
                // `set` can not return `undefined`; see http://jsapi.info/jquery/1.7.1/val#L2363
                return $element;
            }
        };

        if (!isInputSupported) {
            valHooks.input = hooks;
            propHooks.value = hooks;
        }
        if (!isTextareaSupported) {
            valHooks.textarea = hooks;
            propHooks.value = hooks;
        }

        $(function () {
            // Look for forms
            $(document).delegate('form', 'submit.placeholder', function () {
                // Clear the placeholder values so they don't get submitted
                var $inputs = $('.placeholder', this).each(clearPlaceholder);
                setTimeout(function () {
                    $inputs.each(setPlaceholder);
                }, 10);

            });
        });

        // Clear placeholder values upon page reload
        $(window).bind('beforeunload.placeholder', function () {
            $('.placeholder').each(function () {
                this.value = '';
            });
        });

    }

    function args(elem) {
        // Return an object of element attributes
        var newAttrs = {};
        var rinlinejQuery = /^jQuery\d+$/;
        $.each(elem.attributes, function (i, attr) {
            if (attr.specified && !rinlinejQuery.test(attr.name)) {
                newAttrs[attr.name] = attr.value;
            }
        });
        return newAttrs;
    }

    function clearPlaceholder(event, value) {
        var input = this;
        var $input = $(input);
        if (input.value == $input.attr('placeholder') && $input.hasClass('placeholder')) {
            if ($input.data('placeholder-password')) {
                $input = $input.hide().next().show().attr('id', $input.removeAttr('id').data('placeholder-id'));
                // If `clearPlaceholder` was called from `$.valHooks.input.set`
                if (event === true) {
                    return $input[0].value = value;
                }
                $input.focus();
            } else {
                input.value = '';
                $input.removeClass('placeholder');
                input == safeActiveElement() && input.select();
            }
        }
    }

    function setPlaceholder() {
        var $replacement;
        var input = this;
        var $input = $(input);
        var id = this.id;
        if (input.value == '') {
            if (input.type == 'password') {
                if (!$input.data('placeholder-textinput')) {
                    try {
                        $replacement = $input.clone().attr({'type': 'text'});
                    } catch (e) {
                        $replacement = $('<input>').attr($.extend(args(this), {'type': 'text'}));
                    }
                    $replacement
                        .removeAttr('name')
                        .data({
                            'placeholder-password': $input,
                            'placeholder-id': id
                        })
                        .bind('focus.placeholder', clearPlaceholder);
                    $input
                        .data({
                            'placeholder-textinput': $replacement,
                            'placeholder-id': id
                        })
                        .before($replacement);
                }
                $input = $input.removeAttr('id').hide().prev().attr('id', id).show();
                // Note: `$input[0] != input` now!
            }
            $input.addClass('placeholder');
            $input[0].value = $input.attr('placeholder');
        } else {
            $input.removeClass('placeholder');
        }
    }

    function safeActiveElement() {
        // Avoid IE9 `document.activeElement` of death
        // https://github.com/mathiasbynens/jquery-placeholder/pull/99
        try {
            return document.activeElement;
        } catch (err) {
        }
    }

}(this, document, jQuery));

/*!
 * JavaScript Custom Forms
 *
 * Copyright 2014 PSD2HTML (http://psd2html.com)
 * Released under the MIT license (LICENSE.txt)
 *
 * Version: 1.0.3
 */
(function (root, factory) {
    if (typeof define === 'function' && define.amd) {
        define(['jquery'], factory);
    } else if (typeof exports === 'object') {
        module.exports = factory(require('jquery'));
    } else {
        root.jcf = factory(jQuery);
    }
}(this, function ($) {
    'use strict';

    // private variables
    var customInstances = [];

    // default global options
    var commonOptions = {
        optionsKey: 'jcf',
        dataKey: 'jcf-instance',
        rtlClass: 'jcf-rtl',
        focusClass: 'jcf-focus',
        pressedClass: 'jcf-pressed',
        disabledClass: 'jcf-disabled',
        hiddenClass: 'jcf-hidden',
        resetAppearanceClass: 'jcf-reset-appearance',
        unselectableClass: 'jcf-unselectable'
    };

    // detect device type
    var isTouchDevice = ('ontouchstart' in window) || window.DocumentTouch && document instanceof DocumentTouch,
        isWinPhoneDevice = /Windows Phone/.test(navigator.userAgent);
    commonOptions.isMobileDevice = !!(isTouchDevice || isWinPhoneDevice);

    // create global stylesheet if custom forms are used
    var createStyleSheet = function () {
        var styleTag = $('<style>').appendTo('head'),
            styleSheet = styleTag.prop('sheet') || styleTag.prop('styleSheet');

        // crossbrowser style handling
        var addCSSRule = function (selector, rules, index) {
            if (styleSheet.insertRule) {
                if (!index) {
                    index = 0;
                }
                styleSheet.insertRule(selector + '{' + rules + '}', index);
            } else {
                styleSheet.addRule(selector, rules, index);
            }
        };

        // add special rules
        addCSSRule('.' + commonOptions.hiddenClass, 'position:absolute !important;left:-9999px !important;height:1px !important;width:1px !important;margin:0 !important;border-width:0 !important;-webkit-appearance:none;-moz-appearance:none;appearance:none');
        addCSSRule('.' + commonOptions.rtlClass + '.' + commonOptions.hiddenClass, 'right:-9999px !important; left: auto !important');
        addCSSRule('.' + commonOptions.unselectableClass, '-webkit-user-select: none; -moz-user-select: none; -ms-user-select: none; user-select: none;');
        addCSSRule('.' + commonOptions.resetAppearanceClass, 'background: none; border: none; -webkit-appearance: none; appearance: none; opacity: 0; filter: alpha(opacity=0);');

        // detect rtl pages
        var html = $('html'), body = $('body');
        if (html.css('direction') === 'rtl' || body.css('direction') === 'rtl') {
            html.addClass(commonOptions.rtlClass);
        }

        // handle form reset event
        html.on('reset', function () {
            setTimeout(function () {
                api.refreshAll();
            }, 0);
        });

        // mark stylesheet as created
        commonOptions.styleSheetCreated = true;
    };

    // simplified pointer events handler
    (function () {
        var pointerEventsSupported = navigator.pointerEnabled || navigator.msPointerEnabled,
            touchEventsSupported = ('ontouchstart' in window) || window.DocumentTouch && document instanceof DocumentTouch,
            eventList, eventMap = {}, eventPrefix = 'jcf-';

        // detect events to attach
        if (pointerEventsSupported) {
            eventList = {
                pointerover: navigator.pointerEnabled ? 'pointerover' : 'MSPointerOver',
                pointerdown: navigator.pointerEnabled ? 'pointerdown' : 'MSPointerDown',
                pointermove: navigator.pointerEnabled ? 'pointermove' : 'MSPointerMove',
                pointerup: navigator.pointerEnabled ? 'pointerup' : 'MSPointerUp'
            };
        } else {
            eventList = {
                pointerover: 'mouseover',
                pointerdown: 'mousedown' + (touchEventsSupported ? ' touchstart' : ''),
                pointermove: 'mousemove' + (touchEventsSupported ? ' touchmove' : ''),
                pointerup: 'mouseup' + (touchEventsSupported ? ' touchend' : '')
            };
        }

        // create event map
        $.each(eventList, function (targetEventName, fakeEventList) {
            $.each(fakeEventList.split(' '), function (index, fakeEventName) {
                eventMap[fakeEventName] = targetEventName;
            });
        });

        // jQuery event hooks
        $.each(eventList, function (eventName, eventHandlers) {
            eventHandlers = eventHandlers.split(' ');
            $.event.special[eventPrefix + eventName] = {
                setup: function () {
                    var self = this;
                    $.each(eventHandlers, function (index, fallbackEvent) {
                        if (self.addEventListener) self.addEventListener(fallbackEvent, fixEvent, false);
                        else self['on' + fallbackEvent] = fixEvent;
                    });
                },
                teardown: function () {
                    var self = this;
                    $.each(eventHandlers, function (index, fallbackEvent) {
                        if (self.addEventListener) self.removeEventListener(fallbackEvent, fixEvent, false);
                        else self['on' + fallbackEvent] = null;
                    });
                }
            };
        });

        // check that mouse event are not simulated by mobile browsers
        var lastTouch = null;
        var mouseEventSimulated = function (e) {
            var dx = Math.abs(e.pageX - lastTouch.x),
                dy = Math.abs(e.pageY - lastTouch.y),
                rangeDistance = 25;

            if (dx <= rangeDistance && dy <= rangeDistance) {
                return true;
            }
        };

        // normalize event
        var fixEvent = function (e) {
            var origEvent = e || window.event,
                touchEventData = null,
                targetEventName = eventMap[origEvent.type];

            e = $.event.fix(origEvent);
            e.type = eventPrefix + targetEventName;

            if (origEvent.pointerType) {
                switch (origEvent.pointerType) {
                    case 2:
                        e.pointerType = 'touch';
                        break;
                    case 3:
                        e.pointerType = 'pen';
                        break;
                    case 4:
                        e.pointerType = 'mouse';
                        break;
                    default:
                        e.pointerType = origEvent.pointerType;
                }
            } else {
                e.pointerType = origEvent.type.substr(0, 5); // "mouse" or "touch" word length
            }

            if (!e.pageX && !e.pageY) {
                touchEventData = origEvent.changedTouches ? origEvent.changedTouches[0] : origEvent;
                e.pageX = touchEventData.pageX;
                e.pageY = touchEventData.pageY;
            }

            if (origEvent.type === 'touchend') {
                lastTouch = {x: e.pageX, y: e.pageY};
            }
            if (e.pointerType === 'mouse' && lastTouch && mouseEventSimulated(e)) {

            } else {
                return ($.event.dispatch || $.event.handle).call(this, e);
            }
        };
    }());

    // custom mousewheel/trackpad handler
    (function () {
        var wheelEvents = ('onwheel' in document || document.documentMode >= 9 ? 'wheel' : 'mousewheel DOMMouseScroll').split(' '),
            shimEventName = 'jcf-mousewheel';

        $.event.special[shimEventName] = {
            setup: function () {
                var self = this;
                $.each(wheelEvents, function (index, fallbackEvent) {
                    if (self.addEventListener) self.addEventListener(fallbackEvent, fixEvent, false);
                    else self['on' + fallbackEvent] = fixEvent;
                });
            },
            teardown: function () {
                var self = this;
                $.each(wheelEvents, function (index, fallbackEvent) {
                    if (self.addEventListener) self.removeEventListener(fallbackEvent, fixEvent, false);
                    else self['on' + fallbackEvent] = null;
                });
            }
        };

        var fixEvent = function (e) {
            var origEvent = e || window.event;
            e = $.event.fix(origEvent);
            e.type = shimEventName;

            // old wheel events handler
            if ('detail'      in origEvent) {
                e.deltaY = -origEvent.detail;
            }
            if ('wheelDelta'  in origEvent) {
                e.deltaY = -origEvent.wheelDelta;
            }
            if ('wheelDeltaY' in origEvent) {
                e.deltaY = -origEvent.wheelDeltaY;
            }
            if ('wheelDeltaX' in origEvent) {
                e.deltaX = -origEvent.wheelDeltaX;
            }

            // modern wheel event handler
            if ('deltaY' in origEvent) {
                e.deltaY = origEvent.deltaY;
            }
            if ('deltaX' in origEvent) {
                e.deltaX = origEvent.deltaX;
            }

            // handle deltaMode for mouse wheel
            e.delta = e.deltaY || e.deltaX;
            if (origEvent.deltaMode === 1) {
                var lineHeight = 16;
                e.delta *= lineHeight;
                e.deltaY *= lineHeight;
                e.deltaX *= lineHeight;
            }

            return ($.event.dispatch || $.event.handle).call(this, e);
        };
    }());

    // extra module methods
    var moduleMixin = {
        // provide function for firing native events
        fireNativeEvent: function (elements, eventName) {
            $(elements).each(function () {
                var element = this, eventObject;
                if (element.dispatchEvent) {
                    eventObject = document.createEvent('HTMLEvents');
                    eventObject.initEvent(eventName, true, true);
                    element.dispatchEvent(eventObject);
                } else if (document.createEventObject) {
                    eventObject = document.createEventObject();
                    eventObject.target = element;
                    element.fireEvent('on' + eventName, eventObject);
                }
            });
        },
        // bind event handlers for module instance (functions beggining with "on")
        bindHandlers: function () {
            var self = this;
            $.each(self, function (propName, propValue) {
                if (propName.indexOf('on') === 0 && $.isFunction(propValue)) {
                    // dont use $.proxy here because it doesn't create unique handler
                    self[propName] = function () {
                        return propValue.apply(self, arguments);
                    };
                }
            });
        }
    };

    // public API
    var api = {
        modules: {},
        getOptions: function () {
            return $.extend({}, commonOptions);
        },
        setOptions: function (moduleName, moduleOptions) {
            if (arguments.length > 1) {
                // set module options
                if (this.modules[moduleName]) {
                    $.extend(this.modules[moduleName].prototype.options, moduleOptions);
                }
            } else {
                // set common options
                $.extend(commonOptions, moduleName);
            }
        },
        addModule: function (proto) {
            // add module to list
            var Module = function (options) {
                // save instance to collection
                options.element.data(commonOptions.dataKey, this);
                customInstances.push(this);

                // save options
                this.options = $.extend({}, commonOptions, this.options, options.element.data(commonOptions.optionsKey), options);

                // bind event handlers to instance
                this.bindHandlers();

                // call constructor
                this.init.apply(this, arguments);
            };

            // set proto as prototype for new module
            Module.prototype = proto;

            // add mixin methods to module proto
            $.extend(proto, moduleMixin);
            if (proto.plugins) {
                $.each(proto.plugins, function (pluginName, plugin) {
                    $.extend(plugin.prototype, moduleMixin);
                });
            }

            // override destroy method
            var originalDestroy = Module.prototype.destroy;
            Module.prototype.destroy = function () {
                this.options.element.removeData(this.options.dataKey);

                for (var i = customInstances.length - 1; i >= 0; i--) {
                    if (customInstances[i] === this) {
                        customInstances.splice(i, 1);
                        break;
                    }
                }

                if (originalDestroy) {
                    originalDestroy.apply(this, arguments);
                }
            };

            // save module to list
            this.modules[proto.name] = Module;
        },
        getInstance: function (element) {
            return $(element).data(commonOptions.dataKey);
        },
        replace: function (elements, moduleName, customOptions) {
            var self = this,
                instance;

            if (!commonOptions.styleSheetCreated) {
                createStyleSheet();
            }

            $(elements).each(function () {
                var moduleOptions,
                    element = $(this);

                instance = element.data(commonOptions.dataKey);
                if (instance) {
                    instance.refresh();
                } else {
                    if (!moduleName) {
                        $.each(self.modules, function (currentModuleName, module) {
                            if (module.prototype.matchElement.call(module.prototype, element)) {
                                moduleName = currentModuleName;
                                return false;
                            }
                        });
                    }
                    if (moduleName) {
                        moduleOptions = $.extend({element: element}, customOptions);
                        instance = new self.modules[moduleName](moduleOptions);
                    }
                }
            });
            return instance;
        },
        refresh: function (elements) {
            $(elements).each(function () {
                var instance = $(this).data(commonOptions.dataKey);
                if (instance) {
                    instance.refresh();
                }
            });
        },
        destroy: function (elements) {
            $(elements).each(function () {
                var instance = $(this).data(commonOptions.dataKey);
                if (instance) {
                    instance.destroy();
                }
            });
        },
        replaceAll: function (context) {
            var self = this;
            $.each(this.modules, function (moduleName, module) {
                $(module.prototype.selector, context).each(function () {
                    self.replace(this, moduleName);
                });
            });
        },
        refreshAll: function (context) {
            if (context) {
                $.each(this.modules, function (moduleName, module) {
                    $(module.prototype.selector, context).each(function () {
                        var instance = $(this).data(commonOptions.dataKey);
                        if (instance) {
                            instance.refresh();
                        }
                    });
                });
            } else {
                for (var i = customInstances.length - 1; i >= 0; i--) {
                    customInstances[i].refresh();
                }
            }
        },
        destroyAll: function (context) {
            var self = this;
            if (context) {
                $.each(this.modules, function (moduleName, module) {
                    $(module.prototype.selector, context).each(function (index, element) {
                        var instance = $(element).data(commonOptions.dataKey);
                        if (instance) {
                            instance.destroy();
                        }
                    });
                });
            } else {
                while (customInstances.length) {
                    customInstances[0].destroy();
                }
            }
        }
    };

    return api;
}));

/*!
 * JavaScript Custom Forms : Select Module
 *
 * Copyright 2014 PSD2HTML (http://psd2html.com)
 * Released under the MIT license (LICENSE.txt)
 *
 * Version: 1.0.3
 */
(function ($, window) {
    'use strict';

    jcf.addModule({
        name: 'Select',
        selector: 'select',
        options: {
            element: null
        },
        plugins: {
            ListBox: ListBox,
            ComboBox: ComboBox,
            SelectList: SelectList
        },
        matchElement: function (element) {
            return element.is('select');
        },
        init: function () {
            this.element = $(this.options.element);
            this.createInstance();
        },
        isListBox: function () {
            return this.element.is('[size]:not([jcf-size]), [multiple]');
        },
        createInstance: function () {
            if (this.instance) {
                this.instance.destroy();
            }
            if (this.isListBox()) {
                this.instance = new ListBox(this.options);
            } else {
                this.instance = new ComboBox(this.options);
            }
        },
        refresh: function () {
            var typeMismatch = (this.isListBox() && this.instance instanceof ComboBox) ||
                (!this.isListBox() && this.instance instanceof ListBox);

            if (typeMismatch) {
                this.createInstance();
            } else {
                this.instance.refresh();
            }
        },
        destroy: function () {
            this.instance.destroy();
        }
    });

    // combobox module
    function ComboBox(options) {
        this.options = $.extend({
            wrapNative: true,
            wrapNativeOnMobile: true,
            fakeDropInBody: true,
            useCustomScroll: true,
            flipDropToFit: true,
            maxVisibleItems: 10,
            fakeAreaStructure: '<span class="jcf-select"><span class="jcf-select-text"></span><span class="jcf-select-opener"></span></span>',
            fakeDropStructure: '<div class="jcf-select-drop"><div class="jcf-select-drop-content"></div></div>',
            optionClassPrefix: 'jcf-option-',
            selectClassPrefix: 'jcf-select-',
            dropContentSelector: '.jcf-select-drop-content',
            selectTextSelector: '.jcf-select-text',
            dropActiveClass: 'jcf-drop-active',
            flipDropClass: 'jcf-drop-flipped'
        }, options);
        this.init();
    }

    $.extend(ComboBox.prototype, {
        init: function (options) {
            this.initStructure();
            this.bindHandlers();
            this.attachEvents();
            this.refresh();
        },
        initStructure: function () {
            // prepare structure
            this.win = $(window);
            this.doc = $(document);
            this.realElement = $(this.options.element);
            this.fakeElement = $(this.options.fakeAreaStructure).insertAfter(this.realElement);
            this.selectTextContainer = this.fakeElement.find(this.options.selectTextSelector);
            this.selectText = $('<span></span>').appendTo(this.selectTextContainer);
            makeUnselectable(this.fakeElement);

            // copy classes from original select
            this.fakeElement.addClass(getPrefixedClasses(this.realElement.prop('className'), this.options.selectClassPrefix));

            // detect device type and dropdown behavior
            if (this.options.isMobileDevice && this.options.wrapNativeOnMobile && !this.options.wrapNative) {
                this.options.wrapNative = true;
            }

            if (this.options.wrapNative) {
                // wrap native select inside fake block
                this.realElement.prependTo(this.fakeElement).css({
                    position: 'absolute',
                    height: '100%',
                    width: '100%'
                }).addClass(this.options.resetAppearanceClass);
            } else {
                // just hide native select
                this.realElement.addClass(this.options.hiddenClass);
                this.fakeElement.attr('title', this.realElement.attr('title'));
                this.fakeDropTarget = this.options.fakeDropInBody ? $('body') : this.fakeElement;
            }
        },
        attachEvents: function () {
            // delayed refresh handler
            var self = this;
            this.delayedRefresh = function () {
                setTimeout(function () {
                    self.refresh();
                    if (self.list) {
                        self.list.refresh();
                    }
                }, 1);
            };

            // native dropdown event handlers
            if (this.options.wrapNative) {
                this.realElement.on({
                    focus: this.onFocus,
                    change: this.onChange,
                    click: this.onChange,
                    keydown: this.onChange
                });
            } else {
                // custom dropdown event handlers
                this.realElement.on({
                    focus: this.onFocus,
                    change: this.onChange,
                    keydown: this.onKeyDown
                });
                this.fakeElement.on({
                    'jcf-pointerdown': this.onSelectAreaPress
                });
            }
        },
        onKeyDown: function (e) {
            if (e.which === 13) {
                this.toggleDropdown();
            } else if (this.dropActive) {
                this.delayedRefresh();
            }
        },
        onChange: function () {
            this.refresh();
        },
        onFocus: function () {
            if (!this.pressedFlag || !this.focusedFlag) {
                this.fakeElement.addClass(this.options.focusClass);
                this.realElement.on('blur', this.onBlur);
                this.toggleListMode(true);
                this.focusedFlag = true;
            }
        },
        onBlur: function () {
            if (!this.pressedFlag) {
                this.fakeElement.removeClass(this.options.focusClass);
                this.realElement.off('blur', this.onBlur);
                this.toggleListMode(false);
                this.focusedFlag = false;
            }
        },
        onResize: function () {
            if (this.dropActive) {
                this.hideDropdown();
            }
        },
        onSelectDropPress: function () {
            this.pressedFlag = true;
        },
        onSelectDropRelease: function (e, pointerEvent) {
            this.pressedFlag = false;
            if (pointerEvent.pointerType === 'mouse') {
                this.realElement.focus();
            }
        },
        onSelectAreaPress: function (e) {
            // skip click if drop inside fake element or real select is disabled
            var dropClickedInsideFakeElement = !this.options.fakeDropInBody && $(e.target).closest(this.dropdown).length;
            if (dropClickedInsideFakeElement || e.button > 1 || this.realElement.is(':disabled')) {
                return;
            }

            // toggle dropdown visibility
            this.selectOpenedByEvent = e.pointerType;
            this.toggleDropdown();

            // misc handlers
            if (!this.focusedFlag) {
                if (e.pointerType === 'mouse') {
                    this.realElement.focus();
                } else {
                    this.onFocus(e);
                }
            }
            this.pressedFlag = true;
            this.fakeElement.addClass(this.options.pressedClass);
            this.doc.on('jcf-pointerup', this.onSelectAreaRelease);
        },
        onSelectAreaRelease: function (e) {
            if (this.focusedFlag && e.pointerType === 'mouse') {
                this.realElement.focus();
            }
            this.pressedFlag = false;
            this.fakeElement.removeClass(this.options.pressedClass);
            this.doc.off('jcf-pointerup', this.onSelectAreaRelease);
        },
        onOutsideClick: function (e) {
            var target = $(e.target),
                clickedInsideSelect = target.closest(this.fakeElement).length || target.closest(this.dropdown).length;

            if (!clickedInsideSelect) {
                this.hideDropdown();
            }
        },
        onSelect: function () {
            this.hideDropdown();
            this.refresh();
            this.fireNativeEvent(this.realElement, 'change');
        },
        toggleListMode: function (state) {
            if (!this.options.wrapNative) {
                if (state) {
                    // temporary change select to list to avoid appearing of native dropdown
                    this.realElement.attr({
                        size: 4,
                        'jcf-size': ''
                    });
                } else {
                    // restore select from list mode to dropdown select
                    if (!this.options.wrapNative) {
                        this.realElement.removeAttr('size jcf-size');
                    }
                }
            }
        },
        createDropdown: function () {
            // destroy previous dropdown if needed
            if (this.dropdown) {
                this.list.destroy();
                this.dropdown.remove();
            }

            // create new drop container
            this.dropdown = $(this.options.fakeDropStructure).appendTo(this.fakeDropTarget);
            this.dropdown.addClass(getPrefixedClasses(this.realElement.prop('className'), this.options.selectClassPrefix));
            makeUnselectable(this.dropdown);

            // set initial styles for dropdown in body
            if (this.options.fakeDropInBody) {
                this.dropdown.css({
                    position: 'absolute',
                    top: -9999
                });
            }

            // create new select list instance
            this.list = new SelectList({
                useHoverClass: true,
                handleResize: false,
                alwaysPreventMouseWheel: true,
                maxVisibleItems: this.options.maxVisibleItems,
                useCustomScroll: this.options.useCustomScroll,
                holder: this.dropdown.find(this.options.dropContentSelector),
                element: this.realElement
            });
            $(this.list).on({
                select: this.onSelect,
                press: this.onSelectDropPress,
                release: this.onSelectDropRelease
            });
        },
        repositionDropdown: function () {
            var selectOffset = this.fakeElement.offset(),
                selectWidth = this.fakeElement.outerWidth(),
                selectHeight = this.fakeElement.outerHeight(),
                dropHeight = this.dropdown.css('width', selectWidth).outerHeight(),
                winScrollTop = this.win.scrollTop(),
                winHeight = this.win.height(),
                calcTop, calcLeft, bodyOffset, needFlipDrop = false;

            // check flip drop position
            if (selectOffset.top + selectHeight + dropHeight > winScrollTop + winHeight && selectOffset.top - dropHeight > winScrollTop) {
                needFlipDrop = true;
            }

            if (this.options.fakeDropInBody) {
                bodyOffset = this.fakeDropTarget.css('position') !== 'static' ? this.fakeDropTarget.offset().top : 0;
                if (this.options.flipDropToFit && needFlipDrop) {
                    // calculate flipped dropdown position
                    calcLeft = selectOffset.left;
                    calcTop = selectOffset.top - dropHeight - bodyOffset;
                } else {
                    // calculate default drop position
                    calcLeft = selectOffset.left;
                    calcTop = selectOffset.top + selectHeight - bodyOffset;
                }

                // update drop styles
                this.dropdown.css({
                    width: selectWidth,
                    left: calcLeft,
                    top: calcTop
                });
            }

            // refresh flipped class
            this.dropdown.add(this.fakeElement).toggleClass(this.options.flipDropClass, this.options.flipDropToFit && needFlipDrop);
        },
        showDropdown: function () {
            // do not show empty custom dropdown
            if (!this.realElement.prop('options').length) {
                return;
            }

            // create options list if not created
            if (!this.dropdown) {
                this.createDropdown();
            }

            // show dropdown
            this.dropActive = true;
            this.dropdown.appendTo(this.fakeDropTarget);
            this.fakeElement.addClass(this.options.dropActiveClass);
            this.refreshSelectedText();
            this.repositionDropdown();
            this.list.setScrollTop(this.savedScrollTop);
            this.list.refresh();

            // add temporary event handlers
            this.win.on('resize', this.onResize);
            this.doc.on('jcf-pointerdown', this.onOutsideClick);
        },
        hideDropdown: function () {
            if (this.dropdown) {
                this.savedScrollTop = this.list.getScrollTop();
                this.fakeElement.removeClass(this.options.dropActiveClass + ' ' + this.options.flipDropClass);
                this.dropdown.removeClass(this.options.flipDropClass).detach();
                this.doc.off('jcf-pointerdown', this.onOutsideClick);
                this.win.off('resize', this.onResize);
                this.dropActive = false;
                if (this.selectOpenedByEvent === 'touch') {
                    this.onBlur();
                }
            }
        },
        toggleDropdown: function () {
            if (this.dropActive) {
                this.hideDropdown();
            } else {
                this.showDropdown();
            }
        },
        refreshSelectedText: function () {
            // redraw selected area
            var selectedIndex = this.realElement.prop('selectedIndex'),
                selectedOption = this.realElement.prop('options')[selectedIndex],
                selectedOptionImage = selectedOption ? selectedOption.getAttribute('data-image') : null,
                selectedOptionClasses,
                selectedFakeElement;

            if (!selectedOption) {
                if (this.selectImage) {
                    this.selectImage.hide();
                }
                this.selectText.removeAttr('class').empty();
            } else if (this.currentSelectedText !== selectedOption.innerHTML || this.currentSelectedImage !== selectedOptionImage) {
                selectedOptionClasses = getPrefixedClasses(selectedOption.className, this.options.optionClassPrefix);
                this.selectText.attr('class', selectedOptionClasses).html(selectedOption.innerHTML);

                if (selectedOptionImage) {
                    if (!this.selectImage) {
                        this.selectImage = $('<img>').prependTo(this.selectTextContainer).hide();
                    }
                    this.selectImage.attr('src', selectedOptionImage).show();
                } else if (this.selectImage) {
                    this.selectImage.hide();
                }

                this.currentSelectedText = selectedOption.innerHTML;
                this.currentSelectedImage = selectedOptionImage;
            }
        },
        refresh: function () {
            // refresh fake select visibility
            if (this.realElement.prop('style').display === 'none') {
                this.fakeElement.hide();
            } else {
                this.fakeElement.show();
            }

            // refresh selected text
            this.refreshSelectedText();

            // handle disabled state
            this.fakeElement.toggleClass(this.options.disabledClass, this.realElement.is(':disabled'));
        },
        destroy: function () {
            // restore structure
            if (this.options.wrapNative) {
                this.realElement.insertBefore(this.fakeElement).css({
                    position: '',
                    height: '',
                    width: ''
                }).removeClass(this.options.resetAppearanceClass);
            } else {
                this.realElement.removeClass(this.options.hiddenClass);
                if (this.realElement.is('[jcf-size]')) {
                    this.realElement.removeAttr('size jcf-size');
                }
            }

            // removing element will also remove its event handlers
            this.fakeElement.remove();

            // remove other event handlers
            this.doc.off('jcf-pointerup', this.onSelectAreaRelease);
            this.realElement.off({
                focus: this.onFocus
            });
        }
    });

    // listbox module
    function ListBox(options) {
        this.options = $.extend({
            wrapNative: true,
            useCustomScroll: true,
            fakeStructure: '<span class="jcf-list-box"><span class="jcf-list-wrapper"></span></span>',
            selectClassPrefix: 'jcf-select-',
            listHolder: '.jcf-list-wrapper'
        }, options);
        this.init();
    }

    $.extend(ListBox.prototype, {
        init: function (options) {
            this.bindHandlers();
            this.initStructure();
            this.attachEvents();
        },
        initStructure: function () {
            var self = this;
            this.realElement = $(this.options.element);
            this.fakeElement = $(this.options.fakeStructure).insertAfter(this.realElement);
            this.listHolder = this.fakeElement.find(this.options.listHolder);
            makeUnselectable(this.fakeElement);

            // copy classes from original select
            this.fakeElement.addClass(getPrefixedClasses(this.realElement.prop('className'), this.options.selectClassPrefix));
            this.realElement.addClass(this.options.hiddenClass);

            this.list = new SelectList({
                useCustomScroll: this.options.useCustomScroll,
                holder: this.listHolder,
                selectOnClick: false,
                element: this.realElement
            });
        },
        attachEvents: function () {
            // delayed refresh handler
            var self = this;
            this.delayedRefresh = function (e) {
                if (e && e.keyCode == 16) {
                    // ignore SHIFT key

                } else {
                    clearTimeout(self.refreshTimer);
                    self.refreshTimer = setTimeout(function () {
                        self.refresh();
                    }, 1);
                }
            };

            // other event handlers
            this.realElement.on({
                focus: this.onFocus,
                click: this.delayedRefresh,
                keydown: this.delayedRefresh
            });

            // select list event handlers
            $(this.list).on({
                select: this.onSelect,
                press: this.onFakeOptionsPress,
                release: this.onFakeOptionsRelease
            });
        },
        onFakeOptionsPress: function (e, pointerEvent) {
            this.pressedFlag = true;
            if (pointerEvent.pointerType === 'mouse') {
                this.realElement.focus();
            }
        },
        onFakeOptionsRelease: function (e, pointerEvent) {
            this.pressedFlag = false;
            if (pointerEvent.pointerType === 'mouse') {
                this.realElement.focus();
            }
        },
        onSelect: function () {
            this.fireNativeEvent(this.realElement, 'change');
            this.fireNativeEvent(this.realElement, 'click');
        },
        onFocus: function () {
            if (!this.pressedFlag || !this.focusedFlag) {
                this.fakeElement.addClass(this.options.focusClass);
                this.realElement.on('blur', this.onBlur);
                this.focusedFlag = true;
            }
        },
        onBlur: function () {
            if (!this.pressedFlag) {
                this.fakeElement.removeClass(this.options.focusClass);
                this.realElement.off('blur', this.onBlur);
                this.focusedFlag = false;
            }
        },
        refresh: function () {
            this.fakeElement.toggleClass(this.options.disabledClass, this.realElement.is(':disabled'));
            this.list.refresh();
        },
        destroy: function () {
            this.list.destroy();
            this.realElement.insertBefore(this.fakeElement).removeClass(this.options.hiddenClass);
            this.fakeElement.remove();
        }
    });

    // options list module
    function SelectList(options) {
        this.options = $.extend({
            holder: null,
            maxVisibleItems: 10,
            selectOnClick: true,
            useHoverClass: false,
            useCustomScroll: false,
            handleResize: true,
            alwaysPreventMouseWheel: false,
            indexAttribute: 'data-index',
            cloneClassPrefix: 'jcf-option-',
            containerStructure: '<span class="jcf-list"><span class="jcf-list-content"></span></span>',
            containerSelector: '.jcf-list-content',
            captionClass: 'jcf-optgroup-caption',
            disabledClass: 'jcf-disabled',
            optionClass: 'jcf-option',
            groupClass: 'jcf-optgroup',
            hoverClass: 'jcf-hover',
            selectedClass: 'jcf-selected',
            scrollClass: 'jcf-scroll-active'
        }, options);
        this.init();
    }

    $.extend(SelectList.prototype, {
        init: function () {
            this.initStructure();
            this.refreshSelectedClass();
            this.attachEvents();
        },
        initStructure: function () {
            this.element = $(this.options.element);
            this.indexSelector = '[' + this.options.indexAttribute + ']';
            this.container = $(this.options.containerStructure).appendTo(this.options.holder);
            this.listHolder = this.container.find(this.options.containerSelector);
            this.lastClickedIndex = this.element.prop('selectedIndex');
            this.rebuildList();
        },
        attachEvents: function () {
            this.bindHandlers();
            this.listHolder.on('jcf-pointerdown', this.indexSelector, this.onItemPress);
            this.listHolder.on('jcf-pointerdown', this.onPress);

            if (this.options.useHoverClass) {
                this.listHolder.on('jcf-pointerover', this.indexSelector, this.onHoverItem);
            }
        },
        onPress: function (e) {
            $(this).trigger('press', e);
            this.listHolder.on('jcf-pointerup', this.onRelease);
        },
        onRelease: function (e) {
            $(this).trigger('release', e);
            this.listHolder.off('jcf-pointerup', this.onRelease);
        },
        onHoverItem: function (e) {
            var hoverIndex = parseFloat(e.currentTarget.getAttribute(this.options.indexAttribute));
            this.fakeOptions.removeClass(this.options.hoverClass).eq(hoverIndex).addClass(this.options.hoverClass);
        },
        onItemPress: function (e) {
            if (e.pointerType === 'touch' || this.options.selectOnClick) {
                // select option after "click"
                this.tmpListOffsetTop = this.list.offset().top;
                this.listHolder.on('jcf-pointerup', this.indexSelector, this.onItemRelease);
            } else {
                // select option immediately
                this.onSelectItem(e);
            }
        },
        onItemRelease: function (e) {
            // remove event handlers and temporary data
            this.listHolder.off('jcf-pointerup', this.indexSelector, this.onItemRelease);

            // simulate item selection
            if (this.tmpListOffsetTop === this.list.offset().top) {
                this.listHolder.on('click', this.indexSelector, this.onSelectItem);
            }
            delete this.tmpListOffsetTop;
        },
        onSelectItem: function (e) {
            var clickedIndex = parseFloat(e.currentTarget.getAttribute(this.options.indexAttribute)),
                range;

            // remove click event handler
            this.listHolder.off('click', this.indexSelector, this.onSelectItem);

            // ignore clicks on disabled options
            if (e.button > 1 || this.realOptions[clickedIndex].disabled) {
                return;
            }

            if (this.element.prop('multiple')) {
                if (e.metaKey || e.ctrlKey || e.pointerType === 'touch') {
                    // if CTRL/CMD pressed or touch devices - toggle selected option
                    this.realOptions[clickedIndex].selected = !this.realOptions[clickedIndex].selected;
                } else if (e.shiftKey) {
                    // if SHIFT pressed - update selection
                    range = [this.lastClickedIndex, clickedIndex].sort(function (a, b) {
                        return a - b;
                    });
                    this.realOptions.each(function (index, option) {
                        option.selected = (index >= range[0] && index <= range[1]);
                    });
                } else {
                    // set single selected index
                    //abe--
                    //this.element.prop('selectedIndex', clickedIndex);
                    //abe++
                    this.realOptions[clickedIndex].selected = !this.realOptions[clickedIndex].selected;
                }
            } else {
                this.element.prop('selectedIndex', clickedIndex);
            }

            // save last clicked option
            if (!e.shiftKey) {
                this.lastClickedIndex = clickedIndex;
            }

            // refresh classes
            this.refreshSelectedClass();

            // scroll to active item in desktop browsers
            if (e.pointerType === 'mouse') {
                this.scrollToActiveOption();
            }

            // make callback when item selected
            $(this).trigger('select');
        },
        rebuildList: function () {
            // rebuild options
            var self = this,
                rootElement = this.element[0];

            // recursively create fake options
            this.storedSelectHTML = rootElement.innerHTML;
            this.optionIndex = 0;
            this.list = $(this.createOptionsList(rootElement));
            this.listHolder.empty().append(this.list);
            this.realOptions = this.element.find('option');
            this.fakeOptions = this.list.find(this.indexSelector);
            this.fakeListItems = this.list.find('.' + this.options.captionClass + ',' + this.indexSelector);
            delete this.optionIndex;

            // detect max visible items
            var maxCount = this.options.maxVisibleItems,
                sizeValue = this.element.prop('size');
            if (sizeValue > 1 && !this.element.is('[jcf-size]')) {
                maxCount = sizeValue;
            }

            // handle scrollbar
            var needScrollBar = this.fakeOptions.length > maxCount;
            this.container.toggleClass(this.options.scrollClass, needScrollBar);
            if (needScrollBar) {
                // change max-height
                this.listHolder.css({
                    maxHeight: this.getOverflowHeight(maxCount),
                    overflow: 'auto'
                });

                if (this.options.useCustomScroll && jcf.modules.Scrollable) {
                    // add custom scrollbar if specified in options
                    jcf.replace(this.listHolder, 'Scrollable', {
                        handleResize: this.options.handleResize,
                        alwaysPreventMouseWheel: this.options.alwaysPreventMouseWheel
                    });
                    return;
                }
            }

            // disable edge wheel scrolling
            if (this.options.alwaysPreventMouseWheel) {
                this.preventWheelHandler = function (e) {
                    var currentScrollTop = self.listHolder.scrollTop(),
                        maxScrollTop = self.listHolder.prop('scrollHeight') - self.listHolder.innerHeight(),
                        maxScrollLeft = self.listHolder.prop('scrollWidth') - self.listHolder.innerWidth();

                    // check edge cases
                    if ((currentScrollTop <= 0 && e.deltaY < 0) || (currentScrollTop >= maxScrollTop && e.deltaY > 0)) {
                        e.preventDefault();
                    }
                };
                this.listHolder.on('jcf-mousewheel', this.preventWheelHandler);
            }
        },
        refreshSelectedClass: function () {
            var self = this,
                selectedItem,
                isMultiple = this.element.prop('multiple'),
                selectedIndex = this.element.prop('selectedIndex');

            if (isMultiple) {
                this.realOptions.each(function (index, option) {
                    self.fakeOptions.eq(index).toggleClass(self.options.selectedClass, !!option.selected);
                });
            } else {
                this.fakeOptions.removeClass(this.options.selectedClass + ' ' + this.options.hoverClass);
                selectedItem = this.fakeOptions.eq(selectedIndex).addClass(this.options.selectedClass);
                if (this.options.useHoverClass) {
                    selectedItem.addClass(this.options.hoverClass);
                }
            }
        },
        scrollToActiveOption: function () {
            // scroll to target option
            var targetOffset = this.getActiveOptionOffset();
            this.listHolder.prop('scrollTop', targetOffset);
        },
        getSelectedIndexRange: function () {
            var firstSelected = -1, lastSelected = -1;
            this.realOptions.each(function (index, option) {
                if (option.selected) {
                    if (firstSelected < 0) {
                        firstSelected = index;
                    }
                    lastSelected = index;
                }
            });
            return [firstSelected, lastSelected];
        },
        getChangedSelectedIndex: function () {
            var selectedIndex = this.element.prop('selectedIndex'),
                targetIndex;

            if (this.element.prop('multiple')) {
                // multiple selects handling
                if (!this.previousRange) {
                    this.previousRange = [selectedIndex, selectedIndex];
                }
                this.currentRange = this.getSelectedIndexRange();
                targetIndex = this.currentRange[this.currentRange[0] !== this.previousRange[0] ? 0 : 1];
                this.previousRange = this.currentRange;
                return targetIndex;
            } else {
                // single choice selects handling
                return selectedIndex;
            }
        },
        getActiveOptionOffset: function () {
            // calc values
            var dropHeight = this.listHolder.height(),
                dropScrollTop = this.listHolder.prop('scrollTop'),
                currentIndex = this.getChangedSelectedIndex(),
                fakeOption = this.fakeOptions.eq(currentIndex),
                fakeOptionOffset = fakeOption.offset().top - this.list.offset().top,
                fakeOptionHeight = fakeOption.innerHeight();

            // scroll list
            if (fakeOptionOffset + fakeOptionHeight >= dropScrollTop + dropHeight) {
                // scroll down (always scroll to option)
                return fakeOptionOffset - dropHeight + fakeOptionHeight;
            } else if (fakeOptionOffset < dropScrollTop) {
                // scroll up to option
                return fakeOptionOffset;
            }
        },
        getOverflowHeight: function (sizeValue) {
            var item = this.fakeListItems.eq(sizeValue - 1),
                listOffset = this.list.offset().top,
                itemOffset = item.offset().top,
                itemHeight = item.innerHeight();

            return itemOffset + itemHeight - listOffset;
        },
        getScrollTop: function () {
            return this.listHolder.scrollTop();
        },
        setScrollTop: function (value) {
            this.listHolder.scrollTop(value);
        },
        createOption: function (option) {
            var newOption = document.createElement('span');
            newOption.className = this.options.optionClass;
            //abe-- newOption.innerHTML = option.innerHTML ;
            //abe++
            newOption.innerHTML = option.text;
            newOption.setAttribute(this.options.indexAttribute, this.optionIndex++);

            var optionImage, optionImageSrc = option.getAttribute('data-image');
            if (optionImageSrc) {
                optionImage = document.createElement('img');
                optionImage.src = optionImageSrc;
                newOption.insertBefore(optionImage, newOption.childNodes[0]);
            }
            if (option.disabled) {
                newOption.className += ' ' + this.options.disabledClass;
            }
            if (option.className) {
                newOption.className += ' ' + getPrefixedClasses(option.className, this.options.cloneClassPrefix);
            }
            return newOption;
        },
        createOptGroup: function (optgroup) {
            var optGroupContainer = document.createElement('span'),
                optGroupName = optgroup.getAttribute('label'),
                optGroupCaption, optGroupList;

            // create caption
            optGroupCaption = document.createElement('span');
            optGroupCaption.className = this.options.captionClass;
            optGroupCaption.innerHTML = optGroupName;
            optGroupContainer.appendChild(optGroupCaption);

            // create list of options
            if (optgroup.children.length) {
                optGroupList = this.createOptionsList(optgroup);
                optGroupContainer.appendChild(optGroupList);
            }

            optGroupContainer.className = this.options.groupClass;
            return optGroupContainer;
        },
        createOptionContainer: function () {
            var optionContainer = document.createElement('li');
            return optionContainer;
        },
        createOptionsList: function (container) {
            var self = this,
                list = document.createElement('ul');

            $.each(container.children, function (index, currentNode) {
                var item = self.createOptionContainer(currentNode),
                    newNode;

                switch (currentNode.tagName.toLowerCase()) {
                    case 'option':
                        newNode = self.createOption(currentNode);
                        break;
                    case 'optgroup':
                        newNode = self.createOptGroup(currentNode);
                        break;
                }
                list.appendChild(item).appendChild(newNode);
            });
            return list;
        },
        refresh: function () {
            // check for select innerHTML changes
            if (this.storedSelectHTML !== this.element.prop('innerHTML')) {
                this.rebuildList();
            }

            // refresh custom scrollbar
            var scrollInstance = jcf.getInstance(this.listHolder);
            if (scrollInstance) {
                scrollInstance.refresh();
            }

            // scroll active option into view
            this.scrollToActiveOption();

            // refresh selectes classes
            this.refreshSelectedClass();
        },
        destroy: function () {
            this.listHolder.off('jcf-mousewheel', this.preventWheelHandler);
            this.listHolder.off('jcf-pointerdown', this.indexSelector, this.onSelectItem);
            this.listHolder.off('jcf-pointerover', this.indexSelector, this.onHoverItem);
            this.listHolder.off('jcf-pointerdown', this.onPress);
        }
    });

    // helper functions
    var getPrefixedClasses = function (className, prefixToAdd) {
        return className ? className.replace(/[\s]*([\S]+)+[\s]*/gi, prefixToAdd + '$1 ') : '';
    };
    var makeUnselectable = (function () {
        var unselectableClass = jcf.getOptions().unselectableClass;

        function preventHandler(e) {
            e.preventDefault();
        }

        return function (node) {
            node.addClass(unselectableClass).on('selectstart', preventHandler);
        };
    }());

}(jQuery, this));

/*!
 * JavaScript Custom Forms : Radio Module
 *
 * Copyright 2014 PSD2HTML (http://psd2html.com)
 * Released under the MIT license (LICENSE.txt)
 *
 * Version: 1.0.3
 */
(function ($, window) {
    'use strict';

    jcf.addModule({
        name: 'Radio',
        selector: 'input[type="radio"]',
        options: {
            wrapNative: true,
            checkedClass: 'jcf-checked',
            uncheckedClass: 'jcf-unchecked',
            labelActiveClass: 'jcf-label-active',
            fakeStructure: '<span class="jcf-radio"><span></span></span>'
        },
        matchElement: function (element) {
            return element.is(':radio');
        },
        init: function (options) {
            this.initStructure();
            this.attachEvents();
            this.refresh();
        },
        initStructure: function () {
            // prepare structure
            this.doc = $(document);
            this.realElement = $(this.options.element);
            this.fakeElement = $(this.options.fakeStructure).insertAfter(this.realElement);
            this.labelElement = this.getLabelFor();

            if (this.options.wrapNative) {
                // wrap native radio inside fake block
                this.realElement.prependTo(this.fakeElement).css({
                    position: 'absolute',
                    opacity: 0
                });
            } else {
                // just hide native radio
                this.realElement.addClass(this.options.hiddenClass);
            }
        },
        attachEvents: function () {
            // add event handlers
            this.realElement.on({
                focus: this.onFocus,
                click: this.onRealClick
            });
            this.fakeElement.on('click', this.onFakeClick);
            this.fakeElement.on('jcf-pointerdown', this.onPress);
        },
        onRealClick: function (e) {
            // redraw current radio and its group (setTimeout handles click that might be prevented)
            var self = this;
            this.savedEventObject = e;
            setTimeout(function () {
                self.refreshRadioGroup();
            }, 0);
        },
        onFakeClick: function (e) {
            // skip event if clicked on real element inside wrapper
            if (this.options.wrapNative && this.realElement.is(e.target)) {
                return;
            }

            // toggle checked class
            if (!this.realElement.is(':disabled')) {
                delete this.savedEventObject;
                this.currentActiveRadio = this.getCurrentActiveRadio();
                this.stateChecked = this.realElement.prop('checked');
                this.realElement.prop('checked', true);
                this.fireNativeEvent(this.realElement, 'click');
                if (this.savedEventObject && this.savedEventObject.isDefaultPrevented()) {
                    this.realElement.prop('checked', this.stateChecked);
                    this.currentActiveRadio.prop('checked', true);
                } else {
                    this.fireNativeEvent(this.realElement, 'change');
                }
                delete this.savedEventObject;
            }
        },
        onFocus: function () {
            if (!this.pressedFlag || !this.focusedFlag) {
                this.focusedFlag = true;
                this.fakeElement.addClass(this.options.focusClass);
                this.realElement.on('blur', this.onBlur);
            }
        },
        onBlur: function () {
            if (!this.pressedFlag) {
                this.focusedFlag = false;
                this.fakeElement.removeClass(this.options.focusClass);
                this.realElement.off('blur', this.onBlur);
            }
        },
        onPress: function (e) {
            if (!this.focusedFlag && e.pointerType === 'mouse') {
                this.realElement.focus();
            }
            this.pressedFlag = true;
            this.fakeElement.addClass(this.options.pressedClass);
            this.doc.on('jcf-pointerup', this.onRelease);
        },
        onRelease: function (e) {
            if (this.focusedFlag && e.pointerType === 'mouse') {
                this.realElement.focus();
            }
            this.pressedFlag = false;
            this.fakeElement.removeClass(this.options.pressedClass);
            this.doc.off('jcf-pointerup', this.onRelease);
        },
        getCurrentActiveRadio: function () {
            return this.getRadioGroup(this.realElement).filter(':checked');
        },
        getRadioGroup: function (radio) {
            // find radio group for specified radio button
            var name = radio.attr('name'),
                parentForm = radio.parents('form');

            if (name) {
                if (parentForm.length) {
                    return parentForm.find('input[name="' + name + '"]');
                } else {
                    return $('input[name="' + name + '"]:not(form input)');
                }
            } else {
                return radio;
            }
        },
        getLabelFor: function () {
            var parentLabel = this.realElement.closest('label'),
                elementId = this.realElement.prop('id');

            if (!parentLabel.length && elementId) {
                parentLabel = $('label[for="' + elementId + '"]');
            }
            return parentLabel.length ? parentLabel : null;
        },
        refreshRadioGroup: function () {
            // redraw current radio and its group
            this.getRadioGroup(this.realElement).each(function () {
                jcf.refresh(this);
            });
        },
        refresh: function () {
            // redraw current radio button
            var isChecked = this.realElement.is(':checked'),
                isDisabled = this.realElement.is(':disabled');

            this.fakeElement.toggleClass(this.options.checkedClass, isChecked)
                .toggleClass(this.options.uncheckedClass, !isChecked)
                .toggleClass(this.options.disabledClass, isDisabled);

            if (this.labelElement) {
                this.labelElement.toggleClass(this.options.labelActiveClass, isChecked);
            }
        },
        destroy: function () {
            // restore structure
            if (this.options.wrapNative) {
                this.realElement.insertBefore(this.fakeElement).css({
                    position: '',
                    width: '',
                    height: '',
                    opacity: '',
                    margin: ''
                });
            } else {
                this.realElement.removeClass(this.options.hiddenClass);
            }

            // removing element will also remove its event handlers
            this.fakeElement.off('jcf-pointerdown', this.onPress);
            this.fakeElement.remove();

            // remove other event handlers
            this.doc.off('jcf-pointerup', this.onRelease);
            this.realElement.off({
                blur: this.onBlur,
                focus: this.onFocus,
                click: this.onRealClick
            });
        }
    });

}(jQuery, this));

/*!
 * JavaScript Custom Forms : Checkbox Module
 *
 * Copyright 2014 PSD2HTML (http://psd2html.com)
 * Released under the MIT license (LICENSE.txt)
 *
 * Version: 1.0.3
 */
(function ($, window) {
    'use strict';

    jcf.addModule({
        name: 'Checkbox',
        selector: 'input[type="checkbox"]',
        options: {
            wrapNative: true,
            checkedClass: 'jcf-checked',
            uncheckedClass: 'jcf-unchecked',
            labelActiveClass: 'jcf-label-active',
            fakeStructure: '<span class="jcf-checkbox"><span></span></span>'
        },
        matchElement: function (element) {
            return element.is(':checkbox');
        },
        init: function (options) {
            this.initStructure();
            this.attachEvents();
            this.refresh();
        },
        initStructure: function () {
            // prepare structure
            this.doc = $(document);
            this.realElement = $(this.options.element);
            this.fakeElement = $(this.options.fakeStructure).insertAfter(this.realElement);
            this.labelElement = this.getLabelFor();

            if (this.options.wrapNative) {
                // wrap native checkbox inside fake block
                this.realElement.appendTo(this.fakeElement).css({
                    position: 'absolute',
                    height: '100%',
                    width: '100%',
                    opacity: 0,
                    margin: 0
                });
            } else {
                // just hide native checkbox
                this.realElement.addClass(this.options.hiddenClass);
            }
        },
        attachEvents: function () {
            // add event handlers
            this.realElement.on({
                focus: this.onFocus,
                click: this.onRealClick
            });
            this.fakeElement.on('click', this.onFakeClick);
            this.fakeElement.on('jcf-pointerdown', this.onPress);
        },
        onRealClick: function (e) {
            // just redraw fake element (setTimeout handles click that might be prevented)
            var self = this;
            this.savedEventObject = e;
            setTimeout(function () {
                self.refresh();
            }, 0);
        },
        onFakeClick: function (e) {
            // skip event if clicked on real element inside wrapper
            if (this.options.wrapNative && this.realElement.is(e.target)) {
                return;
            }

            // toggle checked class
            if (!this.realElement.is(':disabled')) {
                delete this.savedEventObject;
                this.stateChecked = this.realElement.prop('checked');
                this.realElement.prop('checked', !this.stateChecked);
                this.fireNativeEvent(this.realElement, 'click');
                if (this.savedEventObject && this.savedEventObject.isDefaultPrevented()) {
                    this.realElement.prop('checked', this.stateChecked);
                } else {
                    this.fireNativeEvent(this.realElement, 'change');
                }
                delete this.savedEventObject;
            }
        },
        onFocus: function () {
            if (!this.pressedFlag || !this.focusedFlag) {
                this.focusedFlag = true;
                this.fakeElement.addClass(this.options.focusClass);
                this.realElement.on('blur', this.onBlur);
            }
        },
        onBlur: function () {
            if (!this.pressedFlag) {
                this.focusedFlag = false;
                this.fakeElement.removeClass(this.options.focusClass);
                this.realElement.off('blur', this.onBlur);
            }
        },
        onPress: function (e) {
            if (!this.focusedFlag && e.pointerType === 'mouse') {
                this.realElement.focus();
            }
            this.pressedFlag = true;
            this.fakeElement.addClass(this.options.pressedClass);
            this.doc.on('jcf-pointerup', this.onRelease);
        },
        onRelease: function (e) {
            if (this.focusedFlag && e.pointerType === 'mouse') {
                this.realElement.focus();
            }
            this.pressedFlag = false;
            this.fakeElement.removeClass(this.options.pressedClass);
            this.doc.off('jcf-pointerup', this.onRelease);
        },
        getLabelFor: function () {
            var parentLabel = this.realElement.closest('label'),
                elementId = this.realElement.prop('id');

            if (!parentLabel.length && elementId) {
                parentLabel = $('label[for="' + elementId + '"]');
            }
            return parentLabel.length ? parentLabel : null;
        },
        refresh: function () {
            // redraw custom checkbox
            var isChecked = this.realElement.is(':checked'),
                isDisabled = this.realElement.is(':disabled');

            this.fakeElement.toggleClass(this.options.checkedClass, isChecked)
                .toggleClass(this.options.uncheckedClass, !isChecked)
                .toggleClass(this.options.disabledClass, isDisabled);

            if (this.labelElement) {
                this.labelElement.toggleClass(this.options.labelActiveClass, isChecked);
            }
        },
        destroy: function () {
            // restore structure
            if (this.options.wrapNative) {
                this.realElement.insertBefore(this.fakeElement).css({
                    position: '',
                    width: '',
                    height: '',
                    opacity: '',
                    margin: ''
                });
            } else {
                this.realElement.removeClass(this.options.hiddenClass);
            }

            // removing element will also remove its event handlers
            this.fakeElement.off('jcf-pointerdown', this.onPress);
            this.fakeElement.remove();

            // remove other event handlers
            this.doc.off('jcf-pointerup', this.onRelease);
            this.realElement.off({
                focus: this.onFocus,
                click: this.onRealClick
            });
        }
    });

}(jQuery, this));

/*!
 * JavaScript Custom Forms : Scrollbar Module
 *
 * Copyright 2014 PSD2HTML (http://psd2html.com)
 * Released under the MIT license (LICENSE.txt)
 *
 * Version: 1.0.3
 */
(function ($, window) {
    'use strict';

    jcf.addModule({
        name: 'Scrollable',
        selector: '.jcf-scrollable',
        plugins: {
            ScrollBar: ScrollBar
        },
        options: {
            mouseWheelStep: 150,
            handleResize: true,
            alwaysShowScrollbars: false,
            alwaysPreventMouseWheel: false,
            scrollAreaStructure: '<div class="jcf-scrollable-wrapper"></div>'
        },
        matchElement: function (element) {
            return element.is('.jcf-scrollable');
        },
        init: function (options) {
            this.initStructure();
            this.attachEvents();
            this.rebuildScrollbars();
        },
        initStructure: function () {
            // prepare structure
            this.doc = $(document);
            this.win = $(window);
            this.realElement = $(this.options.element);
            this.scrollWrapper = $(this.options.scrollAreaStructure).insertAfter(this.realElement);

            // set initial styles
            this.scrollWrapper.css('position', 'relative');
            this.realElement.css('overflow', 'hidden');
            this.vBarEdge = 0;
        },
        attachEvents: function () {
            // create scrollbars
            var self = this;
            this.vBar = new ScrollBar({
                holder: this.scrollWrapper,
                vertical: true,
                onScroll: function (scrollTop) {
                    self.realElement.scrollTop(scrollTop);
                }
            });
            this.hBar = new ScrollBar({
                holder: this.scrollWrapper,
                vertical: false,
                onScroll: function (scrollLeft) {
                    self.realElement.scrollLeft(scrollLeft);
                }
            });

            // add event handlers
            this.realElement.on('scroll', this.onScroll);
            if (this.options.handleResize) {
                this.win.on('resize orientationchange load', this.onResize);
            }

            // add pointer/wheel event handlers
            this.realElement.on('jcf-mousewheel', this.onMouseWheel);
            this.realElement.on('jcf-pointerdown', this.onTouchBody);
        },
        onScroll: function () {
            this.redrawScrollbars();
        },
        onResize: function () {
            // do not rebuild scrollbars if form field is in focus
            if (!$(document.activeElement).is(':input')) {
                this.rebuildScrollbars();
            }
        },
        onTouchBody: function (e) {
            if (e.pointerType === 'touch') {
                this.touchData = {
                    scrollTop: this.realElement.scrollTop(),
                    scrollLeft: this.realElement.scrollLeft(),
                    left: e.pageX,
                    top: e.pageY
                };
                this.doc.on({
                    'jcf-pointermove': this.onMoveBody,
                    'jcf-pointerup': this.onReleaseBody
                });
            }
        },
        onMoveBody: function (e) {
            var targetScrollTop,
                targetScrollLeft,
                verticalScrollAllowed = this.verticalScrollActive,
                horizontalScrollAllowed = this.horizontalScrollActive;

            if (e.pointerType === 'touch') {
                targetScrollTop = this.touchData.scrollTop - e.pageY + this.touchData.top;
                targetScrollLeft = this.touchData.scrollLeft - e.pageX + this.touchData.left;

                // check that scrolling is ended and release outer scrolling
                if (this.verticalScrollActive && (targetScrollTop < 0 || targetScrollTop > this.vBar.maxValue)) {
                    verticalScrollAllowed = false;
                }
                if (this.horizontalScrollActive && (targetScrollLeft < 0 || targetScrollLeft > this.hBar.maxValue)) {
                    horizontalScrollAllowed = false;
                }

                this.realElement.scrollTop(targetScrollTop);
                this.realElement.scrollLeft(targetScrollLeft);

                if (verticalScrollAllowed || horizontalScrollAllowed) {
                    e.preventDefault();
                } else {
                    this.onReleaseBody(e);
                }
            }
        },
        onReleaseBody: function (e) {
            if (e.pointerType === 'touch') {
                delete this.touchData;
                this.doc.off({
                    'jcf-pointermove': this.onMoveBody,
                    'jcf-pointerup': this.onReleaseBody
                });
            }
        },
        onMouseWheel: function (e) {
            var currentScrollTop = this.realElement.scrollTop(),
                currentScrollLeft = this.realElement.scrollLeft(),
                maxScrollTop = this.realElement.prop('scrollHeight') - this.embeddedDimensions.innerHeight,
                maxScrollLeft = this.realElement.prop('scrollWidth') - this.embeddedDimensions.innerWidth,
                extraLeft, extraTop, preventFlag;

            // check edge cases
            if (!this.options.alwaysPreventMouseWheel) {
                if (this.verticalScrollActive && e.deltaY) {
                    if (!(currentScrollTop <= 0 && e.deltaY < 0) && !(currentScrollTop >= maxScrollTop && e.deltaY > 0)) {
                        preventFlag = true;
                    }
                }
                if (this.horizontalScrollActive && e.deltaX) {
                    if (!(currentScrollLeft <= 0 && e.deltaX < 0) && !(currentScrollLeft >= maxScrollLeft && e.deltaX > 0)) {
                        preventFlag = true;
                    }
                }
                if (!this.verticalScrollActive && !this.horizontalScrollActive) {
                    return;
                }
            }

            // prevent default action and scroll item
            if (preventFlag || this.options.alwaysPreventMouseWheel) {
                e.preventDefault();
            } else {
                return;
            }

            extraLeft = e.deltaX / 100 * this.options.mouseWheelStep;
            extraTop = e.deltaY / 100 * this.options.mouseWheelStep;

            this.realElement.scrollTop(currentScrollTop + extraTop);
            this.realElement.scrollLeft(currentScrollLeft + extraLeft);
        },
        setScrollBarEdge: function (edgeSize) {
            this.vBarEdge = edgeSize || 0;
            this.redrawScrollbars();
        },
        saveElementDimensions: function () {
            this.savedDimensions = {
                top: this.realElement.width(),
                left: this.realElement.height()
            };
            return this;
        },
        restoreElementDimensions: function () {
            if (this.savedDimensions) {
                this.realElement.css({
                    width: this.savedDimensions.width,
                    height: this.savedDimensions.height
                });
            }
            return this;
        },
        saveScrollOffsets: function () {
            this.savedOffsets = {
                top: this.realElement.scrollTop(),
                left: this.realElement.scrollLeft()
            };
            return this;
        },
        restoreScrollOffsets: function () {
            if (this.savedOffsets) {
                this.realElement.scrollTop(this.savedOffsets.top);
                this.realElement.scrollLeft(this.savedOffsets.left);
            }
            return this;
        },
        getContainerDimensions: function () {
            // save current styles
            var desiredDimensions,
                currentStyles,
                currentHeight,
                currentWidth;

            if (this.isModifiedStyles) {
                desiredDimensions = {
                    width: this.realElement.innerWidth() + this.vBar.getThickness(),
                    height: this.realElement.innerHeight() + this.hBar.getThickness()
                };
            } else {
                // unwrap real element and measure it according to CSS
                this.saveElementDimensions().saveScrollOffsets();
                this.realElement.insertAfter(this.scrollWrapper);
                this.scrollWrapper.detach();

                // measure element
                currentStyles = this.realElement.prop('style');
                currentWidth = parseFloat(currentStyles.width);
                currentHeight = parseFloat(currentStyles.height);

                // reset styles if needed
                if (this.embeddedDimensions && currentWidth && currentHeight) {
                    this.isModifiedStyles |= (currentWidth !== this.embeddedDimensions.width || currentHeight !== this.embeddedDimensions.height);
                    this.realElement.css({
                        overflow: '',
                        width: '',
                        height: ''
                    });
                }

                // calculate desired dimensions for real element
                desiredDimensions = {
                    width: this.realElement.outerWidth(),
                    height: this.realElement.outerHeight()
                };

                // restore structure and original scroll offsets
                this.scrollWrapper.insertAfter(this.realElement);
                this.realElement.css('overflow', 'hidden').prependTo(this.scrollWrapper);
                this.restoreElementDimensions().restoreScrollOffsets();
            }

            return desiredDimensions;
        },
        getEmbeddedDimensions: function (dimensions) {
            // handle scrollbars cropping
            var fakeBarWidth = this.vBar.getThickness(),
                fakeBarHeight = this.hBar.getThickness(),
                paddingWidth = this.realElement.outerWidth() - this.realElement.width(),
                paddingHeight = this.realElement.outerHeight() - this.realElement.height(),
                resultDimensions;

            if (this.options.alwaysShowScrollbars) {
                // simply return dimensions without custom scrollbars
                this.verticalScrollActive = true;
                this.horizontalScrollActive = true;
                resultDimensions = {
                    innerWidth: dimensions.width - fakeBarWidth,
                    innerHeight: dimensions.height - fakeBarHeight
                };
            } else {
                // detect when to display each scrollbar
                this.saveElementDimensions();
                this.verticalScrollActive = false;
                this.horizontalScrollActive = false;

                // fill container with full size
                this.realElement.css({
                    width: dimensions.width - paddingWidth,
                    height: dimensions.height - paddingHeight
                });

                this.horizontalScrollActive = this.realElement.prop('scrollWidth') > this.containerDimensions.width;
                this.verticalScrollActive = this.realElement.prop('scrollHeight') > this.containerDimensions.height;

                this.restoreElementDimensions();
                resultDimensions = {
                    innerWidth: dimensions.width - (this.verticalScrollActive ? fakeBarWidth : 0),
                    innerHeight: dimensions.height - (this.horizontalScrollActive ? fakeBarHeight : 0)
                };
            }
            $.extend(resultDimensions, {
                width: resultDimensions.innerWidth - paddingWidth,
                height: resultDimensions.innerHeight - paddingHeight
            });
            return resultDimensions;
        },
        rebuildScrollbars: function () {
            // resize wrapper according to real element styles
            this.containerDimensions = this.getContainerDimensions();
            this.embeddedDimensions = this.getEmbeddedDimensions(this.containerDimensions);

            // resize wrapper to desired dimensions
            this.scrollWrapper.css({
                width: this.containerDimensions.width,
                height: this.containerDimensions.height
            });

            // resize element inside wrapper excluding scrollbar size
            this.realElement.css({
                overflow: 'hidden',
                width: this.embeddedDimensions.width,
                height: this.embeddedDimensions.height
            });

            // redraw scrollbar offset
            this.redrawScrollbars();
        },
        redrawScrollbars: function () {
            var viewSize, maxScrollValue;

            // redraw vertical scrollbar
            if (this.verticalScrollActive) {
                viewSize = this.vBarEdge ? this.containerDimensions.height - this.vBarEdge : this.embeddedDimensions.innerHeight;
                maxScrollValue = this.realElement.prop('scrollHeight') - this.vBarEdge;

                this.vBar.show().setMaxValue(maxScrollValue - viewSize).setRatio(viewSize / maxScrollValue).setSize(viewSize);
                this.vBar.setValue(this.realElement.scrollTop());
            } else {
                this.vBar.hide();
            }

            // redraw horizontal scrollbar
            if (this.horizontalScrollActive) {
                viewSize = this.embeddedDimensions.innerWidth;
                maxScrollValue = this.realElement.prop('scrollWidth');

                if (maxScrollValue === viewSize) {
                    this.horizontalScrollActive = false;
                }
                this.hBar.show().setMaxValue(maxScrollValue - viewSize).setRatio(viewSize / maxScrollValue).setSize(viewSize);
                this.hBar.setValue(this.realElement.scrollLeft());
            } else {
                this.hBar.hide();
            }

            // set "touch-action" style rule
            var touchAction = '';
            if (this.verticalScrollActive && this.horizontalScrollActive) {
                touchAction = 'none';
            } else if (this.verticalScrollActive) {
                touchAction = 'pan-x';
            } else if (this.horizontalScrollActive) {
                touchAction = 'pan-y';
            }
            this.realElement.css('touchAction', touchAction);
        },
        refresh: function () {
            this.rebuildScrollbars();
        },
        destroy: function () {
            // remove event listeners
            this.win.off('resize orientationchange load', this.onResize);
            this.realElement.off({
                'jcf-mousewheel': this.onMouseWheel,
                'jcf-pointerdown': this.onTouchBody
            });
            this.doc.off({
                'jcf-pointermove': this.onMoveBody,
                'jcf-pointerup': this.onReleaseBody
            });

            // restore structure
            this.saveScrollOffsets();
            this.vBar.destroy();
            this.hBar.destroy();
            this.realElement.insertAfter(this.scrollWrapper).css({
                touchAction: '',
                overflow: '',
                width: '',
                height: ''
            });
            this.scrollWrapper.remove();
            this.restoreScrollOffsets();
        }
    });

    // custom scrollbar
    function ScrollBar(options) {
        this.options = $.extend({
            holder: null,
            vertical: true,
            inactiveClass: 'jcf-inactive',
            verticalClass: 'jcf-scrollbar-vertical',
            horizontalClass: 'jcf-scrollbar-horizontal',
            scrollbarStructure: '<div class="jcf-scrollbar"><div class="jcf-scrollbar-dec"></div><div class="jcf-scrollbar-slider"><div class="jcf-scrollbar-handle"></div></div><div class="jcf-scrollbar-inc"></div></div>',
            btnDecSelector: '.jcf-scrollbar-dec',
            btnIncSelector: '.jcf-scrollbar-inc',
            sliderSelector: '.jcf-scrollbar-slider',
            handleSelector: '.jcf-scrollbar-handle',
            scrollInterval: 10,
            scrollStep: 5
        }, options);
        this.init();
    }

    $.extend(ScrollBar.prototype, {
        init: function () {
            this.initStructure();
            this.attachEvents();
        },
        initStructure: function () {
            // define proporties
            this.doc = $(document);
            this.isVertical = !!this.options.vertical;
            this.sizeProperty = this.isVertical ? 'height' : 'width';
            this.fullSizeProperty = this.isVertical ? 'outerHeight' : 'outerWidth';
            this.invertedSizeProperty = this.isVertical ? 'width' : 'height';
            this.thicknessMeasureMethod = 'outer' + this.invertedSizeProperty.charAt(0).toUpperCase() + this.invertedSizeProperty.substr(1);
            this.offsetProperty = this.isVertical ? 'top' : 'left';
            this.offsetEventProperty = this.isVertical ? 'pageY' : 'pageX';

            // initialize variables
            this.value = this.options.value || 0;
            this.maxValue = this.options.maxValue || 0;
            this.currentSliderSize = 0;
            this.handleSize = 0;

            // find elements
            this.holder = $(this.options.holder);
            this.scrollbar = $(this.options.scrollbarStructure).appendTo(this.holder);
            this.btnDec = this.scrollbar.find(this.options.btnDecSelector);
            this.btnInc = this.scrollbar.find(this.options.btnIncSelector);
            this.slider = this.scrollbar.find(this.options.sliderSelector);
            this.handle = this.slider.find(this.options.handleSelector);

            // set initial styles
            this.scrollbar.addClass(this.isVertical ? this.options.verticalClass : this.options.horizontalClass).css({
                touchAction: this.isVertical ? 'pan-x' : 'pan-y',
                position: 'absolute'
            });
            this.slider.css({
                position: 'relative'
            });
            this.handle.css({
                touchAction: 'none',
                position: 'absolute'
            });
        },
        attachEvents: function () {
            var self = this;
            this.bindHandlers();
            this.handle.on('jcf-pointerdown', this.onHandlePress);
            this.btnDec.add(this.btnInc).on('jcf-pointerdown', this.onButtonPress);
        },
        onHandlePress: function (e) {
            if (e.pointerType === 'mouse' && e.button > 1) {

            } else {
                e.preventDefault();
                this.sliderOffset = this.slider.offset()[this.offsetProperty];
                this.innerHandleOffset = e[this.offsetEventProperty] - this.handle.offset()[this.offsetProperty];

                this.doc.on('jcf-pointermove', this.onHandleDrag);
                this.doc.on('jcf-pointerup', this.onHandleRelease);
            }
        },
        onHandleDrag: function (e) {
            e.preventDefault();
            this.calcOffset = e[this.offsetEventProperty] - this.sliderOffset - this.innerHandleOffset;
            this.setValue(this.calcOffset / (this.currentSliderSize - this.handleSize) * this.maxValue);
            this.triggerScrollEvent(this.value);
        },
        onHandleRelease: function () {
            this.doc.off('jcf-pointermove', this.onHandleDrag);
            this.doc.off('jcf-pointerup', this.onHandleRelease);
        },
        onButtonPress: function (e) {
            var direction;
            if (e.pointerType === 'mouse' && e.button > 1) {

            } else {
                e.preventDefault();
                direction = this.btnDec.is(e.currentTarget) ? -1 : 1;
                this.startButtonScrolling(direction);
                this.doc.on('jcf-pointerup', this.onButtonRelease);
            }
        },
        onButtonRelease: function () {
            this.stopButtonScrolling();
            this.doc.off('jcf-pointerup', this.onButtonRelease);
        },
        startButtonScrolling: function (direction) {
            var self = this;
            this.stopButtonScrolling();
            this.scrollTimer = setInterval(function () {
                if (direction > 0) {
                    self.value += self.options.scrollStep;
                } else {
                    self.value -= self.options.scrollStep;
                }
                self.setValue(self.value);
                self.triggerScrollEvent(self.value);
            }, this.options.scrollInterval);
        },
        stopButtonScrolling: function () {
            clearInterval(this.scrollTimer);
        },
        triggerScrollEvent: function (scrollValue) {
            if (this.options.onScroll) {
                this.options.onScroll(scrollValue);
            }
        },
        getThickness: function () {
            return this.scrollbar[this.thicknessMeasureMethod]();
        },
        setSize: function (size) {
            // resize scrollbar
            var btnDecSize = this.btnDec[this.fullSizeProperty](),
                btnIncSize = this.btnInc[this.fullSizeProperty]();

            // resize slider
            this.currentSize = size;
            this.currentSliderSize = size - btnDecSize - btnIncSize;
            this.scrollbar.css(this.sizeProperty, size);
            this.slider.css(this.sizeProperty, this.currentSliderSize);
            this.currentSliderSize = this.slider[this.sizeProperty]();

            // resize handle
            this.handleSize = Math.round(this.currentSliderSize * this.ratio);
            this.handle.css(this.sizeProperty, this.handleSize);
            this.handleSize = this.handle[this.fullSizeProperty]();

            return this;
        },
        setRatio: function (ratio) {
            this.ratio = ratio;
            return this;
        },
        setMaxValue: function (maxValue) {
            this.maxValue = maxValue;
            this.setValue(Math.min(this.value, this.maxValue));
            return this;
        },
        setValue: function (value) {
            this.value = value;
            if (this.value < 0) {
                this.value = 0;
            } else if (this.value > this.maxValue) {
                this.value = this.maxValue;
            }
            this.refresh();
        },
        setPosition: function (styles) {
            this.scrollbar.css(styles);
            return this;
        },
        hide: function () {
            this.scrollbar.detach();
            return this;
        },
        show: function () {
            this.scrollbar.appendTo(this.holder);
            return this;
        },
        refresh: function () {
            // recalculate handle position
            if (this.value === 0 || this.maxValue === 0) {
                this.calcOffset = 0;
            } else {
                this.calcOffset = (this.value / this.maxValue) * (this.currentSliderSize - this.handleSize);
            }
            this.handle.css(this.offsetProperty, this.calcOffset);

            // toggle inactive classes
            this.btnDec.toggleClass(this.options.inactiveClass, this.value === 0);
            this.btnInc.toggleClass(this.options.inactiveClass, this.value === this.maxValue);
            this.scrollbar.toggleClass(this.options.inactiveClass, this.maxValue === 0);

            if (this.scrollbar.hasClass(this.options.verticalClass)) {
                if (this.handle.outerHeight() > this.slider.outerHeight() - 2) {
                    this.scrollbar.addClass('hide-scroll');
                } else {
                    this.scrollbar.removeClass('hide-scroll');
                }
            }
        },
        destroy: function () {
            //remove event handlers and scrollbar block itself
            this.btnDec.add(this.btnInc).off('jcf-pointerdown', this.onButtonPress);
            this.handle.off('jcf-pointerdown', this.onHandlePress);
            this.doc.off('jcf-pointermove', this.onHandleDrag);
            this.doc.off('jcf-pointerup', this.onHandleRelease);
            this.doc.off('jcf-pointerup', this.onButtonRelease);
            clearInterval(this.scrollTimer);
            this.scrollbar.remove();
        }
    });

}(jQuery, this));

/*!
 * JavaScript Custom Forms : File Module
 *
 * Copyright 2014 PSD2HTML (http://psd2html.com)
 * Released under the MIT license (LICENSE.txt)
 *
 * Version: 1.0.3
 */
(function ($, window) {
    'use strict';

    jcf.addModule({
        name: 'File',
        selector: 'input[type="file"]',
        options: {
            fakeStructure: '<span class="jcf-file"><span class="jcf-fake-input"></span><span class="jcf-upload-button"><span class="jcf-button-content"></span></span></span>',
            buttonText: 'Choose file',
            placeholderText: 'No file chosen',
            realElementClass: 'jcf-real-element',
            extensionPrefixClass: 'jcf-extension-',
            selectedFileBlock: '.jcf-fake-input',
            buttonTextBlock: '.jcf-button-content'
        },
        matchElement: function (element) {
            return element.is('input[type="file"]');
        },
        init: function (options) {
            this.initStructure();
            this.attachEvents();
            this.refresh();
        },
        initStructure: function () {
            this.doc = $(document);
            this.realElement = $(this.options.element).addClass(this.options.realElementClass);
            this.fakeElement = $(this.options.fakeStructure).insertBefore(this.realElement);
            this.fileNameBlock = this.fakeElement.find(this.options.selectedFileBlock);
            this.buttonTextBlock = this.fakeElement.find(this.options.buttonTextBlock).text(this.options.buttonText);

            this.realElement.appendTo(this.fakeElement).css({
                position: 'absolute',
                opacity: 0
            });
        },
        attachEvents: function () {
            this.realElement.on({
                'jcf-pointerdown': this.onPress,
                'change': this.onChange,
                'focus': this.onFocus
            });
        },
        onChange: function () {
            this.refresh();
        },
        onFocus: function () {
            this.fakeElement.addClass(this.options.focusClass);
            this.realElement.on('blur', this.onBlur);
        },
        onBlur: function () {
            this.fakeElement.removeClass(this.options.focusClass);
            this.realElement.off('blur', this.onBlur);
        },
        onPress: function () {
            this.fakeElement.addClass(this.options.pressedClass);
            this.doc.on('jcf-pointerup', this.onRelease);
        },
        onRelease: function () {
            this.fakeElement.removeClass(this.options.pressedClass);
            this.doc.off('jcf-pointerup', this.onRelease);
        },
        getFileName: function () {
            return this.realElement.val().replace(/^[\s\S]*(?:\\|\/)([\s\S^\\\/]*)$/g, '$1');
        },
        getFileExtension: function () {
            var fileName = this.realElement.val();
            return fileName.lastIndexOf('.') < 0 ? '' : fileName.substring(fileName.lastIndexOf('.') + 1).toLowerCase();
        },
        updateExtensionClass: function () {
            var currentExtension = this.getFileExtension(),
                currentClassList = this.fakeElement.prop('className'),
                cleanedClassList = currentClassList.replace(new RegExp('(\\s|^)' + this.options.extensionPrefixClass + '[^ ]+', 'gi'), '');

            this.fakeElement.prop('className', cleanedClassList);
            if (currentExtension) {
                this.fakeElement.addClass(this.options.extensionPrefixClass + currentExtension);
            }
        },
        refresh: function () {
            var selectedFileName = this.getFileName() || this.options.placeholderText;
            this.fakeElement.toggleClass(this.options.disabledClass, this.realElement.is(':disabled'));
            this.fileNameBlock.text(selectedFileName);
            this.updateExtensionClass();
        },
        destroy: function () {
            // reset styles and restore element position
            this.realElement.insertBefore(this.fakeElement).removeClass(this.options.realElementClass).css({
                position: '',
                opacity: ''
            });
            this.fakeElement.remove();

            // remove event handlers
            this.realElement.off({
                'jcf-pointerdown': this.onPress,
                'change': this.onChange,
                'focus': this.onFocus,
                'blur': this.onBlur
            });
            this.doc.off('jcf-pointerup', this.onRelease);
        }
    });

}(jQuery, this));

/*!
 * JavaScript Custom Forms : Range Module
 *
 * Copyright 2014 PSD2HTML (http://psd2html.com)
 * Released under the MIT license (LICENSE.txt)
 *
 * Version: 1.0.3
 */
(function ($, window) {
    'use strict';

    jcf.addModule({
        name: 'Range',
        selector: 'input[type="range"]',
        options: {
            realElementClass: 'jcf-real-element',
            fakeStructure: '<span class="jcf-range"><span class="jcf-range-wrapper"><span class="jcf-range-track"><span class="jcf-range-handle"></span></span></span></span>',
            dataListMark: '<span class="jcf-range-mark"></span>',
            dragValueDisplay: '<span class="jcf-drag-value"></span>',
            handleSelector: '.jcf-range-handle',
            trackSelector: '.jcf-range-track',
            verticalClass: 'jcf-vertical',
            orientation: 'horizontal',
            dragHandleCenter: true,
            snapToMarks: true,
            snapRadius: 5
        },
        matchElement: function (element) {
            return element.is(this.selector);
        },
        init: function (options) {
            this.initStructure();
            this.attachEvents();
            this.refresh();
        },
        initStructure: function () {
            this.page = $('html');
            this.realElement = $(this.options.element).addClass(this.options.hiddenClass);
            this.fakeElement = $(this.options.fakeStructure).insertBefore(this.realElement).prepend(this.realElement);
            this.track = this.fakeElement.find(this.options.trackSelector);
            this.handle = this.fakeElement.find(this.options.handleSelector);

            // handle orientation
            this.isVertical = (this.options.orientation === 'vertical');
            this.directionProperty = this.isVertical ? 'top' : 'left';
            this.offsetProperty = this.isVertical ? 'bottom' : 'left';
            this.eventProperty = this.isVertical ? 'pageY' : 'pageX';
            this.sizeMethod = this.isVertical ? 'innerHeight' : 'innerWidth';
            if (this.isVertical) {
                this.fakeElement.addClass(this.options.verticalClass);
            }

            // set initial values
            this.minValue = parseFloat(this.realElement.attr('min'));
            this.maxValue = parseFloat(this.realElement.attr('max'));
            this.stepValue = parseFloat(this.realElement.attr('step')) || 1;

            // check attribute values
            this.minValue = isNaN(this.minValue) ? 0 : this.minValue;
            this.maxValue = isNaN(this.maxValue) ? 100 : this.maxValue;

            // handle range
            if (this.stepValue !== 1) {
                this.maxValue -= (this.maxValue - this.minValue) % this.stepValue;
            }
            this.stepsCount = (this.maxValue - this.minValue) / this.stepValue + 1;
            this.createDataList();
        },
        attachEvents: function () {
            this.realElement.on({
                'focus': this.onFocus
            });
            this.handle.on('jcf-pointerdown', this.onPress);
        },
        createDataList: function () {
            var self = this,
                dataValues = [],
                dataListId = this.realElement.attr('list');

            if (dataListId) {
                $('#' + dataListId).find('option').each(function () {
                    var itemValue = parseFloat(this.value || this.innerHTML),
                        mark, markOffset;

                    if (!isNaN(itemValue)) {
                        markOffset = self.valueToOffset(itemValue);
                        dataValues.push({
                            value: itemValue,
                            offset: markOffset
                        });
                        mark = $(self.options.dataListMark).text(itemValue).attr({
                            'data-mark-value': itemValue
                        }).css(self.offsetProperty, markOffset + '%').appendTo(self.track);
                    }
                });
                if (dataValues.length) {
                    self.dataValues = dataValues;
                }
            }
        },
        onPress: function (e) {
            var trackSize, trackOffset, innerOffset;

            e.preventDefault();
            if (!this.realElement.is(':disabled')) {
                trackSize = this.track[this.sizeMethod]();
                trackOffset = this.track.offset()[this.directionProperty];
                innerOffset = this.options.dragHandleCenter ? this.handle[this.sizeMethod]() / 2 : e[this.eventProperty] - this.handle.offset()[this.directionProperty];

                this.dragData = {
                    trackSize: trackSize,
                    innerOffset: innerOffset,
                    trackOffset: trackOffset,
                    min: trackOffset,
                    max: trackOffset + trackSize
                };
                this.page.on({
                    'jcf-pointermove': this.onMove,
                    'jcf-pointerup': this.onRelease
                });

                if (e.pointerType === 'mouse') {
                    this.realElement.focus();
                }
            }
        },
        onMove: function (e) {
            var self = this,
                newOffset, dragPercent, stepIndex, valuePercent;

            // calculate offset
            if (this.isVertical) {
                newOffset = this.dragData.max + (this.dragData.min - e[this.eventProperty]) - this.dragData.innerOffset;
            } else {
                newOffset = e[this.eventProperty] - this.dragData.innerOffset;
            }

            // fit in range
            if (newOffset < this.dragData.min) {
                newOffset = this.dragData.min;
            } else if (newOffset > this.dragData.max) {
                newOffset = this.dragData.max;
            }

            e.preventDefault();
            if (this.options.snapToMarks && this.dataValues) {
                // snap handle to marks
                var dragOffset = newOffset - this.dragData.trackOffset;
                dragPercent = (newOffset - this.dragData.trackOffset) / this.dragData.trackSize * 100;

                $.each(this.dataValues, function (index, item) {
                    var markOffset = item.offset / 100 * self.dragData.trackSize,
                        markMin = markOffset - self.options.snapRadius,
                        markMax = markOffset + self.options.snapRadius;

                    if (dragOffset >= markMin && dragOffset <= markMax) {
                        dragPercent = item.offset;
                        return false;
                    }
                });
            } else {
                // snap handle to steps
                dragPercent = (newOffset - this.dragData.trackOffset) / this.dragData.trackSize * 100;
            }
            stepIndex = Math.round(dragPercent * this.stepsCount / 100);
            valuePercent = stepIndex * (100 / this.stepsCount);

            if (this.dragData.stepIndex !== stepIndex) {
                this.dragData.stepIndex = stepIndex;
                this.dragData.offset = valuePercent;
                this.handle.css(this.offsetProperty, this.dragData.offset + '%');
            }
        },
        onRelease: function () {
            var newValue;
            if (typeof this.dragData.offset === 'number') {
                newValue = this.stepIndexToValue(this.dragData.stepIndex);
                this.realElement.val(newValue).trigger('change');
            }

            this.page.off({
                'jcf-pointermove': this.onMove,
                'jcf-pointerup': this.onRelease
            });
            delete this.dragData;
        },
        onFocus: function () {
            this.fakeElement.addClass(this.options.focusClass);
            this.realElement.on({
                'blur': this.onBlur,
                'keydown': this.onKeyPress
            });
        },
        onBlur: function () {
            this.fakeElement.removeClass(this.options.focusClass);
            this.realElement.off({
                'blur': this.onBlur,
                'keydown': this.onKeyPress
            });
        },
        onKeyPress: function (e) {
            var incValue = (e.which === 38 || e.which === 39),
                decValue = (e.which === 37 || e.which === 40);

            if (decValue || incValue) {
                e.preventDefault();
                this.step(incValue ? this.stepValue : -this.stepValue);
            }
        },
        step: function (changeValue) {
            var originalValue = parseFloat(this.realElement.val()),
                newValue = originalValue;

            if (isNaN(originalValue)) {
                newValue = 0;
            }

            newValue += changeValue;

            if (newValue > this.maxValue) {
                newValue = this.maxValue;
            } else if (newValue < this.minValue) {
                newValue = this.minValue;
            }

            if (newValue !== originalValue) {
                this.realElement.val(newValue).trigger('change');
                this.setSliderValue(newValue);
            }
        },
        stepIndexToValue: function (stepIndex) {
            return this.minValue + this.stepValue * stepIndex;
        },
        valueToOffset: function (value) {
            var range = this.maxValue - this.minValue,
                percent = (value - this.minValue) / range;

            return percent * 100;
        },
        setSliderValue: function (value) {
            // set handle position accordion according to value
            this.handle.css(this.offsetProperty, this.valueToOffset(value) + '%');
        },
        refresh: function () {
            // handle disabled state
            var isDisabled = this.realElement.is(':disabled');
            this.fakeElement.toggleClass(this.options.disabledClass, isDisabled);

            // refresh handle position according to current value
            var realValue = parseFloat(this.realElement.val()) || 0;
            this.setSliderValue(realValue);
        },
        destroy: function () {
            this.realElement.removeClass(this.options.hiddenClass).insertBefore(this.fakeElement);
            this.fakeElement.remove();

            this.realElement.off({
                'keydown': this.onKeyPress,
                'focus': this.onFocus,
                'blur': this.onBlur
            });
        }
    });

}(jQuery, this));

/*!
 * JavaScript Custom Forms : Number Module
 *
 * Copyright 2014 PSD2HTML (http://psd2html.com)
 * Released under the MIT license (LICENSE.txt)
 *
 * Version: 1.0.3
 */
(function ($, window) {
    'use strict';

    jcf.addModule({
        name: 'Number',
        selector: 'input[type="number"]',
        options: {
            realElementClass: 'jcf-real-element',
            fakeStructure: '<span class="jcf-number"><span class="jcf-btn-inc"></span><span class="jcf-btn-dec"></span></span>',
            btnIncSelector: '.jcf-btn-inc',
            btnDecSelector: '.jcf-btn-dec',
            pressInterval: 150
        },
        matchElement: function (element) {
            return element.is(this.selector);
        },
        init: function (options) {
            this.initStructure();
            this.attachEvents();
            this.refresh();
        },
        initStructure: function () {
            this.page = $('html');
            this.realElement = $(this.options.element).addClass(this.options.realElementClass);
            this.fakeElement = $(this.options.fakeStructure).insertBefore(this.realElement).prepend(this.realElement);
            this.btnDec = this.fakeElement.find(this.options.btnDecSelector);
            this.btnInc = this.fakeElement.find(this.options.btnIncSelector);

            this.ending = this.realElement.data('ending');

            if (this.ending) {
                jQuery('<span class="ending"/>').text(this.ending).insertAfter(this.realElement).parent().addClass('jcf-holder');
            }

            // set initial values
            this.initialValue = parseFloat(this.realElement.val()) || 0;
            this.minValue = parseFloat(this.realElement.attr('min'));
            this.maxValue = parseFloat(this.realElement.attr('max'));
            this.stepValue = parseFloat(this.realElement.attr('step')) || 1;

            // check attribute values
            this.minValue = isNaN(this.minValue) ? -Infinity : this.minValue;
            this.maxValue = isNaN(this.maxValue) ? Infinity : this.maxValue;

            // handle range
            if (isFinite(this.maxValue)) {
                this.maxValue -= (this.maxValue - this.minValue) % this.stepValue;
            }
        },
        attachEvents: function () {
            this.realElement.on({
                'focus': this.onFocus
            });
            this.btnDec.add(this.btnInc).on('jcf-pointerdown', this.onBtnPress);
        },
        onBtnPress: function (e) {
            var self = this,
                increment;

            if (!this.realElement.is(':disabled')) {
                increment = this.btnInc.is(e.currentTarget);

                self.step(increment);
                clearInterval(this.stepTimer);
                this.stepTimer = setInterval(function () {
                    self.step(increment);
                }, this.options.pressInterval);

                this.page.on('jcf-pointerup', this.onBtnRelease);
            }
        },
        onBtnRelease: function () {
            clearInterval(this.stepTimer);
            this.page.off('jcf-pointerup', this.onBtnRelease);
        },
        onFocus: function () {
            this.fakeElement.addClass(this.options.focusClass);
            this.realElement.on({
                'blur': this.onBlur,
                'keydown': this.onKeyPress
            });
        },
        onBlur: function () {
            this.fakeElement.removeClass(this.options.focusClass);
            this.realElement.off({
                'blur': this.onBlur,
                'keydown': this.onKeyPress
            });
        },
        onKeyPress: function (e) {
            if (e.which === 38 || e.which === 40) {
                e.preventDefault();
                this.step(e.which === 38);
            }
        },
        step: function (increment) {
            var originalValue = parseFloat(this.realElement.val()),
                newValue = originalValue || 0,
                addValue = this.stepValue * (increment ? 1 : -1),
                edgeNumber = isFinite(this.minValue) ? this.minValue : this.initialValue - Math.abs(newValue * this.stepValue),
                diff = Math.abs(edgeNumber - newValue) % this.stepValue;

            // handle step diff
            if (diff) {
                if (increment) {
                    newValue += addValue - diff;
                } else {
                    newValue -= diff;
                }
            } else {
                newValue += addValue;
            }

            // handle min/max limits
            if (newValue < this.minValue) {
                newValue = this.minValue;
            } else if (newValue > this.maxValue) {
                newValue = this.maxValue;
            }

            // update value in real input if its changed
            if (newValue !== originalValue) {
                this.realElement.val(newValue).trigger('change');
                this.refresh();
            }
        },
        refresh: function () {
            // handle disabled state
            var isDisabled = this.realElement.is(':disabled');
            this.fakeElement.toggleClass(this.options.disabledClass, isDisabled);

            // refresh button classes
            this.btnDec.toggleClass(this.options.disabledClass, this.realElement.val() == this.minValue);
            this.btnInc.toggleClass(this.options.disabledClass, this.realElement.val() == this.maxValue);
        },
        destroy: function () {
            // restore original structure
            this.realElement.removeClass(this.options.realElementClass).insertBefore(this.fakeElement);
            this.fakeElement.remove();
            clearInterval(this.stepTimer);

            // remove event handlers
            this.page.off('jcf-pointerup', this.onBtnRelease);
            this.realElement.off({
                'keydown': this.onKeyPress,
                'focus': this.onFocus,
                'blur': this.onBlur
            });
        }
    });

}(jQuery, this));

/*!
 * JavaScript Custom Forms : Textarea Module
 *
 * Copyright 2014 PSD2HTML (http://psd2html.com)
 * Released under the MIT license (LICENSE.txt)
 *
 * Version: 1.0.3
 */
(function ($, window) {
    'use strict';

    jcf.addModule({
        name: 'Textarea',
        selector: 'textarea',
        options: {
            resize: true,
            resizerStructure: '<span class="jcf-resize"></span>',
            fakeStructure: '<span class="jcf-textarea"></span>'
        },
        matchElement: function (element) {
            return element.is('textarea');
        },
        init: function (options) {
            this.initStructure();
            this.attachEvents();
            this.refresh();
        },
        initStructure: function () {
            // prepare structure
            this.doc = $(document);
            this.realElement = $(this.options.element);
            this.fakeElement = $(this.options.fakeStructure).insertAfter(this.realElement);
            this.resizer = $(this.options.resizerStructure).appendTo(this.fakeElement);

            // add custom scrollbar
            if (jcf.modules.Scrollable) {
                this.realElement.prependTo(this.fakeElement).addClass().css({
                    overflow: 'hidden',
                    resize: 'none'
                });

                this.scrollable = new jcf.modules.Scrollable({
                    element: this.realElement,
                    alwaysShowScrollbars: true
                });
                this.scrollable.setScrollBarEdge(this.resizer.outerHeight());
            }
        },
        attachEvents: function () {
            // add event handlers
            this.realElement.on({
                focus: this.onFocus,
                keyup: this.onChange,
                change: this.onChange
            });

            this.resizer.on('jcf-pointerdown', this.onResizePress);
        },
        onResizePress: function (e) {
            var resizerOffset = this.resizer.offset(),
                areaOffset = this.fakeElement.offset();

            this.dragData = {
                areaOffset: areaOffset,
                innerOffsetLeft: e.pageX - resizerOffset.left,
                innerOffsetTop: e.pageY - resizerOffset.top
            };
            this.doc.on({
                'jcf-pointermove': this.onResizeMove,
                'jcf-pointerup': this.onResizeRelease
            });
        },
        onResizeMove: function (e) {
            var newWidth = e.pageX + this.dragData.innerOffsetLeft - this.dragData.areaOffset.left,
                newHeight = e.pageY + this.dragData.innerOffsetTop - this.dragData.areaOffset.top,
                widthDiff = this.fakeElement.innerWidth() - this.realElement.innerWidth();

            // prevent text selection or page scroll on touch devices
            e.preventDefault();

            // resize textarea and refresh scrollbars
            this.realElement.innerWidth(newWidth - widthDiff).innerHeight(newHeight);
            this.refreshCustomScrollbars();
        },
        onResizeRelease: function (e) {
            this.doc.off({
                'jcf-pointermove': this.onResizeMove,
                'jcf-pointerup': this.onResizeRelease
            });
        },
        onFocus: function () {
            this.isFocused = true;
            this.fakeElement.addClass(this.options.focusClass);
            this.realElement.on('blur', this.onBlur);
        },
        onBlur: function () {
            this.isFocused = false;
            this.fakeElement.removeClass(this.options.focusClass);
            this.realElement.off('blur', this.onBlur);
        },
        onChange: function () {
            this.refreshCustomScrollbars();
        },
        refreshCustomScrollbars: function () {
            // refresh custom scrollbars
            if (this.isFocused) {
                this.scrollable.redrawScrollbars();
            } else {
                this.scrollable.refresh();
            }
        },
        refresh: function () {
            // refresh custom scroll position
            var isDisabled = this.realElement.is(':disabled');
            this.fakeElement.toggleClass(this.options.disabledClass, isDisabled);
        },
        destroy: function () {
            // destroy custom scrollbar
            this.scrollable.destroy();

            // restore styles and remove event listeners
            this.realElement.css({
                overflow: '',
                resize: ''
            }).insertBefore(this.fakeElement).off({
                focus: this.onFocus,
                blur: this.onBlur
            });

            // remove scrollbar and fake wrapper
            this.fakeElement.remove();
        }
    });

}(jQuery, this));

/*
 * Responsive Layout helper
 */
ResponsiveHelper = (function ($) {
    // init variables
    var handlers = [],
        prevWinWidth,
        win = $(window),
        nativeMatchMedia = false;

    // detect match media support
    if (window.matchMedia) {
        if (window.Window && window.matchMedia === Window.prototype.matchMedia) {
            nativeMatchMedia = true;
        } else if (window.matchMedia.toString().indexOf('native') > -1) {
            nativeMatchMedia = true;
        }
    }

    // prepare resize handler
    function resizeHandler() {
        var winWidth = win.width();
        if (winWidth !== prevWinWidth) {
            prevWinWidth = winWidth;

            // loop through range groups
            $.each(handlers, function (index, rangeObject) {
                // disable current active area if needed
                $.each(rangeObject.data, function (property, item) {
                    if (item.currentActive && !matchRange(item.range[0], item.range[1])) {
                        item.currentActive = false;
                        if (typeof item.disableCallback === 'function') {
                            item.disableCallback();
                        }
                    }
                });

                // enable areas that match current width
                $.each(rangeObject.data, function (property, item) {
                    if (!item.currentActive && matchRange(item.range[0], item.range[1])) {
                        // make callback
                        item.currentActive = true;
                        if (typeof item.enableCallback === 'function') {
                            item.enableCallback();
                        }
                    }
                });
            });
        }
    }

    win.bind('load resize orientationchange', resizeHandler);

    // test range
    function matchRange(r1, r2) {
        var mediaQueryString = '';
        if (r1 > 0) {
            mediaQueryString += '(min-width: ' + r1 + 'px)';
        }
        if (r2 < Infinity) {
            mediaQueryString += (mediaQueryString ? ' and ' : '') + '(max-width: ' + r2 + 'px)';
        }
        return matchQuery(mediaQueryString, r1, r2);
    }

    // media query function
    function matchQuery(query, r1, r2) {
        if (window.matchMedia && nativeMatchMedia) {
            return matchMedia(query).matches;
        } else if (window.styleMedia) {
            return styleMedia.matchMedium(query);
        } else if (window.media) {
            return media.matchMedium(query);
        } else {
            return prevWinWidth >= r1 && prevWinWidth <= r2;
        }
    }

    // range parser
    function parseRange(rangeStr) {
        var rangeData = rangeStr.split('..');
        var x1 = parseInt(rangeData[0], 10) || -Infinity;
        var x2 = parseInt(rangeData[1], 10) || Infinity;
        return [x1, x2].sort(function (a, b) {
            return a - b;
        });
    }

    // export public functions
    return {
        addRange: function (ranges) {
            // parse data and add items to collection
            var result = {data: {}};
            $.each(ranges, function (property, data) {
                result.data[property] = {
                    range: parseRange(property),
                    enableCallback: data.on,
                    disableCallback: data.off
                };
            });
            handlers.push(result);

            // call resizeHandler to recalculate all events
            prevWinWidth = null;
            resizeHandler();
        }
    };
}(jQuery));

/*
 * Simple star rating module
 */
function StarRating() {
    this.options = {
        activeClass: 'active',
        settedClass: 'setted',
        element: null,
        items: null,
        onselect: null
    };
    this.init.apply(this, arguments);
}
StarRating.prototype = {
    init: function (opt) {
        this.setOptions(opt);
        if (this.element) {
            this.getElements();
            this.addEvents();
        }
    },
    setOptions: function (opt) {
        for (var p in opt) {
            if (opt.hasOwnProperty(p)) {
                this.options[p] = opt[p];
            }
        }
        if (this.options.element) {
            this.element = this.options.element;
        }
    },
    getElements: function () {
        // get switch objects
        if (this.options.items === null) {
            this.items = this.element.children;
        } else {
            if (typeof this.options.items === 'string') {
                this.items = this.element.getElementsByTagName(this.options.items);
            } else if (typeof this.options.items === 'object') {
                this.items = this.options.items;
            }
        }

        // find default active index
        for (var i = 0; i < this.items.length; i++) {
            if (lib.hasClass(this.items[i], this.options.activeClass)) {
                this.activeIndex = i;
            }
            if (lib.hasClass(this.items[i], this.options.settedClass)) {
                this.settedIndex = i;
            }
        }
    },
    addEvents: function () {
        for (var i = 0; i < this.items.length; i++) {
            this.items[i].onmouseover = lib.bind(this.overHandler, this, i);
            this.items[i].onmouseout = lib.bind(this.outHandler, this, i);
            this.items[i].onclick = lib.bind(this.clickHandler, this, i);
        }
    },
    overHandler: function (ind) {
        this.hovering = true;
        this.hoverIndex = ind;
        this.refreshClasses();
    },
    outHandler: function (ind) {
        this.hovering = false;
        this.refreshClasses();
    },
    clickHandler: function (ind) {
        this.hovering = false;
        this.settedIndex = ind;
        if (typeof this.options.onselect === 'function') {
            this.options.onselect(ind);
        }
        this.refreshClasses();
        return false;
    },
    refreshClasses: function () {
        for (var i = 0; i < this.items.length; i++) {
            lib.removeClass(this.items[i], this.options.activeClass);
            lib.removeClass(this.items[i], this.options.settedClass);
        }
        if (this.hovering) {
            lib.addClass(this.items[this.hoverIndex], this.options.activeClass);
        } else {
            if (typeof this.settedIndex === 'number') {
                lib.addClass(this.items[this.settedIndex], this.options.settedClass);
            } else {
                if (typeof this.activeIndex === 'number') {
                    lib.addClass(this.items[this.activeIndex], this.options.activeClass);
                }
            }
        }
    }
};

/*
 * Utility module
 */
lib = {
    hasClass: function (el, cls) {
        return el && el.className ? el.className.match(new RegExp('(\\s|^)' + cls + '(\\s|$)')) : false;
    },
    addClass: function (el, cls) {
        if (el && !this.hasClass(el, cls)) el.className += " " + cls;
    },
    removeClass: function (el, cls) {
        if (el && this.hasClass(el, cls)) {
            el.className = el.className.replace(new RegExp('(\\s|^)' + cls + '(\\s|$)'), ' ');
        }
    },
    extend: function (obj) {
        for (var i = 1; i < arguments.length; i++) {
            for (var p in arguments[i]) {
                if (arguments[i].hasOwnProperty(p)) {
                    obj[p] = arguments[i][p];
                }
            }
        }
        return obj;
    },
    each: function (obj, callback) {
        var property, len;
        if (typeof obj.length === 'number') {
            for (property = 0, len = obj.length; property < len; property++) {
                if (callback.call(obj[property], property, obj[property]) === false) {
                    break;
                }
            }
        } else {
            for (property in obj) {
                if (obj.hasOwnProperty(property)) {
                    if (callback.call(obj[property], property, obj[property]) === false) {
                        break;
                    }
                }
            }
        }
    },
    event: (function () {
        var fixEvent = function (e) {
            e = e || window.event;
            if (e.isFixed) return e; else e.isFixed = true;
            if (!e.target) e.target = e.srcElement;
            e.preventDefault = e.preventDefault || function () {
                this.returnValue = false;
            };
            e.stopPropagation = e.stopPropagation || function () {
                this.cancelBubble = true;
            };
            return e;
        };
        return {
            add: function (elem, event, handler) {
                if (!elem.events) {
                    elem.events = {};
                    elem.handle = function (e) {
                        var ret, handlers = elem.events[e.type];
                        e = fixEvent(e);
                        for (var i = 0, len = handlers.length; i < len; i++) {
                            if (handlers[i]) {
                                ret = handlers[i].call(elem, e);
                                if (ret === false) {
                                    e.preventDefault();
                                    e.stopPropagation();
                                }
                            }
                        }
                    };
                }
                if (!elem.events[event]) {
                    elem.events[event] = [];
                    if (elem.addEventListener) elem.addEventListener(event, elem.handle, false);
                    else if (elem.attachEvent) elem.attachEvent('on' + event, elem.handle);
                }
                elem.events[event].push(handler);
            },
            remove: function (elem, event, handler) {
                var handlers = elem.events[event];
                for (var i = handlers.length - 1; i >= 0; i--) {
                    if (handlers[i] === handler) {
                        handlers.splice(i, 1);
                    }
                }
                if (!handlers.length) {
                    delete elem.events[event];
                    if (elem.removeEventListener) elem.removeEventListener(event, elem.handle, false);
                    else if (elem.detachEvent) elem.detachEvent('on' + event, elem.handle);
                }
            }
        };
    }()),
    queryElementsBySelector: function (selector, scope) {
        scope = scope || document;
        if (!selector) return [];
        if (selector === '>*') return scope.children;
        if (typeof document.querySelectorAll === 'function') {
            return scope.querySelectorAll(selector);
        }
        var selectors = selector.split(',');
        var resultList = [];
        for (var s = 0; s < selectors.length; s++) {
            var currentContext = [scope || document];
            var tokens = selectors[s].replace(/^\s+/, '').replace(/\s+$/, '').split(' ');
            for (var i = 0; i < tokens.length; i++) {
                token = tokens[i].replace(/^\s+/, '').replace(/\s+$/, '');
                if (token.indexOf('#') > -1) {
                    var bits = token.split('#'), tagName = bits[0], id = bits[1];
                    var element = document.getElementById(id);
                    if (element && tagName && element.nodeName.toLowerCase() != tagName) {
                        return [];
                    }
                    currentContext = element ? [element] : [];
                    continue;
                }
                if (token.indexOf('.') > -1) {
                    var bits = token.split('.'), tagName = bits[0] || '*', className = bits[1], found = [], foundCount = 0;
                    for (var h = 0; h < currentContext.length; h++) {
                        var elements;
                        if (tagName == '*') {
                            elements = currentContext[h].getElementsByTagName('*');
                        } else {
                            elements = currentContext[h].getElementsByTagName(tagName);
                        }
                        for (var j = 0; j < elements.length; j++) {
                            found[foundCount++] = elements[j];
                        }
                    }
                    currentContext = [];
                    var currentContextIndex = 0;
                    for (var k = 0; k < found.length; k++) {
                        if (found[k].className && found[k].className.match(new RegExp('(\\s|^)' + className + '(\\s|$)'))) {
                            currentContext[currentContextIndex++] = found[k];
                        }
                    }
                    continue;
                }
                if (token.match(/^(\w*)\[(\w+)([=~\|\^\$\*]?)=?"?([^\]"]*)"?\]$/)) {
                    var tagName = RegExp.$1 || '*', attrName = RegExp.$2, attrOperator = RegExp.$3, attrValue = RegExp.$4;
                    if (attrName.toLowerCase() == 'for' && this.browser.msie && this.browser.version < 8) {
                        attrName = 'htmlFor';
                    }
                    var found = [], foundCount = 0;
                    for (var h = 0; h < currentContext.length; h++) {
                        var elements;
                        if (tagName == '*') {
                            elements = currentContext[h].getElementsByTagName('*');
                        } else {
                            elements = currentContext[h].getElementsByTagName(tagName);
                        }
                        for (var j = 0; elements[j]; j++) {
                            found[foundCount++] = elements[j];
                        }
                    }
                    currentContext = [];
                    var currentContextIndex = 0, checkFunction;
                    switch (attrOperator) {
                        case '=':
                            checkFunction = function (e) {
                                return (e.getAttribute(attrName) == attrValue)
                            };
                            break;
                        case '~':
                            checkFunction = function (e) {
                                return (e.getAttribute(attrName).match(new RegExp('(\\s|^)' + attrValue + '(\\s|$)')))
                            };
                            break;
                        case '|':
                            checkFunction = function (e) {
                                return (e.getAttribute(attrName).match(new RegExp('^' + attrValue + '-?')))
                            };
                            break;
                        case '^':
                            checkFunction = function (e) {
                                return (e.getAttribute(attrName).indexOf(attrValue) == 0)
                            };
                            break;
                        case '$':
                            checkFunction = function (e) {
                                return (e.getAttribute(attrName).lastIndexOf(attrValue) == e.getAttribute(attrName).length - attrValue.length)
                            };
                            break;
                        case '*':
                            checkFunction = function (e) {
                                return (e.getAttribute(attrName).indexOf(attrValue) > -1)
                            };
                            break;
                        default :
                            checkFunction = function (e) {
                                return e.getAttribute(attrName)
                            };
                    }
                    currentContext = [];
                    var currentContextIndex = 0;
                    for (var k = 0; k < found.length; k++) {
                        if (checkFunction(found[k])) {
                            currentContext[currentContextIndex++] = found[k];
                        }
                    }
                    continue;
                }
                tagName = token;
                var found = [], foundCount = 0;
                for (var h = 0; h < currentContext.length; h++) {
                    var elements = currentContext[h].getElementsByTagName(tagName);
                    for (var j = 0; j < elements.length; j++) {
                        found[foundCount++] = elements[j];
                    }
                }
                currentContext = found;
            }
            resultList = [].concat(resultList, currentContext);
        }
        return resultList;
    },
    trim: function (str) {
        return str.replace(/^\s+/, '').replace(/\s+$/, '');
    },
    bind: function (f, scope, forceArgs) {
        return function () {
            return f.apply(scope, typeof forceArgs !== 'undefined' ? [forceArgs] : arguments);
        };
    }
};
