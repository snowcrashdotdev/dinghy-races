module.exports = {
    plugins: [
        require('postcss-import')({from:'public/css/style.css'}),
        require('postcss-preset-env')({stage: 0, browsers: '> 3%'}),
        require('cssnano')({preset: 'advanced'})
    ]
}