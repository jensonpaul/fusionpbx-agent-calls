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

//provide call details when extension request is received
//request with api key instead of user login
if (!empty($_GET['extension'])) {
	$_SESSION['user']['extensions'][] = $_GET['extension'];

	$sql = "select ";
	$sql .= "e.extension_uuid ";
	$sql .= "from ";
	$sql .= "v_extensions as e ";
	$sql .= "where ";
	$sql .= "e.extension LIKE :extension ";
	$sql .= "or e.number_alias LIKE :number_alias ";
	$parameters['extension'] = $_GET['extension'];
	$parameters['number_alias'] = $_GET['extension'];
	$database = new database;
	$extension_uuid = $database->select($sql, $parameters, 'column');
	unset($sql, $parameters);

	$sql = "select ";
	$sql .= "eu.user_uuid ";
	$sql .= "from ";
	$sql .= "v_extension_users as eu ";
	$sql .= "where ";
	$sql .= "eu.extension_uuid::text LIKE :extension_uuid ";
	$parameters['extension_uuid'] = $extension_uuid;
	$database = new database;
	$user_uuid = $database->select($sql, $parameters, 'column');
	unset($sql, $parameters);

	/*
	$sql = "select ";
	$sql .= "eu.user_uuid ";
	$sql .= "from ";
	$sql .= "v_users as eu ";
	$sql .= "where ";
	$sql .= "eu.username LIKE :username ";
	$parameters['username'] = '%'.$_GET['extension'].'%';
	$database = new database;
	$user_uuid = $database->select($sql, $parameters, 'column');
	unset($sql, $parameters);
	*/

	$_SESSION['domain_uuid'] = "ae21945e-948b-4e24-9b4c-70f68c494266";
	$_SESSION['event_socket_ip_address'] = "127.0.0.1";
	$_SESSION['event_socket_port'] = "8021";
	$_SESSION['event_socket_password'] = "ClueCon";
	$_SESSION['domain_name'] = "pbx.avaza.co";
	$_SESSION['user']['user_uuid'] = $user_uuid;
	unset($extension_uuid, $user_uuid);

	#$_SESSION['domain_uuid'] = "bac758bd-3ac3-4f15-a710-c9d6b3f66bb7";
	#$_SESSION['domain_name'] = "52.55.236.220";
}

//get the call activity
$agent_live_call = new agent_live_call;
$activity = $agent_live_call->call_activity();

//prevent warnings
if (!is_array($_SESSION['user']['extensions'])) {
	$_SESSION['user']['extensions'] = array();
}

//get registrations -- All SIP profiles
$obj = new registrations;
$registrations = $obj->get("all");

$active_calls = [];

//loop through the array
if (is_array($activity)) {
	$x = 0;
	foreach ($activity as $extension => $ext) {

		$extension = $ext['extension'];

		//stop process if extension does not belong to user
		if (!in_array($extension, $_SESSION['user']['extensions'])) {
			continue;
		}

		//check if feature code being called
		$format_number = (substr($ext['dest'], 0, 1) == '*') ? false : true;

		//determine extension state, direction icon, and displayed name/number for caller/callee
		if ($ext['state'] == 'CS_EXECUTE') {
			if (($ext['callstate'] == 'RINGING' || $ext['callstate'] == 'EARLY' || $ext['callstate'] == 'RING_WAIT') && $ext['direction'] == 'inbound') {
				$ext_state = 'ringing';
			}
			else if ($ext['callstate'] == 'ACTIVE' && $ext['direction'] == 'outbound') {
				$ext_state = 'active';
			}
			else if ($ext['callstate'] == 'HELD' && $ext['direction'] == 'outbound') {
				$ext_state = 'held';
			}
			else if ($ext['callstate'] == 'RING_WAIT' && $ext['direction'] == 'outbound') {
				$ext_state = 'ringing';
			}
			else if ($ext['callstate'] == 'ACTIVE' && $ext['direction'] == 'inbound') {
				$ext_state = 'active';
			}
			else if ($ext['callstate'] == 'HELD' && $ext['direction'] == 'inbound') {
				$ext_state = 'held';
			}
			if (!$format_number) {
				$call_name = 'System';
				$call_number = $ext['dest'];
			}
			else {
				$call_name = $activity[$ext['dest']]['effective_caller_id_name'];
				$call_number = format_phone($ext['dest']);
			}
		}
		else if ($ext['state'] == 'CS_HIBERNATE') {
			if ($ext['callstate'] == 'ACTIVE') {
				$ext_state = 'active';
				if ($ext['direction'] == 'inbound') {
					$call_name = $activity[$ext['dest']]['effective_caller_id_name'];
					$call_number = format_phone($ext['dest']);
				}
				else if ($ext['direction'] == 'outbound') {
					$call_name = $activity[$ext['cid_num']]['effective_caller_id_name'];
					$call_number = format_phone($ext['cid_num']);
				}
			}
		}
		else if ($ext['state'] == 'CS_EXCHANGE_MEDIA' && $ext['callstate'] == 'ACTIVE' && $ext['direction'] == 'inbound') {
			//valet park
			$ext_state = 'active';
					$call_name = $activity[$ext['dest']]['effective_caller_id_name'];
					$call_number = format_phone($ext['dest']);
		}
		else if ($ext['state'] == 'CS_SOFT_EXECUTE' && $ext['callstate'] == 'ACTIVE' && $ext['direction'] == 'outbound') {
			//valet park
			$ext_state = 'active';
					$call_name = $activity[$ext['dest']]['effective_caller_id_name'];
					$call_number = format_phone($ext['dest']);
		}
		else if ($ext['state'] == 'CS_CONSUME_MEDIA' || $ext['state'] == 'CS_EXCHANGE_MEDIA') {
			if ($ext['state'] == 'CS_CONSUME_MEDIA' && $ext['callstate'] == 'RINGING' && $ext['direction'] == 'outbound') {
				$ext_state = 'ringing';
			}
			else if ($ext['state'] == 'CS_EXCHANGE_MEDIA' && $ext['callstate'] == 'ACTIVE' && $ext['direction'] == 'outbound') {
				$ext_state = 'active';
			}
			else if ($ext['state'] == 'CS_EXCHANGE_MEDIA' && $ext['callstate'] == 'ACTIVE' && $ext['direction'] == 'outbound') {
				$ext_state = 'active';
			}
			else if ($ext['state'] == 'CS_CONSUME_MEDIA' && $ext['callstate'] == 'HELD' && $ext['direction'] == 'outbound') {
				$ext_state = 'held';
			}
			else if ($ext['state'] == 'CS_EXCHANGE_MEDIA' && $ext['callstate'] == 'HELD' && $ext['direction'] == 'outbound') {
				$ext_state = 'held';
			}
			$call_name = $activity[$ext['cid_num']]['effective_caller_id_name'];
			$call_number = format_phone($ext['cid_num']);
		}
		else {
			unset($ext_state, $call_name, $call_number);
		}

		//determin extension register status
		$extension_number = $extension.'@'.$_SESSION['domain_name'];
		$found_count = 0;
		if (is_array($registrations)) {
			foreach ($registrations as $array) {
				if ($extension_number == $array['user']) {
					$found_count++;
				}
			}
		}
		if ($found_count > 0) {	
			//determine block style by state (if any) and register status
			$style = ($ext_state != '') ? "op_ext op_state_".$ext_state : "op_ext";
		} else {
			$style = "off_ext";	
		}
		unset($extension_number, $found_count, $array);

		//determine the call identifier passed on drop
		if ($ext['uuid'] == $ext['call_uuid'] && $ext['variable_bridge_uuid'] == '') { // transfer an outbound internal call
			$call_identifier = $activity[$call_number]['uuid'];
		}
		else if (($ext['variable_call_direction'] == 'outbound' || $ext['variable_call_direction'] == 'local') && $ext['variable_bridge_uuid'] != '') { // transfer an outbound external call
			$call_identifier = $ext['variable_bridge_uuid'];
		}
		else {
			if( $ext['call_uuid'] ) {
				$call_identifier = $ext['call_uuid']; // transfer all other call types
			}
			else {
				$call_identifier = $ext['uuid']; // e.g. voice menus
			}
		}

		//check if call uuid record exists in client_call_records
		$sql = "select * from v_client_call_records ";
		$sql .= "where call_uuid = :call_uuid ";
		$parameters['call_uuid'] = $ext['call_uuid'];
		$database = new database;
		$row = $database->select($sql, $parameters, 'row');
		if (is_array($row) && sizeof($row) != 0) {
			$access_code = $row["access_code"];
			$cost_center_id = $row["cost_center_id"];
			$employee_id = $row["employee_id"];
			$caller_name = $row["caller_name"];
			$interpret_language = $row["interpret_language"];
			$interpreter_id = $row["interpreter_id"];
			$fusion_group_interpreter_id = $row["fusion_group_interpreter_id"];
			$interpret_stamp_begin = $row["start_timestamp"];
		} else {
			$access_code = '';
			$cost_center_id = '';
			$employee_id = '';
			$caller_name = '';
			$interpret_language = '';
			$interpreter_id = '';
			$fusion_group_interpreter_id = '';
			$interpret_stamp_begin = '';
		}
		unset($sql, $parameters, $row);

		//get the caller ID from variables
			if (!isset($caller_id_number) && isset($ext['variable_caller_id_number'])) {
				$caller_id_number = urldecode($ext['variable_caller_id_number']);
			}
			if (!isset($caller_id_number) && isset($ext['variable_sip_from_user'])) {
				$caller_id_number = urldecode($ext['variable_sip_from_user']);
			}

		//if the origination caller id name and number are set then use them
			if (isset($ext['variable_origination_caller_id_number'])) {
				$caller_id_number = urldecode($ext['variable_origination_caller_id_number']);
			}

		//if the call is outbound use the external caller ID
			if (isset($ext['variable_origination_caller_id_number'])) {
				$caller_id_number = urldecode($ext['variable_origination_caller_id_number']);
			}

			if (urldecode($ext['variable_call_direction']) == 'outbound' && isset($ext['variable_effective_caller_id_number'])) {
				$caller_id_number = urldecode($ext['variable_effective_caller_id_number']);
			}

		//if the sip_from_domain and domain_name are not the same then original call direction was inbound
			//when an inbound call is forward the call_direction is set to inbound and then updated to outbound
			//use sip_from_display and sip_from_user to get the original caller ID instead of the updated caller ID info from the forward
			if (isset($ext['variable_sip_from_domain']) && urldecode($ext['variable_sip_from_domain']) != urldecode($ext['variable_domain_name'])) {
				if (isset($ext['variable_sip_from_user'])) {
					$caller_id_number = urldecode($ext['variable_sip_from_user']);
				}
			}

		if (in_array($extension, $_SESSION['user']['extensions'])) {
			//build the list of active calls
			$active_calls[$x]['style'] = $style;
			$active_calls[$x]['call_uuid'] = escape($ext['call_uuid']);
			$active_calls[$x]['variable_bridge_uuid'] = escape($ext['variable_bridge_uuid']);
			$active_calls[$x]['call_number'] = escape($call_number);
			$active_calls[$x]['call_length'] = escape($ext['call_length']);
			$active_calls[$x]['caller_number'] = escape($caller_id_number);
			$active_calls[$x]['caller_destination'] = escape($ext['dest']);
			$active_calls[$x]['destination'] = escape($ext['destination']);
			$active_calls[$x]['interpret_stamp_begin'] = $interpret_stamp_begin;
			$active_calls[$x]['label_start_timestamp'] = $text['label-start_timestamp'];

			$active_calls[$x]['access_code'] = escape($access_code);
			$active_calls[$x]['cost_center_id'] = escape($cost_center_id);
			$active_calls[$x]['employee_id'] = escape($employee_id);
			$active_calls[$x]['caller_name'] = escape($caller_name);
			$active_calls[$x]['interpret_language'] = $interpret_language;
			$active_calls[$x]['interpreter_id'] = escape($interpreter_id);
			$active_calls[$x]['fusion_group_interpreter_id'] = escape($fusion_group_interpreter_id);

			$x++;
		}
	}
}

echo json_encode($active_calls);

?>
