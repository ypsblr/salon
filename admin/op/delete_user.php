<?php
// session_start();
include("../inc/session.php");
include("../inc/connect.php");
include("../inc/lib.php");

define("SALON_ROOT", http_method() . $_SERVER['SERVER_NAME']);

if ( (! empty($_SESSION['admin_id'])) && (! empty($_SESSION['admin_yearmonth'])) && isset($_REQUEST['delete_id']) ) {

	$yearmonth = $_SESSION['admin_yearmonth'];
	$user_id = $_REQUEST['delete_id'];

	// Check if the user has any assignments in the present year or in the past
	$sql = "SELECT * FROM assignment WHERE user_id = '$user_id' ";
	$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_num_rows($query) == 0) {
		// Delete the User
		$sql = "DELETE FROM user WHERE user_id = '$user_id' ";
		mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		$_SESSION['success_msg'] = "User deleted !";
	}
	else
		$_SESSION['err_msg'] = "User has assignments to Salons. Assignments must be deleted before deleting the user !";

	header("Location: " . $_SERVER['HTTP_REFERER']);
	printf("<script>location.href='" . $_SERVER['HTTP_REFERER'] . "'</script>");
}
else {
	$_SESSION['err_msg'] = "Invalid Parameters";
	header("Location: ".$_SERVER['HTTP_REFERER']);
	printf("<script>location.href='".$_SERVER['HTTP_REFERER']."'</script>");
}
?>
