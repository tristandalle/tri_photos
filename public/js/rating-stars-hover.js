$(function () {
    var oneStar = $('.rating a:nth-child(1)');
    var twoStars = $('.rating a:nth-child(2)');
    var threeStars = $('.rating a:nth-child(3)');
    oneStar.hover(
        function () {
            oneStar.addClass('hover');
        }, function () {
            oneStar.removeClass('hover');
        }
    );
    twoStars.hover(
        function () {
            oneStar.addClass('hover');
            twoStars.addClass('hover');
        }, function () {
            oneStar.removeClass('hover');
            twoStars.removeClass('hover');
        }
    );
    threeStars.hover(
        function () {
            oneStar.addClass('hover');
            twoStars.addClass('hover');
            threeStars.addClass('hover');
        }, function () {
            oneStar.removeClass('hover');
            twoStars.removeClass('hover');
            threeStars.removeClass('hover');
        }
    );
    var filterOneStar = $('.filter a:nth-child(1)');
    var filterTwoStars = $('.filter a:nth-child(2)');
    var filterThreeStars = $('.filter a:nth-child(3)');
    filterOneStar.hover(
        function () {
            filterOneStar.addClass('hover');
        }, function () {
            filterOneStar.removeClass('hover');
        }
    );
    filterTwoStars.hover(
        function () {
            filterOneStar.addClass('hover');
            filterTwoStars.addClass('hover');
        }, function () {
            filterOneStar.removeClass('hover');
            filterTwoStars.removeClass('hover');
        }
    );
    filterThreeStars.hover(
        function () {
            filterOneStar.addClass('hover');
            filterTwoStars.addClass('hover');
            filterThreeStars.addClass('hover');
        }, function () {
            filterOneStar.removeClass('hover');
            filterTwoStars.removeClass('hover');
            filterThreeStars.removeClass('hover');
        }
    );
});