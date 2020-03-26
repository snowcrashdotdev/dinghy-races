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