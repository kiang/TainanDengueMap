<?php

$cunliCodes = array();
$fh = fopen(dirname(__DIR__) . '/data.tainan/cunli_code.csv', 'r');
while ($line = fgetcsv($fh, 2048)) {
    $cunliCodes[$line[4]] = $line[3] . $line[5];
}
fclose($fh);

$json = json_decode(file_get_contents(dirname(__DIR__) . '/DengueTN.json'), true);

/*
 * looking for 0822 record
 */
foreach ($json['total'] AS $day) {
    if ($day[0] === '2015-08-22') {
        die('0822 record existed');
    }
}

$cdcJson = json_decode(file_get_contents(dirname(__DIR__) . '/taiwan/Dengue.json'), true);

$total = 0;
foreach ($cdcJson AS $cunliCode => $logs) {
    if (substr($cunliCode, 0, 3) === '670') {
        foreach ($logs AS $log) {
            if ($log[0] === '2015-08-22') {
                echo "{$cunliCodes[$cunliCode]}: {$log[1]}\n";

                $areaKey = $cunliCodes[$cunliCode];
                $log[1] = intval($log[1]);

                if ($log[1] > 0) {
                    $total += $log[1];
                    if (!isset($json[$areaKey])) {
                        $json[$areaKey] = array();
                    }
                    $json[$areaKey][] = array(
                        '2015-08-22',
                        $log[1],
                    );
                }
            }
        }
    }
}

$json['total'][] = array(
    '2015-08-22',
    $total,
);

file_put_contents(dirname(__DIR__) . '/DengueTN.json', json_encode($json, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));