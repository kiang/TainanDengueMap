$.ajaxSetup({async: false});

var map;
$.getJSON('DengueTN.json', function (data) {
    DengueTN = data
});
var currentPlayIndex = false;

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
        if (DengueTN[key]) {
            DengueTN[key].forEach(function (val) {
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
        $('#detail > #content').empty();
        $('#detail > #content').append('<div>' + Cunli + ' ：' + event.feature.getProperty('num') + ' 例</div>');
    });

    map.data.addListener('mouseout', function (event) {
        map.data.revertStyle();
        $('#detail > #content').empty();
        $('#detail > #content').append('&nbsp;');
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

    $('#playButton1').click(function () {
        var maxIndex = DengueTN['total'].length;
        if (false === currentPlayIndex) {
            currentPlayIndex = 0;
        } else {
            currentPlayIndex += 1;
        }

        if (currentPlayIndex < maxIndex) {
            showDateMap(new Date(DengueTN['total'][currentPlayIndex][0]));
            setTimeout(function () {
                $('#playButton1').trigger('click');
            }, 300);
        } else {
            currentPlayIndex = false;
        }
        return false;
    });
    
    $('#playButton2').click(function () {
        var maxIndex = DengueTN['total'].length;
        if (false === currentPlayIndex) {
            currentPlayIndex = 0;
        } else {
            currentPlayIndex += 1;
        }

        if (currentPlayIndex < maxIndex) {
            showDayMap(new Date(DengueTN['total'][currentPlayIndex][0]));
            setTimeout(function () {
                $('#playButton2').trigger('click');
            }, 300);
        } else {
            currentPlayIndex = false;
        }
        return false;
    });
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
                            showDayMap(new Date(this.x));
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

function showDateMap(clickedDate) {
    var yyyy = clickedDate.getFullYear().toString();
    var mm = (clickedDate.getMonth() + 1).toString();
    var dd = clickedDate.getDate().toString();
    var clickedDateKey = yyyy + '-' + (mm[1] ? mm : "0" + mm[0]) + '-' + (dd[1] ? dd : "0" + dd[0]);
    $('#detail > #title').text(clickedDateKey + ' 累積病例');
    cunli.forEach(function (value) {
        var key = value.getProperty('T_Name') + value.getProperty('V_Name');
        var count = 0;
        if (DengueTN[key]) {
            DengueTN[key].forEach(function (val) {
                var recordDate = new Date(val[0]);
                if (recordDate <= clickedDate) {
                    count += val[1];
                }
            });
        }
        value.setProperty('num', count);
    });
}

function showDayMap(clickedDate) {
    var yyyy = clickedDate.getFullYear().toString();
    var mm = (clickedDate.getMonth() + 1).toString();
    var dd = clickedDate.getDate().toString();
    var clickedDateKey = yyyy + '-' + (mm[1] ? mm : "0" + mm[0]) + '-' + (dd[1] ? dd : "0" + dd[0]);
    $('#detail > #title').text(clickedDateKey + ' 當日病例');
    cunli.forEach(function (value) {
        var key = value.getProperty('T_Name') + value.getProperty('V_Name');
        var count = 0;
        if (DengueTN[key]) {
            DengueTN[key].forEach(function (val) {
                if (clickedDateKey == val[0]) {
                    count += val[1];
                }
            });
        }
        value.setProperty('num', count);
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