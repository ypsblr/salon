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
		$avatar_file = strtoupper(substr(str_alpha($email), 0, 8)) . "-" . rand(1000,9999) . "." . $file_ext;
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

// Upload Age Proof File if provided
function uploadAgeProof($email) {
	$errMSG = '';
	$age_proof_file = '';
	$file_name = $_FILES['age_proof_file']['name'];
	$file_tmp_name = $_FILES['age_proof_file']['tmp_name'];
	$file_size = $_FILES['age_proof_file']['size'];

	if($file_name) {
		$upload_dir = '../res/age_proof/'; // upload directory for Age Proof
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
	$file_name = $_FILES['club_logo']['name'];
	$file_tmp_name = $_FILES['club_logo']['tmp_name'];
	$file_size = $_FILES['club_logo']['size'];
	$logo_file = "";

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


// Main Code

$resArray = array();

// if( isset($_SESSION['USER_ID']) && isset($_POST['update_info']) && isset($_POST['verified'])) {
if ( (! empty($_SESSION['SALONBOND'])) && (! empty($_REQUEST['ypsd'])) ) {

	// Decrypt the inputs
	$param = CryptoJsAes::decrypt($_REQUEST['ypsd'], $_SESSION['SALONBOND']);
	debug_dump("PARAM", $param, __FILE__, __LINE__);


    // Verify Captcha Confirmation
	/*
	** Blocking Google Recaptcha from logged-in forms
	**
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
	*/

	if (empty($param['verified']))
		handle_error("Confirm correctness by ticking the checkbox", __FILE__, __LINE__);

	// Assemble all inputs
	$contest_yearmonth = $param['contest_yearmonth'];
	$profile_id = $param['profile_id'];
	$email = mysqli_real_escape_string($DBCON, $param['email']);
	$salutation = $param['salutation'];
	$first_name = mysqli_real_escape_string($DBCON, $param['first_name']);
	$last_name = mysqli_real_escape_string($DBCON, $param['last_name']);
	$profile_name = mysqli_real_escape_string($DBCON, $param['first_name'] . " " . $param['last_name']);
	// $profile_name = strtoupper($first_name . " " . $last_name);
	$gender = $param['gender'];
	$date_of_birth = $param['date_of_birth'];
	$age_proof = $param['age_proof'];

	// Upload Age Proof File
	if((isset($_FILES['age_proof_file'])) && ! empty($_FILES['age_proof_file']['name']) ) {
		list($errMSG, $age_proof_file) = uploadAgeProof($param['email']);
		if ($errMSG != '')
			handle_error($errMSG, __FILE__, __LINE__);
	}
	else
		$age_proof_file = $param['cur_age_proof_file'];

	$honors = mysqli_real_escape_string($DBCON, strtoupper($param['honors']));
	$address_1 = mysqli_real_escape_string($DBCON, strtoupper($param['address_1']));
	$address_2 = mysqli_real_escape_string($DBCON, strtoupper($param['address_2']));
	$address_3 = mysqli_real_escape_string($DBCON, strtoupper($param['address_3']));
	$country_id = $param['country_id'];
	$city = mysqli_real_escape_string($DBCON, strtoupper($param['city']));
	$state = mysqli_real_escape_string($DBCON, strtoupper($param['state']));
	$pin = $param['pin'];
	$phone = $param['phone'];
	$whatsapp = $param['whatsapp'];
	$facebook_account = $param['facebook_account'];
	$twitter_account = $param['twitter_account'];
	$instagram_account = $param['instagram_account'];

	// Upload Avatar
	if( isset($_FILES['avatar']) && ! empty($_FILES['avatar']['name']) ) {
		list($errMSG, $avatar) = uploadAvatar($param['email']);
		if ($errMSG != '')
			handle_error($errMSG, __FILE__, __LINE__);
	}
	else
		$avatar = $param['cur_avatar'];

	// Bank Details
	if (isset($param['require_bank_details']) && $param['require_bank_details'] == "Yes") {
		$bank_account_number = mysqli_real_escape_string($DBCON, $param['bank_account_number'] . "");
		$bank_account_name = mysqli_real_escape_string($DBCON, strtoupper($param['bank_account_name']));
		$bank_account_type = mysqli_real_escape_string($DBCON, $param['bank_account_type']);
		$bank_name = mysqli_real_escape_string($DBCON, $param['bank_name']);
		$bank_branch = mysqli_real_escape_string($DBCON, $param['bank_branch']);
		$bank_ifsc_code = mysqli_real_escape_string($DBCON, $param['bank_ifsc_code']);
	}

	// Update/Insert club
	$yps_login_id = $param['yps_login_id'];
	if ($yps_login_id == "") {
		$club_id = (empty($param['club_id']) || $param['club_id'] == "") ? "0" : $param['club_id'];
		if ($club_id == "new") {
			// New Club, if created
			debug_dump("adding club", $club_id, __FILE__, __LINE__);
			$new_club_type = $param['club_type'];
			$new_club_name = mysqli_real_escape_string($DBCON, strtoupper($param['club_name']));
			$new_club_website = mysqli_real_escape_string($DBCON, $param['club_website']);
			$new_club_address = mysqli_real_escape_string($DBCON, strtoupper($param['club_address']));
			// Upload Logo
			if( isset($_FILES['club_logo']) && ! empty($_FILES['club_logo']['name']) ) {
				list($errMSG, $club_logo) = uploadClubLogo($param['profile_id']);
				if ($errMSG != '')
					handle_error($errMSG, __FILE__, __LINE__);
			}
			else
				$club_logo = 'club.jpg';
			// Create a Club row
			$sql  = "INSERT INTO club (club_type, club_name, club_address, club_contact, club_phone, club_email, club_website, club_logo, last_updated_by) ";
			$sql .= "VALUES('$new_club_type', '$new_club_name', '$new_club_address', '$profile_name', '$phone', '$email', '$new_club_website', '$club_logo', '$profile_id') ";
			mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
			$club_id = mysqli_insert_id($DBCON);
		}
		else if ($club_id != "0") {
			// Update existing club
			$sql = "SELECT * FROM club WHERE club_id = '$club_id' ";
			$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
			if (mysqli_num_rows($query) == 0)
				handle_error("Club not found", __FILE__, __LINE__);
			else {
				$club = mysqli_fetch_array($query);
				if ($club['club_type'] != $param['club_type'] || $club['club_name'] != $param['club_name'] ||
						$club['club_website'] != $param['club_website'] || $club['club_address'] != $param['club_address'] ||
						isset($_FILES['club_logo']) || isset($param['make_me_club_contact']) ) {
					// Update the club
					$club_type = $param['club_type'];
					$club_name = mysqli_real_escape_string($DBCON, strtoupper($param['club_name']));
					$club_website = mysqli_real_escape_string($DBCON, $param['club_website']);
					$club_address = mysqli_real_escape_string($DBCON, strtoupper($param['club_address']));
					if (isset($param['make_me_club_contact'])) {
						$club_contact = mysqli_real_escape_string($DBCON, $profile_name);
						$club_phone = $phone;
						$club_email = mysqli_real_escape_string($DBCON, $email);
					}
					else {
						$club_contact = mysqli_real_escape_string($DBCON, $club['club_contact']);
						$club_phone = $club['club_phone'];
						$club_email = mysqli_real_escape_string($DBCON, $club['club_email']);
					}
					// Upload Avatar
					if( isset($_FILES['club_logo']) && ! empty($_FILES['club_logo']['name']) ) {
						list($errMSG, $club_logo) = uploadClubLogo($param['profile_id']);
						if ($errMSG != '')
							handle_error($errMSG, __FILE__, __LINE__);
					}
					else
						$club_logo = $club['club_logo'];
					// Update
					$sql  = "UPDATE club ";
					$sql .= "   SET club_type = '$club_type' ";
					$sql .= "     , club_name = '$club_name' ";
					$sql .= "     , club_contact = '$club_contact' ";
					$sql .= "     , club_phone = '$club_phone' ";
					$sql .= "     , club_email = '$club_email' ";
					$sql .= "     , club_website = '$club_website' ";
					$sql .= "     , club_address = '$club_address' ";
					$sql .= "     , club_logo = '$club_logo' ";
					$sql .= "     , last_updated_by = '$profile_id' ";
					$sql .= " WHERE club_id = '$club_id' ";
					mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
				}
			}
		}
	}
	else {
		$club_id = 0;
	}

	// Update Database
	$sql  = "UPDATE profile ";
	$sql .= "SET email = '$email' ";	// Generally Readonly. Update allowed to sync with yps
	$sql .= ",   salutation = '$salutation' ";
	$sql .= ",   first_name = '$first_name' ";
	$sql .= ",   last_name = '$last_name' ";
	$sql .= ",   profile_name = '$profile_name' ";
	$sql .= ",   gender = '$gender' ";
	$sql .= ",   date_of_birth = '$date_of_birth' ";
	$sql .= ",   age_proof = '$age_proof' ";
	$sql .= ",   age_proof_file = '$age_proof_file' ";
	$sql .= ",   verified = 1 ";
	$sql .= ",   honors = '$honors' ";
	$sql .= ",   address_1 = '$address_1' ";
	$sql .= ",   address_2 = '$address_2' ";
	$sql .= ",   address_3 = '$address_3' ";
	$sql .= ",   city = '$city' ";
	$sql .= ",   state = '$state' ";
	$sql .= ",   pin = '$pin' ";
	$sql .= ",   country_id = '$country_id' ";
	$sql .= ",   phone = '$phone' ";
	$sql .= ",   whatsapp = '$whatsapp' ";
	$sql .= ",   facebook_account = '$facebook_account' ";
	$sql .= ",   twitter_account = '$twitter_account' ";
	$sql .= ",   instagram_account = '$instagram_account' ";
	$sql .= ",   avatar = '$avatar' ";
	$sql .= ",   club_id = '$club_id' ";
	if (isset($param['require_bank_details']) && $param['require_bank_details'] == "Yes") {
		$sql .= ",  bank_account_number = '$bank_account_number' ";
		$sql .= ",  bank_account_name = '$bank_account_name' ";
		$sql .= ",  bank_account_type = '$bank_account_type' ";
		$sql .= ",  bank_name = '$bank_name' ";
		$sql .= ",  bank_branch = '$bank_branch' ";
		$sql .= ",  bank_ifsc_code = '$bank_ifsc_code' ";
	}
	$sql .= " WHERE profile_id = '$profile_id' ";
	mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	$resArray['success'] = TRUE;
	$resArray['msg'] = $_SESSION['success_msg'] = "Changes have been successfully updated.";
	echo json_encode($resArray);
}
else
	handle_error("Invalid Update Request", __FILE__, __LINE__);
 ?>
