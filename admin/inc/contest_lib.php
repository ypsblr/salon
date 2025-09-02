<?php

function get_contest($yearmonth = 0) {
	global $DBCON;
	
	if ($yearmonth == 0) {
		if (isset($_SESSION['admin_yearmonth']))
			$yearmonth = $_SESSION['admin_yearmonth'];
		else {
			$sql = "SELECT MAX(yearmonth) AS last_contest FROM contest";
			$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
			$row = mysqli_fetch_array($query);
			$yearmonth = $row['last_contest'];
		}
	}
	// Fetch Contest Details
	$sql = "SELECT * FROM contest WHERE yearmonth = '$yearmonth' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$row = mysqli_fetch_array($query);
	
	return $row;
}

function get_contest_list() {
	global $DBCON;
	
	$sql = "SELECT yearmonth, contest_name FROM contest ORDER BY yearmonth DESC";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	
	$contest_list = [];
	while($row = mysqli_fetch_array($query, MYSQLI_ASSOC))
		$contest_list[] = $row;
	
	return $contest_list;
}

function get_section_list($yearmonth) {
	global $DBCON;

	// Prepare List of Sections
	$section_list_list = [];
	$sql = "SELECT * FROM section WHERE yearmonth = '$yearmonth' ";
	$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	while($row = mysqli_fetch_array($query, MYSQLI_ASSOC))
		$section_list[$row['section']] = $row;
	
	return $section_list;
}

?>