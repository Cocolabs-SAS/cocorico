$(window).load(function () {
    var $toggleFilter = $('#toggle-filter');

    var $baFilter = $('.sonata-ba-filter');
    $toggleFilter.on('click', function () {
        if ($baFilter.length > 0) {
            if ($baFilter.css("display") == "none") {
                $baFilter.show();
                $('.sonata-ba-list').removeClass('col-md-12').addClass('col-md-10');
            } else {
                $baFilter.hide();
                $('.sonata-ba-list').removeClass('col-md-10').addClass('col-md-12');
            }
        }
    });

    if ($baFilter.length > 0) {
        $toggleFilter.show();
        $toggleFilter.click();
    } else {
        $toggleFilter.hide();
    }

    //jQuery(".datepicker").datepicker({"dateFormat": 'yy-mm-dd'});

    //Change img style when sidebar toggle
    $("[data-toggle='offcanvas']").click(function (e) {
        var $logoImg = $('.logo img');
        if ($('.right-side').hasClass('strech') || window.matchMedia('(max-width: 992px)').matches) {
            $logoImg.css('height', '50px');
            $logoImg.css('width', '55px'); // auto
            $('body > .header .logo').css('height', '50px');
            $('.left-side').css('top', '0px');
        }
        else {
            $logoImg.css('height', '122px');
            $logoImg.css('width', '134px');
            $('body > .header .logo').css('height', '122px');
            $('.left-side').css('top', '72px');

        }
    });

});