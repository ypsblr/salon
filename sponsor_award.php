<?php
// Login / Create sponsor using email
include_once("inc/session.php");
include_once("inc/sponsor_lib.php");

// Sponsor ID is required
if (empty($_REQUEST['spid']) || empty($_REQUEST['contest'])) {
	$_SESSION['err_msg'] = "Sponsor/Salon not selected. Click on the Sponsor button to start.";
	die();
}

// Get Sponsor Details
$sponsor_id = $_REQUEST['spid'];
$sql = "SELECT * FROM sponsor WHERE sponsor_id = '$sponsor_id' ";
$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
if (mysqli_num_rows($query) > 0)
	$sponsor = mysqli_fetch_array($query, MYSQLI_ASSOC);
else {
	$_SESSION['err_msg'] = "Sponsor Not Found. Report to YPS if the issue repeats.";
	die();
}

// Get Contest Details
$sponsor_yearmonth = empty($_REQUEST['contest']) ? $contest_yearmonth : $_REQUEST['contest'];
$sql = "SELECT * FROM contest WHERE yearmonth = '$sponsor_yearmonth' ";
$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
if (mysqli_num_rows($query) > 0)
	$sponsor_contest = mysqli_fetch_array($query, MYSQLI_ASSOC);
else {
	$_SESSION['err_msg'] = "Salon Not Found. Report to YPS if the issue repeats.";
	die();
}

// Optional Award ID
$sponsor_award_id = empty($_REQUEST['awid']) ? 0 : $_REQUEST['awid'];


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
						<h3 class="first-child text text-color">Sponsor an Award for <?=$sponsor_contest['contest_name'];?></h3>

						<p class="text text-primary lead">Sponsor: <?=$sponsor['sponsor_name'];?></p>

						<div class="divider"></div>

						<form role="form" method="post" action="#" id="sponsorship-form" name="sponsorship-form" enctype="multipart/form-data">
							<input type="hidden" name="yearmonth" id="yearmonth" value="<?=$sponsor_yearmonth;?>" >
							<input type="hidden" name="sponsor_id" id="sponsor_id" value="<?=$sponsor_id;?>" >

							<!-- Display List of available Awards -->
							<h4 class="text-color">List of Awards available for Sponsorship</h4>
							<p>Click on the Award to add to the Cart. Select number of awards using the input box next to the award.</p>

							<!-- Section-wise Picture Awards -->
							<p class="text-info"><big><b>Picture Awards</b></big></p>
							<div class="row" id="available_pic_award_list">
							<?php
								// Prepare a list of sections
								$award_list = spn_sponsorship_open_list($sponsor_yearmonth, "pic");
								$sec_list = array();
								foreach ($award_list AS $award_id => $award_row)
									$sec_list[$award_row['section']] = $award_row['section'];

								// Generate a Column for each section
								$columns = 0;
								foreach ($sec_list AS $award_section) {
							?>
								<div class="col-sm-3">
									<div style="margin-left: 4px; margin-right: 4px;">
										<p class="text-color"><b><?=$award_section;?></b></p><br>
										<?php
											foreach ($award_list AS $award_id => $award_row){
												if ($award_row['section'] == $award_section) {
										?>
										<div class="row" style="border-bottom: 1px solid #ccc" >
											<div class="col-sm-8">
												<a href="javascript: add_award_to_cart(<?=$award_id;?>, '<?=$award_row['section'];?>', '<?=$award_row['award_name'];?>', <?=$award_row['sponsorship_per_award'];?>)">
												<?php
													$award_line = $award_row['award_name'];
													if ($award_row['sponsored_awards'] > 1)
														$award_line .= "(" . ($award_row['sponsored_awards'] - $award_row['number_sponsored']) . "/" . $award_row['sponsored_awards'] . ")";
													echo $award_line;
												?>
												</a>
												<p class="text-muted small">Rs. <?php echo sprintf("%7.0f", $award_row['sponsorship_per_award']);?> each</p>
											</div>
											<div class="col-sm-4" style="padding-left: 5px; padding-right: 5px;">
												<?php
													if ($award_row['sponsored_awards'] > 1) {
														$field_name = "units_" . $award_id;
														$max_units = $award_row['sponsored_awards'] - $award_row['number_sponsored'];
												?>
												<input type="hidden"name="<?=$field_name;?>" id="<?=$field_name;?>" value="1" min="1" max="<?=$max_units;?>" />
												<a href="javascript: increment('<?=$field_name;?>')" style="margin:0; padding-right: 4px;" ><i class="fa fa-caret-up"></i></a>
												<span class="text text-color text-center" id="spin_<?=$field_name;?>" >1</span>
												<a href="javascript: decrement('<?=$field_name;?>')" style="margin:0; padding-left: 4px;" ><i class="fa fa-caret-down"></i></a>
												<?php
													}	// awards available > 1
												?>
											</div>
										</div>
										<?php
												} // if section matches
											}	// awards for this section
										?>
									</div>
								</div>
								<?php
									++ $columns;
									if ($columns % 4 == 0) {
										$columns = 0;
								?>
								<div class="clearfix"></div>
							<?php
									}	// 4 columns generated
								} // foreach section
							?>
							</div>
							<div class="divider"></div>

							<!-- ENTRY AWARDS -->
							<div class="row">
							<?php
								// Check for Availability of sponsorships for entry awards
								$award_list = spn_sponsorship_open_list($sponsor_yearmonth, "entry");
								if (sizeof($award_list) > 0) {
							?>
								<div class="col-sm-6">
									<p class="text-info"><big><b>Individual Awards</b></big></p>
							<?php
									foreach ($award_list as $award_id => $award_row) {
							?>
									<div class="row" style="border-bottom: 1px solid #ccc" >
										<div class="col-sm-8">
											<a href="javascript: add_award_to_cart(<?=$award_id;?>, '<?=$award_row['section'];?>', '<?=$award_row['award_name'];?>', <?=$award_row['sponsorship_per_award'];?>)">
											<?php
												$award_line = $award_row['award_name'];
												if ($award_row['sponsored_awards'] > 1)
													$award_line .= "(" . ($award_row['sponsored_awards'] - $award_row['number_sponsored']) . "/" . $award_row['sponsored_awards'] . ")";
												echo $award_line;
											?>
											</a>
											<p class="text-muted small">Rs. <?php echo sprintf("%7.0f", $award_row['sponsorship_per_award']);?> each</p>
										</div>
										<div class="col-sm-4" style="padding-left: 5px; padding-right: 5px;">
											<?php
												if ($award_row['sponsored_awards'] > 1) {
													$field_name = "units_" . $award_id;
													$max_units = $award_row['sponsored_awards'] - $award_row['number_sponsored'];
											?>
											<input type="hidden"name="<?=$field_name;?>" id="<?=$field_name;?>" value="1" min="1" max="<?=$max_units;?>" />
											<a href="javascript: increment('<?=$field_name;?>')" style="margin:0; padding-right: 4px;" ><i class="fa fa-caret-up"></i></a>
											<span class="text text-color text-center" id="spin_<?=$field_name;?>" >1</span>
											<a href="javascript: decrement('<?=$field_name;?>')" style="margin:0; padding-left: 4px;" ><i class="fa fa-caret-down"></i></a>
											<?php
												}	// awards available > 1
											?>
										</div>
									</div>
							<?php
									}
							?>
								</div>
							<?php
								}
							?>
							</div>

							<!-- CLUB AWARDS -->
							<div class="row">
							<?php
								// Check for Availability of sponsorships for entry awards
								$award_list = spn_sponsorship_open_list($sponsor_yearmonth, "club");
								if (sizeof($award_list) > 0) {
							?>
								<div class="col-sm-6">
									<p class="text-info"><big><b>Club Awards</b></big></p>
							<?php
									foreach ($award_list as $award_id => $award_row) {
							?>
									<div class="row" style="border-bottom: 1px solid #ccc" >
										<div class="col-sm-8">
											<a href="javascript: add_award_to_cart(<?=$award_id;?>, '<?=$award_row['section'];?>', '<?=$award_row['award_name'];?>', <?=$award_row['sponsorship_per_award'];?>)">
											<?php
												$award_line = $award_row['award_name'];
												if ($award_row['sponsored_awards'] > 1)
													$award_line .= "(" . ($award_row['sponsored_awards'] - $award_row['number_sponsored']) . "/" . $award_row['sponsored_awards'] . ")";
												echo $award_line;
											?>
											</a>
											<p class="text-muted small">Rs. <?php echo sprintf("%7.0f", $award_row['sponsorship_per_award']);?> each</p>
										</div>
										<div class="col-sm-4" style="padding-left: 5px; padding-right: 5px;">
											<?php
												if ($award_row['sponsored_awards'] > 1) {
													$field_name = "units_" . $award_id;
													$max_units = $award_row['sponsored_awards'] - $award_row['number_sponsored'];
											?>
											<input type="hidden"name="<?=$field_name;?>" id="<?=$field_name;?>" value="1" min="1" max="<?=$max_units;?>" />
											<a href="javascript: increment('<?=$field_name;?>')" style="margin:0; padding-right: 4px;" ><i class="fa fa-caret-up"></i></a>
											<span class="text text-color text-center" id="spin_<?=$field_name;?>" >1</span>
											<a href="javascript: decrement('<?=$field_name;?>')" style="margin:0; padding-left: 4px;" ><i class="fa fa-caret-down"></i></a>
											<?php
												}	// awards available > 1
											?>
										</div>
									</div>
							<?php
									}
							?>
								</div>
							<?php
								}
							?>
							</div>

							<!-- Cart of Awards -->
							<div class="row">
								<div class="col-sm-8" id="sponsorship_cart">
									<h4 class="text-color">Sponsorship Cart</h4>
									<!-- Print Headings -->
									<div class="row" style="border-bottom: 1px solid #ccc" >
										<div class="col-sm-8"><b><small>Award Name</small></b></div>
										<div class="col-sm-1"><p class="text-center"><b><small>No</small></b></p></div>
										<div class="col-sm-2"><p class="text-right"><b><small>Spons.Amt.</small></b></p></div>
										<div class="col-sm-1"></div>	<!-- For Actions -->
									</div>
									<!-- First Get Historical Data for the current contest -->
									<?php
										// Some Counters
										$sponsorship_payable = 0.0;
										$sponsorship_paid = 0.0;
										$index = 0;

										if ($spn_list = spn_get_sponsored_list($sponsor_id, $sponsor_yearmonth)) {
											foreach($spn_list AS $spn) {
												$sponsorship_payable += $spn['total_sponsorship_amount'];
												// debug_dump("spn", $spn, __FILE__, __LINE__);
									?>
									<div class="row" style="padding-top: 4px; border-bottom: 1px solid #ccc" id="row_<?=$index;?>" >
										<div class="col-sm-8">
											<div class="form-group">
												<p><?php echo $spn['section'] . " - " . $spn['award_name'];?></p>
												<input type="text" class="form-control" name="award_name_suffix[]" id="award_name_suffix_<?=$spn['award_id'];?>"
															value="<?=$spn['award_name_suffix'];?>" <?php echo ($spn['payment_received'] > 0.0) ? "readonly" : "";?> >
											</div>
										</div>
										<div class="col-sm-1"><p class="text-center"><?=$spn['number_of_units'];?></p></div>
										<div class="col-sm-2"><p class="text-right"><?php echo sprintf("%7.0f", $spn['total_sponsorship_amount']);?></p></div>
										<div class="col-sm-1">
										<?php
												if ($spn['payment_received'] == 0.0) {
										?>
											<a href="javascript: delete_sponsorship(<?=$index;?>, <?=$spn['total_sponsorship_amount'];?>)"><i class="fa fa-trash"></i></a>
										<?php
												}
										?>
										</div>	<!-- For Actions -->
										<!-- Hidden Variables to store Form Values -->
										<input type="hidden" name="award_id[]" id="award_id_<?=$index;?>" value="<?=$spn['award_id'];?>" >
										<input type="hidden" name="number_of_units[]" id="number_of_units_<?=$index;?>" value="<?=$spn['number_of_units'];?>" >
										<input type="hidden" name="total_sponsorship_amount[]" id="total_sponsorship_amount_<?=$index;?>" value="<?=$spn['total_sponsorship_amount'];?>" >
										<input type="hidden" name="payment_received[]" id="payment_received_<?=$index;?>" value="<?=$spn['payment_received'];?>" >
									</div>
									<?php
												++ $index;
											}
										}
									?>
									<!-- If this page was visited from Home Page with award_id provided as argument, add the award -->
									<?php
										if ($sponsor_award_id != 0) {
											$sql = "SELECT * FROM award WHERE yearmonth = '$sponsor_yearmonth' AND award_id = '$sponsor_award_id' ";
											$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
											if ($row = mysqli_fetch_array($query)) {
												$sponsorship_payable += $row['sponsorship_per_award'];
									?>
									<div class="row" style="border-bottom: 1px solid #ccc" id="row_<?=$index;?>" >
										<div class="col-sm-8">
											<div class="form-group">
												<p><?php echo $row['section'] . " - " . $row['award_name'];?></p>
												<input type="text" class="form-control" name="award_name_suffix[]" id="award_name_suffix_<?=$row['award_id'];?>" value="" >
											</div>
										</div>
										<div class="col-sm-1"><p class="text-center">1</p></div>
										<div class="col-sm-2"><p class="text-right"><?php echo sprintf("%7.0f", $row['sponsorship_per_award']);?></p></div>
										<div class="col-sm-1"><a href="javascript: delete_sponsorship(<?=$index;?>, <?=$row['sponsorship_per_award'];?>)"><i class="fa fa-trash"></i></a></div>	<!-- For Actions -->
										<!-- Hidden Variables to store Form Values -->
										<input type="hidden" name="award_id[]" id="award_id_<?=$index;?>" value="<?=$row['award_id'];?>" >
										<input type="hidden" name="number_of_units[]" id="number_of_units_<?=$index;?>" value="1" >
										<input type="hidden" name="total_sponsorship_amount[]" id="total_sponsorship_amount_<?=$index;?>" value="<?=$row['sponsorship_per_award'];?>" >
										<input type="hidden" name="payment_received[]" id="payment_received_<?=$index;?>" value="0.0" >
									</div>
									<?php
											++ $index;
											}
										}
									?>
								</div>
								<div class="col-sm-4" style="padding-left: 20px;">
									<h4 class="text-color">Payments</h4>
									<div class="row" style="border-bottom: 1px solid #ccc">
										<div class="col-sm-8"><p class="text-right"><b><small>Sponsorship Total</small></b></p></div>
										<div class="col-sm-4"><p class="text-right"><b><small><span id="sponsorship_payable"><?php echo sprintf("%7.0f", $sponsorship_payable);?></span></small></b></p></div>
									</div>
									<div class="row" style="border-bottom: 1px solid #ccc">
										<div class="col-sm-8"><b><small>Date</small></b></div>
										<div class="col-sm-4"><p class="text-right"><b><small>Paid</small></b></p></div>
									</div>
									<?php
										$sql  = "SELECT datetime, amount FROM payment ";
										$sql .= " WHERE yearmonth = '$sponsor_yearmonth' ";
										$sql .= "   AND account = 'SPN' ";
										$sql .= "   AND link_id = '$sponsor_id' ";
										$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
										$payment_received = 0.0;
										if (mysqli_num_rows($query) > 0) {
											while ($row = mysqli_fetch_array($query)) {
												$sponsorship_paid += $row['amount'];
												$payment_date = print_date(substr($row['datetime'], 0, 4) . "-" . substr($row['datetime'], 4, 2) . "-" . substr($row['datetime'], 4, 2));
									?>
									<div class="row" style="border-bottom: 1px solid #ccc">
										<div class="col-sm-8"><b><?=$payment_date;?></b></div>
										<div class="col-sm-4"><p class="text-right"><b><small><?php echo sprintf("%7.0f", $row['amount']);?></small></b></p></div>
									</div>
									<?php
											}
										}
										else {
									?>
									<div class="row" style="border-bottom: 1px solid #ccc">
										<div class="col-sm-8"><i>-- No Payments --</i></div>
									</div>
									<?php
										}
									?>
									<div class="row" style="border-bottom: 1px solid #ccc">
										<div class="col-sm-8"><p class="text-right"><b><small>Total Paid</small></b></p></div>
										<div class="col-sm-4"><p class="text-right"><b><small><?php echo sprintf("%7.0f", $sponsorship_paid);?></small></b></p></div>
									</div>
									<div class="row" style="border-bottom: 1px solid #ccc">
										<div class="col-sm-8"><p class="text-right"><b>Pay now</b></p></div>
										<div class="col-sm-4"><p class="text-right"><b><span id="sponsorship_due"><?php echo sprintf("%7.0f", $sponsorship_payable - $sponsorship_paid);?></span></b></p></div>
									</div>
									<!-- Some Hidden Variables to Help with Computations -->
									<input type="hidden" name="index" id="index" value="<?=$index;?>" >
									<input type="hidden" name="sum_sponsorship_payable" id="sum_sponsorship_payable" value="<?=$sponsorship_payable;?>" >
									<input type="hidden" name="sum_sponsorship_paid" id="sum_sponsorship_paid" value="<?=$sponsorship_paid;?>" >
									<input type="hidden" name="sum_sponsorship_due" id="sum_sponsorship_due" value="<?php echo $sponsorship_payable - $sponsorship_paid;?>" >
								</div>
							</div>
							<div class="divider"></div>


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

							<div class="form-group">
								<div class="row">
									<div class="col-sm-3"></div>
									<div class="col-lg-6 col-md-6 col-sm-6">
										<div class="g-recaptcha" data-sitekey="6LfpiFQUAAAAALdj6b1ZSgKJjh7LQkxXo_Xs2KNu"></div>
									</div>
									<div class="col-sm-3">
										<br>
										<button type="submit" class="btn btn-color pull-right" name="sponsor_awards" id="sponsor_awards" >Update &amp; Pay</button>
									</div>
								</div>
							</div>
						</form>		<!-- End of Main Form -->
						<form role="form" method="post" action="op/sponsor_award.php" id="submission_form" name="submission_form" >
							<input type="hidden" name="ypsd" id="ypsd" value="">
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

<!-- Templates for use in Javascript -->
<div id="template_cart_row" style="display:none;">
	<div class="row" style="padding-top: 4px; border-bottom: 1px solid #ccc" id="row_~index~" >
		<div class="col-sm-8">
			<div class="form-group">
				<p>~section~ - ~award_name~</p>
				<input type="text" class="form-control" name="award_name_suffix[]" id="award_name_suffix_~award_id~" value="" >
			</div>
		</div>
		<div class="col-sm-1"><p class="text-center">~number_of_units~</p></div>
		<div class="col-sm-2"><p class="text-right">~this_sponsorship~</p></div>
		<div class="col-sm-1"><a href="javascript: delete_sponsorship(~index~, ~this_sponsorship~)"><i class="fa fa-trash"></i></a></div>	<!-- For Actions -->
		<!-- Hidden Variables to store Form Values -->
		<input type="hidden" name="award_id[]" id="award_id_~index~" value="~award_id~" >
		<input type="hidden" name="number_of_units[]" id="number_of_units_~award_id~" value="~number_of_units~" >
		<input type="hidden" name="total_sponsorship_amount[]" id="total_sponsorship_amount_~index~" value="~this_sponsorship~" >
		<input type="hidden" name="payment_received[]" id="payment_received_~index~" value="0.0" >
	</div>
</div>

<!-- JS Global -->
<style>
	.valid-error{
		font-size: 12px;
	}
</style>

<!-- Handle Spinner -->
<script>
function increment(field) {
	var value = $("#"+field).val();
	var max = $("#"+field).prop("max");
	if (Number(value) < Number(max)) {
		++ value;
		$("#"+field).val(value);
		$("#spin_" + field).html(value);
	}
}

function decrement(field) {
	var value = $("#"+field).val();
	var min = $("#"+field).prop("min");
	if (Number(value) > Number(min)) {
		-- value;
		$("#"+field).val(value);
		$("#spin_" + field).html(value);
	}
}

</script>

<!-- Toggle New Club Form based on add_club checkbox -->
<script>
$(document).ready(function () {
	$("#add_sponsor").click(function() {
		if ($("#add_sponsor:checked").length > 0)
			$("#sponsor_name_fields").show();
		else
			$("#sponsor_name_fields").hide();
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

<!-- Get Sponsor Details When Selected -->
<script>
function add_award_to_cart(award_id, section, award_name, sponsorship_per_award) {
	var index = Number($("#index").val());
	var number_of_units = $("#units_" + award_id).val();
	if (number_of_units == undefined)
		number_of_units = 1;
	var this_sponsorship = Number(sponsorship_per_award) * number_of_units;
	var sum_sponsorship_payable = Number($("#sum_sponsorship_payable").val());
		sum_sponsorship_payable += this_sponsorship;
	var sum_sponsorship_paid = Number($("#sum_sponsorship_paid").val());
	var sum_sponsorship_due = sum_sponsorship_payable - sum_sponsorship_paid;

	$("#sum_sponsorship_payable").val(sum_sponsorship_payable);
	$("#sum_sponsorship_due").val(sum_sponsorship_due);
	$("#sponsorship_payable").html(sum_sponsorship_payable);
	$("#sponsorship_due").html(sum_sponsorship_due);

	var srch = ["award_id", "award_name", "index", "number_of_units", "this_sponsorship", "section"];
	var repl = [award_id, award_name, index, number_of_units, this_sponsorship, section];
	var html = $("#template_cart_row").html().fill_template(srch, repl);	// Fill Template by searching for srch members and replacing with equivalent repl
	$("#sponsorship_cart").append(html);
	$("#index").val(index + 1);
}
</script>

<script>
function delete_sponsorship(index, this_sponsorship) {
	var sum_sponsorship_payable = Number($("#sum_sponsorship_payable").val());
		sum_sponsorship_payable -= this_sponsorship;
	var sum_sponsorship_paid = Number($("#sum_sponsorship_paid").val());
	var sum_sponsorship_due = sum_sponsorship_payable - sum_sponsorship_paid;

	$("#sum_sponsorship_payable").val(sum_sponsorship_payable);
	$("#sum_sponsorship_due").val(sum_sponsorship_due);
	$("#sponsorship_payable").html(sum_sponsorship_payable);
	$("#sponsorship_due").html(sum_sponsorship_due);

	$("#row_" + index).remove();
}
</script>

<script>
    $(document).ready(function(){
		$('html, body').animate({ scrollTop: $("#myTab").offset().top-100 }, 500);
	});
</script>

<script>
	$("#sponsor_awards").click(function(e){
		// Prevent the main form from getting submitted
		e.preventDefault();

		// Perform Validations
		if ($("input[name='verified']:checked").length == 0) {
			swal("Updation Failed!", "Confirm correctness of details and Try again!", "error");
			return;
		}

		// Copy to submission form and submit
		let formData = new FormData($("#sponsorship-form").get(0));
		let ypsd = CryptoJS.AES.encrypt(jsonFormData(formData), "<?= SALONBOND;?>", { format: CryptoJSAesJson }).toString();
		$("#ypsd").val(ypsd);
		$("#submission_form").submit();

	});
</script>

</body>

</html>
<?php

?>
