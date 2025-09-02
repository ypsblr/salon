<?php
// session_start();
include("../inc/session.php");
include("../inc/connect.php");

if(isset($_SESSION['jury_id'])) {
	$session_id = $_SESSION['jury_id'];

	if (isset($_REQUEST['jury_yearmonth'])) {
		$_SESSION['jury_yearmonth'] = $_REQUEST['jury_yearmonth'];
	}
}
header("Location: ".$_SERVER['HTTP_REFERER']);
printf("<script>location.href='".$_SERVER['HTTP_REFERER']."'</script>");

?>
