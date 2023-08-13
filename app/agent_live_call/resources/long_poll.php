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

//prevent warnings
if (!is_array($_SESSION['user']['extensions'])) {
	$_SESSION['user']['extensions'] = array();
}

//send the command
$fp = event_socket_create($_SESSION['event_socket_ip_address'], $_SESSION['event_socket_port'], $_SESSION['event_socket_password']);
if ($fp) {
	$switch_result = event_socket_request($fp, 'api show channels as json');
	$json_array = json_decode($switch_result, true);
}

$data = [];

//add the active call details
if (isset($json_array['rows'])) {
	$x = 0;
	foreach($json_array['rows'] as $field) {
		$presence_id = $field['presence_id'];
		$presence = explode("@", $presence_id);
		$presence_id = $presence[0];
		$presence_domain = $presence[1];
		if (in_array($presence_id, $_SESSION['user']['extensions'])) {
			if ($presence_domain == $_SESSION['domain_name']) {
				$data[$x]['extension'] = $presence_id;
				$data[$x]['call_uuid'] = $field['call_uuid'];
			}
		}
		$x++;
	}
}

echo json_encode($data);

?>
