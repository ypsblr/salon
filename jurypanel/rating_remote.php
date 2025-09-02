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

if ( isset($_SESSION['jury_id']) && isset($_SESSION['jury_type']) && $_SESSION['jury_type']=="JURY" &&
	 isset($_SESSION['jury_yearmonth']) & isset($_REQUEST['show']) ) {

	$jury_id = $_SESSION['jury_id'];
	$jury_yearmonth = $_SESSION['jury_yearmonth'];

	list($jury_section, $award_group) = explode("|", decode_string_array($_REQUEST['show']));
	$_SESSION['section'] = $jury_section;
	$_SESSION['award_group'] = $award_group;

	// Get Contest Data
	$sql = "SELECT * FROM contest WHERE yearmonth = '$jury_yearmonth' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$contest = mysqli_fetch_array($query);
	if ($contest['judging_in_progress'] == '0')
		die_with_error('Contest has not yet been opened for judging !', __FILE__, __LINE__);

	// Sync to Jury Session in progress
	$sql  = "SELECT * FROM jury_session, assignment ";
	$sql .= " WHERE jury_session.yearmonth = '$jury_yearmonth' ";
	$sql .= "   AND jury_session.section = '$jury_section' ";
	$sql .= "   AND jury_session.award_group = '$award_group' ";
	$sql .= "   AND assignment.yearmonth = jury_session.yearmonth ";
	$sql .= "   AND assignment.section = jury_session.section ";
	$sql .= "   AND assignment.user_id = '$jury_id' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_num_rows($query) == 0) {
		die_with_error("Judging for $award_group under $jury_section is yet to open. Contact YPS !", __FILE__, __LINE__);
	}
	$assignment = mysqli_fetch_array($query);
	if ($assignment['session_open'] == '0') {
		die_with_error("Judging for $award_group under $jury_section is yet to open. Contact YPS !", __FILE__, __LINE__);
	}
	if ($assignment['status'] == 'COMPLETED') {
		die_with_error("Judging for $jury_section has been completed. Contact score again !", __FILE__, __LINE__);
	}

	// Set entrant_categories for Award Group
	$sql  = "SELECT entrant_category FROM entrant_category ";
	$sql .= " WHERE yearmonth = '$jury_yearmonth' ";
	$sql .= "   AND award_group = '$award_group' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$categories = array();
	while($row = mysqli_fetch_array($query))
		$categories[] = $row['entrant_category'];
	$_SESSION['categories'] = implode(",", $categories);
	$entrant_filter = " AND entry.entrant_category IN (" . implode(",", array_add_quotes($categories)) . ") ";

	// Get Notifications List
	$sql  = "SELECT template_code, template_name ";
	$sql .= "  FROM email_template, section ";
	$sql .= " WHERE section.yearmonth = '$jury_yearmonth' ";
	$sql .= "   AND section.section = '$jury_section' ";
	$sql .= "   AND	template_type = 'user_notification' ";
	$sql .= "   AND will_cause_rejection = '1' ";
	$sql .= "   AND ( (section.section_type = 'P' AND applies_to_print = '1') OR ";
	$sql .= "         (section.section_type = 'D' AND applies_to_digital = '1') ) ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$rejection_reasons = array();
	while ($row = mysqli_fetch_array($query))
		$rejection_reasons[$row['template_code']] = $row['template_name'];

	// Rating Legends
	$rating_legends = ["", "Reject", "Just OK", "Acceptance", "Certificate", "Award"];

	// Get number of pictures left to be scored by the jury
	// Get Number of Pictures
	$sql  = "SELECT COUNT(*) as num_pics FROM entry, pic ";
	$sql .= " WHERE entry.yearmonth = '$jury_yearmonth' ";
	$sql .= $entrant_filter;
	$sql .= "   AND pic.yearmonth = entry.yearmonth ";
	$sql .= "   AND pic.profile_id = entry.profile_id ";
	$sql .= "   AND pic.section = '$jury_section' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$row = mysqli_fetch_array($query);
	$num_pics = $row['num_pics'];

	// Get number of pictures scored
	$sql  = "SELECT rating.rating, COUNT(*) AS num_scored FROM entry, pic, rating ";
	$sql .= " WHERE entry.yearmonth = '$jury_yearmonth' ";
	$sql .= $entrant_filter;
	$sql .= "   AND pic.yearmonth = entry.yearmonth ";
	$sql .= "   AND pic.profile_id = entry.profile_id ";
	$sql .= "   AND pic.section = '$jury_section' ";
	$sql .= "   AND rating.yearmonth = pic.yearmonth ";
	$sql .= "   AND rating.profile_id = pic.profile_id ";
	$sql .= "   AND rating.pic_id = pic.pic_id ";
	$sql .= "   AND rating.user_id = '$jury_id' ";
	$sql .= " GROUP BY rating.rating ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	$num_pics_scored = 0;
	$scores_list = [];
	for ($i =1; $i <= 5; ++ $i)
			$scores_list[$i] = 0;

	while ($row = mysqli_fetch_array($query)) {
		$scores_list[$row['rating']] = $row['num_scored'];
		$num_pics_scored += $row['num_scored'];
	}
	$pics_left = $num_pics - $num_pics_scored;
	$scores_list[0] = $pics_left;


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

	$main_query  = "SELECT pic.profile_id, pic.pic_id, profile.profile_name, pic.section, pic.title, pic.location, pic.picfile, pic.eseq, ";
	$main_query .= "       pic.no_accept, pic.width, pic.height, pic.exif, ";
	$main_query .= "       entry.entrant_category, section.section_type, pic.print_received, pic.notifications, IFNULL(rating.rating, 0) AS rating, ";
	$main_query .= "       IFNULL(rating.tags, '') AS jury_tags";
	$main_query .= "  FROM entry, profile, section, pic ";
	$main_query .= "  LEFT JOIN rating ON rating.yearmonth = pic.yearmonth AND rating.profile_id = pic.profile_id AND rating.pic_id = pic.pic_id ";
	$main_query .= "                  AND rating.user_id = '$jury_id' ";
	$main_query .= " WHERE pic.yearmonth = '$jury_yearmonth' ";
	$main_query .= "   AND pic.section='$jury_section' ";
	$main_query .= "   AND (pic.profile_id >= '$next_profile_id' OR (pic.profile_id = '$next_profile_id' AND pic.pic_id >= '$next_pic_id') ) ";
	$main_query .= "   AND entry.yearmonth = pic.yearmonth ";
	$main_query .= "   AND entry.profile_id = pic.profile_id ";
	$main_query .= "   AND profile.profile_id = pic.profile_id ";
	$main_query .= "   AND section.yearmonth = pic.yearmonth ";
	$main_query .= "   AND section.section = pic.section ";
	$main_query .= $entrant_filter;
	$main_query .= " ORDER BY eseq ASC ";

	// Case 4 - Unscored
	// if (isset($request['unscored'])) {
	// 	$filterText = "Pictures with no rating";
	// 	$main_query  = "SELECT pic.profile_id, pic.pic_id, profile.profile_name, pic.section, pic.title, pic.location, pic.picfile, pic.eseq, ";
	// 	$main_query .= "       entry.entrant_category, section.section_type, pic.print_received, pic.notifications IFNULL(rating.rating, 0) AS rating, ";
	// 	$main_query .= "       IFNULL(rating.tags, '') AS jury_tags";
	// 	$main_query .= "  FROM entry, profile, section, pic ";
	// 	$main_query .= "  LEFT JOIN rating ON rating.yearmonth = pic.yearmonth AND rating.profile_id = pic.profile_id AND rating.pic_id = pic.pic_id ";
	// 	$main_query .= "                  AND rating.user_id = '$jury_id' ";
	// 	$main_query .= " WHERE pic.yearmonth = '$jury_yearmonth' ";
	// 	$main_query .= "   AND pic.section = '$jury_section' ";
	// 	//$main_query .= "   AND pic.pic_id NOT IN (SELECT pic_id FROM temp ) ";
	// 	$main_query .= "   AND entry.yearmonth = pic.yearmonth ";
	// 	$main_query .= "   AND entry.profile_id = pic.profile_id ";
	// 	$main_query .= "   AND profile.profile_id = pic.profile_id ";
	// 	$main_query .= "   AND section.yearmonth = pic.yearmonth ";
	// 	$main_query .= "   AND section.section = pic.section ";
	// 	$main_query .= "   AND rating = 0 ";
	// 	$main_query .= $entrant_filter;
	// 	$main_query .= " ORDER BY eseq ASC ";
	// }

	$tn_per_row = 6;
	$tn_classes = "col-sm-2";

?>
<!DOCTYPE html>
<html>

<head>

    <!-- Page title -->
    <title>Youth Photographic Society | Remote Rating Panel</title>


	<?php include("inc/header.php");?>

    <!-- Vendor styles -->
    <link rel="stylesheet" href="plugin/blueimp-gallery/css/blueimp-gallery.min.css" />
	<link href="plugin/bootstrap-toggle/css/bootstrap-toggle.min.css" rel="stylesheet">
	<link rel="stylesheet" href="plugin/photo-histogram/photo-histogram.css" />


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

/* .blueimp-gallery > .description {
	position: fixed;
	bottom: 0px;
	right: 0px;
	border: 2px solid #808080;
	padding: 2px;
	color: #fff;
	display: block;
} */

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
    max-height: 1080px;
    opacity: 1;
}

.blueimp-gallery > a.description {
	cursor: text;
}

div.normal-description {
	position: fixed;
	bottom: 0px;
	right: 0px;
	border: 2px solid #808080;
	padding: 2px;
	color: #fff;
	display: block;		/* keep description on */
}

div.confirm-rejection {
	width: 400px;
	background-color: #ddd;
	border: 1px solid #fff;
	color: #222;
	position: fixed;
	bottom: 0px;
	right: 0px;
	padding: 15px;
}

div.override-rejection {
	width: 400px;
	background-color: #ddd;
	border: 1px solid #fff;
	color: #222;
	position: fixed;
	bottom: 0px;
	right: 0px;
	padding: 15px;
}

div.exif-info {
	width: 400px;
	background-color: #ddd;
	border: 1px solid #fff;
	color: #222;
	position: fixed;
	bottom: 0px;
	right: 0px;
	padding: 15px;
}

div.histogram-info {
	width: 400px;
	background-color: #ddd;
	border: 1px solid #fff;
	color: #222;
	position: fixed;
	bottom: 0px;
	right: 0px;
	padding: 15px;
}

#exif-text td {
	padding-right : 8px;
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

<!-- checkbox styles -->
<style>
	.btn-ckb-show-hide-0 {
		background-color : #404040;
		color: #fff;
	}
	.btn-ckb-show-hide-1 {
		background-color : #ff0000;
		color: #fff;
	}
	.btn-ckb-show-hide-2 {
		background-color : #f26522;
		color: #fff;
	}
	.btn-ckb-show-hide-3 {
		background-color : #0054a6;
		color: #fff;
	}
	.btn-ckb-show-hide-4 {
		background-color : #005b7f;
		color: #fff;
	}
	.btn-ckb-show-hide-5 {
		background-color : #005e20;
		color: #fff;
	}
	.btn-ckb-show-hide-0:hover {
		color: #ddd;
	}
	.btn-ckb-show-hide-1:hover {
		color: #ddd;
	}
	.btn-ckb-show-hide-2:hover {
		color: #ddd;
	}
	.btn-ckb-show-hide-3:hover {
		color: #ddd;
	}
	.btn-ckb-show-hide-4:hover {
		color: #ddd;
	}
	.btn-ckb-show-hide-5:hover {
		color: #ddd;
	}
</style>


</head>
<body class="fixed-navbar fixed-sidebar">

	<!-- Simple splash screen-->

	<div class="splash">
		<div class="color-line"></div>
		<div class="splash-title">
			<h1>    YOUTH PHOTOGRAPHIC SOCIETY | REMOTE RATING PANEL  </h1>
			<p>We are loading the thumbnails from Server. This may take some time. Please wait !</p>
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
		<?php
			if (! isset($_SESSION["hide_score_board"])) {
				$_SESSION["hide_score_board"] = true;
		?>
			<div class="well" id="score-board" style="height: 100%; width: 600px; margin: 0 auto;" >
				<h3 class="test-info text-center">  LET US PROVIDE THE RIGHT SCORES  </h3>
				<p class="text-justified">
					Please review the scoring pattern that YPS uses which is consistent with global practices.
					Please provide the right scores so that we can provide adequate acceptances.
				</p>
				<table class="table" style="padding-left: 20px; padding-right: 20px;">
					<tr>
						<td width="80">
							<div style="text-align: center;"><img src="img/sq1.png" ></div>
						</td>
						<td>
							<p class="text-info"><strong>Reject</strong></p>
							<p>To be used when picture does not conform to the rules (Watermark, Accepted, Multiple Upload, Print not received) or to the section</p>
						</td>
					</tr>
					<tr>
						<td width="80">
							<div style="text-align: center;"><img src="img/sq2.png" ></div>
						</td>
						<td>
							<p class="text-info"><strong>Just Qualifies</strong></p>
							<p>To be used when picture conforms to the rules but is not suitable for Acceptance</p>
						</td>
					</tr>
					<tr>
						<td width="80">
							<div style="text-align: center;"><img src="img/sq3.png" ></div>
						</td>
						<td>
							<p class="text-info"><strong>Accept</strong></p>
							<p>To be used when picture is suitable for Acceptance</p>
						</td>
					</tr>
					<tr>
						<td width="80">
							<div style="text-align: center;"><img src="img/sq4.png" ></div>
						</td>
						<td>
							<p class="text-info"><strong>Certificate/Mention</strong></p>
							<p>To be used when picture conforms to the rules and is recommended for Certificate or Honorable Mention or Ribbon</p>
						</td>
					</tr>
					<tr>
						<td width="80">
							<div style="text-align: center;"><img src="img/sq5.png" ></div>
						</td>
						<td>
							<p class="text-info"><strong>Award</strong></p>
							<p>To be used when picture conforms to the rules and is recommended for an Award or Medal</p>
						</td>
					</tr>
				</table>
				<div class="row"style="padding-top: 20px; text-align: center;">
					<a class="btn btn-info" href="javascript:hide_score_board()">DISMISS</a>
				</div>
			</div>
		<?php
			}
		?>
			<div class="row">
				<div class="col-lg-12 col-md-12 col-sm-12">
					<!-- Filters Row -->
					<div class="well well-sm">
						<div class="row">
							<div class="col-sm-10">
								<div class="row">
									<!-- Info -->
									<div class="col-sm-3">
										<div class="row">
											<div class="col-sm-12">
												<span class="text-info lead"><?= $contest['contest_name'];?></span>
											</div>
										</div>
										<div class="row">
											<div class="col-sm-7">
												<p>Section : </p><p class="text-center"><b><?= $jury_section;?></b></p>
												<p>Group : </p><p class="text-center"><b><?= $award_group;?></b></p>
											</div>
											<div class="col-sm-5">
												<p>Uploaded : </p><p class="text-center"><big><b><?= $num_pics;?></b></big></p>
												<p>Pending : </p><p class="text-center"><big><b><span id="pics-pending"><?= $num_pics - $num_pics_scored;?></span></b></big></p>
											</div>
										</div>
									</div>
									<!-- Score -->
									<div class="col-sm-3">
										<div class="well">
											<div class="row">
												<div class="col-sm-12 text-center">
													<span class="lead">Score Pictures</span>
												</div>
											</div>
											<div class="row">
												<div class="col-sm-6 text-center">
													<a class="btn btn-danger filter-btn" id="show-rejected" style="width: 90%;">Rejects</a>
												</div>
												<div class="col-sm-6 text-center">
													<a class="btn btn-warning filter-btn" id="show-flagged" style="width: 90%;">Just OK</a>
												</div>
											</div>
											<div class="row" style="margin-top: 8px;">
												<div class="col-sm-6 text-center">
													<a class="btn btn-info filter-btn" id="show-unscored" style="width: 90%;">Unscored</a>
												</div>
												<div class="col-sm-6 text-center">
													<a class="btn btn-success filter-btn" id="show-all" style="width: 90%; border: 4px solid #888;">All</a>
												</div>
											</div>
										</div>
									</div>
									<!-- Adjust and Balance Scores -->
									<div class="col-sm-6">
										<div class="well">
											<div class="row">
												<div class="col-sm-12 text-center">
													<span class="lead">Review Scores</span>
												</div>
											</div>
											<div class="row">
												<div class="col-sm-2 text-center">
													<a class="btn btn-info filter-btn" id="show-scored" style="width: 90%">Scored</a>
												</div>
												<div class="col-sm-2 text-center">
													<input type="checkbox" data-toggle="toggle" data-size="mini"
															data-on="1" data-off="1" data-onstyle="ckb-show-hide-1"
															data-rating="1"
															class="ckb-show-hide" id="ckb-show-hide-1"
															disabled >
												</div>
												<div class="col-sm-2 text-center">
													<input type="checkbox" data-toggle="toggle" data-size="mini"
															data-on="2" data-off="2" data-onstyle="ckb-show-hide-2"
															data-rating="2"
															class="ckb-show-hide" id="ckb-show-hide-2"
															disabled >
												</div>
												<div class="col-sm-2 text-center">
													<input type="checkbox" data-toggle="toggle" data-size="mini"
															data-on="3" data-off="3" data-onstyle="ckb-show-hide-3"
															data-rating="3"
															class="ckb-show-hide" id="ckb-show-hide-3"
															disabled >
												</div>
												<div class="col-sm-2 text-center">
													<input type="checkbox" data-toggle="toggle" data-size="mini"
															data-on="4" data-off="4" data-onstyle="ckb-show-hide-4"
															data-rating="4"
															class="ckb-show-hide" id="ckb-show-hide-4"
															disabled >
												</div>
												<div class="col-sm-2 text-center">
													<input type="checkbox" data-toggle="toggle" data-size="mini"
															data-on="5" data-off="5" data-onstyle="ckb-show-hide-5"
															data-rating="5"
															class="ckb-show-hide" id="ckb-show-hide-5"
															disabled >
												</div>
											</div>
											<div class="row">
												<div class="col-sm-2 text-center"><big><b><span id="pics-scored-total"><?= $num_pics_scored;?></span></b></big></div>
												<div class="col-sm-2 text-center"><big><b><span id="pics-scored-1"><?= $scores_list[1];?></span></b></big></div>
												<div class="col-sm-2 text-center"><big><b><span id="pics-scored-2"><?= $scores_list[2];?></span></b></big></div>
												<div class="col-sm-2 text-center"><big><b><span id="pics-scored-3"><?= $scores_list[3];?></span></b></big></div>
												<div class="col-sm-2 text-center"><big><b><span id="pics-scored-4"><?= $scores_list[4];?></span></b></big></div>
												<div class="col-sm-2 text-center"><big><b><span id="pics-scored-5"><?= $scores_list[5];?></span></b></big></div>
											</div>
											<div class="row">
												<div class="col-sm-2 text-center"></div>
												<div class="col-sm-2 text-center">
													<span id="pct-scored-1"><?= round($num_pics_scored == 0 ? 0 : $scores_list[1] / $num_pics_scored * 100, 0) . "%";?></span>
												</div>
												<div class="col-sm-2 text-center">
													<span id="pct-scored-2"><?= round($num_pics_scored == 0 ? 0 : $scores_list[2] / $num_pics_scored * 100, 0) . "%";?></span>
												</div>
												<div class="col-sm-2 text-center">
													<span id="pct-scored-3"><?= round($num_pics_scored == 0 ? 0 : $scores_list[3] / $num_pics_scored * 100, 0) . "%";?></span>
												</div>
												<div class="col-sm-2 text-center">
													<span id="pct-scored-4"><?= round($num_pics_scored == 0 ? 0 : $scores_list[4] / $num_pics_scored * 100, 0) . "%";?></span>
												</div>
												<div class="col-sm-2 text-center">
													<span id="pct-scored-5"><?= round($num_pics_scored == 0 ? 0 : $scores_list[5] / $num_pics_scored * 100, 0) . "%";?></span>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="col-sm-1">
								<span class="text-right pull-right text-danger">Click on this thumbnail to check your display</span>
							</div>
							<div class="col-sm-1">
								<div class="thumbnail thumbdiv" style="height: 60px;">
									<a href="../salons/<?= $jury_yearmonth;?>/img/adjust_monitor.jpg" title="Adjust Monitor" data-gallery="adjust"
									   data-description="" data-rejection-text= "" data-rating="0">
										<img src="../salons/<?= $jury_yearmonth;?>/img/adjust_monitor.jpg" >
									</a>
								</div>
							</div>
						</div>
					</div>
					<!-- end of Filter Row -->


					<div class="hpanel">
						<div class="panel-body">
							<p><a id="find-last-eseq" class="text-danger">Find last viewed picture</a></p>
							<form method="post" action="#" class="form-inline">
								<input type="hidden" name="assign" value="assign">  <!-- to identify this form -->
								<!-- end of control well -->
								<div class="lightBoxGallery" id="gallery" style="text-align:left">
								<?php
									$sql = $main_query;
									$pics = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
									$max = mysqli_num_rows($pics);
								?>
									<p><big><b><span id="filter-text">All Uploaded Pictures : </span><span id="filtered-num-pics"><?= $max;?></span></b></big></p>
								<?php
									$idx = 0;
									while($tr_pics = mysqli_fetch_array($pics)) {
										$idx++;
										$profile_id = $tr_pics['profile_id'];
										$pic_id = $tr_pics['pic_id'];
										$picfile = $tr_pics['picfile'];
										$pic_section = $tr_pics['section'];
										$pic_alert = $tr_pics['profile_name'] . ' (' .  $tr_pics['profile_id'] . ')' ;
										$pic_rating = $tr_pics['rating'];
										$flag_pic = ($tr_pics['no_accept'] == '0' ? "no" : "yes");
										$pic_width = $tr_pics['width'];
										$pic_height = $tr_pics['height'];
										$pic_exif = $tr_pics['exif'];

										// Process Notifications
										$notes = "";
										$accepted_in_yps = false;
										$print_not_received = false;
										$violates_rules = false;
										$has_watermark = false;
										$disallow_selection = false;
										$notifications = explode("|", $tr_pics['notifications']);
										$rejection_text = "";
										$rejected = "no";
										foreach ($notifications AS $notification) {
											if ($notification != "") {
												list($notification_date, $notification_code_str) = explode(":", $notification);
												$notification_codes = explode(",", $notification_code_str);
												foreach ($notification_codes as $notification_code)
													if (isset($rejection_reasons[$notification_code])) {
														$rejection_text .= (($rejection_text == "") ? "" : ",") . $rejection_reasons[$notification_code];
														$disallow_selection = true;
														$rejected = "yes";
													}
											}
										}
										if ($rejection_text == "") {
											// Assign justification for rejection from jury
											$rejection_text = $tr_pics['jury_tags'];
										}
										if ($rejection_text != "") {
											$notes = "*REJECT*";
										}

										$pic_title = "[" . $tr_pics['profile_id'] . "|" . $tr_pics['pic_id'] . "] " . $notes  . " {" . $idx . "/" . $max . "}";
										$data_description = $tr_pics['eseq'] . ": " . $idx . "/" . $max;
										$pic_path = "../salons/" . $jury_yearmonth . "/upload/" . $pic_section . "/" . $picfile;
										$tn_path = "../salons/" . $jury_yearmonth . "/upload/" . $pic_section . "/tn/" . $picfile;
										// $tnl_path = "../salons/" . $jury_yearmonth . "/upload/" . $pic_section . "/" . $picfile;	// Larger Thumbnail
								?>
									<div class="<?=$tn_classes;?> containerBox salon-pic"
											data-rating="<?= $pic_rating;?>"
											data-rejected="<?= $rejected;?>"
											data-flagged="<?= $flag_pic;?>" >
										<div>
											<span class="text-info"><b>#<?= $idx;?></b></span>
											<span class="text pull-right">[<b><?= $tr_pics['eseq'];?></b>]</span>
										</div>
										<div class="col-sm-12 thumbnail thumbdiv" id="thumbnail-<?=$idx;?>" >
											<a href="<?=$pic_path;?>" title="<?=$pic_title;?>" class="thumb-link"
											   		data-yearmonth="<?= $jury_yearmonth;?>"
											   		data-profile-id="<?= $tr_pics['profile_id'];?>"
													data-pic-id="<?= $tr_pics['pic_id'];?>"
													data-picfile="<?= $picfile;?>"
											   		data-rejection-text="<?= $rejection_text;?>"
											   		data-rating="<?= $pic_rating;?>"
													data-description="<?=$data_description;?>"
													data-width="<?= $pic_width;?>"
													data-height="<?= $pic_height;?>"
													data-exif='<?= $pic_exif;?>'
													data-eseq="<?= $tr_pics['eseq'];?>"
											   		data-gallery="" >
												<img class="lozad" src="img/preview.png" data-src="<?= $tn_path;?>" id="thumbnail-img-<?=$idx;?>" >
												<div class="caption">
													<p>
														<span>
															<i class="fa fa-star-o"></i>
															<b class="thumb-rating"><?= $pic_rating;?></b>
															- <small><span class="thumb-legend"><?= $rating_legends[$pic_rating];?></span></small>
														</span>
														<span class="text-warning pull-right thumb-notes"><?=$notes;?></span>
													</p>
													<small><span class="thumb-rejection-text"><?= $rejection_text;?></span></small>
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
						</div> <!-- panel body -->
					</div>
				</div>
			</div>
		</div>
		<!-- Right sidebar -->
		<!-- Footer-->
	</div>  <!-- / Main Wrapper -->

	<!-- Templates used in Javascript Functions -->
	<template id="confirm-rejection">
		<div class="confirm-rejection">
			<h3 class="text-center">Confirm Rejection</h3>
			<p>Please select one or more reasons from the list and Confirm.</p>
			<p><big><b>Press 1 to <?= sizeof($rejection_reasons);?></b></big> on keyboard to select reasons. No Mouse.</p>
			<p><big><b>Press Y</b></big> to Confirm.</p>
			<p><big><b>Press X</b></big> to Cancel.</p>
			<?php
				$reason_no = 0;
				foreach ($rejection_reasons as $rejection_code => $rejection_text) {
					++ $reason_no;
			?>
			<p style="font-size: 14px; font-weight: bold">
				<i id="rejection-checkbox-<?= $reason_no;?>" class="fa fa-square-o"></i>
				<span style="padding-left: 15px;"><big><?= $reason_no;?>.</big> <span id="rejection-text-<?= $reason_no;?>" ><?= $rejection_text;?></span></span>
			</p>
			<?php
				}
			?>
			<br>
			<p style="text-align: right;">
				<b><big>X</big> - CANCEL &amp; RE-SCORE</b> <span style="padding-left: 15px;"><b><big>Y</big> - CONFIRM RATING</b></span>
			</p>
			<p id="cr-error-message"></p>
		</div>
	</template>

	<template id="override-rejection">
		<div class="override-rejection">
			<h3 class="text-center">Override Rejection</h3>
			<p>This picture has been marked for <b>rejection</b> for the following reasons:</p>
			<p style="padding-left: 20px;"><span id="override-rejection-text">placeholder</span></p>
			<br>
			<p class="pull-right">
				<b><big>X</big> - CANCEL &amp; RE-SCORE</b> <span style="padding-left: 15px;"><b><big>Y</big> - CONFIRM RATING</b></span>
			</p>
		</div>
	</template>

	<template id="exif-info">
		<div class="exif-info">
			<h3 class="text-center">EXIF</h3>
			<!-- <p style="padding-left: 20px;"><span id="exif-text">Loading EXIF. Please wait.</span></p> -->
			<p style="padding-left: 20px;"><span id="exif-text"></span></p>
			<br>
			<p class="pull-right">
				<b><big>X</big> - CLOSE</b></span>
			</p>
		</div>
	</template>

	<template id="histogram-info">
		<div class="histogram-info">
			<h3 class="text-center">Histogram</h3>
			<!-- <p style="padding-left: 20px;"><span id="exif-text">Loading EXIF. Please wait.</span></p> -->
			<div id="histogram-display"></div>
			<br>
			<p class="pull-right">
				<b><big>X</big> - CLOSE</b></span>
			</p>
		</div>
	</template>

	<!-- The Gallery as lightbox dialog, should be a child element of the document body -->
	<div id="blueimp-gallery" class="blueimp-gallery" data-continuous="false" data-toggle-slideshow-on-space="false" data-full-screen="true" data-stretch-images="false"
														data-close-on-slide-click="false" data-close-on-swipe-up-or-down="false" data-filter=":visible" >
		<div class="slides"></div>
		<h3 class="title"></h3>
		<a class="prev">&laquo;</a>
		<a class="next">&raquo;</a>
		<a class="close">&#42;</a>
		<a class="description"><h3>Rating</h3></a>
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
<script src="plugin/bootstrap-toggle/js/bootstrap-toggle.min.js"></script>
<script src="plugin/photo-histogram/photo-histogram.js"></script>

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

<!-- Find JSON String in a response -->
<script>
	function json_str(resp) {
		let start, end;
		// Try Array Object
		start = resp.indexOf("[");
		end = resp.indexOf("]");
		if (start == -1 || end == -1 || end <= start) {
			// Try plain object
			start = resp.indexOf("{");
			end = resp.indexOf("}");
			if (start == -1 || end == -1 || end <= start)
				return "";
		}

		return resp.substr(start, end - start + 1);
	}
</script>


<script>
	// Initiated by Dismiss button on Score Board
	function hide_score_board() {
		$("#score-board").hide();
	}

	// Flash the div with class flash for 0.5 seconds
	function flash_score(score) {
		//$(".flash-text").html(score);
		$(".flash-score").show();
		setTimeout(function (){$(".flash-score").hide();}, 2000);
	}
</script>

<!-- Lazy Load of Thumbnails -->
<script>
	// $(document).ready(function(){
	// 	$("img.lazy-load").each(function() {
	// 		$(this).attr("src", $(this).data("src"));
	// 	});
	// });
</script>


<!-- Handle Selection Buttons and Score Toggles -->
<script>
	$(document).ready(function(){
		var tn_6_per_row = 6;
		var tn_6_classes = "col-sm-2";
		var tn_4_per_row = 4;
		var tn_4_classes = "col-sm-3";
		var tn_per_row = 6;
		var tn_classes = "col-sm-2";
		const JUST_OK_RATING = 2;

		function show_filtered(filter_text, thumbnail_set, show_row_score = false) {
			$("#filter-text").html(filter_text);
			$("#filtered-num-pics").html(thumbnail_set.length);
			// Hide all thumbnails
			$(".salon-pic").hide();
			// Remove all clearfix statements
			$("div.clearfix").remove();
			// Remove bulk score buttons
			$("div.bulk-score").remove();
			// Remove existing boot-strap class and add currently set class
			thumbnail_set.removeClass(tn_4_classes);
			thumbnail_set.removeClass(tn_6_classes);
			thumbnail_set.addClass(tn_classes);
			// Add clearfix statements based on setting
			thumbnail_set.each(function(index, elem) {
				if (show_row_score) {
					let row = Math.floor(index / tn_per_row);
					$(this).attr("data-row", row);
					if (index % tn_per_row == 0) {
						let html = "<div class='col-sm-12 bulk-score'>";
						html += "<a class='row-score-link' data-row='" + row + "'>";
						html += "<span style='color: red;'><b>Assign &quot;Just OK [2]&quot; score to all the pictures in the row below</b></span></a>";
						html += "</div>";
						$(html).insertBefore(elem);
					}
				}
				if (index != 0 && (index % tn_per_row) == 0)
					$("<div class='clearfix'></div>").insertBefore(elem);
			});
			if (show_row_score) {
				// Hide row-score-link if all items are already scored
				$("a.row-score-link").each(function(){
					let row = $(this).attr("data-row");
					if ($(".salon-pic").filter("[data-row='" + row + "']").filter("[data-rating='0']").length == 0)
						$(this).hide();
				});
				// Register Action Handler for bulk scoring
				$("a.row-score-link").click(function(){
					bulk_score($(this).attr("data-row"), JUST_OK_RATING);
					$(this).hide();
				});
			}
			// Display thumbnails
			thumbnail_set.show();
			// Position at the first visible row-score-link
			if (show_row_score) {
				$("a.row-score-link:visible").get(0).scrollIntoView();
			}
			// Disable all rating filter checkboxes
			$("input.ckb-show-hide").prop("checked", false);
			$("input.ckb-show-hide").prop("disabled", true);
			$("input.ckb-show-hide").bootstrapToggle('destroy');
			$("input.ckb-show-hide").bootstrapToggle();
		}

		function highlight_active_button(button) {
			// Turnoff border on all the buttons
			$(".filter-btn").css("border", "0");
			// Draw a colored border
			button.css("border", "4px solid #888");
		}

		// Show Rejects
		$("#show-rejected").click(function(){
			// Show the required thumbnails
			tn_per_row = tn_6_per_row;
			tn_classes = tn_6_classes;
			show_filtered("Notified for Rejection : ", $(".salon-pic").filter("[data-rejected='yes']"));
			// Highlight this button
			highlight_active_button($(this));
			// Disable all rating filter checkboxes
			$(".ckb-show-hide").prop("disabled", true);
		});

		// Show flagged for no acceptance
		$("#show-flagged").click(function(){
			// Show the required thumbnails
			tn_per_row = tn_4_per_row;
			tn_classes = tn_4_classes;
			show_filtered("Flagged for &quot;Just OK (2)&quot; Score : ", $(".salon-pic").filter("[data-flagged='yes']"), true);
			// Highlight this button
			highlight_active_button($(this));
			// Disable all rating filter checkboxes
			$(".ckb-show-hide").prop("disabled", true);
		});

		// Show Unscored
		$("#show-unscored").click(function(){
			// Show the required thumbnails
			tn_per_row = tn_6_per_row;
			tn_classes = tn_6_classes;
			show_filtered("Yet to be scored : ", $(".salon-pic").filter("[data-rating='0']"));
			// Highlight this button
			highlight_active_button($(this));
			// Disable all rating filter checkboxes
			$(".ckb-show-hide").prop("disabled", true);
		});

		// Show Unscored
		$("#show-all").click(function(){
			// Show the required thumbnails
			tn_per_row = tn_6_per_row;
			tn_classes = tn_6_classes;
			show_filtered("All Uploaded Pictures : ", $(".salon-pic"));
			// Highlight this button
			highlight_active_button($(this));
		});

		// Show Scored
		$("#show-scored").click(function(){
			// Show the required thumbnails
			tn_per_row = tn_6_per_row;
			tn_classes = tn_6_classes;
			show_filtered("All Pictures Scored : ", $(".salon-pic").filter("[data-rating!='0']"));
			// Highlight this button
			highlight_active_button($(this));
			// Enable all rating filter checkboxes
			$("input.ckb-show-hide").prop("disabled", false);
			$("input.ckb-show-hide").prop("checked", true);
			$("input.ckb-show-hide").bootstrapToggle('destroy');
			$("input.ckb-show-hide").bootstrapToggle();
		});

		// Score selection toggles
		$("input.ckb-show-hide").change(function(){
			let target = $(this);
			let rating_index;
			if ($(this).data("rating") == 0) {
				// Toggle all checkboxes 1-5 based on setting of Unscored checkbox
				$("input.ckb-show-hide").filter("[data-rating!='0']").each(function(){
					$(this).prop("checked", ! target.prop("checked"));
					$(this).bootstrapToggle('destroy');
					$(this).bootstrapToggle();
				});
			}

			// Show or Hide thumbnails based on checked ratings
			// Always show unscored
			for (let rating = 1; rating <= 5; ++rating) {
				if ($("#ckb-show-hide-" + String(rating)).prop("checked"))
					$(".salon-pic").filter("[data-rating='" + String(rating) + "']").show();
				else
					$(".salon-pic").filter("[data-rating='" + String(rating) + "']").hide();
			}

			// Delete all "clearfix" divs
			$("div.clearfix").remove();

			// Add "clearfix" divs to make thumbnails look orderly
			$(".salon-pic:visible").each(function(index, elem) {
				if (index != 0 && (index % tn_per_row) == 0)
					$("<div class='clearfix'></div>").insertBefore(elem);
			});
		});
	});
</script>


<!-- Asynchronous rating update handler -->
<script>
	// Constants
	const DATA_SYNC_INTERVAL = 5000;			// Every 5 seconds
	const NODATA_SYNC_INTERVAL = 15000;			// Wait for 15 seconds before the next call if there is no new data
	const DATA_ERR_WAIT = 60000;				// Wait for 60 seconds before trying again

	// Global Variables
	var localdb = window.localStorage;
	var ratings_buffer = [];
	var ratings_last_id = 0;

	// Syn Ratings with server
	function sync_ratings() {
		if (ratings_buffer.length == 0)
			setTimeout(sync_ratings, NODATA_SYNC_INTERVAL);
		else {
			let jury_id = <?= $jury_id;?>;
			let yearmonth = <?= $jury_yearmonth;?>;
			let section = "<?= $jury_section;?>";
			let ratings = JSON.stringify(ratings_buffer);
			$.post("ajax/rating_list_remote_update.php",
				{jury_id, yearmonth, section, ratings},
				function (data, status) {
					if (status == "success") {
						// var result = JSON.parse(json_str(data));
						var result = JSON.parse(data);		// Works only for simple responses
						if (result.status == "OK") {
							result.ratings.forEach((rating, index) => {
								// Remove the item from the buffer
								ratings_buffer = ratings_buffer.filter(function(item){ return (item.id != rating.id); });
							});
							// Save buffer
							localdb.setItem("ratings_buffer", JSON.stringify(ratings_buffer));
						}
						setTimeout(sync_ratings, DATA_SYNC_INTERVAL);
					}
					else
						setTimeout(sync_ratings, DATA_ERR_WAIT);
				},
				"text"
			)
			.fail(function() {
				// Ajax call failed. Retry after longer interval
				setTimeout(sync_ratings, DATA_ERR_WAIT);
			});
		}
	}

	$(document).ready(function(){
		// Restore ratings_buffer at startup
		if (saved_ratings_data = localdb.getItem("ratings_buffer")) {
			ratings_buffer = JSON.parse(saved_ratings_data);
			ratings_buffer.forEach((item, i) => {
				if (item.id > ratings_last_id)
					ratings_last_id = item.id;
			});
		}

		// Install Data Sync handler
		sync_ratings();
	});

	// Add ratings to the buffer and save the buffer
	function add_rating_to_buffer(profile_id, pic_id, rating, rejection_text) {

		// Add rating to buffer
		++ ratings_last_id;
		ratings_buffer.push({
				id : ratings_last_id,
				profile_id : profile_id,
				pic_id : pic_id,
				rating : rating,
				rejection_text : rejection_text,
		});

		// Save buffer
		localdb.setItem("ratings_buffer", JSON.stringify(ratings_buffer));
	}

	// Add Just OK rating to all pictures in the row
	function bulk_score(row_id, rating) {
		$(".salon-pic").filter("[data-row='" + row_id + "']").filter("[data-rating='0']").each(function() {
			let profile_id = $(this).find(".thumb-link").attr("data-profile-id");
			let pic_id = $(this).find(".thumb-link").attr("data-pic-id");
			let rejection_text = "";
			// Update Rating
			$(this).attr("data-rating", rating);
			$(this).find(".thumb-link").attr("data-rating", rating);
			$(this).find(".thumb-rating").html(rating);
			$(this).find(".thumb-legend").html(rating_list[rating]);

			// Add item to rating_buffer
			add_rating_to_buffer(profile_id, pic_id, rating, rejection_text);

			// Update totals
			$("#pics-scored-total").html(String($(".salon-pic").filter("[data-rating!='0']").length));
			$("#pics-pending").html(String($(".salon-pic").filter("[data-rating='0']").length));
			$("#pics-scored-" + String(rating)).html(String($(".salon-pic").filter("[data-rating='" + String(rating) + "']").length));

			// Update percentages
			let num_scored = $(".salon-pic").filter("[data-rating!='0']").length;
			if (num_scored > 0) {
				$("#pct-scored-1").html( ($(".salon-pic").filter("[data-rating='1']").length / num_scored * 100).toFixed() + "%" );
				$("#pct-scored-2").html( ($(".salon-pic").filter("[data-rating='2']").length / num_scored * 100).toFixed() + "%" );
				$("#pct-scored-3").html( ($(".salon-pic").filter("[data-rating='3']").length / num_scored * 100).toFixed() + "%" );
				$("#pct-scored-4").html( ($(".salon-pic").filter("[data-rating='4']").length / num_scored * 100).toFixed() + "%" );
				$("#pct-scored-5").html( ($(".salon-pic").filter("[data-rating='5']").length / num_scored * 100).toFixed() + "%" );
			}
		});
	}
</script>

<script>

// Global Variables
var slideshow_open = false;
var exif_in_progress = false;
var histogram_in_progress = false;
var confirm_rejection_in_progress = false;
var override_rejection_in_progress = false;
var max_rejection_number = <?= sizeof($rejection_reasons);?>;	// Cannot be more than 9 - Design Restriction
var rejectionKeyCodeList = [88, 89 <?php for($i = 0; $i < sizeof($rejection_reasons); ++ $i) { echo ", " . (49 + $i) . ", " . (97 + $i); }?>];
var rejection_items = [];
var jury_rejection_text = "";
var override_index = 0;		// Index of Current picture being handled by the Rejection Override and Rejection Confirmation Functions
var override_rating = 0;

// Find last viewed thumbnail
var last_eseq = "";			// Field used by find function
$(document).ready(function(){
	$("#find-last-eseq").click(function(){
		if (last_eseq != "")
			window.find(last_eseq);
	});
});

// Event Handler
$(function(){

	$("#blueimp-gallery").on("opened", function (event) { slideshow_open = true;});		// Set flag indicating opening of slideshow
	$("#blueimp-gallery").on("close", function (event) {
		let gallery = $('#blueimp-gallery').data('gallery');		// Get Gallery Object
		let index = gallery.getIndex();
		last_eseq = gallery.list[index].getAttribute('data-eseq');
	});		// Set flag indicating opening of slideshow
	$("#blueimp-gallery").on("closed", function (event) { slideshow_open = false;});		// Reset any timer in progress
	$("#blueimp-gallery").on("slideend", function (event, index, slide) {
		// Reset Confirmation Screens
		// Rejection confirmation
		confirm_rejection_in_progress = false;	// Handle Keycodes required to reject an image
		// Update Description and stay on the picture to rescore
		let gallery = $('#blueimp-gallery').data('gallery');		// Get Gallery Object
		// Remove rejection text set in show_confirm_rejection
		if (override_index >= 0 && rejection_items.length > 0) {
			gallery.list[override_index].setAttribute('data-rejection-text', '');
			rejection_items = [];			// Empty the reasons
			jury_rejection_text = "";
		}
		// Rejection Override
		// Change state from override_rejection and move on
		override_rejection_in_progress = false;	// Handle Keycodes required to reject an image

		// Remove Exif display
		exif_in_progress = false;

		// Initiate ctl table update on navigation to next slide
		update_description(index);
	});

});

// Exif Display
function show_exif() {
	exif_in_progress = true;

	// Load Gallery Element
	let gallery = $('#blueimp-gallery').data('gallery');		// Get Gallery Object
	let index = gallery.getIndex();
	let picfile = gallery.list[index].getAttribute('data-picfile');
	let width = gallery.list[index].getAttribute('data-width');
	let height = gallery.list[index].getAttribute('data-height');
	let exif_data = gallery.list[index].getAttribute('data-width');

	// Display Description from Rejection Template
	let html = "<div id='gallery-exif'>" + $("#exif-info").html() + "</div>";
	let node = gallery.container.find('.description')[0];
	node.innerHTML = html;

	let exif_html;
	try {
		exif = JSON.parse(exif_data);
		exif_html = "<table>";
		if (exif.iso)
			exif_html += "<tr><td><b>ISO</b></td><td>" + exif.iso + "</td></tr>";
		if (exif.aperture)
			exif_html += "<tr><td><b>Aperture</b></td><td>" + exif.aperture + "</td></tr>";
		if (exif.speed)
			exif_html += "<tr><td><b>Shutter</b></td><td>" + exif.speed + "</td></tr>";
		if (exif.bias)
			exif_html += "<tr><td><b>Bias</b></td><td>" + exif.bias + "</td></tr>";
		if (exif.metering)
			exif_html += "<tr><td><b>Metering</b></td><td>" + exif.metering + "</td></tr>";
		if (exif.white_balance)
			exif_html += "<tr><td><b>WB</b></td><td>" + exif.white_balance + "</td></tr>";
		if (exif.program)
			exif_html += "<tr><td><b>PGM</b></td><td>" + exif.program + "</td></tr>";
		if (exif.flash)
			exif_html += "<tr><td><b>Flash</b></td><td>" + exif.flash + "</td></tr>";
		if (exif.focal_length)
			exif_html += "<tr><td><b>Focal</b></td><td>" + exif.focal_length + "</td></tr>";
		if (exif.lens)
			exif_html += "<tr><td><b>Lens</b></td><td>" + exif.lens + "</td></tr>";
		if (exif.camera)
			exif_html += "<tr><td><b>Camera</b></td><td>" + exif.camera + "</td></tr>";
		if (exif.date)
			exif_html += "<tr><td><b>Date</b></td><td>" + exif.date + "</td></tr>";
		exif_html += "<tr><td><b>Width</b></td><td>" + width + "</td></tr>";
		exif_html += "<tr><td><b>Height</b></td><td>" + height + "</td></tr>";
		exif_html += "</table>"
		$("#exif-text").html(exif_html);
	}
	catch(e) {
		exif_html = "<table>";
		exif_html += "<tr><td><b>EXIF</b></td><td>NOT AVAILABLE</td></tr>";
		exif_html += "<tr><td><b>Width</b></td><td>" + width +"</td></tr>";
		exif_html += "<tr><td><b>Height</b></td><td>" + height +"</td></tr>";
		exif_html += "</table>"
		$("#exif-text").html("NO EXIF ");

	}

}

function hide_exif() {
	exif_in_progress = false;
	// Update Description back and stay on the picture to rescore
	let gallery = $('#blueimp-gallery').data('gallery');		// Get Gallery Object
	let index = gallery.getIndex();
	update_description(index);
}

// Histogram functions
function show_histogram() {
	histogram_in_progress = true;
	let gallery = $('#blueimp-gallery').data('gallery');		// Get Gallery Object
	let index = gallery.getIndex();
	// Display Description from Histogram Template
	let html = "<div id='gallery-histogram'>" + $("#histogram-info").html() + "</div>";
	let node = gallery.container.find('.description')[0];
	node.innerHTML = html;
	// $("#histogram-display").html("");
	// Load thumbnail as it may be pointing to preview
	$("#thumbnail-img-" + (index+1)).attr("src", $("#thumbnail-img-" + (index+1)).attr("data-src"));
	$("#thumbnail-img-" + (index+1)).on("load", function(){
		$("#histogram-display").html("Loading...");
		if (this.complete) {
			$("#histogram-display").html("");
			new PhotoHistogram.Ui($("#histogram-display").get(0), this, { controls : 'none', height : 300});
		}
		else
			$("#histogram-display").html("Loading thumbnail. Close by pressing X and then try again");
	});
	// let histogram = new PhotoHistogram.Ui($("#histogram-display").get(0), gallery.container.find("img.slide-content")[index], { controls : 'none', height : 300});
}

function hide_histogram() {
	histogram_in_progress = false;
	// Update Description back and stay on the picture to rescore
	let gallery = $('#blueimp-gallery').data('gallery');		// Get Gallery Object
	let index = gallery.getIndex();
	update_description(index);
}

// Override Rejection by Committee
function show_override_rejection(rating) {
	override_rejection_in_progress = true;	// Handle Keycodes required to override rejection
	override_rating = rating;

	// Load Gallery Element
	let gallery = $('#blueimp-gallery').data('gallery');		// Get Gallery Object
	override_index = gallery.getIndex();
	let rejection_text = gallery.list[override_index].getAttribute('data-rejection-text');

	let rejection_list = "";
	rejection_text.split(",").forEach(function(reason){
		rejection_list += "<p><i class='fa fa-check-square-o'></i><span style='padding-left: 8px;'>" + reason + "</span></p>";
	});

	// Display Description from Rejection Template
	let html = "<div id='gallery-override-rejection'>" + $("#override-rejection").html() + "</div>";
	let node = gallery.container.find('.description')[0];
	node.innerHTML = html;

	$("#override-rejection-text").html(rejection_list);

}

function hide_override_rejection(action) {
	override_rejection_in_progress = false;	// Handle Keycodes required to reject an image

	if (action == "Y") {
		// Update Description
		// let gallery = $('#blueimp-gallery').data('gallery');		// Get Gallery Object
		// let index = gallery.getIndex();
		// gallery.list[index].setAttribute('data-rejection-text', '');	// Remove rejection text for recording in rating

		// Record rating and move to next image
		rating_remote_update(override_rating, true, "");	// Fine, let us pass this without reject reasons
	}
	else {
		// Update Description and stay on the picture to rescore
		let gallery = $('#blueimp-gallery').data('gallery');		// Get Gallery Object
		override_index = gallery.getIndex();
		update_description(override_index);
	}

	override_rating = 0;
}

// Function to obtain confirmation for Rejection of picture
function show_confirm_rejection() {
	confirm_rejection_in_progress = true;	// Handle Keycodes required to reject an image

	// Load Gallery Element
	let gallery = $('#blueimp-gallery').data('gallery');		// Get Gallery Object
	override_index = gallery.getIndex();
	// let rejection_text = gallery.list[index].getAttribute('data-rejection-text');

	// Display Description from Rejection Template
	let html = "<div id='gallery-confirm-rejection'>" + $("#confirm-rejection").html() + "</div>";
	let node = gallery.container.find('.description')[0];
	node.innerHTML = html;

	// Turn on rejection items selected & update the rejection text on the gallery DOM
	jury_rejection_text = "";
	rejection_items.forEach(function(rejection_item_no) {
		$("#rejection-checkbox-" + rejection_item_no).attr("class", "fa fa-check-square-o");
		jury_rejection_text += (jury_rejection_text == "" ? "" : ",") + $("#gallery-confirm-rejection #rejection-text-" + rejection_item_no).html();
	});
	// gallery.list[override_index].setAttribute('data-rejection-text', rejection_text);
}

function hide_confirm_rejection(action) {
	$("#cr-error-message").html("");		// Hide past error message

	if (action == "Y") {
		if (rejection_items.length == 0)
			$("#cr-error-message").html("Must select one or more reasons for rejection");
		else {
			confirm_rejection_in_progress = false;	// Handle Keycodes required to reject an image
			// Record rating 1 and move to next image
			rating_remote_update(1, true, jury_rejection_text);	// Fine, let us reject this with reasons
			if (override_index >= 0) {
				let gallery = $('#blueimp-gallery').data('gallery');		// Get Gallery Object
				gallery.list[override_index].setAttribute('data-jury-rejection-text', jury_rejection_text);
			}

			rejection_items = [];			// Empty the reasons
			jury_rejection_text = "";
		}
	}
	else {
		confirm_rejection_in_progress = false;	// Handle Keycodes required to reject an image
		// Update Description and stay on the picture to rescore
		let gallery = $('#blueimp-gallery').data('gallery');		// Get Gallery Object
		override_index = gallery.getIndex();
		// Remove rejection text set in show_confirm_rejection
		//if (rejection_items.length > 0) {
			//gallery.list[override_index].setAttribute('data-rejection-text', '');
			rejection_items = [];			// Empty the reasons
			jury_rejection_text = "";
		//}
		update_description(override_index);
	}
}

// Install Keyboard handlers
//  - to change description color between Black and White and Cyan
//	- to register ratings 1 to 5
//
$(document).ready(function(){
	$(document).keydown(function(e) {
		if (slideshow_open) {
			if (confirm_rejection_in_progress) {
				if (rejectionKeyCodeList.includes(e.keyCode)) {
					// Process X
					if (e.keyCode == 88) {
						hide_confirm_rejection("X");
						return;
					}
					// Process Y
					if (e.keyCode == 89) {
						hide_confirm_rejection("Y");
						return;
					}
					// Process Pressing of Numbers
					let numberPressed = 0;
					if (e.keyCode >= 49 && e.keyCode <= 57)
						numberPressed = e.keyCode - 49 + 1;	// start from 1
					if (e.keyCode >= 97 && e.keyCode <= 105)
						numberPressed = e.keyCode - 97 + 1;	// start from 1
					if (numberPressed <= 9 && numberPressed <= max_rejection_number) {
						// Toggle the number from the list
						if (rejection_items.includes(numberPressed)) {
							// Toggle OFF
							rejection_items = rejection_items.filter(function(rejection_item_no){ return rejection_item_no != numberPressed; });
						}
						else {
							// Toggle ON
							rejection_items.push(numberPressed);
						}
						show_confirm_rejection();
					}
				}
			}
			else if (override_rejection_in_progress) {
				// Process X
				if (e.keyCode == 88) {
					hide_override_rejection("X");
					return;
				}
				// Process Y
				if (e.keyCode == 89) {
					hide_override_rejection("Y");
					return;
				}
			}
			else if (exif_in_progress) {
				// Process X
				if (e.keyCode == 88) {
					hide_exif();
					return;
				}
			}
			else if (histogram_in_progress) {
				// Process X
				if (e.keyCode == 88) {
					hide_histogram();
					return;
				}
			}
			else {
				let keyCodeList = [66, 67, 72, 89, 87, 69, 49, 97, 50, 98, 51, 99, 52, 100, 53, 101];
				if (keyCodeList.includes(e.keyCode)) {
					// Change Color of Display
					if(e.keyCode == 66) { 	// B Key pressed to change text to black
						$("div.normal-description").css("color", "#000");
						return;
					}
					if(e.keyCode == 67) { 	// C Key pressed to change text to cyan
						$("div.normal-description").css("color", "#0ff");
						return;
					}
					if(e.keyCode == 89) { 	// Y Key pressed to change text to Yellow
						$("div.normal-description").css("color", "#ff0");
						return;
					}
					if(e.keyCode == 87) { 	// W Key pressed to change text to white
						$("div.normal-description").css("color", "#fff");
						return;
					}
					if(e.keyCode == 69) {	// E key - Show the Exif
						show_exif();
						return;
					}
					if(e.keyCode == 72) {	// H key - Show the Histogram
						show_histogram();
						return;
					}

					// Gather Rejection Information for validation
					let gallery = $('#blueimp-gallery').data('gallery');		// Get Gallery Object
					let index = gallery.getIndex();
					let rejection_text = gallery.list[index].getAttribute('data-rejection-text');
					rejection_items = [];		// Empty rejection list

					// Numbers 1-5
					// Number 1
					if (e.keyCode == 49 || e.keyCode == 97) {
						// Force Confirmation
						if (rejection_text == "") {
							show_confirm_rejection();
						}
						else
							rating_remote_update(1);
						return;
					}
					// Number 2
					if (e.keyCode == 50 || e.keyCode == 98) {
						if (rejection_text == "")
							rating_remote_update(2);
						else
							show_override_rejection(2);
					}
					// Number 3
					if (e.keyCode == 51 || e.keyCode == 99) {
						if (rejection_text == "")
							rating_remote_update(3);
						else
							show_override_rejection(3);
					}
					// Number 4
					if (e.keyCode == 52 || e.keyCode == 100) {
						if (rejection_text == "")
							rating_remote_update(4);
						else
							show_override_rejection(4);
					}
					// Number 5
					if (e.keyCode == 53 || e.keyCode == 101) {
						if (rejection_text == "")
							rating_remote_update(5);
						else
							show_override_rejection(5);
					}
				}
			}
		}
	});
	// Load the Click Sound
	// click_sound = new Audio("/jurypanel/img/button-16.wav");
});

var rating_list = ["", "Reject", "Just OK", "Acceptance", "Certificate", "Award"];

// Update description field on the gallery
function update_description(index, err = "") {
	// Turn off all flags
	exif_in_progress = false;
	histogram_in_progress = false;
	confirm_rejection_in_progress = false;
	override_rejection_in_progress = false;

	var gallery = $('#blueimp-gallery').data('gallery');		// Get Gallery Object
	var rating = gallery.list[index].getAttribute('data-rating');
	var rejection_text = gallery.list[index].getAttribute('data-rejection-text');
	if (rejection_text == "" && rating == 1 && gallery.list[index].getAttribute('data-jury-rejection-text'))
		rejection_text = gallery.list[index].getAttribute('data-jury-rejection-text');
	var description = "";
	if (err == "") {
		description  = "<div class='normal-description'><h3>";
		description += "<span style='font-size: 0.6em; opacity: 0.5;'>" + rejection_text + "</span> ";
		description += "<span class='blinker'>" + (rejection_text == "" ? "" : "* REJECT *") + "</span> ";
		description += "<span style='padding-left: 15px; padding-right: 15px;'>" + rating + " - " + ((rating >= 1 && rating <= 5) ? rating_list[rating].toUpperCase() : "") + "</span>";
		description += "</h3></div>";
	}
	else {
		description = "<div class='normal-description'><h3>";
		description += "<span style='font-size: 0.6em; opacity: 0.5;'>" + err + "</span> ";
		description += "<span class='blinker'>* TRY AGAIN *</span> ";
		description += "</h3></div>";
	}

	var node = gallery.container.find('.description')[0];
	node.innerHTML = description;
}

function rating_remote_update(rating, use_this_text = false, jury_rejection_text = "") {
	if (rating >= 1 && rating <= 5) {

		let jury_id = <?= $jury_id;?>;
		let yearmonth = <?= $jury_yearmonth;?>;
		let section = "<?= $jury_section;?>";

		let gallery = $('#blueimp-gallery').data('gallery');		// Get Gallery Object
		let index = gallery.getIndex();
		let current_rating = gallery.list[index].getAttribute("data-rating");
		let profile_id = gallery.list[index].getAttribute("data-profile-id");
		let pic_id = gallery.list[index].getAttribute("data-pic-id");
		let rejection_text = (use_this_text) ? jury_rejection_text : gallery.list[index].getAttribute('data-rejection-text');
		// let rejection_text = (use_this_text) ? gallery.list[index].getAttribute('data-rejection-text') : jury_rejection_text;

		// Append to ratings_buffer
		add_rating_to_buffer(profile_id, pic_id, rating, rejection_text);

		// Update Gallery
		gallery.list[index].setAttribute("data-rating", rating);
		// Update Description for displaying rating
		update_description(index);

		// Update rating in the thumbnail gallery
		$(gallery.list[index]).find(".thumb-link").attr("data-rating", rating);
		$(gallery.list[index]).find(".thumb-rating").html(rating);
		$(gallery.list[index]).find(".thumb-legend").html((rating >= 1 && rating <= 5) ? rating_list[rating] : "");
		$(gallery.list[index]).find(".thumb-notes").html(rejection_text == "" ? "" : "* REJECT *");
		$(gallery.list[index]).find(".thumb-rejection-text").html(rejection_text);

		// Update rating in the parent for easy filtering
		$(gallery.list[index]).parents("div.salon-pic").attr("data-rating", String(rating));

		// Remove zero-rating ID from the parent
		//$(gallery.list[index]).parent().parent().get(0).id = "";

		// Update Counters of Pictures Scored / Unscored
		$("#pics-scored-total").html(String($(".salon-pic").filter("[data-rating!='0']").length));
		$("#pics-pending").html(String($(".salon-pic").filter("[data-rating='0']").length));
		$("#pics-scored-" + String(rating)).html(String($(".salon-pic").filter("[data-rating='" + String(rating) + "']").length));

		// Update percentages
		let num_scored = $(".salon-pic").filter("[data-rating!='0']").length;
		if (num_scored > 0) {
			$("#pct-scored-1").html( ($(".salon-pic").filter("[data-rating='1']").length / num_scored * 100).toFixed() + "%" );
			$("#pct-scored-2").html( ($(".salon-pic").filter("[data-rating='2']").length / num_scored * 100).toFixed() + "%" );
			$("#pct-scored-3").html( ($(".salon-pic").filter("[data-rating='3']").length / num_scored * 100).toFixed() + "%" );
			$("#pct-scored-4").html( ($(".salon-pic").filter("[data-rating='4']").length / num_scored * 100).toFixed() + "%" );
			$("#pct-scored-5").html( ($(".salon-pic").filter("[data-rating='5']").length / num_scored * 100).toFixed() + "%" );
		}

		// Go to next picture
		gallery.next();

	}
}


// function added by Murali to update ctl table
function resume_slideshow() {
	var gallery = $('#blueimp-gallery');		// Get Gallery Object
	gallery.slide(galidx);
	update_description(galidx);
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
else {
	// Go back to login screen in case of unauthorized access
	$_SESSION['err_msg'] = "Invalid Access / Session expired";
	header("Location: " . http_method() . $_SERVER['SERVER_NAME'] . "/jurypanel/index.php");
	printf("<script>location.href='" . http_method() . $_SERVER['SERVER_NAME'] . "/jurypanel/index.php'</script>");
}

?>
