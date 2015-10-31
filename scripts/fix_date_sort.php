<?php

$json = json_decode(file_get_contents(dirname(__DIR__) . '/DengueTN.json'), true);
unset($json['total']);

$stack = $total = array();
foreach ($json AS $cunli => $logs) {
    if (!isset($stack[$cunli])) {
        $stack[$cunli] = array();
    }
    foreach ($logs AS $log) {
        if (!isset($total[$log[0]])) {
            $total[$log[0]] = 0;
        }
        $total[$log[0]] += $log[1];
        $stack[$cunli][$log[0]] = $log[1];
    }
    ksort($stack[$cunli]);
}
ksort($total);
$stack['total'] = $total;

foreach ($stack AS $cunli => $logs) {
    $json[$cunli] = array();
    foreach ($logs AS $theDate => $num) {
        $json[$cunli][] = array($theDate, $num);
    }
}

file_put_contents(dirname(__DIR__) . '/DengueTN.json', json_encode($json, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
