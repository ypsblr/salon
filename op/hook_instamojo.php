<?php
/*************************************/
/** ==============================  **/
/**  this is a PRODUCTION VERSION   **/
/** ==============================  **/
/*************************************/
include_once "../inc/connect.php";
include_once "../inc/lib.php";

date_default_timezone_set("Asia/Kolkata");

// Log Paypal Webhook Calls
$logfile = $_SERVER["DOCUMENT_ROOT"] . "/logs/instamojo.log";
file_put_contents($logfile, "\r" . date("r") . "\r", FILE_APPEND);
file_put_contents($logfile, print_r($_POST, true), FILE_APPEND);

$data = $_POST;
$mac_provided = $data['mac'];  // Get the MAC from the POST data
unset($data['mac']);  // Remove the MAC key from the data so that it is not used for computing hash_mac

// Sort Keys insensitive to case for use by implode function to generate mac
$ver = explode('.', phpversion());
$major = (int) $ver[0];
$minor = (int) $ver[1];
if($major >= 5 and $minor >= 4){
     ksort($data, SORT_STRING | SORT_FLAG_CASE);
}
else{
     uksort($data, 'strcasecmp');
}

// You can get the 'salt' from Instamojo's developers page(make sure to log in first): https://www.instamojo.com/developers
// Pass the 'salt' without <>
// Substitute Test Values
$mac_calculated = hash_hmac("sha1", implode("|", $data), "b1693fcb1e4746608f03730c82452f08");	// Production Salt
if($mac_provided == $mac_calculated){
    if($data['status'] == "Credit"){
		list($purpose, $account, $yearmonth, $link_id) = explode("|", $data['purpose']);
		$datetime = date("YmdHi");
		$amount = $data['amount'];
		$currency = $data['currency'];
		$request_id = $data['payment_request_id'];
		$payment_ref = $data['payment_id'];
		$payDetail = 'Instamojo, Transaction ID: '.$payment_ref;

		$sql = "INSERT INTO payment (yearmonth, account, link_id, datetime, purpose, amount, currency, gateway, request_id, payment_ref, status) ";
		$sql .= " VALUES ('$yearmonth', '$account', '$link_id', '$datetime', '$purpose', '$amount', '$currency', 'Instamojo',  '$request_id', '$payment_ref', 'PAID') ";
		mysqli_query($DBCON, $sql) or log_sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

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
				file_put_contents($logfile, "*** Payment Received $amount did not match Sponsorship amount to be paid $to_be_paid. \r", FILE_APPEND);
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
	}
}

?>
