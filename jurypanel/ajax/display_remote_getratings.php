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
	isset($_SESSION['categories']) && isset($_REQUEST['rating_time']) &&
 	isset($_REQUEST['profile_id']) && isset($_REQUEST['pic_id']) &&
	isset($_REQUEST['total_rating']) && isset($_REQUEST['offset']) && isset($_REQUEST['limit']) ) {

	$jury_yearmonth = $_SESSION['jury_yearmonth'];
	$section = $_SESSION['section'];
	$session_categories = $_SESSION['categories'];

	$current_time = $_REQUEST['rating_time'];
	$current_profile_id = $_REQUEST['profile_id'];
	$current_pic_id = $_REQUEST['pic_id'];
	$current_total_rating = $_REQUEST['total_rating'];
	$offset = $_REQUEST['offset'];
	$limit = $_REQUEST['limit'];

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
	$sql .= "       SUM(rating.rating) AS total_rating, MAX(rating.modified_date) AS rating_time, ";
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
	if ($current_time != NULL) {
		$sql .= "   AND rating_time >= '$current_time' ";
	}
	$sql .= " ORDER BY rating_time, profile_id, pic_id ";
	$sql .= " LIMIT $offset, $limit ";			// To avoid protocol errors from return data size

	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$num_pics = mysqli_num_rows($query);
	if ($num_pics > 0) {
		$pics = [];
		while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
			// Assemble row
			$row['pic_tn'] = "/salons/$jury_yearmonth/upload/$section/" . $row['picfile'];
			$ratings = explode(":", $row['ratings']);
			$rating_list = [];
			for ($i = 0; $i < $num_juries; ++$i) {
				$jury_id = $jury_list[$i]['user_id'];
				$rating_list[] = get_rating($ratings, $jury_id);
			}
			$row['rating_list'] = $rating_list;
			// Add to pics list
			$pics[] = $row;
		}
	}
	else
		$pics = [];

	$retval = array ("status" => "OK",
					"errmsg" => "",
					"pic_returned" => ($num_pics > 0),
					"pics" => $pics
					);
	echo json_encode($retval);
}
else
	echo json_encode(array("status" => "ERROR", "errmsg" => "Invalid Request", "rows" => 0));
?>
