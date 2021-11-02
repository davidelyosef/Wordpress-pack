(function (window, document, $) {
    "use strict";

    $(document).ready(function ($) {
        let animationClasses = [
            'example1',
            'example2',
        ];

        animationsHandler(animationClasses);

        $(window).width() > 992 && $(window).on('scroll', function () {
            animationsHandler(animationClasses);
        });
    });

})(window, document, jQuery);

/**
 * Animation handler:
 * Search for animation classes and activate them when the user reach them in the scroll position
 * @var animationClasses: str[]
 */
function animationsHandler(animationClasses) {
    animationClasses.map(animationClass => {
        $('.' + animationClass).each(elem => {
            let current = $('.' + animationClass)[elem];
            let position = $(current).offset().top;

            if ($(document).scrollTop() > position - $(window).height() * 0.85) {
                $(current).addClass('active');
            }
        });
    });
}