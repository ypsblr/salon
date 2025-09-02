<?php
include_once("inc/session.php");
include_once("inc/categories.php");
include_once("inc/user_lib.php");


// Validate Login Status
// debug_dump("SESSION", $_SESSION, __FILE__, __LINE__);
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

<style type="text/css">
#div1,#div2,#div3 {
display: none
}
</style>

</head>

<body class="<?php echo THEME;?>">
	<?php include_once("inc/navbar.php") ;?>
    <div class="wrapper" >
    <?php  include_once("inc/Slideshow.php") ;?>

	<div class="container">
        <div class="row">
			<div class="col-sm-3">
				<?php include("inc/user_sidemenu.php");?>
			</div>
			<div class="col-sm-9" id="myTab">
				<div class="row">
					<h3 class="text text-color">FOR <?=$contestName;?></h3>
					<div class="col-sm-12 shopping-cart user-cart">
					<?php
					 	if ($tr_user['blacklist_match'] != "" && $tr_user['blacklist_exception'] == 0) {
					?>

						<div class="alert alert-warning">
							<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
							<p class="text-color lead">
								*** IMPORTANT *** : Your profile matches a profile in the Blacklist published by patronage houses like FIP.
								This will prevent you from participating in our Salons till it is cleared.
								If you think that your profile has been incorrectly classified, please get in touch with the Salon Chairman.
							</p>
						</div>
					<?php
						}
						// else if ( $tr_user['fee_waived'] == 0 && ($tr_user['participation_code'] == 'NONE' || $tr_user['participation_code'] == "" || $tr_user['fees_payable'] == 0.0) ) {
						else if ( $tr_user['digital_sections'] == 0 && $tr_user['print_sections'] == 0 ) {
					?>

						<div class="alert alert-warning">
							<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
							<p><strong>Get started ! </strong> Participate in the Salon.
							<?php
								if ($tr_user['yps_login_id'] == "") {
									// Check if the participant is a member of a group with GROUP_PAYMENT options
									$sql  = "SELECT club_entry.payment_mode, profile.salutation, profile.first_name, profile.last_name ";
									$sql .= "  FROM coupon, club_entry, profile ";
									$sql .= " WHERE coupon.yearmonth = '$contest_yearmonth' ";
									$sql .= "   AND coupon.email = '" . $tr_user['email'] . "' ";
									$sql .= "   AND club_entry.yearmonth = '$contest_yearmonth' ";
									$sql .= "   AND club_entry.club_id = coupon.club_id ";
									$sql .= "   AND profile.profile_id = club_entry.club_entered_by ";
									$qgpmt = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
									if (mysqli_num_rows($qgpmt) > 0) {
										$gpmt = mysqli_fetch_array($qgpmt);
										if ($gpmt['payment_mode'] == 'GROUP_PAYMENT') {
							?>
								Your club, <?=$tr_user['club_name'];?>, has been set up for consolidated payment by the Group/Club Coordinator
								<?=$gpmt['salutation'];?> <?=$gpmt['first_name'];?> <?=$gpmt['last_name'];?>. You will be able to upload pictures
								after the Group Payment is made by the coordinator. If you are not able to Upload Pictures, please get in touch
								with the coordinator!
							<?php
										}
									}
								}
							?>
						</div>
					<?php
						}
					?>

						<h4 class="primary-font">Your Profile</h4>
						<table class="table table-bordered">
							<tr>
								<td><b>Email:</b> <?=$tr_user['email'];?></td><td><b>Name:</b> <?=utf8_decode($tr_user['profile_name']);?></td>
							</tr>
							<tr>
								<td><b>Club:</b> <?php echo ($tr_user['yps_login_id'] == "") ? $tr_user['club_name'] : "Youth Photographic Society, Bengaluru";?></td>
								<td><b>Honors:</b> <?=$tr_user['honors'];?></td>
							</tr>
							<tr>
								<td><b>Phone:</b> <?=$tr_user['phone'];?></td><td><b>YPS Member ID:</b> <?=$tr_user['yps_login_id'];?></td>
							</tr>
							<tr>
								<td rowspan="3"><b>ADDRESS:</b><br>
									<div style="padding-left: 40px;">
										<?php user_print_address($tr_user);?>
									</div>
								</td>
								<td><i class="fa fa-facebook"></i> <?= $tr_user['facebook_account'];?></td>
							</tr>
							<tr>
								<td><i class="fa fa-twitter"></i> <?= $tr_user['twitter_account'];?></td>
							</tr>
							<tr>
								<td><i class="fa fa-instagram"></i> <?= $tr_user['instagram_account'];?></td>
							</tr>
							<tr>
								<td colspan="2"><b>Participation option chosen: </b><?php echo user_participation_str($tr_user);?></td>
							</tr>
							<?php
								if (! $tr_user['fee_waived']) {
							?>
							<tr>
								<td>
									<b>Participation Fee Payable :</b> <?php echo $tr_user['currency'] . " " . sprintf("%.2f", $tr_user['fees_payable']); ?>
							<?php
									if ($tr_user['discount_applicable'] > 0) {
							?>
									<br>
									<b>Discount Applicable:</b> <?php echo $tr_user['currency'] . " " . sprintf("%.2f", $tr_user['discount_applicable']);?>
									<br>
									<b>Net Amount Payable:</b> <?php echo $tr_user['currency'] . " " . sprintf("%.2f", $tr_user['fees_payable'] - $tr_user['discount_applicable']);?>
							<?php
									}
							?>
								</td>
								<td><b>Total Payment Received:</b> <?php echo $tr_user['currency'] . " " . sprintf("%.2f", $tr_user['payment_received']); ?></td>
							</tr>
							<?php
								}
							?>
							<tr>
								<td>
									<b>Number of Sections Uploaded:</b>
									<?php
										$sql  = "SELECT section_type, COUNT(DISTINCT pic.section) AS sections_uploaded FROM pic, section ";
										$sql .= " WHERE section.yearmonth = '$contest_yearmonth' ";
										$sql .= "   AND pic.yearmonth = '$contest_yearmonth' ";
										$sql .= "   AND pic.profile_id = '" . $tr_user['profile_id'] . "' ";
										$sql .= "   AND pic.section = section.section ";
										$sql .= " GROUP BY section_type ";
										$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
										$digital_sections_uploaded = 0;
										$print_sections_uploaded = 0;
										while ($row = mysqli_fetch_array($query)) {
											$digital_sections_uploaded += ($row['section_type'] == 'D') ? $row['sections_uploaded'] : 0;
											$print_sections_uploaded += ($row['section_type'] == 'P') ? $row['sections_uploaded'] : 0;
										}
									?>
									<?php echo ($digital_sections_uploaded == 0 && $print_sections_uploaded == 0) ? " You are yet to upload any picture" : "";?>
									<?php echo ($digital_sections_uploaded > 0) ? "<br><span style='margin-left: 40px;'>Digital: " . $digital_sections_uploaded . "</span>" : "";?>
									<?php echo ($print_sections_uploaded > 0) ? "<br><span style='margin-left: 40px;'>Print: " . $print_sections_uploaded . "</span>" : "";?>
								</td>
								<td>
									<b>Uploads by Section:</b>
									<?php
										$sql  = "SELECT section, COUNT(*) AS pics_uploaded FROM pic ";
										$sql .= " WHERE yearmonth = '$contest_yearmonth' ";
										$sql .= "   AND profile_id = '" . $tr_user['profile_id'] . "' ";
										$sql .= " GROUP BY section ";
										$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
										while ($row = mysqli_fetch_array($query))
											echo "<br><span style='margin-left: 40px;'>" . $row['section'] . ": " . $row['pics_uploaded'] . "</span>";
									?>
								</td>
						</table>
						<?php
							if ($tr_user['yps_login_id'] != "" && isset($yps_user_errmsg) && $yps_user_errmsg == "" &&
							    ($tr_user['email'] != $yps_user['email'] || $tr_user['first_name'] != $yps_user['first_name'] ||
									$tr_user['last_name'] != $yps_user['last_name']) ) {
						?>
						<p class="text-danger">Your YPS profile has changed as follows. Use <b>Edit Profile</b> to sync the details:</p>
						<ul>
						<?php
								if ($tr_user['email'] != $yps_user['email']) {
						?>
							<li>Email changed to <b><?= $yps_user['email'];?></b></li>
						<?php
								}
						?>
						<?php
								if ($tr_user['first_name'] != $yps_user['first_name']) {
						?>
							<li>First Name changed to <b><?= $yps_user['first_name'];?></b></li>
						<?php
								}
						?>
						<?php
								if ($tr_user['last_name'] != $yps_user['last_name']) {
						?>
							<li>Last Name changed to <b><?= $yps_user['last_name'];?></b></li>
						<?php
								}
						?>
						</ul>
						<?php
							}
						?>
					</div>
				</div>
			</div>	<!-- myTab -->
		</div> <!-- / .row -->
	</div> <!-- / .container -->
	<!-- Footer -->
	<?php include_once("inc/footer.php") ;?>
	</div>  <!-- /.wrapper -->

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
	  function countChar(val) {
		  var len = val.value.length;
		  if (len >= 30) {
			  val.value = val.value.substring(0, 30);
		  }
		  else {
			  $('#charNum').text(30 - len);
		  }
	  }
	</script>

  </body>

</html>

<?php
}
else {
	header('Location: index.php');
	printf("<script>location.href='index.php'</script>");
}

?>
