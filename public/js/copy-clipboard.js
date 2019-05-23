$(function () {
    $('[data-toggle="popover"]').popover().click(function () {
        setTimeout(function () {
            $('[data-toggle="popover"]').popover('hide');
        }, 2000);
    })
});

function copyLink() {
    var copyText = $("#linkInput");
    copyText.select();
    document.execCommand("copy");
}