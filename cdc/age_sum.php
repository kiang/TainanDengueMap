<?php

$fh = fopen(__DIR__ . '/Dengue_Daily.csv', 'r');
$data = array();
fgetcsv($fh, 2048);
while ($line = fgetcsv($fh, 2048)) {
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

$base = array(
    '70+' => 0,
    '65-69' => 0,
    '60-64' => 0,
    '55-59' => 0,
    '50-54' => 0,
    '45-49' => 0,
    '40-44' => 0,
    '35-39' => 0,
    '30-34' => 0,
    '25-29' => 0,
    '20-24' => 0,
    '15-19' => 0,
    '10-14' => 0,
    '5-9' => 0,
    4 => 0,
    3 => 0,
    2 => 0,
    1 => 0,
    0 => 0,
);

$fh = fopen(__DIR__ . '/age_sum.csv', 'w');
fputcsv($fh, array_merge(array('年度'), array_keys($base)));

foreach ($data AS $y => $levels) {
    if ($y === 'total')
        continue;
    $new = $base;
    foreach ($levels AS $level => $count) {
        if (isset($new[$level])) {
            $new[$level] = $count;
        }
    }
    fputcsv($fh, array_merge(array($y), $new));
}