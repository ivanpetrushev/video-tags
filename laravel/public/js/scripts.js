Ext.Loader.setConfig('enabled', true);

Ext.Loader.setConfig('paths', {
    'App': '/js/app',
});

Ext.require('App.main')


Ext.application({
    name : 'Fiddle',

    launch : function() {
        Ext.create('App.main').show();
    }
});