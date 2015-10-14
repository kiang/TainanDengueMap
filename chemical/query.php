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

/*
 * data.tainan.gov.tw/dataset/4c260d97-e268-4b4a-8b15-c0fc92a25120/resource/f438f96e-b91c-4891-82fb-37d0b6cf71c8/download/10408.csv
 */
$rawPath = __DIR__ . '/raw';
if (!file_exists($rawPath)) {
    mkdir($rawPath, 0777, true);
}
$urlBase = 'http://data.tainan.gov.tw/dataset/4c260d97-e268-4b4a-8b15-c0fc92a25120/resource';
$resources = array(
    '201510' => 'f438f96e-b91c-4891-82fb-37d0b6cf71c8',
    '201509' => '65660288-b205-4ef4-95cc-c5560aec57c9',
    '201508' => 'a0728d53-3a39-4b4a-9558-72ace6327414',
);
$isLatestDone = false;
$data = array();

foreach ($resources AS $ym => $uuid) {
    $rawFile = $rawPath . '/' . $ym . '.csv';
    if (false === $isLatestDone || !file_exists($rawFile)) {
        $isLatestDone = true;
        echo "getting {$rawFile}\n";
        //file_put_contents($rawFile, file_get_contents($urlBase . '/' . $uuid . '/download'));
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
            'date' => $point['日期'],
            'time' => $point['集合時間'],
            'location' => $point['集合地點'],
            'lat' => $point['緯度'],
            'lng' => $point['經度'],
        );
        /*
         * get cunli code
         */
        $cunliParts = explode('里', $line[3]);
        $line[3] = $cunliParts[0] . '里';
        if (isset($cunliCodes[$line[2] . $line[3]])) {
            $data[$lineDayTime]['cunlis'][] = $cunliCodes[$line[2] . $line[3]];
        }
    }
    fclose($fh);
}

krsort($data);
$data = array_values($data);
file_put_contents(__DIR__ . '/overlays.json', json_encode($data));
