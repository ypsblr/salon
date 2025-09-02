<?php
session_save_path(__DIR__ . "/../inc/session");
session_start();
include("../inc/connect.php");
include("../inc/lib.php");

// sets the active section data so that user will land under the same section
if (isset($_REQUEST['yearmonth']) && isset($_REQUEST['visitor_id']) && isset($_REQUEST['hall']) ) {

	$yearmonth = $_REQUEST['yearmonth'];
	$visitor_id = $_REQUEST['visitor_id'];
	$hall = $_REQUEST['hall'];

	// Retrieve Visitor Book entry for the visitor_id
	$sql = "SELECT * FROM visitor_book WHERE yearmonth = '$yearmonth' AND visitor_id = '$visitor_id' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$row = mysqli_fetch_array($query);

	if ($row["halls_visited"] == "")
		$hall_visits = [];
	else
		$hall_visits = explode(",", $row['halls_visited']);
	$already_visited = false;
	for($idx = 0; $idx < sizeof($hall_visits); ++ $idx) {
		list($visited_hall, $visited_times) = explode("|", $hall_visits[$idx]);
		if ($visited_hall == $hall) {
			$already_visited = true;
			++ $visited_times;
			$hall_visits[$idx] = implode("|", array($visited_hall, $visited_times));
		}
	}
	if (! $already_visited) {
		$hall_visits[] = implode("|", array($hall, 1));
	}
	$halls_visited = implode(",", $hall_visits);

	$sql  = "UPDATE visitor_book ";
	$sql .= "   SET halls_visited = '$halls_visited' ";
	$sql .= " WHERE yearmonth = '$yearmonth' ";
	$sql .= "   AND visitor_id = '$visitor_id' ";
	mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Launch the Exhibition in full screen
	header("Cache-Control: no-cache");
	$exhibition_hall = "/salons/$yearmonth/exhibition/$hall/exhibition.html";
	header('Location: ' . $exhibition_hall);
	printf("<script>location.href='" . $exhibition_hall . "'</script>");
}
else {
	$_SESSION['err_msg'] = "Unable to complete. Invalid request !";
	header('Location: ' . $_SERVER['HTTP_REFERER']);
	printf("<script>location.href='" . $_SERVER['HTTP_REFERER'] . "'</script>");
}
?>
