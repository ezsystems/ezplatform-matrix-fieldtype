const path = require('path');

module.exports = (eZConfig, eZConfigManager) => {
    eZConfigManager.add({
        eZConfig,
        entryName: 'ezplatform-admin-ui-content-type-edit-js',
        newItems: [path.resolve(__dirname, '../public/js/scripts/admin.contenttype.matrix.js')],
    });

    eZConfigManager.add({
        eZConfig,
        entryName: 'ezplatform-admin-ui-content-edit-parts-js',
        newItems: [path.resolve(__dirname, '../public/js/scripts/fieldType/ezmatrix.js')],
    });

    eZConfigManager.add({
        eZConfig,
        entryName: 'ezplatform-admin-ui-content-edit-parts-css',
        newItems: [path.resolve(__dirname, '../public/scss/matrix.scss')],
    });
};
