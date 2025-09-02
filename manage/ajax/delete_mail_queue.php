<?php
include("../inc/session.php");
include "../inc/connect.php";
include_once("ajax_lib.php");



// Main Code

$resArray = array();

if( isset($_SESSION['admin_id']) && isset($_REQUEST['queue_id']) ) {

	$queue_id = $_REQUEST['queue_id'];

	// Start Transaction
	$sql = "START TRANSACTION";
	mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Delete Queue
	$sql  = "UPDATE mail_queue SET status = 'DELETED' WHERE queue_id = '$queue_id' ";
	mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_affected_rows($DBCON) != 1)
		return_error("Unable to create Mail Queue", __FILE__, __LINE__, true);

	$sql = "DELETE FROM mail_to WHERE queue_id = '$queue_id' ";
	mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	$sql = "COMMIT";
	mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

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
