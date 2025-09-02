<?php
// Gather common information from Database
if ( ! empty($_SESSION['admin_id']) ) {

	$admin_id = $_SESSION['admin_id'];
	$admin_name = $_SESSION['admin_name'];
	$admin_avatar = $_SESSION['admin_avatar'];
	$admin_role = $_SESSION['admin_role'];

}
else {
	$_SESSION['err_msg'] = "Session expired. Login again";
	printf("<script>location.href='/index.php'</script>");
	header("Location: /index.php");
	die();
}
?>
