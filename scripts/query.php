<?php

$json = json_decode(file_get_contents(dirname(__DIR__) . '/DengueTN.json'), true);

/*
 * looking for 09-17 record
 */
foreach ($json['total'] AS $day) {
    if ($day[0] === '2015-09-17') {
        die('2015-09-17 record existed');
    }
}

$fh = fopen(__DIR__ . '/latest.csv', 'r');
$currentArea = '';
$total = 0;
while ($line = fgetcsv($fh, 2048)) {
    if (!empty($line[0])) {
        $currentArea = $line[0];
    }
    if (mb_substr($line[3], -1, 1, 'utf-8') !== '里') {
        continue;
    }
    $areaKey = "{$currentArea}{$line[3]}";
    $line[5] = intval($line[5]);
    
    if ($line[5] > 0) {
        $total += $line[5];
        if (!isset($json[$areaKey])) {
            $json[$areaKey] = array();
        }
        $json[$areaKey][] = array(
            '2015-09-17',
            $line[5],
        );
    }
}

$json['total'][] = array(
    '2015-09-17',
    $total,
);


file_put_contents(dirname(__DIR__) . '/DengueTN.json', json_encode($json, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
