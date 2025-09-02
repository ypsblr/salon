<?php
// session_start();
include("inc/session.php");
include("inc/connect.php");
include("inc/lib.php");

if(isset($_SESSION['jury_id']) && isset($_SESSION['jury_type']) && ($_SESSION['jury_type']=="MASTER" || $_SESSION['jury_type']=="ADMIN") && isset($_SESSION['jury_yearmonth']))
{
	$session_id=$_SESSION['jury_id'];
	$sql = "SELECT * FROM user WHERE user_id = '$session_id'";
	$user_query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$user_row = mysqli_fetch_array($user_query);
	$jury_username = $user_row['login'];
	$jury_name = $user_row['user_name'];
	$jury_pic = $user_row['avatar'];
	$user_type = $user_row['type'];

	$jury_yearmonth = $_SESSION['jury_yearmonth'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Page title -->
	<title>Youth Photographic Society | Results Panel</title>

	<?php include "inc/header.php"; ?>


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

.img-responsive {
	min-width: 120px;
	min-height: 100px;
}

</style>
</head>

<body class="fixed-navbar fixed-sidebar">

	<!-- Simple splash screen-->
	<div class="splash">
		<div class="color-line"></div>
		<div class="splash-title">
			<h1>    YOUTH PHOTOGRAPHIC SOCIETY | ADMIN PANEL  </h1>
			<p>Please Wait. </p>
			<div class="spinner">
				<div class="rect1"></div>
				<div class="rect2"></div>
				<div class="rect3"></div>
				<div class="rect4"></div>
				<div class="rect5"></div>
			</div>
		</div>
	</div>

	<!-- Header -->
	<?php
	include "inc/master_topbar.php";
	include "inc/master_sidebar.php";
	?>

	<!-- Wrapper -->
	<div id="wrapper">

		<!-- Page Header -->
		<div class="content">
			<div class="row">
				<div class="col-lg-12 text-center m-t-md">
					<h2>
						YPS International Salon - Consolidated Results
					</h2>
						<?php
							$sql  = "SELECT count(*) AS num_entries FROM entry ";
							$sql .= " WHERE yearmonth = '$jury_yearmonth' ";
							$sql .= "   AND profile_id IN ( ";
							$sql .= "                     SELECT DISTINCT profile_id FROM pic ";
							$sql .= "                      WHERE pic.yearmonth = '$jury_yearmonth' ";
							$sql .= "                  )";
							$qtmp = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
							$rtmp = mysqli_fetch_array($qtmp);
							$contestNumEntries = $rtmp['num_entries'];

							$sql = "SELECT count(*) AS num_pictures FROM pic WHERE yearmonth = '$jury_yearmonth' ";
							$qtmp = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
							$rtmp = mysqli_fetch_array($qtmp);
							$contestNumPictures = $rtmp['num_pictures'];

							$sql  = "SELECT DISTINCT country_id FROM entry, profile ";
							$sql .= " WHERE entry.yearmonth = '$jury_yearmonth' ";
							$sql .= "   AND entry.profile_id = profile.profile_id ";
							$sql .= "   AND profile.profile_id IN ( ";
							$sql .= "                     SELECT DISTINCT profile_id FROM pic ";
							$sql .= "                      WHERE pic.yearmonth = '$jury_yearmonth' ";
							$sql .= "                  )";
							$qtmp = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
							$contestNumCountries = mysqli_num_rows($qtmp);
						?>
					<p>Statistics: <?php echo $contestNumPictures;?> Pictures from <?php echo $contestNumEntries?> Participants from <?php echo $contestNumCountries; ?> countries.</p>
				</div>
			</div>

			<!-- Award List -->
			<div class="row">
				<div class="col-sm-12 col-md-12 col-lg-12">
					<div class="hpanel">
						<div class="panel-body" style="margin-left: 3%; margin-right:3%;">
							<!-- Display CONTEST Level Picture Awards -->
							<?php
								$sql = "SELECT * FROM award WHERE yearmonth = '$jury_yearmonth' AND section = 'CONTEST' AND award_type = 'pic' ORDER BY award_group, level, sequence";
								$qaw = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
								if (mysqli_num_rows($qaw) > 0) {
							?>
							<h2 class="primary-font">OVERALL PICTURE AWARDS</h2>
							<?php
									while ($raw = mysqli_fetch_array($qaw)) {
										$award_id = $raw["award_id"];
										$sql = "SELECT *, pic.section AS pic_section ";
										$sql .= " FROM pic_result, pic, profile, country ";
										$sql .= " WHERE pic_result.yearmonth = '$jury_yearmonth' ";
										$sql .= "   AND pic_result.award_id = '$award_id' ";
										$sql .= "   AND pic.yearmonth = pic_result.yearmonth ";
										$sql .= "   AND pic.profile_id = pic_result.profile_id ";
										$sql .= "   AND pic.pic_id = pic_result.pic_id ";
										$sql .= "   AND profile.profile_id = pic.profile_id ";
										$sql .= "   AND profile.country_id = country.country_id ";
										$sql .= " ORDER BY pic_result.ranking ";
										$qres = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
										if (mysqli_num_rows($qres) > 0) {
							?>
							<table class="table">
								<tr class="info">
									<td><h3><?php echo $raw["name"];?></h3></td>
								</tr>
							</table>
							<?php
									while($rres = mysqli_fetch_array($qres)) {
										$picSection = $rres['pic_section'];
										$picTitle = $rres['title'];
										$picOwner = $rres['profile_name'];
										$picFile = $rres['picfile'];
										$honors = $rres['honors'];
										$email = $rres['email'];
										$phone = $rres['phone'];
										$picRanking = $rres['ranking'];
										$picCountry = $rres['country_name'];
										$picCity = $rres['city'];
							?>
							<div class="col-sm-12 col-md-12 col-lg-12">
								<h4 class="text-primary"><?php echo "[" . $picRanking . "] " . $picTitle . ' <small>by</small> ' . $picOwner . ", " . $picCity . ", " . $picCountry . (($honors == "") ? "" : ('<small>, ' . $honors . '</small>')); ?></h4>
								<img class="img-responsive" src="/salons/<?= $jury_yearmonth;?>/upload/<?php echo $picSection . '/' . $picFile;?>" alt="#" />
							<?php
								if ($picLocation != "") {
							?>
								<p><small><?php echo $picLocation; ?></small></p>
							<?php
								}
							?>
								<hr>
							</div>
							<?php
											}
										}
									}
								}
							?>

						<!-- Display Awards by Section -->
							<?php
								$sql = "SELECT * FROM section WHERE yearmonth = '$jury_yearmonth' ";
								$qsec = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
								$first = true;
								while ($rsec = mysqli_fetch_array($qsec)) {
									// DISPLAY REGULAR AWARDS
									$curSection = $rsec['section'];
									$sql = "SELECT * FROM award WHERE yearmonth = '$jury_yearmonth' AND section = '$curSection' AND level < 99 ORDER BY award_group, level, sequence";
									$qaw = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
									if (mysqli_num_rows($qaw) > 0) {
										$sql  = "SELECT count(*) AS num_entries FROM entry ";
										$sql .= " WHERE yearmonth = '$jury_yearmonth' ";
										$sql .= "   AND profile_id IN ( ";
										$sql .= "                     SELECT DISTINCT profile_id FROM pic ";
										$sql .= "                      WHERE pic.yearmonth = '$jury_yearmonth' ";
										$sql .= "                        AND section='$curSection' ";
										$sql .= "                  ) ";
										$qtmp = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
										$rtmp = mysqli_fetch_array($qtmp);
										$sectionNumEntries = $rtmp['num_entries'];

										$sql = "SELECT count(*) AS num_pictures FROM pic WHERE yearmonth = '$jury_yearmonth' AND section = '$curSection' ";
										$qtmp = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
										$rtmp = mysqli_fetch_array($qtmp);
										$sectionNumPictures = $rtmp['num_pictures'];
							?>
							<div class="panel panel-default">
								<div class="panel-heading">
									<div class="panel-title">
										<a data-toggle="collapse" href="#section-<?php echo str_replace(" ", "_", $curSection);?>" >
											<h2 class="primary-font">
												<?php echo $curSection; ?>
												<small><small> ( <?php echo $sectionNumPictures;?> pictures from <?php echo $sectionNumEntries;?> participants )</small></small>
											</h2>
										</a>
									</div>
								</div>
								<div id="section-<?php echo str_replace(" ", "_", $curSection);?>" class="panel-collapse collapse <?php echo ($first ? "in" : ""); $first=false;?>">
									<div class="panel-body">

							<?php
										while ($raw = mysqli_fetch_array($qaw)) {
											$award_id = $raw["award_id"];
											$sql = "SELECT *, pic.section AS pic_section ";
											$sql .= " FROM pic_result, pic, profile, country ";
											$sql .= " WHERE pic_result.yearmonth = '$jury_yearmonth' ";
											$sql .= "   AND pic_result.award_id = '$award_id' ";
											$sql .= "   AND pic.yearmonth = pic_result.yearmonth ";
											$sql .= "   AND pic.profile_id = pic_result.profile_id ";
											$sql .= "   AND pic.pic_id = pic_result.pic_id ";
											$sql .= "   AND profile.profile_id = pic.profile_id ";
											$sql .= "   AND profile.country_id = country.country_id ";
											$sql .= " ORDER BY pic_result.ranking ";

											$qres = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
											if (mysqli_num_rows($qres) > 0) {
							?>
										<table class="table">
											<tr class="info">
												<td><h3><?php echo $raw["award_name"];?></h3></td>
											</tr>
										</table>
							<?php
												while($rres = mysqli_fetch_array($qres)) {
													$picSection = $rres['pic_section'];
													$picTitle = $rres['title'];
													$picOwner = $rres['profile_name'];
													$picLocation = $rres['location'];
													$picFile = $rres['picfile'];
													$honors = $rres['honors'];
													$email = $rres['email'];
													$phone = $rres['phone'];
													$picRanking = $rres['ranking'];
													$picCountry = $rres['country_name'];
													$picCity = $rres['city'];
							?>
										<div class="col-sm-12 col-md-12 col-lg-12 panel panel-default">
											<h4 class="text-primary"><?php echo "[" . $picRanking . "] " . $picTitle . ' <small>by</small> ' . $picOwner . ", " . $picCity . ", " . $picCountry . (($honors == "") ? "" : ('<small>, ' . $honors . '</small>')); ?></h4>
											<p>Email: <?php echo $email;?>, Phone: <?php echo $phone;?></p>
											<img class="img-responsive" src="/salons/<?= $jury_yearmonth;?>/upload/<?php echo $picSection . '/' . $picFile;?>" alt="#" />
							<?php
													if ($picLocation != "") {
							?>
											<p><small><?php echo $picLocation; ?></small></p>
							<?php
													}
							?>
											<hr>
										</div>
							<?php
												}
											}
										}
									}

									// DISPLAY ACCEPTANCES
									$curSection = $rsec['section'];
									$sql = "SELECT * FROM award WHERE yearmonth = '$jury_yearmonth' AND section = '$curSection' AND level = 99 ORDER BY sequence";
									$qaw = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
									if (mysqli_num_rows($qaw) > 0) {
										while ($raw = mysqli_fetch_array($qaw)) {
											$award_id = $raw["award_id"];
											$sql = "SELECT *, pic.section AS pic_section ";
											$sql .= " FROM pic_result, pic, profile, country ";
											$sql .= " WHERE pic_result.yearmonth = '$jury_yearmonth' ";
											$sql .= "   AND pic_result.award_id = '$award_id' ";
											$sql .= "   AND pic.yearmonth = pic_result.yearmonth ";
											$sql .= "   AND pic.profile_id = pic_result.profile_id ";
											$sql .= "   AND pic.pic_id = pic_result.pic_id ";
											$sql .= "   AND profile.profile_id = pic.profile_id ";
											$sql .= "   AND profile.country_id = country.country_id ";
											$sql .= " ORDER BY profile.profile_name ";

											$qres = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
											$acceptances = mysqli_num_rows($qres);
											if ($acceptances > 0) {
							?>

										<div class="col-sm-12 col-md-12 col-lg-12 panel panel-default">
											<div class="panel-heading">
												<div class="panel-title">
													<a data-toggle="collapse" href="#award-<?php echo $award_id;?>" >
														<h3><?php echo $raw["award_name"];?><small> (<?php echo $acceptances;?>)</small></h3>
													</a>
												</div>
											</div>
											<div id="award-<?php echo $award_id;?>" class="panel-collapse collapse">
												<div class="panel-body">

							<?php
												$col = 0;		// Column Counter or multi-column Acceptance display
												while($rres = mysqli_fetch_array($qres)) {
													$picSection = $rres['pic_section'];
													$picTitle = $rres['title'];
													$picOwner = $rres['profile_name'];
													$picLocation = $rres['location'];
													$picFile = $rres['picfile'];
													$honors = $rres['honors'];
													$email = $rres['email'];
													$phone = $rres['phone'];
													$picRanking = $rres['ranking'];
													$picCountry = $rres['country_name'];
							?>
													<div class="col-sm-2 col-md-2 col-lg-2 thumbnail">
														<div class="caption"><?php echo $picOwner . ", " . $picCountry;?></div>
														<div style="max-width:100%" >
															<img class="img-responsive" style="margin-left:auto; margin-right:auto;"
																 src="/salons/<?= $jury_yearmonth;?>/upload/<?php echo $picSection . '/tn/' . $picFile;?>" ></div>
														<div class="caption"><?php echo $picTitle;?></div>
													</div>
							<?php
													$col++;
													if ($col % 6 == 0) {
							?>
													<div class="clearfix"></div>
							<?php
													}
												}
							?>
												</div>
												<hr>
											</div>
										</div>
							<?php
											} //  there are acceptances
										} // Next Award
									}  // There are awards under this section
							?>
									</div>
								</div>
							</div>
							<?php
								}	// Next Section
							?>
							<hr>
						</div>
					</div>
				</div>
			</div> <!-- / .row -->
		</div> <!-- / .container -->
	<?php
		  include("inc/profile_modal.php");
	?>

	</div>	<!-- / .wrapper -->

	<?php
		include("inc/footer.php");
	?>

<!-- JavaScript
================================================== -->



<script src="plugin/pdfmake/build/pdfmake.min.js"></script>
<script src="plugin/pdfmake/build/vfs_fonts.js"></script>

<!-- App scripts -->
<script src="custom/js/homer.js"></script>



</body>
</html>
<?php
}
else
{
$_SESSION['signin_msg'] = "Use ID with require permission not found !";
header("Location: /jurypanel/index.php");
printf("<script>location.href='/jurypanel/index.php'</script>");
}

?>
