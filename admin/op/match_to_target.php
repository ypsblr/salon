<?php
// session_start();
include("../inc/session.php");
include("../inc/connect.php");
include("../inc/lib.php");

// debug_dump("POST", $_POST, __FILE__, __LINE__);

function has_duplicates($table) {
	global $DBCON;
	global $target_profile_id;
	global $match_profile_id;

	$sql  = "SELECT DISTINCT yearmonth, COUNT(DISTINCT profile_id) AS num_profiles ";
	$sql .= "  FROM " . $table . " ";
	$sql .= " WHERE profile_id IN ('$match_profile_id', '$target_profile_id') ";
	$sql .= " GROUP BY yearmonth ";
	$sql .= " HAVING num_profiles > 1 ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	return (mysqli_num_rows($query) > 0);
}

function move_profile_id ($table) {
	global $DBCON;
	global $target_profile_id;
	global $match_profile_id;

	$sql  = "UPDATE " . $table . " ";
	$sql .= "   SET profile_id = '$target_profile_id' ";
	$sql .= " WHERE profile_id = '$match_profile_id' ";
	mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
}

if ( isset($_SESSION['admin_yearmonth']) && isset($_SESSION['admin_id']) &&
 	 isset($_REQUEST['target']) && isset($_REQUEST['match']) ) {

	$target_profile_id = $_REQUEST['target'];
	$match_profile_id = $_REQUEST['match'];

	// Obtain Target and Match Profiles
	$sql = "SELECT * FROM profile WHERE profile_id = '$target_profile_id' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_num_rows($query) == 0)
		return_error("Target Profile ($target_profile_id) not found ", __FILE__, __LINE__);
	$target = mysqli_fetch_array($query);
	if ($target['profile_disabled'] == '1')
		return_error("Target profile is already disabled !", __FILE__, __LINE__);

	$sql = "SELECT * FROM profile WHERE profile_id = '$match_profile_id' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_num_rows($query) == 0)
		return_error("Match Profile ($match_profile_id) not found ", __FILE__, __LINE__);
	$match = mysqli_fetch_array($query);
	if ($target['profile_disabled'] == '1')
		return_error("Match profile is already disabled !", __FILE__, __LINE__);

	// Perform entire operation as a single transaction that can rollback
	$sql = "START TRANSACTION";
	mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Check and log rows in the entry table for rolling back
	// entry table
	$sql  = "INSERT INTO merge_log (yearmonth, profile_id, moved_to_id, in_archive) ";
	$sql .= "SELECT yearmonth, profile_id, '$target_profile_id', '0' ";
	$sql .= "  FROM entry ";
	$sql .= " WHERE profile_id = '$match_profile_id' ";
	mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$rows_merged = mysqli_affected_rows($DBCON);

	// ar_entry table
	$sql  = "INSERT INTO merge_log (yearmonth, profile_id, moved_to_id, in_archive) ";
	$sql .= "SELECT yearmonth, profile_id, '$target_profile_id', '1' ";
	$sql .= "  FROM ar_entry ";
	$sql .= " WHERE profile_id = '$match_profile_id' ";
	mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$rows_merged += mysqli_affected_rows($DBCON);

	if ($rows_merged > 0) {
		$table_list = ["entry", "ar_entry", "pic", "ar_pic", "rating", "ar_rating", "pic_result",
						"ar_pic_result", "entry_result", "coupon", "catalog_order",
						"stats_bits", "stats_compiled" ];
		foreach ($table_list as $table) {
			if (has_duplicates($table))
				return_error("There are entries for both profiles in $table table !", __FILE__, __LINE__);
			else
				move_profile_id($table);
		}

		// Participation Related
		// move_profile_id("entry");		// entry table
		// move_profile_id("ar_entry");	// ar_entry table
		// move_profile_id("pic");			// pic
		// move_profile_id("ar_pic");		// ar_pic
		// move_profile_id("rating");		// rating
		// move_profile_id("ar_rating");	// ar_rating
		// move_profile_id("pic_result");	// pic_result
		// move_profile_id("ar_pic_result");
		// move_profile_id("entry_result");

		// Monetary Tables
		// move_profile_id("coupon");
		// move_profile_id("catalog_order");

		// Tables storing profile_id in columns with different name
		// payment table - slightly different structure
		$sql  = "UPDATE payment ";
		$sql .= "   SET link_id = '$target_profile_id' ";
		$sql .= " WHERE link_id = '$match_profile_id' ";
		$sql .= "   AND account IN ('IND', 'CTG') ";
		mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

		// Club & Club Entry
		$sql  = "UPDATE club ";
		$sql .= "   SET last_updated_by = '$target_profile_id' ";
		$sql .= " WHERE last_updated_by = '$match_profile_id' ";
		mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

		$sql  = "UPDATE club_entry ";
		$sql .= "   SET club_entered_by = '$target_profile_id' ";
		$sql .= " WHERE club_entered_by = '$match_profile_id' ";
		mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

		// Statistics
		// move_profile_id("stats_bits");
		// move_profile_id("stats_compiled");
		$sql  = "UPDATE stats_participation ";
		$sql .= "   SET stat_profile_id = '$target_profile_id' ";
		$sql .= " WHERE stat_profile_id = '$match_profile_id' ";
		mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	}

	// Diable matching profile
	$sql  = "UPDATE profile ";
	$sql .= "   SET profile_disabled = '1' ";
	$sql .= "     , profile_merged_with = '$target_profile_id' ";
	$sql .= " WHERE profile_id = '$match_profile_id' ";
	mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// If it has survived this far, let us commit
	$sql = "COMMIT";
	mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Go back
	$_SESSION['success_msg'] = "Profile $match_profile_id successfully merged into $target_profile_id ";
	header("Location: ".$_SERVER['HTTP_REFERER']);
	printf("<script>location.href='".$_SERVER['HTTP_REFERER']."'</script>");
}
else {
	return_error("Invalid Request", __FILE__, __LINE__);
}
?>
