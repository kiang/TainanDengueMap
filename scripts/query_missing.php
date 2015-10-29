<?php

foreach (glob(dirname(__DIR__) . '/raw/2015102*') AS $rawFile) {
    exec("/usr/bin/unoconv -f csv {$rawFile}");
    $rawCsvFile = substr($rawFile, 0, strrpos($rawFile, '.xls')) . '.csv';
    if (file_exists($rawCsvFile)) {
        $fh = fopen($rawCsvFile, 'r');
        $line = fgetcsv($fh, 2048);
        if (!empty($line[2])) {
            $fileDate = substr($line[2], strrpos($line[2], '-') + 1);
            $fileDate = explode('/', $fileDate);
            $fileDate[0] += 1911;
            $fileDate = implode('-', $fileDate);

            $json = json_decode(file_get_contents(dirname(__DIR__) . '/DengueTN.json'), true);

            $currentArea = '';
            $total = 0;
            while ($line = fgetcsv($fh, 2048)) {
                if (!empty($line[2])) {
                    $currentArea = $line[2];
                }
                if (mb_substr($line[5], -1, 1, 'utf-8') !== '里') {
                    continue;
                }
                $areaKey = "{$currentArea}{$line[5]}";
                $line[7] = intval($line[7]);

                if ($line[7] > 0) {
                    if (!isset($json[$areaKey])) {
                        $json[$areaKey] = array();
                    }
                    /*
                     * look for the same date
                     */
                    $dateExisted = false;
                    foreach ($json[$areaKey] AS $item) {
                        if ($item[0] === $fileDate) {
                            $dateExisted = true;
                        }
                    }
                    if (false === $dateExisted) {
                        $total += $line[7];
                        $json[$areaKey][] = array(
                            $fileDate,
                            $line[7],
                        );
                    }
                }
            }

            $json['total'][] = array(
                $fileDate,
                $total,
            );

            file_put_contents(dirname(__DIR__) . '/DengueTN.json', json_encode($json, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        }

        unlink($rawCsvFile);
    }
}