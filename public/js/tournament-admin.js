window.addEventListener('DOMContentLoaded', function(){
    document.querySelector('.add-team-to-tournament').addEventListener('click', function(e) {
        var teamList = document.querySelector(e.target.dataset.listSelector)
        var newTeam = teamList.dataset.prototype
        var i = teamList.childElementCount
        newTeam = newTeam.replace(/__name__/g, i)
        i++
        teamList.dataset.widgetCounter = i
        var newListItem = document.createElement(teamList.dataset.tag)
        newListItem.innerHTML = newTeam
        teamList.append(newListItem)
    })
})