;
(function (window, document, $) {
    "use strict";

    $(document).ready(function ($) {

        let chosenModal;

        // Open modal
        $('.open-modal').on('click', function (e) {
            e.preventDefault();
            console.log('open sesame');
            chosenModal = this.dataset.modal;
            $(chosenModal).show();
            $('.modal__exit-button')[0].focus();
        });

        // exit modal
        $('.modal__exit-button').on('click', function() {
            console.log('close sesame');
            $(chosenModal).hide();
        });
    });

})(window, document, jQuery);