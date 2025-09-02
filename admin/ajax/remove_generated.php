<?php
// session_start();
include("../inc/session.php");
include "../inc/connect.php";
include "ajax_lib.php";

function delete_path($dir) {
	if (is_dir($dir)) {
		foreach (glob($dir . '/*') as $path) {
	    	if (is_dir($path))
				delete_path($path);
			else
				unlink($path);
		}
		rmdir($dir);
		return true;
	}
	else
		return false;
}

// MAIN
if (isset($_SESSION['admin_id']) && isset($_SESSION['admin_yearmonth']) ) {

	$yearmonth = $_REQUEST['yearmonth'];

	if (delete_path("../../generated/$yearmonth"))
		echo json_encode(array("success" => true, "msg" => ""));
	else
		echo json_encode(array("success" => false, "msg" => "Deleting generted files for $yearmonth failed"));
}
else {
	//$_SESSION['err_msg'] = "Invalid parameters";
	return_error("Invalid parameters", __FILE__, __LINE__);
}
?>
