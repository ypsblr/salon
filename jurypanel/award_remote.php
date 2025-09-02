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

// Return Exif Data stored in pic table as string
function exif_str($exif_json) {
    if ($exif_json == "")
		return "NO EXIF";

    try {
        $exif = json_decode($exif_json, true);
        $exif_strings = [];
        if (! empty($exif["camera"]))
            $exif_strings[] = $exif["camera"];
        if (! empty($exif["iso"]))
            $exif_strings[] = "ISO " . $exif["iso"];
        if (! empty($exif["program"]))
            $exif_strings[] = $exif["program"];
        if (! empty($exif["aperture"]))
            $exif_strings[] = $exif["aperture"];
        if (! empty($exif["speed"]))
            $exif_strings[] = $exif["speed"];

        if (sizeof($exif_strings) > 0)
            return implode(", ", $exif_strings);
        else
            return "";
    }
    catch (Exception $e) {
        // Not a proper exif
        return "";
    }
}

if ( isset($_SESSION['jury_id']) && isset($_SESSION['jury_type']) && $_SESSION['jury_type']=="JURY" &&
	 isset($_SESSION['jury_yearmonth']) && isset($_REQUEST['show']) ) {

	$jury_id = $_SESSION['jury_id'];
	$jury_yearmonth = $_SESSION['jury_yearmonth'];

	list($jury_section, $award_group) = explode("|", decode_string_array($_REQUEST['show']));
	$_SESSION['section'] = $jury_section;
	$_SESSION['award_group'] = $award_group;

	// Get Contest Data
	$sql = "SELECT * FROM contest WHERE yearmonth = '$jury_yearmonth' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$contest = mysqli_fetch_array($query);

	// Get Number of Jury members assigned to this section
	$sql = "SELECT COUNT(*) AS num_juries FROM assignment WHERE yearmonth = '$jury_yearmonth' && section = '$jury_section' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$row = mysqli_fetch_array($query);
	$num_juries = $row['num_juries'];

	// Load session from database
	// Sync to Jury Session in progress
	$sql  = "SELECT * FROM jury_session, assignment ";
	$sql .= " WHERE jury_session.yearmonth = '$jury_yearmonth' ";
	$sql .= "   AND jury_session.section = '$jury_section' ";
	$sql .= "   AND jury_session.award_group = '$award_group' ";
	$sql .= "   AND assignment.yearmonth = jury_session.yearmonth ";
	$sql .= "   AND assignment.section = jury_session.section ";
	$sql .= "   AND assignment.user_id = '$jury_id' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_num_rows($query) == 0)
		die_with_error("Judging for $award_group under $jury_section is yet to open. Contact YPS !", __FILE__, __LINE__);

	$assignment = mysqli_fetch_array($query);
	if ($assignment['session_open'] == '0') {
		die_with_error("Judging for $award_group under $jury_section is yet to open. Contact YPS !", __FILE__, __LINE__);
	}
	// if ($assignment['status'] == 'COMPLETED') {
	// 	die_with_error("Judging for $jury_section has been completed. Contact YPS to score again !", __FILE__, __LINE__);
	// }

	$session_command_index = $assignment['command_index'];
	$session_award_group = $assignment['award_group'];
	$session_bucket = $assignment['bucket'];
	$session_filter_text = $assignment['filter_criteria'];

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

	// Get number of pictures left to be scored by the jury
	$sql  = "SELECT entrant_category FROM entrant_category ";
	$sql .= " WHERE yearmonth = '$jury_yearmonth' ";
	$sql .= "   AND award_group = '$award_group' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$categories = array();
	while($row = mysqli_fetch_array($query))
		$categories[] = $row['entrant_category'];
	$_SESSION['categories'] = implode(",", $categories);
	$entrant_filter = " AND entry.entrant_category IN (" . implode(",", array_add_quotes($categories)) . ") ";

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
	$sql  = "SELECT COUNT(*) AS num_scored FROM entry, pic, rating ";
	$sql .= " WHERE entry.yearmonth = '$jury_yearmonth' ";
	$sql .= $entrant_filter;
	$sql .= "   AND pic.yearmonth = entry.yearmonth ";
	$sql .= "   AND pic.profile_id = entry.profile_id ";
	$sql .= "   AND pic.section = '$jury_section' ";
	$sql .= "   AND rating.yearmonth = pic.yearmonth ";
	$sql .= "   AND rating.profile_id = pic.profile_id ";
	$sql .= "   AND rating.pic_id = pic.pic_id ";
	$sql .= "   AND rating.user_id = '$jury_id' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$row = mysqli_fetch_array($query);
	$num_pics_scored = $row['num_scored'];
	$pics_left = $num_pics - $num_pics_scored;


	$tn_per_row = 6;		// Default thumbnail display per row

	// Main Query
	$main_query  = "SELECT pic.profile_id, pic.pic_id, pic.section, pic.title, pic.location, pic.picfile, pic.eseq, ";
	$main_query .= "       pic.width, pic.height, pic.exif, pic.notifications, pic.print_received, ";
	$main_query .= "       profile.profile_name, entry.entrant_category, ";
	$main_query .= "       IFNULL(SUM(rating.rating), 0) AS total_rating, IFNULL(COUNT(rating.rating), 0) AS num_rating, ";
	$main_query .= "       IFNULL(MAX(rating.rating), 0) AS max_rating, IFNULL(MIN(rating.rating), 0) AS min_rating ";
	$main_query .= "  FROM entry, profile, pic LEFT JOIN rating ";
	$main_query .= "    ON rating.yearmonth = pic.yearmonth AND rating.profile_id = pic.profile_id AND rating.pic_id = pic.pic_id ";
	$main_query .= " WHERE pic.yearmonth = '$jury_yearmonth' ";
	$main_query .= "   AND pic.section = '$jury_section' ";
	$main_query .= "   AND entry.yearmonth = pic.yearmonth ";
	$main_query .= "   AND entry.profile_id = pic.profile_id ";
	$main_query .= "   AND entry.entrant_category IN (" . implode(",", array_add_quotes($categories)) . ") ";
	$main_query .= "   AND profile.profile_id = pic.profile_id ";
	$main_query .= " GROUP BY profile_id, pic_id ";
	$main_query .= " ORDER BY eseq ASC ";


?>

<!DOCTYPE html>
<html>

<head>

    <!-- Page title -->
    <title>Youth Photographic Society | Remote Rating Panel</title>


	<?php include("inc/header.php");?>

    <!-- Vendor styles -->
    <link rel="stylesheet" href="plugin/blueimp-gallery/css/blueimp-gallery.min.css" />
	<link rel="stylesheet" href="plugin/lightbox/css/lightbox.min.css" />


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
	display: block;		/* keep description on */
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
			<h1>    YOUTH PHOTOGRAPHIC SOCIETY | AWARD FINALIZATION PANEL  </h1>
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

					<!-- Display a replica of projector screen -->
					<div class="hpanel">
						<div class="panel-title">
							<div class="row">
								<div class="col-sm-12">
									<span class="text-info lead">
										<?= $contest['contest_name'];?> <small>(<?= $session_award_group;?>)</small>
									</span>
								</div>
							</div>
							<div class="row">
								<div class="col-lg-3 col-md-3 col-sm-3">
									<p>
										<i>Showing : </i>
										<span class="text-info" style="font-weight: bold;" id="view-description">ALL PICTURES</span>
										(<span class="text-danger" style="font-weight: bold;" id="num-pics"><?= $num_pics;?></span>)
									</p>
								</div>
								<div class="col-lg-3 col-md-3 col-sm-3">
									<p><i>Categories : </i><span class="text-info" style="font-weight: bold;" id="entrant-categories"><?= implode(",", $categories);?></span></p>
								</div>
								<div class="col-lg-3 col-md-3 col-sm-3">
									<p>
										<i>Thumbnail Size : </i>
										<label>
											<input type="radio" class="thumbnail-size" name="thumbnail-size" value="small" checked >
											<span style="padding-right: 15px;" class="text-info"><b>Small</b></span>
										</label>
										<label>
											<input type="radio" class="thumbnail-size" name="thumbnail-size" value="medium" >
											<span class="text-info"><b>Medium</b></span>
										</label>
									</p>
								</div>
							</div>
						</div>
						<div class="panel-body">
							<!-- <form method="post" action="#" class="form-inline"> -->
							<div>
								<div class="lightBoxGallery" id="gallery" style="text-align:left">
								<?php
									$sql = $main_query;
									$pics = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
									$max = mysqli_num_rows($pics);
									$idx = 0;
									while ($tr_pics = mysqli_fetch_array($pics)) {
										$idx++;
										$profile_id = $tr_pics['profile_id'];
										$pic_id = $tr_pics['pic_id'];
										$picfile = $tr_pics['picfile'];
										$pic_section = $tr_pics['section'];
										$pic_alert = $tr_pics['profile_name'] . ' (' .  $tr_pics['profile_id'] . ')' ;
										//$pic_rating = $tr_pics['total_score'];

										// Process Notifications
										$notes = "";
										$disallow_selection = false;
										$rejection_text = "";
										if ($tr_pics['notifications'] != "") {
											$notifications = explode("|", $tr_pics['notifications']);
											foreach ($notifications AS $notification) {
												if ($notification != "") {
													list($notification_date, $notification_code_str) = explode(":", $notification);
													$notification_codes = explode(",", $notification_code_str);
													$rejected = false;
													foreach ($notification_codes as $notification_code)
														if (isset($rejection_reasons[$notification_code])) {
															$rejection_text .= (($rejection_text == "") ? "" : ", ") . $rejection_reasons[$notification_code];
															$disallow_selection = true;
														}
												}
											}
										}
										if ($rejection_text != "") {
											$notes = "*REJECT*";
										}

										$pic_title = "[" . $tr_pics['profile_id'] . "|" . $tr_pics['pic_id'] . "] " . $notes  . " {" . $idx . "/" . $max . "}";
										$data_description = $tr_pics['eseq'] . ": " . $idx . "/" . $max;
										$pic_path = "../salons/" . $jury_yearmonth . "/upload/" . $pic_section . "/" . $picfile;
										$tn_path = "../salons/" . $jury_yearmonth . "/upload/" . $pic_section . "/tn/" . $picfile;
								?>
									<div class="col-sm-2 containerBox salon-pic"
											data-index="<?= $idx;?>"
											data-profile-id="<?= $tr_pics['profile_id'];?>"
											data-entrant-category="<?= $tr_pics['entrant_category'];?>"
											data-pic-id="<?= $tr_pics['pic_id'];?>"
											data-rejection-text="<?= $rejection_text;?>"
											data-width="<?= $tr_pics['width'];?>"
											data-height="<?= $tr_pics['height'];?>"
											data-exif-json='<?= $tr_pics['exif'];?>'
											data-total-rating="<?= $tr_pics['total_rating'];?>"
											data-num-ratings="<?= $tr_pics['num_rating'];?>"
											data-max-rating="<?= $tr_pics['max_rating'];?>"
											data-min-rating="<?= $tr_pics['min_rating'];?>"
										>
										<div class="pull-right" style="display: inline-block;">
											<!-- Exif reveal icon -->
											<a class="show-exif" data-toggke="tooltip" title="EXIF Data">
												<i class="glyphicon glyphicon-info-sign small"></i>
											</a>
										</div>
										<div class="col-md-12 col-lg-12 col-xs-12 thumbnail thumbdiv" id="thumbnail-<?=$idx;?>" >
											<a href="<?=$pic_path;?>" title="<?=$pic_title;?>" class="lightbox-link"
													data-lightbox="pic-visible"
													data-title="<?= exif_str($tr_pics['exif']);?>"
											   		data-gallery-X="thumbnails-gallery" >
												 <img class="lozad" src="img/preview.png"
 													 data-src="<?= $tn_path;?>" >
												<div class="caption">
													<p>
														<span class="text-info"><b>#<?= $idx;?></b></span> [<b><?= $tr_pics['eseq'];?></b>]
														<span class="text-warning pull-right"><?=$notes;?></span>
													</p>
													<small><?= $rejection_text;?></small>
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
							<!-- </form> -->
						</div> <!-- panel body -->
					</div>
				</div>
			</div>
		</div>
		<!-- Right sidebar -->
		<!-- Footer-->
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
		<!-- <a class="description" href="#" onclick="update_description()"><h3>Show Rating</h3></a> -->
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
<script src="plugin/lightbox/js/lightbox.min.js"></script>

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

<script>

var tn_6_per_row = 6;
var tn_6_classes = "col-sm-2";
var tn_4_per_row = 4;
var tn_4_classes = "col-sm-3";
var tn_per_row = 6;
var tn_classes = "col-sm-2";
var yearmonth = <?= $jury_yearmonth;?>;
var section = '<?= $jury_section;?>';
var category_list = "<?= implode(",", $categories);?>";

// Handle Thumbnail size change
$(document).ready(function() {
	$(".thumbnail-size").change(function(){
		// Hide all thumbnails
		// $(".salon-pic").hide();

		// Remove all clearfix statements
		$("div.clearfix").remove();

		// Determine thumbnail size
		if ($(".thumbnail-size:checked").val() == "medium") {
			tn_per_row = tn_4_per_row;
			tn_classes = tn_4_classes;
		}
		else {
			tn_per_row = tn_6_per_row;
			tn_classes = tn_6_classes;
		}

		// Set appropriate classes
		let filtered_set = $(".salon-pic:visible");
		filtered_set.removeClass(tn_4_classes);
		filtered_set.removeClass(tn_6_classes);
		filtered_set.addClass(tn_classes);

		// Add clearfix statements based on setting
		filtered_set.each(function(index, elem) {
			if (index != 0 && (index % tn_per_row) == 0)
				$("<div class='clearfix'></div>").insertBefore(elem);
		});

		// Display thumbnails
		// filtered_set.show();

	})
})

// Render List of thumbnails
function show_filtered(filter_text, filter_criteria, thumbnail_set) {

	$("#entrant-categories").html(category_list);

	let category_selectors = category_list
								.split(",")
								.map(function(val, index) { return "[data-entrant-category='" + val + "']"; })
								.join();

	let filtered_set = thumbnail_set.filter(category_selectors);

	$("#view-description").html(filter_text);
	$("#num-pics").html(filtered_set.length);

	// Hide all thumbnails
	$(".salon-pic").hide();
	// Limit Lightbox navigation to filtered set
	$(".salon-pic").find(".lightbox-link").attr("data-lightbox", "pic-hidden");
	filtered_set.find(".lightbox-link").attr("data-lightbox", "pic-visible");

	// Remove all clearfix statements
	$("div.clearfix").remove();

	// Remove existing boot-strap class and add currently set class
	// filtered_set.removeClass(tn_4_classes);
	// filtered_set.removeClass(tn_6_classes);

	// Determine Thumbnail Size
	// if ($(".thumbnail-size:checked").val() == "medium") {
	// 	tn_per_row = tn_4_per_row;
	// 	tn_classes = tn_4_classes;
	// }
	// else {
	// 	tn_per_row = tn_6_per_row;
	// 	tn_classes = tn_6_classes;
	// }
	// filtered_set.addClass(tn_classes);

	// Add clearfix statements based on setting
	filtered_set.each(function(index, elem) {
		if (index != 0 && (index % tn_per_row) == 0)
			$("<div class='clearfix'></div>").insertBefore(elem);
	});
	// Display thumbnails
	filtered_set.show();

}

// Show ALL
function show_all() {
	show_filtered("All pictures", "ALL", $(".salon-pic"));
}

// Show UNSCORED
function show_unscored() {
	let thumbnail_set = $(".salon-pic[data-num-ratings!='<?= $num_juries;?>']");
	show_filtered("Scoring incomplete", "UNSCORED", thumbnail_set);
}

// Filter by score
function show_for_score(score) {
	let score_plus = false;
	if (score.endsWith("+")) {
		score_plus = true;
		score = score.substr(0, score.length - 1);
	}
	// Filter on score
	let thumbnail_set = $(".salon-pic").filter(function(index, elem){
		let rating = $(elem).attr("data-total-rating");
		if (score_plus)
			return rating >= score;
		else
			return rating == score;
	});
	// Refresh Page
	show_filtered("Total Score" + (score_plus ? " >= " : " = ") + score, "RATING", thumbnail_set);
}

function get_thumbnail_set(pic_str) {
	let pic_list = pic_str.split(",");
	let thumbnail_set = $(".salon-pic").filter(function(index, elem) {
							let pic_key = $(elem).attr("data-profile-id") + "|" + $(elem).attr("data-pic-id");
							return pic_list.includes(pic_key);
						});
	return thumbnail_set;
}

// Show a bucket
function show_bucket(bucket, pic_str) {
	// Refresh Page
	show_filtered(bucket, "BUCKET", get_thumbnail_set(pic_str));
}

// Show award
function show_award(award_name, pic_str){
	// Refresh Page
	show_filtered("Award " + award_name, "AWARD", get_thumbnail_set(pic_str));
}

// Periodic session sync
var command_index = 0;
var query_in_progress = false;

// Perform session sync
function sync_session() {
	if (query_in_progress ) {
		setTimeout(sync_session, 1000);		// Try again after a second
	}
	else {
		query_in_progress = true;
		$.ajax("ajax/remote_session.php",
		   {
				method : 	"POST",
				data : 		{yearmonth, section},
				dataType : 	"text",
				success : 	function (data, status) {
								query_in_progress = false;
								if (status == "success") {
									var result = JSON.parse(data);
									if (result.status == "OK") {
										// Refresh Page if a new command has been executed
										if (command_index != result.session.command_index) {

											category_list = result.session.entrant_categories;

											switch (result.session.filter_criteria) {
												case "ALL" : {
													show_all();
													break;
												}
												case "UNSCORED" : {
													show_unscored();
													break;
												}
												case "RATING" : {
													show_for_score(result.session.bucket);
													break;
												}
												case "BUCKET" : {
													switch(result.session.bucket) {
														case 'bucket_1' : { show_bucket("Bucket 1", result.session.bucket1_list); break; }
														case 'bucket_2' : { show_bucket("Bucket 2", result.session.bucket2_list); break; }
														case 'bucket_3' : { show_bucket("Bucket 3", result.session.bucket3_list); break; }
														case 'bucket_4' : { show_bucket("Bucket 4", result.session.bucket4_list); break; }
														case 'bucket_5' : { show_bucket("Bucket 5", result.session.bucket5_list); break; }
													}
													break;
												}
												case "AWARD" : {
													show_award(result.session.award_name, result.session.award_pic_list);
													break;
												}
											}
										}
									}
								}
								setTimeout(sync_session, 5000);		// Try again after 5 seconds
							},
				error : 	function() {
								query_in_progress = false;
								setTimeout(sync_session, 15000);		// Let us give some time to recover from error
							}
			}
		);
	}
}

// Start Session Sync process
$(document).ready(function(){
	sync_session();
});

</script>

</body>
</html>
<?php
}
else
{
	// Go back to login screen in case of unauthorized access
	$_SESSION['err_msg'] = "Invalid Access / Session expired";
	header("Location: " . http_method() . $_SERVER['SERVER_NAME'] . "/jurypanel/index.php");
	printf("<script>location.href='" . http_method() . $_SERVER['SERVER_NAME'] . "/jurypanel/index.php'</script>");
}

?>
