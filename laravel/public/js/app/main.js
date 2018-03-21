Ext.define('App.main', {
    extend: 'Ext.window.Window',

    createWindow: function () {
        var me = this;

        var win = Ext.create('Ext.window.Window', {
            closable: false,
            width: '90%',
            height: '90%',
            header: false,
            layout: 'border',
            items: [
                me.getWestPanel(),
                me.getCenterPanel(),
                me.getEastPanel()
            ]
        })
        win.show();
    },

    getWestPanel: function() {
        var me = this;
        if (! me.westPanel) {
            me.westPanel = Ext.create('Ext.tree.Panel', {
                title: 'Directories',
                region: 'west',
                border: true,
                width: 300,
                split: true,
                store: Ext.create('Ext.data.TreeStore', {
                    root: {
                        expanded: true,
                        children: [
                            {text: '2018', expanded: true, children: [
                                    {text: '2018_03_18_mavic_iztochen', expanded: true, children: [
                                            {text: 'DJI_0006.mov', leaf: true},
                                            {text: 'DJI_0016.mov', leaf: true},
                                            {text: 'DJI_0018.mov', leaf: true}
                                        ]
                                    },
                                    {text: '2018_03_20_mavic_marica', expanded: true, children: [
                                            {text: 'DJI_1010.mov', leaf: true},
                                            {text: 'DJI_1012.mov', leaf: true}
                                        ]}
                                ]
                            }
                        ]
                    }
                }),
                rootVisible: false,
                tbar: [
                    {
                        xtype: 'button',
                        iconCls: 'x-fa fa-plus color-green',
                        tooltip: 'Add new directory to be scanned',
                        handler: function() {
                            me.getAddDirectoryWindow();
                        }
                    }, {
                        xtype: 'button',
                        iconCls: 'x-fa fa-times color-red'
                    }
                ]
            })
        }
        return me.westPanel;
    },

    getCenterPanel: function() {
        var me = this;
        if (! me.centerPanel) {
            me.centerPanel = Ext.create('Ext.panel.Panel', {
                title: 'Editor',
                region: 'center',
                layout: 'border',
                items: [
                    {
                        region: 'center',
                        border: true,
                        id: 'video-container',
                        // xtype: 'video',
                        // url: '/data/DJI_0010.MOV',
                        // width: 500,
                        // controls: true
                        html: '<video controls width="600" autoplay id="my-video" class="video-js" data-setup=\'{}\' style="margin: 0 auto; width: 600px;">' +
                        '<source src="/data/DJI_0010.MOV" type="video/mp4">' +
                        '</video>'
                    },
                    {
                        region: 'south',
                        height: 300,
                        id: 'timeline-container',
                        listeners: {
                            render: function(){
                                var container = document.getElementById('timeline-container-innerCt');

                                // Create a DataSet (allows two way data-binding)
                                var items = new vis.DataSet([
                                    {id: 1, content: 'Пловдив', start: '2013-04-20'},
                                    {id: 2, content: 'река', start: '2013-04-14'},
                                    {id: 3, content: 'Марица', start: '2013-04-18'},
                                    {id: 4, content: 'залез', start: '2013-04-16', end: '2013-04-19'},
                                    {id: 5, content: 'item 5', start: '2013-04-25'},
                                    {id: 6, content: 'item 6', start: '2013-04-27'}
                                ]);

                                // Configuration for the Timeline
                                var options = {};

                                // Create a Timeline
                                var timeline = new vis.Timeline(container, items, options);
                            }
                        }
                    }
                ]
            })
        }
        return me.centerPanel;
    },

    getEastPanel: function() {
        var me = this;
        if (! me.eastPanel) {
            me.eastPanel = Ext.create('Ext.grid.Panel', {
                title: 'Taglist',
                xtype: 'grid',
                region: 'east',
                border: true,
                split: true,
                width: 300,
                store: Ext.create('Ext.data.Store', {
                    fields:[ 'name', 'email', 'phone'],
                    data: [
                        { tag: 'Пловдив', start: '00:12', duration: '13'},
                        { tag: 'река', start: '00:22', duration: '33'},
                        { tag: 'Марица', start: '00:32', duration: '11'},
                        { tag: 'залез', start: '00:12', duration: '5'}
                    ]
                }),
                columns: [
                    {text: 'Tag', dataIndex: 'tag', flex: 1},
                    {text: 'Start', dataIndex: 'start', flex: 1},
                    {text: 'Duration', dataIndex: 'duration', flex: 1}
                ],
                tbar: [
                    {
                        xtype: 'button',
                        iconCls: 'x-fa fa-plus color-green'
                    }, {
                        xtype: 'button',
                        iconCls: 'x-fa fa-copy color-blue'
                    }, {
                        xtype: 'button',
                        iconCls: 'x-fa fa-times color-red'
                    }
                ]
            })
        }
        return me.eastPanel;
    },

    getAddDirectoryWindow: function() {
        Ext.Msg.prompt('Add directory', 'Enter path relative to public/data', function(btn, val) {
            Ext.Ajax.request({
                url: '/directory',
                method: 'POST',
                jsonData: {
                    directory: {
                        path: val
                    }
                },
                success: function(response) {
                    response = Ext.decode(response.responseText);
                    if (response.success) {
                        console.log("yay!")
                    } else {
                        Ext.Msg.alert('Error', response.error);
                    }
                }
            })
        })
    }
})