<?php

if( isset($_GET["value"])) {
    $f = fopen("f.txt", "w") or die("Unable to open file!");
    fwrite($f, $_GET["value"]);
    fclose($f);
    echo "success";
}
else {
    echo "Please put the value";
}