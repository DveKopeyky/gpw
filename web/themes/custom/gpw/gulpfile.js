const gulp = require('gulp');
const sass = require('gulp-sass');
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
  postcssPresetEnv({
    browsers: ['last 2 versions']
  }),
  cssnano()
];

gulp.task('bootstrap:css', (cb) => {
  pump([
    gulp.src('./src/bootstrap/style.scss', {
      base: './src/*'
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

gulp.task('components:css', (cb) => {
  pump([
    gulp.src(['./src/components/*/*.scss'], {
      base: './src/components'
    }),
    sourcemaps.init(),
    sass(),
    postcss(plugins),
    sourcemaps.write('.'),
    gulp.dest('./dist/components'),
  ], cb);
});

gulp.task('components:js', (cb) => {
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
  gulp.watch(['./src/bootstrap/**/*.scss'], gulp.series('bootstrap:css'));
  gulp.watch(['./src/components/**/*.scss'], gulp.series('components:css'));
  gulp.watch(['./src/components/**/*.js'], gulp.series('components:js', 'uglify'));
  gulp.watch(['./src/images/*'], gulp.series('imagemin'))
});

gulp.task('bootstrap', gulp.series('bootstrap:css', 'bootstrap:js'));

gulp.task('components', gulp.series('components:css', 'components:js'));

gulp.task('build', gulp.series('bootstrap', 'components', 'uglify', 'imagemin'));

gulp.task('default', gulp.series('build', 'watch'));
