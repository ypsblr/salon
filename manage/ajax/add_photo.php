<?php
include("../inc/session.php");
include "../inc/connect.php";
include_once("ajax_lib.php");

define("AVATAR_FOLDER", "../contests/avatar");

// Generate a random name for an avatar
function avatar_file_name($yps_login_id) {

	do {
		$file_name = strtoupper($yps_login_id) . "-" . rand(1000, 9999) . ".jpg";
	} while (file_exists(AVATAR_FOLDER . "/" . $file_name));

	return $file_name;
}


// Main Code

$resArray = array();

if( isset($_SESSION['admin_id']) && isset($_REQUEST['add_photo']) ) {

	$yearmonth = $_REQUEST['yearmonth'];
	$salon_event = $_REQUEST['salon_event'];
	$photo_sequence = $_REQUEST['photo_sequence'];
	$photo_caption = $_REQUEST['photo_caption'];

	// Upload Photo and create thumbnail
	if (isset($_FILES) && $_FILES['event_photo']['error'] != UPLOAD_ERR_NO_FILE) {
		if (! ($_FILES['event_photo']['error'] == UPLOAD_ERR_OK))
			return_error("Error in uploading photo. Try again", __FILE__, __LINE__, true);

		// Create folders
		if ( ! is_dir("../../photos/$yearmonth"))
			mkdir("../../photos/$yearmonth");
		if ( ! is_dir("../../photos/$yearmonth/$salon_event"))
			mkdir("../../photos/$yearmonth/$salon_event");
		if ( ! is_dir("../../photos/$yearmonth/$salon_event/download"))
			mkdir("../../photos/$yearmonth/$salon_event/download");

		$target_folder = "../../photos/$yearmonth/$salon_event";
		$file_name = $_FILES['event_photo']['name'];
		$file_extn = pathinfo($file_name, PATHINFO_EXTENSION);
		$target_file = $target_folder . "/download/" . $file_name;
		$thumbnail = $target_folder . "/" . $file_name;

		if (move_uploaded_file($_FILES['event_photo']['tmp_name'], $target_file)) {
			// Create thumbnail
			$thumb_width = 720;
			$thumb_height = 480;
			list($width, $height) = getimagesize($target_file);

			$thumb_create = imagecreatetruecolor($thumb_width, $thumb_height);

			switch($file_extn) {
				case 'jpg' || 'jpeg':
					$source = imagecreatefromjpeg($target_file);
					break;
				case 'png':
					$source = imagecreatefrompng($target_file);
					break;
				case 'gif':
					$source = imagecreatefromgif($target_file);
					break;
				default:
					$source = imagecreatefromjpeg($target_file);
			}

			imagecopyresized($thumb_create, $source, 0, 0, 0, 0, $thumb_width, $thumb_height, $width, $height);

			switch($file_extn) {
				case 'jpg' || 'jpeg':
					imagejpeg($thumb_create,$thumbnail,100);
					break;
				case 'png':
					imagepng($thumb_create,$thumbnail,100);
					break;
				case 'gif':
					imagegif($thumb_create,$thumbnail,100);
					break;
				default:
					imagejpeg($thumb_create,$thumbnail,100);
			}

		}
		else
			return_error("Error in copying photo", __FILE__, __LINE__, true);
	}
	else
		return_error("No photo selected or photo not uploaded", __FILE__, __LINE__, true);

	// Append to the CSV file
	$csv_file = fopen("../../salons/$yearmonth/blob/photos.csv", "a+");
	$row = ["event" => $salon_event, "sequence" => $photo_sequence, "photo" => $file_name, "caption" => $photo_caption];
	fputcsv($csv_file, $row);
	fclose($csv_file);

	$resArray = [];
	$resArray['success'] = TRUE;
	$resArray['msg'] = "";
	$resArray['photo'] = $row;
	echo json_encode($resArray);
	die();
}
else
	return_error("Invalid Request", __FILE__, __LINE__, true);

?>
