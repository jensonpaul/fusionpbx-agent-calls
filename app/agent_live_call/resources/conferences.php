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

//prevent warnings
if (!is_array($_SESSION['user']['extensions'])) {
	$_SESSION['user']['extensions'] = array();
}

$active_conferences = [];

$fp = event_socket_create($_SESSION['event_socket_ip_address'], $_SESSION['event_socket_port'], $_SESSION['event_socket_password']);
if ($fp) {
	$xml_string = trim(event_socket_request($fp, 'api conference xml_list'));
	try {
		$xml = new SimpleXMLElement($xml_string);
	}
	catch(Exception $e) {
		//echo $e->getMessage();
	}

	$x = 0;
	foreach ($xml->conference as $row) {

		//set the variables
			$name = $row['name'];
			$member_count = $row['member-count'];

		//show the conferences that have a matching domain
			$name_array = explode('@', $name);
			if ($name_array[1] == $_SESSION['domain_name']) {
				$conference_uuid = $name_array[0];

				//if uuid then lookup the conference name
				if (isset($name_array[0]) && is_uuid($name_array[0])) {
					//check for the conference center room
					$sql = "select ";
					$sql .= "cr.conference_room_name, ";
					$sql .= "cc.conference_center_extension, ";
					$sql .= "cr.participant_pin ";
					$sql .= "from v_conference_rooms as cr ";
					$sql .= "left join v_conference_centers as cc on cr.conference_center_uuid = cc.conference_center_uuid ";
					$sql .= "where cr.conference_room_uuid = :conference_room_uuid ";
					$parameters['conference_room_uuid'] = $conference_uuid;
					$database = new database;
					$conference = $database->select($sql, $parameters, 'row');
					$conference_name = $conference['conference_room_name'];
					$conference_extension = $conference['conference_center_extension'];
					$participant_pin = $conference['participant_pin'];
					unset ($parameters, $conference, $sql);
				}
				else if (isset($name_array[0]) && is_numeric($name_array[0])) {
					//check the conference table
					$sql = "select ";
					$sql .= "conference_name, ";
					$sql .= "conference_extension, ";
					$sql .= "conference_pin_number ";
					$sql .= "from ";
					$sql .= "v_conferences ";
					$sql .= "where ";
					$sql .= "domain_uuid = :domain_uuid ";
					$sql .= "and conference_extension = :conference_extension ";
					$parameters['domain_uuid'] = $_SESSION['domain_uuid'];
					$parameters['conference_extension'] = $name_array[0];
					$database = new database;
					$conference = $database->select($sql, $parameters, 'row');
					$conference_name = $conference['conference_name'];
					$conference_extension = $conference['conference_extension'];
					$participant_pin = $conference['conference_pin_number'];
					unset ($parameters, $sql);
				}
				
				if (!empty($_GET['extension']) && in_array($conference_name, $_SESSION['user']['extensions'])) {
					//build the list of active conferences
					$active_conferences[$x]['extension'] = $conference_name;
					$active_conferences[$x]['conference_uuid'] = escape($conference_uuid);
					$active_conferences[$x]['conference_name'] = escape($conference_name);
					$active_conferences[$x]['conference_extension'] = escape($conference_extension);
					$active_conferences[$x]['participant_pin'] = escape($participant_pin);
					$active_conferences[$x]['member_count'] = escape($member_count);
				}

				$x++;
			}
	}
}

echo json_encode($active_conferences);

?>
