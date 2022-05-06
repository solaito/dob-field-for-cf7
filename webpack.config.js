module.exports = {
    entry: {
        'dest/includes/js/auto-validation': './includes/js/auto-validation.js',
        'dest/includes/js/zenkaku-to-hankaku': './includes/js/zenkaku-to-hankaku.js',
    },
    output: {
        path: __dirname,
        filename: '[name].js',
    },
    target: ['web', 'es5'],
    module: {
        rules: [
            {
                // 拡張子 .js の場合
                test: /\.js$/,
                // node_modulesは対象外にしておく
                exclude: /node_modules/,
                use:
                {
                    // Babel を利用する
                    loader: 'babel-loader',
                    // Babel のオプションを指定する
                    options: {
                        presets: [
                            // プリセットを指定することで、ES2019 を ES5 に変換
                            [
                                '@babel/preset-env',
                                {
                                    useBuiltIns: 'usage',
                                    corejs: 3
                                }
                            ]
                        ]
                    }
                }
            },
        ]
    }
}