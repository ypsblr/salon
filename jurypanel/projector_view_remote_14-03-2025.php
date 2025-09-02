<?php
// New version handling all filtering through Javascript
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
    $sections = decode_string_array(str_replace(" ", "", $_REQUEST['sections']));
	$_SESSION['section'] = $sections;

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

	// Create Entrant Category List for filtering by category
	$sql = "SELECT entrant_category FROM entrant_category WHERE yearmonth = '$jury_yearmonth' AND award_group = '$award_group' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$category_list = array();
	while ($row = mysqli_fetch_array($query))
		$category_list[] = $row['entrant_category'];

	// Get Section details
	$sql = "SELECT * FROM section WHERE yearmonth = '$jury_yearmonth' AND section = '$sections' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$section = mysqli_fetch_array($query);

	// Find out the number of juries assigned to this section
	$sql  = "SELECT COUNT(*) AS num_juries FROM assignment ";
	$sql .= " WHERE assignment.yearmonth = '$jury_yearmonth' ";
	$sql .= "   AND assignment.section = '$sections'";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$row = mysqli_fetch_array($query);
	$num_juries = $row['num_juries'];

	// Get Award List
	$sql  = "SELECT * FROM award ";
	$sql .= " WHERE yearmonth = '$jury_yearmonth' ";
	$sql .= "   AND award_type = 'pic' ";
	$sql .= "   AND level != 99 ";
	$sql .= "   AND (section = '$sections' OR section = 'CONTEST') ";
	$sql .= "   AND award_group = '$award_group' ";
	$sql .= " ORDER BY level, sequence ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$awardList = array();
	while ($row = mysqli_fetch_array($query)) {
		$awardName = $row["award_name"] . "-" . $row["number_of_awards"];
		if ($row["section"] == "CONTEST")
			$awardName .= " (OVERALL)";
		$awardList[$row["award_id"]] = $awardName;
		// $awardList[$awardName] = $row["award_id"];
	}

	// Get already finalized results for each picture
	$sql  = "SELECT * FROM pic_result, award ";
	$sql .= " WHERE pic_result.yearmonth = '$jury_yearmonth' ";
	$sql .= "   AND award.yearmonth = pic_result.yearmonth ";
	$sql .= "   AND award.award_id = pic_result.award_id ";
	$sql .= "   AND award.award_type = 'pic' ";
	$sql .= "   AND (award.section = '$sections' OR award.section = 'CONTEST')";
	$sql .= "    AND award_group = '$award_group' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$pic_award_list = array();
	while ($row = mysqli_fetch_array($query)) {
		$awardName = $row["award_name"] . "-" . $row["number_of_awards"];
		$pic_key = $row['profile_id'] . "|" . $row["pic_id"];
		if ($row["section"] == "CONTEST")
			$awardName .= " (OVERALL)";
		// if(isset($resultList[$awardName]))
		// 	array_push($resultList[$awardName], $pic_key);
		// else
		// 	$resultList[$awardName] = array($pic_key);
		// List of awards for each picture
		if (isset($pic_award_list[$pic_key]))
			array_push($pic_award_list[$pic_key], $row['award_id']);
		else
			$pic_award_list[$pic_key] = array($row['award_id']);
	}

	// Generate Bucket List
	$bucketList = array();
	$bucketList["bucket_1"] = ($jury_session['bucket1_list'] != "") ? explode(",", $jury_session['bucket1_list']) : array();
	$bucketList["bucket_2"] = ($jury_session['bucket2_list'] != "") ? explode(",", $jury_session['bucket2_list']) : array();
	$bucketList["bucket_3"] = ($jury_session['bucket3_list'] != "") ? explode(",", $jury_session['bucket3_list']) : array();
	$bucketList["bucket_4"] = ($jury_session['bucket4_list'] != "") ? explode(",", $jury_session['bucket4_list']) : array();
	$bucketList["bucket_5"] = ($jury_session['bucket5_list'] != "") ? explode(",", $jury_session['bucket5_list']) : array();

	// Always start with All Images - Use screen controls to filter
	$filterText = "All Images";
	$session_filter = "ALL";		// ALL, CATEGORY, UNSCORED, RATING, BUCKET, AWARD
	$session_bucket = "NONE";		// Rating criteria (or) Bucket Number (or) Award ID

	// Main Query
	$main_query  = "SELECT pic.profile_id, pic.pic_id, pic.section, pic.title, pic.location, pic.picfile, pic.eseq, ";
	$main_query .= "       pic.width, pic.height, pic.exif, pic.notifications, pic.print_received, ";
	$main_query .= "       profile.profile_name, entry.entrant_category, ";
	$main_query .= "       IFNULL(SUM(rating.rating), 0) AS total_rating, IFNULL(COUNT(rating.rating), 0) AS num_rating, ";
	$main_query .= "       IFNULL(MAX(rating.rating), 0) AS max_rating, IFNULL(MIN(rating.rating), 0) AS min_rating ";
	$main_query .= "  FROM entry, profile, pic LEFT JOIN rating ";
	$main_query .= "    ON rating.yearmonth = pic.yearmonth AND rating.profile_id = pic.profile_id AND rating.pic_id = pic.pic_id ";
	$main_query .= " WHERE pic.yearmonth = '$jury_yearmonth' ";
	$main_query .= "   AND pic.section = '$sections' ";
	$main_query .= "   AND entry.yearmonth = pic.yearmonth ";
	$main_query .= "   AND entry.profile_id = pic.profile_id ";
	$main_query .= "   AND entry.entrant_category IN (" . implode(",", array_add_quotes($category_list)) . ")";
	$main_query .= "   AND profile.profile_id = pic.profile_id ";
	$main_query .= " GROUP BY profile_id, pic_id ";
	$main_query .= " ORDER BY eseq ASC ";

	// Default thumbnail display per row
	$tn_per_row = 6;
	$tn_classes = 'col-sm-2';

	// Save Session for remote judging
	$sql  = "UPDATE jury_session ";
	$sql .= "   SET command_index = command_index + 1 ";
	$sql .= "     , entrant_categories = '" . implode(",", $category_list) . "' ";
	$sql .= "     , bucket = '$session_bucket' ";
	$sql .= "     , filter_criteria = '$session_filter' ";
	$sql .= " WHERE yearmonth = '$jury_yearmonth' ";
	$sql .= "   AND section = '$sections' ";
	$sql .= "   AND award_group = '$award_group' ";
	mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

?>
<!DOCTYPE html>
<html>

<head>

    <!-- Page title -->
    <title>Youth Photographic Society | Projection Panel - Remote</title>


	<?php include("inc/header.php");?>

    <!-- Vendor styles -->
    <link rel="stylesheet" href="plugin/blueimp-gallery/css/blueimp-gallery.min.css" />
	<link rel="stylesheet" href="plugin/lightbox/css/lightbox.min.css" />
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

currently-showing {
	border : 2px solid red;
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

div.inline {
	display : inline-block;
	margin-right : 10px;
}
</style>


</head>
<body class="fixed-navbar fixed-sidebar">

	<!-- Simple splash screen-->

	<div class="splash">
		<div class="color-line"></div>
		<div class="splash-title">
			<h1>YOUTH PHOTOGRAPHIC SOCIETY | PROJECTION PANEL REMOTE</h1>
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
			<div class="well well-sm">
				<div class="row">
					<div class="col-sm-11">
						<!-- Filters by sub-categories -->
						<div class="row" style="margin-bottom: 10px;">
							<div class="col-sm-12">
								<!-- Category selectors -->
								<div class="inline"><span class="text-info"><span class="big"><b>CATEGORY FILTER &gt;</b></span></span></div>
								<div class="inline">
								<?php
									foreach ($category_list as $sub_category) {
								?>
										<label>
											<input type="checkbox" class="filter-subcategory" value="<?= $sub_category;?>" checked >
											<span style="padding-right: 15px;"><?= $sub_category;?></span>
										</label>
								<?php
									}
								?>
								</div>
								<!-- Thumbnail Size -->
								<div class="inline" style="margin-left: 30px;"><span class="text-info"><span class="big"><b>THUMBNAIL SIZE &gt;</b></span></span></div>
								<div class="inline">
									<label>
										<input type="radio" class="thumbnail-size" name="thumbnail-size" value="small" checked >
										<span style="padding-right: 15px;">Small</span>
									</label>
								</div>
								<div class="inline">
									<label>
										<input type="radio" class="thumbnail-size" name="thumbnail-size" value="medium" >
										<span style="padding-right: 15px;">Medium</span>
									</label>
								</div>
							</div>
						</div>
						<!-- Show filtered pictures -->
						<div class="row" style="margin-bottom: 10px;">
							<div class="col-sm-12">
								<div class="inline"><span class="text-info"><span class="lead"><b>VIEW &gt;</b></span></span></div>
								<div class="inline">
									<a class="btn btn-info show-action currently-showing" id="show-all">All <span id="show-all-count"></span></a>
								</div>
								<div class="inline">
									<a class="btn btn-info show-action" id="show-unscored">Unscored <span id="show-unscored-count"></span></a>
								</div>
								<div class="inline" style="margin-right: 0px;">
									<input type="text" class="form-control show-action" placeholder="Score[+]" size="10" id="show-score-input" >
								</div>
								<div class="inline" style="margin-right: 30px;">
									<a class="btn btn-info show-action" id="show-score">Show</a>
								</div>
								<div class="inline" style="margin-right: 0px;">
									<select id="show-bucket-select" class="form-control show-action" >
										<option value="bucket_1">Bucket 1</option>
										<option value="bucket_2">Bucket 2</option>
										<option value="bucket_3">Bucket 3</option>
										<option value="bucket_4">Bucket 4</option>
										<option value="bucket_5">Bucket 5</option>
									</select>
								</div>
								<div class="inline" style="margin-right: 30px;">
									<a class="btn btn-info show-action" id="show-bucket">View</a>
								</div>
								<div class="inline" style="margin-right: 0px;">
									<select id="show-award-select" class="form-control show-action" >
									<?php
										foreach($awardList as $award_id => $award_name) {
									?>
										<option value="<?= $award_id;?>" ><?= $award_name;?></option>
									<?php
										}
									?>
									</select>
								</div>
								<div class="inline">
									<a class="btn btn-info show-action" id="show-award">View</a>
								</div>
								<div class="inline" style="width: 40px;"></div>
								<!-- <div class="inline">
									<button class="btn btn-danger organize-action" id="organize-view-remove" disabled >Remove from Current View</button>
								</div> -->
							</div>
						</div>
						<!-- Organize -->
						<div class="row">
							<div class="col-sm-12">
								<div class="inline"><span class="text-info"><span class="lead"><b>ORGANIZE &gt;</b></span></span></div>
								<div class="inline" style="margin-right: 0px;">
									<select id="organize-bucket-select" class="form-control organize-action" style="width: 150px;" >
										<option value="bucket_1">Bucket 1</option>
										<option value="bucket_2">Bucket 2</option>
										<option value="bucket_3">Bucket 3</option>
										<option value="bucket_4">Bucket 4</option>
										<option value="bucket_5">Bucket 5</option>
									</select>
								</div>
								<div class="inline" style="margin-right: 30px;">
									<a class="btn btn-info organize-action" id="organize-bucket-add">Add</a>
									<a class="btn btn-info organize-action" id="organize-bucket-move" disabled>Move</a>
								</div>
								<div class="inline" style="width: 20px;"></div>
								<div class="inline" style="margin-right: 0px;">
									<select id="organize-award-select" class="form-control organize-action" >
									<?php
										foreach($awardList as $award_id => $award_name) {
									?>
										<option value="<?= $award_id;?>"><?= $award_name;?></span></option>
									<?php
										}
									?>
									</select>
								</div>
								<div class="inline" style="margin-right: 30px;">
									<a class="btn btn-info organize-action" id="organize-award-copy">Copy</a>
									<a class="btn btn-info organize-action" id="organize-award-move" disabled>Move</a>
								</div>
							</div>
						</div>
						<!-- end of Filter Row -->
					</div>
					<div class="col-sm-1">
						<div id="working-sign" style="display: none;">
							<!-- <div style="display: inline-block;">
								<span class="text-danger" style="margin-top: 15px;"><big><big><b>Working</b></big></big></span>
							</div>
							<div style="display: inline-block;"> -->
								<div style="width: 100%;" class="thumbnail">
									<img src="img/progress_bar.gif" style="width: 40px;">
									<p class="text-danger text-center"><b>Please Wait</b></p>
								</div>
							<!-- </div> -->
						</div>
					</div>
				</div>
			</div>	<!-- End of well -->

			<!-- Thumbnails Panel -->
			<div class="hpanel">
				<div class="panel-body" style="height: 900px; overflow-y: scroll;">
					<!-- <form method="post" class="form-inline"> -->
						<h4 class="text-info">Showing : <span id="filter-text"><?php echo $filterText; ?></span> <small>(for the selected categories)</small></h4>
						<p>
							<a href="#" style="color: blue;" onclick="check_all()">Check ALL</a>
							<a href="#" style="color: gray; padding-left: 15px;" onclick="uncheck_all()">Uncheck ALL</a>
							<a href="#" style="color: red; padding-left: 15px; display: none;" id="organize-view-remove">Remove all Checked</a>
							<!-- <a href="#" style="color: green; padding-left: 15px;" id="resume_slideshow">Resume Projection</a> -->
						</p>
						<p>
							<span class="text-success" style="display: none; padding-right: 15px;" id="show-num-pics-selected">
								Selected <span id="num-pics-selected">0</span> pictures
							</span>
							<span class="text-success" style="display: none;" id="organize-successful" >Add/Copy operation successful</span>
						</p>
						<div class="lightBoxGallery" id="gallery" style="text-align:left">
						<?php
							$sql = $main_query;
							$pics = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
							$max = mysqli_num_rows($pics);
						?>
							<p><b>Number of Pictures Displayed : <span id="filtered-num-pics"><?= $max;?></span></b></p>
						<?php
							$idx = 0;
							while($tr_pics = mysqli_fetch_array($pics) ) {
								$idx++;
								$profile_id = $tr_pics['profile_id'];
								$pic_id = $tr_pics['pic_id'];
								$pic_key = "$profile_id|$pic_id";
								$picfile = $tr_pics['picfile'];
								$pic_section = $tr_pics['section'];
								$pic_alert = $tr_pics['profile_name'] . ' (' .  $profile_id . ')' ;
								$notes = rejection_text($tr_pics['notifications']);
								if (! empty($notes))
									$notes = "*** " . $notes . " ***";

								$pic_title = "[" . $tr_pics['profile_id'] . "|" . $tr_pics['pic_id'] . "] " . $notes  . " {" . $idx . "/" . $max . "}";
								$data_description = $tr_pics['eseq'] . ": " . $idx . "/" . $max;
								$pic_path = "../salons/" . $jury_yearmonth . "/upload/" . $pic_section . "/" . $picfile;
								$tn_path = "../salons/" . $jury_yearmonth . "/upload/" . $pic_section . "/tn/" . $picfile;

								// Find out the Buckets in which this picture has been added
								$pic_buckets = [];
								foreach ($bucketList as $bucket => $pic_list) {
									if (in_array($pic_key, $pic_list))
										$pic_buckets[] = $bucket;
								}

								// Check for awards for this picture
								if (isset($pic_award_list[$pic_key]))
									$pic_awards = implode(",", $pic_award_list[$pic_key]);
								else
									$pic_awards = "";
						?>
							<!-- Thumbnail Container -->
							<div class="<?= $tn_classes;?> containerBox salon-pic" id="pc-<?= $profile_id;?>-<?= $pic_id;?>"
									data-index="<?= $idx;?>"
								 	data-profile-id="<?= $profile_id;?>"
									data-entrant-category="<?= $tr_pics['entrant_category'];?>"
									data-pic-id="<?= $pic_id;?>"
									data-width="<?= $tr_pics['width'];?>"
									data-height="<?= $tr_pics['height'];?>"
									data-exif-json='<?= $tr_pics['exif'];?>'
									data-total-rating="<?= $tr_pics['total_rating'];?>"
									data-num-ratings="<?= $tr_pics['num_rating'];?>"
									data-max-rating="<?= $tr_pics['max_rating'];?>"
									data-min-rating="<?= $tr_pics['min_rating'];?>"
									data-buckets="<?= implode(",", $pic_buckets);?>"
									data-awards="<?= $pic_awards;?>"
							>
								<!-- Selection Checkbox -->
								<input name="checkbox[]" type="checkbox" value="<?= $pic_key;?>" class="pic-checkbox" >
								<div class="pull-right" style="display: inline-block;">
									<!-- User Icon to reveal profile name -->
									<a href="#" style="padding-right: 10px; display: none;" class="reveal-user" onclick="alert('Submitted By: <?=$pic_alert;?> ')" >
										<i class="glyphicon glyphicon-user small"></i>
									</a>
									<!-- Other sections icon -->
									<!-- <a class="pull-right show-other-sections" style="padding-right: 10px; display: none;">
										<i class="glyphicon glyphicon-random small"></i>
									</a> -->
									<!-- Show other sections -->
									<a class="show-other-sections" style="padding-right: 10px;" data-toggle="tooltip" title="Uploads by the same author">
										<i class="glyphicon glyphicon-random small"></i>
									</a>
									<!-- Show History -->
									<a class="show-history" style="padding-right: 10px;" data-toggle="tooltip" title="Accepted in past salons">
										<i class="glyphicon glyphicon-flash small"></i>
									</a>
									<!-- Histogram reveal icon -->
									<a class="show-histogram" data-toggle="tooltip" title="Histogram">
										<i class="glyphicon glyphicon-stats small"></i>
									</a>
									<!-- Exif reveal icon -->
									<a class="show-exif" data-toggle="tooltip" title="EXIF Data">
										<i class="glyphicon glyphicon-info-sign small"></i>
									</a>
								</div>
								<!-- Thumbnail -->
								<div class="col-md-12 col-lg-12 col-xs-12 thumbnail" id="thumbnail-<?=$idx;?>" >
									<a href="<?=$pic_path;?>" title="<?=$pic_title;?>" class="picfile"
											data-description="<?=$data_description;?>" data-gallery="" >
										<img class="lozad" src="img/preview.png" data-src="<?= $tn_path;?>" >
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
					<!-- </form> -->
				</div> <!-- panel body -->
			</div>	<!-- hpanel -->
		</div>	<!-- End of Content -->

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

	<!-- Histogram Modal -->
	<div class="modal" id="histogram-modal" tabindex="-1" role="dialog" >
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">Histogram</h5>
  					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
  					</button>
				</div>
				<div class="modal-body">
					<div class="row">
						<div class="col-sm-12" id="histogram-display">
						</div>
					</div>
				</div>
				<div class="modal-footer">
  					<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</div>

	<!-- View other sections or past images -->
	<div class="modal" tabindex="-1" role="dialog" id="other-pics" aria-labelledby="image-review-header-label">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h5 class="modal-title" id="image-review-header-label"><span id="compared-with-what"></span></h5>
				</div>
				<div class="modal-body">
					<div class="row">
						<div class="col-sm-6">
							<div class='thumbnail' style='width: 100%;'>
								<img id='main-image' class='img-responsive' style='margin-left:auto; margin-right:auto;' >
							</div>
						</div>
						<div class="col-sm-6" id="modal-compared-images" style="height: 600px; overflow-y: scroll;">
							Loading thumbnails requested...
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- The Gallery as lightbox dialog, should be a child element of the document body -->
	<div id="blueimp-gallery" class="blueimp-gallery"
				data-continuous="false"
				data-toggle-slideshow-on-space="false"
				data-full-screen="true"
				data-stretch-images="false"
				data-close-on-slide-click="false"
				data-close-on-swipe-up-or-down="false"
				data-filter=":visible" >
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
<script src="plugin/lightbox/js/lightbox.min.js"></script>
<script src="plugin/photo-histogram/photo-histogram.js"></script>

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

$(document).ready(function(){
	// Install handler to show exif
	$(".show-exif").click(function() {

		// Sav current position on page
		tnLocation = window.scrollY;

		// Display Exif Modal
		let container = $(this).parents(".salon-pic");
		$("#exif-img").attr("src", container.find("img").attr("src"));
		let pic_width = container.attr("data-width");
		let pic_height = container.attr("data-height");
		let exif_json = container.attr("data-exif-json");

		// Build EXIF TABLE
		let exif_html = "<table>";
		if (exif_json == "") {
			exif_html += "<tr><td><b>NO EXIF</b></td><td></td></tr>";
			exif_html += "<tr><td><b>Width</b></td><td>" + pic_width +"</td></tr>";
			exif_html += "<tr><td><b>Height</b></td><td>" + pic_height +"</td></tr>";
		}
		else {
			let exif = JSON.parse(exif_json);
			if (exif.iso)
				exif_html += "<tr><td><b>ISO</b></td><td>" + exif.iso +"</td></tr>";
			if (exif.aperture)
				exif_html += "<tr><td><b>Aperture</b></td><td>" + exif.aperture +"</td></tr>";
			if (exif.speed)
				exif_html += "<tr><td><b>Shutter</b></td><td>" + exif.speed +"</td></tr>";
			if (exif.bias)
				exif_html += "<tr><td><b>Bias</b></td><td>" + exif.bias +"</td></tr>";
			if (exif.metering)
				exif_html += "<tr><td><b>Metering</b></td><td>" + exif.metering +"</td></tr>";
			if (exif.white_balance)
				exif_html += "<tr><td><b>WB</b></td><td>" + exif.white_balance +"</td></tr>";
			if (exif.program)
				exif_html += "<tr><td><b>PGM</b></td><td>" + exif.program +"</td></tr>";
			if (exif.flash)
				exif_html += "<tr><td><b>Flash</b></td><td>" + exif.flash +"</td></tr>";
			if (exif.focal_length)
				exif_html += "<tr><td><b>Focal</b></td><td>" + exif.focal_length +"</td></tr>";
			if (exif.lens)
				exif_html += "<tr><td><b>Lens</b></td><td>" + exif.lens +"</td></tr>";
			if (exif.camera)
				exif_html += "<tr><td><b>Camera</b></td><td>" + exif.camera +"</td></tr>";
			if (exif.date)
				exif_html += "<tr><td><b>Date</b></td><td>" + exif.date +"</td></tr>";
			if (exif.width)
				exif_html += "<tr><td><b>Width</b></td><td>" + exif.width +"</td></tr>";
			else
				exif_html += "<tr><td><b>Width</b></td><td>" + pic_width +"</td></tr>";
			if (exif.height)
				exif_html += "<tr><td><b>Height</b></td><td>" + exif.height +"</td></tr>";
			else
				exif_html += "<tr><td><b>Height</b></td><td>" + pic_height +"</td></tr>";
		}
		exif_html += "</table>"
		$("#exif-display").html(exif_html);

		// Launch the modal view
		$("#exif-modal").modal();

	});

});

// Resyore position on closure of Modal
$("#exif-modal").on("hidden.bs.modal", function(){
	window.scrollTo(0, tnLocation);
});

</script>

<!-- Show Histogram -->
<script>
let hstLocation = 0;

$(document).ready(function(){
	// Install handler to show exif
	$(".show-histogram").click(function() {

		// Sav current position on page
		hstLocation = window.scrollY;

		// Display Exif Modal
		let container = $(this).parents(".salon-pic");
		// $("#histogram-img").attr("src", container.find("img").attr("src"));
		$("#histogram-display").html("");
		let histogram = new PhotoHistogram.Ui($("#histogram-display").get(0), container.find("img").get(0));

		// Launch the modal view
		$("#histogram-modal").modal();

	});

});

// Resyore position on closure of Modal
$("#histogram-modal").on("hidden.bs.modal", function(){
	window.scrollTo(0, hstLocation);
});

</script>

<!-- Compare with past acceptances and other sections -->
<script>
// Handle Modal comparison dialogs
var xOffset = 0;
var yOffset = 0;

// Compare other sections
function show_other_sections(profile_id, pic_id, picfile) {
	// Save location
	xOffset = window.pageXOffset;
	yOffset = window.pageYOffset;

	// Set Main Images
	$("#main-image").attr("src", picfile);

	// Set Images from Other sections
	let yearmonth = "<?= $jury_yearmonth; ?>";
	let section = "<?= $sections;?>";
	let columns = '2';
	$("#modal-compared-images").html("");
	$.post("ajax/get_other_sections.php", {yearmonth, profile_id, pic_id, section, columns}, function(data) {
		$("#modal-compared-images").html(data);
	});

	// Show Modal
	$("#compared-with-what").html("Other uploads by the same author");
	$("#other-pics").modal('show');
}

// Show Past Acceptances
function show_past_acceptances(profile_id, picfile) {
	// Save location
	xOffset = window.pageXOffset;
	yOffset = window.pageYOffset;

	// Set Main Images
	$("#main-image").attr("src", picfile);

	// Set Images from Other sections
	let yearmonth = "<?= $jury_yearmonth; ?>";
	let columns = '2';
	$("#modal-compared-images").html("");
	$.post("ajax/get_past_acceptances.php", {yearmonth, profile_id, columns}, function(data) {
		$("#modal-compared-images").html(data);
	});

	// Show Modal
	$("#compared-with-what").html("Past Acceptances");
	$("#other-pics").modal('show');
}

// Go back to saved location
$(".modal").on("hidden.bs.modal", function(){window.scrollTo(xOffset, yOffset);});

$(document).ready(function(){
	// Get Past Acceptances
	$(".show-history").click(function(){
		let profile_id = $(this).parents(".salon-pic").attr("data-profile-id");
		let picfile = $(this).parents(".salon-pic").find("a.picfile").attr("href");
		show_past_acceptances(profile_id, picfile);
	});
	// Show Other Sections
	$(".show-other-sections").click(function(){
		let profile_id = $(this).parents(".salon-pic").attr("data-profile-id");
		let pic_id = $(this).parents(".salon-pic").attr("data-pic-id");
		let picfile = $(this).parents(".salon-pic").find("a.picfile").attr("href");
		show_other_sections(profile_id, pic_id, picfile);
	});
});

</script>

<!-- All Display Redraw Functions -->
<script>
	$(document).ready(function() {
		var tn_6_per_row = 6;
		var tn_6_classes = "col-sm-2";
		var tn_4_per_row = 4;
		var tn_4_classes = "col-sm-3";
		var tn_per_row = 6;
		var tn_classes = "col-sm-2";
		var yearmonth = "<?= $jury_yearmonth;?>";
		var section = "<?= $sections;?>";
		var current_view = "ALL";
		var current_view_text = "All Images";
		var current_view_param = "NONE";

		// Helper Function
		function get_piclist_for_bucket(bucket) {
			return $(".salon-pic[data-buckets*='" + bucket + "']")
					.map(function(index, elem) {
						return $(elem).attr("data-profile-id") + "|" + $(elem).attr("data-pic-id");
					})
					.get()
					.join();
		}

		// Save buckets to the jury session on database
		function session_bucket_save() {
			// Send to server
			$.post("ajax/projector_bucket_save.php",
					{
						yearmonth : "<?=$jury_yearmonth;?>",
						section : "<?= $sections;?>",
						award_group : "<?= $award_group;?>",
						bucket1_list : get_piclist_for_bucket("bucket_1"),
						bucket2_list : get_piclist_for_bucket("bucket_2"),
						bucket3_list : get_piclist_for_bucket("bucket_3"),
						bucket4_list : get_piclist_for_bucket("bucket_4"),
						bucket5_list : get_piclist_for_bucket("bucket_5"),
					});
		}

		function highlight_active_button(button) {
			// Turnoff border on all the buttons
			$(".filter-btn").css("border", "0");
			// Draw a colored border
			button.css("border", "4px solid #888");
		}


		function show_filtered(filter_text, filter_criteria, param, thumbnail_set) {

			$("#working-sign").show();
			$("#organize-successful").hide();
			$(".pic-checkbox:checked").prop("checked", false);

			setTimeout(function() {

				current_view = filter_criteria;
				current_view_text = filter_text;
				current_view_param = param;
				// Enable Move and Remove buttons for BUCKET and AWARD views
				if (current_view == "BUCKET") {
					$("#organize-bucket-move").attr("disabled", false);
					$("#organize-award-move").attr("disabled", false);
					$("#organize-view-remove").show();
					// $("#organize-view-remove").prop("disabled", false);
				}
				else if (current_view == "AWARD") {
					$("#organize-bucket-move").attr("disabled", true);
					$("#organize-award-move").attr("disabled", true);
					$("#organize-view-remove").show();
					// $("#organize-view-remove").prop("disabled", false);
				}
				else {
					$("#organize-bucket-move").attr("disabled", true);
					$("#organize-award-move").attr("disabled", true);
					$("#organize-view-remove").hide();
					// $("#organize-view-remove").prop("disabled", true);
				}

				let category_selectors = $(".filter-subcategory:checked")
											.map(function(index, elem) { return "[data-entrant-category='" + $(elem).val() + "']"; })
											.get()
											.join();
				let category_list = $(".filter-subcategory:checked")
											.map(function(index, elem) { return $(elem).val(); })
											.get()
											.join();

				let filtered_set = thumbnail_set.filter(category_selectors);

				$("#filter-text").html(filter_text);
				$("#filtered-num-pics").html(filtered_set.length);


				// Hide all thumbnails
				$(".salon-pic").hide();

				// Remove all clearfix statements
				$("div.clearfix").remove();

				// Manage data-gallery property
				// $(".salon-pic").removeProp("data-gallery");
				// filtered_set.prop("data-gallery", "");

				// Remove existing boot-strap class and add currently set class
				filtered_set.removeClass(tn_4_classes);
				filtered_set.removeClass(tn_6_classes);
				// Determine Thumbnail Size
				if ($(".thumbnail-size:checked").val() == "medium") {
					tn_per_row = tn_4_per_row;
					tn_classes = tn_4_classes;
				}
				else {
					tn_per_row = tn_6_per_row;
					tn_classes = tn_6_classes;
				}
				filtered_set.addClass(tn_classes);

				// Add clearfix statements based on setting
				filtered_set.each(function(index, elem) {
					if (index != 0 && (index % tn_per_row) == 0)
						$("<div class='clearfix'></div>").insertBefore(elem);
				});

				// Display thumbnails
				filtered_set.show();

				// Display user-reveal icons & reveav-past-acceptance links if we are looking at award view
				if (current_view == "AWARD") {
					filtered_set.find(".reveal-user").show();		// Concealed user name
				}
				else {
					filtered_set.find(".reveal-user").hide();		// Concealed user name
				}


				// Update Jury Session to reflect view chhange
				$.post("ajax/projector_view_change.php",
						{
							yearmonth : "<?=$jury_yearmonth;?>",
							section : "<?= $sections;?>",
							award_group : "<?= $award_group;?>",
							categories : category_list,
							filter_criteria : filter_criteria,
							bucket : param,
						});

				$("#working-sign").hide();

			}, 100);

		}

		// Show ALL
		$("#show-all").click(function() {
			show_filtered("All pictures", "ALL", "NONE", $(".salon-pic"));
		});

		// Show UNSCORED
		$("#show-unscored").click(function() {
			let thumbnail_set = $(".salon-pic[data-num-ratings!='<?= $num_juries;?>']");
			show_filtered("Scoring incomplete", "UNSCORED", "NONE", thumbnail_set);
		});

		// Filter by score
		$("#show-score").click(function(){
			let score_plus = false;
			let score = $("#show-score-input").val();
			if (score.endsWith("+")) {
				score_plus = true;
				score = score.substr(0, score.length - 1);
			}
			// Filter on score
			let thumbnail_set = $(".salon-pic").filter(function(index, elem){
				let rating = $(elem).attr("data-total-rating");
				if (score_plus)
					return Number(rating) >= Number(score);
				else
					return Number(rating) == Number(score);
			});
			// Refresh Page
			show_filtered("Total Score" + (score_plus ? " >= " : " = ") + score, "RATING", $("#show-score-input").val(), thumbnail_set);
		});

		// Show a bucket
		$("#show-bucket").click(function(){
			let bucket = $("#show-bucket-select").val();
			// Filter on score
			let thumbnail_set = $(".salon-pic[data-buckets*='" + bucket + "']");
			// Refresh Page
			show_filtered($("#show-bucket-select").find(":selected").text(), "BUCKET", bucket, thumbnail_set);
		});

		// Show award
		$("#show-award").click(function(){
			let award_id = $("#show-award-select").val();
			let award_name = $("#show-award-select").find(":selected").text();
			// Filter on score
			// let thumbnail_set = $(".salon-pic[data-awards*='" + award_id + "']");
			let thumbnail_set = $(".salon-pic[data-awards!='']")
									.filter(function(index, pic) {
										return $(pic).attr("data-awards").split(",").includes(award_id);
									});
			// Refresh Page
			show_filtered("Awarded " + award_name, "AWARD", award_id, thumbnail_set);
		});

		// Add to Bucket
		$("#organize-bucket-add").click(function(){
			if ($(".pic-checkbox:checked").length > 0) {
				let target_bucket = $("#organize-bucket-select").val();
				// Update data-buckets
				$(".pic-checkbox:checked").each(function(index, elem){
					let bucket_list = $(elem).parent().attr("data-buckets").split(",");
					if (! bucket_list.includes(target_bucket))
						bucket_list.push(target_bucket);
					$(elem).parent().attr("data-buckets", bucket_list.join());
				});
				// Uncheck the checkboxes
				$(".pic-checkbox:checked").prop("checked", false);
				$("#show-num-pics-selected").hide();
				// Update Jury Session
				session_bucket_save();
				// Display success message
				$("#organize-successful").show();
			}
			else {
				alert("No picture has been selected");
			}
		});

		// Move to Bucket
		$("#organize-bucket-move").click(function(){
			if ($(".pic-checkbox:checked").length > 0) {
				// Add to the target bucket
				let target_bucket = $("#organize-bucket-select").val();
				let current_bucket = current_view_param;
				if (current_bucket == target_bucket) {
					alert("Source and Destination buckets are the same !");
				}
				else {
					// Update data-buckets
					$(".pic-checkbox:checked").each(function(index, elem){
						let bucket_list = $(elem).parent().attr("data-buckets").split(",");
						// Add bucket to the picture
						if (! bucket_list.includes(target_bucket))
							bucket_list.push(target_bucket);
						// Remove current bucket from the picture
						if (bucket_list.includes(current_bucket)) {
							bucket_list = bucket_list.filter(function(bucket){
								return bucket != current_bucket;
							});
						}
						$(elem).parent().attr("data-buckets", bucket_list.join());
					});
					// Uncheck the checkboxes
					$(".pic-checkbox:checked").prop("checked", false);
					$("#show-num-pics-selected").hide();
					// Refresh bucket view
					let thumbnail_set = $(".salon-pic[data-buckets*='" + current_bucket + "']");
					// Refresh Page
					show_filtered(current_view_text, current_view, current_view_param, thumbnail_set);
					// Update Jury Session
					session_bucket_save();
					// Display success message
					$("#organize-successful").show();
				}
			}
			else {
				alert("No picture has been selected");
			}
		});

		// Function to save pic_result assignments
		function award_remove(award_id, pic_list) {
			// Add selected pictures to to the award in pic_result
			$.post("ajax/projector_award_assign.php",
				{
					yearmonth : "<?=$jury_yearmonth;?>",
					award_id : award_id,
					pic_list : pic_list,
					action : "del",
				},
				function(data, status) {
					if (status == "success") {
						let result = JSON.parse(data);
						if (result.status == "OK") {
							let refresh_required = false;
							result.pics.forEach(function(pic) {
								if (pic.action_completed) {

									// remove award from data-awards
									let salon_pic = $(".salon-pic[data-profile-id='" + pic.profile_id + "'][data-pic-id='" + pic.pic_id + "']");
									let award_list = salon_pic.attr("data-awards").split(",");
									if (award_list.includes(award_id)) {
										award_list = award_list.filter(function(existing_award){
											return award_id != existing_award;
										});
										salon_pic.attr("data-awards", award_list.join());
										refresh_required = true;
									}

								}
							});
							// Refresh the screen if anything has been removed
							if (refresh_required) {
								// Refresh award view
								let thumbnail_set = $(".salon-pic[data-awards*='" + current_view_param + "']");
								// Refresh Page
								show_filtered(current_view_text, current_view, current_view_param, thumbnail_set);
							}
						}
					}
				}
			);
		}


		// Remove picture from current view
		// Update bucket to jusy session
		// Delete from pic_result in case of award
		$("#organize-view-remove").click(function(){
			if ($(".pic-checkbox:checked").length > 0) {
				if (current_view == "AWARD") {
					// Gather data
					let award_id = current_view_param;
					let pic_list = $(".pic-checkbox:checked")
										.map(function(index, elem) {
											return $(elem).parent().attr("data-profile-id") + "|" + $(elem).parent().attr("data-pic-id");
										})
										.get()
										.join();
					// Remove award from pic_result & Update View
					award_remove(award_id, pic_list);
				}
				else if (current_view == "BUCKET") {
					let current_bucket = current_view_param;
					// Update data-buckets
					$(".pic-checkbox:checked").each(function(index, elem){
						let bucket_list = $(elem).parent().attr("data-buckets").split(",");
						// Remove current bucket from the picture
						if (bucket_list.includes(current_bucket)) {
							bucket_list = bucket_list.filter(function(bucket){
								return bucket != current_bucket;
							});
						}
						$(elem).parent().attr("data-buckets", bucket_list.join());
					});
					// Refresh bucket view
					let thumbnail_set = $(".salon-pic[data-buckets*='" + current_bucket + "']");
					// Refresh Page
					show_filtered(current_view_text, current_view, current_view_param, thumbnail_set);
					// Update Jury Session
					session_bucket_save();
				}
				// Uncheck the checkboxes
				$(".pic-checkbox:checked").prop("checked", false);
				$("#show-num-pics-selected").hide();
			}
			else {
				alert("No picture has been selected");
			}
		});

		// Function to save pic_result assignments
		function award_assign(award_id, pic_list, remove_from_bucket = false) {
			// Add selected pictures to to the award in pic_result
			$.post("ajax/projector_award_assign.php",
				{
					yearmonth : "<?=$jury_yearmonth;?>",
					award_id : award_id,
					pic_list : pic_list,
					action : "add",
				},
				function(data, status) {
					if (status == "success") {
						let result = JSON.parse(data);
						if (result.status == "OK") {
							let refresh_required = false;
							result.pics.forEach(function(pic) {
								if (pic.action_completed) {

									// add to/remove award from data-awards
									// This operation is performed only to add or move picture from a bucket as current view
									// Hence no need to refrsh this award on the screen
									let salon_pic = $(".salon-pic[data-profile-id='" + pic.profile_id + "'][data-pic-id='" + pic.pic_id + "']");
									let award_list = salon_pic.attr("data-awards").split(",");
									if (! award_list.includes(award_id))
										award_list.push(award_id);
									salon_pic.attr("data-awards", award_list.join());

									// If it is a Move operation,
									// Remove current bucket from bucket_list and refresh view
									if (current_view == "BUCKET" && remove_from_bucket) {
										let bucket_list = salon_pic.attr("data-buckets").split(",");
										let current_bucket = current_view_param;
										if (bucket_list.includes(current_bucket)) {
											bucket_list = bucket_list.filter(function(bucket){
												return bucket != current_bucket;
											});
										}
										salon_pic.attr("data-buckets", bucket_list.join());
										refresh_required = true;
									}
								}
							});
							// Refresh the screen if anything has changed
							if (refresh_required) {
								// Refresh bucket view
								let thumbnail_set = $(".salon-pic[data-buckets*='" + current_view_param + "']");
								// Update buckets to Jury Session
								session_bucket_save();
								// Refresh Page
								show_filtered(current_view_text, current_view, current_view_param, thumbnail_set);
							}
						}
					}
				}
			);
		}

		// Assign pictures to award
		$("#organize-award-copy").click(function(){
			if ($(".pic-checkbox:checked").length > 0) {
				// Gather data
				let award_id = $("#organize-award-select").val();
				let pic_list = $(".pic-checkbox:checked")
									.map(function(index, elem) {
										return $(elem).parent().attr("data-profile-id") + "|" + $(elem).parent().attr("data-pic-id");
									})
									.get()
									.join();
				// Save award to database
				award_assign(award_id, pic_list);
				// Uncheck the checkboxes
				$(".pic-checkbox:checked").prop("checked", false);
				$("#show-num-pics-selected").hide();
				// Display success message
				$("#organize-successful").show();
			}
			else {
				alert("No picture has been selected");
			}
		});

		// Assign pictures to award and remove from bucket
		$("#organize-award-move").click(function(){
			if ($(".pic-checkbox:checked").length > 0) {
				// Gather data
				let award_id = $("#organize-award-select").val();
				let pic_list = $(".pic-checkbox:checked")
									.map(function(index, elem) {
										return $(elem).parent().attr("data-profile-id") + "|" + $(elem).parent().attr("data-pic-id");
									})
									.get()
									.join();
				// Save award to database with move flag
				award_assign(award_id, pic_list, true);
				// Uncheck the checkboxes
				$(".pic-checkbox:checked").prop("checked", false);
				$("#show-num-pics-selected").hide();
				// Display success message
				$("#organize-successful").show();
			}
			else {
				alert("No picture has been selected");
			}
		});

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
	// $("#gallery:visible :checkbox").each(function () { $(this).prop("checked", true) });
	$(".pic-checkbox:visible").prop("checked", true);
	$("#organize-successful").hide();
	$("#num-pics-selected").html($(".pic-checkbox:checked").length);
	$("#show-num-pics-selected").show();
}

function uncheck_all() {
	$(".pic-checkbox").prop("checked", false);
	$("#organize-successful").hide();
	$("#show-num-pics-selected").hide();
}

$(document).ready(function(){
	$(".pic-checkbox").change(function(){
		// Turn of any add/move messages
		$("#organize-successful").hide();
		$("#num-pics-selected").html($(".pic-checkbox:checked").length);
		$("#show-num-pics-selected").show();
	});
});

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
