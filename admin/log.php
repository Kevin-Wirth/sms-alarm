<?php
require '../config.inc.php';
require '../dbconnect.php';
?>
<?xml version="1.0" ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Logeinträge SMS-Alarm</title>
</head>
<body>
<h2>Logeinträge SMS-Alarm</h2>
<?php
$result = mysql_query('SELECT * FROM `log` GROUP BY `eid`');
while($log = mysql_fetch_assoc($result)) {
	echo '<p>-x- '.date('d.m.Y, H:i:s', $log['time']).' - '.$log['value'];
	$result2 = mysql_query('SELECT * FROM `log` WHERE `eid` = '.$log['eid'].' LIMIT 1,100');
	while($log2 = mysql_fetch_assoc($result2)) {
		echo '<br /> -xxx- '.date('d.m.Y, H:i:s', $log2['time']).' - '.$log2['value'];
		if($log2['error'] == 1) echo ' <b><span style="color:red">ERROR !!!</span></b>';
	}
	echo '</p>';
}
?>
</body>
</html>