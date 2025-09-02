<?php
header('Content-Type: text/csv');
// header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=mailing_payment.html');
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
	return "=" . implode("&CHAR(10)&", array_double_quote($terms));
}

function award_row($award, $na, $tc, $row, $first) {

	$addr = [];
	if ($award['address_1'] != "")
		$addr[] = $award['address_1'];
	if ($award['address_2'] != "")
		$addr[] = $award['address_2'];
	if ($award['address_3'] != "")
		$addr[] = $award['address_3'];
	$addr[] = $award['city'] . "-" . $award['pin'];
	$addr[] = $award['state'];
	$addr[] = $award['country_name'];
	$address = excel_concat($addr);
	// $address = excel_concat($award['address_1'], $award['address_2'], $award['address_3'], $award['city'] . "-" . $award['pin'], $award['state'], $award['country_name']);

	$artifact = [];
	if ($award['has_medal'] != 0)
		$artifact[] = "Medal";
	if ($award['has_pin'] != 0)
		$artifact[] = "Pin";
	if ($award['has_ribbon'] != 0)
		$artifact[] = "Ribbon";
	if ($award['has_memento'] != 0)
		$artifact[] = "Memento";
	if ($award['has_gift'] != 0)
		$artifact[] = "Gift";
	if ($award['has_certificate'] != 0)
		$artifact[] = "Certificate";
	$articles = implode(", ", $artifact);

	if ($award['bank_account_number'] == "")
		$bank_account = "";
	else {
		$acct = [];
		$acct[] = $award['bank_account_name'];
		$acct[] = $award['bank_account_type'] . " A/c " . $award['bank_account_number'];
		$acct[] = $award['bank_name'];
		$acct[] = $award['bank_branch'] . " Branch";
		$acct[] = "IFSC : " . $award['bank_ifsc_code'];
		$bank_account = excel_concat($acct);
	}

	$html  = "<tr>";
	$html .= $first ? td($row, $na) : "";
	$html .= $first ? td($award['profile_name'], $na) : "";
	$html .= $first ? td($award['salutation'], $na) : "";
	$html .= $first ? td($award['first_name'], $na) : "";
	$html .= $first ? td($award['last_name'], $na) : "";
	$html .= $first ? td($award['email'], $na) : "";
	$html .= $first ? td($award['phone'], $na) : "";
	$html .= $first ? td($address, $na) : "";
	$html .= td(($award['section'] == "CONTEST" ? "" : $award['section'] . " - ") . $award['award_name']);
	$html .= td($articles);
	$html .= td($award['cash_award']);
	$html .= $first ? td($tc, $na) : "";
	$html .= $first ? td($bank_account, $na) : "";
	$html .= $first ? td($award['modified_date'], $na) : "";
	$html .= "</tr>";

	return $html;

}

if ( (! empty($_SESSION['admin_id'])) && (! empty($_SESSION['admin_yearmonth'])) ) {
    // Set contest
    $yearmonth = $_SESSION["admin_yearmonth"];

	// Get Contest Name
	$sql = "SELECT * FROM contest WHERE yearmonth = '$yearmonth' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$contest = mysqli_fetch_array($query);
	$contest_archived = ($contest['archived'] == 1);

	// Open output stream for data download
	$output = fopen('php://output', 'w');

	// Write First Line
	$html  = "<table>";
	$html .= "<tr>";
	// $html .= th("A Addr 1") . th("B Addr 2") . th("C Addr 3") . th("D City") . th("E State") . th("F PIN") . th("G Country");
	// $html .= th("H A-Num") . th("I A-Name",) . th("J A-type") . th("K B-Name") . th("L B-Brn") . th("M B-IFSC");
	$html .= th("#") . th("Name") . th("Salutation") . th("First Name") . th("Last Name") . th("Email") . th("Phone");
	$html .= th("Address") . th("Award Name") . th("Article") . th("Cash Award") . th("Remittance") . th("Bank Account") . th("Updated on");
	$html .= "</tr>";

    // Select Rows from ENTRY table
    // Make List of Awardees
	$awardee_list = [];

	// First Picture Awards
	$sql  = "SELECT pic_result.profile_id, COUNT(*) AS num_awards, SUM(cash_award) AS total_cash ";
	if ($contest_archived)
		$sql .= "  FROM ar_pic_result pic_result, award ";
	else
		$sql .= "  FROM pic_result, award ";
	$sql .= " WHERE pic_result.yearmonth = '$yearmonth' ";
	$sql .= "   AND award.yearmonth = pic_result.yearmonth ";
	$sql .= "   AND award.award_id = pic_result.award_id ";
	$sql .= "   AND award.level < 99 ";
	$sql .= " GROUP BY pic_result.profile_id ";
	$sql .= " ORDER BY pic_result.profile_id ";
	$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC))
		$awardee_list[$row['profile_id']] = $row;

	// Sum up / Add Individual Awards
	$sql  = "SELECT entry_result.profile_id, COUNT(*) AS num_awards, SUM(cash_award) AS total_cash ";
	$sql .= "  FROM entry_result, award ";
	$sql .= " WHERE entry_result.yearmonth = '$yearmonth' ";
	$sql .= "   AND award.yearmonth = entry_result.yearmonth ";
	$sql .= "   AND award.award_id = entry_result.award_id ";
	$sql .= "   AND award.level < 99 ";
	$sql .= " GROUP BY entry_result.profile_id ";
	$sql .= " ORDER BY entry_result.profile_id ";
	$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
		if (isset($awardee_list[$row['profile_id']])) {
			$awardee_list[$row['profile_id']]['num_awards'] += $row['num_awards'];
			$awardee_list[$row['profile_id']]['total_cash'] += $row['total_cash'];
		}
		else
			$awardee_list[$row['profile_id']] = $row;
	}

	$row = 0;
	foreach ($awardee_list as $profile_id => $winner) {
		++ $row;
		$na = $winner['num_awards'];
		$tc = $winner['total_cash'];

		$first = true;

		// Get Picture Awards and Produce Rows
		$sql  = "SELECT profile.profile_id, salutation, first_name, last_name, profile_name, email, phone, address_1, address_2, address_3, city, state, pin, ";
		$sql .= "       IFNULL(country_name, '') AS country_name, ";
		$sql .= "       bank_account_number, bank_account_name, bank_account_type, bank_name, bank_branch, bank_ifsc_code, profile.modified_date, ";
		$sql .= "       section, award_name, has_medal, has_pin, has_ribbon, has_memento, has_gift, has_certificate, cash_award ";
		if ($contest_archived)
			$sql .= "  FROM ar_pic_result pic_result, award, ";
		else
			$sql .= "  FROM pic_result, award, ";
		$sql .= "       profile LEFT JOIN country ON country.country_id = profile.country_id ";
		$sql .= " WHERE pic_result.yearmonth = '$yearmonth' ";
		$sql .= "   AND pic_result.profile_id = '$profile_id' ";
		$sql .= "   AND profile.profile_id = pic_result.profile_id ";
		$sql .= "   AND award.yearmonth = pic_result.yearmonth ";
		$sql .= "   AND award.award_id = pic_result.award_id ";
		$sql .= "   AND award.level < 99 ";
		$award_query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		while ($award = mysqli_fetch_array($award_query)) {
			$html .= award_row($award, $na, $tc, $row, $first);
			$first = false;
		}

		// Add Entry Awards - Select profile details as well, should there be no Picture Results
		$sql  = "SELECT profile.profile_id, salutation, first_name, last_name, profile_name, email, phone, address_1, address_2, address_3, city, state, pin, ";
		$sql .= "       IFNULL(country_name, '') AS country_name, ";
		$sql .= "       bank_account_number, bank_account_name, bank_account_type, bank_name, bank_branch, bank_ifsc_code, profile.modified_date, ";
		$sql .= "       section, award_name, has_medal, has_pin, has_ribbon, has_memento, has_gift, has_certificate, cash_award ";
		$sql .= "  FROM entry_result, award, profile LEFT JOIN country ON country.country_id = profile.country_id ";
		$sql .= " WHERE entry_result.yearmonth = '$yearmonth' ";
		$sql .= "   AND entry_result.profile_id = '$profile_id' ";
		$sql .= "   AND profile.profile_id = entry_result.profile_id ";
		$sql .= "   AND award.yearmonth = entry_result.yearmonth ";
		$sql .= "   AND award.award_id = entry_result.award_id ";
		$award_query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		while ($award = mysqli_fetch_array($award_query)) {
			$html .= award_row($award, $na, $tc, $row, $first);
			$first = false;
		}
	}
	$html .= "</table>";
	fputs($output, $html);
}
else
    debug_dump("SESSION", $_SESSION, __FILE__, __LINE__);
?>
