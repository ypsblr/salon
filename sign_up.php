<?php
include_once("inc/session.php");
include("inc/user_lib.php");

// Go to User Panel if already logged in
if(isset($_SESSION['USER_ID'])) {
	header('Location:index.php');
  	printf("<script>location.href='index.php'</script>");
	die();
}

// Main
$profile = array (
	"first_name" => isset($_SESSION['first_name']) ? $_SESSION['first_name'] : "",
	"last_name" => isset($_SESSION['last_name']) ? $_SESSION['last_name'] : "",
	"gender" => isset($_SESSION['gender']) ? $_SESSION['gender'] : "",
	"email" => isset($_SESSION['email']) ? $_SESSION['email'] : "",
	"yps_login_id" => isset($_SESSION['yps_login_id']) ? $_SESSION['yps_login_id'] : "",
	"phone" => isset($_SESSION['phone']) ? $_SESSION['phone'] : "",
	"address" => isset($_SESSION['address']) ? $_SESSION['address'] : "",
	"city" => isset($_SESSION['city']) ? $_SESSION['city'] : "",
	"state" => isset($_SESSION['state']) ? $_SESSION['state'] : "",
	"pin" => isset($_SESSION['pin']) ? $_SESSION['pin'] : "",
	"avatar" => isset($_SESSION['avatar']) ? $_SESSION['avatar'] : "user.jpg"
);

// unset($_SESSION['yps_login_id']);
// unset($_SESSION['first_name']);
// unset($_SESSION['last_name']);
// unset($_SESSION['gender']);
// unset($_SESSION['email']);
// unset($_SESSION['phone']);
// unset($_SESSION['address']);
// unset($_SESSION['city']);
// unset($_SESSION['state']);
// unset($_SESSION['pin']);
// unset($_SESSION['avatar']);

// Check if any club has created discount coupon against this email any time so that that club can be made the default choice
$default_club_id = 0;
if ($profile['yps_login_id'] == "") {
	$sql  = "SELECT club_id FROM coupon WHERE email = '" . $profile['email'] . "' ORDER BY yearmonth DESC LIMIT 1";
	$qcpn = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if ($rcpn = mysqli_fetch_array($qcpn))
		$default_club_id = $rcpn['club_id'];
}

// Campaign Media List
$campaign_media_list = ["cm_email" => "Email", "cm_website" => "Website", "cm_friend" => "Through Friend", "cm_club" => "Through my Club",
						"cm_whatsapp" => "Whats App", "cm_facebook" => "Facebook", "cm_twitter" => "Twitter", "cm_instagram" => "Instagram"];
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

<!-- PHP Captcha as alternative if Google reCaptcha is not available -->
<script type='text/javascript'>
    function refreshCaptcha(){
        var img = document.images['captchaimg'];
        img.src = img.src.substring(0,img.src.lastIndexOf("?"))+"?rand="+Math.random()*1000;
    }
</script>

<!-- select2 for selecting or adding club -->
<link rel="stylesheet" href="plugin/select2/css/select2.min.css" />

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
						<h3 class="first-child text text-color">Online Registration Form</h3>
						<hr>
						<form role="form" method="post" action="#" id="signup-form" name="signup-form" enctype="multipart/form-data">

							<input type="hidden" name="yps_login_id" value="<?=$profile['yps_login_id'];?>" >
							<input type="hidden" name="email" value="<?=$profile['email'];?>" >

							<div class="form-group">
								<div class="row">
									<div class="col-sm-4">
										<label>Email: <span class="text text-color"><?=$profile['email'];?></span></label>
										<br>
										<?php if ($profile['yps_login_id'] != "") { ?>
										<label>YPS Login ID: <span class="text text-color"><?=$profile['yps_login_id'];?></span></label>
										<?php } ?>
									</div>
									<?php
										if ($profile['yps_login_id'] == "") {
									?>
									<div class="col-sm-4">
										<label  for="family">New Password *</label>
										<input type="password" name="new_password" class="form-control" id="new_password" >
									</div>
									<div class="col-sm-4">
										<label  for="family">Confirm Password *</label>
										<input type="password" name="confirm_password" class="form-control" id="confirm_password" >
									</div>
									<?php
										}
										else {
									?>
									<input type="hidden" name="new_password" value="">
									<input type="hidden" name="confirm_password" value="">
									<?php
										}
									?>
								</div>
							</div>

							<div class="form-group">
								<div class="row">
									<div class="col-sm-8">
										<div class="form-group">
											<label for="salutation">Salutation *</label>
											<div class="radio radio-inline" style="vertical-align:inherit">
												<label><input type="radio" value="Mr." name="salutation" id="sal_mr" required <?php echo ($profile['gender'] == "MALE") ? "checked" : "";?>  >Mr.</label>
											</div>
											<div class="radio radio-inline"><label><input type="radio" value="Ms." name="salutation" id="sal_ms" <?php echo ($profile['gender'] == "FEMALE") ? "checked" : "";?>>Ms.</label></div>
											<div class="radio radio-inline"><label><input type="radio" value="Mrs." name="salutation" id="sal_mrs" >Mrs.</label></div>
											<div class="radio radio-inline"><label><input type="radio" value="Dr." name="salutation" id="sal_dr" >Dr.</label></div>
										</div>
										<div class="form-group">
											<div class="row">
												<div class="col-sm-6">
													<label  for="family">First Name *</label>
													<input type="text" name="first_name" class="form-control" id="first_name"
															value="<?=$profile['first_name'];?>" <?php echo ($profile['yps_login_id'] != "") ? "readonly" : "";?> >
												</div>
												<div class="col-sm-6">
													<label for="family">Last Name *</label>
													<input type="text" name="last_name" class="form-control" id="last_name"
															value="<?=$profile['last_name'];?>" <?php echo ($profile['yps_login_id'] != "") ? "readonly" : "";?> >
												</div>
											</div>
										</div>
										<div class="form-group">
											<div class="row">
												<div class="col-sm-6">
													<label  for="family">Gender *</label>
													<select class="form-control" name="gender" id="gender" value="<?=$profile['gender'];?>">
														<option value="MALE" <?php echo ($profile['gender'] == 'MALE') ? 'selected' : '';?> >Male</option>
														<option value="FEMALE" <?php echo ($profile['gender'] == 'FEMALE') ? 'selected' : '';?> >Female</option>
														<option value="OTHER" <?php echo ($profile['gender'] == 'OTHER') ? 'selected' : '';?> >Other</option>
													</select>
												</div>
												<div class="col-sm-6">
													<label for="family">Date of Birth *</label>
													<input type="date" name="date_of_birth" class="form-control" id="date_of_birth" >
												</div>
											</div>
										</div>
				                    </div>
									<div class="col-sm-4">
										<img src="res/avatar/<?php echo ($profile['avatar'] != "") ? $profile['avatar'] : 'user.jpg';?>" id="avatar_preview" style="height: 80px; border: 1px solid #CCC;">
										<label for="avatar">Upload Avatar (JPEG) </label>
										<input id="avatar" name="avatar" type="file" onchange="loadAvatar(this, '#avatar_preview');" >
										<p class="text-color"><small>Max size 512 KB</small></p>
										<input type="hidden" name="old_avatar" value="<?php echo ($profile['avatar'] != "") ? $profile['avatar'] : 'user.jpg';?>" >
									</div>
								</div>
							</div>


							<div class="form-group">
								<div class="row">
									<p class="text text-color" style="margin-left: 12px;">
										NOTE: Age Proof is required only if you plan to avail any concessions offered by YPS Salons based on age. You are required to upload
										a valid document (PDF/JPEG) issued by Government or by School/College that contains your name and date-of-birth.
									</p>
									<div class="col-sm-6">
										<label for="age_proof">Age Proof </label>
										<select class="form-control" name="age_proof" id="age_proof" value="none" required>
											<option value="none" selected >None</option>
											<option value="birth_certificate" >Birth Certificate</option>
											<option value="passport" >Passport Front Page</option>
											<option value="Aadhar" >Aadhar</option>
											<option value="school_record" >School Record with DoB</option>
											<option value="id_card" >ID Card with DoB</option>
											<option value="others" >Others</option>
										</select>
									</div>
									<div class="col-sm-6">
										<label for="age_proof_file">Upload Age Proof </label>
										<input id="age_proof_file" name="age_proof_file" type="file" required>
									</div>
								</div>
							</div>

							<div class="form-group">
								<div class="row">
									<div class="col-sm-12">
										<label for="honors">Photographic Honors</label>
										<label for="honors"><small><span style="font-weight:normal;"><i>  (Leave it blank if you are yet to achieve any honors)</i></span></small></label>
										<input type="text" name="honors" class="form-control text-uppercase" id="honors" >
									</div>
								</div>
							</div>

							<div class="form-group">
								<div class="row">
									<div class="col-sm-6">
										<label for="phone">Phone Number *</label>
										<div class="input-group">
											<span class="input-group-btn"><button class="btn btn-default"><span id="dial_prefix">+91</span></button></span>
											<input type="text" name="phone" class="form-control text-uppercase" id="phone" value="<?=$profile['phone'];?>" >
										</div>
									</div>
									<div class="col-sm-6">
										<label for="whatsapp">WhatsApp Number</label>
										<input type="text" name="whatsapp" class="form-control" id="whatsapp" >
									</div>
								</div>
							</div>

							<div class="form-group">
								<div class="row">
									<div class="col-sm-6">
										<label for="facebook_account">Facebook Account</label>
										<div class="input-group">
											<span class="input-group-addon"><i class="fa fa-facebook fa-fw"></i></span>
											<input type="text" name="facebook_account" class="form-control" id="facebook_account" >
										</div>
									</div>
									<div class="col-sm-6">
										<label for="twitter_account">Twitter Account</label>
										<div class="input-group">
											<span class="input-group-addon"><i class="fa fa-twitter fa-fw"></i></span>
											<input type="text" name="twitter_account" class="form-control" id="twitter_account" >
										</div>
									</div>
								</div>
							</div>

							<div class="form-group">
								<div class="row">
									<div class="col-sm-6">
										<label for="instagram_account">Instagram Account</label>
										<div class="input-group">
											<span class="input-group-addon"><i class="fa fa-instagram fa-fw"></i></span>
											<input type="text" name="instagram_account" class="form-control" id="instagram_account" >
										</div>
									</div>
									<div class="col-sm-6">
									</div>
								</div>
							</div>

							<div class="row">
								<div class="col-sm-12">
									<div class="form-group">
										<div class="row">
											<div class="col-sm-8">
												<label for="club">Club/Group</label>
												<?php
													if ($profile['yps_login_id'] != "") {
												?>
												<input type="text" class="form-control" name="club_yps" value="Youth Photographic Society, Bengaluru" readonly>
												<?php
													}
													else {
												?>
												<input type="hidden" name="club_id" id="club_id" value="" >
												<select class="form-control text-uppercase" id="club_select" value="" >
													<option value="0|">Not a member of any Club</option>
												<?php
														foreach(user_get_club_list() AS $club_id => $club_row) {
												?>
													<option value="<?= $club_id;?>|<?= $club_row['club_contact'];?>" <?php echo ($club_id == $default_club_id) ? "selected" : "";?> ><?=$club_row['club_name'];?></option>
												<?php
														}
												?>
												</select>
												<?php
													}
												?>
												<div id="club-contact" class="text-danger small" style="padding-left: 15px;"></div>
											</div>
											<?php
												if ($profile['yps_login_id'] == "") {
											?>
											<!-- Option to Add Club -->
											<div class="col-sm-4">
												<div class="form-group" id="add-new-club" style="display: none;">
													<br>
													<div class="radio radio-inline" style="vertical-align:inherit">
														<label><input type="checkbox" name="add_club" id="add_club" value="1" > <b class="text text-info"> Add as a New Club</b></label>
													</div>
												</div>
											</div>
											<?php
												}
											?>
										</div>
									</div>
									<?php
										if ($profile['yps_login_id'] == "") {
									?>
									<!-- Club Form Hidden -->
									<div id="new_club_form" class="well" style="display: none;">
										<div class="row">
											<div class="form-group">
												<div class="col-sm-8">
													<label  for="new_club_name">NEW Club Name</label>
													<input type="text" name="new_club_name" class="form-control text-uppercase" id="new_club_name" readonly required >
												</div>
												<div class="col-sm-4">
													<label  for="club_type">Type</label>
													<select name="club_type" id="club_type" value="CLUB" class="form-control">
														<option value="CLUB">Member Club (Data Reported)</option>
														<option value="GROUP">Arbitrary Group (NOT Reported)</option>
													</select>
												</div>
											</div>
										</div>
										<div class="row" style="padding-top: 15px;">
											<div class="col-sm-8">
												<div class="form-group">
													<div class="row">
														<div class="col-sm-12">
															<label  for="new_club_website">Club Website</label>
															<input type="url" name="new_club_website" class="form-control" id="new_club_website" >
														</div>
													</div>
													<div class="row">
														<div class="col-sm-12">
															<label  for="new_club_address">Club Address</label>
															<textarea name="new_club_address" class="form-control" id="new_club_address" rows="4" required ></textarea>
														</div>
													</div>
												</div>
											</div>
											<div class="col-sm-4">
												<div class="row">
													<div class="col-sm-12">
														<img src="/res/club/club.jpg" id="club_logo_preview" style="max-height: 120px; max-width: 120px; border: 1px solid #CCC;">
													</div>
												</div>
												<div class="row">
													<div class="col-sm-12">
														<label for="new_club_logo">Upload Logo (JPEG) </label>
														<input id="new_club_logo" name="new_club_logo" type="file" onchange="loadAvatar(this, '#club_logo_preview');" >
													</div>
												</div>
											</div>
										</div>
									</div>
									<?php
										}	// Not a YPS Member
									?>
								</div>
							</div>

							<div class="form-group">
								<div class="row">
									<div class="col-sm-6">
										<label for="address">Address *</label>
										<input type="text" class="form-control text-uppercase" name="address_1" id="address_1" placeholder="Your Address" value="<?=$profile['address'];?>" >
										<input type="text" class="form-control text-uppercase" name="address_2" id="address_2" >
										<input type="text" class="form-control text-uppercase" name="address_3" id="address_3" >
<!--
										<p class="text text-muted">Please make a proper country selection. If country is 'India' payment will be accepted only in Indian Rupees. For all other countries,
											payment will be accepted only in US Dollars.
										</p>
-->
									</div>
									<div class="col-sm-6">
										<div class="form-group">
											<div class="row">
												<div class="col-sm-12">
													<label for="city">City *</label>
													<input type="text" name="city" class="form-control text-uppercase" id="city" value="<?=$profile['city'];?>" >
												</div>
											</div>
										</div>
										<div class="form-group">
											<div class="row">
												<div class="col-sm-8">
													<label for="email">State *</label>
													<input type="text" name="state" id="state" class="form-control text-uppercase" value="<?=$profile['state'];?>" >
												</div>
												<div class="col-sm-4">
													<label for="PIN/ZIP">PIN/ZIP *</label>
													<input type="text" name="pin" id="pin" class="form-control margin-bottom-xs text-uppercase" value="<?=$profile['pin'];?>" >
												</div>
											</div>
										</div>
										<div class="form-group">
											<div class="row">
												<div class="col-sm-12">
													<label for="country">Country *</label>
													<select class="form-control" name="country_id" id="country_id" value="101" required>
													<!--option value="">Select Country</option-->
												<?php
														$sql = "SELECT country_id, country_name FROM country ORDER BY country_name ASC";
														$rs = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
														while($row = mysqli_fetch_array($rs)) {
												?>
														<option value="<?php echo $row['country_id'];?>" <?php echo $row['country_id'] == 101 ? "selected" : "";?> >
															<?php echo $row['country_name'];?>
														</option>
												<?php
														}
												?>
													</select>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>

							<div class="form-group">
								<div class="row">
									<div class="col-sm-12">
										<label for="campaign_media">How did you learn about the YPS Salon ? (check all that apply)</label>
										<div class="row">
										<?php
											foreach($campaign_media_list as $cm_tag => $cm_name) {
										?>
											<div class="col-sm-3">
												<div class="radio radio-inline" style="vertical-align:inherit">
													<label><input type="checkbox" name="campaign_media[]" id="<?= $cm_tag;?>" value="<?= $cm_tag;?>" > <b class="text text-info"> <?= $cm_name;?></b></label>
												</div>
											</div>
										<?php
											}
										?>
										</div>
										<div class="row">
											<div class="col-sm-3">
												<div class="radio radio-inline" style="vertical-align:inherit">
														<label><input type="checkbox" name="campaign_media[]" id="cm_other" value="cm_other" > <b class="text text-info"> Other</b></label>
													</div>
											</div>
											<div class="col-sm-9">
												<input type="text" class="form-control" name="cm_other_text" id="cm_other_text" placeholder="Name of other source...">
											</div>
										</div>
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

                            <!-- Set Default Validation Method of Captcha Validation to PHP Captcha. Will be changed to google on load -->
                            <input type="hidden" name="captcha_method" id="captcha_method" value="php" />

                            <div class="form-group">
								<div class="row">
									<div class="col-sm-3"></div>
									<div class="col-lg-6 col-md-6 col-sm-6">
<!--										<div class="g-recaptcha" id="googleRepatcha" data-sitekey="6LfpiFQUAAAAALdj6b1ZSgKJjh7LQkxXo_Xs2KNu"></div>-->
										<div class="g-recaptcha" id="googleRecaptcha" stle="display: none;"></div>
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
									<div class="col-sm-3">
										<br>
										<button type="submit" class="btn btn-color pull-right" name="new_registration">Create Account</button>
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

	<script src="plugin/select2/js/select2.min.js"></script>	<!-- For selection club -->

	<script>
		$("#club_select").select2({
			tags: true,
		});

		// Select control to hide add new club if an existing club is selected
		$("#club_select").on("select2:select", function (e) {
			let id, contact;
			$("#new_club_form").hide();
			if (e.params.data.id == e.params.data.text) {
				// New tag
				if (e.params.data.text.match(/yps/i) || e.params.data.text.match(/youth *photo.*soc.*/i))
					$("#club-contact").html("Invalid club name. Cannot use names YPS or Youth Photographic Society");
				else {
					$("#club_id").val("new");	// store selection in hidden variable
					$("#new_club_name").val(e.params.data.text.toUpperCase());
					$("#club-contact").html("Club does not exist");
					$("#new_club_form").show();
				}
			}
			else {
				[id, contact] = e.params.data.id.split("|");
				$("#club_id").val(id);	// store selection in hidden variable
				if (id == 0)
					$("#club-contact").html("");
				else
					$("#club-contact").html("Club Contact: " + contact);
			}
		});

		$("#country_id").select2();
	</script>

<!-- JS Global -->
<style>
	.valid-error{
		font-size: 12px;
	}
</style>

<!-- Toggle New Club Form based on add_club checkbox -->
<script>
$(document).ready(function () {
	$("#add_club").click(function() {
		if ($("#add_club:checked").length > 0)
			$("#new_club_form").show();
		else
			$("#new_club_form").hide();
	});
});
</script>


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

<script>
    $(document).ready(function(){
		$('html, body').animate({ scrollTop: $("#myTab").offset().top-100 }, 500);
	});
</script>

<script>
	$("#country_id").change(function(){
		const country_id = $("#country_id").val();
		$.post("ajax/get_country_details.php", { "country_id" : country_id}, function(response){
			response = JSON.parse(response);
			if (response.success) {
				$("#dial_prefix").html(response.country.dial_prefix);
			}
		});
	});
</script>


<!-- Form Validation -->
<script src="plugin/validate/js/jquery.validate.min.js"></script>

<!-- Custom Validation Functions -->
<script src="custom/js/validate.js"></script>

<!-- Custom Validation for updateInfoForm -->
<script>
$(document).ready(function(){
	$('#signup-form').validate({
		rules:{
			new_password: {
				required: {
					param: true,
					depends: function () {return $("#yps_login_id").val() == "";}
				},
				minlength: {
					param: 8,
					depends: function () {return $("#yps_login_id").val() == "";}
				},
			},
			confirm_password: {
				required: {
					param: true,
					depends: function () {return $("#yps_login_id").val() == "";}
				},
				minlength: {
					param: 8,
					depends: function () {return $("#yps_login_id").val() == "";}
				},
				equalTo: {
					param: "#new_password",
					depends: function () {return $("#yps_login_id").val() == "";}
				},
			},
			first_name:{ required: true, nosplchars: true, },
			last_name:{ required:true, nosplchars: true, },
			gender: {salutation_match: '-',},
			avatar : {
				extension : "jpg,jpeg",
				filesizekb : 512,
			},
			date_of_birth:{
				required: true,
				date: true,
				date_range : ["<?= EARLIEST_DOB;?>", "<?= LATEST_DOB;?>"],
			},
			age_proof_file: {
				required: {
					param: true,
					depends: function() { return $("#age_proof").val() != "none"; },
				},
				extension : "pdf,jpg,jpeg",
				filesizekb : 512,
			},
			new_club_name : {
				required : true,
			},
			new_club_address : {
				required : true,
			},
			new_club_logo : {
				extension : "jpg,jpeg",
				filesizekb : 512,
			},
			address_1:{
				required:true,
				minlength: 11,
				maxlength: 75,
			},
			address_2 : {
				maxlength : 75,
			},
			address_3 : {
				maxlength : 75,
			},
			city:{ required:true, },
			pin:{
				required:true,
				minlength: 3,
				maxlength: 20,
			},
			state:{
				required:true,
			},
			country: {
				required: true,
			},
			phone:{
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
			new_password: {
				required: "Enter a password for your login"
			},
			confirm_password: {
				required: "Enter a password again for confirmation",
			},
			first_name:{
				required:'First Name is required',
				nosplchars: 'First name cannot contain special characters',
			},
			last_name:{
				required:'Last Name is required',
				nosplchars: 'Last name cannot contain special characters',
			},
			gender:{
				required:'Please select gender.',
			},
			date_of_birth:{
				required:'Please enter Date of Birth.',
				date: 'Please enter a valid date',
				date_range: 'Age must be between 8 and 120 years',
			},
			age_proof_file: {
				required: 'Must upload an Age Proof document in PDF or JPEG format',
			},
			new_club_name : {
				required : "Cannot leave Club Name blank",
			},
			new_club_address : {
				required : "Please provide the Club address",
			},
			address:{
				required:'Please enter Address.',
				minlength: "Address must be at least 11 characters long.",
			},
			city:{
				required:'Please enter city.',
			},
			pin:{
				required:'Please enter pincode.',
			},
			state:{
				required:'Please select state.',
			},
			phone:{
				required:'Please enter phoneno.',
				number: "Please enter valid phone number.",
				minlength: "Phone number must be at least 8 digit long including country code.",
				maxlength: "Phone number should not be greater than 15 digit long.",
			},
			verified: {
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
					url: "ajax/sign_up.php",
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
									title: "Details Saved",
									text: "Details entered have been saved successfully. You are now logged in and can proceed to next step.",
									icon: "success",
									confirmButtonClass: 'btn-success',
									confirmButtonText: 'Great'
							});
							var baseurl= '<?= http_method();?>'+window.location.host;
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

?>
