<?php
include_once("inc/session.php");
include_once("inc/categories.php");
include_once("inc/user_lib.php");

if(isset($_SESSION['USER_ID'])) {

	// Check for blacklist
	if ($tr_user['blacklist_match'] != "" && $tr_user['blacklist_exception'] == 0)
		handle_error("Not permitted due to match with Blacklisted profile.", __FILE__, __LINE__);

	// No need to show the form once Entrant Category has already been chosen
	if ($tr_user['entrant_category'] != "" && $tr_user['payment_received'] > 0) {
		$_SESSION['err_msg'] = "You have already registered for the Salon and Paid.";
		header('Location: user_panel.php');
		printf("<script>location.href='user_panel.php'</script>");
		die();
	}

	$ec_list = array();
	$entrant_category = "";
	$group_code = "";
	$ec_from_group = false;

	// If the user has a coupon issued, the entrant_category and group_code are set to those values
	$sql  = "SELECT entrant_category, group_code ";
	$sql .= "  FROM club_entry, coupon ";
	$sql .= " WHERE club_entry.yearmonth = '$contest_yearmonth' ";
	$sql .= "   AND club_entry.club_id = '" . $tr_user['club_id'] . "' ";
	$sql .= "   AND coupon.yearmonth = club_entry.yearmonth ";
	$sql .= "   AND coupon.club_id = club_entry.club_id ";
	$sql .= "   AND (coupon.email = '" . $tr_user['email'] . "' ";
	$sql .= "        OR coupon.profile_id = '" . $tr_user['profile_id'] . "') ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_num_rows($query) == 1) {
		$result = mysqli_fetch_array($query);
		$ec_from_group = true;
		$entrant_category = $result['entrant_category'];
		$group_code = $result['group_code'];
		$sql = "SELECT * FROM entrant_category WHERE yearmonth = '$contest_yearmonth' AND entrant_category = '" . $result['entrant_category'] . "' ";
		$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		if (mysqli_num_rows($query) > 0) {
			$ec_list[$entrant_category] = mysqli_fetch_array($query, MYSQLI_ASSOC);
		}
	}

	if (sizeof($ec_list) == 0) {
		// Get a list of eligible Entrant Category List for this user
		$ec_list = ec_get_eligible_ec_list($contest_yearmonth, $tr_user);
	}

	if ($ec_list == false || sizeof($ec_list) == 0) {
		$_SESSION['err_msg'] = "None of the Entrant Categories for this Salon match your profile. Please check the Eligibility Criteria under Salon Rules.";
		header('Location: user_panel.php');
		printf("<script>location.href='user_panel.php'</script>");
		die();
	}

?>

<!DOCTYPE html>
<html lang="en">

<head>
<?php include_once("inc/header.php"); ?>

<!-- Google reCaptcha -->
<!-- Blocking Recaptcha in logged-in forms
<script src='https://www.google.com/recaptcha/api.js?onload=onLoadGoogleRecaptcha&render=explicit' async defer></script>
<script type='text/javascript'>
    function onLoadGoogleRecaptcha() {
        $("#captcha_method").val("google");
        grecaptcha.render("googleRecaptcha", { "sitekey" : "6LfpiFQUAAAAALdj6b1ZSgKJjh7LQkxXo_Xs2KNu" });
        $("#googleRecaptcha").show();
        $("#phpCaptcha").hide();
    }
</script>

<!-- PHP Captcha
<script type='text/javascript'>
    function refreshCaptcha(){
        var img = document.images['captchaimg'];
        img.src = img.src.substring(0,img.src.lastIndexOf("?"))+"?rand="+Math.random()*1000;
    }
</script>
-->

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
					<?php include("inc/user_sidemenu.php");?>
				</div>
				<div class="col-lg-9 col-md-9 col-sm-9">
					<div id="hasResponse"></div>
					<div id="loader_img" style="display:none;background: rgba(0, 0, 0, 0.5);z-index: 9999;">
						<img alt="Loader Image" title="Loading..." src="img/progress_bar.gif">
					</div>

					<div class="sign-form" id="myTab">
						<h3 class="first-child text text-color">Registering for <?=$contestName;?></h3>
						<hr>
						<form role="form" method="post" action="#" id="salon_registration_form" name="salon_registration_form" enctype="multipart/form-data">
							<input type="hidden" name="yearmonth" value="<?=$contest_yearmonth;?>" >
							<input type="hidden" name="profile_id" value="<?=$tr_user['profile_id'];?>" >
							<input type="hidden" name="yps_login_id" value="<?=$tr_user['yps_login_id'];?>" >
							<input type="hidden" name="club_id" value="<?=$tr_user['club_id'];?>" >
							<input type="hidden" name="payment_mode" value="<?=$tr_user['club_payment_mode'];?>" >
							<input type="hidden" name="contest_digital_sections" value="<?=$contestDigitalSections;?>" >	<!-- NOT USED -->
							<input type="hidden" name="contest_print_sections" value="<?=$contestPrintSections;?>" >		<!-- NOT USED -->
							<input type="hidden" name="group_code" value="<?=$group_code;?>" >

							<h4 class="text text-color">Select a Category to participate *</h4>
							<?php
								if ($ec_from_group) {
							?>
							<p class="text text-muted">Your category of participation has been set based on settings selected by your club/group (<?=$tr_user['club_name'];?>)
								co-ordinator.</p>
							<div class="form-group">
								<div class="row">
									<div class="col-sm-4">
										<div class="radio">
											<label><input type="radio" value="<?=$entrant_category;?>" name="entrant_category" id="ec_<?=$entrant_category;?>"
														checked readonly ><?=$ec_list[$entrant_category]['entrant_category_name'];?></label>
										</div>
									</div>
									<div class="col-sm-8">
										<p class="text text-color lead"><b><?=$entrant_category;?> Category</b></p>
										<?php ec_print_ec_details($ec_list[$entrant_category]);?>
									</div>
								</div>
							</div>
							<?php
								}
								else {
							?>
							<p class="text text-muted">The list below shows the categories under which you can participate along with a description of
								criteria and features. In most cases, you may find just one Category under which you can participate.
								Where there is a choice, please choose one. Once saved, you cannot change it later.</p>
							<div class="form-group">
								<?php
									foreach($ec_list AS $ec => $ec_row) {
								?>
								<div class="row">
									<div class="col-sm-4">
										<div class="radio">
											<label><input type="radio" value="<?=$ec;?>" name="entrant_category" id="ec_<?=$ec;?>"
														<?php echo ($tr_user['entrant_category'] == $ec) ? 'checked' : '';?> required><?=$ec_row['entrant_category_name'];?></label>
										</div>
									</div>
									<div class="col-sm-8">
										<p class="text text-color lead"><b><?=$ec;?> Category</b></p>
										<?php ec_print_ec_details($ec_row);?>
									</div>
								</div>
								<?php
									}
								?>
							</div>
							<?php
								}
							?>
							<!-- Agree to the Terms and Conditions -->
							<div class="form-group">
								<div class="row">
									<div class="col-sm-12">
										<label for="captcha">Agreement to Terms and Conditions</label>
										<p>This Salon is governed by the following Terms and Conditions:</p>
										<ol type="A">
											<li>Salon Terms and Conditions specified under <a href='term_condition.php' target='_blank'>the Rules Page</a></li>
											<li>Terms and Conditions specified by the following organizations that have extended their patronage to the Salon.
																			Click on the icons to go to their website to familiarize yourself with their terms and conditions,
																			if you plan to apply for their certification/honor</li>
										</ol>
										<p style="margin-left: 20px;">
										<?php
											$sql = "SELECT * FROM recognition WHERE yearmonth = '$contest_yearmonth' ORDER BY short_code ";
											$rcgq = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
											while ($rcgr = mysqli_fetch_array($rcgq)) {
												if ($rcgr['short_code'] == "FIAP" || $rcgr['short_code'] == "FIP")
										?>
											<a href="<?php echo $rcgr['website'];?>" target="_blank">
												<img style="margin-left: 4px; margin-right: 4px; width: 80px;" src="<?= "/salons/$contest_yearmonth/img/recognition/" . $rcgr['logo'];?>" >
											</a>
										<?php
											}
										?>
										</p>
										<ol type="A" start="3">
										<?php
											$sql = "SELECT * FROM recognition WHERE yearmonth = '$contest_yearmonth' ORDER BY short_code ";
											$rcgq = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
											while ($rcgr = mysqli_fetch_array($rcgq)) {
												if ($rcgr['notice'] != "") {
										?>
											<li><?= $rcgr['short_code'];?> Notice - <?= $rcgr['notice'];?></li>
										<?php
												}
											}
										?>
										</ol>
										<div class="checkbox pull-right">
											<label>
												<input type="checkbox" name="agree_to_tc" id="agree_to_tc" value="1" required>
												<b>I agree to all the above Terms &amp; Conditions *</b>
											</label>
										</div>
										<br>
									</div>
								</div>
							</div>

                            <!-- Set Default Validation Method of Captcha Validation to PHP Captcha. Will be changed to google on load -->
                            <input type="hidden" name="captcha_method" id="captcha_method" value="php" />

							<div class="form-group">
								<div class="row">
									<div class="col-sm-3"></div>
									<div class="col-lg-6 col-md-6 col-sm-6">
                                        <!-- <div class="g-recaptcha" data-sitekey="6LfpiFQUAAAAALdj6b1ZSgKJjh7LQkxXo_Xs2KNu"></div> -->
										<!-- Blocking Recaptcha from logged-in forms
                                        <div class="g-recaptcha" id="googleRecaptcha" style="display: none;"></div>
                                        <div id="phpCaptcha" class="row">
                                            <div class="col-sm-4">
                                                <label for="email">Validation code:</label><br>
                                                <img src="inc/captcha/captcha.php?rand=<?php // echo rand();?>" id='captchaimg'>
                                            </div>
                                            <div class="col-sm-8">
                                                <label for="captcha_code">Enter the Validation code displayed :</label>
                                                <input id="captcha_code" class="form-control" name="captcha_code" type="text">
                                                Can't read the image? click <a href='javascript: refreshCaptcha();'>here</a> to refresh
                                            </div>
                                        </div>
										-->
									</div>
									<div class="col-sm-3">
										<br>
										<button type="submit" class="btn btn-color pull-right" name="salon_registration">Register for the Salon</button>
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
	$('#salon_registration_form').validate({
		rules:{
			entrant_category:{ required:true, },
			agree_to_tc: {
				required: true,
				min: 1,
			},
		},
		messages:{
			entrant_category: {
				required: "Select one of the Entrant Categories listed"
			},
			agree_to_tc: {
				required: 'Check the box to indicate agreement to terms and conditions'
			}
		},
		errorElement: "div",
		errorClass: "valid-error",
		submitHandler: function(form) {
			//form.submit();
			var formData = encryptFormData(new FormData(form));
			$('#loader_img').show();
			$.ajax({
					url: "ajax/register.php",
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
									title: "Registration Complete",
									text: "You have successfully registered for the Salon. Select the Sections and make Payment.",
									icon: "success",
									confirmButtonClass: 'btn-success',
									confirmButtonText: 'Great'
							});
							var baseurl= '<?= http_method();?>' + window.location.host;
							setTimeout(function(){ document.location.href = baseurl+'/user_panel.php'; }, 500);

							$('html, body').animate({ scrollTop: $("#myTab").offset().top-150 }, 500);
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
			//return false;
		},
	});
});
</script>

</body>

</html>
<?php
}
else{
	$_SESSION['err_msg'] = "Invalid Request !";
	header("Location: index.php");
	printf("<script>location.href='index.php'</script>");
}
?>
