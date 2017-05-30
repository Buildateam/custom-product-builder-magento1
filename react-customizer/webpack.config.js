module.exports = {
  context: __dirname + "/app",

  debug:true,
  resolve: {
    extensions:  [
      "", ".webpack.js", ".web.js", ".js", ".jsx"
    ]
  },

  entry: {
    javascript: "./app.jsx",
    html: "./index.html",
  },

  output: {
    path: __dirname + "/dist",
    filename: "app.js"
  },

  module: {
    loaders: [
      {
        test: /\.(js|jsx)$/,
        loader: 'babel',

      },
      {
        test: /\.html$/,
        loader: "file?name=[name].[ext]",
      },
      { test: /\.css$/,
        loader: "style!css"
      }
    ]
  }
};
