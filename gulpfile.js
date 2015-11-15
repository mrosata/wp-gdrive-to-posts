var gulp = require('gulp');
var babel = require('gulp-babel');
var rename = require('gulp-rename');

gulp.task('default', ['babel'], function() {
  "use strict";
  gulp.watch('./admin/js/*-es6.js', function() {})
    .on('change', babelGo);
});

gulp.task('babel', babelGo);


function babelGo() {
  "use strict";
  console.log('changed a js file!');
  return gulp.src(['./*-es6.js', './**/*-es6.js', './**/**/*-es6.js'])
    .pipe(rename(function(file) {
      console.log(file);
      file.basename = file.basename.toString().replace(/(.*)-es6$/ig, '$1');
      return file;
    }))
    .pipe(babel({presets: 'es2015'}))
    .pipe(gulp.dest('./'));

}