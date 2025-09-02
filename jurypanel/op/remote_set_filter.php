<?php
// session_start();
include("../inc/session.php");
include("../inc/connect.php");
include("../inc/lib.php");

if(isset($_SESSION['jury_id']) && isset($_SESSION['jury_yearmonth'])) {
	$session_id = $_SESSION['jury_id'];

	if (isset($_REQUEST['set_filter'])) {
		// $_SESSION['jury_yearmonth'] = $_REQUEST['jury_yearmonth'];
		$_SESSION['section'] = $_REQUEST['selected_section'];
		$_SESSION['award_group'] = $_REQUEST['award_group'];

		$sql = "SELECT entrant_category FROM entrant_category WHERE yearmonth = '" . $_SESSION['jury_yearmonth'] . "' AND award_group = '" . $_SESSION['award_group'] . "' ";
		$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		$category_list = array();
		while($row = mysqli_fetch_array($query))
			$category_list[] = $row['entrant_category'];
		$_SESSION['categories'] = implode(",", $category_list);
	}
}
header("Location: ".$_SERVER['HTTP_REFERER']);
printf("<script>location.href='".$_SERVER['HTTP_REFERER']."'</script>");

?>
