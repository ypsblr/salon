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

/*************************************/
/** ==============================  **/
/**  this is a PRODUCTION VERSION   **/
/** ==============================  **/
/*************************************/
// Contains integration code for Instamojo
// Funtion initiate payment to create a Payment Request and redirect to Instamojo Payment Screen
function initiate_payment($purpose, $amount, $yearmonth, $profile_id, $name, $email, $phone, $redirect) {
	// Create an Instamojo Payment Request
	// echo $purpose . "|" . $amount . "|" . $entry . "|" . $name . "|" . $email . "|" . $phone;
	try {
		$api = new Instamojo\Instamojo("f846aec243d5b5dfd76c48b55a112533", "3ba6401957ee54ddd97515648bc5fae2", 'https://www.instamojo.com/api/1.1/');		// Production API Keys and Portal

		$payment_request_param = array(
			"purpose" => sprintf("%s|CTG|%d|%d", $purpose, $yearmonth, $profile_id),
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

function initiate_paypal($purpose, $amount, $yearmonth, $profile_id, $name, $email, $phone, $redirect, $cancelled) {
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

    //The item name and amount can be brought in dynamically by querying the $_POST['item_number'] variable.
    //$querystring .= "&item_name=" . urlencode(sprintf("%s-%04d", $purpose, $entry));
    $querystring .= "&item_name=" . urlencode(sprintf("%s|CTG|%d|%d", $purpose, $yearmonth, $profile_id));
	$querystring .= "&item_number=" . urlencode(sprintf("%d|%d", $yearmonth, $profile_id));
    $querystring .= "&amount=" . urlencode($amount);
	$querystring .= "&currency_code=" . urlencode("USD");
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


if(isset($_REQUEST["order_catalog"]) &&
	isset($_SESSION['USER_ID']) &&
	isset($_REQUEST['profile_id']) &&
	isset($_REQUEST['total_order_value']) &&
	isset($_REQUEST['total_due'] )) {

    // Verify Captcha Confirmation
	/*
	** Blocking Google Recaptcha from logged-in forms
	**
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
	*/

    $yearmonth = $_REQUEST['yearmonth'];
	$profile_id = $uid = $_SESSION['USER_ID'];
	$total_due = $_REQUEST["total_due"];

	$launch_mode = $_REQUEST['launch_mode'];

	// Get profile details
	$sql = "SELECT * FROM profile WHERE profile_id = '$profile_id' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_num_rows($query) == 0)
		handle_error("User ID not valid");
	$profile = mysqli_fetch_array($query);
	$name = $profile['profile_name'];
	$email = $profile['email'];
	$phone = $profile['phone'];

	$catalog_model = $_REQUEST['catalog_model'];
	$currency = $_REQUEST['currency'];
	$number_of_copies = $_REQUEST["number_of_copies"];

	// Insert a new record if number of copies was selected. Otherwise process just payment due
	if ($number_of_copies > 0) {
		// A New/Additional Order has been placed
		$catalog_price = $_REQUEST["catalog_price"];
		$catalog_postage = $_REQUEST["catalog_postage"];
		$order_value = $_REQUEST["order_value"];

		// Get the Next Order ID
		$sql = "SELECT IFNULL(MAX(order_id), 0) AS order_id FROM catalog_order WHERE yearmonth = '$yearmonth' ";
		$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		$row = mysqli_fetch_array($query);
		$catalog_order_id = $row["order_id"] + 1;

		$catalog_order_date = date("Y-m-d");

		// Update the database
		$sql  = "INSERT INTO catalog_order (yearmonth, order_id, order_date, profile_id, catalog_model, number_of_copies, currency, ";
		$sql .= "            catalog_price, catalog_postage, order_value) ";
		$sql .= "VALUES ('$yearmonth', '$catalog_order_id', '$catalog_order_date', '$profile_id', '$catalog_model', '$number_of_copies', '$currency', ";
		$sql .= "        '$catalog_price', '$catalog_postage', '$order_value') ";
		mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	}

	$return_url = "catalog_order.php?msg=OK&mode=" . $launch_mode;
	$cancel_url = "catalog_order.php?msg=CANCELLED&mode=" . $launch_mode;

	if( $total_due > 0 ){

		/**********************************************
		 *** THIS IS THE PRODUCTION VERSION         ***
		 **********************************************/
		if($_REQUEST['gateway'] == 'Instamojo'){
			$msg = initiate_payment('YPS CATALOG ORDER', $total_due, $yearmonth, $profile_id, $name, $email, $phone, "catalog_order.php?mode=" . $launch_mode);
		}
		if($_REQUEST['gateway'] == 'PayPal'){
			//$return_url = "op/payment.php?msg=OK";
			$msg = initiate_paypal('YPS CATALOG_ORDER', $total_due, $yearmonth, $profile_id, $name, $email, $phone, $return_url, $cancel_url);
		}
		/**********************************************
		 *** THIS IS THE PRODUCTION VERSION         ***
		 **********************************************/
	} else{
		$_SESSION['info_msg'] = "There is no amount to be paid !";
		header('Location: /index.php');
		printf("<script>location.href='/index.php'</script>");
	}

	// If a message is returned back then exit to calling page
	// echo $msg;
	if (isset($msg) && $msg != "") {
		$_SESSION['info_msg'] = "Alert: [" . $msg . "]";
		header('Location:' . $_SERVER['HTTP_REFERER']);
		printf("<script>location.href='".$_SERVER['HTTP_REFERER']."'</script>");
	}
}
else {
	handle_error("Invalid Parameters", __FILE__, __LINE__);
}
?>
