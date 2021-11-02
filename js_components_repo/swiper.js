(function (window, document, $) {
    "use strict";

    // https://swiperjs.com/swiper-api
    $(document).ready(function ($) {

        var mySwiper = new Swiper('.home-slider', {
            speed: 500,
            autoplay: {
                delay: 4000,
            },

            loop: true,
            slidesPerView: 1,
            spaceBetween: 0,

            navigation: {
                nextEl: '.home-next',
                prevEl: '.home-prev',
            },

            pagination: {
                el: '.swiper-pagination',
                type: 'bullets',
                clickable: true
            },

            // breakpoints: {},
            // slidesPerColumn,
            // slidesPerColumnFill: 'row',
            // resizeEvent: 'auto',
            // disableAutoResize: false,
        });
        
    });

})(window, document, jQuery);