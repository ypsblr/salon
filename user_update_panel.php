<?php
include_once("inc/session.php");
include_once("inc/categories.php");
include_once("inc/user_lib.php");

if(isset($_SESSION['USER_ID'])) {
	// Check profile details from server to determine any changes
	if ($tr_user['yps_login_id'] != "") {
		list($yps_user_errmsg, $yps_user) = yps_getuserbyemail($tr_user['yps_login_id']);
	}
?>
<!DOCTYPE html>
<html lang="en">

<head>
<?php include_once("inc/header.php"); ?>
<!-- Google reCaptcha -->
<!-- Blocking all Recaptcha in logged in forms

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

<!-- select2 for selecting or adding club -->
<link rel="stylesheet" href="plugin/select2/css/select2.min.css" />

</head>

<body class="<?php echo THEME;?>">
	<?php include_once("inc/navbar.php") ;?>
    <div class="wrapper">
    <?php  include_once("inc/Slideshow.php") ;?>
	<!-- Topic Header -->

	<div class="container">
        <div class="row">
			<div class="col-sm-3">
				<?php include("inc/user_sidemenu.php");?>
			</div>
			<div class="col-sm-9" id="myTab">

				<div id="hasResponse"></div>
				<div id="loader_img" style="display:none;background: rgba(0, 0, 0, 0.5);z-index: 9999;">
					<img alt="Loader Image" title="Loading..." src="img/progress_bar.gif">
				</div>

				<div class="sign-form" id="myTab">
					<h3 class="first-child text text-color">Edit My Profile</h3>
					<hr>
					<form role="form" method="post" action="#" id="updateinfoForm" name="updateinfoForm" enctype="multipart/form-data">
						<input type="hidden" name="contest_yearmonth" value="<?=$contest_yearmonth;?>" >
						<input type="hidden" name="profile_id" value="<?=$tr_user['profile_id'];?>" >
						<input type="hidden" name="yps_login_id" value="<?=$tr_user['yps_login_id'];?>" >
						<input type="hidden" name="cur_age_proof_file" value="<?=$tr_user['age_proof_file'];?>" >
						<input type="hidden" name ="cur_avatar" value="<?=$tr_user['avatar'];?>" >
						<?php
							if ($tr_user['yps_login_id'] != "" && isset($yps_user_errmsg) && $yps_user_errmsg == "") {
						?>
						<input type="hidden" name="yps_email" id="yps_email" value="<?= $yps_user['email'];?>" >
						<input type="hidden" name="yps_first_name" id="yps_first_name" value="<?= $yps_user['first_name'];?>" >
						<input type="hidden" name="yps_last_name" id="yps_last_name" value="<?= $yps_user['last_name'];?>" >
						<?php
							}
						?>

						<div class="row">
							<div class="col-sm-8">
								<div class="form-group">
									<label for="email">Email</label>
									<input type="email" name="email" id="email" class="form-control" value="<?php echo $tr_user['email'];?>" readonly >
								</div>
								<div class="form-group">
									<label for="salutation">Salutation *</label>
									<div class="radio radio-inline" style="vertical-align:inherit">
										<label><input type="radio" value="Mr." name="salutation" id="sal_mr" required <?php echo ($tr_user['salutation'] == "Mr.") ? "checked" : "";?>  >Mr.</label>
									</div>
									<div class="radio radio-inline"><label><input type="radio" value="Ms." name="salutation" id="sal_ms" <?php echo ($tr_user['salutation'] == "Ms.") ? "checked" : "";?>>Ms.</label></div>
									<div class="radio radio-inline"><label><input type="radio" value="Mrs." name="salutation" id="sal_mrs" <?php echo ($tr_user['salutation'] == "Mrs.") ? "checked" : "";?>>Mrs.</label></div>
									<div class="radio radio-inline"><label><input type="radio" value="Dr." name="salutation" id="sal_dr" <?php echo ($tr_user['salutation'] == "Dr.") ? "checked" : "";?>>Dr.</label></div>
								</div>
								<div class="form-group">
									<div class="row">
										<div class="col-sm-6">
											<label  for="first_name">First Name *</label>
											<input type="text" name="first_name" class="form-control" id="first_name"
													value="<?php echo $tr_user['first_name'];?>" <?php echo ($tr_user['yps_login_id'] != "") ? "readonly" : "";?> >
										</div>
										<div class="col-sm-6">
											<label for="last_name">Last Name *</label>
											<input type="text" name="last_name" class="form-control" id="last_name"
													value="<?php echo $tr_user['last_name'];?>" <?php echo ($tr_user['yps_login_id'] != "") ? "readonly" : "";?> >
										</div>
									</div>
								</div>
								<div class="form-group">
									<div class="row">
										<div class="col-sm-6">
											<label  for="gender">Gender *</label>
											<select class="form-control" name="gender" id="gender">
												<option value="MALE" <?php echo ($tr_user['gender'] == 'MALE') ? "selected" : ""; ?> >Male</option>
												<option value="FEMALE" <?php echo ($tr_user['gender'] == 'FEMALE') ? "selected" : ""; ?> >Female</option>
												<option value="OTHER" <?php echo ($tr_user['gender'] == 'OTHER') ? "selected" : ""; ?> >Other</option>
											</select>
										</div>
										<div class="col-sm-6">
											<label for="date_of_birth">Date of Birth *</label>
											<input type="date" name="date_of_birth" class="form-control" id="date_of_birth" value="<?php echo $tr_user['date_of_birth'];?>" >
										</div>
									</div>
								</div>
							</div>
							<div class="col-sm-4">
								<div class="row">
									<div class="col-sm-12">
										<img src="/res/avatar/<?php echo $tr_user['avatar'];?>" id="avatar_preview" style="height: 120px; border: 1px solid #CCC;">
									</div>
								</div>
								<div class="row">
									<div class="col-sm-12">
										<label for="avatar">Upload Avatar (JPEG) </label>
										<input id="avatar" name="avatar" type="file" onchange="loadAvatar(this, '#avatar_preview');" >
										<p class="text-color"><small>Max size 512 KB</small></p>
									</div>
								</div>
							</div>
						</div>

						<!-- Hidden row to be exposed in Javascript depending on the date of birth entered -->
						<div class="row">
							<div class="col-sm-12">
								<p class="text text-color" style="margin-left: 12px;">
									NOTE: Age Proof is required only if you plan to avail any concessions offered by YPS Salons based on age. You are required to upload
									a valid document (PDF/JPEG) issued by Government or by School/College that contains your name and date-of-birth.
								</p>
								<div class="form-group">
									<div class="row">
										<div class="col-sm-6">
											<label for="age_proof">Age Proof</label>
											<select class="form-control" name="age_proof" id="age_proof" value="<?=$tr_user['age_proof'];?>" >
												<option value="none" <?php echo ($tr_user['age_proof'] == "none") ? "selected" : "";?> >None</option>
												<option value="birth_certificate" <?php echo ($tr_user['age_proof'] == "birth_certificate") ? "selected" : "";?> >Birth Certificate</option>
												<option value="passport" <?php echo ($tr_user['age_proof'] == "passport") ? "selected" : "";?> >Passport Front Page</option>
												<option value="Aadhar" <?php echo ($tr_user['age_proof'] == "Aadhar") ? "selected" : "";?> >Aadhar</option>
												<option value="school_record" <?php echo ($tr_user['age_proof'] == "school_record") ? "selected" : "";?> >School Record with DoB</option>
												<option value="id_card" <?php echo ($tr_user['age_proof'] == "id_card") ? "selected" : "";?> >ID Card with DoB</option>
												<option value="others" <?php echo ($tr_user['age_proof'] == "others") ? "selected" : "";?> >Others</option>
											</select>
										</div>
										<div class="col-sm-6">
											<label for="age_proof_file">Upload Age Proof</label>
											<input id="age_proof_file" name="age_proof_file" type="file" >
										</div>
									</div>
								</div>
							</div>
						</div>

						<div class="row">
							<div class="col-sm-12">
								<div class="form-group">
									<div class="row">
										<div class="col-sm-12">
											<label for="honors">Photographic Honors</label>
											<label for="honors"><small><span style="font-weight:normal;"><i>  (Leave it blank if you are yet to achieve any honors)</i></span></small></label>
											<input type="text" name="honors" class="form-control text-uppercase" id="honors" value="<?=$tr_user['honors'];?>" >
										</div>
									</div>
								</div>
							</div>
						</div>

						<div class="row">
							<div class="col-sm-12">
								<div class="form-group">
									<div class="row">
										<div class="col-sm-6">
											<label for="phone">Phone Number *</label>
											<div class="input-group">
												<span class="input-group-btn"><button class="btn btn-default"><span id="dial_prefix"><?=$tr_user['dial_prefix'];?></span></button></span>
												<input type="text" name="phone" class="form-control text-uppercase" id="phone" value="<?=$tr_user['phone'];?>" >
											</div>
										</div>
										<div class="col-sm-6">
											<label for="whatsapp">WhatsApp Number</label>
											<input type="text" name="whatsapp" class="form-control text-uppercase" id="whatsapp" value="<?=$tr_user['whatsapp'];?>" >
										</div>
									</div>
								</div>
							</div>
						</div>

						<div class="form-group">
							<div class="row">
								<div class="col-sm-6">
									<label for="facebook_account">Facebook Account</label>
									<div class="input-group">
										<span class="input-group-addon"><i class="fa fa-facebook fa-fw"></i></span>
										<input type="text" name="facebook_account" class="form-control" id="facebook_account" value="<?= $tr_user['facebook_account'];?>" >
									</div>
								</div>
								<div class="col-sm-6">
									<label for="twitter_account">Twitter Account</label>
									<div class="input-group">
										<span class="input-group-addon"><i class="fa fa-twitter fa-fw"></i></span>
										<input type="text" name="twitter_account" class="form-control" id="twitter_account" value="<?= $tr_user['twitter_account'];?>" >
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
										<input type="text" name="instagram_account" class="form-control" id="instagram_account" value="<?= $tr_user['instagram_account'];?>" >
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
												if ($tr_user['yps_login_id'] != "") {
											?>
											<input type="text" class="form-control" name="club_yps" value="Youth Photographic Society, Bengaluru" readonly>
											<?php
												}
												else {
											?>
											<input type="hidden" name="club_id" id="club_id" value="<?=$tr_user['club_id'];?>" >
											<select name="club_select" class="form-control" id="club_select" value="<?=$tr_user['club_id'];?>"
												 	<?= $tr_user['coupon_coupon_text'] == "" ? "" : "disabled";?> >
												<option value="0">Not a member of any Club</option>
											<?php
													foreach(user_get_club_list() AS $club_id => $club_row) {
											?>
												<option value="<?=$club_id;?>" <?= ($club_id == $tr_user['club_id']) ? "selected" : "";?> ><?=$club_row['club_name'];?></option>
											<?php
													}
											?>
											</select>
											<?php
												}
											?>
										</div>
										<?php
											if ($tr_user['yps_login_id'] == "") {
												// Cannot add a club when you are already a member of a club through which a discount coupon has been issed
												if (empty($tr_user['coupon_coupon_text'])) {
										?>
										<!-- Option to Add Club -->
										<div class="col-sm-4">
											<div class="form-group" id="add-new-club" style="display: none;" >
												<br>
												<div class="radio radio-inline" style="vertical-align:inherit">
													<label><input type="checkbox" name="add_club" id="add_club" value="1" > <b class="text text-info"> Add a New Club</b></label>
												</div>
											</div>
										</div>
										<?php
												}
											}
										?>
									</div>
								</div>
								<?php
									if ($tr_user['yps_login_id'] == "") {
										if ($tr_user['coupon_coupon_text'] == "" || $tr_user['club_entered_by'] == $tr_user['profile_id']) {
								?>
								<!-- Club Form Hidden -->
								<div id="club_form" class="well" style="display: <?= ($tr_user['club_id'] == 0) ? 'none' : 'block';?> ">
									<div class="row">
										<div class="form-group">
											<div class="col-sm-8">
												<label  for="club_name">Club Name</label>
												<!--<input type="text" name="club_name" class="form-control text-uppercase" id="club_name" value="<?= $tr_user['club_name'];?>" required >-->
												<input type="text" name="club_name" class="form-control text-uppercase" id="club_name" value="<?= $tr_user['club_name'];?>" required readonly>
											</div>
											<!--<div class="col-sm-4">
												<label  for="club_type">Type</label>
												<select name="club_type" id="club_type" value="CLUB" class="form-control">
													<option value="CLUB">Member Club (Data Reported)</option>
													<option value="GROUP">Arbitrary Group (NOT Reported)</option>
												</select>
											</div>-->
										</div>
									</div>
									<div class="row">
										<div class="form-group">
											<div class="col-sm-8">
												<label  for="new_club_name">Club Contact</label>
												<input type="text" name="club_contact" id="club_contact" class="form-control text-uppercase" value="<?= $tr_user['club_contact'];?>" readonly >
											</div>
											<div class="col-sm-4">
												<div class="form-group">
													<br>
													<!--<div class="radio radio-inline" style="vertical-align:inherit">
														<label>
															<input type="checkbox" name="make_me_club_contact" id="make_me_club_contact" value="1"
																	<?= ($tr_user['club_contact'] == "") ? "checked" : "";?> >
															<b class="text text-info"> Make me the Club Contact</b>
														 </label>
													</div>-->
												</div>
											</div>
										</div>
									</div>
									<div class="row" style="padding-top: 15px;">
										<div class="col-sm-8">
											<div class="form-group">
												<div class="row">
													<div class="col-sm-12">
														<label  for="club_website">Club Website</label>
														<!--<input type="url" name="club_website" class="form-control" id="club_website" value="<?= $tr_user['club_website'];?>" >-->
														<input type="url" name="club_website" class="form-control" id="club_website" value="<?= $tr_user['club_website'];?>" readonly >
													</div>
												</div>
												<div class="row">
													<div class="col-sm-12">
														<label  for="club_address">Address</label>
														<!--<textarea name="club_address" class="form-control" id="club_address" rows="4" ><?= $tr_user['club_address'];?></textarea>-->
														<textarea name="club_address" class="form-control" id="club_address" rows="4" readonly><?= $tr_user['club_address'];?></textarea>
														
													</div>
												</div>
											</div>
										</div>
										<div class="col-sm-4">
											<div class="row">
												<div class="col-sm-12">
													<img src="<?= ($tr_user['club_logo'] == '') ? '/res/club/club.jpg' : '/res/club/' . $tr_user['club_logo'];?>"
															id="club_logo_preview" style="max-height: 120px; max-width: 120px; border: 1px solid #CCC;" >
												</div>
											</div>
											<div class="row">
												<div class="col-sm-12">
													<label for="new_club_logo">Upload Logo (JPEG) </label>
													<input id="club_logo" name="club_logo" type="file" onchange="loadAvatar(this, '#club_logo_preview');" >
												</div>
											</div>
										</div>
									</div>
								</div>
								<?php
										}
									}	// Not a YPS Member
								?>
							</div>
						</div>

						<div class="form-group">
							<div class="row">
								<div class="col-sm-6">
									<label for="address">Address *</label>
									<input type="text" class="form-control text-uppercase" name="address_1" id="address_1" placeholder="Your Address" value="<?=$tr_user['address_1'];?>" >
									<input type="text" class="form-control text-uppercase" name="address_2" id="address_2" value="<?=$tr_user['address_2'];?>" >
									<input type="text" class="form-control text-uppercase" name="address_3" id="address_3" value="<?=$tr_user['address_3'];?>" >
									<!-- <p class="text text-muted">Please make a proper country selection. If country is 'India' payment will be accepted only in Indian Rupees. For all other countries,
										payment will be accepted only in US Dollars.
									</p> -->
								</div>
								<div class="col-sm-6">
									<div class="form-group">
										<div class="row">
											<div class="col-sm-12">
												<label for="city">City *</label>
												<input type="text" name="city" class="form-control text-uppercase" id="city" value="<?php echo $tr_user['city'];?>">
											</div>
										</div>
									</div>
									<div class="form-group">
										<div class="row">
											<div class="col-sm-8">
												<label for="state">State *</label>
												<input type="text" name="state" id="state" class="form-control text-uppercase" value="<?php echo $tr_user['state'];?>" >
											</div>
											<div class="col-sm-4">
												<label for="PIN/ZIP">PIN/ZIP *</label>
												<input type="text" name="pin" id="pin" class="form-control margin-bottom-xs text-uppercase" value="<?php echo $tr_user['pin'];?>" >
											</div>
										</div>
									</div>
									<div class="form-group">
										<div class="row">
											<div class="col-sm-12">
												<label for="country">Country *</label>
											<?php
												//if ($tr_user['payment_received'] == 0) {
											?>
												<select class="form-control" name="country_id" id="country_id" required>
												<!--option value="">Select Country</option-->
											<?php
													$sql = "SELECT country_id, country_name FROM country ORDER BY country_name ASC";
													$rs = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
													while($row = mysqli_fetch_array($rs)) {
											?>
													<option value="<?php echo $row['country_id'];?>" <?php echo ($tr_user['country_id'] == $row['country_id']) ? 'selected' : '' ?> >
														<?php echo $row['country_name'];?>
													</option>
											<?php
													}
											?>
												</select>
											<?php
												//}
												//else {
													// Do not allow country to be changed after payment has been made successfully
													// This is done by creating a disabled select field and a hidden field to the same name holding country id
													// $sql = "SELECT country_id, country_name FROM country WHERE country_id = '" . $tr_user['country_id'] . "' ";
													// $rs = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
													// $row = mysqli_fetch_array($rs);
											?>
<!--
														<input type="hidden" name="country_id" value="<?php //echo $tr_user['country_id'];?>" >
														<select class="form-control" name="country_id" id="country_id" disabled>
															<option value="<?php //echo $tr_user['country_id'];?>" selected><?php //echo $row['country_name'];?></option>
														</select>
-->
											<?php
												// }
											?>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>


						<?php
							// Check if the profile has won any cash awards
							$sql  = "SELECT * FROM pic_result, award ";
							$sql .= " WHERE pic_result.yearmonth = '$contest_yearmonth' ";
							$sql .= "   AND pic_result.profile_id = '" . $tr_user['profile_id'] . "' ";
							$sql .= "   AND award.yearmonth = pic_result.yearmonth ";
							$sql .= "   AND award.award_id = pic_result.award_id ";
							$sql .= "   AND award.cash_award > 0 ";
							$cashq = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
							$won_cash_awards = (mysqli_num_rows($cashq) > 0);

							// No Picture Awards ? Check Entry Awards
							if (! $won_cash_awards) {
								$sql  = "SELECT * FROM entry_result, award ";
								$sql .= " WHERE entry_result.yearmonth = '$contest_yearmonth' ";
								$sql .= "   AND entry_result.profile_id = '" . $tr_user['profile_id'] . "' ";
								$sql .= "   AND award.yearmonth = entry_result.yearmonth ";
								$sql .= "   AND award.award_id = entry_result.award_id ";
								$sql .= "   AND award.cash_award > 0 ";
								$cashq = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
								$won_cash_awards = (mysqli_num_rows($cashq) > 0);
							}

							if ($resultsReady && $won_cash_awards) {
						?>
						<input type="hidden" id="require_bank_details" name="require_bank_details" value="Yes" >
						<div class="form-group">
							<div class="row">
								<div class="col-sm-6">
									<label for="bank_account_number">Bank Account Number *</label>
									<input type="text" name="bank_account_number" class="form-control" id="bank_account_number" value="<?php echo $tr_user['bank_account_number'];?>" >
								</div>
								<div class="col-sm-6">
									<label for="verify_bank_account_number">Verify Bank Account Number *</label>
									<input type="verify_bank_account_number" name="verify_bank_account_number" id="verify_bank_account_number" class="form-control" value="<?php echo $tr_user['bank_account_number'];?>" >
								</div>
							</div>
						</div>
						<div class="form-group">
							<div class="row">
								<div class="col-sm-9">
									<label for="bank_account_name">Bank Account in the Name of *</label>
									<input type="text" name="bank_account_name" class="form-control text-uppercase" id="bank_account_name"
											value="<?php echo ($tr_user['bank_account_name'] != "") ? $tr_user['bank_account_name'] : $tr_user['profile_name'];?>" >
								</div>
								<div class="col-sm-3">
									<label for="bank_account_type">Account Type *</label>
									<select class="form-control" name="bank_account_type" id="bank_account_type">
										<option value="SAVINGS" <?php echo ($tr_user['bank_account_type'] == 'SAVINGS') ? "selected" : ""; ?> >Savings Account</option>
										<option value="CURRENT" <?php echo ($tr_user['bank_account_type'] == 'CURRENT') ? "selected" : ""; ?> >Current Account</option>
										<option value="NRO" <?php echo ($tr_user['bank_account_type'] == 'NRO') ? "selected" : ""; ?> >Savings NRO</option>
									</select>
								</div>
							</div>
						</div>
						<div class="form-group">
							<div class="row">
								<div class="col-sm-3">
									<label for="bank_ifsc_code">Branch IFSC Code *</label>
									<input type="text" name="bank_ifsc_code" class="form-control text-uppercase" id="bank_ifsc_code" value="<?php echo $tr_user['bank_ifsc_code'];?>" >
								</div>
								<div class="col-sm-2">
									<br>
									<button class="btn btn-color" name="find_ifsc" id="find_ifsc">Find</button>
								</div>
								<div class="col-sm-7">
									<label for="bank_name">Bank Name *</label>
									<input type="text" name="bank_name" class="form-control" id="bank_name" value="<?php echo $tr_user['bank_name'];?>" >
								</div>
							</div>
						</div>
						<div class="form-group">
							<div class="row">
								<div class="col-sm-12">
									<label for="bank_branch">Branch Name and Address *</label>
									<input type="text" name="bank_branch" class="form-control text-uppercase" id="bank_branch" value="<?php echo $tr_user['bank_branch'];?>" >
								</div>
							</div>
						</div>
						<?php
							}
						?>
						<div class="form-group">
							<div class="row">
								<div class="col-sm-12">
									<div class="checkbox pull-right">
										<label>
											<input type="checkbox" name="verified" id="verified" required>
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
									<!-- <div class="g-recaptcha" data-sitekey="6LfpiFQUAAAAALdj6b1ZSgKJjh7LQkxXo_Xs2KNu"></div> -->
									<!--
                                    <div class="g-recaptcha" id="googleRecaptcha" stle="display: none;"></div>
                                    <div id="phpCaptcha" class="row">
                                        <div class="col-sm-4">
                                            <label for="email">Validation code:</label><br>
                                            <img src="inc/captcha/captcha.php?rand=<?php //echo rand();?>" id='captchaimg'>
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
									<button type="submit" class="btn btn-color pull-right" name="update_info">Update Profile</button>
								</div>
							</div>
						</div>

					</form>

                </div>
            </div>
        </div> <!-- / .row -->
		<!-- Footer -->
		<?php include_once("inc/footer.php");?>
      </div> <!-- / .container -->

    </div>

    <!-- Style Toggle -->

	<?php include_once("inc/settingToggle.php") ;?>
    <!-- JavaScript
    ================================================== -->
	<?php include_once("inc/scripts.php"); ?>

	<!-- Form specific scripts -->

	<script src="plugin/select2/js/select2.min.js"></script>	<!-- For selection club -->

	<script>
		$("#club_select").select2({
			tags: true,
		});

		// Select control to hide add new club if an existing club is selected
		$("#club_select").on("select2:select", function (e) {
			if (e.params.data.id == e.params.data.text) {
				// New tag
				if (e.params.data.text.match(/yps/i) || e.params.data.text.match(/youth *photo.*soc.*/i))
					$("#club-contact").html("Invalid club name. Cannot use names YPS or Youth Photographic Society");
				else {
					$("#club_id").val("new");	// store selection in hidden variable
					$("#club_name").val(e.params.data.text.toUpperCase());
					$("#club_contact").val('<?= $tr_user['profile_name'];?>');
					$("#make_me_club_contact").prop("checked", true);
					$("#club_type").val("CLUB");
					$("#club_website").val("");
					$("#club_address").val("");
					$("#club_logo_preview").attr("src", "/res/club/club.jpg");
					$("#club_form").show();
				}
			}
			else {
				let club_id = e.params.data.id;
				$("#club_id").val(club_id);	// store selection in hidden variable
				if (club_id == 0) {
					$("#club_name").val("");
					$("#club_contact").val("");
					$("#make_me_club_contact").prop("checked", false);
					$("#club_type").val("");
					$("#club_website").val("");
					$("#club_address").val("");
					$("#club_logo_preview").attr("src", "/res/club/club.jpg");
					$("#club_form").hide();
				}
				else {
					$.post("ajax/get_club_details.php", {club_id}, function (response) {
						let data = JSON.parse(response);
						if (data.success) {
							$("#club_name").val(data.club.club_name);
							$("#club_contact").val(data.club.club_contact);
							if (data.club.club_contact == "")
								$("#make_me_club_contact").prop("checked", true);
							else
								$("#make_me_club_contact").prop("checked", false);
							$("#club_type").val(data.club.club_type);
							$("#club_website").val(data.club.club_website);
							$("#club_address").val(data.club.club_address);
							$("#club_logo_preview").attr("src", "/res/club/" + data.club.club_logo);
							$("#club_form").show();
						}
					});
				}
			}
		});

		$("#country_id").select2();
	</script>



<!-- Helper Function to search for IFSC Code -->
<script>
	$("#find_ifsc").click(function(e) {
		e.preventDefault();
		var ifsc_code = $("#bank_ifsc_code").val().toUpperCase();

		$.ajax({
			url: "https://ifsc.razorpay.com/" + ifsc_code,
			type: "GET",
			cache: false,
			processData: false,
			contentType: false,
			success: function(ifsc) {
						if (ifsc != null && typeof ifsc == "object") {
							if (ifsc.BANK && ifsc.BRANCH && ifsc.ADDRESS) {
								$("#bank_name").val(ifsc.BANK);
								$("#bank_branch").val(ifsc.BRANCH + ", " + ifsc.ADDRESS);
							}
						}
						else {
							swal("IFSC Incorrect!", "IFSC Code entered is not found. Try again with correct value!", "error");
						}
					}
		});

		// Not working. Retired on 28/07/2021
		// $.ajax({
		// 	url: "http://api.techm.co.in/api/v1/ifsc/" + ifsc_code,
		// 	type: "GET",
		// 	cache: false,
		// 	processData: false,
		// 	contentType: false,
		// 	success: function(response) {
		// 				// response = JSON.parse(response);
		// 				if (response.status == "success") {
		// 					$("#bank_name").val(response.data.BANK);
		// 					$("#bank_branch").val(response.data.BRANCH + ", " + response.data.ADDRESS);
		// 				}
		// 			}
		// });
	});
</script>

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

<!-- Force Update of email and first name and last name from yps records -->
<!--<script>-->
<!--	$(document).ready(function() {-->
<!--		if ($("#yps_email").length > 0) {-->
			<!--// YPS retrieved values-->
<!--			let yps_email = $("#yps_email").val();-->
<!--			let yps_first_name = $("#yps_first_name").val();-->
<!--			let yps_last_name = $("#yps_last_name").val();-->
			<!--// Profile values-->
<!--			let salon_email = $("#email").val();-->
<!--			let salon_first_name = $("#first_name").val();-->
<!--			let salon_last_name = $("#last_name").val();-->
			<!--// Get confirmation and update the form-->
<!--			let yps_profile_changed = false;-->
<!--			let change_text = "Your YPS Profile has been updated. ";-->
<!--			if (salon_email != yps_email) {-->
<!--				yps_profile_changed = true;-->
<!--				change_text += "Email has been updated to '" + yps_email + "'. ";-->
<!--			}-->
<!--			if (salon_first_name != yps_first_name) {-->
<!--				yps_profile_changed = true;-->
<!--				change_text += "First Name has changed to '" + yps_first_name + "'. ";-->
<!--			}-->
<!--			if (salon_last_name != yps_last_name) {-->
<!--				yps_profile_changed = true;-->
<!--				change_text += "Last Name has changed to '" + yps_last_name + "'. ";-->
<!--			}-->
<!--			change_text += " you want to update your profile with these details? ";-->
<!--			if (yps_profile_changed) {-->
<!--				swal({-->
<!--					title: 'Sync YPS Profile',-->
<!--					text:  change_text,-->
<!--					icon: "warning",-->
<!--					showCancelButton: true,-->
<!--					confirmButtonColor: '#00BAFF',-->
<!--					cancelButtonColor: '#696969',-->
<!--					confirmButtonText: 'Update'-->
<!--				})-->
<!--				.then(function() {-->
<!--					$("#email").val(yps_email);-->
<!--					$("#first_name").val(yps_first_name);-->
<!--					$("#last_name").val(yps_last_name);-->
<!--				});-->

<!--			}-->
<!--		}-->
<!--	});-->
<!--</script>-->


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
$(document).ready(function() {
	// Validator for updateinfoForm
	$('#updateinfoForm').validate({
		rules:{
			first_name:{ required:true, english_name: true,  },
			last_name:{ required:true, english_name: true, },
			gender: {salutation_match: '-',},
			avatar : {
				extension: "jpg,jpeg",
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
					depends: function() { return $("#age_proof").val() != "none" && $("#cur_age_proof_file").val() == "" ; },
				},
				extension : "pdf,jpg,jpeg",
				filesizekb : 512,
			},
			club_name : {
				required: true,
				club_name: '-',
				remote: {
					param: {
						url: "ajax/validate_club_name.php",
						type: "post",
						data: {
							club_name: function() { return $("#club_name").val(); },
							club_id : function() { return $("#club_id").val(); },
						}
					},
					// depends: true, // function () { return $("#club_id").val() == "new"; },
				}
			},
			club_address : {required: true, minlength: 11},
			club_logo : {
				extension: "jpg,jpeg",
				filesizekb : 512,
			},
			address_1:{
				required:true,
				english_address: true,
				minlength: 11,
				maxlength : 75,
			},
			address_2 : {
				english_address: true,
				maxlength : 75,
			},
			address_3 : {
				english_address: true,
				maxlength : 75,
			},
			city:{ required:true, english_address: true, },
			pin:{
				required:true,
				english_address: true,
				minlength: 3,
				maxlength: 20,
			},
			state:{
				required: true,
				english_address: true,
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
			bank_account_number: {
				required: {
					param: true,
					depends: function() { return $("#require_bank_details").val() == "Yes"; },
				},
				minlength: 6,
			},
			verify_bank_account_number: {
				required: {
					param: true,
					depends: function() { return $("#require_bank_details").val() == "Yes"; },
				},
				equalTo: "#bank_account_number",
			},
			bank_account_name: {
				required: {
					param: true,
					depends: function() { return $("#require_bank_details").val() == "Yes"; },
				},
			},
			bank_ifsc_code: {
				required: {
					param: true,
					depends: function() { return $("#require_bank_details").val() == "Yes"; },
				},
				minlength: 11,
				maxlength: 11,
			},
			bank_name: {
				required: {
					param: true,
					depends: function() { return $("#require_bank_details").val() == "Yes"; },
				},
			},
			bank_branch: {
				required: {
					param: true,
					depends: function() { return $("#require_bank_details").val() == "Yes"; },
				},
			},
		},
		messages:{
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
			},
			age_proof_file: {
				required: 'Must upload an Age Proof document in PDF or JPEG format',
			},
			club_name : {
				required: 'Club Name cannot be empty',
				remote: 'Clubs with similar names exist !',
			},
			address_1:{
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
			country: {
				required: "Please select a country",
			},
			phone:{
				required:'Please enter Phone No.',
				number: "Please enter valid phone number.",
				minlength: "Phone number must be at least 8 digit long including Country code.",
				maxlength: "Phone number should not be greater than 15 digit long.",
			},
			bank_account_number: {
				required: "Please Enter Bank Account Number",
			},
			verify_bank_account_number: {
				equalTo: "Bank Account Numbers do not match",
			},
			bank_account_name: {
				required: "Name of Bank Account is required",
			},
			bank_ifsc_code: {
				required: "Enter IFSC Code and Click on Find to get Bank details",
				minlength: "IFSC Code must be 11 characters long.",
				maxlength: "IFSC Code must be 11 characters long.",
			},
			bank_name: {
				required: "Enter Bank Name",
			},
			bank_branch: {
				required: "Enter Bank Branch Name and Address",
			},
			captcha_code:{
				required:'',
				remote: "Invaild Captcha Code..",
			},
		},
		errorElement: "div",
		errorClass: "valid-error",
		submitHandler: function(form) {
				//form.submit();
				var formData = encryptFormData(new FormData(form));
				$('#loader_img').show();
				$.ajax({
						url: "ajax/update_info.php",
						type: "POST",
						data: formData,
						cache: false,
						processData: false,
						contentType: false,
						success: function(response) {
							$('#loader_img').hide();
							response = JSON.parse(response);
							if(response.success){
//								$('#hasResponse').html('<div class="alert alert-success"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>'+response.msg+'</div>');
								var baseurl= '<?= http_method();?>' + window.location.host;
								swal({
										title: "Changes Saved",
										text: "Changes have been saved successfully",
										icon: "success",
										confirmButtonClass: 'btn-success',
										confirmButtonText: 'Great'
								}).then( function(){ document.location.href = baseurl+'/user_panel.php'; } );
							}
							else{
								swal({
										title: "Save Failed",
										text: "Something went wrong. Changes could not be saved: " + response.msg,
										icon: "warning",
										confirmButtonClass: 'btn-warning',
										confirmButtonText: 'OK'
								});
//								$('#hasResponse').html('<div class="alert alert-warning"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>'+response.msg+'</div>');
//								$('html, body').animate({ scrollTop: $("#myTab").offset().top-150 }, 500);
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
} else {
	$_SESSION['err_msg'] = "Login and complete the operation !";
	header('Location: index.php');
	printf("<script>location.href='index.php'</script>");
}

?>
