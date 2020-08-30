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
    let flashBag = document.getElementById('flash-bag')

    if (flashBag.childElementCount > 0) {
        flashBag.removeAttribute('hidden')
        setTimeout(function(){
            flashBag.classList.add('fade')
            setTimeout(function(){
                removeAllChildren(flashBag)
                flashBag.setAttribute('hidden', '')
            }, 1000)
        }, 6 * 1000)
    }
})