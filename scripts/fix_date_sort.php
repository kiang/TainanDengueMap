<?php

$json = json_decode(file_get_contents(dirname(__DIR__) . '/DengueTN.json'), true);

$total = 0;
$stack = array();
foreach ($json AS $cunli => $logs) {
    if (!isset($stack[$cunli])) {
        $stack[$cunli] = array();
    }
    foreach ($logs AS $log) {
        $stack[$cunli][$log[0]] = $log[1];
    }
    ksort($stack[$cunli]);
}

foreach ($stack AS $cunli => $logs) {
    $json[$cunli] = array();
    foreach ($logs AS $theDate => $num) {
        $json[$cunli][] = array($theDate, $num);
    }
}

file_put_contents(dirname(__DIR__) . '/DengueTN.json', json_encode($json, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
