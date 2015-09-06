<?php

$fh = fopen(__DIR__ . '/Dengue_Daily.csv', 'r');
$data = array();
fgetcsv($fh, 2048);
while ($line = fgetcsv($fh, 2048)) {
    foreach ($line AS $k => $v) {
        $line[$k] = str_replace(array('　', ' '), '', $v);
    }
    $dayParts = explode('/', $line[2]);
    if (!isset($data[$dayParts[0]])) {
        $data[$dayParts[0]] = array();
    }
    if (!isset($data['total'])) {
        $data['total'] = array();
    }
    if (!isset($data[$dayParts[0]][$line[4]])) {
        $data[$dayParts[0]][$line[4]] = intval($line[13]);
    } else {
        $data[$dayParts[0]][$line[4]] += intval($line[13]);
    }

    if (!isset($data['total'][$line[4]])) {
        $data['total'][$line[4]] = intval($line[13]);
    } else {
        $data['total'][$line[4]] += intval($line[13]);
    }
}
fclose($fh);

$base = array(
    '70+' => 0,
    '65-69' => 0,
    '60-64' => 0,
    '55-59' => 0,
    '50-54' => 0,
    '45-49' => 0,
    '40-44' => 0,
    '35-39' => 0,
    '30-34' => 0,
    '25-29' => 0,
    '20-24' => 0,
    '15-19' => 0,
    '10-14' => 0,
    '5-9' => 0,
    4 => 0,
    3 => 0,
    2 => 0,
    1 => 0,
    0 => 0,
);

$cityPopulation = array();
$fh = fopen(__DIR__ . '/data10407M030.csv', 'r');
fgetcsv($fh, 4096);
while ($line = fgetcsv($fh, 4096)) {
    $city = mb_substr($line[1], 0, 3, 'utf-8');
    if (!isset($cityPopulation[$city])) {
        $cityPopulation[$city] = $base;
    }
    foreach ($line AS $k => $v) {
        if ($k > 146) {
            $cityPopulation[$city]['70+'] += intval($v);
        } elseif ($k > 136) {
            $cityPopulation[$city]['65-69'] += intval($v);
        } elseif ($k > 126) {
            $cityPopulation[$city]['60-64'] += intval($v);
        } elseif ($k > 116) {
            $cityPopulation[$city]['55-59'] += intval($v);
        } elseif ($k > 106) {
            $cityPopulation[$city]['50-54'] += intval($v);
        } elseif ($k > 96) {
            $cityPopulation[$city]['45-49'] += intval($v);
        } elseif ($k > 86) {
            $cityPopulation[$city]['40-44'] += intval($v);
        } elseif ($k > 76) {
            $cityPopulation[$city]['35-39'] += intval($v);
        } elseif ($k > 66) {
            $cityPopulation[$city]['30-34'] += intval($v);
        } elseif ($k > 56) {
            $cityPopulation[$city]['25-29'] += intval($v);
        } elseif ($k > 46) {
            $cityPopulation[$city]['20-24'] += intval($v);
        } elseif ($k > 36) {
            $cityPopulation[$city]['15-19'] += intval($v);
        } elseif ($k > 26) {
            $cityPopulation[$city]['10-14'] += intval($v);
        } elseif ($k > 16) {
            $cityPopulation[$city]['5-9'] += intval($v);
        } elseif ($k > 14) {
            $cityPopulation[$city]['4'] += intval($v);
        } elseif ($k > 12) {
            $cityPopulation[$city]['3'] += intval($v);
        } elseif ($k > 10) {
            $cityPopulation[$city]['2'] += intval($v);
        } elseif ($k > 8) {
            $cityPopulation[$city]['1'] += intval($v);
        } elseif ($k > 6) {
            $cityPopulation[$city]['0'] += intval($v);
        }
    }
}

$cityCase = array();
$fh = fopen(__DIR__ . '/Dengue_Daily.csv', 'r');
fgetcsv($fh, 2048);
while ($line = fgetcsv($fh, 2048)) {
    if (substr($line[2], 0, 4) === '2015') {
        switch ($line[5]) {
            case '台北市':
                $line[5] = '臺北市';
                break;
            case '台南市':
                $line[5] = '臺南市';
                break;
            case '台中市':
                $line[5] = '臺中市';
                break;
            case '台東縣':
                $line[5] = '臺東縣';
                break;
        }
        if (!isset($cityCase[$line[5]])) {
            $cityCase[$line[5]] = $base;
        }
        $cityCase[$line[5]][$line[4]] += intval($line[13]);
    }
}
fclose($fh);

$fh = fopen(__DIR__ . '/city_age_rate_2015.csv', 'w');
fputcsv($fh, array(
    '縣市', '年齡', '人口數', '病例數', '比率'
));
foreach ($cityCase AS $city => $cases) {
    foreach ($cases AS $age => $num) {
        if ($num > 0) {
            fputcsv($fh, array(
                $city, $age, $cityPopulation[$city][$age], $num, ($num / $cityPopulation[$city][$age])
            ));
        }
    }
}
fclose($fh);

$fh = fopen(__DIR__ . '/age_sum.csv', 'w');
fputcsv($fh, array_merge(array('年度'), array_keys($base)));

foreach ($data AS $y => $levels) {
    if ($y === 'total')
        continue;
    $new = $base;
    foreach ($levels AS $level => $count) {
        if (isset($new[$level])) {
            $new[$level] = $count;
        }
    }
    fputcsv($fh, array_merge(array($y), $new));
}