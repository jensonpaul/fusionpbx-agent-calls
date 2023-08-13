<?php

//set the include path
	$conf = glob("{/usr/local/etc,/etc}/fusionpbx/config.conf", GLOB_BRACE);
	set_include_path(parse_ini_file($conf[0])['document.root']);

//includes files
	require_once "resources/require.php";
	require_once "resources/check_auth.php";
	require_once "resources/paging.php";

//check permissions
	if (permission_exists('client_call_record_view')) {
		//access granted
	}
	else {
		echo "access denied";
		exit;
	}

//set 24hr or 12hr clock
	define('TIME_24HR', 1);

//get post or get variables from http
	if (count($_REQUEST) > 0) {
		$caller_id_name = $_REQUEST["caller_id_name"];
		$caller_id_number = $_REQUEST["caller_id_number"];
		$caller_destination = $_REQUEST["caller_destination"];
		$extension_uuid = $_REQUEST["extension_uuid"];
		$destination_number = $_REQUEST["destination_number"];
		$start_stamp_begin = $_REQUEST["start_stamp_begin"];
		$start_stamp_end = $_REQUEST["start_stamp_end"];
		$answer_stamp_begin = $_REQUEST["answer_stamp_begin"];
		$answer_stamp_end = $_REQUEST["answer_stamp_end"];
		$end_stamp_begin = $_REQUEST["end_stamp_begin"];
		$end_stamp_end = $_REQUEST["end_stamp_end"];
		$start_epoch = $_REQUEST["start_epoch"];
		$stop_epoch = $_REQUEST["stop_epoch"];
		$duration_min = $_REQUEST["duration_min"];
		$duration_max = $_REQUEST["duration_max"];
		$billsec = $_REQUEST["billsec"];
		$tta_min = $_REQUEST['tta_min'];
		$tta_max = $_REQUEST['tta_max'];
		$client_call_record_uuid = $_REQUEST["client_call_record_uuid"];
		$access_code = $_REQUEST["access_code"];
		$interpret_language = $_REQUEST["interpret_language"];
		$interpret_stamp_begin = $_REQUEST["interpret_stamp_begin"];
		$interpret_stamp_end = $_REQUEST["interpret_stamp_end"];
		$interpreter_id = $_REQUEST["interpreter_id"];
		$order_by = $_REQUEST["order_by"];
		$order = $_REQUEST["order"];
		if (is_array($_SESSION['ccr']['field'])) {
			foreach ($_SESSION['ccr']['field'] as $field) {
				$array = explode(",", $field);
				$field_name = end($array);
				if (isset($_REQUEST[$field_name])) {
					$$field_name = $_REQUEST[$field_name];
				}
			}
		}
	}

//get variables used to control the order
	$order_by = $_REQUEST["order_by"];
	$order = $_REQUEST["order"];

//validate the order
	switch ($order) {
		case 'asc':
			break;
		case 'desc':
			break;
		default:
			$order = '';
	}

//set the assigned extensions
	if (!permission_exists('client_call_record_domain') && is_array($_SESSION['user']['extension'])) {
		foreach ($_SESSION['user']['extension'] as $row) {
			if (is_uuid($row['extension_uuid'])) {
				$extension_uuids[] = $row['extension_uuid'];
			}
		}
	}

//set the param variable which is used with paging
	$param .= "&caller_id_name=".urlencode($caller_id_name);
	$param .= "&caller_id_number=".urlencode($caller_id_number);
	$param .= "&caller_destination=".urlencode($caller_destination);
	$param .= "&extension_uuid=".urlencode($extension_uuid);
	$param .= "&destination_number=".urlencode($destination_number);
	$param .= "&start_stamp_begin=".urlencode($start_stamp_begin);
	$param .= "&start_stamp_end=".urlencode($start_stamp_end);
	$param .= "&answer_stamp_begin=".urlencode($answer_stamp_begin);
	$param .= "&answer_stamp_end=".urlencode($answer_stamp_end);
	$param .= "&end_stamp_begin=".urlencode($end_stamp_begin);
	$param .= "&end_stamp_end=".urlencode($end_stamp_end);
	$param .= "&start_epoch=".urlencode($start_epoch);
	$param .= "&stop_epoch=".urlencode($stop_epoch);
	$param .= "&duration_min=".urlencode($duration_min);
	$param .= "&duration_max=".urlencode($duration_max);
	$param .= "&billsec=".urlencode($billsec);
	$param .= "&tta_min=".urlencode($tta_min);
	$param .= "&tta_max=".urlencode($tta_max);
	$param .= "&client_call_record_uuid=".urlencode($client_call_record_uuid);
	$param .= "&access_code=".urlencode($access_code);
	$param .= "&interpret_language=".urlencode($interpret_language);
	$param .= "&interpret_stamp_begin=".urlencode($interpret_stamp_begin);
	$param .= "&interpret_stamp_end=".urlencode($interpret_stamp_end);
	$param .= "&interpreter_id=".urlencode($interpreter_id);
	if (is_array($_SESSION['ccr']['field'])) {
		foreach ($_SESSION['ccr']['field'] as $field) {
			$array = explode(",", $field);
			$field_name = end($array);
			if (isset($$field_name)) {
				$param .= "&".$field_name."=".urlencode($$field_name);
			}
		}
	}
	if ($_GET['show'] == 'all' && permission_exists('client_call_record_all')) {
		$param .= "&show=all";
	}
	if (isset($order_by)) {
		$param .= "&order_by=".urlencode($order_by)."&order=".urlencode($order);
	}

//create the sql query to get the xml cdr records
	if (strlen($order_by) == 0) { $order_by  = "start_stamp"; }
	if (strlen($order) == 0) { $order  = "desc"; }

//set a default number of rows to show
	$num_rows = '0';

//count the records in the database
	/*
	if ($_SESSION['client_calls']['limit']['numeric'] == 0) {
		$sql = "select count(*) from v_xml_cdr ";
		$sql .= "where domain_uuid = :domain_uuid ";
		$sql .= ".$sql_where;
		$parameters['domain_uuid'] = $domain_uuid;
		$database = new database;
		$num_rows = $database->select($sql, $parameters, 'column');
		unset($sql, $parameters);
	}
	*/

//limit the number of results
	if ($_SESSION['client_calls']['limit']['numeric'] > 0) {
		$num_rows = $_SESSION['client_calls']['limit']['numeric'];
	}

//set the default paging
	$rows_per_page = $_SESSION['domain']['paging']['numeric'];

//prepare to page the results
	//$rows_per_page = ($_SESSION['domain']['paging']['numeric'] != '') ? $_SESSION['domain']['paging']['numeric'] : 50; //set on the page that includes this page
	if (is_numeric($_GET['page'])) { $page = $_GET['page']; }
	if (!isset($_GET['page'])) { $page = 0; $_GET['page'] = 0; }
	$offset = $rows_per_page * $page;

//set the time zone
	if (isset($_SESSION['domain']['time_zone']['name'])) {
		$time_zone = $_SESSION['domain']['time_zone']['name'];
	}
	else {
		$time_zone = date_default_timezone_get();
	}
	$parameters['time_zone'] = $time_zone;

//get the results from the db
	$sql = "select \n";
	$sql .= "c.domain_uuid, \n";
	$sql .= "c.sip_call_id, \n";
	$sql .= "e.extension, \n";
	$sql .= "c.start_stamp, \n";
	$sql .= "c.end_stamp, \n";
	$sql .= "to_char(timezone(:time_zone, f.start_timestamp), 'DD Mon YYYY') as start_date_formatted, \n";
	$sql .= "to_char(timezone(:time_zone, f.start_timestamp), 'HH12:MI:SS am') as start_time_formatted, \n";
	$sql .= "c.start_epoch, \n";
	$sql .= "c.duration, \n";
	$sql .= "c.billmsec, \n";
	$sql .= "c.xml_cdr_uuid, \n";
	$sql .= "case when length(c.caller_id_number) = 4 then c.destination_number else c.caller_id_name end as caller_id_name, \n";
	$sql .= "case when length(c.caller_id_number) = 4 then c.destination_number else c.caller_id_number end as caller_id_number, \n";
	$sql .= "c.caller_destination, \n";
	$sql .= "c.source_number, \n";
	$sql .= "case when length(c.caller_id_number) = 4 then c.caller_id_number else c.destination_number end as destination_number, \n";
	$sql .= "c.leg, \n";
	if (is_array($_SESSION['ccr']['field'])) {
		foreach ($_SESSION['ccr']['field'] as $field) {
			$array = explode(",", $field);
			$field_name = end($array);
			$sql .= $field_name.", \n";
		}
	}
	if (is_array($_SESSION['ccr']['export'])) {
		foreach ($_SESSION['ccr']['export'] as $field) {
			$sql .= $field.", \n";
		}
	}
	$sql .= "c.answer_stamp, \n";
	$sql .= "f.client_call_record_uuid, \n";
	if ($_REQUEST['export_format'] === "invoice") {
		$sql .= "f.interpreter_id, \n";
		$sql .= "g.account_number, \n";
		$sql .= "g.client_id, \n";
		$sql .= "g.agency, \n";
		$sql .= "g.division, \n";
		$sql .= "g.div_contact, \n";
		$sql .= "g.div_phone, \n";
		$sql .= "g.div_state, \n";
	}
	$sql .= "f.access_code, \n";
	$sql .= "f.cost_center_id, \n";
	$sql .= "f.employee_id_or_name, \n";
	$sql .= "f.interpret_language, \n";
	$sql .= "f.interpreter_id, \n";
	$sql .= "f.fusion_group_interpreter_id, \n";
	$sql .= "f.job_number, \n";
	$sql .= "f.start_timestamp, \n";
	$sql .= "f.start_timestamp as interpret_stamp, \n";
	$sql .= "EXTRACT(EPOCH FROM c.end_stamp - f.start_timestamp) as interpret_duration, \n";
	$sql .= "g.otpls1, \n";
	$sql .= "g.otpls2, \n";
	$sql .= "g.otpls3, \n";
	$sql .= "g.otpls4, \n";
	$sql .= "(c.answer_epoch - c.start_epoch) as tta ";
	if ($_REQUEST['show'] == "all" && permission_exists('client_call_record_all')) {
		$sql .= ", c.domain_name \n";
	}
	$sql .= "from v_xml_cdr as c \n";
	$sql .= "left join v_client_call_records as f on f.call_uuid = c.xml_cdr_uuid \n";
	$sql .= "left join v_clients as g on g.access_code = f.access_code \n";
	$sql .= "left join v_extensions as e on e.extension_uuid = c.extension_uuid \n";
	$sql .= "inner join v_domains as d on d.domain_uuid = c.domain_uuid \n";
	if ($_REQUEST['show'] == "all" && permission_exists('client_call_record_all')) {
		$sql .= "where true \n";
	}
	else {
		$sql .= "where c.domain_uuid = :domain_uuid \n";
		$parameters['domain_uuid'] = $domain_uuid;
	}
	$sql .= "and EXTRACT(EPOCH FROM c.end_stamp - f.start_timestamp) > 0 \n";
	$sql .= "and f.access_code != '' \n";
	if (strlen($access_code) > 0) {
		$mod_access_code = str_replace("*", "%", $access_code);
		if (strstr($mod_access_code, '%')) {
			$sql .= "and f.access_code like :access_code \n";
			$parameters['access_code'] = $mod_access_code;
		}
		else {
			$sql .= "and f.access_code = :access_code \n";
			$parameters['access_code'] = $mod_access_code;
		}
	}
	if (strlen($interpret_language) > 0) {
		$sql .= "and f.interpret_language = :interpret_language \n";
		$parameters['interpret_language'] = ucwords($interpret_language);
	}
	if (strlen($interpreter_id) > 0) {
		$mod_interpreter_id = str_replace("*", "%", $interpreter_id);
		if (strstr($mod_interpreter_id, '%')) {
			$sql .= "and f.interpreter_id like :interpreter_id \n";
			$parameters['interpreter_id'] = $mod_interpreter_id;
		}
		else {
			$sql .= "and f.interpreter_id = :interpreter_id \n";
			$parameters['interpreter_id'] = $mod_interpreter_id;
		}
	}
	if (!permission_exists('client_call_record_domain')) { //only show the user their calls
		if (is_array($extension_uuids) && @sizeof($extension_uuids)) {
			$sql .= "and (c.extension_uuid = '".implode("' or c.extension_uuid = '", $extension_uuids)."') \n";
		}
		else {
			$sql .= "and false \n";
		}
	}
	if (strlen($start_epoch) > 0 && strlen($stop_epoch) > 0) {
		$sql .= "and start_epoch between :start_epoch and :stop_epoch \n";
		$parameters['start_epoch'] = $start_epoch;
		$parameters['stop_epoch'] = $stop_epoch;
	}
	if (strlen($direction) > 0) {
		$sql .= "and direction = :direction \n";
		$parameters['direction'] = $direction;
	}
	if (strlen($caller_id_name) > 0) {
		$mod_caller_id_name = str_replace("*", "%", $caller_id_name);
		if (strstr($mod_caller_id_name, '%')) {
			$sql .= "and caller_id_name like :caller_id_name \n";
			$parameters['caller_id_name'] = $mod_caller_id_name;
		}
		else {
			$sql .= "and caller_id_name = :caller_id_name \n";
			$parameters['caller_id_name'] = $mod_caller_id_name;
		}
	}
	if (strlen($caller_id_number) > 0) {
		$mod_caller_id_number = str_replace("*", "%", $caller_id_number);
		$mod_caller_id_number = preg_replace("#[^\+0-9.%/]#", "", $mod_caller_id_number);
		if (strstr($mod_caller_id_number, '%')) {
			$sql .= "and caller_id_number like :caller_id_number \n";
			$parameters['caller_id_number'] = $mod_caller_id_number;
		}
		else {
			$sql .= "and caller_id_number = :caller_id_number \n";
			$parameters['caller_id_number'] = $mod_caller_id_number;
		}
	}

	if (strlen($extension_uuid) > 0 && is_uuid($extension_uuid)) {
		$sql .= "and e.extension_uuid = :extension_uuid \n";
		$parameters['extension_uuid'] = $extension_uuid;
	}
	if (strlen($caller_destination) > 0) {
		$mod_caller_destination = str_replace("*", "%", $caller_destination);
		$mod_caller_destination = preg_replace("#[^\+0-9.%/]#", "", $mod_caller_destination);
		if (strstr($mod_caller_destination, '%')) {
			$sql .= "and caller_destination like :caller_destination \n";
			$parameters['caller_destination'] = $mod_caller_destination;
		}
		else {
			$sql .= "and caller_destination = :caller_destination \n";
			$parameters['caller_destination'] = $mod_caller_destination;
		}
	}
	if (strlen($destination_number) > 0) {
		$mod_destination_number = str_replace("*", "%", $destination_number);
		$mod_destination_number = preg_replace("#[^\+0-9.%/]#", "", $mod_destination_number);
		if (strstr($mod_destination_number, '%')) {
			$sql .= "and destination_number like :destination_number \n";
			$parameters['destination_number'] = $mod_destination_number;
		}
		else {
			$sql .= "and destination_number = :destination_number \n";
			$parameters['destination_number'] = $mod_destination_number;
		}
	}
	if (is_array($_SESSION['ccr']['field'])) {
		foreach ($_SESSION['ccr']['field'] as $field) {
			$array = explode(",", $field);
			$field_name = end($array);
			if (isset($$field_name)) {
				$$field_name = $_REQUEST[$field_name];
				if (strlen($$field_name) > 0) {
					if (strstr($$field_name, '%')) {
						$sql .= "and $field_name like :".$field_name." \n";
						$parameters[$field_name] = $$field_name;
					}
					else {
						$sql .= "and $field_name = :".$field_name." \n";
						$parameters[$field_name] = $$field_name;
					}
				}
			}
		}
	}

	if (strlen($interpret_stamp_begin) > 0 && strlen($interpret_stamp_end) > 0) {
		$sql .= "and start_timestamp between :interpret_stamp_begin::timestamptz and :interpret_stamp_end::timestamptz \n";
		$parameters['interpret_stamp_begin'] = $interpret_stamp_begin.':00.000 '.$time_zone;
		$parameters['interpret_stamp_end'] = $interpret_stamp_end.':59.999 '.$time_zone;
	}
	else {
		if (strlen($interpret_stamp_begin) > 0) {
			$sql .= "and start_timestamp >= :interpret_stamp_begin \n";
			$parameters['interpret_stamp_begin'] = $interpret_stamp_begin.':00.000 '.$time_zone;
		}
		if (strlen($interpret_stamp_end) > 0) {
			$sql .= "and start_timestamp <= :interpret_stamp_end \n";
			$parameters['interpret_stamp_end'] = $interpret_stamp_end.':59.999 '.$time_zone;
		}
	}
	if (strlen($start_stamp_begin) > 0 && strlen($start_stamp_end) > 0) {
		$sql .= "and start_stamp between :start_stamp_begin::timestamptz and :start_stamp_end::timestamptz \n";
		$parameters['start_stamp_begin'] = $start_stamp_begin.':00.000 '.$time_zone;
		$parameters['start_stamp_end'] = $start_stamp_end.':59.999 '.$time_zone;
	}
	else {
		if (strlen($start_stamp_begin) > 0) {
			$sql .= "and start_stamp >= :start_stamp_begin \n";
			$parameters['start_stamp_begin'] = $start_stamp_begin.':00.000 '.$time_zone;
		}
		if (strlen($start_stamp_end) > 0) {
			$sql .= "and start_stamp <= :start_stamp_end \n";
			$parameters['start_stamp_end'] = $start_stamp_end.':59.999 '.$time_zone;
		}
	}
	if (strlen($answer_stamp_begin) > 0 && strlen($answer_stamp_end) > 0) {
		$sql .= "and answer_stamp between :answer_stamp_begin::timestamptz and :answer_stamp_end::timestamptz \n";
		$parameters['answer_stamp_begin'] = $answer_stamp_begin.':00.000 '.$time_zone;
		$parameters['answer_stamp_end'] = $answer_stamp_end.':59.999 '.$time_zone;
	}
	else {
		if (strlen($answer_stamp_begin) > 0) {
			$sql .= "and answer_stamp >= :answer_stamp_begin \n";
			$parameters['answer_stamp_begin'] = $answer_stamp_begin.':00.000 '.$time_zone;;
		}
		if (strlen($answer_stamp_end) > 0) {
			$sql .= "and answer_stamp <= :answer_stamp_end \n";
			$parameters['answer_stamp_end'] = $answer_stamp_end.':59.999 '.$time_zone;
		}
	}
	if (strlen($end_stamp_begin) > 0 && strlen($end_stamp_end) > 0) {
		$sql .= "and end_stamp between :end_stamp_begin::timestamptz and :end_stamp_end::timestamptz \n";
		$parameters['end_stamp_begin'] = $end_stamp_begin.':00.000 '.$time_zone;
		$parameters['end_stamp_end'] = $end_stamp_end.':59.999 '.$time_zone;
	}
	else {
		if (strlen($end_stamp_begin) > 0) {
			$sql .= "and end_stamp >= :end_stamp_begin \n";
			$parameters['end_stamp_begin'] = $end_stamp_begin.':00.000 '.$time_zone;
		}
		if (strlen($end_stamp_end) > 0) {
			$sql .= "and end_stamp <= :end_stamp_end \n";
			$parameters['end_stamp'] = $end_stamp_end.':59.999 '.$time_zone;
		}
	}
	if (is_numeric($duration_min)) {
		$sql .= "and duration >= :duration_min \n";
		$parameters['duration_min'] = $duration_min;
	}
	if (is_numeric($duration_max)) {
		$sql .= "and duration <= :duration_max \n";
		$parameters['duration_max'] = $duration_max;
	}
	if (strlen($billsec) > 0) {
		$sql .= "and billsec like :billsec \n";
		$parameters['billsec'] = '%'.$billsec.'%';
	}

	if (strlen($client_call_record_uuid) > 0) {
		$sql .= "and client_call_record_uuid = :client_call_record_uuid \n";
		$parameters['client_call_record_uuid'] = $client_call_record_uuid;
	}
	if (is_numeric($tta_min)) {
		$sql .= "and (c.answer_epoch - c.start_epoch) >= :tta_min \n";
		$parameters['tta_min'] = $tta_min;
	}
	if (is_numeric($tta_max)) {
		$sql .= "and (c.answer_epoch - c.start_epoch) <= :tta_max \n";
		$parameters['tta_max'] = $tta_max;
	}
	//end where
	if ($_REQUEST['export_format'] == "invoice") {
		$sql .= " order by f.start_timestamp asc \n";
	} elseif (strlen($order_by) > 0) {
		$sql .= order_by($order_by, $order);
	}
	if ($_REQUEST['export_format'] !== "csv" && $_REQUEST['export_format'] !== "pdf" && $_REQUEST['export_format'] !== "invoice") {
		if ($rows_per_page == 0) {
			$sql .= " limit :limit offset 0 \n";
			$parameters['limit'] = $_SESSION['client_calls']['limit']['numeric'];
		}
		else {
			$sql .= " limit :limit offset :offset \n";
			$parameters['limit'] = $rows_per_page;
			$parameters['offset'] = $offset;
		}
	}
	$sql = str_replace("  ", " ", $sql);
	$database = new database;
	if ($archive_request && $_SESSION['client_calls']['archive_database']['boolean'] == 'true') {
		$database->driver = $_SESSION['client_calls']['archive_database_driver']['text'];
		$database->host = $_SESSION['client_calls']['archive_database_host']['text'];
		$database->type = $_SESSION['client_calls']['archive_database_type']['text'];
		$database->port = $_SESSION['client_calls']['archive_database_port']['text'];
		$database->db_name = $_SESSION['client_calls']['archive_database_name']['text'];
		$database->username = $_SESSION['client_calls']['archive_database_username']['text'];
		$database->password = $_SESSION['client_calls']['archive_database_password']['text'];
	}
	$result = $database->select($sql, $parameters, 'all');
	$result_count = is_array($result) ? sizeof($result) : 0;
	unset($database, $sql, $parameters);

//return the paging
	if ($_REQUEST['export_format'] !== "csv" && $_REQUEST['export_format'] !== "pdf" && $_REQUEST['export_format'] !== "invoice") {
		list($paging_controls_mini, $rows_per_page) = paging($num_rows, $param, $rows_per_page, true, $result_count); //top
		list($paging_controls, $rows_per_page) = paging($num_rows, $param, $rows_per_page, false, $result_count); //bottom
	}

?>
