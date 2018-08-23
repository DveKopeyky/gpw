const gulp = require('gulp');
const sass = require('gulp-sass');
const postcss = require('gulp-postcss');
const sourcemaps = require('gulp-sourcemaps');
const babel = require('gulp-babel');
const concat = require('gulp-concat');
const uglify = require('gulp-uglify');
const pump = require('pump');

const autoprefixer = require('autoprefixer');
const cssnano = require('cssnano');

gulp.task('bootstrap-sass', (cb) => {
  pump([
    gulp.src('./src/bootstrap/style.scss', {
      base: './src/*'
    }),
    sourcemaps.init(),
    sass(),
    sourcemaps.write('.'),
    gulp.dest('./dist/bootstrap'),
  ], cb);
});

gulp.task('bootstrap-js', (cb) => {
  pump([
    gulp.src([
      './bootstrap/assets/javascripts/bootstrap/transition.js',
      './bootstrap/assets/javascripts/bootstrap/alert.js',
      './bootstrap/assets/javascripts/bootstrap/button.js',
      './bootstrap/assets/javascripts/bootstrap/carousel.js',
      './bootstrap/assets/javascripts/bootstrap/collapse.js',
      './bootstrap/assets/javascripts/bootstrap/dropdown.js',
      './bootstrap/assets/javascripts/bootstrap/modal.js',
      './bootstrap/assets/javascripts/bootstrap/tab.js',
      './bootstrap/assets/javascripts/bootstrap/affix.js',
      './bootstrap/assets/javascripts/bootstrap/scrollspy.js',
      './bootstrap/assets/javascripts/bootstrap/tooltip.js',
      './bootstrap/assets/javascripts/bootstrap/popover.js'
    ]),
    babel(),
    concat('bootstrap.js'),
    uglify(),
    gulp.dest('./dist/bootstrap')
  ], cb);
});

gulp.task('components-sass', (cb) => {
  pump([
    gulp.src(['./src/components/*/*.scss'], {
      base: './src/components'
    }),
    sourcemaps.init(),
    sass(),
    sourcemaps.write('.'),
    gulp.dest('./dist/components'),
  ], cb);
});

gulp.task('components-js', (cb) => {
  pump([
    gulp.src('./src/components/**/*.js', {
      base: './src/components'
    }),
    sourcemaps.init(),
    babel(),
    sourcemaps.write('.'),
    gulp.dest('./dist/components')
  ], cb);
});

gulp.task('postcss', (cb) => {
  var plugins = [
    autoprefixer({
      browsers: ['last 1 version']
    }),
    cssnano()
  ];
  pump([
    gulp.src('./dist/**/*.css'),
    postcss(plugins),
    gulp.dest('./dist')
  ], cb);
});

gulp.task('compress', (cb) => {
  pump([
    gulp.src('./dist/**/*.js'),
    uglify(),
    gulp.dest('./dist')
  ], cb);
});

gulp.task('watch', () => {
  gulp.watch(['./src/bootstrap/**/*.scss'], gulp.series('bootstrap-sass'));
  gulp.watch(['./src/components/**/*.scss'], gulp.series('components-sass'));
  gulp.watch(['./src/components/**/*.js'], gulp.series('components-js'));
});

gulp.task('bootstrap', gulp.series('bootstrap-sass', 'bootstrap-js'));

gulp.task('components', gulp.series('components-sass', 'components-js'));

gulp.task('default', gulp.series('bootstrap', 'components', 'watch'));

gulp.task('build', gulp.series('bootstrap', 'components', 'postcss', 'compress'));
