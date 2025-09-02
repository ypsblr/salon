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

if(isset($_SESSION['jury_id']) && $_SESSION['jury_type']=="PROJECTOR"  && isset($_SESSION['jury_yearmonth']) ) {

	$jury_yearmonth = $_SESSION['jury_yearmonth'];

	// Make an array of rejection reasons
	$sql  = "SELECT template_code, template_name FROM email_template WHERE template_type = 'user_notification' AND will_cause_rejection = '1' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$rejection_reasons = [];
	while ($row = mysqli_fetch_array($query))
		$rejection_reasons[$row['template_code']] = $row['template_name'];

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

	// Calculate Maximum Number of Pictures
	$sql  = "SELECT COUNT(*) AS num_pics FROM pic, entry ";
	$sql .= " WHERE pic.yearmonth = '$jury_yearmonth' ";
	$sql .= "   AND pic.section = '$sections' ";
	$sql .= "   AND entry.yearmonth = pic.yearmonth ";
	$sql .= "   AND entry.profile_id = pic.profile_id ";
	$sql .= $entrant_filter;
	$qchk = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$row = mysqli_fetch_array($qchk);
	$totalNumPics = $row['num_pics'];

	// Generate a List of Sections
	$sql = "SELECT * FROM section WHERE yearmonth = '$jury_yearmonth' ";
	$sql = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$sectionList = array();
	for ($idx = 0; $row = mysqli_fetch_array($sql); ++ $idx)
		$sectionList[$idx] = $row['section'];
	$numSections = $idx;

	// Get Award List
	$sql = "SELECT * FROM award WHERE yearmonth = '$jury_yearmonth' AND award_type = 'pic' AND level = 99 AND section = '$sections' ";
	if (isset($_SESSION['award_group']) && $_SESSION['award_group'] != 'NO_FILTER')
		$sql .= "    AND award_group = '" . $_SESSION['award_group'] . "' ";
	$qchk = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$awardList = array();
	while ($row = mysqli_fetch_array($qchk)) {
		$awardName = $row["award_name"] . "-" . $row["number_of_awards"];
		$awardList[$awardName] = $row["award_id"];
	}

	// Get Result List
	$sql  = "SELECT * FROM pic_result, award ";
	$sql .= " WHERE pic_result.yearmonth = '$jury_yearmonth' ";
	$sql .= "   AND award.yearmonth = pic_result.yearmonth ";
	$sql .= "   AND award.award_id = pic_result.award_id ";
	$sql .= "   AND award.section = '$sections' ";
	$qchk = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$resultList = array();
	$awardedPics = array();		// simple array of pic_id for use in SQL queries
	$num_awards_in_group = 0;
	while ($row = mysqli_fetch_array($qchk)) {
		$awardedPics[] = $row["profile_id"] . "|" . $row["pic_id"];
		$awardName = $row["award_name"] . "-" . $row["number_of_awards"];
		if ($row['level'] == 99) {
			if(isset($resultList[$awardName]))
				array_push($resultList[$awardName], $row["profile_id"] . "|" . $row["pic_id"]);
			else
				$resultList[$awardName] = array($row["profile_id"] . "|" . $row["pic_id"]);
		}
		if ($_SESSION['award_group'] == "NO_FILTER" || $_SESSION['award_group'] == $row['award_group'] )
			++ $num_awards_in_group;
	}

	if (sizeof($awardedPics) > 0)
		$picFilter = implode(",", array_add_quotes($awardedPics));
	else
		$picFilter = "'0|0'";

	// Generate Bucket List
	if (isset($_SESSION["acc_buckets"]) && $_SESSION["acc_buckets"]["section"] == $sections) {
		// Restore Bucket List from Session Variable
		$bucketList = $_SESSION["acc_buckets"];
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
		foreach($awardList as $award => $award_id) {
			$bucketList[$award] = array();
			if (isset($resultList[$award]))
				$bucketList[$award] = $resultList[$award];
		}
		$_SESSION["acc_buckets"] = $bucketList;
	}

	if (isset($_REQUEST['image-assign'])) {  // Assign Operation
		$bucketList["current_list"] = $_REQUEST['selected_bucket'];
		$currentBucket = $bucketList["current_list"];
		if (isset($_REQUEST['checkbox'])) {	// Empty array causes the entire operation to return NULL
			$bucketList[$currentBucket] = array_unique(array_merge($bucketList[$currentBucket], $_REQUEST['checkbox']));
			if (isset($awardList[$currentBucket])) 	 // If it is one of the Award Buckets, reset Result List as well
				$resultList[$currentBucket] =  $bucketList[$currentBucket];
		}
		$_SESSION["acc_buckets"] = $bucketList;
		$request = $_SESSION["acc-saved-request"];
	}
	else if (isset($_REQUEST['remove-assign'])) {  // Remove from  Bucket Operation
		$bucketList["current_list"] = $_REQUEST['selected_bucket'];
		$currentBucket = $bucketList["current_list"];
		if (isset($_REQUEST['checkbox'])) {
			$bucketList[$currentBucket] = array_diff($bucketList[$currentBucket], $_REQUEST['checkbox']);
			if (isset($awardList[$currentBucket])) 	 // If it is one of the Award Buckets, reset the Result List as well
				$resultList[$currentBucket] =  $bucketList[$currentBucket];
		}
		$_SESSION["acc_buckets"] = $bucketList;
		$request = $_SESSION["acc-saved-request"];
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
		$_SESSION["acc-saved-request"] = $_REQUEST;
	}

	// debug_dump("Bucket List", $bucketList, __FILE__, __LINE__);

	// Auto save pictures assigned to Award Buckets
	foreach($bucketList as $bucket => $picList) {
		if (isset($awardList[$bucket])) {	// If the bucket is part of Award and Picture List is not empty
			$award_id = $awardList[$bucket];
			// Clear results for Award
			$sql = "DELETE FROM pic_result WHERE yearmonth = '$jury_yearmonth' AND award_id = '$award_id'";
			$qchk = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
			// Insert new result rows for each award
			foreach ($picList as $pic_spec) {
				list($profile_id, $pic_id) = explode("|", $pic_spec);
				$sql = "INSERT INTO pic_result (yearmonth, award_id, profile_id, pic_id, ranking) VALUES ('$jury_yearmonth', '$award_id', '$profile_id', '$pic_id', 0)";
				$qchk = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
			}
		}
	}

	$filterText = "";
	// All filtering queries are prepared here
	// All queries filter by section by default

	// Case 1 - 'no filter' filter
	// Default Query
	// if (isset($request['next']) || isset($request['showall'])) {
	$filterText = "All pictures not selected for Awards";
	if (isset($request['next']))
		list($next_profile_id, $next_pic_id) = explode("|", $request['next']);
	else
		$next_profile_id = $next_pic_id = 0;
	$main_query  = "SELECT pic.profile_id, pic.pic_id, profile.profile_name, pic.section, pic.title, pic.location, pic.picfile, pic.eseq, ";
	$main_query .= "       entry.entrant_category, pic.print_received, pic.notifications, pic.modified_date, ";
	$main_query .= "       SUM(rating.rating) AS total_score, MAX(rating.rating) AS max_score, MIN(rating.rating) AS min_score, ";
	$main_query .= "       GROUP_CONCAT(DISTINCT rating.tags SEPARATOR '|') AS jury_notifications ";
	$main_query .= "  FROM pic, entry, profile, rating ";
	$main_query .= " WHERE pic.yearmonth = '$jury_yearmonth' ";
	$main_query .= "   AND pic.section='$sections' ";
	$main_query .= "   AND CONCAT_WS('|', pic.profile_id, pic.pic_id) NOT IN ($picFilter) ";
	$main_query .= "   AND (pic.profile_id >= '$next_profile_id' OR (pic.profile_id = '$next_profile_id' AND pic.pic_id >= '$next_pic_id') ) ";
	$main_query .= "   AND entry.yearmonth = pic.yearmonth ";
	$main_query .= "   AND entry.profile_id = pic.profile_id ";
	$main_query .= "   AND profile.profile_id = pic.profile_id ";
	$main_query .= "   AND rating.yearmonth = pic.yearmonth ";
	$main_query .= "   AND rating.profile_id = pic.profile_id ";
	$main_query .= "   AND rating.pic_id = pic.pic_id ";
	$main_query .= $entrant_filter;
	$main_query .= " GROUP BY pic.profile_id, pic.pic_id ";
	$main_query .= " ORDER BY total_score DESC, modified_date ASC ";
	//}

	// Case 2 - 'limit' filter
	if (isset($request['limit'])) {
		$limit = $request['limit'];
		$filterText = "Limit selection to (" .$limit . ") of pictures uploaded";
		if (substr($limit, -1) == "%")
			$limitSQL = " LIMIT " . (round($totalNumPics * substr($limit, 0, strlen($limit)-1) / 100) - $num_awards_in_group) . " ";
		else
			$limitSQL = " LIMIT " . ($limit  - $num_awards_in_group) . " ";

		$main_query  = "SELECT pic.profile_id, pic.pic_id, profile.profile_name, pic.section, pic.title, pic.location, pic.picfile, pic.eseq, ";
		$main_query .= "       entry.entrant_category, pic.print_received, pic.notifications, pic.modified_date, ";
		$main_query .= "       SUM(rating.rating) AS total_score, MAX(rating.rating) AS max_score, MIN(rating.rating) AS min_score, ";
		$main_query .= "       GROUP_CONCAT(DISTINCT rating.tags SEPARATOR '|') AS jury_notifications ";
		$main_query .= "  FROM pic, entry, profile, rating ";
		$main_query .= " WHERE pic.yearmonth = '$jury_yearmonth' ";
		$main_query .= "   AND pic.section='$sections' ";
		$main_query .= "   AND CONCAT_WS('|', pic.profile_id, pic.pic_id) NOT IN ($picFilter) ";
		$main_query .= "   AND entry.yearmonth = pic.yearmonth ";
		$main_query .= "   AND entry.profile_id = pic.profile_id ";
		$main_query .= "   AND profile.profile_id = pic.profile_id ";
		$main_query .= "   AND rating.yearmonth = pic.yearmonth ";
		$main_query .= "   AND rating.profile_id = pic.profile_id ";
		$main_query .= "   AND rating.pic_id = pic.pic_id ";
		$main_query .= $entrant_filter;
		$main_query .= " GROUP BY pic.profile_id, pic.pic_id ";
		$main_query .= " ORDER BY total_score DESC, modified_date ASC ";
		$main_query .= $limitSQL;
	}

	// Case 3 - 'score' filter
	if (isset($request['score'])) {
		$score = $request['score'];
		// Find pictures with a total score equal to the score entered
		$score = trim($score);
		// $cut_off = $request['cut-off'];
		$filterText = "Filter by Score " . $score ;

		$main_query  = "SELECT pic.profile_id, pic.pic_id, profile.profile_name, pic.section, pic.title, pic.location, pic.picfile, pic.eseq, ";
		$main_query .= "       entry.entrant_category, pic.print_received, pic.notifications, MAX(pic.modified_date) AS modified_date, ";
		$main_query .= "       SUM(rating.rating) AS total_score, MAX(rating.rating) AS max_score, MIN(rating.rating) AS min_score, ";
		$main_query .= "       GROUP_CONCAT(DISTINCT rating.tags SEPARATOR '|') AS jury_notifications ";
		$main_query .= "  FROM pic, entry, profile, rating ";
		$main_query .= " WHERE pic.yearmonth = '$jury_yearmonth' ";
		$main_query .= "   AND pic.section='$sections' ";
		$main_query .= "   AND CONCAT_WS('|', pic.profile_id, pic.pic_id) NOT IN ($picFilter) ";
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
		$main_query .= " ORDER BY total_score DESC, modified_date ASC ";
	}

	// Case4 - Minimum Individual Rating filter
	if (isset($request['minimum_score'])) {
		$score = $request['minimum_score'];
		// Find pictures with a total score equal to the score entered
		$score = trim($score);
		$cut_off = $request['cut-off'];
		$filterText = "Filter by Minimum Individual Rating " . $score;

		$main_query  = "SELECT pic.profile_id, pic.pic_id, profile.profile_name, pic.section, pic.title, pic.location, pic.picfile, pic.eseq, ";
		$main_query .= "       entry.entrant_category, pic.print_received, pic.notifications, pic.modified_date, MAX(pic.modified_date) AS modified_date, ";
		$main_query .= "       SUM(rating.rating) AS total_score, MAX(rating.rating) AS max_score, MIN(rating.rating) AS min_score, ";
		$main_query .= "       GROUP_CONCAT(DISTINCT rating.tags SEPARATOR '|') AS jury_notifications ";
		$main_query .= "  FROM pic, entry, profile, rating ";
		$main_query .= " WHERE pic.yearmonth = '$jury_yearmonth' ";
		$main_query .= "   AND pic.section='$sections' ";
		$main_query .= "   AND CONCAT_WS('|', pic.profile_id, pic.pic_id) NOT IN ($picFilter) ";
		$main_query .= "   AND entry.yearmonth = pic.yearmonth ";
		$main_query .= "   AND entry.profile_id = pic.profile_id ";
		$main_query .= "   AND profile.profile_id = pic.profile_id ";
		$main_query .= "   AND rating.yearmonth = pic.yearmonth ";
		$main_query .= "   AND rating.profile_id = pic.profile_id ";
		$main_query .= "   AND rating.pic_id = pic.pic_id ";
		$main_query .= $entrant_filter;
		$main_query .= " GROUP BY pic.profile_id, pic.pic_id ";
		$main_query .= " HAVING min_score >= $score ";
		$main_query .= " ORDER BY total_score DESC, modified_date ASC ";
	}

	// Case 5 - View Assigned
	if (isset($request['view-assign'])) {
		$filterText = "By selected Bucket/Award";
		$bucketList["current_list"] = $request['selected_bucket'];
		$currentBucket = $bucketList["current_list"];

		if (isset($awardList[$currentBucket])) {
			$main_query  = "SELECT pic.profile_id, pic.pic_id, profile.profile_name, pic.section, pic.title, pic.location, pic.picfile, pic.eseq, ";
			$main_query .= "       entry.entrant_category, pic.print_received, pic.notifications, pic.modified_date, ";
			$main_query .= "       SUM(rating.rating) AS total_score, MAX(rating.rating) AS max_score, MIN(rating.rating) AS min_score, ";
			$main_query .= "       GROUP_CONCAT(DISTINCT rating.tags SEPARATOR '|') AS jury_notifications ";
			$main_query .= "  FROM pic, entry, profile, rating, pic_result ";
			$main_query .= " WHERE pic.yearmonth = '$jury_yearmonth' ";
			$main_query .= "   AND pic.section='$sections' ";
			//$main_query .= "   AND CONCAT_WS('|', pic.profile_id, pic.pic_id) NOT IN ($picFilter) ";
			$main_query .= "   AND entry.yearmonth = pic.yearmonth ";
			$main_query .= "   AND entry.profile_id = pic.profile_id ";
			$main_query .= "   AND profile.profile_id = pic.profile_id ";
			$main_query .= "   AND rating.yearmonth = pic.yearmonth ";
			$main_query .= "   AND rating.profile_id = pic.profile_id ";
			$main_query .= "   AND rating.pic_id = pic.pic_id ";
			$main_query .= "   AND pic_result.yearmonth = pic.yearmonth ";
			$main_query .= "   AND pic_result.award_id = '" .  $awardList[$currentBucket] . "' ";
			$main_query .= "   AND pic_result.profile_id = pic.profile_id ";
			$main_query .= "   AND pic_result.pic_id = pic.pic_id ";
			$main_query .= $entrant_filter;
			$main_query .= " GROUP BY pic.profile_id, pic.pic_id ";
			$main_query .= " ORDER BY total_score DESC, modified_date ASC ";
		}
		else {
			$picList = $bucketList[$currentBucket];
			$main_query  = "SELECT pic.profile_id, pic.pic_id, profile.profile_name, pic.section, pic.title, pic.location, pic.picfile, pic.eseq, ";
			$main_query .= "       entry.entrant_category, pic.print_received, pic.notifications, pic.modified_date, ";
			$main_query .= "       SUM(rating.rating) AS total_score, MAX(rating.rating) AS max_score, MIN(rating.rating) AS min_score, ";
			$main_query .= "       GROUP_CONCAT(DISTINCT rating.tags SEPARATOR '|') AS jury_notifications ";
			$main_query .= "  FROM pic, entry, profile, rating ";
			$main_query .= " WHERE pic.yearmonth = '$jury_yearmonth' ";
			$main_query .= "   AND pic.section='$sections' ";
			// $main_query .= "   AND CONCAT_WS('|', pic.profile_id, pic.pic_id) NOT IN ($picFilter) ";
			$main_query .= "   AND CONCAT_WS('|', pic.profile_id, pic.pic_id) IN (";
			if (count($picList) > 0)
				$main_query .= implode(",", array_add_quotes($picList));
			else
				$main_query .= "'0|0'";    // Nonexistent picture id
			$main_query .= ") ";
			$main_query .= "   AND entry.yearmonth = pic.yearmonth ";
			$main_query .= "   AND entry.profile_id = pic.profile_id ";
			$main_query .= "   AND profile.profile_id = pic.profile_id ";
			$main_query .= "   AND rating.yearmonth = pic.yearmonth ";
			$main_query .= "   AND rating.profile_id = pic.profile_id ";
			$main_query .= "   AND rating.pic_id = pic.pic_id ";
			$main_query .= $entrant_filter;
			$main_query .= " GROUP BY pic.profile_id, pic.pic_id ";
			$main_query .= " ORDER BY total_score DESC, modified_date ASC ";
		}
	}

	// debug_dump("main_query", $main_query, __FILE__, __LINE__);

	// Set variable to control Thumbnails per row
	if (isset($_REQUEST['tn_per_row']))
		$tn_per_row = $_REQUEST['tn_per_row'];
	else
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
	$pics_left = 0;		// To prevent error message from appearing on sidebar

?>

<!DOCTYPE html>
<html>

<head>

    <!-- Page title -->
    <title>Youth Photographic Society | Projection Panel</title>

	<?php include "inc/header.php";?>
    <!-- Place favicon.ico and apple-touch-icon.png in the root directory -->
    <!--<link rel="shortcut icon" type="image/ico" href="favicon.ico" />-->

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
	font-size: 12px;
	width: 25%;
}

.blueimp-gallery > .description {
	position: fixed;
	bottom: 0px;
	right: 0px;
	border: 2px solid #a0a0a0;
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


</style>


</head>
<body class="fixed-navbar fixed-sidebar">

	<!-- Simple splash screen-->
	<div class="splash">
		<div class="color-line"></div>
		<div class="splash-title">
			<h1>    YOUTH PHOTOGRAPHIC SOCIETY | ACCEPTANCE PANEL  </h1>
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

	<!-- Header -->
	<?php include "inc/projector_sidebar.php";?>
	<!-- Main Wrapper -->
	<div id="wrapper">
		<div class="content">
			<div class="row">
				<div class="col-lg-12 col-md-12 col-sm-12">
					<!-- Filters Row -->
            		<div class="row">
						<div class="col-lg-2 col-sm-2 col-md-2">
							<form method="post" action="projector_acc.php?sections=<?php echo encode_string_array($sections);?>">
								<input type="hidden" name="tn_per_row" value="<?php echo $tn_per_row;?>" class="tn_per_row">	<!-- Will be set in Javascript -->
								<label>Limit To (Count or %)</label>
								<div class="input-group">
									<input type="text" class="form-control" placeholder="say 25%" name="limit" >
									<span class="input-group-btn">
										<button class="btn btn-info form-control" type="submit" name="limit-to-count"><i class="glyphicon glyphicon-filter small"></i></button>
									</span>
								</div>
							</form>
						</div>

						<div class="col-lg-2 col-sm-2 col-md-2">
							<form method="post" action="projector_acc.php?sections=<?php echo encode_string_array($sections);?>">
								<input type="hidden" name="tn_per_row" value="<?php echo $tn_per_row;?>" class="tn_per_row">	<!-- Will be set in Javascript -->
								<label>By Total Score[+]</label>
								<div class="input-group">
									<input type="text" class="form-control" placeholder="Score" name="score" value="<?php echo isset($request['score']) ? $request['score'] : "";?>" >
									<div class="input-group-btn">
										<button class="btn btn-info form-control" type="submit" name="by-total-score"><i class="glyphicon glyphicon-filter small"></i></button>
									</div>
								</div>
							</form>
						</div>

						<div class="col-lg-2 col-sm-2 col-md-2">
							<form method="post" action="projector_acc.php?sections=<?php echo encode_string_array($sections);?>">
								<input type="hidden" name="tn_per_row" value="<?php echo $tn_per_row;?>" class="tn_per_row">	<!-- Will be set in Javascript -->
								<label>By Minimum Individual Rating</label>
								<div class="input-group">
									<input type="text" class="form-control" placeholder="Min Score" name="minimum_score" value="<?php echo isset($request['minimum_score']) ? $request['minimum_score'] : "";?>" >
									<div class="input-group-btn">
										<button class="btn btn-info form-control" type="submit" name="by-minimum-individual-rating"><i class="glyphicon glyphicon-filter small"></i></button>
									</div>
								</div>
							</form>
						</div>

						<div class="col-lg-2 col-sm-2 col-md-2">
							<form method="post" action="projector_acc.php?sections=<?php echo encode_string_array($sections);?>">
								<input type="hidden" name="showall" value="0" >
								<input type="hidden" name="tn_per_row" value="<?php echo $tn_per_row;?>" class="tn_per_row">	<!-- Will be set in Javascript -->
								<label>Reset Filters</label>
								<div class="input-group">
									<span class="input-group-btn">
										<button class="btn btn-info" type="submit" name="no-filter">No Filter</button>
									</span>
								</div>
							</form>
						</div>
					</div>
					<!-- end of Filter Row -->


					<div class="hpanel">
						<h4 class="text text-danger">Filter Criteria : <?php echo $filterText; ?></h4>
						<p><b>Select pictures and Assign/Unassign to Bucket or View a Bucket</b></p>
						<form method="post" action="projector_acc.php?sections=<?php echo encode_string_array($sections);?>" class="form-inline">
							<input type="hidden" name="assign" value="assign">  <!-- to identify this form -->
							<div class="form-group">
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
							</div>
							<div class="input-group">
								<span class="input-group-btn" style="padding-left: 15px;">
									<button class="btn btn-info" type="submit" name="image-assign"><i class="glyphicon glyphicon-ok-circle small"></i> Assign</button>
								</span>
								<span class="input-group-btn" style="padding-left: 15px;">
									<button class="btn btn-info" type="submit" name="remove-assign"><i class="glyphicon glyphicon-ok-circle small"></i> Remove</button>
								</span>
								<span class="input-group-btn" style="padding-left: 15px;">
									<button class="btn btn-info" type="submit" name="view-assign"><i class="glyphicon glyphicon-filter small"></i> View</button>
								</span>
								<!--
								&nbsp;&nbsp;&nbsp;&nbsp;
								<span class="input-group-btn">
									<button class="btn btn-info" type="submit" name="save-award"><i class="glyphicon glyphicon-filter small"></i>Email Result</button>
								</span>
								-->
							</div>
							<div class="form-group">
								<b style="white-space:pre;">                                Set Zoom Level : </b>
								<select class="form-control input-group-select" name="tn_per_row" id="tn_per_row">
									<option value="6" <?php echo ($tn_per_row == 6) ? "selected" : "";?> >6 per row</option>
									<option value="4" <?php echo ($tn_per_row == 4) ? "selected" : "";?> >4 per row</option>
									<option value="3" <?php echo ($tn_per_row == 3) ? "selected" : "";?> >3 per row</option>
									<option value="2" <?php echo ($tn_per_row == 2) ? "selected" : "";?> >2 per row</option>
								</select>
							</div>

							<div class="panel-body">
								<?php
									// var_dump($_REQUEST);
									// var_dump($rankList);
									// var_dump($awardList);
									// var_dump($resultList);
									// var_dump($bucketList);
									// print_r($_SESSION);
									// var_dump($main_query);
									$sql = $main_query;
									$pics = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
									$max = mysqli_num_rows($pics);
									// echo "<p>Showing $max pictures out of $totalNumPics received</p>";
								?>
								<p>
									<a href="#" style="color: blue;" onclick="check_all()">Check ALL</a>
									<a href="#" style="color: red; padding-left: 15px;" onclick="uncheck_all()">Uncheck ALL</a>
									<span style="padding-left: 30px;">
										<span><b>Total Pics: </b><?= $totalNumPics;?></span>
										<span style="padding-left: 15px;"><b>Awarded: </b><?= $num_awards_in_group;?></span>
										<span style="padding-left: 15px;"><b>Filtered: </b><?= $max;?></span>
										<span style="padding-left: 15px;"><b>Rejected: </b><span id="total-rejected">0</span></span>
										<span style="padding-left: 15px;"><b>Total Accepted: </b><span id="total-accepted">0</span></span>
										<span style="padding-left: 15px;"><b>% Accepted: </b><span id="percentage-accepted">0</span>%</span>
									</span>
								</p>

								<div class="lightBoxGallery" id="gallery" style="text-align:left">
								<?php
									$idx = 0;
									$num_rejected = 0;
									while($tr_pics=mysqli_fetch_array($pics)) {
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
												list($notification_date, $notification_code_list) = explode(":", $notification);
												foreach(explode(",", $notification_code_list) as $notification_code) {
													if (isset($rejection_reasons[$notification_code])) {
														$disallow_selection = true;
														$notes .= $rejection_reasons[$notification_code];
													}
												}
											}
										}
										// Check for Jury rejection
										if ($tr_pics['min_score'] == 1 && $notes == "") {
											$notes .= ($notes == "" ? "" : ",") . $tr_pics['jury_notifications'];
											$disallow_selection = true;
										}
										if ($notes != "")
											$notes = "*REJECTED For: " . $notes;
										if ($disallow_selection)
											++ $num_rejected;
										// Cook Up commonly used expressions
										$pic_title = "[" . $tr_pics['profile_id'] . "|" . $tr_pics['pic_id'] . "] " . $notes  . " {Score: " . $tr_pics['total_score'] . "}";
										$data_description = $tr_pics['eseq'] . ": " . $idx . "/" . $max ;
										$pic_path = "../salons/" . $jury_yearmonth . "/upload/" . $pic_section . "/" . $picfile;
										$tn_path = "../salons/" . $jury_yearmonth . "/upload/" . $pic_section . "/tn/" . $picfile;
										$tnl_path = "../salons/" . $jury_yearmonth . "/upload/" . $pic_section . "/" . $picfile;	// Larger Thumbnail

									?>
									<div class="<?=$tn_classes;?> containerBox" >
										<input name="checkbox[]" type="checkbox" class="<?php echo $disallow_selection ? "pic-rejected" : "";?>"
												value="<?=$profile_id . "|" . $pic_id;?>" <?php echo $disallow_selection ? "disabled" : "";?> >
										<span style="padding-left: 8px;">
											SCORE - <b><?=$tr_pics['total_score'];?> </b>( <?=$tr_pics['min_score'];?> - <?=$tr_pics['max_score'];?> )
										</span>
										<a href="#" style="padding-left: 8px;" onclick="alert('Submitted By: <?=$pic_alert;?> ')">
											<i class="glyphicon glyphicon-user small"></i>
										</a>
										<a href="#" style="padding-left: 8px;" onclick="show_score_info('<?= $profile_id;?>', '<?= $pic_id;?>')">
											<i class="glyphicon glyphicon-info-sign small"></i>
										</a>
										<div class="col-md-12 col-lg-12 col-xs-12 thumbnail" id="thumbnail-<?=$idx;?>" >
											<a href="<?=$pic_path;?>" title="<?=$pic_title;?>"
													data-description="<?=$data_description;?>" data-gallery="" >
												<img class="lozad" src="img/preview.png"
													 data-src="<?php echo ($tn_per_row >= 4) ? $tn_path : $tnl_path;?>" >
												<div class="caption">
													<p><b><?=$tr_pics['eseq'];?></b> <?=$notes;?></p>
													<p class="text-muted"><?=$tr_pics['modified_date'];?></p>
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
							</div>
						</form>
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
	<div id="blueimp-gallery" class="blueimp-gallery" data-continuous="false" data-toggle-slideshow-on-space="false">
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

<!-- Lazy Load of Thumbnails -->
<script>
	$(document).ready(function(){
		$("img.lazy-load").each(function() {
			$(this).attr("src", $(this).data("src"));
		});
	});
</script>

<script>
	$(document).ready(function(){
		let total_pics = <?= $totalNumPics;?>;
		let num_awards = <?= $num_awards_in_group;?>;
		let num_filtered = <?= $max;?>;
		let num_rejected = <?= $num_rejected;?>;
		let total_accepted = num_awards + num_filtered - num_rejected;
		$("#total-rejected").html(num_rejected);
		$("#total-accepted").html(total_accepted);
		$("#percentage-accepted").html(Math.round(total_accepted * 100 / total_pics));
	});
</script>

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


<script>
// Keeps tab on ratings being assigned by Juries and displays status
var timeout_var;

// call back function for $.post to update the description element with update on rating
function refresh_rating (data, status) {
	if (status == "success") {
		var gallery = $('#blueimp-gallery').data('gallery');		// Get Gallery Object
		// data contains ratings in 3 digits with colons in between representing ratings by Jury 1 to 3
		var ratings = JSON.parse(data);
		var desc="";
		var rt;
		var jury = 0;
		for (x in ratings) {
			jury = jury + 1;
			if (ratings[x] == 0)
				desc = desc + jury.toString() + "-N  ";
			else if (ratings[x] > 0)
				desc = desc + jury.toString() + "-Y  ";
			else
				desc = desc + jury.toString() + "-Y*  ";     // Tagged
		}
		// var desc = gallery.list[index].getAttribute('data-description');
		var node = gallery.container.find('.description');
		// node.empty();
		if (desc)
			node[0].innerHTML = "<h3>" + desc + "</h3>";
			// node[0].appendChild(document.createTextNode(desc));
	}
}

// function to get ratings from CTL for screen update
function ctl_get() {
	$.post("op/ctl_get.php", {"refresh":"y"}, refresh_rating, "text");	// call php to update the picture ID. It returns ratings passed on to a call back function
}

// function to get ratings from CTL for screen update
function ctl_auto_get() {
	$.post("op/ctl_get.php", "", refresh_rating, "text");	// call php to update the picture ID. It returns ratings passed on to a call back function
	timeout_var = setTimeout(ctl_auto_get, 2000);
}


// function added by Murali to update ctl table
function ctl_update(index) {
	// alert("slide change");
	var gallery = $('#blueimp-gallery').data('gallery');		// Get Gallery Object
	var text = gallery.list[index].getAttribute('title');		// Get title text
	var pic_id = text.substring(1,6);							// Title starts with [nnnnn] containing the picture id
	// alert(pic_id);
    if (pic_id) {
        $.post("op/ctl_set_current.php", {"pic_id":pic_id}, refresh_rating, "text");	// call php to update the picture ID. It returns ratings passed on to a call back function
    }
	clearTimeout(timeout_var);		// Remove any timeouts in progress
	timeout_var = setTimeout(ctl_auto_get, 2000);
}
</script>

<script>
// Handle Check ALL and Uncheck ALL operations
function check_all() {
	// $("#gallery :checkbox").each(function () { $(this).prop("checked", true) });
	$("#gallery :checkbox").prop("checked", true);
	// Uncheck all rejected pictures
	$(".pic-rejected").prop("checked", false);
}

function uncheck_all() {
	$("#gallery :checkbox").each(function () { $(this).prop("checked", false) });
}

</script>

<script>
function show_score_info(profile_id, pic_id) {
	let yearmonth = '<?= $jury_yearmonth;?>';
	$.post("ajax/get_score_info.php",
			{yearmonth, profile_id, pic_id},
			function (data, status) {
				if (status == "success") {
					alert(data);
				}
			},
			"text"
	);	// get alert with info from the server
}
</script>

<script>

//    $(function(){

//		$("#blueimp-gallery").on("slideend", function (event, index, slide) {ctl_update(index);});   // Initiate ctl table update on navigation to next slide
//		$("#blueimp-gallery").on("close", function (event) {clearTimeout(timeout_var);});		// Reset any timer in progress

//    });
</script>

<script>
$("#tn_per_row").change(function() {
	var tn_per_row = $(this).val();
	$(".tn_per_row").each(function() {
		$(this).val(tn_per_row);
	});
});

</script>

</body>
</html>
<?php
}
else
{
// header("Location: ".$_SERVER['HTTP_REFERER']);
// printf("<script>location.href='".$_SERVER['HTTP_REFERER']."'</script>");

	// Go back to login screen in case of unauthorized access
	header("Location: " . http_method() . $_SERVER['SERVER_NAME'] . "/jurypanel/index.php");
	printf("<script>location.href='" . http_method() . $_SERVER['SERVER_NAME'] . "/jurypanel/index.php'</script>");

}

?>
