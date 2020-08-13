<template>
    <div>
        <h2>Rivals</h2>
        <p>Players with similar performance</p>
        <ul>
            <li :key="user.username" v-for="user in rivals">{{user.username}}</li>
        </ul>
    </div>
</template>

<script>
export default {
    props: ['rivals']
}
</script>