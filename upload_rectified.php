<?php
// Upload a rectified image where notification has been sent
// Invoked either from the emails sent to the user or by admin from the image review panel
include_once("inc/session.php");

// Check Permissions
function has_permission($for_list, $permission_list = ["viewer", "reviewer", "receiver", "treasurer", "secretary", "chairman", "admin"] ) {
	foreach($for_list as $for)
		if (in_array($for, $permission_list))
			return true;
	return false;
}

$edit_upload_code = "";
$edit_award_detail = "";
if (isset($_REQUEST['code'])) {
	debug_dump("code", decode_string_array($_REQUEST['code']), __FILE__, __LINE__);
	list($yearmonth, $profile_id, $pic_id, $user_check) = explode("|", decode_string_array($_REQUEST['code']));
	//list($yearmonth, $award_id, $profile_id, $pic_id) = explode("|", decode_string_array($_REQUEST['code']));

	// $upload_code = strrev($_REQUEST['code']);	// Reverse to restore original sequence
	// $entry_id = substr($upload_code, 0, 4);
	// $pic_id = substr($upload_code, 4);

	// Get a list of User Notification Email names
	$notifications = [];
	$sql  = "SELECT * FROM email_template ";
	$sql .= " WHERE template_type = 'user_notification' ";
	$sql .= " ORDER BY template_name";
	$notq = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	while ($notr = mysqli_fetch_array($notq)) {
		$notifications[$notr['template_code']] = $notr;
	}

	// Check if this link is valid
	$sql = "SELECT * FROM contest WHERE yearmonth = '$yearmonth' ";
	$qry = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	if ($contest = mysqli_fetch_array($qry)) {
		// Fetch Link details
		$sql  = "SELECT profile.profile_name, profile.yps_login_id, profile.email, ";
		$sql .= "       pic.section, pic.title, pic.picfile, pic.notifications, ";
		$sql .= "       section.submission_last_date ";
		$sql .= "  FROM pic, profile, section ";
		$sql .= " WHERE pic.yearmonth = '$yearmonth' ";
		$sql .= "   AND pic.profile_id = '$profile_id' ";
		$sql .= "   AND pic.pic_id = '$pic_id' ";
		//$sql .= "   AND pic.notifications != '' ";
		$sql .= "   AND profile.profile_id = pic.profile_id ";
		$sql .= "   AND section.yearmonth = pic.yearmonth ";
		$sql .= "   AND section.section = pic.section ";
		$qry = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		if ($res = mysqli_fetch_array($qry)) {
			// Check validity of admin_id
			$is_run_by_admin = false;

			if ($user_check != $res['profile_name']) {
				$sql = "SELECT * FROM team WHERE yearmonth = '$yearmonth' AND member_login_id = '$user_check' ";
				$subqry = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
				if (mysqli_num_rows($subqry) == 0) {
					$_SESSION['err_msg'] = "Admin Validation Failed !";
					header('Location: /index.php');
					printf("<script>location.href='/index.php'</script>");
					die();
				}
				$admin = mysqli_fetch_array($subqry);
				if (! has_permission(explode(",", $admin['permissions']), ["admin", "secretary", "reviewer", "manager"])) {
					$_SESSION['err_msg'] = "Admin Permission Failed !";
					header('Location: /index.php');
					printf("<script>location.href='/index.php'</script>");
					die();
				}
				$is_run_by_admin = true;
			}

			// Check last date for upload
			if (! $is_run_by_admin) {
				// if ( date_tz(date("Y-m-d", strtotime("+1 day", strtotime($res['submission_last_date']))), $contest['submission_timezone']) > date("Y-m-d") ) {
				if ( date_tz("now", $contest['submission_timezone']) > date("Y-m-d", strtotime($res['submission_last_date'] . " +1 day")) ) {
					$_SESSION['err_msg'] = "The last date for upload of rectified image is over !";
					header('Location: /index.php');
					printf("<script>location.href='/index.php'</script>");
					die();
				}
				if ( $res['notifications'] == "") {
					$_SESSION['err_msg'] = "No issue has been reported against this picture !";
					header('Location: /index.php');
					printf("<script>location.href='/index.php'</script>");
					die();
				}
			}

			$section = $res['section'];
			$yps_login_id = $res['yps_login_id'];
			$email = $res['email'];
			$profile_name = $res['profile_name'];
			$title = $res['title'];
			$tn_picfile = "/salons/" . $yearmonth . "/upload/" . $section . "/tn/" . $res['picfile'];

			// Assemble Notifications
			// Store dates of notifications in the array with notification code as the key
			$pic_notification_list = "";
			foreach(explode("|", $res['notifications']) as $notification_str) {
				if ($notification_str != "") {
					$nr = explode(":", $notification_str);
					foreach(explode(",", $nr[1]) as $nc) {
						if (isset($notifications[$nc]))
							$pic_notification_list .= "<li>" . $nr[0] . ": " . $notifications[$nc]['template_name'] . "</li>";
						else
							$pic_notification_list .= "<li>" . $nr[0] . ": " . $nc . "</li>";
					}

				}
			}
?>
<!DOCTYPE html>
<html lang="en">

<head>
<?php include_once("inc/header.php"); ?>
<style>
table th, td {
	font-size: 14px;
	line-height: 20px;
}

.img-center {
	display: block;
	max-width: 100%;
	margin-left: auto;
	margin-right: auto;
}

</style>


</head>

<body class="<?php echo THEME;?>">
	<?php include_once("inc/navbar.php") ;?>
    <div class="wrapper">
    <?php  include_once("inc/Slideshow.php") ;?>

		<div class="container">
			<div class="row">
				<div class="col-sm-12">
					<h2 class="primary-font">Upload Rectified File</h2>
					<p>You are replacing an image to rectify the errors notified through email. If you face any challenges, please
						reply to the email and include details of the problems faced and error message displayed.</p>
					<br><br>
					<div class="row">
						<br>
						<div class="col-sm-12 col-md-12 col-lg-12">
							<div class="upload-form" id="myTab">
								<!-- Loading image made visible during processing -->
								<div id="loader_img" style="display:none;background: rgba(0, 0, 0, 0.5);z-index: 9999;">
									<img alt="Loader Image" title="Loading..." src="img/progress_bar.gif">
								</div>
								<form role="form" method="post" action="#" enctype="multipart/form-data" name="upload-form" id="upload-form" class="imageUpload">
									<input type="hidden" name="yearmonth" value="<?php echo $yearmonth;?>" >
									<input type="hidden" name="profile_id" value="<?php echo $profile_id;?>" >
									<input type="hidden" name="yps_login_id" value="<?php echo $yps_login_id;?>" >
									<input type="hidden" name="email" value="<?php echo $email;?>" >
									<input type="hidden" name="pic_id" value="<?php echo $pic_id;?>" >
									<div class="form-group">
										<div class="row">
											<div class="col-sm-3 col-md-3 col-lg-3"></div>
											<div class="col-sm-6 col-md-6 col-lg-6">
												<h4 class="text text-color">Picture to be Replaced</h4>
											</div>
											<div class="col-sm-3 col-md-3 col-lg-3"></div>
										</div>
										<div class="row">
											<div class="col-sm-3 col-md-3 col-lg-3"></div>
											<div class="col-sm-2 col-md-2 col-lg-2">
												<img src="<?php echo $tn_picfile;?>" class="img-center">
											</div>
											<div class="col-sm-4 col-md-4 col-lg-4">
												<table class="table">
													<tr><td>Name:</td><td><?php echo $profile_name;?></td></tr>
													<tr><td>Section:</td><td><?php echo $section;?></td></tr>
													<tr><td>Title:</td><td><?php echo $title;?></td></tr>
													<tr>
														<td>Notifications:</td>
														<td>
															<ul><?= $pic_notification_list;?></ul>
														</td>
													</tr>
												</table>
											</div>
											<div class="col-sm-3 col-md-3 col-lg-3"></div>
										</div>
									</div>
									<div class="form-group">
										<!-- Heading -->
										<div class="row">
											<div class="col-sm-3 col-md-3 col-lg-3"></div>
											<div class="col-sm-6 col-md-6 col-lg-6">
												<h4 class="text text-color">Replace with</h4>
											</div>
											<div class="col-sm-3 col-md-3 col-lg-3"></div>
										</div>
										<!-- Select File -->
										<div class="row">
											<div class="col-sm-3 col-md-3 col-lg-3"></div>
											<div class="col-sm-6 col-md-6 col-lg-6">
												<label for="photo">Select Replacement Image File (must be in JPEG format)</label>
												<input type="file" name="photo" onchange="loadPhoto(this);" class="files" id="photo" required>
												<br>
											</div>
											<div class="col-sm-3 col-md-3 col-lg-3"></div>
										</div>
										<!-- Thumbanil of Replacement Picture -->
										<div class="row">
											<div class="col-sm-4 col-md-4 col-lg-4"></div>
											<div class="col-sm-4 col-md-4 col-lg-4">
												<img class="img-responsive" id="replacement-img"
													 style="margin-left:auto; margin-right:auto; max-height: 200px;"
													 src="img/preview.png" >
											</div>
											<div class="col-sm-4 col-md-4 col-lg-4"></div>
										</div>
										<!-- Progress Bar -->
										<div class="row">
											<div class="col-sm-4 col-md-4 col-lg-4"></div>
											<div class="col-sm-4 col-md-4 col-lg-4">
												<div class="progress" style="height: 4px; margin-top: 4px;">
													<div class="progress-bar" role="progressbar" id="progress-bar"
														 style="width: 0%; background-color: orange;"
													 	 aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
													</div>
												</div>
											</div>
											<div class="col-sm-4 col-md-4 col-lg-4"></div>
										</div>
										<!-- Validation Fields -->
										<div class="row">
											<div class="col-sm-3"></div>
											<div class="col-sm-6">
												<div class="row">
													<div class="col-sm-4">
														<label class="control-label">File Size <br><small><small>(Max <?= $contest['max_file_size_in_mb'];?> MB)</small></small></label>
														<input type="text" class="form-control" name="file_size_disp" id="file-size-disp" value="" readonly />
														<input type="hidden" name="file_size" id="file-size" value="" >
														<span class="file_size_warning" id="file-size-warning" style="display:none;">
															<p class="text text-danger">File Size Exceeds <?= $contest['max_file_size_in_mb']; ?>MB</p>
														</span>
													</div>
													<div class="col-sm-4">
														<label class="control-label">Width <br><small><small>(Max <?= $contest['max_width'];?> px)</small></small></label>
														<input type="text" class="form-control" name="width" id="pic-width" value="" readonly/>
													</div>
													<div class="col-sm-4">
														<label class="control-label">Height <br><small><small>(Max <?= $contest['max_height'];?> px)</small></small></label>
														<input type="text" class="form-control" name="height" id="pic-height" value="" readonly/>
													</div>
													<div class="col-sm-12" id="dimension-warning" style="display: none;">
														<span class="text text-danger" id="resize-dimension"></span>
													</div>
													<div class="col-sm-12" id="pic-info-display" style="display: none;">
														<span class="text text-info" id="pic-info"></span>
													</div>
												</div>
											</div>
											<div class="col-sm-3"></div>
										</div>
									</div>

									<!-- Picture Title -->
									<div class="form-group">
										<div class="row">
											<div class="col-sm-3 col-md-3 col-lg-3"></div>
											<div class="col-sm-6">
												<label class="control-label">Title</label>
												<input type="text" maxlength="35" class="form-control"
												 		name="pic_title" id="pic_title" value="<?= $title;?>" />
											</div>
											<div class="col-sm-3 col-md-3 col-lg-3"></div>
										</div>
									</div>

									<!-- Accept User Password to validate identity -->
									<div class="form-group">
										<?php
											if ($is_run_by_admin) {
										?>
										<input type="hidden" name="user_auth" id="user_auth" value="ADMIN" />
										<?php
											}
											else {
										?>
										<div class="row">
											<div class="col-sm-3 col-md-3 col-lg-3"></div>
											<div class="col-sm-6">
												<label class="control-label">Enter Your Password</label>
												<input type="password" class="form-control" placeholder="Your password..."
												 		name="user_auth" id="user_auth" value="" />
											</div>
											<div class="col-sm-3 col-md-3 col-lg-3"></div>
										</div>
										<?php
											}
										?>
									</div>
									<div class="form-group">
										<div class="row">
											<div class="col-sm-3 col-md-3 col-lg-3"></div>
											<div class="col-lg-6 col-md-6 col-sm-6">
												<button type="submit" class="btn btn-color pull-right" id="upload_rectified_img"
														name="upload_rectified_img" >
													Upload Photo
												</button>
												<br><br>
											</div>
											<div class="col-sm-3 col-md-3 col-lg-3"></div>
										</div>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

    <!-- Footer -->
	<?php include_once("inc/footer.php") ;?>
	</div>
    <!-- Style Toggle -->
	<?php include_once("inc/settingToggle.php") ;?>

    <!-- JavaScript -->
	<?php include_once("inc/scripts.php"); ?>

	<!-- Form Validation -->
	<script src="plugin/validate/js/jquery.validate.min.js"></script>

	<!-- Custom Validation Functions -->
	<script src="custom/js/validate.js"></script>
	<!-- Form Validation -->
	<script>
		$(document).ready(function() {
			// Validator for Picture Upload Form
			$('#upload-form').validate({
				rules:{
					photo:{
						required : true,
						extension : "jpg,jpeg",
					},
					pic_title: {
						required: true,
						maxlength : 35,
					},
					user_auth : <?= $is_run_by_admin ? "false" : "true";?>
				},
				messages:{
					photo:{
						required:'Select a full resolution picture to upload',
					},
				},
				errorElement: "div",
				errorClass: "valid-error",
				submitHandler: function(form) {
					// Perform validations on readiness based on hidden fields
					if ( $("#file-size-warning").css("display") == "block" || $("#dimension-warning").css("display") == "block" ) {
						swal("Fix Errors", "The picture selected for replacement, has errors. Please fix and try uploading.", "error");
					 }
					 else {
						 // Get encrypted form data for submission

						// var formData = new FormData(form);
						var formData = encryptFormData(new FormData(form));
						$('#loader_img').show();
						$.ajax({
								url: "ajax/upload_rectified_img.php",
								type: "POST",
								data: formData,
								cache: false,
								processData: false,
								contentType: false,
								xhr : function() {
										// Add a hook to monitor upload progress
										let xhr = $.ajaxSettings.xhr();

										if (xhr.upload) {
											xhr.upload.onprogress = function(e) {
												// Update Progress Bar
												if (e.lengthComputable) {
													var percentComplete = Math.round((e.loaded / e.total) * 100);
													$("#progress-bar").attr("aria-valuenow", percentComplete.toFixed(0));
													$("#progress-bar").css("width", percentComplete.toFixed(0) + "%");
													console.log(percentComplete + '% uploaded');
												}
											}
										}

										// return xhr with custom hook to show progress
										return xhr;
								},
								error: function() {
									$('#loader_img').hide();
									$("#progress-bar").css("background-color", "red");
									swal("Upload Failed!", "File is being rejected by server. Please try again. ", "error");
								},
								success: function(response) {
									$('#loader_img').hide();
									$("#progress-bar").css("background-color", "green");
									response = JSON.parse(response);
									if(response.success){
										document.location.href = '/index.php';
									}
									else{
										swal("Upload Failed!", response.msg, "error");
										$('html, body').animate({ scrollTop: $("#myTab").offset().top-150 }, 500);
									}
								}
						});
					}
				},
			});
		});
	</script>

	<script>
		function showPhoto(picfile) {
			/* Import JPEG Meta File */
			var $j = this.JpegMeta.JpegFile;

			$("#upload-progress-bar").css("width", "0%");

			// File Size checking
			$("#file-size").val(picfile.size);
			$("#file-size-disp").val((picfile.size / 1024 / 1024).toFixed(2) + " MB");
			if (picfile.size > <?= ($contest['max_file_size_in_mb'] * 1024 * 1024);?>)
				$("#file-size-warning").css("display", "block");
			else
				$("#file-size-warning").css("display", "none");

			// Meta values checking
			var reader = new FileReader();

			reader.onload = function (e) {
				// Display Thumbnail
				$("#replacement-img").attr('src', e.target.result);

				// Read and Display EXIF
				var jpeg = new $j(atob(this.result.replace(/^.*?,/,'')), picfile);

				// Height & Width
				var pic_width = jpeg.general.pixelWidth.value;
				var pic_height = jpeg.general.pixelHeight.value;
				$("#pic-width").val(pic_width);
				$("#pic-height").val(pic_height);

				// Resize Suggestion
				var max_height = <?= $contest['max_height'];?>;
				var max_width = <?= $contest['max_width'];?>;
				var resize_height = pic_height;
				var resize_width = pic_width;
				if (pic_width > max_width || pic_height > max_height) {
					// First, adjust height to make resize_width = max_width;
					resize_height = Math.floor(resize_height * max_width / resize_width);
					resize_width = max_width;
					// Second, if height is still larger than allowed max_height, reduce dimensions so that height = max_height
					if (resize_height > max_height) {
						resize_width = Math.floor(resize_width * max_height / resize_height);
						resize_height = max_height;
					}
					$("#resize-dimension").html("Too Large ! Resize to " + resize_width + "x" + resize_height);
					$("#dimension-warning").css("display", "block");
				}
				else {
					$("#dimension-warning").css("display", "none");
					if (pic_width < (max_width * 0.8) && pic_height < (max_height * 0.8) ) {
						let html = "The picture is smaller than the maximum permitted dimensions of <?= $contest['max_width'];?>px width ";
						html += " & <?= $contest['max_height'];?>px height. You will still be able to upload the picture. For best impact, ";
						html += " please upload a larger picture.";
						$("#pic-info").html(html);
						$("#pic-info-display").show();
					}
				}
			};
			reader.readAsDataURL(picfile);
		}

		function loadPhoto(input) {
			if (input.files && input.files[0] != "") {
				showPhoto(input.files[0]);
			}
		}

	</script>

  </body>

</html>
<?php
		}
		else {
			$_SESSION['err_msg'] = "Invalid Upload Link !";
			header('Location: /index.php');
			printf("<script>location.href='/index.php'</script>");
		}
	}
	else {
		$_SESSION['err_msg'] = "Invalid/Expired Upload Link !";
		header('Location: /index.php');
		printf("<script>location.href='/index.php'</script>");
	}
}
else {
	$_SESSION['err_msg'] = "Invalid Parameters!";
	header('Location: /index.php');
	printf("<script>location.href='/index.php'</script>");
}
?>
