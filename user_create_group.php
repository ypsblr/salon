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

if ($tr_user['club_fee_code'] == "") {	// No club_entry details
	$editmode = false;

	// default to current user's settings
	$club_entered_by = $tr_user['profile_id'];
	$currency = $tr_user['currency'];
	$payment_mode = "";
	$entrant_category = $tr_user['entrant_category'];
	$fee_code = "";
	$group_code = "";
	$minimum_group_size = "";

	// Determine fee_code based on current date
	// ----------------------------------------
	$sql  = "SELECT DISTINCT fee_code FROM fee_structure ";
	$sql .= "WHERE yearmonth = '$contest_yearmonth' ";
	$sql .= "  AND fee_start_date <= '" . DATE_IN_SUBMISSION_TIMEZONE . "' ";
	$sql .= "  AND fee_end_date >= '" . DATE_IN_SUBMISSION_TIMEZONE . "' ";
	$fc = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	// There is nothing open to select and fee_code has not been previously selected
	// Can't do anything further
	if (mysqli_num_rows($fc) == 0)
		handle_error("None of the sections are open now for selection and payment.", __FILE__, __LINE__);
	else {
		$rfc = mysqli_fetch_array($fc);
		$fee_code = $rfc['fee_code'];
	}
}
else {
	$editmode = true;
	// Can edit only if the club_entry was created by the same person
	if ($tr_user['club_entered_by'] != $tr_user['profile_id'])
		handle_error("Club was entered into this contest by another person. You cannot modify the entry", __FILE__, __LINE__);

	$club_entered_by = $tr_user['club_entered_by'];
	$currency = $tr_user['currency'];
	$payment_mode = $tr_user['club_payment_mode'];
	$entrant_category = $tr_user['club_entrant_category'];
	$fee_code = $tr_user['club_fee_code'];
	$group_code = $tr_user['club_group_code'];
	$minimum_group_size = $tr_user['club_minimum_group_size'];
}

// Determine if we can allow payment mode to be changed
// start by setting allow_payment_mode edit to false
$allow_payment_mode_edit = false;

if ($payment_mode == "GROUP_PAYMENT") {
	// If payment mode is Group Payment and no payment has been done, payment mode can be flipped
	$sql  = "SELECT SUM(payment_received) AS sum_payment_received FROM coupon ";
	$sql .= " WHERE yearmonth = '$contest_yearmonth' ";
	$sql .= "   AND club_id = '" . $tr_user['club_id'] . "' ";
	$tmpq = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$tmpr = mysqli_fetch_array($tmpq);
	if ($tmpr['sum_payment_received'] == 0)
		$allow_payment_mode_edit = true;
}
else {
	// If any user has registered and paid based on this we cannot allow payment_mode to be changed
	$sql  = "SELECT COUNT(*) AS num_entry FROM entry, coupon ";
	$sql .= " WHERE entry.yearmonth = '$contest_yearmonth' ";
	$sql .= "   AND coupon.yearmonth = entry.yearmonth ";
	$sql .= "   AND coupon.profile_id = entry.profile_id ";
	$sql .= "   AND entry.payment_received != 0.0 ";
	$tmpq = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$tmpr = mysqli_fetch_array($tmpq);
	if ($tmpr['num_entry'] == 0)
		$allow_payment_mode_edit = true;
}

// Prepare Lists
// -------------
// Entrant Category List using this user as a representative Club Member
$ec_list = ec_get_eligible_ec_list($contest_yearmonth, $tr_user);
if ($ec_list == false || sizeof($ec_list) == 0)
	handle_error("None of the Entrant Categories for this Salon match your profile. Please check the Eligibility Criteria under Salon Rules.", __FILE__, __LINE__);

// Create a list of Group Sizes for each entrant_category
$index = 0;
foreach($ec_list as $ec => $ec_row) {
	++ $index;
	// Set the first entrant_category as default selection if existing entrant_category is blank
	if ($entrant_category == "") {
		$entrant_category = $ec;
		$currency = $ec_row['currency'];
	}
	$ec_list[$ec]['key'] = "ec_" . $index;
	$sql  = "SELECT DISTINCT group_code, minimum_group_size, maximum_group_size FROM discount ";
	$sql .= " WHERE yearmonth = '$contest_yearmonth' ";
	$sql .= "   AND discount_code = 'CLUB' ";
	$sql .= "   AND fee_code = '$fee_code' ";
	$sql .= "   AND discount_group = '" . $ec_row['discount_group'] . "' ";
	$sql .= "   AND currency = '" . $ec_row['currency'] . "' ";
	$sql .= " ORDER BY minimum_group_size ";
	$dq = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$group_code_list = array();
	$gcindex = 0;
	while ($dr = mysqli_fetch_array($dq, MYSQLI_ASSOC)) {
		++ $gcindex;
		// Set first item as default group_code & minimum_group_size
		if ($group_code == "") {
			$group_code = $dr['group_code'];
			$minimum_group_size = $dr['minimum_group_size'];
		}
		$group_code_list[$dr['group_code']] = $dr;
		$group_code_list[$dr['group_code']]["key"] = "gc_" . $gcindex;
	}
	// Attach to ec_list
	$ec_list[$ec]['group_code_list'] = $group_code_list;
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
						<h3 class="first-child text text-color">Enter Club &amp; Generate Discount Coupons</h3>
						<hr>
						<form role="form" method="post" action="#" id="group-form" name="group-form" enctype="multipart/form-data">
							<input type="hidden" name="yearmonth" value="<?=$contest_yearmonth;?>" >
							<input type="hidden" name="profile_id" value="<?=$tr_user['profile_id'];?>" >
							<input type="hidden" name="user_email" value="<?=$tr_user['email'];?>" >
							<input type="hidden" name="country_id" value="<?=$tr_user['country_id'];?>" >
							<input type="hidden" name="cur_logo" value="<?=$tr_user['club_logo'];?>" >
							<input type="hidden" name="club_id" value="<?=$tr_user['club_id'];?>" >

							<!-- Edit Fields -->
							<input type="hidden" name="club_entered_by" value="<?=$club_entered_by;?>" >
							<input type="hidden" name="currency" value="<?=$currency;?>" id="currency" >
							<input type="hidden" name="fee_code" value="<?=$fee_code;?>" >
							<input type="hidden" name="group_code" value="<?=$group_code;?>" >
							<!-- <input type="hidden" name="minimum_group_size" value="<?=$minimum_group_size;?>" > -->
							<input type="hidden" name="discount_code" value="CLUB" >
							<input type="hidden" name="total_fees" id="total_fees" value="<?=$tr_user['club_total_fees'];?>" >
							<input type="hidden" name="total_discount" id="total_discount" value="<?=$tr_user['club_total_discount'];?>" >
							<input type="hidden" name="total_payment_received" id="total_payment_received" value="<?=$tr_user['club_total_payment_received'];?>" >

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
										<label for="club_name">Name of the Club *</label>
										<input type="text" name="club_name" class="form-control text-uppercase" id="club_name" readonly
												value="<?=$tr_user['club_name'];?>" >
									</div>
									<div class="col-sm-5">
										<label  for="club_contact">Name of the Contact Person *</label>
										<input type="text" name="club_contact" class="form-control text-uppercase" id="club_contact" readonly
												value="<?=$tr_user['club_contact'];?>" >
									</div>
								</div>
							</div>

<!--
							<div class="form-group">
								<div class="row">
									<div class="col-sm-12">
										<label  for="club_address">Mailing Address *</label>
										<input type="text" name="club_address" class="form-control text-uppercase" id="club_address" readonly
												value="<?=$tr_user['club_address'];?>" >
									</div>
								</div>
							</div>
-->

<!--
							<div class="form-group">
								<div class="row">
									<div class="col-sm-4">
										<label for="club_phone">Phone Number *</label>
										<input type="text" name="club_phone" class="form-control text-uppercase" id="club_phone" value="<?=$tr_user['club_phone'];?>" required >
									</div>
									<div class="col-sm-8">
										<label for="club_email">Email ID *</label>
										<input type="email" name="club_email" id="club_email" class="form-control" required
												value="<?=$tr_user['club_email'];?>"  >
									</div>
								</div>
							</div>
-->

<!--
							<div class="form-group">
								<div class="row">
									<div class="col-sm-6">
										<label  for="club_website">Club Website</label>
										<input type="text" name="club_website" class="form-control" id="club_website"
												value="<?=$tr_user['club_website'];?>" >
									</div>
									<div class="col-sm-6">
										<label for="new_club_logo">Upload Logo (JPEG max height 120px) </label>
										<input id="new_club_logo" name="new_club_logo" type="file" accept=".jpg,.jpeg" >
									</div>
								</div>
							</div>
-->

							<div class="form-group">
								<div class="row">
									<div class="col-sm-6">
										<label  for="entrant_category">Entrant Category *</label>
										<input type="hidden" name="entrant_category" value="<?=$tr_user['club_entrant_category'];?>" >
										<input name="entrant_category_name" value="<?=$tr_user['club_entrant_category_name'];?>" class="form-control" readonly>
<!--
										<select name="entrant_category" value="<?=$tr_user['entrant_category'];?>" id="entrant_category" class="form-control" >
										<?php
											foreach($ec_list as $ec => $ec_row) {
												//debug_dump("json:group_code_list", json_encode($ec_row['group_code_list']), __FILE__, __LINE__);
										?>
											 IMP: data-group-code-list value should be in single quotes to preserve double quoted JSON string
											<option value="<?=$ec_row['entrant_category'];?>" id="<?=$ec_row['key'];?>"
													data-currency="<?=$ec_row['currency'];?>"
													data-group-code-list='<?php echo json_encode($ec_row['group_code_list']);?>'
													<?php echo $entrant_category == $ec_row['entrant_category'] ? "selected" : "";?> >
												<?=$ec_row['entrant_category_name'];?>
											</option>
										<?php
											}
										?>
										</select>
-->
									</div>
									<div class="col-sm-6">
										<label for="minimum_group_size">Promised Group Size *</label>
										<input name="minimum_group_size" value="<?=$tr_user['club_minimum_group_size'];?>" class="form-control" readonly >
<!--
										<select name="group_code" value="<?=$group_code;?>" id="group_code" class="form-control" >
										<?php
											if ($entrant_category != "")
												foreach($ec_list[$entrant_category]['group_code_list'] as $gc => $gc_row) {
										?>
											<option
												id="<?=$gc_row['key'];?>"
												value="<?=$gc;?>"
												data-minimum-group-size = "<?=$gc_row['minimum_group_size'];?>"
												<?php echo $gc == $group_code ? "selected" : "";?>
											>
												<?=$gc;?> (<?=$gc_row['minimum_group_size'];?> to <?=$gc_row['maximum_group_size'];?> members)
											</option>
										<?php
											}
										?>
										</select>
-->
									</div>
								</div>
							</div>

							<div class="form-group">
								<div class="row">
									<div class="col-sm-6">
										<label for="payment_mode">Payment</label>
										<input type="hidden" name="payment_mode" value="<?=$payment_mode;?>" >
										<input name="payment_mode_text" value="<?php echo $payment_mode == 'GROUP_PAYMENT' ? 'Group Payment' : 'Individual Payment';?>"
													class="form-control" readonly>
									</div>
<!--
									<div class="col-sm-4">
										<label>
											<input type="radio" name="payment_mode" value="SELF_PAYMENT" id="payment_mode_self"
												<?php echo ($payment_mode == 'SELF_PAYMENT') ? 'checked' : '' ; ?>
												<?php echo $allow_payment_mode_edit ? '' : 'readonly'; ?>
											>
											By Members Individually
										</label>
									</div>
									<div class="col-sm-6">
										<label>
											<input type="radio" name="payment_mode" value="GROUP_PAYMENT" id="payment_mode_group"
												<?php echo ($tr_user['club_payment_mode'] == 'GROUP_PAYMENT') ? 'checked' : '' ; ?>
												<?php echo $allow_payment_mode_edit ? '' : 'readonly'; ?>
											>
											By Group Co-ordinator for all members
										</label>
									</div>
-->
								</div>
							</div>

							<h3 class="first-child text text-color">Participating Members</h3>
							<hr>
							<p>Add existing and new club members to the Salon as Participating Members. To be able to save and generate discount coupons,
								you should add the minimum number of members as per the Group Size selected by you. A confirmation email will be sent to
								each member. You can add more members any time later. Changing the Group Size later will not benefit members who have already
								registered and paid. The following rules apply for discounts:</p>
							<ul>
								<li>To avail the discount the member should register using the email address added by you.</li>
								<li>The fees and discount applicable will be determined when the member enters the Salon.</li>
							</ul>
							<!-- TABLE OF GROUP EMAILS -->
							<div class="row">
								<div class="col-sm-6">
									<h5 class="first-child text text-color">Add Existing Members</h5>
									<p class="text-muted small">* Shows only members not already added</p>
									<style>
									#member_list.table>tbody>tr>td, #new_members.table>tbody>tr>td, #existing_members.table>tbody>tr>td {
										padding: 0;
										font-size: 14px;
									}
									</style>
									<!-- Place for Actions Select/Deselect for members on page -->
									<!-- Show list of members -->
									<table id="member_list" class="table compact">
									<thead>
										<tr>
											<th>
												<h5 class="first-child text text-color">Member Name</h5>
											</th>
											<th>
												<a href="javascript: select_all_on_list()" >
													<h5 class="first-child text text-color"><i class="fa fa-user-check"></i> Select all on list</h5>
												</a>
												<a href="javascript: add_selected_on_list()" >
													<h5 class="first-child text text-color"><i class="fa fa-user-plus"></i> Add Selected</h5>
												</a>
											</th>
										</tr>
									</thead>
									<tbody id="list_of_club_members">
									<?php
										$sql  = "SELECT profile_id, profile_name, email FROM profile ";
										$sql .= " WHERE club_id = '" . $tr_user['club_id'] . "' ";
										$sql .= "   AND profile_id NOT IN ( ";
										$sql .= "			SELECT profile_id FROM coupon ";
										$sql .= "			 WHERE yearmonth = '$contest_yearmonth' ";
										$sql .= "			   AND coupon.profile_id != 0 ) ";
										$sql .= " ORDER BY profile_name ";
										$qmem_list = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
										while ($rmem = mysqli_fetch_array($qmem_list)) {

									?>
										<tr>
											<td><?=$rmem['profile_name'];?></td>
											<td>
												<div >
													<input type="checkbox" class="member-checkbox" id="mem_<?=$rmem['profile_id'];?>" value="<?=$rmem['profile_id'];?>"
															data-name="<?=$rmem['profile_name'];?>" data-email="<?=$rmem['email'];?>" style="width: 100%; margin-left: auto; margin-right: auto;" >
												</div>
											</td>
										</tr>
									<?php
										}
									?>
									</tbody>
									</table>
									<h5 class="first-child text text-color">Add New Members</h5>
									<!-- Copy Paste from Spreadsheet -->
									<div class="hpanel">
										<div class="panel-body">
											<div class="row">
												<div class="form-group col-sm-12">
													<label>Paste emails</label>
													<p>Emails can be one in each line or comma or blank separated</p>
													<textarea class="form-control" id="paste" rows="4" placeholder="Copy and Paste from Spreadsheet..."></textarea>
												</div>
												<div class="form-group col-sm-12">
													<a href="javascript: void(0)" id="paste_members" class="btn btn-color pull-right" >Add members <i class="fa fa-angle-double-right"></i></a>
												</div>
											</div>
										</div>
									</div>

									<!-- Add Single Member to the Group. Visible only in Edit Mode -->
									<div class="hpanel">
										<div class="panel-body">
											<div class="row">
												<div class="form-group col-sm-12">
													<label>Add Single Email</label>
													<input type="text" class="form-control" name="new_member_email" id="new_member_email" placeholder="Enter Member Email..." >
												</div>
											</div>
											<div class="row">
												<div class="form-group col-sm-12 pull-right">
													<a href="javascript: void(0)" id="add_member" class="btn btn-color pull-right" >Add member <i class="fa fa-angle-double-right"></i></a>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="col-sm-6 club_member_data">
									<!-- <h5 class="first-child text text-color">New Members</h5> -->
									<!-- Placeholder to insert new member additions -->
									<div id="new_member_inputs"></div>	<!-- Used to store hidden input statements related to additions and deletions -->
									<table id="new_members" class="table compact">
									<thead>
										<tr>
											<th>
												<h5 class="first-child text text-color">
													Member Changes
													<a style="margin-left: 40px; font-size: 12px; font-weight: normal;" href="javascript: void(0)" id="clear-new_member-list">Clear the list <i class="fa fa-trash"></i></a>
												</h5>
											</th>
											<th></th>
										</tr>
									</thead>
									<tbody id="new_group_members">
									</tbody>
									</table>
									<!-- Display Existing Members -->
									<hr>
									<!-- <h5 class="first-child text text-color">Existing Members</h5> -->
									<table id="existing_members" class="table compact">
									<thead>
										<tr>
											<th><h5 class="first-child text text-color">Existing Members</h5></th>
											<th><h5 class="text text-color">Registered?</h5></th>
											<th><h5 class="text text-color">Del</h5></th>
										</tr>
									</thead>
									<tbody id="existing_group_members">
									<?php
										$existing_member_email_list = array();
										if($editmode) {
											$club_id = $tr_user['club_id'];
											$sql  = "SELECT coupon.email, IFNULL(entry.profile_id, 0) AS profile_id, IFNULL(entry.payment_received, 0) AS payment_received ";
											$sql .= "  FROM coupon ";
											$sql .= "  LEFT JOIN entry ";
											$sql .= "         ON entry.yearmonth = coupon.yearmonth ";
											$sql .= "        AND entry.profile_id = coupon.profile_id ";
											$sql .= " WHERE coupon.yearmonth = '$contest_yearmonth' AND coupon.club_id = '$club_id' ORDER BY email ";
											$qcoupon = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
											while ($coupon = mysqli_fetch_array($qcoupon)) {
												$existing_member_email_list[] = $coupon['email'];
												$registered = ($coupon['payment_received'] == 0 ? "No" : "Yes");
									?>
										<tr>
											<td><?php echo $coupon['email'];?></td>
											<td><?php echo $registered;?></td>
											<td><a href="javascript:delete_member('<?php echo $coupon['email'];?>', <?=$coupon['payment_received'];?>)"><i class="fa fa-trash"></i></a>
										</tr>
									<?php
											}
										}
//										else {
//											// Add the creating user's email for validation etc.
//											$existing_member_email_list[] = $tr_user['email'];
									?>
<!--
										<tr>
											<td><?php //echo $tr_user['email'];?></td>
											<td>Yes</td>
											<td><a href="javascript:delete_member('<?php //echo $tr_user['email'];?>', true)"><i class="fa fa-trash"></i></a>
										</tr>
-->
									<?php
//										}
									?>
									</tbody>
									</table>
									<input type="hidden" id="existing_member_email_list" value="<?php echo implode(",", $existing_member_email_list);?>" >
								</div>
							</div>

							<div class="form-group">
								<div class="row">
									<div class="col-sm-12">
										<div class="checkbox pull-right">
											<label>
												<input type="checkbox" name="verified" id="verified" value="1" required>
												<b>I confirm that the above members have requested to participate *</b>
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
										<!-- Blocking Recaptcha in Logged-in forms
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
										<button type="submit" class="btn btn-color pull-right" name="generate_discounts" id="generate_discounts">Generate Discounts</button>
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

		// Initialize Data Tables for Existing and New Members
        $('#member_list').dataTable({"lengthChange": false, "searching": false, "ordering": false});
        $('#existing_members').dataTable({"lengthChange": false, "searching": false, "ordering": false});
        $('#new_members').dataTable({"lengthChange": false, "searching": false, "ordering": false});
	});
    </script>

	<script>
	// Common functions
	function email_exists_in_list(email) {
		// Create a list of emails already existing or have recently been added to eliminate duplicates
		var existing_members = $("#existing_member_email_list").val().split(",");		// create an array of existing member emails
		$(".new_member").each(function() {
			existing_members.push($(this).val());										// Add recently added members to this list
		});
		return existing_members.includes(email);
	}

	// Append Data to New Member List
	function append_emails_to_list(html, inputs) {
		if (html == "" || inputs == "") {
			swal("None Selected!", "No member has been selected. Nothing to add.", "info");
		}
		else {
			$("#new_members").DataTable().destroy();		// Make all rows visible
			$("#new_group_members").html($("#new_group_members").html() + html);	// Append to the Table
			$("#new_member_inputs").html($("#new_member_inputs").html() + inputs);	// Add to the hidden list of inputs
			$("#new_members").dataTable({"lengthChange": false, "searching": false, "ordering": false});	// Recreate the Data Table
		}
	}
	</script>

	<!-- Make all checkboxes of members checked -->
	<script>
	function select_all_on_list() {
		$(".member-checkbox").prop("checked", true);
	}
	function add_selected_on_list() {
		var html = "";
		var inputs = "";
		$(".member-checkbox:checked").each(function() {
			if (! email_exists_in_list($(this).data('email'))) {
				html += "<tr><td>" + $(this).data('email') + "</td><td>ADD</td></tr>";
				inputs += "<input type='hidden' name='member_email[]' class='new_member' value='" + $(this).data('email') + "'>";
			}
		});
		// Add to the table and to the inputs list
		append_emails_to_list(html, inputs);

		// Uncheck all
		$(".member-checkbox").prop("checked", false);
	}
	</script>

	<script>
	// Regular expression to extract emails from the text pasted
	function extractEmails (text) {
		return text.match(/([a-zA-Z0-9._-]+@[a-zA-Z0-9._-]+\.[a-zA-Z0-9._-]+)/gi);
	}

	// Adds New Members from list pasted
	$("#paste_members").click(function () {
		var html = "";
		var inputs = "";		// stores hidden inputs statements separately so that table row visibility does not impact passing of members to generate_discount.php
		//var lines = $("#paste").val().split("\n");
		var emails = $("#paste").val().match(/([a-zA-Z0-9._-]+@[a-zA-Z0-9._-]+\.[a-zA-Z0-9._-]+)/gi);

		// Eliminate duplicate emails
		emails = emails.sort();
		var compare_email = "";
		emails = emails.filter(function(this_email){
					if (this_email == compare_email)
						return false;
					else {
						compare_email = this_email;
						return true;
					}
				});

		// Create a list of emails already existing or have recently been added to eliminate duplicates
		var i = 0;
		for (i = 0; i < emails.length; ++i) {
			if (! email_exists_in_list(emails[i])) {
				html += "<tr><td>" + emails[i] + "</td><td>ADD</td></tr>";
				inputs += "<input type='hidden' name='member_email[]' class='new_member' value='" + emails[i] + "'>";
			}
		}

		// Add to the table and to the inputs list
		append_emails_to_list(html, inputs);
		$("#paste").val("");	// clear pasted text
	});
	</script>

	<script>
	// Add a singe new email to the table
	$("#add_member").click(function () {
		email = $("#new_member_email").val();

		if (email.trim() != "" && (! email_exists_in_list(email))) {
			html = "<tr><td>" + email.trim() + "</td><td>ADD</td></tr>";
			input = "<input type='hidden' name='member_email[]' class='new_member' value='" + email.trim() + "'>";
			append_emails_to_list(html, input);
		}
		$("#new_member_email").val("");		// clear the field
	});
	</script>

	<script>
	// Clear the New Member List
	$("#clear-new_member-list").click(function() {
		$("#new_members").DataTable().destroy();
		$("#new_group_members").html("");	// clear tbody
		$("#new_member_inputs").html("");	// Clear the hidden inputs list
		$("#new_members").dataTable({"lengthChange": false, "searching": false, "ordering": false});

	});
	</script>

	<script>
	function delete_member(email, payment_received) {
		if (payment_received == 0) {
			html = "<tr><td>" + email + "</td><td>DEL</td></tr>";
			input = "<input type='hidden' name='delete_email[]' class='delete_member' value='" + email + "'>";
			$("#new_members").DataTable().destroy();
			$("#new_group_members").html($("#new_group_members").html() + html);	// append at the end
			$("#new_member_inputs").html($("#new_member_inputs").html() + input);	// Add to the list of inputs
			$("#new_members").dataTable({"lengthChange": false, "searching": false, "ordering": false});
		}
		else
			swal("Cannot Delete", "Cannot Delete.  Member has already paid.", "error");
	}
	</script>

	<script>
		$("#entrant_category").change(function() {
			// Update Currency
			var currency = $("#entrant_category :selected").attr("data-currency");
			$("#currency").val(currency);

			// Change Group List to match entrant_category
			var group_code_list = JSON.parse($("#entrant_category :selected").attr("data-group-code-list"));	// Returns as an object of objects
			const group_code = $("#group_code").val();
			$("#group_code").empty();		// Clear all options
			var index = 0;
			Object.values(group_code_list).forEach(function (gc) {
				$("#group_code").append(
					$("<option></option>")
					.attr("id", gc.key)
					.attr("value", gc.group_code)
					.attr("data-minimum-group-size", gc.minimum_group_size)
					.text(gc.group_code + " (" + gc.minimum_group_size + " to " + gc.maximum_group_size + " members)")
				);
				if (gc.group_code == group_code)
					$("#" + gc.key).attr("selected", "selected");

			})
		});
	</script>

	<script>
		// Set minimum_group_size hidden form field for saving
		$("#group_code").change(function(){
			var minimum_group_size = $("#group_code :selected").attr("data-minimum-group-size");
			$("#minimum_group_size").val(minimum_group_size);
		});
	</script>

	<!-- Form Validation -->
	<script src="plugin/validate/js/jquery.validate.min.js"></script>

	<!-- Custom Validation Functions -->
	<script src="custom/js/validate.js"></script>


	<!-- Form Validation -->
	<script>
	// Function to handle form submission
	function save_discounts(form) {
		//form.submit();
		// var formData = new FormData(form);
		var formData = encryptFormData(new FormData(form));
		$('#loader_img').show();
		$.ajax({
				url: "ajax/generate_discount.php",
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
							title: "Updations Completed",
							text: response.msg,
							icon: "success",
						})
						.then(function () { document.location.href = baseurl + '/user_panel.php'; });		// Save if Proceed button is pressed
						//swal("Updation Successful!", response.msg, "success")
						//	.then(function(){ document.location.href = baseurl+'/user_panel.php'; });
						//document.location.href = baseurl+'/user_panel.php';
						//setTimeout(function(){ document.location.href = baseurl+'/user_panel.php'; }, 2000);
					}
					else{
						swal("Updation Failed!", response.msg, "error");
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
//				club_name:{ required:true, },
//				club_contact:{ required:true, },
//				club_phone:{
//					required: true,
//					number:true,
//					minlength: 8,
//					maxlength: 15,
//				},
//				club_email:{
//					required:true,
//					email:true,
//				},
//				club_website: {
//					url: true
//				},
//				club_address:{
//					required:true,
//					minlength: 11,
//				},
				new_member_email:{
					email:true,
					in_list: function () {
							var member_list = $("#existing_member_email_list").val().split(",");	// Existing Member List comma separated
							$(".new_member").each(function() {
								member_list.push($(this).val());		// Add newly added members to this list
							});
							return member_list;
					},
				},
//				entrant_category : { required : true, },
//				group_code : { required : true, },
			},
			messages:{
//				club_name:{
//					required:'Club Name is required',
//				},
//				club_name:{
//					required:'Name of Club Contact Person is required',
//				},
//				club_phone:{
//					required:'Please enter phoneno.',
//					number: "Please enter valid phone number.",
//					minlength: "Phone number must be at least 8 digit long including STD code.",
//					maxlength: "Phone number should not be greater than 15 digit long.",
//				},
//				club_email:{
//					required:'Please enter email.',
//					email: "Please enter valid email.",
//				},
//				club_website: {
//					url: "Website URL not in proper format",
//				},
//				club_address:{
//					required:'Please enter Address.',
//					minlength: "Address must be at least 11 characters long.",
//				},
				new_member_email:{
					required:'Please enter email.',
					email: "Please enter valid email.",
					remote: "Email already registered",
					not_exist: "Email already added to the club",
				},
//				entrant_category : { required : "Select a participation option for members" },
//				group_code : { required : "Select one of the group sizes", },
			},
			errorElement: "div",
			errorClass: "valid-error",
			submitHandler: function(form) {
				// Get minimum_group_size selected
				var minimum_group_size = $("#minimum_group_size").val();
				var existing_members = $("#existing_member_email_list").val().split(",");		// create an array of existing member emails
				$(".new_member").each(function() {
					existing_members.push($(this).val());										// Add recently added members to this list
				});
				var number_of_emails = existing_members.length;									// New Members added
				var number_of_email_deletes = $(".delete_member").length;
				if ((number_of_emails - number_of_email_deletes)  < minimum_group_size ) {
					swal("Minimum emails needed!", 'Minimum ' + minimum_group_size + ' emails are required to be eligible for Club/Group Discount.', "warning");
					$('html, body').animate({ scrollTop: $("#myTab").offset().top-150 }, 500);
				}
//				else if ( (! $("#payment_mode_self").prop("checked")) && (! $("#payment_mode_group").prop("checked"))) {
//					swal("Payment Mode", "Select one of the Payment Modes", "warning");
//					$('html, body').animate({ scrollTop: $("#myTab").offset().top-150 }, 500);
//				}
				else {
					if (number_of_email_deletes > 0 && Number($("#total_payment_received").val()) > 0.0) {
						swal({
							title: "Confirm Deletions?",
							text: "By deleting members, payments made by you may exceed fees payable. Refund of fees is not possible. Please confirm if you want to proceed!",
							icon: "warning",
							showCancelButton: true,
							confirmButtonColor: '#00BAFF',
							cancelButtonColor: '#696969',
							confirmButtonText: 'Proceed',
							dangerMode: true,
						})
						.then(function () { save_discounts(form); });		// Save if Proceed button is pressed
					}
					else {
						save_discounts(form);
					}
				}
			},
		});
	});

	</script>

</body>

</html>
