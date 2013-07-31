<?php
require 'config.inc.php';
require 'dbconnect.php';
require 'class.prowl.php';
require 'class.sms.php';

// Startwerte definieren
if($operationsart == 'konsole') {
	$key = $argv[1];
	if($argv[2] == 'zvei') {
		$typ = 'zvei';
		$fms= $argv[3];
		$status = $argv[4];
	} elseif($argv[2] == 'fms') {
		$typ = 'fms';
		$zvei = $argv[3];
		$weckton = $argv[4];
	} else {
		die('Argumente fehlen!');
	}
} elseif($operationsart == 'http') {
	$key = $_GET['key'];
	if($_GET['typ'] == 'zvei') {
		$typ = 'zvei';
		$zvei = $_GET['zvei'];
		$weckton = $_GET['weckton'];
	} elseif($_GET['typ'] == 'fms') {
		$typ = 'fms';
		$fms = $_GET['fms'];
		$status = $_GET['status'];
	} else {
		die('Argumente fehlen!');
	}
}

// Pr�fung Sicherheitskey
if($key != $security_key) die ("Sicherheitskey falsch!");

// ZVEI
if($typ == 'zvei') {

	// Sonderbehandlung LZ Morsbach
	if($zvei == 73591 or $zvei == 73592) {

		// Pr�fen ob letzte Alarmierung l�nger als 60 sekunden her
		$locktime = time()-60;
		$result = mysql_query('SELECT `eid` FROM `event-db` WHERE `zvei` = \'77777\' AND `time` > \''.$locktime.'\'');
		if (mysql_fetch_assoc($result) == FALSE) {

			$o_zvei = $zvei;
			$zvei = 77777;
			
			// Alarm in DB schreiben und ID auslesen
			mysql_query('INSERT INTO `event-db` (`eid`, `time`, `zvei`, `weckruf`) VALUES (NULL, \''.time().'\', \'77777\', \'0\')');
			$eid = mysql_insert_id();

			// Beginn log Eintrag
			$log = 'INSERT INTO `log` (`lid` ,`eid` ,`time` ,`value` ,`error`) VALUES (NULL ,\''.$eid.'\' ,\''.time().'\' ,\'Neue Sammelalarmierung f�r den LZ Morsbach gestartet.\' ,0)';

			// Warten
			sleep(15);
			
			$gs = FALSE;
			$ks = FALSE;
			$sirene = FALSE;
			if($o_zvei == 73591) {
				$ks = TRUE;
				$locktime = time()-90;
				$result = mysql_query('SELECT `eid` FROM `event-db` WHERE `zvei` = \'73592\' AND `time` > \''.$locktime.'\'');
				if (mysql_fetch_assoc($result) != FALSE) $gs = TRUE;
			}
			if($o_zvei == 73592) {
				$gs = TRUE;
				$locktime = time()-90;
				$result = mysql_query('SELECT `eid` FROM `event-db` WHERE `zvei` = \'73591\' AND `time` > \''.$locktime.'\'');
				if (mysql_fetch_assoc($result) != FALSE) $ks = TRUE;
			}
			$result = mysql_query('SELECT `eid` FROM `event-db` WHERE `zvei` = \'73590\' AND `weckruf` = \'2\' AND `time` > \''.$locktime.'\'');
			if (mysql_fetch_assoc($result) != FALSE) $sirene = TRUE;

			// Alarmierungsparameter erstellen
			if($sirene == TRUE) {
				$alarm_event = 'SIRENENALARM'; 
			} else {
				$alarm_event = 'MELDERALARM';
			}
			$alarm_text = 'LZ Morsbach - ';
			if($ks == TRUE) $alarm_text = $alarm_text.'kleine Schleife';
			if($ks == TRUE AND $gs == TRUE) $alarm_text = $alarm_text.' - ';
			if($gs == TRUE) $alarm_text = $alarm_text.'grosse Schleife';
			$alarm_text = $alarm_text.' - Alarmierungszeit: '.date('d.m.Y, H:i:s');
			$log = $log.', (NULL ,\''.$eid.'\' ,\''.time().'\' ,\'Text des Sammelalarms: '.$alarm_event.' - '.$alarm_text.'\', 0)';
		}
	}

	if($zvei != 77777) {

		// Alarm in DB schreiben, Pr�fen ob letzte Alarmierung l�nger als 60 sekunden her und ID auslesen
		$locktime = time()-60;
		$result = mysql_query('SELECT `eid` FROM `event-db` WHERE `zvei` = \''.$zvei.'\' AND `time` > \''.$locktime.'\'');
		mysql_query('INSERT INTO `event-db` (`eid`, `time`, `zvei`, `weckruf`) VALUES (NULL, \''.time().'\', \''.$zvei.'\', \''.$weckton.'\')');
		$eid = mysql_insert_id();
		if (mysql_fetch_assoc($result) != FALSE) die ("Zeitsperre");

		// Pr�fen ob ZVEI vorhanden; FALSE->DIE; TRUE->Daten in Array
		$result = mysql_query('SELECT `zvei`, `value` FROM `zvei-db` WHERE `zvei` = \''.$zvei.'\'');
		$mapping = mysql_fetch_assoc($result);
		if($mapping == FALSE) 	die ("ZVEI nicht bekannt!");
		
		// Warten
		sleep(5);

		// Beginn log Eintrag
		$log = 'INSERT INTO `log` (`lid` ,`eid` ,`time` ,`value` ,`error`) VALUES (NULL ,\''.$eid.'\' ,\''.time().'\' ,\'Neue Alarmierung gestartet ('.$zvei.'/'.$mapping['value'].')\' ,0)';

		// Alarmierungsparameter erstellen
		$sirene = FALSE;
		$result = mysql_query('SELECT `eid` FROM `event-db` WHERE `zvei` = \''.$zvei.'\' AND `weckruf` = \'2\' AND `time` > \''.$locktime.'\'');
		if (mysql_fetch_assoc($result) != FALSE) $sirene = TRUE;
		if($sirene == TRUE) {
			$alarm_event = 'SIRENENALARM'; 
		} else {
			$alarm_event = 'MELDERALARM';
		}
		$alarm_text = $mapping['value'].' - Alarmierungszeit: '.date('d.m.Y, H:i:s');
		$log = $log.', (NULL ,\''.$eid.'\' ,\''.time().'\' ,\'Alarmtext: '.$alarm_event.' - '.$alarm_text.'\', 0)';
	}

	// Alarmierungsempf�nger auslesen + versenden
	$result = mysql_query('SELECT `address`.`aid` AS `aid`, `user`.`uid` AS `uid`, `mid`, `type`, `value`, `username`, `zvei`, `prio` FROM `mapping` INNER JOIN `address` ON `address`.`aid` = `mapping`.`aid` INNER JOIN `user` ON `user`.`uid` = `address`.`uid` WHERE `zvei` = \''.$zvei.'\'');
	while($alarm = mysql_fetch_assoc($result)) {
		if($alarm['type'] == 'prowl') {
			$prowl_error = null;

			try {
				$config = array(
					'apiKey' => $alarm['value'], // provide an API key to test
					'apiProviderKey' => $prowl_providerKey,
				);
				$prowl = new Prowl($config);

				$notification = array(
					'application' => $prowl_application,
					'event' => $alarm_event,
					'description' => $alarm_text,
					'url' => $prowl_url,
					'priority'  => $alarm['prio']
				);

				$message = $prowl->add($notification);	
				
			} catch (Exception $message) {
				$prowl_error = 'Fehler: ' . $message->getMessage();
			}

			if ($prowl_error != null) {
				$log = $log.', (NULL ,\''.$eid.'\' ,\''.time().'\' ,\'User: '.$alarm['username'].' (uid: '.$alarm['uid'].', aid: '.$alarm['aid'].', mid: '.$alarm['mid'].'); '.$prowl_error.'\', 1)';
			} else {
				$log = $log.', (NULL ,\''.$eid.'\' ,\''.time().'\' ,\'User: '.$alarm['username'].' (uid: '.$alarm['uid'].', aid: '.$alarm['aid'].', mid: '.$alarm['mid'].'); Alarmierung wurde erfolgreich verschickt!\', 0)';
			}
		} elseif($alarm['type'] == 'sms') {
			$url = 'http://sms77.de/gateway/' .
				'?u=' . urlencode($sms_u) .
				'&p=' . urlencode($sms_p) .
				'&to=' . urlencode($alarm['value']) .
				'&text=' . urlencode($alarm_event.': '.$alarm_text) .
				'&type=' . urlencode($sms_type) .
				'&from=' . urlencode($sms_from);

			$ret = @file($url);

			if ($ret[0] == '100') {
				$log = $log.', (NULL ,\''.$eid.'\' ,\''.time().'\' ,\'User: '.$alarm['username'].' (uid: '.$alarm['uid'].', aid: '.$alarm['aid'].', mid: '.$alarm['mid'].'); SMS-Alarmierung wurde erfolgreich verschickt!\', 0)';
			} else {
				$log = $log.', (NULL ,\''.$eid.'\' ,\''.time().'\' ,\'User: '.$alarm['username'].' (uid: '.$alarm['uid'].', aid: '.$alarm['aid'].', mid: '.$alarm['mid'].'); Fehler beim SMS-Versand! Fehlercode: '.$ret[0].' ('.$sms_err_code[$ret[0]].')\', 1)';
			}
		} elseif($alarm['type'] == 'aapp') {
			unset($aapp_ret);
			exec($aapp_pfad . 'AlarmPushTool.exe -c '.$zvei.' -t "'.$alarm_event.'" -m "'.$alarm_text.'" -p '.$aapp_pfad, $aapp_ret);

			$aapp_log = 'AlarmApp R�ckmeldung: ';
			foreach ($aapp_ret as $value) {
				$aapp_log = $aapp_log.$value;
			}
			unset($value);

			$log = $log.', (NULL ,\''.$eid.'\' ,\''.time().'\' ,\''.$aapp_log.'\', 0)';
		}
	}

	// log Eintr�ge speichern
	mysql_query($log.', (NULL ,\''.$eid.'\' ,\''.time().'\' ,\'Alarmverarbeitung beendet.\' ,0)');
	
// FMS
} else {

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

}

?>