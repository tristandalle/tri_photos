var htmlForm = $('form'),
    fileInput = document.querySelector('#photo_file'),
    inProgress =  $('.tranfert-in-progress'),
    containerProgressBar = $('.progress'),
    progressBar = $('.progress-bar'),
    progressPercent = $('.percent-progress'),
    animeCss = $('.lds-roller'),
    redirectionLinks = $('.container-links');


htmlForm.submit(function (e) {
    e.preventDefault();
    var xhr = new XMLHttpRequest();
    xhr.open('POST', '/add');
    var form = new FormData();
    form.append('select-album', document.getElementById('select-album').value);
    form.append('photo[_token]', document.getElementById('photo__token').value);
    for (var i = 0; i < fileInput.files.length; i++) {
        form.append('photo[file][]', fileInput.files[i]);
    }
    xhr.addEventListener("load", transferComplete, false);
    xhr.upload.addEventListener('progress', function (e) {
        loadedValue = e.loaded;
        totalValue = e.total;
        loadedPercent = (e.loaded * 100) / e.total;
        inProgress.show();
        containerProgressBar.css('display', 'flex');
        progressBar.css('display', 'flex');
        progressBar.width(loadedPercent + '%');
        progressPercent.show();
        progressPercent.html(loadedPercent.toFixed() + ' %');
        if (e.loaded === e.total) {
            inProgress.html('Merci de patienter quelques instants ...');
            containerProgressBar.delay(1000).fadeTo( "slow", 0 );
            animeCss.css('display', 'inline-block');
            progressPercent.hide('slow');
        }
    });
    function transferComplete(evt) {
        inProgress.delay(1000).fadeTo( "slow", 0 );
        animeCss.delay(1000).css({
            visibility : 'hidden',
            opacity : '0'
        });
        progressPercent.show('slow').html('<i class="fas fa-check"></i><br>Transfert terminé avec succès !').css({
            color : '#3399F3',
            fontSize : '2em'
        });
        redirectionLinks.delay(1000).fadeIn("slow");
    }
    console.log(form);
    htmlForm.hide();
    xhr.send(form);
});