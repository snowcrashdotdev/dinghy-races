<template>
    <div>
        <h2>Suggested Games</h2>
        <label for="difficulty">Difficulty</label>
        <input class="difficulty" type="range" name="difficulty" v-model="difficulty" min="0.01" max="0.25" step="0.005">
        <ul>
            <li :key="suggestion.game" v-for="suggestion in suggestions">{{suggestion.title}}</li>
        </ul>
    </div>
</template>

<style scoped>
.difficulty {
    background: transparent;
}

.difficulty::-moz-range-track {
    background: var(--color-three);
}

.difficulty::-ms-track {
    background: var(--color-three);
}

.difficulty::-webkit-slider-runnable-track {
    background: var(--color-three);
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

            return this.stddev.filter(d => deviations.some(d2 => d2.title === d.title && (d2.dev / d.stddev) <= this.difficulty)).sort((d,d2) => d.stddev - d2.stddev)
        }
    }
}
</script>