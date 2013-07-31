<?php
require "config.inc.php";

mysql_connect($db_host,$db_user,$db_pass) or die ("Keine Verbindung moeglich");
mysql_select_db($db_table) or die ("Die Datenbank existiert nicht.");
?>