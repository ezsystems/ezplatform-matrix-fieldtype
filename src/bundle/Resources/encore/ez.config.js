const path = require('path');

module.exports = (Encore) => {
    Encore
        .addEntry('ezplatform-matrix-fieldtype-common-js', [
            path.resolve(__dirname, '../public/js/scripts/fieldType/ezmatrix.js'),
            path.resolve(__dirname, '../public/js/scripts/admin.contenttype.matrix.js'),
        ]);
};
