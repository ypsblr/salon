<?php
include_once("inc/session.php");
include_once("inc/categories.php");
include_once("inc/user_lib.php");

// Minimal Validations
if(empty($_SESSION['USER_ID']))
	handle_error("Must be logged in to use this feature.", __FILE__, __LINE__);

// Check for blacklist
if ($tr_user['blacklist_match'] != "" && $tr_user['blacklist_exception'] == 0)
	handle_error("Not permitted due to match with Blacklisted profile.", __FILE__, __LINE__);

// tr_user contains details of club as well as club_entry and values are set to blank if there are no existing details
// ------------------------------------
// Must be a member of the Club. Club can be created from Edit Profile
if ($tr_user['club_id'] == 0 || $tr_user['club_name'] == "")
	handle_error("You must be a member of a club to Enter the Club into a Salon. You can create a Club by using Edit Profile option.", __FILE__, __LINE__);

if ($tr_user['club_entered_by'] != "") {
	handle_error("Your club has already been registered for discount", __FILE__, __LINE__);
}

if ($tr_user['fee_group'] == "") {
	handle_error("You must register yourself for the Salon before registering the Club.", __FILE__, __LINE__);
}

// Entrant Category List using this user as a representative Club Member
$ec_list = ec_get_eligible_ec_list($contest_yearmonth, $tr_user);
if ($ec_list == false || sizeof($ec_list) == 0)
	handle_error("None of the Entrant Categories for this Salon match your profile. Please check the Eligibility Criteria under Salon Rules.", __FILE__, __LINE__);

// Set default field values
// ------------------------
$club_entered_by = $tr_user['profile_id'];
$currency = $tr_user['currency'];
$entrant_category = $tr_user['entrant_category'];
$fee_group = $tr_user['fee_group'];
$discount_group = $tr_user['discount_group'];

$fee_code_list = array();
$group_code = sprintf("GC_%03d", $tr_user['club_id']);
$payment_mode = "";
$minimum_group_size = "";

// Create fee_code List
// --------------------
$sql  = "SELECT DISTINCT fee_code, fee_start_date, fee_end_date FROM fee_structure ";
$sql .= "WHERE yearmonth = '$contest_yearmonth' ";
$sql .= "  AND fee_group = '$fee_group' ";
$sql .= "  AND fee_end_date >= '" . DATE_IN_SUBMISSION_TIMEZONE . "' ";
$fc = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
// debug_dump("SQL", $sql, __FILE__, __LINE__);
// There is nothing open to select and fee_code has not been previously selected
// Can't do anything further
if (mysqli_num_rows($fc) == 0)
	handle_error("None of the sections are open now for participation.", __FILE__, __LINE__);

?>

<!DOCTYPE html>
<html lang="en">

<head>
<?php include_once("inc/header.php"); ?>

<!-- Google reCaptcha -->
<!-- Blocking all Recaptcha from logged in forms

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

<link rel="stylesheet" href="plugin/datatable/css/dataTables.bootstrap.min.css" />

</head>

<body class="<?php echo THEME;?>">
	<?php include_once("inc/navbar.php") ;?>
    <div class="wrapper">
    <?php  include_once("inc/Slideshow.php") ;?>

		<div class="container">
			<div class="row">
				<div class="col-sm-3">
					<?php include("inc/user_sidemenu.php");?>
				</div>
				<div class="col-sm-9" id="myTab">
					<!-- Loading image made visible during processing -->
					<div id="loader_img" style="display:none;background: rgba(0, 0, 0, 0.5);z-index: 9999;">
						<img alt="Loader Image" title="Loading..." src="img/progress_bar.gif">
					</div>

					<div class="sign-form" id="myTab">
						<h3 class="first-child text text-color">Request Club/Group Discount</h3>
						<hr>
						<form role="form" method="post" action="#" id="group-form" name="group-form" enctype="multipart/form-data">
							<input type="hidden" name="yearmonth" value="<?=$contest_yearmonth;?>" >
							<input type="hidden" name="contest_name" value="<?=$contestName;?>" >
							<input type="hidden" name="profile_id" value="<?=$tr_user['profile_id'];?>" >
							<input type="hidden" name="profile_name" value="<?=$tr_user['profile_name'];?>" >
							<input type="hidden" name="club_id" value="<?=$tr_user['club_id'];?>" >
							<input type="hidden" name="entrant_category_name" id="entrant_category_name" value="<?=$tr_user['entrant_category_name'];?>" >
							<input type="hidden" name="currency" value="<?=$currency;?>" id="currency" >
							<input type="hidden" name="discount_code" value="CLUB" >

							<div class="form-group">
								<div class="row">
									<?php
										if ($tr_user['club_logo'] != "") {
									?>
									<div class="col-sm-1">
										<img src="/res/club/<?=$tr_user['club_logo'];?>" style="max-height:60px" >
									</div>
									<?php
										}
									?>
									<div class="col-sm-6">
										<label for="club_name">Club Name</label>
										<input type="text" name="club_name" class="form-control text-uppercase" id="club_name" readonly
												value="<?=$tr_user['club_name'];?>" >
									</div>
									<div class="col-sm-5">
										<label  for="club_contact">Club Contact Person</label>
										<input type="text" name="club_contact" class="form-control text-uppercase" id="club_contact" readonly
												value="<?=$tr_user['club_contact'];?>" >
									</div>
								</div>
							</div>

							<div class="form-group">
								<div class="row">
									<div class="col-sm-4">
										<label for="phone">My Phone Number</label>
										<input type="text" name="phone" class="form-control text-uppercase" id="phone" value="<?=$tr_user['phone'];?>" readonly >
									</div>
									<div class="col-sm-8">
										<label for="email">My Email</label>
										<input type="email" name="email" id="email" class="form-control" readonly
												value="<?=$tr_user['email'];?>"  >
									</div>
								</div>
							</div>

							<div class="form-group">
								<div class="row">
									<div class="col-sm-6">
										<label  for="entrant_category">Category of Participation *</label>
										<select name="entrant_category" value="<?=$tr_user['entrant_category'];?>" id="entrant_category" class="form-control" >
										<?php
											foreach($ec_list as $ec => $ec_row) {
										?>
											<!-- IMP: data-group-code-list value should be in single quotes to preserve double quoted JSON string -->
											<option value="<?=$ec_row['entrant_category'];?>" id="<?=$ec;?>"
													data-entrant-category-name="<?=$ec_row['entrant_category_name'];?>"
													<?php echo $entrant_category == $ec_row['entrant_category'] ? "selected" : "";?> >
												<?=$ec_row['entrant_category_name'];?>
											</option>
										<?php
											}
										?>
										</select>
									</div>
									<div class="col-sm-6">
										<label for="minimum_group_size">Minimum Promised Group Size *</label>
										<input type="number" name="minimum_group_size" class="form-control" id="minimum_group_size" required >
									</div>
								</div>
							</div>

							<div class="form-group">
								<div class="row">
									<div class="col-sm-2">
										<label for="payment_mode">Payment *</label>
									</div>
									<div class="col-sm-4">
										<label>
											<input type="radio" name="payment_mode" value="SELF_PAYMENT" id="payment_mode_self"
												<?php echo ($payment_mode == 'SELF_PAYMENT') ? 'checked' : '' ; ?>
												<?php // echo $allow_payment_mode_edit ? '' : 'readonly'; ?>
											>
											By Members Individually
										</label>
									</div>
									<div class="col-sm-6">
										<label>
											<input type="radio" name="payment_mode" value="GROUP_PAYMENT" id="payment_mode_group"
												<?php echo ($tr_user['club_payment_mode'] == 'GROUP_PAYMENT') ? 'checked' : '' ; ?>
												<?php // echo $allow_payment_mode_edit ? '' : 'readonly'; ?>
											>
											By me (as Group Co-ordinator) for all members
										</label>
									</div>
								</div>
							</div>
							<hr>
							<h5 class="text text-color">Confirmation</h5>
							<ul>
								<li>Our Club is keen on participating in this Salon.</li>
								<li>I represent the Club members as Group Coordinator for this Salon.</li>
								<li>We request YPS to offer us the best discount possible for the participation specified above.</li>
								<li>We have read and understood the Salon's terms of conditions, dates and normal fee structure.</li>
								<li>We understand that if the club participation falls short of the numbers promised above, YPS has the right
									to,
									<ol>
										<li>demand payment of non-discounted fee,</li>
										<li>and also withhold results till the difference is paid by all the participating members.</li>
									</ol>
								</li>
							</ul>

							<div class="form-group">
								<div class="row">
									<div class="col-sm-12">
										<div class="checkbox pull-right">
											<label>
												<input type="checkbox" name="verified" id="verified" value="1" required>
												<b>I have understood the conditions under which discount is offered and request YPS to extend the best discount possible. *</b>
											</label>
										</div>
										<br>
									</div>
								</div>
							</div>

							<hr>

                            <!-- Set Default Validation Method of Captcha Validation to PHP Captcha. Will be changed to google on load -->
                            <input type="hidden" name="captcha_method" id="captcha_method" value="php" />

                            <div class="form-group">
								<div class="row">
									<div class="col-sm-3"></div>
									<div class="col-lg-6 col-md-6 col-sm-6">
                                        <!-- <div class="g-recaptcha" data-sitekey="6LfpiFQUAAAAALdj6b1ZSgKJjh7LQkxXo_Xs2KNu"></div> -->
										<!-- Blocking Recaptcha
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
										<br>
										<button type="submit" class="btn btn-color pull-right" name="request_discounts" id="request_discounts">Request Discounts</button>
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
	</div>		<!-- / .wrapper -->

    <!-- Style Toggle -->

	<?php include_once("inc/settingToggle.php") ;?>
    <!-- JavaScript
    ================================================== -->
	<?php include_once("inc/scripts.php"); ?>

	<script src="plugin/datatable/js/jquery.dataTables.min.js"></script>
	<script src="plugin/datatable/js/dataTables.bootstrap.min.js"></script>

	<!-- Page specific scripts -->
	<!-- Initialize Tables -->
	<script>
    $(document).ready(function(){
		$('html, body').animate({ scrollTop: $("#myTab").offset().top-100 }, 500);
	});
    </script>


	<!-- Form Validation -->
	<script src="plugin/validate/js/jquery.validate.min.js"></script>

	<!-- Custom Validation Functions -->
	<script src="custom/js/validate.js"></script>

	<script>
		$("#entrant_category").change(function() {
			// Update Entrant Category Name
			const entrant_category_name = $("#entrant_category :selected").attr("data-entrant-category-name");
			$("#entrant_category_name").val(entrant_category_name);
		});
	</script>



	<!-- Form Validation -->
	<script>
	// Function to handle form submission
	function send_discount_request(form) {
		//form.submit();
		var formData = encryptFormData(new FormData(form));
		$('#loader_img').show();
		$.ajax({
				url: "ajax/send_discount_request.php",
				type: "POST",
				data: formData,
				cache: false,
				processData: false,
				contentType: false,
				success: function(response) {
					$('#loader_img').hide();
					response = JSON.parse(response);
					if(response.success){
						var baseurl= '<?= http_method();?>' + window.location.host;
						swal({
							title: "Request Sent",
							text: response.msg,
							icon: "success",
						})
						.then(function () { document.location.href = baseurl+'/user_panel.php'; });		// Save if Proceed button is pressed
					}
					else{
						swal("Send Failed!", response.msg, "error");
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
	}

	$(document).ready(function() {
		// Validator for Group Creation Form
		$('#group-form').validate({
			rules:{
				entrant_category : {
					required:true,
				},
				minimum_group_size : {
					required:true,
					number : true,
					min : 5,
				},
				payment_mode : {
					required : true,
				},
				verified : {
					required : true,
				},
			},
			messages:{
				entrant_category : {
					required : 'Chose a Participation Option applicable to all the members',
				},
				minimum_group_size : {
					required : 'Please specify the minimum promised group size',
					min : 'Minimum size for the group is 5',
				},
				payment_mode :  {
					required : 'Specify how the payment will be made',
				},
				verified : {
					required : 'Your confirmation of understanding of the conditions is required.',
				},
			},
			errorElement: "div",
			errorClass: "valid-error",
			submitHandler: function(form) {
				send_discount_request(form);
//				// Get minimum_group_size selected
//				var minimum_group_size = $("#minimum_group_size").val();
//				var existing_members = $("#existing_member_email_list").val().split(",");		// create an array of existing member emails
//				$(".new_member").each(function() {
//					existing_members.push($(this).val());										// Add recently added members to this list
//				});
//				var number_of_emails = existing_members.length;									// New Members added
//				var number_of_email_deletes = $(".delete_member").length;
//				if ((number_of_emails - number_of_email_deletes)  < minimum_group_size ) {
//					swal("Minimum emails needed!", 'Minimum ' + minimum_group_size + ' emails are required to be eligible for Club/Group Discount.', "warning");
//					$('html, body').animate({ scrollTop: $("#myTab").offset().top-150 }, 500);
//				}
//				else if ( (! $("#payment_mode_self").prop("checked")) && (! $("#payment_mode_group").prop("checked"))) {
//					swal("Payment Mode", "Select one of the Payment Modes", "warning");
//					$('html, body').animate({ scrollTop: $("#myTab").offset().top-150 }, 500);
//				}
//				else {
//					if (number_of_email_deletes > 0 && Number($("#total_payment_received").val()) > 0.0) {
//						swal({
//							title: "Confirm Deletions?",
//							text: "By deleting members, payments made by you may exceed fees payable. Refund of fees is not possible. Please confirm if you want to proceed!",
//							icon: "warning",
//							showCancelButton: true,
//							confirmButtonColor: '#00BAFF',
//							cancelButtonColor: '#696969',
//							confirmButtonText: 'Proceed',
//							dangerMode: true,
//						})
//						.then(function () { save_discounts(form); });		// Save if Proceed button is pressed
//					}
//					else {
//						save_discounts(form);
//					}
//				}
			},
		});
	});

	</script>

</body>

</html>
