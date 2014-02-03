var gulp = require('gulp'),
    gutil = require('gulp-util'),
    sass = require('gulp-sass'),
    autoprefixer = require('gulp-autoprefixer'),
    minifycss = require('gulp-minify-css'),
    jshint = require('gulp-jshint'),
    uglify = require('gulp-uglify'),
    imagemin = require('gulp-imagemin'),
    rename = require('gulp-rename'),
    clean = require('gulp-clean'),
    concat = require('gulp-concat'),
    notify = require('gulp-notify'),
    cache = require('gulp-cache'),
    livereload = require('gulp-livereload'),
    coffee = require('gulp-coffee'),
    coffeelint = require('gulp-coffeelint'),
    header = require('gulp-header'),
    lr = require('tiny-lr'),
    filter = require('gulp-filter'),
    server = lr();

var pkg = require('./package.json');
var banner = '/*! <%= pkg.title %> - v<%= pkg.version %>\n' +
' * <%= pkg.homepage %>\n' +
' * Licensed GPLv2+' +
' */\n';

gulp.task('default', function() {
    gulp.start('sass', 'coffee', 'images');
});

gulp.task('sass', function() {
    return gulp.src('assets/src/sass/**/*.scss')
        .pipe(sass({ style: 'expanded' }))
        .pipe(autoprefixer('last 2 version', 'safari 5', 'ie 8', 'ie 9', 'opera 12.1', 'ios 6', 'android 4'))
        .pipe(gulp.dest('assets/dist/css/'))
        .pipe(livereload(server))
        .pipe(rename({suffix: '.min'}))
        .pipe(minifycss())
        .pipe(gulp.dest('assets/dist/css/'))
        .pipe(notify({ message: "Styles compiled."} ));
});

gulp.task('coffee', function() {
    return gulp.src('assets/src/coffee/**/*.coffee')
        .pipe(coffeelint({ 
            "indentation": {
                "name": "indentation",
                "value": 4,
                "level": "error"
            }
        }))
        .pipe(coffeelint.reporter())
        .pipe(coffee({bare: true})).on('error', gutil.log)
        .pipe(gulp.dest('assets/dist/js/'))
        .pipe(livereload(server))
        .pipe(rename({suffix: '.min'}))
        .pipe(uglify())
        .pipe(header(banner, { pkg: pkg }))
        .pipe(gulp.dest('assets/dist/js/'))
        .pipe(notify({ message: "Coffeescript compiled." }));
});

gulp.task('images', function() {
    return gulp.src('assets/src/img/**/*')
        .pipe(imagemin({ optimizationLevel: 3, progressive: true, interlaced: true}))
        .pipe(gulp.dest('assets/dist/img/'))
        .pipe(livereload(server))
        .pipe(notify({message: "Images minified."}));
});

gulp.task('watch', function() {
    gulp.start('default');
    
    server.listen(35729, function(err) {
        if (err) {
            return gutil.log(err);
        }
        gulp.watch('assets/src/**/*.scss', ['sass']);
        gulp.watch('assets/src/**/*.coffee', ['coffee']);
        gulp.watch('assets/src/img/**/*', ['images']);
    });
});