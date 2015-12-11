/**
 * Created by Stayshine Web Development.
 * Author: Michael Rosata
 * Email: mike@stayshine.com
 * Date: 12/6/15
 * Time: 8:11 PM
 *
 * Project: wp-dev
 */
"use strict";
var gulp = require('gulp');
var babel = require('gulp-babel');
var rename = require('gulp-rename');
var autoprefixer = require('gulp-autoprefixer');
var sass = require('gulp-sass');


/**
 *  Default task will watch admin/js/**-es6.js and transpile it into
 *  ES5 in the same folder under the same name except it will drop the
 *  `-es6`. So for example:
 *      admin/js/mike-is-ninja-es6.js >>==> admin/js/mike-is-ninja.js
 *
 *  This task runs babel and SCSS.
 */
gulp.task('default', function() {
  "use strict";
  gulp.watch('./admin/js/*-es6.js', function() {})
    .on('change', babelGo);

  gulp.watch(['./**/css/raw/*.scss', './**/css/raw/wp-gtp-partials/*.scss'], function () {
      console.log('you touched my SCSS!');
    })
    .on('change', autoSCSS);

});

/**
 *  To use vanilla CSS, will prefix and save admin/css/raw/***.css as admin/css/***.css
 */
gulp.task('css', ['default'], function () {

  gulp.watch('./**/css/raw/*.css', function () {})
    .on('change', autoCSS);
});


/**
 *  To use SCSS, will compike admin/css/raw/***.scss as admin/css/***.css
 */
gulp.task('sass', ['default'], function () {

  gulp.watch(['./**/css/raw/*.scss', './**/css/raw/wp-gtp-partials/*.scss'], function () {
      console.log('you touched my SCSS!');
    })
    .on('change', autoSCSS);
});


/**
 * Sass Transpiling
 */
function autoSCSS () {
  "use strict";
  console.log('Ahh, you touched my SASS!');

  return gulp.src('./**/**/raw/*.scss')
    .pipe(sass().on('error', sass.logError))
    .pipe(rename(function(file) {
      console.log('Working on %s', file.basename);
      file.dirname = file.dirname.toString().replace(/(.*)raw\/?$/ig, '$1');
    }))
    .pipe(gulp.dest('./'));
}


/**
 *  CSS Auto-prefix transpile.
 */
function autoCSS() {
  "use strict";
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

/**
 *  Babel Transpiling
 */
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