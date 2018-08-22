const autoprefixer = require('autoprefixer');
const postcssPxtorem = require('postcss-pxtorem');
const cssMqpacker = require('css-mqpacker');

module.exports = {
  plugins: [
    autoprefixer({
      grid: true,
      browsers: ['last 2 versions', 'ie >= 10', 'Safari >= 10']
    }),
    postcssPxtorem({
      propList: ['*', '!border*'],
      mediaQuery: true
    }),
    cssMqpacker()
  ]
};
