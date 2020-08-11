import Vue from 'vue';
import Dashboard from './components/Dashboard'

const dash = new Vue({
    el: '#dash',
    render: h => h(Dashboard)
})

Vue.filter('number_format', function(n) {
    return Number(n).toLocaleString('en-us', {maximumFractionDigits: 0})
})