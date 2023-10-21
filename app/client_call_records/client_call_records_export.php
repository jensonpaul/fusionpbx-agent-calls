<?php

//set the include path
	$conf = glob("{/usr/local/etc,/etc}/fusionpbx/config.conf", GLOB_BRACE);
	set_include_path(parse_ini_file($conf[0])['document.root']);

//includes files
	require_once "resources/require.php";
	require_once "resources/check_auth.php";

//check permissions
	if (permission_exists('client_call_record_export')) {
		//access granted
	}
	else {
		echo "access denied";
		exit;
	}

//add multi-lingual support
	$language = new text;
	$text = $language->get();

//additional includes
	$rows_per_page = ($_SESSION['domain']['paging']['numeric'] != '') ? $_SESSION['domain']['paging']['numeric'] : 50;
	$archive_request = $_POST['archive_request'] == 'true' ? true : false;
	require_once "client_call_records_inc.php";

//get the format
	$export_format = $_REQUEST['export_format'];

//export the csv
	if (permission_exists('client_call_record_export_csv') && $export_format == 'csv') {

		//define file name
			if ($_GET['show'] == 'all' && permission_exists('client_call_record_all')) {
				$csv_filename = "ccr_".date("Ymd_His").".csv";
			}
			else {
				$csv_filename = "ccr_".$_SESSION['domain_name']."_".date("Ymd_His").".csv";
			}

		//set the http headers
			header('Content-type: application/octet-binary');
			header('Content-Disposition: attachment; filename='.$csv_filename);

		//set the csv headers
			$z = 0;
			foreach ($result[0] as $key => $val) {
				if ($key != "xml" && $key != "json") {
					if ($z == 0) {
						echo '"'.$key.'"';
					}
					else {
						echo ',"'.$key.'"';
					}
				}
				$z++;
			}
			echo "\n";

		//show the csv data
			$x=0;
			while (true) {
				$z = 0;
				foreach ($result[0] as $key => $val) {
					if ($key != "xml" && $key != "json") {
						if ($z == 0) {
							echo '"'.$result[$x][$key].'"';
						}
						else {
							echo ',"'.$result[$x][$key].'"';
						}
					}
					$z++;
				}
				echo "\n";
				++$x;
				if ($x > ($result_count-1)) {
					break;
				}
			}
	}

//export as a PDF
	if (permission_exists('client_call_record_export_pdf') && $export_format == 'pdf') {

		//load pdf libraries
		require_once "resources/tcpdf/tcpdf.php";
		require_once "resources/fpdi/fpdi.php";

		//determine page size
		switch ($_SESSION['fax']['page_size']['text']) {
			case 'a4':
				$page_width = 11.7; //in
				$page_height = 8.3; //in
				break;
			case 'legal':
				$page_width = 14; //in
				$page_height = 8.5; //in
				break;
			case 'letter':
			default	:
				$page_width = 11; //in
				$page_height = 8.5; //in
		}

		// initialize pdf
		$pdf = new FPDI('L', 'in');
		$pdf->SetAutoPageBreak(false);
		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(false);
		$pdf->SetMargins(0.5, 0.5, 0.5, true);

		//set default font
		$pdf->SetFont('helvetica', '', 7);
		//add new page
		$pdf->AddPage('L', array($page_width, $page_height));

		//set the number of columns
		$columns = 12;

		//write the table column headers
		$data_start = '<table cellpadding="0" cellspacing="0" border="0" width="100%">';
		$data_end = '</table>';

		$data_head = '<tr>';
		$data_head .= '<td width="5%"><b>'.$text['label-direction'].'</b></td>';
		$data_head .= '<td width="9%"><b>'.$text['label-caller_id_name'].'</b></td>';
		$data_head .= '<td width="9%"><b>'.$text['label-caller_id_number'].'</b></td>';
		$data_head .= '<td width="9%"><b>'.$text['label-destination'].'</b></td>';
		$data_head .= '<td width="10%" nowrap="nowrap"><b>'.$text['label-start'].'</b></td>';
		$data_head .= '<td width="3%" align="right"><b>'.$text['label-tta'].'</b></td>';
		$data_head .= '<td width="8%" align="right"><b>'.$text['label-duration'].'</b></td>';
		$data_head .= '<td width="8%" align="right"><b>'.$text['label-billsec'].'</b></td>';
		$data_head .= '<td width="5%" align="right"><b>'."PDD".'</b></td>';
		$data_head .= '<td width="5%" align="center"><b>'."MOS".'</b></td>';
		if (is_array($_SESSION['ccr']['field'])) {
			foreach ($_SESSION['ccr']['field'] as $field) {
				$array = explode(",", $field);
				$field_name = end($array);
				$field_label = ucwords(str_replace("_", " ", $field_name));
				$field_label = str_replace("Sip", "SIP", $field_label);
				if ($field_name != "destination_number") {
					$data_head .= '<td width="10%" align="left"><b>'.$field_label.'</b></td>';
				}
				$columns = $columns + 1;
			}
		}
		$data_head .= '<td width="1%"></td>';
		$data_head .= '<td width="10%"><b>'.$text['label-hangup_cause'].'</b></td>';
		$data_head .= '</tr>';
		$data_head .= '<tr><td colspan="'.$columns.'"><hr></td></tr>';

		//initialize total variables
		$total['duration'] = 0;
		$total['billmsec'] = 0;
		$total['pdd_ms'] = 0;
		$total['rtp_audio_in_mos'] = 0;
		$total['tta'] = 0;

		//write the row cells
		$z = 0; // total counter
		$p = 0; // per page counter
		if (sizeof($result) > 0) {
			foreach ($result as $ccr_num => $fields) {
				$data_body[$p] .= '<tr>';
				$data_body[$p] .= '<td>'.$text['label-'.$fields['direction']].'</td>';
				$data_body[$p] .= '<td>'.$fields['caller_id_name'].'</td>';
				$data_body[$p] .= '<td>'.$fields['caller_id_number'].'</td>';
				$data_body[$p] .= '<td>'.format_phone($fields['destination_number']).'</td>';
				$data_body[$p] .= '<td>'.$fields['start_timestamp'].'</td>';
				$total['tta'] += ($fields['tta'] > 0) ? $fields['tta'] : 0;
				$data_body[$p] .= '<td align="right">'.(($fields['tta'] >= 0) ? $fields['tta'].'s' : null).'</td>';
				$seconds = ($fields['hangup_cause'] == "ORIGINATOR_CANCEL") ? $fields['duration'] : round(($fields['billmsec'] / 1000), 0, PHP_ROUND_HALF_UP);
				$total['duration'] += $seconds;
				$data_body[$p] .= '<td align="right">'.gmdate("G:i:s", $seconds).'</td>';
				$total['billmsec'] += $fields['billmsec'];
				$data_body[$p] .= '<td align="right">'.number_format(round($fields['billmsec'] / 1000, 2), 2).'s</td>';

				if (is_array($_SESSION['ccr']['field'])) {
					foreach ($_SESSION['ccr']['field'] as $field) {
						$array = explode(",", $field);
						$field_name = end($array);
						$field_label = ucwords(str_replace("_", " ", $field_name));
						$field_label = str_replace("Sip", "SIP", $field_label);
						if ($field_name != "destination_number") {
							$data_body[$p] .= '<td align="right">';
							$data_body[$p] .= $fields[$field_name];
							$data_body[$p] .= '</td>';
						}
					}
				}

				$data_body[$p] .= '<td>&nbsp;</td>';
				$data_body[$p] .= '<td>'.ucwords(strtolower(str_replace("_", " ", $fields['hangup_cause']))).'</td>';
				$data_body[$p] .= '</tr>';

				$z++;
				$p++;

				if ($p == 60) {
					//output data
					$data_body_chunk = $data_start.$data_head;
					foreach ($data_body as $data_body_row) {
						$data_body_chunk .= $data_body_row;
					}
					$data_body_chunk .= $data_end;
					$pdf->writeHTML($data_body_chunk, true, false, false, false, '');
					unset($data_body_chunk);
					unset($data_body);
					$p = 0;

					//add new page
					$pdf->AddPage('L', array($page_width, $page_height));
				}

			}

		}

		//write divider
		$data_footer = '<tr><td colspan="'.$columns.'"></td></tr>';

		//write totals
		$data_footer .= '<tr>';
		$data_footer .= '<td><b>'.$text['label-total'].'</b></td>';
		$data_footer .= '<td>'.$z.'</td>';
		$data_footer .= '<td colspan="3"></td>';
		$data_footer .= '<td align="right"><b>'.number_format(round($total['tta'], 1), 0).'s</b></td>';
		$data_footer .= '<td align="right"><b>'.gmdate("G:i:s", $total['duration']).'</b></td>';
		$data_footer .= '<td align="right"><b>'.gmdate("G:i:s", round($total['billmsec'] / 1000, 0)).'</b></td>';
		$data_footer .= '<td align="right"><b>'.number_format(round(($total['pdd_ms'] / 1000), 2), 2).'s</b></td>';
		$data_footer .= '<td colspan="2"></td>';
		$data_footer .= '</tr>';

		//write divider
		$data_footer .= '<tr><td colspan="'.$columns.'"><hr></td></tr>';

		//write averages
		$data_footer .= '<tr>';
		$data_footer .= '<td><b>'.$text['label-average'].'</b></td>';
		$data_footer .= '<td colspan="4"></td>';
		$data_footer .= '<td align="right"><b>'.round(($total['tta'] / $z), 1).'</b></td>';
		$data_footer .= '<td align="right"><b>'.gmdate("G:i:s", ($total['duration'] / $z)).'</b></td>';
		$data_footer .= '<td align="right"><b>'.gmdate("G:i:s", round($total['billmsec'] / $z / 1000, 0)).'</b></td>';
		$data_footer .= '<td align="right"><b>'.number_format(round(($total['pdd_ms'] / $z / 1000), 2), 2).'s</b></td>';
		$data_footer .= '<td align="right"><b>'.round(($total['rtp_audio_in_mos'] / $z), 2).'</b></td>';
		$data_footer .= '<td></td>';
		$data_footer .= '</tr>';

		//write divider
		$data_footer .= '<tr><td colspan="'.$columns.'"><hr></td></tr>';

		//add last page
		if ($p >= 55) {
			$pdf->AddPage('L', array($page_width, $page_height));
		}
		//output remaining data
		$data_body_chunk = $data_start.$data_head;
		foreach ($data_body as $data_body_row) {
			$data_body_chunk .= $data_body_row;
		}
		$data_body_chunk .= $data_footer.$data_end;
		$pdf->writeHTML($data_body_chunk, true, false, false, false, '');
		unset($data_body_chunk);

		//define file name
		$pdf_filename = "ccr_".$_SESSION['domain_name']."_".date("Ymd_His").".pdf";

		header("Content-Type: application/force-download");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/download");
		header("Content-Description: File Transfer");
		header('Content-Disposition: attachment; filename="'.$pdf_filename.'"');
		header("Content-Type: application/pdf");
		header('Accept-Ranges: bytes');
		header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
		header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // date in the past

		// push pdf download
		$pdf -> Output($pdf_filename, 'D');	// Display [I]nline, Save to [F]ile, [D]ownload

	}

//generate invoices
	if (permission_exists('client_call_record_generate_invoice') && $export_format == 'invoice') {

		//load pdf libraries
		require_once "resources/tcpdf/tcpdf.php";
		require_once "resources/fpdi/fpdi.php";

		// Extend the TCPDF class to create custom Header and Footer
			class MYPDF extends FPDI {

				//Page header
				public function Header() {
					// Get header data
					$headerData = $this->getHeaderData();
					// Set font
					$this->SetFont('roboto', 'B', 6);
					// Header html data string
					$this->writeHTML($headerData['string'], true, false, false, false, '');
				}

				// Page footer
				public function Footer() {
					// Set font
					$this->SetFont('roboto', 'B', 7);
					// Footer html data string
					$footertext = '<table cellpadding="0" cellspacing="0" border="0" width="100%">';
					$footertext .= '<tr>';
					$footertext .= '<td align="left">Confidential</td>';
					$footertext .= '<td align="center">Date of Invoice: '.date("m/d/Y").'</td>';
					$footertext .= '<td align="right">Page '.$this->getAliasNumPage().' of '.$this->getAliasNbPages().'</td>';
					$footertext .= '</tr>';
					$footertext .= '</table>';
					$this->writeHTML($footertext, true, false, false, false, '');
				}
			}

		//determine page size
		switch ($_SESSION['fax']['page_size']['text']) {
			case 'a4':
				$page_width = 11.7; //in
				$page_height = 8.3; //in
				break;
			case 'legal':
				$page_width = 14; //in
				$page_height = 8.5; //in
				break;
			case 'letter':
			default	:
				$page_width = 11; //in
				$page_height = 8.5; //in
		}

		$result_alt = [];
		foreach ($result as $record) {
			if (strlen($record['client_id']) > 0 && strlen($record['access_code']) > 0)
				$result_alt[$record['client_id']][$record['access_code']][] = $record;
		}
		$result = $result_alt;
		unset($result_alt);

		//echo '<pre><code>';
		//print_r($result);
		//echo '</code></pre>';
		//exit;

		//define invoice storage path
		$invoice_storage_path = $_SERVER['DOCUMENT_ROOT'] . "storage/invoices/";

		if (sizeof($result) > 0) {
			foreach ($result as $client_id => $access_code_records) {

				// initialize pdf
				$pdf = new MYPDF('P', 'in');
				$pdf->SetAutoPageBreak(false);
				$pdf->setPrintHeader(true);
				$pdf->setPrintFooter(true);
				$pdf->SetMargins(0.25, 0.75, 0.25, true);
				$pdf->SetHeaderMargin(0.25);
				$pdf->SetFooterMargin(0.25);

				//sort array by access code (array keys) before printing
				ksort($access_code_records);

				//client details
				$header_html = '<table cellpadding="0" cellspacing="0" border="0" width="100%">';
				$header_html .= '<tr>';
				$header_html .= '<td width="50%" nowrap="nowrap" align="left">';
				$header_html .= '<b>Account Number: '.array_values($access_code_records)[0][0]['account_number'].'</b><br>';
				$header_html .= '<b>Client ID: '.$client_id.'</b><br>';
				$header_html .= '<b>'.array_values($access_code_records)[0][0]['agency'].'</b>';
				$header_html .= '</td>';
				$header_html .= '<td width="50%" nowrap="nowrap" align="right">';
				$header_html .= '<b>Avaza Language Services Corp.</b><br>';
				$header_html .= '<b>Over The Phone Interpretation Statement</b>';
				$header_html .= '</td>';
				$header_html .= '</tr>';
				$header_html .= '</table>';

				$pdf->setHeaderData($ln='', $lw=0, $ht='', $hs=$header_html, $tc=array(0,0,0), $lc=array(0,0,0));

				//set default font
				$pdf->SetFont('roboto', '', 6);
				//add new page
				$pdf->AddPage('P', array($page_width, $page_height));

				//set the number of columns
				$columns = 12;

				/*
				//client details
				$client_details = '<table cellpadding="0" cellspacing="0" border="0" width="100%">';
				$client_details .= '<tr>';
				$client_details .= '<td width="50%" nowrap="nowrap" align="left">';
				$client_details .= '<b>Account Number: '.array_values($access_code_records)[0][0]['account_number'].'</b><br>';
				$client_details .= '<b>Client ID: '.$client_id.'</b><br>';
				$client_details .= '<b>'.array_values($access_code_records)[0][0]['agency'].'</b>';
				$client_details .= '</td>';
				$client_details .= '<td width="50%" nowrap="nowrap" align="right">';
				$client_details .= '<b>Avaza Language Services Corp.</b><br>';
				$client_details .= '<b>Over The Phone Interpretation Statement</b>';
				$client_details .= '</td>';
				$client_details .= '</tr>';
				$client_details .= '</table>';

				//write client details
				$pdf->writeHTML($client_details, true, false, false, false, '');

				$pdf->Ln();
				*/

				//write the table column headers
				$data_start = '<table cellpadding="1" cellspacing="0" border="0" width="100%">';
				$data_end = '</table>';

				$data_head = '<tr><td colspan="'.$columns.'" style="border-bottom:1px solid black;"></td></tr>';
				$data_head .= '<tr>';
				$data_head .= '<td width="5%" nowrap="nowrap" align="center">ITEM</td>';
				$data_head .= '<td width="8%" nowrap="nowrap" align="center">JOB NUMBER</td>';
				$data_head .= '<td width="8%" nowrap="nowrap" align="center">DATE</td>';
				$data_head .= '<td width="8%" nowrap="nowrap" align="center">START TIME</td>';
				$data_head .= '<td width="8%" nowrap="nowrap" align="center">END TIME</td>';
				$data_head .= '<td width="7%" nowrap="nowrap" align="center">LANGUAGE</td>';
				$data_head .= '<td width="10%" nowrap="nowrap" align="center">INTERPRETER ID</td>';
				$data_head .= '<td width="12%" nowrap="nowrap" align="center">CONTACT PERSON</td>';
				$data_head .= '<td width="10%" nowrap="nowrap" align="center">CONTACT NUMBER</td>';
				$data_head .= '<td width="8%" nowrap="nowrap" align="center">RATE CODE</td>';
				$data_head .= '<td width="8%" nowrap="nowrap" align="center">MINUTES</td>';
				$data_head .= '<td width="8%" nowrap="nowrap" align="center">CHARGE</td>';
				$data_head .= '</tr>';
				$data_head .= '<tr><td colspan="'.$columns.'" style="border-top:1px solid black;"></td></tr>';

				//initialize summary total variables
				$total['summary_minutes'] = 0;
				$total['summary_charge'] = 0;

				$x = 0; // call record counter
				$y = 0; // access code counter
				$p = 0; // per page counter
				$p_old = 0;
				$write_head = true;
				foreach ($access_code_records as $access_code => $client_call_records) {

					//initialize total variables
					$total['minutes'] = 0;
					$total['charge'] = 0;

					//start empty table row
					$data_body = [];

					$data_body[$p] .= '<tr>';
					$data_body[$p] .= '<td colspan="'.$columns.'" align="left" style="border-top:1px solid black;"><b>Division Name: '.$client_call_records[0]['division'].'</b></td>';
					$data_body[$p] .= '</tr>';
					$p++;
					$data_body[$p] .= '<tr>';
					$data_body[$p] .= '<td colspan="'.$columns.'" align="left" style="border-bottom:1px solid black;"><b>Access Code: '.$access_code.'</b></td>';
					$data_body[$p] .= '</tr>';
					$p++;

					//write the row cells
					foreach ($client_call_records as $ccr_num => $fields) {
						$data_body[$p] .= '<tr>';
						$data_body[$p] .= '<td width="5%" nowrap="nowrap" align="center">'.($x+1).'</td>';
						$data_body[$p] .= '<td width="8%" nowrap="nowrap" align="center">'.escape($fields['job_number']).'</td>';
						$data_body[$p] .= '<td width="8%" nowrap="nowrap" align="center">'.date('m/d/Y', strtotime($fields['start_timestamp'])).'</td>';
						$data_body[$p] .= '<td width="8%" nowrap="nowrap" align="center">'.date('H:i', strtotime($fields['start_timestamp'])).'</td>';
						$data_body[$p] .= '<td width="8%" nowrap="nowrap" align="center">'.date('H:i', strtotime($fields['end_stamp'])).'</td>';
						$languageCode = (new ISO639)->codeByLanguage($fields['interpret_language']);
						$data_body[$p] .= '<td width="7%" nowrap="nowrap" align="center">'.$languageCode.'</td>';
						$data_body[$p] .= '<td width="10%" nowrap="nowrap" align="center">'.$fields['interpreter_id'].'</td>';
						$numbers_only = preg_replace("/[^\d]/", "", $fields['caller_id_number']);
						$contact_number = preg_replace("/^1?(\d{3})(\d{3})(\d{4})$/", "1($1) $2-$3", $numbers_only);
						$data_body[$p] .= '<td width="12%" nowrap="nowrap" align="center">'.ucwords(trim($fields['caller_name'])).'</td>';
						$data_body[$p] .= '<td width="10%" nowrap="nowrap" align="center">'.$contact_number.'</td>';
						$languageSet = (new ISO639)->languageSetByLanguage($fields['interpret_language']);
						$data_body[$p] .= '<td width="8%" nowrap="nowrap" align="center">'.$languageSet.'</td>';
						$minutes = (($fields['interpret_duration'] % 60) > 0) ? floor($fields['interpret_duration'] / 60) + 1 : ceil($fields['interpret_duration'] / 60);
						$total['minutes'] += ($minutes > 0) ? $minutes : 0;
						$total['summary_minutes'] += ($minutes > 0) ? $minutes : 0;
						$data_body[$p] .= '<td width="8%" nowrap="nowrap" align="center">'.$minutes.'</td>';
						$charge = $fields[strtolower($languageSet)] * $minutes;
						$charge_amount = (strlen($fields[strtolower($languageSet)]) > 0) ? "$".number_format($charge, 2) : "";
						$total['charge'] += ($charge > 0) ? $charge : 0;
						$total['summary_charge'] += ($charge > 0) ? $charge : 0;
						$data_body[$p] .= '<td width="8%" nowrap="nowrap" align="center">'.$charge_amount.'</td>';
						$data_body[$p] .= '</tr>';

						//reports summary data grouped by access code and language
						$reports_data['access_code'][$access_code]['minutes'] += ($minutes > 0) ? $minutes : 0;
						$reports_data['access_code'][$access_code]['calls'] += 1;
						$reports_data['access_code'][$access_code]['charges'] += ($charge > 0) ? $charge : 0;
						$reports_data['language'][$fields['interpret_language']]['minutes'] += ($minutes > 0) ? $minutes : 0;
						$reports_data['language'][$fields['interpret_language']]['calls'] += 1;
						$reports_data['language'][$fields['interpret_language']]['charges'] += ($charge > 0) ? $charge : 0;

						$x++;
						$p++;

						if ($p == 60) {
							//add new page
							$pdf->AddPage('P', array($page_width, $page_height));

							//output data
							$data_body_chunk = $data_start.$data_head;
							foreach ($data_body as $data_body_row) {
								$data_body_chunk .= $data_body_row;
							}
							$data_body_chunk .= $data_end;
							$pdf->writeHTML($data_body_chunk, true, false, false, false, '');
							unset($data_body_chunk);
							unset($data_body);
							$p = 0;
						}
					}

					//write totals
					$data_footer = '<tr>';
					$data_footer .= '<td colspan="'.($columns - 3).'"></td>';
					$data_footer .= '<td align="center" style="border-top:1px solid black;border-bottom:1px solid black;border-left:1px solid black;"><b>SUBTOTAL</b></td>';
					$data_footer .= '<td align="center" style="border-top:1px solid black;border-bottom:1px solid black;"><b>'.$total['minutes'].'</b></td>';
					$data_footer .= '<td align="center" style="border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;"><b>'."$".number_format($total['charge'], 2).'</b></td>';
					$data_footer .= '</tr>';
					$p++;

					$p_old = $p;
					if ($p >= 55) {
						//add new page
						$pdf->AddPage('P', array($page_width, $page_height));
						$p = 0;
						$write_head = true;
					}
					//output data
					if ($write_head) {
						$data_body_chunk = $data_start.$data_head;
					} else {
						$data_body_chunk = $data_start;
					}
					foreach ($data_body as $data_body_row) {
						$data_body_chunk .= $data_body_row;
					}
					$data_body_chunk .= $data_footer.$data_end;
					$pdf->writeHTML($data_body_chunk, true, false, false, false, '');
					unset($data_body_chunk);

					$y++;
					$write_head = false;
				}

				//restart from last per page counter
				$p = $p_old;

				$pdf->Ln();
				$p++;

				//summary
				$summary = '<table cellpadding="1" cellspacing="0" border="0" width="100%">';
				$summary .= '<tr>';
				$summary .= '<td width="84%" nowrap="nowrap" align="left" style="padding-left:25px;border-top:1px solid black;border-bottom:1px solid black;border-left:1px solid black;"><b>SUMMARY</b></td>';
				$summary .= '<td width="8%" nowrap="nowrap" align="center" style="border-top:1px solid black;border-bottom:1px solid black;"><b>MINUTES</b></td>';
				$summary .= '<td width="8%" nowrap="nowrap" align="center" style="border-top:1px solid black;border-right:1px solid black;border-bottom:1px solid black;"><b>CHARGES</b></td>';
				$summary .= '</tr>';
				$p++;
				$summary .= '<tr>';
				$summary .= '<td width="21%" nowrap="nowrap" align="left"></td>';
				$summary .= '<td width="63%" nowrap="nowrap" align="center" style="border-bottom:1px solid black;border-left:1px solid black;"><b>Over-the-phone Interpretation:</b></td>';
				$summary .= '<td width="8%" nowrap="nowrap" align="center" style="border-bottom:1px solid black;"><b>'.$total['summary_minutes'].'</b></td>';
				$summary .= '<td width="8%" nowrap="nowrap" align="center" style="border-bottom:1px solid black;border-right:1px solid black;"><b>'."$".number_format($total['summary_charge'], 2).'</b></td>';
				$summary .= '</tr>';
				$p++;
				$summary .= '</table>';

				//write summary
				$pdf->writeHTML($summary, true, false, false, false, '');

				$pdf->Ln();
				$p++;

				//total charges
				$total_charges = '<table cellpadding="1" cellspacing="0" border="0" width="100%">';
				$total_charges .= '<tr>';
				$total_charges .= '<td width="52%" nowrap="nowrap" align="left"></td>';
				$total_charges .= '<td width="32%" nowrap="nowrap" align="center" style="font-size:10px;border-top:1px solid black;border-bottom:1px solid black;border-left:1px solid black;"><b>TOTAL CHARGES</b></td>';
				$total_charges .= '<td width="16%" nowrap="nowrap" align="center" style="font-size:10px;border-top:1px solid black;border-right:1px solid black;border-bottom:1px solid black;"><b>'."$".number_format($total['summary_charge'], 2).'</b></td>';
				$total_charges .= '</tr>';
				$p++;
				$total_charges .= '</table>';

				//write total charges
				$pdf->writeHTML($total_charges, true, false, false, false, '');

				$pdf->Ln();
				$p++;
				$pdf->Ln(0.5);
				$p += 5;

				if ($p >= 60) {
					//add new page
					//$pdf->AddPage('P', array($page_width, $page_height));
					$p = 0;
				}

				//reports
				//$reports = '<table cellpadding="1" cellspacing="0" border="0" width="100%">';
				$reports .= '<tr>';
				$reports .= '<td colspan="'.$columns.'"width="100%" nowrap="nowrap" align="left" style="padding-left:25px;border-top:1px solid black;border-right:1px solid black;border-bottom:1px solid black;border-left:1px solid black;"><b>REPORTS</b></td>';
				$reports .= '</tr>';
				$p++;
				//write divider
				$reports .= '<tr><td colspan="'.$columns.'"></td></tr>';
				$p++;
				//reports by access code
				$reports .= '<tr>';
				$reports .= '<td colspan="'.($columns - 6).'"></td>';
				$reports .= '<td width="10%" nowrap="nowrap" align="center" style="border-top:1px solid black;border-bottom:1px solid black;border-left:1px solid black;"><b>Access Code</b></td>';
				$reports .= '<td width="8%" nowrap="nowrap" align="center" style="border-top:1px solid black;border-bottom:1px solid black;"><b>Minutes</b></td>';
				$reports .= '<td width="8%" nowrap="nowrap" align="center" style="border-top:1px solid black;border-bottom:1px solid black;"><b>Calls</b></td>';
				$reports .= '<td width="8%" nowrap="nowrap" align="center" style="border-top:1px solid black;border-bottom:1px solid black;"><b>% Total</b></td>';
				$reports .= '<td width="8%" nowrap="nowrap" align="center" style="border-top:1px solid black;border-right:1px solid black;border-bottom:1px solid black;"><b>Charges</b></td>';
				$reports .= '<td></td>';
				$reports .= '</tr>';
				$p++;
				ksort($reports_data['access_code']); // sort array by access code (array keys) before printing
				foreach ($reports_data['access_code'] as $access_code => $data) {
					$reports .= '<tr>';
					$reports .= '<td colspan="'.($columns - 6).'"></td>';
					$reports .= '<td width="10%" nowrap="nowrap" align="center" style="border-top:1px solid black;border-bottom:1px solid black;border-left:1px solid black;"><b>'.$access_code.'</b></td>';
					$reports .= '<td width="8%" nowrap="nowrap" align="center" style="border-top:1px solid black;border-bottom:1px solid black;"><b>'.$data['minutes'].'</b></td>';
					$reports .= '<td width="8%" nowrap="nowrap" align="center" style="border-top:1px solid black;border-bottom:1px solid black;"><b>'.$data['calls'].'</b></td>';
					$reports .= '<td width="8%" nowrap="nowrap" align="center" style="border-top:1px solid black;border-bottom:1px solid black;"><b>'.round((float)($data['charges'] / array_sum(array_column($reports_data['access_code'], 'charges'))) * 100).'%'.'</b></td>';
					$reports .= '<td width="8%" nowrap="nowrap" align="center" style="border-top:1px solid black;border-right:1px solid black;border-bottom:1px solid black;"><b>'."$".number_format($data['charges'], 2).'</b></td>';
					$reports .= '<td></td>';
					$reports .= '</tr>';
					$p++;
					if ($p == 60) {
						//add new page
						$pdf->AddPage('P', array($page_width, $page_height));

						//write reports
						$pdf->writeHTML($data_start.$reports.$data_end, true, false, false, false, '');
						unset($reports);
						$p = 0;
					}
				}
				$reports .= '<tr>';
				$reports .= '<td colspan="'.($columns - 6).'"></td>';
				$reports .= '<td width="10%" nowrap="nowrap" align="center" style="border-top:1px solid black;border-bottom:1px solid black;border-left:1px solid black;"><b>TOTALS</b></td>';
				$reports .= '<td width="8%" nowrap="nowrap" align="center" style="border-top:1px solid black;border-bottom:1px solid black;"><b>'.array_sum(array_column($reports_data['access_code'], 'minutes')).'</b></td>';
				$reports .= '<td width="8%" nowrap="nowrap" align="center" style="border-top:1px solid black;border-bottom:1px solid black;"><b>'.array_sum(array_column($reports_data['access_code'], 'calls')).'</b></td>';
				$reports .= '<td width="8%" nowrap="nowrap" align="center" style="border-top:1px solid black;border-bottom:1px solid black;"><b>'.round((float)(array_sum(array_column($reports_data['access_code'], 'charges')) / array_sum(array_column($reports_data['access_code'], 'charges'))) * 100).'%'.'</b></td>';
				$reports .= '<td width="8%" nowrap="nowrap" align="center" style="border-top:1px solid black;border-right:1px solid black;border-bottom:1px solid black;"><b>'."$".number_format(array_sum(array_column($reports_data['access_code'], 'charges')), 2).'</b></td>';
				$reports .= '<td></td>';
				$reports .= '</tr>';
				$p++;

				if ($p >= 60) {
					//add new page
					$pdf->AddPage('P', array($page_width, $page_height));

					//write reports
					$pdf->writeHTML($data_start.$reports.$data_end, true, false, false, false, '');
					unset($reports);
					$p = 0;
				}

				//write divider
				$reports .= '<tr><td colspan="'.$columns.'"></td></tr>';
				$p++;
				//reports by language
				$reports .= '<tr>';
				$reports .= '<td colspan="'.($columns - 6).'"></td>';
				$reports .= '<td width="10%" nowrap="nowrap" align="center" style="border-top:1px solid black;border-bottom:1px solid black;border-left:1px solid black;"><b>Language</b></td>';
				$reports .= '<td width="8%" nowrap="nowrap" align="center" style="border-top:1px solid black;border-bottom:1px solid black;"><b>Minutes</b></td>';
				$reports .= '<td width="8%" nowrap="nowrap" align="center" style="border-top:1px solid black;border-bottom:1px solid black;"><b>Calls</b></td>';
				$reports .= '<td width="8%" nowrap="nowrap" align="center" style="border-top:1px solid black;border-bottom:1px solid black;"><b>% Total</b></td>';
				$reports .= '<td width="8%" nowrap="nowrap" align="center" style="border-top:1px solid black;border-right:1px solid black;border-bottom:1px solid black;"><b>Charges</b></td>';
				$reports .= '<td></td>';
				$reports .= '</tr>';
				$p++;
				ksort($reports_data['language']); // sort array by language (array keys) before printing
				foreach ($reports_data['language'] as $language => $data) {
					$reports .= '<tr>';
					$reports .= '<td colspan="'.($columns - 6).'"></td>';
					$reports .= '<td width="10%" nowrap="nowrap" align="center" style="border-top:1px solid black;border-bottom:1px solid black;border-left:1px solid black;"><b>'.strtoupper($language).'</b></td>';
					$reports .= '<td width="8%" nowrap="nowrap" align="center" style="border-top:1px solid black;border-bottom:1px solid black;"><b>'.$data['minutes'].'</b></td>';
					$reports .= '<td width="8%" nowrap="nowrap" align="center" style="border-top:1px solid black;border-bottom:1px solid black;"><b>'.$data['calls'].'</b></td>';
					$reports .= '<td width="8%" nowrap="nowrap" align="center" style="border-top:1px solid black;border-bottom:1px solid black;"><b>'.round((float)($data['charges'] / array_sum(array_column($reports_data['language'], 'charges'))) * 100).'%'.'</b></td>';
					$reports .= '<td width="8%" nowrap="nowrap" align="center" style="border-top:1px solid black;border-right:1px solid black;border-bottom:1px solid black;"><b>'."$".number_format($data['charges'], 2).'</b></td>';
					$reports .= '<td></td>';
					$reports .= '</tr>';
					$p++;
					if ($p == 60) {
						//add new page
						$pdf->AddPage('P', array($page_width, $page_height));

						//write reports
						$pdf->writeHTML($data_start.$reports.$data_end, true, false, false, false, '');
						unset($reports);
						$p = 0;
					}
				}
				$reports .= '<tr>';
				$reports .= '<td colspan="'.($columns - 6).'"></td>';
				$reports .= '<td width="10%" nowrap="nowrap" align="center" style="border-top:1px solid black;border-bottom:1px solid black;border-left:1px solid black;"><b>TOTALS</b></td>';
				$reports .= '<td width="8%" nowrap="nowrap" align="center" style="border-top:1px solid black;border-bottom:1px solid black;"><b>'.array_sum(array_column($reports_data['language'], 'minutes')).'</b></td>';
				$reports .= '<td width="8%" nowrap="nowrap" align="center" style="border-top:1px solid black;border-bottom:1px solid black;"><b>'.array_sum(array_column($reports_data['language'], 'calls')).'</b></td>';
				$reports .= '<td width="8%" nowrap="nowrap" align="center" style="border-top:1px solid black;border-bottom:1px solid black;"><b>'.round((float)(array_sum(array_column($reports_data['language'], 'charges')) / array_sum(array_column($reports_data['language'], 'charges'))) * 100).'%'.'</b></td>';
				$reports .= '<td width="8%" nowrap="nowrap" align="center" style="border-top:1px solid black;border-right:1px solid black;border-bottom:1px solid black;"><b>'."$".number_format(array_sum(array_column($reports_data['language'], 'charges')), 2).'</b></td>';
				$reports .= '<td></td>';
				$reports .= '</tr>';
				$p++;
				//$reports .= '</table>';

				if ($p >= 60) {
					//add new page
					$pdf->AddPage('P', array($page_width, $page_height));
				}

				//write reports
				$pdf->writeHTML($data_start.$reports.$data_end, true, false, false, false, '');
				unset($reports_data);
				unset($reports);
				unset($p);

				//define pdf file name
				//$pdf_filename = "Invoice_".$client_id."_".date("Ymd_His").".pdf";
				$div_state_part = trim(array_values($access_code_records)[0][0]['div_state']);
				$division_part = preg_replace('/\s+/', '-', trim(array_values($access_code_records)[0][0]['division']));
				$start_date = strlen(trim($interpret_stamp_begin)) > 0 ? date('Ymd', strtotime(trim($interpret_stamp_begin))) : "";
				$end_date = strlen(trim($interpret_stamp_end)) > 0 ? date('Ymd', strtotime(trim($interpret_stamp_end))) : "";
				$period_part = (strlen($start_date) > 0 ? $start_date."-" : "").(strlen($start_date) > 0 ? $end_date : "");
				$pdf_filename = strlen($div_state_part) > 0 ? strtoupper($div_state_part)."-" : "";
				$pdf_filename .= $client_id."-".$division_part."-".(strlen($period_part)>0?$period_part:date("Ymd")).".pdf";

				//sanitize the filename
				$pdf_filename = filter_filename($pdf_filename);

				//include pdf file in zip archive
				$pdfs_to_zip[] = $pdf_filename;

				//save pdf file
				$pdf->Output($invoice_storage_path.$pdf_filename, 'F');
			}

			//define zip file name
			$zip_filename = "Generated_Client_Invoices_".date("Ymd_His").".zip";

			// ZIP the PDF files
			exec('cd '.$invoice_storage_path.' && zip -1 -9 '.$zip_filename.' '.implode(' ', $pdfs_to_zip));

			//delete pdf files after creating zip file
			foreach ($pdfs_to_zip as $pdf_file) {
				unlink($invoice_storage_path.$pdf_file);
			}

			header('Content-type: application/zip');
			header('Content-Disposition: attachment; filename="'.$zip_filename.'"');
			readfile($invoice_storage_path.$zip_filename);

			//delete the zip file
			unlink($invoice_storage_path.$zip_filename);
		} else {
			// return an empty pdf

			// initialize pdf
			$pdf = new FPDI('L', 'in');
			$pdf->SetAutoPageBreak(false);
			$pdf->setPrintHeader(false);
			$pdf->setPrintFooter(false);
			$pdf->SetMargins(0.5, 0.5, 0.5, true);

			//set default font
			$pdf->SetFont('helvetica', '', 7);
			//add new page
			$pdf->AddPage('L', array($page_width, $page_height));

			//print an empty string
			$pdf->writeHTML('', true, false, false, false, '');

			//define file name
			$pdf_filename = "Invoice_".$_SESSION['domain_name']."_".date("Ymd_His").".pdf";

			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-Type: application/download");
			header("Content-Description: File Transfer");
			header('Content-Disposition: attachment; filename="'.$pdf_filename.'"');
			header("Content-Type: application/pdf");
			header('Accept-Ranges: bytes');
			header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
			header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // date in the past

			// push pdf download
			$pdf -> Output($pdf_filename, 'D');	// Display [I]nline, Save to [F]ile, [D]ownload
		}

		/*
		//define file name
		$pdf_filename = "ccr_".$_SESSION['domain_name']."_".date("Ymd_His").".pdf";

		header("Content-Type: application/force-download");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/download");
		header("Content-Description: File Transfer");
		header('Content-Disposition: attachment; filename="'.$pdf_filename.'"');
		header("Content-Type: application/pdf");
		header('Accept-Ranges: bytes');
		header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
		header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // date in the past

		// push pdf download
		$pdf -> Output($pdf_filename, 'D');	// Display [I]nline, Save to [F]ile, [D]ownload
		*/
	}

	function filter_filename($filename, $beautify=true) {
		// sanitize filename
		$filename = preg_replace(
			'~
			[<>:"/\\\|?*]|            # file system reserved https://en.wikipedia.org/wiki/Filename#Reserved_characters_and_words
			[\x00-\x1F]|             # control characters http://msdn.microsoft.com/en-us/library/windows/desktop/aa365247%28v=vs.85%29.aspx
			[\x7F\xA0\xAD]|          # non-printing characters DEL, NO-BREAK SPACE, SOFT HYPHEN
			[#\[\]@!$&\'()+,;=]|     # URI reserved https://www.rfc-editor.org/rfc/rfc3986#section-2.2
			[{}^\~`]                 # URL unsafe characters https://www.ietf.org/rfc/rfc1738.txt
			~x',
			'-', $filename);
		// avoids ".", ".." or ".hiddenFiles"
		$filename = ltrim($filename, '.-');
		// optional beautification
		if ($beautify) $filename = beautify_filename($filename);
		// maximize filename length to 255 bytes http://serverfault.com/a/9548/44086
		$ext = pathinfo($filename, PATHINFO_EXTENSION);
		$filename = mb_strcut(pathinfo($filename, PATHINFO_FILENAME), 0, 255 - ($ext ? strlen($ext) + 1 : 0), mb_detect_encoding($filename)) . ($ext ? '.' . $ext : '');
		return $filename;
	}

	function beautify_filename($filename) {
		// reduce consecutive characters
		$filename = preg_replace(array(
			// "file   name.zip" becomes "file-name.zip"
			'/ +/',
			// "file___name.zip" becomes "file-name.zip"
			'/_+/',
			// "file---name.zip" becomes "file-name.zip"
			'/-+/'
		), '-', $filename);
		$filename = preg_replace(array(
			// "file--.--.-.--name.zip" becomes "file.name.zip"
			'/-*\.-*/',
			// "file...name..zip" becomes "file.name.zip"
			'/\.{2,}/'
		), '.', $filename);
		// lowercase for windows/unix interoperability http://support.microsoft.com/kb/100625
		#$filename = mb_strtolower($filename, mb_detect_encoding($filename));
		// ".file-name.-" becomes "file-name"
		$filename = trim($filename, '.-');
		return $filename;
	}

?>
