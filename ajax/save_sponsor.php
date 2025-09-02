<?php
session_save_path(__DIR__ . "/../inc/session");
session_start();
include_once("../inc/connect.php");
include_once("ajax_lib.php");

// CryptoJs
use Nullix\CryptoJsAes\CryptoJsAes;
// require("../inc/CryptoJsAes.php");

// Upload Club Logo
function uploadSponsorLogo($email){
	$errMSG = '';
	$avatar_file = '';
	$file_name = $_FILES['logo']['name'];
	$file_tmp_name = $_FILES['logo']['tmp_name'];
	$file_size = $_FILES['logo']['size'];

	if($file_name) {
		$upload_dir = '../res/sponsor/'; // Club Logo directory
		$file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
		$logo_file =  strtoupper(substr(str_alpha($email), 0, 8)) . "-" . rand(1000,9999) . "." . $file_ext;

		// Resize Logo if required. Otherwise copy
		list($width, $height) = getimagesize($file_tmp_name);
		if ($height > 512 || $width > 512) {
			if ($height > 512) {
				$new_width = $width * 512 / $height;
				$new_height = 512;
			}
			if ($width > 512) {
				$new_height = $height * 512 / $width;
				$new_width = 512;
			}
			$resized_image = imagecreatetruecolor($new_width, $new_height);
			$uploaded_image = imagecreatefromjpeg($file_tmp_name);
			imagecopyresampled($resized_image, $uploaded_image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
			imagejpeg($resized_image, $upload_dir . $logo_file, 100);
		}
		else
			move_uploaded_file($file_tmp_name, $upload_dir . $logo_file);
	}
	else
		$errMSG = "Error uploading Club Logo File '$file_name'";

	return array($errMSG, $logo_file);
}

// Main Code

// $resArray = array();

// if( (! empty($_REQUEST['yearmonth'])) && isset($_REQUEST['sponsor_id']) && isset($_REQUEST['verified']) ) {
if ( (! empty($_SESSION['SALONBOND'])) && (! empty($_REQUEST['ypsd'])) ) {

	// Decrypt the inputs
	$param = CryptoJsAes::decrypt($_REQUEST['ypsd'], $_SESSION['SALONBOND']);
	debug_dump("PARAM", $param, __FILE__, __LINE__);

	// Verify Google reCaptcha code for spam protection
	if (strpos($_SERVER['HTTP_HOST'], "localhost") == false) {
		if ( ! (isset($param['g-recaptcha-response']) && $param['g-recaptcha-response'] != "" && verify_recaptcha($param['g-recaptcha-response']) == "") ) {
			handle_error("Click on I am not Robot before submitting !", __FILE__, __LINE__);
		}
	}

	// debug_dump("REQUEST", $param, __FILE__, __LINE__);
	// Sponsor Data
	$yearmonth = $param['yearmonth'];
	$sponsor_id = $param['sponsor_id'];
	$award_id = $param['award_id'];
	$sponsor_name = mysqli_real_escape_string($DBCON, $param['salutation'] . " " . $param['sponsor_name']);
	$sponsor_email = mysqli_real_escape_string($DBCON, $param['sponsor_email']);
	$sponsor_phone = $param['sponsor_phone'];
	$sponsor_website = mysqli_real_escape_string($DBCON, $param['sponsor_website']);

	// Upload Logo
	// debug_dump("FILES", $_FILES, __FILE__, __LINE__);
	if( isset($_FILES['logo']) && ! empty($_FILES['logo']['name']) ) {
		list($errMSG, $sponsor_logo) = uploadSponsorLogo($sponsor_email);
		if ($errMSG != '')
			handle_error($errMSG, __FILE__, __LINE__);
	}
	else
		$sponsor_logo = $param['old_logo'];

	// Save Sponsor Data
	if ($sponsor_id == 0) {
		// New Sponsor
		$sql  = "INSERT INTO sponsor (sponsor_name, sponsor_logo, sponsor_email, sponsor_phone, sponsor_website) ";
		$sql .= "VALUES('$sponsor_name', '$sponsor_logo', '$sponsor_email', '$sponsor_phone', '$sponsor_website') ";
		mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		$sponsor_id = mysqli_insert_id($DBCON);
	}
	else {
		// Existing Sponsor - Details are saved again
		$sql  = "UPDATE sponsor ";
		$sql .= "SET sponsor_name = '$sponsor_name', ";
		$sql .= "    sponsor_logo = '$sponsor_logo', ";
		$sql .= "    sponsor_email = '$sponsor_email', ";
		$sql .= "    sponsor_phone = '$sponsor_phone', ";
		$sql .= "    sponsor_website = '$sponsor_website' ";
		$sql .= "WHERE sponsor_id = '$sponsor_id' ";
		mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	}

	// Set sponsor_id in the session to make it easy
	// $_SESSION['sponsor_id'] = $sponsor_id;
	// $_SESSION['success_msg'] = "Sponsor details have been saved !";
	// header("Location: /sponsor_award.php?contest=" . $yearmonth . "&awid=" . $award_id . "&spid=" . $sponsor_id );
	// printf("<script>location.href='/sponsor_award.php'</script>");

	// Load Sponsorship Form
	$resArray['success'] = TRUE;
	$resArray['msg'] = "";
	$resArray['sponsor_id'] = $sponsor_id;		// Return ID of the newly added Sponsor
	echo json_encode($resArray);
}
else
	handle_error("Invalid Request", __FILE__, __LINE__);
