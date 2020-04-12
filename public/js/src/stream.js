import 'whatwg-fetch'
import Vue from 'vue/dist/vue.common.prod'

document.addEventListener('DOMContentLoaded', function() {
    const endpoint = document.body.dataset.endpoint
    const streamKit = new Vue({
        el: '#stream-kit',
        data: {
            scores: [],
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
            }
        },
        mounted: function () {
            this.fetch()
        },
        filters: {
            number_format: function (value) {
                return Number(value).toLocaleString('en-us', {maximumFractionDigits: 0})
            }
        }
    })

    setInterval(streamKit.fetch, 1000 * 60)
})