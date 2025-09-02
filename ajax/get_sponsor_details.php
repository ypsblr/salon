<?php
session_save_path(__DIR__ . "/../inc/session");
session_start();
include("../inc/connect.php");
include("ajax_lib.php");

if (isset($_REQUEST['sponsor_email'])) {
	$sponsor_email = $_REQUEST['sponsor_email'];
	$sql = "SELECT * FROM sponsor WHERE sponsor_email = '$sponsor_email' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_num_rows($query) > 0) {
		$sponsor = mysqli_fetch_array($query, MYSQLI_ASSOC);
		$resArray = array();
		$resArray['success'] = true;
		$resArray['msg'] = '';
		$resArray['sponsor'] = $sponsor;
		echo json_encode($resArray);
	}
	else {
		handle_error("Sponsor not found!", __FILE__, __LINE__);
	}
}
else {
	handle_error("Invalid Update Request", __FILE__, __LINE__);
}
?>
