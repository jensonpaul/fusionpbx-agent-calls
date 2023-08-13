<?php

//set the include path
	$conf = glob("{/usr/local/etc,/etc}/fusionpbx/config.conf", GLOB_BRACE);
	set_include_path(parse_ini_file($conf[0])['document.root']);

//includes files
	require_once "resources/require.php";
	require_once "resources/check_auth.php";
	require_once "resources/paging.php";

//check permissions
	if (permission_exists('client_view')) {
		//access granted
	}
	else {
		echo "access denied";
		exit;
	}

//add multi-lingual support
	$language = new text;
	$text = $language->get();

//get the http post data
	if (is_array($_POST['clients'])) {
		$action = $_POST['action'];
		$search = $_POST['search'];
		$clients = $_POST['clients'];
	}

//process the http post data by action
	if ($action != '' && is_array($clients) && @sizeof($clients) != 0) {
		switch ($action) {
			case 'copy':
				if (permission_exists('client_add')) {
					$obj = new clients;
					$obj->copy($clients);
				}
				break;
			case 'delete':
				if (permission_exists('client_delete')) {
					$obj = new clients;
					$obj->delete($clients);
				}
				break;
		}

		header('Location: clients.php'.($search != '' ? '?search='.urlencode($search) : null));
		exit;
	}

//get variables used to control the order
	$order_by = $_GET["order_by"];
	$order = $_GET["order"];

//add the search string
	if (isset($_GET["search"])) {
		$search =  strtolower($_GET["search"]);
		$sql_search = " (";
		$sql_search .= "	lower(client_name) like :search ";
		$sql_search .= "	or lower(invoice_detail) like :search ";
		$sql_search .= "	or lower(agency) like :search ";
		$sql_search .= "	or lower(division) like :search ";
		$sql_search .= "	or lower(county) like :search ";
		$sql_search .= "	or lower(account_number) like :search ";
		$sql_search .= "	or lower(div_contact) like :search ";
		$sql_search .= "	or lower(access_code) like :search ";
		$sql_search .= "	or lower(contract_number) like :search ";
		$sql_search .= "	or lower(client_id) like :search ";
		$sql_search .= ") ";
		$parameters['search'] = '%'.$search.'%';
	}

//get the count
	$sql = "select count(client_uuid) from v_clients ";
	if (isset($sql_search)) {
		$sql .= "where ".$sql_search;
	}
	$database = new database;
	$num_rows = $database->select($sql, $parameters, 'column');

//prepare to page the results
	$rows_per_page = ($_SESSION['domain']['paging']['numeric'] != '') ? $_SESSION['domain']['paging']['numeric'] : 50;
	$param = $search ? "&search=".$search : null;
	$page = is_numeric($_GET['page']) ? $_GET['page'] : 0;
	list($paging_controls, $rows_per_page) = paging($num_rows, $param, $rows_per_page);
	list($paging_controls_mini, $rows_per_page) = paging($num_rows, $param, $rows_per_page, true);
	$offset = $rows_per_page * $page;

//get the  list
	$sql = str_replace('count(client_uuid)', '*', $sql);
	$sql .= order_by($order_by, $order, 'insert_date', 'desc');
	$sql .= limit_offset($rows_per_page, $offset);
	$database = new database;
	$clients = $database->select($sql, $parameters, 'all');
	unset($sql, $parameters);

//set the http header
if ($_REQUEST['type'] == "csv") {

	//get list of all client records
		$sql = "select * from v_clients ";
		$database = new database;
		$clients_all_csv = $database->select($sql, null, 'all');
		unset($sql);

	//set the headers
		header('Content-type: application/octet-binary');
		header('Content-Disposition: attachment; filename=client-list.csv');

	//show the column names on the first line
		$z = 0;
		foreach($clients_all_csv[1] as $key => $val) {
			if ($z == 0) {
				echo '"'.$key.'"';
			}
			else {
				echo ',"'.$key.'"';
			}
			$z++;
		}
		echo "\n";

	//add the values to the csv
		$x = 0;
		foreach($clients_all_csv as $clients_csv) {
			$z = 0;
			foreach($clients_csv as $key => $val) {
				if ($z == 0) {
					echo '"'.$clients_all_csv[$x][$key].'"';
				}
				else {
					echo ',"'.$clients_all_csv[$x][$key].'"';
				}
				$z++;
			}
			echo "\n";
			$x++;
		}
		exit;
}

//create token
	$object = new token;
	$token = $object->create($_SERVER['PHP_SELF']);

//include the header
	$document['title'] = $text['title-clients'];
	require_once "resources/header.php";

//show the content
	echo "<div class='action_bar' id='action_bar'>\n";
	echo "	<div class='heading'><b>".$text['header-clients']." (".$num_rows.")</b></div>\n";
	echo "	<div class='actions'>\n";
	if (permission_exists('client_import')) {
		echo button::create(['type'=>'button','label'=>$text['button-import'],'icon'=>$_SESSION['theme']['button_icon_import'],'link'=>'client_import.php']);
	}
	echo button::create(['type'=>'button','label'=>$text['button-download_csv'],'icon'=>$_SESSION['theme']['button_icon_download'],'collapse'=>'hide-sm-dn','style'=>'margin-right: 15px;','link'=>'clients.php?'.(strlen($_SERVER["QUERY_STRING"]) > 0 ? $_SERVER["QUERY_STRING"].'&' : null).'type=csv']);
	if (permission_exists('client_add')) {
		echo button::create(['type'=>'button','label'=>$text['button-add'],'icon'=>$_SESSION['theme']['button_icon_add'],'id'=>'btn_add','link'=>'client_edit.php']);
	}
	if (permission_exists('client_add') && $clients) {
		echo button::create(['type'=>'button','label'=>$text['button-copy'],'icon'=>$_SESSION['theme']['button_icon_copy'],'id'=>'btn_copy','name'=>'btn_copy','style'=>'display: none;','onclick'=>"modal_open('modal-copy','btn_copy');"]);
		echo modal::create(['id'=>'modal-copy','type'=>'copy','actions'=>button::create(['type'=>'button','label'=>$text['button-continue'],'icon'=>'check','id'=>'btn_copy','style'=>'float: right; margin-left: 15px;','collapse'=>'never','onclick'=>"modal_close(); list_action_set('copy'); list_form_submit('form_list');"])]);
	}
	if (permission_exists('client_delete') && $clients) {
		echo button::create(['type'=>'button','label'=>$text['button-delete'],'icon'=>$_SESSION['theme']['button_icon_delete'],'id'=>'btn_delete','name'=>'btn_delete','style'=>'display: none;','onclick'=>"modal_open('modal-delete','btn_delete');"]);
		echo modal::create(['id'=>'modal-delete','type'=>'delete','actions'=>button::create(['type'=>'button','label'=>$text['button-continue'],'icon'=>'check','id'=>'btn_delete','style'=>'float: right; margin-left: 15px;','collapse'=>'never','onclick'=>"modal_close(); list_action_set('delete'); list_form_submit('form_list');"])]);
	}
	echo 		"<form id='form_search' class='inline' method='get'>\n";
	echo 		"<input type='text' class='txt list-search' name='search' id='search' value=\"".escape($search)."\" placeholder=\"".$text['label-search']."\" onkeydown=''>";
	echo button::create(['label'=>$text['button-search'],'icon'=>$_SESSION['theme']['button_icon_search'],'type'=>'submit','id'=>'btn_search']);
	echo button::create(['label'=>$text['button-reset'],'icon'=>$_SESSION['theme']['button_icon_reset'],'type'=>'button','id'=>'btn_reset','link'=>'clients.php','style'=>($search == '' ? 'display: none;' : null)]);
	if ($paging_controls_mini != '') {
		echo 	"<span style='margin-left: 15px;'>".$paging_controls_mini."</span>\n";
	}
	echo "		</form>\n";
	echo "	</div>\n";
	echo "	<div style='clear: both;'></div>\n";
	echo "</div>\n";

	if (permission_exists('client_add') && $clients) {
		echo modal::create(['id'=>'modal-copy','type'=>'copy','actions'=>button::create(['type'=>'button','label'=>$text['button-continue'],'icon'=>'check','id'=>'btn_copy','style'=>'float: right; margin-left: 15px;','collapse'=>'never','onclick'=>"modal_close(); list_action_set('copy'); list_form_submit('form_list');"])]);
	}
	if (permission_exists('client_delete') && $clients) {
		echo modal::create(['id'=>'modal-delete','type'=>'delete','actions'=>button::create(['type'=>'button','label'=>$text['button-continue'],'icon'=>'check','id'=>'btn_delete','style'=>'float: right; margin-left: 15px;','collapse'=>'never','onclick'=>"modal_close(); list_action_set('delete'); list_form_submit('form_list');"])]);
	}

	echo $text['description-clients']."\n";
	echo "<br /><br />\n";

	echo "<form id='form_list' method='post'>\n";
	echo "<input type='hidden' id='action' name='action' value=''>\n";
	echo "<input type='hidden' name='search' value=\"".escape($search)."\">\n";

	echo "<table class='list'>\n";
	echo "<tr class='list-header'>\n";
	if (permission_exists('client_add') || permission_exists('client_delete')) {
		echo "	<th class='checkbox'>\n";
		echo "		<input type='checkbox' id='checkbox_all' name='checkbox_all' onclick='list_all_toggle(); checkbox_on_change(this);' ".($clients ?: "style='visibility: hidden;'").">\n";
		echo "	</th>\n";
	}
	echo th_order_by('client_id', $text['label-client_id'], $order_by, $order);
	echo th_order_by('client_name', $text['label-client_name'], $order_by, $order);
	echo th_order_by('access_code', $text['label-access_code'], $order_by, $order);
	echo th_order_by('invoice_detail', $text['label-invoice_detail'], $order_by, $order);
	echo th_order_by('active', $text['label-active'], $order_by, $order);
	#echo th_order_by('client_description', $text['label-description'], $order_by, $order, null, "class='hide-sm-dn'");
	if (permission_exists('client_edit') && $_SESSION['theme']['list_row_edit_button']['boolean'] == 'true') {
		echo "	<td class='action-button'>&nbsp;</td>\n";
	}
	echo "</tr>\n";

	if (is_array($clients) && @sizeof($clients) != 0) {
		$x = 0;
		foreach ($clients as $row) {
			$list_row_url = "client_edit.php?id=".urlencode($row['client_uuid']);
			echo "<tr class='list-row' href='".$list_row_url."'>\n";
			if (permission_exists('client_add') || permission_exists('client_delete')) {
				echo "	<td class='checkbox'>\n";
				echo "		<input type='checkbox' name='clients[$x][checked]' id='checkbox_".$x."' value='true' onclick=\"checkbox_on_change(this); if (!this.checked) { document.getElementById('checkbox_all').checked = false; }\">\n";
				echo "		<input type='hidden' name='clients[$x][uuid]' value='".escape($row['client_uuid'])."' />\n";
				echo "	</td>\n";
			}
			echo "	<td>".escape($row['client_id'])."&nbsp;</td>\n";
			echo "	<td>";
			if (permission_exists('client_edit')) {
				echo "<a href='".$list_row_url."'>".escape($row['client_name'])."</a>";
			}
			else {
				echo escape($row['client_name']);
			}
			echo "	</td>\n";
			echo "	<td>".escape($row['access_code'])."&nbsp;</td>\n";
			echo "	<td>".escape($row['invoice_detail'])."&nbsp;</td>\n";
			echo "	<td>".escape($row['active'])."&nbsp;</td>\n";
			#echo "	<td class='description overflow hide-sm-dn'>".escape($row['client_description'])."&nbsp;</td>\n";
			if (permission_exists('client_edit') && $_SESSION['theme']['list_row_edit_button']['boolean'] == 'true') {
				echo "	<td class='action-button'>\n";
				echo button::create(['type'=>'button','title'=>$text['button-edit'],'icon'=>$_SESSION['theme']['button_icon_edit'],'link'=>$list_row_url]);
				echo "	</td>\n";
			}
			echo "</tr>\n";
			$x++;
		}
		unset($clients);
	}

	echo "</table>\n";
	echo "<br />\n";
	echo "<div align='center'>".$paging_controls."</div>\n";
	echo "<input type='hidden' name='".$token['name']."' value='".$token['hash']."'>\n";
	echo "</form>\n";

//include the footer
	require_once "resources/footer.php";

?>
