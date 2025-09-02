<?php
session_save_path(__DIR__ . "/../inc/session");
session_start();

include_once("../inc/connect.php");
include_once("../inc/lib.php");

function safe_file_name ($str) {
	// Remove stars
	$str = str_replace('*', '_', $str);
	// replace forward slashes
	$str = str_replace('/', '_', $str);
	// replace backward slashes
	$str = str_replace('\\', '_', $str);
	// replace full stops
	$str = str_replace('.', '_', $str);
	// replace %
	$str = str_replace('%', '_', $str);
	// replace @
	$str = str_replace('@', '_', $str);
	// Replace single quotes
	$str = str_replace("'", '_', $str);
	// Replace double quotes
	$str = str_replace('"', '_', $str);
	// Replace Spaces
	$str = str_replace(' ', '_', $str);

	return $str;
}

if (isset($_REQUEST['profile_id']) && isset($_REQUEST['pic_id']) && isset($_REQUEST['yearmonth']) && isset($_REQUEST['upload_full_img']) ) {

    // Verify Captcha Confirmation
    if ( empty($_REQUEST['captcha_method']) ){
        handle_error("Invalid Authentication !", __FILE__, __LINE__);
    }
    switch($_REQUEST['captcha_method']) {
        case "php" : {
            // Validate Captcha Code with Session Variable
            if ( empty($_SESSION['captcha_code']) || empty($_REQUEST['captcha_code']) || $_SESSION['captcha_code'] != $_REQUEST['captcha_code'] )
                handle_error("Authentication Failed. Check Validation Code !", __FILE__, __LINE__);
            break;
        }
        case "google" : {
            // Verify Google reCaptcha code for spam protection
            if (strpos($_SERVER['HTTP_HOST'], "localhost") == false) {
                if ( ! (isset($_POST['g-recaptcha-response']) && $_POST['g-recaptcha-response'] != "" && verify_recaptcha($_POST['g-recaptcha-response']) == "") ) {
                    handle_error("Click on I am not Robot before submitting !", __FILE__, __LINE__);
                }
            }
            break;
        }
        default : handle_error("Invalid Authentication !", __FILE__, __LINE__);
    }

	$upload_error = "";

	$yearmonth = $_REQUEST['yearmonth'];
	$profile_id = $_REQUEST['profile_id'];
	$pic_id = $_REQUEST['pic_id'];
	$award_id = $_REQUEST['award_id'];

	// Get contest details
	$sql = "SELECT * FROM contest WHERE yearmonth = '$yearmonth' ";
	$qc = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$contest = mysqli_fetch_array($qc);

	$sql = "SELECT profile.profile_name, profile.email, award.award_name, pic.section, pic.title ";
	$sql .= "  FROM pic_result, award, pic, profile ";
	$sql .= " WHERE pic_result.yearmonth = '$yearmonth' ";
	$sql .= "   AND pic_result.award_id = '$award_id' ";
	$sql .= "   AND pic_result.profile_id = '$profile_id' ";
	$sql .= "   AND pic_result.pic_id = '$pic_id' ";
	$sql .= "   AND award.yearmonth = pic_result.yearmonth ";
	$sql .= "   AND award.award_id = pic_result.award_id ";
	$sql .= "   AND pic.yearmonth = pic_result.yearmonth ";
	$sql .= "   AND pic.profile_id = pic_result.profile_id ";
	$sql .= "   AND pic.pic_id = pic_result.pic_id ";
	$sql .= "   AND profile.profile_id = pic_result.profile_id ";

	$qry = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if ($res = mysqli_fetch_array($qry)) {
		$section = $res['section'];
		$award_name = $res['award_name'];
		$profile_name = $res['profile_name'];
		$email = $res['email'];
		$title = $res['title'];

//		if(isset($_FILES['photo'])){
		if(isset($_REQUEST['uploaded_file'])){
//			$name = $_FILES['photo']['name'];
//			$tmp_name = $_FILES['photo']['tmp_name'];
//			$type = $_FILES['photo']['type'];
//			$size = $_FILES['photo']['size'];
//			$error = $_FILES['photo']['error'];
			$tmp_name = $_REQUEST['uploaded_file'];
			$picfile= safe_file_name($section . "_" . $award_name . "_" . $profile_id . "_" . $pic_id) . ".jpg";

			$fullPicPath = "../salons/" . $yearmonth . "/upload/" . $section . "/full";
			if (! is_dir($fullPicPath))
				mkdir($fullPicPath);

			debug_dump("uploaded_file", $_REQUEST['uploaded_file'], __FILE__, __LINE__);
			debug_dump("fullPicPath", $fullPicPath, __FILE__, __LINE__);

//			if($error == UPLOAD_ERR_OK && move_uploaded_file($tmp_name, $fullPicPath . "/" . $picfile)) {
			if(rename($tmp_name, $fullPicPath . "/" . $picfile)) {
				// Update PIC record
				$sql  = "UPDATE pic ";
				$sql .= "   SET full_picfile = '$picfile' ";
				$sql .= " WHERE yearmonth = '$yearmonth' ";
				$sql .= "   AND profile_id = '$profile_id' ";
				$sql .= "   AND pic_id = '$pic_id' ";
				mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
				/* mail */
				$msg = "<html><body>";
				$msg .= "<p>Dear " . $profile_name . ",<p><br>";
				$msg .= "<p>Thank you for uploading full resolution file of the picture with the title '" . $title . "' ";
				$msg .= "that won " . $award_name . " award under " . $section . " section for Exhibition Printing / Catalog Publication. ";
				$msg .= "We will verify the file and revert to you if any changes are required.</p><br><br>";
				$msg .= "<p><a href='" . http_method() . $_SERVER['SERVER_NAME'] . "'>" . $contest['contest_name'] . "</a></p>";

				$subject = $profile_name . " (" . $profile_id . ") has uploaded an Exhibition Image";

				$headers = "MIME-Version: 1.0" . "\r\n";
				$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
				$headers .= 'From: YPS Salon <salon@ypsbengaluru.in>' . "\r\n";
				$headers .= 'Cc: salon@ypsbengaluru.in' . "\r\n";

				$send = mail($email, $subject, $msg, $headers);
			}
			else
				handle_error("Error copying uploaded photo", __FILE__, __LINE__);
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
