<?php
/**
 * Created by PhpStorm.
 * User: hendrysetiadi
 * Date: 05/10/2016
 * Time: 16:57
 */

ignore_user_abort(true);
set_time_limit(0);
require_once 'AvalonBot.php';
//define('BOT_TOKEN', <set this in httpd-xampp.conf>);
$bot = new AvalonBot(getenv('BOTTOKEN'), 'AvalonBotChat');

$bot->runLongpoll();

//
//$bot_token = "272750070:AAH86cH2Xx2n8r4zM4Z5B_LLCWawi2IzBqE";
//$website = "https://api.telegram.org/bot". $bot_token;
//
//$update = file_get_contents($website."/getupdates?timeout=2");
//// $update = file_get_contents("php://input");
//
//print_r ($update);
//echo "<br /><br />";
//
//$updateArray= json_decode($update, TRUE);
//
//$text = $updateArray["result"][0]["message"]["text"];
//
//print_r($text);
//
//$chatId = $updateArray["result"][0]["message"]["chat"]["id"];
//
//var_dump($keyboard = json_encode($keyboard = [
//    'keyboard' => [
//        ['Yes'],['No'],['Maybe'],
//        ['1'],['2'],['3'],
//    ] ,
//
//    'resize_keyboard' => true,
//    'one_time_keyboard' => true,
//    'selective' => true
//]),true);
//
//file_get_contents($website."/sendmessage?chat_id=".$chatId."&text=aaaa&reply_to_message_id=140");

//grup maen -154015457
// grup private 286457946
//file_get_contents($website."/sendmessage?chat_id=-154015457&text=".$update);





