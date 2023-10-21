<?php

//set the include path
	$conf = glob("{/usr/local/etc,/etc}/fusionpbx/config.conf", GLOB_BRACE);
	set_include_path(parse_ini_file($conf[0])['document.root']);

//includes files
	require_once "resources/require.php";
	require_once "resources/check_auth.php";

//check permissions
	if (permission_exists('client_call_record_edit')) {
		//access granted
	}
	else {
		echo "access denied";
		exit;
	}

//add multi-lingual support
	$language = new text;
	$text = $language->get();

//action update
	if (is_uuid($_REQUEST["id"])) {
		$action = "update";
		$client_call_record_uuid = $_REQUEST["id"];
	}
	else {
		$action = "";
	}

//clear the values
	$_access_code = '';
	$_cost_center_id = '';
	$_employee_id = '';
	$_caller_name = '';
	$_interpret_language = '';
	$_interpreter_id = '';
	$_fusion_group_interpreter_id = '';
	$_active_interpret = '';

//get http post variables and set them to php variables
	if (count($_POST)>0) {
		$_access_code = $_POST["access_code"];
		$_cost_center_id = $_POST["cost_center_id"];
		$_employee_id = $_POST["employee_id"];
		$_caller_name = $_POST["caller_name"];
		$_interpret_language = $_POST["interpret_language"];
		$_interpreter_id = $_POST["interpreter_id"];
		$_fusion_group_interpreter_id = $_POST["fusion_group_interpreter_id"];
		$_active_interpret = $_POST["active_interpret"];
	}

if (count($_POST)>0 && strlen($_POST["persistformvar"]) == 0) {

	$msg = '';
	if ($action == "update") {
		$client_call_record_uuid = $_POST["client_call_record_uuid"];
	}

	//delete the client call record
		if (permission_exists('client_call_record_delete')) {
			if ($_POST['action'] == 'delete' && is_uuid($client_call_record_uuid)) {
				//prepare
					$array[0]['checked'] = 'true';
					$array[0]['uuid'] = $client_call_record_uuid;
				//delete
					$obj = new client_call_records;
					$obj->delete($array);
				//redirect
					header('Location: client_call_records.php');
					exit;
			}
		}

	//validate the token
		$token = new token;
		if (!$token->validate($_SERVER['PHP_SELF'])) {
			message::add($text['message-invalid_token'],'negative');
			header('Location: client_call_records.php');
			exit;
		}

	//check for all required data
		if (strlen($_access_code) == 0) { $msg .= $text['message-required'].$text['label-access_code']."<br>\n"; }
		if (strlen($_interpret_language) == 0) { $msg .= $text['message-required'].$text['label-interpret_language']."<br>\n"; }
		if (strlen($_interpreter_id) == 0) { $msg .= $text['message-required'].$text['label-interpreter_id']."<br>\n"; }
		//if (strlen($_cost_center_id) == 0) { $msg .= $text['message-required'].$text['label-cost_center_id']."<br>\n"; }
		//if (strlen($_employee_id) == 0) { $msg .= $text['message-required'].$text['label-employee_id']."<br>\n"; }
		if (strlen($_caller_name) == 0) { $msg .= $text['message-required'].$text['label-caller_name']."<br>\n"; }
		//if (strlen($_fusion_group_interpreter_id) == 0) { $msg .= $text['message-required'].$text['label-fusion_group_interpreter_id']."<br>\n"; }
		if (strlen($msg) > 0 && strlen($_POST["persistformvar"]) == 0) {
			require_once "resources/header.php";
			require_once "resources/persist_form_var.php";
			echo "<div align='center'>\n";
			echo "<table><tr><td>\n";
			echo $msg."<br />";
			echo "</td></tr></table>\n";
			persistformvar($_POST);
			echo "</div>\n";
			require_once "resources/footer.php";
			return;
		}

	//check if access code record exists
	if (strlen($_access_code) > 0) {
		$sql = "select count(*) from v_clients where access_code = :access_code ";
		$parameters['access_code'] = $_access_code;
		$database = new database;
		$count = $database->select($sql, $parameters, 'column');
		unset($sql);
		unset($parameters);
		if ($count > 0) {
			//valid access code
			//client exists
		} else {
			$msg .= "Invalid Access Code.<br>\nClient not found.<br>\n";
			require_once "resources/header.php";
			require_once "resources/persist_form_var.php";
			echo "<div align='center'>\n";
			echo "<table><tr><td>\n";
			echo $msg."<br />";
			echo "</td></tr></table>\n";
			persistformvar($_POST);
			echo "</div>\n";
			require_once "resources/footer.php";
			return;
		}
	}

	//update the client call record
	if ($_POST["persistformvar"] != "true") {

		//begin array
			$array['client_call_records'][0]['access_code'] = $_access_code;
			$array['client_call_records'][0]['cost_center_id'] = $_cost_center_id;
			$array['client_call_records'][0]['employee_id'] = $_employee_id;
			$array['client_call_records'][0]['caller_name'] = $_caller_name;
			$array['client_call_records'][0]['interpret_language'] = $_interpret_language;
			$array['client_call_records'][0]['interpreter_id'] = $_interpreter_id;
			$array['client_call_records'][0]['fusion_group_interpreter_id'] = $_fusion_group_interpreter_id;
			//fetch and update interpret timestamp as same as answer timestamp
			if ($_active_interpret) {
				//fetch call uuid
				$sql = "select call_uuid from v_client_call_records ";
				$sql .= "where client_call_record_uuid = :client_call_record_uuid ";
				$parameters['client_call_record_uuid'] = $client_call_record_uuid;
				$database = new database;
				$row = $database->select($sql, $parameters, 'row');
				if (is_array($row) && sizeof($row) != 0) {
					$call_uuid = $row["call_uuid"];
				}
				unset($sql, $parameters, $row);
				//fetch xml cdr answer timestamp
				$xml_cdr_uuid = $call_uuid;
				$sql = "select answer_stamp from v_xml_cdr ";
				$sql .= "where xml_cdr_uuid = :xml_cdr_uuid ";
				$parameters['xml_cdr_uuid'] = $xml_cdr_uuid;
				$database = new database;
				$row = $database->select($sql, $parameters, 'row');
				if (is_array($row) && sizeof($row) != 0) {
					$answer_stamp = $row["answer_stamp"];
					$array['client_call_records'][0]['start_timestamp'] = $answer_stamp;
				}
				unset($sql, $parameters, $row);
			}

		if ($action == "update") {
			//add uuid to update
				$array['client_call_records'][0]['client_call_record_uuid'] = $client_call_record_uuid;

				$database = new database;
				$database->app_name = 'client_call_records';
				$database->app_uuid = 'f5273585-5399-4c4c-9fd0-db155327ad35';
				$database->save($array);
				unset($array);

			//redirect the browser
				message::add($text['message-update']);
				header("Location: client_call_records.php");
				exit;
		}
	}
}

//pre-populate the form
	if (count($_GET)>0 && $_POST["persistformvar"] != "true") {
		$client_call_record_uuid = $_GET["id"];
		$sql = "select * from v_client_call_records ";
		$sql .= "where client_call_record_uuid = :client_call_record_uuid ";
		$parameters['client_call_record_uuid'] = $client_call_record_uuid;
		$database = new database;
		$row = $database->select($sql, $parameters, 'row');
		if (is_array($row) && sizeof($row) != 0) {
			$_access_code = $row["access_code"];
			$_cost_center_id = $row["cost_center_id"];
			$_employee_id = $row["employee_id"];
			$_caller_name = $row["caller_name"];
			$_interpret_language = $row["interpret_language"];
			$_interpreter_id = $row["interpreter_id"];
			$_fusion_group_interpreter_id = $row["fusion_group_interpreter_id"];
			if (!empty($row["start_timestamp"])) {
				$_active_interpret = 1;
			} else {
				$_active_interpret = 0;
			}
		}
		unset($sql, $parameters, $row);
	}

//create token
	$object = new token;
	$token = $object->create($_SERVER['PHP_SELF']);

//include the header
	if ($action == "update") {
		$document['title'] = $text['title-client_call_record-edit'];
	}
	require_once "resources/header.php";

//show the content
	echo "<form method='post' name='frm' id='frm'>\n";

	echo "<div class='action_bar' id='action_bar'>\n";
	echo "	<div class='heading'>";
	if ($action == "update") {
		echo "<b>".$text['header-client_call_record-edit']."</b>";
	}
	echo "	</div>\n";
	echo "	<div class='actions'>\n";
	echo button::create(['type'=>'button','label'=>$text['button-back'],'icon'=>$_SESSION['theme']['button_icon_back'],'id'=>'btn_back','style'=>'margin-right: 15px;','link'=>'client_call_records.php']);
	if ($action == 'update' && permission_exists('client_call_record_delete')) {
		echo button::create(['type'=>'button','label'=>$text['button-delete'],'icon'=>$_SESSION['theme']['button_icon_delete'],'name'=>'btn_delete','style'=>'margin-right: 15px;','onclick'=>"modal_open('modal-delete','btn_delete');"]);
	}
	echo button::create(['type'=>'submit','label'=>$text['button-save'],'icon'=>$_SESSION['theme']['button_icon_save'],'id'=>'btn_save','name'=>'action','value'=>'save']);
	echo "	</div>\n";
	echo "	<div style='clear: both;'></div>\n";
	echo "</div>\n";

	if ($action == 'update' && permission_exists('client_call_record_delete')) {
		echo modal::create(['id'=>'modal-delete','type'=>'delete','actions'=>button::create(['type'=>'submit','label'=>$text['button-continue'],'icon'=>'check','id'=>'btn_delete','style'=>'float: right; margin-left: 15px;','collapse'=>'never','name'=>'action','value'=>'delete','onclick'=>"modal_close();"])]);
	}

	if ($action == "update") {
		echo $text['description-client_call_record-edit']."\n";
	}
	echo "<br /><br />\n";

	echo "<table width='100%' border='0' cellpadding='0' cellspacing='0'>\n";
	

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo " ".$text['label-access_code']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo " <input required class='formfld' type='number' name='access_code' maxlength='255' value=\"".escape($_access_code)."\">\n";
	echo "<br />\n";
	echo $text['description-access_code']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo " ".$text['label-cost_center_id']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo " <input class='formfld' type='text' name='cost_center_id' maxlength='255' value=\"".escape($_cost_center_id)."\">\n";
	echo "<br />\n";
	echo $text['description-cost_center_id']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo " ".$text['label-employee_id']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo " <input class='formfld' type='text' name='employee_id' maxlength='255' value=\"".escape($_employee_id)."\">\n";
	echo "<br />\n";
	echo $text['description-employee_id']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo " ".$text['label-caller_name']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo " <input class='formfld' type='text' name='caller_name' maxlength='255' value=\"".escape($_caller_name)."\">\n";
	echo "<br />\n";
	echo $text['description-caller_name']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	//interpretation languages
	$iso = new ISO639;
	$interpret_languages_list = $iso->allLanguages();
	$interpret_languages_list = array_column($interpret_languages_list, 1);
	$optionList = '';
	foreach ($interpret_languages_list as $interpret_language_name) {
		$interpret_language_name = ucwords($interpret_language_name);
		$selected = ($_interpret_language == $interpret_language_name) ? "selected='selected'" : null;
		$optionList .= "		<option value='".escape($interpret_language_name)."' ".$selected.">".escape($interpret_language_name)."</option>\n";
	}
	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo " ".$text['label-interpret_language']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo "	<select required class='formfld' name='interpret_language'>\n";
	echo "		<option value=''>---</option>\n";
	echo $optionList;
	echo "	</select>\n";
	echo "<br />\n";
	echo $text['description-interpret_language']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo " ".$text['label-interpreter_id']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo " <input required class='formfld' type='text' name='interpreter_id' maxlength='255' value=\"".escape($_interpreter_id)."\">\n";
	echo "<br />\n";
	echo $text['description-interpreter_id']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo " ".$text['label-fusion_group_interpreter_id']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo " <input class='formfld' type='text' name='fusion_group_interpreter_id' maxlength='255' value=\"".escape($_fusion_group_interpreter_id)."\">\n";
	echo "<br />\n";
	echo $text['description-fusion_group_interpreter_id']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo " Active Interpret?\n";
	echo "</td>\n";
	if ($_active_interpret) {
		echo "<td class='vtable' align='left'>\n";
		echo "	YES\n";
		echo "</td>\n";
	} else {
		echo "<td class='vtable' align='left'>\n";
		echo "	<select required class='formfld' name='active_interpret'>\n";
		echo "		<option value='1' ".($_active_interpret ? "selected='selected'" : "").">Yes</option>\n";
		echo "		<option value='0' ".($_active_interpret ? "" : "selected='selected'").">No</option>\n";
		echo "	</select>\n";
		echo "</td>\n";
	}
	echo "</tr>\n";

	echo "</table>";
	echo "<br><br>";

	if ($action == "update") {
		echo "<input type='hidden' name='client_call_record_uuid' value='".escape($client_call_record_uuid)."'>\n";
	}
	echo "<input type='hidden' name='".$token['name']."' value='".$token['hash']."'>\n";

	echo "</form>";

//include the footer
	require_once "resources/footer.php";

?>