<?php
$x = "abc";
$$x = 200;
echo $x . "<br/>";
echo $$x . "<br/>";
echo $abc;
$filename = ".\\README.md";
$handle = fopen($filename, "r");

$contents = fread($handle, filesize($filename)); //read file    

echo "<pre>$contents</pre>"; //printing data of file  
fclose($handle);//close file  
//更多请阅读：https://www.yiibai.com/php/php-read-file.html
