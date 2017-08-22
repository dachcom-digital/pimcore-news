pimcore.registerNS('pimcore.object.classes.data.entryTypeSelect');
pimcore.object.classes.data.entryTypeSelect = Class.create(pimcore.object.classes.data.data, {

    type: 'entryTypeSelect',

    allowIn: {
        object: true,
        objectbrick: false,
        fieldcollection: false,
        localizedfield: false
    },

    initialize: function (treeNode, initData) {
        this.type = 'entryTypeSelect';
        this.initData(initData);
        this.treeNode = treeNode;
    },

    getTypeName: function () {
        return t('news.type_select');
    },

    getIconClass: function () {
        return 'pimcore_icon_select';
    },

    getGroup: function () {
        return 'select';
    }
});
