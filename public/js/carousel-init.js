$(function () {
    $('.loader').hide();
    var indicator = $('.indicator');
    var item = $('.carousel-item');

    if (indicator.hasClass('active')) {
        console.log('yes')
    } else {
        indicator.first().addClass("active");
        item.first().addClass("active");
    }

    /*$( "input[type='submit']" ).click(function () {
        indicator.removeClass("active");
        item.removeClass("active");
    })*/
});
