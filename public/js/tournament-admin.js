var teamCollection
var gameCollection
var teamSection
var addTeamButton = document.createElement('button')
addTeamButton.type = 'button'
addTeamButton.innerText = 'Add team'
addTeamButton.classList.add('add-team-member-button')
var userSearchUrl
var gameSearchUrl

window.addEventListener('DOMContentLoaded', function(){
    teamCollection = document.querySelector('#teams-collection')
    teamSection = document.querySelector('#teams')
    teamSection.appendChild(addTeamButton)
    teamCollection.dataset.index = teamCollection.childElementCount
    userSearchUrl = teamSection.dataset.ajaxUrl

    gameCollection = document.getElementById('games-collection')
    gameCollection.dataset.index = gameCollection.childElementCount
    gameSearchUrl = gameCollection.dataset.ajaxUrl

    addGameButton = document.getElementById('add-game-to-tournament')
    addGameButton.addEventListener('click', function(e) {
        addGameForm(gameCollection)
    })

    addTeamButton.addEventListener('click', function(e) {
        addTeamForm(teamCollection)
    })

    addHandlerToButtons()
    addSearchToElements()
})

function addGameForm(collection) {
    var prototype = collection.dataset.prototype
    var index = collection.dataset.index
    var form = prototype.replace(/__game__/g, index)
    collection.dataset.index++
    var newGame = document.createElement('li')
    newGame.innerHTML = form
    collection.appendChild(newGame)
    addSearchToElements()
}

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
        return true;
    }
}

/** NOT VERY DRY */
function searchForGame() {
    var q = this.value
    if (q.length > 3) {
        var url = gameSearchUrl + '/' + q
        window.fetch(url, {method:'POST'})
        .then(function(res){
            return res.json()
        })
        .then(function(json){
            console.info(json)
        })
    } else {
        return true
    }
}

function addSearchToElements() {
    var searchElements = document.getElementsByClassName('user-search')
    if (searchElements) {
        for (var i = 0; i < searchElements.length; i++) {
            searchElements[i].addEventListener('keyup', searchForUser, false)
        }
    }

    var gameSearchElements = document.getElementsByClassName('game-search')
    if (gameSearchElements) {
        for (var i = 0; i < gameSearchElements.length; i++) {
            gameSearchElements[i].addEventListener('keyup', searchForGame, false)
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