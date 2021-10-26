(function(global, doc) {
    const SELECTOR_SETTINGS_COLUMNS = '.ez-matrix-settings__columns';
    const SELECTOR_COLUMN = '.ez-matrix-settings__column';
    const SELECTOR_COLUMNS_CONTAINER = '.ibexa-table__body';
    const SELECTOR_COLUMN_CHECKBOX = '.ez-matrix-settings__column-checkbox';
    const SELECTOR_ADD_COLUMN = '.ibexa-btn--add-column';
    const SELECTOR_REMOVE_COLUMN = '.ibexa-btn--remove-column';
    const SELECTOR_TEMPLATE = '.ez-matrix-settings__column-template';
    const NUMBER_PLACEHOLDER = /__number__/g;
    const getNextIndex = (parentNode) => {
        return parentNode.dataset.nextIndex++;
    };
    const findCheckedColumns = (parentNode) => {
        return parentNode.querySelectorAll(`${SELECTOR_COLUMN_CHECKBOX}:checked`);
    };
    const updateDisabledState = (parentNode) => {
        const isEnabled = findCheckedColumns(parentNode).length > 0;
        const methodName = isEnabled ? 'removeAttribute' : 'setAttribute';

        parentNode.querySelectorAll(SELECTOR_REMOVE_COLUMN).forEach((btn) => btn[methodName]('disabled', !isEnabled));
    };
    const addItem = (event) => {
        const settingsNode = event.target.closest(SELECTOR_SETTINGS_COLUMNS);
        const template = settingsNode.querySelector(SELECTOR_TEMPLATE).innerHTML;
        const node = settingsNode.querySelector(SELECTOR_COLUMNS_CONTAINER);

        node.insertAdjacentHTML('beforeend', template.replace(NUMBER_PLACEHOLDER, getNextIndex(node)));

        initColumns(settingsNode);
    };
    const removeItems = (event) => {
        const settingsNode = event.target.closest(SELECTOR_SETTINGS_COLUMNS);

        findCheckedColumns(settingsNode).forEach((btn) => btn.closest(SELECTOR_COLUMN).remove());

        initColumns(settingsNode);
    };
    const checkColumn = (event) => {
        const settingsNode = event.target.closest(SELECTOR_SETTINGS_COLUMNS);

        updateDisabledState(settingsNode);
    };
    const initColumns = (parentNode) => {
        updateDisabledState(parentNode);

        parentNode.querySelectorAll(SELECTOR_COLUMN_CHECKBOX).forEach((btn) => {
            btn.removeEventListener('click', checkColumn, false);
            btn.addEventListener('click', checkColumn, false);
        });
    };
    const initComponent = (container) => {
        container.querySelector(SELECTOR_ADD_COLUMN).addEventListener('click', addItem, false);
        container.querySelector(SELECTOR_REMOVE_COLUMN).addEventListener('click', removeItems, false);

        initColumns(container);
    };

    doc.querySelectorAll(SELECTOR_SETTINGS_COLUMNS).forEach((container) => {
        initComponent(container);
    });

    doc.body.addEventListener(
        'ibexa-drop-field-definition',
        (event) => {
            const { nodes } = event.detail;

            nodes.forEach((container) => {
                if (!container.querySelector(SELECTOR_SETTINGS_COLUMNS)) {
                    return;
                }

                initComponent(container);
            });
        },
        false,
    );
})(window, document);
