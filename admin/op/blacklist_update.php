<?php
// session_start();
include("../inc/session.php");
include("../inc/connect.php");
include("../inc/lib.php");
include("../inc/blacklist_lib.php");

define("SALON_ROOT", http_method() . $_SERVER['SERVER_NAME']);


if ( (! empty($_SESSION['admin_id'])) && (! empty($_SESSION['admin_yearmonth'])) ) {

	// Assemble Information
	// Form Data
	$yearmonth = $_SESSION['admin_yearmonth'];

	$num_blacklists_marked = 0;
	$num_blacklists_unmarked = 0;

	// Re-run blacklist match on all profiles & update as needed
	$sql = "SELECT profile_id, profile_name, email, phone, blacklist_match, blacklist_exception FROM profile";
	$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	while($pr = mysqli_fetch_array($query)) {
		$profile_id = $pr['profile_id'];
		$profile_name = $pr['profile_name'];
		$email = $pr['email'];
		$phone = $pr['phone'];
		$profile_blacklist_match = $pr['blacklist_match'];
		$profile_blacklist_exception = $pr['blacklist_exception'];

		// Check every profile against blacklist
		list($blacklist_match, $blacklist_name) = check_blacklist($profile_name, $email, $phone);

		if ($blacklist_match != $profile_blacklist_match) {
			if ($blacklist_match == "")
				++ $num_blacklists_unmarked;
			else
				++ $num_blacklists_marked;
			// Update Profile
			$sql = "UPDATE profile SET blacklist_match = '$blacklist_match' WHERE profile_id = '$profile_id' ";
			mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		}
	}

	if ($num_blacklists_marked == 0 && $num_blacklists_unmarked == 0)
		$_SESSION['success_msg'] = "All profiles checked. There are no changes";
	else
		$_SESSION['success_msg'] = "All profiles checked. " . $num_blacklists_marked . " profiles have been marked and " . $num_blacklists_unmarked . " profiles unmarked !";

	header("Location: ".$_SERVER['HTTP_REFERER']);
	printf("<script>location.href='".$_SERVER['HTTP_REFERER']."'</script>");
}
else {
	$_SESSION['err_msg'] = "Invalid Parameters";
	header("Location: ".$_SERVER['HTTP_REFERER']);
	printf("<script>location.href='".$_SERVER['HTTP_REFERER']."'</script>");
}
?>
