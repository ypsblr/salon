<?php
session_save_path(__DIR__ . "/inc/session");
session_start();
include_once("inc/lib.php");

/* Special Auto Launch from Mail */
$email_launch = false;
if (isset($_REQUEST['code'])) {
	list($catalog_yearmonth, $catalog_profile_id) = explode("|", decode_string_array($_REQUEST['code']));
	if ( isset($catalog_yearmonth) && isset($catalog_profile_id) ) {
		$_SESSION['yearmonth'] = $catalog_yearmonth;
		$_SESSION['USER_ID'] = $catalog_profile_id;
		$email_launch = true;
	}
}
if (isset($_REQUEST['mode']))
	$email_launch = ($_REQUEST['mode'] == "email");

include_once("inc/session.php");
include_once("inc/user_lib.php");

// Validate return code from payment
if (isset($_REQUEST["msg"])) {
	if ($_REQUEST["msg"] == "OK") {
		$_SESSION["success_msg"] = "Your Order has been placed !";
		if ($email_launch) {
			unset($_SESSION['USER_ID']);
			header('Location: /index.php');
			printf("<script>location.href='/index.php'</script>");
		}
		else {
			header('Location: /user_panel.php');
			printf("<script>location.href='/user_panel.php'</script>");
		}
	}
	if ($_REQUEST["msg"] == "CANCEL") {
		$_SESSION["err_msg"] = "Payment Cancelled by you !";
	}
}

// Validate if the Catalog is open for order
if (DATE_IN_SUBMISSION_TIMEZONE > $catalogOrderLastDate) {
	$_SESSION["err_msg"] = "The last date for ordering catalog was $catalogOrderLastDate ! ";
	if ($email_launch)
		unset($_SESSION['USER_ID']);
	header('Location: /index.php');
	printf("<script>location.href='/index.php'</script>");
	die();
}
// Validate Login Status
// debug_dump("SESSION", $_SESSION, __FILE__, __LINE__);
// debug_dump("tr_user", $tr_user, __FILE__, __LINE__);
if ( isset($_SESSION['USER_ID']) && isset($tr_user['profile_id']) ) {
	debug_dump("tr_user", $tr_user, __FILE__, __LINE__);

	if ($tr_user['currency'] == "INR" || $tr_user['country_id'] == 101) {
		if ($catalogPriceInINR == "") {
			$_SESSION["err_msg"] = "Catalog not available in INR !";
			if ($email_launch)
				unset($_SESSION['USER_ID']);
			header('Location: /index.php');
			printf("<script>location.href='/index.php'</script>");
			die();
		}
		else {
			$catalog_available_in_inr = true;
			$currency = "INR";
			// list($catalog_price, $catalog_postage) = explode("|", $catalogPriceInINR);
			$catalog_models = json_decode($catalogPriceInINR, true);
			debug_dump("catalogs", $catalog_models, __FILE__, __LINE__);
		}
	}
	else {
		if ($catalogPriceInUSD == "") {
			$_SESSION["err_msg"] = "Catalog not available in USD !";
			if ($email_launch)
				unset($_SESSION['USER_ID']);
			header('Location: /index.php');
			printf("<script>location.href='/index.php'</script>");
			die();
		}
		else {
			$currency = "USD";
			$catalog_available_in_usd = true;
			// list($catalog_price, $catalog_postage) = explode("|", $catalogPriceInUSD);
			$catalog_models = json_decode($catalogPriceInUSD, true);
			debug_dump("catalogs", $catalog_models, __FILE__, __LINE__);
		}
	}


	$profile_id = $tr_user['profile_id'];
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

<style>
	div.col-left {
		text-align: left;
	}
	div.col-center {
		text-align: center;
	}
	div.col-right {
		text-align: right;
	}
</style>

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
					<?php
						if ($email_launch) {
					?>
					<br><br><br>
					<p><b><?= $tr_user['profile_name'];?></b></p>
					<p><?= $tr_user['address_1'];?></p>
					<p><?= $tr_user['address_2'];?></p>
					<p><?= $tr_user['address_3'];?></p>
					<p><?= $tr_user['city'];?></p>
					<p><?= $tr_user['state'] . " - " . $tr_user['pin'];?></p>
					<p><b><?= $tr_user['country_name'];?></b></p>
					<?php
						}
						else
							include("inc/user_sidemenu.php");
					?>
				</div>
				<div class="col-lg-9 col-md-9 col-sm-9">
					<div id="hasResponse"></div>
					<div id="loader_img" style="display:none;background: rgba(0, 0, 0, 0.5);z-index: 9999;">
						<img alt="Loader Image" title="Loading..." src="img/progress_bar.gif">
					</div>

					<div class="sign-form" id="myTab">
						<h2 class="text-color"><?= $contestName;?></h2>
						<h3 class="first-child text text-color">ORDER CATALOG</h3>
						<form role="form" method="post" action="op/catalog_payment.php" id="order-form" name="order-form" >
							<input type="hidden" name="yearmonth" id="yearmonth" value="<?= $contest_yearmonth;?>" >
							<input type="hidden" name="profile_id" id="profile_id" value="<?= $profile_id;?>" >

							<h4>ORDERS</h4>
							<div class="form-group">
								<hr>
								<div class="row">
									<div class="col-sm-2"><label>Date</label></div>
									<div class="col-sm-4 col-center"><label>Catalog Type</label></div>
									<div class="col-sm-1 col-right"><label>Price</label></div>
									<div class="col-sm-1 col-right"><label>Shipping</label></div>
									<div class="col-sm-2 col-center"><label>Copies</label></div>
									<div class="col-sm-2 col-right"><label>Order Value<br><?= $currency;?></label></div>

								</div>
							</div>
							<hr>
							<?php
								$order_value = 0;
								$number_of_copies = 0;
								$sql = "SELECT * FROM catalog_order WHERE yearmonth = '$contest_yearmonth' AND profile_id = '$profile_id' ";
								$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
								if (mysqli_num_rows($query) > 0) {
							?>
							<p><b><u>Past Orders</u></b></p>
							<?php
									while($row = mysqli_fetch_array($query)) {
										$order_id = $row['order_id'];
										$order_value += $row["order_value"];
										// $payment_received += $row["payment_received"];
										$number_of_copies += $row["number_of_copies"];
							?>
							<div class="row">
								<div class="col-sm-2"><?= $row["order_date"];?></div>
								<div class="col-sm-4 col-center"><?= $row['catalog_model'];?></div>
								<div class="col-sm-1 col-right"><?= $row["catalog_price"];?></div>
								<div class="col-sm-1 col-right"><?= $row["catalog_postage"];?></div>
								<div class="col-sm-2 col-center"><?= $row["number_of_copies"];?></div>
								<div class="col-sm-2 col-right"><?= $row["order_value"];?></div>
							</div>
							<?php
									}
								}
							?>

							<!-- Book Additional Orders -->
							<br><p><b><u>New Order</u></b></p>
							<div class="row">
								<div class="col-sm-2"><?= date("Y-m-d");?></div>
								<div class="col-sm-4">
									<?php
										$catalog_price = 0;
										$catalog_postage = 0;
										$first = true;
										foreach ($catalog_models as $model) {
											if ($first) {
												$catalog_price = $model['price'];
												$catalog_postage = $model['postage'];
											}
									?>
									<div class="radio">
										<label>
											<input type="radio" value="<?=$model['model'];?>" name="catalog_model" <?= $first ? "checked" : "";?>
													data-price="<?= $model['price'];?>" data-postage="<?= $model['postage'];?>" ><?=$model['model'];?>
										</label>
									</div>
									<?php
											$first = false;
										}
									?>
								</div>
								<div class="col-sm-1 col-right" id="catalog_price_display"><?= sprintf("%.2f", $catalog_price);?></div>
								<div class="col-sm-1 col-right" id="catalog_postage_display"><?= sprintf("%.2f", $catalog_postage);?></div>
								<div class="col-sm-2 col-center">
									<input type="number" value="0" class="form-control" name="number_of_copies" id="number_of_copies" >
								</div>
								<div class="col-sm-2 col-right"><span id="order_value_display">0.00</span></div>
							</div>

							<!-- Fields used for calculation -->
							<input type="hidden" name="launch_mode" value="<?= $email_launch ? 'email' : 'menu';?>" >
							<input type="hidden" name="past_number_of_copies" id="past_number_of_copies" value="<?= $number_of_copies;?>" >
							<input type="hidden" name="past_order_value" id="past_order_value" value="<?= $order_value;?>" >
							<input type="hidden" name="currency" id="currency" value="<?= $currency;?>" >
							<input type="hidden" name="catalog_price" id="catalog_price" value="<?= $catalog_price;?>" >
							<input type="hidden" name="catalog_postage" id="catalog_postage" value="<?= $catalog_postage;?>" >
							<input type="hidden" name="order_value" id="order_value" value="0" >
							<input type="hidden" value="<?= $number_of_copies;?>" name="total_number_of_copies" id="total_number_of_copies">
							<input type="hidden" value="<?= $order_value;?>" name="total_order_value" id="total_order_value">

							<!-- TOTAL -->
							<hr>
							<div class="row">
								<div class="col-sm-2"><b>TOTAL</b></div>
								<div class="col-sm-4 col-right"></div>
								<div class="col-sm-1"></div>
								<div class="col-sm-1"></div>
								<div class="col-sm-2 col-center"><b><span id="total_number_of_copies_display"><?= $number_of_copies;?></span></b></div>
								<div class="col-sm-2 col-right"><b><span id="total_order_value_display"><?= sprintf("%.2f", $order_value);?></span></b></div>
							</div>

							<!-- Payments -->
							<?php
								$payment_received = 0;
								// Get Payment Record
								$sql  = "SELECT * FROM payment ";
								$sql .= " WHERE yearmonth = '$contest_yearmonth' ";
								$sql .= "   AND account = 'CTG' ";
								$sql .= "   AND link_id = '$profile_id' ";
								$payment_query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
								if (mysqli_num_rows($payment_query) == 0) {
							?>

							<div class="row">
								<div class="col-sm-2">PAYMENT</div>
								<div class="col-sm-4"></div>
								<div class="col-sm-1"></div>
								<div class="col-sm-1"></div>
								<div class="col-sm-2 col-center"></div>
								<div class="col-sm-2 col-right">0.00</div>
							</div>

							<?php
								}
								else {
									while($payment = mysqli_fetch_array($payment_query)) {
										$payment_received += $payment["amount"];
										$dt = $payment["datetime"];
										$payment_date = substr($dt, 0, 4) . "-" . substr($dt, 4, 2) . "-" . substr($dt, 6, 2);
							?>
							<div class="row">
								<div class="col-sm-2">PAYMENT</div>
								<div class="col-sm-2"><?= $payment_date;?></div>
								<div class="col-sm-2"><?= $payment['gateway'];?></div>
								<div class="col-sm-2"><?= $payment['payment_ref'];?></div>
								<div class="col-sm-2 col-center"><?= $payment['currency'];?></div>
								<div class="col-sm-2 col-right"><?= $payment['amount'];?></div>
							</div>
							<?php
									}
								}
							?>

							<input type="hidden" name="past_payment_received" id="past_payment_received" value="<?= $payment_received;?>" >
							<input type="hidden" value="<?= $payment_received;?>" name="total_payment_received" id="total_payment_received">
							<input type="hidden" value="<?= $order_value - $payment_received;?>" name="total_due" id="total_due" >

							<!-- DUE -->
							<hr>
							<div class="row">
								<div class="col-sm-2"><b>BALANCE</b></div>
								<div class="col-sm-4"></div>
								<div class="col-sm-1"></div>
								<div class="col-sm-1"></div>
								<div class="col-sm-2"></div>
								<div class="col-sm-2 col-right"><b><span id="total_due_display"><?= sprintf("%.2f", $order_value - $payment_received);?></span></b></div>
							</div>
							<div class="divider"></div>

							<div class="form-group">
								<div class="row">
									<div class="col-sm-7"></div>
									<div class="col-sm-5 pull-right">
										<label>Payment Network *</label>&nbsp;&nbsp;&nbsp;&nbsp;
										<?php
											if($currency == "USD") {
										?>
											&nbsp;&nbsp;<input type="radio" value="PayPal" name="gateway" id="gateway" checked>&nbsp;&nbsp;PayPal .
										<?php
											}
											else {
										?>
											&nbsp;&nbsp;<input type="radio" value="Instamojo" name="gateway" id="gateway" checked>&nbsp;&nbsp;Instamojo
										<?php
											}
										?>
									</div>
								</div>
							</div>


                            <input type="hidden" name="captcha_method" id="captcha_method" value="php" />
							<div class="form-group">
								<div class="row">
									<div class="col-lg-6 col-md-6 col-sm-6">
										<!-- Captcha not used in logged in forms
                                        <div class="g-recaptcha" id="googleRecaptcha" style="display: none;"></div>
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
									<div class="col-sm-6">
										<br>
										<button type="submit" class="btn btn-color pull-right" name="order_catalog" id="order_catalog"
												<?= ($order_value - $payment_received) > 0 ? "" : "disabled";?> >
											Order and Pay
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

	// Common routine to calculate values and update the input fields and display fields
	function calculate_order () {
		// Get Values
		let past_copies = $("#past_number_of_copies").val() * 1;
		let past_order_value = $("#past_order_value").val() * 1;
		let payment_received = $("#past_payment_received").val() * 1;
		let new_copies = $("#number_of_copies").val() * 1;
		let price = $("#catalog_price").val() * 1;
		let postage = $("#catalog_postage").val() * 1;
		let new_order_value = (price + postage) * new_copies;
		let total_copies = past_copies + new_copies;
		let total_order_value = past_order_value + new_order_value;

		// Update Form Variables
		$("#order_value").val(new_order_value);
		$("#total_number_of_copies").val(total_copies);
		$("#total_order_value").val(total_order_value);
		$("#total_due").val(total_order_value - payment_received);

		// Update Display
		$("#order_value_display").html(new_order_value.toFixed(2));
		$("#total_number_of_copies_display").html(total_copies);
		$("#total_order_value_display").html(total_order_value.toFixed(2));
		$("#total_due_display").html((total_order_value - payment_received).toFixed(2));

		// Enable / Disable Button
		if ((total_order_value - payment_received) > 0) {
			$("#order_catalog").removeAttr("disabled");
		}
		else {
			$("#order_catalog").attr("disabled", "disabled");
		}
	}

    $(document).ready(function(){
		$('html, body').animate({ scrollTop: $("#myTab").offset().top-100 }, 500);

		// Change Price and Postage based on selection
		$("[name='catalog_model']").change(function() {
			$("#catalog_price").val($(this).attr("data-price"));
			$("#catalog_price_display").html(($(this).attr("data-price") * 1).toFixed(2));
			$("#catalog_postage").val($(this).attr("data-postage"));
			$("#catalog_postage").html(($(this).attr("data-postage") * 1).toFixed(2));
			calculate_order();
		});

		// Calculate price based on number of copies
		$("#number_of_copies").change(function() {
			calculate_order();
		});
	});
</script>

<!-- Form Validation -->
<script src="plugin/validate/js/jquery.validate.min.js"></script>

<!-- Custom Validation Functions -->
<script src="custom/js/validate.js"></script>


</body>

</html>
<?php
}
else {
	$_SESSION['err_msg'] = "Invalid Authentication on Catalog Order Page";
	header('Location: /index.php');
	printf("<script>location.href='/index.php'</script>");
}
?>
