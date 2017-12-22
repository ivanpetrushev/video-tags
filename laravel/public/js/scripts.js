var openPopupId = null;
var filterCategories = {};
var xhrGeojson = null;

$(document).ready(function () {

    var map = L.map('mapid').setView([42.48112, 25.48645], 10);
    L.tileLayer('http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery © <a href="http://cloudmade.com">CloudMade</a>',
        maxZoom: 18
    }).addTo(map);

    L.control.scale().addTo(map);

    var hash = new L.Hash(map);

    var greenIcon = new L.Icon({
        iconUrl: 'https://cdn.rawgit.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png',
        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34],
        shadowSize: [41, 41]
    });

    var blueIcon = new L.Icon({
        iconUrl: 'https://cdn.rawgit.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-blue.png',
        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34],
        shadowSize: [41, 41]
    });

    var fnGeneratePopup = function (layer) {
        var props = layer.feature.properties;
        var popup = $('<div>', {class: 'popup'});
        popup.append($('<div>', {class: 'title'}).html(props.title));

        var images = $('<div>', {class: 'images'});
        for (var i in props.images) {
            images.append($('<img>', {src: props.images[i].url, class: 'img-thumbnail'}));
        }
        popup.append(images);

        popup.append($('<div>', {class: 'description'}).html(props.description));

        var blogs = $('<div>', {class: 'list-group blogs'});
        for (var i in props.blogs) {
            blogs.append($('<a>', {
                href: props.blogs[i].url,
                class: 'list-group-item list-group-item-action',
                text: props.blogs[i].title,
                target: '_blank'
            }));
        }
        popup.append(blogs);

        return popup.html();
    }

    L.uGeoJSONLayer({
        endpoint: '/geojson',
        parameters: {
            filterCategories: function () {
                return JSON.stringify(window.filterCategories)
            }
        },
        onEachFeature: function (feature, layer) {
            if (feature.properties) {
                layer.bindPopup(fnGeneratePopup).openPopup();
            }
        },
        after: function (data) {
            map.eachLayer(function (layer) {
                if (layer.feature && openPopupId && layer.feature.properties.id == openPopupId) {
                    layer.openPopup();
                    openPopupId = null;
                }
            })
        },
        pointToLayer: function (feature, latlng) {
            if (feature.properties.is_visited) {
                return L.marker(latlng, {icon: greenIcon})
            } else {
                return L.marker(latlng, {icon: blueIcon})
            }
        }
    }).addTo(map);

    // load sidebar
    fnLoadSidebar();

    $('body').on('click', '.list-group-item.place', function (e) {
        e.preventDefault();
        e.stopPropagation();

        var lat = $(this).data('lat');
        var lon = $(this).data('lon');
        openPopupId = $(this).data('id');

        map.panTo([lat, lon]);
        setTimeout(function () {
            map.fireEvent('dragend');
        }, 500);

    })

    $('.search input').on('keyup', function (e) {
        var val = $(this).val();
        $('.place').each(function () {
            $(this).removeClass('hidden');

            if (!$(this).html().toLowerCase().match(val.toLowerCase())) {
                $(this).addClass('hidden');
            }
        })
    })

    // load categories
    $.ajax({
        url: '/categories',
        method: 'get',
        dataType: 'json',
        success: function (response) {
            if (response.success) {
                for (var i in response.data) {
                    var col = $('<label>', {
                        html: response.data[i].title + ' (' + response.data[i].num_occurencies + ')',
                        class: 'col-md-3'
                    });
                    var input = $('<input>', {
                        type: 'checkbox',
                        class: 'tristate',
                        name: 'category-' + response.data[i].id
                    });
                    var img = $('<img>', {src: '/images/chk0.gif'});
                    col.prepend(input);
                    col.prepend(img);
                    $('.category-list').append(col);
                }

                $('.tristate').tristate({
                    checked: '1',
                    unchecked: '0',
                    indeterminate: '2',
                    change: function (state, value) {
                        $(this).siblings('img').attr('src', '/images/chk' + value + '.gif');
                    }
                });
            }
        }
    })

    $('.do-filter').click(function () {
        window.filterCategories = {};
        var cnt = 0;

        $('#category-form input').each(function (x, el) {
            var name = $(el).attr('name');
            var val = $(el).val();
            window.filterCategories[name] = val;
            if (val != 0) {
                cnt++;
            }
        })

        fnLoadSidebar();

        if (cnt == 0) {
            $('#category-counter').html('');
        } else {
            $('#category-counter').html(cnt);
        }

        setTimeout(function () {
            map.fireEvent('dragend');
        }, 500);
    })
})

function fnLoadSidebar() {
    if (xhrGeojson) {
        xhrGeojson.abort();
    }

    xhrGeojson = $.ajax({
        url: '/geojson',
        method: 'post',
        data: {
            filterCategories: JSON.stringify(filterCategories),
            north: 90,
            south: -90,
            east: 180,
            west: -180,
        },
        dataType: 'json',
        success: function (data) {
            xhrGeojson = null;
            $('.search-items').empty();

            for (var i in data.features) {
                var feature = data.features[i];
                var tag = $('<a href="#" class="list-group-item small place">' + feature.properties.title + '</a>');
                tag.attr('data-id', feature.properties.id);
                tag.attr('data-lat', feature.geometry.coordinates[1]);
                tag.attr('data-lon', feature.geometry.coordinates[0]);
                if (feature.properties.is_visited) {
                    tag.addClass('visited');
                } else {
                    tag.addClass('not-visited');
                }

                if (feature.properties.description != '') {
                    tag.append('<i class="fa fa-fw fa-comment"></i>');
                }
                $('.search-items').append(tag)
            }
        },
        error: function() {
            xhrGeojson = null;
        }
    })
}