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
include_once("payment.php");
include_once("paypal.php");

// function debug_to_console($data) {
//     $output = $data;
//     if (is_array($output))
//         $output = implode(',', $output);

//     echo "<script>console.log('Debug Objects: " . $output . "' );</script>";
// }

// CryptoJs
use Nullix\CryptoJsAes\CryptoJsAes;
// require("../inc/CryptoJsAes.php");

if ( (! empty($_SESSION['SALONBOND'])) && (! empty($_REQUEST['ypsd'])) ) {

	// Decrypt the inputs
	$param = CryptoJsAes::decrypt($_REQUEST['ypsd'], $_SESSION['SALONBOND']);
	debug_dump("PARAM", $param, __FILE__, __LINE__);

	if(isset($_SESSION['USER_ID']) &&
	   isset($param['profile_id']) &&
	   isset($param['fees_payable']) &&
	   isset($param['balance_payment'] )) {

        debug_to_console(1);
        
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
		$profile_id = $uid = $_SESSION['USER_ID'];
		$currency = $param['currency'];
		$name = $param['profile_name'];
		$email = $param['email'];
		$phone = $param['phone'];
		$fee_code = $param['fee_code'];
		$fee_group = $param['fee_group'];
		$discount_group = $param['discount_group'];
		$group_code = $param['group_code'];
		$participation_code_list = array();
		if (isset($param['participation_code_all']))
			$participation_code_list[] = $param['participation_code_all'];
		if (isset($param['participation_code_digital']))
			$participation_code_list[] = $param['participation_code_digital'];
		if (isset($param['participation_code_print']))
			$participation_code_list[] = $param['participation_code_print'];
		$participation_code = implode(",", $participation_code_list);
		$fees_payable = $param['fees_payable'];
		$discount_applicable = $param['discount_applicable'];
		$payment_received = $param['payment_received'];
		$balance_payment = $param['balance_payment'];
		$digital_sections = $param['digital_sections'];
		$print_sections = $param['print_sections'];

		// Update the database
		$sql  = "UPDATE entry ";
		$sql .= "SET participation_code = '$participation_code', ";
		$sql .= "    fees_payable = '$fees_payable', ";
		$sql .= "    discount_applicable = '$discount_applicable', ";
		$sql .= "    fee_code = '$fee_code', ";
		$sql .= "    group_code = '$group_code', ";
		$sql .= "    digital_sections = '$digital_sections', ";
		$sql .= "    print_sections = '$print_sections' ";
		$sql .= "WHERE yearmonth = '$yearmonth' ";
		$sql .= "  AND profile_id = '$profile_id' ";
		mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

		if (isset($param['has_coupon'])) {
        debug_to_console(2);
			$sql  = "UPDATE coupon ";
			$sql .= "SET profile_id = '$profile_id', ";
			$sql .= "    fees_payable = '$fees_payable', ";
			$sql .= "    discount_applicable = '$discount_applicable', ";
			$sql .= "    participation_code = '$participation_code' ";
			$sql .= "WHERE yearmonth = '$yearmonth' ";
			$sql .= "  AND email = '$email' ";
			mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		}

		$return_url = "user_payment.php?msg=OK";
		$cancel_url = "user_payment.php?msg=CANCELLED";

		if( $balance_payment > 0 ){
        debug_to_console(3);
        debug_to_console($balance_payment);
        debug_to_console($param['gateway']);
			/**********************************************
			 *** THIS IS THE PRODUCTION VERSION         ***
			 **********************************************/
			if($param['gateway'] == 'Instamojo'){
				$msg = initiate_payment('YPS SALON FEE', $balance_payment, $yearmonth, $profile_id, $name, $email, $phone, "user_panel.php");
			}
			if($param['gateway'] == 'PayPal'){
				//$return_url = "op/payment.php?msg=OK";
				$msg = initiate_paypal('YPS SALON FEE', $balance_payment, $currency, $yearmonth, $profile_id, $name, $email, $phone, $return_url, $cancel_url);
			}
			debug_to_console($msg);
			/**********************************************
			 *** THIS IS THE PRODUCTION VERSION         ***
			 **********************************************/
		} else{
		            debug_to_console(4);

			$_SESSION['info_msg'] = "There is no amount to be paid !";
			header('Location: /user_panel.php');
			printf("<script>location.href='/user_panel.php'</script>");
		}

		// If a message is returned back then exit to calling page
		// echo $msg;
		if (isset($msg) && $msg != "") {
		            debug_to_console(5);

			$_SESSION['info_msg'] = "Alert: [" . $msg . "]";
			header('Location:' . $_SERVER['HTTP_REFERER']);
			printf("<script>location.href='".$_SERVER['HTTP_REFERER']."'</script>");
		}
	}
	else
		handle_error("Invalid Parameters", __FILE__, __LINE__);
}
else {
	handle_error("Invalid Data", __FILE__, __LINE__);
}
?>
