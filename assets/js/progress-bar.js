var fileInput = document.querySelector('#photo_file'),
    progress = document.querySelector('#progress');

$('form').submit(function (e) {
    var xhr = new XMLHttpRequest();

    xhr.open('POST', '/add');
    var form = new FormData();
    form.append('file', fileInput.file);
    xhr.upload.addEventListener('progress', function(e) {
        progress.value = e.loaded;
        progress.max = e.total;
    });

    xhr.send(form);
});
/*
$('form').submit(function (e){
    e.preventDefault();
    var formData = new FormData(this);
    var inputElement = $('#photo_file');
    var nbFiles = inputElement[0].files.length;
    var totalSize = 0;

    for (i = 0; i < nbFiles; i++) {
        totalSize += inputElement[0].files[i].size
    }

    console.log(inputElement[0].files.length);
    console.log(totalSize);

    $('#progress-bar').width(0 +'%');
    var xhr = new XMLHttpRequest();
    xhr.open('POST', '/add');
    xhr.onprogress = function(e){
        var Etotal = e.total;
        console.log('etotal='+ Etotal);
        var loaded = Math.round((e.loaded / e.total) * 100);
        $('#progress-bar').width(loaded + '%');
        console.log(loaded);
    };
*/

    /*xhr.onload = function(){
        $('#progress-bar').width(100 +'%');
    }*/
/*

    formData.append('file', inputElement[0].files);
    console.log(formData);
    xhr.send(formData);

});

var progressBar = $('#progress-bar');

$('form').submit(function (e) {
    e.preventDefault();
    var formData = new FormData(this);
    $.ajax({
        url: '/add',
        xhr: function () {
            var xhr = new XMLHttpRequest()
            var total = $('#photo_file').files[0].size;
            xhr.upload.addEventListener('progress', function (e) {
                var loaded = Math.round((e.loaded / total) * 100);
                $('#progress-bar').width(loaded + '%');
            })
            return xhr;
        }

    })
});
*/
