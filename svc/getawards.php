<?php
// This service is used to generate JSON data of Awards (level < 99) or Acceptances (level = 99)
// for import into Google Sheet

include_once("../op/connect.php");

function clean($string) {
   $string = str_replace('', '-', $string); // Replaces all spaces with hyphens.
   return preg_replace('/[^a-zA-Z0-9\s]/', '', $string); // Removes special chars.
}

function json_error($errmsg) {
	$errors = array('status' => 'ERR', 'errmsg' => $errmsg);
	return json_encode(array('errors' => $errors));
}

if(isset($_GET['auth']) &&  $_GET['auth'] == "AX100RBK" && isset($_GET['level']) && $_GET['level'] !='' ) {
	if ($_GET['level'] == 'ACCEPTANCE') {
		$sql  = "SELECT * FROM acceptance WHERE level = 99";
	}
	else {
		$sql = "SELECT * FROM acceptance WHERE level < 99";
	}
	$query = mysql_query($sql) or die(json_error(mysql_error()));
	$data = array();
	while ($row = mysql_fetch_array($query, MYSQL_ASSOC))
		$data[] = $row;
	
	echo json_encode(array($_GET['level'] => $data));
}
else
	echo json_error('Invalid Parameters');

?>
