$.ajaxSetup({async: false});

var map, infowindow = new google.maps.InfoWindow();

$.getJSON('../cdc/points.min.json', function (data) {
    points = data;
});

function initialize() {

    /*map setting*/
    $('#map-canvas').height(window.outerHeight / 2.2);

    map = new google.maps.Map(document.getElementById('map-canvas'), {
        zoom: 12,
        center: {lat: 23.00, lng: 120.30}
    });

    var markers = [];
    for (city in points) {
        for (i in points[city]['records']) {
            if (points[city]['records'][i]['sickdate'].substring(0, 4) === '2015') {
                var latLng = new google.maps.LatLng(points[city]['records'][i]['lat'], points[city]['records'][i]['lng']);
                var marker = new google.maps.Marker({
                    position: latLng, clickable: true
                });
                marker.info = points[city]['records'][i];
                google.maps.event.addListener(marker, 'click', (function (marker, i) {
                    return function () {
                        var info = '<b>日期：</b>' + marker.info.sickdate;
                        info += '<br /><b>數量：</b>' + marker.info.count;
                        info += '<br /><b>類型：</b>';
                        if(marker.info.immigration === 0) {
                            info += '本土';
                        } else {
                            info += '境外';
                        }
                        infowindow.setContent(info);
                        infowindow.open(map, marker);
                    }
                })(marker, i));
                markers.push(marker);
            }
        }
    }
    var markerCluster = new MarkerClusterer(map, markers, {maxZoom: 16});
}

google.maps.event.addDomListener(window, 'load', initialize);