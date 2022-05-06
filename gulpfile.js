/* eslint-disable */

const gulp = require('gulp');
const replace = require('gulp-replace');
const exec = require('child_process').exec;
const webpack = require('webpack');

const webpackDevConfig = require('./webpack.dev.js');
const webpackProdConfig = require('./webpack.prod.js');

const jsAmdPaths = ['./amd/src/*.js'];

const jsUiPaths = ['./ui/src/**/*.ts', './ui/src/**/*.tsx', './ui/src/**/*.js'];


/** Moodle. */

function moodleGruntAmd(cb) {
  exec('grunt amd', function (err, stdout, stderr) {
    cb(err);
  });
}

function removeAmdDefinedName(cb) {
  // We must do this to support older Moodle versions that did not expect
  // the name to be included as part of the build file. That can be seen
  // in Moodle 3.3, and maybe older versions, where the code will attempt
  // to add the module even if it is already present. As of Moodle 4.0,
  // the module name will be injected if not present, so we can safely remove it.
  return gulp
    .src('./amd/build/*.js')
    .pipe(replace(/^\s*define\s*\(\s*['"][a-z0-9_/-]+['"]\s*,/m, 'define('))
    .pipe(gulp.dest('./amd/build'));
}

const moodleAmd = gulp.series(moodleGruntAmd, removeAmdDefinedName);

/** Webpack. */

function webpackBuildFromConfig(config) {
  return new Promise((resolve, reject) => {
    webpack(config, (err, stats) => {
      if (err) {
        return reject(err);
      } else if (stats.hasErrors()) {
        return reject(new Error(stats.compilation.errors.map((e) => e.message).join('\n')));
      }
      resolve();
    });
  });
}

function webpackDev(cb) {
  return webpackBuildFromConfig(webpackDevConfig);
}

function webpackProd(cb) {
  return webpackBuildFromConfig(webpackProdConfig);
}

/** Watch. */

function watchAmd(cb) {
  return gulp.watch(jsAmdPaths, gulp.series(moodleAmd));
}

const watchJs = gulp.parallel(watchUi, watchAmd);

function watchUi(cb) {
  return gulp.watch(jsUiPaths, gulp.series(webpackDev, moodleAmd));
}

exports['dist'] = gulp.series(webpackProd, moodleAmd);
exports['dist:dev'] = gulp.series(webpackDev, moodleAmd);
exports['watch'] = gulp.parallel(watchJs);
exports['watch:js'] = watchJs;
exports['webpack:dev'] = webpackDev;
exports['webpack:prod'] = webpackProd;
