<?php
session_save_path(__DIR__ . "/../inc/session");
session_start();
include_once("../inc/connect.php");
include_once("ajax_lib.php");

// CryptoJs
use Nullix\CryptoJsAes\CryptoJsAes;
// require("../inc/CryptoJsAes.php");

// Upload User Avatar
function uploadAvatar($email){
	$errMSG = '';
	$avatar_file = '';
	$file_name = $_FILES['avatar']['name'];
	$file_tmp_name = $_FILES['avatar']['tmp_name'];
	$file_size = $_FILES['avatar']['size'];

	if($file_name) {
		$upload_dir = '../res/avatar/'; // avatar directory
		$file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
		$avatar_file =  strtoupper(substr(str_alpha($email), 0, 8)) . "-" . rand(1000,9999) . "." . $file_ext;
		// Resize Avatar if required. Otherwise copy
		// 2021-08-15 : This functionality is removed because the avatar is now limited to 512 KB on the front-end
		// list($width, $height) = getimagesize($file_tmp_name);
		// if ($height > 512 || $width > 512) {
		// 	if ($height > 512) {
		// 		$new_width = $width * 512 / $height;
		// 		$new_height = 512;
		// 	}
		// 	if ($width > 512) {
		// 		$new_height = $height * 512 / $width;
		// 		$new_width = 512;
		// 	}
		// 	$resized_image = imagecreatetruecolor($new_width, $new_height);
		// 	$uploaded_image = imagecreatefromjpeg($file_tmp_name);
		// 	imagecopyresampled($resized_image, $uploaded_image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
		// 	imagejpeg($resized_image, $upload_dir . $avatar_file, 100);
		// }
		// else
			move_uploaded_file($file_tmp_name, $upload_dir . $avatar_file);
	}
	else
		$errMSG = "Error uploading Avatar File '$file_name'";

	return array($errMSG, $avatar_file);
}

function uploadAgeProof($email) {
	$errMSG = '';
	$age_proof_file = '';
	$file_name = $_FILES['age_proof_file']['name'];
	$file_tmp_name = $_FILES['age_proof_file']['tmp_name'];
	$file_size = $_FILES['age_proof_file']['size'];

	if($file_name) {
		$upload_dir = '../res/age_proof/'; // upload directory
		$file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
		$age_proof_file =  strtoupper(substr(str_alpha($email), 0, 8)) . "-" . rand(1000,9999) . "." . $file_ext;
		move_uploaded_file($file_tmp_name, $upload_dir . $age_proof_file);
	}
	else
		$errMSG = "Error uploading Age Proof File '$file_name'";

	return array($errMSG, $age_proof_file);
}

// Upload Club Logo
function uploadClubLogo($email){
	$errMSG = '';
	$avatar_file = '';
	$file_name = $_FILES['new_club_logo']['name'];
	$file_tmp_name = $_FILES['new_club_logo']['tmp_name'];
	$file_size = $_FILES['new_club_logo']['size'];

	if($file_name) {
		$upload_dir = '../res/club/'; // Club Logo directory
		$file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
		$logo_file =  strtoupper(substr(str_alpha($email), 0, 8)) . "-" . rand(1000,9999) . "." . $file_ext;

		// Resize Logo if required. Otherwise copy
		// 2021-08-15 : This functionality is removed because the avatar is now limited to 512 KB on the front-end
		// list($width, $height) = getimagesize($file_tmp_name);
		// if ($height > 1024 || $width > 1024) {
		// 	if ($height > 1024) {
		// 		$new_width = $width * 1024 / $height;
		// 		$new_height = 1024;
		// 	}
		// 	if ($width > 1024) {
		// 		$new_height = $height * 1024 / $width;
		// 		$new_width = 1024;
		// 	}
		// 	$resized_image = imagecreatetruecolor($new_width, $new_height);
		// 	$uploaded_image = imagecreatefromjpeg($file_tmp_name);
		// 	imagecopyresampled($resized_image, $uploaded_image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
		// 	imagejpeg($resized_image, $upload_dir . $logo_file, 100);
		// }
		// else
			move_uploaded_file($file_tmp_name, $upload_dir . $logo_file);
	}
	else
		$errMSG = "Error uploading Club Logo File '$file_name'";

	return array($errMSG, $logo_file);
}


function is_in_blacklist($chk_email) {
	global $DBCON;
	// Check blacklist
	$sql = "SELECT * FROM blacklist WHERE email = '$chk_email' AND expiry_date >= '" . date("Y-m-d") . "' ";
	$qchk = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_num_rows($qchk) > 0)
		return true;
	else
		return false;

}

// Main Code

$resArray = array();

// if( (! empty($_REQUEST['email'])) && isset($_REQUEST['new_registration']) && isset($_REQUEST['verified']) ) {
if ( (! empty($_SESSION['SALONBOND'])) && (! empty($_REQUEST['ypsd'])) ) {

	// Decrypt the inputs
	$param = CryptoJsAes::decrypt($_REQUEST['ypsd'], $_SESSION['SALONBOND']);
	debug_dump("PARAM", $param, __FILE__, __LINE__);

    // Verify Captcha Confirmation
    if ( empty($param['captcha_method']) ){
        handle_error("Invalid Authentication !", __FILE__, __LINE__);
    }
    switch($param['captcha_method']) {
        case "php" : {
            // Validate Captcha Code with Session Variable
            if ( empty($_SESSION['captcha_code']) || empty($param['captcha_code']) || $_SESSION['captcha_code'] != $param['captcha_code'] )
                handle_error("Authentication Failed. Check Validation Code !", __FILE__, __LINE__);
            break;
        }
        case "google" : {
            // Verify Google reCaptcha code for spam protection
            if (strpos($_SERVER['HTTP_HOST'], "localhost") == false) {
                if ( ! (isset($param['g-recaptcha-response']) && $param['g-recaptcha-response'] != "" && verify_recaptcha($param['g-recaptcha-response']) == "") ) {
                    handle_error("Click on I am not Robot before submitting !", __FILE__, __LINE__);
                }
            }
            break;
        }
        default : handle_error("Invalid Authentication !", __FILE__, __LINE__);
    }

	// Assemble all inputs
	$profile_id = 0;
	$email = mysqli_real_escape_string($DBCON, $param['email']);
	$yps_login_id = $param['yps_login_id'];
	$salutation = $param['salutation'];
	$first_name = mysqli_real_escape_string($DBCON, $param['first_name']);
	$last_name = mysqli_real_escape_string($DBCON, $param['last_name']);
	$profile_name = $first_name . " " . $last_name;
	$gender = $param['gender'];
	$date_of_birth = $param['date_of_birth'];

	$age_proof = $param['age_proof'];
	if((isset($_FILES['age_proof_file'])) && ! empty($_FILES['age_proof_file']['name']) ) {
		list($errMSG, $age_proof_file) = uploadAgeProof($email);
		if ($errMSG != '')
			handle_error($errMSG, __FILE__, __LINE__);
	}
	else
		$age_proof_file = "";

	$honors = mysqli_real_escape_string($DBCON, strtoupper($param['honors']));
	$phone = $param['phone'];
	$whatsapp = $param['whatsapp'];
	$facebook_account = mysqli_real_escape_string($DBCON, $param['facebook_account']);
	$twitter_account = mysqli_real_escape_string($DBCON, $param['twitter_account']);
	$instagram_account = mysqli_real_escape_string($DBCON, $param['instagram_account']);

	$address_1 = mysqli_real_escape_string($DBCON, strtoupper($param['address_1']));
	$address_2 = mysqli_real_escape_string($DBCON, strtoupper($param['address_2']));
	$address_3 = mysqli_real_escape_string($DBCON, strtoupper($param['address_3']));
	$country_id = $param['country_id'];
	$city = mysqli_real_escape_string($DBCON, strtoupper($param['city']));
	$state = mysqli_real_escape_string($DBCON, strtoupper($param['state']));
	$pin = $param['pin'];

	$campaign_media_list = isset($param['campaign_media']) ? $param['campaign_media'] : [];
	$cm_other_text = $param['cm_other_text'];
	for ($i = 0; $i < sizeof($campaign_media_list); ++ $i)
		if ($campaign_media_list[$i] == "cm_other")
			$campaign_media_list[$i] .= ":" . $cm_other_text;

	$campaign_media = mysqli_real_escape_string($DBCON, implode("|", $campaign_media_list));

	$password = mysqli_real_escape_string($DBCON, $param['new_password']);
	if (empty($param['verified']))
		return_error("Confirm correctness by ticking the checkbox");
	$verified = $param['verified'];


	if( isset($_FILES['avatar']) && ! empty($_FILES['avatar']['name']) ) {
		list($errMSG, $avatar) = uploadAvatar($email);
		if ($errMSG != '')
			handle_error($errMSG, __FILE__, __LINE__);
	}
	else
		$avatar = "user.jpg";

	// Check if email has already been registered
	$sql = "SELECT * FROM profile WHERE email = '$email' ";
	$qchk = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_num_rows ($qchk) > 0)
		handle_error("Email '$email' has already been registered", __FILE__, __LINE__);

	// Check if the email is in blacklist
	$blacklist_match = "";
	$blacklist_exception = '0';
	list($blacklist_match, $blacklist_name) = check_blacklist($param['first_name'] . " " . $param['last_name'], $param['email'], $param['phone']);
	if ($blacklist_match == "MATCH" || $blacklist_match == "SIMILAR") {
		$_SESSION['info_msg'] = "Please note that your profile matches one of the profiles in the Blacklist published by patronage organizations. ";
		$_SESSION['info_msg'] .= "Though you may sign up, your participation in Salons will not be permitted. ";
		$_SESSION['info_msg'] .= "If you feel that this is an incorrect classification, pleaase speak to the Salon Chairman.";
	}

	// New Club, if created

	if ($yps_login_id == "") {
		$club_id = (empty($param['club_id']) || $param['club_id'] == "") ? "0" : $param['club_id'];
		// $club_id = (isset($param['club_id']) && $param['club_id'] != "") ? $param['club_id'] : 0;		// Default value of club_id is 0 if no club is selected

		if ($club_id == "new") {
			$new_club_type = $param['club_type'];
			$new_club_name = mysqli_real_escape_string($DBCON, strtoupper($param['new_club_name']));
			$new_club_website = mysqli_real_escape_string($DBCON, $param['new_club_website']);
			$new_club_address = mysqli_real_escape_string($DBCON, strtoupper($param['new_club_address']));
			// Upload Avatar
			if( isset($_FILES['new_club_logo']) && ! empty($_FILES['new_club_logo']['name']) ) {
				list($errMSG, $club_logo) = uploadClubLogo($param['email']);
				if ($errMSG != '')
					handle_error($errMSG, __FILE__, __LINE__);
			}
			else
				$club_logo = 'club.jpg';
			// Create a Club row
			$sql  = "INSERT INTO club (club_type, club_name, club_address, club_country_id, club_contact, club_phone, club_email, club_website, club_logo) ";
			$sql .= "VALUES('$new_club_type', '$new_club_name', '$new_club_address', '$country_id', '$profile_name', '$phone', '$email', '$new_club_website', '$club_logo') ";
			mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
			$club_id = mysqli_insert_id($DBCON);
		}
	}
	else {
		$club_id = 0;
	}

	// Update Database
	$sql =  "INSERT INTO profile (salutation, first_name, last_name, profile_name, gender, date_of_birth, ";
	$sql .= "                   age_proof, age_proof_file, honors, ";
	$sql .= "                   address_1, address_2, address_3, city, state, pin, country_id, phone, whatsapp, email, club_id, ";
	$sql .= "                   facebook_account, twitter_account, instagram_account, campaign_media, ";
	$sql .= "                   yps_login_id, password, verified, avatar, blacklist_match, blacklist_exception) ";
	$sql .= "            VALUES('$salutation', '$first_name', '$last_name', '$profile_name', '$gender','$date_of_birth', ";
	$sql .= "                   '$age_proof', '$age_proof_file', '$honors', ";
	$sql .= "                   '$address_1', '$address_2', '$address_3', '$city', '$state', '$pin', '$country_id', '$phone', '$whatsapp', '$email', '$club_id', ";
	$sql .= "                   '$facebook_account', '$twitter_account', '$instagram_account', '$campaign_media', ";
	$sql .= "                   '$yps_login_id', '$password', '$verified', '$avatar', '$blacklist_match', '$blacklist_exception') ";
	mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$profile_id = mysqli_insert_id($DBCON);

	if ($club_id != 0) {
		$sql = "UPDATE club SET last_updated_by = '$profile_id' WHERE club_id = '$club_id' ";
		mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	}

	// Prepare Email
	$url = http_method() . $_SERVER['HTTP_HOST'];
	if ($yps_login_id != "") {
		$email_prompt = "Email (or) YPS Member ID";
		$password_msg 	= "Login using the Email or YPS Member ID and YPS Password. ";
		$password_msg .= "In case you forgot the password, use the <i>Forgot Password</i> link on ";
		$password_msg .= "<a href='https://www.ypsbengaluru.com/membership-login/password-reset/'>YPS Website</a> ";
		$password_msg .= "to receive the password through email.";
	}
	else {
		$email_prompt = "Email";
		$password_msg 	= "Login using the above Email and Password. In case you forgot the password, ";
		$password_msg .= "use the <i>Reset Password</i> button on Home page to receive a generated password through email.";
	}
	$replace_text = array($profile_name, $email_prompt, $email, $password_msg, $url);
	$text = array('{{NAME}}', '{{EMAIL_PROMPT}}', '{{EMAIL}}', '{{PASSWORD_MSG}}', '{{URL}}');
	$subject = 'Congratulations '. $first_name . '! Your user Registration is complete!';

	send_salon_email($email, $subject, 'registration', $text, $replace_text);

	// Unset session variables used for signing up
	if (isset($_SESSION['yps_login_id'])) unset($_SESSION['yps_login_id']);
	if (isset($_SESSION['first_name'])) unset($_SESSION['first_name']);
	if (isset($_SESSION['last_name'])) unset($_SESSION['last_name']);
	if (isset($_SESSION['gender'])) unset($_SESSION['gender']);
	if (isset($_SESSION['email'])) unset($_SESSION['email']);
	if (isset($_SESSION['phone'])) unset($_SESSION['phone']);
	if (isset($_SESSION['address'])) unset($_SESSION['address']);
	if (isset($_SESSION['city'])) unset($_SESSION['city']);
	if (isset($_SESSION['state'])) unset($_SESSION['state']);
	if (isset($_SESSION['pin'])) unset($_SESSION['pin']);
	if (isset($_SESSION['avatar'])) unset($_SESSION['avatar']);


	$resArray['success'] = TRUE;
	$resArray['msg'] = $_SESSION['success_msg'] = "Registration Successful. A confirmation email has been sent to you. You can proceed to login.";
	echo json_encode($resArray);
}
else
	handle_error("Invalid Registration Request", __FILE__, __LINE__);

?>
