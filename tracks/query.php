<?php

/*
 * data.tainan.gov.tw/dataset/4c260d97-e268-4b4a-8b15-c0fc92a25120/resource/f438f96e-b91c-4891-82fb-37d0b6cf71c8/download/10408.csv
 */

$rawPath = __DIR__ . '/raw';
if (!file_exists($rawPath)) {
    mkdir($rawPath, 0777, true);
}
$urlBase = 'http://data.tainan.gov.tw/dataset/cc526066-95de-4687-b5a9-74e30f6628a5/resource';
$data = array();
$dataset = json_decode(file_get_contents('http://data.tainan.gov.tw/api/3/action/package_show?id=cc526066-95de-4687-b5a9-74e30f6628a5'), true);
foreach ($dataset['result']['resources'] AS $resource) {
    $csvFile = $rawPath . '/' . $resource['name'] . '.csv';
    if (!file_exists($csvFile)) {
        file_put_contents($csvFile, file_get_contents($urlBase . '/' . $resource['id'] . '/download'));
    }
    if (filesize($csvFile) === 0) {
        unlink($csvFile);
    } else {
        $fh = fopen($csvFile, 'r');
        $lastTime = 0;
        $lastPoint = array();
        fgetcsv($fh, 2048);
        while ($line = fgetcsv($fh, 2048)) {
            $line[1] = floatval($line[1]); //latitude
            if ($line[1] > 23.421940 || $line[1] < 22.876448) {
                continue;
            }
            $line[2] = floatval($line[2]); //longitude
            if ($line[2] > 120.652876 || $line[2] < 120.026655) {
                continue;
            }
            $currentPoint = array($line[2], $line[1]);
            if ($currentPoint === $lastPoint) {
                continue;
            }
            $lastPoint = $currentPoint;
            $pointTime = strtotime($line[4]);
            $weekNum = date('YW', $pointTime);
            if (!isset($data[$weekNum])) {
                $data[$weekNum] = array();
            }
            end($data[$weekNum]);
            $lastKey = key($data[$weekNum]);
            if (NULL === $lastKey) {
                $lastKey = -1;
            }
            if (abs($pointTime - $lastTime) > 3600) {
                ++$lastKey;
                $data[$weekNum][$lastKey] = array(
                    'device' => $line[3],
                    'timeBegin' => $line[4],
                    'timeEnd' => $line[4],
                    'points' => array(
                        $currentPoint
                    ),
                );
            } else {
                $data[$weekNum][$lastKey]['timeEnd'] = $line[4];
                $data[$weekNum][$lastKey]['points'][] = $currentPoint;
            }
            $lastTime = $pointTime;
        }
    }
}

ksort($data);

$jsonPath = __DIR__ . '/json';

foreach ($data AS $weekNum => $weekData) {
    $yPath = $jsonPath . '/' . substr($weekNum, 0, 4);
    if (!file_exists($yPath)) {
        mkdir($yPath, 0777, true);
    }
    $fc = new stdClass();
    $fc->type = 'FeatureCollection';
    $fc->features = array();
    foreach ($weekData AS $line) {
        $f = new stdClass();
        $f->type = 'Feature';
        $f->properties = array(
            'device' => $line['device'],
            'timeBegin' => $line['timeBegin'],
            'timeEnd' => $line['timeEnd'],
        );
        $f->geometry = new stdClass();
        $f->geometry->type = 'LineString';
        $f->geometry->coordinates = $line['points'];
        $fc->features[] = $f;
    }
    file_put_contents($yPath . '/' . $weekNum . '.json', json_encode($fc, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
}