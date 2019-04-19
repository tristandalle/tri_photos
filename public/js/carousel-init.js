$(function () {
    $('.loader').hide();
    var indicator = $('.indicator');
    var item = $('.carousel-item');

    if (!indicator.hasClass('active')) {
        indicator.first().addClass("active");
        item.first().addClass("active");
    }
});
