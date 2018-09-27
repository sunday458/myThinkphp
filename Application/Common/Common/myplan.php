<?php  
    
    //echo date("Y-m-d H:i:s")."执行定时任务！" . "\r\n<br>";
    $content = date("Y-m-d H:i:s");
	$file1 = './newfile.txt';
	$fp = fopen($file1, 'w');
	fwrite($fp, $content);
	fclose($fp);