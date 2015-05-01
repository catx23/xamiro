// Include gulp
var gulp = require('gulp'); 

// Include Our Plugins
var jshint = require('gulp-jshint');
var less   = require('gulp-less');
var minifyCSS = require('gulp-minify-css');
var path = require('path');
var concat = require('gulp-concat');
var uglify = require('gulp-uglify');
var rename = require('gulp-rename');
var sourcemaps = require('gulp-sourcemaps');
var browserSync = require('browser-sync');
var autoprefixer = require('gulp-autoprefixer');

gulp.task('less', function () {
  gulp.src('./assets/less/main.less')
  	//.pipe(sourcemaps.init())
    .pipe(less())
    .on('error', function (err) {
    	this.emit('end');
    })
   	.pipe(autoprefixer({
         browsers: ['last 2 versions'],
         cascade: false,
         remove: false
    }))
    //.pipe(sourcemaps.write())
    .pipe(minifyCSS())
    .pipe(gulp.dest('./assets/css'))
    .pipe(browserSync.reload({stream:true}));
});

gulp.task('integrate-less', function () {
    gulp.src('./assets/less/integrate.less')
        .pipe(less())
        .on('error', function (err) {
            this.emit('end');
        })
        .pipe(autoprefixer({
             browsers: ['last 2 versions'],
             cascade: false,
             remove: false,
        }))
        .pipe(minifyCSS())
        .pipe(gulp.dest('./assets/css'))
        .pipe(browserSync.reload({stream:true}));
});

// Concatenate & Minify JS
gulp.task('scripts', function() {
    return gulp.src([
        'assets/js/editor/resources/colors.js',
        'assets/js/editor/resources/gradients.js',
    	'assets/js/vendor/jquery.js',
        'assets/js/vendor/jquery-ui.js',
        'assets/js/vendor/file-saver.js',
        'assets/js/vendor/pagination.js',
        'assets/js/vendor/spectrum.js',
        'assets/js/vendor/hammer.js',
        'assets/js/vendor/scrollbar.js',
    	'assets/js/vendor/angular.min.js',
        'node_modules/angular-animate/angular-animate.js',
        'node_modules/angular-aria/angular-aria.js',
        'assets/js/vendor/angular-material.js',
    	'assets/js/vendor/fabric.js',
    	'assets/js/editor/App.js',
        'assets/js/editor/LocalStorage.js',
        'assets/js/editor/Canvas.js',
        'assets/js/editor/crop/cropper.js',
        'assets/js/editor/crop/cropzone.js',
        'assets/js/editor/crop/cropController.js',
        'assets/js/editor/basics/RotateController.js',
        'assets/js/editor/basics/CanvasBackgroundController.js',
        'assets/js/editor/basics/ResizeController.js',
        'assets/js/editor/basics/RoundedCornersController.js',
        'assets/js/editor/zoomController.js',
        'assets/js/editor/TopPanelController.js',
        'assets/js/editor/directives/Tabs.js',
        'assets/js/editor/directives/PrettyScrollbar.js',
        'assets/js/editor/directives/ColorPicker.js',
        'assets/js/editor/directives/FileUploader.js',
        'assets/js/editor/directives/TogglePanelVisibility.js',
        'assets/js/editor/directives/ToggleSidebar.js',
        'assets/js/editor/text/Text.js',
        'assets/js/editor/text/TextController.js',
        'assets/js/editor/text/TextAlignButtons.js',
        'assets/js/editor/text/TextDecorationButtons.js',
        'assets/js/editor/text/Fonts.js',
        'assets/js/editor/drawing/Drawing.js',
        'assets/js/editor/drawing/DrawingController.js',
        'assets/js/editor/drawing/RenderBrushesDirective.js',
        'assets/js/editor/History.js',
        'assets/js/editor/Saver.js',
        'assets/js/editor/filters/FiltersController.js',
        'assets/js/editor/filters/Filters.js',
        'assets/js/editor/shapes/SimpleShapesController.js',
        'assets/js/editor/shapes/StickersController.js',
        'assets/js/editor/shapes/StickersCategories.js',
        'assets/js/editor/shapes/SimpleShapes.js',
        'assets/js/editor/shapes/Polygon.js',
        'assets/js/editor/objects/ObjectsPanelController.js',
        'assets/js/editor/objects/ObjectsPanelSortableDirective.js',

	])
	.pipe(sourcemaps.init())
    .pipe(concat('scripts.min.js'))
    .pipe(sourcemaps.write())
    //.pipe(uglify())
    .pipe(gulp.dest('assets/js')) 
    .pipe(browserSync.reload({stream:true}));
});

// Watch Files For Changes
gulp.task('watch', function() {
	browserSync({
        proxy: "localhost/images/"
        //server: {
		 //   baseDir: "./"
        //}
    });

    gulp.watch('assets/js/**/*.js', ['scripts']);
    gulp.watch('assets/less/**/*.less', ['less']);
    gulp.watch('assets/less/**/integrate.less', ['integrate-less']);
});

// Default Task
gulp.task('default', ['less', 'scripts', 'watch']);