<?php
// session_start();
include("../inc/session.php");
include("../inc/connect.php");
include("../inc/lib.php");

if ( isset($_SESSION['admin_yearmonth']) && isset($_SESSION['admin_id']) &&
 	 isset($_REQUEST['target']) && isset($_REQUEST['match']) ) {

	$target_profile_id = $_REQUEST['target'];
	$match_profile_id = $_REQUEST['match'];
    $remove_exclusion = isset($_REQUEST['remove']);

	// Perform entire operation as a single transaction that can rollback
	$sql = "START TRANSACTION";
	mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Exclude all matches to target
	$sql = "SELECT * FROM exclude_match WHERE profile_id = '$target_profile_id' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_num_rows($query) == 0) {
        // No existing records
        if (! $remove_exclusion ) {
    		$sql = "INSERT INTO exclude_match (profile_id, excluded_profiles) VALUES ('$target_profile_id', '$match_profile_id') ";
    		mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
        }
	}
	else {
		// Already there are some excludes
		$exclude = mysqli_fetch_array($query);
		$excluded_profiles = explode(",", $exclude['excluded_profiles']);
        if ($remove_exclusion) {
            // Remove exclusion pair if pair exists
            if (in_array($match_profile_id, $excluded_profiles)) {
                $excluded_profiles = array_diff($excluded_profiles, [$match_profile_id]);
                if (sizeof($excluded_profiles) == 0) {
                    $sql = "DELETE FROM exclude_match WHERE profile_id = '$target_profile_id' ";
                    mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
                }
                else {
                    $sql  = "UPDATE exclude_match SET excluded_profiles = '" . implode(",", $excluded_profiles) . "' ";
        			$sql .= " WHERE profile_id = '$target_profile_id' ";
        			mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
                }
            }
        }
        else {
    		if (! in_array($match_profile_id, $excluded_profiles)) {
    			$excluded_profiles[] = $match_profile_id;
    			$sql  = "UPDATE exclude_match SET excluded_profiles = '" . implode(",", $excluded_profiles) . "' ";
    			$sql .= " WHERE profile_id = '$target_profile_id' ";
    			mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
    		}
        }
	}

	// Exclude all targets against match
	$sql = "SELECT * FROM exclude_match WHERE profile_id = '$match_profile_id' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_num_rows($query) == 0) {
		// No existing records
        if (! $remove_exclusion) {
            $sql = "INSERT INTO exclude_match (profile_id, excluded_profiles) VALUES ('$match_profile_id', '$target_profile_id') ";
            mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
        }
	}
	else {
		// Already there are some excludes
		$exclude = mysqli_fetch_array($query);
		$excluded_profiles = explode(",", $exclude['excluded_profiles']);
        if ($remove_exclusion) {
            // Remove exclusion pair if pair exists
            if (in_array($target_profile_id, $excluded_profiles)) {
                $excluded_profiles = array_diff($excluded_profiles, [$target_profile_id]);
                if (sizeof($excluded_profiles) == 0) {
                    $sql = "DELETE FROM exclude_match WHERE profile_id = '$match_profile_id' ";
                    mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
                }
                else {
                    $sql  = "UPDATE exclude_match SET excluded_profiles = '" . implode(",", $excluded_profiles) . "' ";
        			$sql .= " WHERE profile_id = '$match_profile_id' ";
        			mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
                }
            }
        }
        else {
    		if (! in_array($target_profile_id, $excluded_profiles)) {
    			$excluded_profiles[] = $target_profile_id;
    			$sql  = "UPDATE exclude_match SET excluded_profiles = '" . implode(",", $excluded_profiles) . "' ";
    			$sql .= " WHERE profile_id = '$match_profile_id' ";
    			mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
    		}
        }
	}


	// If it has survived this far, let us commit
	$sql = "COMMIT";
	mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Go back
    if ($remove_exclusion)
        $_SESSION['success_msg'] = "Profile matches between $match_profile_id and $target_profile_id will be proposed for merger";
    else
	   $_SESSION['success_msg'] = "Profile matches between $match_profile_id and $target_profile_id will be ignored";
	header("Location: ".$_SERVER['HTTP_REFERER']);
	printf("<script>location.href='".$_SERVER['HTTP_REFERER']."'</script>");
}
else {
	return_error("Invalid Request", __FILE__, __LINE__);
}
?>
