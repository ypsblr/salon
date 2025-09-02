<?php
// session_start();
include("../inc/session.php");
include("../inc/connect.php");

if(isset($_SESSION['jury_id']) && isset($_SESSION['jury_yearmonth'])) {

	$jury_yearmonth = $_SESSION['jury_yearmonth'];

	// Turn all jury_sessions off for all sections
	//$sql = "UPDATE jury_session SET session_open = '0' WHERE yearmonth = '$jury_yearmonth' ";
	//mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

}

printf("<script>location.href='/jurypanel/index.php'</script>");
header("Location: /jurypanel/index.php");
?>
