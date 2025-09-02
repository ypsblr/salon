<?php
include_once("inc/session.php");
// include_once("inc/lib.php");
include_once("inc/salonlib.php");


if (! empty($_REQUEST['contest'])) {
	$exhibition_yearmonth = $_REQUEST['contest'];
}
else if (! empty($_SESSION['yearmonth']))
	$exhibition_yearmonth = $_SESSION['yearmonth'];
else {
	// If SESSION is not set, set to the latest
	$sql = "SELECT max(yearmonth) AS yearmonth FROM contest";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$row = mysqli_fetch_array($query);
	$exhibition_yearmonth = $row['yearmonth'];
}

if (! ($salon = get_contest($exhibition_yearmonth)))
	handle_error("This requested Salon is yet to publish results", __FILE__, __LINE__);

// $exhibition_yearmonth = empty($_REQUEST['contest']) ? $contest_yearmonth : $_REQUEST['contest'];
// $sql = "SELECT * FROM exhibition WHERE yearmonth = '$exhibition_yearmonth' ";
// $query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
// $exhibition = mysqli_fetch_array($query);

// Validate if a virtual exhibition has been configured and is ready
// if (date("Y-m-d") >= $exhibitionStartDate && date("Y-m-d") <= $exhibitionEndDate &&
//	$exhibition && $exhibition['is_virtual'] == 1 && $exhibition['virtual_tour_ready'] == 1) {
if (date("Y-m-d") >= $salon['contest']['exhibition_start_date'] && date("Y-m-d") <= $salon['contest']['exhibition_end_date'] &&
	isset($salon['exhibition']) && $salon['exhibition']['is_virtual'] && $salon['exhibition']['virtual_tour_ready']) {
	// Get Visitor Details
	if (! empty($_SESSION['VISITOR_ID'])) {
		// $visitor_id = $_SESSION['VISITOR_ID'];
		$sql = "SELECT * FROM visitor_book WHERE yearmonth = '$exhibition_yearmonth' AND visitor_id = '" . $_SESSION['VISITOR_ID'] . "' ";
		$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		if (mysqli_num_rows($query) > 0) {
			$visitor = mysqli_fetch_array($query);
			$profile_id = $visitor['visitor_id'];
			$profile_id = $visitor['profile_id'];
			$visitor_name = $visitor['visitor_name'];
			$visitor_email = $visitor['visitor_email'];
			$visitor_phone = $visitor['visitor_phone'];
			$visitor_whatsapp = $visitor['visitor_whatsapp'];
			$visitor_comments = $visitor['visitor_comments'];
		}
	}
	// Else get user details assuming first time visitor
	else if (! empty($tr_user)) {
		$profile_id = $tr_user['profile_id'];
		$visitor_name = $tr_user['profile_name'];
		$visitor_email = $tr_user['email'];
		$visitor_phone = $tr_user['phone'];
		$visitor_whatsapp = $tr_user['whatsapp'];
		$visitor_comments = "";
	}

	$leaving = isset($_REQUEST['leaving']);
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
						<h2 class="text-color"><?= $contestName;?></h2>
						<h3 class="first-child text text-color">GUEST BOOK</h3>
						<p class="text-justify">
							If you are a participant of the Salon. Please login before visiting this page. Other Guest visitors can continue and register.
						</p>
						<?php
							if (empty($visitor_email)) {
						?>
						<div class="well">
							<div class="form-group">
								<div class="row">
									<div class="col-sm-9">
										<p><b>Returning Visitors:</b> Use Email to get details entered earlier.</p>
										<label for="returning_phone">Email used to visit the Exhibition</label>
										<div class="input-group">
											<input type="text" class="form-control" name="find_email" id="find_email" />
											<span class="input-group-btn">
												<button class="btn btn-color" onclick="get_visitor_details()">Get Details</button>
											</span>
										</div>
									</div>
								</div>
							</div>
						</div>
						<?php
							}
						?>
						<form role="form" method="post" action="op/enter_exhibition.php" id="visitor-form" name="visitor-form" >
							<input type="hidden" name="yearmonth" id="yearmonth" value="<?= $exhibition_yearmonth;?>" >
							<input type="hidden" name="profile_id" id="profile_id" value="<?= isset($profile_id) ? $profile_id : "0";?>" >
							<input type="hidden" name="visitor_id" id="visitor_id" value="<?= isset($visitor_id) ? $visitor_id : "0";?>" >

							<div class="form-group">
								<div class="row">
									<div class="col-sm-6">
										<label for="visitor_name">Enter your Name *</label>
										<input type="text" class="form-control" name="visitor_name" id="visitor_name"
														value="<?= isset($visitor_name) ? $visitor_name : '';?>" <?= $leaving ? "disabled": "";?> >
									</div>
									<div class="col-sm-6">
										<label for="visitor_email">Enter your email *</label>
										<input type="text" class="form-control" name="visitor_email" id="visitor_email"
														value="<?= isset($visitor_email) ? $visitor_email : '';?>" <?= $leaving ? "disabled": "";?> >
									</div>
								</div>
							</div>

							<div class="form-group">
								<div class="row">
									<div class="col-sm-6">
										<label for="visitor_phone">Enter your Phone *</label>
										<input type="text" class="form-control" name="visitor_phone" id="visitor_phone"
														value="<?= isset($visitor_phone) ? $visitor_phone : '';?>" <?= $leaving ? "disabled": "";?> >
									</div>
									<div class="col-sm-6">
										<label for="visitor_whatsapp">Enter your Whatsapp</label>
										<input type="text" class="form-control" name="visitor_whatsapp" id="visitor_whatsapp"
														value="<?= isset($visitor_whatsapp) ? $visitor_whatsapp : '';?>" <?= $leaving ? "disabled": "";?> >
									</div>
								</div>
							</div>

							<?php
								if ($leaving) {
							?>
							<input type="hidden" name="leaving" value="leaving">
							<div class="form-group">
								<div class="row">
									<div class="col-sm-12">
										<label for="visitor_name">Appreciate your feedback</label>
										<textarea class="form-control" rows="4" name="visitor_comments"
												  id="visitor_comments" ><?= isset($visitor_comments) ? $visitor_comments : '';?></textarea>
									</div>
								</div>
							</div>
							<?php
								}
							?>

                            <input type="hidden" name="captcha_method" id="captcha_method" value="php" />
							<div class="form-group">
								<div class="row">
									<div class="col-lg-6 col-md-6 col-sm-6">
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
									<div class="col-sm-6">
										<br>
										<button type="submit" class="btn btn-color pull-right" name="enter_exhibition" id="enter_exhibition" >
											<?= $leaving ? "Leave Comment" : "Enter the Exhibition"; ?>
										</button>
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
	function get_visitor_details() {
		let find_email = $("#find_email").val();
		let yearmonth = $("#yearmonth").val();
		$.post("ajax/get_visitor_details.php", {yearmonth, find_email}, function(response) {
			let data = JSON.parse(response);
			if (data.success) {
				$("#visitor_id").val(data.visitor.visitor_id);
				$("#visitor_name").val(data.visitor.visitor_name);
				$("#visitor_email").val(data.visitor.visitor_email);
				$("#visitor_phone").val(data.visitor.visitor_phone);
				$("#visitor_whatsapp").val(data.visitor.visitor_whatsapp);
			}
			else {
				swal({
					title: 'Error',
					text:  data.msg,
					icon: "error",
					button: 'Dismiss'
				});
			}
		});
	}
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
	$('#visitor-form').validate({
		rules:{
			visitor_name: {required: true},
			visitor_email: {
					required: true,
					email: true },
			visitor_phone:{
				required: true,
				number:true,
				minlength: 8,
				maxlength: 15,
			},
		},
		errorElement: "div",
		errorClass: "valid-error",
		submitHandler: function(form) {
			form.submit();
			return false;
		},
	});
});
</script>

</body>

</html>
<?php
}
else {
	$_SESSION['err_msg'] = "No Virtual exhibitions are open";
	if (isset($_SERVER["HTTP_REFERER"])) {
		header('Location: ' . $_SERVER['HTTP_REFERER']);
		printf("<script>location.href='" . $_SERVER['HTTP_REFERER'] . "'</script>");
	}
	else {
		echo "Unable to proceed: " . $_SESSION['err_msg'];
	}
}
?>
