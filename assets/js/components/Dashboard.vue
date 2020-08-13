<template>
    <div class="dashboard">
        <matchup
            v-bind:scores="scores"
            v-bind:user="user"
            v-bind:opponents="opponents">
        </matchup>

        <suggested-games
            v-bind:scores="scores"
            v-bind:user="user"
            v-bind:stddev="stddev">
        </suggested-games>

        <rivals v-bind:rivals="rivals"></rivals>
    </div>
</template>

<style scoped>
.dashboard {
    padding: 8px;
    margin: 1.6rem 0;
}
</style>

<script>
import matchup from './Matchup'
import suggestedGames from './SuggestedGames'
import rivals from './Rivals'

export default {
    data() {
        return {
            user: {
                username: '',
                scores: []
            },
            scores: [],
            stddev: [],
            rivals: []
        }
    },

    computed: {
        opponents: function() {
            function sortByUsername(a,b) {
                return a.username.localeCompare(b.username)
            }

            function removeDuplicates(array, key) {
                let lookup = {};
                let result = [];
                for (let i=0; i<array.length; i++) {
                    if (!lookup[array[i][key]]) {
                        lookup[array[i][key]] = true;
                        result.push(array[i]);
                    }
                }
                return result;
            }

            let users = this.scores.map(s => s.user).filter(u => u.username !== this.user.username).sort(sortByUsername)

            return removeDuplicates(users, 'id')
        }
    },

    components: {
        'matchup': matchup,
        'suggested-games': suggestedGames,
        'rivals': rivals
    },

    mounted() {
        function sortByGame(a,b) {
            return a.game.title.localeCompare(b.game.title)
        }
    
        let data = JSON.parse(document.querySelector("aside[data-dashboard]").dataset.dashboard)

        for (let prop in data) {
            this[prop] = data[prop]
        }

        this.user.scores.sort(sortByGame)
        this.scores.sort(sortByGame)
    }
}
</script>