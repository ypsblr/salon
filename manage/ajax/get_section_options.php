<?php
// session_start();
include("../inc/session.php");
include "../inc/connect.php";
include "ajax_lib.php";

// function notify_error($context, $errmsg, $file, $line, $updated = false) {
// 	debug_dump($context, $errmsg, $file, $line);
// 	echo json_encode(array("status" => "ERROR", "context" => $context, "errmsg" => $errmsg, "file" => $file, "line" => $line, "updated" => $updated));
// 	die();
// }

// MAIN
if (isset($_SESSION['admin_id']) && isset($_REQUEST['yearmonth']) ) {

	$yearmonth = $_REQUEST['yearmonth'];

	// Get contest details
	$sql = "SELECT section FROM section WHERE yearmonth = '$yearmonth' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	$html = "";
	while ($row = mysqli_fetch_array($query)) {
		$html .= "<option value='" . $row['section'] . "'>" . $row['section'] . "</option>";
	}
	$html .= "<option value='CONTEST'>CONTEST</option>";

	$resArray = [];
	$resArray['success'] = TRUE;
	$resArray['msg'] = "";
	$resArray["section_options"] = $html;
	echo json_encode($resArray);
	die();
}
else {
	//$_SESSION['err_msg'] = "Invalid parameters";
	return_error("Invalid parameters", __FILE__, __LINE__);
}
?>
