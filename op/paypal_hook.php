<?php
/*************************************/
/** ==============================  **/
/**  this is a PRODUCTION VERSION   **/
/** ==============================  **/
/*************************************/
// Handle Notification & Record Payment
require('PaypalIPN.php');
include_once("../inc/connect.php");
include_once("../inc/lib.php");

date_default_timezone_set("Asia/Kolkata");

// Log Paypal Webhook Calls
$logfile = $_SERVER["DOCUMENT_ROOT"] . "/logs/paypal.log";
file_put_contents($logfile, "\r" . date("r") . "\r", FILE_APPEND);
file_put_contents($logfile, print_r($_POST, true), FILE_APPEND);

if (isset($_POST['item_name']) && isset($_POST['item_number']) && isset($_POST['payment_status']) && isset($_POST['mc_gross']) && isset($_POST['mc_currency']) &&
    		isset($_POST['txn_id']) && isset($_POST['receiver_email']) && isset($_POST['payer_email'])) {

	// assign posted variables to local variables
	$data['purpose']          = $_POST['item_name'];
	$data['item_number']        = $_POST['item_number'];
	$data['payment_status']     = $_POST['payment_status'];
	$data['payment_amount']     = $_POST['mc_gross'];
	$data['payment_currency']   = $_POST['mc_currency'];
	$data['txn_id']             = $_POST['txn_id'];
	$data['receiver_email']     = $_POST['receiver_email'];
	$data['payer_email']        = $_POST['payer_email'];
	// $data['custom']             = $_POST['custom'];

	//use PaypalIPN;
	$ipn = new PaypalIPN();

	$verified = $ipn->verifyIPN();

	if ($verified) {
		// Record Payment
		// $entry_id = substr($data['item_name'], -4);
		// $purpose = substr($data['item_name'], 0, -5);
		list($purpose, $account, $yearmonth, $link_id) = explode("|", $data['purpose']);
		$amount = $data['payment_amount'];
		$datetime = date("YmdHi");
		$currency = $data['payment_currency'];
		$request_id = $data['item_number'];
		$payment_ref = $data['txn_id'];
		// $edate = date('D, M jS, Y H:i:s A');
		$payDetail = 'Paypal, Transaction ID: '.$payment_ref;

		$sql = "INSERT INTO payment (yearmonth, account, link_id, datetime, purpose, amount, currency, gateway, request_id, payment_ref, status) ";
		$sql .= " VALUES ('$yearmonth', '$account', '$link_id', '$datetime', '$purpose', '$amount', '$currency', 'Paypal',  '$request_id', '$payment_ref', 'PAID') ";
		mysqli_query($DBCON, $sql) or log_sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

		// $sql  = "UPDATE entry SET payment_received = payment_received + '$amount' WHERE yearmonth = '$yearmonth'AND profile_id = '$link_id' ";
		// mysqli_query($DBCON, $sql) or log_sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

		if ($account == "IND") {
			$sql = "UPDATE entry SET payment_received = payment_received + '$amount' WHERE yearmonth = '$yearmonth' AND profile_id = '$link_id' ";
			mysqli_query($DBCON, $sql) or log_sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
			$sql = "UPDATE coupon SET payment_received = payment_received + '$amount' WHERE yearmonth = '$yearmonth' AND profile_id = '$link_id' ";
			mysqli_query($DBCON, $sql) or log_sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		}
		elseif ($account == "GRP") {
			// Update Club
			$sql = "UPDATE club_entry SET total_payment_received = total_payment_received + '$amount' WHERE yearmonth = '$yearmonth' AND club_id = '$link_id' ";
			mysqli_query($DBCON, $sql) or log_sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

			// Update Coupon
			$sql = "UPDATE coupon SET payment_received = fees_payable - discount_applicable WHERE yearmonth = '$yearmonth' AND club_id = '$link_id' ";
			mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

			// Update paid_participants in club_entry
			$sql  = "UPDATE club_entry ";
			$sql .= "   SET paid_participants = ( ";
			$sql .= "       SELECT COUNT(*) FROM coupon ";
			$sql .= "        WHERE coupon.yearmonth = club_entry.yearmonth ";
			$sql .= "          AND coupon.club_id = club_entry.club_id ";
			$sql .= "          AND coupon.payment_received > 0 ";
			$sql .= "       ) ";
			$sql .= " WHERE yearmonth = '$yearmonth' ";
			$sql .= "   AND club_id = '$link_id' ";
			mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

			// Update Entry
			$sql  = "UPDATE entry, coupon ";
			$sql .= "SET entry.payment_received = coupon.payment_received ";
			$sql .= "WHERE entry.yearmonth = '$yearmonth' ";
			$sql .= "  AND entry.yearmonth = coupon.yearmonth ";
			$sql .= "  AND entry.profile_id = coupon.profile_id ";
			mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		}
		elseif ($account == "SPN") {
			$sql  = "SELECT IFNULL(SUM(total_sponsorship_amount), 0) AS to_be_paid FROM sponsorship ";
			$sql .= " WHERE yearmonth = '$yearmonth' ";
			$sql .= "   AND sponsor_id = '$link_id' ";
			$sql .= "   AND payment_received = 0.0 ";
			$query = mysqli_query($DBCON, $sql) or log_sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
			$row = mysqli_fetch_array($query);
			$to_be_paid = $row['to_be_paid'];
			if ($to_be_paid == $amount) {
				$sql = "UPDATE sponsorship SET payment_received = total_sponsorship_amount WHERE yearmonth = '$yearmonth' AND sponsor_id = '$link_id' AND payment_received = 0.0 ";
				mysqli_query($DBCON, $sql) or log_sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
			}
			else {
				file_put_contents($logfile, "\r" . date("r") . "\r", FILE_APPEND);
				file_put_contents($logfile, "Payment Received $amount did not match Sponsorship amount to be paid $to_be_paid. \r", FILE_APPEND);
			}
		}
		elseif ($account == "CTG") {
			$sql  = "SELECT IFNULL(SUM(order_value), 0) AS to_be_paid FROM catalog_order ";
			$sql .= " WHERE yearmonth = '$yearmonth' ";
			$sql .= "   AND profile_id = '$link_id' ";
			$sql .= "   AND payment_received = 0.0 ";
			$query = mysqli_query($DBCON, $sql) or log_sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
			$row = mysqli_fetch_array($query);
			$to_be_paid = $row['to_be_paid'];
			if ($to_be_paid == $amount) {
				$sql = "UPDATE catalog_order SET payment_received = order_value WHERE yearmonth = '$yearmonth' AND profile_id = '$link_id' AND payment_received = 0.0 ";
				mysqli_query($DBCON, $sql) or log_sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
			}
			else {
				file_put_contents($logfile, "\r" . date("r") . "\r", FILE_APPEND);
				file_put_contents($logfile, "*** Payment Received $amount did not match Catalog Order amount to be paid $to_be_paid. \r", FILE_APPEND);
			}
		}

		$_SESSION['info_msg']='Payment successful. Please contact <salon@ypsbengaluru.in> for any queries.<br>
								<b>Payment RequestID: </b>'.$request_id.' &nbsp;&nbsp;<b>Payment ReferenceNo: </b>'.$payment_ref;

	}
}
else
	file_put_contents($logfile, "\rPaypal Hook called with missing data. Payment not updated.\r", FILE_APPEND);

header("HTTP/1.1 200 OK");
?>
