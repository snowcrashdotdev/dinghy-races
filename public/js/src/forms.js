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
        const inputRemove = document.getElementById(input.id + '_remove')

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
                inputRemove.value = 0
            }
        })
    }

    var fileRemovalButtons = document.getElementsByClassName('remove-file')
    for (let button of fileRemovalButtons) {
        let fileInputId = button.parentElement.getAttribute('data-file-input')
        let fileInput = document.getElementById(fileInputId)
        let fileRemoveInput = document.getElementById(fileInputId + '_remove')
        let filePreview = document.getElementById(fileInputId + '_preview')
        let sibling = button.previousElementSibling
        button.addEventListener('click', function(e) {
            e.stopPropagation()  
            fileInput.value = ''
            fileInput.removeAttribute('hidden')
            filePreview.src = ''
            
            fileRemoveInput.value = 1

            sibling.style.display = 'none'
            this.style.display = 'none'
        })
    }
})