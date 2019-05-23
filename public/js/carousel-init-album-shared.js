$(function () {
    var clickedId = $('.hover-photo-album-shared');
    clickedId.click(function () {
        var idToActive = $(this).find('a').attr('id');
        $('.carousel-item').removeClass('active');
        $('.' + idToActive).addClass('active');
    });
});