<?php
include_once("inc/session.php");
include_once("inc/lib.php");

if(isset($_SESSION['USER_ID'])) {

	// Handle return from update_group_payment.php
	if (isset($_GET['msg']) && $_GET['msg'] != "") {
		if ($_GET['msg'] == "CANCELLED")
			$_SESSION['err_msg'] = "Payment operation cancelled by you !";
	}

	// Check for blacklist
	if ($tr_user['blacklist_match'] != "" && $tr_user['blacklist_exception'] == 0)
		handle_error("Not permitted due to match with Blacklisted profile.", __FILE__, __LINE__);

	// Validations
	// Must have registered a club
	if ($tr_user['club_entered_by'] != $tr_user['profile_id'])
		handle_error("You have not registered any clubs in this Salon that require Group Payment !", __FILE__, __LINE__);

	// Fetch club_entry which has most of the det

	// Fetch the list of Clubs/Groups registered by the user. If no club/group is registered, this screen is not for this user
//	$sql  = "SELECT * FROM club_entry, club ";
//	$sql .= " WHERE club_entry.yearmonth = '$contest_yearmonth' ";
//	$sql .= "   AND club_entry.club_id = club.club_id ";
//	$sql .= "   AND club_entered_by = '" . $tr_user['profile_id'] . "' ";
//	$sql .= "   AND payment_mode = 'GROUP_PAYMENT' ";
//	$q_club = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
//	if (mysqli_num_rows($q_club) > 0)
//		$my_club = mysqli_fetch_array($q_club);
//	else {
//		handle_error("You have not registered any clubs in this Salon that require Group Payment !", __FILE__, __LINE__);
//	}

	// Determine Fee Code
	$fee_code = $tr_user['club_fee_code'];
	if ($tr_user['club_total_payment_received'] == 0 || $tr_user['club_fee_code'] == "") {
		$sql  = "SELECT DISTINCT fee_code FROM fee_structure ";
		$sql .= " WHERE yearmonth = '$contest_yearmonth' ";
		$sql .= "   AND fee_start_date <= '" . DATE_IN_SUBMISSION_TIMEZONE . "' ";
		$sql .= "   AND fee_end_date >= '" . DATE_IN_SUBMISSION_TIMEZONE . "' ";
		$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		$row = mysqli_fetch_array($query);
		$fee_code = $row['fee_code'];
	}

	$club_id = $tr_user['club_id'];
	$entrant_category = $tr_user['club_entrant_category'];
	$currency = $tr_user['club_currency'];
	$fee_group = $tr_user['fee_group'];
	$discount_group = $tr_user['discount_group'];
	$group_code = $tr_user['club_group_code'];
	$paid_participants = $tr_user['club_paid_participants'];
	$total_fees = $tr_user['club_total_fees'];
	$total_discount = $tr_user['club_total_discount'];
	$total_payment_received = $tr_user['club_total_payment_received'];

	$sql  = "SELECT MAX(digital_sections) AS max_digital_sections, MAX(print_sections) AS max_print_sections ";
	$sql .= "  FROM fee_structure ";
	$sql .= " WHERE yearmonth = '$contest_yearmonth' ";
	$sql .= "   AND fee_code = '$fee_code' ";
	$sql .= "   AND fee_group = '$fee_group' ";
	$sql .= "   AND currency = '$currency' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$row = mysqli_fetch_array($query);
	$max_digital_sections = $row['max_digital_sections'];
	$max_print_sections = $row['max_print_sections'];


	// Find the GENERAL entrant_category - No restrictions and club can be created
//	$sql  = "SELECT * FROM entrant_category ";
//	$sql .= " WHERE yps_membership_required = 0 ";
//	$sql .= "   AND gender_must_match = 0 ";
//	$sql .= "   AND age_within_range = 0 ";
//	$sql .= "   AND currency = 'INR' ";
//	$sql .= "   AND can_create_club = 1 ";
//	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
//	if (mysqli_num_rows($query) != 1)
//		handle_error("Suitable Category is not found", __FILE__, __LINE__);
//	$row = mysqli_fetch_array($query);
//	$entrant_category = $row['entrant_category'];			// Only option available for this salon

	// Build Fee Table Array for this Club
	// -----------------------------------
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
	if ($tr_user['club_participation_codes'] != "") {
		$sql .= "   AND participation_code IN (" . implode(", ", $pc_filter_list) . ") ";
	}
	$sql .= "ORDER BY digital_sections DESC, print_sections DESC ";
	debug_dump("SQL", $sql, __FILE__, __LINE__);

	$fs = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	$all_print_digital_participation_code = "";
	$all_print_digital_fees = 0.0;
	$all_print_digital_discount = 0.0;
	$all_print_digital_digital_sections = 0;
	$all_print_digital_print_sections = 0;

	$all_digital_participation_code = "";
	$all_digital_sections = 0;
	$all_print_participation_code = "";
	$all_print_sections = 0;

//	$digital_section_options = array();
//	$print_section_options = array();

	$has_discount_percentage = false;

	while ($rfs = mysqli_fetch_array($fs)) {
		// Fetch discounts applicable
		$sql  = "SELECT * FROM discount ";
		$sql .= "WHERE yearmonth = '$contest_yearmonth' ";
		$sql .= "  AND discount_code = 'CLUB' ";
		$sql .= "  AND fee_code = '$fee_code' ";
		$sql .= "  AND discount_group = '$discount_group' ";
		$sql .= "  AND participation_code = '" . ($contestFeeModel == "POLICY" ? "POLICY" : $rfs['participation_code']) . "' ";
		$sql .= "  AND currency = '" . $rfs['currency'] . "' ";
		// debug_dump("Discount Query", $sql, __FILE__, __LINE__);
		$disc = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		if (mysqli_num_rows($disc) > 0) {
			$rdisc = mysqli_fetch_array($disc);
			$discount = $rdisc['discount'];
			$discount_percentage = $rdisc['discount_percentage'];
			$discount_round_digits = $rdisc['discount_round_digits'];
			if ($discount_percentage > 0)
				$has_discount_percentage = true;
		}
		else {
			$discount = 0.0;
			$discount_percentage = 0.0;
			$discount_round_digits = 0;
		}

		// Group Options
		if ($rfs['digital_sections'] > 0 && $rfs['print_sections'] > 0)
			$fee_type = "ALL";
		elseif ($rfs['digital_sections'] > 0)
			$fee_type = "DIGITAL";
		else
			$fee_type = "PRINT";

		// Build Fee Table
		$fee_table[$rfs['participation_code']] = array(
														"fee_type" => $fee_type,
														"description" => $rfs['description'],
														"digital_sections" => $rfs['digital_sections'],
														"print_sections" => $rfs['print_sections'],
														"fees" => $rfs['fees'],
														"exclusive" => $rfs['exclusive'],
														"discount" => $discount,
														"discount_percentage" => $discount_percentage,
														"discount_round_digits" => $discount_round_digits,
														);

		// Other fields required
		if ($rfs['digital_sections'] == $max_digital_sections && $rfs['print_sections'] == $max_print_sections) {
			$all_print_digital_participation_code = $rfs['participation_code'];
			$all_print_digital_fees = $rfs['fees'];
			if ($discount_percentage != 0)
				$all_print_digital_discount = round($all_print_digital_fees * $discount_percentage, $discount_round_digits);
			else
				$all_print_digital_discount = $discount;
			$all_print_digital_digital_sections = $rfs['digital_sections'];
			$all_print_digital_print_sections = $rfs['print_sections'];
		}
		if ($rfs['digital_sections'] == $max_digital_sections && $rfs['print_sections'] == 0) {
			$all_digital_participation_code = $rfs['participation_code'];
			$all_digital_sections = $rfs['digital_sections'];
		}
		if ($rfs['print_sections'] == $max_print_sections && $rfs['digital_sections'] == 0) {
			$all_print_participation_code = $rfs['participation_code'];
			$all_print_sections = $rfs['print_sections'];
		}

//		if ($rfs['digital_sections'] != 0)
//			$digital_section_options[$rfs['digital_sections']] = $digital_section_options[$rfs['digital_sections']] . " Digital";			// Create an array
//		if ($rfs['print_sections'] != 0)
//			$print_section_options[$rfs['print_sections']] = $print_section_options[$rfs['print_sections']] . " Print";			// Create an array
	}
	// Add a DIGITAL_NONE and a PRINT_NONE option
	// DIGITAL_NONE
	if ($contestDigitalSections > 0) {
		$fee_table['DIGITAL_NONE'] = array(
											"fee_type" => "DIGITAL",
											"description" => "NO Digital",
											"digital_sections" => "0",
											"print_sections" => "0",
											"fees" => "0.00",
											"exclusive"=> 0,
											"discount" => "0.00",
											"discount_percentage" => "0.00",
											"discount_round_digits" => "0"
											);
	}
	// PRINT_NONE
	if ($contestPrintSections > 0) {
		$fee_table['PRINT_NONE'] = array(
											"fee_type" => "PRINT",
											"description" => "NO Print",
											"digital_sections" => "0",
											"print_sections" => "0",
											"fees" => "0.00",
											"exclusive"=> 0,
											"discount" => "0.00",
											"discount_percentage" => "0.00",
											"discount_round_digits" => "0"
											);
	}
	//debug_dump("Fee Table", $fee_table, __FILE__, __LINE__);


?>

<!DOCTYPE html>
<html lang="en">

<head>
<?php include_once("inc/header.php"); ?>

<link rel="stylesheet" href="plugin/datatable/css/dataTables.bootstrap.min.css" />

<style type="text/css">
#div1,#div2,#div3 {
	display: none
}
label {
	font-weight: 500 !important;
}
</style>

<!-- Google reCaptcha -->
<!-- Disabling captcha for all forms invoked after logging in -->
<!--
<script src='https://www.google.com/recaptcha/api.js?onload=onLoadGoogleRecaptcha&render=explicit' async defer></script>
<script type='text/javascript'>
    function onLoadGoogleRecaptcha() {
        $("#captcha_method").val("google");
        grecaptcha.render("googleRecaptcha", { "sitekey" : "6LfpiFQUAAAAALdj6b1ZSgKJjh7LQkxXo_Xs2KNu" });
        $("#googleRecaptcha").show();
        $("#phpCaptcha").hide();
    }
</script>
-->

<!-- PHP Captcha -->
<!--
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
							<h2 class="primary-font">GROUP PAYMENT FORM</h2>
							<div class="sign-form">

								<div class="collapse1" id="online_payment1">
								<?php
									if ($total_fees > 0 && ($total_fees - $total_discount) <= $total_payment_received) {
								?>
									<div class="alert alert-info">
										<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
										<strong>Your have already paid for the currently selected participation options for your club members. You can increase
												the number of sections, if applicable, by changing the selections and can pay the difference in fees.
												Please note that it is not possible to reduce the number of sections of participation in digital and in print
												that may result in reduced payment.
										</strong>
									</div>
								<?php
									}
								?>
									<form method="post" action="#"  name="group_payment_form" id="group_payment_form" enctype="multipart/form-data" >
										<div id="form-preliminary-section">
											<!-- hidden fields -->
											<input type="hidden" name="yearmonth" id="yearmonth" value="<?=$contest_yearmonth;?>" >
											<input type="hidden" name="profile_id" id="profile_id" value="<?php echo $tr_user['profile_id'];?>">
											<input type="hidden" name="entrant_category" id="entrant_category" value="<?=$entrant_category;?>" >
											<input type="hidden" name="club_id" id="club_id" value="<?php echo $tr_user['club_id'];?>" >
											<input type="hidden" name="currency" id="currency" value="<?php echo $currency;?>" >
											<input type="hidden" name="fee_code" id="fee_code" value="<?php echo $fee_code;?>" >
											<input type="hidden" name="fee_group" id="fee_group" value="<?=$fee_group;?>" >
											<input type="hidden" name="discount_group" id="discount_group" value="<?=$discount_group;?>" >
											<input type="hidden" name="total_fees" id="total_fees" value="<?php echo $total_fees;?>" >
											<input type="hidden" name="total_discount" id="total_discount" value="<?php echo $total_discount;?>" >
											<input type="hidden" name="total_payment_received" id="total_payment_received" value="<?php echo $total_payment_received;?>" >

											<!-- Basic Information -->
											<div class="row">
												<div class="col-sm-10">
													<div class="form-group">
														<div class="row">
															<div class="col-sm-6">
																<label  for="club_name">Club/Group Name</label>
																<input type="text" name="club_name" class="form-control text-uppercase" id="club_name" value="<?php echo $tr_user['club_name'];?>" readonly>
															</div>
															<div class="col-sm-6">
																<label for="club_contact">Club/Group Contact</label>
																<input type="text" name="club_contact" class="form-control text-uppercase" id="club_contact" value="<?php echo $tr_user['club_contact'];?>" readonly>
															</div>
														</div>
													</div>
												</div>
												<div class="col-sm-2">
													<?php
														if ($tr_user['club_logo'] != "") {
													?>
													<img src="/res/club/<?php echo $tr_user['club_logo'];?>" style="max-height:60px" >
													<?php
														}
													?>
												</div>
											</div>
										</div>

										<!-- Edit Participation -->
										<div id="fee-table">
											<div class="row">
												<h4 class="text-color" style="padding-left : 15;" >Edit Participation for each member</h4>
												<div class="col-sm-12">
													<div class="row">
														<div class="col-sm-12">
															<table class="table" id="member-table" style="width:100%">
															<thead>
																<tr>
																	<th rowspan="2">Email</th>
																	<th colspan="2" class="text-center">Select Sections</th>
																	<th colspan="3" class="text-center">Amounts in <?php echo $currency;?></th>
																</tr>
																<tr>
																<?php
																	if ($contestDigitalSections > 0) {
																?>
																	<th class="text-center">DIGITAL</th>
																<?php
																	}
																	if ($contestPrintSections > 0) {
																?>
																	<th class="text-center">PRINT</th>
																<?php
																	}
																?>
																	<th class="text-right">FEES</th>
																	<th class="text-right">DISCOUNT</th>
																	<th class="text-right">PMT RECEIVED</th>
																</tr>
															</thead>
															<tbody>
																<?php
																	$club_total_fees = 0;
																	$club_total_discount = 0;
																	$sql = "SELECT * FROM coupon WHERE yearmonth = '$contest_yearmonth' AND club_id = '$club_id' ";
																	$qcn = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
																	$pc_list = array();
																	for($row_id = 1; $coupon = mysqli_fetch_array($qcn); ++ $row_id) {
																		$cid = sprintf("%04d", $row_id);		// Used to generate unique field IDs
																		$digital_participation_code = "DIGITAL_NONE";
																		$print_participation_code = "PRINT_NONE";
																		if ($coupon['participation_code'] != 'NONE') {
																			foreach(explode(",", $coupon['participation_code']) as $pc) {
																				$fee_found = array_intersect_key($fee_table, array($pc => 0));
																				foreach ($fee_found as $pc_found => $fee_row_found) {
																					switch ($fee_row_found['fee_type']) {
																						case 'ALL' :	$digital_participation_code = $all_digital_participation_code;
																										$print_participation_code = $all_print_participation_code;
																										break;
																						case 'DIGITAL':	$digital_participation_code = $pc_found;
																										break;
																						case 'PRINT' :	$print_participation_code = $pc_found;
																										break;
																					}
																				}
																			}
																		}

																		$digital_sections = $coupon['digital_sections'];
																		$print_sections = $coupon['print_sections'];
																		$fees = $coupon['fees_payable'];
																		$discount = $coupon['discount_applicable'];
																		$payment_received = $coupon['payment_received'];
																		// $coupon_rec = sprintf("%s|%s|%s|%d|%d|%.2f|%.2f|%.2f", $cid, $coupon['email'], implode(",", $participation_codes), $digital_sections, $print_sections, $fees, $discount, $payment_received);
																		// $inputs[$coupon['coupon_id']] = $coupon_rec;

																		$club_total_fees += $fees;
																		$club_total_discount += $discount;

																		// Check if a user has registered and uploaded pictures. If so, restrict choice of sections to > number of sections uploaded
																		$digital_uploaded_sections = 0;
																		$print_uploaded_sections = 0;
																		if ($coupon['profile_id'] != 0) {
																			$sql  = "SELECT section_type, COUNT(DISTINCT pic.section) AS num_uploaded_sections ";
																			$sql .= "FROM pic, section ";
																			$sql .= "WHERE pic.yearmonth = '$contest_yearmonth' ";
																			$sql .= "  AND pic.profile_id = '" . $coupon['profile_id'] . "' ";
																			$sql .= "  AND pic.yearmonth = section.yearmonth ";
																			$sql .= "  AND pic.section = section.section ";
																			$sql .= "GROUP BY section_type ";
																			$qnsec = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
																			while ($rnsec = mysqli_fetch_array($qnsec)) {
																				if ($rnsec['section_type'] == 'D')
																					$digital_uploaded_sections = $rnsec['num_uploaded_sections'];
																				if ($rnsec['section_type'] == 'P')
																					$print_uploaded_sections = $rnsec['num_uploaded_sections'];
																			}
																		}

																		// Retrieve Name if the user has a valid profile. Name will be displayed in place of email
																		$profile_name = "";
																		if ($coupon['profile_id'] != 0) {
																			$sql = "SELECT profile_name FROM profile WHERE profile_id = '" . $coupon['profile_id'] . "' ";
																			$qprf = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
																			if ($rprf = mysqli_fetch_array($qprf))
																				$profile_name = $rprf['profile_name'];
																		}
																?>
																<tr>
																	<div class="form-group">
																		<td>
																			<input type="hidden" name="participation_code[]" id="participation_code-<?php echo $cid;?>" value="<?php echo $coupon['participation_code'];?>" >
																			<input type="hidden" name="email[]" class="form-control" value="<?php echo $coupon['email'];?>" >
																			<input type="text" class="form-control" readonly
																					value="<?php echo ($profile_name != "") ? $profile_name : $coupon['email'];?>"
																					<?php echo (($digital_uploaded_sections + $print_uploaded_sections) > 0) ? "style='color:blue;'" : "";?>  >
																		</td>
																		<?php
																			if ($contestDigitalSections > 0) {
																		?>
																		<td>
																			<select name="digital_sections[]" class="form-control" id="digital_sections-<?php echo $cid;?>" >
																			<?php
																				foreach ($fee_table AS $fee_participation_code => $fee_row) {
																					if ($fee_row['fee_type'] == "DIGITAL" && $fee_row['digital_sections'] >= $digital_uploaded_sections ) {
																			?>
																				<option value="<?php echo $fee_row['digital_sections'];?>" <?php echo ($fee_row['digital_sections'] == $digital_sections) ? "selected" : "";?> >
																					<?php echo ($fee_row['digital_sections'] == 0) ? "None" : $fee_row['digital_sections'] . " Sections";?>
																				</option>
																			<?php
																					}
																				}
																			?>
																			</select>
																		</td>
																		<?php
																			}
																		?>
																		<?php
																			if ($contestPrintSections > 0) {
																		?>
																		<td>
																			<select name="print_sections[]" class="form-control" id="print_sections-<?php echo $cid;?>" value="<?php echo $print_sections;?>">
																			<?php
																				foreach ($fee_table AS $fee_participation_code => $fee_row) {
																					if ($fee_row['fee_type'] == "PRINT" && $fee_row['print_sections'] >= $print_uploaded_sections ) {
																			?>
																				<option value="<?php echo $fee_row['print_sections'];?>" <?php echo ($fee_row['print_sections'] == $print_sections) ? "selected" : "";?> >
																					<?php echo ($fee_row['print_sections'] == 0) ? "None" : $fee_row['print_sections'] . " Sections";?>
																				</option>
																			<?php
																					}
																				}
																			?>
																			</select>
																		</td>
																		<?php
																			}
																		?>

																		<td>
																			<input type="text" name="fees_payable[]" id="fees_payable-<?php echo $cid;?>" value="<?php echo $coupon['fees_payable'];?>"
																				class="form-control text-right" style="max-width:100px;" readonly >
																		</td>
																		<td>
																			<input type="text" name="discount_applicable[]" id="discount_applicable-<?php echo $cid;?>"
																				value="<?php echo $coupon['discount_applicable'];?>" class="form-control text-right" style="max-width:100px;" readonly>
																		</td>
																		<td>
																			<input type="text" name="payment_received[]" id="payment_received-<?php echo $cid;?>"
																				value="<?php echo $coupon['payment_received'];?>" class="form-control text-right" style="max-width:100px;" readonly>
																		</td>
																	</div>
																</tr>
																<?php
																	}
																?>
															</tbody>
															</table>
															<p><span style="color:blue;">Email/Name in blue</span> - Member has registered and uploaded pictures. Section selection options will be restricted !</p>
														</div>
													</div>
													<div class="divider" style="margin-top: 4px; margin-bottom: 8px;"></div>
													<div id="form-payment-section">		<!-- Required for collecting form data -->
														<h4 class="text-color">Group Payment Summary</h4>
														<div class="form-group">
															<div class="row">
																<div class="col-sm-6"></div>
																<div class="col-sm-2">
																	<label for="club_total_fees" class="pull-right">FEES</label>
																	<input name="club_total_fees" id="club_total_fees" class="form-control text-right"
																							value="<?php echo sprintf("%7.2f",$club_total_fees);?>" readonly>
																</div>
																<div class="col-sm-2">
																	<label for="club_total_discount" class="pull-right">DISCOUNT</label>
																	<input name="club_total_discount" id="club_total_discount" class="form-control text-right"
																							value="<?php echo sprintf("%7.2f",$club_total_discount);?>" readonly>
																</div>
																<div class="col-sm-2">
																	<label for="club_total_payable" class="pull-right">PAYABLE</label>
																	<input name="club_total_payable" id="club_total_payable" class="form-control text-right"
																							value="<?php echo sprintf("%7.2f", $club_total_fees - $club_total_discount);?>" readonly>
																</div>
															</div>
														</div>
														<div class="divider" style="margin-top: 4px; margin-bottom: 8px;"></div>
														<h4 class="text text-color">Payments received : </h4>
														<div class="row">
															<div class="col-sm-2"><b>Date</b></div>
															<div class="col-sm-8"><b>Reference</b></div>
															<div class="col-sm-2"><span class="pull-right"><b>Amount</b></span></div>
														</div>
														<div class="divider" style="margin-top: 4px; margin-bottom: 8px;"></div>
													<?php
														$sql  = "SELECT * FROM payment ";
														$sql .= " WHERE yearmonth = '$contest_yearmonth' ";
														$sql .= "   AND account = 'GRP' ";
														$sql .= "   AND link_id = '$club_id' ";
														$sql .= " ORDER BY datetime";
														$qpay = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
														$calculated_payment_received = 0.0;
														while ($rpay = mysqli_fetch_array($qpay)) {
															$calculated_payment_received += $rpay['amount'];
													?>
														<div class="row">
															<div class="col-sm-2"><?php echo substr($rpay['datetime'], 0, 4) . "-" . substr($rpay['datetime'], 4, 2) . "-" . substr($rpay['datetime'], 6, 2);?></div>
															<div class="col-sm-8"><?php echo $rpay['payment_ref'];?></div>
															<div class="col-sm-2"><span class="pull-right"><?php echo $rpay['amount'];?></span></div>
														</div>

													<?php
														}
													?>
														<div class="divider" style="margin-top: 4px; margin-bottom: 8px;"></div>
														<div class="form-group">
															<div class="row">
																<div class="col-sm-2"></div>
																<div class="col-sm-8"><span class="pull-right">TOTAL PAYMENT RECEIVED</span></div>
																<div class="col-sm-2">
																	<input type="text" class="form-control text-right" name="club_total_payment_received" id="club_total_payment_received"
																				value="<?php printf("%7.2f", $calculated_payment_received);?>" readonly>
																</div>
															</div>
														</div>
														<div class="divider" style="margin-top: 4px; margin-bottom: 8px;"></div>
														<div class="form-group">
															<div class="row">
																<div class="col-sm-2"></div>
																<div class="col-sm-8"><span class="pull-right"><b>AMOUNT TO BE PAID</b></span></div>
																<div class="col-sm-2">
																	<input type="text" class="form-control text-right" style="font-weight: bold; color: red;" name="club_total_payment_due" id="club_total_payment_due"
																				value="<?php printf("%7.2f", $club_total_fees - $club_total_discount - $calculated_payment_received);?>" readonly>
																</div>
															</div>
														</div>
													</div>
												</div>		<!-- Form Payment Section -->
											</div>  	<!-- END OF FEE OPTIONS, FEEs and DOSOUNTs -->
										</div>			<!-- / fee_table -->

                                        <!-- Set Default Validation Method of Captcha Validation to PHP Captcha. Will be changed to google on load -->
                                        <input type="hidden" name="captcha_method" id="captcha_method" value="php" />

										<div id="form-captcha-section">
											<h4 class="text-color">Complete the Payment</h4>
											<div class="form-group">
												<div class="row">
													<div class="col-sm-6">
														<label>Payment Network *</label>&nbsp;&nbsp;&nbsp;&nbsp;
														<?php
															if($currency == "USD") {
														?>
															<input type="radio" value="PayPal" name="gateway" id="gateway" checked>&nbsp;&nbsp;PayPal .
														<?php
															}
															else {
														?>
															&nbsp;&nbsp;<input type="radio" value="Instamojo" name="gateway" id="gateway_instamojo" checked>&nbsp;&nbsp;Instamojo
															<!-- &nbsp;&nbsp;<input type="radio" value="Paypal" name="gateway" id="gateway_paypal" >&nbsp;&nbsp;Paypal -->
														<?php
															}
														?>
													</div>
													<div class="col-sm-6">
														<div class="pull-right">
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
													</div>
												</div>
											</div>

											<div class="row">
												<div class="col-sm-9"></div>
												<div class="col-sm-3">
													<div class="pull-right">
														<button type="submit" class="btn btn-color" name="make_group_payment" id="make_group_payment"
																<?php echo ($club_total_fees > 0 && ($club_total_fees - $club_total_discount - $calculated_payment_received) < 0) ? "disabled" : "";?> >Update / Pay</button>
													</div>
												</div>
											</div>
										</div>
									</form>
									<br>
									<!-- Form used to submit encrypted Data -->
									<form method="post" action="op/update_group_payment.php"  name="submission_form" id="submission_form" enctype="multipart/form-data" >
										<input type="hidden" name="ypsd" id="ypsd" value="" >
									</form>
								</div> <!-- online__form -->
								<div class="alert alert-info">
										If the payment made by you is not appearing here, please do not make another payment. Please send us details through Contact Us page.
								</div>

							<?php
								if(($club_total_fees - $club_total_discount) < 0) {
							?>
								<div class="alert alert-danger">
									You can change the number of sections to participate for any member any time and pay the difference There is no provision to provide a refund.
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
	<script src="plugin/datatable/js/jquery.dataTables.min.js"></script>
	<script src="plugin/datatable/js/dataTables.bootstrap.min.js"></script>

	<!-- Page specific scripts -->
	<script>
    </script>

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
	// Global Variables prepared at the time of loading
	var fee_table = JSON.parse('<?php echo json_encode($fee_table);?>');		// Load the fee table into JS Object
	var currency = "<?php echo $currency;?>";

	// Fee Table related computed variable values
	var contest_digital_sections = Number(<?=$contestDigitalSections;?>);
	var contest_print_sections = Number(<?=$contestPrintSections;?>);
	// ALL_PRINT_DIGITAL
	var all_print_digital_participation_code = "<?php echo $all_print_digital_participation_code;?>";
	var all_print_digital_fees = Number(<?php echo $all_print_digital_fees;?>);
	var all_print_digital_discount = Number(<?php echo $all_print_digital_discount;?>);
	var all_print_digital_digital_sections = Number(<?php echo $all_print_digital_digital_sections;?>);
	var all_print_digital_print_sections = Number(<?php echo $all_print_digital_print_sections;?>);
	var there_is_all_print_digital_option = (contest_digital_sections > 0 && contest_print_sections > 0 && all_print_digital_participation_code != "");
	// ALL_DIGITAL
	var all_digital_participation_code = "<?php echo $all_digital_participation_code;?>";
	var all_digital_sections = "<?php echo $all_digital_sections;?>";
	var all_print_participation_code = "<?php echo $all_print_participation_code;?>";
	var all_print_sections = "<?php echo $all_print_sections;?>";

	// Computed Group Totals
	var club_total_fees = Number("<?php echo $club_total_fees;?>");
	var club_total_discount = Number("<?php echo $club_total_discount;?>");
	var payment_received = Number("<?php echo $calculated_payment_received;?>");

	// member_table to hold all DataTable Data
	var member_table;

    $(document).ready(function(){
		$('html, body').animate({ scrollTop: $("#myTab").offset().top - 100 }, 500);

		// Initialize Data Tables for Existing and New Members
        member_table = $('#member-table').DataTable({"lengthChange": false, "searching": false, "ordering": false});

		// Register a listener to each select statement in the member-table

	});

	// Register Listeners to the "select" fields in member-table"
	$(document).ready(function(){
		member_table.$("select").each(function(){
			$(this).change(redrawFeeTable);
		});
	})


	function redrawFeeTable(event){

		var section_select = event.target;
		var section_id = section_select.id;
		var cid = section_id.substr(-4);
		var is_digital = section_id.startsWith("digital");
		var is_print = section_id.startsWith("print");
		var present_fees = Number(member_table.$("#fees_payable-"+cid).val());
		var present_discount = Number(member_table.$("#discount_applicable-"+cid).val());
		if (contest_digital_sections > 0)
			var digital_sections = Number(member_table.$("#digital_sections-"+cid).val());
		else
			var digital_sections = 0;
		if (contest_print_sections > 0)
			var print_sections = Number(member_table.$("#print_sections-"+cid).val());
		else
			var print_sections = 0;

		// Compute fees & discount after the change
		var computed_fees = 0.0;
		var computed_discount = 0.0;
		var digital_participation_code = "";
		var print_participation_code = "";

		var participation_code, fee_row;

		var pc_list = [];

		for (participation_code in fee_table) {
			fee_row = fee_table[participation_code];
			if (contest_digital_sections > 0 && fee_row.fee_type == "DIGITAL" && fee_row.digital_sections == digital_sections) {
				computed_fees += Number(fee_row.fees);
				var temp_discount = 0;
				if (Number(fee_row.discount_percentage) != 0) {
					temp_discount = Number(fee_row.fees) * Number(fee_row.discount_percentage);
					temp_discount = Math.round(temp_discount * Math.pow(10, Number(fee_row.discount_round_digits))) / Math.pow(10, Number(fee_row.discount_round_digits));
				}
				else
					temp_discount = Number(fee_row.discount);
				computed_discount += temp_discount;
				digital_participation_code = participation_code;
				if (digital_sections != 0)
					pc_list[pc_list.length] = participation_code;
			}
			if (contest_print_sections > 0 && fee_row.fee_type == "PRINT" && fee_row.print_sections == print_sections) {
				computed_fees += Number(fee_row.fees);
				var temp_discount = 0;
				if (Number(fee_row.discount_percentage) != 0) {
					temp_discount = Number(fee_row.fees) * Number(fee_row.discount_percentage);
					temp_discount = Math.round(temp_discount * Math.pow(10, Number(fee_row.discount_round_digits))) / Math.pow(10, Number(fee_row.discount_round_digits));
				}
				else
					temp_discount = Number(fee_row.discount);
				computed_discount += temp_discount;
				print_participation_code = participation_code;
				if (print_sections != 0)
					pc_list[pc_list.length] = participation_code;
			}
		}


		// Chose ALL_PRINT_DIGITAL if fees for ALL_PRINT_DIGITAL is less
		if ( there_is_all_print_digital_option &&
				(	(digital_participation_code == all_digital_participation_code && print_participation_code == all_print_digital_participation_code) ||
					((computed_fees - computed_discount) >= (all_print_digital_fees - all_print_digital_discount)) 	)
			) {
			pc_list = [all_print_digital_participation_code];
			computed_fees = all_print_digital_fees;
			computed_discount = all_print_digital_discount;
			digital_sections = all_print_digital_digital_sections;
			print_sections = all_print_digital_print_sections;
			// Set adjusted fields
			member_table.$("#digital_sections-"+cid).val(digital_sections);
			member_table.$("#print_sections-"+cid).val(print_sections);
		}

		// Update fees in the row
		member_table.$("#fees_payable-"+cid).val(format_float(computed_fees, 7, 2));
		member_table.$("#discount_applicable-"+cid).val(format_float(computed_discount, 7, 2));
		member_table.$("#participation_code-"+cid).val(pc_list.toString());

		member_table.draw(false);

		// Recalculate Totals
		club_total_fees = club_total_fees - present_fees + computed_fees;
		club_total_discount = club_total_discount - present_discount + computed_discount;

		$("#club_total_fees").val(format_float(club_total_fees, 7, 2));
		$("#club_total_discount").val(format_float(club_total_discount, 7, 2));
		$("#club_total_payable").val(format_float(club_total_fees - club_total_discount, 7, 2));
		$("#club_total_payment_due").val(format_float(club_total_fees - club_total_discount - payment_received, 7, 2));

		// Enable Pay Now button
		if ((club_total_fees - club_total_discount - payment_received) >= 0)
			$("#make_group_payment").removeAttr("disabled");
		else
			$("#make_group_payment").attr("disabled", "");

	}


	</script>

	<!-- Form Validation -->
	<script src="plugin/validate/js/jquery.validate.min.js"></script>

	<!-- Custom Validation Functions -->
	<script src="custom/js/validate.js"></script>

	<!-- Form Validation -->
	<script>
	// Function to handle form submission

	$(document).ready(function() {
		// Validator for Group Creation Form
		$('#group_payment_form').validate({
			rules:{
			},
			messages:{
			},
			errorElement: "div",
			errorClass: "valid-error",
			submitHandler: function(form) {
				$('#loader_img').show();
				// Destroy datatable so that all form fields in all rows are exposed
				member_table.destroy();
				// form.submit();
				// Submit Encrypted Data
				let formData = new FormData(form);
				let ypsd = CryptoJS.AES.encrypt(jsonFormData(formData), "<?= SALONBOND;?>", { format: CryptoJSAesJson }).toString();
				$("#ypsd").val(ypsd);
				$("#submission_form").submit();
				/*****
				var formData = new FormData(form);
				//var formData = $("#form-preliminary-section input,select").serialize();
				$.ajax({
						url: "op/update_group_payment.php",
						type: "POST",
						data: formData,
						cache: false,
						processData: false,
						contentType: false,
						success: function(response) {
							$('#loader_img').hide();
							response = JSON.parse(response);
							if(response.success){
								var baseurl= 'http://'+window.location.host;
								swal("Updation Successful!", response.msg, "success");
								//document.location.href = baseurl+'/user_panel.php';
								setTimeout(function(){ document.location.href = baseurl+'/user_panel.php'; }, 2000);
							}
							else{
								// Restore Member Table
								member_table = $('#member-table').DataTable({"lengthChange": false, "searching": false, "ordering": false});
								swal("Updation Failed!", response.msg, "error");
								$('html, body').animate({ scrollTop: $("#myTab").offset().top-150 }, 500);
							}
						},
						error: function() {
							$('#loader_img').hide();
							// Restore Member Table
							member_table = $('#member-table').DataTable({"lengthChange": false, "searching": false, "ordering": false});
							swal("Updation Failed", "Updation Failed due to Server Error. Retry operation. Report to YPS if the error persists!", "error");
							$('html, body').animate({ scrollTop: $("#myTab").offset().top-150 }, 500);
						}
				});
				****/
				//return false;
			},
		});
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
