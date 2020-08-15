<template>
    <div class="stream">
        <article class="gadget my-scores">
            <h3>High Scores for {{user.username}}</h3>
            <ul class="score-list">
                <li class="score"
                    :key="score.id"
                    v-for="score in user_scores">
                    <span class="score-rank">{{score.rank}}</span>
                    <span class="score-game">{{score.game.slug}}</span>
                    <span class="score-points">{{score.points|number_format}}</span>
                </li>
            </ul>
        </article>

        <article class="gadget my-stats">
            <h3>{{user.username}} Tournament Stats</h3>
            <ul class="stats-list">
                <li class="stat">
                    <span class="stat-label">Place</span>
                    <span class="stat-value">{{place|ordinal}}</span>
                </li>
                <li class="stat">
                    <span class="stat-label">Ranked Points</span>
                    <span class="stat-value">{{user.ranked_points|number_format}}</span>
                </li>
                <li class="stat">
                    <span class="stat-label">Avg Score Rank</span>
                    <span class="stat-value">{{user.avg_rank|number_format}}</span>
                </li>
                <li class="stat">
                    <span class="stat-label">Score Submissions</span>
                    <span class="stat-value">{{user.completion|number_format}}</span>
                </li>
            </ul>
        </article>

        <article class="gadget recent-scores">
            <h3>Recent Scores</h3>
            <ul class="recent-score-list">
                <li class="recent-score" :key="score.id" v-for="score in recent_scores">
                    <div class="recent-score-details">
                    <span class="recent-score-game">{{score.game.slug}}</span>
                    <span class="recent-score-points">{{score.points|number_format}}</span>
                    </div>
                    <div class="recent-score-submitted-by">
                        <span class="recent-score-points">{{score.user.username}}</span>
                        <span class="recent-score-date">{{score.updated_at|date_diff}}</span>
                    </div>
                </li>
            </ul>
        </article>
    </div>
</template>

<style>
@import url('https://fonts.googleapis.com/css?family=Montserrat:500|Roboto:900&display=swap');
@import '../../css/partials/variables.css';
@import '../../css/partials/typography.css';

:root {
    font-size: 14px;
}

body {
    margin: 0;
    padding: 0;
    background-color: transparent;
}

.stream {
    width: 100vw;
    height: 100vh;
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
}

.gadget {
    display: block;
    box-sizing: border-box;
    flex: 0 1 0;
    line-height: 1.25;
    margin: 0;
    padding-top: calc(var(--margin-large) + 4px);
    padding-left: var(--padding);
    padding-right: var(--padding);
    padding-bottom: var(--padding);
    position: relative;
    background-image: var(--gradient-gadget);
}

.gadget :--heading {
    position: absolute;
    top: 0;
    left: 0;
    line-height: var(--margin-large);
    margin: 0;
    padding-left: calc(var(--margin) / 2);
    background-color: var(--color-gray);
    border-left: 0.3em solid var(--color-three);    
    color: var(--color-white);
    text-shadow: var(--outlined);
    text-transform: uppercase;

    &::after {
        display: block;
        content: '';
        position: absolute;
        top: 0;
        right: calc(var(--margin-large) * -1);
        width: var(--margin-large);
        height: var(--margin-large);
        background-image: linear-gradient(315deg, transparent 50%, var(--color-gray) 51%);
        background-repeat: no-repeat;
        background-size: cover;
    }
}

.score {
    display: flex;
}

.my-scores {
    flex-grow: 1;
}

.recent-score {
    display: flex;
    margin-bottom: var(--padding);
}

.recent-score span {
    display: block;
}

.recent-score-submitted-by {
    flex: 1;
    text-align: right;
}

.recent-score-points {
    color: var(--color-white);
    text-shadow: var(--outlined);
}

.recent-score-game {
    color: var(--color-two);
}

.recent-score-user {
    color: var(--color-three);
}

.recent-score-date {
    font-size: var(--size-small);
}

.score-rank,
.stat-label,
.team-name {
    color: var(--color-three);
    margin-right: var(--padding);
}

.team-name {
    color: var(--color-two);
}

.score-rank {
    width: 2em;
}

.score-rank::before {
    content: '#'
}

.score-game,
.team-name,
.stat-label {
    flex-grow: 1;
}

.top-player {
    text-align: center;
}

.top-player-name {
    color: var(--color-white);
    font-size: var(--size-large);
    text-shadow: var(--outlined);
}

.top-player-team {
    color: var(--color-two);
}

.top-player-points {
    font-family: 'Roboto', sans-serif;
    font-size: var(--size-larger);
    text-shadow: var(--outlined);
    margin-bottom: var(--margin);
}
</style>

<script>
import 'whatwg-fetch'

export default {
    data() {
        return {
            endpoint: '',
            user: {},
            scores: [],
            place: 0,
            recent_scores: []
        }
    },
    computed: {
        user_scores: function () {
            return this.scores.slice(0,20)
        }
    },
    methods: {
        fetch: function () {
            let app = this
            let options = {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }
            window.fetch(this.endpoint, options)
                .then(r => r.json())
                .then(function(json) {
                    for (let prop in json) {
                        app[prop] = json[prop]
                    }
                })
            }
        },
        mounted: function () {
            let app = this
            this.endpoint = document.body.dataset.endpoint
            this.fetch()
            setInterval(app.fetch, 1000 * 60)
        },
        filters: {
            number_format: function (value) {
                return Number(value).toLocaleString('en-us', {maximumFractionDigits: 0})
            },
            date_diff: function (date) {
                let delta = Math.round((new Date() - new Date(date)) / 1000)
                let minute = 60
                let hour = minute * 60

                if (delta < minute) {
                    return delta + ' seconds ago'
                } else if (delta < 2 * minute) {
                    return 'a minute ago'
                } else if (delta < hour) {
                    return Math.floor(delta / minute) + ' minutes ago'
                } else if (Math.floor(delta / hour) === 1) {
                    return 'an hour ago'
                } else {
                    return Math.floor(delta / hour) + ' hours ago'
                }
            },
            ordinal: function (n) {
                var s = ["th", "st", "nd", "rd"],
                    v = n % 100;
                return n + (s[(v - 20) % 10] || s[v] || s[0]);
            }
    }
}
</script>