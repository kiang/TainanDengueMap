$.ajaxSetup({async: false});

function ColorBar(value) {
    switch (value) {
        case '0':
            return 'white';
        case '1':
            return 'gray';
        case '2':
            return 'green'
        case '3':
            return 'olive';
        case '4':
            return 'yellow';
        case '5':
            return 'orange';
        case '6':
            return 'red';
        case '7':
            return 'purple';
        case '8':
            return 'darkblue';
        case '9':
            return 'black';
    }
    return 'white';
}

var map,
        currentPlayIndex = false,
        cunli,
        currentKey = 'BILv',
        cunliTitle = {},
        labels = {
            'AreaType': '調查地區分類',
            'HouseHold': '調查戶數',
            'InspectType': '調查人員種類',
            'PosHH': '陽性戶數',
            'PosHHAeg': '陽性戶數(有埃及班蚊)',
            'ConIn': '調查容器數(戶內)',
            'ConOut': '調查容器數(戶外)',
            'ConAll': '調查容器數(合計)',
            'PosConIn': '陽性容器數(戶內)',
            'PosConOut': '陽性容器數(戶外)',
            'PosConAll': '陽性容器數(合計)',
            'FAegIn': '採獲埃及斑紋雌蟲數(戶內)',
            'FAegOut': '採獲埃及斑紋雌蟲數(戶外)',
            'FAlbIn': '採獲白線斑紋雌蟲數(戶內)',
            'FAlbOut': '採獲白線斑紋雌蟲數(戶外)',
            'LarvaAeg': '孳生埃及斑紋幼蟲隻數',
            'LarvaAlb': '孳生白線斑紋幼蟲隻數',
            'LarvaNEC': '孳生斑紋幼蟲隻數(未分類)',
            'PI': '蛹指數 Pupa index (PI)',
            'BI': '布氏指數 Breteau index (BI)',
            'BILv': '布氏級數',
            'AIAeg': '成蟲指數(埃及)',
            'AIAlb': '成蟲指數(白線)',
            'HI': '住宅指數 House index (HI)',
            'HIAeg': '住宅指數(有埃及斑蚊)',
            'HILv': '住宅級數',
            'HILvAeg': '住宅級數(有埃及斑蚊)',
            'CI': '容器指數 Container index (CI)',
            'CILv': '容器級數',
            'LI': '幼蟲指數 Larva index (LI)',
            'LILv': '幼蟲級數',
            'Pupa': '孳生斑紋蛹數',
            'AI': '成蟲指數 Adult index (AI)',
            'Con100HH': '每百戶積水容器數'
        };

var currentYear = new Date().getFullYear();

$.getJSON('json/' + currentYear + '.json', function (data) {
    mosIndex = data;
});
function initialize() {

    /*map setting*/
    $('#map-canvas').height(window.outerHeight / 2.2);

    map = new google.maps.Map(document.getElementById('map-canvas'), {
        zoom: 12,
        center: {lat: 23.00, lng: 120.30}
    });

    $.getJSON('../taiwan/2015/cunli.json', function (data) {
        cunli = map.data.addGeoJson(topojson.feature(data, data.objects.cunli));
    });

    map.data.addListener('mouseover', function (event) {
        var Cunli = cunliTitle[event.feature.getProperty('VILLAGE_ID')];
        map.data.revertStyle();
        map.data.overrideStyle(event.feature, {fillColor: 'white'});
        $('#content').html('<div>' + Cunli + ' ：' + event.feature.getProperty('num') + ' @ ' + event.feature.getProperty('numDate') + '</div>').removeClass('text-muted');
    });

    map.data.addListener('mouseout', function (event) {
        map.data.revertStyle();
        $('#content').html('在地圖上滑動或點選以顯示數據').addClass('text-muted');
    });

    map.data.addListener('click', function (event) {
        var Cunli = event.feature.getProperty('VILLAGE_ID');
        var CunliTitle = cunliTitle[Cunli];
        $('#title').html(CunliTitle);
        if ($('#myTab a[name|="' + Cunli + '"]').tab('show').length === 0) {
            $('#myTab').append('<li><a name="' + Cunli + '" href="#' + Cunli + '" data-toggle="tab">' + CunliTitle +
                    '<button class="close" onclick="closeTab(this.parentNode)">×</button></a></li>');
            $('#myTabContent').append('<div class="tab-pane fade" id="' + Cunli + '"><div></div></div>');
            $('#myTab a:last').tab('show');
            createStockChart(Cunli);
            $('#myTab li a:last').click(function (e) {
                $(window).trigger('resize');
            });
        }
    });

    $('a', $('#keyButtons')).click(function () {
        currentKey = $(this).attr('data-key');
        updateNum();
        return false;
    });

    Highcharts.setOptions({
        lang: {
            months: ['一月', '二月', '三月', '四月', '五月', '六月', '七月', '八月', '九月', '十月', '十一月', '十二月'],
            shortMonths: ['一月', '二月', '三月', '四月', '五月', '六月', '七月', '八月', '九月', '十月', '十一月', '十二月'],
            weekdays: ['星期日', '星期一', '星期二', '星期三', '星期四', '星期五', '星期六'],
            loading: '載入中'
        }
    });

    updateNum();
}

function updateNum() {
    cunli.forEach(function (value) {
        var key = value.getProperty('VILLAGE_ID');
        cunliTitle[key] = value.getProperty('C_Name') + value.getProperty('T_Name') + value.getProperty('V_Name');
        if (mosIndex[key]) {
            for (k in mosIndex[key]) {
                value.setProperty('num', mosIndex[key][k][currentKey]);
                value.setProperty('numDate', k);
                break;
            }
        } else {
            value.setProperty('num', '0');
            value.setProperty('numDate', '-');
        }
    });

    map.data.setStyle(function (feature) {
        color = ColorBar(feature.getProperty('num'));
        return {
            fillColor: color,
            fillOpacity: 0.6,
            strokeColor: 'gray',
            strokeWeight: 1
        }
    });

    $('a', $('#keyButtons')).each(function() {
        var buttonKey = $(this).attr('data-key');
        if(buttonKey === currentKey) {
            $(this).addClass('active disabled').find('.glyphicon').show();
        } else {
            $(this).removeClass('active disabled').find('.glyphicon').hide();
        }
    });
}

function showDateBlock(clickedDate, cunliCode) {
    var yyyy = clickedDate.getFullYear().toString(),
            mm = (clickedDate.getMonth() + 1).toString(),
            dd = clickedDate.getDate().toString(),
            clickedDateKey = yyyy + '-' + (mm[1] ? mm : '0' + mm[0]) + '-' + (dd[1] ? dd : '0' + dd[0]);
    var table = '<table class="table table-bordered">';
    table += '<tr><td class="info">村里</td><td>' + cunliTitle[cunliCode] + '</td></tr>';
    table += '<tr><td class="info">日期</td><td>' + clickedDateKey + '</td></tr>';
    for (k in mosIndex[cunliCode][clickedDateKey]) {
        table += '<tr><td class="info">' + labels[k] + '</td><td>' + mosIndex[cunliCode][clickedDateKey][k] + '</td></tr>';
    }
    table += '</table>';
    $('#cunliDateBlock').html(table);
    $('#title').html(cunliTitle[cunliCode]);
}

function createStockChart(Cunli) {
    var series = [];

    for (k in mosIndex[Cunli]) {
        var lineVal = mosIndex[Cunli][k][currentKey];
        if (lineVal.indexOf('.') === -1) {
            lineVal = parseInt(lineVal);
        } else {
            lineVal = parseFloat(lineVal);
        }
        series.push([new Date(k).getTime(), lineVal]);
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
                            showDateBlock(new Date(this.x), Cunli);
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

function closeTab(node) {
    var nodename = node.name;
    node.parentNode.remove();
    $('#' + nodename).remove();
}

google.maps.event.addDomListener(window, 'load', initialize);
