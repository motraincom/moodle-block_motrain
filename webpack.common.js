const webpack = require('webpack');
const path = require('path');

module.exports = {
    target: ['web', 'es5'],
    entry: {
        'ui-completion-rules': './ui/completion-rules/index.tsx',
        'ui-program-rules': './ui/program-rules/index.tsx',
    },
    output: {
        filename: '[name]-lazy.js',
        path: path.resolve(__dirname, './amd/src'),
        libraryTarget: 'amd',
    },
    module: {
        rules: [
            {
                test: /\.tsx?$/,
                use: 'ts-loader',
                exclude: /node_modules/,
            },
        ],
    },
    resolve: {
        extensions: ['.tsx', '.ts', '.js'],
    },
    plugins: [
    // Without this, Moodle prevents grunt from compiling the file.
        new webpack.BannerPlugin({
            banner: '/* eslint-disable */\n/* Do not edit directly, refer to ui/ folder. */',
            raw: true,
            entryOnly: true,
        }),
    ],
};
