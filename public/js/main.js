var sToken = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6ImU5ZTVmM2I1ZTJmNGI2MDA4YmI5NWY2MTczMzAxZjQ1MGYyZWFlYTVlZTI0ZDY5OWUyYzZjNWFiMGQ4YmFmNGJlOTI2NjBlOWFmNWI0MzBlIn0.eyJhdWQiOiIyIiwianRpIjoiZTllNWYzYjVlMmY0YjYwMDhiYjk1ZjYxNzMzMDFmNDUwZjJlYWVhNWVlMjRkNjk5ZTJjNmM1YWIwZDhiYWY0YmU5MjY2MGU5YWY1YjQzMGUiLCJpYXQiOjE0OTcwMDM3ODcsIm5iZiI6MTQ5NzAwMzc4NywiZXhwIjoxNTI4NTM5Nzg3LCJzdWIiOiI0Iiwic2NvcGVzIjpbXX0.M9jaWN99NftGj-Lex0ZhdkfPqPwmeasH5rMlyjqENA0OxTH_M-01_AADmGensf3RJwBmCqO7I3Hm1yZUG6bQg5jCtk4XT3Is7Himw39LSIiBiOYPhxWN3MYK8iQHmr9yaLc6zydfe_QveMqYdxvD5K-2x_7C0VJ84TbdcFZJ4AY6Zw_A9xKcxyNUnKI9zRLf7ajC_H9cAVFH73mfsNM538g_jvR0C6f_oCptTVQgZubr4AUBsYuHUsW66oW4Ty1iwATKofoo-KTvCnBZLHLlL9JZWf_f28LYWnGAh50yDEsMFoSbBCsZy54uWd3Yn0qMUjc05-tPluYgHiloLJJJa75F36Zmu3XKWbSxgi-FkuKN47jsygKOtFKgQQJlEupl0SQRtHozYJ8cbtg2QeAklCgPxhmgTIQg_1vjs9etvx3ZceZMhoZdaJR9jfbOy2Zlb-bW0au06ErShVqxf4BchIb7cH0XgIXDki2N6-b1C2SiUezkjGfcnhUATcSIVOxuuaIXUoy-Rzu7XB88OqvI5oL8NPpI9R0PeIFIvux1i0K2FUbf5-yOJVV9IIWr0tRefSfUOWyzFvXj1c4jfG20OBs0g0IwglERa0PCKpkyeNl2-BbO3LPdGhYMyMNDcns8fkqMY8dtex1GLF_54UMj1R_Q6Wlh5qMerQLE_aeQJXE';

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
        var oModule = new LawyerWindow();
        oModule.createWindow();
    }
});