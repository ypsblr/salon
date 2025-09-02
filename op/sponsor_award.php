<?php
session_save_path(__DIR__ . "/../inc/session");
session_start();
include_once("../inc/connect.php");
include_once("../inc/lib.php");

// CryptoJs
use Nullix\CryptoJsAes\CryptoJsAes;
// require("../inc/CryptoJsAes.php");

include_once "Instamojo.php";

function debug_to_console($data) {
    $output = $data;
    if (is_array($output))
        $output = implode(',', $output);

    echo "<script>console.log('Debug Objects: " . $output . "' );</script>";
}

// Function initiate payment to create a Payment Request and redirect to Instamojo Payment Screen
function initiate_payment($purpose, $amount, $yearmonth, $sponsor, $name, $email, $phone, $redirect) {
	// Create an Instamojo Payment Request
	// echo $purpose . "|" . $amount . "|" . $entry . "|" . $name . "|" . $email . "|" . $phone;
	try {
		$api = new Instamojo\Instamojo("f846aec243d5b5dfd76c48b55a112533", "3ba6401957ee54ddd97515648bc5fae2", 'https://www.instamojo.com/api/1.1/');		// Production API Keys and Portal

		$payment_request_param = array(
			"purpose" => sprintf("%s|SPN|%d|%d", $purpose, $yearmonth, $sponsor),
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
		debug_dump("payment_request", $payment_request, __FILE__, __LINE__);
		header('Location: ' . $payment_request['longurl']);		// Load Instamojo Payment URL
		printf("<script>location.href='" . $payment_request['longurl'] . "'</script>");
		//return ("");
		die();
	}
	catch (Exception $e) {
		handle_error($e->getMessage());
	}
}

// Callback function to fill the SPONSORSHIP_LIST in the email
function sponsorship_list_call_back($sponsorship_list_template) {
	global $yearmonth, $sponsor_id;
	global $DBCON;

	$sql  = "SELECT * FROM sponsorship, award ";
	$sql .= " WHERE sponsorship.yearmonth = '$yearmonth' ";
	$sql .= "   AND sponsorship.sponsor_id = '$sponsor_id' ";
	$sql .= "   AND sponsorship.sponsorship_type = 'AWARD' ";
	$sql .= "   AND award.yearmonth = sponsorship.yearmonth ";
	$sql .= "   AND award.award_id = sponsorship.link_id ";

	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$expanded_text = "";
	while ($row = mysqli_fetch_array($query)) {
		$srch_text = array('{{SECTION}}', '{{AWARD_NAME}}', '{{AWARD_SUFFIX}}', '{{NUM_AWARDS}}', '{{SPONSORSHIP_AMOUNT}}');
		$replace_text = array($row['section'], $row['award_name'], $row['award_name_suffix'], $row['number_of_units'], sprintf("%9.2f", (float) $row['total_sponsorship_amount']));
		$expanded_text .= str_replace($srch_text, $replace_text, $sponsorship_list_template);
	}
	return $expanded_text;
}

// Main Code

//$resArray = array();

// if( (! empty($_REQUEST['yearmonth'])) && isset($_REQUEST['sponsor_id']) && isset($_REQUEST['verified']) ) {
if ( (! empty($_SESSION['SALONBOND'])) && (! empty($_REQUEST['ypsd'])) ) {

	// Decrypt the inputs
	$param = CryptoJsAes::decrypt($_REQUEST['ypsd'], $_SESSION['SALONBOND']);
	debug_dump("PARAM", $param, __FILE__, __LINE__);

    debug_to_console(1);
    
	// Verify Google reCaptcha code for spam protection
	if (strpos($_SERVER['HTTP_HOST'], "localhost") == false) {
		if ( ! (isset($param['g-recaptcha-response']) && $param['g-recaptcha-response'] != "" && verify_recaptcha($param['g-recaptcha-response']) == "") ) {
			handle_error("Click on I am not Robot before submitting !", __FILE__, __LINE__);
		}
	}

    debug_to_console(2);

	// debug_dump("REQUEST", $param, __FILE__, __LINE__);
	// Sponsor Data
	$yearmonth = $param['yearmonth'];
	$sql = "SELECT * FROM contest WHERE yearmonth = '$yearmonth' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$contest = mysqli_fetch_array($query);

	$sponsor_id = $param['sponsor_id'];
	$sql = "SELECT * FROM sponsor WHERE sponsor_id = '$sponsor_id' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$sponsor = mysqli_fetch_array($query);

    debug_to_console(3);

	// Lists
	$award_id = isset($param['award_id']) ? $param['award_id'] : array();
	$number_of_units = isset($param['number_of_units']) ? $param['number_of_units'] : array();
	$award_name_suffix = isset($param['award_name_suffix']) ? $param['award_name_suffix'] : array();
	$total_sponsorship_amount = isset($param['total_sponsorship_amount']) ? $param['total_sponsorship_amount'] : array();
	$payment_received = isset($param['payment_received']) ? $param['payment_received'] : array();

	// Update Sponsorship
	$sql = "START TRANSACTION";
	mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Step 1 - Delete all records with 0 payment_received
	$sql  = "DELETE FROM sponsorship ";
	$sql .= " WHERE yearmonth = '$yearmonth' ";
	$sql .= "   AND sponsorship_type = 'AWARD' ";
	$sql .= "   AND sponsor_id = '$sponsor_id' ";
	$sql .= "   AND payment_received = 0.0 ";
	mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Now Insert the new records (unpaid)
	$errors = false;
	$failed = array();
	for ($i = 0; $i < count($award_id); ++ $i) {
		if ($payment_received[$i] == 0) {
			// Get Award information
			$sql = "SELECT * FROM award WHERE yearmonth = '$yearmonth' AND award_id = '" . $award_id[$i] . "' AND sponsorship_last_date >= '" . date("Y-m-d") . "' " ;
			$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
			$row = mysqli_fetch_array($query);
			$sponsored_awards = $row['sponsored_awards'];
			$sponsorship_per_award = $row['sponsorship_per_award'];

			// Get Number of Awards already sponsored_awards
			$sql  = "SELECT IFNULL(SUM(number_of_units), 0) AS num_awards_sponsored, IFNULL(MAX(sponsorship_no), 0) AS last_seq FROM sponsorship ";
			$sql .= " WHERE yearmonth = '$yearmonth' ";
			$sql .= "   AND sponsorship_type = 'AWARD' ";
			$sql .= "   AND link_id = '" . $award_id[$i] . "' ";
			$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
			$row = mysqli_fetch_array($query);
			$num_awards_sponsored = $row['num_awards_sponsored'];
			$next_seq = $row['last_seq'] + 1;

			// Validate data & insert
			if ($num_awards_sponsored >= $sponsored_awards)
				$errors = true;
			else {
				// No problems Insert the record
				// Payment Received will be updated by Paypal/Instamojo hook
				$total_sponsorship_amount = $sponsorship_per_award * $number_of_units[$i];
				$sql  = "INSERT INTO sponsorship (yearmonth, sponsorship_type, link_id, sponsorship_no, number_of_units, ";
				$sql .= "            total_sponsorship_amount, award_name_suffix, sponsor_id) ";
				$sql .= "VALUES ('$yearmonth', 'AWARD', '" . $award_id[$i] . "', '$next_seq', '" . $number_of_units[$i] . "', ";
				$sql .= "        '$total_sponsorship_amount', '" . mysqli_real_escape_string($DBCON, $award_name_suffix[$i]) . "', '$sponsor_id' )";
				mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
			}
		}
	}
	// Validate Totals against what was shown on the form
	// Totals from the form
	$sum_sponsorship_payable = $param['sum_sponsorship_payable'];
	$sum_sponsorship_paid = $param['sum_sponsorship_paid'];
	$sum_sponsorship_due = $param['sum_sponsorship_due'];

	// Totals from the saved Data past and present
	$sql  = "SELECT IFNULL(SUM(total_sponsorship_amount), 0) AS amount_payable, IFNULL(SUM(payment_received), 0) AS payment_received ";
	$sql .= "FROM sponsorship WHERE yearmonth = '$yearmonth' AND sponsorship_type = 'AWARD' AND sponsor_id = '$sponsor_id' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$row = mysqli_fetch_array($query);

	$calc_amount_payable = $row['amount_payable'];
	$calc_amount_paid = $row['payment_received'];
	$calc_amount_due = $calc_amount_payable - $calc_amount_paid;

	if ($sum_sponsorship_payable != $calc_amount_payable || $sum_sponsorship_paid != $calc_amount_paid)
		$errors = true;

	if ($errors)
		handle_error("Not all awards selected could be processed. Please check the form again before making payment.", __FILE__, __LINE__);

	// Commit changes
	$sql = "COMMIT";
	mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Proceed to Payment
	$return_url = "sponsor_award.php?msg=OK";
	$cancel_url = "sponsor_award.php?msg=CANCELLED";

	// Send email
	$url = http_method() . $_SERVER['HTTP_HOST'];
	$text = array('{{NAME}}', '{{CONTEST}}', '{{TOTAL_SPONSORSHIP}}', '{{URL}}');
	$replace_text = array($sponsor['sponsor_name'], $contest['contest_name'], sprintf("%9.2f", (float) $sum_sponsorship_payable), $url);
	$subject = 'Thank you '. $sponsor['sponsor_name'] . ' for the sponsorship!';

	send_salon_email($sponsor['sponsor_email'], $subject, 'sponsorship', $text, $replace_text, array("SPONSORSHIP_LIST" => "sponsorship_list_call_back"));


	if( $calc_amount_due > 0 ){

		/**********************************************
		 *** THIS IS THE PRODUCTION VERSION         ***
		 **********************************************/
		$msg = initiate_payment('SPONSORSHIP', $calc_amount_due, $yearmonth, $sponsor_id, $sponsor['sponsor_name'], $sponsor['sponsor_email'], $sponsor['sponsor_phone'], "index.php");
		/**********************************************
		 *** THIS IS THE PRODUCTION VERSION         ***
		 **********************************************/
	} else{
		$_SESSION['info_msg'] = "There are no dues to pay !";
		header("Location: /sponsor.php");
		printf("<script>location.href='/sponsor.php'</script>");
		// $resArray['success'] = TRUE;
		// $resArray['msg'] = $_SESSION['info_msg'] = "There are no dues to pay !";
		// echo json_encode($resArray);
		die();
	}
	// $_SESSION['success_msg'] = "Thank you for sponsoring. All Records have been Saved. Payment Initiated !";
	// header("Location: /sponsor.php");
	// printf("<script>location.href='/sponsor.php'</script>");
	// $resArray['success'] = TRUE;
	// $resArray['msg'] = $_SESSION['success_msg'] = "Thank you for sponsoring. All Records have been Saved. Payment Initiated !";
	// echo json_encode($resArray);
}
else
	handle_error("Invalid Request", __FILE__, __LINE__);
