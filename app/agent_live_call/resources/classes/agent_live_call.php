<?php

/**
 * Define the agent_live_call class
 */
if (!class_exists('agent_live_call')) {
	class agent_live_call {

		/**
		 * Define the variables
		 */
		public $domain_uuid;

		/**
		 * Called when the object is created
		 */
		public function __construct() {
			if (!isset($this->domain_uuid)) {
				$this->domain_uuid = $_SESSION['domain_uuid'];
			}
		}

		/**
		 * Called when there are no references to a particular object
		 * unset the variables used in the class
		 */
		public function __destruct() {
			foreach ($this as $key => $value) {
				unset($this->$key);
			}
		}

		/**
		 * Get the call activity
		 */
		public function call_activity() {

			//define the global variable
				global $ext_user_status;

			//get the extensions and their user status
				$sql = "select ";
				$sql .= "e.extension, ";
				$sql .= "e.number_alias, ";
				$sql .= "e.effective_caller_id_name, ";
				$sql .= "lower(e.effective_caller_id_name) as filter_name, ";
				$sql .= "e.effective_caller_id_number, ";
				$sql .= "e.call_group, ";
				$sql .= "e.description, ";
				$sql .= "u.user_uuid, ";
				$sql .= "u.user_status ";
				$sql .= "from ";
				$sql .= "v_extensions as e ";
				$sql .= "left outer join v_extension_users as eu on ( eu.extension_uuid = e.extension_uuid and eu.domain_uuid = :domain_uuid ) ";
				$sql .= "left outer join v_users as u on ( u.user_uuid = eu.user_uuid and u.domain_uuid = :domain_uuid ) ";
				$sql .= "where ";
				$sql .= "e.enabled = 'true' and ";
				$sql .= "u.user_uuid = :user_uuid and ";
				$sql .= "e.domain_uuid = :domain_uuid ";
				$sql .= "order by ";
				$sql .= "e.extension asc ";
				$parameters['user_uuid'] = $_SESSION['user']['user_uuid'];
				$parameters['domain_uuid'] = $_SESSION['domain_uuid'];
				$database = new database;
				$extensions = $database->select($sql, $parameters);

			//send the command
				$fp = event_socket_create($_SESSION['event_socket_ip_address'], $_SESSION['event_socket_port'], $_SESSION['event_socket_password']);
				if ($fp) {
					$switch_result = event_socket_request($fp, 'api show channels as json');
					$json_array = json_decode($switch_result, true);
				}

			//build the response
				$x = 0;
				if (isset($extensions)) {

					//add the active call details
						if (isset($json_array['rows'])) {
							$user_extensions = array_map(function ($row) {
								return strlen($row['number_alias']) >0 ? $row['number_alias'] : $row['extension'];
							}, $extensions);
							foreach($json_array['rows'] as $field) {
								if (isset($array) && count($array) > 0) {
									$extensions_added = array_column($array, 'extension');
								} else {
									$extensions_added = [];
								}
								$presence_id = $field['presence_id'];
								$presence = explode("@", $presence_id);
								$presence_id = $presence[0];
								$presence_domain = $presence[1];
								//if (in_array($presence_id, $user_extensions) && !in_array($presence_id, $extensions_added)) {
								if (in_array($presence_id, $user_extensions)) {
									$user = $presence_id;
									if ($presence_domain == $_SESSION['domain_name']) {

										//add the extension details
											$this_key = array_search($presence_id, $user_extensions);
											$array[$x] = $extensions[$this_key];

										//normalize the array
											$array[$x]["uuid"] =  $field['uuid'];
											$array[$x]["direction"] = $field['direction'];
											$array[$x]["created"] = $field['created'];
											$array[$x]["created_epoch"] = $field['created_epoch'];
											$array[$x]["name"] = $field['name'];
											$array[$x]["state"] = $field['state'];
											$array[$x]["cid_name"] = $field['cid_name'];
											$array[$x]["cid_num"] = $field['cid_num'];
											$array[$x]["ip_addr"] = $field['ip_addr'];
											$array[$x]["dest"] = $field['dest'];
											$array[$x]["application"] = $field['application'];
											$array[$x]["application_data"] = $field['application_data'];
											$array[$x]["dialplan"] = $field['dialplan'];
											$array[$x]["context"] = $field['context'];
											$array[$x]["read_codec"] = $field['read_codec'];
											$array[$x]["read_rate"] = $field['read_rate'];
											$array[$x]["read_bit_rate"] = $field['read_bit_rate'];
											$array[$x]["write_codec"] = $field['write_codec'];
											$array[$x]["write_rate"] = $field['write_rate'];
											$array[$x]["write_bit_rate"] = $field['write_bit_rate'];
											$array[$x]["secure"] = $field['secure'];
											$array[$x]["hostname"] = $field['hostname'];
											$array[$x]["presence_id"] = $field['presence_id'];
											$array[$x]["presence_data"] = $field['presence_data'];
											$array[$x]["callstate"] = $field['callstate'];
											$array[$x]["callee_name"] = $field['callee_name'];
											$array[$x]["callee_num"] = $field['callee_num'];
											$array[$x]["callee_direction"] = $field['callee_direction'];
											$array[$x]["call_uuid"] = $field['call_uuid'];
											$array[$x]["sent_callee_name"] = $field['sent_callee_name'];
											$array[$x]["sent_callee_num"] = $field['sent_callee_num'];
											$array[$x]["destination"] = $user;

											//calculate and set the call length
											$call_length_seconds = time() - $array[$x]["created_epoch"];
											$call_length_hour = floor($call_length_seconds/3600);
											$call_length_min = floor($call_length_seconds/60 - ($call_length_hour * 60));
											$call_length_sec = $call_length_seconds - (($call_length_hour * 3600) + ($call_length_min * 60));
											$call_length_min = sprintf("%02d", $call_length_min);
											$call_length_sec = sprintf("%02d", $call_length_sec);
											$call_length = $call_length_hour.':'.$call_length_min.':'.$call_length_sec;
											$array[$x]['call_length'] = $call_length;

											//send the command
											if ($field['state'] != '') {
												if ($fp) {
													if (is_uuid($field['uuid'])) {
														$switch_cmd = 'uuid_dump '.$field['uuid'].' json';
														$dump_result = event_socket_request($fp, 'api '.$switch_cmd);
														$dump_array = json_decode($dump_result, true);
														if (is_array($dump_array)) {
															foreach ($dump_array as $dump_var_name => $dump_var_value) {
																$array[$x][$dump_var_name] = $dump_var_value;
															}
														}
													}
												}
											}
									}
								}

								//increment the row
									$x++;
							}
						}
				}

				//reindex array using extension instead of auto-incremented value
				$result = array();
				if (is_array($array)) {
					$y = 0;
					foreach ($array as $index => $subarray) {
						$extension = $subarray['extension'];
						if (is_array($subarray)) foreach ($subarray as $field => $value) {
							//$result[$extension][$field] = $array[$index][$field];
							$result[$y][$field] = $array[$index][$field];
							unset($array[$index][$field]);
						}
						unset($array[$subarray['extension']]['extension']);
						unset($array[$index]);
						$y++;
					}
				}

			//return array
				return $result;
		}
	}
}

?>
