/**
 * Load this file if you want to handle inner page navigations on your site.
 * Just Add 'element-link' class to your link and 'data-id' attribute to the id of the element you wish to go.
 * It will add the class 'active' to the link according to your current position. 
 */

(function (window, document, $) {

    $(document).ready(function ($) {

        function findLinkElementsIDs() {
            let arr = [];

            $('.element-link').each(function(index) {
                this.dataset.id && arr.push(this.dataset.id);
            });

            return arr;
        }

        function findLinkButtonByDataId(dataId) {
            return $('.element-link').filter(function() {
                return this.dataset.id == dataId;
            });
        }

        const idArr = findLinkElementsIDs();

        function elementScrollPosition() {
            idArr.map(id => {
                if ($(document).scrollTop() > $(`#${id}`).offset().top - 150) {
                    const button = findLinkButtonByDataId(id);
                    $('.element-link').removeClass('active');
                    $(button[0]).addClass('active');
                }
            })
        }

        elementScrollPosition();

        $(window).width() > 992 && $(window).on('scroll', function () {
            elementScrollPosition();
        });
        
    });
    
})(window, document, jQuery);