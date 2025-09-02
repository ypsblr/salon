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


if ( isset($_REQUEST['section']) && isset($_SESSION['admin_id']) && isset($_SESSION['admin_yearmonth']) ) {
	// Set up user and contest information
	include ("inc/load_common_data.php");

    $section_param = decode_string_array(trim($_REQUEST['section']));

	$sql = "SELECT * FROM section WHERE yearmonth = '$admin_yearmonth' AND section = '$section_param' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_num_rows($query) == 0) {
		$_SESSION['err_msg'] = "Section " . $section_param . " not found.";
		header("Location: ".$_SERVER['HTTP_REFERER']);
		printf("<script>location.href='".$_SERVER['HTTP_REFERER']."'</script>");
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

//    $sections = str_replace(" ", "", $sections);
//    $sections = decode_string_array($sections);

	$table_name = $section_stub . "-table"; 		// Name of the table so that DataTable restores only pages having the same name

	$next = isset($_REQUEST['next']) ? $_REQUEST['next'] : 0;

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
?>
<!DOCTYPE html>
<html>

<head>
    <!-- Page title -->
	<title>Youth Photographic Society | Picture Review Panel</title>

	<?php include "inc/header.php"; ?>

    <link rel="stylesheet" href="plugin	/blueimp-gallery/css/blueimp-gallery.min.css" />
	<link rel="stylesheet" href="plugin/lightbox/css/lightbox.min.css" />
	<style>
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
	</style>

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

<!--
					<div id="hbreadcrumb" class="pull-right" style="margin-top:1px">
						<ol class="hbreadcrumb breadcrumb">

							<li><a href="dashboard.php">Dashboard</a></li>
							<li class="active">
								<span>ALL Images</span>
							</li>
						</ol>
					</div>
-->
					<h3 class="font-light m-b-xs">
						All Images under <?php echo $review_section;?> for <?=$admin_contest_name;?>
					</h3>
					<!-- Restrict view to pictures with notifications -->
					<p>
						<a href="review_image.php?only_notified&section=<?= $_REQUEST['section'];?>" class="btn btn-info">
							Show Notified
						</a>
					</p>
				</div>
			</div>
		</div>


		<div class="content">
			<div class="row">
				<div class="col-lg-12">
					<div class="hpanel">
						<div class="panel-body">
							<table id="<?=$table_name;?>" class="table table-striped table-bordered table-hover">
							<thead>
								<tr>
									<th>#</th>
									<th>Images</th>
									<th width="60%">Pictures uploaded in Other Sections in this Salon and Past Acceptances</th>
								</tr>
							</thead>
							<tbody>
						<?php
							// Generate list of Pictures
							$sql  = "SELECT pic.profile_id, profile_name, email, phone, yps_login_id, blacklist_match, blacklist_exception, ";
							$sql .= "       club_name, MAX(pic.modified_date) as modified_date ";
							if ($contest_archived)
								$sql .= "  FROM ar_pic pic, profile ";
							else
								$sql .= "  FROM pic, profile ";
							$sql .= "  LEFT JOIN club ON profile.club_id = club.club_id ";
							$sql .= " WHERE yearmonth = '$admin_yearmonth' ";
							$sql .= "   AND pic.section = '$review_section' ";
							if (isset($_REQUEST['only_notified']))
								$sql .= "   AND pic.notifications != '' ";
							$sql .= "   AND profile.profile_id = pic.profile_id ";
							$sql .= " GROUP BY pic.profile_id, profile_name, email, phone, yps_login_id, blacklist_match, blacklist_exception, club_name ";
							$sql .= " ORDER BY modified_date DESC ";
							$pq = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

							$row_no = 0;
							$reviewing_till = date("y-m-d H:m:s");
							// Render table rows
							while ($pr = mysqli_fetch_array($pq)) {
								if ($row_no == 0)
									$reviewing_till = $pr['modified_date'];
								$profile_id = $pr['profile_id'];
								$entry_name = $pr['profile_name'];
								$club_name = $pr['yps_login_id'] != "" ? ("YPS MEMBER " . $pr['yps_login_id']) : ($pr['club_name'] != NULL ? $pr['club_name'] : "");
								$entry_email = $pr['email'];
								$blacklist_match = $pr['blacklist_match'];
								$blacklist_exception = $pr['blacklist_exception'];
								if ($blacklist_match == "" && $blacklist_exception == 0) {
									list($blacklist_match, $blacklist_name) = check_blacklist($pr['profile_name'], $pr['email'], $pr['phone']);
									if ($blacklist_match != "MATCH" || $blacklist_match != "SIMILAR")
										$blacklist_match = "";
								}
								$is_reviewd = (in_array($profile_id, $reviewed_profiles) && $pr['modified_date'] <= $reviewed_till);
								++ $row_no;
						?>
								<tr class="profile-row <?= $is_reviewd ? 'reviewed' : '';?>" data-profile-id="<?= $profile_id;?>" >
									<td><div id="row-<?= $row_no;?>"><?=$row_no;?></div></td>
									<td>
										<?php
											if ($member_is_admin) {
										?>
										<p><strong><?php echo $entry_name;?></strong><br><?=$club_name;?></p>
										<?php
											}
											else {
										?>
										<p><strong><?= "Participant-" . $profile_id;?></strong></p>
										<?php
											}
										?>
						<?php
								if ($blacklist_match != "" && $blacklist_exception == 0) {
						?>
										<p class="text-color"><b>*** Blacklist Match ***</b></p>
						<?php
								}
						?>
										<table class="pics-table">
											<tbody>
						<?php
								if ($contest_archived)
									$sql  = "SELECT * FROM ar_pic pic, section ";
								else
									$sql  = "SELECT * FROM pic, section ";
								$sql .= " WHERE pic.yearmonth = '$admin_yearmonth' ";
								$sql .= "   AND pic.section = '$review_section' ";
								$sql .= "   AND pic.profile_id = '$profile_id' ";
								$sql .= "   AND section.yearmonth = pic.yearmonth ";
								$sql .= "   AND section.section = pic.section ";
								$sql .= " ORDER BY pic.modified_date DESC";
//								if (isset($_REQUEST['last']))
//									$sql .= " LIMIT " . $_REQUEST['last'];
								$pics = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
								$num_pics = mysqli_num_rows($pics);		// Number of pictures for this user
								$pic_titles = array();
								$submitted_files = array();
								while($tr_pics = mysqli_fetch_array($pics)) {
									$pic_id = $tr_pics['pic_id'];
									$submitted_file = $tr_pics['submittedfile'];
									$submitted_files[] = $submitted_file;
									$pic_titles[] = htmlspecialchars($tr_pics['title'], ENT_QUOTES);
									$pic_src = "../salons/" . $admin_yearmonth . "/upload/" . $tr_pics['section'] . "/tn/" . $tr_pics['picfile'];
									// $pic_title_encoded = htmlspecialchars($tr_pics['title'], ENT_QUOTES);
									$pic_title_encoded = addslashes($tr_pics['title']);
									// list($width,$height) = getimagesize("../salons/" . $admin_yearmonth . "/upload/" . $tr_pics['section'] . "/" . $tr_pics['picfile']);

									// Assemble Notifications
									// Store dates of notifications in the array with notification code as the key
									$pic_notifications = array();
									foreach(explode("|", $tr_pics['notifications']) as $notification_str) {
										if ($notification_str != "") {
											$nr = explode(":", $notification_str);
											foreach(explode(",", $nr[1]) as $nc) {
												if (isset($pic_notifications[$nc]))
													$pic_notifications[$nc] .= "," . $nr[0];
												else
													$pic_notifications[$nc] = $nr[0];
											}
										}
									}
						?>
												<tr>
													<td>
														<p class="text-info text-center"><b><u>Compare with</u></b></p>
														<p>
															<a href="javascript:compare_other_sections(<?=$tr_pics['profile_id'];?>, <?=$tr_pics['pic_id'];?>, '<?=$tr_pics['section'];?>', '<?=$pic_title_encoded;?>', '<?=$pic_src;?>', '<?=$submitted_file;?>')"
															   class="btn btn-info">OTH SEC</a>
															<a href="javascript:compare_past_acceptances(<?=$tr_pics['profile_id'];?>, <?=$tr_pics['pic_id'];?>, '<?=$tr_pics['section'];?>', '<?=$pic_title_encoded;?>', '<?=$pic_src;?>', '<?=$submitted_file;?>')"
															   class="btn btn-warning pull-right">PAST ACC</a>
														</p>
														<div class="lightBoxGallery">
															<p>
																<a href="../salons/<?=$admin_yearmonth;?>/upload/<?php echo $tr_pics['section'] . "/" . $tr_pics['picfile'];?>"
																		title=" User ID: <?php printf("%04d", $tr_pics['profile_id']);?> & Title: <?php echo $tr_pics['title'];?>"
																    	data-row-no = "<?= $row_no;?>" data-gallery="" >
																	<img class="img-responsive lazy-load" src="img/preview.png"
																			data-src="../salons/<?=$admin_yearmonth;?>/upload/<?= $tr_pics['section'] . "/tn/" . $tr_pics['picfile'];?>"
																			style="max-width:180px; max-height:180px"
																			data-pic="<?= $tr_pics['profile_id'] . "|" . $tr_pics['pic_id'] . "|" . $tr_pics['picfile'];?>"  >
																</a>
															</p>
															<br><b><?php echo $tr_pics['title'];?></b>
															<br>[ <span id="dimensions-<?= $tr_pics['profile_id'];?>-<?= $tr_pics['pic_id'];?>"></span> ]
															<?php
																if ($tr_pics['total_rating'] > 0) {
															?>
															<br>Total Score: <?php echo $tr_pics['total_rating'];?>
															<?php
																}
															?>
															<br>
															<a href="/upload_rectified.php?code=<?= encode_string_array(implode("|", [$admin_yearmonth, $tr_pics['profile_id'], $tr_pics['pic_id'], $_SESSION['admin_id']]));?> "
																	target="_blank">
																<b><?php printf("%04d-%02d", $tr_pics['profile_id'], $tr_pics['pic_id']);?></b> : <b><?php echo $tr_pics['eseq'];?></b>
															</a>
															<?php
																if($tr_pics['section_type'] == 'P' && $tr_pics['print_received'] == 0) {
															?>
															<br><span style="color: red;">PRINT NOT RECEIVED</span>
															<?php
																}
															?>
															<br><span class="text-muted"><small><?=$tr_pics['modified_date'];?></small></span>
														</div>
													</td>
													<td>
														<div id="notifications-<?=$profile_id;?>-<?=$tr_pics['pic_id'];?>">
															<!-- The value of row-xxx field will be updated with page number for returning to the same page
															<input type="hidden" name="page-no" class="page-no" value="0" >
															<input type="hidden" name="page-len" class="page-len" value="10" > -->
															<input type="hidden" name="admin_yearmonth" value="<?=$admin_yearmonth;?>" >
															<input type="hidden" name="profile_id" value="<?=$profile_id;?>" >
															<input type="hidden" name="pic_id" value="<?php echo $tr_pics['pic_id'];?>" >
															<input type="hidden" name="email" value="<?php echo $entry_email; ?>">
															<input type="hidden" name="name" value="<?php echo $entry_name; ?>">
															<input type="hidden" name="picture" value="<?php echo $tr_pics['picfile']; ?>" >
															<input type="hidden" name="title" value="<?php echo $tr_pics['title']; ?>">
															<input type="hidden" name="section" value="<?php echo $tr_pics['section']; ?>">
														<?php
															foreach ($notifications as $template_code => $template_row ) {
														?>
															<input type="checkbox" name="notification[]"
																   value="<?php echo $template_code;?>" data-name="<?=$template_row['template_name'];?>"
																   <?= isset($pic_notifications[$template_code]) ? "checked" : ""; ?> >
															<span id="ntf-<?=$profile_id;?>-<?=$tr_pics['pic_id'];?>-<?=$template_code;?>">
																<?= $template_row['template_name'] . ($template_row['will_cause_rejection'] == 1 ? "*" : ""); ?>
																<?= isset($pic_notifications[$template_code]) ?  " (<span style='color: #C00;'><small>" . $pic_notifications[$template_code] . "</small></span>)" : "";?>
															</span>
															<br>
														<?php
															}
														?>
															<p><small>* - Will cause Rejection</small></p>
															<br>
															<input type="submit" class="btn btn-info action-notify" value="Notify" data-profile="<?=$profile_id;?>" data-pic="<?=$tr_pics['pic_id'];?>">
															<input type="submit" class="btn btn-info action-notify" value="Update" data-profile="<?=$profile_id;?>" data-pic="<?=$tr_pics['pic_id'];?>">
															<br>
															<span style="color: #C00;" id="err-<?=$profile_id;?>-<?=$tr_pics['pic_id'];?>"></span>
														</div>
													</td>
												</tr>
						<?php
								}	// while($tr_pics = mysqli_fetch_array($pics))
						?>
											</tbody>
										</table>
									</td>
									<td>
										<p><b>Submitted under other sections in this Salon</b></p>
										<div class="other-sections" data-profile="<?=$profile_id;?>"
											 data-section="<?= $review_section;?>"
											 data-titles='<?= json_encode($pic_titles);?>'
											 data-submittedfiles='<?= json_encode($submitted_files);?>' >
										</div>
										<br><p><b>Accepted in the Past YPS Salons</b></p>
										<div class="past-acceptance" data-profile="<?=$profile_id;?>"
											 data-titles='<?= json_encode($pic_titles);?>'
											 data-submittedfiles='<?= json_encode($submitted_files);?>' >
										</div>
									</td>
								</tr>
						<?php
							}		// while ($pr = mysqli_fetch_array($pq))
						?>
							</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php include "inc/profile_modal.php";?>
	</div>
	<div class="modal" tabindex="-1" role="dialog" aria-labelledby="image-review-header-label">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title" id="image-review-header-label">Compare with <span id="compared-with-what"></span></h4>
				</div>
				<div class="modal-body">
					<div class="row">
						<div class="col-sm-6">
							<div class='thumbnail' style='width: 100%;'>
								<img id='main-image' class='img-responsive' style='margin-left:auto; margin-right:auto;' >
							</div>
						</div>
						<div class="col-sm-6" id="modal-compared-images" style="height: 600px; overflow-y: scroll;">
							Thumbnails from this Salon or previous Salon
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<?php
		  include("inc/footer.php");
	?>

	<script src="plugin/lightbox/js/lightbox.min.js"></script>
	<script>
	// Enable All tooltips
	// Warning "clearfix" removes all tooltips in elements before it.
	$(document).ready(function() {
		$('img[data-toggle="tooltip"]').tooltip({container: 'body'});
	});
	</script>

	<div id="blueimp-gallery" class="blueimp-gallery">
		<div class="slides"></div>
		<h3 class="title"></h3>
		<a class="prev">&laquo;</a>
		<a class="next">&raquo;</a>
		<a class="close">&#42;</a>
		<a class="play-pause"></a>
		<ol class="indicator"></ol>
	</div>


<!-- DataTables -->
<script src="plugin/datatable/js/jquery.dataTables.min.js"></script>
<script src="plugin/datatable/js/dataTables.bootstrap.min.js"></script>

<!-- DataTables buttons scripts -->
<script src="plugin/pdfmake/build/pdfmake.min.js"></script>
<script src="plugin/pdfmake/build/vfs_fonts.js"></script>
<script src="plugin/datatables.net-buttons/js/buttons.html5.min.js"></script>
<script src="plugin/datatables.net-buttons/js/buttons.print.min.js"></script>
<script src="plugin/datatables.net-buttons/js/dataTables.buttons.min.js"></script>
<script src="plugin/datatables.net-buttons-bs/js/buttons.bootstrap.min.js"></script>

<script src="plugin/blueimp-gallery/js/jquery.blueimp-gallery.min.js"></script>

<script src="plugin/moment/min/moment.min.js"></script>

<script>
	// Place a tage to get back after ending slideshow
	var current_row_no = 1;

	$(function(){

		// Save current row number
		$("#blueimp-gallery").on("slide", function (event) {
			let gallery = $('#blueimp-gallery').data('gallery');		// Get Gallery Object
			let index = gallery.getIndex();
			current_row_no = gallery.list[index].getAttribute("data-row-no");
		});

		// go back to saved row number
		$("#blueimp-gallery").on("closed", function (event) {
			window.location.href = "#row-" + current_row_no;
		});

    });

</script>

<script>
	// Handle Modal comparison dialogs
	var xOffset = 0;
	var yOffset = 0;

	// Compare other sections
	function compare_other_sections(profile_id, pic_id, section, title, picfile, submitted_file) {
		// Save location
		xOffset = window.pageXOffset;
		yOffset = window.pageYOffset;

		// Set Main Images
		$("#main-image").attr("src", picfile);

		// Set Images from Other sections
		let yearmonth = <?= $admin_yearmonth; ?>;
		let pic_titles = [title];
		let submitted_files = [submitted_file];
		let col_spec = 'col-sm-6';
		$("#modal-compared-images").html("");
		$.post("ajax/get_other_sections.php", {yearmonth, profile_id, section, pic_titles, submitted_files, col_spec}, function(data) {
			$("#modal-compared-images").html(data);
		});

		// Show Modal
		$("#compared-with-what").html(" Other Sections");
		$(".modal").modal('show');
	}

	// Compare Past Acceptances
	function compare_past_acceptances(profile_id, pic_id, section, title, picfile, submitted_file) {
		// Save location
		xOffset = window.pageXOffset;
		yOffset = window.pageYOffset;

		// Set Main Images
		$("#main-image").attr("src", picfile);

		// Set Images from Other sections
		let yearmonth = <?= $admin_yearmonth; ?>;
		let pic_titles = [title];
		let submitted_files = [submitted_file];
		let col_spec = 'col-sm-6';
		$("#modal-compared-images").html("");
		$.post("ajax/get_past_acceptances.php", {yearmonth, profile_id, pic_titles, submitted_files, col_spec}, function(data) {
			$("#modal-compared-images").html(data);
		});

		// Show Modal
		$("#compared-with-what").html(" Past Acceptances");
		$(".modal").modal('show');
	}

	// Go back to saved location
	$(".modal").on("hidden.bs.modal", function(){window.scrollTo(xOffset, yOffset);});
</script>

<script>

    $(document).ready(function () {

		// Install the event handler before the table is loaded
		$('#<?=$table_name;?>').on('draw.dt', function() {
			// console.log('table.draw');
			// Load actual images instead of preview Thumbnails
			let pic_list = [];
			$("img.lazy-load").each(function() {
				$(this).attr("src", $(this).data("src"));
				pic_list.push($(this).data("pic"));
			});

			// Get Picture dimensions for pictures in pic_list
			if (pic_list.length > 0) {
				$.post(
						"ajax/get_pic_dimensions.php",
						{
							yearmonth : '<?= $admin_yearmonth; ?>',
							section : '<?= $review_section;?>',
							pic_list : pic_list,
						},
						function (response) {
							let data = JSON.parse(response);
							if (data.status == "OK") {
								data.dimension_list.forEach(function (dimension) {
									$("#dimensions-" + dimension.profile_id + "-" + dimension.pic_id).html(dimension.width + " x " + dimension.height);
								});
							}
						}
				);
			}

			// Scroll to top
			window.scrollTo(0, 0);
			// Load Other Sections
			$(".other-sections").each(function(){
				var yearmonth = <?= $admin_yearmonth; ?>;
				var profile_id = $(this).data("profile");
				var section = $(this).data("section");
				var pic_titles = $(this).data("titles");
				var submitted_files = $(this).data("submittedfiles");
				var target = $(this);
				$.post("ajax/get_other_sections.php", {yearmonth, profile_id, section, pic_titles, submitted_files}, function(data) {
					target.html(data);
				});
			});
			// Load past acceptances
			$(".past-acceptance").each(function(){
				var yearmonth = <?= $admin_yearmonth; ?>;
				var profile_id = $(this).data("profile");
				var pic_titles = $(this).data("titles");
				var submitted_files = $(this).data("submittedfiles");
				var target = $(this);
				$.post("ajax/get_past_acceptances.php", {yearmonth, profile_id, pic_titles, submitted_files}, function(data) {
					target.html(data);
				});
			});
		});

		// Initialize Example 2
        var image_table = $('#<?=$table_name;?>').DataTable( {ordering: false, stateSave : true} );

		// Invoke Notification
		$("#<?=$table_name;?>").on("click", ".action-notify", function() {
			var yearmonth = <?= $admin_yearmonth; ?>;
			var profile_id = $(this).data("profile");
			var pic_id = $(this).data("pic");
			var action = $(this).val();
			var ntf_div = "#notifications-" + profile_id + "-" + pic_id;
			var email = $(ntf_div + " > [name='email']").val();
			var name = $(ntf_div + " > [name='name']").val();
			var picture = $(ntf_div + " > [name='picture']").val();
			var title = $(ntf_div + " > [name='title']").val();
			var section = $(ntf_div + " > [name='section']").val();
			var notification = $(ntf_div + " > [name='notification[]']:checked").map(function() { return $(this).val(); }).get();
			// var ntf_name = $(ntf_div + " > [name='notification[]']:checked").map(function() { return $(this).data("name"); }).get();

			// Reset Error
			$("#err-" + profile_id + "-" + pic_id).html("");

			$.post(
				"ajax/notify.php",
				{yearmonth, profile_id, pic_id, email, name, picture, title, section, notification, action},
				function (data) {
					console.log(data);
					var retval = JSON.parse(data);
					if (retval.status == "OK" || (retval.status == "ERROR" && retval.updated) ) {
						// Update the Statuses
						$(ntf_div + " > [name='notification[]']").each(function() {
							let code = $(this).val();
							let name = $(this).data("name");
							let ntf_text = name;
							if (this.checked) {
								let dt = new Date();
								ntf_text = " " + name + " (<span style='color: #C00;'><small>" + dt.toISOString().substr(0, 10) + "</small></span>)";
							}
							$("#ntf-" + profile_id + "-" + pic_id + "-" + code).html(ntf_text);
						});
					}
					if (retval.status == "ERROR") {
						$("#err-" + profile_id + "-" + pic_id).html(retval.context + ": " + retval.errmsg + (retval.updated ? " (database updated)" : ""));
					}
				}
			);
		});

		// Keep track of pictures reviewed
		// 1. Determine if an element is on screen
		$.fn.isOnScreen = function(){
			let view_top = $(window).scrollTop();
			let view_bottom = $(window).scrollTop() + $(window).height();

			let profile_top = this.offset().top;
			let profile_bottom = profile_top + this.outerHeight();

			return ( profile_top < view_bottom && profile_bottom > view_top );

		};

		// Global variable to track profiles reviewed
		// review_section['section'] = ['profile_id_list']
		<?php
			if (empty($reviewed_profiles) || $reviewed_profiles == null) {
		?>
		var reviewed_profiles = [];
		<?php
			}
			else {
		?>
		var reviewed_profiles = JSON.parse('<?= json_encode($reviewed_profiles);?>');
		<?php
			}
		?>

		// Record profile_ids as user scrolls through views
		$(window).on('DOMContentLoaded load resize scroll', function() {
			$(".profile-row").each(function(idx, elem) {
				if ($(this).isOnScreen()) {
					// Check and Add pofile ID to list and update time
					// review_session.reviewed_time = moment().format("YYYY-MM-DD HH:mm:ss");
					let profile_id = $(this).data("profile-id").toString();
					if (reviewed_profiles.length == 0 || (reviewed_profiles.indexOf(profile_id) < 0))
						reviewed_profiles.push(profile_id);
					// if (review_session.reviewed_profiles.length == 0 || (review_session.reviewed_profiles.indexOf(profile_id) < 0))
					// 	review_session.reviewed_profiles.push(profile_id);

					// Set row as reviewed
					// $(this).addClass("reviewed");
				}
			});
		});

		// Save Review Session at the time of exit
		$(window).on("beforeunload", function() {
			console.log("Unloading review_image.php");
			$.post("ajax/save_review_session.php",
					{
						yearmonth : '<?= $_SESSION['admin_yearmonth'];?>',
						member_login_id : '<?= $_SESSION['admin_id'];?>',
						section : '<?= $review_section;?>',
						reviewed_till : '<?= $reviewing_till;?>',
						reviewed_profiles : reviewed_profiles,
					}
			);
			return;
		});

    });

</script>

</body>

</html>
<?php
}
else
{
	$_SESSION['signin_msg'] = "Use ID with require permission not found !";
	header("Location: index.php");
	printf("<script>location.href='index.php'</script>");
}

?>
