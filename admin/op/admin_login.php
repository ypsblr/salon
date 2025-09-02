<?php
// session_start();
include("../inc/session.php");
include("../inc/connect.php");
include("../inc/lib.php");

// debug_dump("POST", $_POST, __FILE__, __LINE__);
if(isset($_REQUEST['admin_login_check']) && isset($_REQUEST['admin_yearmonth']) && isset($_REQUEST['admin_id']) && isset($_REQUEST['password']) ) {
	
	$admin_yearmonth = $_REQUEST['admin_yearmonth'];
	$admin_id = $_REQUEST['admin_id'];
    $password = $_REQUEST['password'];

	$sql = "SELECT * FROM team WHERE yearmonth = '$admin_yearmonth' AND member_login_id = '$admin_id' AND member_password = '$password' ";
	$qchk = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$count = mysqli_num_rows($qchk);
	// debug_dump("count", $count, __FILE__, __LINE__);
	if ($count > 0) {
		$row = mysqli_fetch_array($qchk);
		if (has_permission(explode(",", $row['permissions']))) {
			$_SESSION['admin_id'] = $admin_id;
			$_SESSION['admin_yearmonth'] = $admin_yearmonth;
			$_SESSION['admin_email'] = $row['email'];

			printf("<script>location.href='/admin/entry_dashboard.php'</script>");
			header("Location: /admin/entry_dashboard.php");
		}
		else {
			$_SESSION['err_msg']="Login Failed !!! Insufficient Permission.";
			printf("<script>location.href='/admin/index.php'</script>");
			header("Location: /admin/index.php");
		}
	}
	else {
		$_SESSION['err_msg']="Login Failed !!! Check your User Name and Password";
		printf("<script>location.href='/admin/index.php'</script>");
		header("Location: /admin/index.php");
	}
}
else {
	$_SESSION['err_msg'] = "Invalid Request";
	printf("<script>location.href='/admin/index.php'</script>");
	header("Location: /admin/index.php");
}
?>
