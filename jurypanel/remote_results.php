<?php
// session_start();
include("inc/session.php");
include("inc/connect.php");
include("inc/lib.php");
?>

<?php
function display_full($rres) {
	global $jury_yearmonth;
?>
	<div class="col-sm-9 col-md-9 col-lg-9">
		<div class="row">
<?php
	if ($rres['avatar'] != "" && $rres['avatar'] != "user.jpg" && is_file("../res/avatar/" . $rres['avatar'])) {
?>
			<div class="col-sm-1">
				<img class="img-responsive" src="/res/avatar/<?=$rres['avatar'];?>" alt="#" />
			</div>
<?php
	}
?>
			<div class="col-sm-6">
				<h4 class="text-primary">
<?php
					echo $rres['profile_name'];
					if ($rres['honors'] != "")
						echo ", <small>". $rres['honors'] . "</small>";
					echo "<br><span style='font-size: 0.8em;'>" . $rres['city'] . ", " . $rres['state'] . ", " . $rres['country_name'] . "</span>";
?>
				</h4>
			</div>
		</div>
		<h4 class="text-primary">
<?php
	if ($rres['ranking'] > 0)
		echo "[" . $rres['ranking'] . "] ";
	echo $rres['title'];
?>
		</h4>
		<img class="img-responsive" src="/salons/<?=$jury_yearmonth;?>/upload/<?=$rres['section'];?>/<?=$rres['picfile'];?>" alt="#" />
<?php
	if ($rres['location'] != "") {
?>
		<p><small><?php echo $rres['location']; ?></small></p>
<?php
	}
?>
		<hr>
	</div>
<?php
}
?>

<?php
function display_thumbnails($rres) {
	global $jury_yearmonth;
?>
	<div class="col-sm-3 col-md-3 col-lg-3 thumbnail">
		<div class="caption"><?=$rres['profile_name'];?>, <?=$rres['country_name'];?></div>
		<div style="max-width: 100%;">
			<img class="img-responsive" style="margin-left:auto; margin-right:auto;"
					src="/salons/<?=$jury_yearmonth;?>/upload/<?=$rres['section'];?>/tn/<?=$rres['picfile'];?>" >
		</div>
		<div class="caption"><?=$rres['title'];?></div>
	</div>
<?php
}
?>

<?php
function display_awards($award_id, $award_name, $thumbnails = false) {
	global $jury_yearmonth;
	global $DBCON;

	$sql  = "SELECT pic.section, pic.title, pic.picfile, pic.location, ";
	$sql .= "       profile.profile_name, profile.city, profile.state, country.country_name, profile.honors, profile.email, profile.phone, profile.avatar, ";
	$sql .= "       pic_result.ranking ";
	$sql .= "  FROM pic_result, pic, profile, country ";
	$sql .= " WHERE pic_result.yearmonth = '$jury_yearmonth' ";
	$sql .= "   AND pic_result.award_id = '$award_id' ";
	$sql .= "   AND pic.yearmonth = pic_result.yearmonth ";
	$sql .= "   AND pic.profile_id = pic_result.profile_id ";
	$sql .= "   AND pic.pic_id = pic_result.pic_id ";
	$sql .= "   AND profile.profile_id = pic_result.profile_id ";
	$sql .= "   AND country.country_id = profile.country_id ";
	$sql .= " ORDER BY pic_result.ranking, pic_result.profile_id, pic_result.pic_id ";
	$qres = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_num_rows($qres) > 0) {
?>
		<table class="table">
			<tr class="info">
				<td><h3><?=$award_name;?></h3></td>
			</tr>
		</table>
		<div class="row">
<?php
		$col = 0;
		while($rres = mysqli_fetch_array($qres)) {
			if ($thumbnails)
				display_thumbnails($rres);
			else
				display_full($rres);
			++ $col;
			if ($thumbnails && $col % 4 == 0) {
?>
			<div class="clearfix"></div>
<?php
			}
		}
?>
		</div>
<?php
	}
}
?>

<?php

if(isset($_SESSION['jury_id']) && isset($_SESSION['jury_yearmonth']) && isset($_REQUEST['show']) ) {

	$jury_yearmonth = $_SESSION['jury_yearmonth'];
	$jury_id = $_SESSION['jury_id'];

	$jury_section = decode_string_array($_REQUEST['show']);
	$_SESSION['section'] = $jury_section;

	// Get Contest
	$sql = "SELECT * FROM contest WHERE yearmonth = '$jury_yearmonth' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$contest = mysqli_fetch_array($query);

	// Sync to Jury Session in progress
	$sql  = "SELECT * FROM jury_session, assignment ";
	$sql .= " WHERE jury_session.yearmonth = '$jury_yearmonth' ";
	$sql .= "   AND jury_session.section = '$jury_section' ";
	$sql .= "   AND assignment.yearmonth = jury_session.yearmonth ";
	$sql .= "   AND assignment.section = jury_session.section ";
	$sql .= "   AND assignment.user_id = '$jury_id' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	// if (mysqli_num_rows($query) == 0)
	// 	die_with_error("Judging for $award_group under $jury_section is yet to open. Contact YPS !", __FILE__, __LINE__);

	// Check if results are ready for all award Groups
	$have_results = true;
	while ($row = mysqli_fetch_array($query))
		$have_results = ($row['result_ready'] == '0' ? false : $have_results);

	if (! $have_results)
		die_with_error("We do not have all the results for $jury_section yet ", __FILE__, __LINE__);
?>
<!DOCTYPE html>
<html lang="en">
<head>

    <!-- Page title -->
    <title>Youth Photographic Society | Results Panel</title>

	<?php include("inc/header.php");?>
    <!-- Place favicon.ico and apple-touch-icon.png in the root directory -->
    <!--<link rel="shortcut icon" type="image/ico" href="favicon.ico" />-->

    <!-- Vendor styles -->
    <link rel="stylesheet" href="plugin/blueimp-gallery/css/blueimp-gallery.min.css" />


<style type="text/css">
.containerBox {
    position: relative;
    display: inline-block;
}
.text-box {
    position: absolute;
    text-align: center;
    width: 100%;
}
.text-box:before {
   content: '';
   display: inline-block;
   height: 100%;
   vertical-align: middle;
}

h4 {
   display: inline-block;
   font-size: 18px; /*or whatever you want*/
   color: #FFF;
}

img {
  display: block;
  max-width: 100%;
  height: auto;
}
</style>
</head>

<body class="body-blue">

	<!-- Header -->
	<?php include("inc/remote_sidebar.php");?>

	<!-- Wrapper -->
	<div id="wrapper">

		<!-- Page Header -->
		<div class="content">
			<div class="row">
				<div class="col-lg-12 text-center m-t-md">
					<h2>
						Results for <?=$jury_section;?> - <?= $contest['contest_name'];?>
					</h2>
				</div>
			</div>

			<!-- Award List -->
			<div class="row">
				<div class="col-sm-12 col-md-12 col-lg-12">
					<div class="hpanel">
						<div class="panel-body" style="margin-left: 3%; margin-right:3%;">
							<!-- Display CONTEST Level Awards -->
							<?php
								$sql = "SELECT * FROM award WHERE yearmonth = '$jury_yearmonth' AND award_type = 'pic' AND section = 'CONTEST' ORDER BY award_group, level, sequence";
								$qaw = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
								if (mysqli_num_rows($qaw) > 0) {
							?>
							<h2 class="primary-font">OVERALL AWARDS</h2>
							<?php
									while ($raw = mysqli_fetch_array($qaw)) {
										$award_id = $raw["award_id"];
										display_awards($award_id, $raw['award_name']);
									}
								}
							?>

						<!-- Display Awards for Section -->
							<?php
								$sql = "SELECT * FROM section WHERE yearmonth = '$jury_yearmonth' AND section = '$jury_section'";
								$qsec = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
								while ($rsec = mysqli_fetch_array($qsec)) {
									// DISPLAY PICTURE AWARDS
									$curSection = $rsec['section'];
									$sql = "SELECT * FROM award WHERE yearmonth = '$jury_yearmonth' AND award_type = 'pic' ";
									$sql .= " AND section = '$curSection' AND level < 99 ORDER BY award_group, level, sequence";
									$qaw = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
									if (mysqli_num_rows($qaw) > 0) {
										$sql  = "SELECT count(*) AS num_entries FROM entry ";
										$sql .= " WHERE yearmonth = '$jury_yearmonth' ";
										$sql .= "   AND profile_id IN (SELECT DISTINCT profile_id FROM pic WHERE yearmonth = '$jury_yearmonth' AND section = '$curSection')";
										$qtmp = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
										$rtmp = mysqli_fetch_array($qtmp);
										$sectionNumEntries = $rtmp['num_entries'];

										$sql = "SELECT count(*) AS num_pictures FROM pic WHERE yearmonth = '$jury_yearmonth' AND section = '$curSection' ";
										$qtmp = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
										$rtmp = mysqli_fetch_array($qtmp);
										$sectionNumPictures = $rtmp['num_pictures'];

							?>
							<h2 class="primary-font">
								<?=$curSection;?>
								<small><small> ( <?=$sectionNumPictures;?> pictures from <?=$sectionNumEntries;?> participants )</small></small>
							</h2>
							<?php
										while ($raw = mysqli_fetch_array($qaw)) {
											$award_id = $raw["award_id"];
											display_awards($award_id, $raw['award_name']);
										}
									}

									// DISPLAY ACCEPTANCES
//									$curSection = $rsec['section'];
//									$sql = "SELECT * FROM award WHERE yearmonth = '201812' AND section = '$curSection' AND level = 99 ORDER BY sequence";
//									$qaw = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
//									if (mysqli_num_rows($qaw) > 0) {
//										while ($raw = mysqli_fetch_array($qaw)) {
//											$award_id = $raw["award_id"];
//											display_awards($award_id, $raw['award_name'], true);
//										} // Next Award
//									}  // There are awards under this section
								}	// Next Section
							?>
							<hr>
							<!-- Confirmation of Results by Jury -->
							<form role="form" method="post" action="op/confirm_result.php" id="result_form" name="result_form" enctype="multipart/form-data">
								<input type="hidden" name="yearmonth" value="<?= $jury_yearmonth;?>" >
								<input type="hidden" name="section" value="<?= $jury_section;?>" >
								<input type ="hidden"name="jury_id" value="<?= $_SESSION['jury_id']; ?>" >
								<div class="row">
									<div class="col-sm-12">
										<div class="pull-right">
											<label for="confirm_result">
												<input type="checkbox" name="confirm_result" id="confirm_result" value="1" required>
												<span style="padding-left: 8px;">I have reviewed the results displayed and concur</span>
											</label>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-sm-12">
										<br>
										<button type="submit" class="btn btn-info pull-right" name="send_confirmation_mail">Send Confirmation Email</button>
									</div>
								</div>
							</form>
						</div> <!-- panel body -->
					</div>
				</div>
			</div> <!-- / .row -->

		</div> <!-- / .container -->
	<?php
		include("inc/profile_modal.php");
	?>
	</div>	<!-- / .wrapper -->

<!-- JavaScript
================================================== -->


	<?php
      include("inc/footer.php");
	?>

<!-- DataTables buttons scripts -->
<script src="plugin/pdfmake/build/pdfmake.min.js"></script>
<script src="plugin/pdfmake/build/vfs_fonts.js"></script>

</body>
</html>
<?php
}
else {
	$_SESSION['err_msg'] = "Use ID with require permission not found !";
	header("Location: /jurypanel/index.php");
	printf("<script>location.href='/jurypanel/index.php'</script>");
}
?>
