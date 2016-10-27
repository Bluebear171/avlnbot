<?php

if( isset($_GET["value"])) {
    $f = fopen("connc.txt", "w") or die("Unable to open file!");
    fwrite($f, $_GET["value"]);
    fclose($f);
    echo "success";
}
else {
    echo "please put the value";
}