<?php

$geojson = json_decode(file_get_contents(dirname(__DIR__) . '/cunliTN.json'), true);
$json = json_decode(file_get_contents(dirname(__DIR__) . '/DengueTN.json'), true);

$toMerge = array(
    '安定區海竂里' => '安定區海寮里',
    '安南區鳯凰里' => '安南區鳳凰里',
    '中西區赤崁里' => '中西區赤嵌里',
    '永康區鹽洲里' => '永康區塩洲里',
    '永康北灣里' => '永康區北灣里',
    '永康安康里' => '永康區安康里',
    '永康區鹽行里' => '永康區塩行里',
    '北區合順里' => '北區和順里',
    '七股區鹽埕里' => '七股區塩埕里',
    '永康區二五里' => '永康區二王里',
    '南區 文華里' => '南區文華里',
);

foreach ($toMerge AS $keyFrom => $keyTo) {
    if (!isset($json[$keyTo])) {
        $json[$keyTo] = $json[$keyFrom];
    } else {
        $json[$keyTo] = array_merge($json[$keyFrom], $json[$keyTo]);
    }
    unset($json[$keyFrom]);
}

file_put_contents(dirname(__DIR__) . '/DengueTN.json', json_encode($json, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

//$geoKeys = array();
//foreach ($geojson['features'] AS $geo) {
//    $geoKeys["{$geo['properties']['T_Name']}{$geo['properties']['V_Name']}"] = true;
//}
//
//foreach ($json AS $jsonKey => $records) {
//    if(!isset($geoKeys[$jsonKey])) {
//        echo "{$jsonKey}\n";
//    } else {
//        unset($geoKeys[$jsonKey]);
//    }
//}