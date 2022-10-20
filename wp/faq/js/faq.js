import $ from 'jquery';

let faqComponentQI = $('.faq-component .faq-section__item-title');
faqComponentQI.on('click', function (e) {
    e.preventDefault();

    var allFaqQIs = faqComponentQI.closest('.faq-section__item'),
        thisFaqQI = $(this).closest('.faq-section__item');

    if (thisFaqQI.hasClass('faq-item-opened')) {
        thisFaqQI.removeClass('faq-item-opened');
        thisFaqQI.find('.faq-section__item-answertext').slideUp();
    } else {
        allFaqQIs.removeClass('faq-item-opened');
        allFaqQIs.find('.faq-section__item-answertext').slideUp();
        thisFaqQI.addClass('faq-item-opened');
        thisFaqQI.find('.faq-section__item-answertext').slideDown({
            complete: function () {
                let heightHeader = $('header').outerHeight();
                let scrollTop = thisFaqQI.offset().top - heightHeader - 16;

                $('html, body').animate({
                    scrollTop: scrollTop
                }, 500);
            }
        });
    }
});
