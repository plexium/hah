<?php

include ('../source/muffin/core/hah.php');

$test = new HahDocument( 'test.hah' );
$test->set('color', 'fff');
$test->set('passval', 'pass');

echo $test;