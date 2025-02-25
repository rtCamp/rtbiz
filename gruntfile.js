'use strict';
const sass = require('sass');

module.exports = function (grunt) {

    // Load all grunt tasks matching the `grunt-*` pattern
    require('load-grunt-tasks')(grunt);

    grunt.initConfig({
        watch: {
            sass: {
                files: ['admin/css/**/*.{scss,sass}'],
                tasks: ['sass', 'postcss', 'cssmin']
            },
            js: {
                files: ['gruntfile.js', 'admin/js/**/*.js'],
                tasks: ['jshint', 'uglify']
            }
        },
        // Sass task
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
        // PostCSS task
        postcss: {
            options: {
                map: false,
                plugins: [
                    require('autoprefixer')({
                        overrideBrowserslist: ['last 2 versions']
                    })
                ]
            },
            dist: {
                src: 'admin/css/*.css'
            }
        },
        // Minify CSS
        cssmin: {
            options: {
                keepSpecialComments: 1
            },
            minify: {
                expand: true,
                cwd: 'admin/css',
                src: ['*.css', '!*.min.css'],
                dest: 'admin/css',
                ext: '.min.css'
            }
        },
        // JavaScript linting with jshint
        jshint: {
            options: {
                jshintrc: '.jshintrc',
                force: true
            },
            all: ['gruntfile.js', 'admin/js/**/*.js']
        },
        // Uglify JavaScript (minify and create source maps)
        uglify: {
            admin: {
                options: {
                    sourceMap: true,
                    sourceMapName: 'admin/js/rtbiz-main.js.map'
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
    });

    // Register default task (for development)
    grunt.registerTask('default', ['sass', 'postcss', 'cssmin', 'uglify', 'watch']);

    // Register build task (for production)
    grunt.registerTask('build', ['sass', 'postcss', 'cssmin', 'uglify']);
};
