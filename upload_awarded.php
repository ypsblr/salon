<?php
define ("NO_SESSION_ALIVE", true);
include_once("inc/session.php");

define ("MIN_UPLOAD_SIZE", 2400);

function has_permission($for_list, $permission_list = ["viewer", "reviewer", "receiver", "treasurer", "secretary", "chairman", "admin"] ) {
	foreach($for_list as $for)
		if (in_array($for, $permission_list))
			return true;
	return false;
}

$edit_upload_code = "";
$edit_award_detail = "";
if (isset($_REQUEST['code'])) {
	list($yearmonth, $award_id, $profile_id, $pic_id) = explode("|", decode_string_array($_REQUEST['code']));

	// Check if this is run by an admin_id
	$is_run_by_admin = false;
	if (sizeof(explode("-", $yearmonth)) == 2) {
		list($yearmonth, $admin_id) = explode("-", $yearmonth);
		$sql = "SELECT * FROM team WHERE yearmonth = '$yearmonth' AND member_login_id = '$admin_id' ";
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

	// $upload_code = strrev($_REQUEST['code']);	// Reverse to restore original sequence
	// $entry_id = substr($upload_code, 0, 4);
	// $pic_id = substr($upload_code, 4);

	// Check if this link is valid
	$sql = "SELECT * FROM contest WHERE yearmonth = '$yearmonth' ";
	$qry = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$contest = mysqli_fetch_array($qry);
	if ($yearmonth == $contest['yearmonth'] && ($is_run_by_admin ||  (date("Y-m-d") >= $contest['update_start_date'] && date("Y-m-d") <= $contest['update_end_date']))) {
	// if ($yearmonth == $contest['yearmonth'] && date("Y-m-d") >= $contest['update_start_date'] && date("Y-m-d") <= $contest['update_end_date']) {
		// Fetch Link details
		$sql  = "SELECT profile.profile_name, award.award_name, pic.section, pic.title, pic.picfile ";
		$sql .= "  FROM pic_result, award, pic, profile ";
		$sql .= " WHERE pic_result.yearmonth = '$yearmonth' ";
		$sql .= "   AND pic_result.award_id = '$award_id' ";
		$sql .= "   AND pic_result.profile_id = '$profile_id' ";
		$sql .= "   AND pic_result.pic_id = '$pic_id' ";
		$sql .= "   AND award.yearmonth = pic_result.yearmonth ";
		$sql .= "   AND award.award_id = pic_result.award_id ";
		$sql .= "   AND pic.yearmonth = pic_result.yearmonth ";
		$sql .= "   AND pic.profile_id = pic_result.profile_id ";
		$sql .= "   AND pic.pic_id = pic_result.pic_id ";
		$sql .= "   AND profile.profile_id = pic_result.profile_id ";
		$qry = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		if ($res = mysqli_fetch_array($qry)) {
			$section = $res['section'];
			$award_name = $res['award_name'];
			$profile_name = $res['profile_name'];
			$title = $res['title'];
			$tn_picfile = "/salons/" . $yearmonth . "/upload/" . $section . "/tn/" . $res['picfile'];

?>
<!DOCTYPE html>
<html lang="en">

<head>
<?php include_once("inc/header.php"); ?>

<!-- Google reCaptcha -->
<script src='https://www.google.com/recaptcha/api.js?onload=onLoadGoogleRecaptcha&render=explicit' async defer></script>
<script type='text/javascript'>
    function onLoadGoogleRecaptcha() {
        $("#captcha_method").val("google");
        grecaptcha.render("googleRecaptcha", { "sitekey" : "6LfpiFQUAAAAALdj6b1ZSgKJjh7LQkxXo_Xs2KNu" });
        $("#googleRecaptcha").show();
        $("#phpCaptcha").hide();
    }
</script>

<!-- PHP Captcha -->
<script type='text/javascript'>
    function refreshCaptcha(){
        var img = document.images['captchaimg'];
        img.src = img.src.substring(0,img.src.lastIndexOf("?"))+"?rand="+Math.random()*1000;
    }
</script>


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
					<h2 class="primary-font">Upload Full Resolution File</h2>
					<p>YPS Salon Committee requires full resolution JPEG file for printing 12x18 inch prints in 300 DPI for display at the exhibition
						and/or for compiling the Salon Catalog. Please upload the highest resolution possible. Picture should not be less than <?= MIN_UPLOAD_SIZE;?>
						pixels on any of the two sides. If you face any challenges, please
						write to <a href="mailto:salon@ypsbengaluru.in">salon@ypsbengaluru.in</a> with details of the file and error message.</p>
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
									<input type="hidden" name="award_id" value="<?=$award_id;?>" >
									<input type="hidden" name="profile_id" value="<?php echo $profile_id;?>" >
									<input type="hidden" name="pic_id" value="<?php echo $pic_id;?>" >
									<input type="hidden" name="uploaded_file" id="uploaded_file" value="" >
									<div class="form-group">
										<div class="row">
											<div class="col-sm-3 col-md-3 col-lg-3"></div>
											<div class="col-sm-6 col-md-6 col-lg-6">
												<h4 class="text text-color">Picture to be Uploaded</h4>
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
													<tr><td>Award:</td><td><?php echo $award_name;?></td></tr>
												</table>
											</div>
											<div class="col-sm-3 col-md-3 col-lg-3"></div>
										</div>
									</div>
									<div class="form-group">
										<div class="row">
											<div class="col-sm-3 col-md-3 col-lg-3"></div>
											<div class="col-sm-6 col-md-6 col-lg-6">
												<label for="photo">Select Full Resolution Image File (must be in JPEG format)</label>
												<input type="file"  name="photo" onchange="uploadURL(this);" class="files" id="photo" required>
												<p class="small text-danger" id="file-upload-error"></p>
												<div class="progress" style="width: 50%;">
													<div class="progress-bar" role="progressbar" id="file-upload-progress"
														 style="width: 0%; background-color: #428BCA; color: #ddd;"
														 aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">Uploading...</div>
												</div>
											</div>
											<div class="col-sm-3 col-md-3 col-lg-3"></div>
										</div>
									</div>

									<!-- Set Default Validation Method of Captcha Validation to PHP Captcha. Will be changed to google on load -->
                            		<input type="hidden" name="captcha_method" id="captcha_method" value="php" />

									<div class="form-group" id="div-upload" style="display: none;">
										<div class="row">
											<div class="col-sm-3"></div>
											<div class="col-lg-6 col-md-6 col-sm-6">
												<!-- <div class="g-recaptcha" data-sitekey="6LfpiFQUAAAAALdj6b1ZSgKJjh7LQkxXo_Xs2KNu"></div> -->
												<div class="g-recaptcha" id="googleRecaptcha" style="display: none;"></div>
												<div id="phpCaptcha" class="row">
													<div class="col-sm-4">
														<label for="email">Validation code:</label><br>
														<img src="inc/captcha/captcha.php?rand=<?php echo rand();?>" id='captchaimg'>
													</div>
													<div class="col-sm-8">
														<label for="captcha_code">Enter the Validation code displayed :</label>
														<input id="captcha_code" class="form-control" name="captcha_code" type="text">
														Can't read the image? click <a href='javascript: refreshCaptcha();'>here</a> to refresh
													</div>
												</div>
											</div>
											<div class="col-sm-3 col-md-3 col-lg-3">
												<button type="submit" class="btn btn-color" name="upload_full_img" >Upload Photo</button><br><br>
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

    <script>
    $(document).ready(function(){
		$('html, body').animate({ scrollTop: $("#myTab").offset().top-100 }, 500);
		$("#upload_code").change( function () {
			var upload_code = $("#upload_code").val();
			if (upload_code != "") {
				$.ajax({
					type: 'POST',
					url: 'op/validate_upload_code.php',
					data: {'upload_code' : upload_code},
					success: function(response) {
								if (response == "ERR") {
									$("#award-detail").html("Invalid Upload Code");
								}
								else {
									$("#award-detail").html(response);
								}
					}
				});
			}
		});
	});
    </script>

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
					required:{
						param: true,
						depends: function() { return $("#upload_mode").val() == "new"; },
					},
					extension : "jpg,jpeg",
				},
			},
			messages:{
				photo:{
					required:'Select a full resolution picture to upload',
				},
			},
			errorElement: "div",
			errorClass: "valid-error",
			submitHandler: function(form) {
				// Perform validations on readiness of hidden fields
				//form.submit();
				var formData = new FormData(form);
				formData.delete("photo");		// Remove the upload file
				$('#loader_img').show();
				suspend_ticker = true;		// defined scripts.php to run a ticker every minute
				$.ajax({
						url: "op/upload_full_img.php",
						type: "POST",
						data: formData,
						cache: false,
						processData: false,
						contentType: false,
						timeout: 120000,		// 2 minutes
						error: function() {
							$('#loader_img').hide();
							suspend_ticker = false;		// defined scripts.php to run a ticker every minute
							swal("Upload Failed!", "File is being rejected by server. Please report to YPS using the Contact US page for rectification. ", "error");
						},
						success: function(response) {
							$('#loader_img').hide();
							suspend_ticker = false;		// defined scripts.php to run a ticker every minute
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
				//return false;
			},
		});
	});

	</script>

	<script>
		//$('html, body').animate({ scrollTop: $("#myTab").offset().top-10 }, 500);
		function readURL(input) {
			if (input.files && input.files[0]) {
				var reader = new FileReader();

				reader.onload = function (e) {
					$('#blah')
						.attr('src', e.target.result)
						.width(90)
						.height(80);
				};

				reader.readAsDataURL(input.files[0]);
			}
		}

		// Initialize Ajax Upload
		$(document).ready(function (){
			initAjaxUpload();
		});

		// Ajax upload invoked when input file selected
		function uploadURL(input) {
			$("#file-upload-progress").html("Copying...");
			$("#uploaded_file").val("");
			// $("#upload_full_img").attr("disabled", "true");	// Disallow Upload Buttom

			if (input.files && input.files[0]) {
				ajaxPictureUpload(
					input.id,
					function(tmpfile) {
						$("#uploaded_file").val(tmpfile);
						$("#file-upload-progress").html("Click Upload Photo");
						// $("#file-upload-progress").css("background-color", "#28a745");
						$("#file-upload-progress").css("background-color", "#D26C22");
						$("#div-upload").show();
						// $("#upload_full_img").removeAttr("disabled");	// Allow Upload Buttom
					},
					function(errmsg) {
						$("#file-upload-error").html(errmsg);
					},
					function(percent_complete) {
						$("#file-upload-progress").css("width", percent_complete + "%");
					},
					<?= MIN_UPLOAD_SIZE;?> 		// minimum size
				);
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
