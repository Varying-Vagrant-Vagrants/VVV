module.exports = function(grunt) {

	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),
		markdown:{
			all:{
				files:[
					{
						expand: true,
						src: 'README.md',
						dest: '',
						ext: '.html'
					}
				]
			}
		},
		watch:{
			files: ['README.md'],
			tasks: ['markdown']
		}	
	});

	grunt.loadNpmTasks('chains-markdown');
	grunt.loadNpmTasks('grunt-contrib-watch');
	grunt.registerTask('default', ['markdown','watch']);

};