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
	$text = $language->get();

//set the title
	$document['title'] = $text['title-agent_live_call'];

//include the header
	require_once "resources/header.php";

?>

<!-- autocomplete for contact lookup -->
<link rel="stylesheet" type="text/css" href="<?php echo PROJECT_PATH; ?>/resources/jquery/jquery-ui.min.css">
<script language="JavaScript" type="text/javascript" src="<?php echo PROJECT_PATH; ?>/resources/jquery/jquery-ui.min.js"></script>
<script type="text/javascript">

<?php
//determine refresh rate
$refresh_default = 1500; //milliseconds
$refresh = is_numeric($_SESSION['agent_live_call']['refresh']['numeric']) ? $_SESSION['agent_live_call']['refresh']['numeric'] : $refresh_default;
if ($refresh >= 0.5 && $refresh <= 120) { //convert seconds to milliseconds
	$refresh = $refresh * 1000;
}
else if ($refresh < 0.5 || ($refresh > 120 && $refresh < 500)) {
	$refresh = $refresh_default; //use default
}
else {
	//>= 500, must be milliseconds
}
unset($refresh_default);
?>

<?php
//interpretation languages
$iso = new ISO639;
$interpret_languages_list = $iso->allLanguages();
$interpret_languages_list = array_column($interpret_languages_list, 1);
$option_list = "";
foreach ($interpret_languages_list as $interpret_language_name) {
	$interpret_language_name = ucwords($interpret_language_name);
	$option_list .= '<option value="' . $interpret_language_name . '">' . $interpret_language_name . '</option>';
}
?>

//update call length of each call every second
	var call_length_interval_id = window.setInterval(function(){
		$('#agent_live_call .op_call_info').each(function() {
			var current_call_length = $(this).text();
			var ss = current_call_length.split(":");
			var dt = new Date();
			dt.setHours(ss[0]);
			dt.setMinutes(ss[1]);
			dt.setSeconds(ss[2]);
			var dt2 = new Date(dt.valueOf() + 1000);
			var ts = dt2.toTimeString().split(" ")[0];
			$(this).html(ts);
		});
	}, 1000);

//active calls list
	function list_active_calls(call_list) {

		var option_list = '<?php echo $option_list; ?>';

		var active_calls_on_screen = $('#agent_live_call').find('[id^=client-details-form-]').map(function() {
			return $(this).attr('id').replace('client-details-form-', '');
		}).toArray();
		active_calls_on_screen = active_calls_on_screen.filter(item => item);

		var active_calls_incoming = [];
		if ( call_list.length != 0 ) {
			call_list = JSON.parse(call_list);
			active_calls_incoming = Object.keys(call_list).map(function( key ) {
				return call_list[key].call_uuid;
			});
			active_calls_incoming = active_calls_incoming.filter(item => item);
		}

		var calls_to_remove = active_calls_on_screen.filter(x => !active_calls_incoming.includes(x));

		$.each(calls_to_remove, function( key, value ) {
			$('#'+value).fadeOut(1500);
			setTimeout(() => {
				$('#'+value).remove();
			}, 1500);
		});

		$.each( call_list, function( key, value ) {

			if (value.call_uuid.length === 0 || value.call_uuid === undefined || value.call_uuid === false || value.call_uuid === null) {
				//return false; // this is equivalent of 'break' for jQuery loop
				//return;       // this is equivalent of 'continue' for jQuery loop
				return;
			}

			var call_uuid = value.call_uuid;
			var style = value.style && value.style.length > 0 ? value.style : '';
			var call_number = value.call_number && value.call_number.length > 0 ? value.call_number : '';
			var call_length = value.call_length && value.call_length.length > 0 ? value.call_length : '';
			var destination = value.destination && value.destination.length > 0 ? value.destination : '';
			var interpret_stamp_begin = value.interpret_stamp_begin && value.interpret_stamp_begin.length > 0 ? value.interpret_stamp_begin : '';
			var label_start_timestamp = value.label_start_timestamp && value.label_start_timestamp.length > 0 ? value.label_start_timestamp : '';

			var access_code = value.access_code && value.access_code.length > 0 ? value.access_code : '';
			var cost_center_id = value.cost_center_id && value.cost_center_id.length > 0 ? value.cost_center_id : '';
			var employee_id_or_name = value.employee_id_or_name && value.employee_id_or_name.length > 0 ? value.employee_id_or_name : '';
			var interpret_language = value.interpret_language && value.interpret_language.length > 0 ? value.interpret_language : '';
			var interpreter_id = value.interpreter_id && value.interpreter_id.length > 0 ? value.interpreter_id : '';
			var fusion_group_interpreter_id = value.fusion_group_interpreter_id && value.fusion_group_interpreter_id.length > 0 ? value.fusion_group_interpreter_id : '';

			//create call div if not exists
			//else update existing call div
			if ($.inArray(call_uuid, active_calls_on_screen) !== -1) {
				// update div
				$('#'+call_uuid + ', #' + call_uuid + ' .op_ext').removeClass('op_ext op_state_ringing op_state_active op_state_held').addClass(style);
				//$('#'+call_uuid + ' .op_call_info').html(call_length);
			} else {
				// create div
				var block = '';

				block += '<div id="' + call_uuid + '" class="w-auto mb-3 float-none active-client-call ' + style + '">';
				block += '	<table class="h-auto ' + style + '">';
				block += '		<tbody>';
				block += '			<tr>';
				block += '				<td class="op_ext_info ' + style + '">';
				block += '		  			<form autocomplete="off" id="client-details-form-' + call_uuid + '" ';
				block += '		  				onsubmit="client_details_form_submit(event, &apos;' + call_uuid + '&apos;)">';
				block += '						<div class="form-row">';
				block += '							<div class="">';
				block += '								<span class="ml-10 mr-5 op_user_info">';
				block += '									<strong class="strong">' + call_number + '</strong>';
				block += '								</span><br>';
				block += '								<span class="ml-10 mr-10 op_call_info">' + call_length + '</span><br>';
				block += '							</div>';
				block += '							<div class="mb-auto form-group col-md-1">';
				block += '								<input required type="number" class="form-control form-control-sm" id="client_access_code_input" ';
				block += '									name="access_code" value="' + access_code + '" autocomplete="off" placeholder="Access Code *" ';
				block += '									onkeyup="validate_client_access_code(this);">';
				block += '								<span id="client_access_code_validate_status_text" class="form-text"></span>';
				block += '							</div>';
				block += '							<div class="mb-auto form-group col-md-1">';
				block += '								<input type="text" class="form-control form-control-sm" name="cost_center_id" ';
				block += '									value="' + cost_center_id + '" autocomplete="off" placeholder="Cost Center ID">';
				block += '							</div>';
				block += '							<div class="mb-auto form-group col-md-2">';
				block += '								<input type="text" class="form-control form-control-sm" name="employee_id_or_name" ';
				block += '									value="' + employee_id_or_name + '" autocomplete="off" placeholder="Employee ID / Representative Name">';
				block += '							</div>';
				block += '							<div class="mb-auto form-group col-md-2">';
				block += '								<select required class="form-control form-control-sm" name="interpret_language">';
				block += '									<option value="">Choose Language...</option>';
				block += '									' + option_list;
				block += '								</select>';
				block += '							</div>';
				block += '							<div class="mb-auto form-group col-md-1">';
				block += '								<input required type="text" class="form-control form-control-sm" name="interpreter_id" ';
				block += '									value="' + interpreter_id + '" autocomplete="off" placeholder="Interpreter ID *"">';
				block += '							</div>';
				block += '							<div class="mb-auto form-group col-md-2">';
				block += '								<input type="text" class="form-control form-control-sm" name="fusion_group_interpreter_id" ';
				block += '									value="' + fusion_group_interpreter_id + '" autocomplete="off" placeholder="Fusion Group Interpreter ID">';
				block += '							</div>';
				block += '							<input type="hidden" name="call_uuid" value="' + call_uuid + '">';

				//call start timestamp
				if ((interpret_stamp_begin.length > 0 )) {
					block += 		'							<button type="button" class="mr-5 h-100 btn btn-default btn-sm disabled interpret_stamp_begin_btn" disabled="disabled">Started...</button>';
				} else {
					block += 		'							<button type="button" class="mr-5 h-100 btn btn-default btn-sm interpret_stamp_begin_btn" title="' + label_start_timestamp + '" onclick="start_timestamp(this, &apos;' + destination + '&apos;, &apos;' + call_uuid + '&apos;);">Start Interpret</button>';
				}

				block += '							<button type="submit" class="h-100 btn btn-primary btn-sm">Save</button>';
				block += '						</div>';
				block += '					</form>';
				block += '				</td>';
				block += '			</tr>';
				block += '		</tbody>';
				block += '	</table>';
				block += '</div>';

				//add call div
				$('#agent_live_call').append(block);

				//pre-select interpret language selection if available
				if (interpret_language.length > 0 ) {
					//$('#'+call_uuid).find('select[name="interpret_language"]').val(interpret_language);
					$('#'+call_uuid).find('select[name="interpret_language"] option[value="' + interpret_language + '"]').prop('selected', true);
				}
			}
		});
	}

//ajax refresh
	var refresh = <?php echo $refresh; ?>;
	var source_url = 'resources/content.php?' <?php if (isset($_GET['debug'])) { echo " + '&debug'"; } ?>;
	var interval_timer_id;

	function loadXmlHttp(url, id) {
		var f = this;
		f.xmlHttp = null;
		/*@cc_on @*/ // used here and below, limits try/catch to those IE browsers that both benefit from and support it
		/*@if(@_jscript_version >= 5) // prevents errors in old browsers that barf on try/catch & problems in IE if Active X disabled
		try {f.ie = window.ActiveXObject}catch(e){f.ie = false;}
		@end @*/
		if (window.XMLHttpRequest&&!f.ie||/^http/.test(window.location.href))
			f.xmlHttp = new XMLHttpRequest(); // Firefox, Opera 8.0+, Safari, others, IE 7+ when live - this is the standard method
		else if (/(object)|(function)/.test(typeof createRequest))
			f.xmlHttp = createRequest(); // ICEBrowser, perhaps others
		else {
			f.xmlHttp = null;
			 // Internet Explorer 5 to 6, includes IE 7+ when local //
			/*@cc_on @*/
			/*@if(@_jscript_version >= 5)
			try{f.xmlHttp=new ActiveXObject("Msxml2.XMLHTTP");}
			catch (e){try{f.xmlHttp=new ActiveXObject("Microsoft.XMLHTTP");}catch(e){f.xmlHttp=null;}}
			@end @*/
		}
		if(f.xmlHttp != null){
			f.el = document.getElementById(id);
			f.xmlHttp.open("GET",url,true);
			f.xmlHttp.onreadystatechange = function(){f.stateChanged();};
			f.xmlHttp.send(null);
		}
	}

	loadXmlHttp.prototype.stateChanged=function () {
		var url = new URL(this.xmlHttp.responseURL);
		if (/login\.php$/.test(url.pathname)) {
			// You are logged out. Stop refresh!
			refresh_stop();
			url.searchParams.set('path', '<?php echo $_SERVER['REQUEST_URI']; ?>');
			window.location.href = url.href;
			return;
		}

		if (this.xmlHttp.readyState == 4 && (this.xmlHttp.status == 200 || !/^http/.test(window.location.href))) {
			//this.el.innerHTML = this.xmlHttp.responseText;
			//document.getElementById('ajax_reponse').innerHTML = this.xmlHttp.responseText;
			list_active_calls(this.xmlHttp.responseText);
		}
	}

	var requestTime = function() {
		var url = source_url;
		<?php
		if (isset($_GET['debug'])) {
			echo "url += '&debug';";
		}
		?>
		new loadXmlHttp(url, 'ajax_reponse');
		refresh_start();
	}

	if (window.addEventListener) {
		window.addEventListener('load', requestTime, false);
	}
	else if (window.attachEvent) {
		window.attachEvent('onload', requestTime);
	}

//refresh controls
	function refresh_stop() {
		clearInterval(interval_timer_id);
		if (document.getElementById('refresh_state')) { document.getElementById('refresh_state').innerHTML = "<img src='resources/images/refresh_paused.png' style='width: 16px; height: 16px; border: none; margin-top: 1px; cursor: pointer;' onclick='refresh_start();' alt=\"<?php echo $text['label-refresh_enable']?>\" title=\"<?php echo $text['label-refresh_enable']?>\">"; }
	}

	function refresh_start() {
		if (document.getElementById('refresh_state')) { document.getElementById('refresh_state').innerHTML = "<img src='resources/images/refresh_active.gif' style='width: 16px; height: 16px; border: none; margin-top: 3px; cursor: pointer;' alt=\"<?php echo $text['label-refresh_pause']?>\" title=\"<?php echo $text['label-refresh_pause']?>\">"; }
		refresh_stop();
		interval_timer_id = setInterval( function() {
			url = source_url;
			<?php
			if (isset($_GET['debug'])) {
				echo "url += '&debug';";
			}
			?>
			new loadXmlHttp(url, 'ajax_reponse');
		}, refresh);
	}

//used for start timestamp of agent live call record
	function start_timestamp(btn_element, ext, chan_uuid) {
		if (chan_uuid == '') {
			return;
		}
		if (window.XMLHttpRequest) {// code for IE7+, Firefox, Chrome, Opera, Safari
			xmlhttp=new XMLHttpRequest();
		}
		else {// code for IE6, IE5
			xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
		}
		url = 'exec.php?ext=' + ext + '&chan_uuid=' + chan_uuid;
		xmlhttp.onreadystatechange = () => {
			if (xmlhttp.readyState === XMLHttpRequest.DONE && (xmlhttp.status === 200 || xmlhttp.status === 201)) {
				btn_element.removeAttribute("onclick");
				btn_element.classList.add("disabled");
				btn_element.disabled = true;
				btn_element.innerHTML = "Started...";
			}
		};
		xmlhttp.open("GET",url,false);
		xmlhttp.send(null);
		//document.getElementById('cmd_reponse').innerHTML=xmlhttp.responseText;
		if (xmlhttp.responseText.errors) {
			display_message(xmlhttp.responseText.errors, 'negative');
		}
	}

<?php
//determine access code validation rule
	$access_code_allow_invalid = true;
	if (!empty($_SESSION['agent_live_call']['access_code_allow_invalid']['boolean']))
		$access_code_allow_invalid = $_SESSION['agent_live_call']['access_code_allow_invalid']['boolean'];
?>

//verify & validate access code
	var access_code_allow_invalid = <?php echo $access_code_allow_invalid; ?>;
	var valid = false;

	function validate_client_access_code(input_element) {
		var validateRequest = null;
		var minlength = 3;
		var that = input_element,
		access_code = $(input_element).val();

		if (access_code.length >= minlength) {
			if (validateRequest != null) 
				validateRequest.abort();
			validateRequest = $.ajax({
				type: 'POST',
				url: 'exec.php',
				data: {
					action: 'validate',
					access_code: access_code
				},
				dataType: "json",
				beforeSend: function() {
					valid = false;
					$(that).next().html('');
				},
				success: function(response){
					//we need to check if the access_code is the same
					if (access_code==$(that).val()) {
						var _html = response.data.message;
						if (response.data.status == 'success') {
							valid = true;
							$(that).next().removeClass('text-danger').addClass('text-success');
							//_html += '<br>' + 'Client Name: <strong>' + response.data.client.client_name + '</strong>';
							_html = 'Client: <strong>' + response.data.client.client_name + '</strong>';
						} else if (response.data.status == 'error') {
							$(that).next().removeClass('text-success').addClass('text-danger');
						}
						$(that).next().html(_html);
					}
				},
				error: function(){
					display_message('Unable to validate client access code at the moment, please try again.', 'negative');
				}
			});
			/*
			validateRequest.done(function(response){
				var _html = response.data.message;
				if (response.data.status == 'success') {
					var valid = true;
					$('#client_access_code_validate_status_text').addClass('text-success');
					_html += '<br>' + 'Client Name: <strong>' + response.data.client.client_name + '</strong>';
				} else if (response.data.status == 'error') {
					$('#client_access_code_validate_status_text').addClass('text-danger');
				}
				$('#client_access_code_validate_status_text').text(_html);
			});
			validateRequest.fail(function(){
				display_message('Unable to validate client access code at the moment, please try again.', 'negative');
			});
			*/
		} else {
			valid = false;
			$(that).next().html('');
		}
	}

//client details form handle
	function client_details_form_submit(event, chan_uuid){
		event.preventDefault();
		event.stopPropagation();

		//var form = document.getElementById('client-details-form-'+chan_uuid);

		//submit form
			if ( (access_code_allow_invalid == true) || 
				 (access_code_allow_invalid == false && valid == true) ) {
				$.ajax({
					url: 'exec.php',
					type: 'POST',
					data: $('#client-details-form-'+chan_uuid).serialize(),
					dataType: "json",
				}).done(function(){
					display_message('Customer details saved.', 'positive');
				}).fail(function(){
					display_message('An error occurred, please try again.', 'negative');
				}).always(function() {
					refresh_start();
					$('#client-details-form-'+chan_uuid).find('#client_access_code_validate_status_text').html('');
				});
			} else {
				display_message('Cannot save unverified client access code.', 'negative');
			}
	}

//refresh call records
	function refresh_call_records() {
		$.get( 'resources/call_records.php', function( response ) {
			$('#call_records').html(response);
		});
	}
	$(refresh_call_records);
	var call_records_timer_id = setInterval( function() {
		$(refresh_call_records);
	}, refresh);

//ajax paging call records
	$(document).on('click', '#paging_controls a', function(e){ 
		e.preventDefault();
		var page_url = $(this).attr('href');
		$(this).find('button').html('<span class="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span>Loading...');
		$.get(page_url, function( response ) {
			$('#call_records').html(response);
		});
	});
</script>

<style type="text/css">
	TABLE {
		border-spacing: 0px;
		border-collapse: collapse;
		border: none;
		}
	.active-client-call {
		opacity: 1;
		transition: opacity 1.5s;
		}
	.active-client-call.fade {
		opacity: 0;
		}
</style>

<?php

//create simple array of users own extensions
unset($_SESSION['user']['extensions']);
if (is_array($_SESSION['user']['extension'])) {
	foreach ($_SESSION['user']['extension'] as $assigned_extensions) {
		$_SESSION['user']['extensions'][] = $assigned_extensions['user'];
	}
}

?>

<table width='100%'>
	<tr>
		<td valign='top' align='left' width='50%' nowrap>
			<b><?php echo $text['title-agent_live_call']; ?></b>
		</td>
	</tr>
</table>
<br>

<table width='100%'>
	<tr>
		<td>
			<div id='agent_live_call'></div>
		</td>
	</tr>
</table>
<br><br><br>

<div id='call_records'></div>
<br><br><br>

<?php

//include the footer
	require_once "resources/footer.php";

?>
