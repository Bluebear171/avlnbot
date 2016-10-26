<?php

$f = fopen("connc.txt", "w") or die("Unable to open file!");
fwrite($f,"1");
fclose($f);