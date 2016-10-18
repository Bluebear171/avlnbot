<?php

require 'vendor/predis/predis/autoload.php';

\Predis\Autoloader::register();

//Connecting to Redis server on localhost
$redis = new Predis\Client('redis://h:pf93dvkuhnprvr9pbad8k3j2206@ec2-23-23-218-119.compute-1.amazonaws.com:23399');
//$redis->connect('redis://h:pf93dvkuhnprvr9pbad8k3j2206@ec2-23-23-218-119.compute-1.amazonaws.com',
//    23399);
echo "Connection to server sucessfully<br/>";
//check whether server is running or not
echo "Server is running: ".$redis->ping() ."<br />";

//$myarr = array();
//$myarr["aaa"] = array(1,2,3,4,5);
//$myarr["b"] = array(
//    array(6,7,8),
//    array(9,10,11)
//);
//
//echo "<br/>";
//print_r($myarr);

//$redis->set('foo', json_encode($myarr));


//$afterarr = (array)json_decode($redis->get('foo'));
//
//echo "<br/>";
//print_r($afterarr);

// Get all keys
//$a = $redis->keys("*");
//print_r($a);

//$redis->save();
