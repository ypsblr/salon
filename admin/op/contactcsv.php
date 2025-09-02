<?php
header('Content-Type: text/csv');
//header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=contacts.csv');
// session_start();
include("../inc/session.php");

include("../inc/connect.php");
include("../inc/lib.php");

function salon_name($yearmonth) {
	global $DBCON;
	$sql = "SELECT contest_name FROM contest WHERE yearmonth = '$yearmonth' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_num_rows($query) == 0)
		return "";
	$row = mysqli_fetch_array($query);
	return $row['contest_name'];
}

if ( (! empty($_SESSION['admin_id'])) && (! empty($_SESSION['admin_yearmonth'])) ) {

	$yearmonth = $_SESSION['admin_yearmonth'];
	$new_contacts = isset($_REQUEST['new']);
	$salon_contacts = isset($_REQUEST['salon']);
	$start_profile_id = 0;

	// Find the last profile_id from previous salons
	if ($new_contacts) {
		// Changed to take into cognizance that the profile could have been merged with a newer profile
		$sql  = "SELECT MAX(IFNULL(merge_log.profile_id, entry.profile_id)) AS start_profile_id ";
		$sql .= "  FROM entry LEFT JOIN merge_log ";
		$sql .= "    ON merge_log.yearmonth = entry.yearmonth ";
		$sql .= "   AND merge_log.moved_to_id = entry.profile_id ";
		$sql .= " WHERE entry.yearmonth != '$yearmonth' ";
		// $sql = "SELECT MAX(profile_id) AS start_profile_id FROM entry WHERE yearmonth != '$yearmonth' ";
		$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		$row = mysqli_fetch_array($query);
		$start_profile_id = $row['start_profile_id'];
	}

	// Open output stream for data download
	$output = fopen('php://output', 'w');

	// Write First Line
	fputcsv($output, array('Entry#', 'Name', 'Email', 'Country', 'Phone', 'YPS Mem ID', 'No of Salons', 'First Salon', 'Last Salon'));

	// Select Rows from ENTRY table
	$sql  = "SELECT profile_id, profile_name, email, country_name, phone, yps_login_id  ";
	$sql .= "  FROM profile, country ";
	$sql .= " WHERE profile.country_id = country.country_id ";
	$sql .= "   AND profile_id > '$start_profile_id' ";
	if ($salon_contacts) {
		$sql .= "   AND profile_id IN ( ";
		$sql .= "       SELECT profile_id FROM entry ";
		$sql .= "        WHERE yearmonth = '$yearmonth' ";
		$sql .= "          AND participation_code != '' ) ";
	}
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	while ($row = mysqli_fetch_array($query)) {
		$profile_id = $row['profile_id'];
		$sql  = "SELECT COUNT(*) AS num_salons, IFNULL(MIN(entry.yearmonth), '0') AS first_salon, IFNULL(MAX(entry.yearmonth), '0') AS last_salon ";
		$sql .= "  FROM entry, contest ";
		$sql .= " WHERE entry.profile_id = '$profile_id' ";
		$sql .= "   AND contest.yearmonth = entry.yearmonth ";
		$qhist = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		$rhist = mysqli_fetch_array($qhist);

		fputcsv($output, array($row['profile_id'], $row['profile_name'], $row['email'], $row['country_name'], $row['phone'], $row['yps_login_id'],
							   $rhist['num_salons'], salon_name($rhist['first_salon']), salon_name($rhist['last_salon'])));
	}
}
?>
