const defaultConfig = require('@wordpress/scripts/config/webpack.config');
const path = require('path');

module.exports = {
    ...defaultConfig,
    entry: {
        'oort-editor': path.resolve(__dirname, 'assets/src/editor.js'),
        'oort-dashboard': path.resolve(__dirname, 'assets/src/dashboard.js'),
    },
    output: {
        path: path.resolve(__dirname, 'assets/dist'),
        filename: '[name].js',
    },
};
