$.ajaxSetup({async: false});

var map;
$.getJSON('DengueTN.json', function (data) {
    DengueTN = data
});

function initialize() {
    /*map setting*/
    map = new google.maps.Map(document.getElementById('map-canvas'), {
        zoom: 12,
        center: {lat: 23.00, lng: 120.30}
    });

    $.getJSON('cunliTN.json', function (data) {
        cunli = map.data.addGeoJson(data);
    });

    cunli.forEach(function (value) {
        var key = value.getProperty('T_Name') + value.getProperty('V_Name');
        var count = 0;
        if(DengueTN[key]) {
            DengueTN[key].forEach(function(val) {
                count += val[1];
            });
        }
        value.setProperty('num', count);
    });

    map.data.setStyle(function (feature) {
        var num = feature.getProperty('num');
        color = ColorBar(num);
        return {
            fillColor: color,
            fillOpacity: 0.6,
            strokeColor: 'gray',
            strokeWeight: 1
        }
    });

    map.data.addListener('mouseover', function (event) {
        var Cunli = event.feature.getProperty('T_Name') + event.feature.getProperty('V_Name');
        map.data.revertStyle();
        map.data.overrideStyle(event.feature, {fillColor: 'white'});
        $('#detial > #content').empty();
        $('#detial > #content').append('<div>' + Cunli + ':' + event.feature.getProperty('num') + '</div>');
    });

    map.data.addListener('mouseout', function (event) {
        map.data.revertStyle();
        $('#detial > #content').empty();
    });

    map.data.addListener('click', function (event) {
        var Cunli = event.feature.getProperty('T_Name') + event.feature.getProperty('V_Name');
        if ($('#myTab a[name|="' + Cunli + '"]').tab('show').length == 0) {
            $('#myTab').append('<li><a name="' + Cunli + '" href="#' + Cunli + '" data-toggle="tab">' + Cunli +
                    '<button class="close" onclick="closeTab(this.parentNode)">×</button></a></li>');
            $('#myTabContent').append('<div class="tab-pane fade" id="' + Cunli + '"><div></div></div>');
            $('#myTab a:last').tab('show');
            createStockChart(Cunli);
            $('#myTab li a:last').click(function (e) {
                $(window).trigger('resize');
            });
        }
    });
    createStockChart('total');
}

function createStockChart(Cunli) {
    var series = []
    for (var i = 0; i < DengueTN[Cunli].length; i = i + 1) {
        series.push([new Date(DengueTN[Cunli][i][0]).getTime(), DengueTN[Cunli][i][1]]);
    }

    $('#' + Cunli).highcharts('StockChart', {
        chart: {
            alignTicks: false,
            width: $('#myTabContent').width(),
            height: $('#myTabContent').height()
        },
        rangeSelector: {
            enabled: false
        },
        tooltip: {
            enabled: true,
            positioner: function () {
                return {x: 10, y: 30}
            }
        },
        plotOptions: {
            series: {
                cursor: 'pointer',
                point: {
                    events: {
                        click: function () {
                            alert('hi');
                        }
                    }
                },
            }
        },
        series: [{
                type: 'column',
                name: Cunli,
                data: series,
            }]
    });
}

$(window).resize(function () {
    var len = $('#myTabContent > div').length;
    for (var i = 1; i <= len; i = i + 1) {
        $('#myTabContent > div:nth-child(' + i + ')').highcharts().setSize($('#myTabContent').width(), $('#myTabContent').height());
    }
});

function closeTab(node) {
    var nodename = node.name;
    node.parentNode.remove();
    $('#' + nodename).remove();
    $('#myTab a:first').tab('show');
}

google.maps.event.addDomListener(window, 'load', initialize);