<template>
    <div class="dash-component">
        <h2 class="title">Create A Matchup</h2>
        <p>Compare scores for two players side-by-side.</p>
        <div class="matchup-controls">
            <select class="matchup-select" v-model="player1">
                <option value="">Player One</option>
                <option :value="user.username">-My Scores-</option>
                <option :key="opp.id" :value="opp.username" v-for="opp in opponents">{{opp.username}}</option>
            </select>
            <select class="matchup-select" v-model="player2">
                <option value="">Player Two</option>
                <option :value="user.username">-My Scores-</option>
                <option :key="opp.id" :value="opp.username" v-for="opp in opponents">{{opp.username}}</option>
            </select>
        </div>
        <div class="matchup-result">
        <table class="matchup-scores" v-if="matchupScores.length > 0">
            <thead>
                <tr>
                    <th class="game-title">Game</th>
                    <th class="rank text-right">P1 Rank</th>
                    <th class="text-right">P1 Score</th>
                    <th class="rank text-right">P2 Rank</th>
                    <th class="text-right">P2 Score</th>
                </tr>
            </thead>
            <tbody>
                <tr :key="match.id" v-for="match in matchupScores">
                    <td>{{match.game.title}}</td>
                    <td class="text-right">{{match.p1.rank}}</td>
                    <td class="text-right">{{match.p1.points|number_format}}</td>
                    <td class="text-right">{{match.p2.rank}}</td>
                    <td class="text-right">{{match.p2.points|number_format}}</td>
                </tr>
            </tbody>
        </table>
        </div>
    </div>
</template>

<style scoped>
.matchup-controls {
    display: flex;
    justify-content: space-between;
}

.matchup-select {
    background-color: var(--color-black);
    border: 1px solid var(--color-prime);
    color: var(--color-white);
    padding: 4px;
}

.matchup-result {
    max-height: 100%;
    overflow-y: scroll;
    margin-top: var(--margin);
}

.matchup-scores {
    table-layout: fixed;
    width: 100%;
    margin-bottom: 0;
    position: relative;
    white-space: nowrap;
}

.matchup-scores .rank {
    width: 5em;
}

.matchup-scores .game-title {
    width: 30em;
}

.matchup-scores th {
    font-size: 0.6em;
    position: sticky;
    top: 0;
    z-index: 1;
}

.matchup-scores td {
    overflow: hidden;
    text-overflow: ellipsis;
}
</style>

<script>
export default {
    data() {
        return {
            player1: '',
            player2: '',
        }
    },
    props: ['user', 'opponents', 'scores'],
    computed: {
        scoresPlayer1: function() {
            return this.scores.filter(s => s.user.username === this.player1)
        },

        scoresPlayer2: function() {
            return this.scores.filter(s => s.user.username === this.player2)
        },

        commonScores: function() {
            return this.scoresPlayer1.filter(s => this.scoresPlayer2.filter(m => m.game.id === s.game.id).length >= 1)
        },

        matchupScores: function() {
            return this.commonScores.map(s => ({
                id: [this.player1, this.player2, s.game.id].join('-'),
                game: s.game,
                p1: s,
                p2: this.scoresPlayer2.find(o => o.game.id === s.game.id)
            }))
        }
    }
}
</script>