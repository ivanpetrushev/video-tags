Ext.define('App.main', {
    extend: 'Ext.window.Window',

    timeline: null,

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

    getTreeStore: function() {
        var me = this;
        if (! me.treeStore) {
            Ext.define('Filepath', {
                extend: 'Ext.data.Model',
                fields: [
                    // { name: 'id', type: 'int'},
                    { name: 'directory_id', type: 'int'},
                    { name: 'fullpath', type: 'string'},
                    { name: 'path', type: 'string'},
                    { name: 'filename', type: 'string'},
                    { name: 'duration', type: 'int'}
                ],
                proxy: {
                    type: 'ajax',
                    api: {
                        read: '/directory/tree'
                    }
                }
            });

            me.treeStore = Ext.create('Ext.data.TreeStore', {
                model: 'Filepath',
                root: {
                    name: 'data',
                    expanded: true
                }
            })

        }
        return me.treeStore;
    },

    getTagStore: function() {
        var me = this;
        if (! me.tagStore) {
            me.tagStore = Ext.create('Ext.data.Store', {
                fields: ['tag_name', 'start_time', 'duration', 'start_time_is', 'duration_is'],
                proxy: {
                    type: 'ajax',
                    url: '/file/tags',
                    reader: {
                        type: 'json',
                        rootProperty: 'data'
                    }
                }
            })
        }
        return me.tagStore;
    },

    getWestPanel: function () {
        var me = this;
        if (!me.westPanel) {
            me.westPanel = Ext.create('Ext.tree.Panel', {
                title: 'Directories',
                region: 'west',
                border: true,
                width: 300,
                split: true,
                store: me.getTreeStore(),
                rootVisible: false,
                columns: [
                    {
                        xtype: 'treecolumn',
                        text: 'File',
                        dataIndex: 'text',
                        flex: 1
                    }, {
                        text: 'Duration',
                        dataIndex: 'duration',
                        width: 80
                    }
                ],
                tbar: [
                    {
                        xtype: 'button',
                        iconCls: 'x-fa fa-plus color-green',
                        tooltip: 'Add new directory to be scanned',
                        handler: function () {
                            me.getAddDirectoryWindow();
                        }
                    }, {
                        xtype: 'button',
                        iconCls: 'x-fa fa-times color-red'
                    }
                ],
                listeners: {
                    selectionchange: function(cmp, selected) {
                        if (Ext.isEmpty(selected)) {
                            return;
                        }
                        rec = selected[0].data;
                        var url = '/data/' + rec.fullpath;
                        var player = videojs('my-video')
                        player.src({
                            type: 'video/mp4',
                            src: url
                        })

                        var dtStart = new Date('2001-01-01 00:00:00')
                        var dtEnd = new Date('2001-01-01 00:00:00')
                        var seconds = dtStart.getSeconds();
                        dtEnd.setSeconds(seconds + rec.duration);

                        me.timeline.setWindow(dtStart, dtEnd);

                        me.getTagStore().load({
                            params: {
                                file_id: rec.id
                            }
                        })
                    }
                }
            })
        }
        return me.westPanel;
    },

    getCenterPanel: function () {
        var me = this;
        if (!me.centerPanel) {
            me.centerPanel = Ext.create('Ext.panel.Panel', {
                title: 'Editor',
                region: 'center',
                layout: 'border',
                items: [
                    {
                        region: 'center',
                        border: true,
                        id: 'video-container',
                        html: '<video controls width="600" id="my-video" class="video-js" data-setup=\'{}\' style="margin: 0 auto; width: 600px;">' +
                                '<source src="/data/dir2/DJI_0010.MOV" type="video/mp4">' +
                            '</video>'
                    },
                    {
                        region: 'south',
                        height: 300,
                        id: 'timeline-container',
                        listeners: {
                            render: function () {
                                var container = document.getElementById('timeline-container-innerCt');

                                // Create a DataSet (allows two way data-binding)
                                var items = new vis.DataSet([
                                    {id: 1, content: 'Пловдив', start: '2001-01-01 00:00:03'},
                                    {id: 2, content: 'река', start: '2001-01-01 00:00:15'},
                                    {id: 3, content: 'Марица', start: '2001-01-01 00:00:10'},
                                    {id: 4, content: 'залез', start: '2001-01-01 00:00:05', end: '2001-01-01 00:00:22'},
                                ]);

                                // Configuration for the Timeline
                                var options = {
                                    showCurrentTime: true,
                                    showMajorLabels: false,
                                    start: '2001-01-01 00:00:00',
                                    end: '2001-01-01 00:00:55'
                                };

                                // Create a Timeline
                                me.timeline = new vis.Timeline(container, items, options);
                                me.timeline.addCustomTime('2001-01-01 00:00:00', 't1')

                                var player = videojs('my-video')
                                player.on('timeupdate', function (e) {
                                    var pos = player.currentTime();
                                    if (pos == 0) return;
                                    var dt = new Date('2001-01-01 00:00:00');
                                    var seconds = dt.getSeconds();
                                    dt.setSeconds(seconds + pos);
                                    me.timeline.setCustomTime(dt, 't1');
                                })

                                me.timeline.on('timechanged', function (e) {
                                    if (e.id == 't1') {
                                        var dt = new Date(e.time)
                                        var dtStart = new Date('2001-01-01 00:00:00');
                                        var seconds = (dt.getTime() - dtStart.getTime()) / 1000;
                                        player.currentTime(seconds);
                                    }
                                })
                            }
                        }
                    }
                ]
            })
        }
        return me.centerPanel;
    },

    getEastPanel: function () {
        var me = this;
        if (!me.eastPanel) {
            me.eastPanel = Ext.create('Ext.grid.Panel', {
                title: 'Taglist',
                xtype: 'grid',
                region: 'east',
                border: true,
                split: true,
                width: 300,
                store: me.getTagStore(),
                columns: [
                    {text: 'Tag', dataIndex: 'tag_name', flex: 1},
                    {text: 'Start', dataIndex: 'start_time_is', flex: 1},
                    {text: 'Duration', dataIndex: 'duration_is', flex: 1}
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

    getAddDirectoryWindow: function () {
        Ext.Msg.prompt('Add directory', 'Enter path relative to public/data', function (btn, val) {
            if (btn == 'ok') {
                Ext.Ajax.request({
                    url: '/directory',
                    method: 'POST',
                    jsonData: {
                        directory: {
                            path: val
                        }
                    },
                    success: function (response) {
                        response = Ext.decode(response.responseText);
                        if (response.success) {
                            console.log("yay!")
                        } else {
                            Ext.Msg.alert('Error', response.error);
                        }
                    }
                })
            }
        })
    }
})