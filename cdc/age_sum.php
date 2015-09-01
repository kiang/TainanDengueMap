<?php

$fh = fopen(__DIR__ . '/Dengue_Daily.csv', 'r');
$data = array();
fgetcsv($fh, 2048);
while ($line = fgetcsv($fh, 2048)) {
    if (empty($line[7]))
        continue;
    foreach ($line AS $k => $v) {
        $line[$k] = str_replace(array('　', ' '), '', $v);
    }
    $dayParts = explode('/', $line[2]);
    if (!isset($data[$dayParts[0]])) {
        $data[$dayParts[0]] = array();
    }
    if (!isset($data['total'])) {
        $data['total'] = array();
    }
    if (!isset($data[$dayParts[0]][$line[4]])) {
        $data[$dayParts[0]][$line[4]] = intval($line[13]);
    } else {
        $data[$dayParts[0]][$line[4]] += intval($line[13]);
    }

    if (!isset($data['total'][$line[4]])) {
        $data['total'][$line[4]] = intval($line[13]);
    } else {
        $data['total'][$line[4]] += intval($line[13]);
    }
}

foreach ($data AS $y => $r) {
    arsort($data[$y]);
}

print_r($data);
