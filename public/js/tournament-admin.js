var teamCollection
var teamSection
var addTeamButton = document.createElement('button')
addTeamButton.type = 'button'
addTeamButton.innerText = 'Add team'
addTeamButton.classList.add('add-team-member-button')
var userSearchUrl;

window.addEventListener('DOMContentLoaded', function(){
    teamCollection = document.querySelector('#teams-collection')
    teamSection = document.querySelector('#teams')
    teamSection.appendChild(addTeamButton)
    teamCollection.dataset.index = teamCollection.childElementCount
    userSearchUrl = teamSection.dataset.ajaxUrl

    addTeamButton.addEventListener('click', function(e) {
        addTeamForm(teamCollection)
    })

    addHandlerToButtons()
    addSearchToElements()
})

function addTeamForm(collection) {
    var prototype = collection.dataset.prototype
    var index = collection.dataset.index
    var form = prototype.replace(/__team__/g, index)
    collection.dataset.index++
    var newTeam = document.createElement('li')
    newTeam.innerHTML= form
    collection.appendChild(newTeam)

    var addMemberButton = document.createElement('button')
    addMemberButton.type = 'button'
    addMemberButton.innerText = 'Add member'
    addMemberButton.classList.add('add-team-member')
    addMemberButton.addEventListener('click', handleAddMember)


    newTeam.appendChild(addMemberButton).dataset.collection = newTeam.firstElementChild.id + '_members'
}

function handleAddMember() {
    var membersCollection = document.getElementById(this.dataset.collection)
    membersCollection.dataset.index = membersCollection.childElementCount
    addMemberForm(membersCollection)
}

function addMemberForm(collection) {
    var prototype = collection.dataset.prototype
    var index = collection.dataset.index
    var form = prototype.replace(/__member__/g, index)
    var newMember = document.createRange().createContextualFragment(form)
    collection.appendChild(newMember)
    addSearchToElements()
}

function searchForUser() {
    var q = this.value
    if (q.length > 3) {
        var url = userSearchUrl + '/' + q
        window.fetch(url, {method: 'POST'})
            .then(function(res){
                return res.json()
            })
            .then(function(json){
                console.info(json)
            })
    } else {
        return false;
    }
}

function addSearchToElements() {
    var searchElements = document.getElementsByClassName('user-search')
    if (searchElements) {
        for (var i = 0; i < searchElements.length; i++) {
            searchElements[i].addEventListener('keyup', searchForUser, false)
        }
    }
}

function addHandlerToButtons() {
    var addTeamMemberButtons = document.getElementsByClassName('add-team-member-button')
    if (addTeamMemberButtons) {
        for (var i = 0; i < addTeamMemberButtons.length; i++) {
            addTeamMemberButtons[i].addEventListener('click', handleAddMember)
        }
    }
}