document.addEventListener('DOMContentLoaded', function() {
    let fileInputs = document.querySelectorAll('[type="file"]')

    for (let input of fileInputs) {
        let label = input.parentElement.firstElementChild
        let type = input.getAttribute('data-type')
        let src = input.getAttribute('data-src').replace('public', '')
        let defaultLabel = input.getAttribute('data-default-label')

        if (type === 'image' && src !== '') {
            label.style.backgroundImage = `url(${src})`
        }

        const reader = new FileReader()
        reader.addEventListener('load', function() {
            label.style.backgroundImage = `url(${reader.result})`
        })

        let menu = document.createElement('div')
        menu.classList.add('file-input-menu')
        let optionRemove = document.createElement('button')
        optionRemove.innerText = 'Remove This File'
        let optionReplace = document.createElement('button')
        optionReplace.innerText = 'Upload Different File'

        let removeFieldId = input.id + '_remove'
        let removeField = document.getElementById(removeFieldId)

        for (let button of [optionRemove, optionReplace]) {
            button.type = 'button'
            button.classList.add('file-input-control')
        }

        optionRemove.hide = function() {
            this.style.display = 'none'
        }

        optionRemove.show = function() {
            this.removeAttribute('style')
        }

        if (src === '') {
            optionRemove.hide()
        }

        menu.open = function() {
            this.classList.add('flex')
        }

        menu.close = function() {
            this.classList.remove('flex')
        }

        menu.appendChild(optionReplace)
        menu.appendChild(optionRemove)
        label.parentElement.appendChild(menu)

        label.addEventListener('click', function(e) {
            e.preventDefault()
            if (input.getAttribute('data-src') === '') {
                input.click()
            } else {
                menu.open()
            }
        }, false)

        optionReplace.addEventListener('click', function(e) {
            input.click()
        })

        optionRemove.addEventListener('click', function(e) {
            if (removeField) {
                removeField.value = 1
                input.setAttribute('data-src', '')
                label.removeAttribute('style')
                label.innerText = defaultLabel
                optionRemove.hide()
            }
        })

        document.addEventListener('click', function(e) {
            if (e.target !== label) {
                menu.close()
            }
        })

        input.addEventListener('change', function() {
            let file = this.files[0]

            if (file) {
                let filename = this.value.split('\\').pop()
                if (type === 'image') {
                    reader.readAsDataURL(file)
                }

                label.innerText = filename
                input.setAttribute('data-src', filename)

                removeField.value = 0
                optionRemove.show()
            }
        })
    }
})