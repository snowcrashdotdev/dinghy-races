window.addEventListener('DOMContentLoaded', function(e) {
    var addToCollectionButtons = document.getElementsByClassName('add-to-collection');

    for (let button of addToCollectionButtons) {
        button.addEventListener('click', addFormToCollection, false)
    }

    var ajaxSearchInputs = document.getElementsByClassName('ajax-search')

    for (let search of ajaxSearchInputs) {
        search.addEventListener('keydown', performAjaxSearch, false)
    }
});

function addFormToCollection() {
    var collection = document.getElementById(this.dataset.collection)
    var prototype = collection.dataset.prototype
    var prototypeName = collection.dataset.prototypeName
    var index = collection.dataset.index
    var form = prototype.replace(new RegExp(prototypeName, 'g'), index)
    form = document.createRange().createContextualFragment(form)
    collection.appendChild(form)

    childCollection = document.getElementById([collection.id, index, 'members'].join('_'))

    if (childCollection) {
        childCollection.dataset.index = 0
        childCollection.dataset.prototypeName = 'member'
        var btn = document.createElement('button')
        btn.classList.add('add-to-collection')
        btn.dataset.collection = childCollection.id
        btn.type = 'button'
        btn.innerText = 'Add member'
        btn.addEventListener('click', addFormToCollection)

        childCollection.before(btn)
    }

    var newSearchInputs = collection.getElementsByClassName('ajax-search')
    if (newSearchInputs) {
        for (let search of newSearchInputs) {
            search.addEventListener('keydown', performAjaxSearch, false)
        }
    }

    collection.dataset.index++
}

function performAjaxSearch() {
    var q = this.value
    if (this.value < 3) {
        return true;
    } else {
        var url = [document.getElementById('ajax-search-path').dataset[this.dataset.entity], this.value].join('/')

        window.fetch(url, {method:'POST'})
            .then(function(res) {
                return res.json()
            })
            .then(function(json) {
                console.log(json)
            })
    }
}