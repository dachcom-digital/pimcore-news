pimcore.registerNS('pimcore.plugin.news');

pimcore.plugin.news = Class.create(pimcore.plugin.admin, {
    getClassName: function() {
        return 'pimcore.plugin.news';
    },

    initialize: function() {
        pimcore.plugin.broker.registerPlugin(this);
    },

    postOpenObject: function (obj) {
        if(obj.data.general.o_className === 'NewsEntry') {
            if(obj.data._invalidEntryType === true) {
                Ext.MessageBox.show({
                    title: t('news.permission_error'),
                    msg: t('news.no_permission_for_entry_type') + ' "' + obj.data.data.entryType + '".',
                    icon: Ext.MessageBox.ERROR,
                    buttons: Ext.Msg.OK,
                    fn: function() {
                        pimcore.globalmanager.remove('object_' + obj.id);
                        pimcore.helpers.forgetOpenTab('object_' + obj.id + '_object');
                        pimcore.helpers.forgetOpenTab('object_' + obj.id + '_variant');
                        pimcore.helpers.closeObject(obj.id);
                    }
                });
            }
        }
    }
});

new pimcore.plugin.news();