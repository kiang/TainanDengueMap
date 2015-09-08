<?php

$rootPath = dirname(__DIR__);

exec("/usr/local/bin/topojson -o {$rootPath}/taiwan/cunli.json {$rootPath}/geojson/cunli.json -p");