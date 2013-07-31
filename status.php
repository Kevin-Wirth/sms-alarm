<?php
require 'config.inc.php';
require 'dbconnect.php';
require 'class.prowl.php';
require 'class.sms.php';

// Startwerte definieren
if($operationsart == 'konsole') {
	$key = $argv[1];
	$fms= $argv[2];
	$status = $argv[3];
} elseif($operationsart == 'http') {
	$key = $_GET['key'];
	$fms = $_GET['fms'];
	$status = $_GET['status'];
}

// Prüfung Sicherheitskey
if($key != $security_key) die ("Sicherheitskey falsch!");

$fms_list = array(
'69692810'	=>	'Morsbach 1-KDOW',
'69692819'	=>	'Morsbach 1-MTF',
'69692831'	=>	'Morsbach 1-LF20',
'69692817'	=>	'Morsbach 1-HLF20',
'69692847'	=>	'Morsbach 1-LF16TS',
'69692802'	=>	'Morsbach 1-WLF',
'69692891'	=>	'Morsbach 1-GWG',
'69692919'	=>	'Morsbach 2-MTF',
'69692852'	=>	'Morsbach 2-TSF',
'69692940'	=>	'Morsbach 2-LF10',
'69692914'	=>	'Morsbach 2-RW1',
'69693019'	=>	'Morsbach 3-MTF',
'69693040'	=>	'Morsbach 3-LF10',
'69693119'	=>	'Morsbach 4-MTF',
'69693140'	=>	'Morsbach 4-HLF10'
);

if (array_key_exists($fms, $fms_list)) {
	mysql_query('INSERT INTO `fms` (`id`, `kennung`, `status`, `time`) VALUES (NULL, \''.$fms.'\', \''.$status.'\',\''.time().'\')');
	$status_id = mysql_insert_id();
	$locktime = time()-60;
	$result = mysql_query('SELECT `id` FROM `fms` WHERE `kennung` = \''.$fms.'\' AND `status` =\''.$status.'\' AND `time` > \''.$locktime.'\' ORDER BY `id` ASC LIMIT 0,1');
	$id_res = mysql_fetch_array($result);
	if ($id_res['id'] == $status_id) {
		mysql_query('INSERT INTO `fms` (`id`, `kennung`, `status`, `time`) VALUES (NULL, \''.$fms.'\', \''.$status.'\',\''.time().'\')');

			$config = array(
				'apiKey' => 'fe44f426f24997d7092553cc5d6c7b4a1f854c80',
				'apiProviderKey' => $prowl_providerKey,
			);
			$prowl = new Prowl($config);

			$notification = array(
				'application' => 'FMS-Info',
				'event' => $fms_list[$fms],
				'description' => 'Status: '.$status.' - '.date('d.m.Y, H:i:s'),
				'url' => $prowl_url,
				'priority'  => '-2'
			);

			$message = $prowl->add($notification);
	} else {
		die;
	}
} else {
	die;
}
?>