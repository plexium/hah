<?php

//define('HAH_CACHE', '../cache/');
//define('HAH_DEBUG', true);

include ('hah.php');

$test = new HahDocument( 'test.hah' );
$test->set('color', 'fff');
$test->set('harry', 'reed');
$test->set('passval', 'pass');
$test->set('padding', '10');

echo $test;