var map, overlays, cunli, currentOverlayIndex = 0, markers = [], infowindow = new google.maps.InfoWindow();
$.ajaxSetup({async: false});

$.getJSON('overlays.json', function (data) {
    overlays = data
});
function initialize() {
    /*map setting*/
    $('#map-canvas').height(window.outerHeight / 2.2);

    map = new google.maps.Map(document.getElementById('map-canvas'), {
        zoom: 11,
        center: {lat: 23.00, lng: 120.30}
    });

    $.getJSON('../cunliTN.json', function (data) {
        cunli = map.data.addGeoJson(data);
    });

    map.data.addListener('mouseover', function (event) {
        var Cunli = event.feature.getProperty('T_Name') + event.feature.getProperty('V_Name');
        map.data.revertStyle();
        map.data.overrideStyle(event.feature, {fillColor: 'white'});
        $('#content').html('<div>' + Cunli + '</div>').removeClass('text-muted');
    });

    map.data.addListener('mouseout', function (event) {
        map.data.revertStyle();
        $('#content').html('在地圖上滑動可以顯示村里資訊').addClass('text-muted');
    });

    showOverlays();

    $('a#btnPrevious').click(function () {
        currentOverlayIndex += 1;
        if (currentOverlayIndex > overlays.length - 1) {
            currentOverlayIndex = overlays.length - 1;
        } else {
            showOverlays();
        }
    });
    
    $('a#btnNext').click(function () {
        currentOverlayIndex -= 1;
        if (currentOverlayIndex < 0) {
            currentOverlayIndex = 0;
        } else {
            showOverlays();
        }
    });
}

function showOverlays() {
    var isTitleSet = false, bounds = new google.maps.LatLngBounds;
    cunli.forEach(function (value) {
        var key = value.getProperty('VILLAGE_ID');
        if (overlays[currentOverlayIndex]['cunlis'].indexOf(key) !== -1) {
            value.setProperty('color', 'blue');
        } else {
            value.setProperty('color', 'transparent');
        }
    });
    map.data.setStyle(function (feature) {
        return {
            fillColor: feature.getProperty('color'),
            fillOpacity: 0.6,
            strokeColor: feature.getProperty('color'),
            strokeWeight: 1
        }
    });
    
    for(i in markers) {
        markers[i].setMap(null);
    }

    for (i in overlays[currentOverlayIndex]['points']) {
        if (false === isTitleSet) {
            $('#title').html(overlays[currentOverlayIndex]['points'][i][4]);
            isTitleSet = true;
        }
        var latLng = new google.maps.LatLng(overlays[currentOverlayIndex]['points'][i][14], overlays[currentOverlayIndex]['points'][i][13]);
        var marker = new google.maps.Marker({
            position: latLng,
            clickable: true,
            map: map
        });
        bounds.extend(latLng);
        marker.info = overlays[currentOverlayIndex]['points'][i];
        google.maps.event.addListener(marker, 'click', (function (marker, i) {
            return function () {
                var info = '<b>時間：</b>' + marker.info[6];
                info += '<br /><b>地點：</b>' + marker.info[7];
                infowindow.setContent(info);
                infowindow.open(map, marker);
            }
        })(marker, i));
        markers.push(marker);
    }
    map.fitBounds(bounds);
}

google.maps.event.addDomListener(window, 'load', initialize);