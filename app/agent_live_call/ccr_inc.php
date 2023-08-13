<?php

//set the include path
	$conf = glob("{/usr/local/etc,/etc}/fusionpbx/config.conf", GLOB_BRACE);
	set_include_path(parse_ini_file($conf[0])['document.root']);

//includes files
	require_once "resources/require.php";
	require_once "resources/check_auth.php";
	require_once "resources/paging.php";

//check permissions
	if (permission_exists('agent_live_call_view')) {
		//access granted
	}
	else {
		echo "access denied";
		exit;
	}

//set 24hr or 12hr clock
	define('TIME_24HR', 1);

//set the assigned extensions
	foreach ($_SESSION['user']['extension'] as $row) {
		if (is_uuid($row['extension_uuid'])) {
			$extension_uuids[] = $row['extension_uuid'];
		}
	}

//set a default number of rows to show
	$num_rows = '0';

//count the records in the database
	/*
	if ($_SESSION['client_calls']['limit']['numeric'] == 0) {
		$sql = "select count(*) from v_xml_cdr ";
		$sql .= "where domain_uuid = :domain_uuid ";
		if (is_array($extension_uuids) && @sizeof($extension_uuids)) {
			$sql .= "and (extension_uuid = '".implode("' or extension_uuid = '", $extension_uuids)."') \n";
		}
		$sql .= " and start_stamp::date = now()::date \n";
		$parameters['domain_uuid'] = $domain_uuid;
		$database = new database;
		$num_rows = $database->select($sql, $parameters, 'column');
		unset($sql, $parameters);
	}
	*/
	$sql = "select count(*) from v_xml_cdr ";
	$sql .= "where domain_uuid = :domain_uuid ";
	/*
	if (is_array($extension_uuids) && @sizeof($extension_uuids)) {
		$sql .= "and (extension_uuid = '".implode("' or extension_uuid = '", $extension_uuids)."') \n";
	}
	*/
	// if user does not have any extension available, the list will be empty
	$sql .= "and (extension_uuid = '".implode("' or extension_uuid = '", $extension_uuids)."') \n";
	$sql .= " and start_stamp::date = now()::date \n";
	$parameters['domain_uuid'] = $domain_uuid;
	$database = new database;
	$num_rows = $database->select($sql, $parameters, 'column');
	unset($sql, $parameters);

//limit the number of results
	/*
	if ($_SESSION['client_calls']['limit']['numeric'] > 0) {
		$num_rows = $_SESSION['client_calls']['limit']['numeric'];
	}
	*/

//set the default paging
	//$rows_per_page = $_SESSION['domain']['paging']['numeric'];
	$rows_per_page = 10;

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
	$sql .= "to_char(timezone(:time_zone, start_stamp), 'DD Mon YYYY') as start_date_formatted, \n";
	$sql .= "to_char(timezone(:time_zone, start_stamp), 'HH12:MI:SS am') as start_time_formatted, \n";
	$sql .= "c.start_epoch, \n";
	$sql .= "c.hangup_cause, \n";
	$sql .= "c.duration, \n";
	$sql .= "c.billmsec, \n";
	$sql .= "c.xml_cdr_uuid, \n";
	$sql .= "c.direction, \n";
	$sql .= "c.billsec, \n";
	$sql .= "case when length(c.caller_id_number) = 4 then c.destination_number else c.caller_id_name end as caller_id_name, \n";
	$sql .= "case when length(c.caller_id_number) = 4 then c.destination_number else c.caller_id_number end as caller_id_number, \n";
	$sql .= "c.caller_destination, \n";
	$sql .= "c.source_number, \n";
	$sql .= "case when length(c.caller_id_number) = 4 then c.caller_id_number else c.destination_number end as destination_number, \n";
	$sql .= "c.leg, \n";
	$sql .= "c.answer_stamp, \n";
	$sql .= "c.sip_hangup_disposition, \n";
	$sql .= "f.client_call_record_uuid, \n";
	$sql .= "f.access_code, \n";
	$sql .= "f.cost_center_id, \n";
	$sql .= "f.employee_id_or_name, \n";
	$sql .= "f.interpret_language, \n";
	$sql .= "f.interpreter_id, \n";
	$sql .= "f.fusion_group_interpreter_id, \n";
	$sql .= "f.job_number, \n";
	$sql .= "f.start_timestamp, \n";
	$sql .= "f.start_timestamp as interpret_stamp, \n";
	$sql .= "f.start_timestamp as interpret_stamp_begin, \n";
	$sql .= "EXTRACT(EPOCH FROM c.end_stamp - f.start_timestamp) as interpret_duration, \n";
	$sql .= "(c.answer_epoch - c.start_epoch) as tta ";
	if ($_REQUEST['show'] == "all" && permission_exists('client_call_record_all')) {
		$sql .= ", c.domain_name \n";
	}
	$sql .= "from v_xml_cdr as c \n";
	$sql .= "left join v_client_call_records as f on f.call_uuid = c.xml_cdr_uuid \n";
	$sql .= "left join v_extensions as e on e.extension_uuid = c.extension_uuid \n";
	$sql .= "inner join v_domains as d on d.domain_uuid = c.domain_uuid \n";
	if ($_REQUEST['show'] == "all" && permission_exists('client_call_record_all')) {
		$sql .= "where true \n";
	}
	else {
		$sql .= "where c.domain_uuid = :domain_uuid \n";
		$parameters['domain_uuid'] = $domain_uuid;
	}
//only show the user their calls
	/*
	if (is_array($extension_uuids) && @sizeof($extension_uuids)) {
		$sql .= "and (c.extension_uuid = '".implode("' or c.extension_uuid = '", $extension_uuids)."') \n";
	}
	*/
	// if user does not have any extension available, the list will be empty
	$sql .= "and (c.extension_uuid = '".implode("' or c.extension_uuid = '", $extension_uuids)."') \n";
//only daily calls
	$sql .= " and c.start_stamp::date = now()::date \n";
//order by
	$sql .= " order by c.start_stamp desc \n";
//limit and offset
	if ($rows_per_page == 0) {
		$sql .= " limit :limit offset 0 \n";
		$parameters['limit'] = $_SESSION['client_calls']['limit']['numeric'];
	}
	else {
		$sql .= " limit :limit offset :offset \n";
		$parameters['limit'] = $rows_per_page;
		$parameters['offset'] = $offset;
	}
	$sql = str_replace("  ", " ", $sql);
	$database = new database;
	$result = $database->select($sql, $parameters, 'all');
	$result_count = is_array($result) ? sizeof($result) : 0;
	unset($database, $sql, $parameters);

//return the paging
	list($paging_controls_mini, $rows_per_page) = paging($num_rows, $param, $rows_per_page, true, $result_count); //top
	list($paging_controls, $rows_per_page) = paging($num_rows, $param, $rows_per_page, false, $result_count); //bottom

?>
