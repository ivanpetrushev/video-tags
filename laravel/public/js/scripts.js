Ext.Loader.setConfig('enabled', true);

Ext.Loader.setConfig('paths', {
    'App': '/js/app',
});

Ext.require('App.main')


Ext.application({
    name : 'Video Tags',

    launch : function() {
        Ext.create('App.main').createWindow();
    }
});