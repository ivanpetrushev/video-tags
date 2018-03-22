Ext.define('App.main', {
    extend: 'Ext.window.Window',

    closable: false,
    width: '90%',
    height: '90%',
    header: false,
    layout: 'border',

    items: [
        {
            title: 'Directories',
            xtype: 'treepanel',
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
                    iconCls: 'x-fa fa-plus color-green'
                }, {
                    xtype: 'button',
                    iconCls: 'x-fa fa-times color-red'
                }
            ]
        }, {
            title: 'Editor',
            region: 'center',
            layout: 'border',
            items: [
                {
                    region: 'center',
                    border: true,
                    id: 'video-container',
                    html: '<video controls width="600" id="my-video" class="video-js" data-setup=\'{}\' style="margin: 0 auto; width: 600px;">' +
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
                                {id: 1, content: 'Пловдив', start: '2001-01-01 00:00:03'},
                                {id: 2, content: 'река', start: '2001-01-01 00:00:15'},
                                {id: 3, content: 'Марица', start: '2001-01-01 00:00:10'},
                                {id: 4, content: 'залез', start: '2001-01-01 00:00:05', end: '2001-01-01 00:00:22'},
                            ]);

                            // Configuration for the Timeline
                            var options = {
                                showCurrentTime: true,
                                start: '2001-01-01 00:00:00',
                                end: '2001-01-01 00:00:55:'
                            };

                            // Create a Timeline
                            var timeline = new vis.Timeline(container, items, options);
                            timeline.addCustomTime('2001-01-01 00:00:00', 't1')

                            var player = videojs('my-video')
                            player.on('timeupdate', function(e){
                                var pos = player.currentTime();
                                if (pos == 0) return;
                                var dt = new Date('2001-01-01 00:00:00');
                                var seconds = dt.getSeconds();
                                dt.setSeconds(seconds + pos);
                                timeline.setCustomTime(dt, 't1');
                            })

                            timeline.on('timechanged', function(e) {
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
        }, {
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
        }
    ]
})