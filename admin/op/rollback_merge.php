<?php
//
// Rollback Profile Merge performed earlier
//
// session_start();
include("../inc/session.php");
include("../inc/connect.php");
include("../inc/lib.php");

// debug_dump("POST", $_POST, __FILE__, __LINE__);

// Replace $target_profile_id back with $match_profile_id for salons for which there is a merge log
function revert_profile_id ($table, $yearmonth) {
	global $DBCON;
	global $target_profile_id;
	global $match_profile_id;

	$sql  = "UPDATE " . $table . " ";
	$sql .= "   SET profile_id = '$match_profile_id' ";
	$sql .= " WHERE yearmonth = '$yearmonth' ";
	$sql .= "   AND profile_id = '$target_profile_id' ";
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

	$sql = "SELECT * FROM profile WHERE profile_id = '$match_profile_id' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_num_rows($query) == 0)
		return_error("Match Profile ($match_profile_id) not found ", __FILE__, __LINE__);
	$match = mysqli_fetch_array($query);

	// Determine the true target and match
	if ( ! ($match['profile_disabled'] == '1' && $match['profile_merged_with'] == $target_profile_id) ) {
		// Not the normal case of match being merged with target, but probably the target is merged with match
		if ($target['profile_disabed'] == '1' && $target['profile_merged_with'] == $match_profile_id) {
			// Swap target and match
			$tmp = $target_profile_id;
			$target_profile_id = $match_profile_id;
			$match_profile_id = $tmp;
		}
		else
			return_error("Unable to find paired profiles matching $target_profile_id and $match_profile_id !", __FILE__, __LINE__);
	}

	$table_list = ["entry", "ar_entry", "pic", "ar_pic", "rating", "ar_rating", "pic_result",
					"ar_pic_result", "entry_result", "coupon", "catalog_order",
					"stats_bits", "stats_compiled" ];

	// Perform entire operation as a single transaction that can rollback
	$sql = "START TRANSACTION";
	mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Get the contests from merge log matching target and match
	$sql  = "SELECT * FROM merge_log ";
	$sql .= " WHERE profile_id = '$match_profile_id' ";
	$sql .= "   AND moved_to_id = '$target_profile_id' ";
	$log_query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	while ( $log = mysqli_fetch_array($log_query) ) {
		$log_yearmonth = $log['yearmonth'];
		// Unmerge regular tables with yearmonth and profile_id as keys
		foreach ($table_list as $table)
			revert_profile_id($table, $log_yearmonth);

		// Tables storing profile_id in columns with different name
		// payment table - slightly different structure
		$sql  = "UPDATE payment ";
		$sql .= "   SET link_id = '$match_profile_id' ";
		$sql .= " WHERE yearmonth = '$log_yearmonth' ";
		$sql .= "   AND link_id = '$target_profile_id' ";
		$sql .= "   AND account IN ('IND', 'CTG') ";
		mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

		$sql  = "UPDATE club_entry ";
		$sql .= "   SET club_entered_by = '$match_profile_id' ";
		$sql .= " WHERE yearmonth = '$log_yearmonth' ";
		$sql .= "   AND club_entered_by = '$target_profile_id' ";
		mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

		// Statistics
		$sql  = "UPDATE stats_participation ";
		$sql .= "   SET stat_profile_id = '$match_profile_id' ";
		$sql .= " WHERE yearmonth = '$log_yearmonth' ";
		$sql .= "   AND stat_profile_id = '$target_profile_id' ";
		mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	}

	// Diable matching profile
	$sql  = "UPDATE profile ";
	$sql .= "   SET profile_disabled = '0' ";
	$sql .= "     , profile_merged_with = '0' ";
	$sql .= " WHERE profile_id = '$match_profile_id' ";
	mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Delete Merge Log for completed rollbacks
	$sql  = "DELETE FROM merge_log ";
	$sql .= " WHERE profile_id = '$match_profile_id' ";
	$sql .= "   AND moved_to_id = '$target_profile_id' ";
	mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// If it has survived this far, let us commit
	$sql = "COMMIT";
	mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Go back
	$_SESSION['success_msg'] = "Profile $match_profile_id successfully unmerged from $target_profile_id ";
	header("Location: ".$_SERVER['HTTP_REFERER']);
	printf("<script>location.href='".$_SERVER['HTTP_REFERER']."'</script>");
}
else {
	return_error("Invalid Request", __FILE__, __LINE__);
}
?>
