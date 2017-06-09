Ext.define('LawyerWindow', {
    extend: 'Ext.window.Window',

    constructor: function () {

    },

    renderDate: function (val) {
        var dt = Ext.Date.parse(val, 'Y-m-d H:i:s');
        if (!dt) {
            dt = Ext.Date.parse(val, 'Y-m-d');
        }
        return Ext.Date.format(dt, "d.m.Y");
    },

    renderTime: function (val) {
        var dt = Ext.Date.parse(val, 'H:i:s');
        return Ext.Date.format(dt, "H:i");
    },

    getStore: function () {
        var me = this;

        if (!me.store) {
            me.store = Ext.create('Ext.data.Store', {
                fields: [
                    'customer_user_id', 'lawyer_user_id', 'status', 
                    'lawyer_names', 'schedule_date', 'schedule_time',
                    'is_conflicting'
                ],
                pageSize: 5,
                proxy: {
                    type: 'ajax',
                    url: '/api/v1/appointments',
                    reader: {
                        type: 'json',
                        rootProperty: 'data',
                        totalProperty: 'total'
                    },
                    headers: {
                        'Authorization': 'Bearer ' + sToken
                    }
                },
                autoLoad: true,
                remoteSort: true
            })
        }
        return me.store;
    },

    getGrid: function () {
        var me = this;

        if (!me.grid) {
            me.grid = Ext.create('Ext.grid.Panel', {
                store: me.getStore(),
                columns: [
                    {
                        xtype: 'actioncolumn',
                        width: 24,
                        items: [
                            {
                                getClass: function(v, meta, rec){
                                    if (rec.get('is_conflicting')) {
                                        return 'x-fa fa-exclamation-triangle';
                                    }
                                    return false;
                                },
                                handler: function(grid, rowIndex, colIndex){
                                    var rec = grid.getStore().getAt(rowIndex);
                                    if (rec.get('is_conflicting')) {
                                        Ext.Msg.alert('Conflict', 'This appointment conflicts with another one. Please, change the date or time.', function(){
                                            me.getAddEditWindow(rec.data);
                                        });
                                    }
                                }
                            }
                        ]
                    },
                    {text: 'Customer', dataIndex: 'customer_names', flex: 1},
                    {text: 'For Date', dataIndex: 'schedule_date', width: 100, renderer: me.renderDate},
                    {text: 'At', dataIndex: 'schedule_time', width: 80, renderer: me.renderTime},
                    {text: 'Status', dataIndex: 'status', flex: 1}
                ],
                bbar: {
                    xtype: 'pagingtoolbar',
                    displayInfo: true,
                    store: me.getStore()
                },
                tbar: [
                    {
                        xtype: 'button',
                        text: 'Approve',
                        handler: function () {
                            var aSelection = me.getGrid().getSelectionModel().getSelection();
                            if (Ext.isEmpty(aSelection)) {
                                Ext.Msg.alert('Info', 'Select a record for approving')
                                return;
                            }

                            Ext.Ajax.request({
                                url: '/api/v1/appointments/'+aSelection[0].data.id,
                                jsonData: {
                                    _method: 'PUT',
                                    appointment: {
                                        status: 'approved'
                                    }
                                },
                                success: function(){
                                    me.getStore().load();
                                }
                            })
                        }
                    }, {
                        xtype: 'button',
                        text: 'Reject',
                        handler: function () {
                            var aSelection = me.getGrid().getSelectionModel().getSelection();
                            if (Ext.isEmpty(aSelection)) {
                                Ext.Msg.alert('Info', 'Select a record for rejecting')
                                return;
                            }

                            Ext.Ajax.request({
                                url: '/api/v1/appointments/'+aSelection[0].data.id,
                                jsonData: {
                                    _method: 'PUT',
                                    appointment: {
                                        status: 'rejected'
                                    }
                                },
                                success: function(){
                                    me.getStore().load();
                                }
                            })
                        }
                    },{
                        xtype: 'button',
                        text: 'Delete',
                        handler: function () {
                            var aSelection = me.getGrid().getSelectionModel().getSelection();
                            if (Ext.isEmpty(aSelection)) {
                                Ext.Msg.alert('Info', 'Select a record for deleting')
                                return;
                            }

                            Ext.Msg.show({
                                title: 'Removing',
                                msg: 'Are you sure?',
                                buttons: Ext.Msg.OKCANCEL,
                                icon: Ext.Msg.WARNING,
                                fn: function (btn) {
                                    if (btn == 'ok') {
                                        var aSelected = aSelection[0];
                                        var id = aSelected.get('id');
                                        if (id != 0) {
                                            Ext.Ajax.request({
                                                url: '/api/v1/appointments/' + aSelection[0].data.id,
                                                jsonData: {
                                                    _method: 'DELETE'
                                                },
                                                success: function () {
                                                    me.getStore().load();
                                                }
                                            })
                                        }
                                    }
                                }
                            })
                        }
                    },
                    '-',
                    {
                        xtype: 'textfield',
                        fieldLabel: 'Search by customer',
                        labelWidth: 120,
                        enableKeyEvents: true,
                        listeners: {
                            buffer: 1000,
                            keyup: function (cmp) {
                                var val = cmp.getValue();
                                me.getStore().getProxy().extraParams.query_customer = val;
                                me.getStore().loadPage(1);
                            }
                        }
                    }
                ]
            })
        }
        return me.grid;
    },

    getAddEditWindow: function (rec) {
        var me = this;

        var store = Ext.create('Ext.data.Store', {
            fields: ['id', 'user_names'],
            proxy: {
                type: 'ajax',
                url: '/api/v1/users/lawyers',
                reader: {
                    type: 'json',
                    rootProperty: 'data'
                },
                headers: {
                    'Authorization': 'Bearer ' + sToken
                }
            },
            autoLoad: true
        })

        var form = Ext.create('Ext.form.Panel', {
            bodyPadding: 10,
            defaultButton: 'okButton',
            referenceHolder: true,
            defaults: {
                allowBlank: false
            },
            items: [
                {
                    xtype: 'hidden',
                    name: 'id'
                }, {
                    xtype: 'combo',
                    name: 'lawyer_user_id',
                    fieldLabel: 'Lawyer',
                    store: store,
                    valueField: 'id',
                    displayField: 'user_names',
                    forceSelection: true,
                    allowBlank: false,
                    queryParam: 'query_name',
                    minChars: 2
                }, {
                    xtype: 'datefield',
                    name: 'schedule_date',
                    fieldLabel: 'Schedule date',
                    format: 'Y-m-d',
                    submitFormat: 'Y-m-d',
                    altFormats: 'Y-m-d'
                }, {
                    xtype: 'timefield',
                    name: 'schedule_time',
                    fieldLabel: 'Time',
                    submitFormat: 'H:i',
                    altFormats: 'H:i:s'
                }, {
                    xtype: 'textarea',
                    name: 'description',
                    fieldLabel: 'Description',
                    allowBlank: true
                }
            ],
            buttons: [
                {
                    reference: 'okButton',
                    text: 'Submit',
                    formBind: true,
                    handler: function () {
                        var oValues = this.up('form').getValues();

                        Ext.Ajax.request({
                            url: '/api/v1/appointments',
                            jsonData: {
                                appointment: oValues,
                                _method: 'POST'
                            },
                            success: function (res) {
                                res = Ext.decode(res.responseText);
                                if (res.success) {
                                    me.getStore().load();
                                    win.close();
                                } else {
                                    Ext.Msg.alert('Error', res.error);
                                }
                            }
                        })
                    }
                }
            ]
        })

        if (!Ext.isEmpty(rec)) {
            form.getForm().setValues(rec);
        }

        var win = Ext.create('Ext.window.Window', {
            title: 'New appointment',
            modal: true,
            items: [form]
        })
        win.show();
    },

    createWindow: function () {
        var me = this;

        var win = Ext.create('Ext.window.Window', {
            title: 'Lawyer Area: Appointments',
            layout: 'fit',
            width: 700,
            height: 500,
            items: [me.getGrid()]
        })
        win.show();
    }
})