<?php
session_save_path(__DIR__ . "/../inc/session");
session_start();
include_once("ajax_lib.php");

function return_error($errmsg) {
	$resArray = array();
	$resArray['success'] = FALSE;
	$resArray['tmpfile'] = "";
	$resArray['errmsg'] = $errmsg;
	echo json_encode($resArray);
}

function generate_token($length = 32) {
	$letters = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
	$token = "";
	for ($i = 0; $i < $length; ++$i) {
		$offset = rand(0, strlen($letters)-1);
		$token .= substr($letters, $offset, 1);
	}
	return $token;
}

function get_file_transfer_error_message($code) {
	switch($code) {
		case UPLOAD_ERR_INI_SIZE : return "File size exceeds the system limit of " . round(ini_get("upload_max_filesize") / (1024 * 1024), 0) . "MB. Resize and Retry.";
		case UPLOAD_ERR_FORM_SIZE : return "File size exceeds maximum specified. Resize and Retry.";
		case UPLOAD_ERR_PARTIAL : return "File not fully uploadd. Retry.";
		case UPLOAD_ERR_NO_FILE : return "No file was uploaded. Retry.";
		case UPLOAD_ERR_NO_TMP_DIR : return "Error in system configuration. Report to YPS.";
		case UPLOAD_ERR_CANT_WRITE : return "Unable to save uploaded file. Report to YPS.";
		case UPLOAD_ERR_EXTENSION : return "Error in system program. Report to YPS.";
		default : {
			if ($code == UPLOAD_ERR_OK)
				return "";
			else
				return "Unknown Error (code " . $code . "). Report to YPS.";
		}
	}
}

function file_upload_max_size() {

	// debug_dump("post_max_size", ini_get("post_max_size"), __FILE__, __LINE__);
	// debug_dump("upload_max_filesize", ini_get("upload_max_filesize"), __FILE__, __LINE__);

	// Start with post_max_size.
	$post_max_size = parse_size(ini_get('post_max_size'));

	// If upload_max_size is less, then reduce. Except if upload_max_size is
	// zero, which indicates no limit.
	$upload_max_size = parse_size(ini_get('upload_max_filesize'));
	if ($upload_max_size == 0)	// No upload limit. Limited to post_max_size
		return $post_max_size;
	else
		return min($post_max_size, $upload_max_size);
}

function parse_size($size) {
	$unit = preg_replace('/[^bkmgtpezy]/i', '', $size); // Remove the non-unit characters from the size.
	$size = preg_replace('/[^0-9\.]/', '', $size); // Remove the non-numeric characters from the size.
	if ($unit) {
    	// Find the position of the unit in the ordered string which is the power of magnitude to multiply a kilobyte by.
    	return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
	}
	else {
    	return round($size);
	}
}

// Main Code
// debug_dump("_SERVER", $_SERVER, __FILE__, __LINE__);

// debug_dump("REQUEST", $_REQUEST, __FILE__, __LINE__);
// debug_dump("FILES", $_FILES, __FILE__, __LINE__);


if( isset($_REQUEST['rurobot']) && $_REQUEST['rurobot'] == 'IamHuman' &&
  	( isset($_REQUEST['set_token']) || (isset($_REQUEST['auth_token']) && isset($_FILES)) )   ) {

	if (isset($_REQUEST['set_token'])) {
		$_SESSION['ajax_upload_token'] = generate_token();

		$resArray = array();
		$resArray['success'] = TRUE;
		$resArray['token'] = $_SESSION['ajax_upload_token'];
		$resArray['upload_max_filesize'] = file_upload_max_size();
		$resArray['errmsg'] = "";
		echo json_encode($resArray);
	}
	else if ($_REQUEST['auth_token'] == $_SESSION['ajax_upload_token']) {
		// Create Temp Folder
		$target_folder = $_SERVER['DOCUMENT_ROOT'] . "/tmp";
		if (! is_dir($target_folder))
			mkdir($target_folder);

		// Copy uploaded file
		$temp_file = "PHOTO-TMP-" . rand(100000, 999999) . ".jpg";
		$target_file = $target_folder . "/" . $temp_file;
		if ($_FILES['photo']['error'] == UPLOAD_ERR_OK) {
			if (move_uploaded_file($_FILES['photo']['tmp_name'], $target_file)) {
				$resArray = array();
				$resArray['success'] = TRUE;
				$resArray['tmpfile'] = $target_file;
				$resArray['errmsg'] = "";
				echo json_encode($resArray);
			}
			else
				handle_error("Error saving uploaded file in salon folder. Report to YPS");
		}
		else
			handle_error(get_file_transfer_error_message($_FILES['photo']['error']));
	}
	else
		handle_error("Unauthorized operation");
}
else
	handle_error("Invalid Update Request");


?>
