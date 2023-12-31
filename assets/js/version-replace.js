const fs = require('fs-extra');
const replace = require('replace-in-file');

const pluginFiles = ['includes/**/*', 'src/*', 'ozopanel.php'];

const { version } = JSON.parse(fs.readFileSync('package.json'));

replace({
	files: pluginFiles,
	from: /RAKIB_SINCE/g,
	to: version,
});
