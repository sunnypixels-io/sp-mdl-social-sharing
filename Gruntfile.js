'use strict';

module.exports = function (grunt) {
    // load all tasks
    require('load-grunt-tasks')(grunt, {scope: 'devDependencies'});

    grunt.config.init({
        pkg: grunt.file.readJSON('package.json'),

        dirs: {
            css: 'assets/css',
            js: 'assets/js',
        },

        makepot: {
            target: {
                options: {
                    domainPath: '/languages/',
                    potFilename: '<%= pkg.name %>.pot',
                    potHeaders: {
                        poedit: true,
                        'x-poedit-keywordslist': true
                    },
                    processPot: function (pot, options) {
                        pot.headers['report-msgid-bugs-to'] = 'https://sunnypixels.io/';
                        pot.headers['language-team'] = 'SunnyPixels <info@sunnypixels.io>';
                        pot.headers['last-translator'] = 'SunnyPixels <info@sunnypixels.io>';
                        pot.headers['language-team'] = 'SunnyPixels <info@sunnypixels.io>';
                        return pot;
                    },
                    updateTimestamp: true,
                    type: 'wp-plugin'

                }
            }
        },

        addtextdomain: {
            target: {
                options: {
                    updateDomains: true, // bool || [] List of text domains to replace.
                    textdomain: '<%= pkg.name %>'
                },
                files: {
                    src: [
                        '*.php',
                        '!node_modules/**'
                    ]
                }
            }
        },

        checktextdomain: {
            standard: {
                options: {
                    text_domain: ['<%= pkg.name %>', 'sunnypixels'], //Specify allowed domain(s)
                    create_report_file: 'true',
                    keywords: [ //List keyword specifications
                        '__:1,2d',
                        '_e:1,2d',
                        '_x:1,2c,3d',
                        'esc_html__:1,2d',
                        'esc_html_e:1,2d',
                        'esc_html_x:1,2c,3d',
                        'esc_attr__:1,2d',
                        'esc_attr_e:1,2d',
                        'esc_attr_x:1,2c,3d',
                        '_ex:1,2c,3d',
                        '_n:1,2,4d',
                        '_nx:1,2,4c,5d',
                        '_n_noop:1,2,3d',
                        '_nx_noop:1,2,3c,4d'
                    ]
                },
                files: [
                    {
                        src: [
                            '**/*.php',
                            '!**/node_modules/**',
                        ], //all php
                        expand: true
                    }]
            }
        },

        clean: {
            init: {
                src: ['build/']
            },
            build: {
                src: [
                    'build/*',
                    '!build/<%= pkg.name %>.v<%= pkg.version %>.zip' //TODO checkout
                ]
            },
        },

        copy: {
            build: {
                expand: true,
                src: [
                    '**',
                    '!.*',
                    '!sass',
                    '!assets/css/*.dev.css',
                    '!assets/css/*.css.map',
                    '!assets/js/*.dev.js',
                    '!assets/js/*.js.map',
                    '!node_modules/**',
                    '!build/**',
                    '!readme.md',
                    '!README.md',
                    '!phpcs.ruleset.xml',
                    '!Gruntfile.js',
                    '!package.json',
                    '!package-lock.json'],
                dest: 'build/'
            }
        },

        compress: {
            build: {
                options: {
                    pretty: true,
                    archive: '<%= pkg.name %>.v<%= pkg.version %>.zip'
                },
                expand: true,
                cwd: 'build/',
                src: ['**/*'],
                dest: '<%= pkg.name %>/'
            }
        },

        // Not needed in this project
        // TODO add WebP converter
        //imagemin: {
        //    jpg: {
        //        options: {
        //            progressive: true
        //        }
        //    },
        //    png: {
        //        options: {
        //            optimizationLevel: 7
        //        }
        //    },
        //    dynamic: {
        //        files: [
        //            {
        //                expand: true,
        //                cwd: 'assets/img/',
        //                src: ['**/*.{png,jpg,gif}'],
        //                dest: 'assets/img/'
        //            }]
        //    }
        //},

        babel: {
            options: {
                "sourceMap": true
            },
            dist: {
                files: {
                    'assets/js/admin.dev.js': 'assets/js/admin.js',
                    'assets/js/public.dev.js': 'assets/js/public.js',
                }
            }
        },

        uglify: {
            options: {
                "sourceMap": false
            },
            my_target: {
                files: {
                    'assets/js/admin.min.js': 'assets/js/admin.dev.js',
                    'assets/js/public.min.js': 'assets/js/public.dev.js',
                }
            }
        },

        sass: {
            dist: {
                options: {
                    style: 'expanded'
                },
                files: {
                    'assets/css/admin.dev.css': 'sass/admin.scss',
                    'assets/css/public.dev.css': 'sass/public.scss',
                }
            }
        },

        autoprefixer: {
            dist: {
                files: {
                    'assets/css/admin.css': 'assets/css/admin.dev.css',
                    'assets/css/public.css': 'assets/css/public.dev.css',
                }
            }
        },

        csso: {
            compress: {
                options: {
                    report: 'min'
                },
                files: {
                    'assets/css/admin.min.css': 'assets/css/admin.css',
                    'assets/css/public.min.css': 'assets/css/public.css',
                }
            }
        },

        watch: {
            css: {
                files: 'sass/**/*.scss',
                tasks: ['sass', 'autoprefixer', 'csso:compress']
            },
            js: {
                files: ['assets/js/*.js', '!assets/js/*.min.js', '!assets/js/*.dev.js'],
                tasks: ['babel', 'uglify']
            }
        }

    });

    // Check Missing Text Domain Strings
    grunt.registerTask('textdomain', [
        'checktextdomain'
    ]);

    grunt.registerTask('build-css', [
        'sass',
        'autoprefixer',
        'csso:compress']);

    grunt.registerTask('build-js', [
        'babel',
        'uglify']);

    // Minify Images
    //grunt.registerTask('minimg', [
    //    'imagemin:dynamic'
    //]);

    // Build task
    grunt.registerTask('build-archive', [
        'clean:init',
        'copy',
        'compress:build',
        'clean:init'
    ]);
};
