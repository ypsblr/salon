<?php
include_once("inc/session.php");
include_once("inc/categories.php");
include_once("inc/user_lib.php");

if(isset($_SESSION['USER_ID']) ) {

	// Handle return from make_payment.php
	if (isset($_GET['msg']) && $_GET['msg'] != "") {
		if ($_GET['msg'] == "CANCELLED")
			$_SESSION['err_msg'] = "Payment operation was canceled by you !";
	}

	// Check for blacklist
	if ($tr_user['blacklist_match'] != "" && $tr_user['blacklist_exception'] == 0)
		handle_error("Not permitted due to match with Blacklisted profile.", __FILE__, __LINE__);

	// Check if the uses matches any entrant categories
	$ec_list = ec_get_eligible_ec_list($contest_yearmonth, $tr_user);
	if ($ec_list == false) {
		$_SESSION['err_msg'] = "There are no matching Entrant Categories for you in this Salon. Please check Home Page for who can participate.";
		die();
	}

	// Check for entrant_category
	if ($tr_user['entrant_category'] == "") {
		$_SESSION['err_msg'] = "Register for the Salon before selecting sections.";
		die();
	}

	$entrant_category = $tr_user['entrant_category'];	// entry
	$fee_group = $tr_user['fee_group'];					// entry->entrant_category
	$discount_group = $tr_user['discount_group'];		// entry->entrant_category
	$fee_code = $tr_user['fee_code'];					// entry (or) club_entry
	$currency = $tr_user['currency'];					// entry (or) club_entry -> entrant_category
	$group_code = $tr_user['club_group_code'];			// club_entry
	$participation_code =  $tr_user['participation_code'];	// entry
	$participation_sections = explode(",", $participation_code);	// entry
	$digital_sections = $tr_user['digital_sections'];	// entry
	$print_sections = $tr_user['print_sections'];		// entry
	$fees_payable = $tr_user['fees_payable'];			// entry
	$discount_applicable = $tr_user['discount_applicable'];		// entry
	$payment_received = $tr_user['payment_received'];			// entry

	// Determine Fee Code if not already set (or) if the fee_code has been set but no payment has been done.
	// Fee code is populated in anticipation :
	//    (a) When the user registers for the contest (or)
	//    (b) When a coupon has been set up.
	// If the fee has been paid either individually or through Group Payment, payment_received would have been updated
	// If payment has been made, allow sections to be added under the same fee_code
	// Otherwise, re-calculate the fee_code based on the date of registration
	if ($tr_user['payment_received'] == 0 || $fee_code == "") {
		// Query fee structure
		$sql  = "SELECT DISTINCT fee_code FROM fee_structure ";
		$sql .= "WHERE yearmonth = '$contest_yearmonth' ";
		$sql .= "  AND fee_group = '$fee_group' ";
		$sql .= "  AND currency = '$currency' ";
		$sql .= "  AND fee_start_date <= '" . DATE_IN_SUBMISSION_TIMEZONE . "' ";
		$sql .= "  AND fee_end_date >= '" . DATE_IN_SUBMISSION_TIMEZONE . "' ";
		$fc = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		if (mysqli_num_rows($fc) == 0) {
			// There is nothing open to select and fee_code has not been previously selected
			// Can't do anything further
			$_SESSION['err_msg'] = "None of the sections are open now for selection and payment.";
			die();
		}
		else {
			$rfc = mysqli_fetch_array($fc);
			$fee_code = $rfc['fee_code'];
		}
	}

	// Fetch Discount Coupon for this participant
	if ($tr_user['coupon_coupon_text'] != "") {
		// There is coupon
		$has_coupon = true;
		$has_discount = true;
		$discount_code = $tr_user['coupon_discount_code'];
	}
	else {
		$has_discount = false;
		$has_coupon = false;
		$discount_code = "CLUB";	// default discount_code
	}

	// Determine if the user is eligible for any of the non-group/POLICY discount group_codes - e.g. YPS
	if ($group_code == "") {
		$sql  = "SELECT DISTINCT group_code FROM discount ";
		$sql .= " WHERE yearmonth = '$contest_yearmonth' ";
		$sql .= "   AND discount_code = '$discount_code' ";
		$sql .= "   AND fee_code = '$fee_code' ";
		$sql .= "   AND discount_group = '$discount_group' ";
		$sql .= "   AND currency = '$currency' ";
		$sql .= "   AND minimum_group_size <= 1 ";
		$sql .= "   AND maximum_group_size >= 1 ";
		//debug_dump("SQL", $sql, __FILE__, __LINE__);
		$qgc = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		if (mysqli_num_rows($qgc) == 1) {		// single matching group code
			$rgc = mysqli_fetch_array($qgc);
			$group_code = $rgc['group_code'];
			$has_discount = true;
		}

	}

	// Build Fee Table Array for this participant
	// ------------------------------------------
	// Take into consideration any list of participation codes in club_entry
	// If present only the codes in the "|" separated list are permitted for the club
	//
	$pc_filter_list = [];
	foreach(explode("|", $tr_user['club_participation_codes']) as $participation_code)
		$pc_filter_list[] = "'" . $participation_code . "'";	// Put quotes around for making a list

	$fee_table = array();
	$sql  = "SELECT * FROM fee_structure ";
	$sql .= "WHERE yearmonth = '$contest_yearmonth' ";
	$sql .= "  AND fee_code = '$fee_code' ";
	$sql .= "  AND fee_group = '$fee_group' ";
	$sql .= "  AND currency = '$currency' ";
	if ($tr_user['coupon_coupon_text'] != "" && $tr_user['club_participation_codes'] != "") {
		$sql .= "   AND participation_code IN (" . implode(", ", $pc_filter_list) . ") ";
	}
	$sql .= "ORDER BY digital_sections DESC, print_sections DESC ";

	$number_of_ALL_Sections = 0;
	$number_of_digital_sections = 0;
	$number_of_print_sections = 0;
	$non_exclusive_digital_options = 0;
	$non_exclusive_print_options = 0;
	$fs = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	while ($rfs = mysqli_fetch_array($fs)) {
		$discount_amount = 0.0;
		$discount_percentage = 0.0;
		$discount_round_digits = 0;
		$discount = 0.0;
		// Fetch any discounts applicable
		if ($has_discount) {
			$sql  = "SELECT * FROM discount ";
			$sql .= "WHERE yearmonth = '$contest_yearmonth' ";
			$sql .= "  AND discount_code = '$discount_code' ";	// = 'CLUB'
			$sql .= "  AND fee_code = '$fee_code' ";
			$sql .= "  AND discount_group = '$discount_group' ";
			$sql .= "  AND currency = '$currency' ";
			$sql .= "  AND group_code = '$group_code' ";
			$sql .= "  AND participation_code = '" . ($contestFeeModel == "POLICY" ? "POLICY" : $rfs['participation_code']) . "' ";
			// debug_dump("SQL", $sql, __FILE__, __LINE__);
			$disc = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
			if (mysqli_num_rows($disc) > 0) {
				$rdisc = mysqli_fetch_array($disc);
				$discount_amount = $rdisc['discount'];
				$discount_percentage = $rdisc['discount_percentage'];
				$discount_round_digits = $rdisc['discount_round_digits'];
				if ($discount_amount > 0.0)
					$discount = $discount_amount;
				else
					$discount = round($rfs['fees'] * $discount_percentage, $discount_round_digits);
			}
		}

		if ($rfs['digital_sections'] > 0 && $rfs['print_sections'] > 0) {
			$fee_type = "ALL";
			++ $number_of_ALL_Sections;
		}
		elseif ($rfs['digital_sections'] > 0) {
			$fee_type = "DIGITAL";
			++ $number_of_digital_sections;
			if ($rfs['exclusive'] == 0)
				++ $non_exclusive_digital_options;
		}
		else {
			$fee_type = "PRINT";
			++ $number_of_print_sections;
			if ($rfs['exclusive'] == 0)
				++ $non_exclusive_print_options;
		}
		$fee_table[$rfs['participation_code']] = array(
														"fee_type" => $fee_type,
														"description" => $rfs['description'],
														"digital_sections" => $rfs['digital_sections'],
														"print_sections" => $rfs['print_sections'],
														"fee_end_date" => ($has_coupon == false) ? $rfs['fee_end_date'] : $registrationLastDate,
														"fees" => $rfs['fees'],
														"exclusive" => $rfs['exclusive'],
														"discount" => $discount,				// computed discount
														"discount_amount" => $discount_amount,
														"discount_percentage" => $discount_percentage,
														"discount_round_digits" => $discount_round_digits,
														"selected" => in_array($rfs['participation_code'], $participation_sections)
														);
	}
	// Add a DIGITAL_NONE and a PRINT_NONE option
	// DIGITAL_NONE
	if ($number_of_digital_sections > 0 && $non_exclusive_digital_options > 0) {
		$fee_table['DIGITAL_NONE'] = array(
										"fee_type" => "DIGITAL",
										"description" => "NO Digital",
										"digital_sections" => "0",
										"print_sections" => "0",
										"fee_end_date" => $registrationLastDate,
										"fees" => "0.00",
										"exclusive" => "0",
										"discount" => "0.00",
										"discount_amount" => "0.00",
										"discount_percentage" => "0.00",
										"discount_round_digits" => "0",
										"selected" => in_array('DIGITAL_NONE', $participation_sections)
										);
	}
	// PRINT_NONE
	if ($number_of_print_sections > 0 && $non_exclusive_print_options > 0) {
		$fee_table['PRINT_NONE'] = array(
										"fee_type" => "PRINT",
										"description" => "NO Print",
										"digital_sections" => "0",
										"print_sections" => "0",
										"fee_end_date" => $registrationLastDate,
										"fees" => "0.00",
										"exclusive" => "0",
										"discount" => "0.00",
										"discount_amount" => "0.00",
										"discount_percentage" => "0.00",
										"discount_round_digits" => "0",
										"selected" => in_array('PRINT_NONE', $participation_sections)
										);
	}

	// Get number of sections under which pictures have been uploaded by the user
	$sql  = "SELECT pic.section AS section, section_type, COUNT(*) AS num_uploads ";
	$sql .= "FROM pic, section ";
	$sql .= "WHERE pic.yearmonth = '$contest_yearmonth' ";
	$sql .= "  AND pic.profile_id = '" . $tr_user['profile_id'] . "' ";
	$sql .= "  AND section.yearmonth = pic.yearmonth ";
	$sql .= "  AND section.section = pic.section ";
	$sql .= "GROUP BY section, section_type ";
	$qup = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$uploaded_digital_sections = 0;
	$uploaded_print_sections = 0;
	while ($uploads = mysqli_fetch_array($qup)) {
		$uploaded_digital_sections += ($uploads['section_type'] == "D" ? 1 : 0);
		$uploaded_print_sections += ($uploads['section_type'] == "P" ? 1 : 0);
	}
?>

<!DOCTYPE html>
<html lang="en">

<head>
<?php include_once("inc/header.php"); ?>

<style type="text/css">
#div1,#div2,#div3 {
	display: none
}
label {
	font-weight: 500 !important;
}
</style>
<!-- Google reCaptcha -->
<!-- Blocking reCaptcha
<script src='https://www.google.com/recaptcha/api.js?onload=onLoadGoogleRecaptcha&render=explicit' async defer></script>
<script type='text/javascript'>
    function onLoadGoogleRecaptcha() {
        $("#captcha_method").val("google");
        grecaptcha.render("googleRecaptcha", { "sitekey" : "6LfpiFQUAAAAALdj6b1ZSgKJjh7LQkxXo_Xs2KNu" });
        $("#googleRecaptcha").show();
        $("#phpCaptcha").hide();
    }
</script>

<script type='text/javascript'>
function refreshCaptcha(){
  var img = document.images['captchaimg'];
  img.src = img.src.substring(0,img.src.lastIndexOf("?"))+"?rand="+Math.random()*1000;
}
</script>
-->


</head>

<body class="<?php echo THEME;?>">

	<?php include_once("inc/navbar.php") ;?>
    <div class="wrapper" style="padding-bottom:0px">
    <?php  include_once("inc/Slideshow.php") ;?>

		<div class="container">
			<div class="row">
				<div class="col-sm-3">
				<?php include("inc/user_sidemenu.php");?>
				</div>	<!-- col-sm-3 -->

				<div class="col-sm-9" id="myTab">
					<!-- Loading image made visible during processing -->
					<div id="loader_img" style="display:none;background: rgba(0, 0, 0, 0.5);z-index: 9999;">
						<img alt="Loader Image" title="Loading..." src="img/progress_bar.gif">
					</div>
					<div class="row">
						<div class="col-sm-12 user-cart">
							<h2 class="primary-font">PAYMENT FORM</h2>
							<div class="sign-form">

								<div class="collapse1" id="online_payment1">
								<?php
									if ($fees_payable > 0 && $payment_received >= ($fees_payable - $discount_applicable)) {
								?>
									<div class="alert alert-info">
										<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
										<strong>Your have already paid for the currently selected participation options. You can increase
												the number of sections, if applicable, by changing the selections and can pay the difference in fees.
												Please note that it is not possible to reduce the number of sections of participation.
												There is no facility to process refund excess fee.
										</strong>
									</div>
								<?php
									}
								?>
									<form method="post" action="#"  name="payment-form" id="payment-form" >
										<!-- hidden fields -->
										<input type="hidden" name="profile_id" id="profile_id" value="<?=$tr_user['profile_id'];?>">
										<input type="hidden" name="profile_name" id="profile_name" value="<?=$tr_user['profile_name'];?>">
										<input type="hidden" name="currency" id="currency" value="<?=$currency;?>" >
										<input type="hidden" name="fee_group" id="fee_group" value="<?=$fee_group;?>" >
										<input type="hidden" name="discount_group" id="discount_group" value="<?=$discount_group;?>" >
										<input type="hidden" name="group_code" id="group_code" value="<?=$group_code;?>" >
										<input type="hidden" name="email" id="email" value="<?=$tr_user['email'];?>" >
										<input type="hidden" name="yearmonth" id="yearmonth" value="<?=$contest_yearmonth;?>" >
										<input type="hidden" name="phone" id="phone" value="<?=$tr_user['phone'];?>" >
										<input type="hidden" name="has_coupon" id="has_coupon" value="<?=$has_coupon;?>" >
										<input type="hidden" name="has_discount" id="has_discount" value="<?=$has_discount;?>" >

										<div class="form-group">
											<div class="row">
												<div class="col-sm-6">
													<label  for="fee_code">Fee Category</label>
													<input type="text" name="fee_code" class="form-control" id="fee_code" value="<?php echo $fee_code;?>" readonly>
												</div>
												<div class="col-sm-6">
													<?php
														if ($tr_user['yps_login_id'] == "" && $has_coupon) {
													?>
													<input type="hidden" name="club_id" value="<?php echo $tr_user['club_id'];?>" >
													<input type="hidden" name="has_coupon" value="<?=$has_coupon;?>" >
													<label for="club">Discount Coupon (<small><?php echo $tr_user['club_name'];?></small>)</label>
													<input type="text" name="coupon_display" class="form-control text-uppercase" id="coupon_display"
																		value="<?=$tr_user['coupon_coupon_text'];?>" readonly>
													<?php
														}
													?>
												</div>
											</div>
										</div>

										<!-- Select Participation -->
										<div class="form-group" id="fee-table">
											<div class="row">
												<div class="col-sm-12">
													<h4 class="text text-color">Choose the number of Digital and Print sections to participate:</h4>
													<?php
														$calculated_total_fees = 0.0;
														$calculated_total_discount = 0.0;
														$calculated_digital_sections = 0;
														$calculated_print_sections = 0;
													?>
													<!-- Table Header -->
													<div class="row">
														<div class="col-sm-6"><b>OPTIONS</b></div>
														<?php
															if ($has_discount) {
														?>
														<div class="col-sm-2"><b>FEES</b></div>
														<div class="col-sm-2"><b>DISCOUNT</b></div>
														<?php
															}
														?>
														<div class="col-sm-2"><b>FEES PAYABLE</b></div>
													</div>
													<div class="divider" style="margin-top: 4px; margin-bottom: 8px;"></div>
													<!-- First the "ALL_PRINT_DIGITAL" option(s) -->
													<?php
														if ($number_of_ALL_Sections > 0) {
													?>
													<div class="row">
														<!-- Selection Options -->
														<div class="col-sm-6">
															<div class="radio">
																<?php
																	$all_print_digital_participation_code = "";
																	$all_print_digital_fees = 0.0;
																	$all_print_digital_discount = 0.0;
																	$calculated_fees = 0.0;
																	$calculated_discount = 0.0;
																	foreach ($fee_table AS $fee_participation_code => $fee_row) {
																		if ($fee_row['fee_type'] == "ALL") {
																			$all_print_digital_participation_code = $fee_participation_code;
																			$all_print_digital_fees = $fee_row['fees'];
																			$all_print_digital_discount = $fee_row['discount'];		// Calculated discount
																			$all_print_digital_digital_sections = $fee_row['digital_sections'];
																			$all_print_digital_print_sections = $fee_row['print_sections'];
																			$is_selected = false;
																			if ($fee_row['selected']){
																				$is_selected = true;
																				$calculated_fees += $fee_row['fees'];
																				$calculated_total_fees += $fee_row['fees'];
																				$calculated_discount += $fee_row['discount'];
																				$calculated_total_discount += $fee_row['discount'];
																				$calculated_digital_sections += $fee_row['digital_sections'];
																				$calculated_print_sections += $fee_row['print_sections'];
																			}
																?>
																<label>
																	<input type="radio" value="<?php echo $fee_participation_code;?>"
																		name="participation_code_all" id="OPT-<?php echo $fee_participation_code;?>" <?php echo $is_selected ? "checked" : ""; ?>
																		data-fee-type="<?php echo $fee_row['fee_type'];?>"
																		data-fee="<?php echo $fee_row['fees'];?>"
																		data-discount="<?php echo $fee_row['discount'];?>"
																		data-discount-amount="<?=$fee_row['discount_amount'];?>"
																		data-discount-percentage="<?=$fee_row['discount_percentage'];?>";
																		data-discount-round-digits="<?=$fee_row['discount_round_digits'];?>";
																		data-digital-sections = "<?php echo $fee_row['digital_sections'];?>"
																		data-print-sections = "<?php echo $fee_row['print_sections'];?>"
																		data-exclusive-option = "<?=$fee_row['exclusive'];?>"
																		data-participation-code = "<?=$fee_participation_code;?>"
																		<?php echo ($fee_row['fee_end_date'] < DATE_IN_SUBMISSION_TIMEZONE) ? "disabled" : "";?> >
																	<?php echo $fee_row['description'];?>
																	<small>
																		(
																		<?php echo $currency . " " . $fee_row['fees'];?>
																		<?php
																			if ($fee_row['discount'] > 0.0) {
																				if ($fee_row['discount_amount'] > 0.0)
																					echo sprintf(" less %.2f", $fee_row['discount_amount']);
																				else
																					echo sprintf(" less %.1f%%", $fee_row['discount_percentage'] * 100);
																			}
																		?>
																		)
																	</small>
																</label>
																<?php
																		}
																	}
																?>
															</div>
														</div>

														<?php
															if ($has_discount) {		// Display Fees & Discount Columns
														?>
														<!-- Fee Display -->
														<div class="col-sm-2" id="FEE-ALL"><?php echo $currency . sprintf(" %7.2f", $calculated_fees);?></div>
														<!-- Discount Display -->
														<?php
																if ($fee_row['discount_percentage'] > 0.0) {
														?>
														<div class="col-sm-2" id="DIS-ALL"><?php echo $currency . sprintf(" %7.2f", $calculated_discount, $fee_row['discount_percentage']*100);?></div>
														<?php
																}
																else {
														?>
														<div class="col-sm-2" id="DIS-ALL"><?php echo $currency . sprintf(" %7.2f", $calculated_discount);?></div>
														<?php
																}
															}
														?>
														<!-- Net Fee Payable Display -->
														<div class="col-sm-2" id="NET-ALL"><?php echo $currency . sprintf(" %7.2f", $calculated_fees - $calculated_discount);?></div>
													</div>		<!-- ALL_PRINT_DIGITAL -->
													<div class="divider" style="margin-top: 4px; margin-bottom: 8px;"></div>
													<?php
														}
														if ($number_of_digital_sections > 0) {
													?>
													<!-- Next the "DIGITAL" option(s) -->
													<div class="row">
														<!-- Selection Options -->
														<div class="col-sm-6">
															<div class="radio">
																<?php
																	$calculated_fees = 0.0;
																	$calculated_discount = 0.0;
																	foreach ($fee_table AS $fee_participation_code => $fee_row) {
																		if ($fee_row['fee_type'] == "DIGITAL") {
																			$is_selected = false;
																			if ($fee_row['selected']){
																				$is_selected = true;
																				$calculated_fees += $fee_row['fees'];
																				$calculated_total_fees += $fee_row['fees'];
																				$calculated_discount += $fee_row['discount'];
																				$calculated_total_discount += $fee_row['discount'];
																				$calculated_digital_sections += $fee_row['digital_sections'];
																			}
																?>
																<label>
																	<input type="radio" value="<?php echo $fee_participation_code;?>"
																		name="participation_code_digital" id="OPT-<?php echo $fee_participation_code;?>" <?php echo $is_selected ? "checked" : ""; ?>
																		data-fee-type="<?php echo $fee_row['fee_type'];?>"
																		data-fee="<?php echo $fee_row['fees'];?>"
																		data-discount="<?php echo $fee_row['discount'];?>"
																		data-discount-amount="<?=$fee_row['discount_amount'];?>"
																		data-discount-percentage="<?=$fee_row['discount_percentage'];?>";
																		data-discount-round-digits="<?=$fee_row['discount_round_digits'];?>";
																		data-digital-sections = "<?php echo $fee_row['digital_sections'];?>"
																		data-print-sections = "<?php echo $fee_row['print_sections'];?>"
																		data-exclusive-option = "<?=$fee_row['exclusive'];?>"
																		data-participation-code = "<?=$fee_participation_code;?>"
																		<?php echo ($fee_row['digital_sections'] < $uploaded_digital_sections) ? "disabled" : "";?>
																		<?php echo ($fee_row['fee_end_date'] < DATE_IN_SUBMISSION_TIMEZONE) ? "disabled" : "";?>    >
																	<?php echo $fee_row['description']; ?>
																	<small>
																		(
																		<?php echo $currency . " " . $fee_row['fees'];?>
																		<?php
																			if ($fee_row['discount'] > 0.0) {
																				if ($fee_row['discount_amount'] > 0.0)
																					echo sprintf(" less %.2f", $fee_row['discount_amount']);
																				else
																					echo sprintf(" less %.1f%%", $fee_row['discount_percentage'] * 100);
																			}
																		?>
																		)
																	</small>
																</label>
																<?php
																		}
																	}
																?>
															</div>
														</div>

														<?php
															if ($has_discount) {		// Display Fees & Discount Columns
														?>
														<!-- Fee Display -->
														<div class="col-sm-2" id="FEE-DIGITAL"><?php echo $currency . sprintf(" %7.2f", $calculated_fees);?></div>
														<!-- Discount Display -->
														<?php
																if ($fee_row['discount_percentage'] > 0.0) {
														?>
														<div class="col-sm-2" id="DIS-DIGITAL"><?php echo $currency . sprintf(" %7.2f", $calculated_discount, $fee_row['discount_percentage']*100);?></div>
														<?php
																}
																else {
														?>
														<div class="col-sm-2" id="DIS-DIGITAL"><?php echo $currency . sprintf(" %7.2f", $calculated_discount);?></div>
														<?php
																}
															}
														?>
														<!-- Net Fee Payable Display -->
														<div class="col-sm-2" id="NET-DIGITAL"><?php echo $currency . sprintf(" %7.2f", $calculated_fees - $calculated_discount);?></div>
													</div>		<!-- ALL_PRINT_DIGITAL -->
													<div class="divider" style="margin-top: 4px; margin-bottom: 8px;"></div>
													<?php
														}
														if ($number_of_print_sections > 0) {
													?>

													<!-- Next the "PRINT" option(s) -->
													<div class="row">
														<!-- Selection Options -->
														<div class="col-sm-6">
															<div class="radio">
																<?php
																	$calculated_fees = 0.0;
																	$calculated_discount = 0.0;
																	foreach ($fee_table AS $fee_participation_code => $fee_row) {
																		if ($fee_row['fee_type'] == "PRINT") {
																			$is_selected = false;
																			if ($fee_row['selected']){
																				$is_selected = true;
																				$calculated_fees += $fee_row['fees'];
																				$calculated_total_fees += $fee_row['fees'];
																				$calculated_discount += $fee_row['discount'];
																				$calculated_total_discount += $fee_row['discount'];
																				$calculated_print_sections += $fee_row['print_sections'];
																			}
																?>
																<label>
																	<input type="radio" value="<?php echo $fee_participation_code;?>"
																		name="participation_code_print" id="OPT-<?php echo $fee_participation_code;?>" <?php echo $is_selected ? "checked" : ""; ?>
																		data-fee-type="<?php echo $fee_row['fee_type'];?>"
																		data-fee="<?php echo $fee_row['fees'];?>"
																		data-discount="<?php echo $fee_row['discount'];?>"
																		data-discount-amount="<?=$fee_row['discount_amount'];?>"
																		data-discount-percentage="<?=$fee_row['discount_percentage'];?>";
																		data-discount-round-digits="<?=$fee_row['discount_round_digits'];?>";
																		data-digital-sections = "<?php echo $fee_row['digital_sections'];?>"
																		data-print-sections = "<?php echo $fee_row['print_sections'];?>"
																		data-exclusive-option = "<?=$fee_row['exclusive'];?>"
																		data-participation-code = "<?=$fee_participation_code;?>"
																		<?php echo ($fee_row['print_sections'] < $uploaded_print_sections) ? "disabled" : "";?>
																		<?php echo ($fee_row['fee_end_date'] < DATE_IN_SUBMISSION_TIMEZONE) ? "disabled" : "";?>    >
																	<?php echo $fee_row['description']; ?>
																	<small>
																		(
																		<?php echo $currency . " " . $fee_row['fees'];?>
																		<?php
																			if ($fee_row['discount'] > 0.0) {
																				if ($fee_row['discount_amount'] > 0.0)
																					echo sprintf(" less %.2f", $fee_row['discount_amount']);
																				else
																					echo sprintf(" less %.1f%%", $fee_row['discount_percentage'] * 100);
																			}
																		?>
																		)
																	</small>
																</label>
																<?php
																		}
																	}
																?>
															</div>
														</div>

														<?php
															if ($has_discount) {		// Display Fees & Discount Columns
														?>
														<!-- Fee Display -->
														<div class="col-sm-2" id="FEE-PRINT"><?php echo $currency . sprintf(" %7.2f", $calculated_fees);?></div>
														<!-- Discount Display -->
														<?php
																if ($fee_row['discount_percentage'] > 0.0) {
														?>
														<div class="col-sm-2" id="DIS-PRINT"><?php echo $currency . sprintf(" %7.2f", $calculated_discount, $fee_row['discount_percentage']*100);?></div>
														<?php
																}
																else {
														?>
														<div class="col-sm-2" id="DIS-PRINT"><?php echo $currency . sprintf(" %7.2f", $calculated_discount);?></div>
														<?php
																}
															}
														?>
														<!-- Net Fee Payable Display -->
														<div class="col-sm-2" id="NET-PRINT"><?php echo $currency . sprintf(" %7.2f", $calculated_fees - $calculated_discount);?></div>
													</div>		<!-- ALL_PRINT_DIGITAL -->
													<div class="divider" style="margin-top: 4px; margin-bottom: 8px;"></div>
													<?php
														}
													?>

													<!-- Display Column Totals -->
													<div class="row">
														<div class="col-sm-6"><b>TOTAL</b></div>
														<?php
															if ($has_discount) {		// Display Fees & Discount Columns
														?>
														<!-- Fee Display -->
														<div class="col-sm-2" id="FEE-TOTAL"><b><?php echo $currency . sprintf(" %7.2f", $calculated_total_fees);?></b></div>
														<!-- Discount Display -->
														<div class="col-sm-2" id="DIS-TOTAL"><b><?php echo $currency . sprintf(" %7.2f", $calculated_total_discount);?></b></div>
														<?php
															}
														?>
														<!-- Net Fee Payable Display -->
														<div class="col-sm-2" id="NET-TOTAL"><b><?php echo $currency . sprintf(" %7.2f", $calculated_total_fees - $calculated_total_discount);?></b></div>
													</div>
													<div class="divider" style="margin-top: 4px; margin-bottom: 8px;"></div>
												</div>
											</div>  	<!-- END OF FEE OPTIONS, FEEs and DOSOUNTs -->
										</div>			<!-- / fee_table -->

										<!-- DISPLAY PAYMENTS RECEIVED AND AMOUNT DUE -->
										<div class="form-group">
											<div class="row">
												<div class="col-sm-12">
													<h4 class="text text-color">Payments received : </h4>
													<div class="row">
														<div class="col-sm-2"><b>Date</b></div>
														<div class="col-sm-3"><b>Gateway</b></div>
														<div class="col-sm-5"><b>Reference</b></div>
														<div class="col-sm-2"><b>Amount</b></div>
													</div>
													<div class="divider" style="margin-top: 4px; margin-bottom: 8px;"></div>
												<?php
													$sql = "SELECT * FROM payment WHERE yearmonth = '$contest_yearmonth' AND account = 'IND' AND link_id = '" . $tr_user['profile_id'] . "' ORDER BY datetime";
													$qpay = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
													$calculated_payment_received = 0.0;
													while ($rpay = mysqli_fetch_array($qpay)) {
														$calculated_payment_received += $rpay['amount'];
												?>
													<div class="row">
														<div class="col-sm-2"><?php echo substr($rpay['datetime'], 0, 4) . "-" . substr($rpay['datetime'], 4, 2) . "-" . substr($rpay['datetime'], 6, 2);?></div>
														<div class="col-sm-3"><?php echo $rpay['gateway'];?></div>
														<div class="col-sm-5"><?php echo $rpay['payment_ref'];?></div>
														<div class="col-sm-2"><?php echo $currency . " " . $rpay['amount'];?></div>
													</div>

												<?php
													}
												?>
													<div class="divider" style="margin-top: 4px; margin-bottom: 8px;"></div>
													<div class="row">
														<div class="col-sm-2"></div>
														<div class="col-sm-8"><span class="pull-right">TOTAL PAYMENT RECEIVED</span></div>
														<div class="col-sm-2">
															<span id="display_payment_received"><?php printf("%s %7.2f", $currency, $calculated_payment_received);?></span>
															<input type="hidden" name="payment_received" id="payment_received" value="<?php echo $calculated_payment_received;?>" >
														</div>
													</div>
													<div class="divider" style="margin-top: 4px; margin-bottom: 8px;"></div>
													<div class="row">
														<div class="col-sm-2"></div>
														<div class="col-sm-8"><span class="pull-right"><b>PAYMENT DUE</b></span></div>
														<div class="col-sm-2">
															<span id="display_balance_payment" style="font-weight: bold;"><?php printf("%s %7.2f", $currency, $calculated_total_fees - $calculated_total_discount - $calculated_payment_received);?></span>
															<input type="hidden" name="fees_payable" id="fees_payable" value="<?php echo $calculated_total_fees;?>" >
															<input type="hidden" name="discount_applicable" id="discount_applicable" value="<?php echo $calculated_total_discount;?>" >
															<input type="hidden" name="balance_payment" id="balance_payment" value="<?php echo $calculated_total_fees - $calculated_total_discount - $calculated_payment_received;?>" >
															<input type="hidden" name="digital_sections" id="digital_sections" value="<?php echo $calculated_digital_sections;?>" >
															<input type="hidden" name="print_sections" id="print_sections" value="<?php echo $calculated_print_sections;?>" >
														</div>
													</div>
													<div class="divider" style="margin-top: 4px; margin-bottom: 8px;"></div>
												</div>
											</div>
										</div>			<!-- END OF Payments Display -->

                                        <!-- Set Default Validation Method of Captcha Validation to PHP Captcha. Will be changed to google on load -->
                                        <input type="hidden" name="captcha_method" id="captcha_method" value="php" />

										<!-- reCAPTCHA -->
										<div class="form-group">
											<div class="row">
												<div class="col-sm-5">
													<label>Payment Network *</label>&nbsp;&nbsp;&nbsp;&nbsp;
													<?php
														if($currency == "USD") {
													?>
														<input type="radio" value="PayPal" name="gateway" id="gateway-paypal" checked>&nbsp;&nbsp;PayPal .
													<?php
														}
														else {
													?>
														<!-- &nbsp;&nbsp;<input type="radio" value="PayPal" name="gateway" id="gateway-paypal" checked>&nbsp;&nbsp;PayPal . -->
														&nbsp;&nbsp;<input type="radio" value="Instamojo" name="gateway" id="gateway-instamojo" checked>&nbsp;&nbsp;Instamojo
													<?php
														}
													?>
												</div>
											</div>
										</div>
										<div class="form-group">
											<div class="row">
												<div class="col-sm-3"></div>
												<div class="col-sm-6">
                                                    <!-- <div class="g-recaptcha" data-sitekey="6LfpiFQUAAAAALdj6b1ZSgKJjh7LQkxXo_Xs2KNu"></div> -->
													<!-- Block Recaptcha from logged in forms
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
													<button type="submit" class="btn btn-color" name="make_payment" id="make_payment" <?php echo (($fees_payable - $discount_applicable - $payment_received) < 0) ? "disabled" : "";?> >
														<?php echo (($calculated_total_fees - $calculated_total_discount) > $calculated_payment_received) ? "Pay Now" : "Update";?>
													</button>
												</div>
											</div>
										</div>

									</form>
									<br>
								</div> <!-- online__form -->
								<!-- Form that will be used to submit data -->
								<form id="submission_form" name="submission_form" action="op/make_payment.php" method="post">
									<input type="hidden" name="ypsd" id="ypsd" value="" >
								</form>
								<div class="alert alert-info">
										If the payment made by you is not appearing here, please do not make another payment. Please send us details through Contact Us page.
								</div>

							<?php
								if($tr_user['fees_payable'] < 0) {
							?>
								<div class="alert alert-danger">
									You can change the number of sections to participate any time and pay the difference There is no provision to provide a refund.
								</div>
							<?php
								}
							?>

							</div>
						</div>
					</div>
				</div> <!-- / right column -->
			</div> <!-- / .row -->
		</div> <!-- / .container -->
		<!-- Footer -->
		<?php include_once("inc/footer.php");?>
	</div> <!-- / .wrapper -->

    <!-- Style Toggle -->

	<?php include_once("inc/settingToggle.php") ;?>
    <!-- JavaScript
    ================================================== -->
	<?php include_once("inc/scripts.php"); ?>

	<!-- Form specific scripts -->

	<!-- Script to redraw the fee table based on the selection -->
	<script>
	function format_float(number, width, decimal) {
		var tmp = Math.round(number * Math.pow(10, decimal)).toString();		// Multiply to make it a whole number and convert to string
		var zero = "0";
		if (tmp.length <= decimal)	// Probably just "0"
			tmp = zero.repeat(decimal - tmp.length + 1).concat(tmp);
		var str = tmp.substr(0, tmp.length - decimal).concat(".", tmp.substr(decimal * -1));
		var space = " ";
		if (str.length < width)
			return space.repeat(width - str.length).concat(str);
		else
			return str;
	}
	</script>

	<script>
	//var fee_table = JSON.parse(<?php echo json_encode($fee_table);?>);		// Load the fee table into JS Object
	var currency = "<?php echo $currency;?>";
	var feeModel = "<?=$contestFeeModel;?>";
	var all_print_digital_participation_code = "<?php echo isset($all_print_digital_participation_code) ? $all_print_digital_participation_code : '' ;?>";
	var all_print_digital_fees = Number(<?php echo isset($all_print_digital_fees) ? $all_print_digital_fees : 0;?>);
	var all_print_digital_discount = Number(<?php echo isset($all_print_digital_discount) ? $all_print_digital_discount : 0;?>);
	var all_print_digital_digital_sections = Number(<?php echo isset($all_print_digital_digital_sections) ? $all_print_digital_digital_sections : 0;?>);
	var all_print_digital_print_sections = Number(<?php echo isset($all_print_digital_print_sections) ? $all_print_digital_print_sections : 0;?>);

	function redrawFeeTable(event) {
		// Uncheck Digital and Print options if ALL selected
		var fee_type = $(this).attr("data-fee-type");
		var exclusive_option = $(this).attr("data-exclusive-option");
		var participation_code = $(this).attr("data-participation-code");

		// Handle Exclusive Selection of radio buttons
		// When an exclusive option like "ALL" is checked, all others will be unchecked
		// When anything else is checked, exclusive option will be unchecked
		if (exclusive_option != "0") {
			// Uncheck all other options
			$("#fee-table").find("input").each(function(){
				// uncheck fee-type != current fee_type
				if ($(this).attr("data-participation-code") != participation_code) {
					$(this).prop("checked", false);
				}
			});
		}
		else {
			// Uncheck the exclusive option
			$("#fee-table").find("input").each(function() {
				// Uncheck fee-type = ALL
				if ($(this).attr("data-exclusive-option") != "0") {
					$(this).prop("checked", false);
				}
			});
		}

		// Counters
		var all_fees = 0.0 ;
		var all_discount = 0.0;
		var digital_fees = 0.0;
		var digital_discount = 0.0;
		var print_fees = 0.0;
		var print_discount = 0.0;
		var digital_sections = 0;
		var print_sections = 0;

		// Recalculate amounts of fees and discount using radio boxes checked
		$("#fee-table").find("input").each(function(){
			if ($(this).prop("checked")) {
				digital_sections += Number($(this).attr("data-digital-sections"));
				print_sections += Number($(this).attr("data-print-sections"));
				if ($(this).attr("data-fee-type") == "ALL") {
					all_fees = Number($(this).attr("data-fee"));
					all_discount = Number($(this).attr("data-discount"));
				}
				if ($(this).attr("data-fee-type") == "DIGITAL") {
					digital_fees = Number($(this).attr("data-fee"));
					digital_discount = Number($(this).attr("data-discount"));
				}
				if ($(this).attr("data-fee-type") == "PRINT") {
					print_fees = Number($(this).attr("data-fee"));
					print_discount = Number($(this).attr("data-discount"));
				}
			}
		});

		// Automatically enable "ALL_PRINT_DIGITAL_FEE" option if the user had selected multiple options that are more expensinve than the ALL_PRINT_DIGITAL option
		<?php
			// Generate this section only if there is a fee_type all
			if (isset($all_print_digital_participation_code)) {
		?>
		if ((digital_fees + print_fees) > all_print_digital_fees) {
			all_fees = all_print_digital_fees;
			all_discount = all_print_digital_discount;
			digital_sections = all_print_digital_digital_sections;
			print_sections = all_print_digital_print_sections;
			digital_fees = 0.0;
			digital_discount = 0.0;
			print_fees = 0.0;
			print_discount = 0.0;
			// Check ALL option and uncheck other options
			$("#fee-table").find("input").each(function(){
				if ($(this).attr("data-fee-type") == "ALL")
					$(this).prop("checked", true);
				else
					$(this).prop("checked", false);
			});
		}
		<?php
			}
		?>

		var total_fees = all_fees + digital_fees + print_fees;
		var total_discount = all_discount + digital_discount + print_discount;

		var payment_received = Number($("#payment_received").val());

		// Update the Screen
		$("#FEE-ALL").html(currency + " " + all_fees.toFixed(2));
		$("#DIS-ALL").html(currency + " " + all_discount.toFixed(2));
		$("#NET-ALL").html(currency + " " + (all_fees - all_discount).toFixed(2));
		$("#FEE-DIGITAL").html(currency + " " + digital_fees.toFixed(2));
		$("#DIS-DIGITAL").html(currency + " " + digital_discount.toFixed(2));
		$("#NET-DIGITAL").html(currency + " " + (digital_fees - digital_discount).toFixed(2));
		$("#FEE-PRINT").html(currency + " " + print_fees.toFixed(2));
		$("#DIS-PRINT").html(currency + " " + print_discount.toFixed(2));
		$("#NET-PRINT").html(currency + " " + (print_fees - print_discount).toFixed(2));
		$("#FEE-TOTAL").html("<b>" + currency + " " + total_fees.toFixed(2) + "</b>");
		$("#DIS-TOTAL").html("<b>" + currency + " " + total_discount.toFixed(2) + "</b>");
		$("#NET-TOTAL").html("<b>" + currency + " " + (total_fees - total_discount).toFixed(2) + "</b>");
		$("#fees_payable").val(total_fees);
		$("#discount_applicable").val(total_discount);
		$("#balance_payment").val(total_fees - total_discount - payment_received);
		$("#display_balance_payment").html(currency + " " + (total_fees - total_discount - payment_received).toFixed(2));
		$("#digital_sections").val(digital_sections);
		$("#print_sections").val(print_sections);

		// Enable Pay button
		if (total_fees - total_discount - payment_received >= 0) {
			$("#make_payment").prop("disabled", false);
			if (total_fees - total_discount - payment_received > 0)
				$("#make_payment").html("Pay Now");
			else
				$("#make_payment").html("Update");
		}
		else
			$("#make_payment").prop("disabled", true);
	}

	$(document).ready(function(){
		$("#fee-table").find("input").each(function(index, element){
			// Attach mouse click handler
			console.log("Attaching to " + $(this).val());
			console.log("element is " + element.id);
			$(this).click(redrawFeeTable);
		});
		$("#coupon_text").change(redrawFeeTable);
	});

	</script>

	<script>
    $(document).ready(function(){
		$('html, body').animate({ scrollTop: $("#myTab").offset().top-100 }, 500);
	});
    </script>

	<!-- Encrypt and transfer data to submission_form -->
	<script>
		$("#make_payment").click(function(e){
			// Prevent the main form from getting submitted
			e.preventDefault();

			// Encrypt Data and Submit the Form
			let formData = new FormData($("#payment-form").get(0));
			let ypsd = CryptoJS.AES.encrypt(jsonFormData(formData), "<?= SALONBOND;?>", { format: CryptoJSAesJson }).toString();
			$("#ypsd").val(ypsd);
			$("#submission_form").submit();
		});
	</script>

</body>

</html>

<?php
} else{
	header('Location: index.php');
   printf("<script>location.href='index.php'</script>");
}

?>
