<?php

//define('HAH_CACHE', '../cache/');
//define('HAH_DEBUG', true);

include ('hah.php');

$test = new HahDocument( 'test.hah' );
$test->set('color', 'fff');
$test->set('harry', 'reed');
$test->set('passval', 'pass');
$test->set('padding', '10');
$test->set('parmvar', 'a;slkdkjf');
$test->set('outvar', 5);
$test->set('colorlist', array('red','blue','green','black','white'));
$test->set('hash', array('one' => 1, 'two' => 2, 'three' => 3));
$test->set('contacts', array(
   array('id' => 'id', 'name' => 'name', 'phone' => 'phone', 'age' => 'age', 'dob' => 'dob'),
   array('id' => 1, 'name' => 'Bob Smith', 'phone' => '810-555-5555', 'age' => 30, 'dob' => '1979-07-17'),
   array('id' => 2, 'name' => 'Rob Smith', 'phone' => null, 'age' => 12, 'dob' => '1999-07-17'),
   array('id' => 3, 'name' => 'Larry Smith', 'phone' => '810-555-1234', 'age' => 66, 'dob' => '1939-07-17'),
));

echo $test;