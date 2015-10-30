var map, overlays, roads = {}, currentOverlayIndex = 0, markers = [], infowindow = new google.maps.InfoWindow();
$.ajaxSetup({async: false});

function initialize() {
    /*map setting*/
    $('#map-canvas').height(window.outerHeight / 2.2);

    map = new google.maps.Map(document.getElementById('map-canvas'), {
        zoom: 12,
        center: {lat: 23.00, lng: 120.30}
    });

    $.getJSON('json/2015/201542.json', function (data) {
        roads.w42 = data;
        map.data.addGeoJson(roads.w42);
    });
    $.getJSON('json/2015/201543.json', function (data) {
        roads.w43 = data;
        map.data.addGeoJson(roads.w43);
    });
    $.getJSON('json/2015/201544.json', function (data) {
        roads.w44 = data;
        map.data.addGeoJson(roads.w44);
    });

    map.data.setStyle(function (feature) {
        var color = 'gray';
        return /** @type {google.maps.Data.StyleOptions} */({
            fillColor: color,
            strokeColor: color,
            strokeWeight: 2
        });
    });


    map.data.addListener('mouseover', function (event) {
        var title = event.feature.getProperty('timeBegin') + ' ~ ' + event.feature.getProperty('timeEnd');
        $('#content').html('<div>' + title + '</div>').removeClass('text-muted');
    });

    map.data.addListener('click', function (event) {
        map.data.revertStyle();
        map.data.overrideStyle(event.feature, {fillColor: 'red', strokeColor: 'red'});
        var body = '<div>設備代號： ' + event.feature.getProperty('device') + '</div>';
        body += '<div>開始時間： ' + event.feature.getProperty('timeBegin') + '</div>';
        body += '<div>結束時間： ' + event.feature.getProperty('timeEnd') + '</div>';
        $('#dangerBody').html(body);
    });

    map.data.addListener('mouseout', function (event) {
        $('#content').html('在地圖上滑動可以顯示資訊').addClass('text-muted');
    });

}
google.maps.event.addDomListener(window, 'load', initialize);