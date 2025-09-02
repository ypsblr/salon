<?php
session_save_path(__DIR__ . "/../inc/session");
session_start();
include("../inc/connect.php");
include("ajax_lib.php");

function this_sql_error($sql, $errmsg, $phpfile, $phpline) {
	log_sql_error($sql, $errmsg, $phpfile, $phpline);
	echo json_encode(false);
}

// Creates a regular expression to perform a loose match based only on each word
function make_expr($str) {
	if ($str == "")
		return "";
	else {
		// Remove no letters - leaving letlers and spaces
		$clean_str = "";
		for ($i = 0; $i < strlen($str); ++$i) {
			$c = strtoupper(substr($str, $i, 1));
			if (($c >= 'A' && $c <= 'Z') || ($c == ' '))
				$clean_str .= $c;
		}
		$expr = "^";
		$words = explode(" ", $clean_str);
		foreach ($words as $word) {
			if ($word != "")
				$expr .= $word . ".*";
		}
		$expr .= '$';
		return $expr;
	}
}

if ( isset($_SESSION['USER_ID']) && isset($_REQUEST['club_name']) && isset($_REQUEST['club_id']) ) {

	$expr = make_expr($_REQUEST['club_name']);

	$sql = "SELECT club_id, club_name FROM club WHERE club_name REGEXP '$expr' ";
	// debug_dump("SQL", $sql, __FILE__, __LINE__);
	$query = mysqli_query($DBCON, $sql) or this_sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_num_rows($query) == 0)
		echo json_encode(true);
	else {
		$row = mysqli_fetch_array($query);
		// Name in the retrieved record belongs to this club, hence ok
		echo json_encode( $row['club_id'] == $_REQUEST['club_id'] );
	}
}
else {
	echo json_encode(false);
}
?>
