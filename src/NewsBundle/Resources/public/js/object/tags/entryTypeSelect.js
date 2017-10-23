
pimcore.registerNS('pimcore.object.tags.entryTypeSelect');
pimcore.object.tags.entryTypeSelect = Class.create(pimcore.object.tags.select, {

    type: 'entryTypeSelect',

    getGridColumnEditor: function (field) {
        //grid column configuration for news type is currently not supported
        return false;
    },

    getLayoutEdit: function () {

        var obj = this.getObject(),
            store = new Ext.data.JsonStore({
                proxy: {
                    type: 'ajax',
                    url: '/admin/news/settings/get-entry-types',
                    extraParams:  {
                        'objectId' : obj.id
                    },
                    reader: {
                        type: 'json',
                        rootProperty: 'options',
                        successProperty: 'success',
                        messageProperty: 'message'
                    }
                },
                fields: ['key', 'value', 'custom_layout_id', 'default'],
                listeners: {
                    load: function(store, records, success, operation) {
                        if (!success) {
                            pimcore.helpers.showNotification(t('error'), t('error_loading_options'), 'error', operation.getError());
                        }
                    }.bind(this)
                },
                autoLoad: true
            }),
            options = {
                name: this.fieldConfig.name,
                triggerAction: 'all',
                editable: true,
                typeAhead: true,
                forceSelection: true,
                selectOnFocus: true,
                fieldLabel: this.fieldConfig.title,
                store: store,
                itemCls: 'object_field',
                width: 300,
                displayField: 'key',
                valueField: 'value',
                queryMode: 'local',
                autoSelect: false,
                autoLoadOnValue: true,
                value: this.data,
                listConfig: {
                    getInnerTpl: function() {
                        return '<tpl for="."><tpl if="published == true">{key}<tpl else><div class="x-combo-item-disabled x-item-disabled">{key}</div></tpl></tpl>';
                    }
                }
            };

        if (this.fieldConfig.width) {
            options.width = this.fieldConfig.width;
        }

        this.component = new Ext.form.ComboBox(options);

        this.component.getStore().on('load',function() {

            var componentValue = this.component.getValue();

            //changedEntryType only exists if object just has been reloaded!
            if (typeof obj.options === 'object' && obj.options.changedEntryType !== undefined) {
                componentValue = obj.options.changedEntryType;
                //happens after a new object has been created.
            } else if (this.component.getValue() === null) {
                var firstRecord = this.component.getStore().getAt(0);
                componentValue = firstRecord.get('default');
                //check if default exists. if not, use first record.
                if(!this.component.getStore().findRecord('value', componentValue)) {
                    componentValue = this.component.getStore().getAt(0).get('value');
                }
            }

            var currentLayoutId = obj.data.currentLayoutId,
                componentLayoutRecord = this.component.getStore().findRecord('value', componentValue),
                componentLayoutId = componentLayoutRecord ? componentLayoutRecord.get('custom_layout_id') : this.component.getStore().getAt(0).get('custom_layout_id'),
                objectLayoutRecord = this.component.getStore().findRecord('custom_layout_id', currentLayoutId),
                objectLayoutId = objectLayoutRecord ? objectLayoutRecord.get('custom_layout_id') : currentLayoutId;

            //if there is still a miss-match between store layout and current layout: reset to object layout!
            if (objectLayoutRecord && componentLayoutId !== objectLayoutId) {
                componentValue = objectLayoutRecord.get('value');
            }

            this.component.setValue(componentValue);

            if (this.component.getStore().getCount() === 1) {
                this.component.setReadOnly(true);
            }

        }.bind(this));

        this.component.addListener('beforeselect', function (combo, record, index, e) {

            var currentLayout = obj.data.currentLayoutId == '' || isNaN(obj.data.currentLayoutId) ? null : parseInt(obj.data.currentLayoutId);

            if(record.data.custom_layout_id === currentLayout) {
                return true;
            }

            if (this.canContinue) {
                this.canContinue = false;
                return true;
            } else {
                if(obj.isDirty()) {
                    Ext.Msg.confirm(
                        t('element_has_unsaved_changes'),
                        t('element_unsaved_changes_message'),
                        function(buttonId) {
                            if (buttonId === 'yes') {
                                this.canContinue = true;
                                combo.select(record);
                                combo.fireEvent('select', combo, record, true);
                            } else {
                                this.canContinue = false;
                            }
                        },
                        this
                    );
                    return false;
                }
                return true;
            }
        });

        this.component.addListener('select', function (combo, record) {

            var currentLayout = obj.data.currentLayoutId == '' || isNaN(obj.data.currentLayoutId) ? null : parseInt(obj.data.currentLayoutId);

            if(record.data.custom_layout_id === currentLayout) {
                return true;
            }

            var clId = record.data.custom_layout_id = null, options = {};

            this.component.setDisabled(true);

            Ext.Ajax.request({
                url: '/admin/news/settings/change-entry-type',
                params: {
                    objectId: obj.id,
                    entryTypeId: record.data.value
                },
                success: function(response) {
                    options.layoutId = clId;
                    options.changedEntryType = combo.getValue();
                    this.reloadObject(obj, options);

                }.bind(this)
            });

        }.bind(this));

        return this.component;
    },

    reloadObject: function(obj, options) {

        window.setTimeout(function (id, options) {
            pimcore.helpers.openObject(id, 'object', options);
        }.bind(window, obj.id, options), 500);

        pimcore.helpers.closeObject(obj.id);

    }
});