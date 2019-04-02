var fileInput = document.querySelector('#photo_file'),
    containerProgressBar = $('.progress'),
    progressBar = $('.progress-bar'),
    progressPercent = $('.percent-progress');
    animeCss = $('.lds-roller');

containerProgressBar.hide();
progressBar.hide();
progressPercent.hide();
animeCss.hide();

$('form').submit(function (e) {
    var xhr = new XMLHttpRequest();

    xhr.open('POST', '/add');
    var form = new FormData();
    for (var i = 0; i < fileInput.files.length; i++){
        form.append('file', fileInput.files[i]);
    }

    xhr.upload.addEventListener('progress', function(e) {
        loadedValue = e.loaded;
        totalValue = e.total;

console.log(fileInput.files.length);
        loadedPercent = (e.loaded * 100) / e.total;
        containerProgressBar.show();
        progressBar.show();
        progressPercent.show();
        progressBar.width(loadedPercent + '%');
        progressPercent.html(loadedPercent.toFixed() + ' %');
        if (e.loaded === e.total) {
            progressPercent.fadeOut(2000)
                .queue(function(n) {
                    $(this).html("transfert terminé ! Vous allez être redirigé vers vos photos");
                    n();
                }).fadeIn(500);
            animeCss.show();
        }
    });

    xhr.addEventListener('load', function (e) {
        console.log(xhr.responseText)
    });
   /* xhr.upload.addEventListener('loadstart', function(e) {
        console.log("loadstart", e)
    });
    xhr.upload.addEventListener('abord', function(e) {
        console.log("abord", e)
    });
    xhr.upload.addEventListener('error', function(e) {
        console.log("error", e)
    });*/

    xhr.send(form);

});