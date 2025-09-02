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

function get_rating($ratings_list, $jury_id) {
	foreach($ratings_list as $user_rating) {
		list($user_id, $rating) = explode("-", $user_rating);
		if ($user_id == $jury_id)
			return $rating;
	}
	return 0;
}

function jury_seq($user_id) {
	global $jury_list;

	foreach($jury_list as $jury)
		if ($jury['user_id'] == $user_id)
			return intval($jury['jurynumber']);

	return false;
}

if (isset($_SESSION['jury_id']) && isset($_SESSION['jury_yearmonth']) && isset($_SESSION['section']) &&
	isset($_SESSION['categories']) && isset($_REQUEST['current_time']) &&
 	isset($_REQUEST['current_profile_id']) && isset($_REQUEST['current_pic_id']) &&
	isset($_REQUEST['current_total_rating']) && isset($_REQUEST['current_operation']) ) {

	$jury_yearmonth = $_SESSION['jury_yearmonth'];
	$section = $_SESSION['section'];
	$session_categories = $_SESSION['categories'];

	$current_time = $_REQUEST['current_time'];
	$current_profile_id = $_REQUEST['current_profile_id'];
	$current_pic_id = $_REQUEST['current_pic_id'];
	$current_total_rating = $_REQUEST['current_total_rating'];

	// Process current_operation to adjust time
	switch ($_REQUEST['current_operation']) {
		case "beginning" : {
			// Get the rating time for the first picture rated
			$sql  = "SELECT IFNULL(MIN(modified_date), '" . date("Y-m-d 00:00:00") . "') AS min_rating_datetime ";
			$sql .= "  FROM rating, assignment ";
			$sql .= " WHERE rating.yearmonth = '$jury_yearmonth' ";
			$sql .= "   AND assignment.yearmonth = rating.yearmonth ";
			$sql .= "   AND assignment.section = '$section' ";
			$sql .= "   AND assignment.user_id = rating.user_id ";
			$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
			$row = mysqli_fetch_array($query);
			$current_time = $row['min_rating_datetime'];
			$current_profile_id = 0;
			$current_pic_id = 0;
			$current_total_rating = 0;
			break;
		}
		case "forward" : {
			// Forward by 15 minutes
			$current_time = date("Y-m-d H:i:s", strtotime("+15 min", strtotime($current_time)));
			$current_profile_id = 0;
			$current_pic_id = 0;
			$current_total_rating = 0;
			break;
		}
		case "rewind" : {
			// Rewind by 15 minutes
			$current_time = date("Y-m-d H:i:s", strtotime("-15 min", strtotime($current_time)));
			$current_profile_id = 0;
			$current_pic_id = 0;
			$current_total_rating = 0;
			break;
		}
		case "last" : {
			// Start from the latest scored picture
			$current_time = date("Y-m-d H:i:s");
			$current_profile_id = 0;
			$current_pic_id = 0;
			$current_total_rating = 0;
			break;
		}
		case "next" : {
			break;
		}
	}

	$categories = explode(",", $session_categories);
	$entrant_filter = " AND entry.entrant_category IN (" . implode(",", array_add_quotes($categories)) . ") ";

	// Load Jury List
	$sql  = "SELECT user.user_id, user_name, avatar, honors, jurynumber FROM assignment, user ";
	$sql .= " WHERE assignment.yearmonth = '$jury_yearmonth' ";
	$sql .= "   AND assignment.section = '$section' ";
	$sql .= "   AND assignment.user_id = user.user_id  ";
	$sql .= " ORDER BY jurynumber";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$num_juries = mysqli_num_rows($query);
	$jury_list = [];
	while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC))
		$jury_list[] = $row;

	// Load first list of pictures to display
	$sql  = "SELECT rating.profile_id, rating.pic_id, pic.title, pic.picfile, COUNT(*) AS num_ratings, ";
	$sql .= "       SUM(rating.rating) AS total_rating, MAX(rating.modified_date) AS rating_date, ";
	$sql .= "       GROUP_CONCAT(CONCAT_WS('-', rating.user_id, rating.rating) SEPARATOR ':') as ratings  ";
	$sql .= "  FROM rating, pic, entry ";
	$sql .= " WHERE rating.yearmonth = '$jury_yearmonth' ";
	$sql .= "   AND pic.yearmonth = rating.yearmonth ";
	$sql .= "   AND pic.profile_id = rating.profile_id ";
	$sql .= "   AND pic.pic_id = rating.pic_id ";
	$sql .= "   AND pic.section = '$section' ";
	$sql .= "   AND entry.yearmonth = pic.yearmonth ";
	$sql .= "   AND entry.profile_id = pic.profile_id ";
	$sql .= $entrant_filter;
	$sql .= " GROUP BY profile_id, pic_id, title, picfile ";
	$sql .= "HAVING num_ratings = '$num_juries' ";
	$sql .= "   AND NOT (rating.profile_id = '$current_profile_id' ";
	$sql .= "            AND rating.pic_id = '$current_pic_id' ";
	$sql .= "            AND total_rating = '$current_total_rating' )";
	if ( $_REQUEST['current_operation'] == "last" || ($_REQUEST['current_operation'] == "forward" && strtotime($current_time) > time()) ) {
		$sql .= "   AND rating_date <= '$current_time' ";
		$sql .= " ORDER BY rating_date DESC, rating.profile_id DESC, rating.pic_id DESC ";
	}
	else {
		$sql .= "   AND rating_date >= '$current_time' ";
		$sql .= " ORDER BY rating_date, rating.profile_id, rating.pic_id ";
	}
	$sql .= " LIMIT 1 ";

	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$num_pics = mysqli_num_rows($query);
	if ($num_pics > 0) {
		$row = mysqli_fetch_array($query, MYSQLI_ASSOC);
		$row['pic_tn'] = "/salons/$jury_yearmonth/upload/$section/" . $row['picfile'];
		$ratings = explode(":", $row['ratings']);
		$rating_list = [];
		for ($i = 0; $i < $num_juries; ++$i) {
			$jury_id = $jury_list[$i]['user_id'];
			$rating_list[] = get_rating($ratings, $jury_id);
		}
		$row['rating_list'] = $rating_list;
	}
	else
		$row = [];

	$retval = array ("status" => "OK",
					"errmsg" => "",
					"pic_returned" => ($num_pics > 0),
					"pic" => $row
					);
	echo json_encode($retval);
}
else
	echo json_encode(array("status" => "ERROR", "errmsg" => "Invalid Request", "rows" => 0));
?>
