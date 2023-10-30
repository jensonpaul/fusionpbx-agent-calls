<?php

//includes
//set the include path
	$conf = glob("{/usr/local/etc,/etc}/fusionpbx/config.conf", GLOB_BRACE);
	set_include_path(parse_ini_file($conf[0])['document.root']);

//includes files
	require_once "resources/require.php";
	require_once "resources/check_auth.php";

//check permissions
	if (permission_exists('agent_live_call_view')) {
		//access granted
	}
	else {
		echo "access denied";
		exit;
	}

//add multi-lingual support
	$language = new text;
	$text = $language->get(null,'app/agent_live_call');

//get the http values and set them to a variable
	if (is_uuid($_REQUEST["id"])) {
		$uuid = $_REQUEST["id"];
	}

// pre-define SESSION variables
	$_SESSION['switch']['log']['dir'] = '/var/log/freeswitch';

//get the cdr string from the database
	$sql = "select * from v_xml_cdr ";
	if (permission_exists('xml_cdr_all')) {
		$sql .= "where xml_cdr_uuid  = :xml_cdr_uuid ";
	}
	else {
		$sql .= "where xml_cdr_uuid  = :xml_cdr_uuid ";
		$sql .= "and domain_uuid = :domain_uuid ";
		$parameters['domain_uuid'] = $domain_uuid;
	}
	$parameters['xml_cdr_uuid'] = $uuid;
	$database = new database;
	$row = $database->select($sql, $parameters, 'row');
	if (is_array($row) && @sizeof($row) != 0) {
		$start_stamp = trim($row["start_stamp"]);
		$xml_string = trim($row["xml"]);
		$json_string = trim($row["json"]);
	}
	unset($sql, $parameters, $row);

//get the format
	if (strlen($xml_string) > 0) {
		$format = "xml";
	}
	if (strlen($json_string) > 0) {
		$format = "json";
	}

//get cdr from the file system
	if ($format != "xml" && $format != "json") {
		$tmp_time = strtotime($start_stamp);
		$tmp_year = date("Y", $tmp_time);
		$tmp_month = date("M", $tmp_time);
		$tmp_day = date("d", $tmp_time);
		$tmp_dir = $_SESSION['switch']['log']['dir'].'/xml_cdr/archive/'.$tmp_year.'/'.$tmp_month.'/'.$tmp_day;
		if (file_exists($tmp_dir.'/'.$uuid.'.json')) {
			$format = "json";
			$json_string = file_get_contents($tmp_dir.'/'.$uuid.'.json');
		}
		if (file_exists($tmp_dir.'/'.$uuid.'.xml')) {
			$format = "xml";
			$xml_string = file_get_contents($tmp_dir.'/'.$uuid.'.xml');
		}
	}

//parse the xml to get the call detail record info
	try {
		if ($format == 'json') {
			$array = json_decode($json_string,true);
			if (is_null($array)) {
				$j = stripslashes($json_string);
				$array = json_decode($j,true);
			}
		}
		if ($format == 'xml') {
			$array = json_decode(json_encode((array)simplexml_load_string($xml_string)),true);
		}
	}
	catch (Exception $e) {
		echo $e->getMessage();
	}

//detail summary
	//get the variables
		$call_info['xml_cdr_uuid'] = urldecode($array["variables"]["uuid"]);
		$call_info['direction'] = urldecode($array["variables"]["call_direction"]);
		$call_info['language'] = urldecode($array["variables"]["language"]);
		$call_info['start_epoch'] = urldecode($array["variables"]["start_epoch"]);
		$call_info['start_stamp'] = urldecode($array["variables"]["start_stamp"]);
		$call_info['start_uepoch'] = urldecode($array["variables"]["start_uepoch"]);
		$call_info['answer_stamp'] = urldecode($array["variables"]["answer_stamp"]);
		$call_info['answer_epoch'] = urldecode($array["variables"]["answer_epoch"]);
		$call_info['answer_uepoch'] = urldecode($array["variables"]["answer_uepoch"]);
		$call_info['end_epoch'] = urldecode($array["variables"]["end_epoch"]);
		$call_info['end_uepoch'] = urldecode($array["variables"]["end_uepoch"]);
		$call_info['end_stamp'] = urldecode($array["variables"]["end_stamp"]);
		$call_info['duration'] = urldecode($array["variables"]["duration"]);
		$call_info['mduration'] = urldecode($array["variables"]["mduration"]);
		$call_info['billsec'] = urldecode($array["variables"]["billsec"]);
		$call_info['billmsec'] = urldecode($array["variables"]["billmsec"]);
		$call_info['bridge_uuid'] = urldecode($array["variables"]["bridge_uuid"]);
		$call_info['read_codec'] = urldecode($array["variables"]["read_codec"]);
		$call_info['write_codec'] = urldecode($array["variables"]["write_codec"]);
		$call_info['remote_media_ip'] = urldecode($array["variables"]["remote_media_ip"]);
		$call_info['hangup_cause'] = urldecode($array["variables"]["hangup_cause"]);
		$call_info['hangup_cause_q850'] = urldecode($array["variables"]["hangup_cause_q850"]);

echo json_encode($call_info);

?>
