<?php

$json = json_decode(file_get_contents(dirname(__DIR__) . '/DengueTN.json'), true);

/*
 * looking for 0822 record
 */
foreach ($json['total'] AS $day) {
    if ($day[0] === '2015-08-22') {
        die('0822 record existed');
    }
}

$cunliTotal = array();
foreach ($json AS $jsonKey => $records) {
    if (!isset($cunliTotal[$jsonKey])) {
        $cunliTotal[$jsonKey] = 0;
    }
    foreach ($records AS $record) {
        $cunliTotal[$jsonKey] += $record[1];
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
    $areaNum = 0;

    if (isset($cunliTotal[$areaKey])) {
        /*
         * $cunliTotal[$areaKey] = 08/21 累積數字 / 1555
         * $line[5] = 到 08/23 累積數字 / 1816
         * $line[6] = 08/23 新增數字 / 147
         * $line[5] - $line[6] = 08/22 累積數字 / 1669
         * 08/22 新增病例 114
         * 
         * 
         */
        $areaNum = ($line[5] - $line[6]) - $cunliTotal[$areaKey];
    } else {
        $areaNum = ($line[5] - $line[6]);
    }
    
    if($areaNum < 0) {
        print_r($line);
        print_r($cunliTotal[$areaKey]);
        print_r($json[$areaKey]);
        echo "{$areaKey}: {$areaNum}\n";
    }
    

    if (!empty($areaNum)) {
        $total += $areaNum;
        if (!isset($json[$areaKey])) {
            $json[$areaKey] = array();
        }
        $json[$areaKey][] = array(
            '2015-08-22',
            $areaNum,
        );
    }
}

$json['total'][] = array(
    '2015-08-22',
    $total,
);

echo $total;

//file_put_contents(dirname(__DIR__) . '/DengueTN.json', json_encode($json, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
