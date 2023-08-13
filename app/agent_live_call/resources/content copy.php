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

//set the onhover paush refresh
$onhover_pause_refresh = " onmouseover='refresh_stop();' onmouseout='refresh_start();'";

echo "<table width='100%'>\n";
echo "	<tr>\n";
echo "		<td valign='top' align='left' width='50%' nowrap>\n";
echo "			<b>".$text['title-agent_live_call']."</b>\n";
echo "		</td>\n";
echo "		<td valign='top' align='right' width='50%' nowrap>\n";
echo "			<table cellpadding='0' cellspacing='0' border='0'>\n";
echo "				<tr>\n";
echo "					<td valign='middle' nowrap='nowrap' style='padding-right: 15px' id='refresh_state'>\n";
echo "						<img src='resources/images/refresh_active.gif' style='width: 16px; height: 16px; border: none; margin-top: 3px; cursor: pointer;' onclick='refresh_stop();' alt=\"".$text['label-refresh_pause']."\" title=\"".$text['label-refresh_pause']."\">\n";
echo "					</td>\n";
echo "				</tr>\n";
echo "			</table>\n";
echo "		</td>\n";
echo "	</tr>\n";
echo "</table>\n";
echo "<br>\n";

//define the arrays to ensure no errors are omitted below with the sizeof operators
$user_extensions = array();

//loop through the array
if (is_array($activity)) {
	foreach ($activity as $extension => $ext) {
		unset($block);

		$extension = $ext['extension'];

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
			$dir_icon = 'outbound';
		}
		else if ($ext['state'] == 'CS_HIBERNATE') {
			if ($ext['callstate'] == 'ACTIVE') {
				$ext_state = 'active';
				if ($ext['direction'] == 'inbound') {
					$call_name = $activity[$ext['dest']]['effective_caller_id_name'];
					$call_number = format_phone($ext['dest']);
					$dir_icon = 'outbound';
				}
				else if ($ext['direction'] == 'outbound') {
					$call_name = $activity[$ext['cid_num']]['effective_caller_id_name'];
					$call_number = format_phone($ext['cid_num']);
					$dir_icon = 'inbound';
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
			$dir_icon = 'inbound';
			$call_name = $activity[$ext['cid_num']]['effective_caller_id_name'];
			$call_number = format_phone($ext['cid_num']);
		}
		else {
			unset($ext_state, $dir_icon, $call_name, $call_number);
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
			$employee_id_or_name = $row["employee_id_or_name"];
			$interpret_language = $row["interpret_language"];
			$interpreter_id = $row["interpreter_id"];
			$fusion_group_interpreter_id = $row["fusion_group_interpreter_id"];
			$interpret_stamp_begin = $row["start_timestamp"];
		} else {
			$access_code = '';
			$cost_center_id = '';
			$employee_id_or_name = '';
			$interpret_language = '';
			$interpreter_id = '';
			$fusion_group_interpreter_id = '';
			$interpret_stamp_begin = '';
		}
		unset($sql, $parameters, $row);

		//build the list of extensions
		$block .= "<div id='".escape($ext['call_uuid'])."' class='active-client-call ".$style."' style='width:465px'>";
		$block .= "<table class='".$style."'>\n";
		$block .= "	<tr>\n";
		$block .= "		<td class='op_ext_info ".$style."'>\n";
		if ($dir_icon != '') {
			$block .= "			<img src='resources/images/".$dir_icon.".png' align='right' style='margin-top: 3px; margin-right: 1px; width: 12px; height: 12px; cursor: help;' alt=\"".$text['label-call_direction']."\" title=\"".$text['label-call_direction']."\">\n";
		}
		$block .= "			<span class='op_user_info'>\n";
		if ($ext['effective_caller_id_name'] != '' && escape($ext['effective_caller_id_name']) != $extension) {
			$block .= "			<strong class='strong'>".escape($ext['effective_caller_id_name'])."</strong> (".escape($extension).")\n";
		}
		else {
			$block .= "			<strong class='strong'>".escape($extension)."</strong>\n";
		}
		$block .= "			</span><br>\n";

		$block .= "		<span class='op_caller_info'>\n";
		$block .= "			<table align='right'><tr><td style='text-align: right;'>\n";
		$block .= "				<span class='op_call_info'>".escape($ext['call_length'])."</span><br>\n";
		$block .= "				<span class='call_control'>\n";
		/*
		//call start timestamp
		$call_identifier_ts = $ext['call_uuid'];
		$ext_key = array_search($ext['destination'], array_column($_SESSION['user']['extension'], 'destination'));
		$last_timestamp_uuid = $_SESSION['user']['extension'][$ext_key]['last_timestamp_uuid'];
		if ((strlen($last_timestamp_uuid) > 0 ) AND $last_timestamp_uuid == $call_identifier_ts) {
			$block .= 		"<button type='button' class='btn btn-default disabled' disabled='disabled'>Started...</button>\n";
		}
		else {
			$block .= 		"<button type='button' class='btn btn-default' title=\"".$text['label-start_timestamp']."\" onclick=\"start_timestamp(this, '".escape($ext['destination'])."','".$call_identifier_ts."');\">Start Interpret</button>\n";
		}
		*/
		//call start timestamp
		if ((strlen($interpret_stamp_begin) > 0 )) {
			$block .= 		"<button type='button' class='btn btn-default disabled interpret_stamp_begin_btn' disabled='disabled'>Started...</button>\n";
		} else {
			$block .= 		"<button type='button' class='btn btn-default interpret_stamp_begin_btn' title=\"".$text['label-start_timestamp']."\" onclick=\"start_timestamp(this, '".escape($ext['destination'])."','".escape($ext['call_uuid'])."');\">Start Interpret</button>\n";
		}
		$block .=				"</span>\n";
		$block .= "			</td></tr></table>\n";
		$block .= "			<span id='op_caller_details_".escape($extension)."'><strong>".escape($call_name)."</strong><br>".escape($call_number)."</span>\n";
		$block .= "		</span>\n";

		$block .= "		</td>\n";
		$block .= "	</tr>\n";
		$block .= "	<tr>\n";
		$block .= "		<td class='op_ext_info' style='padding-top:15px;padding-bottom:15px;'>\n";

		//interpretation languages
		$iso = new ISO639;
		$interpret_languages_list = $iso->allLanguages();
		$interpret_languages_list = array_column($interpret_languages_list, 1);
		$optionList = '';
		foreach ($interpret_languages_list as $interpret_language_name) {
			$interpret_language_name = ucwords($interpret_language_name);
			$selected = ($interpret_language == $interpret_language_name) ? "selected='selected'" : null;
			$optionList .= "						<option value='".escape($interpret_language_name)."' ".$selected.">".escape($interpret_language_name)."</option>\n";
		}

		//form
		$block .= "			<form autocomplete='off' id='client-details-form-" . escape($ext['call_uuid']). "' onsubmit='client_details_form_submit(event, \"" . escape($ext['call_uuid']). "\")'>\n";
		$block .= "				<div class='form-group'>\n";
		$block .= "					<label for='access_code'>Access Code <span class='text-danger'>*</span></label>\n";
		$block .= "					<input required type='number' class='form-control form-control-sm' id='client_access_code_input' \n";
		$block .= "						name='access_code' value='".escape($access_code)."' autocomplete='off' onkeyup='validate_client_access_code(this);'>\n";
		$block .= "						<span id='client_access_code_validate_status_text' class='form-text'></span>\n";
		$block .= "				</div>\n";
		$block .= "				<div class='form-group'>\n";
		$block .= "					<label for='cost_center_id'>Cost Center ID</label>\n";
		$block .= "					<input type='text' class='form-control form-control-sm' name='cost_center_id' value='".escape($cost_center_id)."' autocomplete='off'>\n";
		$block .= "				</div>\n";
		$block .= "				<div class='form-group'>\n";
		$block .= "					<label for='employee_id_or_name'>Employee ID / Representative Name</label>\n";
		$block .= "					<input type='text' class='form-control form-control-sm' name='employee_id_or_name' value='".escape($employee_id_or_name)."' autocomplete='off'>\n";
		$block .= "				</div>\n";
		$block .= "				<div class='form-group'>\n";
		$block .= "					<label for='interpret_language'>Language <span class='text-danger'>*</span></label>\n";
		$block .= "					<select required class='form-control form-control-sm' name='interpret_language'>\n";
		$block .= "						<option value=''>---</option>\n";
		$block .= 						$optionList;
		$block .= "					</select>\n";
		$block .= "				</div>\n";
		$block .= "				<div class='form-group'>\n";
		$block .= "					<label for='interpreter_id'>Interpreter ID <span class='text-danger'>*</span></label>\n";
		$block .= "					<input required type='text' class='form-control form-control-sm' name='interpreter_id' value='".escape($interpreter_id)."' autocomplete='off'>\n";
		$block .= "				</div>\n";
		$block .= "				<div class='form-group'>\n";
		$block .= "					<label for='fusion_group_interpreter_id'>Fusion Group Interpreter ID</label>\n";
		$block .= "					<input type='text' class='form-control form-control-sm' name='fusion_group_interpreter_id' value='".escape($fusion_group_interpreter_id)."' autocomplete='off'>\n";
		$block .= "				</div>\n";
		$block .= "				<input type='hidden' name='call_uuid' value='".escape($ext['call_uuid'])."'>";
		$block .= "				<button type='submit' class='btn btn-primary btn-sm'>Save</button>\n";
		$block .= "			</form>\n";
		$block .= "		</td>\n";
		$block .= "	</tr>\n";
		$block .= "</table>\n";

		if (if_group("superadmin") && isset($_GET['debug'])) {
			$block .= "<span style='font-size: 10px;'>\n";
			$block .= "From ID<br>&nbsp;&nbsp;&nbsp;&nbsp;<strong style='color: maroon'>".escape($extension)."</strong><br>\n";
			$block .= "uuid<br>&nbsp;&nbsp;&nbsp;&nbsp;<strong style='color: ".($call_identifier == $ext['uuid'] ? 'blue' : 'black').";'>".escape($ext['uuid'])."</strong><br>\n";
			$block .= "call_uuid<br>&nbsp;&nbsp;&nbsp;&nbsp;<strong style='color: ".($call_identifier == $ext['call_uuid'] ? 'blue' : 'black').";'>".escape($ext['call_uuid'])."</strong><br>\n";
			$block .= "variable_bridge_uuid<br>&nbsp;&nbsp;&nbsp;&nbsp;<strong style='color: ".($call_identifier == $ext['variable_bridge_uuid'] ? 'blue' : 'black').";'>".escape($ext['variable_bridge_uuid'])."</strong><br>\n";
			$block .= "direction<br>&nbsp;&nbsp;&nbsp;&nbsp;<strong style='color: black;'>".escape($ext['direction'])."</strong><br>\n";
			$block .= "variable_call_direction<br>&nbsp;&nbsp;&nbsp;&nbsp;<strong style='color: black;'>".escape($ext['variable_call_direction'])."</strong><br>\n";
			$block .= "state<br>&nbsp;&nbsp;&nbsp;&nbsp;<strong style='color: black;'>".escape($ext['state'])."</strong><br>\n";
			$block .= "cid_num<br>&nbsp;&nbsp;&nbsp;&nbsp;<strong style='color: black;'>".escape($ext['cid_num'])."</strong><br>\n";
			$block .= "dest<br>&nbsp;&nbsp;&nbsp;&nbsp;<strong style='color: black;'>".escape($ext['dest'])."</strong><br>\n";
			$block .= "context<br>&nbsp;&nbsp;&nbsp;&nbsp;<strong style='color: black;'>".escape($ext['context'])."</strong><br>\n";
			$block .= "presence_id<br>&nbsp;&nbsp;&nbsp;&nbsp;<strong style='color: black;'>".escape($ext['presence_id'])."</strong><br>\n";
			$block .= "callstate<br>&nbsp;&nbsp;&nbsp;&nbsp;<strong style='color: black;'>".escape($ext['callstate'])."</strong><br>\n";
			$block .= "</span>\n";
		}
		$block .= "</div>\n";

		if (in_array($extension, $_SESSION['user']['extensions'])) {
			$user_extensions[] = $block;
		}
	}
}

if (sizeof($user_extensions) > 0) {
	echo "<table width='100%'><tr><td>\n";
	if (is_array($user_extensions)) {
		foreach ($user_extensions as $ext_block) {
			echo $ext_block;
		}
	}

	echo "</td></tr></table><br>\n";
}

echo "<br><br>\n";

/*
if (if_group("superadmin") && isset($_GET['debug'])) {
	echo '$activity<br>';
	echo "<textarea style='width: 100%; height: 600px; overflow: scroll;' onfocus='refresh_stop();' onblur='refresh_start();'>";
	print_r($activity);
	echo "</textarea>";
	echo "<br><br>";

	echo '$_SESSION<br>';
	echo "<textarea style='width: 100%; height: 600px; overflow: scroll;' onfocus='refresh_stop();' onblur='refresh_start();'>";
	print_r($_SESSION);
	echo "</textarea>";
}
*/

?>
