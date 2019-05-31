window.addEventListener('DOMContentLoaded', function(e) {
    var addToCollectionButtons = document.getElementsByClassName('add-to-collection');

    for (let button of addToCollectionButtons) {
        button.addEventListener('click', addFormToCollection, false)
    }

    var ajaxSearchInputs = document.getElementsByClassName('ajax-search')

    for (let search of ajaxSearchInputs) {
        search.addEventListener('keydown', performAjaxSearch, false)
    }

    var teamMembers = document.getElementsByClassName('team-member-input')

    for (let member of teamMembers) {
        var removeMemberButton = document.createElement('button')
        removeMemberButton.innerText = 'X'
        removeMemberButton.addEventListener('click', removeMember)
        member.parentElement.appendChild(removeMemberButton)
    }
});

function removeMember(e) {
    e.target.parentNode.remove()
}

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
        childCollection.dataset.prototypeName = '__member__'
        childCollection.classList.add('tournament-collection')
        var btn = document.createElement('button')
        btn.classList.add('add-to-collection', 'push-center')
        btn.dataset.collection = childCollection.id
        btn.type = 'button'
        btn.innerText = 'Add member'
        btn.addEventListener('click', addFormToCollection)

        childCollection.appendChild(btn)
    }

    var newSearchInputs = collection.getElementsByClassName('ajax-search')
    if (newSearchInputs) {
        for (let search of newSearchInputs) {
            search.addEventListener('input', performAjaxSearch, false)
        }
    }

    collection.dataset.index++
    collection.appendChild(this)
}

var ajaxResults = document.createElement('ul')
ajaxResults.classList.add('ajax-results-collection')
function performAjaxSearch(e) {
    var q = e.target.value
    if (this.value.length < 3) {
        while(ajaxResults.hasChildNodes()) {
            ajaxResults.removeChild(ajaxResults.lastChild)
        }
        return true;
    } else {
        var url = [document.getElementById('ajax-search-path').dataset[this.dataset.entity], this.value].join('/')
        var autocomplete = e.target
        e.target.parentNode.classList.add('position')

        window.fetch(url, {method:'POST'})
            .then(function(res) {
                return res.json()
            })
            .then(function(json) {
                if (json.data.length) {
                    while(ajaxResults.hasChildNodes()) {
                        ajaxResults.removeChild(ajaxResults.lastChild)
                    }
                    ajaxResults.remove()
                    for (let result of json.data) {
                        var li = document.createElement('li');
                        li.innerText = result.value
                        li.classList.add('ajax-search-result')
                        li.addEventListener('click', function(e){
                            autocomplete.value = e.target.innerText
                            while(ajaxResults.hasChildNodes()) {
                                ajaxResults.removeChild(ajaxResults.lastChild)
                            }
                            return false
                        })
                        ajaxResults.appendChild(li)
                        autocomplete.parentNode.insertBefore(ajaxResults, autocomplete)
                    }
                }
            })
    }
}