<?php
header('Content-Type: text/csv');
// header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=salon_receipts.html');
// session_start();
include("../inc/session.php");

include("../inc/connect.php");
include("../inc/lib.php");

function th($term, $rows=0, $cols=0) {
	return "<th" . ($rows > 1 ? " rowspan='" . $rows . "'" : "") . ($cols > 1 ? " colspan='" . $cols . "'" : "") . ">" . $term . "</th>" . chr(13) . chr(10);
}

function td($term, $rows=0, $cols=0) {
	return "<td" . ($rows > 1 ? " rowspan='" . $rows . "'" : "") . ($cols > 1 ? " colspan='" . $cols . "'" : "") . ">" . $term . "</td>". chr(13) . chr(10);
}

function excel_escape_double_quote($text) {
	$out = "";
	for ($i = 0; $i < strlen($text); ++ $i) {
		$c = substr($text, $i, 1);
		if ($c == '"')
			$out .= '""';
		else
			$out .= $c;
	}
	return $out;
}

function array_double_quote($terms) {
	$target = [];
	foreach($terms as $term) {
		$target[] = '"' . excel_escape_double_quote($term) . '"';
	}
	return $target;
}

function excel_concat($terms) {
	return (sizeof($terms) > 1 ? "=" : "") . implode("&CHAR(10)&", array_double_quote($terms));
}


if ( (! empty($_SESSION['admin_id'])) && (! empty($_SESSION['admin_yearmonth'])) ) {

    // Set contest
    $yearmonth = $_SESSION["admin_yearmonth"];
	$sql = "SELECT * FROM contest WHERE yearmonth = '$yearmonth'";
	$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$contest = mysqli_fetch_array($query);
	$contest_archived = ($contest['archived'] == '1');

	// Open output stream for data download
	$output = fopen('php://output', 'w');

	// First Load all payments into array
	$sql = "SELECT * FROM payment WHERE yearmonth = '$yearmonth' ";
	$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$receipts = [];
	while ($row = mysqli_fetch_array($query)) {
		$key = $row['account'] . "|" . $row['link_id'];
		$row['applied'] = false;
		$receipts[$key][] = $row;
	}

	// Group Payment Reconciliations
	$group  = "<p><b>Group Payment Reconciliation</b></p>";
	$group .= "<table>";

	$sql  = "SELECT club.club_id, club_name, currency, SUM(fees_payable) AS club_fees_payable, SUM(discount_applicable) AS club_discount_applicable, ";
	$sql .= "       SUM(payment_received) AS club_payment_received ";
	$sql .= "  FROM club_entry, club, coupon ";
	$sql .= " WHERE club_entry.yearmonth = '$yearmonth' ";
	$sql .= "   AND payment_mode = 'GROUP_PAYMENT' ";
	$sql .= "   AND club.club_id = club_entry.club_id ";
	$sql .= "   AND coupon.yearmonth = club_entry.yearmonth ";
	$sql .= "   AND coupon.discount_code = 'CLUB' ";
	$sql .= "   AND coupon.club_id = club_entry.club_id ";
	$sql .= " GROUP BY club.club_id, club_name, currency ";
	$sql .= " HAVING club_fees_payable > 0.0 ";
	$club_query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	while ($club = mysqli_fetch_array($club_query)) {
		// Club Header
		$club_id = $club['club_id'];
		$club_net_due = $club['club_fees_payable'] - $club['club_discount_applicable'];
		$coupon_payment_mismatch = ($club_net_due != $club['club_payment_received']);
		$group .= "<tr>";
		$group .= td($club['club_name'], 3, 3) . td("Fees") . td($club['club_fees_payable']);
		$group .= "</tr>";
		$group .= "<tr>";
		$group .= td("Discount") . td($club['club_discount_applicable']);
		$group .= "</tr>";
		$group .= "<tr>";
		$group .= td("Net Due") . td($club_net_due);
		$group .= "</tr>";
		// Payments List
		// Header
		$group .= "<tr>";
		$group .= th("Date & Time") . th("Gateway") . th("Reference") . th("Currency") . th("Amount");
		$group .= "</tr>";

		// Rows
		// $sql = "SELECT * FROM payment WHERE yearmonth = '$yearmonth' AND account = 'GRP' AND link_id = '$club_id' ";
		// $payment_query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		$total_payment_received = 0.0;
		$currency_mismatch = false;
		$key = "GRP|" . $club_id;
		if (isset($receipts[$key])) {
			$i = 0;
			foreach ($receipts[$key] as $payment) {
				$receipts[$key][$i]['applied'] = true;
				$total_payment_received += $payment['amount'];
				$currency_mismatch = ($club['currency'] != $payment['currency']);
				$dt = $payment['datetime'];
				$datetime = substr($dt, 6, 2) . "-" . substr($dt, 4, 2) . "-" . substr($dt, 0, 4) . " " . substr($dt, 8, 2) . ":" . substr($dt, 10);
				$group .= "<tr>";
				$group .= td($datetime) . td($payment['gateway']) . td($payment['payment_ref']) . td($payment['currency']) . td($payment['amount']);
				$group .= "</tr>";
				++ $i;
			}
		}
		else {
			$group .= "<tr>";
			$group .= td("*** No payments received ***", 0, 5);
			$group .= "</tr>";
		}
		// Total and Reconciliation
		$total_mismatch = $club_net_due != $total_payment_received;
		$payment_msg = "";
		$payment_msg .= ($coupon_payment_mismatch ? "[Mismatch in coupon amounts] " : "");
		$payment_msg .= ($currency_mismatch ? "[Currency Mismatch] " : "");
		$payment_msg .= ($total_mismatch ? "[Amount Due does not match Amount Paid] " : "");
		if ($payment_msg == "")
			$payment_msg = "Payments match Coupon Totals";
		$group .= "<tr>";
		$group .= td($payment_msg, 1, 4) . td($total_payment_received);
		$group .= "</tr>";
	}
	$group .= "</table>";
	fputs($output, $group);

	// Salon Fee Receipts
	// Write Header for Entry Results
	$html  = "<br><br><br><b>Salon Fee Receipts</b>";
	$html .= "<br>";
	$html .= "<table>";
	$html .= "<tr>";
	$html .= th("#") . th("Name") . th("Participation") . th("Fees Payable") . th("Discount Applicable") . th("Payment Due");
	$html .= th("Date Time") . th("Currency") . th("Payment Received") . th("Paid By") . th("Gateway") . th("Reference") . th("Balance Due");
	$html .= "</tr>";

	// Fetch Data
	$sql  = "SELECT profile.profile_id, profile_name, yps_login_id, entry.participation_code, entry.fees_payable, entry.discount_applicable, entry.payment_received, ";
	$sql .= "       entry.currency, IFNULL(coupon.club_id, 0) AS club_id, IFNULL(payment_mode, '') AS payment_mode, IFNULL(coupon_text, '') AS coupon_text, ";
	$sql .= "       IFNULL(coupon.payment_received, 0) AS coupon_payment_received, IFNULL(coupon.modified_date, '') AS coupon_payment_datetime ";
	if ($contest_archived)
		$sql .= "  FROM profile, ar_entry entry LEFT JOIN (coupon INNER JOIN club_entry) ";
	else
		$sql .= "  FROM profile, entry LEFT JOIN (coupon INNER JOIN club_entry) ";
	$sql .= "    ON coupon.yearmonth = entry.yearmonth AND coupon.profile_id = entry.profile_id ";
	$sql .= "   AND club_entry.yearmonth = coupon.yearmonth AND club_entry.club_id = coupon.club_id ";
	$sql .= " WHERE entry.yearmonth = '$yearmonth' ";
	$sql .= "   AND entry.payment_received > 0.0 ";
	$sql .= "   AND profile.profile_id = entry.profile_id ";
	$entry_query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$idx = 0;
	while ($entry = mysqli_fetch_array($entry_query)) {
		++ $idx;
		$profile_id = $entry['profile_id'];
		$profile_name = $entry['profile_name'];
		$yps_member_id = $entry['yps_login_id'];
		$participation = $entry['participation_code'];
		$fees_payable = $entry['fees_payable'];
		$discount_applicable = $entry['discount_applicable'];
		$entry_payment_received = $entry['payment_received'];
		$currency = $entry['currency'];
		$payment_due = $entry['fees_payable'] - $entry['discount_applicable'];
		$payment_mismatch = ($entry_payment_received != $payment_due);
		$currency_mismatch = false;

		$total_payment_received = 0.0;
		$max_datetime = "";
		$gateway = [];
		// $datetime = [];
		$payment_ref = [];
		// $amount = [];
		$paid_by = [];
		$i = 0;

		// If there is an individual payment, use the individual payment
		$key = "IND|" . $profile_id;
		if (isset($receipts[$key])) {
			foreach ($receipts[$key] as $payment) {
				$receipts[$key][$i]['applied'] = true;
				$dt = $payment['datetime'];
				$payment_datetime = substr($dt, 6, 2) . "-" . substr($dt, 4, 2) . "-" . substr($dt, 0, 4);
				$gateway[$i] = $payment['gateway'];
				// $datetime[$i] = $payment_datetime;
				$payment_ref[$i] = $payment_datetime . " " . $payment['payment_ref'] . ": " . $payment['amount'];
				// $amount[$i] = $payment['amount'];
				$paid_by[$i] = "SELF";
				$max_datetime = ($payment['datetime'] > $max_datetime) ? $payment['datetime'] : $max_datetime;
				$total_payment_received += $payment['amount'];
				++ $i;
			}
		}
		// Check for any group payments to apply
		// This is an extreme condition where individual payment and group payment both are made. App does not support this.
		if ($entry['club_id'] != 0 && $entry['payment_mode'] != "" && $entry['payment_mode'] != "SELF_PAYMENT") {
			$datetime = date("Ymd", strtotime($entry['coupon_payment_datetime'])) . "0000";
			$gateway[$i] = "CPN";
			// $datetime[$i] = $entry['coupon_payment_datetime'];
			$payment_ref[$i] = date("d-m-Y", strtotime($entry['coupon_payment_datetime'])) . " " . $entry['coupon_text'] . ": " . $entry['coupon_payment_received'];
			// $amount[$i] = $entry['coupon_payment_received'];
			$paid_by[$i] = "CLUB";
			$total_payment_received += $entry['coupon_payment_received'];
			$max_datetime = ($datetime > $max_datetime) ? $datetime : $max_datetime;
			++ $i;
		}

		$balance_due = $payment_due - $total_payment_received;
		$paid_by_text = excel_concat($paid_by);
		$gateway_text = excel_concat($gateway);
		$reference_text = excel_concat($payment_ref);
		$dt = $max_datetime;
		$payment_datetime = substr($dt, 6, 2) . "-" . substr($dt, 4, 2) . "-" . substr($dt, 0, 4) . " " . substr($dt, 8, 2) . ":" . substr($dt, 10);

		$html .= "<tr>";
		$html .= td($idx) . td($profile_name) . td($participation) . td($fees_payable) . td($discount_applicable) . td($payment_due);
		$html .= td($payment_datetime) . td($currency) . td($total_payment_received) . td($paid_by_text) . td($gateway_text) . td($reference_text);
		$html .= td($balance_due == 0 ? "" : $balance_due);
		$html .= "</tr>";
	}
	$html .= "</table>";

	fputs($output, $html);

	// Check for Unapplied payments
	$html = "<p><b>Salon Fee Not Applied</b></p>";
	foreach ($receipts as $key => $payments) {
		if (substr($key, 0, 3) == "IND" || substr($key, 0, 3) == "GRP") {
			foreach ($payments as $payment) {
				if (! $payment['applied'])
					$html .= "<p>" . $payment['account'] . " " . $payment['link_id'] . " " . $payment['payment_ref'] . " " . $payment['amount'] . "</p>";
			}
		}
	}
	$html .= "<p>--- END OF LIST ---</p>";
	fputs($output, $html);


	// Sponsor Payment
	$html  = "<br><br><br><br>";
	$html .= "<p><b>Sponsorship Payment</b></p>";
	$html .= "<table>";
	$html .= "<tr>";
	$html .= th("Sponsor") . th("Award") . th("Award Amount") . th("Number of Awards") . th("Sponsorship Amount") . th("Sponsorship Amount Due");
	$html .= th("Last Payment") . th("Gateway") . th("Reference") . th("Payment Received") . th("Balance Due");
	$html .= "</tr>";

	$sql  = "SELECT sponsor.sponsor_id, sponsor_name, COUNT(link_id) AS num_rows, SUM(number_of_units) AS num_awards, ";
	$sql .= "       SUM(total_sponsorship_amount) AS total_sponsorship_amount, SUM(payment_received) AS payment_received ";
	$sql .= "  FROM sponsorship, sponsor ";
	$sql .= " WHERE sponsorship.yearmonth = '$yearmonth' ";
	$sql .= "   AND sponsorship.sponsorship_type = 'AWARD' ";
	$sql .= "   AND sponsor.sponsor_id = sponsorship.sponsor_id ";
	$sql .= " GROUP BY sponsor.sponsor_id, sponsor_name ";
	$sponsorship_query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	while ($sponsorship = mysqli_fetch_array($sponsorship_query)) {
		$sponsor_id = $sponsorship['sponsor_id'];
		$sponsor_name = $sponsorship['sponsor_name'];
		$sponsorship_amount_due = $sponsorship['total_sponsorship_amount'];

		// Get Payment Details
		$sql  = "SELECT MAX(datetime) AS datetime, SUM(amount) AS total_amount, MAX(gateway) AS gateway, GROUP_CONCAT(payment_ref) AS payment_ref ";
		$sql .= "  FROM payment ";
		$sql .= " WHERE yearmonth = '$yearmonth' ";
		$sql .= "   AND account = 'SPN' ";
		$sql .= "   AND link_id = '$sponsor_id' ";
		$payment_query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		if (mysqli_num_rows($payment_query) > 0) {
			$payment = mysqli_fetch_array($payment_query);
			$dt = $payment['datetime'];
			$payment_datetime = substr($dt, 6, 2) . "-" . substr($dt, 4, 2) . "-" . substr($dt, 0, 4) . " " . substr($dt, 8, 2) . ":" . substr($dt, 10);
			$total_payment_received = $payment['total_amount'];
			$gateway = $payment['gateway'];
			$reference = $payment['payment_ref'];
			$balance_due = $sponsorship_amount_due - $total_payment_received;
		}
		else {
			$payment_datetime = "";
			$total_payment_received = "0.00";
			$gateway = "";
			$reference = "";
			$balance_due = $sponsorship_amount_due;
		}

		// Get Award Details
		$sql  = "SELECT award.award_id, section, award_name, cash_award, number_of_units, total_sponsorship_amount ";
		$sql .= "  FROM sponsorship, award ";
		$sql .= " WHERE sponsorship.yearmonth = '$yearmonth' ";
		$sql .= "   AND sponsorship.sponsorship_type = 'AWARD' ";
		$sql .= "   AND sponsorship.sponsor_id = '$sponsor_id' ";
		$sql .= "   AND award.yearmonth = sponsorship.yearmonth ";
		$sql .= "   AND award.award_id = sponsorship.link_id ";
		$award_query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		$nr = mysqli_num_rows($award_query);
		$first = true;
		while ($award = mysqli_fetch_array($award_query)) {
			$award_name = $award['section'] . " - " . $award['award_name'];
			$cash_award = $award['cash_award'];
			$number_of_units = $award['number_of_units'];
			$sponsorship_amount = $award['total_sponsorship_amount'];
			$html .= "<tr>";
			if ($first) {
				$html .= td($sponsor_name, $nr) . td($award_name) . td($cash_award) . td($number_of_units) . td($sponsorship_amount);
				$html .= td($sponsorship_amount_due, $nr) . td($payment_datetime, $nr) . td($gateway, $nr) . td($reference, $nr);
				$html .= td($total_payment_received, $nr) . td($balance_due, $nr);
				$first = false;
			}
			else {
				$html .= td($award_name) . td($cash_award) . td($number_of_units) . td($sponsorship_amount);
			}
			$html .= "</tr>";
		}
	}

	$html .= "</table>";
	fputs($output, $html);

}
else
    debug_dump("SESSION", $_SESSION, __FILE__, __LINE__);
?>
