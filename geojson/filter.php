<?php

$json = json_decode(file_get_contents(__DIR__ . '/cunli.json'));
foreach ($json->features AS $k => $f) {
    if ($f->properties->C_Name !== '臺南市') {
        unset($json->features[$k]);
    }
}
$json->features = array_values($json->features);
file_put_contents(dirname(__DIR__) . '/cunliTN.json', json_encode($json, JSON_UNESCAPED_UNICODE));
