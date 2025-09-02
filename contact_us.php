<?php
include_once("inc/session.php");
include_once("inc/lib.php");
?>
<!DOCTYPE html>
<html lang="en">

<head>
<?php include_once("inc/header.php"); ?>
<!-- Google reCaptcha -->
<script src='https://www.google.com/recaptcha/api.js'></script>

<script type='text/javascript'>
function refreshCaptcha(){
	var img = document.images['captchaimg'];
	img.src = img.src.substring(0,img.src.lastIndexOf("?"))+"?rand="+Math.random()*1000;
}
</script>

</head>

<body class="<?php echo THEME;?>">

    <?php include_once("inc/navbar.php") ;?>
    <!-- Wrapper -->
    <div class="wrapper">

		<!-- Topic Header -->
		<?php  include_once("inc/Slideshow.php") ;?>
		<!-- Slideshow -->

		<div class="container">
			<div class="row">
				<div class="col-sm-8 contact-us-p" id="myTab">
					<h2 class="headline first-child text-color">
						<span class="border-color">Contact Us</span>
					</h2>
					<div id="loader_img" style="display:none;background: rgba(0, 0, 0, 0.5);z-index: 9999;">
						<img alt="Loader Image" title="Loading..." src="img/progress_bar.gif">
					</div>

					<form role="form"  method="post" action="#" name="contact-form" id="contact-form">

						<div class="form-group">
							<label for="full_name">Your name</label>
							<input type="text" name="full_name" class="form-control" id="full_name" placeholder="FirstName LastName..."
									data-toggle="popover" data-placement="bottom" data-trigger="focus" data-content="Enter your Full Name, that is FirstName SecondName" data-original-title="Full Name" required>
							<span class="help-block"></span>
						</div>

						<div class="form-group">
							<label for="yor_email">Your email address</label>
							<input type="email" name="your_email" class="form-control" id="your_email" placeholder="E-mail"
							       data-toggle="popover" data-placement="bottom" data-trigger="focus" data-content="Enter your email to revert to" data-original-title="Email" required>
							<span class="help-block"></span>
						</div>

						<div class="form-group noshow">
							<label for="email">Your email</label>
							<input type="email" name="email" class="form-control nomail" id="email" placeholder="E-mail" required>
							<span class="help-block"></span>
						</div>

						<div class="form-group">
							<label for="phone">Phone/Mobile Number</label>
							<input type="text" name="phone" class="form-control" id="phone" placeholder="Phone/Mobile Number"
									data-toggle="popover" data-placement="bottom" data-trigger="focus" data-content="Enter your Phone/Mobile Number including country code and STD code." data-original-title="Phone/Mobile Number" required >
							<span class="help-block"></span>
						</div>

						<div class="form-group">
							<label for="contest">Query relates to</label>
							<select name="contest" id="contest" class="form-control" value="<?=$contest_yearmonth;?>" >
							<?php
								$sql = "SELECT yearmonth, contest_name FROM contest";
								$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
								while ($row = mysqli_fetch_array($query)) {
							?>
								<option value="<?=$row['yearmonth'];?>" <?php echo ($row['yearmonth'] == $contest_yearmonth) ? "selected" : "";?> ><?=$row['contest_name'];?></option>
							<?php
								}
							?>
							</select>
							<span class="help-block"></span>
						</div>

						<div class="form-group">
							<label for="message">Your Query</label>
							<textarea name="message" class="form-control" rows="8" id="message" placeholder="Message"
										data-toggle="popover" data-placement="bottom" data-trigger="focus" data-content="Enter Query in brief. Maximum allowed is 500 characters." data-original-title="Your Query" required></textarea>
							<span class="help-block"></span>
						</div>

			  			<div class="form-group">
							<div class="row">
								<div class="col-lg-2 col-md-2 col-sm-2"></div>
								<div class="col-lg-6 col-md-6 col-sm-6">
									<div class="g-recaptcha" data-sitekey="6LfpiFQUAAAAALdj6b1ZSgKJjh7LQkxXo_Xs2KNu"></div>
								</div>
								<div class="col-lg-4 col-md-4 col-sm-4">
									<button type="submit" class="btn btn-color btn-lg pull-right" name="btn_send">Contact Us</button>
								</div>
							</div>
						</div>

					</form>
				</div>
				<div class="col-sm-4">
					<h2 class="headline first-child first-child-m text-color">
						<span class="border-color">Our Address</span>
					</h2>
					<p>YOUTH PHOTOGRAPHIC SOCIETY,<br>
						3rd Floor, State Youth Center, <br/>
						Nrupathunga Road, Bengaluru - 560001.<br/>
						Website: <a href="https://www.ypsbengaluru.com" target="_blank">www.ypsbengaluru.com</a><br />
						Email: <a href="mailto:contactus@ypsbengaluru.com">contactus@ypsbengaluru.com</a><br/>
						Phone: +91-9513-YPS-BLR (+91-9513-977-257)
					</p>
					<p>YPS President: Mr. Manju Vikas Sastry V, AFIP, sastry.vikas@gmail.com</p>
					<p>YPS Secretary: Ms. Prema Kakade, EFIAP, EFIP, cMoL, A.CPE, pmkakade@gmail.com</p>
					<h2 class="headline text-color">
						<span class="border-color">Google Map</span>
					</h2>
					<div class="embed-responsive embed-responsive-4by3">
						<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d15552.157548942632!2d77.58395861939744!3d12.969331501008284!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3bae1673793fefc5%3A0xffb51bb3af1f933a!2sYouth+Photographic+Society!5e0!3m2!1sen!2sin!4v1490210340483" width="400" height="300" frameborder="0" style="border:0" allowfullscreen></iframe>
					</div>
				</div>
			</div>
			<!-- FOOTER -->
			<div class="row">
				<?php include_once("inc/footer.php") ;?>
			</div>
		</div>
    </div> <!-- / .wrapper -->


    <!-- Style Toggle -->
	<?php include_once("inc/settingToggle.php") ;?>
    <!-- JavaScript
    ================================================== -->
	<?php include_once("inc/scripts.php"); ?>

    <script>
    $(document).ready(function(){
		$('html, body').animate({ scrollTop: $("#myTab").offset().top-100 }, 500);
	});
    </script>

	<script>
	$(function(){ $('#full_name').popover(); });
	$(function(){ $('#your_email').popover(); });
	$(function(){ $('#phone').popover(); });
	$(function(){ $('#message').popover(); });
	</script>


	<!-- Form Validation -->
	<script src="plugin/validate/js/jquery.validate.min.js"></script>

	<script>
		$(document).ready(function () {
			$(".noshow").hide();
			$(".nomail").val("wasteoftime@nosuchmail.com");
		});
	</script>

	<!-- Custom Validation Functions -->
	<script src="custom/js/validate.js"></script>
	<!-- Form Validation -->
	<script>
	$(document).ready(function() {
		// Validator for Picture Upload Form
		$('#contact-form').validate({
			rules:{
				full_name: {
					required: true,
					minlength: 3,
					spam_name_filter: '-',
				},
				your_email: {
					required: true,
					email: true,
					spam_email_filter: '-',
				},
				message: {
					minlength: 10,
					maxlength: 500,
					spam_text_filter: '-',
				},
			},
			messages:{
				full_name:{
					required:'Full Name is required',
					spam_name_filter: "Name does not have the First and Last Names or Ill formed human name !",
				},
				your_email:{
					required:'Valid email is required',
					spam_email_filter: "Unable to accept the email entered !",
				},
				message: {
					minlength: "Message should contain a minimum of 10 letters!",
					maxlength: "Cannot handle messages with more than 500 characters ! Send separate email !",
					spam_text_filter: "Message contains text out of context !",
				},
			},
			errorElement: "div",
			errorClass: "valid-error",
			showErrors: function(errorMap, errorList) {
							$("div.valid-error").remove();		// Remove all error messages to avoid duplicate messages
							this.defaultShowErrors();			// Show Errors
						},
			submitHandler: function(form) {
					//form.submit();
					var formData = new FormData(form);
					$('#loader_img').show();
					$.ajax({
							url: "ajax/contact_mail.php",
							type: "POST",
							data: formData,
							cache: false,
							processData: false,
							contentType: false,
							error: function() {
								$('#loader_img').hide();
								swal("Operation failed!", "Server Error: Please report to YPS using the Contact US page for rectification. You should be able to do other operations !", "error");
							},
							success: function(response) {
								$('#loader_img').hide();
								response = JSON.parse(response);
								if(response.success){
									document.location.href = '/index.php';
								}
								else{
									swal("Operation Failed!", response.msg, "error");
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
