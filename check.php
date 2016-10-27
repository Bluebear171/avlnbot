<?php

$connfile = fopen("connc.txt", "r") or die("Unable to open file!");
$connFlag = fread($connfile,1); // read 1 byte
fclose($connfile);
echo "Connc.txt : ". $connFlag ."<br />";