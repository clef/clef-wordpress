module.exports = function( grunt ) {

    // Project configuration
    grunt.initConfig( {
        pkg:    grunt.file.readJSON( 'package.json' ),
        jshint: {
            all: [
                'Gruntfile.js',
                'assets/js/src/**/*.js',
                'assets/js/test/**/*.js'
            ],
            options: {
                curly:   true,
                eqeqeq:  true,
                immed:   true,
                latedef: true,
                newcap:  true,
                noarg:   true,
                sub:     true,
                undef:   true,
                boss:    true,
                eqnull:  true,
                browser: true,
                globals: {
                    exports: true,
                    module:  false,
                    jQuery: true,
                    wp: true,
                    ajaxurl: true,
                    console: true
                }
            }       
        },
        uglify: {
            all: {
                files: {
                    'assets/js/clef_heartbeat.js': ['assets/js/src/clef_heartbeat.js'],
                    'assets/js/keys.js': ['assets/js/src/keys.js']
                },
                options: {
                    banner: '/*! <%= pkg.title %> - v<%= pkg.version %>\n' +
                        ' * <%= pkg.homepage %>\n' +
                        ' * Copyright (c) <%= grunt.template.today("yyyy") %>;' +
                        ' * Licensed GPLv2+' +
                        ' */\n',
                    mangle: {
                        except: ['jQuery']
                    }
                }
            }
        },
        test:   {
            files: ['assets/js/test/**/*.js']
        },
        
        sass:   {
            all: {
                files: {
                    'assets/css/wpclef.css': 'assets/css/sass/wpclef.scss'
                }
            }
        },
        
        cssmin: {
            options: {
                banner: '/*! <%= pkg.title %> - v<%= pkg.version %>\n' +
                    ' * <%= pkg.homepage %>\n' +
                    ' * Copyright (c) <%= grunt.template.today("yyyy") %>;' +
                    ' * Licensed GPLv2+' +
                    ' */\n'
            },
            minify: {
                expand: true,
                
                cwd: 'assets/css/',             
                src: ['wpclef.css'],
                
                dest: 'assets/css/',
                ext: '.min.css'
            }
        },
        watch:  {
            
            sass: {
                files: ['assets/css/sass/*.scss'],
                tasks: ['sass', 'cssmin'],
                options: {
                    debounceDelay: 500
                }
            },
            
            scripts: {
                files: ['assets/js/src/**/*.js', 'assets/js/vendor/**/*.js'],
                tasks: ['jshint', 'uglify'],
                options: {
                    debounceDelay: 500
                }
            }
        },
        clean: {
            main: ['release/<%= pkg.version %>']
        },
        copy: {
            // Copy the plugin to a versioned release directory
            main: {
                src:  [
                    '**',
                    '!node_modules/**',
                    '!release/**',
                    '!.git/**',
                    '!.sass-cache/**',
                    '!css/src/**',
                    '!js/src/**',
                    '!img/src/**',
                    '!Gruntfile.js',
                    '!package.json',
                    '!.gitignore',
                    '!.gitmodules'
                ],
                dest: 'release/<%= pkg.version %>/'
            }       
        },
        compress: {
            main: {
                options: {
                    mode: 'zip',
                    archive: './release/wpclef.<%= pkg.version %>.zip'
                },
                expand: true,
                cwd: 'release/<%= pkg.version %>/',
                src: ['**/*'],
                dest: 'wpclef/'
            }       
        }
    } );
    
    // Load other tasks
    grunt.loadNpmTasks('grunt-contrib-jshint');
    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-cssmin');
    
    grunt.loadNpmTasks('grunt-contrib-sass');
    
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks( 'grunt-contrib-clean' );
    grunt.loadNpmTasks( 'grunt-contrib-copy' );
    grunt.loadNpmTasks( 'grunt-contrib-compress' );
    
    // Default task.
    
    grunt.registerTask( 'default', ['jshint', 'uglify', 'sass', 'cssmin'] );
    
    
    grunt.registerTask( 'build', ['default', 'clean', 'copy', 'compress'] );

    grunt.util.linefeed = '\n';
};