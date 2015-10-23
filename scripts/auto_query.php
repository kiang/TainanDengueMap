<?php

$page1Url = 'http://health.tainan.gov.tw/tnhealth/Medical/Medical.aspx?Medical_Index=4&Medical_Class=108';
$page1 = file_get_contents($page1Url);
$page1Parts = explode('detail.aspx?Id=', $page1);
if (isset($page1Parts[1])) {
    $page2Url = 'http://health.tainan.gov.tw/tnhealth/Medical/detail.aspx?Id=' . substr($page1Parts[1], 0, strpos($page1Parts[1], '>'));
    $page2 = file_get_contents($page2Url);
    $page2Parts = explode('manasystem/files/Medical/', $page2);
    if (isset($page2Parts[1])) {
        $fileName = substr($page2Parts[1], 0, strpos($page2Parts[1], '.xls')) . '.xls';
        $rawFile = dirname(__DIR__) . '/raw/' . $fileName;
        if (!file_exists($rawFile)) {
            file_put_contents($rawFile, file_get_contents('http://health.tainan.gov.tw/tnhealth/manasystem/files/Medical/' . urlencode($fileName)));
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
                            $total += $line[7];
                            if (!isset($json[$areaKey])) {
                                $json[$areaKey] = array();
                            }
                            $json[$areaKey][] = array(
                                $fileDate,
                                $line[7],
                            );
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
    }
}