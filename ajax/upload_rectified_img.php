<?php
session_save_path(__DIR__ . "/../inc/session");
session_start();

include_once("../inc/connect.php");
include_once("ajax_lib.php");

// CryptoJs
use Nullix\CryptoJsAes\CryptoJsAes;
// require("../inc/CryptoJsAes.php");

/*** Create Thumbnail Image ***/
function makeThumbnail($fileName = '', $upload_image, $thumb_path = '', $thumb_width = '', $thumb_height = '') {
	// $thumb_path	= $thumb_path . '/tn';
	// create thumbnail directory if not exists
	if(! is_dir($thumb_path))
		mkdir($thumb_path);

	$thumbnail = $thumb_path . "/" . $fileName;
	list($width, $height, $img_type, $attr) = getimagesize($upload_image);
	$file_ext = image_type_to_extension($img_type);

	$thumb_create = imagecreatetruecolor($thumb_width,$thumb_height);

	switch($file_ext){
		case 'jpg':
			$source = imagecreatefromjpeg($upload_image);
			break;
		case 'jpeg':
			$source = imagecreatefromjpeg($upload_image);
			break;
		case 'png':
			$source = imagecreatefrompng($upload_image);
			break;
		case 'gif':
			$source = imagecreatefromgif($upload_image);
			break;
		default:
			$source = imagecreatefromjpeg($upload_image);
	}

	imagecopyresized($thumb_create,$source,0,0,0,0,$thumb_width,$thumb_height,$width,$height);

	switch($file_ext){
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
/******  End ****/

if ( (! empty($_SESSION['SALONBOND'])) && (! empty($_REQUEST['ypsd'])) ) {

	// Decrypt the inputs
	$param = CryptoJsAes::decrypt($_REQUEST['ypsd'], $_SESSION['SALONBOND']);
	debug_dump("PARAM", $param, __FILE__, __LINE__);

// if ( isset($_SESSION['SALONBOND']) && isset($_REQUEST['profile_id']) && isset($_REQUEST['pic_id']) &&
// 	 isset($_REQUEST['yearmonth']) && isset($_REQUEST['pic_title']) && isset($_REQUEST['user_auth']) &&
// 	 isset($_REQUEST['upload_rectified_img']) ) {

	$upload_error = "";

	$yearmonth = $param['yearmonth'];
	$profile_id = $param['profile_id'];
	$yps_login_id = $param['yps_login_id'];
	$email = $param['email'];
	$pic_id = $param['pic_id'];
	$pic_title = mysqli_real_escape_string($DBCON, trim($param['pic_title']));
	$user_auth = $param['user_auth'];

	// Validate User Authentication
	if ($user_auth != "ADMIN") {
		if ($yps_login_id == "") {
			$sql = "SELECT * FROM profile WHERE profile_id = '$profile_id' AND password = '$user_auth' ";
			$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
			if (mysqli_num_rows($query) == 0)
				handle_error('User Authentication Failed. Use correct password !', __FILE__, __LINE__);
		}
		else {
			list ($errmsg) = yps_login($yps_login_id, $user_auth);
			if ($errmsg != "")
				handle_error('User Authentication Failed. Use correct password !', __FILE__, __LINE__);
		}
	}

	// Get contest details
	$sql = "SELECT * FROM contest WHERE yearmonth = '$yearmonth' ";
	$qc = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$contest = mysqli_fetch_array($qc);

	// Validate existence of picture
	$sql = "SELECT * FROM pic ";
	$sql .= " WHERE pic.yearmonth = '$yearmonth' ";
	$sql .= "   AND pic.profile_id = '$profile_id' ";
	$sql .= "   AND pic.pic_id = '$pic_id' ";
	// $sql .= "   AND pic.notifications != '' ";		// Front-end checks are performed to filter this
	$qry = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if ($res = mysqli_fetch_array($qry)) {
		$section = $res['section'];

		if(isset($_FILES['photo'])) {
			$name = $_FILES['photo']['name'];
			$tmp_name = $_FILES['photo']['tmp_name'];
			$type = $_FILES['photo']['type'];
			$file_size = $_FILES['photo']['size'];
			$error = $_FILES['photo']['error'];

			if ($error == UPLOAD_ERR_OK) {
				// Validate Image for meeting Salon Size requirements
				list($img_width, $img_height, $img_type, $attr) = getimagesize($tmp_name);
				if (image_type_to_extension($img_type) != '.jpg' && image_type_to_extension($img_type) != '.jpeg')
					handle_error('Image type not supported. Can only upload JPEG files !', __FILE__, __LINE__);

				if ($img_width > $contest['max_width'] || $img_height > $contest['max_height'])
					handle_error('Picture dimensions exceed width limit of '. $contest['max_width'] . ' pixels and/or height limit of ' . $contest['max_height'] . ' pixels !', __FILE__, __LINE__);

				if ($file_size > $contest['max_file_size_in_mb'] * 1024 * 1024)
					handle_error('File size exceeds ' . $contest['max_file_size_in_mb'] . 'MB Limit !', __FILE__, __LINE__);

				// Upload File
				//create upload directory if not exist
				$upload_dir = "../salons/$yearmonth/upload/" . $section;
				$random = mt_rand(1111111, 9999999);
				$picfile = $profile_id . '_' . $random . '_' . date('dmyhis') . '.jpg';
				$fullPicPath = $upload_dir . "/" . $picfile;
				if( ! move_uploaded_file($tmp_name, $fullPicPath))
					handle_error('Picture upload failed. Please check the file for corruption or virus infection !', __FILE__, __LINE__);

				// Create Thumbnail
				makeThumbnail($picfile, $fullPicPath, $upload_dir . "/tn", 480, $img_height * 480 / $img_width);

				// create large Thumbnail as well
				// makeThumbnail($picfile, $fullPicPath, $upload_dir . "/tnl", 960, $img_height * 960 / $img_width);

				// Update PIC record
				$sql  = "UPDATE pic ";
				$sql .= "   SET picfile = '$picfile' ";
				$sql .= "     , title = '$pic_title' ";
				$sql .= "     , submittedfile = '$name' ";
				$sql .= "     , notifications = '' ";
				$sql .= "     , reviewed = '0' ";
				$sql .= "     , no_accept = '0' ";
				$sql .= "     , reviewer_id = '0' ";
				$sql .= " WHERE yearmonth = '$yearmonth' ";
				$sql .= "   AND profile_id = '$profile_id' ";
				$sql .= "   AND pic_id = '$pic_id' ";
				debug_dump("SQL", $sql, __FILE__, __LINE__);
				mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
			}
			else
				handle_error("Error uploading $name", __FILE__, __LINE__);
		}
		else
			handle_error("File could not be uploaded. Contact YPS Salon Committee !", __FILE__, __LINE__);
	}
	else
		handle_error("Invalid Upload Code", __FILE__, __LINE__);

	$resArray = array();
	$resArray['success'] = TRUE;
	$resArray['msg'] = $_SESSION['success_msg'] = "Full Picture Upload successful !";
	echo json_encode($resArray);
}
else {
	// Parameter error - go home
	handle_error("File Upload failed. Possibly file is large. Please contact YPS for action !", __FILE__, __LINE__);
}
?>
