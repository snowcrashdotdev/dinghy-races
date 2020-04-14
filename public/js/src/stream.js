import 'whatwg-fetch'
import Vue from 'vue/dist/vue.common.prod'

document.addEventListener('DOMContentLoaded', function() {
    const endpoint = document.body.dataset.endpoint
    const streamKit = new Vue({
        el: '#stream-kit',
        data: {
            scores: [],
            recent_scores: [],
            stats: {},
            teamScores: [],
            topPlayer: {}
        },
        methods: {
            fetch: function () {
                let app = this
                let options = {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                }
                window.fetch(endpoint, options)
                    .then(function(res) {
                        return res.json()
                    })
                    .then(function(json) {
                        let result = JSON.parse(json)
                        for (let prop in result) {
                            app[prop] = result[prop]
                        }
                    })
            },
            fadeScores: function () {
                let self = this
                this.$refs.recentScores.classList.toggle('fade');
                setTimeout(function() {
                    this.$refs.recentScores.classList.toggle('fade');
                }.bind(self), 1000 * 30)
            }
        },
        mounted: function () {
            this.fetch()
        },
        updated: function () {
            this.$refs.recentScores.style.height = this.$refs.myScores.offsetHeight + 'px'
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
            }
        }
    })

    setInterval(streamKit.fetch, 1000 * 60)
    setInterval(streamKit.fadeScores, 1000 * 60 * 5)
})