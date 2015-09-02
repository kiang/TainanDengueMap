<?php

$cunliCodes = $code2name = array();
$fh = fopen(dirname(__DIR__) . '/data.tainan/cunli_code.csv', 'r');
while ($line = fgetcsv($fh, 2048)) {
    $cunliCodes[$line[1] . $line[3] . $line[5]] = $line[4];
    $code2name[$line[4]] = $line[1] . $line[3] . $line[5];
}
fclose($fh);

$fh = fopen(__DIR__ . '/data10407M030.csv', 'r');
fgetcsv($fh, 2048);
fgetcsv($fh, 2048);
$missingCodes = array(
    'd5d8c6d8313cd41a3f5fde49e132a521' => '6500300-017',
    '893e1822d62465332ddd80a3caf706e4' => '6500300-054',
    '6b0597669dec9030092445561526d986' => '6500700-007',
    '64df88d51f4d14d1d89beae21485d579' => '6502000-004',
    '7b6e5fc633a95618746304a997bcb177' => '6300200-019',
    '61b281277c96e843b86dd2436fb23f46' => '6300700-015',
    'b11482605c7e64981900235283974c8e' => '6602200-004',
    'e9d40edd76cb2f41dbe675e6b5df4a64' => '6701400-004',
    '4cdefb28b8e1f4e6a59960b68c068283' => '6701800-018',
    'e8f6ad54c5f64578f55dc2e4e2ec15b5' => '6703000-008',
    'a949fe1e88d517b5a2e6be24084df4b7' => '6703500-003',
    '87c37798b42c26757ec7aa6ef342d0ff' => '6703500-024',
    '7be6d727ba3db854eba13aecb306c6eb' => '1000701-002',
    '0642a662ce7134b67288e51d4213e426' => '1000701-030',
    '4f6060d3e2822fab935784aee7ec8209' => '1000701-039',
    '33fd8af33a5d993874bcfeb15d238fc7' => '1000701-059',
    'b946d59162ed8f108cb47f27cfc14d09' => '1000714-003',
    'b826dd75367b17abb96d05ae16d601f0' => '1000714-010',
    '0118542758a39f4e9c3494790fc98988' => '1000723-013',
    '961eb29485811f893dfaed3f9942eb6a' => '1000804-005',
    '4e30b4625134b4cc7d170e7da49489b4' => '1000806-012',
    '7bf99f910aeedc312a8d48074aac798c' => '1000913-003',
    'f6b15fba2f94b3417f2bf22b6f52e74d' => '1000916-001',
    '881c5197b5c1574680eda7d1ae373d07' => '1000917-018',
    '472773b7eb5ec8cfb26b31ce109ef4ea' => '1000918-016',
    '6ab920899610be7da348a257ee900abc' => '1000918-021',
    '7c3a8625aeb6f54401f2bc2634ff34c1' => '1000919-014',
    'd67204f6134d16c6cd583f6cf8ac17c8' => '1000920-021',
    '4d54c76fc4b64251ec03a6169885c488' => '1001013-002',
    'f2508f31459330a9e7a8faea07671d07' => '1001013-014',
    'fa356532b1fec387a4b91673c0d8b440' => '1001303-017',
    '47ccf5f8f825f63a08840dcfcf605aa2' => '1001309-012',
    '3dfb59afb081138df92279c72540ff89' => '1001317-001',
    '23a4e433f40bbe8cddccfe1ae52f1f42' => '1001327-001',
    '95835870a2ae33a2b487049be821f722' => '1001601-031',
    'ed64c6e95ce6dd1507d48ccecd12eab4' => '1002002-018',
);

$dengue = json_decode(file_get_contents(dirname(__DIR__) . '/taiwan/Dengue.json'), true);
$cunliRateFh = fopen(__DIR__ . '/cunli_rate.csv', 'w');
fputcsv($cunliRateFh, array(
    '村里代碼',
    '村里',
    '人口數',
    '病例數',
    '發生率',
));

while ($line = fgetcsv($fh, 2048)) {
    $key = $line[1] . $line[2];
    $md5 = md5($key);
    if (!isset($cunliCodes[$key])) {
        $replacedKey = strtr($key, array(
            '臺北市' => '台北市',
            ' ' => '',
            '臺中市' => '台中市',
            '臺南市' => '台南市',
            '三民一' => '三民區',
            '三民二' => '三民區',
            '鳳山一' => '鳳山區',
            '鳳山二' => '鳳山區',
        ));
        if (!isset($cunliCodes[$replacedKey])) {
            if (!isset($missingCodes[$md5])) {
                echo $key . "\n";
                echo md5($key) . "\n";
                $prefix = mb_substr($key, 0, 6, 'utf-8');
                foreach ($cunliCodes AS $cunliKey => $code) {
                    if (false !== strpos($cunliKey, $prefix)) {
                        echo "{$cunliKey} => {$code}\n";
                    }
                }
                $prefix = mb_substr($replacedKey, 0, 6, 'utf-8');
                foreach ($cunliCodes AS $cunliKey => $code) {
                    if (false !== strpos($cunliKey, $prefix)) {
                        echo "{$cunliKey} => {$code}\n";
                    }
                }
                exit();
            } else {
                $cunliCode = $missingCodes[$md5];
            }
        } else {
            $cunliCode = $cunliCodes[$replacedKey];
        }
    } else {
        $cunliCode = $cunliCodes[$key];
    }
    if (isset($dengue[$cunliCode])) {
        $line[4] = intval($line[4]);
        $sum = 0;
        foreach ($dengue[$cunliCode] AS $day) {
            $sum += $day[1];
        }

        fputcsv($cunliRateFh, array(
            $cunliCode,
            $code2name[$cunliCode],
            $line[4],
            $sum,
            round($sum / $line[4], 6),
        ));
    }
}
fclose($fh);
