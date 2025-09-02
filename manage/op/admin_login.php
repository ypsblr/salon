<?php
// session_start();
include("../inc/session.php");
include("../inc/connect.php");
include("../inc/lib.php");

// debug_dump("POST", $_POST, __FILE__, __LINE__);
if(isset($_REQUEST['admin_login_check']) && isset($_REQUEST['admin_id']) && isset($_REQUEST['password']) ) {

	$admin_id = $_REQUEST['admin_id'];
    $password = $_REQUEST['password'];

	$sql = "SELECT * FROM user WHERE login = '$admin_id' AND password = '$password' ";
	$qchk = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$count = mysqli_num_rows($qchk);
	// debug_dump("count", $count, __FILE__, __LINE__);
	if ($count > 0) {
		$row = mysqli_fetch_array($qchk);
		if ($row['type'] == "MASTER" || $row['type'] == "ADMIN") {
			$_SESSION['admin_id'] = $admin_id;
			$_SESSION['admin_name'] = $row['user_name'];
			$_SESSION['admin_avatar'] = $row['avatar'];
			$_SESSION['admin_role'] = $row['type'];

			printf("<script>location.href='/manage/manage_home.php'</script>");
			header("Location: /manage/manage_home.php");
		}
		else {
			$_SESSION['err_msg']="Login Failed !!! Insufficient Permission.";
			printf("<script>location.href='/manage/index.php'</script>");
			header("Location: /manage/index.php");
		}
	}
	else {
		$_SESSION['err_msg']="Login Failed !!! Check your User Name and Password";
		printf("<script>location.href='/manage/index.php'</script>");
		header("Location: /manage/index.php");
	}
}
else {
	$_SESSION['err_msg'] = "Invalid Request";
	printf("<script>location.href='/manage/index.php'</script>");
	header("Location: /manage/index.php");
}
?>
