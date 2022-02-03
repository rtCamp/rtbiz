'use strict';
const sass = require('node-sass');

module.exports = function ( grunt ) {

	// load all grunt tasks matching the `grunt-*` pattern
	// Ref. https://npmjs.org/package/load-grunt-tasks
	require( 'load-grunt-tasks' )( grunt );

	grunt.initConfig( {
		// watch for changes and trigger sass, jshint, uglify and livereload
		watch: {
			sass: {
				files: [ 'admin/css/**/*.{scss,sass}' ],
				tasks: [ 'sass', 'autoprefixer', 'cssmin' ]
			},
			js: {
				files: '<%= jshint.all %>',
				tasks: [ 'jshint', 'uglify' ]
			}
		},
		// sass
		sass: {
			dist: {
				options: {
					implementation: sass,
					style: 'expanded'
				},
				files: {
					'admin/css/biz-admin.css': 'admin/css/scss/biz-admin.scss'
				}
			}
		},
		// autoprefixer
		autoprefixer: {
			options: {
				browsers: [ 'last 2 versions', 'ie 9', 'ios 6', 'android 4' ],
				map: true
			},
			files: {
				expand: true,
				flatten: true,
				src: 'admin/css/*.css',
				dest: 'admin/css'
			}
		},
		// css minify
		cssmin: {
			options: {
				keepSpecialComments: 1
			},
			minify: {
				expand: true,
				cwd: 'admin/css',
				src: [ '*.css', '!*.min.css' ],
				dest: 'admin/css',
				ext: '.min.css'
			}
		},
		// javascript linting with jshint
		jshint: {
			options: {
				jshintrc: '.jshintrc',
				"force": true
			},
			all: [
				'gruntfile.js',
				'admin/js/**/*.js'
			]
		},
		// uglify to concat, minify, and make source maps
		uglify: {
			admin: {
				options: {
					sourceMap: 'admin/js/rtbiz-main.js.map',
					sourceMappingURL: 'rtbiz-main.js.map',
					sourceMapPrefix: 2
				},
				files: {
					'admin/js/rtbiz-main.min.js': [
						'admin/js/admin.js',
						'admin/js/rtbiz-plugin-check.js',
						'admin/js/validation.js'
					]
				}
			}
		}
	} );

	// register task
	grunt.registerTask( 'default', [ 'sass', 'autoprefixer', 'cssmin', 'uglify', 'watch' ] );
};