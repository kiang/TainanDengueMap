<?php

$cunliCodes = array();
$fh = fopen(dirname(__DIR__) . '/data.tainan/cunli_code.csv', 'r');
fgetcsv($fh, 2048);
while ($line = fgetcsv($fh, 2048)) {
    if ($line[1] === '台南市') {
        $cunliCodes[$line[3] . $line[5]] = $line[4];
    }
}
fclose($fh);

$mappings = array(
    '新化區清水/觀音里' => array(
        '6701800-006', '6701800-008'
    ),
    '新化區武安市場里' => array(
        '6701800-001',
    ),
    '麻豆區晉江里' => array(
        '6700700-004'
    ),
    '麻豆區第五市場里' => array(
        '6700700-011'
    ),
    '永康區鹽行里' => array('6703100-010'),
    '永康區鹽洲里' => array('6703100-029'),
    '安南區沙崙里' => array('6703500-033'),
    '北區北區里' => array(),
    '麻豆區麻豆里' => array(),
    '新市區三合里' => array('6702000-006'),
);

/*
 * data.tainan.gov.tw/dataset/4c260d97-e268-4b4a-8b15-c0fc92a25120/resource/f438f96e-b91c-4891-82fb-37d0b6cf71c8/download/10408.csv
 */
$rawPath = __DIR__ . '/raw';
if (!file_exists($rawPath)) {
    mkdir($rawPath, 0777, true);
}
$urlBase = 'http://data.tainan.gov.tw/dataset/4c260d97-e268-4b4a-8b15-c0fc92a25120/resource';
$resources = array();
$dataset = json_decode(file_get_contents('http://data.tainan.gov.tw/api/action/package_show?id=4c260d97-e268-4b4a-8b15-c0fc92a25120'), true);
foreach ($dataset['result']['resources'] AS $resource) {
    $parts = explode('年', substr($resource['name'], 0, strpos($resource['name'], '月')));
    $parts[0] += 1911;
    $key = $parts[0] . str_pad($parts[1], 2, '0', STR_PAD_LEFT);
    $resources[$key] = $resource['id'];
}
krsort($resources);
$isLatestDone = false;
$data = array();

foreach ($resources AS $ym => $uuid) {
    $rawFile = $rawPath . '/' . $ym . '.csv';
    if (false === $isLatestDone || !file_exists($rawFile)) {
        $isLatestDone = true;
        file_put_contents($rawFile, file_get_contents($urlBase . '/' . $uuid . '/download'));
    }
    $fh = fopen($rawFile, 'r');
    $headers = fgetcsv($fh, 2048);
    while ($line = fgetcsv($fh, 2048)) {
        /*
         * get date/time
         */
        preg_match_all('/[0-9]+/i', $line[4], $dateParts);
        if (empty($dateParts[0][0])) {
            print_r($line);
            exit();
        }
        $lineDayTime = strtotime('2015-' . implode('-', $dateParts[0]));
        if (!isset($data[$lineDayTime])) {
            $data[$lineDayTime] = array(
                'points' => array(),
                'cunlis' => array(),
            );
        }
        if (count($headers) !== count($line)) {
            $line[] = '';
        }

        $point = array_combine($headers, $line);
        $data[$lineDayTime]['points'][] = array(
            'date' => date('Y-m-d', $lineDayTime),
            'time' => $point['集合時間'],
            'location' => $point['集合地點'],
            'lat' => $point['緯度'],
            'lng' => $point['經度'],
        );
        /*
         * get cunli code
         */
        $areaParts = explode('區', $line[2]);
        $cunliParts = explode('里', $line[3]);
        $line[2] = $areaParts[0] . '區';
        $line[3] = $cunliParts[0] . '里';
        $line[3] = preg_replace('/[a-z\\(\\) ]/i', '', $line[3]);
        $finalKey = $line[2] . $line[3];
        if (isset($mappings[$finalKey])) {
            foreach ($mappings[$finalKey] AS $mapedCode) {
                $data[$lineDayTime]['cunlis'][] = $mapedCode;
            }
        } else if (isset($cunliCodes[$finalKey])) {
            $data[$lineDayTime]['cunlis'][] = $cunliCodes[$finalKey];
        } else {
            echo "{$line[2]}{$line[3]}\n";
        }
    }
    fclose($fh);
}

krsort($data);
$data = array_values($data);
file_put_contents(__DIR__ . '/overlays.json', json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
