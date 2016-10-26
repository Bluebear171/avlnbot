<?php

require 'vendor/predis/predis/autoload.php';

\Predis\Autoloader::register();

//Connecting to Redis server on localhost
$redis = new Predis\Client(getenv('REDIS_URL'));
//$redis->connect('redis://h:pf93dvkuhnprvr9pbad8k3j2206@ec2-23-23-218-119.compute-1.amazonaws.com',
//    23399);
echo "Connection to server sucessfully<br/>";
//check whether server is running or not
echo "Server is running: ".$redis->ping() ."<br />";

$connfile = fopen("connc.txt", "r") or die("Unable to open file!");
$connFlag = fread($connfile,1); // read 1 byte
fclose($connfile);
echo "Connc.txt : ". $connFlag ."<br />";

//****************************************************************
// SET ARRAY KEY
//$myarr = array();
//$myarr["aaa"] = array(1,2,3,4,5);
//$myarr["b"] = array(
//    array(6,7,8),
//    array(9,10,11)
//);
//
//echo "<br/>";
//print_r($myarr);
//
//$redis->set('foo', json_encode($myarr));
//
//$afterarr = (array)json_decode($redis->get('foo'));
//echo "<br/>";
//print_r($afterarr);

//****************************************************************
// CHECK EXISTS
//$exists = $redis->exists('foo');
//echo "<br />". $exists;


//****************************************************************
// INCREMENT DECREMENT
// set key
//$redis->set('foo5', 1);
//$redis->incr('foo5');
//echo "<br />". $redis->get('foo5');


//****************************************************************
// OBJECT HMSET HMGET
//$redis->hmset('obj', array(
//    "a"=>100,
//    "b"=>200
//));
//$obj = $redis->hmget('obj', array("b"));
//print_r($obj);


//****************************************************************
// LIST
//$redis->lpush('objlist', array(
//    100,200));
//$redis->lpush('objlist', array(300));
//$redis->lpush('objlist', array(400));
//$objlistsize = $redis->llen('objlist');
//echo "objlist size <br />". $objlistsize;
//
//$popvalue = $redis->lpop('objlist');
//echo "<br /> pop ". $popvalue;

//****************************************************************
// SADD ==> seperti LIST tapi distinct
//$redis->sadd('objsadd', array(
//    100,200));
//$redis->sadd('objsadd', array(300));
//$redis->sadd('objsadd', array(400));
//
//$saddmembers = $redis->smembers('objsadd');
//print_r($saddmembers);
//
//$isMember= $redis->sismember('objsadd', 300);
//echo "<br /> isMember ". $isMember ."<br />"; // true
//
//$numRemoved= $redis->srem('objsadd', 300);
//$saddmembers = $redis->smembers('objsadd');
//print_r($saddmembers);

//****************************************************************
// SORTED SET
// ZADD ==> seperti set, tapi sorted


//****************************************************************
// SET SINGLE KEY
// set key
//$redis->set('foo', "test");
//$redis->set('foo2', "test2");
//$redis->set('foo3', "test3");

//****************************************************************
// GET ALL KEYS
$redisKeys = $redis->keys("*");
foreach ($redisKeys as $key) {
    echo $key . " - " . $redis->get($key). "<br />";
}
//echo "<br/>";
//print_r($a);

//****************************************************************
// DELETE KEY
//$redis->del("foo");
//echo "<br/>";
//print_r($a);


//****************************************************************
// DELETE ALL KEYS
//$redisKeys = $redis->keys("*");
//foreach ($redisKeys as $key) {
//    $redis->del($key);
//}
