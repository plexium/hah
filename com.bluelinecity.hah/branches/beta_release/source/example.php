<?php

define('HAH_CACHE', '../cache/');
//define('HAH_DEBUG', true);

include ('hah.php');

$test = new HahDocument( 'example.hah' );

echo $test;