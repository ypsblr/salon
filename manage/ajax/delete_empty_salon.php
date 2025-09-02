<?php
include("../inc/session.php");
include "../inc/connect.php";
include_once("ajax_lib.php");


// Main Code
if( isset($_SESSION['admin_id']) && isset($_REQUEST['yearmonth']) && isset($_REQUEST['del-salon-action']) ) {

	$yearmonth = $_REQUEST['yearmonth'];	// Source

	// Check if contests already exists for target yearmonth
	$sql = "SELECT * FROM contest WHERE yearmonth = '$yearmonth' ";
	$query = mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_num_rows($query) == 0)
		return_error("Salon does not exist", __FILE__, __LINE__, true);

	// Check if the salon is empty as a safety measure
	foreach (array('entry', 'pic', 'club_entry', 'sponsorship') as $table) {
		$sql = "SELECT COUNT(*) AS rowcount FROM " . $table . " WHERE yearmonth = '$yearmonth' ";
		$query = mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		$row = mysqli_fetch_array($query);
		if ($row['rowcount'] != 0)
			return_error("Salon is not empty. Table $table has some records.", __FILE__, __LINE__, true);
	}

	// Start Transaction
	$sql = "START TRANSACTION";
	mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Delete CONTEST
	$sql  = "DELETE FROM contest ";
	$sql .= " WHERE yearmonth = '$yearmonth' ";
	mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Delete SECTIONS
	$sql  = "DELETE FROM section ";
	$sql .= " WHERE yearmonth = '$yearmonth' ";
	mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Delete ENTRANT CATEGORIES
	$sql  = "DELETE FROM entrant_category ";
	$sql .= " WHERE yearmonth = '$yearmonth' ";
	mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Delete AWARD
	$sql  = "DELETE FROM award ";
	$sql .= " WHERE yearmonth = '$yearmonth' ";
	mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Delete FEES & DISCOUNT structures
	// FEE structure
	$sql  = "DELETE FROM fee_structure ";
	$sql .= " WHERE yearmonth = '$yearmonth' ";
	mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	 // DISCOUNT structure
	 $sql  = "DELETE FROM discount ";
	 $sql .= " WHERE yearmonth = '$yearmonth' ";
	 mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	 // Delete Team
	 $sql  = "DELETE FROM team ";
	 $sql .= " WHERE yearmonth = '$yearmonth' ";
	 mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	 // Delete Jury Assignment
	 $sql  = "DELETE FROM assignment ";
	 $sql .= " WHERE yearmonth = '$yearmonth' ";
	 mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	 $sql = "COMMIT";
	 mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	 $resArray = [];
	 $resArray['success'] = TRUE;
	 $resArray['msg'] = "";
	 echo json_encode($resArray);
	 die();
}
else
	return_error("Invalid Update Request", __FILE__, __LINE__, true);

?>
