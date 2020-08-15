<template>
    <div class="dashboard">
        <matchup class="matchup"
            v-bind:scores="scores"
            v-bind:user="user"
            v-bind:opponents="opponents">
        </matchup>

        <suggested-games class="suggested-games"
            v-bind:scores="scores"
            v-bind:user="user"
            v-bind:stddev="stddev">
        </suggested-games>

        <rivals class="rivals" v-bind:rivals="rivals"></rivals>
    </div>
</template>

<style scoped>
@media (min-width: 74em) {
    .dashboard {
        display: grid;
        grid-auto-columns: 1fr;
        grid-template-areas:
            "t t t s1 s1"
            "t t t s2 s2";
        grid-auto-rows: 16rem;
        grid-gap: var(--padding);
    }

    .matchup {
        grid-area: t;
    }

    .suggested-games {
        grid-area: s2;
    }

    .rivals {
        grid-area: s1;
    }
}

.dashboard {
    grid-gap: calc( var(--margin) / 2 );
}

.dashboard >>> h2 {
    color: var(--color-two);
    font-size: 1.1em;
    text-shadow: var(--outlined);
    text-transform: uppercase;
}

.dashboard >>> p {
    font-size: 0.8em;
    margin-bottom: var(--margin);
}

.dashboard .dash-component {
    border: 1px solid var(--color-gray);
    border-radius: 0.1em;
    box-shadow: 0.1em 0.2em 0.2em #000;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    padding: 0.8rem;
}

.dashboard >>> .range {
    background: transparent;
    max-width: 100% ;
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