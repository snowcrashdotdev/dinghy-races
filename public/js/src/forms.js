document.addEventListener('DOMContentLoaded', function() {
    var uploadPreviews = document.getElementsByClassName('upload-preview');

    for (let preview of uploadPreviews) {
        preview.addEventListener('click', function(e) {
            document.getElementById(preview.getAttribute('data-file-input')).click();
        });
    }
});