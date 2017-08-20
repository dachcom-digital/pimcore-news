pimcore.registerNS('pimcore.object.classes.data.newsTypeSelect');
pimcore.object.classes.data.newsTypeSelect = Class.create(pimcore.object.classes.data.data, {

    type: 'newsTypeSelect',

    allowIn: {
        object: true,
        objectbrick: false,
        fieldcollection: false,
        localizedfield: false
    },

    initialize: function (treeNode, initData) {
        this.type = 'newsTypeSelect';
        this.initData(initData);
        this.treeNode = treeNode;
    },

    getTypeName: function () {
        return t('news_type_select');
    },

    getIconClass: function () {
        return 'pimcore_icon_select';
    },

    getGroup: function () {
        return 'select';
    }
});
