<?php
include("../inc/session.php");
include "../inc/connect.php";
include_once("ajax_lib.php");



// Main Code

$resArray = array();

if( isset($_SESSION['admin_id']) ) {

	// Return Mail Queue for update
	$sql  = "SELECT * FROM mail_queue WHERE status != 'COMPLETED' AND status != 'DELETED' ";
	$query = mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$mail_queue = [];
	while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC))
		$mail_queue[] = $row;

	$resArray = [];
	$resArray['success'] = TRUE;
	$resArray['msg'] = "";
	$resArray['mail_queue'] = $mail_queue;
	echo json_encode($resArray);
	die();
}
else
	return_error("Invalid Update Request", __FILE__, __LINE__, true);

?>
