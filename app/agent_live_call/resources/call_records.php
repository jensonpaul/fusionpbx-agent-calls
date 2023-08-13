<?php

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

//call record include
	$rows_per_page = ($_SESSION['domain']['paging']['numeric'] != '') ? $_SESSION['domain']['paging']['numeric'] : 50;
	require_once "../ccr_inc.php";

//show the content
	echo "<div class='heading'>";
	echo "<b>".$text['title-call_detail_records']."</b>";
	echo "</div>\n";

//show the results
	echo "<table class=''>\n";
	echo "<tr class='list-header'>\n";

//column headings
	echo "<th class='shrink'>&nbsp;</th>\n";
	echo "<th class='shrink'>".$text['label-ext']."</th>\n";
	$col_count++;
	if (permission_exists('client_call_record_all') && $_REQUEST['show'] == "all") {
		echo "<th>".$text['label-domain']."</th>\n";
	}
	echo "<th class='no-wrap hide-md-dn' style='min-width: 90px;'>".$text['label-caller_id_name']."</th>\n";
	echo "<th class='no-wrap'>".$text['label-caller_id_number']."</th>\n";
	echo "<th class='no-wrap hide-md-dn'>".$text['label-caller_destination']."</th>\n";
	echo "<th class=''>".$text['label-destination']."</th>\n";
	echo "<th class='no-wrap'>".$text['label-access_code']."</th>\n";
	echo "<th class='no-wrap'>".$text['label-cost_center_id']."</th>\n";
	echo "<th class='no-wrap'>Employee ID/Name</th>\n";
	echo "<th class=''>".$text['label-interpret_language']."</th>\n";
	echo "<th class='no-wrap'>".$text['label-interpreter_id']."</th>\n";
	echo "<th class='center shrink'>".$text['label-date']."</th>\n";
	echo "<th class='center shrink hide-md-dn'>".$text['label-time']."</th>\n";
	echo "<th class='center shrink'>".$text['label-duration']."</th>\n";
	echo "<th class='center shrink'>Interpreted?</th>\n";
	echo "</tr>\n";

//show results
	if (is_array($result)) {

		//determine if theme images exist
			$theme_image_path = $_SERVER["DOCUMENT_ROOT"]."/themes/".$_SESSION['domain']['template']['name']."/images/";
			$theme_cdr_images_exist = (
				file_exists($theme_image_path."icon_cdr_inbound_answered.png") &&
				file_exists($theme_image_path."icon_cdr_inbound_voicemail.png") &&
				file_exists($theme_image_path."icon_cdr_inbound_cancelled.png") &&
				file_exists($theme_image_path."icon_cdr_inbound_failed.png") &&
				file_exists($theme_image_path."icon_cdr_outbound_answered.png") &&
				file_exists($theme_image_path."icon_cdr_outbound_cancelled.png") &&
				file_exists($theme_image_path."icon_cdr_outbound_failed.png") &&
				file_exists($theme_image_path."icon_cdr_local_answered.png") &&
				file_exists($theme_image_path."icon_cdr_local_voicemail.png") &&
				file_exists($theme_image_path."icon_cdr_local_cancelled.png") &&
				file_exists($theme_image_path."icon_cdr_local_failed.png")
				) ? true : false;

		//loop through the results
			$x = 0;
			foreach ($result as $index => $row) {

				//if call cancelled, show the ring time, not the bill time.
					$seconds = $row['hangup_cause'] == "ORIGINATOR_CANCEL" ? $row['duration'] : round(($row['billmsec'] / 1000), 0, PHP_ROUND_HALF_UP);

				//set an empty content variable
					$content = '';
					$content .= "<tr class='list-row'>\n";
				//determine call result and appropriate icon
					$content .= "<td class='middle'>\n";
					if ($theme_cdr_images_exist) {
						if ($row['direction'] == 'inbound' || $row['direction'] == 'local') {
							if ($row['answer_stamp'] != '' && $row['bridge_uuid'] != '') { $call_result = 'answered'; }
							else if ($row['answer_stamp'] != '' && $row['bridge_uuid'] == '') { $call_result = 'voicemail'; }
							else if ($row['answer_stamp'] == '' && $row['bridge_uuid'] == '' && $row['sip_hangup_disposition'] != 'send_refuse') { $call_result = 'cancelled'; }
							else { $call_result = 'failed'; }
						}
						else if ($row['direction'] == 'outbound') {
							if ($row['answer_stamp'] != '' && $row['bridge_uuid'] != '') { $call_result = 'answered'; }
							else if ($row['hangup_cause'] == 'NORMAL_CLEARING') { $call_result = 'answered'; }
							else if ($row['answer_stamp'] == '' && $row['bridge_uuid'] != '') { $call_result = 'cancelled'; }
							else { $call_result = 'failed'; }
						}
						if (strlen($row['direction']) > 0) {
							$image_name = "icon_cdr_" . $row['direction'] . "_" . $call_result;
							if ($row['leg'] == 'b') {
								$image_name .= '_b';
							}
							$image_name .= ".png";
							$content .= "<img src='".PROJECT_PATH."/themes/".$_SESSION['domain']['template']['name']."/images/".escape($image_name)."' width='16' style='border: none; cursor: help;' title='".$text['label-'.$row['direction']].": ".$text['label-'.$call_result]. ($row['leg']=='b'?'(b)':'') . "'>\n";
						}
					}
					else { $content .= "&nbsp;"; }
					$content .= "</td>\n";
				//extension
					$content .= "	<td class='middle'>".$row['extension']."</td>\n";
				//domain name
					if (permission_exists('client_call_record_all') && $_REQUEST['show'] == "all") {
						$content .= "	<td class='middle'>".$row['domain_name']."</td>\n";
					}
				//caller id name
					$content .= "	<td class='middle overflow hide-md-dn'>".escape($row['caller_id_name'])."</td>\n";
				//source
					$content .= "	<td class='middle no-wrap'>";
					if (is_numeric($row['caller_id_number'])) {
						$content .= "		".escape(format_phone(substr($row['caller_id_number'], 0, 20))).' ';
					}
					else {
						$content .= "		".escape(substr($row['caller_id_number'], 0, 20)).' ';
					}
					$content .= "	</td>\n";
				//caller destination
					$content .= "	<td class='middle no-wrap hide-md-dn'>";
					if (is_numeric($row['caller_destination'])) {
						$content .= "		".format_phone(escape(substr($row['caller_destination'], 0, 20))).' ';
					}
					else {
						$content .= "		".escape(substr($row['caller_destination'], 0, 20)).' ';
					}
					$content .= "	</td>\n";
				//destination
					$content .= "	<td class='middle no-wrap'>";
					if (is_numeric($row['destination_number'])) {
						$content .= format_phone(escape(substr($row['destination_number'], 0, 20)))."\n";
					}
					else {
						$content .= escape(substr($row['destination_number'], 0, 20))."\n";
					}
					$content .= "	</td>\n";
				//client access code
					$content .= "	<td class='middle no-wrap'>".escape($row['access_code'])."</td>\n";
				//cost center id
					$content .= "	<td class='middle no-wrap'>".escape($row['cost_center_id'])."</td>\n";
				//employee id or name
					$content .= "	<td class='middle no-wrap'>".escape($row['employee_id_or_name'])."</td>\n";
				//interpret language
					$content .= "	<td class='middle no-wrap'>".escape($row['interpret_language'])."</td>\n";
				//interpreter id
					$content .= "	<td class='middle no-wrap'>".escape($row['interpreter_id'])."</td>\n";
				//start
					$content .= "	<td class='middle center no-wrap'>".$row['start_date_formatted']."</td>\n";
					$content .= "	<td class='middle center no-wrap hide-md-dn'>".$row['start_time_formatted']."</td>\n";
				//duration
					$content .= "	<td class='middle center hide-sm-dn'>".gmdate("G:i:s", $seconds)."</td>\n";
				//is interpret or not
					if (!empty($row['interpret_stamp_begin'])) {
						$content .= "	<td class='middle center hide-sm-dn'><i class='fa fa-check-circle text-success'></i></td>\n";
					} else {
						$content .= "	<td class='middle center hide-sm-dn'>&nbsp;</td>\n";
					}
					$content .= "</tr>\n";
				//display content
					echo $content;
					unset($content);

				$x++;
			}
			unset($sql, $result, $row_count);
	}

	echo "</table>\n";
	echo "<br />\n";
	echo "<div align='center' id='paging_controls'>".$paging_controls."</div>\n";

?>

<script type="text/javascript">
//prevent custom page number favoring ajax paging (bypass default paging behavior)
	/*
	$(document).on('keypress', '#paging_page_num', function(e){ 
		e.preventDefault();
		e.stopPropagation();
		e.stopImmediatePropagation();
	});
	$('#paging_page_num').attr('onkeypress','');
	*/
	var paging_page_num = document.getElementById('paging_page_num');
	if (paging_page_num) {
		paging_page_num.setAttribute('onkeypress', '');
		paging_page_num.onkeypress = function(evt) {
			evt = evt || window.event;
			if (evt.preventDefault) {
				evt.preventDefault();
			} else {
				evt.returnValue = false;
			}
		};
	}
</script>
