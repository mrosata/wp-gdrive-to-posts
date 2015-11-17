var gulp = require('gulp');
var babel = require('gulp-babel');
var rename = require('gulp-rename');
var autoprefixer = require('gulp-autoprefixer');


gulp.task('default', ['babel', 'autoCSS'], function() {
  "use strict";
  gulp.watch('./admin/js/*-es6.js', function() {})
    .on('change', babelGo);

  gulp.watch('./**/css/raw/*.css', function() {})
    .on('change', autoCSS);

});

gulp.task('babel', babelGo);
gulp.task('autoCSS', autoCSS);


function autoCSS() {
  // Return css file with prefixes for IE8+ *I hope!
  console.log('changed a CSS file!');
  return gulp.src('./**/css/raw/*.css')
    .pipe(rename(function(file) {
      console.log('Working on %s', file.basename);
      file.dirname = file.dirname.toString().replace(/(.*)raw\/?$/ig, '$1');
    }))
    .pipe(autoprefixer({
      browsers: ['> 1%', 'IE 8'],
      cascade: true
    }))
    .pipe(gulp.dest('./'));
}


function babelGo() {
  "use strict";
  console.log('changed a JS2015 file!');
  return gulp.src(['./*-es6.js', './**/*-es6.js', './**/**/*-es6.js'])
    .pipe(rename(function(file) {
      console.log(file);
      file.basename = file.basename.toString().replace(/(.*)-es6$/ig, '$1');
      return file;
    }))
    .pipe(babel({presets: 'es2015'}))
    .pipe(gulp.dest('./'));

}