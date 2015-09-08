$.ajaxSetup({async: false});

var map,
        currentPlayIndex = false,
        cunli;

$.getJSON('Dengue.json', function (data) {
    DengueTW = data;
});
function initialize() {

    /*map setting*/
    $('#map-canvas').height(window.outerHeight / 2.2);

    map = new google.maps.Map(document.getElementById('map-canvas'), {
        zoom: 12,
        center: {lat: 23.00, lng: 120.30}
    });

    $.getJSON('cunli.json', function (data) {
        cunli = map.data.addGeoJson(topojson.feature(data, data.objects.cunli));
    });

    var areas = [];
    cunli.forEach(function (value) {
        var key = value.getProperty('VILLAGE_ID'),
                countyId = value.getProperty('COUNTY_ID'),
                townId = value.getProperty('TOWN_ID'),
                count = 0;
        if (DengueTW[key]) {
            DengueTW[key].forEach(function (val) {
                count += val[1];
            });
        }
        value.setProperty('num', count);
        
        if(countyId.length === 2) {
            countyId += '000';
        }
        if(!areas[countyId]) {
            areas[countyId] = value.getProperty('C_Name');
        }
        if(!areas[townId]) {
            areas[townId] = value.getProperty('C_Name') + value.getProperty('T_Name');
        }
    });

    var totalNum = 0, ignoreNum = 0;
    var block = '下面病例數字未包含村里資訊，因此無法在地圖中顯示：<div class="clearfix"><br /></div>';
    $.each(DengueTW, function (k, v) {
        if (k.length !== 11) {
            var num = 0;
            for (i in v) {
                num += v[i][1];
            }
            if (k !== 'total') {
                ignoreNum += num;
                block += '<div class="col-md-2">' + areas[k] + ': ' + num + '</div>';
            } else {
                totalNum = num;
            }
        }
    })
    block += '<div class="clearfix"><br /></div>';
    block += '目前共有病例 ' + totalNum + ' ，無法顯示的數量為 ' + ignoreNum;
    $('div#listNoneCunli').html(block);

    map.data.setStyle(function (feature) {
        color = ColorBar(feature.getProperty('num'));
        return {
            fillColor: color,
            fillOpacity: 0.6,
            strokeColor: 'gray',
            strokeWeight: 1
        }
    });

    map.data.addListener('mouseover', function (event) {
        var Cunli = event.feature.getProperty('C_Name') + event.feature.getProperty('T_Name') + event.feature.getProperty('V_Name');
        map.data.revertStyle();
        map.data.overrideStyle(event.feature, {fillColor: 'white'});
        $('#content').html('<div>' + Cunli + ' ：' + event.feature.getProperty('num') + ' 例</div>').removeClass('text-muted');
    });

    map.data.addListener('mouseout', function (event) {
        map.data.revertStyle();
        $('#content').html('在地圖上滑動或點選以顯示數據').addClass('text-muted');
    });

    map.data.addListener('click', function (event) {
        var Cunli = event.feature.getProperty('VILLAGE_ID');
        var CunliTitle = event.feature.getProperty('C_Name') + event.feature.getProperty('T_Name') + event.feature.getProperty('V_Name');
        if ($('#myTab a[name|="' + Cunli + '"]').tab('show').length === 0) {
            $('#myTab').append('<li><a name="' + Cunli + '" href="#' + Cunli + '" data-toggle="tab">' + CunliTitle +
                    '<button class="close" onclick="closeTab(this.parentNode)">×</button></a></li>');
            $('#myTabContent').append('<div class="tab-pane fade" id="' + Cunli + '"><div></div></div>');
            $('#myTab a:last').tab('show');
            createStockChart(Cunli, cunli);
            $('#myTab li a:last').click(function (e) {
                $(window).trigger('resize');
            });
        }
    });
    createStockChart('total', cunli);

    $('#playButton1').on('click', function () {
        var maxIndex = DengueTW['total'].length;
        if (false === currentPlayIndex) {
            currentPlayIndex = 0;
        } else {
            currentPlayIndex += 1;
            $(this).addClass('active disabled').find('.glyphicon').show();
        }

        if (currentPlayIndex < maxIndex) {
            showDateMap(new Date(DengueTW['total'][currentPlayIndex][0]), cunli);
            setTimeout(function () {
                $('#playButton1').trigger('click');
            }, 300);
        } else {
            $(this).removeClass('active disabled').find('.glyphicon').hide();
            currentPlayIndex = false;
            $('#title').html('');
        }
        return false;
    });

    $('#playButton2').on('click', function () {
        var maxIndex = DengueTW['total'].length;
        if (false === currentPlayIndex) {
            currentPlayIndex = 0;
        } else {
            currentPlayIndex += 1;
            $(this).addClass('active disabled').find('.glyphicon').show();
        }

        if (currentPlayIndex < maxIndex) {
            showDayMap(new Date(DengueTW['total'][currentPlayIndex][0]), cunli);
            setTimeout(function () {
                $('#playButton2').trigger('click');
            }, 300);
        } else {
            $(this).removeClass('active disabled').find('.glyphicon').hide();
            currentPlayIndex = false;
            $('#title').html('');
        }
        return false;
    });
}

function createStockChart(Cunli, cunli) {
    var series = [];

    for (var i = 0; i < DengueTW[Cunli].length; i++) {
        series.push([new Date(DengueTW[Cunli][i][0]).getTime(), DengueTW[Cunli][i][1]]);
    }

    Highcharts.setOptions({
        lang: {
            months: ['一月', '二月', '三月', '四月', '五月', '六月', '七月', '八月', '九月', '十月', '十一月', '十二月'],
            shortMonths: ['一月', '二月', '三月', '四月', '五月', '六月', '七月', '八月', '九月', '十月', '十一月', '十二月'],
            weekdays: ['星期日', '星期一', '星期二', '星期三', '星期四', '星期五', '星期六'],
            loading: '載入中'
        }
    });

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
                            showDayMap(new Date(this.x), cunli);
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

function showDateMap(clickedDate, cunli) {
    var yyyy = clickedDate.getFullYear().toString(),
            mm = (clickedDate.getMonth() + 1).toString(),
            dd = clickedDate.getDate().toString(),
            clickedDateKey = yyyy + '-' + (mm[1] ? mm : '0' + mm[0]) + '-' + (dd[1] ? dd : '0' + dd[0]);

    $('#title').html(clickedDateKey + ' 累積病例');
    cunli.forEach(function (value) {
        var key = value.getProperty('VILLAGE_ID'),
                count = 0;

        if (DengueTW[key]) {
            DengueTW[key].forEach(function (val) {
                var recordDate = new Date(val[0]);
                if (recordDate <= clickedDate) {
                    count += val[1];
                }
            });
        }
        value.setProperty('num', count);
    });
}

function showDayMap(clickedDate, cunli) {
    var yyyy = clickedDate.getFullYear().toString(),
            mm = (clickedDate.getMonth() + 1).toString(),
            dd = clickedDate.getDate().toString(),
            clickedDateKey = yyyy + '-' + (mm[1] ? mm : "0" + mm[0]) + '-' + (dd[1] ? dd : "0" + dd[0]);

    $('#title').html(clickedDateKey + ' 當日病例');
    cunli.forEach(function (value) {
        var key = value.getProperty('VILLAGE_ID'),
                count = 0;

        if (DengueTW[key]) {
            DengueTW[key].forEach(function (val) {
                if (clickedDateKey == val[0]) {
                    count += val[1];
                }
            });
        }
        value.setProperty('num', count);
    });
}

$(window).resize(function () {
    var len = $('#myTabContent > .tab-pane').length;
    for (var i = 0; i < len; i++) {
        $('#myTabContent > .tab-pane').eq(i).highcharts().setSize($('#myTabContent').width(), $('#myTabContent').height());
    }
});

function closeTab(node) {
    var nodename = node.name;
    node.parentNode.remove();
    $('#' + nodename).remove();
    $('#myTab a:first').tab('show');
}

google.maps.event.addDomListener(window, 'load', initialize);