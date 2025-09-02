<?php
include_once("inc/session.php");
include_once("inc/categories.php");
include_once("inc/user_lib.php");

function rejection_text($notifications) {
	global $rejection_reasons;

	$notification_list = explode("|", $notifications);
	$rejection_text = "";
	foreach ($notification_list AS $notification) {
		if ($notification != "") {
			list($notification_date, $notification_code_str) = explode(":", $notification);
			$notification_codes = explode(",", $notification_code_str);
			$rejected = false;
			foreach ($notification_codes as $notification_code)
				if (isset($rejection_reasons[$notification_code])) {
					$rejection_text .= (($rejection_text == "") ? "" : ",") . $rejection_reasons[$notification_code];
				}
		}
	}
	return $rejection_text;
}

function jury_notifications($profile_id, $pic_id) {
	global $DBCON;
	global $contest_yearmonth;
	global $contest_archived;

	$sql  = "SELECT yearmonth, MIN(rating) AS min_score, GROUP_CONCAT(DISTINCT tags SEPARATOR '|') AS jury_notifications ";
	if ($contest_archived)
		$sql .= "  FROM ar_rating rating ";
	else
		$sql .= "  FROM rating ";
	$sql .= " WHERE yearmonth = '$contest_yearmonth' ";
	$sql .= "   AND profile_id = '$profile_id' ";
	$sql .= "   AND pic_id = '$pic_id' ";
	$sql .= " GROUP BY yearmonth ";

	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if ($row = mysqli_fetch_array($query)) {
		if ($row['min_score'] == '1') {
			$notifications = explode("|", $row['jury_notifications']);
			$notification_list = [];
			foreach ($notifications as $notification) {
				if ($notification != "" && (! isset($notification_list[$notification])))
					$notification_list[$notification] = $notification;
			}
			return implode(", ", $notification_list);
		}
	}
	return "";
}

if(isset($_SESSION['USER_ID'])) {

	// Gather List of Rejection Reasons
		// Get Notifications List
	$sql  = "SELECT template_code, template_name ";
	$sql .= "  FROM email_template ";
	$sql .= " WHERE template_type = 'user_notification' ";
	$qntf = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$rejection_reasons = array();
	while ($rntf = mysqli_fetch_array($qntf))
		$rejection_reasons[$rntf['template_code']] = $rntf['template_name'];

	// Determine the Salons in which the user has participated
	$salons_participated = array();
	// Current Salon
	$sql  = "SELECT DISTINCT yearmonth FROM pic WHERE profile_id = '" . $tr_user['profile_id'] . "' ";
	$sql .= " UNION ";
	$sql .= "SELECT DISTINCT yearmonth FROM ar_pic WHERE profile_id = '" . $tr_user['profile_id'] . "' ";
	$sql .= " ORDER BY yearmonth DESC ";
	$tmpq = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	while ($tmpr = mysqli_fetch_array($tmpq))
		$salons_participated[] = $tmpr['yearmonth'];
	// Archived Salons
	// $sql = "SELECT DISTINCT yearmonth FROM ar_pic WHERE profile_id = '" . $tr_user['profile_id'] . "' ORDER BY yearmonth DESC";
	// $tmpq = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	// while ($tmpr = mysqli_fetch_array($tmpq))
	// 	$salons_participated[] = $tmpr['yearmonth'];

	// Sort salons in descending order
?>


<!DOCTYPE html>
<html lang="en">

<head>
<?php include_once("inc/header.php"); ?>

<style type="text/css">

#div1,#div2,#div3 {
display: none;
}

</style>

<script type="text/javascript">
/***
function showHide(elem) {
        document.getElementById('div1').style.display = 'block';
}

window.onload = function() {
    //get the divs to show/hide
    divsO = document.getElementById("frmMyform").getElementsByTagName('div');
}
***/
</script>

<script src="http://code.jquery.com/jquery-1.5.js"></script>

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
</head>

<body class="<?=THEME;?>">
	<?php include_once("inc/navbar.php") ;?>
    <div class="wrapper" style="padding-bottom:0px">
    <?php  include_once("inc/Slideshow.php") ;?>

		<div class="container">
			<div class="row">
				<div class="col-sm-3">
					<?php include("inc/user_sidemenu.php");?>
				</div>

				<div class="col-sm-9" id="myTab">
					<div class="row">
						<div class="col-sm-12 shopping-cart user-cart">
							<h4 class="primary-font">My Participation in YPS Salons</h4>
						<?php
						$first = true;
						foreach($salons_participated as $salon_yearmonth) {
							$ar_contest = user_get_contest($salon_yearmonth);
							$all_upload_code = encode_string_array($salon_yearmonth . "|PROFILE|" . $tr_user['profile_id'] . "|ALL");
						?>
							<div class="panel panel-warning">
								<div class="panel-heading">
									<div class="row">
										<div class="col-sm-9">
											<h4 class="text">
												<a data-toggle="collapse" href="#salon-<?php echo $salon_yearmonth;?>" ><?php echo $ar_contest['contest_name']; ?></a>
											</h4>
										</div>
										<div class="col-sm-3">
											<?php
												// if ( file_exists("salons/" . $salon_yearmonth . "/certdef.php") &&
												if ( file_exists("salons/" . $salon_yearmonth . "/blob/certdef.json") &&
													 $ar_contest['certificates_ready'] == '1' &&
												 	 user_has_acceptances($salon_yearmonth, $tr_user['profile_id']) ) {
											?>
											<a href="op/certificate.php?cert=<?=$all_upload_code;?>" class="btn btn-info pull-right" download >
												<span style="color:white;"><i class="fa fa-download"></i> Download All Certificates</span>
											</a>
											<?php
												}
											?>
										</div>
									</div>
								</div>
								<div class="panel-body panel-collapse collapse <?php echo $first ? 'in' : '';?>" id="salon-<?php echo $salon_yearmonth;?>" >
									<!-- Entry Award -->
									<?php
										$entry_awards = user_get_entry_award($salon_yearmonth, $tr_user['profile_id']);
										if (sizeof($entry_awards) > 0) {
											foreach ($entry_awards as $entry_award) {
									?>
									<p class="text text-primary lead"><i class="fa fa-trophy"></i> Winner of <?php echo $entry_award['award_name']; ?></p>
									<br>
									<?php
											}
										}
									?>
									<!-- Picture Awards -->
									<?php
										$pic_list = user_get_picture_list($salon_yearmonth, $tr_user['profile_id']);
										// debug_dump("pic_list", $pic_list, __FILE__, __LINE__);
										if ($pic_list != false && sizeof($pic_list) > 0){
									?>
									<div class="row" style="margin-left: 0px;">
										<?php
											$prev_section = "";
											$thumbnail_count = 0;
											foreach ($pic_list AS $rpic) {
												// Start a New Section
												if ($rpic['section'] != $prev_section) {
										?>
										<div class="clearfix"></div>
										<p class="text text-color lead"><?=$rpic['section'];?></p><br>
										<?php
													$prev_section = $rpic['section'];
													$thumbnail_count = 0;
												}
												$award_name = ($ar_contest['results_ready'] == '1' ? $rpic['award_name'] : "");
										?>
										<div class="col-sm-3 col-md-3 col-lg-3 thumbnail">
											<div class="caption"><?=$rpic['title'];?></div>
											<div style="max-width:100%;" >
												<a href="/salons/<?=$salon_yearmonth;?>/upload/<?=$rpic['section'];?>/<?=$rpic['picfile'];?>"
														data-lightbox="<?=$salon_yearmonth;?>"
														data-title="<?php echo $rpic['title'] . ($award_name != '' ? ' won ' . $award_name : '');?> " >
													<div style="max-width:100%;" >
														<img class="img-responsive" style="margin-left:auto; margin-right:auto;"
																src="/salons/<?=$salon_yearmonth;?>/upload/<?=$rpic['section'];?>/tn/<?=$rpic['picfile'];?>" >
													</div>
												</a>
											</div>
											<div class="caption">
												<?php
													$rejection_text = rejection_text($rpic['notifications']);
													$jury_rejection_text = jury_notifications($tr_user['profile_id'], $rpic['pic_id']);
													if ($jury_rejection_text != "")
														$rejection_text = ($rejection_text == "" ? "" : ", ") . $jury_rejection_text;
													if ($rejection_text != "") {
												?>
												<p class="small text-danger">
													<i class="fa fa-exclamation-triangle"></i> <?= $rejection_text;?>
												</p>
												<?php
													}
													if ($award_name != "" && $rpic['award_id'] != 0) {
														// Upload Code for Certificates
														$upload_code = encode_string_array($salon_yearmonth . "|" . $rpic['award_id'] . "|" . $tr_user['profile_id'] . "|" . $rpic['pic_id']);
														// $upload_code = encode_string_array($contest_yearmonth . "|" . $rawd['award_id'] . "|" . $tr_user['profile_id'] . "|" . $pic_id);

														// Generate icon for display
														if ($rpic['has_medal'] == 1)
															$icon = "fa-certificate";
														else if ($rpic['has_pin'] == 1)
															$icon = "fa-map-pin";
														else if ($rpic['has_ribbon'] == 1)
															$icon = "fa-bookmark";
														else if ($rpic['has_memento'] == 1)
															$icon = "fa-trophy";
														else if ($rpic['has_gift'] == 1)
															$icon = "fa-gift";
														else if ($rpic['has_certificate'] == 1)
															$icon = "fa-graduation-cap";
														else
															$icon = "";
												?>
												<p class="text-center text-color">
													<i class="fa <?=$icon;?>"></i> <?= $award_name;?>
												</p>
												<?php
													}	// has award
													// if ($rpic['has_certificate'] && file_exists("salons/" . $salon_yearmonth . "/certificate.php") && $contestCatalog != "" ) {
													if ($rpic['has_certificate'] == '1' && file_exists("salons/" . $salon_yearmonth . "/blob/certdef.json") && $ar_contest['certificates_ready'] == '1' ) {
												?>
												<p class="text-center">
													<a href="op/certificate.php?cert=<?=$upload_code;?>" download >
														<span class="text-muted"><i class="fa fa-download"></i> Download Certificate</span>
													</a>
												</p>
												<?php
													}	// has_certificate
												?>
											</div>
										</div>
										<?php
											if ($thumbnail_count % 4 == 0 && $thumbnail_count != 0) {
										?>
										<div class="clearfix"></div>
										<?php
											} // thumbnail count
										?>
									<?php
										}	// for each picture
									?>
									</div>
								<?php
									}	// $pic_list > 0
								?>
								</div>
							</div>
						<?php
							$first = false;
						} // for each Salon
						?>
						</div>
					</div>
				</div>			<!-- My Tab -->
			</div> <!-- / .row -->
		</div> <!-- / .container -->

    <!-- Footer -->
	<?php include_once("inc/footer.php") ;?>

    <!-- Style Toggle -->
	<?php include_once("inc/settingToggle.php") ;?>

    <!-- JavaScript -->
	<?php include_once("inc/scripts.php"); ?>
    <script src="plugin/lightbox/js/lightbox.min.js"></script>

	<script>
		$(document).ready(function(){
			$('html, body').animate({ scrollTop: $("#myTab").offset().top-100 }, 500);
		});
    </script>


</body>

</html>

<?php
}
else
{
header('Location: index.php');
printf("<script>location.href='index.php'</script>");
}

?>
