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


if ( isset($_REQUEST['section']) && isset($_REQUEST['pic_list']) && isset($_REQUEST['pic_index']) &&
 	 isset($_SESSION['admin_id']) && isset($_SESSION['admin_yearmonth']) ) {
	// Set up user and contest information
	include ("inc/load_common_data.php");

	if ($admin_contest['review_in_progress'] == '0' && (! has_permission($member_permissions, ["admin"])) ) {
		$_SESSION['err_msg'] = "Salon is not open for review";
		header("Location: ".$_SERVER['HTTP_REFERER']);
		printf("<script>location.href='".$_SERVER['HTTP_REFERER']."'</script>");
		die();
	}

    $section_param = decode_string_array(trim($_REQUEST['section']));
	$filter_param = isset($_REQUEST['filter_param']) ? $_REQUEST['filter_param'] : "";

	$pic_list = json_decode($_REQUEST['pic_list'], true);
	$pic_index = $_REQUEST['pic_index'];

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

	// Get a list of User Notification Email names
	$notifications = [];
	$sql  = "SELECT template_code, template_name, will_cause_rejection FROM email_template ";
	$sql .= " WHERE template_type = 'user_notification' ";
	if ($section_type == "P")
		$sql .= "  AND applies_to_print = '1' ";
	else
		$sql .= "  AND applies_to_digital = '1' ";
	$sql .= " ORDER BY template_name";
	$notq = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	while ($notr = mysqli_fetch_array($notq, MYSQLI_ASSOC)) {
		$notifications[] = $notr;
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
	<link rel="stylesheet" href="plugin/photo-histogram/photo-histogram.css" />
	<style>
		div.centered {
			height: 100%;
			display: flex;
			justify-content: center;
			align-items: center;
		}
	</style>

</head>
<body class="fixed-navbar fixed-sidebar"  style="overflow-y: hidden;">

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
	<div id="wrapper" style="overflow-x: hidden;">
		<!-- Picture Block -->
		<div class="row">
			<!-- Picture being reviewed -->
			<div class="col-sm-8">
				<div class="panel">
					<div class="panel-body" id="img-panel">
						<!-- Image -->
						<div class="row centered">
							<div class="col-sm-12 text-center" >
								<a href="#" target="_blank" id="current-pic-link">
									<img id="current-pic" src="img/preview.png">
								</a>
								<p>
									<span class="pull-left">
										<b><span id="current-pic-eseq"></span></b>
										<a style="padding-left: 8px;" class="show-histogram" data-toggle="tooltip" title="Show Histogram">
											<i class="glyphicon glyphicon-stats small"></i>
										</a>
									</span>
									<b><span class="pull-right" id="current-pic-title"></span></b></p>
							</div>
						</div>
					</div>
					<div class="panel-footer">
						<!-- Info & Actions -->
						<div class="row">
							<div class="col-sm-6">
								<span id="show-reviewed" style="display: none; margin-right: 10px;" class="text-success">
									<b>[ Reviewed by <span id="show-reviewed-by"></span> ]</b>
								</span>
								<span id="show-tagged" style="display: none; margin-right: 10px;" class="text-warning"><b>[ Recommended No-Acceptance ]</b></span>
								<span style="display: inline-block; margin-right: 10px;">
									[ W x H <span id="pic-width"></span> x <span id="pic-height"></span>
											<span id="undersized" class="text-info" style="display: none;">Undersized</span> ]
								</span>
								<span style="display: inline-block; margin-right: 10px;">
									<span id="rejection-text" class="text-danger"></span>
								</span>
								<span style="display: inline-block; margin-right: 10px;">
									<span id="exif-str"></span>
								</span>
							</div>
							<div class="col-sm-6 pull-right">
								<a class="btn btn-default" id="btn-go-prev" style="margin-right: 10px;"><i class="fa fa-caret-left fa-2x"></i></a>
								<a class="btn btn-default" id="btn-go-next" style="margin-right: 10px;"><i class="fa fa-caret-right fa-2x"></i></a>
								<a class="btn btn-success" id="btn-mark-reviewed" style="margin-right: 10px;">Reviewed <i class="fa fa-caret-right"></i></a>
								<!-- <a class="btn btn-warning" id="btn-tag-no-acc" style="margin-right: 10px;">Tag 2 <i class="fa fa-caret-right"></i></a> -->
								<!-- <a class="btn btn-warning" id="btn-untag-no-acc" style="margin-right: 10px;">Untag 2 <i class="fa fa-caret-right"></i></a> -->
								<a class="btn btn-danger" id="btn-notify" style="margin-right: 10px;">Notify <i class="fa fa-paper-plane"></i></a>
								<a class="btn btn-default" id="btn-return" ><i class="fa fa-reply"></i></a>
							</div>
						</div>
						<!-- Error Messages -->
						<div class="row">
							<div class="col-sm-12">
								<p class="text-danger"><small><span id="errmsg"></span></small></p>
							</div>
						</div>
					</div>
				</div>
			</div>
			<!-- Past Acceptances & Other Sections -->
			<div class="col-sm-4">
				<div class="panel" >
					<div class="panel-body" id="side-panel" style="overflow-y: scroll;">
						<p class="text-info"><big><b>Other pictures uploaded in this salon :</b></big></p>
						<div class="row">
							<div class="col-sm-12">
								<div id="other-sections"></div>
							</div>
						</div>
						<p class="text-info" style="margin-top: 20px;"><big><b>Accepted in past salons :</b></big></p>
						<div class="row">
							<div class="col-sm-12">
								<div id="past-acceptance"></div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php include "inc/profile_modal.php";?>
	</div>
	<div class="modal" id="ntf-modal" tabindex="-1" role="dialog" aria-labelledby="notify-header-label">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title" id="notify-header-label">Notify Participant</h4>
				</div>
				<div class="modal-body">
					<div id="notifications-form">
						<input type="hidden" name="admin_yearmonth" value="<?=$admin_yearmonth;?>" >
						<input type="hidden" name="section" value="<?= $section_param;?>">
						<input type="hidden" name="profile_id" id="ntf-profile-id" value="" >
						<input type="hidden" name="pic_id" id="ntf-pic-id" value="" >
						<input type="hidden" name="email" id="ntf-email" value="">
						<input type="hidden" name="name" id="ntf-name" value="">
						<input type="hidden" name="picture" id="ntf-picfile" value="" >
						<input type="hidden" name="title" id="ntf-title" value="">
						<input type="hidden" name="eseq" id="ntf-eseq" value="">
						<p>Select all notifications relevant to this picture and click Notify button.</p>
						<div style="margin-left: 50px;">
					<?php
						foreach ($notifications as $template ) {
					?>
							<input type="checkbox" name="notification[]" class="ckb-notification" id="ckb-<?= $template['template_code'];?>"
								   value="<?= $template['template_code'];?>" data-name="<?= $template['template_name'];?>" >
							<span>
								<?= $template['template_name'] . ($template['will_cause_rejection'] == 1 ? " *" : ""); ?>
								<span style='color: #C00;'><small><span id="pic-<?=$template['template_code'];?>" ></span></small></span>
							</span>
							<br>
					<?php
						}
					?>
						</div>
						<p class="text-danger"><small>* - Will cause Rejection</small></p>
						<br>
						<div class="pull-right">
							<input type="submit" class="btn btn-info action-notify" id="sub-notify" value="Notify" style="margin-right: 15px;" disabled>
							<input type="submit" class="btn btn-info action-notify" id="sub-update" value="Update" >
						</div>
						<br>
						<p style="margin-bottom: 15px;"><span style="color: #C00;" id="ntf-errmsg"></span></p>
					</div>
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

	<!-- to return to the review dashboard -->
	<div style="display: none;">
		<form name="review-dash-form" id="review-dash-form" action="review_image_dash.php" method="post" >
			<input type="hidden" name="section" value="<?= $_REQUEST['section'];?>" >
			<input type="hidden" name="<?= $filter_param;?>" >
			<input type="hidden" name="last_pic" id="last_pic" value="" >
		</form>
	</div>

	<?php
		  include("inc/footer.php");
	?>

<script src="plugin/lightbox/js/lightbox.min.js"></script>
<script src="plugin/photo-histogram/photo-histogram.js"></script>

<!-- Show Histogram -->
<script>

$(document).ready(function(){
	// Install handler to show exif
	$(".show-histogram").click(function() {
		// Display Exif Modal
		$("#histogram-display").html("");
		let histogram = new PhotoHistogram.Ui($("#histogram-display").get(0), $("#current-pic").get(0));

		// Launch the modal view
		$("#histogram-modal").modal();
	});

});

</script>

<script>
	// Enable All tooltips
	// Warning "clearfix" removes all tooltips in elements before it.
	$(document).ready(function() {
		$('img[data-toggle="tooltip"]').tooltip({container: 'body'});
	});
</script>

<script src="plugin/moment/min/moment.min.js"></script>

<script>

	var yearmonth = '<?= $admin_yearmonth;?>';
	var undersized_width = <?= round($admin_contest['max_width'] * 0.75, 0);?>;
	var undersized_height = <?= round($admin_contest['max_height'] * 0.75, 0);?>;
	var pic_list = JSON.parse('<?= $_REQUEST['pic_list'];?>');
	var pic_index = '<?= $_REQUEST['pic_index'];?>';
	var notifications = JSON.parse('<?= json_encode($notifications);?>');
	var pic;
	var current_profile_id = 0;
	var notification_panel_on = false;

	// Display Other sections
	function show_salon_uploads() {
		$.post("ajax/get_other_sections.php",
			{
				yearmonth,
				profile_id : pic.profile_id,
				section : pic.section,
				pic_titles : [pic.title],
				submitted_files : [pic.submittedfile],
				col_spec : 'col-sm-3'
			},
			function(data) {
				$("#other-sections").html(data);
				$("#oth-upload-pic-id-" + pic.pic_id).css("background-color", "#222");
			}
		);
	}

	// Display Past Acceptances
	function show_past_acceptances() {
		$.post("ajax/get_past_acceptances.php",
			{
				yearmonth,
				profile_id : pic.profile_id,
				pic_titles : [pic.title],
				submitted_files : pic.submittedfile,
				col_spec : 'col-sm-3'
			},
			function(data) {
				$("#past-acceptance").html(data);
			}
		);
	}

	// return notificatios string
	// Notification Format:
	// <date1>:<error1>,<error2>,...|<date2>:<error1>,<error2>,...|...
	function notification_str() {
		if (pic.notifications == "")
			return "";

		// Split the Notifications By Date
		let pic_notifications = pic.notifications.split("|");
		let will_be_rejected = false;
		let nstrs = [];
		pic_notifications.forEach(function (pic_notification) {
			if (pic_notification != "") {
				// Process List of Errors Notified (separated by comma)
				pic_notification.split(":")[1].split(",").forEach(function(ncode) {
					let notification_date = pic_notification.split(":")[0];
					let notification = notifications.find(function(ntf) { return ntf.template_code == ncode;});
					if (notification) {
						nstrs.push(notification.template_name + " [" + notification_date + "]");
						if (notification.will_cause_rejection == '1')
							will_be_rejected = true;
					}
				});
			}
		});
		return (will_be_rejected ? "Rejected " : "") + nstrs.join(", ");
	}

	// return dates on which a picture has been notified with a code
	function notification_dates(code) {
		if (pic.notifications == "")
			return "";

		let pic_notifications = pic.notifications.split("|");
		let ndates = [];
		pic_notifications.forEach(function (pic_notification) {
			if (pic_notification != "") {
				// Seggregate date and list of codes
				let notification_date = pic_notification.split(":")[0];
				let notification_codes = pic_notification.split(":")[1].split(",");
				if (notification_codes.includes(code))
					ndates.push(notification_date);
			}
		});
		return ndates.join(",");
	}


	// Display current picture
	function display_pic() {
		$("#current-pic").css("max-width", (($(window).width() - 180) * 0.65) - 30 );
		$("#current-pic").css("max-height", (($(window).height() - 0) * 0.75) - 30 );
		$("#current-pic").attr("src", "../salons/" + yearmonth + "/upload/" + pic.section + "/" + pic.picfile);
		$("#current-pic-link").attr("href", "../salons/" + yearmonth + "/upload/" + pic.section + "/" + pic.picfile);

		$("#current-pic-eseq").html(pic.eseq);
		$("#current-pic-title").html(pic.title);

		if (pic.reviewed == 0) {
			$("#show-reviewed").hide();
		}
		else {
			$("#show-reviewed-by").html(pic.reviewed_by);
			$("#show-reviewed").show();
		}

		if (pic.no_accept == 0) {
			$("#show-tagged").hide();
		}
		else {
			$("#show-tagged").show();
		}

		$("#pic-width").html(pic.width);
		$("#pic-height").html(pic.height);
		if (pic.width < undersized_width && pic.height < undersized_height)
			$("#undersized").show();
		else
			$("#undersized").hide();

		let rejection_text = notification_str();
		if (rejection_text != "")
			$("#rejection-text").html("[ <i class='fa fa-exclamation-triangle'></i> " + rejection_text + "]");
		else
			$("#rejection-text").html("");

		if (pic['exif_str'] == "")
			$("#exif-str").html("");
		else
			$("#exif-str").html("[" + pic.exif_str + "]");

		if (pic['profile_id'] != current_profile_id) {
			current_profile_id = pic['profile_id'];
			show_salon_uploads();
			show_past_acceptances();
		}
		else {
			// Highlight this picture in salon uploads
			$("[id|='oth-upload-pic-id']").css("background-color", "#fff");
			$("#oth-upload-pic-id-" + pic.pic_id).css("background-color", "#222");
		}
	}

	// Display the picture shown under pic_index
	function show_pic() {
		$("#errmsg").html("");
		let profile_id = pic_list[pic_index][0];
		let pic_id = pic_list[pic_index][1];
		$.post("ajax/get_pic_data.php", {yearmonth, profile_id, pic_id}, function(result) {
			let data = JSON.parse(result);
			if (data.success) {
				pic = data.pic;
				display_pic();
			}
			else {
				$("#errmsg").html(data.msg);
			}
		});
	}

	function show_next_pic() {
		if (pic_index < pic_list.length - 1) {
			++ pic_index;
			show_pic();
		}
		else
			$("#errmsg").html("You are at the last picture");
	}

	function show_prev_pic() {
		if (pic_index > 0) {
			-- pic_index;
			show_pic();
		}
		else
			$("#errmsg").html("You are at the first picture");
	}

	function mark_reviewed() {
		let profile_id = pic.profile_id;
		let pic_id = pic.pic_id;
		$.post("ajax/mark_pic_reviewed.php", {yearmonth, profile_id, pic_id}, function(result) {
			let data = JSON.parse(result);
			if (data.success) {
				$("#oth-upload-pic-id-" + pic.pic_id).css("border", "solid 2px #62cb31");
				show_next_pic();
			}
			else {
				$("#errmsg").html(data.msg);
			}

		});
	}

	function tag_no_acceptance() {
		let profile_id = pic.profile_id;
		let pic_id = pic.pic_id;
		$.post("ajax/mark_pic_reviewed.php", {yearmonth, profile_id, pic_id, tag_no_acc : 1}, function(result) {
			let data = JSON.parse(result);
			if (data.success) {
				$("#oth-upload-pic-id-" + pic.pic_id).css("border", "solid 2px #ffb606");
				show_next_pic();
			}
			else {
				$("#errmsg").html(data.msg);
			}

		});
	}

	function untag_no_acceptance() {
		let profile_id = pic.profile_id;
		let pic_id = pic.pic_id;
		$.post("ajax/mark_pic_reviewed.php", {yearmonth, profile_id, pic_id, tag_no_acc : 0}, function(result) {
			let data = JSON.parse(result);
			if (data.success) {
				$("#oth-upload-pic-id-" + pic.pic_id).css("border", "solid 2px #62cb31");
				show_next_pic();
			}
			else {
				$("#errmsg").html(data.msg);
			}

		});
	}

	function notify_dialog() {
		// Populate the Dialog
		$("#ntf-profile-id").val(pic.profile_id);
		$("#ntf-pic-id").val(pic.pic_id);
		$("#ntf-email").val(pic.email);
		$("#ntf-name").val(pic.profile_name);
		$("#ntf-picfile").val(pic.picfile);
		$("#ntf-title").val(pic.title);
		$("#ntf-eseq").val(pic.eseq);

		// Notification Checkboxes and Dates
		$(".ckb-notification").each(function(){
			let code = $(this).val();
			let ndates = notification_dates(code);
			if (ndates == "") {
				$(this).prop("checked", false);
				$("#pic-" + code).html("");
			}
			else {
				$(this).prop("checked", true);
				$("#pic-" + code).html(ndates);
			}
		});

		// Determine state of Notify button
		if (there_is_notification())
			$("#sub-notify").prop("disabled", false);
		else
			$("#sub-notify").prop("disabled", true);

		// Show the Dialog
		notification_panel_on = true;
		$("#ntf-modal").modal("show");
	}

	// Test is any notification has been checked
	function there_is_notification() {
		return ($(".ckb-notification:checked").length > 0);
	}

    $(document).ready(function () {

		// Scroll to top
		window.scrollTo(0, 0);

		// Set Panel Sizes
		$("#img-panel").css("width", ($(window).width() - 180) * 0.65);
		$("#img-panel").css("height", ($(window).height() - 0) * 0.85);
		$("#side-panel").css("width", ($(window).width() - 180) * 0.32);
		$("#side-panel").css("height", ($(window).height() - 0) * 0.92);


		// Display the current picture
		show_pic();

		// Register Handlers
		// Forward Button
		$("#btn-go-next").click(show_next_pic);

		// Backward Button
		$("#btn-go-prev").click(show_prev_pic);

		// Mark Reviewed
		$("#btn-mark-reviewed").click(mark_reviewed);

		// Tag No-Acceptance
		$("#btn-tag-no-acc").click(tag_no_acceptance);

		// Untag No Acceptance
		$("#btn-untag-no-acc").click(untag_no_acceptance);

		// Open Notify Dialog
		$("#btn-notify").click(notify_dialog);

		// Return to previous screen
		$("#btn-return").click(function(){
			// window.history.go(-1);
			// $("#last_pic").val(pic.profile_id + "-" + pic.pic_id);
			$("#last_pic").val(pic.eseq);
			$("#review-dash-form").submit();
		});

		// Handle Closing of Modal
		$("#ntf-modal").on("hidden.bs.modal", function(){ notification_panel_on = false; });

		// Keyboard Handlers
		$(document).keydown(function(e) {
			if (! notification_panel_on) {
				// [N]ext or Right Arrow
				if (e.keyCode == 39 || e.keyCode == 78) {
					show_next_pic();
				}
				// [P]rev or Left Arrow Key
				else if (e.keyCode == 37 || e.keyCode == 80) {
					show_prev_pic();
				}
			}
		});

		// Handle Enable/Disable of Notify button based on checking of Checkboxes
		$(".ckb-notification").click(function(){
			if (there_is_notification())
				$("#sub-notify").prop("disabled", false);
			else
				$("#sub-notify").prop("disabled", true);
		});
	});

	// Invoke Notification
	$(".action-notify").click(function() {
		var yearmonth = "<?= $admin_yearmonth; ?>";
		var section = "<?= $section_param; ?>";
		var profile_id = $("#ntf-profile-id").val();
		var pic_id = $("#ntf-pic-id").val();
		var action = $(this).val();
		var email = $("#ntf-email").val();
		var name = $("#ntf-name").val();
		var picture = $("#ntf-picfile").val();
		var title = $("#ntf-title").val();
		var eseq = $("#ntf-eseq").val();
		var notification = $(".ckb-notification:checked").map(function() { return $(this).val(); }).get();

		// Reset Error
		$("#ntf-errmag").html("");

		$.post(
			"ajax/notify.php",
			{yearmonth, profile_id, pic_id, email, name, picture, title, eseq, section, notification, action},
			function (data) {
				var retval = JSON.parse(data);
				if (retval.status == "OK" || (retval.status == "ERROR" && retval.updated) ) {
					$("#ntf-modal").modal("hide");
					if (notification.length > 0)
						$("#oth-upload-pic-id-" + pic.pic_id).css("border", "solid 2px #e74c3c");
					else
						$("#oth-upload-pic-id-" + pic.pic_id).css("border", "solid 2px #62cb31");
					show_next_pic();
				}
				else if (retval.status == "ERROR") {
					$("#ntf-errmsg").html(retval.context + ": " + retval.errmsg + (retval.updated ? " (database updated)" : ""));
				}
			}
		);
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
