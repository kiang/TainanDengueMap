<?php

$cunliCodes = array();
$fh = fopen(dirname(__DIR__) . '/data.tainan/cunli_code.csv', 'r');
while ($line = fgetcsv($fh, 2048)) {
    $cunliCodes[$line[4]] = $line[3] . $line[5];
}
fclose($fh);

$json = json_decode(file_get_contents(dirname(__DIR__) . '/DengueTN.json'), true);

$cdcJson = json_decode(file_get_contents(dirname(__DIR__) . '/taiwan/Dengue.json'), true);

$total = 0;
$insertTime = strtotime('2015-10-20');
foreach ($cdcJson AS $cunliCode => $logs) {
    if (substr($cunliCode, 0, 3) === '670') {
        foreach ($logs AS $log) {
            if ($log[0] === '2015-10-20') {
                $areaKey = $cunliCodes[$cunliCode];
                $log[1] = intval($log[1]);

                if ($log[1] > 0) {
                    $total += $log[1];
                    if (!isset($json[$areaKey])) {
                        $json[$areaKey] = array();
                    }
                    $index = 0;
                    foreach ($json[$areaKey] AS $k => $v) {
                        if (strtotime($v[0]) < $insertTime) {
                            $index = $k + 1;
                        }
                    }
                    array_splice($json[$areaKey], $index, 0, array(array(
                            '2015-10-20',
                            $log[1],
                        ))
                    );
                }
            }
        }
    }
}

$index = 0;
foreach ($json['total'] AS $k => $v) {
    if (strtotime($v[0]) < $insertTime) {
        $index = $k + 1;
    }
}
array_splice($json['total'], $index, 0, array(array(
        '2015-10-20',
        $total,
    ))
);

file_put_contents(dirname(__DIR__) . '/DengueTN.json', json_encode($json, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
