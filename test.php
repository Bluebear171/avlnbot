<?php

$file = 'serverlog.txt';
// The new person to add to the file
$text = date("Y-m-d h:i:sa", strtotime("+5 hours"))."\n";
// Write the contents to the file,
// using the FILE_APPEND flag to append the content to the end of the file
// and the LOCK_EX flag to prevent anyone else writing to the file at the same time
file_put_contents($file, $text, FILE_APPEND | LOCK_EX);
