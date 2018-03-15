<?php

/*
 * http://nidss.cdc.gov.tw/download/MosIndex/MosIndex_All_last12m.csv
 * 
 * Array
  (
  [0] => Date	調查日期
  [1] => County	縣市
  [2] => Town	鄉鎮市區
  [3] => Village	村里
  [4] => VillageID	村里代碼(主計總處代碼)
  [5] => VillageLon	村里中心點經度
  [6] => VillageLat	村里中心點緯度
  [7] => AreaType	調查地區分類
  [8] => HouseHold	調查戶數
  [9] => InspectType	調查人員種類
  [10] => PosHH	陽性戶數
  [11] => PosHHAeg	陽性戶數(有埃及班蚊)
  [12] => ConIn	調查容器數(戶內)
  [13] => ConOut	調查容器數(戶外)
  [14] => ConAll	調查容器數(合計)
  [15] => PosConIn	陽性容器數(戶內)
  [16] => PosConOut	陽性容器數(戶外)
  [17] => PosConAll	陽性容器數(合計)
  [18] => FAegIn	採獲埃及斑紋雌蟲數(戶內)
  [19] => FAegOut	採獲埃及斑紋雌蟲數(戶外)
  [20] => FAlbIn	採獲白線斑紋雌蟲數(戶內)
  [21] => FAlbOut	採獲白線斑紋雌蟲數(戶外)
  [22] => LarvaAeg	孳生埃及斑紋幼蟲隻數
  [23] => LarvaAlb	孳生白線斑紋幼蟲隻數
  [24] => LarvaNEC	孳生斑紋幼蟲隻數(未分類)
  [25] => PI	蛹指數 Pupa index (PI)
  [26] => BI	布氏指數 Breteau index (BI)
  [27] => BILv	布氏級數
  [28] => AIAeg	成蟲指數(埃及)
  [29] => AIAlb	成蟲指數(白線)
  [30] => HI	住宅指數 House index (HI)
  [31] => HIAeg	住宅指數(有埃及斑蚊)
  [32] => HILv	住宅級數
  [33] => HILvAeg	住宅級數(有埃及斑蚊)
  [34] => CI	容器指數 Container index (CI)
  [35] => CILv	容器級數
  [36] => LI	幼蟲指數 Larva index (LI)
  [37] => LILv	幼蟲級數
  [38] => Pupa	孳生斑紋蛹數
  [39] => AI	成蟲指數 Adult index (AI)
  [40] => Con100HH	每百戶積水容器數
  )
 */
$rootPath = dirname(__DIR__);
//$fh = fopen($rootPath . '/raw/MosIndex_All.csv', 'r');

$arrContextOptions=array(
    "ssl"=>array(
        "verify_peer"=>false,
        "verify_peer_name"=>false,
    ),
);

file_put_contents($rootPath . '/raw/MosIndex_All_last12m.csv', file_get_contents('https://nidss.cdc.gov.tw/download/MosIndex/MosIndex_All_last12m.csv', false, stream_context_create($arrContextOptions)));
$fh = fopen($rootPath . '/raw/MosIndex_All_last12m.csv', 'r');

$headers = fgetcsv($fh, 2048);
for ($k = 0; $k < 7; $k++) {
    unset($headers[$k]);
}
$data = array();
while ($line = fgetcsv($fh, 2048)) {
    $dateParts = explode('/', $line[0]);
    $cunliCode = $line[4];
    $dateKey = implode('-', $dateParts);
    if (!isset($data[$dateParts[0]])) {
        $data[$dateParts[0]] = array();
    }
    if (!isset($data[$dateParts[0]][$line[4]])) {
        $data[$dateParts[0]][$cunliCode] = array();
    }
    for ($k = 0; $k < 7; $k++) {
        unset($line[$k]);
    }
    $data[$dateParts[0]][$cunliCode][$dateKey] = array_combine($headers, $line);
}

if (!file_exists($rootPath . '/json')) {
    mkdir($rootPath . '/json', 0777, true);
}

foreach ($data AS $y => $cunlis) {
    $yJson = $rootPath . '/json/' . $y . '.json';
    if (!file_exists($yJson)) {
        foreach ($cunlis AS $cunliCode => $items) {
            krsort($cunlis[$cunliCode]);
        }
        file_put_contents($yJson, json_encode($cunlis, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    } else {
        $orig = json_decode(file_get_contents($yJson), true);
        foreach ($cunlis AS $cunliCode => $items) {
            if (!isset($orig[$cunliCode])) {
                $orig[$cunliCode] = array();
            }
            foreach ($items AS $date => $item) {
                if (!isset($orig[$cunliCode][$date])) {
                    $orig[$cunliCode][$date] = $item;
                }
            }
        }
        foreach ($orig AS $cunliCode => $items) {
            krsort($orig[$cunliCode]);
        }
        file_put_contents($yJson, json_encode($orig, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }
}
