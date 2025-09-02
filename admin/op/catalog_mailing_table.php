<?php
header('Content-Type: text/csv');
// header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=catalog_mailing.html');
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

function order_row($order) {

	static $sl_no = 0;

	++ $sl_no;

	$addr = [];
	$addr[] = $order['profile_name'];
	if ($order['address_1'] != "")
		$addr[] = $order['address_1'];
	if ($order['address_2'] != "")
		$addr[] = $order['address_2'];
	if ($order['address_3'] != "")
		$addr[] = $order['address_3'];
	$addr[] = $order['city'] . "-" . $order['pin'];
	$addr[] = $order['state'];
	$addr[] = $order['country_name'];
	$addr[] = "(P:" . $order['phone'] . ")";
	$address = excel_concat($addr);

	$profile = [];
	$profile[] = $order['profile_name'];
	$profile[] = $order['email'];
	$profile[] = $order['phone'];
	$profile_cell = excel_concat($profile);

	$html  = "<tr>";
	$html .= td($sl_no);
	$html .= td($profile_cell);
	$html .= td($address);
	$html .= td($order['currency']);
	$html .= td($order['copies']);
	$html .= td($order['price']);
	$html .= td($order['portage']);
	$html .= td($order['order_value']);
	$html .= td($order['payment_received']);
	$html .= td($order['gateway']);
	$html .= td($order['payment_ref']);
	$html .= "</tr>";

	return $html;

}

if ( (! empty($_SESSION['admin_id'])) && (! empty($_SESSION['admin_yearmonth'])) ) {
    // Set contest
    $yearmonth = $_SESSION["admin_yearmonth"];

	// Open output stream for data download
	$output = fopen('php://output', 'w');

	// Write First Line
	$html  = "<table>";
	$html .= "<tr>";
	// $html .= th("A Addr 1") . th("B Addr 2") . th("C Addr 3") . th("D City") . th("E State") . th("F PIN") . th("G Country");
	// $html .= th("H A-Num") . th("I A-Name",) . th("J A-type") . th("K B-Name") . th("L B-Brn") . th("M B-IFSC");
	$html .= th("#") . th("Name") . th("Address") . th("Currency") . th("# Copies") . th("Price") . th("Postage") . th("Order Value");
	$html .= th("Payment Received") . th("Gateway") . th("Payment Ref");
	$html .= "</tr>";

	// Catalog Order Summary
	$sql  = "SELECT catalog_order.profile_id, profile_name, phone, email, address_1, address_2, address_3, city, pin, state, country_name, ";
	$sql .= "       catalog_order.currency, IFNULL(gateway, '') AS gateway, ";
	$sql .= "       SUM(number_of_copies) AS copies, SUM(catalog_price) AS price, ";
	$sql .= "       SUM(catalog_postage) AS postage, SUM(order_value) AS order_value, ";
	$sql .= "       IFNULL(SUM(payment.amount), 0) AS payment_received, ";
	$sql .= "       IFNULL(GROUP_CONCAT(payment.payment_ref SEPARATOR ','), '') AS payment_ref ";
	$sql .= "  FROM profile, country, catalog_order LEFT JOIN payment ";
	$sql .= "       ON payment.yearmonth = catalog_order.yearmonth AND payment.account = 'CTG' ";
	$sql .= "       AND payment.link_id = catalog_order.profile_id ";
	$sql .= " WHERE catalog_order.yearmonth = '$yearmonth' ";
	$sql .= "   AND profile.profile_id = catalog_order.profile_id ";
	$sql .= "   AND country.country_id = profile.country_id ";
	$sql .= " GROUP BY catalog_order.profile_id ";
	$sql .= " ORDER BY profile_name ";
	$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC))
		$html .= order_row($row);

	$html .= "</table>";
	fputs($output, $html);
}
else
    debug_dump("SESSION", $_SESSION, __FILE__, __LINE__);
?>
