<?php

function contest_has_sponsorship($yearmonth) {
	global $DBCON;
	
	$sql  = "SELECT SUM(sponsored_awards) AS num_sponsored_awards FROM award ";
	$sql .= " WHERE yearmonth = '$yearmonth' ";
	
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$row = mysqli_fetch_array($query, MYSQLI_ASSOC);

	return ($row['num_sponsored_awards'] > 0);
}

function sponsor_list($yearmonth) {
	global $DBCON;
	
	$sql  = "SELECT sponsor.sponsor_id, sponsor_name, sponsor_email, sponsor_phone,  IFNULL(SUM(number_of_units), 0) AS num_awards_sponsored, ";
	$sql .= "       IFNULL(SUM(total_sponsorship_amount), 0) AS total_sponsorship_amount, IFNULL(SUM(payment_received), 0) AS payment_received, sponsorship.link_id, ";
	$sql .= "       IFNULL(COUNT(link_id), 0) AS num_sponsorships ";
	$sql .= "  FROM sponsor, sponsorship ";
	$sql .= " WHERE sponsorship.yearmonth = '$yearmonth' ";
	$sql .= "   AND sponsorship.sponsorship_type = 'AWARD' ";
	$sql .= "   AND sponsor.sponsor_id = sponsorship.sponsor_id ";
	$sql .= " GROUP BY sponsor_id, sponsor_name, sponsor_email, sponsor_phone ";
	$sql .= " ORDER BY sponsor_name ";

	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$sponsor_list = [];
	while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC))
		$sponsor_list[] = $row;

	return $sponsor_list;
}
?>