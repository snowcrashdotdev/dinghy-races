document.addEventListener('DOMContentLoaded', function(){
    let gameCollectionItems = document.getElementsByClassName('game-form-list-item')

    for (let item of gameCollectionItems) {
        item.addEventListener('click', removeGameFromCollection)
    }
})

function addGameToCollection(json,resultList) {
    let gameCollection = document.getElementById('game_collection_games')
    let gamePreviews = document.getElementById('game-form-list')

    for (let result of json) {
        let li = document.createElement('li')
        li.classList.add('ajax-search-result')
        li.classList.add('game-search-result')
        li.innerHTML = `${result.title} <small>(${result.slug}.zip)</small>`
        resultList.appendChild(li)

        li.addEventListener('click', function(e) {
            let consent = confirm(`Add the game ${result.title} to this tournament?`)

            if (!consent) { return false; }

            let gameCollectionPlaceholder = document.getElementById('game-form-no-items')

            if (gameCollectionPlaceholder) {
                gameCollectionPlaceholder.remove()
            }

            let newInput = createNewInput(gameCollection)
            gameCollection.appendChild(newInput)
            gameCollection.lastElementChild.value = result.slug

            let newPreview = document.createElement('span')
            newPreview.classList.add('game-form-list-item')
            newPreview.setAttribute('data-input-id', gameCollection.lastElementChild.id)
            newPreview.innerText = result.slug
            newPreview.addEventListener('click', removeGameFromCollection)
            gamePreviews.appendChild(newPreview)

            resultList.previousElementSibling.value = ''
        })
    }
}

function removeGameFromCollection(event) {
    let gamePreview = event.target
    let hiddenInput = document.getElementById(gamePreview.getAttribute('data-input-id'))

    if (hiddenInput) {
        let consent = confirm('Do you want to remove this game?')
        if (consent === true) {
            let gameCollection = gamePreview.parentElement
            gamePreview.remove()
            hiddenInput.value = ''

            if (gameCollection.children.length < 1) {
                let gameCollectionPlaceholder = document.createElement('p')
                gameCollectionPlaceholder.id = 'game-form-no-items'
                gameCollectionPlaceholder.innerText = 'No games selected.'
                gameCollection.appendChild(gameCollectionPlaceholder)
            }
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    let eligiblePlayersInput = document.getElementById('eligible-players')
    let eligiblePlayersData = eligiblePlayersInput ?
        eligiblePlayersInput.getAttribute('data-eligible-players')
        : null
    
    if (eligiblePlayersData) {
        let eligiblePlayers = JSON.parse(eligiblePlayersData)
        let eligilbePlayersList = document.getElementById('eligible-players-list')

        for (player of eligiblePlayers) {
            let listItem = document.createElement('span')
            listItem.id = player.username
            listItem.innerText = player.username
            listItem.classList.add('eligible-players-list-item')
            listItem.addEventListener('click', addPlayerToRoster)
            eligilbePlayersList.appendChild(listItem)
        }
    }

    let rosterListItems = document.getElementsByClassName('team-roster-list-item')

    for (let item of rosterListItems) {
        item.addEventListener('click', openRosterModal)
    }
})

function addPlayerToRoster(e) {
    let player = e.target.id
    let consent = confirm(`Add ${player} to this team?`)

    if (!consent) { return false }

    let roster = document.getElementById('team-roster-list')
    let rosterPlaceholder = document.getElementById('team-roster-placeholder')
    let rosterForm = document.getElementById('roster_members')
    let newInput = createNewInput(rosterForm)
    rosterForm.appendChild(newInput)
    rosterForm.lastElementChild.value = player

    let newRosterItem = document.createElement('span')
    newRosterItem.classList.add('team-roster-list-item')
    newRosterItem.setAttribute('data-input-id', rosterForm.lastElementChild.id)
    newRosterItem.innerText = player

    if (rosterPlaceholder) { rosterPlaceholder.remove() }

    roster.appendChild(newRosterItem)
    newRosterItem.addEventListener('click', openRosterModal)
    e.target.remove()
}

function createNewInput(collection) {
    let prototype = collection.getAttribute('data-prototype')
    let lastChild = collection.lastElementChild
    let newName = lastChild ? getNewItemIndex(lastChild) : 0
    let newMarkup = prototype.replace(new RegExp('__name__', 'g'), newName)
    return document.createRange().createContextualFragment(newMarkup)
}

function openRosterModal(e) {
    let rosterListItem = e.target
    let rosterInputId = rosterListItem.getAttribute('data-input-id')
    let rosterListInput = document.getElementById(rosterInputId)
    let player = rosterListItem.innerText
    let modal = document.getElementById('roster-modal')
    let modal_prime = modal.cloneNode(true)
    let teamId = modal.getAttribute('data-team-id')
    let token = modal.getAttribute('data-token')
    let playerName = document.getElementById('player-name')
    let playerSend = document.getElementById('player-send')
    let playerRemove = document.getElementById('player-remove')
    let teamSelect = document.getElementById('team-select')
    let buttonClose = modal.firstElementChild
    let buttonBack = buttonClose.nextElementSibling

    playerName.innerText = player
    document.body.classList.add('modal-open')
    modal.classList.remove('off-canvas')
    modal.removeAttribute('hidden')

    playerSend.addEventListener('click', function(e) {
        e.preventDefault()
        buttonBack.removeAttribute('hidden')
        this.innerText = 'send to..'
        this.style.border = 'none';
        this.style.color = 'white';
        this.setAttribute('disabled', '')
        playerRemove.setAttribute('hidden', '')
        teamSelect.removeAttribute('hidden')

        buttonBack.addEventListener('click', function(e) {
            e.preventDefault()
            playerSend.innerText = 'Send Player'
            playerSend.removeAttribute('style')
            playerSend.removeAttribute('disabled')
            playerRemove.removeAttribute('hidden')
            teamSelect.setAttribute('hidden', '')
            this.setAttribute('hidden', '')
        })

        teamSelect.addEventListener('change', function(e) {
            if (this.selectedIndex > 0) {
                let consent = confirm(`Send ${player} to the selected team?`)

                if (!consent) { return false }
                let sendUrl = `/teams/${teamId}/send/${player}/${this.value}`
                let sendForm = new FormData()
                sendForm.append('_token', token)

                window.fetch(sendUrl, {
                    method: 'POST',
                    body: sendForm,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(function(res) { return res.json() })
                .then(function(json) {
                    if (json.success) {
                        closeRosterModal()
                        rosterListItem.remove()
                        rosterListInput.value = ''
                        createFlash('success', json.message)
                    } else {
                        createFlash('error', json.message)
                    }
                })
            }
        })
    })

    playerRemove.addEventListener('click', function(e) {
        e.preventDefault()
        let consent = confirm(`Remove ${player} from team?`)
    
        if (!consent) { return false }
    
        let eligibleListItem = document.createElement('span')
        let eligiblePlayersList = document.getElementById('eligible-players-list')
        eligibleListItem.id = player
        eligibleListItem.innerText = player
        eligibleListItem.addEventListener('click', addPlayerToRoster)
        eligibleListItem.classList.add('eligible-players-list-item')
    
        rosterListInput.value = ''
        rosterListItem.remove()
        eligiblePlayersList.appendChild(eligibleListItem)
        closeRosterModal()
    })

    buttonClose.addEventListener('click', closeRosterModal)

    function closeRosterModal() {
        modal.remove()
        document.body.classList.remove('modal-open')
        document.body.appendChild(modal_prime)
    }
}

function getNewItemIndex(el) {
    return Number(
        el.id.split('_').slice(-1)
    ) + 1
}

document.addEventListener('DOMContentLoaded', function() {
    let ajaxForms = document.getElementsByClassName('ajax-form')

    for (let form of ajaxForms) {
        form.addEventListener('submit', function(e) {
            e.preventDefault()
            const formData = new FormData(this)

            window.fetch(this.action, {
                method: this.method,
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(json => {
                if (json.success) {
                    createFlash('success', json.message)
                } else {
                    createFlash('error', json.message)
                }
            })
        })
    }
})

document.addEventListener('DOMContentLoaded', function() {
    let removeDraftEntryLinks = document.getElementsByClassName('remove-draft-entry')
    let draftEntryCount = document.getElementById('entry-count')

    for (let link of removeDraftEntryLinks) {
        let entryId = link.getAttribute('data-id')
        let token = link.getAttribute('data-token')
        let formData = new FormData()
        formData.append('_method', 'DELETE')
        formData.append('_token', token)
        let action = `/entry/${entryId}`

        link.addEventListener('click', function(e) {
            e.preventDefault()
            let consent = confirm('Remove draft entry?')

            if (!consent) { return false }

            window.fetch(action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(json => {
                if (json.success) {
                    createFlash('success', json.message)
                    link.parentElement.parentElement.remove()
                    draftEntryCount.innerText -= 1
                } else {
                    createFlash('error', json.message)
                }
            })
        })
    }
})

document.addEventListener('DOMContentLoaded', function() {
    let scoringRefresh = document.getElementById('scoring-refresh')
    if (scoringRefresh) {
        let tournament = scoringRefresh.getAttribute('data-tournament')
        let token = scoringRefresh.getAttribute('data-token')
        let formData = new FormData()
        formData.append('_token', token)
        let action = `/tournament/scoring/${tournament}/refresh`

        scoringRefresh.addEventListener('click', function(e) {
            e.preventDefault();
            window.fetch(action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(json => {
                if (json.success) {
                    createFlash('success', json.message)
                } else {
                    createFlash('error', json.message)
                }
            })
        })
    }
})


function createFlash(key, message) {
    let flashBag = document.getElementById('flash-bag')
    let div = document.createElement('div')
    div.classList.add('flash')
    div.classList.add('flash-' + key)
    div.innerText = message
    flashBag.appendChild(div)
    showFlashBag()
    fadeFlashBag()

    function showFlashBag() {
        flashBag.removeAttribute('hidden')
        flashBag.classList.remove('fade')
    }
    
    function fadeFlashBag() {
        if (flashBag.childElementCount > 0) {
            setTimeout(function(){
                flashBag.classList.add('fade')
                setTimeout(function(){
                    removeAllChildren(flashBag)
                    flashBag.setAttribute('hidden', '')
                }, 1000)
            }, 6 * 1000)
        }
    }
}

function removeAllChildren(el) {
    while(el.hasChildNodes()) {
        el.removeChild(el.lastChild)
    }
}

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
                    let data = JSON.parse(json)
                    console.log(data)

                    switch (cb) {
                        case 'addGameToCollection': addGameToCollection(data, ajaxSearchResultsList)
                        break
                    }
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
