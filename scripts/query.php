<?php

$json = json_decode(file_get_contents(dirname(__DIR__) . '/DengueTN.json'), true);

/*
 * looking for 09-19 record
 */
foreach ($json['total'] AS $day) {
    if ($day[0] === '2015-09-19') {
        die('2015-09-19 record existed');
    }
}

$fh = fopen(__DIR__ . '/latest.csv', 'r');
$currentArea = '';
$total = 0;
while ($line = fgetcsv($fh, 2048)) {
    if (!empty($line[2])) {
        $currentArea = $line[2];
    }
    if (mb_substr($line[5], -1, 1, 'utf-8') !== '里') {
        continue;
    }
    $areaKey = "{$currentArea}{$line[5]}";
    $line[7] = intval($line[7]);
    
    if ($line[7] > 0) {
        $total += $line[7];
        if (!isset($json[$areaKey])) {
            $json[$areaKey] = array();
        }
        $json[$areaKey][] = array(
            '2015-09-19',
            $line[7],
        );
    }
}

$json['total'][] = array(
    '2015-09-19',
    $total,
);


file_put_contents(dirname(__DIR__) . '/DengueTN.json', json_encode($json, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
