<?php

$rootPath = dirname(__DIR__);
$now = date('Y-m-d H:i:s');

system("{$rootPath}/cdc/query.php");
system("{$rootPath}/cdc/points.php");
system("{$rootPath}/cdc/age_sum.php");
system("{$rootPath}/cdc/cunli_rate.php");

system("cd {$rootPath} && /usr/bin/git add -A");

system("cd {$rootPath} && /usr/bin/git commit --author 'auto commit <noreply@localhost>' -m 'auto update @ {$now}'");

system("cd {$rootPath} && /usr/bin/git push origin master");