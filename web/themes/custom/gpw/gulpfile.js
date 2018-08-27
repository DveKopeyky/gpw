const gulp = require('gulp');
const sass = require('gulp-sass');
const sassImportOnce = require('gulp-sass-import-once');
const postcss = require('gulp-postcss');
const postcssPresetEnv = require('postcss-preset-env');
const sourcemaps = require('gulp-sourcemaps');
const babel = require('gulp-babel');
const concat = require('gulp-concat');
const uglify = require('gulp-uglify');
const imagemin = require('gulp-imagemin');

const pump = require('pump');
const cache = require('gulp-cached');

const cssnano = require('cssnano');

const plugins = [
  postcssPresetEnv(),
  cssnano()
];

gulp.task('css', (cb) => {
  pump([
    gulp.src(['./src/**/*.scss', '!./src/bootstrap-scss/**/*.scss']),
    sourcemaps.init(),
    sass(),
    postcss(plugins),
    sourcemaps.write('.'),
    gulp.dest('./dist'),
  ], cb);
});

gulp.task('js', (cb) => {
  pump([
    gulp.src('./src/components/**/*.js', {
      base: './src/components'
    }),
    sourcemaps.init(),
    babel(),
    uglify(),
    sourcemaps.write('.'),
    gulp.dest('./dist/components')
  ], cb);
});

gulp.task('bootstrap:css', (cb) => {
  pump([
    gulp.src('./src/bootstrap-scss/style.scss', {
      base: './src/bootstrap-scss'
    }),
    sourcemaps.init(),
    sass(),
    postcss(plugins),
    sourcemaps.write('.'),
    gulp.dest('./dist/bootstrap'),
  ], cb);
});

gulp.task('bootstrap:js', (cb) => {
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

// gulp.task('components:css', (cb) => {
//   pump([
//     gulp.src(['./src/components/*/*.scss'], {
//       base: './src/components'
//     }),
//     sourcemaps.init(),
//     sass(),
//     postcss(plugins),
//     sourcemaps.write('.'),
//     gulp.dest('./dist/components'),
//   ], cb);
// });


gulp.task('postcss', (cb) => {
  pump([
    gulp.src('./dist/**/*.css'),
    cache('postcss'),
    postcss(plugins),
    gulp.dest('./dist')
  ], cb);
});

gulp.task('uglify', (cb) => {
  pump([
    gulp.src('./dist/**/*.js'),
    cache('uglify'),
    uglify(),
    gulp.dest('./dist')
  ], cb);
});

gulp.task('imagemin', (cb) => {
  pump([
    gulp.src('./src/images/**/*'),
    cache('imagemin'),
    imagemin(),
    gulp.dest('./dist/images')
  ], cb);
});

gulp.task('watch', () => {
  gulp.watch(
    [
      './src/bootstrap-scss/**/*.scss',
      '!./src/_colors.scss',
      '!./src/_utilities.scss'
     ],
     gulp.series('bootstrap:css'));

  gulp.watch(
    [
      './src/**/*.scss',
      '!./src/bootstrap-scss/**/*.scss',
      '!./src/_colors.scss',
      '!./src/_utilities.scss'
    ], gulp.series('css'));

  gulp.watch(
    [
      './src/_colors.scss',
      './src/_utilities.scss'
    ], gulp.series('bootstrap:css', 'css'));

  gulp.watch(['./src/**/*.js'], gulp.series('js'));

  gulp.watch(['./src/images/*'], gulp.series('imagemin'))
});

gulp.task('bootstrap', gulp.series('bootstrap:css', 'bootstrap:js'));

gulp.task('theme', gulp.series('css', 'js'));

gulp.task('build', gulp.series('bootstrap', 'theme', 'imagemin'));

gulp.task('default', gulp.series('build', 'watch'));
