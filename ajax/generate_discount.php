<?php
session_save_path(__DIR__ . "/../inc/session");
session_start();
include_once("../inc/connect.php");
include_once("ajax_lib.php");

// CryptoJs
use Nullix\CryptoJsAes\CryptoJsAes;
// require("../inc/CryptoJsAes.php");

// Add single quotes at the start and end of a string
function quote_string($str) {
	return "'" . $str . "'";
}

function extract_email($row) {
	return $row["email"];
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
		list($width, $height) = getimagesize($file_tmp_name);
		if ($height > 240 || $width > 240) {
			if ($height > 240) {
				$new_width = $width * 240 / $height;
				$new_height = 240;
			}
			if ($width > 240) {
				$new_height = $height * 240 / $width;
				$new_width = 240;
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
$resArray = array();

// if( (! empty($_SESSION['USER_ID'])) && isset($_REQUEST['generate_discounts']) && isset($_REQUEST['verified']) ) {
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

	$yearmonth = $param['yearmonth'];
	$profile_id = $param['profile_id'];
	$user_email = $param['user_email'];
	$currency = $param['currency'];
	$fee_code = $param['fee_code'];
	$club_entered_by = $param['club_entered_by'];
	$minimum_group_size = $param['minimum_group_size'];

	// Get Creator Details
	$sql = "SELECT * FROM profile WHERE profile_id = '$profile_id'";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$user = mysqli_fetch_array($query);

    debug_dump("Test", "Got Creator Details", __FILE__, __LINE__);


	// Assemble all inputs
	// CLUB Details
	$club_id = $param['club_id'];
	$sql = "SELECT * FROM club WHERE club_id = '$club_id'";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$club = mysqli_fetch_array($query);

	$club_name = mysqli_real_escape_string($DBCON, strtoupper($param['club_name']));
	$club_contact = mysqli_real_escape_string($DBCON, strtoupper($param['club_contact']));
//	$club_address = mysqli_real_escape_string($DBCON, strtoupper($param['club_address']));
//	$club_phone = mysqli_real_escape_string($DBCON, $param['club_phone']);
//	$club_email = mysqli_real_escape_string($DBCON, $param['club_email']);
//	$club_website = mysqli_real_escape_string($DBCON, $param['club_website']);

	// CLUB ENTRY Details
	$entrant_category = $param['entrant_category'];
	$group_code = $param['group_code'];
	$payment_mode = $param['payment_mode'];
	$discount_code = $param['discount_code'];

	if (isset($param['member_email']))
		$member_email = $param['member_email'];	// Array of emails added
	else
		$member_email = array();

	if (isset($param['delete_email']))
		$delete_email = $param['delete_email'];	// Array of Emails to be deleted
	else
		$delete_email = array();

	// Validate Emails & Coupon Eligibility
	// valid_emails = member_email - invalid_emails - ypslist
	$invalid_emails = array();
	$valid_emails = array();

	// Add emails for which coupon has already been generated and add them to $invalid_emails
	if (sizeof($member_email) > 0 ) {
		$sql  = "SELECT email FROM coupon ";
		$sql .= "WHERE discount_code = '$discount_code' ";
		$sql .= "  AND yearmonth = '$yearmonth' ";
		$sql .= "  AND club_id = '$club_id' ";
		$sql .= "  AND email IN (" . implode(",", array_map("quote_string", $member_email)) . ") ";
		debug_dump("SQL", $sql, __FILE__, __LINE__);
		$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		while($row = mysqli_fetch_array($query))
			$invalid_emails[] = $row['email'];
	}
debug_dump("Test", "end of first if", __FILE__, __LINE__);


	// If a profile with a matching email is already registered for the Salon and fees paid, add to $invalid_emails
	if (sizeof(array_diff($member_email, $invalid_emails)) > 0) {
		$sql  = "SELECT email FROM entry, profile ";
		$sql .= "WHERE yearmonth = '$yearmonth' ";
		$sql .= "  AND entry.profile_id = profile.profile_id ";
		$sql .= "  AND payment_received > 0.0 ";
		$sql .= "  AND email IN (" . implode(",", array_map("quote_string", array_diff($member_email, $invalid_emails))) . ") ";
		debug_dump("SQL", $sql, __FILE__, __LINE__);
		$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		while($row = mysqli_fetch_array($query))
			$invalid_emails[] = $row['email'];
	}
debug_dump("Test", "end of second if", __FILE__, __LINE__);


	// Eliminate members with different entrant_category
	if (sizeof(array_diff($member_email, $invalid_emails)) > 0) {
		$sql  = "SELECT email FROM entry, profile ";
		$sql .= "WHERE yearmonth = '$yearmonth' ";
		$sql .= "  AND entry.profile_id = profile.profile_id ";
		$sql .= "  AND entry.entrant_category != '$entrant_category' ";
		$sql .= "  AND email IN (" . implode(",", array_map("quote_string", array_diff($member_email, $invalid_emails))) . ") ";
		debug_dump("SQL", $sql, __FILE__, __LINE__);
		$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		while($row = mysqli_fetch_array($query))
			$invalid_emails[] = $row['email'];
	}
debug_dump("Test", "end of third if", __FILE__, __LINE__);


	// Eliminate Members of YPS from the email list


// Commenting this line due to time delay in fetching yps_users
// 	list($errmsg, $ypslist) = yps_users();
// 	debug_dump("YPSBENGALURU.COM", $ypslist , __FILE__, __LINE__);

    $errmsg = "*";
	if ($errmsg == "")
		$yps_email_list = array_map("extract_email", $ypslist);
	else
		$yps_email_list = array();		// If there is a challenge getting yps member list. make ypslist empty

	debug_dump("yps_email_list", $yps_email_list , __FILE__, __LINE__);

	// Final List of valid emails to be added
	$valid_emails = array_diff($member_email, $invalid_emails, $yps_email_list); 	// Filter invalid emails and yps emails from member emails

	// debug_dump("member_email", $member_email, __FILE__, __LINE__);
	// debug_dump("invalid_emails", array_diff($member_email, $valid_emails), __FILE__, __LINE__);
	// debug_dump("valid_emails", $valid_emails, __FILE__, __LINE__);

	// Validating eligibility for discounts by meeting minimum_group_size
	// existng_members + new_valid_members - deleted_members should be >= minimum_group_size
	//
	// existing members
	$sql = "SELECT * FROM coupon WHERE yearmonth = '$yearmonth' AND club_id = '$club_id'";
	debug_dump("SQL1", $sql , __FILE__, __LINE__);
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$existing_members = mysqli_num_rows($query);

	// members requested to be deleted
	// determine how many can be deleted
	// Those who have entered the contest and paid cannot be deleted
	//
	if (sizeof($delete_email) > 0) {
		$sql  = "SELECT * FROM coupon ";
		$sql .= " WHERE yearmonth = '$yearmonth' ";
		$sql .= "   AND club_id = '$club_id' ";
		$sql .= "   AND email IN ('dummy', " . implode(",", array_map("quote_string", $delete_email)) . ") ";
		$sql .= "   AND profile_id NOT IN ( ";
		$sql .= "       SELECT profile_id FROM entry ";
		$sql .= "        WHERE yearmonth = '$yearmonth' ";
		$sql .= "          AND payment_received != 0 ) ";
    	debug_dump("SQL2", $sql , __FILE__, __LINE__);
		$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		$deleting_members = mysqli_num_rows($query);
	}
	else
		$deleting_members = 0;

	if (($existing_members - $deleting_members + sizeof($valid_emails)) < $minimum_group_size)
		if (sizeof(array_diff($member_email, $valid_emails)) != 0)	// If emails have been deleted from $member_email
			handle_error("Not meeting the minimum number of " . $minimum_group_size . " members required after eliminating the following emails already registered under different categories:" . implode(", ", array_diff($member_email, $valid_emails)), __FILE__, __LINE__);
		else
			handle_error("Not meeting the minimum number of " . $minimum_group_size . " members required.", __FILE__, __LINE__);

	debug_dump("Test Msg1", "Test Msg1" , __FILE__, __LINE__);

	// CLUB Updates
	// Upload Club Logo
	$club_logo = $param['cur_logo'];
	if( isset($_FILES['new_club_logo']) && ! empty($_FILES['new_club_logo']['name']) ) {
		list($errMSG, $club_logo) = uploadClubLogo($user_email);
		if ($errMSG != '')
			handle_error($errMSG, __FILE__, __LINE__);
	}

	debug_dump("Test Msg2", "Test Msg2" , __FILE__, __LINE__);

	// Update Club Details with revisions made
//	$sql  = "UPDATE club ";
//	$sql .= "   SET club_name = '$club_name', ";
//	$sql .= "       club_address = '$club_address', ";
//	$sql .= "       club_contact = '$club_contact', ";
//	$sql .= "       club_phone = '$club_phone', ";
//	$sql .= "       club_email = '$club_email', ";
//	$sql .= "       club_website = '$club_website', ";
//	$sql .= "       club_logo = '$club_logo' ";
//	$sql .= " WHERE club_id = '$club_id' ";
//	mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// CLUB_ENTRY Updates
	// Insert or Update Club Entry
	//
//	$sql = "SELECT * FROM club_entry WHERE yearmonth = '$yearmonth' AND club_id = '$club_id' ";
//	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
//	if (mysqli_num_rows($query) == 0) {
//		// First time - Insert club_entry
//		$sql  = "INSERT INTO club_entry (yearmonth, club_id, club_entered_by, currency, payment_mode, entrant_category, fee_code, group_code, minimum_group_size) ";
//		$sql .= "VALUES ('$yearmonth', '$club_id', '$club_entered_by', '$currency', '$payment_mode', '$entrant_category', '$fee_code', '$group_code', '$minimum_group_size' ) ";
//		mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
//	}
//	else {
//		$sql  = "UPDATE club_entry ";
//		$sql .= "   SET currency = '$currency' ";
//		$sql .= "     , payment_mode = '$payment_mode' ";
//		$sql .= "     , entrant_category = '$entrant_category' ";
//		$sql .= "     , fee_code = '$fee_code' ";
//		$sql .= "     , group_code = '$group_code' ";
//		$sql .= "     , minimum_group_size = '$minimum_group_size' ";
//		$sql .= " WHERE yearmonth = '$yearmonth' ";
//		$sql .= "   AND club_id = '$club_id' ";
//		mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
//	}

	// COUPON Updates

	// Delete coupons first, assuming that any subsequent additions are supposed to remain
	// Delete Coupons for emails marked for deletion
	if (sizeof($delete_email) > 0) {
		$sql  = "DELETE FROM coupon ";
		$sql .= " WHERE yearmonth = '$yearmonth' ";
		$sql .= "   AND club_id = '$club_id' ";
		$sql .= "   AND email IN (" . implode(",", array_map("quote_string", $delete_email)) . ") ";
		$sql .= "   AND (coupon.profile_id = 0 OR ";
		$sql .= "        coupon.profile_id IN ( ";
		$sql .= "           SELECT profile_id FROM entry ";
		$sql .= "            WHERE yearmonth = '$yearmonth' ";
		$sql .= "              AND payment_received = 0 ";
		$sql .= "           ) ";
		$sql .= "        ) ";
		debug_dump("SQL", $sql, __FILE__, __LINE__);
		mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	}

	// Add Coupons

	// Get Email Template
	$sql = "SELECT * FROM email_template WHERE template_code = 'discount_coupon'";
	$queryEmail = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$rowEmail = mysqli_fetch_array($queryEmail);

	$sql = "SELECT * FROM contest WHERE yearmonth = '$yearmonth' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$contest = mysqli_fetch_array($query);

	// Create coupons and send email
	foreach ($valid_emails as $email) {
		// Check if a profile match is found for the email
		$sql = "SELECT profile_id FROM profile WHERE email = '$email' ";
		$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		if (mysqli_num_rows($query) > 0) {
			$row = mysqli_fetch_array($query);
			$coupon_profile_id = $row['profile_id'];
		}
		else
			$coupon_profile_id = 0;
		$coupon_text = sprintf("C%03d", $club_id) . "-" . rand(1000,4999) . "-" . rand(5000,9999);
		$discount_code = $param['discount_code'];
		$sql  = "INSERT INTO coupon (yearmonth, email, coupon_text, discount_code, club_id, profile_id, fee_code) ";
		$sql .= "VALUES ('$yearmonth', '$email', '$coupon_text', '$discount_code', '$club_id', '$coupon_profile_id', '$fee_code') ";
		mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

		// Send Email
		$url = http_method() . $_SERVER['HTTP_HOST'];
		// $email_format = $rowEmail['email_body'];

		if (file_exists("../salons/$contest_yearmonth/blob/partner_data.php")) {
			include("../salons/$contest_yearmonth/blob/partner_data.php");
			$partner_footer = partner_email_footer($yearmonth);
		}
		else
			$partner_footer = "";

		$replace_text = array($user['profile_name'], $email, $coupon_text, $club_name,
								$contest['registration_last_date'], $url, $contest['contest_name'], $partner_footer);
		$text = array('{{NAME}}', '{{EMAIL}}', '{{COUPON}}', '{{CLUB_NAME}}', '{{LAST_DATE}}', '{{URL}}', '{{CONTEST}}', '{{PARTNERS}}');
		// $email_format = str_replace($text, $replace_text, $email_format);
		$subject = 'Congratulations ! You have a discount code waiting for ' . $contest['contest_name'];

		send_salon_email($email, $subject, 'discount_coupon', $text, $replace_text);

	}

	// ENTRY updates
	// Update all entry records if payment has not been made
	$sql  = "UPDATE entry, coupon ";
	$sql .= "   SET entry.fee_code = '$fee_code' ";
	$sql .= "     , entry.entrant_category = '$entrant_category' ";
	$sql .= "     , entry.group_code = '$group_code' ";
	$sql .= " WHERE entry.yearmonth = '$yearmonth' ";
	$sql .= "   AND coupon.yearmonth = entry.yearmonth ";
	$sql .= "   AND coupon.profile_id = entry.profile_id ";
	$sql .= "   AND entry.payment_received = 0.0 ";
	mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);


	$resArray['success'] = TRUE;
	$resArray['msg'] = $_SESSION['signup_msg'] = "Club Registered. Coupons mailed. " . ((sizeof(array_diff($member_email, $valid_emails)) > 0) ? "The following emails are not eligible and are not added: " . implode(", ", array_diff($member_email, $valid_emails)) : "");
	echo json_encode($resArray);
}
else
	handle_error("Invalid Update Request", __FILE__, __LINE__);

	 // header("Location:../sign_up.php");
     // printf("<script>location.href='../sign_up.php'</script>");

?>
