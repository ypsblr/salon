<?php
/*************************************/
/** ==============================  **/
/**  this is a PRODUCTION VERSION   **/
/** ==============================  **/
/*************************************/
include_once "Instamojo.php";

function debug_to_console($data) {
    $output = $data;
    if (is_array($output))
        $output = implode(',', $output);

    echo "<script>console.log('Debug Objects: " . $output . "' );</script>";
}


// Funtion initiate payment to create a Payment Request and redirect to Instamojo Payment Screen
function initiate_payment($purpose, $amount, $yearmonth, $profile_id, $name, $email, $phone, $redirect) {
	// Create an Instamojo Payment Request
	try {
	    debug_to_console(11);
		$api = new Instamojo\Instamojo("f846aec243d5b5dfd76c48b55a112533", "3ba6401957ee54ddd97515648bc5fae2", 'https://www.instamojo.com/api/1.1/');		// Production API Keys and Portal
        debug_to_console(12);
		$payment_request_param = array(
			"purpose" => sprintf("%s|IND|%d|%d", $purpose, $yearmonth, $profile_id),
			"amount" => $amount,
			"send_email" => false,
			"buyer_name" => $name,
			"email" => $email,
			"phone" => "$phone",
			"allow_repeated_payments" => false,
			"redirect_url" => http_method() . $_SERVER['SERVER_NAME'] . "/" . $redirect,		// Return Back to the calling page
			"webhook" => http_method() . $_SERVER['SERVER_NAME'] . "/op/hook_instamojo.php"
			);
			debug_to_console(13);
			debug_to_console($payment_request_param);
			// echo $payment_request_param;
		$payment_request = $api->paymentRequestCreate($payment_request_param);
		debug_to_console(14);
		header('Location: ' . $payment_request['longurl']);		// Load Instamojo Payment URL
		debug_to_console(15);
		printf("<script>location.href='" . $payment_request['longurl'] . "'</script>");
		debug_to_console(16);
		return ("");
	}
	catch (Exception $e) {
		debug_dump("Instamojo", $e, __FILE__, __LINE__);
		return('Error: ' . $e->getMessage());
	}
}
?>