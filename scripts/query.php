<?php

$json = json_decode(file_get_contents(dirname(__DIR__) . '/DengueTN.json'), true);

/*
 * looking for 0824 record
 */
foreach ($json['total'] AS $day) {
    if ($day[0] === '2015-08-24') {
        die('0824 record existed');
    }
}

$fh = fopen(__DIR__ . '/latest.csv', 'r');
$currentArea = '';
$total = 0;
while ($line = fgetcsv($fh, 2048)) {
    if (!empty($line[1])) {
        $currentArea = $line[1];
    }
    if (mb_substr($line[4], -1, 1, 'utf-8') !== '里') {
        continue;
    }
    $areaKey = "{$currentArea}{$line[4]}";
    $line[6] = intval($line[6]);

    if ($line[6] > 0) {
        $total += $line[6];
        if (!isset($json[$areaKey])) {
            $json[$areaKey] = array();
        }
        $json[$areaKey][] = array(
            '2015-08-24',
            $line[6],
        );
    }
}

$json['total'][] = array(
    '2015-08-24',
    $total,
);


file_put_contents(dirname(__DIR__) . '/DengueTN.json', json_encode($json, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
