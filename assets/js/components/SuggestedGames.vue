<template>
    <div class="dash-component">
        <h2>Suggested Games</h2>
        <p>Find games where you have a good chance of ranking up.</p>
        <label for="difficulty">Difficulty</label>
        <input class="range" type="range" name="difficulty" v-model="difficulty" min="0" max="100" step="1">

        <div class="suggestions">
            <ul>
                <li :key="suggestion.game" v-for="suggestion in suggestions">{{suggestion.title}}</li>
            </ul>
        </div>
    </div>
</template>

<style scoped>
.suggestions {
    overflow: hidden;
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
            difficulty: 0
        }
    },
    props: ['user', 'scores', 'stddev'],
    computed: {
        userStdDevs: function() {
            let scores = this.scores.filter(s => this.user.username !== s.user.username).sort((s1, s2) => s1.points - s2.points)
            
            return this.user.scores.map(s => {
                let sNext = scores.find(n => n.game.id === s.game.id && n.points > s.points)
                let stddev = this.stddev.find(d => s.game.title === d.title).stddev

                return {
                    title: s.game.title,
                    stddev: Math.abs(sNext.points - s.points) / stddev
                }
            })
            .sort((d1,d2) => d1.stddev - d2.stddev)
        },
        suggestions: function() {
            let count = this.userStdDevs.length
            let slice = 4
            let offset = 100 - this.difficulty
            let i = Math.floor((count * 100 - count * offset) / 100);
            return this.userStdDevs.slice(i,i+slice)
        }
    }
}
</script>