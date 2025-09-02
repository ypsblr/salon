<?php
/*************************************/
/** ==============================  **/
/**  this is a PRODUCTION VERSION   **/
/** ==============================  **/
/*************************************/
session_save_path(__DIR__ . "/../inc/session");
session_start();
include_once("../inc/connect.php");
include_once("../inc/lib.php");
include_once "Instamojo.php";

// CryptoJs
use Nullix\CryptoJsAes\CryptoJsAes;
// require("../inc/CryptoJsAes.php");

/*************************************/
/** ==============================  **/
/**  this is a PRODUCTION VERSION   **/
/** ==============================  **/
/*************************************/
// Contains integration code for Instamojo
// Funtion initiate payment to create a Payment Request and redirect to Instamojo Payment Screen
function initiate_payment($purpose, $amount, $yearmonth, $club, $name, $email, $phone, $redirect) {
	// Create an Instamojo Payment Request
	// echo $purpose . "|" . $amount . "|" . $entry . "|" . $name . "|" . $email . "|" . $phone;
	try {
		$api = new Instamojo\Instamojo("f846aec243d5b5dfd76c48b55a112533", "3ba6401957ee54ddd97515648bc5fae2", 'https://www.instamojo.com/api/1.1/');		// Production API Keys and Portal

		$payment_request_param = array(
			"purpose" => sprintf("%s|GRP|%d|%d", $purpose, $yearmonth, $club),
			"amount" => $amount,
			"send_email" => false,
			"buyer_name" => $name,
			"email" => $email,
			"phone" => $phone,
			"allow_repeated_payments" => false,
			"redirect_url" => http_method() . $_SERVER['SERVER_NAME'] . "/" . $redirect,		// Return Back to the calling page
			"webhook" => http_method() . $_SERVER['SERVER_NAME'] . "/op/hook_instamojo.php"
			);
		$payment_request = $api->paymentRequestCreate($payment_request_param);
		// var_dump($payment_request);
		header('Location: ' . $payment_request['longurl']);		// Load Instamojo Payment URL
		printf("<script>location.href='" . $payment_request['longurl'] . "'</script>");
		return ("");
	}
	catch (Exception $e) {
		return('Error: ' . $e->getMessage());
	}
}

/*************************************/
/** ==============================  **/
/**  this is a PRODUCTION VERSION   **/
/** ==============================  **/
/*************************************/
// Contains integration code for Paypal
// Implements function initiate_payment() to request payment
// $msg = initiate_paypal("CLUB_FEES", $club_total_payment_due, $yearmonth, $club_id, $user['profile_name'], $user['email'], $user['phone'], $return_url, $cancel_url);

function initiate_paypal($purpose, $amount, $currency, $yearmonth, $club_id, $name, $email, $phone, $redirect, $cancelled) {
	// PayPal settings for Production
	$paypal_email = "treasurer@ypsbengaluru.com";				// Live Account
	$return_url = http_method() . $_SERVER['SERVER_NAME'] . "/" . $redirect;
	$cancel_url = http_method() . $_SERVER['SERVER_NAME'] . "/" . $cancelled;
	$notify_url = http_method() . $_SERVER['SERVER_NAME'] . "/op/paypal_hook.php";
	$logo_url = http_method() . $_SERVER['SERVER_NAME'] . "/img/ypsLogo.png";

    $querystring = '';

    // Firstly Append paypal account to querystring
    $querystring .= "?business=" . urlencode($paypal_email);

    // Append amount&amp;amp;amp; currency (£) to querystring so it cannot be edited in html

    //The item name and amount can be brought in dynamically by querying the $param['item_number'] variable.
    //$querystring .= "&item_name=" . urlencode(sprintf("%s-%04d", $purpose, $entry));
    $querystring .= "&item_name=" . urlencode(sprintf("%s|GRP|%d|%d", $purpose, $yearmonth, $club_id));
	$querystring .= "&item_number=" . urlencode(sprintf("%d|%d", $yearmonth, $club_id));
    $querystring .= "&amount=" . urlencode($amount);
	$querystring .= "&currency_code=" . urlencode($currency);
	$querystring .= "&payer_email=" . urlencode($email);
	$querystring .= "&first_name=" . urlencode($name);
	$querystring .= "&last_name=" . urlencode($name);

	// Other mandatory hidden fields
	$querystring .= "&cmd=" .  urlencode("_xclick");
	$querystring .= "&no_shipping=" . urlencode("1");
	$querystring .= "&handling=" . urlencode("0");
	$querystring .= "&cpp_header_image=" . urlencode(stripslashes($logo_url));
	$querystring .= "&bn=" . urlencode("PP-BuyNowBF:btn_buynow_LG.gif:NonHostedGuest");

    // Append paypal return addresses
    $querystring .= "&return=".urlencode(stripslashes($return_url));
    $querystring .= "&cancel_return=".urlencode(stripslashes($cancel_url));
    $querystring .= "&notify_url=".urlencode($notify_url);

    // Append querystring with custom field
    //$querystring .= "&amp;amp;amp;custom=".USERID;

    // Redirect to paypal IPN
	//	echo $querystring;
	// header('HTTP/1.0 302 Found');
	// debug_dump("querystring", $querystring, __FILE__, __LINE__);
	header('location:https://www.paypal.com/cgi-bin/webscr' . $querystring);										// Live Account
	echo "<script>location.href='https://www.paypal.com/cgi-bin/webscr" . $querystring . "'</script>";			// Live

	return "";
}



// Add single quotes at the start and end of a string
function quote_string($str) {
	return "'" . $str . "'";
}


// Main Code
$resArray = array();

// if( isset($_SESSION['USER_ID']) && isset($_REQUEST['make_group_payment']) ) {
if ( (! empty($_SESSION['SALONBOND'])) && (! empty($_REQUEST['ypsd'])) ) {

	// Decrypt the inputs
	$param = CryptoJsAes::decrypt($_REQUEST['ypsd'], $_SESSION['SALONBOND']);
	debug_dump("PARAM", $param, __FILE__, __LINE__);

    // debug_dump("REQUEST", $param, __FILE__, __LINE__);
    // Verify Captcha Confirmation
	// Doing Away with Captcha validation for logged in forms
	//
	/***
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
	***/

	$yearmonth = $param['yearmonth'];

	// Get Creator Details
	$profile_id = $param['profile_id'];
	$sql = "SELECT * FROM profile WHERE profile_id = '$profile_id' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$user = mysqli_fetch_array($query);

	// Get Club Details
	$club_id = $param['club_id'];
	$sql = "SELECT * FROM club WHERE club_id = '$club_id' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$club = mysqli_fetch_array($query);

	// Assemble inputs
	$currency = $param['currency'];
	$fee_code = $param['fee_code'];

	// Table fields as arrays
	// $coupon_id = $param["coupon_id"];
	$coupon_participation_code = $param['participation_code'];
	$coupon_email = $param['email'];
	$coupon_digital_sections = isset($param['digital_sections']) ? $param['digital_sections'] : false;
	$coupon_print_sections = isset($param['print_sections']) ? $param['print_sections'] : false;
	$coupon_fees_payable = $param['fees_payable'];
	$coupon_discount_applicable = $param['discount_applicable'];
	$coupon_payment_received = $param['payment_received'];

	$club_total_fees = $param['club_total_fees'];
	$club_total_discount = $param['club_total_discount'];
	$club_total_payable = $param['club_total_payable'];
	$club_total_payment_received = $param['club_total_payment_received'];
	$club_total_payment_due = $param['club_total_payment_due'];

	// Update the CLUB_ENTRY
	$sql  = "UPDATE club_entry ";
	$sql .= "SET currency = '$currency', ";
	$sql .= "    fee_code = '$fee_code', ";
	$sql .= "    total_fees = '$club_total_fees', ";
	$sql .= "    total_discount = '$club_total_discount' ";
	$sql .= "WHERE yearmonth = '$yearmonth' ";
	$sql .= "  AND club_id = '$club_id' ";

	mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Update Coupons
	for ($i = 0; $i < sizeof($coupon_email); ++ $i) {
		if ($coupon_digital_sections)
			$update_digital_sections = $coupon_digital_sections[$i];
		else
			$update_digital_sections = 0;
		if ($coupon_print_sections)
			$update_print_sections = $coupon_print_sections[$i];
		else
			$update_print_sections = 0;
		$sql  = "UPDATE coupon ";
		$sql .= "SET participation_code = '" . $coupon_participation_code[$i] . "', ";
		$sql .= "    fee_code = '$fee_code', ";
		$sql .= "    digital_sections = '$update_digital_sections', ";
		$sql .= "    print_sections = '$update_print_sections', ";
		$sql .= "    fees_payable = '" . trim($coupon_fees_payable[$i]) . "', ";
		$sql .= "    discount_applicable = '" . trim($coupon_discount_applicable[$i]) . "' ";
		$sql .= "WHERE yearmonth = '$yearmonth' ";
		$sql .= "  AND club_id = '$club_id' ";
		$sql .= "  AND email = '" . $coupon_email[$i] . "' ";
		mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	}

	// Update Entry table with fee received for all members who have entered into the Salon
	$sql  = "UPDATE entry, coupon ";
	$sql .= "SET entry.fee_code = coupon.fee_code, ";
	$sql .= "    entry.participation_code = coupon.participation_code, ";
	$sql .= "    entry.digital_sections = coupon.digital_sections, ";
	$sql .= "    entry.print_sections = coupon.print_sections, ";
	$sql .= "    entry.fees_payable = coupon.fees_payable, ";
	$sql .= "    entry.discount_applicable = coupon.discount_applicable ";
	$sql .= "WHERE entry.yearmonth = '$yearmonth' ";
	$sql .= "  AND entry.yearmonth = coupon.yearmonth ";
	$sql .= "  AND entry.profile_id = coupon.profile_id ";
	$sql .= "  AND coupon.club_id = '$club_id' ";
	mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Now it is time to initiate payment
	if( $club_total_payment_due > 0 ){

		/**********************************************
		 *** REMOVE COMMENTS BEFORE MOVING TO PRODN ***
		 **********************************************/
		if($param['gateway'] == 'Instamojo'){
			$msg = initiate_payment("CLUB_FEES", $club_total_payment_due, $yearmonth, $club_id, $user['profile_name'], $user['email'], $user['phone'], "user_panel.php");
		}
		if($param['gateway'] == 'PayPal') {
			$return_url = "group_payment.php?msg=OK";
			$cancel_url = "group_payment.php?msg=CANCELLED";
			$msg = initiate_paypal("CLUB_FEES", $club_total_payment_due, $currency, $yearmonth, $club_id, $user['profile_name'], $user['email'], $user['phone'], $return_url, $cancel_url);
		}
		/**********************************************
		 *** REMOVE COMMENTS BEFORE MOVING TO PRODN ***
		 **********************************************/
	} else{
		// Net additional amount payable is zero and we cannot go through Instamojo or Paypal with Zero amount
		// Hence, let us complete updation of coupon, club and entry with appropriate payment_received amounts
		$sql = "UPDATE club_entry SET total_payment_received = total_payment_received + '$club_total_payment_due' WHERE yearmonth = '$yearmonth' AND club_id = '$club_id' ";
		mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

		$sql = "UPDATE coupon SET payment_received = fees_payable - discount_applicable WHERE yearmonth = '$yearmonth' AND club_id = '$club_id' ";
		mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

		// Update Entry table
		$sql  = "UPDATE entry, coupon ";
		$sql .= "SET entry.payment_received = coupon.payment_received ";
		$sql .= "WHERE entry.yearmonth = '$yearmonth' ";
		$sql .= "  AND entry.yearmonth = coupon.yearmonth ";
		$sql .= "  AND entry.profile_id = coupon.profile_id ";
		$sql .= "  AND coupon.club_id = '$club_id' ";
		mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	}

	if (isset($msg) && $msg != "")
		handle_error($msg, __FILE__, __LINE__);

	// Form will get redirected to Instamojo Form and will be redirected to user_panel.php after payment
}
else
	handle_error("Invalid Update Request", __FILE__, __LINE__);

?>
