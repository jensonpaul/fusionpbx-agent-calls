<?php

//set the include path
	$conf = glob("{/usr/local/etc,/etc}/fusionpbx/config.conf", GLOB_BRACE);
	set_include_path(parse_ini_file($conf[0])['document.root']);

//includes files
	require_once "resources/require.php";
	require_once "resources/check_auth.php";
	require_once "resources/paging.php";

//check permisions
	if (permission_exists('client_call_record_view')) {
		//access granted
	}
	else {
		echo "access denied";
		exit;
	}

//add multi-lingual support
	$language = new text;
	$text = $language->get();

//get posted data
	if (!$archive_request && is_array($_POST['client_call_records'])) {
		$action = $_POST['action'];
		$client_call_records = $_POST['client_call_records'];
	}

//process the http post data by action
	if (!$archive_request && $action != '' && is_array($client_call_records) && @sizeof($client_call_records) != 0) {
		switch ($action) {
			case 'delete':
				if (permission_exists('client_call_record_delete')) {
					$obj = new client_call_records;
					$obj->delete($client_call_records);
				}
				break;
		}

		header('Location: client_call_records.php');
		exit;
	}

//create token
	$object = new token;
	$token = $object->create($_SERVER['PHP_SELF']);

//include the header
	if ($archive_request) {
		$document['title'] = $text['title-call_detail_records_archive'];
	}
	else {
		$document['title'] = $text['title-call_detail_records'];
	}
	require_once "resources/header.php";

//client call record include
	$rows_per_page = ($_SESSION['domain']['paging']['numeric'] != '') ? $_SESSION['domain']['paging']['numeric'] : 50;
	require_once "client_call_records_inc.php";

//javascript to toggle export select box
	echo "<script language='javascript' type='text/javascript'>";
	echo "	var fade_speed = 400;";
	echo "	function toggle_select(select_id) {";
	echo "		$('#'+select_id).fadeToggle(fade_speed, function() {";
	echo "			document.getElementById(select_id).selectedIndex = 0;";
	echo "			document.getElementById(select_id).focus();";
	echo "		});";
	echo "	}";
	echo "</script>";

//show the content
	echo "<div class='action_bar' id='action_bar'>\n";
	echo "	<div class='heading'>";
	if ($archive_request) {
		echo "<b>".$text['title-call_detail_records_archive']."</b>";
	}
	else {
		echo "<b>".$text['title-call_detail_records']."</b>";
	}
	echo "</div>\n";
	echo "	<div class='actions'>\n";
	if (!$archive_request) {
		if (permission_exists('client_call_record_archive')) {
			echo button::create(['type'=>'button','label'=>$text['button-archive'],'icon'=>'archive','link'=>'client_call_records_archive.php'.($_REQUEST['show'] == 'all' ? '?show=all' : null)]);
		}
	}
	echo 		"<form id='frm_export' class='inline' method='post' action='client_call_records_export.php'>\n";
	if ($archive_request) {
		echo "	<input type='hidden' name='archive_request' value='true'>\n";
	}
	echo "		<input type='hidden' name='direction' value='".escape($direction)."'>\n";
	echo "		<input type='hidden' name='caller_name' value='".escape($caller_name)."'>\n";
	echo "		<input type='hidden' name='start_stamp_begin' value='".escape($start_stamp_begin)."'>\n";
	echo "		<input type='hidden' name='start_stamp_end' value='".escape($start_stamp_end)."'>\n";
	echo "		<input type='hidden' name='caller_id_number' value='".escape($caller_id_number)."'>\n";
	echo "		<input type='hidden' name='caller_destination' value='".escape($caller_destination)."'>\n";
	echo "		<input type='hidden' name='extension_uuid' value='".escape($extension_uuid)."'>\n";
	echo "		<input type='hidden' name='destination_number' value='".escape($destination_number)."'>\n";
	echo "		<input type='hidden' name='answer_stamp_begin' value='".escape($answer_stamp_begin)."'>\n";
	echo "		<input type='hidden' name='answer_stamp_end' value='".escape($answer_stamp_end)."'>\n";
	echo "		<input type='hidden' name='end_stamp_begin' value='".escape($end_stamp_begin)."'>\n";
	echo "		<input type='hidden' name='end_stamp_end' value='".escape($end_stamp_end)."'>\n";
	echo "		<input type='hidden' name='start_epoch' value='".escape($start_epoch)."'>\n";
	echo "		<input type='hidden' name='stop_epoch' value='".escape($stop_epoch)."'>\n";
	echo "		<input type='hidden' name='duration' value='".escape($duration)."'>\n";
	echo "		<input type='hidden' name='billsec' value='".escape($billsec)."'>\n";
	echo "		<input type='hidden' name='client_call_record_uuid' value='".escape($client_call_record_uuid)."'>\n";
	echo "		<input type='hidden' name='access_code' value='".escape($access_code)."'>\n";
	echo "		<input type='hidden' name='cost_center_id' value='".escape($cost_center_id)."'>\n";
	echo "		<input type='hidden' name='employee_id' value='".escape($employee_id)."'>\n";
	echo "		<input type='hidden' name='caller_name' value='".escape($caller_name)."'>\n";
	echo "		<input type='hidden' name='interpret_language' value='".escape($interpret_language)."'>\n";
	echo "		<input type='hidden' name='interpret_stamp_begin' value='".escape($interpret_stamp_begin)."'>\n";
	echo "		<input type='hidden' name='interpret_stamp_end' value='".escape($interpret_stamp_end)."'>\n";
	echo "		<input type='hidden' name='interpreter_id' value='".escape($interpreter_id)."'>\n";
	echo "		<input type='hidden' name='fusion_group_interpreter_id' value='".escape($fusion_group_interpreter_id)."'>\n";
	if (permission_exists('client_call_record_all') && $_REQUEST['show'] == 'all') {
		echo "	<input type='hidden' name='show' value='all'>\n";
	}
	if (is_array($_SESSION['ccr']['field'])) {
		foreach ($_SESSION['ccr']['field'] as $field) {
			$array = explode(",", $field);
			$field_name = $array[count($array) - 1];
			if (isset($_REQUEST[$field_name])) {
				echo "	<input type='hidden' name='".escape($field_name)."' value='".escape($$field_name)."'>\n";
			}
		}
	}
	if (isset($order_by)) {
		echo "	<input type='hidden' name='order_by' value='".escape($order_by)."'>\n";
		echo "	<input type='hidden' name='order' value='".escape($order)."'>\n";
	}
	if ($archive_request) {
		echo button::create(['type'=>'button','label'=>$text['button-back'],'icon'=>$_SESSION['theme']['button_icon_back'],'link'=>'client_call_records.php']);
	}
	echo button::create(['type'=>'button','label'=>$text['button-refresh'],'icon'=>'sync-alt','style'=>'margin-left: 15px;','onclick'=>'location.reload(true);']);

	if (permission_exists('client_call_record_export')) {
		echo button::create(['type'=>'button','label'=>$text['button-export'],'icon'=>$_SESSION['theme']['button_icon_export'],'onclick'=>"toggle_select('export_format'); this.blur();"]);
		echo 		"<select class='formfld' style='display: none; width: auto;' name='export_format' id='export_format' onchange=\"display_message('".$text['message-preparing_download']."'); toggle_select('export_format'); document.getElementById('frm_export').submit();\">";
		echo "			<option value='' disabled='disabled' selected='selected'>".$text['label-format']."</option>";
		if (permission_exists('client_call_record_export_csv')) {
			echo "			<option value='csv'>CSV</option>";
		}
		if (permission_exists('client_call_record_export_pdf')) {
			echo "			<option value='pdf'>PDF</option>";
		}
		echo "		</select>";
	}
	if ($_GET['show'] == 'all' && permission_exists('client_call_record_all')) {
		//do not show generate invoice button
	} elseif (!$archive_request && permission_exists('client_call_record_generate_invoice')) {
		echo button::create(['type'=>'submit','label'=>$text['button-generate_invoice'],'icon'=>$_SESSION['theme']['button_icon_copy'],'style'=>'margin-right: 15px;','name'=>'export_format','value'=>'invoice','onclick'=>"display_message('".$text['message-preparing_download']."');"]);
	}
	if (permission_exists('client_call_record_add')) {
		echo button::create(['type'=>'button','label'=>$text['button-add'],'icon'=>$_SESSION['theme']['button_icon_add'],'id'=>'btn_add_client_call_record','onclick'=>"new bootstrap.Modal(document.getElementById('modal_add_client_call_record'), {}).show();document.body.appendChild(document.getElementById('modal_add_client_call_record'));"]);
		echo "<div class='modal fade' id='modal_add_client_call_record' tabindex='-1' role='dialog' aria-labelledby='addClientCallRecord' aria-hidden='true'>\n";
		echo "  <div class='modal-dialog modal-xl' role='document'>\n";
		echo "    <div class='modal-content'>\n";
		echo "      <div class='modal-header'>\n";
		echo "        <h5 class='modal-title' id='addClientCallRecord'>Add Client Call Record</h5>\n";
		echo "        <button type='button' class='close' data-dismiss='modal' aria-label='Close'>\n";
		echo "          <span aria-hidden='true'>&times;</span>\n";
		echo "        </button>\n";
		echo "      </div>\n";
		echo "      <div class='modal-body'>\n";
		echo "        <div id='cdr_list'></div>\n";
		echo "      </div>\n";
		echo "      <div class='modal-footer'>\n";
		echo "        <button type='button' class='btn btn-secondary' data-dismiss='modal'>Close</button>\n";
		echo "      </div>\n";
		echo "    </div>\n";
		echo "  </div>\n";
		echo "</div>\n";
	}
	?>
	<script type="text/javascript">
		$('#modal_add_client_call_record').on('shown.bs.modal', function (e) {
			$('#cdr_list').html(''); // init empty div
			//fetch cdr list
			$.get( 'resources/cdr.php', function( response ) {
				$('#cdr_list').html(response);
			});
		});
		//ajax paging call records
		$(document).on('click', '#cdr_list #paging_controls a', function(e) { 
			e.preventDefault();
			var page_url = $(this).attr('href');
			$(this).find('button').html('<span class="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span>Loading...');
			$.get(page_url, function( response ) {
				$('#cdr_list').html(response);
			});
		});
		//add ccr execute
		$(document).on('click', '.btn-ccr-add', function(e) {
			var btn_this = $(this);
			console.log(btn_this.data('uuid'));
			$.ajax({
				type: 'POST',
				url: 'resources/cdr.php',
				data: {
					action: 'add_ccr',
					xml_cdr_uuid: btn_this.data('uuid')
				},
				dataType: 'html',
				success: function (data, status, xhr) {   // success callback function
					$('#cdr_list').html(data);
					display_message('Client Call Record created.', 'positive');
				},
				error: function (jqXhr, textStatus, errorMessage) { // error callback
					display_message('Error: ' + errorMessage, 'negative');
				}
			});
		});
	</script>
	<?php
	if (!$archive_request && permission_exists('client_call_record_delete') && $result) {
		echo button::create(['type'=>'button','label'=>$text['button-delete'],'icon'=>$_SESSION['theme']['button_icon_delete'],'id'=>'btn_delete','name'=>'btn_delete','style'=>'display: none;','onclick'=>"modal_open('modal-delete','btn_delete');"]);
		echo modal::create(['id'=>'modal-delete','type'=>'delete','actions'=>button::create(['type'=>'button','label'=>$text['button-continue'],'icon'=>'check','id'=>'btn_delete','style'=>'float: right; margin-left: 15px;','collapse'=>'never','onclick'=>"modal_close(); list_action_set('delete'); list_form_submit('form_list');"])]);
	}
	if (permission_exists('client_call_record_all') && $_REQUEST['show'] !== 'all') {
		echo button::create(['type'=>'button','label'=>$text['button-show_all'],'icon'=>$_SESSION['theme']['button_icon_all'],'link'=>'?show=all']);
	}
	if ($paging_controls_mini != '') {
		echo 	"<span style='margin-left: 15px;'>".$paging_controls_mini."</span>";
	}
	echo "		</form>\n";
	echo "	</div>\n";
	echo "	<div style='clear: both;'></div>\n";
	echo "</div>\n";

	echo $text['description']." \n";
	echo $text['description2']." \n";
	echo $text['description-3']." \n";
	echo $text['description-4']." \n";
	echo "<br /><br />\n";

//basic search of call detail records
	if (permission_exists('client_call_record_search')) {
		echo "<form name='frm' id='frm' method='get'>\n";

		echo "<div class='form_grid'>\n";

		if (permission_exists('client_call_record_search_extension')) {
			$sql = "select extension_uuid, extension, number_alias from v_extensions ";
			$sql .= "where domain_uuid = :domain_uuid ";
			if (!permission_exists('client_call_record_domain') && is_array($extension_uuids) && @sizeof($extension_uuids != 0)) {
				$sql .= "and extension_uuid in ('".implode("','",$extension_uuids)."') "; //only show the user their extensions
			}
			$sql .= "order by extension asc, number_alias asc ";
			$parameters['domain_uuid'] = $_SESSION['domain_uuid'];
			$database = new database;
			$result_e = $database->select($sql, $parameters, 'all');
			echo "	<div class='form_set'>\n";
			echo "		<div class='label'>\n";
			echo "			".$text['label-extension']."\n";
			echo "		</div>\n";
			echo "		<div class='field'>\n";
			echo "			<select class='formfld' name='extension_uuid' id='extension_uuid'>\n";
			echo "				<option value=''></option>";
			if (is_array($result_e) && @sizeof($result_e) != 0) {
				foreach ($result_e as &$row) {
					$selected = ($row['extension_uuid'] == $extension_uuid) ? "selected" : null;
					echo "		<option value='".escape($row['extension_uuid'])."' ".escape($selected).">".((is_numeric($row['extension'])) ? escape($row['extension']) : escape($row['number_alias'])." (".escape($row['extension']).")")."</option>";
				}
			}
			echo "			</select>\n";
			echo "		</div>\n";
			echo "	</div>\n";
			unset($sql, $parameters, $result_e, $row, $selected);
		}
		/*
		if (permission_exists('client_call_record_search_caller_id')) {
			echo "	<div class='form_set'>\n";
			echo "		<div class='label'>\n";
			echo "			".$text['label-caller_id']."\n";
			echo "		</div>\n";
			echo "		<div class='field no-wrap'>\n";
			echo "			<input type='text' class='formfld' name='caller_id_name' style='min-width: 115px; width: 115px;' placeholder=\"".$text['label-name']."\" value='".escape($caller_id_name)."'>\n";
			echo "			<input type='text' class='formfld' name='caller_id_number' style='min-width: 115px; width: 115px;' placeholder=\"".$text['label-number']."\" value='".escape($caller_id_number)."'>\n";
			echo "		</div>\n";
			echo "	</div>\n";
		}
		*/
		if (permission_exists('client_call_record_search_start_range')) {
			echo "	<div class='form_set'>\n";
			echo "		<div class='label'>\n";
			echo "			".$text['label-start_range']."\n";
			echo "		</div>\n";
			echo "		<div class='field no-wrap'>\n";
			echo "			<input type='text' class='formfld datetimepicker' data-toggle='datetimepicker' data-target='#interpret_stamp_begin' onblur=\"$(this).datetimepicker('hide');\" style='min-width: 115px; width: 115px;' name='interpret_stamp_begin' id='interpret_stamp_begin' placeholder='".$text['label-from']."' value='".escape($interpret_stamp_begin)."' autocomplete='off'>\n";
			echo "			<input type='text' class='formfld datetimepicker' data-toggle='datetimepicker' data-target='#interpret_stamp_end' onblur=\"$(this).datetimepicker('hide');\" style='min-width: 115px; width: 115px;' name='interpret_stamp_end' id='interpret_stamp_end' placeholder='".$text['label-to']."' value='".escape($interpret_stamp_end)."' autocomplete='off'>\n";
			echo "		</div>\n";
			echo "	</div>\n";
		}
		if (permission_exists('client_call_record_search_caller_destination')) {
			echo "	<div class='form_set'>\n";
			echo "		<div class='label'>\n";
			echo "			".$text['label-caller_destination']."\n";
			echo "		</div>\n";
			echo "		<div class='field'>\n";
			echo "			<input type='text' class='formfld' name='caller_destination' value='".escape($caller_destination)."'>\n";
			echo "		</div>\n";
			echo "	</div>\n";
		}
		if (permission_exists('client_call_record_search_destination')) {
			echo "	<div class='form_set'>\n";
			echo "		<div class='label'>\n";
			echo "			".$text['label-destination']."\n";
			echo "		</div>\n";
			echo "		<div class='field'>\n";
			echo "			<input type='text' class='formfld' name='destination_number' id='destination_number' value='".escape($destination_number)."'>\n";
			echo "		</div>\n";
			echo "	</div>\n";
		}
		if (permission_exists('client_call_record_search_access_code')) {
			echo "	<div class='form_set'>\n";
			echo "		<div class='label'>\n";
			echo "			".$text['label-access_code']."\n";
			echo "		</div>\n";
			echo "		<div class='field'>\n";
			echo "			<input type='text' class='formfld' name='access_code' id='access_code' value='".escape($access_code)."'>\n";
			echo "		</div>\n";
			echo "	</div>\n";
		}
		if (permission_exists('client_call_record_search_interpret_language')) {
			echo "	<div class='form_set'>\n";
			echo "		<div class='label'>\n";
			echo "			".$text['label-interpret_language']."\n";
			echo "		</div>\n";
			echo "		<div class='field'>\n";
			echo "			<select name='interpret_language' class='formfld'>\n";
			echo "				<option value=''></option>\n";
			//interpretation languages
			$iso = new ISO639;
			$interpret_languages_list = $iso->allLanguages();
			$interpret_languages_list = array_column($interpret_languages_list, 1);
			foreach ($interpret_languages_list as $interpret_language_name) {
				$selected = ($interpret_language == $interpret_language_name) ? "selected='selected'" : null;
				$interpret_language_label = ucwords(strtolower(str_replace("_", " ", $interpret_language_name)));
				echo "			<option value='".escape($interpret_language_name)."' ".$selected.">".escape($interpret_language_label)."</option>\n";
			}
			echo "			</select>\n";
			echo "		</div>\n";
			echo "	</div>\n";
		}
		if (permission_exists('client_call_record_search_interpreter_id')) {
			echo "	<div class='form_set'>\n";
			echo "		<div class='label'>\n";
			echo "			".$text['label-interpreter_id']."\n";
			echo "		</div>\n";
			echo "		<div class='field'>\n";
			echo "			<input type='text' class='formfld' name='interpreter_id' id='interpreter_id' value='".escape($interpreter_id)."'>\n";
			echo "		</div>\n";
			echo "	</div>\n";
		}
		if (permission_exists('client_call_record_search_order')) {
			echo "	<div class='form_set'>\n";
			echo "		<div class='label'>\n";
			echo "			".$text['label-order']."\n";
			echo "		</div>\n";
			echo "		<div class='field no-wrap'>\n";
			echo "			<select name='order_by' class='formfld'>\n";
			if (permission_exists('client_call_record_extension')) {
				echo "			<option value='extension' ".($order_by == 'extension' ? "selected='selected'" : null).">".$text['label-extension']."</option>\n";
			}
			if (permission_exists('client_call_record_all')) {
				echo "			<option value='domain_name' ".($order_by == 'domain_name' ? "selected='selected'" : null).">".$text['label-domain']."</option>\n";
			}
			if (permission_exists('client_call_record_caller_id_name')) {
				echo "			<option value='caller_name' ".($order_by == 'caller_name' ? "selected='selected'" : null).">".$text['label-caller_name']."</option>\n";
			}
			if (permission_exists('client_call_record_caller_id_number')) {
				echo "			<option value='caller_id_number' ".($order_by == 'caller_id_number' ? "selected='selected'" : null).">".$text['label-caller_id_number']."</option>\n";
			}
			if (permission_exists('client_call_record_caller_destination')) {
				echo "			<option value='caller_destination' ".($order_by == 'caller_destination' ? "selected='selected'" : null).">".$text['label-caller_destination']."</option>\n";
			}
			if (permission_exists('client_call_record_destination')) {
				echo "			<option value='destination_number' ".($order_by == 'destination_number' ? "selected='selected'" : null).">".$text['label-destination']."</option>\n";
			}
			if (permission_exists('client_call_record_start')) {
				echo "			<option value='start_stamp' ".($order_by == 'start_stamp' || $order_by == '' ? "selected='selected'" : null).">".$text['label-start']."</option>\n";
			}
			echo "			</select>\n";
			echo "			<select name='order' class='formfld'>\n";
			echo "				<option value='desc' ".($order == 'desc' ? "selected='selected'" : null).">".$text['label-descending']."</option>\n";
			echo "				<option value='asc' ".($order == 'asc' ? "selected='selected'" : null).">".$text['label-ascending']."</option>\n";
			echo "			</select>\n";
			echo "		</div>\n";
			echo "	</div>\n";
		}

		echo "</div>\n";

		button::$collapse = false;
		echo "<div style='float: right; padding-top: 15px; margin-left: 20px; white-space: nowrap;'>";
		if (permission_exists('client_call_record_all') && $_REQUEST['show'] == 'all') {
			echo "<input type='hidden' name='show' value='all'>\n";
		}
		echo button::create(['label'=>$text['button-reset'],'icon'=>$_SESSION['theme']['button_icon_reset'],'type'=>'button','link'=>($archive_request ? 'client_call_records_archive.php' : 'client_call_records.php')]);
		echo button::create(['label'=>$text['button-search'],'icon'=>$_SESSION['theme']['button_icon_search'],'type'=>'submit','id'=>'btn_save','name'=>'submit']);
		echo "</div>\n";
		echo "<div style='font-size: 85%; padding-top: 12px; margin-bottom: 40px;'>".$text['description_search']."</div>\n";

		echo "</form>";
	}

//mod paging parameters for inclusion in column sort heading links
	$param = substr($param, 1); //remove leading '&'
	$param = substr($param, 0, strrpos($param, '&order_by=')); //remove trailing order by

//show the results
	echo "<form id='form_list' method='post'>\n";
	echo "<input type='hidden' id='action' name='action' value=''>\n";

	echo "<table class='list'>\n";
	echo "<tr class='list-header'>\n";
	$col_count = 0;
	if (!$archive_request && permission_exists('client_call_record_delete')) {
		echo "	<th class='checkbox'>\n";
		echo "		<input type='checkbox' id='checkbox_all' name='checkbox_all' onclick='list_all_toggle(); checkbox_on_change(this);' ".($result ?: "style='visibility: hidden;'").">\n";
		echo "	</th>\n";
		$col_count++;
	}

//column headings
	if (permission_exists('client_call_record_extension')) {
		echo "<th class='shrink'>".$text['label-ext']."</th>\n";
		$col_count++;
	}
	if (permission_exists('client_call_record_all') && $_REQUEST['show'] == "all") {
		echo "<th>".$text['label-domain']."</th>\n";
		$col_count++;
	}
	if (permission_exists('client_call_record_caller_id_name')) {
		echo "<th class='hide-md-dn' style='min-width: 90px;'>".$text['label-caller_name']."</th>\n";
		$col_count++;
	}
	if (permission_exists('client_call_record_caller_id_number')) {
		echo "<th>".$text['label-caller_id_number']."</th>\n";
		$col_count++;
	}
	if (permission_exists('client_call_record_caller_destination')) {
		echo "<th class='no-wrap hide-md-dn'>".$text['label-caller_destination']."</th>\n";
		$col_count++;
	}
	if (permission_exists('client_call_record_destination')) {
		echo "<th class='shrink'>".$text['label-destination']."</th>\n";
		$col_count++;
	}
	if (permission_exists('client_call_record_access_code')) {
		echo "<th class='no-wrap'>".$text['label-access_code']."</th>\n";
		$col_count++;
	}
	if (permission_exists('client_call_record_interpret_language')) {
		echo "<th class='shrink'>".$text['label-interpret_language']."</th>\n";
		$col_count++;
	}
	if (permission_exists('client_call_record_interpreter_id')) {
		echo "<th class='shrink'>".$text['label-interpreter_id']."</th>\n";
		$col_count++;
	}
	if (permission_exists('client_call_record_start')) {
		echo "<th class='center shrink'>".$text['label-date']."</th>\n";
		echo "<th class='center shrink hide-md-dn'>".$text['label-time']."</th>\n";
		$col_count += 2;
	}
	if (permission_exists('client_call_record_interpret_duration')) {
		echo "<th class='shrink'>".$text['label-interpret_duration']."</th>\n";
		$col_count++;
	}
	/*
	if (permission_exists('client_call_record_charge')) {
		echo "<th class='shrink'>".$text['label-charge']."</th>\n";
		$col_count++;
	}
	*/
	echo "</tr>\n";

//show results
	if (is_array($result)) {

		//loop through the results
			$x = 0;
			foreach ($result as $index => $row) {

				//set an empty content variable
					$content = '';

					if (permission_exists('client_call_record_edit')) {
						$list_row_url = "client_call_record_edit.php?id=".urlencode($row['client_call_record_uuid']);
					}
					$content .= "<tr class='list-row' href='".$list_row_url."'>\n";
					if (!$archive_request && permission_exists('client_call_record_delete')) {
						$content .= "	<td class='checkbox middle'>\n";
						$content .= "		<input type='checkbox' name='client_call_records[$x][checked]' id='checkbox_".$x."' value='true' onclick=\"checkbox_on_change(this); if (!this.checked) { document.getElementById('checkbox_all').checked = false; }\">\n";
						$content .= "		<input type='hidden' name='client_call_records[$x][uuid]' value='".escape($row['client_call_record_uuid'])."' />\n";
						$content .= "	</td>\n";
					}

				//extension
					if (permission_exists('client_call_record_extension')) {
						$content .= "	<td class='middle'>".$row['extension']."</td>\n";
					}
				//domain name
					if (permission_exists('client_call_record_all') && $_REQUEST['show'] == "all") {
						$content .= "	<td class='middle'>".$row['domain_name']."</td>\n";
					}
				//caller name
					if (permission_exists('client_call_record_caller_id_name')) {
						$content .= "	<td class='middle overflow hide-md-dn' title=\"".escape($row['caller_name'])."\">".escape($row['caller_name'])."</td>\n";
					}
				//source
					if (permission_exists('client_call_record_caller_id_number')) {
						$content .= "	<td class='middle no-wrap'>";
						if (is_numeric($row['caller_id_number'])) {
							$content .= "		".escape(format_phone(substr($row['caller_id_number'], 0, 20))).' ';
						}
						else {
							$content .= "		".escape(substr($row['caller_id_number'], 0, 20)).' ';
						}
						$content .= "	</td>\n";
					}
				//caller destination
					if (permission_exists('client_call_record_caller_destination')) {
						$content .= "	<td class='middle no-wrap hide-md-dn'>";
						if (is_numeric($row['caller_destination'])) {
							$content .= "		".format_phone(escape(substr($row['caller_destination'], 0, 20))).' ';
						}
						else {
							$content .= "		".escape(substr($row['caller_destination'], 0, 20)).' ';
						}
						$content .= "	</td>\n";
					}
				//destination
					if (permission_exists('client_call_record_destination')) {
						$content .= "	<td class='middle no-wrap'>";
						if (is_numeric($row['destination_number'])) {
							$content .= format_phone(escape(substr($row['destination_number'], 0, 20)))."\n";
						}
						else {
							$content .= escape(substr($row['destination_number'], 0, 20))."\n";
						}
						$content .= "	</td>\n";
					}
				//client access code
					if (permission_exists('client_call_record_access_code')) {
						$content .= "	<td class='middle no-wrap'>".escape($row['access_code'])."</td>\n";
					}
				//interpret language
					if (permission_exists('client_call_record_interpret_language')) {
						$content .= "	<td class='middle no-wrap'>".escape($row['interpret_language'])."</td>\n";
					}
				//interpreter id
					if (permission_exists('client_call_record_interpreter_id')) {
						$content .= "	<td class='middle no-wrap'>".escape($row['interpreter_id'])."</td>\n";
					}
				//start
					if (permission_exists('client_call_record_start')) {
						$content .= "	<td class='middle right no-wrap'>".$row['start_date_formatted']."</td>\n";
						$content .= "	<td class='middle right no-wrap hide-md-dn'>".$row['start_time_formatted']."</td>\n";
					}
				//interpret duration
					if (permission_exists('client_call_record_interpret_duration')) {
						$content .= "	<td class='middle center hide-sm-dn'>".gmdate("G:i:s", $row['interpret_duration'])."</td>\n";
					}
				/*
				//interpret charge
					if (permission_exists('client_call_record_charge')) {
						$languageSet = (new ISO639)->languageSetByLanguage($row['interpret_language']);
						$minutes = (($row['interpret_duration'] % 60) > 0) ? floor($row['interpret_duration'] / 60) + 1 : ceil($row['interpret_duration'] / 60);
						$charge = $row[strtolower($languageSet)] * $minutes;
						$charge_amount = (strlen($row[strtolower($languageSet)]) > 0) ? "$".number_format($charge, 2) : "";
						$content .= "	<td class='middle center hide-sm-dn'>".$charge_amount."</td>\n";
					}
				*/
					$content .= "</tr>\n";
				//show the leg b only to those with the permission
					if ($row['leg'] == 'a') {
						echo $content;
					}
					else if ($row['leg'] == 'b' && permission_exists('client_call_record_b_leg')) {
						echo $content;
					}
					unset($content);

				$x++;
			}
			unset($sql, $result, $row_count);
	}

	echo "</table>\n";
	echo "<br />\n";
	echo "<div align='center'>".$paging_controls."</div>\n";
	echo "<input type='hidden' name='".$token['name']."' value='".$token['hash']."'>\n";
	echo "</form>\n";

//store last search/sort query parameters in session
	$_SESSION['client_call_record']['last_query'] = $_SERVER["QUERY_STRING"];

//show the footer
	require_once "resources/footer.php";

?>
