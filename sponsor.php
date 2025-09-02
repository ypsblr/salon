<?php
include_once("inc/session.php");
include_once("inc/lib.php");
include_once("inc/sponsor_lib.php");

$sponsor_yearmonth = empty($_REQUEST['contest']) ? $contest_yearmonth : $_REQUEST['contest'];

// Get Contest Name
$sql = "SELECT contest_name FROM contest WHERE yearmonth = '$sponsor_yearmonth' ";
$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
$row = mysqli_fetch_array($query);
$sponsor_contest_name = $row['contest_name'];


// Check if there are opportunities available
// $sql = "SELECT * FROM opportunity WHERE yearmonth = '$sponsor_yearmonth' ";
// $query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
// if (mysqli_num_rows($query) == 0)
// 	handle_error("No sponsorships open for this Contest/Salon", __FILE__, __LINE__);

$sponsor_id = 0;
$sponsor = false;
if (isset($_SESSION['sponsor_id'])) {
	$sponsor_id = $_SESSION['sponsor_id'];
	$sql = "SELECT * FROM sponsor WHERE sponsor_id = '$sponsor_id' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$sponsor = mysqli_fetch_array($query, MYSQLI_ASSOC);
}

// Get a list of contests with Sponsorship Slots open
$contests_with_sponsorship_open = spn_sponsorship_open_contest_list("pic");


?>

<!DOCTYPE html>
<html lang="en">

<head>
<?php include_once("inc/header.php"); ?>

<!-- Google reCaptcha -->
<script src='https://www.google.com/recaptcha/api.js' async defer></script>

<script type='text/javascript'>
function refreshCaptcha(){
	var img = document.images['captchaimg'];
	img.src = img.src.substring(0,img.src.lastIndexOf("?"))+"?rand="+Math.random()*1000;
}
</script>

</head>

<body class="<?php echo THEME;?>">

    <?php include_once("inc/navbar.php");?>

    <!-- Wrapper -->
    <div class="wrapper">

		<?php  include_once("inc/Slideshow.php") ;?>
		<!-- Slideshow -->

		<div class="container">
			<div class="row blog-p">
				<div class="col-lg-3 col-md-3 col-sm-3">
					<?php //include("inc/user_sidemenu.php");?>
				</div>
				<div class="col-lg-9 col-md-9 col-sm-9">
					<div id="hasResponse"></div>
					<div id="loader_img" style="display:none;background: rgba(0, 0, 0, 0.5);z-index: 9999;">
						<img alt="Loader Image" title="Loading..." src="img/progress_bar.gif">
					</div>

					<div class="sign-form" id="myTab">
						<h3 class="first-child text text-color">SELECT/CREATE SPONSOR</h3>
						<hr>
						<form role="form" method="post" action="#" id="sponsor-form" name="sponsor-form" enctype="multipart/form-data">
							<input type="hidden" name="award_id" id="award_id" value="<?php echo isset($_REQUEST['awid']) ? $_REQUEST['awid'] : 0;?>" >
							<input type="hidden" name="sponsor_id" id="sponsor_id" value="<?=$sponsor_id;?>" >

							<div class="form-group">
								<div class="row">
									<div class="col-sm-6">
										<label for="sponsor_email">Enter your email *</label>
										<input type="text" class="form-control" name="sponsor_email" id="sponsor_email"
														value="<?php echo $sponsor ? $sponsor['sponsor_email'] : '';?>" >
									</div>
									<div class="col-sm-2">
										<br>
										<a href="javascript: get_sponsor()" class="btn btn-color">Edit</a>
									</div>
								</div>
							</div>

							<!-- Sponsor Details. Will be initialized to Blank if Add Sponsor is selected -->
							<div class="form-group">
								<div class="row">
									<div class="col-sm-8">
										<h5 class="text-color">Sponsor Details</h5>
										<div class="form-group" id="sponsor_name_fields">
											<div class="row">
												<div class="col-sm-12">
													<label for="sponsor_salutation">Name *</label>
													<?php
														// extract Salutation from Sponsor Name if $sponsor is set
														if ($sponsor) {
															list($salutation) = explode(" ", $sponsor['sponsor_name']);
															$sponsor_name = trim(substr($sponsor['sponsor_name'], strlen($salutation)));
														}
													?>
													<div class="radio radio-inline" style="vertical-align:inherit">
														<label><input type="radio" value="" name="salutation" id="sal_none" required <?php echo ($sponsor && $salutation == "") ? "checked" : "";?>>None</label>
													</div>
													<div class="radio radio-inline">
														<label><input type="radio" value="Mr." name="salutation" id="sal_mr" <?php echo ($sponsor && $salutation == "Mr.") ? "checked" : "";?> >Mr.</label>
													</div>
													<div class="radio radio-inline">
														<label><input type="radio" value="Ms." name="salutation" id="sal_ms" <?php echo ($sponsor && $salutation == "Ms.") ? "checked" : "";?>>Ms.</label>
													</div>
													<div class="radio radio-inline">
														<label><input type="radio" value="Mrs." name="salutation" id="sal_mrs" <?php echo ($sponsor && $salutation == "Mrs.") ? "checked" : "";?>>Mrs.</label>
													</div>
													<div class="radio radio-inline">
														<label><input type="radio" value="Dr." name="salutation" id="sal_dr" <?php echo ($sponsor && $salutation == "Dr.") ? "checked" : "";?>>Dr.</label>
													</div>
												</div>
											</div>
											<div class="row">
												<div class="col-sm-12">
													<!-- <label  for="sponsor_name">Sponsor Name *</label> -->
													<input type="text" name="sponsor_name" class="form-control" id="sponsor_name" value="<?php echo $sponsor ? $sponsor_name : "";?>" required >
												</div>
											</div>
										</div>
										<div class="form-group">
											<div class="row">
												<div class="col-sm-6">
													<label for="sponsor_phone">Phone Number *</label>
													<input type="text" name="sponsor_phone" class="form-control" id="sponsor_phone" value="<?php echo $sponsor ? $sponsor['sponsor_phone'] : "";?>" required >
												</div>
												<div class="col-sm-6">
													<label for="sponsor_website">Website</label>
													<input type="url" name="sponsor_website" class="form-control" id="sponsor_website" value="<?php echo $sponsor ? $sponsor['sponsor_website'] : "";?>" >
												</div>
											</div>
										</div>
				                    </div>
									<div class="col-sm-4">
										<img src="res/sponsor/<?php echo $sponsor ? $sponsor['sponsor_logo'] : 'user.jpg';?>" id="logo_preview" style="height: 120px; border: 1px solid #CCC;">
										<br>
										<label for="logo">Upload Logo </label>
										<input id="logo" name="logo" type="file" onchange="loadAvatar(this, '#logo_preview');" >
										<input type="hidden" name="old_logo" id="old_logo"  value="<?php echo $sponsor ? $sponsor['sponsor_logo'] : "";?>" >
									</div>
								</div>
							</div>

							<div class="form-group">
								<div class="row">
									<div class="col-sm-12">
										<div class="checkbox pull-right">
											<label>
												<input type="checkbox" name="verified" id="verified" value="1" required>
												<b>I confirm correctness of details provided above *</b>
											</label>
										</div>
										<br>
									</div>
								</div>
							</div>
							<div class="divider"></div>

							<?php
								if (sizeof($contests_with_sponsorship_open) > 0) {
							?>
							<div class="form-group">
								<div class="row">
									<div class="col-sm-6"></div>
									<div class="col-sm-6">
										<label for="sponsor_id">Select the Salon *</label>
										<select name="yearmonth" class="form-control" id="yearmonth" required >
										<?php
											foreach($contests_with_sponsorship_open AS $yearmonth => $contest_name) {
										?>
											<option value="<?=$yearmonth;?>" <?php echo ($yearmonth == $sponsor_yearmonth) ? "selected" : "";?>><?=$contest_name;?></option>
										<?php
											}
										?>
										</select>
									</div>
								</div>
							</div>
							<?php
								}
								else {
							?>
							<input type="hidden" name="yearmonth" id="yearmonth" value="<?= $sponsor_yearmonth;?>" >
							<div class="form-group">
								<div class="row">
									<div class="col-sm-6"></div>
									<div class="col-sm-6">
										<label for="sponsor_id">For Salon</label>
										<input type="text" class="form-control" value="<?= $sponsor_contest_name;?>" readonly>
									</div>
								</div>
							</div>
							<?php
								}
							?>

							<div class="form-group">
								<div class="row">
									<div class="col-sm-3"></div>
									<div class="col-lg-6 col-md-6 col-sm-6">
										<div class="g-recaptcha" data-sitekey="6LfpiFQUAAAAALdj6b1ZSgKJjh7LQkxXo_Xs2KNu"></div>
									</div>
									<div class="col-sm-3">
										<br>
										<button type="submit" class="btn btn-color pull-right" name="sponsor_awards">Sponsor Award</button>
									</div>
								</div>
							</div>

						</form>
					</div>
				</div>
			</div> <!-- / .row -->
			<!-- FOOTER -->
			<div class="row">
				<?php include_once("inc/footer.php") ;?>
			</div>
		</div>	<!-- container -->
    </div> <!-- / .wrapper -->

    <!-- Style Toggle -->

	<?php include_once("inc/settingToggle.php") ;?>
    <!-- JavaScript
    ================================================== -->
	<?php include_once("inc/scripts.php"); ?>

<!-- JS Global -->
<style>
	.valid-error{
		font-size: 12px;
	}
</style>



<!-- Live view of Avatar selected for upload -->
<script>
function loadAvatar(input, target) {
	if (input.files && input.files[0] != "") {
		var reader = new FileReader();

		reader.onload = function (e) {
			$(target).attr('src', e.target.result);
		};
		reader.readAsDataURL(input.files[0]);
	}
}
</script>

<!-- Get Sponsor Details When Selected -->
<script>
// Fill details of sponsor on the form
function render_sponsor(sponsor) {
	$("#sponsor_id").val(sponsor.sponsor_id);
	var salutation = "";
	var sponsor_name = "";
	if (sponsor.sponsor_name.startsWith("Mr.")) {
		salutation = "Mr.";
		$("#sal_mr").prop("checked", true);
		sponsor_name = sponsor.sponsor_name.substr(3).trim();
	}
	else if (sponsor.sponsor_name.startsWith("Ms.")) {
		salutation = "Ms.";
		$("#sal_ms").prop("checked", true);
		sponsor_name = sponsor.sponsor_name.substr(3).trim();
	}
	else if (sponsor.sponsor_name.startsWith("Dr.")) {
		salutation = "Dr.";
		$("#sal_dr").prop("checked", true);
		sponsor_name = sponsor.sponsor_name.substr(3).trim();
	}
	else if (sponsor.sponsor_name.startsWith("Mrs.")) {
		salutation = "Mrs.";
		$("#sal_mrs").prop("checked", true);
		sponsor_name = sponsor.sponsor_name.substr(4).trim();
	}
	else {
		salutation = "None";
		$("#sal_none").prop("checked", true);
		sponsor_name = sponsor.sponsor_name;
	}
	$("#sponsor_name").val(sponsor_name);
	sponsor.sponsor_logo = (sponsor.sponsor_logo == "") ? "user.jpg" : sponsor.sponsor_logo;
	$("#logo_preview").prop("src", "/res/sponsor/" + sponsor.sponsor_logo);
	$("#old_logo").val(sponsor.sponsor_logo);
	$("#sponsor_email").val(sponsor.sponsor_email);
	$("#sponsor_phone").val(sponsor.sponsor_phone);
	$("#sponsor_website").val(sponsor.sponsor_website);		// Reset any file selection
}

// Get Sponsor using Email
function get_sponsor(silent = false) {
	sponsor_email = $("#sponsor_email").val();
	if (sponsor_email != undefined && sponsor_email != "") {
		$.post("ajax/get_sponsor_details.php", { "sponsor_email" : sponsor_email}, function(response){
			response = JSON.parse(response);
			if (response.success) {
				render_sponsor(response.sponsor);
			}
			else {
				if (! silent)
					swal("Selection Failed", "Sponsor Not Found!", "error");
			}
			return false;
		});
	}
}

$(document).ready(function(){
	$("#sponsor_email").change(function(){
		if ($("#sponsor_email").val().includes("@"))
			get_sponsor(true);
	});
});
</script>

<script>
    $(document).ready(function(){
		$('html, body').animate({ scrollTop: $("#myTab").offset().top-100 }, 500);
	});
</script>

<!-- Form Validation -->
<script src="plugin/validate/js/jquery.validate.min.js"></script>

<!-- Custom Validation Functions -->
<script src="custom/js/validate.js"></script>

<!-- Custom Validation for updateInfoForm -->
<script>
$(document).ready(function(){
	$('#sponsor-form').validate({
		rules:{
			yearmonth: {required: true},
			salutation: {required: true},
			sponsor_name: {required: true},
			logo : {extension : "jp?g", },
			sponsor_email: {
					required: true,
					email: true },
			sponsor_phone:{
				required: true,
				number:true,
				minlength: 8,
				maxlength: 15,
			},
			verified: {
				required: true,
				min: 1,
			},
		},
		messages:{
			yearmonth: {
				required: "Select a Salon to sponsor"
			},
			verified: {
				required: 'Check the box to indicate agreement to terms and conditions'
			}
		},
		errorElement: "div",
		errorClass: "valid-error",
		submitHandler: function(form) {
			// form.submit();
			var formData = encryptFormData(new FormData(form));
			$('#loader_img').show();
			$.ajax({
					url: "ajax/save_sponsor.php",
					type: "POST",
					data: formData,
					cache: false,
					processData: false,
					contentType: false,
					success: function(response) {
						$('#loader_img').hide();
						response = JSON.parse(response);
						if(response.success){
							swal({
									title: "Sponsor Details Saved",
									text: "Sponsor details entered have been saved successfully. Loading Details of Awards for sponsoring.",
									icon: "success",
									confirmButtonClass: 'btn-success',
									confirmButtonText: 'Great'
							});
							var baseurl= '<?= http_method();?>' + window.location.host;
							var yearmonth = $("#yearmonth").val();
							var award_id = $("#award_id").val();
							var sponsor_id = response.sponsor_id;
							setTimeout(function(){ document.location.href = baseurl + "/sponsor_award.php?contest=" + yearmonth + "&awid=" + award_id + "&spid=" + sponsor_id; }, 1000);

						}
						else{
							swal({
									title: "Save Failed",
									text: "Something went wrong. Changes could not be saved: " + response.msg,
									icon: "warning",
									confirmButtonClass: 'btn-warning',
									confirmButtonText: 'OK'
							});
							$('html, body').animate({ scrollTop: $("#myTab").offset().top-150 }, 500);
						}
					},
					error : function(xHr, status, error) {
						$('#loader_img').hide();
						swal("Updation Failed!", "Unable to complete the operation (" + status + ") . Try again!", "error");
						$('html, body').animate({ scrollTop: $("#myTab").offset().top-150 }, 500);
					}
			});
			return false;
		},
	});
});
</script>

</body>

</html>
<?php

?>
