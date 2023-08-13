<?php

//set the include path
	$conf = glob("{/usr/local/etc,/etc}/fusionpbx/config.conf", GLOB_BRACE);
	set_include_path(parse_ini_file($conf[0])['document.root']);

//includes files
	require_once "resources/require.php";
	require_once "resources/check_auth.php";

//check permissions
	if (permission_exists('client_add') || permission_exists('client_edit')) {
		//access granted
	}
	else {
		echo "access denied";
		exit;
	}

//add multi-lingual support
	$language = new text;
	$text = $language->get();

//action add or update
	if (is_uuid($_REQUEST["id"])) {
		$action = "update";
		$client_uuid = $_REQUEST["id"];
	}
	else {
		$action = "add";
	}

//clear the values
	$_access_code = '';
	$_active = '';
	$_invoice = '';
	$_contract_number = '';
	$_client_id = '';
	$_otp_sp_in = '';
	$_otp_instructions = '';
	$_sp_type = '';
	$_invoice_detail = '';
	$_client_name = '';
	$_agency = '';
	$_division = '';
	$_county = '';
	$_account_number = '';
	$_div_contact = '';
	$_div_address = '';
	$_div_street = '';
	$_div_building = '';
	$_div_suite = '';
	$_div_city = '';
	$_div_state = '';
	$_div_zip = '';
	$_div_phone = '';
	$_div_fax = '';
	$_div_email = '';
	$_bil_contact = '';
	$_bil_address = '';
	$_bill_to_1 = '';
	$_bill_to_2 = '';
	$_bill_street = '';
	$_bill_bulding_name = '';
	$_bill_suite = '';
	$_bill_city = '';
	$_bill_state = '';
	$_bill_zip = '';
	$_bil_phone = '';
	$_bil_fax = '';
	$_bil_email = '';
	$_otpls1 = '';
	$_otpls2 = '';
	$_otpls3 = '';
	$_otpls4 = '';
	$_otpls1a = '';
	$_otpls2a = '';
	$_otpls3a = '';
	$_otpls4a = '';
	$_osls1 = '';
	$_osls2 = '';
	$_osls3 = '';
	$_osls4 = '';
	$_osahf = '';
	$_osrf = '';
	$_osrt = '';
	$_oscf = '';
	$_osct = '';
	$_onsite_instructions = '';
	$_charge_callout = '';
	$_rate_callout = '';
	$_special_invoice = '';
	$_vrisl1 = '';
	$_vrisl2 = '';
	$_vrisl3 = '';
	$_vrisl4 = '';
	$_vricf = '';
	$_vrict = '';
	$_vriahf = '';
	$_tls1 = '';
	$_tls2 = '';
	$_tls3 = '';
	$_tls4 = '';
	$_tff = '';
	$_trf = '';

//get http post variables and set them to php variables
	if (count($_POST)>0) {
		$_access_code = $_POST["access_code"];
		$_active = $_POST["active"];
		$_invoice = $_POST["invoice"];
		$_contract_number = $_POST["contract_number"];
		$_client_id = $_POST["client_id"];
		$_otp_sp_in = $_POST["otp_sp_in"];
		$_otp_instructions = $_POST["otp_instructions"];
		$_sp_type = $_POST["sp_type"];
		$_invoice_detail = $_POST["invoice_detail"];
		$_client_name = $_POST["client_name"];
		$_agency = $_POST["agency"];
		$_division = $_POST["division"];
		$_county = $_POST["county"];
		$_account_number = $_POST["account_number"];
		$_div_contact = $_POST["div_contact"];
		$_div_address = $_POST["div_address"];
		$_div_street = $_POST["div_street"];
		$_div_building = $_POST["div_building"];
		$_div_suite = $_POST["div_suite"];
		$_div_city = $_POST["div_city"];
		$_div_state = $_POST["div_state"];
		$_div_zip = $_POST["div_zip"];
		$_div_phone = $_POST["div_phone"];
		$_div_fax = $_POST["div_fax"];
		$_div_email = $_POST["div_email"];
		$_bil_contact = $_POST["bil_contact"];
		$_bil_address = $_POST["bil_address"];
		$_bill_to_1 = $_POST["bill_to_1"];
		$_bill_to_2 = $_POST["bill_to_2"];
		$_bill_street = $_POST["bill_street"];
		$_bill_bulding_name = $_POST["bill_bulding_name"];
		$_bill_suite = $_POST["bill_suite"];
		$_bill_city = $_POST["bill_city"];
		$_bill_state = $_POST["bill_state"];
		$_bill_zip = $_POST["bill_zip"];
		$_bil_phone = $_POST["bil_phone"];
		$_bil_fax = $_POST["bil_fax"];
		$_bil_email = $_POST["bil_email"];
		$_otpls1 = $_POST["otpls1"];
		$_otpls2 = $_POST["otpls2"];
		$_otpls3 = $_POST["otpls3"];
		$_otpls4 = $_POST["otpls4"];
		$_otpls1a = $_POST["otpls1a"];
		$_otpls2a = $_POST["otpls2a"];
		$_otpls3a = $_POST["otpls3a"];
		$_otpls4a = $_POST["otpls4a"];
		$_osls1 = $_POST["osls1"];
		$_osls2 = $_POST["osls2"];
		$_osls3 = $_POST["osls3"];
		$_osls4 = $_POST["osls4"];
		$_osahf = $_POST["osahf"];
		$_osrf = $_POST["osrf"];
		$_osrt = $_POST["osrt"];
		$_oscf = $_POST["oscf"];
		$_osct = $_POST["osct"];
		$_onsite_instructions = $_POST["onsite_instructions"];
		$_charge_callout = $_POST["charge_callout"];
		$_rate_callout = $_POST["rate_callout"];
		$_special_invoice = $_POST["special_invoice"];
		$_vrisl1 = $_POST["vrisl1"];
		$_vrisl2 = $_POST["vrisl2"];
		$_vrisl3 = $_POST["vrisl3"];
		$_vrisl4 = $_POST["vrisl4"];
		$_vricf = $_POST["vricf"];
		$_vrict = $_POST["vrict"];
		$_vriahf = $_POST["vriahf"];
		$_tls1 = $_POST["tls1"];
		$_tls2 = $_POST["tls2"];
		$_tls3 = $_POST["tls3"];
		$_tls4 = $_POST["tls4"];
		$_tff = $_POST["tff"];
		$_trf = $_POST["trf"];
	}

if (count($_POST)>0 && strlen($_POST["persistformvar"]) == 0) {

	$msg = '';
	if ($action == "update") {
		$client_uuid = $_POST["client_uuid"];
	}

	//delete the client
		if (permission_exists('client_delete')) {
			if ($_POST['action'] == 'delete' && is_uuid($client_uuid)) {
				//prepare
					$array[0]['checked'] = 'true';
					$array[0]['uuid'] = $client_uuid;
				//delete
					$obj = new clients;
					$obj->delete($array);
				//redirect
					header('Location: clients.php');
					exit;
			}
		}

	//validate the token
		$token = new token;
		if (!$token->validate($_SERVER['PHP_SELF'])) {
			message::add($text['message-invalid_token'],'negative');
			header('Location: clients.php');
			exit;
		}

	//check for all required data
		//if (strlen($client_driver) == 0) { $msg .= $text['message-required'].$text['label-driver']."<br>\n"; }
		//if (strlen($client_type) == 0) { $msg .= $text['message-required'].$text['label-type']."<br>\n"; }
		//if (strlen($client_host) == 0) { $msg .= $text['message-required'].$text['label-host']."<br>\n"; }
		//if (strlen($client_port) == 0) { $msg .= $text['message-required'].$text['label-port']."<br>\n"; }
		//if (strlen($client_name) == 0) { $msg .= $text['message-required'].$text['label-name']."<br>\n"; }
		//if (strlen($client_username) == 0) { $msg .= $text['message-required'].$text['label-username']."<br>\n"; }
		//if (strlen($client_password) == 0) { $msg .= $text['message-required'].$text['label-password']."<br>\n"; }
		//if (strlen($client_path) == 0) { $msg .= $text['message-required'].$text['label-path']."<br>\n"; }
		//if (strlen($client_description) == 0) { $msg .= $text['message-required'].$text['label-description']."<br>\n"; }
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

	//add or update the client
	if ($_POST["persistformvar"] != "true") {

		//begin array
			$array['clients'][0]['access_code'] = $_access_code;
			$array['clients'][0]['active'] = $_active;
			$array['clients'][0]['invoice'] = $_invoice;
			$array['clients'][0]['contract_number'] = $_contract_number;
			$array['clients'][0]['client_id'] = $_client_id;
			$array['clients'][0]['otp_sp_in'] = $_otp_sp_in;
			$array['clients'][0]['otp_instructions'] = $_otp_instructions;
			$array['clients'][0]['sp_type'] = $_sp_type;
			$array['clients'][0]['invoice_detail'] = $_invoice_detail;
			$array['clients'][0]['client_name'] = $_client_name;
			$array['clients'][0]['agency'] = $_agency;
			$array['clients'][0]['division'] = $_division;
			$array['clients'][0]['county'] = $_county;
			$array['clients'][0]['account_number'] = $_account_number;
			$array['clients'][0]['div_contact'] = $_div_contact;
			$array['clients'][0]['div_address'] = $_div_address;
			$array['clients'][0]['div_street'] = $_div_street;
			$array['clients'][0]['div_building'] = $_div_building;
			$array['clients'][0]['div_suite'] = $_div_suite;
			$array['clients'][0]['div_city'] = $_div_city;
			$array['clients'][0]['div_state'] = $_div_state;
			$array['clients'][0]['div_zip'] = $_div_zip;
			$array['clients'][0]['div_phone'] = $_div_phone;
			$array['clients'][0]['div_fax'] = $_div_fax;
			$array['clients'][0]['div_email'] = $_div_email;
			$array['clients'][0]['bil_contact'] = $_bil_contact;
			$array['clients'][0]['bil_address'] = $_bil_address;
			$array['clients'][0]['bill_to_1'] = $_bill_to_1;
			$array['clients'][0]['bill_to_2'] = $_bill_to_2;
			$array['clients'][0]['bill_street'] = $_bill_street;
			$array['clients'][0]['bill_bulding_name'] = $_bill_bulding_name;
			$array['clients'][0]['bill_suite'] = $_bill_suite;
			$array['clients'][0]['bill_city'] = $_bill_city;
			$array['clients'][0]['bill_state'] = $_bill_state;
			$array['clients'][0]['bill_zip'] = $_bill_zip;
			$array['clients'][0]['bil_phone'] = $_bil_phone;
			$array['clients'][0]['bil_fax'] = $_bil_fax;
			$array['clients'][0]['bil_email'] = $_bil_email;
			$array['clients'][0]['otpls1'] = $_otpls1;
			$array['clients'][0]['otpls2'] = $_otpls2;
			$array['clients'][0]['otpls3'] = $_otpls3;
			$array['clients'][0]['otpls4'] = $_otpls4;
			$array['clients'][0]['otpls1a'] = $_otpls1a;
			$array['clients'][0]['otpls2a'] = $_otpls2a;
			$array['clients'][0]['otpls3a'] = $_otpls3a;
			$array['clients'][0]['otpls4a'] = $_otpls4a;
			$array['clients'][0]['osls1'] = $_osls1;
			$array['clients'][0]['osls2'] = $_osls2;
			$array['clients'][0]['osls3'] = $_osls3;
			$array['clients'][0]['osls4'] = $_osls4;
			$array['clients'][0]['osahf'] = $_osahf;
			$array['clients'][0]['osrf'] = $_osrf;
			$array['clients'][0]['osrt'] = $_osrt;
			$array['clients'][0]['oscf'] = $_oscf;
			$array['clients'][0]['osct'] = $_osct;
			$array['clients'][0]['onsite_instructions'] = $_onsite_instructions;
			$array['clients'][0]['charge_callout'] = $_charge_callout;
			$array['clients'][0]['rate_callout'] = $_rate_callout;
			$array['clients'][0]['special_invoice'] = $_special_invoice;
			$array['clients'][0]['vrisl1'] = $_vrisl1;
			$array['clients'][0]['vrisl2'] = $_vrisl2;
			$array['clients'][0]['vrisl3'] = $_vrisl3;
			$array['clients'][0]['vrisl4'] = $_vrisl4;
			$array['clients'][0]['vricf'] = $_vricf;
			$array['clients'][0]['vrict'] = $_vrict;
			$array['clients'][0]['vriahf'] = $_vriahf;
			$array['clients'][0]['tls1'] = $_tls1;
			$array['clients'][0]['tls2'] = $_tls2;
			$array['clients'][0]['tls3'] = $_tls3;
			$array['clients'][0]['tls4'] = $_tls4;
			$array['clients'][0]['tff'] = $_tff;
			$array['clients'][0]['trf'] = $_trf;

		if ($action == "add") {
			//add new uuid
				$array['clients'][0]['client_uuid'] = uuid();

				$database = new database;
				$database->app_name = 'clients';
				$database->app_uuid = '6061d7cc-8beb-494c-b2a6-ea77293aa4c7';
				$database->save($array);
				unset($array);

			//redirect the browser
				message::add($text['message-add']);
				header("Location: clients.php");
				exit;
		}

		if ($action == "update") {
			//add uuid to update
				$array['clients'][0]['client_uuid'] = $client_uuid;

				$database = new database;
				$database->app_name = 'clients';
				$database->app_uuid = '6061d7cc-8beb-494c-b2a6-ea77293aa4c7';
				$database->save($array);
				unset($array);

			//redirect the browser
				message::add($text['message-update']);
				header("Location: clients.php");
				exit;
		}
	}
}

//pre-populate the form
	if (count($_GET)>0 && $_POST["persistformvar"] != "true") {
		$client_uuid = $_GET["id"];
		$sql = "select * from v_clients ";
		$sql .= "where client_uuid = :client_uuid ";
		$parameters['client_uuid'] = $client_uuid;
		$database = new database;
		$row = $database->select($sql, $parameters, 'row');
		if (is_array($row) && sizeof($row) != 0) {
			$_access_code = $row["access_code"];
			$_active = $row["active"];
			$_invoice = $row["invoice"];
			$_contract_number = $row["contract_number"];
			$_client_id = $row["client_id"];
			$_otp_sp_in = $row["otp_sp_in"];
			$_otp_instructions = $row["otp_instructions"];
			$_sp_type = $row["sp_type"];
			$_invoice_detail = $row["invoice_detail"];
			$_client_name = $row["client_name"];
			$_agency = $row["agency"];
			$_division = $row["division"];
			$_county = $row["county"];
			$_account_number = $row["account_number"];
			$_div_contact = $row["div_contact"];
			$_div_address = $row["div_address"];
			$_div_street = $row["div_street"];
			$_div_building = $row["div_building"];
			$_div_suite = $row["div_suite"];
			$_div_city = $row["div_city"];
			$_div_state = $row["div_state"];
			$_div_zip = $row["div_zip"];
			$_div_phone = $row["div_phone"];
			$_div_fax = $row["div_fax"];
			$_div_email = $row["div_email"];
			$_bil_contact = $row["bil_contact"];
			$_bil_address = $row["bil_address"];
			$_bill_to_1 = $row["bill_to_1"];
			$_bill_to_2 = $row["bill_to_2"];
			$_bill_street = $row["bill_street"];
			$_bill_bulding_name = $row["bill_bulding_name"];
			$_bill_suite = $row["bill_suite"];
			$_bill_city = $row["bill_city"];
			$_bill_state = $row["bill_state"];
			$_bill_zip = $row["bill_zip"];
			$_bil_phone = $row["bil_phone"];
			$_bil_fax = $row["bil_fax"];
			$_bil_email = $row["bil_email"];
			$_otpls1 = $row["otpls1"];
			$_otpls2 = $row["otpls2"];
			$_otpls3 = $row["otpls3"];
			$_otpls4 = $row["otpls4"];
			$_otpls1a = $row["otpls1a"];
			$_otpls2a = $row["otpls2a"];
			$_otpls3a = $row["otpls3a"];
			$_otpls4a = $row["otpls4a"];
			$_osls1 = $row["osls1"];
			$_osls2 = $row["osls2"];
			$_osls3 = $row["osls3"];
			$_osls4 = $row["osls4"];
			$_osahf = $row["osahf"];
			$_osrf = $row["osrf"];
			$_osrt = $row["osrt"];
			$_oscf = $row["oscf"];
			$_osct = $row["osct"];
			$_onsite_instructions = $row["onsite_instructions"];
			$_charge_callout = $row["charge_callout"];
			$_rate_callout = $row["rate_callout"];
			$_special_invoice = $row["special_invoice"];
			$_vrisl1 = $row["vrisl1"];
			$_vrisl2 = $row["vrisl2"];
			$_vrisl3 = $row["vrisl3"];
			$_vrisl4 = $row["vrisl4"];
			$_vricf = $row["vricf"];
			$_vrict = $row["vrict"];
			$_vriahf = $row["vriahf"];
			$_tls1 = $row["tls1"];
			$_tls2 = $row["tls2"];
			$_tls3 = $row["tls3"];
			$_tls4 = $row["tls4"];
			$_tff = $row["tff"];
			$_trf = $row["trf"];
		}
		unset($sql, $parameters, $row);
	}

//create token
	$object = new token;
	$token = $object->create($_SERVER['PHP_SELF']);

//include the header
	if ($action == "update") {
		$document['title'] = $text['title-client-edit'];
	}
	if ($action == "add") {
		$document['title'] = $text['title-client-add'];
	}
	require_once "resources/header.php";

//show the content
	echo "<form method='post' name='frm' id='frm'>\n";

	echo "<div class='action_bar' id='action_bar'>\n";
	echo "	<div class='heading'>";
	if ($action == "add") {
		echo "<b>".$text['header-client-add']."</b>";
	}
	if ($action == "update") {
		echo "<b>".$text['header-client-edit']."</b>";
	}
	echo "	</div>\n";
	echo "	<div class='actions'>\n";
	echo button::create(['type'=>'button','label'=>$text['button-back'],'icon'=>$_SESSION['theme']['button_icon_back'],'id'=>'btn_back','style'=>'margin-right: 15px;','link'=>'clients.php']);
	if ($action == 'update' && permission_exists('client_delete')) {
		echo button::create(['type'=>'button','label'=>$text['button-delete'],'icon'=>$_SESSION['theme']['button_icon_delete'],'name'=>'btn_delete','style'=>'margin-right: 15px;','onclick'=>"modal_open('modal-delete','btn_delete');"]);
	}
	echo button::create(['type'=>'submit','label'=>$text['button-save'],'icon'=>$_SESSION['theme']['button_icon_save'],'id'=>'btn_save','name'=>'action','value'=>'save']);
	echo "	</div>\n";
	echo "	<div style='clear: both;'></div>\n";
	echo "</div>\n";

	if ($action == 'update' && permission_exists('client_delete')) {
		echo modal::create(['id'=>'modal-delete','type'=>'delete','actions'=>button::create(['type'=>'submit','label'=>$text['button-continue'],'icon'=>'check','id'=>'btn_delete','style'=>'float: right; margin-left: 15px;','collapse'=>'never','name'=>'action','value'=>'delete','onclick'=>"modal_close();"])]);
	}

	if ($action == "add") {
		echo $text['description-client-add']."\n";
	}
	if ($action == "update") {
		echo $text['description-client-edit']."\n";
	}
	echo "<br /><br />\n";

	echo "<table width='100%' border='0' cellpadding='0' cellspacing='0'>\n";
	

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo " ".$text['label-access_code']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo " <input class='formfld' type='text' name='access_code' maxlength='255' value=\"".escape($_access_code)."\">\n";
	echo "<br />\n";
	echo $text['description-access_code']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo " ".$text['label-active']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo " <input class='formfld' type='text' name='active' maxlength='255' value=\"".escape($_active)."\">\n";
	echo "<br />\n";
	echo $text['description-active']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo " ".$text['label-invoice']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo " <input class='formfld' type='text' name='invoice' maxlength='255' value=\"".escape($_invoice)."\">\n";
	echo "<br />\n";
	echo $text['description-invoice']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo " ".$text['label-contract_number']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo " <input class='formfld' type='text' name='contract_number' maxlength='255' value=\"".escape($_contract_number)."\">\n";
	echo "<br />\n";
	echo $text['description-contract_number']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo " ".$text['label-client_id']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo " <input class='formfld' type='text' name='client_id' maxlength='255' value=\"".escape($_client_id)."\">\n";
	echo "<br />\n";
	echo $text['description-client_id']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo " ".$text['label-otp_sp_in']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo " <input class='formfld' type='text' name='otp_sp_in' maxlength='255' value=\"".escape($_otp_sp_in)."\">\n";
	echo "<br />\n";
	echo $text['description-otp_sp_in']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo " ".$text['label-otp_instructions']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo " <input class='formfld' type='text' name='otp_instructions' maxlength='255' value=\"".escape($_otp_instructions)."\">\n";
	echo "<br />\n";
	echo $text['description-otp_instructions']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo " ".$text['label-sp_type']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo " <input class='formfld' type='text' name='sp_type' maxlength='255' value=\"".escape($_sp_type)."\">\n";
	echo "<br />\n";
	echo $text['description-sp_type']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo " ".$text['label-invoice_detail']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo " <input class='formfld' type='text' name='invoice_detail' maxlength='255' value=\"".escape($_invoice_detail)."\">\n";
	echo "<br />\n";
	echo $text['description-invoice_detail']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo " ".$text['label-client_name']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo " <input class='formfld' type='text' name='client_name' maxlength='255' value=\"".escape($_client_name)."\">\n";
	echo "<br />\n";
	echo $text['description-client_name']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo " ".$text['label-agency']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo " <input class='formfld' type='text' name='agency' maxlength='255' value=\"".escape($_agency)."\">\n";
	echo "<br />\n";
	echo $text['description-agency']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo " ".$text['label-division']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo " <input class='formfld' type='text' name='division' maxlength='255' value=\"".escape($_division)."\">\n";
	echo "<br />\n";
	echo $text['description-division']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo " ".$text['label-county']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo " <input class='formfld' type='text' name='county' maxlength='255' value=\"".escape($_county)."\">\n";
	echo "<br />\n";
	echo $text['description-county']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo " ".$text['label-account_number']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo " <input class='formfld' type='text' name='account_number' maxlength='255' value=\"".escape($_account_number)."\">\n";
	echo "<br />\n";
	echo $text['description-account_number']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo " ".$text['label-div_contact']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo " <input class='formfld' type='text' name='div_contact' maxlength='255' value=\"".escape($_div_contact)."\">\n";
	echo "<br />\n";
	echo $text['description-div_contact']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo " ".$text['label-div_address']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo " <input class='formfld' type='text' name='div_address' maxlength='255' value=\"".escape($_div_address)."\">\n";
	echo "<br />\n";
	echo $text['description-div_address']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo " ".$text['label-div_street']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo " <input class='formfld' type='text' name='div_street' maxlength='255' value=\"".escape($_div_street)."\">\n";
	echo "<br />\n";
	echo $text['description-div_street']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo " ".$text['label-div_building']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo " <input class='formfld' type='text' name='div_building' maxlength='255' value=\"".escape($_div_building)."\">\n";
	echo "<br />\n";
	echo $text['description-div_building']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo " ".$text['label-div_suite']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo " <input class='formfld' type='text' name='div_suite' maxlength='255' value=\"".escape($_div_suite)."\">\n";
	echo "<br />\n";
	echo $text['description-div_suite']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo " ".$text['label-div_city']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo " <input class='formfld' type='text' name='div_city' maxlength='255' value=\"".escape($_div_city)."\">\n";
	echo "<br />\n";
	echo $text['description-div_city']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo " ".$text['label-div_state']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo " <input class='formfld' type='text' name='div_state' maxlength='255' value=\"".escape($_div_state)."\">\n";
	echo "<br />\n";
	echo $text['description-div_state']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo " ".$text['label-div_zip']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo " <input class='formfld' type='text' name='div_zip' maxlength='255' value=\"".escape($_div_zip)."\">\n";
	echo "<br />\n";
	echo $text['description-div_zip']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo " ".$text['label-div_phone']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo " <input class='formfld' type='text' name='div_phone' maxlength='255' value=\"".escape($_div_phone)."\">\n";
	echo "<br />\n";
	echo $text['description-div_phone']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo " ".$text['label-div_fax']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo " <input class='formfld' type='text' name='div_fax' maxlength='255' value=\"".escape($_div_fax)."\">\n";
	echo "<br />\n";
	echo $text['description-div_fax']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo " ".$text['label-div_email']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo " <input class='formfld' type='text' name='div_email' maxlength='255' value=\"".escape($_div_email)."\">\n";
	echo "<br />\n";
	echo $text['description-div_email']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo " ".$text['label-bil_contact']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo " <input class='formfld' type='text' name='bil_contact' maxlength='255' value=\"".escape($_bil_contact)."\">\n";
	echo "<br />\n";
	echo $text['description-bil_contact']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo " ".$text['label-bil_address']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo " <input class='formfld' type='text' name='bil_address' maxlength='255' value=\"".escape($_bil_address)."\">\n";
	echo "<br />\n";
	echo $text['description-bil_address']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo " ".$text['label-bill_to_1']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo " <input class='formfld' type='text' name='bill_to_1' maxlength='255' value=\"".escape($_bill_to_1)."\">\n";
	echo "<br />\n";
	echo $text['description-bill_to_1']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo " ".$text['label-bill_to_2']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo " <input class='formfld' type='text' name='bill_to_2' maxlength='255' value=\"".escape($_bill_to_2)."\">\n";
	echo "<br />\n";
	echo $text['description-bill_to_2']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo " ".$text['label-bill_street']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo " <input class='formfld' type='text' name='bill_street' maxlength='255' value=\"".escape($_bill_street)."\">\n";
	echo "<br />\n";
	echo $text['description-bill_street']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo " ".$text['label-bill_bulding_name']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo " <input class='formfld' type='text' name='bill_bulding_name' maxlength='255' value=\"".escape($_bill_bulding_name)."\">\n";
	echo "<br />\n";
	echo $text['description-bill_bulding_name']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo " ".$text['label-bill_suite']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo " <input class='formfld' type='text' name='bill_suite' maxlength='255' value=\"".escape($_bill_suite)."\">\n";
	echo "<br />\n";
	echo $text['description-bill_suite']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo " ".$text['label-bill_city']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo " <input class='formfld' type='text' name='bill_city' maxlength='255' value=\"".escape($_bill_city)."\">\n";
	echo "<br />\n";
	echo $text['description-bill_city']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo " ".$text['label-bill_state']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo " <input class='formfld' type='text' name='bill_state' maxlength='255' value=\"".escape($_bill_state)."\">\n";
	echo "<br />\n";
	echo $text['description-bill_state']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo " ".$text['label-bill_zip']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo " <input class='formfld' type='text' name='bill_zip' maxlength='255' value=\"".escape($_bill_zip)."\">\n";
	echo "<br />\n";
	echo $text['description-bill_zip']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo " ".$text['label-bil_phone']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo " <input class='formfld' type='text' name='bil_phone' maxlength='255' value=\"".escape($_bil_phone)."\">\n";
	echo "<br />\n";
	echo $text['description-bil_phone']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo " ".$text['label-bil_fax']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo " <input class='formfld' type='text' name='bil_fax' maxlength='255' value=\"".escape($_bil_fax)."\">\n";
	echo "<br />\n";
	echo $text['description-bil_fax']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo " ".$text['label-bil_email']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo " <input class='formfld' type='text' name='bil_email' maxlength='255' value=\"".escape($_bil_email)."\">\n";
	echo "<br />\n";
	echo $text['description-bil_email']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo " ".$text['label-otpls1']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo " <input class='formfld' type='text' name='otpls1' maxlength='255' value=\"".escape($_otpls1)."\">\n";
	echo "<br />\n";
	echo $text['description-otpls1']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo " ".$text['label-otpls2']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo " <input class='formfld' type='text' name='otpls2' maxlength='255' value=\"".escape($_otpls2)."\">\n";
	echo "<br />\n";
	echo $text['description-otpls2']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo " ".$text['label-otpls3']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo " <input class='formfld' type='text' name='otpls3' maxlength='255' value=\"".escape($_otpls3)."\">\n";
	echo "<br />\n";
	echo $text['description-otpls3']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo " ".$text['label-otpls4']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo " <input class='formfld' type='text' name='otpls4' maxlength='255' value=\"".escape($_otpls4)."\">\n";
	echo "<br />\n";
	echo $text['description-otpls4']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo " ".$text['label-otpls1a']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo " <input class='formfld' type='text' name='otpls1a' maxlength='255' value=\"".escape($_otpls1a)."\">\n";
	echo "<br />\n";
	echo $text['description-otpls1a']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo " ".$text['label-otpls2a']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo " <input class='formfld' type='text' name='otpls2a' maxlength='255' value=\"".escape($_otpls2a)."\">\n";
	echo "<br />\n";
	echo $text['description-otpls2a']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo " ".$text['label-otpls3a']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo " <input class='formfld' type='text' name='otpls3a' maxlength='255' value=\"".escape($_otpls3a)."\">\n";
	echo "<br />\n";
	echo $text['description-otpls3a']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo " ".$text['label-otpls4a']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo " <input class='formfld' type='text' name='otpls4a' maxlength='255' value=\"".escape($_otpls4a)."\">\n";
	echo "<br />\n";
	echo $text['description-otpls4a']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo " ".$text['label-osls1']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo " <input class='formfld' type='text' name='osls1' maxlength='255' value=\"".escape($_osls1)."\">\n";
	echo "<br />\n";
	echo $text['description-osls1']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo " ".$text['label-osls2']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo " <input class='formfld' type='text' name='osls2' maxlength='255' value=\"".escape($_osls2)."\">\n";
	echo "<br />\n";
	echo $text['description-osls2']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo " ".$text['label-osls3']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo " <input class='formfld' type='text' name='osls3' maxlength='255' value=\"".escape($_osls3)."\">\n";
	echo "<br />\n";
	echo $text['description-osls3']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo " ".$text['label-osls4']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo " <input class='formfld' type='text' name='osls4' maxlength='255' value=\"".escape($_osls4)."\">\n";
	echo "<br />\n";
	echo $text['description-osls4']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo " ".$text['label-osahf']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo " <input class='formfld' type='text' name='osahf' maxlength='255' value=\"".escape($_osahf)."\">\n";
	echo "<br />\n";
	echo $text['description-osahf']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo " ".$text['label-osrf']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo " <input class='formfld' type='text' name='osrf' maxlength='255' value=\"".escape($_osrf)."\">\n";
	echo "<br />\n";
	echo $text['description-osrf']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo " ".$text['label-osrt']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo " <input class='formfld' type='text' name='osrt' maxlength='255' value=\"".escape($_osrt)."\">\n";
	echo "<br />\n";
	echo $text['description-osrt']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo " ".$text['label-oscf']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo " <input class='formfld' type='text' name='oscf' maxlength='255' value=\"".escape($_oscf)."\">\n";
	echo "<br />\n";
	echo $text['description-oscf']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo " ".$text['label-osct']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo " <input class='formfld' type='text' name='osct' maxlength='255' value=\"".escape($_osct)."\">\n";
	echo "<br />\n";
	echo $text['description-osct']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo " ".$text['label-onsite_instructions']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo " <input class='formfld' type='text' name='onsite_instructions' maxlength='255' value=\"".escape($_onsite_instructions)."\">\n";
	echo "<br />\n";
	echo $text['description-onsite_instructions']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo " ".$text['label-charge_callout']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo " <input class='formfld' type='text' name='charge_callout' maxlength='255' value=\"".escape($_charge_callout)."\">\n";
	echo "<br />\n";
	echo $text['description-charge_callout']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo " ".$text['label-rate_callout']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo " <input class='formfld' type='text' name='rate_callout' maxlength='255' value=\"".escape($_rate_callout)."\">\n";
	echo "<br />\n";
	echo $text['description-rate_callout']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo " ".$text['label-special_invoice']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo " <input class='formfld' type='text' name='special_invoice' maxlength='255' value=\"".escape($_special_invoice)."\">\n";
	echo "<br />\n";
	echo $text['description-special_invoice']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo " ".$text['label-vrisl1']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo " <input class='formfld' type='text' name='vrisl1' maxlength='255' value=\"".escape($_vrisl1)."\">\n";
	echo "<br />\n";
	echo $text['description-vrisl1']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo " ".$text['label-vrisl2']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo " <input class='formfld' type='text' name='vrisl2' maxlength='255' value=\"".escape($_vrisl2)."\">\n";
	echo "<br />\n";
	echo $text['description-vrisl2']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo " ".$text['label-vrisl3']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo " <input class='formfld' type='text' name='vrisl3' maxlength='255' value=\"".escape($_vrisl3)."\">\n";
	echo "<br />\n";
	echo $text['description-vrisl3']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo " ".$text['label-vrisl4']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo " <input class='formfld' type='text' name='vrisl4' maxlength='255' value=\"".escape($_vrisl4)."\">\n";
	echo "<br />\n";
	echo $text['description-vrisl4']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo " ".$text['label-vricf']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo " <input class='formfld' type='text' name='vricf' maxlength='255' value=\"".escape($_vricf)."\">\n";
	echo "<br />\n";
	echo $text['description-vricf']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo " ".$text['label-vrict']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo " <input class='formfld' type='text' name='vrict' maxlength='255' value=\"".escape($_vrict)."\">\n";
	echo "<br />\n";
	echo $text['description-vrict']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo " ".$text['label-vriahf']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo " <input class='formfld' type='text' name='vriahf' maxlength='255' value=\"".escape($_vriahf)."\">\n";
	echo "<br />\n";
	echo $text['description-vriahf']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo " ".$text['label-tls1']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo " <input class='formfld' type='text' name='tls1' maxlength='255' value=\"".escape($_tls1)."\">\n";
	echo "<br />\n";
	echo $text['description-tls1']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo " ".$text['label-tls2']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo " <input class='formfld' type='text' name='tls2' maxlength='255' value=\"".escape($_tls2)."\">\n";
	echo "<br />\n";
	echo $text['description-tls2']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo " ".$text['label-tls3']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo " <input class='formfld' type='text' name='tls3' maxlength='255' value=\"".escape($_tls3)."\">\n";
	echo "<br />\n";
	echo $text['description-tls3']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo " ".$text['label-tls4']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo " <input class='formfld' type='text' name='tls4' maxlength='255' value=\"".escape($_tls4)."\">\n";
	echo "<br />\n";
	echo $text['description-tls4']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo " ".$text['label-tff']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo " <input class='formfld' type='text' name='tff' maxlength='255' value=\"".escape($_tff)."\">\n";
	echo "<br />\n";
	echo $text['description-tff']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo " ".$text['label-trf']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo " <input class='formfld' type='text' name='trf' maxlength='255' value=\"".escape($_trf)."\">\n";
	echo "<br />\n";
	echo $text['description-trf']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "</table>";
	echo "<br><br>";

	if ($action == "update") {
		echo "<input type='hidden' name='client_uuid' value='".escape($client_uuid)."'>\n";
	}
	echo "<input type='hidden' name='".$token['name']."' value='".$token['hash']."'>\n";

	echo "</form>";

//include the footer
	require_once "resources/footer.php";

?>