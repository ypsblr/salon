<?php
// session_start();
include("../inc/session.php");
include("../inc/connect.php");
include("../inc/lib.php");

function array_add_quotes($array) {
	$new_array = array();
	foreach($array AS $key => $value)
		$new_array[$key] = "'" . $value . "'";

	return $new_array;
}

if (isset($_SESSION['jury_id'])) {

	// Load Current Picture and related details
	$sql  = "SELECT * FROM ctl, pic ";
	$sql .= " WHERE ctl.yearmonth = pic.yearmonth ";
	$sql .= "   AND ctl.profile_id = pic.profile_id ";
	$sql .= "   AND ctl.pic_id = pic.pic_id ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$row = mysqli_fetch_array($query);
	$jury_yearmonth = $row['yearmonth'];
	$section = $row['section'];
	$profile_id = $row['profile_id'];
	$pic_id = $row['pic_id'];
	$pic_title = $row['title'];
	$picfile = $row['picfile'];
	$pic_eseq = $row['eseq'];
	$entrant_categories = $row['entrant_categories'];
	list($width, $height, $type, $attr) = getimagesize("../../salons/$jury_yearmonth/upload/" . $row["section"] . "/" . $row["picfile"]);

	// Set session variables to identify change
	if ( (! isset($_SESSION['dr_last_profile_id'])) || (! isset($_SESSION['dr_last_pic_id']))  ) {
		$_SESSION['dr_last_profile_id'] = $profile_id;
		$_SESSION['dr_last_pic_id'] = $pic_id;
	}

	// Determine if CTL table has been updated with a new picture to optimize processing
	$pic_changed = false;
	if ($_SESSION['dr_last_profile_id'] != $profile_id || $_SESSION['dr_last_pic_id'] != $pic_id)	{
		// Set session variables to display the rating for previous picture
		$_SESSION['dr_prev_profile_id'] = $_SESSION['dr_last_profile_id'];
		$_SESSION['dr_prev_pic_id'] = $_SESSION['dr_last_pic_id'];
		// Save the picture ids for comparison next time display_refresh is invoked
		$_SESSION['dr_last_profile_id'] = $profile_id;
		$_SESSION['dr_last_pic_id'] = $pic_id;
		$pic_changed = true;
	}

	// Load Rating List for current picture
	$sql  = "SELECT * FROM assignment ";
	$sql .= " WHERE yearmonth = '$jury_yearmonth' ";
	$sql .= "   AND section = '$section' ";
	$sql .= " ORDER BY jurynumber";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$ratingList = array();
	$totalrating = 0;
	for ($idx = 0; $assign = mysqli_fetch_array($query); $idx++) {
		$jury_id = $assign['user_id'];
		$sql  = "SELECT * FROM rating ";
		$sql .= " WHERE yearmonth = '$jury_yearmonth' ";
		$sql .= "   AND user_id = '$jury_id' ";
		$sql .= "   AND profile_id = '$profile_id' ";
		$sql .= "   AND pic_id = '$pic_id' ";

		$qass = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		if ($qrow = mysqli_fetch_array($qass)) {
			$ratingList[$idx] = $qrow['rating'];
			$totalrating += $qrow['rating'];
		}
		else
			$ratingList[$idx] = "-";
	}

	// Load Rating for prev picture
	$prev_profile_id = 0;
	$prev_pic_id = 0;
	$prev_pic_title = "";
	$prev_pic_file = "";
	$prev_pic_rating = 0;
	if (isset($_SESSION['dr_prev_profile_id']) && isset($_SESSION['dr_prev_pic_id'])) {
		$prev_profile_id = $_SESSION['dr_prev_profile_id'];
		$prev_pic_id = $_SESSION['dr_prev_pic_id'];
		$sql  = "SELECT pic.pic_id, pic.title, pic.picfile, IFNULL(SUM(rating.rating), 0) AS rating FROM pic ";
		$sql .= "  LEFT JOIN rating ON (rating.yearmonth = pic.yearmonth AND rating.profile_id = pic.profile_id AND rating.pic_id = pic.pic_id) ";
		$sql .= " WHERE pic.yearmonth = '$jury_yearmonth' ";
		$sql .= "   AND pic.profile_id = '$prev_profile_id' ";
		$sql .= "   AND pic.pic_id = '$prev_pic_id' ";
		$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		$row = mysqli_fetch_array($query);
		$prev_pic_title = $row['title'];
		$prev_pic_file = $row['picfile'];
		$prev_pic_rating = $row['rating'];
	}

	// Load Next Picture
	$sql  = "SELECT * FROM pic, entry ";
	$sql .= " WHERE pic.yearmonth = '$jury_yearmonth' ";
	$sql .= "   AND pic.section = '$section' ";
	$sql .= "   AND pic.eseq >= '$pic_eseq' ";			// There being a chance of two pictures being uploaded the same second, compare profile and pic ids
	$sql .= "   AND CONCAT(pic.profile_id, '-', pic.pic_id) != CONCAT('$profile_id', '-', '$pic_id') ";
	$sql .= "   AND entry.yearmonth = pic.yearmonth ";
	$sql .= "   AND entry.profile_id = pic.profile_id ";
	if ($entrant_categories != "") {
		$category_list = "(" . implode(",", array_add_quotes(explode(",", $entrant_categories))) . ")";
		$sql .= " AND entry.entrant_category IN " . $category_list . " ";
	}
	$sql .= " ORDER BY eseq ASC LIMIT 1";

	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_num_rows($query) > 0) {
		$row = mysqli_fetch_array($query);
		$next_profile_id = $row['profile_id'];
		$next_pic_id = $row['pic_id'];
		$next_pic_title = $row['title'];
		$next_pic_file = $row['picfile'];
	}
	else {
		$next_profile_id = 0;
		$next_pic_id = 0;
		$next_pic_title = '';
		$next_pic_file = '';
	}

	$retval = array ("pic_id" => $pic_id,
					"section" => $section,
					"title" => $pic_title,
					"pic" => $picfile,
					"juryrating" => $ratingList,
					"totalrating" => $totalrating,
					"height" => $height,
					"width" => $width,
					"has_pic_changed" => ($pic_changed) ? "YES" : "NO",
					"prev_profile_id" => $prev_profile_id,
					"prev_pic_id" => $prev_pic_id,
					"prev_pic_title" => $prev_pic_title,
					"prev_pic_file" => $prev_pic_file,
					"prev_pic_rating" => $prev_pic_rating,
					"next_profile_id" => $next_profile_id,
					"next_pic_id" => $next_pic_id,
					"next_pic_title" => $next_pic_title,
					"next_pic_file" => $next_pic_file);
	echo json_encode($retval);
}
else
	handle_error("Invalid Request", __FILE__, __LINE__);
?>
