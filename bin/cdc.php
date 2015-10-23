<?php

$rootPath = dirname(__DIR__);
$now = date('Y-m-d H:i:s');

exec("cd {$rootPath} && /usr/bin/git pull");

exec("/usr/bin/php -q {$rootPath}/cdc/query.php");
exec("/usr/bin/php -q {$rootPath}/cdc/points.php");
exec("/usr/bin/php -q {$rootPath}/cdc/age_sum.php");
exec("/usr/bin/php -q {$rootPath}/cdc/cunli_rate.php");
exec("/usr/bin/php -q {$rootPath}/cdc/cunli_sum.php");

exec("/usr/bin/php -q {$rootPath}/chemical/query.php");
exec("/usr/bin/php -q {$rootPath}/scripts/auto_query.php");

exec("cd {$rootPath} && /usr/bin/git add -A");

exec("cd {$rootPath} && /usr/bin/git commit --author 'auto commit <noreply@localhost>' -m 'auto update @ {$now}'");

exec("cd {$rootPath} && /usr/bin/git push origin gh-pages");
