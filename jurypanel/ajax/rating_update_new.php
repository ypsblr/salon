<?php
// session_start();
include("../inc/session.php");
// Insert a rating or tag into ratings table

include("../inc/connect.php");
include("./ajax_lib.php");

// A function to return an error message and stop
function return_error($errmsg) {
	$resArray = array();
	$resArray['status'] = "ERR";
	$resArray['errmsg'] = $errmsg;
	echo json_encode($resArray);
	die;
}

// Usage: $query = mysql_query($sql) or sql_error($sql, mysql_error(), __FILE__, __LINE__);
function sql_json_error($sql, $errmsg, $phpfile, $phpline) {
	log_sql_error($sql, $errmsg, $phpfile, $phpline);
	return_error("Server Error");
	die;
}

if((isset($_REQUEST['rating']) || isset($_REQUEST['tag']))  && isset($_SESSION['jury_id'])) {
	$session_id = $_SESSION['jury_id'];

	// Load Current Picture and related details
	$sql  = "SELECT pic.yearmonth, pic.profile_id, pic.pic_id, section, title, picfile, eseq FROM ctl, pic ";
	$sql .= " WHERE pic.yearmonth = ctl.yearmonth ";
	$sql .= "   AND pic.profile_id = ctl.profile_id ";
	$sql .= "   AND pic.pic_id = ctl.pic_id";
	$query = mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$row = mysqli_fetch_array($query);
	$jury_yearmonth = $row['yearmonth'];
	$cur_profile_id = $row['profile_id'];
	$cur_pic_id = $row["pic_id"];
	$cur_pic_section = $row["section"];		// Section of this picture
	$cur_pic_title = $row["title"];
	$cur_pic_file = $row["picfile"];
	$cur_pic_eseq = $row['eseq'];

	// Check if Jury is assigned to this section
	$sql  = "SELECT * FROM assignment ";
	$sql .= " WHERE yearmonth = '$jury_yearmonth' ";
	$sql .= "   AND section = '$cur_pic_section' ";
	$sql .= "   AND user_id = '$session_id' ";
	$query = mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_num_rows($query) == 0)		// Jury not assigned to this section and hence cannot provide rating
		return_error('You are not assigned to this section');

	// Check if there is a rating record
	$sql  = "SELECT * FROM rating ";
	$sql .= " WHERE yearmonth = '$jury_yearmonth' ";
	$sql .= "   AND profile_id = '$cur_profile_id' ";
	$sql .= "   AND pic_id = '$cur_pic_id' ";
	$sql .= "   AND user_id = '$session_id' ";
	$query = mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_num_rows($query) == 0) {	// Insert New Rating
		if (isset($_REQUEST['tag']))	// cannot tag image without a rating
			return_error('Picture must be rated before it is tagged');
		$new_rating = $_REQUEST['rating'];
		$sql  = "INSERT INTO rating(yearmonth, profile_id, pic_id, user_id, rating, tags) ";
		$sql .= "VALUES ('$jury_yearmonth', '$cur_profile_id', '$cur_pic_id', '$session_id', '$new_rating', '')";
		mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		$cur_pic_rating = $new_rating;
		$cur_pic_tags = "";
	}
	else {	// Update rating or tag
		$row = mysqli_fetch_array($query);
		$cur_pic_rating = $row['rating'];
		$cur_pic_tags = $row['tags'];
		$sql = "UPDATE rating ";
		if (isset($_REQUEST['tag'])) {
			$sql .= "SET tags = 'TAGGED' ";
			$cur_pic_tags = 'TAGGED';
		}
		else {
			$sql .= "SET rating = '" . $_REQUEST['rating'] . "' ";
			$cur_pic_rating = $_REQUEST['rating'];
		}
		$sql .= " WHERE yearmonth = '$jury_yearmonth' ";
		$sql .= "   AND profile_id = '$cur_profile_id' ";
		$sql .= "   AND pic_id = '$cur_pic_id' ";
		$sql .= "   AND user_id = '$session_id' ";
		mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);;
	}
	$cur_pic_tags = is_null($cur_pic_tags) ? "" : $cur_pic_tags;
	$retval = array('status' => 'OK',
					'errmsg' => "",
					'cur_pic_section' => $cur_pic_section,
					'jury_yearmonth' => $jury_yearmonth,
					'cur_profile_id' => $cur_profile_id,
					'cur_pic_id' => $cur_pic_id,
					'cur_pic_title' => $cur_pic_title,
					'cur_pic_file' => $cur_pic_file,
					'cur_pic_eseq' => $cur_pic_eseq,
					'cur_pic_rating' => $cur_pic_rating,
					'cur_pic_tags' => $cur_pic_tags);
	echo json_encode($retval);
}
else
	return_error("Invalid access");
?>
