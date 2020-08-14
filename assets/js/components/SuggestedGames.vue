<template>
    <div class="dash-component">
        <h2>Suggested Games</h2>
        <p>Find games where you have a good chance of ranking up.</p>
        <label for="difficulty">Difficulty</label>
        <input class="range" type="range" name="difficulty" v-model="difficulty" min="0.01" max="0.25" step="0.005">

        <div class="suggestions">
            <ul>
                <li :key="suggestion.game" v-for="suggestion in suggestions">{{suggestion.title}}</li>
            </ul>
        </div>
    </div>
</template>

<style scoped>
.suggestions {
    overflow-x: hidden;
    overflow-y: scroll;
}

ul {
    max-height: 100%;
}

.range::-moz-range-track {
    background: linear-gradient(to left, var(--color-danger), var(--color-three));
}

.range::-ms-track {
    background: linear-gradient(to left, var(--color-danger), var(--color-three));
}

.range::-webkit-slider-runnable-track {
    background: linear-gradient(to left, var(--color-danger), var(--color-three));
}
</style>

<script>
export default {
    name: 'suggested-games',
    data() {
        return {
            difficulty: 0.01
        }
    },
    props: ['user', 'scores', 'stddev'],
    computed: {
        suggestions: function() {
            let deviations = this.scores.filter(s => this.user.scores.filter(s2 => s2.game.id === s.game.id && s.points > s2.points).length >= 1 && this.user.username !== s.user.username).map(s => ({
                title: s.game.title,
                dev: Math.abs(s.points - this.user.scores.find(s2 => s2.game.id === s.game.id).points)
            }))

            return this.stddev.filter(d => deviations.some(d2 => d2.title === d.title && (d2.dev / d.stddev) <= this.difficulty)).sort((d,d2) => d.stddev - d2.stddev).slice(-4)
        }
    }
}
</script>