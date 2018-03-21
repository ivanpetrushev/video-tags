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
                    html: 'center'
                }, {
                    region: 'south',
                    height: 300,
                    html: 'south'
                }
            ]
        }, {
            title: 'Taglist',
            xtype: 'grid',
            region: 'east',
            border: true,
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