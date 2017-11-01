$(document).ready(function(){
    var mymap = L.map('mapid').setView([42.48112, 25.48645], 13);
    L.tileLayer('http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery © <a href="http://cloudmade.com">CloudMade</a>',
        maxZoom: 18
    }).addTo(mymap);

    L.uGeoJSONLayer({
        endpoint : '/geojson',
        onEachFeature: function(feature, layer){
            if (feature.properties && feature.properties.title) {
                layer.bindPopup(feature.properties.title);
            }
        }
    }).addTo(mymap);
})
