'use strict';
module.exports = function(grunt) {

	grunt.initConfig({

		pkg : grunt.file.readJSON( 'package.json' ),

		watch: {
			options: {
				livereload: 12348,
			},
			js: {
				files: [
					'js/**/*.js',
					'inc/**/*.js',
					'!js/**/*.min.js',
					'!inc/**/*.min.js',
				],
				tasks: ['uglify']
			},
			css: {
				files: [
					'css/sass/**/*.scss',
					'css/sass/*.scss'
				],
				tasks: ['sass', 'concat', 'cssjanus', 'cssmin' ]
			}
		},

		// uglify to concat, minify, and make source maps
		uglify: {
			dist: {
				files: {
					// Results.
					'inc/results/js/listings.min.js': [
						'inc/results/js/listings.js',
					],
					'inc/results/js/results.min.js': [
						'inc/results/js/results.js',
					],
					'inc/results/js/map.min.js': [
						'inc/results/js/map.js',
					],
					'inc/results/js/map-googlemaps.min.js': [
						'inc/results/js/vendor/googlemaps/infobubble/infobubble.js',
						'inc/results/js/vendor/googlemaps/richmarker/richmarker.js',
						'inc/results/js/vendor/googlemaps/markerclusterer/markerclusterer.js',
					],
					'inc/results/js/map-mapbox.min.js': [
						'inc/results/js/vendor/mapbox/geosearch/leaflet.geosearch.js',
						'inc/results/js/vendor/mapbox/markerclusterer/leaflet.markercluster.js',
					],
					// Integrations.
					'inc/integrations/wp-job-manager/js/wp-job-manager.min.js': [
						'inc/integrations/wp-job-manager/js/vendor/timepicker/jquery.timepicker.min.js',
						'inc/integrations/wp-job-manager/js/wp-job-manager.js',
						'inc/integrations/wp-job-manager/js/wp-job-manager-gallery.js',
					],
					'inc/integrations/wp-job-manager/js/listing/app.min.js': [
						'inc/integrations/wp-job-manager/js/listing/app.js'
					],
					'js/app.min.js': [
						'js/vendor/**.js',
						'js/vendor/**/*.js',
						'inc/integrations/wp-job-manager/js/wp-job-manager.min.js',
						'inc/integrations/facetwp/js/source/facetwp.js',
						'inc/integrations/woocommerce/js/source/woocommerce.js',
						'inc/integrations/jetpack/js/jetpack.js',
						'js/source/app.js',

						'!js/vendor/salvattore/salvattore.min.js',
						'!js/vendor/flexibility/flexibility.min.js',
					],
				}
			}
		},

		jshint: {
			all: [ 
				'inc/results/js/map.js',
				'inc/results/js/listings.js',
				'inc/results/js/results.js',
			]
		},

		jsonlint: {
			dist: {
				src: [ 'inc/setup/import-content/**/*.json' ],
				options: {
					formatter: 'prose'
				}
			}
		},

		sass: {
			dist: {
				files: {
					'css/editor-style.css' : 'css/sass/modules/editor-style.scss',
					'css/style.css' : 'css/sass/style.scss'
				}
			}
		},

		concat: {
			dist: {
				files: {
					'css/style.css': ['css/vendor/*.css', 'css/style.css']
				}
			}
		},

		cssmin: {
			dist: {
				files: {
					'css/style.min.css': [ 'css/style.css' ],
					'css/style.min-rtl.css': [ 'css/style.rtl.css' ]
				}
			}
		},

		clean: {
			dist: {
				src: [
					'css/style.css',
					'css/style.rtl.css',
					'listify'
				]
			}
		},

		cssjanus: {
			theme: {
				options: {
					swapLtrRtlInUrl: false
				},
				files: [
					{
						src: 'css/style.css',
						dest: 'css/style.rtl.css'
					}
				]
			}
		},

		makepot: {
			theme: {
				options: {
					type: 'wp-theme'
				}
			}
		},

		glotpress_download: {
			theme: {
				options: {
					url: 'https://astoundify.com/glotpress',
					domainPath: 'languages',
					slug: 'listify',
					textdomain: 'listify',
					formats: [ 'mo', 'po' ],
					file_format: '%domainPath%/%wp_locale%.%format%',
					filter: {
						translation_sets: false,
						minimum_percentage: 50,
						waiting_strings: false
					}
				}
			}
		},

		checktextdomain: {
			standard: {
				options:{
					force: true,
					text_domain: 'listify',
					create_report_file: false,
					correct_domain: true,
					keywords: [
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
				files: [{
					src: ['**/*.php','!node_modules/**'],
					expand: true,
				}],
			},
		},

		bump: {
			options: {
				files: ['package.json', 'readme.txt', 'style.css'],
				push: false,
				createTag: false,
				commitMessage: 'Version bump %VERSION%',
				commitFiles: ['package.json', 'readme.txt', 'style.css']
			}
		},

		copy: {
			build: {
				src: [
					'**',
					'**/**',
					'!node_modules/**',
					'!**/node_modules/**',
				],
				dest: 'listify/',
			},
		},

		zip: {
			'<%= pkg.name %>-<%= pkg.version %>.zip': ['listify/**'],
		},

	});

	grunt.loadNpmTasks( 'grunt-contrib-watch' );
	grunt.loadNpmTasks( 'grunt-contrib-concat' );
	grunt.loadNpmTasks( 'grunt-contrib-cssmin' );
	grunt.loadNpmTasks( 'grunt-contrib-uglify' );
	grunt.loadNpmTasks( 'grunt-contrib-clean' );
	grunt.loadNpmTasks( 'grunt-contrib-jshint' );
	grunt.loadNpmTasks( 'grunt-sass' );
	grunt.loadNpmTasks( 'grunt-wp-i18n' );
	grunt.loadNpmTasks( 'grunt-cssjanus' );
	grunt.loadNpmTasks( 'grunt-exec' );
	grunt.loadNpmTasks( 'grunt-potomo' );
	grunt.loadNpmTasks( 'grunt-jsonlint' );
	grunt.loadNpmTasks( 'grunt-glotpress' );
	grunt.loadNpmTasks( 'grunt-checktextdomain' );
	grunt.loadNpmTasks( 'grunt-bump' );
	grunt.loadNpmTasks( 'grunt-contrib-copy' );
	grunt.loadNpmTasks( 'grunt-zip' );

	// register task
	grunt.registerTask('default', ['watch']);
	grunt.registerTask( 'i18n', [ 'makepot', 'glotpress_download' ] );
	grunt.registerTask( 'clean', ['clean'] );

	grunt.registerTask('build', [ 'jsonlint', 'uglify', 'sass', 'concat', 'cssjanus', 'cssmin', 'i18n', 'clean' ]);
	grunt.registerTask( 'build_zip', [ 'copy', 'zip', 'clean' ] );
};
