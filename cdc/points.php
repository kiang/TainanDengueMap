<?php

$data = array();

$cityCodes = array(
    '01' => '台北市',
    '03' => '台中市',
    '05' => '台南市',
    '07' => '高雄市',
    '11' => '基隆市',
    '12' => '新竹市',
    '22' => '嘉義市',
    '31' => '新北市',
    '32' => '桃園市',
    '33' => '新竹縣',
    '34' => '宜蘭縣',
    '35' => '苗栗縣',
    '37' => '彰化縣',
    '38' => '南投縣',
    '39' => '雲林縣',
    '40' => '嘉義縣',
    '43' => '屏東縣',
    '44' => '澎湖縣',
    '45' => '花蓮縣',
    '46' => '台東縣',
    '90' => '金門縣',
    '91' => '連江縣',
);

foreach ($cityCodes AS $cityCode => $city) {
    echo "processing {$city}\n";
    $data[$cityCode] = array(
        'city' => $city,
        'records' => array(),
    );
    for ($i = 0; $i < 2; $i ++) {
        $opts = array('http' =>
            array(
                'method' => 'POST',
                'header' => "Content-type: application/json; charset=utf-8\r\n" .
                "Referer: http://cdcdengue.azurewebsites.net/\r\n" .
                "X-Requested-With: XMLHttpRequest",
                'content' => "{citycode:'{$cityCode}', immigration: '{$i}'}"
            )
        );

        $context = stream_context_create($opts);

        $result = file_get_contents('http://cdcdengue.azurewebsites.net/DengueData.asmx/GetDengueLocation', false, $context);

        $json = json_decode($result);
        $records = json_decode($json->d, true);
        foreach ($records AS $record) {
            $record['immigration'] = $i;
            $data[$cityCode]['records'][] = $record;
        }
    }
}

file_put_contents(__DIR__ . '/points.json', json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
file_put_contents(__DIR__ . '/points.min.json', json_encode($data));
