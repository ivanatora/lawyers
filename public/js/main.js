var sToken = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6ImI4NzYzZjZkNTA2MmJlNzg5NWExMGRjY2VmZWRhM2MwZGUwMjM1NjNlOTM3NGIwNTBjMzNlYmNiYjhkOGI3YTAwOTAxYTcyZmE2MzA5ZmYwIn0.eyJhdWQiOiIyIiwianRpIjoiYjg3NjNmNmQ1MDYyYmU3ODk1YTEwZGNjZWZlZGEzYzBkZTAyMzU2M2U5Mzc0YjA1MGMzM2ViY2JiOGQ4YjdhMDA5MDFhNzJmYTYzMDlmZjAiLCJpYXQiOjE0OTY5OTgzNzEsIm5iZiI6MTQ5Njk5ODM3MSwiZXhwIjoxNTI4NTM0MzcxLCJzdWIiOiIzIiwic2NvcGVzIjpbXX0.lEe8mV20nPDdlARX__xjFtW9ELBlSGptnH1AM_22y5bkUbpETIA39YlFXNbPpJydbx2aciiLZNi1PGWPKxslfsuGc10O-OFI6_9YVm9AjAczxH0wDME4YBeLetMksBJPOupo2X2va8VRyb_cYSD9pnC6vpKiGrCCL9foGYs8IB9qDbQHOCTS07aItixK4HQvnAsoBC0D8UBG15Wb4UMgfjxyLCAuw5iFjfTAyJMTVpyl38qPC4hF-yvuPfRCyigdmtBBm8P2DpzJa9tUiI6zPNMu2XT7ZlC6HdNop1s7RGs5XfTgyNQc9Ym_tCZ7GrUtWznnIN3Jbfh5kzgvolbH_-ou3OVKge4oufcNyuU4Z2uYdoyMJF7XOkeTmLFwqz-ppkWUWv9Q1_c07eRBd00UjA3_ZD_BOVinfXy6rdQRu37kGVAqXQS1ouQO_H3Uu5JpnW1RvmSAJQvQSQovrgWOuN2qGMCxJCSGKGu4jXacpmOx1U0SgjydGM1K7ICzaQvE6jxWHl0-n_sznPLAXtAPETDWJ51gywpIyoF80c-joj-eSnH8VrID0PW43dvItksQGmQOgW9rWyQrZxMoCoo4oWHjD2L2VY1RBCBL9oOc6QK2TB4C3eZtaaNam_kPkGMTbyeP-yFcA159HmVAzxTKiaSwZT177TAvtgMoOnDpP_I';
Ext.Ajax.setDefaultHeaders({
'Content-type': 'application/json',
        'Authorization': 'Bearer ' + sToken
})
                                
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
                    fieldLabel: 'E-mail',
                    name: 'username',
                    allowBlank: false
                }, {
                    xtype: 'textfield',
                    fieldLabel: 'Password',
                    name: 'password',
                    inputType: 'password',
                    allowBlank: false
                }, {
                    xtype: 'displayfield',
                    fieldLabel: 'Hint',
                    value: 'Try lawyer@test.com / lawyer or customer@test.com / customer for demo access'
                }
            ],
            buttons: [
                {
                    text: 'Register',
                    handler: function(){
                        me.getRegisterWindow();
                    }
                },{
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
                                    'Content-type': 'application/json',
                                    'Authorization': 'Bearer ' + sToken
                                })

                                Ext.Ajax.request({
                                    url: '/api/v1/me',
                                    success: function (res) {
                                        res = Ext.decode(res.responseText);
                                        if (res.data.type == 'customer') {
                                            me.getCustomerWindow();
                                        }
                                        if (res.data.type == 'lawyer') {
                                            me.getLawyerWindow();
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
            width: 400,
            items: [form]
        });
        win.show();
    },
    
    getRegisterWindow: function(){
        var me = this;
        
        var form = Ext.create('Ext.form.Panel', {
            bodyPadding: 10,
            defaultButton: 'okButton',
            referenceHolder: true,
            defaults: {
                allowBlank: false
            },
            items: [
                {
                    xtype: 'textfield',
                    name: 'first_name',
                    fieldLabel: 'First name'
                },{
                    xtype: 'textfield',
                    name: 'last_name',
                    fieldLabel: 'Last name'
                },{
                    xtype: 'textfield',
                    name: 'email',
                    fieldLabel: 'E-mail',
                    vtype: 'email'
                },{
                    xtype: 'textfield',
                    name: 'password',
                    fieldLabel: 'Password',
                    inputType: 'password'
                },{
                    xtype: 'textfield',
                    name: 'repeat_password',
                    fieldLabel: 'Repeat password',
                    inputType: 'password'
                },{
                    xtype: 'combo',
                    store: Ext.create('Ext.data.Store', {
                        fields: ['id', 'name'],
                        data: [
                            ['customer', 'Customer'],
                            ['lawyer', 'Lawyer']
                        ]
                    }),
                    name: 'type',
                    fieldLabel: 'User type',
                    value: 'customer',
                    valueField: 'id',
                    displayField: 'name',
                    forceSelection: true,
                    editable: false
                }
            ],
            buttons: [
                {
                    text: 'Register',
                    formBind: true,
                    reference: 'okButton',
                    handler: function(){
                        var oValues = this.up('form').getValues();
                        if (oValues.password != oValues.repeat_password) {
                            Ext.Msg.alert('Error', 'Password does not match');
                            return;
                        }
                        
                        Ext.Ajax.request({
                            url: '/users/register',
                            jsonData: {
                                user: oValues
                            },
                            success: function (res) {
                                res = Ext.decode(res.responseText);
                                if (res.success) {
                                    Ext.Msg.alert('Done', 'You are now ready to log in');
                                    win.close();
                                } else {
                                    Ext.Msg.alert('Error', res.error);
                                }
                            },
                            failure: function (res) {
                                res = Ext.decode(res.responseText);
                                console.log('fail1', res)
                            }
                        })
                    }
                },{
                    text: 'Cancel',
                    handler: function(){
                        win.close();
                    }
                }
            ]
        })
        
        var win = Ext.create('Ext.window.Window', {
            title: 'Register',
            modal: true,
            items: [form]
        })
        win.show();
    },

    getCustomerWindow: function(){
        var oModule = new CustomerWindow();
        oModule.createWindow();
    },
    
    getLawyerWindow: function(){
        console.log('@TODO: createLawyerWindow');
    }
});