;
(function ($, window, document, undefined) {
    var $win = $(window);
    var $doc = $(document);

    $doc.ready(function () {

        $win.load(function () {
            var $sliderSecond = $('.slider-secondary');
            if (!$sliderSecond.length) {
                return;
            }
            var ww = $win.width(),
                $sliderSecondary = $sliderSecond.find('.slides'),
                config = {
                    auto: false,
                    responsive: true,
                    items: {
                        visible: {
                            min: 1,
                            max: 3
                        },
                        height: 'variable'
                    },
                    prev: {
                        button: '.slider-secondary-actions .slider-prev',
                        key: 'left'
                    },
                    next: {
                        button: '.slider-secondary-actions .slider-next',
                        key: 'right'
                    },
                    pagination: {
                        container: '.slider-secondary-paging'
                    },
                    swipe: {
                        onTouch: true,
                        options: {
                            excludedElements: ''
                        }
                    }
                },
                configMobile = {
                    responsive: true,
                    auto: false,
                    items: {
                        visible: 1
                    },
                    scroll: {
                        items: 1
                    }
                },
                configTablet = {
                    responsive: true,
                    auto: false,
                    items: {
                        visible: 2
                    },
                    scroll: {
                        items: 2
                    }
                };

            $sliderSecondary.carouFredSel(config);
            if (ww < 768) {
                $sliderSecondary.trigger('configuration', configMobile);
            } else if (ww < 1023) {
                $sliderSecondary.trigger('configuration', configTablet);
            } else {
                $sliderSecondary.trigger('configuration', config);
            }

            $win.on('resize', function () {
                ww = $win.width();

                if (ww < 768) {
                    $sliderSecondary.trigger('configuration', configMobile);
                } else if (ww < 1023) {
                    $sliderSecondary.trigger('configuration', configTablet);
                } else {
                    $sliderSecondary.trigger('configuration', config);
                }
            }).trigger('resize');

            //testimonial
            //$(".testimonial-slider .slides").carouFredSel({
            //    auto: true,
            //    responsive: true,
            //    height: 'variable',
            //
            //    pagination: {
            //        container: '.testimonial-paging'
            //    },
            //    swipe: {
            //        onTouch: true,
            //        options: {
            //            excludedElements: ''
            //        }
            //    }
            //});
        });

    });

})(jQuery, window, document);
