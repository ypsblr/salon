<?php
session_save_path(__DIR__ . "/../inc/session");
session_start();
include("../inc/connect.php");
include("ajax_lib.php");

if (isset($_REQUEST['country_id'])) {
	$country_id = $_REQUEST['country_id'];
	$sql = "SELECT * FROM country WHERE country_id = '$country_id' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_num_rows($query) > 0) {
		$row = mysqli_fetch_array($query, MYSQLI_ASSOC);
		$resArray = array();
		$resArray['success'] = true;
		$resArray['msg'] = '';
		$resArray['country'] = $row;
		echo json_encode($resArray);
	}
	else {
		handle_error("Country not found!", __FILE__, __LINE__);
	}
}
else {
	handle_error("Invalid Request", __FILE__, __LINE__);
}
?>
