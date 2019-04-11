$(function () {
    if (($('.indicator').not('active')) && $('.carousel-item').not('active')) {
        $('.indicator').first().addClass("active");
        $('.carousel-item').first().addClass("active");
    }
    $( "input[type='submit']" ).click(function () {
        $('.indicator').removeClass("active");
        $('.carousel-item').removeClass("active");
    })
});
