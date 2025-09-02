<?php
// session_start();
include("../inc/session.php");
include("../inc/connect.php");
include("../inc/lib.php");

if ( isset($_SESSION['jury_id']) && isset($_SESSION['jury_yearmonth']) &&
	 isset($_REQUEST['jury_yearmonth']) && isset($_REQUEST['award_group']) ) {
	$session_id = $_SESSION['jury_id'];

	// If yearmonth is changed select the award group
	// Set the default award_group to the first award_group
	if ($_SESSION['jury_yearmonth'] != $_REQUEST['jury_yearmonth']) {
		$sql = "SELECT DISTINCT award_group FROM entrant_category WHERE yearmonth = '" . $_REQUEST['jury_yearmonth'] . "' ";
		$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		$row = mysqli_fetch_array($query);
		$award_group = $row['award_group'];
	}
	else
		$award_group = $_REQUEST['award_group'];

	$_SESSION['jury_yearmonth'] = $_REQUEST['jury_yearmonth'];
	$_SESSION['award_group'] = $award_group;

	$sql  = "SELECT entrant_category FROM entrant_category ";
	$sql .= " WHERE yearmonth = '" . $_SESSION['jury_yearmonth'] . "' ";
	$sql .= "   AND award_group = '" . $_SESSION['award_group'] . "' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$category_list = array();
	while($row = mysqli_fetch_array($query))
		$category_list[] = $row['entrant_category'];
	$_SESSION['categories'] = implode(",", $category_list);
	$categories = $_SESSION['categories'];

	// Update ctl for use by display to predict next picture
	$sql = "UPDATE ctl SET entrant_categories = '$categories', yearmonth = '" . $_SESSION['jury_yearmonth'] . "' ";
	mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
}
else {
	$_SESSION['err_msg'] = "Select both the contest and the Award Group";
}
header("Location: ".$_SERVER['HTTP_REFERER']);
printf("<script>location.href='".$_SERVER['HTTP_REFERER']."'</script>");

?>
