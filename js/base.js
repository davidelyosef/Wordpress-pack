;
(function (window, document, $) {
    "use strict";

    $(document).ready(function ($) {
        console.log(111);
        // Accordion functionality //////////////////////////
        const accordionButton = $('.accordion');

        if (accordionButton) {
            accordionButton.on("click", function () {
                $(this).next().toggleClass('accordion-friend--active');
                $(this).toggleClass('accordion--active');
            })
        }

        // Header menu trigger ////////////////////////////////
        const menuTrigger = $("#menuTrigger");
        let activatedMenu = false;

        // Display menu
        menuTrigger.on("click", function () {

            if (!activatedMenu) {
                $(".header + .dropdown").slideDown(400, function () {
                    window.dispatchEvent(new Event('resize'));
                    $("body").addClass('body--menu');
                });
                activatedMenu = true;
                changeMenuImage(this);
            } else {
                $(".header + .dropdown").fadeOut('slow', function () {
                    $("body").removeClass('body--menu');
                });
                activatedMenu = false;
                changeMenuImage(this);
            }

            $("#menuTrigger .menu-text").toggleClass('active');

        });

        function changeMenuImage(element) {
            // Lines animation
            $(element).toggleClass('active');
            $(element).toggleClass('not-active');
        }

        /**
         * Link to elements
         * include 'data-id' attribute in .element-link button
         * that equals to the id you want to reach to.
         */
        $('.element-link').on('click', function () {
            let elem = this.dataset.id;
            scroll_to(elem);
        })
    });

})(window, document, jQuery);

function getTop() {
    return jQuery(window).scrollTop();
}

function isIE11() {
    return !!window.MSInputMethodContext && !!document.documentMode;
}

function isEmail(email) {
    var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
    return regex.test(email);
}

function scroll_to(elementID, distance = 10) {
    $([document.documentElement, document.body]).animate({
        scrollTop: $(`#${elementID}`).offset().top
    }, 500);
}