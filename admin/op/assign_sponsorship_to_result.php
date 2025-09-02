<?php
//
// To be executed after results are finalized
// Links sponsorship and results
//
// Send Header to save the file
header('Content-Type: text');
header('Content-Disposition: attachment; filename=sponsor_to_result.sql');
// session_start();
include("../inc/session.php");

include("../inc/connect.php");
include("../inc/lib.php");

if(isset($_SESSION['admin_yearmonth']) && isset($_SESSION['admin_id']) && preg_match("/localhost/i", $_SERVER['SERVER_NAME']) ) {

    // Set contest
    $yearmonth = $_SESSION["admin_yearmonth"];

	// Open output stream for data download
	$output = fopen('php://output', 'w');
	fprintf($output, "--\r\n");
	fprintf($output, "-- Dumping UPDATE SQL statements to assign sponsors to each result for the contest %s\r\n", $yearmonth);
	fprintf($output, "--\r\n");

	$num_updates = 0;

	// Determine if contest is archived
	$sql = "SELECT * FROM contest WHERE yearmonth = '$jury_yearmonth' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__, false);
	$contest = mysqli_fetch_array($query);
	$contest_archived = ($contest['archived'] == '1');

	// Get a List of Awards
	$sql = "SELECT * FROM award WHERE yearmonth = '$yearmonth' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	while ($row = mysqli_fetch_array($query)) {

		$award_id = $row['award_id'];

		// Get Sponsorship records for the award and assign them to sponsorship slots
		$sql  = "SELECT * FROM sponsorship ";
		$sql .= " WHERE yearmonth = '$yearmonth' ";
		$sql .= "   AND sponsorship_type = 'AWARD' ";
		$sql .= "   AND link_id = '$award_id' ";
		$sql .= " ORDER BY sponsorship_no ";
		$spq = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		if (mysqli_num_rows($spq) > 0) {
			// Fill Sponsorship Slots with sponsorship_no
			$sponsorship_slot = array();
			while ($spr = mysqli_fetch_array($spq)) {
				for ($i = 0; $i < $spr['number_of_units']; ++$i)
					$sponsorship_slot[] = $spr['sponsorship_no'];
			}

			// Fetch relevant results
			switch ($row['award_type']) {
				case 'pic' : {
                    if ($contest_archived)
                        $sql = "SELECT * FROM ar_pic_result pic_result WHERE yearmonth = '$yearmonth' AND award_id = '$award_id' ";
                    else
                        $sql = "SELECT * FROM pic_result WHERE yearmonth = '$yearmonth' AND award_id = '$award_id' ";
					$resq = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
					for ($row_no = 0; $resr = mysqli_fetch_array($resq); ++$row_no) {
						if (isset($sponsorship_slot[$row_no])) {
							$sql  = "UPDATE pic_result ";
							$sql .= "   SET sponsorship_no = '" . $sponsorship_slot[$row_no] . "' ";
							$sql .= " WHERE yearmonth = '$yearmonth' ";
							$sql .= "   AND award_id = '$award_id' ";
							$sql .= "   AND profile_id = '" . $resr['profile_id'] . "' ";
							$sql .= "   AND pic_id = '" . $resr['pic_id'] . "' ";
							fprintf($output, "%s ;\r\n", $sql);
							mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
							++ $num_updates;
						}
					}
					break;
				}
				case 'entry' : {
					$sql = "SELECT * FROM entry_result WHERE yearmonth = '$yearmonth' AND award_id = '$award_id' ";
					$resq = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
					for ($row_no = 0; $resr = mysqli_fetch_array($resq); ++$row_no) {
						if (isset($sponsorship_slot[$row_no])) {
							$sql  = "UPDATE entry_result ";
							$sql .= "   SET sponsorship_no = '" . $sponsorship_slot[$row_no] . "' ";
							$sql .= " WHERE yearmonth = '$yearmonth' ";
							$sql .= "   AND award_id = '$award_id' ";
							$sql .= "   AND profile_id = '" . $resr['profile_id'] . "' ";
							fprintf($output, "%s ;\r\n", $sql);
							mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
						}
					}
					break;
				}
				case 'club' : {
					$sql = "SELECT * FROM club_result WHERE yearmonth = '$yearmonth' AND award_id = '$award_id' ";
					$resq = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
					for ($row_no = 0; $resr = mysqli_fetch_array($resq); ++$row_no) {
						if (isset($sponsorship_slot[$row_no])) {
							$sql  = "UPDATE club_result ";
							$sql .= "   SET sponsorship_no = '" . $sponsorship_slot[$row_no] . "' ";
							$sql .= " WHERE yearmonth = '$yearmonth' ";
							$sql .= "   AND award_id = '$award_id' ";
							$sql .= "   AND club_id = '" . $resr['club_id'] . "' ";
							fprintf($output, "%s ;\r\n", $sql);
							mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
						}
					}
					break;
				}
			}	// switch by award_type
		}	// there are sponsorships for this award
	}
	fprintf($output, "--\r\n");
	fprintf($output, "-- Completed assignment of %d sponsorships to results\r\n", $num_updates);
	fprintf($output, "--\r\n");
}
else
    debug_dump("SESSION", $_SESSION, __FILE__, __LINE__);
?>
