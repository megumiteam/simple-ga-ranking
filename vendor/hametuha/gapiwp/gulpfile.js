var gulp = require('gulp'),
    $ = require('gulp-load-plugins')();


// Sass's task
gulp.task('sass',function(){

    return gulp.src(['./assets/scss/**/*.scss'])
        .pipe($.plumber())
        .pipe($.sourcemaps.init())
        .pipe($.sass({
            errLogToConsole: true,
            outputStyle: 'compressed',
            sourceComments: 'normal',
            sourcemap: true,
            includePaths: [
                './assets/scss'
            ]
        }))
        .pipe($.sourcemaps.write('./map'))
        .pipe(gulp.dest('./assets/css'));
});

// watch
gulp.task('watch',function(){
    gulp.watch('./assets/scss/**/*.scss',['sass']);
});

// Build
gulp.task('build', ['sass']);

// Default Tasks
gulp.task('default', ['watch']);
