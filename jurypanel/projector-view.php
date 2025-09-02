<?php
// session_start();
include("inc/session.php");
include("inc/connect.php");
include("inc/lib.php");

function array_add_quotes($array) {
	$new_array = array();
	foreach($array AS $key => $value)
		$new_array[$key] = "'" . $value . "'";

	return $new_array;
}

// Fetches rejection text for display
function rejection_text($notifications) {
	static $rejection_reasons = [];
	global $DBCON;

	if (sizeof($rejection_reasons) == 0) {
		// Gather List of Rejection Reasons
		// Get Notifications List
		$sql  = "SELECT template_code, template_name ";
		$sql .= "  FROM email_template ";
		$sql .= " WHERE template_type = 'user_notification' ";
		$sql .= "   AND will_cause_rejection = '1' ";
		$qntf = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		$rejection_reasons = array();
		while ($rntf = mysqli_fetch_array($qntf))
			$rejection_reasons[$rntf['template_code']] = $rntf['template_name'];
	}

	$notification_list = explode("|", $notifications);
	$rejection_text = "";
	foreach ($notification_list AS $notification) {
		if ($notification != "") {
			list($notification_date, $notification_code_str) = explode(":", $notification);
			$notification_codes = explode(",", $notification_code_str);
			$rejected = false;
			foreach ($notification_codes as $notification_code)
				if (isset($rejection_reasons[$notification_code])) {
					$rejection_text .= (($rejection_text == "") ? "" : ",") . $rejection_reasons[$notification_code];
				}
		}
	}
	return $rejection_text;
}

if ( isset($_SESSION['jury_id']) && isset($_SESSION['jury_type']) && $_SESSION['jury_type']=="PROJECTOR" &&
     isset($_SESSION['jury_yearmonth']) && isset($_SESSION['award_group']) &&
	 isset($_REQUEST['sections']) ) {

	$jury_yearmonth = $_SESSION['jury_yearmonth'];
	$award_group = $_SESSION['award_group'];

	if (isset($_REQUEST['clear_filters'])) {
		if (isset($_SESSION['categories']))
			unset($_SESSION['categories']);
		if (isset($_SESSION['sub_categories']))
			unset($_SESSION['sub_categories']);
	}

	$sql = "SELECT entrant_category FROM entrant_category WHERE yearmonth = '$jury_yearmonth' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$category_list = array();
	while ($row = mysqli_fetch_array($query))
		$category_list[] = $row['entrant_category'];

	$sectionCount = array();
	if (isset($_SESSION['categories'])) {
		// $session_filter = explode(",", $_SESSION['categories']);
		$categories = explode(",", $_SESSION['categories']);
		$entrant_filter = " AND entrant_category IN (" . implode(",", array_add_quotes($categories)) . ") ";
	}
	else {
		$categories = $category_list;
		// $_SESSION['categories'] = implode(",", $categories);
		// $session_filter = explode(",", implode(",", $category_list));
		$entrant_filter = "";
	}

	if (isset($_REQUEST['sub_categories'])) {
		$_SESSION['sub_categories'] = implode(",", $_REQUEST['sub_categories']);
	}
	if (isset($_SESSION['sub_categories'])) {
		$sub_categories = explode(",", $_SESSION['sub_categories']);
		$entrant_filter = " AND entrant_category IN (" . implode(",", array_add_quotes($sub_categories)) . ") ";
	}

    $sections = decode_string_array(str_replace(" ", "", $_REQUEST['sections']));
	$_SESSION['section'] = $sections;
	// debug_dump($sections . " PROGRAM START", date_format(date_create(), "H:i:s.u"), __FILE__, __LINE__);

	// Get Section details
	$sql = "SELECT * FROM section WHERE yearmonth = '$jury_yearmonth' AND section = '$sections' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$section = mysqli_fetch_array($query);

	// Check to see if the jury_session is open
	$sql  = "SELECT * FROM jury_session ";
	$sql .= " WHERE yearmonth = '$jury_yearmonth' ";
	$sql .= "   AND section = '$sections' ";
	$sql .= "   AND award_group = '$award_group' ";
	$sql .= "   AND session_open = '1' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_num_rows($query) == 0)
		die_with_error("$sections section is not yet opened for judging", __FILE__, __LINE__);
	$jury_session = mysqli_fetch_array($query);

	// Generate a List of Jury Assignments
	$sql  = "SELECT COUNT(*) AS num_juries FROM assignment ";
	$sql .= " WHERE assignment.yearmonth = '$jury_yearmonth' ";
	$sql .= "   AND assignment.section = '$sections'";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$row = mysqli_fetch_array($query);
	$num_juries = $row['num_juries'];

	// Get Number of Pictures to be scored
	$sql  = "SELECT COUNT(rating.rating) AS num_scores ";
	$sql .= "  FROM entry, pic LEFT JOIN rating ";
	$sql .= "    ON rating.yearmonth = pic.yearmonth ";
	$sql .= "   AND rating.profile_id = pic.profile_id ";
	$sql .= "   AND rating.pic_id = pic.pic_id ";
	$sql .= " WHERE pic.yearmonth = '$jury_yearmonth' ";
	$sql .= "   AND pic.section = '$sections' ";
	$sql .= "   AND entry.yearmonth = pic.yearmonth ";
	$sql .= "   AND entry.profile_id = pic.profile_id ";
	$sql .= $entrant_filter;
	$sql .= " GROUP BY pic.yearmonth, pic.profile_id, pic.pic_id ";
	$sql .= "HAVING num_scores < '$num_juries' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$pics_left = mysqli_num_rows($query);

	// Get Award List
	$sql  = "SELECT * FROM award ";
	$sql .= " WHERE yearmonth = '$jury_yearmonth' ";
	$sql .= "   AND award_type = 'pic' ";
	$sql .= "   AND level != 99 ";
	$sql .= "   AND (section = '$sections' OR section = 'CONTEST') ";
	$sql .= "    AND award_group = '" . $_SESSION['award_group'] . "' ";
	// debug_dump("SQL", $sql, __FILE__, __LINE__);
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$awardList = array();
	while ($row = mysqli_fetch_array($query)) {
		$awardName = $row["award_name"] . "-" . $row["number_of_awards"];
		if ($row["section"] == "CONTEST")
				$awardName .= " (OVERALL)";
		$awardList[$awardName] = $row["award_id"];
	}

	// Get Result List
	$sql  = "SELECT * FROM pic_result, award ";
	$sql .= " WHERE pic_result.yearmonth = '$jury_yearmonth' ";
	$sql .= "   AND award.yearmonth = pic_result.yearmonth ";
	$sql .= "   AND award.award_id = pic_result.award_id ";
	$sql .= "   AND award.award_type = 'pic' ";
	$sql .= "   AND (award.section = '$sections' OR award.section = 'CONTEST')";
	$sql .= "    AND award_group = '" . $_SESSION['award_group'] . "' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$resultList = array();
	while ($row = mysqli_fetch_array($query)) {
		$awardName = $row["award_name"] . "-" . $row["number_of_awards"];
		if ($row["section"] == "CONTEST")
			$awardName .= " (OVERALL)";
		if(isset($resultList[$awardName]))
			array_push($resultList[$awardName], $row['profile_id'] . "|" . $row["pic_id"]);
		else
			$resultList[$awardName] = array($row['profile_id'] . "|" . $row["pic_id"]);
	}


	// Generate Bucket List
	// debug_dump("awardList", $awardList, __FILE__, __LINE__);
	if (isset($_SESSION["buckets"]) &&
			$_SESSION["buckets"]["section"] == $sections &&
			$_SESSION["buckets"]["award_group"] == $_SESSION['award_group'] ) {
		// Restore Bucket List from Session Variable
		$bucketList = $_SESSION["buckets"];
	}
	else {
		// Create a Bucket List
		$bucketList = array();
		$bucketList["section"] = $sections;
		if (isset($_SESSION['award_group']))
			$bucketList["award_group"] = $_SESSION['award_group'];
		else
			$bucketList["award_group"] = "";
		$bucketList["current_list"] = "";

		$bucketList["Bucket 1"] = ($jury_session['bucket1_list'] != "") ? explode(",", $jury_session['bucket1_list']) : array();
		$bucketList["Bucket 2"] = ($jury_session['bucket2_list'] != "") ? explode(",", $jury_session['bucket2_list']) : array();
		$bucketList["Bucket 3"] = ($jury_session['bucket3_list'] != "") ? explode(",", $jury_session['bucket3_list']) : array();
		$bucketList["Bucket 4"] = ($jury_session['bucket4_list'] != "") ? explode(",", $jury_session['bucket4_list']) : array();
		$bucketList["Bucket 5"] = ($jury_session['bucket5_list'] != "") ? explode(",", $jury_session['bucket5_list']) : array();

		foreach($awardList as $award => $award_id) {
			$bucketList[$award] = array();
			if (isset($resultList[$award]))
				$bucketList[$award] = $resultList[$award];
		}
		$_SESSION["buckets"] = $bucketList;
	}

	if (isset($_REQUEST['image-assign'])) {  // Assign Operation
		$bucketList["current_list"] = $_REQUEST['selected_bucket'];
		$currentBucket = $bucketList["current_list"];
		if (isset($_REQUEST['checkbox'])) {	// Empty array causes the entire operation to return NULL
			$bucketList[$currentBucket] = array_unique(array_merge($bucketList[$currentBucket], $_REQUEST['checkbox']));
			if (isset($awardList[$currentBucket])) 	 // If it is one of the Award Buckets, reset Result List as well
				$resultList[$currentBucket] =  $bucketList[$currentBucket];
		}
		$_SESSION["buckets"] = $bucketList;
		$request = $_SESSION["saved-request"];
	}
	else if (isset($_REQUEST['remove-assign'])) {  // Remove from  Bucket Operation
		$bucketList["current_list"] = $_REQUEST['selected_bucket'];
		$currentBucket = $bucketList["current_list"];
		if (isset($_REQUEST['checkbox'])) {
			$bucketList[$currentBucket] = array_diff($bucketList[$currentBucket], $_REQUEST['checkbox']);
			if (isset($awardList[$currentBucket])) 	 // If it is one of the Award Buckets, reset the Result List as well
				$resultList[$currentBucket] =  $bucketList[$currentBucket];
		}
		$_SESSION["buckets"] = $bucketList;
		$request = $_SESSION["saved-request"];
	} else if (isset($_REQUEST['image-move'])) {	// Copy to selected bucket and remove from the "from" bucket
		// Copy selected images to the Selected bucket
		$bucketList["current_list"] = $_REQUEST['selected_bucket'];
		$currentBucket = $bucketList["current_list"];
		if (isset($_REQUEST['checkbox'])) {	// Empty array causes the entire operation to return NULL
			$bucketList[$currentBucket] = array_unique(array_merge($bucketList[$currentBucket], $_REQUEST['checkbox']));
			if (isset($awardList[$currentBucket])) 	 // If it is one of the Award Buckets, reset Result List as well
				$resultList[$currentBucket] =  $bucketList[$currentBucket];
		}

		// Remove from the "from" bucket if it is not blank
		if (isset($_REQUEST['bucket_from']) && $_REQUEST['bucket_from'] != "" && $_REQUEST['bucket_from'] != $currentBucket ) {
			$currentBucket = $_REQUEST['bucket_from'];
			if (isset($_REQUEST['checkbox'])) {
				$bucketList[$currentBucket] = array_diff($bucketList[$currentBucket], $_REQUEST['checkbox']);
				if (isset($awardList[$currentBucket])) 	 // If it is one of the Award Buckets, reset the Result List as well
					$resultList[$currentBucket] =  $bucketList[$currentBucket];
			}
		}
		$_SESSION["buckets"] = $bucketList;		// Save bucket list to Session
		$request = $_SESSION["saved-request"];
	}
	else {
		$request = $_REQUEST;
		$_SESSION["saved-request"] = $_REQUEST;
	}

	// Auto save pictures assigned to Award Buckets
	foreach($bucketList as $bucket => $picList) {
		if (is_array($picList) && sizeof($picList) > 0 && isset($awardList[$bucket])) {	// If the bucket is part of Award and Picture List is not empty
			$award_id = $awardList[$bucket];

			// Retrieve existing rankings to be applied back
			$sql  = "SELECT * FROM pic_result ";
			$sql .= " WHERE yearmonth = '$jury_yearmonth' ";
			$sql .= "   AND award_id = '$award_id'";
			$qchk = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
			$rankList = array();
			while ($row = mysqli_fetch_array($qchk))
				$rankList[$row['profile_id'] . "|" . $row['pic_id']] = $row['ranking'];

			// Clear results for Award
			$sql = "DELETE FROM pic_result WHERE yearmonth = '$jury_yearmonth' AND award_id = '$award_id'";
			mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
			// Insert new result rows for each award
			foreach ($picList as $profile_pic_id) {
				// Split Profile ID and Pic ID
				list($profile_id, $pic_id) = explode("|", $profile_pic_id);
				// Find the ranking
				if (isset($_REQUEST["ranking"]) && isset($_REQUEST["ranking"][$profile_pic_id])) 		// If ranking has been assigned use it
					$pic_ranking = $_REQUEST["ranking"][$profile_pic_id];
				else if (isset($rankList[$profile_pic_id]))		// Use previous ranking if available
					$pic_ranking = $rankList[$profile_pic_id];
				else									// Default Ranking is 0
					$pic_ranking = 0;
				$sql  = "INSERT INTO pic_result (yearmonth, award_id, profile_id, pic_id, ranking) ";
				$sql .= "VALUES ('$jury_yearmonth', '$award_id', '$profile_id', '$pic_id', '$pic_ranking')";
				$qchk = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
			}
		}
	}

	$filterText = "";
	$session_filter_text = "";
	$session_bucket = "Full " . $sections . " section";

	// All filtering queries are prepared here
	// All queries filter by section by default
	// Case 1 - 'next' filter is set with a picture id
	// This is also the default query for projector-view
	$filterText = "All Images";
	$session_filter_text = "All Images";
	$main_query  = "SELECT pic.profile_id, pic.pic_id, profile.profile_name, pic.section, pic.title, pic.location, pic.picfile, pic.eseq, ";
	$main_query .= "       entry.entrant_category, pic.print_received, pic.notifications ";
	$main_query .= "  FROM pic, entry, profile ";
	$main_query .= " WHERE pic.yearmonth = '$jury_yearmonth' ";
	$main_query .= "   AND pic.section = '$sections' ";
	$main_query .= "   AND entry.yearmonth = pic.yearmonth ";
	$main_query .= "   AND entry.profile_id = pic.profile_id ";
	$main_query .= "   AND profile.profile_id = pic.profile_id ";
	$main_query .= $entrant_filter;
	$main_query .= " ORDER BY eseq ASC ";
	// }
	// Case 2 - 'title' filter
	if (isset($request['title'])) {
		$title = $request['title'];
		$filterText = "Filter by Title/ID (" .$title . ")";
		$session_filter_text = "Filter by Title/ID (" .$title . ")";
		$main_query  = "SELECT pic.profile_id, pic.pic_id, profile.profile_name, pic.section, pic.title, pic.location, pic.picfile, pic.eseq, ";
		$main_query .= "       entry.entrant_category, pic.print_received, pic.notifications ";
		$main_query .= "  FROM pic, entry, profile ";
		$main_query .= " WHERE pic.yearmonth = '$jury_yearmonth' ";
		$main_query .= "   AND pic.section='$sections' ";
		$main_query .= "   AND (pic.title LIKE '%$title%' ";
		$main_query .= "        OR pic.location LIKE '%$title%' ";
		$main_query .= "        OR pic.eseq LIKE '%$title%') ";
		$main_query .= "   AND entry.yearmonth = pic.yearmonth ";
		$main_query .= "   AND entry.profile_id = pic.profile_id ";
		$main_query .= "   AND profile.profile_id = pic.profile_id ";
		$main_query .= $entrant_filter;
		$main_query .= " ORDER BY eseq ASC ";
	}
	// Case 3 - 'score' filter
	if (isset($request['score'])) {
		$score = $request['score'];
		if ($score == "inconsistent") {
			// Find pictures with difference of more than 2 marks between minimum and maximum
			$filterText = "Filtered for ratings with a difference of 2 or more";
			$session_filter_text = "Filtered for ratings with a difference of 2 or more";
			$main_query  = "SELECT pic.profile_id, pic.pic_id, profile.profile_name, pic.section, pic.title, pic.location, pic.picfile, pic.eseq, ";
			$main_query .= "       entry.entrant_category, pic.print_received, pic.notifications, MIN(rating.rating) AS min_score, MAX(rating.rating) AS max_score ";
			$main_query .= "  FROM pic, entry, profile, rating ";
			$main_query .= " WHERE pic.yearmonth = '$jury_yearmonth' ";
			$main_query .= "   AND pic.section='$sections' ";
			$main_query .= "   AND entry.yearmonth = pic.yearmonth ";
			$main_query .= "   AND entry.profile_id = pic.profile_id ";
			$main_query .= "   AND profile.profile_id = pic.profile_id ";
			$main_query .= "   AND rating.yearmonth = pic.yearmonth ";
			$main_query .= "   AND rating.profile_id = pic.profile_id ";
			$main_query .= "   AND rating.pic_id = pic.pic_id ";
			$main_query .= $entrant_filter;
			$main_query .= " GROUP BY pic.profile_id, pic.pic_id ";
			$main_query .= " HAVING (max_score - min_score) > 2 ";
			$main_query .= " ORDER BY eseq ASC ";
		}
		else if ($score == "tagged") {
			// Find pictures with a total score equal to the score entered
			$score = trim($score);
			$filterText = "Tagged Images";
			$session_filter_text = "Tagged Images";
			$main_query  = "SELECT pic.profile_id, pic.pic_id, profile.profile_name, pic.section, pic.title, pic.location, pic.picfile, pic.eseq, ";
			$main_query .= "       entry.entrant_category, pic.print_received, pic.notifications, SUM(rating) AS total_score, IFNULL(GROUP_CONCAT(rating.tags), '') AS all_tags ";
			$main_query .= "  FROM pic, entry, profile, rating ";
			$main_query .= " WHERE pic.yearmonth = '$jury_yearmonth' ";
			$main_query .= "   AND pic.section = '$sections' ";
			$main_query .= "   AND entry.yearmonth = pic.yearmonth ";
			$main_query .= "   AND entry.profile_id = pic.profile_id ";
			$main_query .= "   AND profile.profile_id = pic.profile_id ";
			$main_query .= "   AND rating.yearmonth = pic.yearmonth ";
			$main_query .= "   AND rating.profile_id = pic.profile_id ";
			$main_query .= "   AND rating.pic_id = pic.pic_id ";
			$main_query .= $entrant_filter;
			$main_query .= " GROUP BY pic.profile_id, pic.pic_id ";
			$main_query .= " HAVING all_tags > '' ";
			$main_query .= " ORDER BY eseq ASC ";
		}
		else {
			// Find pictures with a total score equal to the score entered
			$score = trim($score);
			$filterText = "Filter by Score " . $score;
			$session_filter_text = "Filter by Score " . $score;
			$main_query  = "SELECT pic.profile_id, pic.pic_id, profile.profile_name, pic.section, pic.title, pic.location, pic.picfile, pic.eseq, ";
			$main_query .= "       entry.entrant_category, pic.print_received, pic.notifications, SUM(rating) AS total_score ";
			$main_query .= " FROM pic, entry, profile, rating ";
			$main_query .= " WHERE pic.yearmonth = '$jury_yearmonth' ";
			$main_query .= "   AND pic.section = '$sections' ";
			$main_query .= "   AND entry.yearmonth = pic.yearmonth ";
			$main_query .= "   AND entry.profile_id = pic.profile_id ";
			$main_query .= "   AND profile.profile_id = pic.profile_id ";
			$main_query .= "   AND rating.yearmonth = pic.yearmonth ";
			$main_query .= "   AND rating.profile_id = pic.profile_id ";
			$main_query .= "   AND rating.pic_id = pic.pic_id ";
			$main_query .= $entrant_filter;
			$main_query .= " GROUP BY pic.profile_id, pic.pic_id ";
			if (substr($score, -1) == "+") {
				$score = substr($score, 0, strlen($score) - 1);
				$main_query .= " HAVING total_score >= $score ";
			}
			else
				$main_query .= " HAVING total_score = $score ";
			$main_query .= " ORDER BY eseq ASC ";
		}
	}

	// Case 4 - Unscored
	if (isset($request['unscored'])) {
		$filterText = "Pictures with no/partial rating";
		$session_filter_text = "Pictures with no/partial rating";

		// Pic pictures with less number of scores
		$main_query  = "SELECT pic.profile_id, pic.pic_id, profile.profile_name, pic.section, pic.title, pic.location, pic.picfile, pic.eseq, ";
		$main_query .= "       entry.entrant_category, pic.print_received, pic.notifications, IFNULL(COUNT(rating.rating), 0) AS num_scored ";
		$main_query .= "  FROM entry, profile, pic ";
		$main_query .= "  LEFT JOIN rating ";
		$main_query .= "    ON rating.yearmonth = pic.yearmonth ";
		$main_query .= "   AND rating.profile_id = pic.profile_id ";
		$main_query .= "   AND rating.pic_id = pic.pic_id ";
		$main_query .= " WHERE pic.yearmonth = '$jury_yearmonth' ";
		$main_query .= "   AND pic.section = '$sections' ";
		$main_query .= "   AND entry.yearmonth = pic.yearmonth ";
		$main_query .= "   AND entry.profile_id = pic.profile_id ";
		$main_query .= "   AND profile.profile_id = entry.profile_id ";
		$main_query .= $entrant_filter;
		$main_query .= " GROUP BY pic.profile_id, pic.pic_id, profile.profile_name, pic.section, pic.title, pic.location, pic.picfile, pic.eseq, ";
		$main_query .= "          entry.entrant_category, pic.print_received, pic.notifications ";
		$main_query .= "HAVING num_scored < '$num_juries' ";
		$main_query .= " ORDER BY eseq ASC ";
	}

	// Case 5 - View Assigned
	if (isset($request['view-assign'])) {
		$filterText = "For the bucket " . $request['selected_bucket'];
		$session_filter_text = "All Images";
		$session_bucket = "For " . $request['selected_bucket'];
		$bucketList["current_list"] = $request['selected_bucket'];
		$currentBucket = $bucketList["current_list"];
		if (isset($awardList[$currentBucket])) { // If this relates to Awards - order by ranking assigned
			$main_query = "SELECT pic.profile_id, pic.pic_id, profile.profile_name, pic.section, pic.title, pic.location, pic.picfile, pic.eseq, ";
			$main_query .= "      entry.entrant_category, pic_result.ranking, pic.print_received, pic.notifications ";
			$main_query .= " FROM pic, entry, profile, pic_result ";
			$main_query .= " WHERE pic.yearmonth = '$jury_yearmonth' ";
			$main_query .= "   AND entry.yearmonth = pic.yearmonth ";
			$main_query .= "   AND entry.profile_id = pic.profile_id ";
			$main_query .= "   AND profile.profile_id = pic.profile_id ";
			$main_query .= "   AND pic_result.yearmonth = pic.yearmonth ";
			$main_query .= "   AND pic_result.award_id = '" . $awardList[$currentBucket] . "' ";
			$main_query .= "   AND pic_result.profile_id = pic.profile_id ";
			$main_query .= "   AND pic_result.pic_id = pic.pic_id ";
			$main_query .= " ORDER BY pic_result.ranking, pic.pic_id ";
		}
		else {
			$picList = $bucketList[$currentBucket];
			$main_query  = "SELECT pic.profile_id, pic.pic_id, profile.profile_name, pic.section, pic.title, pic.location, pic.picfile, pic.eseq, ";
			$main_query .= "       entry.entrant_category, pic.print_received, pic.notifications ";
			$main_query .= " FROM pic, entry, profile ";
			$main_query .= " WHERE pic.yearmonth = '$jury_yearmonth' ";
			$main_query .= "   AND CONCAT_WS('|', pic.profile_id, pic.pic_id) IN (";
			if (count($picList) > 0)
				$main_query .= implode(",", array_add_quotes($picList));
			else
				$main_query .= "'0|0'";    // Nonexistent profile_id | picture id
			$main_query .= ") ";
			$main_query .= "   AND entry.yearmonth = pic.yearmonth ";
			$main_query .= "   AND entry.profile_id = pic.profile_id ";
			$main_query .= "   AND profile.profile_id = pic.profile_id ";
			$main_query .= " ORDER BY eseq ASC ";
		}
	}

	debug_dump("query", $main_query, __FILE__, __LINE__);
	debug_dump("request", $_REQUEST, __FILE__, __LINE__);

	// Set variable to control Thumbnails per row
	if (isset($_REQUEST['tn_per_row'])) {
		// Allow Large Thumbnails only for View Bucket option
		if (isset($_REQUEST['view-assign']))
			$tn_per_row = $_REQUEST['tn_per_row'];
		else
			$tn_per_row = ($_REQUEST['tn_per_row'] >= 4) ? $_REQUEST['tn_per_row'] : 6;
	} else
		$tn_per_row = 6;		// Default thumbnail display per row

	// Save Session for remote judging
	$sql  = "UPDATE jury_session ";
	$sql .= "   SET command_index = command_index + 1 ";
	$sql .= "     , bucket = '$session_bucket' ";
	$sql .= "     , bucket1_list = '" . implode(",", $bucketList['Bucket 1']) . "' ";
	$sql .= "     , bucket2_list = '" . implode(",", $bucketList['Bucket 2']) . "' ";
	$sql .= "     , bucket3_list = '" . implode(",", $bucketList['Bucket 3']) . "' ";
	$sql .= "     , bucket4_list = '" . implode(",", $bucketList['Bucket 4']) . "' ";
	$sql .= "     , bucket5_list = '" . implode(",", $bucketList['Bucket 5']) . "' ";
	$sql .= "     , filter_criteria = '$session_filter_text' ";
	$sql .= "     , filter_sql = '" . mysqli_real_escape_string($DBCON, $main_query) . "' ";
	$sql .= "     , thumbnails_per_row = '$tn_per_row' ";
	$sql .= " WHERE yearmonth = '$jury_yearmonth' ";
	$sql .= "   AND section = '$sections' ";
	$sql .= "   AND award_group = '$award_group' ";
	mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	switch ($tn_per_row) {
		case 6 : 	$tn_classes = 'col-lg-2 col-md-2';
					break;
		case 4 : 	$tn_classes = 'col-lg-3 col-md-3';
					break;
		case 3 :	$tn_classes = 'col-lg-4 col-md-4';
					break;
		case 2 :	$tn_classes = 'col-lg-6 col-md-6';
					break;
		default :	$tn_classes = 'col-lg-2 col-md-2';
					break;
	}

?>
<!DOCTYPE html>
<html>

<head>

    <!-- Page title -->
    <title>Youth Photographic Society | Projection Panel</title>


	<?php include("inc/header.php");?>

    <!-- Vendor styles -->
    <link rel="stylesheet" href="plugin/blueimp-gallery/css/blueimp-gallery.min.css" />


<style type="text/css">
.containerBox {
    position: relative;
    display: inline-block;
}
.text-box {
    position: absolute;
    text-align: center;
    width: 100%;
}
.text-box:before {
   content: '';
   display: inline-block;
   height: 100%;
   vertical-align: middle;
}

h4 {
   display: inline-block;
   font-size: 18px; /*or whatever you want*/
   color: #FFF;
}

img {
  display: block;
  max-width: 100%;
  height: auto;
}

.blueimp-gallery > .title {
	display: none;
	font-size: 12px;
	width: 25%;
}

.blueimp-gallery > .description {
	position: fixed;
	bottom: 0px;
	right: 0px;
	border: 2px solid #808080;
	padding: 2px;
	color: #fff;
	display: none;
}

.blueimp-gallery-controls > .description {
	display: block;
}

.blueimp-gallery-controls > .prev {
	display: none;
}

.blueimp-gallery-controls > .next {
	display: none;
}

.blueimp-gallery-controls > .close {
	display: none;
}

.blueimp-gallery>.slides>.slide>.slide-content {
    margin: auto;
	max-width: 1920px;
    max-height: 1200px;
    opacity: 1;
}

.blinker {
	animation: blinker 2s linear infinite;
}

@keyframes blinker {
  50% {
    opacity: 25%;
  }
}

#exif-display td {
	padding-right : 8px;
}
</style>


</head>
<body class="fixed-navbar fixed-sidebar">

	<!-- Simple splash screen-->

	<div class="splash">
		<div class="color-line"></div>
		<div class="splash-title">
			<h1>    YOUTH PHOTOGRAPHIC SOCIETY | PROJECTION PANEL  </h1>
			<p>Please Wait. </p>
			<div class="spinner">
				<div class="rect1"></div>
				<div class="rect2"></div>
				<div class="rect3"></div>
				<div class="rect4"></div>
				<div class="rect5"></div>
			</div>
		</div>
	</div>

	<!--[if lt IE 7]>
		<p class="alert alert-danger">You are using an <strong>outdated</strong> browser. Please <a href="#">upgrade your browser</a> to improve your experience.</p>
	<![endif]-->

	<!-- Navigation Left Bar -->
	<?php include("inc/projector_sidebar.php");?>

	<!-- Main Wrapper -->
	<div id="wrapper" style="top: 0px;">
		<div class="content">
			<div class="row">
				<div class="col-lg-12 col-md-12 col-sm-12">
					<!-- Filters Row -->
					<div class="well well-sm">
						<div class="row">
						</div>
						<div class="row">
							<!-- Find eseq -->
							<!-- <div class="col-lg-1 col-sm-1 col-md-1">
								<form method="post" action="projector-view.php?sections=<?php echo encode_string_array($sections);?>">
									<input type="hidden" name="tn_per_row" value="<?php echo $tn_per_row;?>" class="tn_per_row">
									<label>By Eseq</label>
									<div class="input-group">
										<input type="text" class="form-control" placeholder="xxxx-xxxx" name="title" >
										<span class="input-group-btn">
											<button class="btn btn-info" type="submit" name="image-search"><i class="glyphicon glyphicon-filter small"></i></button>
										</span>
									</div>
								</form>
							</div> -->
							<!-- Filter by score -->
							<div class="col-lg-2 col-sm-2 col-md-2">
								<form method="post" action="projector-view.php?sections=<?php echo encode_string_array($sections);?>">
									<input type="hidden" name="tn_per_row" value="<?php echo $tn_per_row;?>" class="tn_per_row">	<!-- Will be set in Javascript -->
									<label>By Score[+]</label>
									<div class="input-group">
										<input type="text" class="form-control" placeholder="Total Score" name="score" >
										<span class="input-group-btn">
											<button class="btn btn-info" type="submit" name="image-search"><i class="glyphicon glyphicon-filter small"></i></button>
										</span>
									</div>
								</form>
							</div>

							<!-- Unscored/Partially Scored -->
							<div class="col-lg-1 col-sm-1 col-md-1">
								<form method="post" action="projector-view.php?sections=<?php echo encode_string_array($sections);?>">
									<input type="hidden" name="unscored" value="unscored" >
									<input type="hidden" name="tn_per_row" value="<?php echo $tn_per_row;?>" class="tn_per_row">	<!-- Will be set in Javascript -->
									<label>Unscored (<?= $pics_left;?>)</label>
									<div class="input-group">
										<span class="input-group-btn">
											<button class="btn btn-info" type="submit" name="image-search"><i class="glyphicon glyphicon-filter small"></i> Unscored</button>
										</span>
									</div>
								</form>
							</div>

							<!-- Pictures with wide Variations in scores -->
							<div class="col-lg-1 col-sm-1 col-md-1">
								<form method="post" action="projector-view.php?sections=<?php echo encode_string_array($sections);?>">
									<input type="hidden" name="score" value="inconsistent" >
									<input type="hidden" name="tn_per_row" value="<?php echo $tn_per_row;?>" class="tn_per_row">	<!-- Will be set in Javascript -->
									<label>Ratings</label>
									<div class="input-group">
										<span class="input-group-btn">
											<button class="btn btn-info" type="submit" name="image-search"><i class="glyphicon glyphicon-filter small"></i> Variations</button>
										</span>
									</div>
								</form>
							</div>

							<!-- Tagged Pictures -->
							<!-- <div class="col-lg-1 col-sm-1 col-md-1">
								<form method="post" action="projector-view.php?sections=<?php echo encode_string_array($sections);?>">
									<input type="hidden" name="score" value="tagged" >
									<input type="hidden" name="tn_per_row" value="<?php echo $tn_per_row;?>" class="tn_per_row">
									<label>Tagged</label>
									<div class="input-group">
										<span class="input-group-btn">
											<button class="btn btn-info" type="submit" name="image-search"><i class="glyphicon glyphicon-filter small"></i> Tagged</button>
										</span>
									</div>
								</form>
							</div> -->

							<!-- Filter by Entrant Category -->
							<div class="col-lg-4 col-sm-4 col-md-4" style="border : 0.5px solid #ddd;">
								<form method="post" action="projector-view.php?sections=<?php echo encode_string_array($sections);?>">
									<input type="hidden" name="tn_per_row" value="<?php echo $tn_per_row;?>" class="tn_per_row">
									<p><b>Categories</b></p>
									<div class="input-group">
										<div>
									<?php
										if (isset($_SESSION['sub_categories']))
											$sub_category_list = explode(",", $_SESSION['sub_categories']);
										else
											$sub_category_list = $categories;

										foreach ($categories as $sub_category) {
											$is_checked = in_array($sub_category, $sub_category_list) ? "checked" : "";
									?>
											<label>
												<input type="checkbox" name="sub_categories[]" value="<?= $sub_category;?>" <?= $is_checked;?> >
												<span style="padding-right: 15px;"><?= $sub_category;?></span>
											</label>
									<?php
										}
									?>
										</div>
										<div class="input-group-btn">
											<button class="btn btn-warning btn-sm" type="submit" name="image-search"><i class="glyphicon glyphicon-filter small"></i> Set Filter</button>
										</div>
									</div>
								</form>
							</div>

							<!-- Clear all filters -->
							<div class="col-lg-1 col-sm-1 col-md-1">
								<form method="post" action="projector-view.php?sections=<?php echo encode_string_array($sections);?>">
									<!-- <input type="hidden" name="next" value="0|0" > -->
									<input type="hidden" name="tn_per_row" value="<?php echo $tn_per_row;?>" class="tn_per_row">	<!-- Will be set in Javascript -->
									<label>Reset Filters</label>
									<div class="input-group">
										<span class="input-group-btn">
											<button class="btn btn-info" type="submit" name="clear_filters"> Clear Filters</button>
										</span>
									</div>
								</form>
							</div>
						</div>
					</div>
					<!-- end of Filter Row -->


					<div class="hpanel">
						<div class="panel-body">
							<form method="post" action="projector-view.php?sections=<?php echo encode_string_array($sections);?>" class="form-inline">
								<!-- <input type="hidden" name="sections" value="<?php echo encode_string_array($sections);?>" > -->
								<input type="hidden" name="assign" value="assign">  <!-- to identify this form -->
								<input type="hidden" name="bucket_from" value="<?php echo $bucketList['current_list'];?>" >   <!-- Added to support move from one bucket to a target -->
								<!-- Bucket Panels -->
								<div class="well well-sm">
									<div class="row">
										<div class="col-sm-12">
											<div class="form-group">
												<p><b>Zoom Level </b></p>
												<select class="form-control input-group-select" name="tn_per_row" id="tn_per_row">
													<option value="6" <?php echo ($tn_per_row == 6) ? "selected" : "";?> >6 per row</option>
													<option value="4" <?php echo ($tn_per_row == 4) ? "selected" : "";?> >4 per row</option>
													<?php
														// Allow larger thumbnails for buckets
														if (isset($_REQUEST['view-assign'])) {
													?>
													<option value="3" <?php echo ($tn_per_row == 3) ? "selected" : "";?> >3 per row</option>
													<option value="2" <?php echo ($tn_per_row == 2) ? "selected" : "";?> >2 per row</option>
													<?php
														}
													?>
												</select>
											</div>
											<div class="form-group" style="padding-left: 15px;">
												<p><b>Select pictures and Assign/Unassign to Bucket or View a Bucket</b></p>
												<select class="form-control input-group-select" placeholder="Select a Bucket" name="selected_bucket" >
												<?php
													foreach($bucketList as $bucket => $value) {
														if($bucket != "section" && $bucket != "current_list" && $bucket != "award_group" ) {
												?>
													<option value="<?php echo $bucket;?>" <?php if($bucket == $bucketList['current_list']) echo 'selected';?>>
														<?php echo $bucket; ?>
													</option>
												<?php
														}
													}
												?>
												</select>
												<div class="input-group">
													<span class="input-group-btn" style="padding-left: 15px;">
														<button class="btn btn-info" type="submit" name="image-assign"><i class="glyphicon glyphicon-copy small"></i> Copy</button>
													</span>
													<span class="input-group-btn" style="padding-left: 15px;">
														<button class="btn btn-info" type="submit" name="image-move"><i class="glyphicon glyphicon-move small"></i> Move</button>
													</span>
													<span class="input-group-btn" style="padding-left: 15px;">
														<button class="btn btn-info" type="submit" name="remove-assign"><i class="glyphicon glyphicon-remove-circle small"></i> Remove</button>
													</span>
													<span class="input-group-btn" style="padding-left: 15px;">
														<button class="btn btn-info" type="submit" name="view-assign"><i class="glyphicon glyphicon-inbox small"></i> View Bucket</button>
													</span>
													<span class="input-group-btn" style="padding-left: 15px;">
														<button class="btn btn-info" type="submit" name="view-section"><i class="glyphicon glyphicon-th small"></i> View Section</button>
													</span>
												</div>
											</div>
										</div>
									</div>
								</div>
								<!-- end of control well -->
								<h4 class="text-info">Showing : <?php echo $filterText; ?></h4>
								<p>
									<a href="#" style="color: blue;" onclick="check_all()">Check ALL</a>
									<a href="#" style="color: red; padding-left: 15px;" onclick="uncheck_all()">Uncheck ALL</a>
									<a href="#" style="color: green; padding-left: 15px;" id="resume_slideshow">Resume Projection</a>
								</p>
								<div class="lightBoxGallery" id="gallery" style="text-align:left">
								<?php
									// var_dump($_REQUEST);
									// var_dump($rankList);
									// var_dump($awardList);
									// var_dump($resultList);
									// var_dump($bucketList);
									// print_r($_SESSION);
									$sql = $main_query;
									// debug_dump($sections . " QUERY START", date_format(date_create(), "H:i:s.u"), __FILE__, __LINE__);
									$pics = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
									// debug_dump($sections . " QUERY END", date_format(date_create(), "H:i:s.u"), __FILE__, __LINE__);
									$max = mysqli_num_rows($pics);
								?>
								<p><b>Number of Pictures Displayed : <?= $max;?></b></p>
								<?php
									$idx = 0;
									while($tr_pics = mysqli_fetch_array($pics) ) {
										$idx++;
										$profile_id = $tr_pics['profile_id'];
										$pic_id = $tr_pics['pic_id'];
										$picfile = $tr_pics['picfile'];
										$pic_section = $tr_pics['section'];
										$pic_alert = $tr_pics['profile_name'] . ' (' .  $profile_id . ')' ;
										$notes = rejection_text($tr_pics['notifications']);
										if (! empty($notes))
											$notes = "*** " . $notes . " ***";

										// list($img_width, $img_height) = getimagesize("../salons/$jury_yearmonth/upload/" . $pic_section . "/" . $picfile);
										$pic_title = "[" . $tr_pics['profile_id'] . "|" . $tr_pics['pic_id'] . "] " . $notes  . " {" . $idx . "/" . $max . "}";
										$data_description = $tr_pics['eseq'] . ": " . $idx . "/" . $max;
										$pic_path = "../salons/" . $jury_yearmonth . "/upload/" . $pic_section . "/" . $picfile;
										$tn_path = "../salons/" . $jury_yearmonth . "/upload/" . $pic_section . "/tn/" . $picfile;
										$tnl_path = "../salons/" . $jury_yearmonth . "/upload/" . $pic_section . "/" . $picfile;	// Larger Thumbnail
								?>
									<div class="<?=$tn_classes;?> containerBox" >
										<input name="checkbox[]" type="checkbox" value="<?=$profile_id . "|" . $pic_id;?>" >
									<?php
										if(isset($_REQUEST['view-assign'])) {		// Display button to view entry details
											if(isset($awardList[$currentBucket])) {		// Show ranking options
												$pic_ranking = $tr_pics["ranking"];
									?>
										<span style="padding-left: 15px;">
											<a href="#" style="margin:0; padding: 0;" onclick="increment('ranking[<?=$profile_id . "|" . $pic_id;?>]')">
												<i class="glyphicon glyphicon-plus small"></i>
											</a>
											<input type="text" size="2" readonly style="text-align: center; border: 0; font-weight: bold;"
													name="ranking[<?=$profile_id . "|" . $pic_id;?>]"
													id="ranking[<?=$profile_id . "|" . $pic_id;?>]"
													value="<?=$pic_ranking;?>"
											/>
											<a href="#" style="margin:0; padding: 0;" onclick="decrement('ranking[<?=$profile_id . "|" . $pic_id;?>]')">
												<i class="glyphicon glyphicon-minus small"></i>
											</a>
										</span>
									<?php
											}
									?>
										<a href="#" style="padding-left: 15px;" onclick="alert('Submitted By: <?=$pic_alert;?> ')"><i class="glyphicon glyphicon-user small"></i></a>
									<?php
										}
									?>
										<a href="Javascript:void(0)" class="pull-right" onclick="showExif('<?= $picfile;?>')"><i class="glyphicon glyphicon-info-sign small"></i></a>
										<div class="col-md-12 col-lg-12 col-xs-12 thumbnail" id="thumbnail-<?=$idx;?>" >
											<a href="<?=$pic_path;?>" title="<?=$pic_title;?>"
													data-description="<?=$data_description;?>" data-gallery="" >
												<img class="lozad" src="img/preview.png"
													 data-src="<?php echo ($tn_per_row >= 4) ? $tn_path : $tnl_path;?>" >
												<!-- <img class="lazy-load" src="img/preview.png"
													 data-src="<?php echo ($tn_per_row >= 4) ? $tn_path : $tnl_path;?>" > -->
												<div class="caption">
													<p>
														<span class="text-info"><b>#<?= $idx;?></b></span> [<b><?=$tr_pics['eseq'];?></b>]
														<span class="blinker text-danger strong"><?=$notes;?></span>
													</p>
												</div>
											</a>
										</div>
									</div>
								<?php
										if ($idx % $tn_per_row == 0) {
								?>
											<div class="clearfix"></div>
								<?php
										}

									}
									// debug_dump($sections . " RENDER END", date_format(date_create(), "H:i:s.u"), __FILE__, __LINE__);
								?>
								</div>
							</form>
						</div>`<!-- panel body -->
					</div>
				</div>
			</div>
		</div>

		<!-- Right sidebar -->
		<!-- Footer-->
<?php
// include("inc/profile_modal.php");
?>
	</div>  <!-- / Main Wrapper -->

	<!-- EXIF Modal -->
	<div class="modal" id="exif-modal" tabindex="-1" role="dialog" >
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">EXIF</h5>
  					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
  					</button>
				</div>
				<div class="modal-body">
					<div class="row">
						<div class="col-sm-4">
							<img style="padding: 15px;" src="#" id="exif-img">
						</div>
						<div class="col-sm-8" id="exif-display">
						</div>
					</div>
				</div>
				<div class="modal-footer">
  					<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</div>

	<!-- The Gallery as lightbox dialog, should be a child element of the document body -->
	<div id="blueimp-gallery" class="blueimp-gallery" data-continuous="false" data-toggle-slideshow-on-space="false" data-full-screen="true" data-stretch-images="false"
														data-close-on-slide-click="false" data-close-on-swipe-up-or-down="false" >
		<div class="slides"></div>
		<h3 class="title"></h3>
		<a class="prev">&laquo;</a>
		<a class="next">&raquo;</a>
		<a class="close">&#42;</a>
		<a class="description" href="#" onclick="ctl_get()"><h3>Refresh Rating</h3></a>
		<!-- <a class="play-pause"></a> -->
		<!-- <ol class="indicator"></ol> -->
	</div>

	<?php
      include("inc/footer.php");
	?>


<!-- Vendor scripts -->
<script src="plugin/blueimp-gallery/js/jquery.blueimp-gallery.min.js"></script>
<script src="plugin/jquery-ui/jquery-ui.min.js"></script>
<script src="plugin/bootstrap-star-rating/js/star-rating.min.js"></script>
<script src="plugin/lozad/lozad.js"></script>

<!-- Initialize lozad -->
<script>
	const observer = lozad();
	observer.observe();
</script>

<!-- Local style for demo purpose -->
<style>

    .lightBoxGallery {
        text-align: center;
    }

    .lightBoxGallery a {
        margin: 5px;
        display: inline-block;
    }

</style>

<!-- Local styel for demo purpose - remove text shadow from original plugin styles -->
<style>
    .rating-container .rating-stars:before {
        text-shadow: none;
    }
</style>

<!-- Handle Spinner -->
<script>
function increment(field) {
	// var rankings = document.getElementsByName('ranking[]');
	// var rankingValue = Number(rankings[index].value);
	var ranking = document.getElementById(field);
	var rankingValue = Number(ranking.value);
	if (rankingValue < 99)
		rankingValue = rankingValue + 1;
	// rankings[index].value = rankingValue;
	ranking.value = rankingValue;
}

function decrement(field) {
	// var rankings = document.getElementsByName('ranking[]');
	// var rankingValue = Number(rankings[index].value);
	var ranking = document.getElementById(field);
	var rankingValue = Number(ranking.value);
	if (rankingValue > 0)
		rankingValue = rankingValue - 1;
	// rankings[index].value = rankingValue;
	ranking.value = rankingValue;
}

</script>

<!-- Display EXIF -->
<script>
let tnLocation = 0;
function showExif(picfile) {

	// Sav current position on page
	// tnLocation = $(elem).get(0).offsetTop;
	tnLocation = window.scrollY;

	// Display Exif Modal
	$("#exif-img").attr("src", "../salons/<?= $jury_yearmonth;?>/upload/<?= $sections;?>/tn/" + picfile);
	$("#exif-display").html("<p class='text-center'>Loading EXIF...</p>");
	$("#exif-modal").modal();

	// Load exif
	$.post("ajax/get_exif.php",
		{picfile},
		function (data, status) {
			if (status == "success") {
				let result = JSON.parse(data);
				if (result.status == "OK") {
					let exif_html = "<table>";
					exif_html += "<tr><td><b>ISO</b></td><td>" + result.exif.iso +"</td></tr>";
					exif_html += "<tr><td><b>Aperture</b></td><td>" + result.exif.aperture +"</td></tr>";
					exif_html += "<tr><td><b>Shutter</b></td><td>" + result.exif.speed +"</td></tr>";
					exif_html += "<tr><td><b>Bias</b></td><td>" + result.exif.bias +"</td></tr>";
					exif_html += "<tr><td><b>Metering</b></td><td>" + result.exif.metering +"</td></tr>";
					exif_html += "<tr><td><b>WB</b></td><td>" + result.exif.white_balance +"</td></tr>";
					exif_html += "<tr><td><b>PGM</b></td><td>" + result.exif.program +"</td></tr>";
					exif_html += "<tr><td><b>Flash</b></td><td>" + result.exif.flash +"</td></tr>";
					exif_html += "<tr><td><b>Focal</b></td><td>" + result.exif.focal_length +"</td></tr>";
					exif_html += "<tr><td><b>Lens</b></td><td>" + result.exif.lens +"</td></tr>";
					exif_html += "<tr><td><b>Camera</b></td><td>" + result.exif.camera +"</td></tr>";
					exif_html += "<tr><td><b>Date</b></td><td>" + result.exif.date +"</td></tr>";
					exif_html += "<tr><td><b>Width</b></td><td>" + result.exif.width +"</td></tr>";
					exif_html += "<tr><td><b>Height</b></td><td>" + result.exif.height +"</td></tr>";
					exif_html += "</table>"
					$("#exif-display").html(exif_html);
				}
				else {
					$("#exif-display").html(result.errmsg);
				}
			}
			else {
				$("#exif-display").html("unable to load EXIF. Cancel and ty again.");
			}
		}
	);
}

// Resyore position on closure of Modal
$("#exif-modal").on("hidden.bs.modal", function(){
	window.scrollTo(0, tnLocation);
});

</script>


<script>
// Keeps tab on ratings being assigned by Juries and displays status
var timeout_var;

// Gallery Index
var galidx = 0;

// call back function for $.post to update the description element with update on rating
function refresh_rating (data, status) {
	if (status == "success") {
		var gallery = $('#blueimp-gallery').data('gallery');		// Get Gallery Object to retrieve data description computed while loading the picture

		// data contains 4 values
		// status - 'SUCCESS' or 'FAIL'
		// errmsg - Error message if status = 'FAIL'
		// notifications - Specific notifications sent to the participant to fix
		// ratings - array of values for each jury assigned to the section
		var result = JSON.parse(data);
		if (result.status == 'SUCCESS') {
			//var desc = result.notifications + " ";
			var desc = "";
			var rt;
			var jury = 0;
			for (x in result.ratings) {
				jury = jury + 1;
				if (result.ratings[x] == 0)
					desc = desc + jury.toString() + "-N  ";
				else if (result.ratings[x] > 0)
					desc = desc + jury.toString() + "-Y  ";
				else
					desc = desc + jury.toString() + "-Y*  ";     // Tagged
			}
			var pos = gallery.list[galidx].getAttribute('data-description');
			var node = gallery.container.find('.description');
			// node.empty();
			// if (desc)
				node[0].innerHTML = " <h3><span style='font-size: 0.6em; opacity: 0.5;'>" + pos + "</span> <span class='blinker'>" + result.notifications + "</span> " + desc + "</h3>";
				// node[0].appendChild(document.createTextNode(desc));
		}
	}
}

// function to get ratings from CTL for screen update
function ctl_get() {
	$.post("ajax/ctl_get.php", {"refresh":"y"}, refresh_rating, "text");	// call php to update the picture ID. It returns ratings passed on to a call back function
}

// function to get ratings from CTL for screen update
function ctl_auto_get() {
	$.post("ajax/ctl_get.php", "", refresh_rating, "text");	// call php to update the picture ID. It returns ratings passed on to a call back function
	timeout_var = setTimeout(ctl_auto_get, 2000);
}


// function added by Murali to update ctl table
function ctl_update(index) {
	// alert("slide change");
	galidx = index;
	// Update the link in Resume Slideshow
	$("#resume_slideshow").attr("href", "#thumbnail-" + index);
	var gallery = $('#blueimp-gallery').data('gallery');		// Get Gallery Object
	var text = gallery.list[index].getAttribute('title');		// Get title text
	var start = text.indexOf("[") + 1;
	var end = text.indexOf("]");
	var pic_str = text.substring(start, end);
	var sep = pic_str.indexOf("|");
	var profile_id = pic_str.substring(0, sep);
	var pic_id = pic_str.substring(sep + 1);						// Title starts with [nnnnn] containing the picture id
	console.log("Profile: " + profile_id + "  Pic:" + pic_id);
    if (pic_id) {
        $.post("ajax/ctl_set_current.php",
				{jury_yearmonth : <?=$jury_yearmonth;?>, profile_id : profile_id, pic_id : pic_id},
				refresh_rating, "text");	// call php to update the picture ID. It returns ratings passed on to a call back function
    }
	clearTimeout(timeout_var);		// Remove any timeouts in progress
	timeout_var = setTimeout(ctl_auto_get, 2000);
}


// function added by Murali to update ctl table
function resume_slideshow() {
	var gallery = $('#blueimp-gallery');		// Get Gallery Object
	gallery.slide(galidx);
	ctl_update(galidx);
}

</script>

<script>
// Handle Check ALL and Uncheck ALL operations
function check_all() {
	$("#gallery :checkbox").each(function () { $(this).prop("checked", true) });
}

function uncheck_all() {
	$("#gallery :checkbox").each(function () { $(this).prop("checked", false) });
}

</script>

<script>
    $(function(){
		$("#blueimp-gallery").on("slideend", function (event, index, slide) {ctl_update(index);});   // Initiate ctl table update on navigation to next slide
		$("#blueimp-gallery").on("close", function (event) {clearTimeout(timeout_var);});		// Reset any timer in progress
    });
</script>
<!-- Lazy Load of Thumbnails -->
<script>
	$(document).ready(function(){
		$("img.lazy-load").each(function() {
			$(this).attr("src", $(this).data("src"));
		});
	});
</script>


<script>
$("#tn_per_row").change(function() {
	var tn_per_row = $(this).val();
	$(".tn_per_row").each(function() {
		$(this).val(tn_per_row);
	});
});

</script>

<script>

	// Install Keyboard handler to change description color between Black and White
	$(document).ready(function(){
		$(document).keydown(function(e) {
			if(e.keyCode == 66) { 	// B Key pressed to change text to black
				//$(".blueimp-gallery > .description").css("border", "2px solid #202020");
				$(".blueimp-gallery > .description").css("color", "#000");
			}
			if(e.keyCode == 67) { 	// C Key pressed to change text to cyan
				//$(".blueimp-gallery > .description").css("border", "2px solid #202020");
				$(".blueimp-gallery > .description").css("color", "#0ff");
			}
			if(e.keyCode == 87) { 	// W Key pressed to change text to white
				//$(".blueimp-gallery > .description").css("border", "2px solid #a0a0a0");
				$(".blueimp-gallery > .description").css("color", "#fff");
			}
		});
		// Load the Click Sound
		// click_sound = new Audio("/jurypanel/img/button-16.wav");
	});
</script>


</body>
</html>
<?php
}
else
{
	// Go back to login screen in case of unauthorized access
	$_SESSION['err_msg'] = "Invalid Request";
	header("Location: " . http_method() . $_SERVER['SERVER_NAME'] . "/jurypanel/index.php");
	printf("<script>location.href='" . http_method() . $_SERVER['SERVER_NAME'] . "/jurypanel/index.php'</script>");
}

?>
