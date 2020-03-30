document.addEventListener('DOMContentLoaded', function(){
    let gameCollectionItems = document.getElementsByClassName('game-form-list-item')

    for (let item of gameCollectionItems) {
        item.addEventListener('click', removeGameFromCollection)
    }
})

function addGameToCollection(json,resultList) {
    let gameCollection = document.getElementById('game_collection_games')
    let prototype = gameCollection.getAttribute('data-prototype')
    let gamePreviews = document.getElementById('game-form-list')

    for (let result of json) {
        let li = document.createElement('li')
        li.classList.add('ajax-search-result')
        li.classList.add('game-search-result')
        li.innerHTML = `${result.description} <small>(${result.name}.zip)</small>`
        resultList.appendChild(li)

        li.addEventListener('click', function(e) {
            let consent = confirm(`Add the game ${result.description} to this tournament?`)

            if (!consent) { return false; }

            let gameCollectionPlaceholder = document.getElementById('game-form-no-items')

            if (gameCollectionPlaceholder) {
                gameCollectionPlaceholder.remove()
            }

            let lastGame = gameCollection.lastElementChild
            let name = lastGame ? getNewGameIndex(lastGame) : 0
            let newMarkup = prototype.replace(new RegExp('__name__', 'g'), name)
            let newInput = document.createRange().createContextualFragment(newMarkup)
            gameCollection.appendChild(newInput)
            gameCollection.lastElementChild.value = result.name

            let newPreview = document.createElement('span')
            newPreview.classList.add('game-form-list-item')
            newPreview.setAttribute('data-input-id', gameCollection.lastElementChild.id)
            newPreview.innerText = result.name
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
            hiddenInput.value = null

            if (gameCollection.children.length < 1) {
                let gameCollectionPlaceholder = document.createElement('p')
                gameCollectionPlaceholder.id = 'game-form-no-items'
                gameCollectionPlaceholder.innerText = 'No games selected.'
                gameCollection.appendChild(gameCollectionPlaceholder)
            }
        }
    }
}

function getNewGameIndex(el) {
    return Number(
        el.id.split('_').slice(-1)
    ) + 1
}