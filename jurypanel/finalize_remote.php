<?php
//
// Remote Rating Software
//
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

if(isset($_SESSION['jury_id']) && isset($_SESSION['jury_type']) && $_SESSION['jury_type']=="JURY" && isset($_SESSION['jury_yearmonth']) ) {

	$jury_id = $_SESSION['jury_id'];
	$jury_yearmonth = $_SESSION['jury_yearmonth'];

	$sql = "SELECT entrant_category FROM entrant_category WHERE yearmonth = '$jury_yearmonth' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$category_list = array();
	while ($row = mysqli_fetch_array($query))
		$category_list[] = $row['entrant_category'];

	$sectionCount = array();
	if (isset($_SESSION['categories'])) {
		$session_filter = explode(",", $_SESSION['categories']);
		$categories = explode(",", $_SESSION['categories']);
		$entrant_filter = " AND entrant_category IN (" . implode(",", array_add_quotes($categories)) . ") ";
	}
	else {
		$session_filter = explode(",", implode(",", $category_list));
		$entrant_filter = "";
	}

    $sections = $_REQUEST['sections'];
    $sections = str_replace(" ", "", $sections);
    $sections = decode_string_array($sections);

	// Generate a List of Sections
	$sql = "SELECT * FROM section WHERE yearmonth = '$jury_yearmonth' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$sectionList = array();
	for ($idx = 0; $row = mysqli_fetch_array($query); ++ $idx)
		$sectionList[$idx] = $row['section'];
	$numSections = $idx;

	// Generate a List of Jury Assignments
	$sql  = "SELECT * FROM section, assignment ";
	$sql .= " WHERE section.yearmonth = '$jury_yearmonth' ";
	$sql .= "   AND section.section = '$sections'";
	$sql .= "   AND assignment.yearmonth = section.yearmonth ";
	$sql .= "   AND assignment.section = section.section ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$juryList = array();
	for ($idx = 0; $row = mysqli_fetch_array($query); $idx++)
		$juryList[$idx] = $row["user_id"];
	$numJuries = $idx;

	// Prepare Temp table with list of pictures having rating from all juries
	$sql = "TRUNCATE TABLE temp ";
	mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$sql  = "INSERT INTO temp (profile_id, pic_id, rating_count) ";
	$sql .= "SELECT profile_id, pic_id, COUNT(*) AS rc FROM rating ";
	$sql .= " WHERE rating.yearmonth = '$jury_yearmonth' ";
	$sql .= " GROUP BY profile_id, pic_id ";
	$sql .= "HAVING rc = '$numJuries' ";
	mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Get number of pictures left to be scored comparing with Temp Table
	$sql  = "SELECT pic.profile_id, pic.pic_id, IFNULL(temp.rating_count, 0) AS rc FROM entry, pic ";
	$sql .= "  LEFT JOIN temp ON temp.profile_id = pic.profile_id AND temp.pic_id = pic.pic_id ";
	$sql .= " WHERE pic.yearmonth = '$jury_yearmonth' ";
	$sql .= "   AND pic.section = '$sections' ";
	$sql .= "   AND entry.yearmonth = pic.yearmonth ";
	$sql .= "   AND entry.profile_id = pic.profile_id ";
	$sql .= "   AND IFNULL(temp.rating_count, 0) = 0 ";
	$sql .= $entrant_filter;
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$pics_left = mysqli_num_rows($query);

	// Get Award List
	$sql  = "SELECT * FROM award ";
	$sql .= " WHERE yearmonth = '$jury_yearmonth' ";
	$sql .= "   AND award_type = 'pic' ";
	$sql .= "   AND level != 99 ";
	$sql .= "   AND (section = '$sections' OR section = 'CONTEST') ";
	if (isset($_SESSION['award_group']) && $_SESSION['award_group'] != 'NO_FILTER')
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
	// debug_dump("SESSION", $_SESSION, __FILE__, __LINE__);
	if (isset($_SESSION["buckets"]) &&
			$_SESSION["buckets"]["section"] == $sections &&
			$_SESSION["buckets"]["award_group"] == (isset($_SESSION['award_group']) ? $_SESSION['award_group'] : '') ) {
		// Restore Bucket List from Session Variable
		$bucketList = $_SESSION["buckets"];
	}
	else {
		$bucketList = array();
		$bucketList["section"] = $sections;
		if (isset($_SESSION['award_group']))
			$bucketList["award_group"] = $_SESSION['award_group'];
		else
			$bucketList["award_group"] = "";
		$bucketList["current_list"] = "";
		$bucketList["Bucket 1"] = array();
		$bucketList["Bucket 2"] = array();
		$bucketList["Bucket 3"] = array();
		$bucketList["Bucket 4"] = array();
		$bucketList["Bucket 5"] = array();
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
	/******* Now saving of awards is done automatically. Hence this is not used *********
	else if (isset($_REQUEST['save-award'])) {
		foreach($bucketList as $bucket => $picList) {
			if (isset($awardList[$bucket])) {
				$award_id = $awardList[$bucket];
				$qchk = mysql_query("DELETE FROM result WHERE award_id = '$award_id'") or die(mysql_error());
				foreach ($picList as $pic_id)
					$qchk = mysql_query("INSERT INTO result (award_id, pic_id, ranking) VALUES ('$award_id', '$pic_id', '0')") or die(mysql_error());
			}
		}
		$request = $_SESSION["saved-request"];
	}
	**********/
	else {
		$request = $_REQUEST;
		$_SESSION["saved-request"] = $_REQUEST;
	}

	// Auto save pictures assigned to Award Buckets
	foreach($bucketList as $bucket => $picList) {
		if (isset($awardList[$bucket])) {	// If the bucket is part of Award and Picture List is not empty
			$award_id = $awardList[$bucket];
			// Retrieve existing rankings to be applied back if there is no new ranking
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
	// All filtering queries are prepared here
	// All queries filter by section by default
	// Case 1 - 'next' filter is set with a picture id
	// This is also the default query for projector-view
	// if (isset($request['next']) || isset($request['view-section'])) {
	if (isset($request['next']))
		list($next_profile_id, $next_pic_id) = explode("|", $request['next']);	// Start display of 28 pictures from
	else {
		$next_profile_id = 0;
		$next_pic_id = 0;
	}
	$filterText = "No Filter";
	$main_query  = "SELECT pic.profile_id, pic.pic_id, profile.profile_name, pic.section, pic.title, pic.location, pic.picfile, pic.eseq, ";
	$main_query .= "       entry.entrant_category, section.section_type, pic.print_received, pic.notifications ";
	$main_query .= "  FROM pic, entry, profile, section ";
	$main_query .= " WHERE pic.yearmonth = '$jury_yearmonth' ";
	$main_query .= "   AND pic.section='$sections' ";
	$main_query .= "   AND (pic.profile_id >= '$next_profile_id' OR (pic.profile_id = '$next_profile_id' AND pic.pic_id >= '$next_pic_id') ) ";
	$main_query .= "   AND entry.yearmonth = pic.yearmonth ";
	$main_query .= "   AND entry.profile_id = pic.profile_id ";
	$main_query .= "   AND profile.profile_id = pic.profile_id ";
	$main_query .= "   AND section.yearmonth = pic.yearmonth ";
	$main_query .= "   AND section.section = pic.section ";
	$main_query .= $entrant_filter;
	$main_query .= " ORDER BY eseq ASC ";
	// }
	// Case 2 - 'title' filter
	if (isset($request['title'])) {
		$title = $request['title'];
		$filterText = "Filter by Title/ID (" .$title . ")";
		$main_query  = "SELECT pic.profile_id, pic.pic_id, profile.profile_name, pic.section, pic.title, pic.location, pic.picfile, pic.eseq, ";
		$main_query .= "       entry.entrant_category, section.section_type, pic.print_received, pic.notifications ";
		$main_query .= "  FROM pic, entry, profile, section ";
		$main_query .= " WHERE pic.yearmonth = '$jury_yearmonth' ";
		$main_query .= "   AND pic.section='$sections' ";
		$main_query .= "   AND (pic.title LIKE '%$title%' ";
		$main_query .= "        OR pic.location LIKE '%$title%' ";
		$main_query .= "        OR pic.eseq LIKE '%$title%') ";
		$main_query .= "   AND entry.yearmonth = pic.yearmonth ";
		$main_query .= "   AND entry.profile_id = pic.profile_id ";
		$main_query .= "   AND profile.profile_id = pic.profile_id ";
		$main_query .= "   AND section.yearmonth = pic.yearmonth ";
		$main_query .= "   AND section.section = pic.section ";
		$main_query .= $entrant_filter;
		$main_query .= " ORDER BY eseq ASC ";
	}
	// Case 3 - 'score' filter
	if (isset($request['score'])) {
		$score = $request['score'];
		if ($score == "inconsistent") {
			// Find pictures with difference of more than 2 marks between minimum and maximum
			$filterText = "Filtered for ratings with a difference of 2 or more";
			$main_query  = "SELECT pic.profile_id, pic.pic_id, profile.profile_name, pic.section, pic.title, pic.location, pic.picfile, pic.eseq, ";
			$main_query .= "       entry.entrant_category, pic.print_received, section.section_type, pic.notifications, MIN(rating.rating) AS min_score, MAX(rating.rating) AS max_score ";
			$main_query .= "  FROM pic, entry, profile, rating, section ";
			$main_query .= " WHERE pic.yearmonth = '$jury_yearmonth' ";
			$main_query .= "   AND pic.section='$sections' ";
			$main_query .= "   AND entry.yearmonth = pic.yearmonth ";
			$main_query .= "   AND entry.profile_id = pic.profile_id ";
			$main_query .= "   AND profile.profile_id = pic.profile_id ";
			$main_query .= "   AND rating.yearmonth = pic.yearmonth ";
			$main_query .= "   AND rating.profile_id = pic.profile_id ";
			$main_query .= "   AND rating.pic_id = pic.pic_id ";
			$main_query .= "   AND section.yearmonth = pic.yearmonth ";
			$main_query .= "   AND section.section = pic.section ";
			$main_query .= $entrant_filter;
			$main_query .= " GROUP BY pic.profile_id, pic.pic_id ";
			$main_query .= " HAVING (max_score - min_score) > 2 ";
			$main_query .= " ORDER BY eseq ASC ";
		}
		else if ($score == "tagged") {
			// Find pictures with a total score equal to the score entered
			$score = trim($score);
			$filterText = "Tagged Images";
			$main_query  = "SELECT pic.profile_id, pic.pic_id, profile.profile_name, pic.section, pic.title, pic.location, pic.picfile, pic.eseq, ";
			$main_query .= "       entry.entrant_category, pic.print_received, section.section_type, pic.notifications, SUM(rating) AS total_score, IFNULL(GROUP_CONCAT(tags), '') AS all_tags ";
			$main_query .= "  FROM pic, entry, profile, rating, section ";
			$main_query .= " WHERE pic.yearmonth = '$jury_yearmonth' ";
			$main_query .= "   AND pic.section = '$sections' ";
			$main_query .= "   AND entry.yearmonth = pic.yearmonth ";
			$main_query .= "   AND entry.profile_id = pic.profile_id ";
			$main_query .= "   AND profile.profile_id = pic.profile_id ";
			$main_query .= "   AND rating.yearmonth = pic.yearmonth ";
			$main_query .= "   AND rating.profile_id = pic.profile_id ";
			$main_query .= "   AND rating.pic_id = pic.pic_id ";
			$main_query .= "   AND section.yearmonth = pic.yearmonth ";
			$main_query .= "   AND section.section = pic.section ";
			$main_query .= $entrant_filter;
			$main_query .= " GROUP BY pic.profile_id, pic.pic_id ";
			$main_query .= " HAVING all_tags > '' ";
			$main_query .= " ORDER BY eseq ASC ";
		}
		else {
			// Find pictures with a total score equal to the score entered
			$score = trim($score);
			$filterText = "Filter by Score " . $score;
			$main_query  = "SELECT pic.profile_id, pic.pic_id, profile.profile_name, pic.section, pic.title, pic.location, pic.picfile, pic.eseq, ";
			$main_query .= "       entry.entrant_category, pic.print_received, section.section_type, pic.notifications, SUM(rating) AS total_score ";
			$main_query .= " FROM pic, entry, profile, rating, section ";
			$main_query .= " WHERE pic.yearmonth = '$jury_yearmonth' ";
			$main_query .= "   AND pic.section = '$sections' ";
			$main_query .= "   AND entry.yearmonth = pic.yearmonth ";
			$main_query .= "   AND entry.profile_id = pic.profile_id ";
			$main_query .= "   AND profile.profile_id = pic.profile_id ";
			$main_query .= "   AND rating.yearmonth = pic.yearmonth ";
			$main_query .= "   AND rating.profile_id = pic.profile_id ";
			$main_query .= "   AND rating.pic_id = pic.pic_id ";
			$main_query .= "   AND section.yearmonth = pic.yearmonth ";
			$main_query .= "   AND section.section = pic.section ";
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
		$filterText = "Pictures with no rating";
		$main_query  = "SELECT pic.profile_id, pic.pic_id, profile.profile_name, pic.section, pic.title, pic.location, pic.picfile, pic.eseq, ";
		$main_query .= "       entry.entrant_category, section.section_type, pic.print_received, pic.notifications ";
		$main_query .= " FROM pic, entry, profile, section ";
		$main_query .= " WHERE pic.yearmonth = '$jury_yearmonth' ";
		$main_query .= "   AND pic.section = '$sections' ";
		$main_query .= "   AND pic.pic_id NOT IN (SELECT pic_id FROM temp ) ";
		$main_query .= "   AND entry.yearmonth = pic.yearmonth ";
		$main_query .= "   AND entry.profile_id = pic.profile_id ";
		$main_query .= "   AND profile.profile_id = pic.profile_id ";
		$main_query .= "   AND section.yearmonth = pic.yearmonth ";
		$main_query .= "   AND section.section = pic.section ";
		$main_query .= $entrant_filter;
		$main_query .= " ORDER BY eseq ASC ";

	}

	// Case 5 - View Assigned
	if (isset($request['view-assign'])) {
		$filterText = "For the bucket " . $request['selected_bucket'];
		$bucketList["current_list"] = $request['selected_bucket'];
		$currentBucket = $bucketList["current_list"];
		if (isset($awardList[$currentBucket])) { // If this relates to Awards - order by ranking assigned
			$main_query = "SELECT pic.profile_id, pic.pic_id, profile.profile_name, pic.section, pic.title, pic.location, pic.picfile, pic.eseq, ";
			$main_query .= "      entry.entrant_category, section.section_type, pic_result.ranking, pic.print_received, pic.notifications ";
			$main_query .= " FROM pic, entry, profile, pic_result, section ";
			$main_query .= " WHERE pic.yearmonth = '$jury_yearmonth' ";
			$main_query .= "   AND entry.yearmonth = pic.yearmonth ";
			$main_query .= "   AND entry.profile_id = pic.profile_id ";
			$main_query .= "   AND profile.profile_id = pic.profile_id ";
			$main_query .= "   AND pic_result.yearmonth = pic.yearmonth ";
			$main_query .= "   AND pic_result.award_id = '" . $awardList[$currentBucket] . "' ";
			$main_query .= "   AND pic_result.profile_id = pic.profile_id ";
			$main_query .= "   AND pic_result.pic_id = pic.pic_id ";
			$main_query .= "   AND section.yearmonth = pic.yearmonth ";
			$main_query .= "   AND section.section = pic.section ";
			$main_query .= " ORDER BY pic_result.ranking, pic.pic_id ";
		}
		else {
			$picList = $bucketList[$currentBucket];
			$main_query  = "SELECT pic.profile_id, pic.pic_id, profile.profile_name, pic.section, pic.title, pic.location, pic.picfile, pic.eseq, ";
			$main_query .= "       entry.entrant_category, section.section_type, pic.print_received, pic.notifications ";
			$main_query .= " FROM pic, entry, profile, section ";
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
			$main_query .= "   AND section.yearmonth = pic.yearmonth ";
			$main_query .= "   AND section.section = pic.section ";
			$main_query .= " ORDER BY eseq ASC ";
		}
	}

	// Set variable to control Thumbnails per row
	if (isset($_REQUEST['tn_per_row'])) {
		// Allow Large Thumbnails only for View Bucket option
		if (isset($_REQUEST['view-assign']))
			$tn_per_row = $_REQUEST['tn_per_row'];
		else
			$tn_per_row = ($_REQUEST['tn_per_row'] >= 4) ? $_REQUEST['tn_per_row'] : 6;
	} else
		$tn_per_row = 6;		// Default thumbnail display per row

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
	animation: blinker 1s linear infinite;
}

@keyframes blinker {
  50% {
    opacity: 0;
  }
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
	<?php include("inc/remote_sidebar.php");?>

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
							<div class="col-lg-3 col-sm-3 col-md-3">
								<form method="post" action="projector-view.php?sections=<?php echo encode_string_array($sections);?>">
									<input type="hidden" name="tn_per_row" value="<?php echo $tn_per_row;?>" class="tn_per_row">	<!-- Will be set in Javascript -->
									<label>By Title/Eseq</label>
									<div class="input-group">
										<input type="text" class="form-control" placeholder="By Title/ID" name="title" >
										<span class="input-group-btn">
											<button class="btn btn-info" type="submit" name="image-search"><i class="glyphicon glyphicon-filter small"></i></button>
										</span>
									</div>
								</form>
							</div>
							<div class="col-lg-3 col-sm-3 col-md-3">
								<form method="post" action="projector-view.php?sections=<?php echo encode_string_array($sections);?>">
									<input type="hidden" name="tn_per_row" value="<?php echo $tn_per_row;?>" class="tn_per_row">	<!-- Will be set in Javascript -->
									<label>By Score[+]</label>
									<div class="input-group">
										<input type="text" class="form-control" placeholder="By Total Score" name="score" >
										<span class="input-group-btn">
											<button class="btn btn-info" type="submit" name="image-search"><i class="glyphicon glyphicon-filter small"></i></button>
										</span>
									</div>
								</form>
							</div>
							<div class="col-lg-2 col-sm-2 col-md-2">
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

							<div class="col-lg-2 col-sm-2 col-md-2">
								<form method="post" action="projector-view.php?sections=<?php echo encode_string_array($sections);?>">
									<input type="hidden" name="score" value="tagged" >
									<input type="hidden" name="tn_per_row" value="<?php echo $tn_per_row;?>" class="tn_per_row">	<!-- Will be set in Javascript -->
									<label>Tagged</label>
									<div class="input-group">
										<span class="input-group-btn">
											<button class="btn btn-info" type="submit" name="image-search"><i class="glyphicon glyphicon-filter small"></i> Tagged</button>
										</span>
									</div>
								</form>
							</div>
							<div class="col-lg-2 col-sm-2 col-md-2">
								<form method="post" action="projector-view.php?sections=<?php echo encode_string_array($sections);?>">
									<input type="hidden" name="next" value="0|0" >
									<input type="hidden" name="tn_per_row" value="<?php echo $tn_per_row;?>" class="tn_per_row">	<!-- Will be set in Javascript -->
									<label>Reset Filters</label>
									<div class="input-group">
										<span class="input-group-btn">
											<button class="btn btn-info" type="submit" name="image-search">No Filter</button>
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
									<div class="row" style="padding-left: 16;">
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
										<div class="form-group" style="padding-left: 16;">
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
												<!--
												&nbsp;&nbsp;&nbsp;&nbsp;
												<span class="input-group-btn">
													<button class="btn btn-info" type="submit" name="save-award"><i class="glyphicon glyphicon-filter small"></i>Email Result</button>
												</span>
												-->
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
									$sql = $main_query;
									$pics = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
									$max = mysqli_num_rows($pics);
									echo "<p>Number of Pictures: " . $max . "</p>";
									$idx = 0;
									while($tr_pics = mysqli_fetch_array($pics)) {
										$idx++;
										$profile_id = $tr_pics['profile_id'];
										$pic_id = $tr_pics['pic_id'];
										$picfile = $tr_pics['picfile'];
										$pic_section = $tr_pics['section'];
										$pic_alert = $tr_pics['profile_name'] . ' (' .  $tr_pics['profile_id'] . ')' ;

										// Process Notifications
										$notes = "";
										$accepted_in_yps = false;
										$print_not_received = false;
										$violates_rules = false;
										$has_watermark = false;
										$disallow_selection = false;
										$notifications = explode("|", $tr_pics['notifications']);
										foreach ($notifications AS $notification) {
											if ($notification != "") {
												list($notification_date, $notification_code_str) = explode(":", $notification);
												$notification_codes = explode(",", $notification_code_str);
												$accepted_in_yps = in_array("accepted_error", $notification_codes);
												// $print_not_received = ($notification_code == "print_not_received");
												$print_not_received = ($tr_pics['section_type'] == "P" && $tr_pics['print_received'] == 0);
												$violates_rules = in_array("rules_error", $notification_codes);
												$has_watermark = in_array("watermark_error", $notification_codes);
											}
										}
										if ($accepted_in_yps || $print_not_received || $violates_rules || $has_watermark) {
											$disallow_selection = true;
											if ($accepted_in_yps)
												$notes .= "*PA*";
											if ($violates_rules)
												$notes .= "*VR*";
											if ($has_watermark)
												$notes .= "*WM*";
											if ($print_not_received)
												$notes .= "*NR*";
										}

										list($img_width, $img_height) = getimagesize("../salons/$jury_yearmonth/upload/" . $pic_section . "/" . $picfile);
										$pic_title = "[" . $tr_pics['profile_id'] . "|" . $tr_pics['pic_id'] . "] " . $notes  . " {" . $idx . "/" . $max . "}";
										$data_description = $tr_pics['eseq'] . ": " . $idx . "/" . $max . "(" . $img_width . "x" . $img_height . ")";
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
										<div class="col-md-12 col-lg-12 col-xs-12 thumbnail" id="thumbnail-<?=$idx;?>" >
											<a href="<?=$pic_path;?>" title="<?=$pic_title;?>"
											   		data-gallery=""
													data-imgwidth="<?=$img_width;?>" data-imgheight="<?=$img_height;?>" >
												<img src="<?php echo ($tn_per_row >= 4) ? $tn_path : $tnl_path;?>" >
												<div class="caption">
													<p><b><?=$tr_pics['eseq'];?></b> <?=$notes;?></p>
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
include("inc/profile_modal.php");
?>
	</div>  <!-- / Main Wrapper -->

	<!-- The Gallery as lightbox dialog, should be a child element of the document body -->
	<div id="blueimp-gallery" class="blueimp-gallery" data-continuous="false" data-toggle-slideshow-on-space="false" data-full-screen="true" data-stretch-images="false"
														data-close-on-slide-click="false" data-close-on-swipe-up-or-down="false" >
		<div class="slides"></div>
		<h3 class="title"></h3>
		<a class="prev">&laquo;</a>
		<a class="next">&raquo;</a>
		<a class="close">&#42;</a>
		<!-- <a class="description" href="#" onclick="ctl_get()"><h3>Refresh Rating</h3></a> -->
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

</body>
</html>
<?php
}
else
{
	// Go back to login screen in case of unauthorized access
	header("Location: " . http_method() . $_SERVER['SERVER_NAME'] . "/jurypanel/index.php");
	printf("<script>location.href='" . http_method() . $_SERVER['SERVER_NAME'] . "/jurypanel/index.php'</script>");
}

?>
