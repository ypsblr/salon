<?php
//
// review_image - Display all portfolios in descending order of modified time
// 2019-11-21 - Modified to show each portfolio completely and optimize loading of past acceptances
// 2020-05-28 - Added differentiation for what causes error and what applies to print/digital
//
// session_start();
include("inc/session.php");
include ("inc/connect.php");
include ("inc/lib.php");
include ("inc/contest_lib.php");
include ("inc/blacklist_lib.php");

// Strip String of special characters and space and force lower case
function strip_string($str) {
	$ret_str = "";
	$str = strtolower($str);
	for ($i = 0; $i < strlen($str); ++$i) {
		if ( ($str[$i] >= 'a' && $str[$i] <= 'z') || ($str[$i] >= '0' && $str[$i] <= '9') )
			$ret_str .= $str[$i];
	}

	return $ret_str;
}

// Compare two strings for a match after removing space and special characters and comparing equal case
function match_strings ($str1, $str2) {

	$str1 = strip_string($str1);
	$str2 = strip_string($str2);

	return ($str1 == $str2);

}

// function exif_str($exif_json) {
// 	if ($exif_json == "")
// 		return "NO EXIF";
//
// 	try {
// 		$exif = json_decode($exif_json);
// 		$exif_strings = [];
// 		if (isset($exif['iso']))
// 			$exif_strings[] = "ISO " . $exif['iso'];
// 		if (isset($exif['aperture']))
// 			$exif_strings[] = $exif['aperture'];
// 		if (isset($exif['speed']))
// 			$exif_strings[] = $exif['speed'];
// 		if (isset($exif['program']))
// 			$exif_strings[] = $exif['program'];
// 		return implode(", ", $exif_strings);
// 	}
// 	catch(Exception $e) {
// 		return "";
// 	}
// }

function blacklist_match($pic) {
	$blacklist_match = $pic['blacklist_match'];
	$blacklist_exception = $pic['blacklist_exception'];
	if ($blacklist_match == "" && $blacklist_exception == 0) {
		list($blacklist_match, $blacklist_name) = check_blacklist($pic['profile_name'], $pic['email'], $pic['phone']);
		if ($blacklist_match != "MATCH" && $blacklist_match != "SIMILAR")
			$blacklist_match = "";
	}
	return $blacklist_match;
}


if ( isset($_REQUEST['section']) && isset($_SESSION['admin_id']) && isset($_SESSION['admin_yearmonth']) ) {
	// Set up user and contest information
	include ("inc/load_common_data.php");

    $section_param = decode_string_array(trim($_REQUEST['section']));

	$sql = "SELECT * FROM section WHERE yearmonth = '$admin_yearmonth' AND section = '$section_param' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_num_rows($query) == 0) {
		$_SESSION['err_msg'] = "Section " . $section_param . " not found.";
		// header("Location: " . $_SERVER['HTTP_REFERER']);
		// printf("<script>location.href='".$_SERVER['HTTP_REFERER']."'</script>");
		header("Location: entry_dashboard.php");
		printf("<script>location.href='entry_dashboard.php'</script>");
		die();
	}
	$section_row = mysqli_fetch_array($query, MYSQLI_ASSOC);
	$review_section = $section_row['section'];
	$section_stub = $section_row['stub'];
	$section_type = $section_row['section_type'];

	if ($user_row['reviewed'] == NULL) {
		$reviewed_till = "";
		$reviewed_profiles = [];
	}
	else {
		$tmp = json_decode($user_row['reviewed'], true);
		if (isset($tmp[$review_section])) {
			$reviewed_till = $tmp[$review_section]['reviewed_till'];
			$reviewed_profiles = $tmp[$review_section]['reviewed_profiles'];
		}
		else {
			$reviewed_till = "";
			$reviewed_profiles = [];
		}
		unset($tmp);
	}

	// Get Notifications List
	$sql  = "SELECT template_code, template_name ";
	$sql .= "  FROM email_template, section ";
	$sql .= " WHERE section.yearmonth = '$admin_yearmonth' ";
	$sql .= "   AND section.section = '$review_section' ";
	$sql .= "   AND	template_type = 'user_notification' ";
	$sql .= "   AND will_cause_rejection = '1' ";
	$sql .= "   AND ( (section.section_type = 'P' AND applies_to_print = '1') OR ";
	$sql .= "         (section.section_type = 'D' AND applies_to_digital = '1') ) ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$rejection_reasons = array();
	while ($row = mysqli_fetch_array($query))
		$rejection_reasons[$row['template_code']] = $row['template_name'];


	// Get a list of User Notification Email names
	$notifications = [];
	$sql  = "SELECT * FROM email_template ";
	$sql .= " WHERE template_type = 'user_notification' ";
	if ($section_type == "P")
		$sql .= "  AND applies_to_print = '1' ";
	else
		$sql .= "  AND applies_to_digital = '1' ";
	$sql .= " ORDER BY template_name";
	$notq = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	while ($notr = mysqli_fetch_array($notq)) {
		$notifications[$notr['template_code']] = $notr;
	}

	// Get a list of awarded pictures
	$awarded_pic_list = [];
	$sql  = "SELECT profile_id, pic_id, pic_result.award_id, award.award_name FROM pic_result, award ";
	$sql .= " WHERE pic_result.yearmonth = '$admin_yearmonth' ";
	$sql .= "   AND award.yearmonth = pic_result.yearmonth ";
	$sql .= "   AND award.award_id = pic_result.award_id ";
	$sql .= "   AND award.section = '$review_section' ";
	$sql .= "   AND award.level < 99 ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	while ($row = mysqli_fetch_array($query)) {
		$awarded_pic_list[$row['profile_id'] . "-" . $row['pic_id']] = array("award_id" => $row['award_id'], "award_name" => $row['award_name']);
	}

	// Build Main Query
	// Generate list of Pictures
	$mainsql  = "SELECT pic.*, profile_name, profile.email, profile.phone, blacklist_match, blacklist_exception, IFNULL(member_name, '') AS reviewed_by ";
	if ($contest_archived)
		$mainsql .= "  FROM profile, ar_pic pic LEFT JOIN team ON team.yearmonth = pic.yearmonth AND team.member_id = pic.reviewer_id ";
	else
		$mainsql .= "  FROM profile, pic LEFT JOIN team ON team.yearmonth = pic.yearmonth AND team.member_id = pic.reviewer_id ";
	$mainsql .= " WHERE pic.yearmonth = '$admin_yearmonth' ";
	$mainsql .= "   AND pic.section = '$review_section' ";
	// if (isset($_REQUEST['only_notified']))
	// 	$mainsql .= "   AND pic.notifications != '' ";
	// elseif (isset($_REQUEST['only_reviewed']))
	// 	$mainsql .= "   AND pic.reviewed = '1' ";
	// elseif (isset($_REQUEST['only_unreviewed']))
	// 	$mainsql .= "   AND pic.reviewed = '0' ";
	// elseif (isset($_REQUEST['only_flagged_no_acc']))
	// 	$mainsql .= "   AND pic.recommended_rating = '2' ";
	$mainsql .= "   AND profile.profile_id = pic.profile_id ";
	// Do not optimize options used by reviewer of reviews
	if (isset($_REQUEST['only_reviewed']) || isset($_REQUEST['only_notified']) || isset($_REQUEST['only_flagged_no_acc']) ||
			isset($_REQUEST['only_reviewed_not_flagged']))
		$mainsql .= " ORDER BY reviewed ASC, modified_date DESC ";
	else
		$mainsql .= " ORDER BY reviewed ASC, pic.profile_id ASC, modified_date DESC ";

	// Set all_pics flag
	if (isset($_REQUEST['only_reviewed']) || isset($_REQUEST['only_notified']) || isset($_REQUEST['only_flagged_no_acc']) ||
			isset($_REQUEST['only_reviewed_not_flagged']) || isset($_REQUEST['only_notreviewed']) || isset($_REQUEST['only_awarded']) )
		$all_pics_view = false;
	else
		$all_pics_view = true;


?>
<!DOCTYPE html>
<html>

<head>
    <!-- Page title -->
	<title>Youth Photographic Society | Picture Review Panel</title>

	<?php include "inc/header.php"; ?>

    <!-- <link rel="stylesheet" href="plugin	/blueimp-gallery/css/blueimp-gallery.min.css" />
	<link rel="stylesheet" href="plugin/lightbox/css/lightbox.min.css" /> -->
	<!-- <style>
		table.pics-table {
			width: 100%;
			border : 0;
			border-collapse: collapse;
		}
		table.pics-table, tr {
			border-top : 1px solid lightgray;
			border-bottom : 1px solid lightgray;
		}
		table.pics-table, td {
			padding : 8px;
		}
		tr.reviewed {
			background-color : #eafaf1 ! important;
		}
	</style> -->

</head>
<body class="fixed-navbar fixed-sidebar">

	<!-- Simple splash screen-->
	<div class="splash">
		<div class="color-line"></div>
		<div class="splash-title">
			<h1>    YOUTH PHOTOGRAPHIC SOCIETY | ADMIN PANEL  </h1>
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

<!-- Header -->
<?php
include "inc/master_topbar.php";
include "inc/master_sidebar.php";
?>

	<!-- Main Wrapper -->
	<div id="wrapper">

		<div class="normalheader transition animated fadeIn">
			<div class="hpanel">
				<div class="panel-body">
					<a class="small-header-action" href="#">
						<div class="clip-header">
							<i class="fa fa-arrow-up"></i>
						</div>
					</a>
					<h3 class="font-light m-b-xs">
						All Images under <?php echo $review_section;?> for <?=$admin_contest_name;?>
					</h3>
					<!-- Restrict view to pictures with notifications -->
					<p>
						<div style="display: inline-block; margin-right: 10px;">
							<a href="review_image_dash.php?section=<?= $_REQUEST['section'];?>" class="btn btn-info"
									style="<?= $all_pics_view ? 'color:black; font-weight:bold;': '';?>" >
								<i class="fa fa-picture-o"></i> All Pictures [<span id="all-pics"></span>]
							</a>
						</div>
						<div style="display: inline-block; margin-right: 10px;">
							<a href="review_image_dash.php?only_reviewed&section=<?= $_REQUEST['section'];?>" class="btn btn-info"
									style="<?= isset($_REQUEST['only_reviewed']) ? 'color:black; font-weight:bold;': '';?>" >
								<i class="fa fa-check-square-o"></i> Reviewed [<span id="reviewed-pics"></span>]
							</a>
						</div>
						<div style="display: inline-block; margin-right: 10px;">
							<a href="review_image_dash.php?only_notified&section=<?= $_REQUEST['section'];?>" class="btn btn-danger"
									style="<?= isset($_REQUEST['only_notified']) ? 'color:black; font-weight:bold;': '';?>" >
								<i class="fa fa-exclamation-triangle"></i> Notified [<span id="notified-pics"></span>]
							</a>
						</div>
						<div style="display: inline-block; margin-right: 10px;">
							<a href="review_image_dash.php?only_flagged_no_acc&section=<?= $_REQUEST['section'];?>" class="btn btn-warning"
									style="<?= isset($_REQUEST['only_flagged_no_acc']) ? 'color:black; font-weight:bold;': '';?>" >
								<i class="fa fa-thumbs-o-down"></i> Flagged (No-Acceptance) [<span id="flagged-pics"></span>]
							</a>
						</div>
						<div style="display: inline-block; margin-right: 10px;">
							<a href="review_image_dash.php?only_reviewed_not_flagged&section=<?= $_REQUEST['section'];?>" class="btn btn-success"
									style="<?= isset($_REQUEST['only_reviewed_not_flagged']) ? 'color:black; font-weight:bold;': '';?>" >
								<i class="fa fa-thumbs-o-up"></i> Reviewd &amp; OK [<span id="not-flagged-pics"></span>]
							</a>
						</div>
						<div style="display: inline-block; margin-right: 10px;">
							<a href="review_image_dash.php?only_notreviewed&section=<?= $_REQUEST['section'];?>" class="btn btn-info"
									style="<?= isset($_REQUEST['only_notreviewed']) ? 'color:black; font-weight:bold;': '';?>" >
								<i class="fa fa-square-o"></i> Not reviewed [<span id="not-reviewed-pics"></span>]
							</a>
						</div>
						<?php
							if (has_permission($member_permissions, ["admin"])) {
						?>
						<div style="display: inline-block; margin-right: 10px;">
							<a href="review_image_dash.php?only_awarded&section=<?= $_REQUEST['section'];?>" class="btn btn-default"
									style="<?= isset($_REQUEST['only_awarded']) ? 'color:black; font-weight:bold;': '';?>" >
								<i class="fa fa-trophy"></i> Awarded [<span id="awarded-pics"></span>]
							</a>
						</div>
						<?php
							}
						?>
					</p>
				</div>
			</div>
		</div>


		<!-- <div class="content animate-panel"> -->
		<div class="content">
			<div class="row">
				<div class="col-lg-12">
					<div class="hpanel">
						<div class="panel-head">
							<b><big>Thumbnails (most recent first). Click on any thumbnail to start reviewing.</big></b>
						</div>
						<div class="panel-body">
						<?php
							if (isset($_REQUEST['last_pic'])) {
						?>
							<p><a id="find-last-pic" data-key="<?= $_REQUEST['last_pic'];?>" class="text-info">Find the last reviewed picture</a></p>
						<?php
							}
						?>
							<div class="row">
						<?php
							$pq = mysqli_query($DBCON, $mainsql)or sql_error($mainsql, mysqli_error($DBCON), __FILE__, __LINE__);

							$idx = 0;
							$pic_list = [];
							$all_pics = mysqli_num_rows($pq);
							$reviewed_pics = 0;
							$not_reviewed_pics = 0;
							$notified_pics = 0;
							$flagged_pics = 0;
							$not_flagged_pics = 0;
							$awarded_pics = 0;
							// Render table rows
							while ($pic = mysqli_fetch_array($pq, MYSQLI_ASSOC)) {
								// Update Counters
								$reviewed_pics += ($pic['reviewed'] == '1' ? 1 : 0);
								$not_reviewed_pics += ($pic['reviewed'] == '0' ? 1 : 0);
								$notified_pics += ($pic['notifications'] == '' ? 0 : 1);
								$flagged_pics += ($pic['no_accept'] == '1' ? 1 : 0);
								$not_flagged_pics += (($pic['reviewed'] == '1' && $pic['no_accept'] == '0' && $pic['notifications'] == '') ? 1 : 0);
								$awarded_pics += ( isset($awarded_pic_list[$pic['profile_id'] . "-" . $pic['pic_id']]) ? 1 : 0);
								// Apply Filters
								if (isset($_REQUEST['only_notified']) && $pic['notifications'] == "") {
									continue;
								}
								elseif (isset($_REQUEST['only_reviewed']) && $pic['reviewed'] == '0') {
									continue;
								}
								elseif (isset($_REQUEST['only_notreviewed']) && $pic['reviewed'] == '1') {
									continue;
								}
								elseif (isset($_REQUEST['only_flagged_no_acc']) && $pic['no_accept'] == '0') {
									continue;
								}
								elseif ( isset($_REQUEST['only_reviewed_not_flagged']) &&
											($pic['reviewed'] == '0' || $pic['no_accept'] == '1' || $pic['notifications'] != '')) {
									continue;
								}
								if (isset($_REQUEST['only_awarded'])) {
									$award_key = $pic['profile_id'] . "-" . $pic['pic_id'];
									if ( isset($awarded_pic_list[$award_key])) {
										$award_id = $awarded_pic_list[$award_key]['award_id'];
										$award_name = $awarded_pic_list[$award_key]['award_name'];
									}
									else
										continue;
								}

								// Build PIC List
								$pic_list[] = [$pic['profile_id'], $pic['pic_id']];

								// Assemble Data
								$blacklist_match = blacklist_match($pic);
								$is_reviewed = ($pic['reviewed'] == 1);
								$is_flagged = ($pic['no_accept'] == 1);
								$pic_id = $pic['pic_id'];
								$pic_src = "../salons/" . $admin_yearmonth . "/upload/" . $pic['section'] . "/tn/" . $pic['picfile'];
								$width = $pic['width'];
								$height = $pic['height'];
								$reviewed_by = $pic['reviewed_by'];
								$full_pic_uploaded = false;

								// Get width and height of uploaded high resolution picture
								if (isset($_REQUEST['only_awarded'])) {
									if ( $pic['full_picfile'] != NULL && $pic['full_picfile'] != "") {
										$full_picfile = "../salons/" . $admin_yearmonth . "/upload/" . $pic['section'] . "/full/" . $pic['full_picfile'];
										if (file_exists($full_picfile)) {
											$full_pic_uploaded = true;
											list ($width, $height) = getimagesize($full_picfile);
										}
									}
								}

								// Assemble Notifications
								// Store dates of notifications in the array with notification code as the key
								$pic_notifications = (trim($pic['notifications']) == "") ? [] : explode("|", $pic['notifications']);
								$rejection_text = "";
								foreach ($pic_notifications AS $pic_notification) {
									if ($pic_notification != "") {
										list($notification_date, $notification_code_str) = explode(":", $pic_notification);
										$notification_codes = explode(",", $notification_code_str);
										$rejected = false;
										foreach ($notification_codes as $notification_code)
											if (isset($notifications[$notification_code])) {
												$rejection_text .= (($rejection_text == "") ? "" : ",") . $notifications[$notification_code]['template_name'];
											}
									}
								}

								// Determine the color of the box
								$tn_background = "#fff";
								if ($is_reviewed) {
									if (sizeof($pic_notifications) > 0)
										$tn_background = "#ffcccc";		// pastel red
									elseif ($is_flagged)
										$tn_background = "#ffffcc";		// pastel yellow
									else
										$tn_background = "#ccffcc";		// pastel green
								}

								// Fix icons
						?>
								<div class="col-sm-2 containerBox salon-pic" data-idx="<?= $idx;?>" >
									<div>
										<span class="text-info"><b>#<?= $idx + 1;?></b></span>
										<span class="text" style="margin-left: 8px;">[<b><?= $pic['eseq'];?></b>]</span>
										<div class="pull-right" style="display: inline-block;">
										<?php
											if ($blacklist_match != "") {
										?>
											<i class="fa fa-dot-circle-o" data-toggle="tooltip" title="User in Restricted Lists"></i>
										<?php
											}
											if (sizeof($pic_notifications) > 0) {
										?>
											<i class="fa fa-exclamation-triangle" style="margin-left: 4px;" data-toggle="tooltip" title="Has notifications"></i>
										<?php
											}
											if ($is_flagged) {
										?>
											<i class="fa fa-flag-o" style="margin-left: 4px;" data-toggle="tooltip" title="Acceptance not recommended"></i>
										<?php
											}
											if ($is_reviewed) {
										?>
											<i class="fa fa-check-square-o" style="margin-left: 4px;"data-toggle="tooltip" title="Picture reviewed by <?= $reviewed_by;?>"></i>
										<?php
											}
											else {
										?>
											<i class="fa fa-square-o" style="margin-left: 4px;" data-toggle="tooltip" title="Not Reviewed"></i>
										<?php
											}
											if (isset($_REQUEST['only_notified'])) {
												$upload_code = implode("|", [$admin_yearmonth, $pic['profile_id'], $pic['pic_id'], $_SESSION['admin_id']]);
										?>
										<a href="/upload_rectified.php?code=<?= encode_string_array($upload_code);?>" target="_blank">
											<i class="fa fa-upload" style="margin-left: 4px;" data-toggle="tooltip" title="Upload replacement picture"></i>
										</a>
										<?php
											}
											if (isset($_REQUEST['only_awarded'])) {
												$upload_code = implode("|", [$admin_yearmonth . "-" . $_SESSION['admin_id'], $award_id, $pic['profile_id'], $pic['pic_id']]);
										?>
										<a href="/upload_awarded.php?code=<?= encode_string_array($upload_code);?>" target="_blank">
											<i class="fa fa-upload" style="margin-left: 4px;" data-toggle="tooltip" title="Upload high resolution picture"></i>
										</a>
										<?php
											}
										?>
										</div>
									</div>
									<div class="col-sm-12 thumbnail thumbdiv" id="thumbnail-<?=$idx;?>" style="background-color: <?= $tn_background;?>" >
										<a class="review-link" data-idx="<?= $idx;?>" data-key="<?= $pic['profile_id'] . '-' . $pic['pic_id'];?>" >
											<img class="lozad" src="img/preview.png"
												data-src="<?= $pic_src;?>" >
											<div class="caption">
												<span><small><?= $pic['title'];?></small></span><br>
												<?php
													if (isset($_REQUEST['only_awarded'])) {
												?>
												<span><small><?= $award_name;?></small></span><br>
												<?php
													}
												?>
												<span><small><b>W x H = <?= $width;?> x <?= $height;?></b></small><br>
												<small><span class="text-danger"><?= $rejection_text;?></span></small>
											</div>
										</a>
									</div>
								</div>
						<?php
								++ $idx;
								if ($idx % 6 == 0) {
						?>
								<div class="clearfix"></div>
						<?php
								}
							}		// while ($pr = mysqli_fetch_array($pq))
						?>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php include "inc/profile_modal.php";?>
	</div>
	<div style="display: none;">
		<form name="review-form" id="review-form" action="review_image_new.php" method="post" >
			<input type="hidden" name="section" value="<?= $_REQUEST['section'];?>" >
			<input type="hidden" name="pic_list" value='<?= json_encode($pic_list);?>' >
			<input type="hidden" name="pic_index" id="pic_index" value="0" >
			<?php
				$filter_param = "";
				if (isset($_REQUEST['only_notified']))
					$filter_param = "only_notified";
				elseif (isset($_REQUEST['only_reviewed']))
					$filter_param = "only_reviewed";
				elseif (isset($_REQUEST['only_flagged_no_acc']))
					$filter_param = "only_flagged_no_acc";
				elseif (isset($_REQUEST['only_reviewed_not_flagged']))
					$filter_param = "only_reviewed_not_flagged";
				elseif (isset($_REQUEST['only_notreviewed']))
					$filter_param = "only_notreviewed";
			?>
			<input type="hidden" name="filter_param" value="<?= $filter_param;?>" >
		</form>
	</div>

	<?php
		  include("inc/footer.php");
	?>


<script src="plugin/moment/min/moment.min.js"></script>

<!-- Delayed Loading -->
<script src="plugin/lozad/lozad.js"></script>


<script>

    $(document).ready(function () {
		// Scroll to top
		window.scrollTo(0, 0);

		// Update counts on buttons
		$("#all-pics").html("<?= $all_pics;?>");
		$("#reviewed-pics").html("<?= $reviewed_pics;?>");
		$("#not-reviewed-pics").html("<?= $not_reviewed_pics;?>");
		$("#notified-pics").html("<?= $notified_pics;?>");
		$("#flagged-pics").html("<?= $flagged_pics;?>");
		$("#not-flagged-pics").html("<?= $not_flagged_pics;?>");
		$("#awarded-pics").html("<?= $awarded_pics;?>");

		// Install handler to launch review screen
		$(".review-link").click(function(){
			$("#pic_index").val($(this).attr("data-idx"));
			$("#review-form").submit();
		});

		// Initialize lozad
		const observer = lozad();
		observer.observe();

		// Turn tooltip on
		$('[data-toggle="tooltip"]').tooltip();

		// Action to locate the last picture
		$("#find-last-pic").click(function(){
			window.find($(this).attr("data-key"));
		});
	});

</script>

</body>

</html>
<?php
}
else
{
	$_SESSION['signin_msg'] = "Use ID with required permission not found !";
	header("Location: index.php");
	printf("<script>location.href='index.php'</script>");
}

?>
