const gulp = require('gulp');
const sass = require('gulp-sass');
const postcss = require('gulp-postcss');
const sourcemaps = require('gulp-sourcemaps');

const autoprefixer = require('autoprefixer');
const cssnano = require('cssnano');

gulp.task('sass', () => {
  return gulp.src('./scss/style.scss')
    .pipe(sourcemaps.init())
    .pipe(sass())
    .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest('./dist/css'))
});

gulp.task('css', function() {
  var plugins = [
    autoprefixer({
      browsers: ['last 1 version']
    }),
    cssnano()
  ];
  return gulp.src('./dist/css/style.css')
    .pipe(sourcemaps.init())
    .pipe(postcss(plugins))
    .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest('./dist/css'));
});

gulp.task('watch', () => {
  gulp.watch('./scss/**/*.scss', gulp.series('sass', 'css'));
});

gulp.task('default', gulp.series('sass', 'watch'));

gulp.task('build', gulp.series('sass', 'css'));
