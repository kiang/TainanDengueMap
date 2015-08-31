<?php

$cunliCodes = array();
$fh = fopen(dirname(__DIR__) . '/data.tainan/cunli_code.csv', 'r');
while ($line = fgetcsv($fh, 2048)) {
    $cunliCodes[$line[1] . $line[3] . $line[5]] = $line[4];
}
fclose($fh);

/*
 * Array
  (
  [0] => 發病日
  [1] => 個案研判日
  [2] => 通報日
  [3] => 性別
  [4] => 年齡層
  [5] => 居住縣市
  [6] => 居住鄉鎮
  [7] => 居住村里
  [8] => 感染縣市
  [9] => 感染鄉鎮
  [10] => 感染村里
  [11] => 是否境外移入
  [12] => 感染國家
  [13] => 確定病例數
  )
 */

$replaces = array(
    '台南市永康區鹽行里' => '台南市永康區塩行里',
    '台南市永康區鹽洲里' => '台南市永康區塩洲里',
    '台南市學甲區西明里' => '台南市學甲區明宜里',
    '台東縣太麻里鄉金崙村' => '臺東縣太麻里鄉金崙村',
    '高雄市左營區部南里' => '高雄市左營區廍南里',
    '台南市學甲區煥昌里' => '台南市學甲區秀昌里',
    '台南市安南區鹽田里' => '台南市安南區塩田里',
);

file_put_contents(__DIR__ . '/Dengue_Daily.csv', file_get_contents('http://nidss.cdc.gov.tw/download/Dengue_Daily.csv'));

$fh = fopen(__DIR__ . '/Dengue_Daily.csv', 'r');
$areaCounter = $timeCounter = array();
while ($line = fgetcsv($fh, 2048)) {
    if (empty($line[7]))
        continue;
    foreach ($line AS $k => $v) {
        $line[$k] = str_replace(array('　', ' '), '', mb_convert_encoding($v, 'utf-8', 'big5'));
    }
    $dayParts = explode('/', $line[2]);
    if ($dayParts[0] === '2015') {
        $currentDay = implode('-', array(
            $dayParts[0],
            str_pad(intval($dayParts[1]), 2, '0', STR_PAD_LEFT),
            str_pad(intval($dayParts[2]), 2, '0', STR_PAD_LEFT),
        ));

        $areaKey = $line[5] . $line[6] . $line[7];
        $areaKey = strtr($areaKey, $replaces);
        $areaKey = $cunliCodes[$areaKey];

        if (!isset($areaCounter[$areaKey])) {
            $areaCounter[$areaKey] = array(
                'total' => $line[13],
                'logs' => array(),
            );
        } else {
            $areaCounter[$areaKey]['total'] += $line[13];
        }

        if (!isset($timeCounter[$currentDay])) {
            $timeCounter[$currentDay] = $line[13];
        } else {
            $timeCounter[$currentDay] += $line[13];
        }
        if (!isset($areaCounter[$areaKey]['logs'][$currentDay])) {
            $areaCounter[$areaKey]['logs'][$currentDay] = $line[13];
        } else {
            $areaCounter[$areaKey]['logs'][$currentDay] += $line[13];
        }
    }
}

ksort($areaCounter);

$json = array();

foreach ($areaCounter AS $areaKey => $val) {
    $json[$areaKey] = array();
    ksort($val['logs']);
    foreach ($val['logs'] AS $theDate => $num) {
        $json[$areaKey][] = array(
            $theDate,
            intval($num),
        );
    }
}

$json['total'] = array();
ksort($timeCounter);
foreach ($timeCounter AS $date => $val) {
    $json['total'][] = array(
        $date,
        intval($val),
    );
}

file_put_contents(dirname(__DIR__) . '/taiwan/Dengue.json', json_encode($json, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
