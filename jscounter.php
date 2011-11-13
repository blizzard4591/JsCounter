<?php

	require('Config.inc.php');

	require('class.MySQL.inc.php');
	
	if (array_key_exists('ajax', $_GET)) {
		//file_put_contents('debug.txt', "ajax called\r\n", FILE_APPEND);
		if (array_key_exists('sync', $_GET) && is_numeric($_GET['sync'])) {
			//file_put_contents('debug.txt', "sync called with ".$_GET['sync']."\r\n", FILE_APPEND);
			echo json_encode(Array('timestamp' => time(), 'sync' => $_GET['sync']));
			exit;
		}else if (array_key_exists('event', $_GET) && is_numeric($_GET['event'])) {
			$mysql = new mysql();
			$mysql->query('INSERT INTO `events` (`ip`, `timestamp`, `group`, `type`) VALUES (\''.mysql_real_escape_string($_SERVER['REMOTE_ADDR']).'\', \''.mysql_real_escape_string($_GET['event']).'\', \''.mysql_real_escape_string($_GET['gruppe']).'\', \''.mysql_real_escape_string($_GET['type']).'\');');
			//file_put_contents('debug.txt', 'INSERT INTO `events` (`ip`, `timestamp`, `group`, `type`) VALUES (\''.mysql_real_escape_string($_SERVER['REMOTE_ADDR']).'\', \''.mysql_real_escape_string($_GET['event']).'\', \''.mysql_real_escape_string($_GET['gruppe']).'\', \''.mysql_real_escape_string($_GET['type']).'\'));'."\r\n", FILE_APPEND);
			echo json_encode(Array('status' => 200, 'time' => $_GET['event']));
			exit;
		}
	}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
		<title>JS Picture test</title>

		<script language="JavaScript" type="text/javascript" src="jquery-1.7.js"></script>
		<script language="JavaScript" type="text/javascript" src="jscounter.js"></script>
		<script language="JavaScript" type="text/javascript">
			$(document).ready(function(){
				$("#submitButton").click(function(event){
					starteZaehlen();
					event.preventDefault();
				});

				writeToLog('#statusLog', "Welcome to JsCounter Demo Page");

				syncTime();
			});
		</script>
		<script src="./highcharts-2.1.9/js/highcharts.js" type="text/javascript"></script>
		<link type="text/css" rel="stylesheet" href="jscounter.css">
	</head>
<body>

<h2>JsCounter Library Demo Page</h2>

<div id="startSelection">
	<h3>Start, Dauer und Z&auml;hlgruppe</h3>
	<form id="startSelectionForm" name="startsel" action="">
		Dauer: <select name="dauer" id="selectionDauer">
			<option value="15">15min</option>
			<option value="30">30min</option>
			<option value="45">45min</option>
			<option value="60">60min</option>
			<option value="75">75min</option>
			<option value="90">90min</option>
		</select> <br>
		Name der Z&auml;hlgruppe: <input type="text" name="gruppe" id="inputGruppe" size="25"> <br>
		Startzeitpunkt: <span id="startpunkt"><i>noch nicht gestartet</i></span> <br>
		Graph anzeigen: <input type="checkbox" name="showGraph" value="showGraph" id="showGraphCheckbox"><br>
		<input id="submitButton" type="submit" value="Z&auml;hlvorgang starten">
	</form>
</div>

<div id="chartContainer" style="width: 100%; height: 400px"></div>

<div id="statusLog"> </div>
</body>
</html>