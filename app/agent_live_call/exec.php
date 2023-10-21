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

//authorized referrer
// 	if(stristr($_SERVER["HTTP_REFERER"], '/index.php') === false) {
// 		if(stristr($_SERVER["HTTP_REFERER"], '/index_inc.php') === false) {
// 			echo " access denied";
// 			exit;
// 		}
// 	}

//set log storage path
	$log_storage_path = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . "storage/logs";

//response text
	$response['code'] = 201;
	$response['data'] = [];
	$response['errors'] = '';

//clear the values
	$call_uuid = '';
	$access_code = '';
	$cost_center_id = '';
	$employee_id = '';
	$caller_name = '';
	$interpret_language = '';
	$interpreter_id = '';
	$fusion_group_interpreter_id = '';

//process the requests
if (count($_GET) > 0) {
	//set the variables
		$chan_uuid = $_GET["chan_uuid"];

	//start timestamp
		if (strlen($chan_uuid) > 0) {
			$uuid_pattern = '/[^-A-Fa-f0-9]/';
			$num_pattern = '/[^-A-Za-z0-9()*#]/';
			$uuid = preg_replace($uuid_pattern,'',$chan_uuid);
			$ext = preg_replace($num_pattern,'',$_GET['ext']);
			$ext_key = array_search($ext, array_column($_SESSION['user']['extension'], 'destination'));

			//check if uuid record exists
				$sql = "select client_call_record_uuid from v_client_call_records where call_uuid = :call_uuid ";
				$parameters['call_uuid'] = $uuid;
				$database = new database;
				$client_call_record_uuid = $database->select($sql, $parameters, 'column');
				unset($sql);
				unset($parameters);

			//begin array
				$array['client_call_records'][0]['call_uuid'] = $uuid;
				$array['client_call_records'][0]['user_uuid'] = $_SESSION['user']['user_uuid'];
				$array['client_call_records'][0]['domain_uuid'] = $_SESSION['domain_uuid'];
				$array['client_call_records'][0]['start_timestamp'] = (new \DateTime())->format('Y-m-d H:i:s.uT');

			if ($client_call_record_uuid) {
				//update record
					$array['client_call_records'][0]['client_call_record_uuid'] = $client_call_record_uuid;
			} else {
				//create record
					$array['client_call_records'][0]['client_call_record_uuid'] = uuid();
			}

			//add or update records
				$database = new database;
				$database->app_name = 'client_call_record';
				$database->app_uuid = '0106c183-c7f3-4f5b-82c6-b7aee0ced23e';
				$result = $database->save($array);
				unset($array);

			/*
			//replace last timestamp uuid value
				$_SESSION['user']['extension'][$ext_key]['last_timestamp_uuid'] = $uuid;
				*/
		}
		else {
			//response text
			$response['code'] = 400;
			$response['errors'] = 'An error occurred. Please refresh the page and try again.';
		}
}
elseif (count($_POST) > 0) {
	//check form action
		$action = $_POST['action'];

	//get http post variables and set them to php variables
		$call_uuid = $_POST['call_uuid'];
		$access_code = $_POST['access_code'];
		$cost_center_id = $_POST['cost_center_id'];
		$employee_id = $_POST['employee_id'];
		$caller_name = $_POST['caller_name'];
		$interpret_language = $_POST['interpret_language'];
		$interpreter_id = $_POST['interpreter_id'];
		$fusion_group_interpreter_id = $_POST['fusion_group_interpreter_id'];

	//log
		$log_file = fopen($log_storage_path."/log.txt", "a") or die("Unable to open file!");
		$txt = date('Ymd_His')."\n";
		fwrite($log_file, $txt);
		$txt = "POST Form Data: ".json_encode($_POST)."\n\n";
		fwrite($log_file, $txt);
		fclose($log_file);

	if ($action == 'validate') {

		//check if access code record exists
			$sql = "select count(*) from v_clients where access_code = :access_code ";
			$parameters['access_code'] = $access_code;
			$database = new database;
			$count = $database->select($sql, $parameters, 'column');

		//get client details if access code exists
			if ($count > 0) {
				// retrieve client details
					$sql = str_replace('count(*)', '*', $sql);
					$database = new database;
					$client = $database->select($sql, $parameters, 'row');

				//fill response data
					$response['data'] = [
						"status" => "success",
						"message" => "Client found.",
						"client" => $client
					];
			} else {
				//fill response data
					$response['data'] = [
						"status" => "error",
						"message" => "Client not found.",
						"client" => [] //empty client data
					];
			}
			unset($sql);
			unset($parameters);
	} elseif (strlen($call_uuid) > 0 || strlen($access_code) > 0 || strlen($interpret_language) > 0 || strlen($interpreter_id) > 0) {

		//log
			$log_file = fopen($log_storage_path."/log.txt", "a") or die("Unable to open file!");
			$txt = date('Ymd_His')."\n";
			fwrite($log_file, $txt);
			$txt = "Call UUID: ".$call_uuid."\n\n";
			fwrite($log_file, $txt);
			fclose($log_file);

		//check if uuid record exists
			$sql = "select client_call_record_uuid from v_client_call_records where call_uuid = :call_uuid ";
			$parameters['call_uuid'] = $call_uuid;
			$database = new database;
			$client_call_record_uuid = $database->select($sql, $parameters, 'column');
			unset($sql);
			unset($parameters);

		//begin array
			$array['client_call_records'][0]['call_uuid'] = $call_uuid;
			$array['client_call_records'][0]['user_uuid'] = $_SESSION['user']['user_uuid'];
			$array['client_call_records'][0]['domain_uuid'] = $_SESSION['domain_uuid'];
			$array['client_call_records'][0]['access_code'] = $access_code;
			$array['client_call_records'][0]['cost_center_id'] = $cost_center_id;
			$array['client_call_records'][0]['employee_id'] = $employee_id;
			$array['client_call_records'][0]['caller_name'] = $caller_name;
			$array['client_call_records'][0]['interpret_language'] = $interpret_language;
			$array['client_call_records'][0]['interpreter_id'] = $interpreter_id;
			$array['client_call_records'][0]['fusion_group_interpreter_id'] = $fusion_group_interpreter_id;

		//log
			$log_file = fopen($log_storage_path."/log.txt", "a") or die("Unable to open file!");
			$txt = date('Ymd_His')."\n";
			fwrite($log_file, $txt);
			$txt = "Client Call Record UUID: ".json_encode($client_call_record_uuid)."\n\n";
			fwrite($log_file, $txt);
			fclose($log_file);

		if ($client_call_record_uuid) {
			//update record
				$array['client_call_records'][0]['client_call_record_uuid'] = $client_call_record_uuid;
		} else {
			//create record
				$array['client_call_records'][0]['client_call_record_uuid'] = uuid();
		}

		//log
			$log_file = fopen($log_storage_path."/log.txt", "a") or die("Unable to open file!");
			$txt = date('Ymd_His')."\n";
			fwrite($log_file, $txt);
			$txt = "Client Call Record before DB Save: ".json_encode($array['client_call_records'])."\n\n";
			fwrite($log_file, $txt);
			fclose($log_file);

		//add or update records
			$database = new database;
			$database->app_name = 'client_call_record';
			$database->app_uuid = '0106c183-c7f3-4f5b-82c6-b7aee0ced23e';
			$result = $database->save($array);
			unset($array);

		//log
			$log_file = fopen($log_storage_path."/log.txt", "a") or die("Unable to open file!");
			$txt = date('Ymd_His')."\n";
			fwrite($log_file, $txt);
			$txt = "DB Save Status: ".json_encode($result)."\n\n";
			fwrite($log_file, $txt);
			fclose($log_file);
	} else {
		//missing required data
		$response['code'] = 400;
		$response['errors'] = $text['message-missing_required'];
	}
}
else {
	//response text
	$response['code'] = 400;
	$response['errors'] = 'An error occurred. Please refresh the page and try again.';
}

//send response
echo json_encode($response);
?>
