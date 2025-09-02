<?php
session_save_path(__DIR__ . "/../inc/session");
session_start();
include_once("../inc/connect.php");
include_once("ajax_lib.php");

function make_path($path) {
	if (! is_dir(dirname($path)))
		make_path(dirname($path));
	if (! is_dir($path))
		mkdir($path);
}

// Main Code

debug_dump("REQUEST", $_REQUEST, __FILE__, __LINE__);
debug_dump("FILES", $_FILES, __FILE__, __LINE__);

if( isset($_SESSION['USER_ID']) && $_SESSION['USER_ID'] != "" &&
    isset($_REQUEST['yearmonth']) && isset($_REQUEST['profile_id']) && isset($_REQUEST['stub']) &&
	isset($_FILES['droppedfile']) && sizeof($_FILES['droppedfile']) > 0 ) {

	$yearmonth = $_REQUEST['yearmonth'];
	$profile_id = $_REQUEST['profile_id'];
	$stub = $_REQUEST['stub'];

	// Create Temp Folder
	$target_folder = "../salons/$yearmonth/upload/dropped";
	make_path(($target_folder));

	// Copy uploaded file
	$temp_file = "TMP-" . $profile_id . "-" . $stub . "-" . date("Ymd") . "-" . rand(1000, 9999) . ".jpg";
	$target_file = $target_folder . "/" . $temp_file;
	if ($_FILES['droppedfile']['error'] == UPLOAD_ERR_OK && move_uploaded_file($_FILES['droppedfile']['tmp_name'], $target_file)) {
		$resArray = array();
		$resArray['success'] = TRUE;
		$resArray['tmpfile'] = $temp_file;
		$resArray['msg'] = "";
		echo json_encode($resArray);
	}
	else
		handle_error("Upload failed", __FILE__, __LINE__);
}
else
	handle_error("Invalid Update Request", __FILE__, __LINE__);


?>
