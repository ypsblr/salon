<?php
// Get List of Contests for which sponsorhip slots are available
function spn_sponsorship_open_contest_list($award_type = "pic") {
	global $DBCON;
	global $contest_yearmonth;

	$sql  = "SELECT yearmonth, contest_name FROM contest ";
	$sql .= "WHERE contest.yearmonth IN ";
	$sql .= "         (SELECT DISTINCT award.yearmonth FROM award";
	$sql .= "           WHERE award.yearmonth = contest.yearmonth ";
	if ($award_type != "*")
		$sql .= "         AND award.award_type = '$award_type' ";
	$sql .= "             AND award.sponsored_awards > 0 ";
	$sql .= "             AND award.sponsored_awards > ";
	$sql .= "                   (SELECT IFNULL(SUM(number_of_units), 0) FROM sponsorship ";
	$sql .= "                     WHERE sponsorship.yearmonth = award.yearmonth ";
	$sql .= "                       AND sponsorship.sponsorship_type = 'AWARD' ";
	$sql .= "                       AND sponsorship.link_id = award.award_id ) ";
	$sql .= "             AND award.sponsorship_last_date >= '" . date("Y-m-d") . "') ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_num_rows($query) > 0) {
		$contest_list = array();
		while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC))
			$contest_list[$row['yearmonth']] = $row['contest_name'];
		return $contest_list;
	}
	else
		return [];
}
?>

<?php
// Get List of Awards for which sponsorhip slots are available
function spn_sponsorship_open_list($yearmonth = '$contest_yearmonth', $award_type = "pic") {
	global $DBCON;
	global $contest_yearmonth;

	$sql  = "SELECT award.award_id, award.section, award.award_name, award.sponsorship_per_award, award.sponsored_awards, IFNULL(SUM(sponsorship.number_of_units), 0) AS number_sponsored ";
	$sql .= "  FROM award ";
	$sql .= "  LEFT JOIN sponsorship ";
	$sql .= "    ON sponsorship.yearmonth = award.yearmonth ";
	$sql .= "   AND sponsorship.sponsorship_type = 'AWARD' ";
	$sql .= "   AND sponsorship.link_id = award.award_id ";
	$sql .= " WHERE award.yearmonth = '$yearmonth' ";
	if ($award_type != "*")
		$sql .= "   AND award.award_type = '$award_type' ";
	$sql .= "   AND award.sponsored_awards > 0 ";
	$sql .= "   AND award.sponsorship_last_date >= '" . date("Y-m-d") . "' ";
	$sql .= " GROUP BY award.award_id ";
	$sql .= "HAVING number_sponsored < award.sponsored_awards ";
	$sql .= " ORDER BY award.section, award.level ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_num_rows($query) > 0) {
		$award_list = array();
		while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC))
			$award_list[$row['award_id']] = $row;
		return $award_list;
	}
	else
		return array();
}
?>

<?php
// Get List of Awards for which sponsorhip slots are available
function spn_num_sponsorship_open($award_type = "") {
	global $DBCON;
	global $contest_yearmonth;

	$sql  = "SELECT COUNT(*) AS num_awards FROM award ";
	$sql .= "WHERE award.yearmonth = '$contest_yearmonth' ";
	if ($award_type != "")
		$sql .= "  AND award.award_type = '$award_type' ";
	$sql .= "  AND award.sponsored_awards > 0 ";
	$sql .= "  AND award.sponsored_awards > ";
	$sql .= "         (SELECT SUM(number_of_units) FROM sponsorship ";
	$sql .= "           WHERE sponsorship.yearmonth = '$contest_yearmonth' ";
	$sql .= "             AND sponsorship.sponsorship_type = 'AWARD' ";
	$sql .= "             AND sponsorship.link_id = award.award_id ) ";
	$sql .= "  AND award.sponsorship_last_date >= '" . $date("Y-m-d") . "' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$row = mysqli_fetch_array($query);
	return $row['num_awards'];
}
?>

<?php
// Get a list of sponsors
function spn_sponsor_list() {
	global $DBCON;
	
	$sql = "SELECT * FROM sponsor ORDER BY sponsor_name";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_num_rows($query) > 0) {
		$spn_list = array();
		while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC))
			$spn_list[$row['sponsor_id']] = $row;
		return $spn_list;
	}
	else
		return false;
}
//- spn_sponsor_lis()
?>

<?php
// Get a List of Awards Sponsored
function spn_get_sponsored_list($sponsor_id, $yearmonth = '$contest_yearmonth') {
	global $DBCON;
	
	$sql  = "SELECT award.award_id, award.section, award.award_name, ";
	$sql .= "       number_of_units, total_sponsorship_amount, award_name_suffix, payment_received ";
	$sql .= "  FROM sponsorship, award ";
	$sql .= " WHERE sponsorship.yearmonth = '$yearmonth' ";
	$sql .= "   AND sponsorship.sponsorship_type = 'AWARD' ";
	$sql .= "   AND sponsorship.sponsor_id = '$sponsor_id' ";
	$sql .= "   AND award.yearmonth = sponsorship.yearmonth ";
	$sql .= "   AND award.award_id = sponsorship.link_id ";
	$sql .= " ORDER BY payment_received DESC ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_num_rows($query) > 0) {
		$spn_list = array();
		while($row = mysqli_fetch_array($query)) {
			$spn_list[] = $row;
		}
		return $spn_list;
	}
	else
		return false;
}
?>

<?php
// Get Number of slots sponsored for an award
function spn_num_awards_sponsored($award_id) {
	global $DBCON;
	global $contest_yearmonth;
	
	$sql  = "SELECT SUM(number_of_units) AS num_awards_sponsored ";
	$sql .= "  FROM sponsorship ";
	$sql .= " WHERE yearmonth = '$contest_yearmonth' ";
	$sql .= "   AND sponsorship_type = 'AWARD' ";
	$sql .= "   AND link_id = '$award_id' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if ($row = mysqli_fetch_array($query))
		return $row['num_awards_sponsored'];
	else
		return 0;
}
?>

