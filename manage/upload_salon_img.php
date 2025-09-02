<?php
include("../inc/session.php");
include "../inc/connect.php";
include_once("ajax_lib.php");

// Main Code

$resArray = array();

if( isset($_SESSION['admin_id']) && isset($_REQUEST['yearmonth']) && isset($_FILES) && isset($_REQUEST['file_name']) ) {

	$yearmonth = $_REQUEST['yearmonth'];
	$target_folder = "../../salons/$yearmonth/img";
	if (isset($_REQUEST['sub_folder']) && $_REQUEST['sub_folder'] != "")
		$target_folder .= "/" . $_REQUEST['sub_folder'];
	$file_name = $_REQUEST['file_name'];

	foreach($_FILES as $file => $status) {
		if ($status['error'] != UPLOAD_ERR_NO_FILE) {
			// debug_dump("FILES", $_FILES, __FILE__, __LINE__);
			if (! ($status['error'] == UPLOAD_ERR_OK))
				return_error("Error in uploading image. Try again", __FILE__, __LINE__, true);

			// $target_file = $target_folder . "/" . $status['name'];
			$target_file = $target_folder . "/" . $file_name;
			if (file_exists($target_file)) {
				$info = pathinfo($target_file);
				rename($target_file, $target_folder . "/" . $info['filename'] . "_" . date("YmdHi") . "." . $info['extension']);
			}

			if (! move_uploaded_file($status['tmp_name'], $target_file))
				return_error("Error in copying image", __FILE__, __LINE__, true);

		}
	}

	$resArray = [];
	$resArray['success'] = TRUE;
	$resArray['msg'] = "";
	echo json_encode($resArray);
	die();
}
else
	return_error("Invalid Update Request", __FILE__, __LINE__, true);

?>
