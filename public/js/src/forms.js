document.addEventListener('DOMContentLoaded', function() {
    var uploadPreviews = document.getElementsByClassName('upload-preview')

    for (let preview of uploadPreviews) {
        preview.addEventListener('click', function(e) {
            document.getElementById(preview.getAttribute('data-file-input')).click()
        })
    }

    var pictureInputs = document.getElementsByClassName('picture-input')
    for (let input of pictureInputs) {
        const reader = new FileReader()
        const preview = document.getElementById(input.id + '_preview')

        reader.addEventListener('load', function(){
            preview.src = reader.result
            preview.style.display = 'block'
            preview.nextElementSibling.style.display = 'block'
            input.setAttribute('hidden', '')
        }, false)

        input.addEventListener('change', function(){
            let file = this.files[0]
            if (file) {
                reader.readAsDataURL(file)
            }
        })
    }

    var fileRemovalButtons = document.getElementsByClassName('remove-file')
    for (let button of fileRemovalButtons) {
        let fileInput = document.getElementById(button.getAttribute('data-file-input'))
        let sibling = button.previousElementSibling
        button.addEventListener('click', function(e) {
            e.stopPropagation()  
            fileInput.value = ''
            fileInput.removeAttribute('hidden')

            sibling.style.display = 'none'
            this.style.display = 'none'
        })
    }
})