var sToken = '';

Ext.application({
    name: 'Lawyers and Customers',

    launch: function () {
        var me = this;

        var form = Ext.create('Ext.form.Panel', {
            url: '/oauth/token',
            bodyPadding: 10,
            defaultButton: 'okButton',
            referenceHolder: true,
            items: [
                {
                    xtype: 'textfield',
                    fieldLabel: 'Username',
                    name: 'username',
                    allowEmpty: false
                }, {
                    xtype: 'textfield',
                    fieldLabel: 'Password',
                    name: 'password',
                    inputType: 'password',
                    allowEmpty: false
                }, {
                    xtype: 'displayfield',
                    fieldLabel: 'Hint',
                    value: 'Try lawyer/lawyer or customer/customer for demo access'
                }
            ],
            buttons: [
                {
                    text: 'Login',
                    formBind: true,
                    reference: 'okButton',
                    handler: function () {
                        var oValues = this.up('form').getValues();
                        oValues.grant_type = 'password';
                        oValues.client_id = 2;
                        oValues.client_secret = '8G6ZTi4NGrYEf1TGk0uRwqH1041ASMp9BQZahF3D';

                        Ext.Ajax.request({
                            url: '/oauth/token',
                            method: 'post',
                            params: oValues,
                            success: function (res) {
                                res = Ext.decode(res.responseText);
                                sToken = res.access_token;

                                Ext.Ajax.setDefaultHeaders({
                                    'Authorization': 'Bearer ' + sToken
                                })

                                Ext.Ajax.request({
                                    url: '/api/v1/me',
                                    success: function (res) {
                                        res = Ext.decode(res.responseText);
                                        if (res.type == 'customer') {
                                            me.createCustomerWindow();
                                        }
                                        if (res.type == 'lawyer') {
                                            me.createLawyerWindow();
                                        }
                                        win.hide();
                                    },
                                    failure: function (res) {
                                        console.log('fail', res)
                                    }
                                })

                            },
                            failure: function (res) {
                                Ext.Msg.alert('Error', 'Wrong credentials');
                            }
                        })
                    }
                }
            ]
        })
        
        var win = Ext.create('Ext.window.Window', {
            title: 'Login',
            closable: false,
            items: [form]
        });
        win.show();
    },

    createCustomerWindow: function(){
        
    },
    
    createLawyerWindow: function(){
        console.log('@TODO: createLawyerWindow');
    }
});