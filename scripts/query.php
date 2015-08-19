<?php

$fh = fopen(__DIR__ . '/latest.csv', 'r');
$areaCounter = $timeCounter = array();
while ($line = fgetcsv($fh, 2048)) {
    if ($line[0] === '確定日期') {
        //empty vars
        $currentArea = '';
        $currentDay = array();
    } elseif (!empty($line[3])) {
        if (empty($currentDay)) {
            $dayParts = explode('/', $line[0]);
            $currentDay = implode('-', array(
                $dayParts[0] + 1911,
                str_pad(intval($dayParts[1]), 2, '0', STR_PAD_LEFT),
                str_pad(intval($dayParts[2]), 2, '0', STR_PAD_LEFT),
            ));
        }
        if (!empty($line[1])) {
            $currentArea = $line[1];
        }
        $cPos = strpos($line[2], '(');
        if (false !== $cPos) {
            $line[2] = substr($line[2], 0, $cPos);
        }
        $areaKey = "{$currentArea}{$line[2]}";
        if (!isset($areaCounter[$areaKey])) {
            $areaCounter[$areaKey] = array(
                'total' => $line[3],
                'logs' => array(),
            );
        } else {
            $areaCounter[$areaKey]['total'] += $line[3];
        }
        
        if (!isset($timeCounter[$currentDay])) {
            $timeCounter[$currentDay] = $line[3];
        } else {
            $timeCounter[$currentDay] += $line[3];
        }
        $areaCounter[$areaKey]['logs'][] = array(
            $currentDay,
            $line[3],
        );
    }
}

$json = array();

foreach ($areaCounter AS $areaKey => $val) {
    $json[$areaKey] = array();
    foreach ($val['logs'] AS $log) {
        $log[1] = intval($log[1]);
        $json[$areaKey][] = $log;
    }
}

$json['total'] = array();

foreach($timeCounter AS $date => $val) {
    $json['total'][] = array(
        $date,
        intval($val),
    );
}

file_put_contents(dirname(__DIR__) . '/DengueTN.json', json_encode($json, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));