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
                    { name: 'duration', type: 'int'},
                    { name: 'duration_hi', type: 'string'},
                    { name: 'filesize', type: 'int'}
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
                fields: ['id', 'name'],
                proxy: {
                    type: 'ajax',
                    url: '/tag',
                    reader: {
                        type: 'json',
                        rootProperty: 'data'
                    }
                }
            })
        }
        return me.tagStore;
    },

    getFileTagStore: function() {
        var me = this;
        if (! me.fileTagStore) {
            me.fileTagStore = Ext.create('Ext.data.Store', {
                fields: ['tag_name', 'start_time', 'duration', 'start_time_is', 'duration_is'],
                proxy: {
                    type: 'ajax',
                    url: '/file/tags',
                    reader: {
                        type: 'json',
                        rootProperty: 'data'
                    }
                },
                listeners: {
                    load: function(store, items) {
                        var dataset = [];
                        for (var i in items) {
                            var rec = items[i].data;

                            var dt = new Date('2001-01-01 00:00:00');
                            var dtStart = new Date('2001-01-01 00:00:00');
                            var dtEnd = new Date('2001-01-01 00:00:00');

                            dtStart.setSeconds(dt.getSeconds() + rec.start_time)
                            dtEnd.setSeconds(dt.getSeconds() + rec.start_time + rec.duration)

                            dataset.push({
                                id: rec['id'],
                                content: rec['tag_name'],
                                start: dtStart,
                                end: dtEnd
                            })
                        }

                        // Create a DataSet (allows two way data-binding)
                        var items = new vis.DataSet(dataset);
                        me.timeline.setItems(items);

                        items.on('remove', function (event, properties) {
                            Ext.Ajax.request({
                                url: '/file/remove_tag',
                                method: 'DELETE',
                                params: {
                                    tag_id: properties.items[0]
                                },
                                success: function() {
                                    me.getFileTagStore().reload();
                                }
                            })
                        });
                        items.on('update', function(event, properties) {
                            var dt = new Date('2001-01-01 00:00:00');
                            var dtStart = properties.data[0].start;
                            var dtEnd = properties.data[0].end;
                            var duration = (dtEnd.getTime() - dtStart.getTime()) / 1000;

                            Ext.Ajax.request({
                                url: '/file/save_tag',
                                method: 'PUT',
                                jsonData: {
                                    tag: {
                                        id: properties.items[0],
                                        start_time: (dtStart.getTime() - dt.getTime()) / 1000,
                                        duration: duration
                                    }
                                },
                                success: function() {
                                    me.getFileTagStore().reload();
                                }
                            })
                        })
                    }
                }
            })
        }
        return me.fileTagStore;
    },

    getWestPanel: function () {
        var me = this;
        if (!me.westPanel) {
            me.westPanel = Ext.create('Ext.tree.Panel', {
                title: 'Directories',
                region: 'west',
                border: true,
                width: 400,
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
                        dataIndex: 'duration_hi',
                        width: 80
                    }, {
                        text: 'Size',
                        dataIndex: 'filesize',
                        width: 80,
                        renderer: function(val, meta, rec) {
                            var sReturn = '';

                            if (rec.data.leaf) {
                                sReturn = val;
                                var i = -1;
                                var byteUnits = [' kB', ' MB', ' GB', ' TB', 'PB', 'EB', 'ZB', 'YB'];
                                do {
                                    sReturn = sReturn / 1024;
                                    i++;
                                } while (sReturn > 1024);
                                console.log('val', sReturn)

                                sReturn = Math.max(sReturn, 0.1).toFixed(1) + byteUnits[i];
                            }
                            return sReturn;
                        }
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
                    },'-', {
                        xtype: 'button',
                        iconCls: 'x-fa fa-arrow-circle-up color-blue',
                        tooltip: 'Export video tags'
                    }, {
                        xtype: 'button',
                        iconCls: 'x-fa fa-arrow-circle-down color-blue',
                        tooltip: 'Import video tags'
                    }
                ],
                listeners: {
                    selectionchange: function(cmp, selected) {
                        if (Ext.isEmpty(selected)) {
                            return;
                        }

                        me.getCenterPanel().enable();
                        me.getEastPanel().enable();

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

                        me.getFileTagStore().load({
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
                disabled: true,
                items: [
                    {
                        region: 'center',
                        border: true,
                        id: 'video-container',
                        html: '<video controls width="600" id="my-video" class="video-js" data-setup=\'{}\' style="margin: 0 auto; width: 600px;">' +
                            '</video>'
                    },
                    {
                        region: 'south',
                        height: 200,
                        id: 'timeline-container',
                        listeners: {
                            render: function () {
                                var container = document.getElementById('timeline-container-innerCt');

                                // Create a DataSet (allows two way data-binding)
                                var items = new vis.DataSet([]);

                                // Configuration for the Timeline
                                var options = {
                                    showCurrentTime: true,
                                    showMajorLabels: false,
                                    start: '2001-01-01 00:00:00',
                                    end: '2001-01-01 00:00:55',
                                    editable: true
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

                                me.timeline.on('timechange', function (e) {
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
                disabled: true,
                width: 350,
                store: me.getFileTagStore(),
                columns: [
                    {text: 'Tag', dataIndex: 'tag_name', flex: 1},
                    {text: 'Start', dataIndex: 'start_time_is', flex: 1},
                    {text: 'Duration', dataIndex: 'duration_is', flex: 1},
                    {
                        xtype: 'actioncolumn',
                        width: 50,
                        items: [
                            {
                                iconCls: 'x-fa fa-clock-o color-blue',
                                tooltip: 'Stop tag that is still counting',
                                getClass: function(v, meta, rec) {
                                    if (rec.data.duration == 0) {
                                        return 'x-fa fa-clock-o color-blue';
                                    }
                                },
                                handler: function(grid, rowIndex) {
                                    var rec = grid.getStore().getAt(rowIndex);

                                    var player = videojs('my-video')
                                    var pos = player.currentTime();
                                    var duration = parseInt(pos - rec.data.start_time);

                                    Ext.Ajax.request({
                                        url: '/file/stop_tag',
                                        method: 'PUT',
                                        params: {
                                            tag_id: rec.data.id,
                                            duration: duration
                                        },
                                        success: function() {
                                            me.getFileTagStore().reload();
                                        }
                                    })
                                }
                            }, {
                                iconCls: 'x-fa fa-times color-red',
                                tooltip: 'Remove tag',
                                handler: function(grid, rowIndex) {
                                    var rec = grid.getStore().getAt(rowIndex);
                                    Ext.Ajax.request({
                                        url: '/file/remove_tag',
                                        method: 'DELETE',
                                        params: {
                                            tag_id: rec.data.id
                                        },
                                        success: function() {
                                            me.getFileTagStore().reload();
                                        }
                                    })
                                }
                            }
                        ]
                    }
                ],
                tbar: [
                    {
                        xtype: 'button',
                        iconCls: 'x-fa fa-plus color-green',
                        tooltip: 'Add new tag',
                        handler: function() {
                            me.getAddTagWindow();
                        }
                    }, {
                        xtype: 'button',
                        iconCls: 'x-fa fa-copy color-blue',
                        tooltip: 'Duplicated selected tag',
                        handler: function() {
                            var selection = me.getEastPanel().getSelection();
                            if (Ext.isEmpty(selection)) {
                                return;
                            }

                            var player = videojs('my-video')
                            var pos = parseInt(player.currentTime());

                            Ext.Ajax.request({
                                url: '/file/copy_tag',
                                method: 'POST',
                                params: {
                                    tag_id: selection[0].data.id,
                                    start_time: pos
                                },
                                success: function() {
                                    me.getFileTagStore().reload();
                                }
                            })
                        }
                    }, {
                        xtype: 'button',
                        iconCls: 'x-fa fa-times color-red',
                        tooltip: 'Remove selected tag',
                        handler: function() {
                            var selection = me.getEastPanel().getSelection();
                            if (Ext.isEmpty(selection)) {
                                return;
                            }
                            Ext.Ajax.request({
                                url: '/file/remove_tag',
                                method: 'DELETE',
                                params: {
                                    tag_id: selection[0].data.id
                                },
                                success: function() {
                                    me.getFileTagStore().reload();
                                }
                            })
                        }
                    }
                ],
                listeners: {
                    itemdblclick: function(cmp, rec) {
                        var id = rec.data.id;
                        var start_time = rec.data.start_time;
                        var dtStart = new Date('2001-01-01 00:00:00');
                        dtStart.setSeconds(dtStart.getSeconds() + start_time)

                        me.timeline.setSelection(id);

                        var player = videojs('my-video')
                        var pos = player.currentTime(start_time);
                    }
                }
            })
        }
        return me.eastPanel;
    },

    getAddDirectoryWindow: function () {
        Ext.Msg.prompt('Add directory', 'Enter path relative to public/data', function (btn, val) {
            if (btn != 'ok') {
                return;
            }
            
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
        })
    },

    getAddTagWindow: function() {
        var me = this;

        var form = Ext.create('Ext.form.Panel', {
            bodyPadding: 10,
            url: '/file/new_tag',
            items: [
                {
                    xtype: 'combo',
                    store: me.getTagStore(),
                    name: 'tag_id',
                    fieldLabel: 'Tag',
                    displayField: 'name',
                    valueField: 'id',
                    minChars: 1,
                    listeners: {
                        render: function(cmp) {
                            cmp.focus();
                        }
                    }
                }
            ],
            buttons: [
                {
                    iconCls: 'x-fa fa-check color-green',
                    text: 'Save',
                    handler: function() {
                        var selection = me.getWestPanel().getSelection();

                        var player = videojs('my-video')
                        var pos = player.currentTime();

                        this.up('form').submit({
                            params: {
                                file_id: selection[0].data.id,
                                start_time: pos
                            },
                            success: function() {
                                me.getFileTagStore().reload();
                                win.close();
                            }
                        })
                    }
                }, {
                    iconCls: 'x-fa fa-ban color-red',
                    text: 'Cancel',
                    handler: function() {
                        win.close();
                    }
                }
            ]
        })

        var win = Ext.create('Ext.window.Window', {
            title: 'Add tag',
            width: 300,
            height: 150,
            layout: 'fit',
            modal: true,
            items: [form]
        })
        win.show();
    }
})