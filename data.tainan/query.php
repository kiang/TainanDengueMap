<?php

$cunliCodes = array();
$fh = fopen(__DIR__ . '/cunli_code.csv', 'r');
while ($line = fgetcsv($fh, 2048)) {
    if ($line[1] === '台南市') {
        $cunliCodes[$line[3] . $line[5]] = $line[4];
    }
}
fclose($fh);

$stack = array();
$cnt = 0;


$fh = fopen(__DIR__ . '/0829.csv', 'r');
fgetcsv($fh, 2048);
while ($line = fgetcsv($fh, 2048)) {
    foreach ($line AS $k => $v) {
        $line[$k] = str_replace(array('　', ' '), '', mb_convert_encoding($v, 'utf-8', 'big5'));
    }
    if (!isset($cunliCodes[$line[2] . $line[3]])) {
        $stack[] = $line[0];
    }
    ++$cnt;
}

$fh = fopen(__DIR__ . '/1040826.csv', 'r');
fgetcsv($fh, 2048);
while ($line = fgetcsv($fh, 2048)) {
    foreach ($line AS $k => $v) {
        $line[$k] = str_replace(array('　', ' '), '', mb_convert_encoding($v, 'utf-8', 'big5'));
    }
    if (!isset($cunliCodes[$line[2] . $line[3]])) {
        $stack[] = $line[0];
    }
    ++$cnt;
}

sort($stack);

echo $cnt . "\n\n";

echo implode(', ', $stack);
