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

//refresh call records
	function refresh_call_records() {
		$.get( 'resources/call_records.php', function( response ) {
			$('#call_records').html(response);
		});
	}
	$(refresh_call_records);

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

			var active_calls_on_screen = $('#ajax_reponse').find('[id^=client-details-form-]').map(function() {
				return $(this).attr('id').replace('client-details-form-', '');
			}).toArray();
			active_calls_on_screen = active_calls_on_screen.filter(item => item);
			var active_calls_incoming = $(this.xmlHttp.responseText).find('[id^=client-details-form-]').map(function() {
				return $(this).attr('id').replace('client-details-form-', '');
			}).toArray();
			active_calls_incoming = active_calls_incoming.filter(item => item);
			var is_same = $(active_calls_on_screen).not(active_calls_incoming).length === 0 && $(active_calls_incoming).not(active_calls_on_screen).length === 0;
			if (!is_same) {
				//var diff = $(active_calls_on_screen).not(active_calls_incoming).get();
				var difference = active_calls_on_screen.filter(x => !active_calls_incoming.includes(x));
				difference.forEach((call_uuid) => {
					//move to inactive calls list
					/*
					$("#"+call_uuid).prependTo("#agent_inactive_call");
					document.getElementById('agent_inactive_call').appendChild(
						document.getElementById(call_uuid)
					);
					*/
					//theParent.insertBefore(theKid, theParent.firstChild);
					document.getElementById('agent_inactive_call').insertBefore(
						document.getElementById(call_uuid),
						document.getElementById('agent_inactive_call').firstChild
					);
					/*
					//remove the interpret stamp button
					document.getElementById(call_uuid).getElementsByClassName('interpret_stamp_begin_btn')[0].disabled = true;
					*/
					//remove active call class
					var els = document.getElementById(call_uuid).getElementsByClassName('op_ext');
					Array.prototype.forEach.call(els, function(el) {
						if (el.classList.contains('op_state_ringing')) {
							el.classList.remove('op_state_ringing');
						}
						if (el.classList.contains('op_state_active')) {
							el.classList.remove('op_state_active');
						}
						if (el.classList.contains('op_state_held')) {
							el.classList.remove('op_state_held');
						}
					});
					if (document.getElementById(call_uuid).classList.contains('op_state_ringing')) {
						document.getElementById(call_uuid).classList.remove('op_state_ringing');
					}
					if (document.getElementById(call_uuid).classList.contains('op_state_active')) {
						document.getElementById(call_uuid).classList.remove('op_state_active');
					}
					if (document.getElementById(call_uuid).classList.contains('op_state_held')) {
						document.getElementById(call_uuid).classList.remove('op_state_held');
					}
					/*
					//add close button
					var htmlString = '<button type="button" class="close" aria-label="Close">' +
										'<span aria-hidden="true">&times;</span>' +
									 '</button>';
					var div = document.createElement('div');
					div.innerHTML = htmlString.trim();
					document.getElementById(call_uuid).insertBefore(
						div.firstChild,
						document.getElementById(call_uuid).firstChild
					);
					*/
					//start countdown and auto-close the call div
					setTimeout(function () {
						document.getElementById(call_uuid).classList.add('fade');
						setTimeout(() => {
							document.getElementById(call_uuid).remove();
						}, 1500);
					}, 30000);
					//finally refresh the call records
					refresh_call_records();
				});
			}

			//this.el.innerHTML = this.xmlHttp.responseText;
			document.getElementById('ajax_reponse').innerHTML = this.xmlHttp.responseText;
		}
	}

	/*
	$(document).on('click', '.active-client-call .close', function(){ 
		$(this).parent('.active-client-call').fadeOut(1500);
		setTimeout(() => {
			$(this).parent('.active-client-call').remove();
		}, 1500);
	});
	*/

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

//stop refresh when form interaction started
	$(document).on('focus', '#ajax_reponse input, #ajax_reponse select', function () {
		var extensionForm = $(this).parents('form:first');
		var lookup_call_uuid = extensionForm.attr('id').replace('client-details-form-', '');
		if (lookup_call_uuid != '') {
			refresh_stop();
		}
	});

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
							_html += '<br>' + 'Client Name: <strong>' + response.data.client.client_name + '</strong>';
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

//long poll to update new calls extension form in case if refresh is paused at client side

	function longPoll() {
		$.get( 'resources/long_poll.php', function( response ) {
			if ( response.length != 0 ) {
				response = JSON.parse(response);
				//check if all the calls are listed
				//refresh if not
				$.each( response, function( key, value ) {
					var extension = value.extension;
					var call_uuid = value.call_uuid;
					/*
					var extensionForm = $('#'+extension).find('form');
					var lookup_call_uuid = extensionForm.attr('id').replace('client-details-form-', '');
					if (lookup_call_uuid != '' && lookup_call_uuid != call_uuid) {
						$('#'+extension).fadeOut(1000);
						//restart the refresh
						refresh_start();
					}
					*/
					if (extension.length === 0 || call_uuid.length === 0) {
						//return false; // this is equivalent of 'break' for jQuery loop
						//return;       // this is equivalent of 'continue' for jQuery loop
						return;
					}
					var call_div = $('#'+call_uuid);
					var call_form = $('#client-details-form-'+call_uuid);
					if (call_div.length === 0 || call_form.length === 0) {
						//restart the refresh
						refresh_start();
					}
				});
				//check if active calls listed are the same as live calls
				var live_calls = Object.keys(response).map(function( key ) {
					return response[key].call_uuid;
				});
				live_calls = live_calls.filter(item => item);
				var active_calls = $('#ajax_reponse').find('[id^=client-details-form-]').map(function() {
					return $(this).attr('id').replace('client-details-form-', '');
				}).toArray();
				active_calls = active_calls.filter(item => item);
				var is_same = $(live_calls).not(active_calls).length === 0 && $(active_calls).not(live_calls).length === 0;
				if (!is_same) {
					refresh_start();
				}
			}
		});
	}

	setInterval( function() {
		longPoll();
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

<div id='ajax_reponse'></div>

<table width='100%'>
	<tr>
		<td valign='top' align='left' width='50%' nowrap>
			<b><?php echo $text['title-agent_inactive_call']; ?></b>
		</td>
	</tr>
</table>
<br>

<table width='100%'>
	<tr>
		<td>
			<div id='agent_inactive_call'></div>
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
