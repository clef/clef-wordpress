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
    plumber = require('gulp-plumber'),
    runSequence = require('run-sequence'),
    zip = require('gulp-zip'),
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
        .pipe(plumber())
        .pipe(sass({ style: 'expanded', bare: true })).on('error', gutil.log)
        .pipe(autoprefixer('last 2 version', 'safari 5', 'ie 8', 'ie 9', 'opera 12.1', 'ios 6', 'android 4'))
        .pipe(gulp.dest('assets/dist/css/'))
        .pipe(livereload(server))
        .pipe(rename({suffix: '.min'}))
        .pipe(minifycss())
        .pipe(gulp.dest('assets/dist/css/'))
        .pipe(notify({ message: "Styles compiled."} ));
});

gulp.task('coffee', function() {
    build(gulp.src(['assets/src/coffee/**/*.coffee', '!assets/src/coffee/settings/**/*.coffee']));
    build(gulp.src([
        'assets/src/coffee/settings/utils.coffee',
        'assets/src/coffee/settings/invite.coffee',
        'assets/src/coffee/settings/multisite.coffee',
        'assets/src/coffee/settings/tutorial.coffee',
        'assets/src/coffee/settings/settings.coffee',
        'assets/src/coffee/settings/connect.coffee',
        'assets/src/coffee/settings/pro.coffee'
    ]), 'settings.js');
    build(gulp.src([
        'assets/src/coffee/settings/utils.coffee',
        'assets/src/coffee/settings/tutorial.coffee',
        'assets/src/coffee/settings/connect.coffee'
        ]), 'connect.js'
    )

    function build(strm, output) {
        strm = strm
            .pipe(plumber())
            .pipe(coffeelint({
                "indentation": {
                    "name": "indentation",
                    "value": 4,
                    "level": "error"
                }
            }))
            .pipe(coffeelint.reporter())
            .pipe(coffee({bare: true})).on('error', gutil.log);

        if (output) {
            strm = strm.pipe(concat(output));
        }

        strm
            .pipe(gulp.dest('assets/dist/js/'))
            .pipe(livereload(server))
            .pipe(rename({suffix: '.min'}))
            .pipe(uglify())
            .pipe(header(banner, { pkg: pkg }))
            .pipe(gulp.dest('assets/dist/js/'));
    }
});

gulp.task('images', function() {
    return gulp.src('assets/src/img/**/*')
        .pipe(imagemin({ optimizationLevel: 3, progressive: true, interlaced: true}))
        .pipe(gulp.dest('assets/dist/img/'))
        .pipe(livereload(server))
        .pipe(notify({message: "Images minified."}));
});

gulp.task('build', function() {
    runSequence(
        ['images', 'sass', 'coffee'],
        function() {
            gulp.src(
                [
                    'templates/**',
                    'languages/**',
                    'includes/**',
                    'assets/**',
                    'clef-require.php',
                    'wpclef.php'
                ],
                { base: './' }
            ).pipe(gulp.dest('build/wpclef/'))
            .pipe(zip('wpclef.zip'))
            .pipe(gulp.dest('build/'));
        }
    );
})

gulp.task('watch', function() {
    server.listen(35729, function(err) {
        if (err) {
            gutil.log(err);
        }
    });

    gulp.watch('assets/src/**/*.scss', ['sass']);
    gulp.watch('assets/src/**/*.coffee', ['coffee']);
    gulp.watch('assets/src/img/**/*', ['images']);
});
