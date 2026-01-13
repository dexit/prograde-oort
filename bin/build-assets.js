/**
 * Simple script to copy Ace Editor from node_modules to assets/vendor for redistribution.
 */

const fs = require('fs');
const path = require('path');

const srcDir = path.join(__dirname, '../node_modules/ace-builds/src-min-noconflict');
const destDir = path.join(__dirname, '../assets/vendor/ace');

function copyFolderSync(from, to) {
    if (!fs.existsSync(to)) {
        fs.mkdirSync(to, { recursive: true });
    }
    fs.readdirSync(from).forEach(element => {
        if (fs.lstatSync(path.join(from, element)).isFile()) {
            fs.copyFileSync(path.join(from, element), path.join(to, element));
        } else {
            copyFolderSync(path.join(from, element), path.join(to, element));
        }
    });
}

if (fs.existsSync(srcDir)) {
    console.log('Copying Ace builds to assets...');
    copyFolderSync(srcDir, destDir);
    console.log('Successfully copied Ace Editor assets.');
} else {
    console.error('Error: ace-builds not found in node_modules. Run npm install first.');
    process.exit(1);
}
