<template>
    <div class="dash-component">
        <h2>Rivals</h2>
        <p>See players who have ranked similar to you.</p>
        <label for="similarity">Similarity</label>
        <input class="range" type="range" name="similarity" v-model="similarity" min="0" max="100" step="1">

        <div class="rivals">
            <ul>
                <li :key="rival.username" v-for="rival in rivalsBySimilarity">{{rival.username}}</li>
            </ul>
        </div>
    </div>
</template>

<style scoped>
.rivals {
    overflow-x: hidden;
    overflow-y: scroll;
}

ul {
    max-height: 100%;
}

.range::-moz-range-track {
    background: linear-gradient(to right, var(--color-danger), var(--color-three));
}

.range::-ms-track {
    background: linear-gradient(to right, var(--color-danger), var(--color-three));
}

.range::-webkit-slider-runnable-track {
    background: linear-gradient(to right, var(--color-danger), var(--color-three));
}
</style>

<script>
export default {
    data() {
        return {
            similarity: 100
        }
    },
    props: ['rivals'],
    computed: {
        rivalsBySimilarity() {
            let count = this.rivals.length
            let slice = 4
            let i = Math.floor((count * 100 - count * this.similarity) / 100);

            return this.rivals.slice(i, i+slice)
        }
    }
}
</script>