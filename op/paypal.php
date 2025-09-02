<?php
/*************************************/
/** ==============================  **/
/**  this is a PRODUCTION VERSION   **/
/** ==============================  **/
/*************************************/
// Contains integration code for Paypal 
// Implements function initiate_payment() to request payment

function initiate_paypal($purpose, $amount, $currency, $yearmonth, $profile_id, $name, $email, $phone, $redirect, $cancelled) {
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
    $querystring .= "&item_name=" . urlencode(sprintf("%s|IND|%d|%d", $purpose, $yearmonth, $profile_id));
	$querystring .= "&item_number=" . urlencode(sprintf("%d|%d", $yearmonth, $profile_id));
    $querystring .= "&amount=" . urlencode($amount);
	//$querystring .= "&currency_code=" . urlencode("USD");
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
	header('location:https://www.paypal.com/cgi-bin/webscr' . $querystring);									// Live Account
	echo "<script>location.href='https://www.paypal.com/cgi-bin/webscr" . $querystring . "'</script>";			// Live
    
}

?>