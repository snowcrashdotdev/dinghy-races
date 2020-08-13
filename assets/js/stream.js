import Vue from 'vue'
import Stream from './components/Stream'

document.addEventListener('DOMContentLoaded', function() {
    const streamKit = new Vue({
        el: '#stream-kit',
        render: h => h(Stream)
    })
})