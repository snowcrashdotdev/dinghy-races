import 'whatwg-fetch'

document.addEventListener('DOMContentLoaded', function() {
    let dropdownAnchors = Array.from(document.getElementsByClassName('has-sub-items')).map(e => e.firstElementChild)
    let dropdownMenus = document.getElementsByClassName('sub-navigation')

    dropdownAnchors.forEach(a => a.addEventListener('click', function(e) {
        e.preventDefault()
        let subMenu = this.nextElementSibling
        subMenu.classList.add('block')
    }))

    document.body.addEventListener('click', closeSubMenus)

    function closeSubMenus(event) {
        for (let menu of dropdownMenus) {
            let self = event.target.nextElementSibling

            if (menu !== self)
                menu.classList.remove('block')
            }
        }
    }
)

document.addEventListener('DOMContentLoaded', function() {
    let ajaxSearchInputs = document.getElementsByClassName('ajax-search')
    for (let input of ajaxSearchInputs) {
        let ajaxSearchResultsList = document.createElement('ul')
        ajaxSearchResultsList.classList.add('ajax-search-results')
        input.parentElement.appendChild(ajaxSearchResultsList)
        let entity = input.getAttribute('data-entity')
        let cb = input.getAttribute('data-cb')

        input.addEventListener('keyup', function(e) {
            let query = input.value
            let path = [entity, query].join('/')
            if (query < 2) {
                removeAllChildren(ajaxSearchResultsList)
                return false;
            } else {
                window.fetch('/ajax/search/' + path, {
                    'headers': {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(function(res) {
                    return res.json()
                })
                .then(function(json) {
                    removeAllChildren(ajaxSearchResultsList)
                    window[cb](JSON.parse(json),ajaxSearchResultsList)
                })
            }
        })

        input.addEventListener('focus', toggleAjaxSearchShow)

        input.addEventListener('blur', toggleAjaxSearchShow)
    }

    function toggleAjaxSearchShow(e) {
        let target = e.target
        setTimeout(function() {
            removeAllChildren(target.nextElementSibling)
            target.nextElementSibling.classList.toggle('show')
        }, 1000)
    }
})

function removeAllChildren(el) {
    while(el.hasChildNodes()) {
        el.removeChild(el.lastChild)
    }
}

document.addEventListener('DOMContentLoaded', function() {
    let listFilters = document.getElementsByClassName('list-filter')

    for (let filter of listFilters) {
        let list = filter.nextElementSibling
        filter.addEventListener('keyup', function() {
            let query = this.value.toUpperCase()
            let listItems = Array.from(list.children)

            if (query === '') {
                listItems.forEach(item => item.removeAttribute('hidden'))
            } else {
                listItems.forEach(function(item) {
                    let username = item.innerText.toUpperCase()
                    if (username.indexOf(query) > -1) {
                        item.removeAttribute('hidden')
                    } else {
                        item.setAttribute('hidden', '')
                    }
                })
            }
        })
    }
})

document.addEventListener('DOMContentLoaded', function() {
    let flashBag = document.getElementById('flash-bag')

    if (flashBag.childElementCount > 0) {
        setTimeout(function(){
            flashBag.classList.add('fade')
            setTimeout(function(){
                removeAllChildren(flashBag)
                flashBag.setAttribute('hidden', '')
            }, 1000)
        }, 6 * 1000)
    }
})