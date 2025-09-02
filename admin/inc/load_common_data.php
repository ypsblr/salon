<?php
// Gather common information from Database
if ( (! empty($_SESSION['admin_id'])) && (! empty($_SESSION['admin_yearmonth'])) ) {

	$admin_id = $_SESSION['admin_id'];
	$admin_yearmonth = $_SESSION['admin_yearmonth'];

	// Gather User Information
	$sql = "SELECT * FROM team WHERE yearmonth = '$admin_yearmonth' AND member_login_id = '$admin_id'";
	$user_query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$user_row = mysqli_fetch_array($user_query);
	$member_name = $user_row['member_name'];
	$member_role = $user_row['role'];
	$member_role_name = $user_row['role_name'];
	$member_honors = $user_row['honors'];
	$member_avatar = $user_row['avatar'];
	$member_email = $user_row['email'];
	$member_permissions = explode(",", $user_row['permissions']);		// List of permissions as array
	$member_sections = explode("|", $user_row['sections']);				// List of sections the member has access to
	$member_is_admin = in_array("admin", $member_permissions);
	$member_is_reviewer = in_array("reviewer", $member_permissions);
	$member_is_print_coordinator = in_array("print_coordinator", $member_permissions);
	// $member_is_reviewer = ($user_row['is_reviewer'] != 0);
	// $member_is_print_coordinator = ($user_row['is_print_coordinator'] != 0);

	// Gather Contest Information
	$sql = "SELECT * FROM contest WHERE yearmonth = '$admin_yearmonth' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_num_rows($query) == 0) {
		$_SESSION['err_msg'] = "Current contest not set / Session expired";
		printf("<script>location.href='/index.php'</script>");
		header("Location: /index.php");
		die();
	}
	$admin_contest = mysqli_fetch_array($query);
	$admin_contest_name = $admin_contest['contest_name'];
	$contest_open = (date_tz(date("Y-m-d"), $admin_contest['submission_timezone']) >= $admin_contest['registration_start_date'] &&
						date_tz(date("Y-m-d"), $admin_contest['submission_timezone']) <= $admin_contest['registration_last_date']);
	$contest_closed = date_tz(date("Y-m-d"), $admin_contest['submission_timezone']) > $admin_contest['registration_last_date'];
	$results_ready = ($admin_contest['results_ready'] == '1');
	$judging_end_date = $admin_contest['judging_end_date'];
	$exhibition_end_date = $admin_contest['exhibition_end_date'];
	$update_open = date("Y-m-d") > $admin_contest['update_start_date'] && date("Y-m-d") <= $admin_contest['update_end_date'];
	$update_closed = date("Y-m-d") > $admin_contest['update_end_date'];
	$catalog_order_open = ($admin_contest['catalog_ready'] == '1' && date("Y-m-d") <= $admin_contest['catalog_order_last_date']);
	$contest_archived = ($admin_contest['archived'] == '1');
}
else {
	$_SESSION['err_msg'] = "Session expired. Login again";
	printf("<script>location.href='/index.php'</script>");
	header("Location: /index.php");
	die();
}
?>
