<?php
include_once("inc/categories.php");
include_once("inc/user_lib.php");
$php_file = basename($_SERVER['PHP_SELF']);

function debug_to_console($data) {
    $output = $data;
    if (is_array($output))
        $output = implode(',', $output);

    echo "<script>console.log('Debug Objects: " . $output . "' );</script>";
}

function user_uploads_open() {
    debug_to_console('in user_uploads_open');
	global $DBCON;
	global $contest_yearmonth;

	$sql = "SELECT MAX(submission_last_date) AS submission_last_date FROM section WHERE yearmonth = '$contest_yearmonth' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_num_rows($query) > 0) {
		$row = mysqli_fetch_array($query);
		$result = (DATE_IN_SUBMISSION_TIMEZONE <= $row['submission_last_date']);
		debug_to_console($result);
		return $result;
	}
	else
	{
        debug_to_console('returning true');
		return false;
	}
}

function get_contest_list() {
	global $DBCON;

	$sql  = "SELECT * FROM contest ORDER BY yearmonth DESC ";
	// $sql .= " WHERE registrationStartDate <= '" . DATE_IN_SUBMISSION_TIMEZONE . "' ";
	// $sql .= "   AND registrationLastDate >= '" . DATE_IN_SUBMISSION_TIMEZONE . "' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$contest_list = array();
	while ($row = mysqli_fetch_array($query))
		$contest_list[$row['yearmonth']] = $row;

	return $contest_list;
}

function menu_list($tr_user) {
	global $DBCON;
	global $registrationLastDate;
	global $resultsReady;
	global $contest_yearmonth;
	global $contestHasDiscounts;
	global $catalogReady;
	global $catalogPriceInINR;
	global $catalogPriceInUSD;
	global $catalogOrderLastDate;

	// Blacklist Restriction status
	$blacklist_active = ($tr_user['blacklist_match'] != "" && $tr_user['blacklist_exception'] == 0);

	// Determine if there are matching entrant categories
	$ec_list = ec_get_eligible_ec_list($contest_yearmonth, $tr_user);

	$menu["DB"]   = array( "name" => "Dashboard", "icon" => "fa-user", "link" => "/user_panel.php", "state" => "", "reason" => "" );
	$menu["EDIT"] = array( "name" => "Edit Profile", "icon" => "fa-edit", "link" => "/user_update_panel.php", "state" => "", "reason" => "" );
	$menu["REG"]   = array( "name" => "Participate in this Salon", "icon" => "fa-registered", "link" => "/user_register.php", "state" => "", "reason" => "" );
	$menu["PAY"]   = array( "name" => "Select Sections & Pay", "icon" => "fa-credit-card", "link" => "/user_payment.php", "state" => "", "reason" => "" );
	$menu["UPLD"]   = array( "name" => "Upload", "icon" => "fa-upload", "link" => "/user_upload_pic.php", "state" => "", "reason" => "" );
	$menu["GRD"]   = array( "name" => "Request Group Discount", "icon" => "fa-at", "link" => "/user_request_discount.php", "state" => "", "reason" => "" );
	$menu["GRP"]   = array( "name" => "Generate Group Discount", "icon" => "fa-users", "link" => "/user_create_group.php", "state" => "", "reason" => "" );
	$menu["GPAY"]   = array( "name" => "Group Payment", "icon" => "fa-credit-card", "link" => "/group_payment.php", "state" => "", "reason" => "" );
	$menu["RES"]   = array( "name" => "My Results", "icon" => "fa-file", "link" => "/user_results.php", "state" => "", "reason" => "" );
	$menu["CTLG"]   = array( "name" => "Order Catalog", "icon" => "fa-book", "link" => "/catalog_order.php", "state" => "", "reason" => "" );
	$menu["CLUB"]   = array( "name" => "My Club Results", "icon" => "fa-scroll", "link" => "/user_club_results.php", "state" => "", "reason" => "" );
	$menu["HIST"]   = array( "name" => "My History", "icon" => "fa-history", "link" => "/user_history.php", "state" => "", "reason" => "" );

    debug_to_console($menu);
    
	// Now Let us change the state as desired

	// Dashboard - Always visible

	// Edit Profile - Always visible

	// Register - Visible only of the current contest is open for registration
	// Modification dt: 2020/11/02 - Close registration 15 minutes before closing time
	// ========
	
	debug_to_console("console reg");
	debug_to_console($menu["REG"]["state"]);
	if ($menu["REG"]["state"] == "hidden" || $menu["REG"]["state"] == "disabled") {
		$menu["REG"]["reason"] = "This menu option has been hidden/disabled by the Administrator.";
	}
	else if ($blacklist_active) {
		$menu["REG"]["state"] = "disabled";
		$menu["REG"]["reason"] = "User in Blacklist published by a patronage organization. Contact Salon Chairman if it is not correct.";
	}
	else if ( REGISTRATION_CLOSED ) {
		$menu["REG"]["state"] = "disabled";
		$menu["REG"]["reason"] = "Registration for this Salon closeds 15 minutes before midnight of " . print_date($registrationLastDate) . ". ";
	}
	else if ($ec_list == false) {
		$menu["REG"]["state"] = "disabled";
		$menu["REG"]["reason"] = "There are no matching Entrant Categories on this Salon. Check Who can participate on the home page";
	}
	else if ($tr_user['entrant_category'] != "" && $tr_user['payment_received'] > 0) {
		$menu["REG"]["state"] = "disabled";
		$menu["REG"]["reason"] = "You have already registered for the Salon and Paid.";
	}

	debug_to_console("console pay");
	debug_to_console($menu["PAY"]["state"]);
	// Select & Pay only visible if the current contest is open for upload and user has registered
	// ============
	if ($menu["PAY"]["state"] == "hidden" || $menu["PAY"]["state"] == "disabled") {
		$menu["PAY"]["reason"] = "This menu option has been hidden/disabled by the Administrator.";
	}
	else if ($blacklist_active) {
		$menu["PAY"]["state"] = "disabled";
		$menu["PAY"]["reason"] = "User in Blacklist published by a patronage organization. Contact Salon Chairman if it is not correct.";
	}
	else if (user_uploads_open() == false) {
		$menu["PAY"]["state"] = "disabled";
		$menu["PAY"]["reason"] = "All Sections in this Salon is closed for Uploads.";
	}
	else if ($ec_list == false) {
		$menu["PAY"]["state"] = "disabled";
		$menu["PAY"]["reason"] = "There are no matching Entrant Categories on this Salon. Check Who can participate on the home page";
	}
	else if($tr_user['entrant_category'] == "") {
		$menu["PAY"]["state"] = "disabled";
		$menu["PAY"]["reason"] = "You can make payment after Registering for the Salon.";
	}
	else if($tr_user['fee_waived'] == "1") {
		$menu["PAY"]["state"] = "disabled";
		$menu["PAY"]["reason"] = "Salon Fees waived for your category. You can proceed to Upload.";
	}
	else if($tr_user['club_payment_mode'] == "GROUP_PAYMENT") {
		$menu["PAY"]["state"] = "disabled";
		$menu["PAY"]["reason"] = "Your Club has opted for Group Payment by each participant.";
	}

	debug_to_console("console grd");
	debug_to_console($menu["GRD"]["state"]);
	// Request Group Discount is visible only if (a) Registration is Open & (b) User is a member of a Club & (c) User has registred for the salon & (d) there are discounts
	// ======================
	if ($menu["GRD"]["state"] == "hidden" || $menu["GRD"]["state"] == "disabled") {
		$menu["GRD"]["reason"] = "This menu option has been hidden/disabled by the Administrator.";
	}
	else if ($blacklist_active) {
		$menu["GRD"]["state"] = "disabled";
		$menu["GRD"]["reason"] = "User in Blacklist published by a patronage organization. Contact Salon Chairman if it is not correct.";
	}
	else if($tr_user['yps_login_id'] != "") {
		$menu["GRD"]["state"] = "hidden";
		$menu["GRD"]["reason"] = "Not available for YPS Members";
	}
	else if(! $contestHasDiscounts){
		$menu["GRD"]["state"] = "hidden";
		$menu["GRD"]["reason"] = "This Salon does not have any discount scheme.";
	}
	else if (DATE_IN_SUBMISSION_TIMEZONE > $registrationLastDate) {
		$menu["GRD"]["state"] = "disabled";
		$menu["GRD"]["reason"] = "This Salon was closed on " . print_date($registrationLastDate) . ". ";
	}
	else if($tr_user['club_id'] == "0") {
		$menu["GRD"]["state"] = "disabled";
		$menu["GRD"]["reason"] = "You must be a member of a Club/Group to Request Discount. Edit Profile and add a Club.";
	}
	else if ($tr_user['entrant_category'] == "") {
		$menu["GRD"]["state"] = "disabled";
		$menu["GRD"]["reason"] = "You must first register yourself for the Salon before requesting a discount.";
	}
	else if($tr_user['club_entered_by'] != "") {
		$menu["GRD"]["state"] = "disabled";
		$menu["GRD"]["reason"] = "The Club/Group has already registered for Discount.";
	}
	else if ($ec_list == false) {
		$menu["GRD"]["state"] = "disabled";
		$menu["GRD"]["reason"] = "There are no matching Entrant Categories on this Salon. Check Who can participate on the home page";
	}



	debug_to_console("console grp");
	debug_to_console($menu["GRP"]["state"]);
	// Generate Group Discount is visible only if (a) Registration is Open & (b) User is a member of a Club, (c) there are discounts & (d) user is club_coordinator
	// =======================
	if ($menu["GRP"]["state"] == "hidden" || $menu["GRP"]["state"] == "disabled") {
		$menu["GRP"]["reason"] = "This menu option has been hidden/disabled by the Administrator.";
	}
	else if ($blacklist_active) {
		$menu["GRP"]["state"] = "disabled";
		$menu["GRP"]["reason"] = "User in Blacklist published by a patronage organization. Contact Salon Chairman if it is not correct.";
	}
	else if($tr_user['yps_login_id'] != "") {
		$menu["GRP"]["state"] = "hidden";
		$menu["GRP"]["reason"] = "Not available for YPS Members";
	}
	else if(! $contestHasDiscounts){
		$menu["GRP"]["state"] = "hidden";
		$menu["GRP"]["reason"] = "This Salon does not have any discount scheme.";
	}
	else if (DATE_IN_SUBMISSION_TIMEZONE > $registrationLastDate) {
		$menu["GRP"]["state"] = "disabled";
		$menu["GRP"]["reason"] = "This Salon was closed on " . print_date($registrationLastDate) . ". ";
	}
	else if ($tr_user['club_group_code'] == "") {
		$menu["GRP"]["state"] = "disabled";
		$menu["GRP"]["reason"] = "No discount has been set up for this group.";
	}
	else if($tr_user['club_id'] == "0") {
		$menu["GRP"]["state"] = "disabled";
		$menu["GRP"]["reason"] = "You must be a member of a Club to Generate Discount. Edit Profile and add a Club.";
	}
	else if($tr_user['club_entered_by'] != $tr_user['profile_id']) {
		$menu["GRP"]["state"] = "disabled";
		$menu["GRP"]["reason"] = "You must be the co-ordinator of the Club/Group to Generate Discount. ";
	}
	else if ($ec_list == false) {
		$menu["GRP"]["state"] = "disabled";
		$menu["GRP"]["reason"] = "There are no matching Entrant Categories on this Salon. Check Who can participate on the home page";
	}

	debug_to_console("console gpay");
	debug_to_console($menu["GPAY"]["state"]);
	// Group Payment is visible only if (a) Registration is Open for Uploads & (b) User has entered a Club & (c) Payment Mode is GROUP PAYMENT
	// =============
	if ($menu["GPAY"]["state"] == "hidden" || $menu["GPAY"]["state"] == "disabled") {
		$menu["GPAY"]["reason"] = "This menu option has been hidden/disabled by the Administrator.";
	}
	else if ($blacklist_active) {
		$menu["GPAY"]["state"] = "disabled";
		$menu["GPAY"]["reason"] = "User in Blacklist published by a patronage organization. Contact Salon Chairman if it is not correct.";
	}
	else if($tr_user['yps_login_id'] != "") {
		$menu["GPAY"]["state"] = "hidden";
		$menu["GPAY"]["reason"] = "Not available for YPS Members";
	}
	else if(! $contestHasDiscounts){
		$menu["GPAY"]["state"] = "hidden";
		$menu["GPAY"]["reason"] = "This Salon does not have any discount scheme.";
	}
	else if (user_uploads_open() == false) {
		$menu["GPAY"]["state"] = "disabled";
		$menu["GPAY"]["reason"] = "All sections in this Salon was closed on are closed for Uploads.";
	}
	else if($tr_user['club_entered_by'] != $tr_user['profile_id']) {
		$menu["GPAY"]["state"] = "disabled";
		$menu["GPAY"]["reason"] = "Group Payment can be made only by the Group Coordinator " . $tr_user['club_entered_by_name'];
	}
	else if($tr_user['club_payment_mode'] != "GROUP_PAYMENT") {
		$menu["GPAY"]["state"] = "disabled";
		$menu["GPAY"]["reason"] = "Your Club has opted for Individual Payment by each participant.";
	}

	debug_to_console("console upld");

	//var_dump($tr_user);
	// Upload is visible only if (a) Some sections still allow Upload and (b) Salon Fee has been paid
	// ======
	
	if(empty($tr_user['fees_payable']))
	    $tr_user['fees_payable'] = 0;
	if(empty($tr_user['discount_applicable']))
	    $tr_user['discount_applicable'] = 0;


	if ($menu["UPLD"]["state"] == "hidden" || $menu["UPLD"]["state"] == "disabled") {
		$menu["UPLD"]["reason"] = "This menu option has been hidden/disabled by the Administrator.";
	}
	else if ($blacklist_active) {
		$menu["UPLD"]["state"] = "disabled";
		$menu["UPLD"]["reason"] = "User in Blacklist published by a patronage organization. Contact Salon Chairman if it is not correct.";
	}
	else if (user_uploads_open() == false) {
		$menu["UPLD"]["state"] = "disabled";
		$menu["UPLD"]["reason"] = "None of the sections is allowing an upload as of now.";
	}
	else if ((! $tr_user['fee_waived']) && (($tr_user['fees_payable'] == 0) || (($tr_user['fees_payable'] - $tr_user['discount_applicable']) > $tr_user['payment_received']))) {
	    	debug_to_console("inside else if");
		$menu["UPLD"]["state"] = "disabled";
		$menu["UPLD"]["reason"] = "Upload will be enabled once you select sections and make the payment.";
	}


	debug_to_console("console res");
	debug_to_console($menu["RES"]["state"]);
	// My results will be visible only if Results are ready
	// ==========
	if ($menu["RES"]["state"] == "hidden" || $menu["RES"]["state"] == "disabled") {
		$menu["RES"]["reason"] = "This menu option has been hidden/disabled by the Administrator.";
	}
	else if (! $resultsReady) {
		$menu["RES"]["state"] = "disabled";
		$menu["RES"]["reason"] = "Results have not yet been published for this Salon";
	}

	debug_to_console("console ctlg");
	debug_to_console($menu["CTLG"]["state"]);
	// Catalog Order will be visible only if catalog is ready, catalog is offered for a price and Catalog date is less than the last order date
	// =============
	if ($menu["CTLG"]["state"] == "hidden" || $menu["CTLG"]["state"] == "disabled") {
		$menu["CTLG"]["reason"] = "This menu option has been hidden/disabled by the Administrator.";
	}
	else if (! $catalogReady) {
		$menu["CTLG"]["state"] = "disabled";
		$menu["CTLG"]["reason"] = "Catalog has not yet been published for this Salon";
	}
	else if ($catalogPriceInINR == "" && $catalogPriceInUSD == "") {
		$menu["CTLG"]["state"] = "disabled";
		$menu["CTLG"]["reason"] = "Catalog is not available to order";
	}
	else if ($catalogOrderLastDate != null && $catalogOrderLastDate < date("Y-m-d")) {
		$menu["CTLG"]["state"] = "disabled";
		$menu["CTLG"]["reason"] = "Last date for ordering the Catalog was " . print_date($catalogOrderLastDate) . ". ";
	}

	debug_to_console("console club");
	debug_to_console($menu["CLUB"]["state"]);
	// My Club results will be visible only if Results are ready and user is part of a club
	// =======
	if ($menu["CLUB"]["state"] == "hidden" || $menu["CLUB"]["state"] == "disabled") {
		$menu["CLUB"]["reason"] = "This menu option has been hidden/disabled by the Administrator.";
	}
	else if (! $resultsReady) {
		$menu["CLUB"]["state"] = "disabled";
		$menu["CLUB"]["reason"] = "Results have not yet been published for this Salon";
	}
	else if ($tr_user['club_id'] == 0) {
		$menu["CLUB"]["state"] = "disabled";
		$menu["CLUB"]["reason"] = "No club specified in profile";
	}
	// History - always visible

	return $menu;
}

?>

<!-- Avatar and Name -->
<div class="team-member user-avatar text-center" style="margin-bottom: 2px;">
	<?php
		if (isset($_SESSION['USER_ID']) && $_SESSION['USER_ID'] != "") {
	?>
	<img class="profilePic center-block" src="/res/avatar/<?php echo $tr_user['avatar'];?>" alt="...">
	<b><?php echo $_SESSION['USER_NAME'];?></b>
	<?php
			if ($tr_user['yps_login_id'] != "") {
	?>
	<p class="text-muted"><?=$tr_user['yps_login_id'];?>, Youth Photographic Society, Bengaluru</p>
	<?php
			}
			else {
	?>
	<p class="text-muted"><?php echo $tr_user['club_name'];?></p>
	<?php
			}
		}
		else {
	?>
	<img class="profilePic center-block" src="/res/avatar/user.jpg" alt="...">
	<p>NEW USER</p>
	<?php
		}
	?>
</div>

<!-- Set a current contest -->
<?php
if (isset($_SESSION['USER_ID']) && $_SESSION['USER_ID'] != "") {
?>


<div class="row">
	<div class="col-sm-12">
		<h4 class="text text-color text-center">Current Contest</h4>
		<p class="text text-center"><big><b><?=$contestName;?></b></big></p>
		<?php
			if ($tr_user['entrant_category'] != "") {
		?>

		<p class="text-muted text-center">Participating under <?php echo $tr_user['entrant_category'];?> Category</p>
		<?php
			}
			else {
		?>

		<p class="text-muted text-center">NOT REGISTERED</p>
		<?php
			}
		?>
		<hr>
		<form role="form" method="post" action="/user_panel.php" id="login_form" style="width:100%">
			<div class="row">
				<div class="col-sm-12">
					<div class="form-group">
						<label for="set_yearmonth">Choose another Contest to work with</label>
						<select name="set_yearmonth" class="form-control" value="<?=$contest_yearmonth;?>" >
						<?php
							foreach(get_contest_list() AS $set_yearmonth => $set_contest_row) {
						?>
							<option value="<?=$set_yearmonth;?>" <?php echo ($set_yearmonth == $contest_yearmonth) ? "selected" : "";?> ><?=$set_contest_row['contest_name'];?></option>
						<?php
							}
						?>
						</select>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-sm-12">
					<div class="pull-right">
						<button type="submit" class="btn btn-color">Set as Current Contest</button>
					</div>
				</div>
			</div>
		</form>
		<div class="divider"></div>
	</div>
</div>
<?php
}
?>


<!-- Side Menu -->
<?php
	if (isset($_SESSION['USER_ID']) && $_SESSION['USER_ID'] != "") {
?>
<ul class="nav nav-pills nav-stacked">
	<?php
		foreach (menu_list($tr_user) AS $key => $menu) {
			if ($menu['state'] == "disabled")
				$link = "javascript: void(0)";
			else
				$link = $menu['link'];
			if ($menu['state'] != "hidden") {
	?>
	<li class="<?=$menu['state'];?>"><a href="<?=$link;?>" data-toggle="tooltip" title="<?=$menu['reason'];?>" ><i class="fa <?=$menu['icon'];?>"></i> <?=$menu['name'];?></a></li>
	<?php
			}
		}
	?>
	<li><a href="op/logout.php"><i class="fa fa-sign-out-alt"></i> Logout</a></li>
	<?php
		if ($tr_user['yps_login_id'] == "") {
	?>
	<li><a href="#recover-password" data-toggle="collapse" aria-expanded="false" aria-controls="recover-password"><i class="fa fa-random"></i> Change Password</a></li>
	<?php
		}
	?>
</ul>
<?php
	}
?>

<!-- Change Password -->
<div class="collapse" id="recover-password">
	<br>
	<p class="text-muted"><b>Change the Password</b></p>
	<form role="form" method="post" action="op/update_password.php" id="changePassword" name="changePassword">
		<div class="form-group">
			<label class="sr-only" for="opassword">Old Password</label>
			<input type="password" class="form-control" name="opassword" id="opassword" placeholder="Enter Old Password">
		</div>

		<div class="form-group">
			<label class="sr-only" for="npassword">New Password</label>
			<input type="password" class="form-control" name="npassword" placeholder="Enter New Password">
		</div>
		<div class="form-group">
			<button type="submit" class="btn btn-color" name="update_password">Change</button>
		</div>
	</form>
</div>  <!-- collapse -->
<?php
	if (isset($_SESSION['USER_ID']) && $_SESSION['USER_ID'] != "" && $tr_user['print_sections'] > 0) {
?>  
<div>
	<b><span style="color: red;">Address for Mailing Prints:</span></b><br>
	<div style="margin-left: 20px; font-weight: bold;">
	<?php
		$sql = "SELECT * FROM team WHERE yearmonth = '$contest_yearmonth' AND is_print_coordinator = '1' ";
		$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		if (mysqli_num_rows($query) == 0) {
		?>
			TO BE ANNONCED<br>Get in touch with YPSp
		<?php
		}
		else {
			$row = mysqli_fetch_array($query);
			$address_html = str_replace("|", "<br>", $row['address']);
		?>
		<?= $row['member_name'];?><br>
		<?= $address_html;?><br>
		Phone : <?= $row['phone'];?><br>
		Email : <?= $row['email'];?>
		<?php
		}
		?>
	</div>
</div>
<?php
		//include_once("inc/partners.php") ;
	}
?>
