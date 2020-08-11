<template>
    <div>
        <h2>Head-To-Head Matchup</h2>
        <select class="matchup-select" v-model="matchup">
            <option value="">Choose an opponent</option>
            <option :key="user.id" v-bind:value="user.username" v-for="user in opponents">{{user.username}}</option>
        </select>
        <table class="matchup-scores" v-if="matchupScores.length > 0">
            <thead>
                <tr>
                    <th>Game</th>
                    <th class="text-right">Your Rank</th>
                    <th class="text-right">Your Score</th>
                    <th class="text-right">Opponent Rank</th>
                    <th class="text-right">Opponent Score</th>
                </tr>
            </thead>
            <tbody>
                <tr :key="match.id" v-for="match in matchupScores">
                    <td>{{match.game.title}}</td>
                    <td class="text-right">{{match.user.rank}}</td>
                    <td class="text-right">{{match.user.points|number_format}}</td>
                    <td class="text-right">{{match.opponent.rank}}</td>
                    <td class="text-right">{{match.opponent.points|number_format}}</td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<style scoped>
.matchup-select {
    background-color: var(--color-black);
    border: 1px solid var(--color-gray);
    border-radius: 2px;
    box-shadow: var(--outlined);
    color: var(--color-white);
    display: block;
    width: 13.6em;
    height: 2.4em;
    max-width: 100%;
    margin-bottom: 1rem;
}

.matchup-select option {
    line-height: 1.3;
}

.matchup-scores {
    display: inline-block;
    height: 400px;
    overflow-y: scroll;
}
</style>

<script>
export default {
    data() {
        return {
            matchup: ''
        }
    },
    props: ['user', 'opponents', 'scores'],
    computed: {
        opponentScores: function() {
            return this.scores.filter(s => s.user.username === this.matchup && this.user.scores.filter(u => u.game.id === s.game.id).length >= 1)
        },

        commonScores: function() {
            return this.user.scores.filter(s => this.opponentScores.filter(m => m.game.id === s.game.id).length >= 1)
        },

        matchupScores: function() {
            return this.commonScores.map((s,i)=> ({
                id: [this.user.id, this.matchup, s.game.id].join('-'),
                game: s.game,
                user: s,
                opponent: this.opponentScores.find(o => o.game.id === s.game.id)
            }))
        }
    }
}
</script>