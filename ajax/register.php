<?php
session_save_path(__DIR__ . "/../inc/session");
session_start();
include_once("../inc/connect.php");
include_once("ajax_lib.php");

// CryptoJs
use Nullix\CryptoJsAes\CryptoJsAes;
// require("../inc/CryptoJsAes.php");

// A function to return an error message and stop
function return_error($errmsg) {
	$resArray['success'] = FALSE;
	$resArray['msg'] = $errmsg;
	echo json_encode($resArray);
	die;
}


// Main Code

$resArray = array();

//if( isset($_SESSION['USER_ID']) && (! empty($_REQUEST['profile_id'])) && isset($_REQUEST['salon_registration']) && isset($_REQUEST['agree_to_tc']) ) {
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

	// Assemble all inputs
	if (empty($param['agree_to_tc']))
		handle_error("Tick on Agree to Terms and Conditions to register for the salon", __FILE__, __LINE__);

	$yearmonth = $param['yearmonth'];
	$profile_id = $param['profile_id'];
	$yps_login_id = $param['yps_login_id'];
	$club_id = $param['club_id'];
	$payment_mode = $param['payment_mode'];
	$entrant_category = $param['entrant_category'];
	//$contest_digital_sections = $param['contest_digital_sections'];
	//$contest_print_sections = $param['contest_print_sections'];
	$group_code = $param['group_code'];

	// Get Contest
	$sql = "SELECT * FROM contest WHERE yearmonth = '$yearmonth' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$contest = mysqli_fetch_array($query);

	// Update Database
	// Check if already registered - allow changes to be made
	$sql = "SELECT * FROM entry WHERE yearmonth = '$yearmonth' AND profile_id = '$profile_id' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_num_rows($query) == 0) {
		// Yet to Register
		// Modified on 2020/11/02 - Check for End Date
		$closing_time = $contest['registration_last_date'] . " 23:45";
		$timezone = $contest['submission_timezone'];
		if (strtotime_tz("now", $timezone) > strtotime_tz($closing_time, $timezone))
			handle_error("Registration closes 15 mminutes earlier to provide time to pay and upload.", __FILE__, __LINE__);

		$sql  = "INSERT INTO entry (yearmonth, profile_id, entrant_category, fee_waived, participation_code, digital_sections, print_sections, currency, group_code, agree_to_tc) ";
		$sql .= "SELECT yearmonth, '$profile_id', entrant_category, fee_waived, default_participation_code, default_digital_sections, default_print_sections, currency, '$group_code', '1' ";
		$sql .= "  FROM entrant_category ";
		$sql .= " WHERE yearmonth = '$yearmonth' ";
		$sql .= "   AND entrant_category = '$entrant_category' ";
		mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	}
	else {
		// Already Registered
		// Set all fee_code etc to default
		$sql  = "UPDATE entry, entrant_category ";
		$sql .= "   SET entry.entrant_category = entrant_category.entrant_category ";
		$sql .= "     , entry.fee_waived = entrant_category.fee_waived ";
		$sql .= "     , entry.currency = entrant_category.currency ";
		$sql .= "     , entry.group_code = '$group_code' ";
		$sql .= "     , participation_code = entrant_category.default_participation_code ";
		$sql .= "     , digital_sections = entrant_category.default_digital_sections ";
		$sql .= "     , print_sections = entrant_category.default_print_sections ";
		$sql .= "     , fees_payable = '0.0' ";
		$sql .= "     , discount_applicable = '0.0' ";
		$sql .= "     , fee_code = '' ";
		$sql .= " WHERE entry.yearmonth = '$yearmonth' ";
		$sql .= "   AND entry.profile_id = '$profile_id' ";
		$sql .= "   AND entrant_category.yearmonth = '$yearmonth' ";
		$sql .= "   AND entrant_category.entrant_category = '$entrant_category' ";
		mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	}


	// Check if there are Group Settings and Payments
	// Update from pre-paid coupons
	if ($yps_login_id == "" && $club_id != 0 && $payment_mode = 'GROUP_PAYMENT') {
		$sql  = "UPDATE entry, coupon, profile ";
		$sql .= "   SET entry.participation_code = coupon.participation_code, ";
		$sql .= "       entry.digital_sections = coupon.digital_sections, ";
		$sql .= "       entry.print_sections = coupon.print_sections, ";
		$sql .= "       entry.fees_payable = coupon.fees_payable, ";
		$sql .= "       entry.discount_applicable = coupon.discount_applicable, ";
		$sql .= "       entry.payment_received = coupon.payment_received, ";
		$sql .= "       entry.fee_code = coupon.fee_code, ";
		$sql .= "       coupon.profile_id = entry.profile_id ";
		$sql .= " WHERE entry.yearmonth = '$yearmonth' ";
		$sql .= "   AND coupon.yearmonth = '$yearmonth' ";
		$sql .= "   AND entry.profile_id = '$profile_id' ";
		$sql .= "   AND profile.profile_id = entry.profile_id ";
		$sql .= "   AND coupon.email = profile.email ";
		$sql .= "   AND coupon.club_id = profile.club_id ";
		mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	}

	$resArray['success'] = TRUE;
	$resArray['msg'] = $_SESSION['success_msg'] = "Registration to Salon is successful. Select sections and make payment before uploading.";
	
	debug_dump("SQL", $sql, __FILE__, __LINE__);

	echo json_encode($resArray);
}
else
	handle_error("Invalid Registration Request", __FILE__, __LINE__);

?>
