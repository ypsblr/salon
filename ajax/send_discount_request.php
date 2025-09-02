<?php
session_save_path(__DIR__ . "/../inc/session");
session_start();
include_once("../inc/connect.php");
include_once("ajax_lib.php");

// CryptoJs
use Nullix\CryptoJsAes\CryptoJsAes;
// require("../inc/CryptoJsAes.php");

// A function to return an error message and stop
//function return_error($errmsg, $email) {
//	$resArray = array();
//	$resArray['success'] = FALSE;
//	$resArray['msg'] = $_SESSION['err_msg'] = $errmsg;
//	file_put_contents("spam_contact.txt", date("Y-m-d H:i") . ": Email ($email)  ErrMsg:('$errmsg')" . chr(13) . chr(10), FILE_APPEND);
//	echo json_encode($resArray);
//	die;
//}

$resArray = array();

//if(isset($_REQUEST['request_discounts']) && isset($_REQUEST['verified']) && isset($_SESSION['USER_ID']) && $_SESSION['USER_ID'] != "") {
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

	$url = http_method() . $_SERVER['HTTP_HOST'];
	$contest_name = $param['contest_name'];
	$profile_name = $param['profile_name'];
	$phone = $param['phone'];
	$email = $param['email'];
	$club_name = $param['club_name'];
	$entrant_category_name = $param['entrant_category_name'];
	$minimum_group_size = $param['minimum_group_size'];
	$payment_mode = $param['payment_mode'] == "GROUP_PAYMENT" ? "By me for the entire group" : "By members individually";

	$subject= "Request for Discount for " . $club_name;
	$replacement = array($url, $contest_name, $profile_name, $email, $phone, $club_name, $entrant_category_name, $minimum_group_size, $payment_mode );
	$token = array('{{URL}}', '{{SALON}}', '{{NAME}}', '{{EMAIL}}', '{{PHONE}}', '{{CLUB_NAME}}', '{{ENTRANT_CATEGORY}}', '{{MINIMUM_GROUP_SIZE}}', '{{PAYMENT_MODE}}');

	if (send_salon_email($email, $subject, 'send_discount_request', $token, $replacement)) {
		$resArray['success'] = TRUE;
		$resArray['msg'] = $_SESSION['success_msg'] = "Your query has been forwarded to the YPS Salon/Contest Committee !";
		echo json_encode($resArray);
		die;
	}
	else
		handle_error("Email send failed ! Check correctness of details provided !", __FILE__, __LINE__);
}
else
	return_error("Message not sent due to authentication failure !", __FILE__, __LINE__);
?>
