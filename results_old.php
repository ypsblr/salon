<?php 
include_once("inc/session.php");

// Check if results have been announced to prevent spurious accesses
// $sql = "SELECT results_ready FROM contest WHERE yearmonth = '$contest_yearmonth' ";
// $tmpq = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
// $tmpr = mysqli_fetch_array($tmpq);
// $resultsReady = $tmpr['results_ready'];

if($resultsReady) {
?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php include_once("inc/header.php"); ?>

<style>
	.img-responsive {
		min-width: 60px;
		min-height: 60px;
	}
</style>
</head>

<body class="<?php echo THEME;?>">

	<!-- Header -->
	<?php include_once("inc/navbar.php") ;?>

	<!-- Wrapper -->
	<div id="wrapper">
      <!-- Jumbotron -->
      <?php  include_once("inc/Slideshow.php") ;?>
     <!-- Slideshow -->

		<!-- Page Header -->
		<div class="content animate-panel">
			<div class="row">
			</div>
			<div class="row">
			</div>

			
			<div class="row">
				<!-- Award Display -->
				<div class="col-sm-7 col-md-7 col-lg-7" style="margin-left: 3%; ">
					<h2 class="text-center m-t-md">
						Congratulations to ALL !
					</h2>
					<?php
						$sql = "SELECT count(*) AS num_entries FROM entry WHERE profile_id IN (SELECT DISTINCT profile_id FROM pic)";
						$qtmp = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
						$rtmp = mysqli_fetch_array($qtmp);
						$contestNumEntries = $rtmp['num_entries'];
						
						$sql = "SELECT count(*) AS num_pictures FROM pic WHERE entry_id IN (SELECT entry_id FROM entry)";
						$qtmp = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
						$rtmp = mysqli_fetch_array($qtmp);
						$contestNumPictures = $rtmp['num_pictures'];
						
						$sql = "SELECT DISTINCT country FROM entry WHERE entry_id IN (SELECT DISTINCT entry_id FROM pic)";
						$qtmp = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
						$contestNumCountries = mysqli_num_rows($qtmp);
					?>
					<!-- Overall Statistics -->
					<h3 class="headline text-color"><span class="border-color"><?php echo $contestName;?></span></h3>   
					<p>
						Youth Photographic Society thanks all the participants for participating in the Salon and all the judges for taking time to select the award winning pictures.
						It was a tough competition and we congratulate all the winners for emerging victorious.
					</p>
					<?php echo merge_data($resultsDescription, $contest_values);?>
					<br>
					<p><b>- Salon Executive Committee</b></p>
					<div class="divider"></div>
					<div class="hpanel">	<!-- Overll Results Panel -->
						<div class="panel-body" >
						
							<!-- 1 A PANEL GROUP FOR GROUPING SECTIONS -->
							<div class="panel-group" id="sections">
								<?php
									$sql = "SELECT * FROM award WHERE section = 'CONTEST' ORDER BY sequence";
									$qaw = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
									if (mysqli_num_rows($qaw) > 0) {
								?>
								<!-- 1.1 SECTION = CONTEST -->
								<div class="panel panel-default">
									<div class="panel-heading">
										<h2 class="primary-font">
											<a data-toggle="collapse" data-parent="#sections" href="#section-contest">OVERALL AWARDS</a>
										</h2>
									</div>
									<div id="section-contest" class="panel-collapse collapse">
										<div class="panel-body">
											<!-- 1.1.1 PANEL FOR GROUP AWARDS -->
											<div class="panel-group" id="awards-contest">
												<?php
													$sql = "SELECT * FROM award WHERE section = 'CONTEST' ORDER BY sequence";
													$qaw = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
													while ($raw = mysqli_fetch_array($qaw)) {
														$aID = $raw["award_id"];
												?>
												<div class="panel panel-default">
													<div class="panel-heading">
														<h3 class="primary-font">
															<a data-toggle="collapse" href="#award-<?php echo $aID;?>">
																<?php echo $raw['name'];?>
															</a>
														</h3>
														<?php echo ($raw['sponsor'] != "") ? "<p><b>Sponsored By " . $raw['sponsor'] . "</b></p>" : ""; ?>
													</div>
													<div id="award-<?php echo $aID;?>" class="panel-collapse collapse in">
														<div class="panel-body">
															<?php
																if ($raw['type'] == 'pic') {
																	$sql = "SELECT *, pic.section AS pic_section, entry.name AS entry_name, country.name AS country_name ";
																	$sql .= " FROM result, pic, entry, country ";
																	$sql .= " WHERE result.award_id = '$aID' AND ";
																	$sql .= "       result.pic_id = pic.pic_id AND ";
																	$sql .= "       pic.entry_id = entry.entry_id AND ";
																	$sql .= "       entry.country = country.id ";
																	$sql .= " ORDER BY entry_name ";
																	$qres = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
																	while($rres = mysqli_fetch_array($qres)) {
																		$picSection = $rres['pic_section'];
																		$picTitle = $rres['title'];
																		$picOwner = $rres['entry_name'];
																		$picFile = $rres['picfile'];
																		$honors = $rres['honors'];
																		$email = $rres['email'];
																		$phone = $rres['phone'];
																		$picRanking = $rres['ranking'];
																		$picCountry = $rres['country_name'];
															?>
															<div class="col-sm-12 col-md-12 col-lg-12">
																<h4 class="primary-font">
																	<?php 
																		echo $picTitle . ' <small>by</small> ' . $picOwner . ", " . $picCountry;
																		echo (($honors == "") ? "" : ('<small>, ' . $honors . '</small>')); 
																	?>
																</h4>
																<img class="img-responsive" src="/upload/<?php echo $picSection . '/' . $picFile;?>" alt="#" />
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
																else if ($raw['type'] == 'entry') {
																	$sql  = "SELECT *, entry.name AS entry_name, country.name AS country_name ";
																	$sql .= " FROM entry, country, result ";
																	$sql .= " WHERE result.award_id = '$aID' AND ";
																	$sql .= "       entry.entry_id = result.pic_id AND ";
																	$sql .= "       entry.country = country.id ";
																	$sql .= " ORDER BY entry_name";
																	$qres = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
																	while ($rres = mysqli_fetch_array($qres)) {
															?>
															<div class="row">
																<div class="col-sm-3 col-md-3 col-lg-3 thumbnail">
																	<div style="max-width:100%;" >
																		<img class="img-responsive" style="margin-left:auto; margin-right:auto;" 
																				src="/upload/avatar/<?php echo $rres['avatar'];?>" >
																	</div>
																</div>
																<div class="col-sm-9 col-md-9 col-lg-9">
																	<h4 class='primary-font'>
																	<?php 
																		echo $rres['entry_name'] . ", " . $rres['country_name'];
																		echo $rres['honors'] != "" ? " (<small><small>" . $rres['honors'] . "</small></small>)" : "";
																		echo $rres['club'] != "" ? " <small>CLUB: " . $rres['club'] . "</small>" : "";
																	?>
																	</h4>
																	<hr>
																	<h5>List of Awards</h5>
																	<?php
																		$sql = "SELECT * FROM result, award, pic ";
																		$sql .= " WHERE result.award_id = award.award_id AND ";
																		$sql .= "       award.section != 'CONTEST' AND ";
																		$sql .= "       result.pic_id = pic.pic_id AND ";
																		$sql .= "       pic.entry_id = '" . $rres['entry_id'] . "' ";
																		$sql .= " ORDER BY award.section, award.level, award.sequence ";
																		$qtmp = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
																		$acceptances = 0;
																		while ($rtmp = mysqli_fetch_array($qtmp)) {
																			if ($rtmp['level'] == 99)
																				$acceptances ++;
																			else {
																	?>
																	<div class="row">
																		<div class="col-sm-1 col-md-1 col-lg-1"></div>
																		<div class="col-sm-7 col-md-7 col-lg-7">
																			<p>Section: <b><?php echo $rtmp['section']; ?></b></p>
																			<p>Award: <b><?php echo $rtmp['name']; ?></b></p>
																			<p>Title: <b><?php echo $rtmp['title']; ?></b></p>
																		</div>
																		<div class="col-sm-4 col-md-4 col-lg-4 thumbnail">
																			<div style="max-width: 100%;">
																				<img class="img-responsive" style="margin:auto;"
																					src="/upload/<?php echo $rtmp['section'] . '/tn/' . $rtmp['picfile'];?>" >
																			</div>
																		</div>
																	</div>
																	<?php
																			}
																		}
																	?>
																	<h5>Number of Acceptances: <?php echo $acceptances;?></h5>
																</div>
															</div>  <!-- row -->
															<?php
																	} // while
																} // if type
																else if ($raw['type'] == "club" ) {
																	$sql = "SELECT * FROM result, club ";
																	$sql .= " WHERE result.award_id = '$aID' ";
																	$sql .= "   AND result.pic_id = club.club_id ";
																	$qclub = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
																	while ($rclub = mysqli_fetch_array($qclub)) {
																		$club_id = $rclub['club_id'];
																		// Get Statistics
																		$sql  = "SELECT COUNT(*) AS num_participants, SUM(uploads) AS num_uploads, ";
																		$sql .= "       SUM(awards) AS num_awards, SUM(hms) AS num_hms, SUM(acceptances) AS num_acceptances, ";
																		$sql .= "       SUM(score) AS total_score ";
																		$sql .= " FROM coupon, entry ";
																		$sql .= " WHERE coupon.club_id = '$club_id' ";
																		$sql .= "   AND coupon.entry_id = entry.entry_id ";
																		$sql .= "   AND entry.uploads > 0 ";
																		
																		$qtmp = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
																		if ($rtmp = mysqli_fetch_array($qtmp)) {
																			$num_participants = $rtmp['num_participants'];
																			$num_uploads = $rtmp['num_uploads'];
																			$num_awards = $rtmp['num_awards'];
																			$num_hms = $rtmp['num_hms'];
																			$num_acceptances = $rtmp['num_acceptances'];
																			$total_score = $rtmp['total_score'];
																		}
																		else {
																			$num_participants = 0;
																			$num_uploads = 0;
																			$num_awards = 0;
																			$num_hms = 0;
																			$num_acceptances = 0;
																			$total_score = 0;
																		}
															?>
															<div class="row">
																<div class="col-sm-4 col-md-4 col-lg-4">
																	<img src="/upload/avatar/<?php echo $rclub['club_logo'];?>" style="width: 100%"; />
																</div>
																<div class="col-sm-8 col-md-8 col-lg-8">
																	<h3 class="primary-font">
																		<?php echo $rclub['club_name'];?>
																	</h3>
																	<p><a href="<?php echo $rclub['club_website']; ?>" target="_blank"><?php echo $rclub['club_website'];?></a></p>
																	<br>
																	<p>Number of Participants: <b><?php echo $num_participants; ?></b></p>
																	<p>Number of Picture Uploads: <b><?php echo $num_uploads; ?></b></p>
																	<p>Number of Awards: <b><?php echo $num_awards; ?></b></p>
																	<p>Number of Honorable Mentions: <b><?php echo $num_hms; ?></b></p>
																	<p>Number of Acceptances: <b><?php echo $num_acceptances; ?></b></p>
																	<br>
																	<p><b>Hearty Congratulations to all the members!</b></p>
																</div>
															</div>
															<div class="row">
																<h4>Participating Members</h4>
																<br>
															<?php
																$sql  = "SELECT * FROM coupon, entry ";
																$sql .= " WHERE coupon.club_id = '$club_id' ";
																$sql .= "   AND coupon.entry_id = entry.entry_id ";
																$sql .= "   AND entry.uploads > 0 ";
																$sql .= " ORDER BY name ";
																$qtmp = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
																$idx = 0;
																while ($rtmp = mysqli_fetch_array($qtmp)) {
																	$idx++;
															?>
																<div class="col-sm-4 col-md-4 col-lg-4">
																	<div class="row" >
																		<div class="col-sm-4 col-md-4 col-lg-4">
																			<img src="/upload/avatar/<?php echo $rtmp['avatar'];?>" style="width: 100%">
																		</div>
																		<div class="col-sm-8 col-md-8 col-lg-8">
																			<?php echo $rtmp['name'];?>
																		</div>
																	</div>
																</div>
																<div class="<?php echo ($idx % 3) == 0 ? 'clearfix' : '';?>"></div>
															<?php
																}
															?>
															</div>
															<div class="row"><hr></div>
															<?php
																	} // while
																} // if
															?>
														</div>
													</div>
												</div>	<!-- END OF PANEL FOR Contest Level AWards -->
												<?php
													}
												?>
											</div>
										</div>
									</div>
								</div>
								<!-- END OF SECTION = CONTEST -->
								<?php
									}
								?>
								<!-- 1.2 OTHER SECTIONS -->
								<?php
									$sql = "SELECT * FROM section";
									$qsec = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
									while ($rsec = mysqli_fetch_array($qsec)) {
										$curSection = $rsec['section'];

										$sql = "SELECT count(*) AS num_entries FROM entry WHERE entry_id IN (SELECT entry_id FROM pic WHERE section='$curSection')";
										$qtmp = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
										$rtmp = mysqli_fetch_array($qtmp);
										$sectionNumEntries = $rtmp['num_entries'];

										$sql = "SELECT count(*) AS num_pictures FROM pic WHERE section = '$curSection' AND entry_id IN (SELECT entry_id FROM entry)";
										$qtmp = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
										$rtmp = mysqli_fetch_array($qtmp);
										$sectionNumPictures = $rtmp['num_pictures'];
								?>
								<div class="panel panel-default">
									<div class="panel-heading">
										<h2 class="primary-font">
											<a data-toggle="collapse" data-parent="#sections" href="#section-<?php echo str_replace(" ", "_", $curSection);?>"><?php echo $curSection; ?></a>
											<small><small> ( <?php echo $sectionNumPictures;?> pictures from <?php echo $sectionNumEntries;?> participants )</small></small>
										</h2>
									</div>
									<div id="section-<?php echo str_replace(" ", "_", $curSection);?>" class="panel-collapse collapse">
										<div class="panel-body">
											<div class="panel-group" id="awards-<?php echo str_replace(" ", "_", $curSection);?>" >
											<?php
												$sql = "SELECT * FROM award WHERE section = '$curSection' AND level < 99 ORDER BY level, sequence";
												$qaw = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
												while ($raw = mysqli_fetch_array($qaw)) {
													$aID = $raw["award_id"];
											?>
												<div class="panel panel-default">
													<div class="panel-heading">
														<h3 class="primary-font">
															<a data-toggle="collapse" href="#award-<?php echo $aID;?>">
																<?php echo $raw['name'];?>
															</a>
														</h3>
														<?php echo ($raw['sponsor'] != "") ? "<p><b>Sponsored By " . $raw['sponsor'] . "</b></p>" : ""; ?>
													</div>
													<div id="award-<?php echo $aID;?>" class="panel-collapse collapse in">
														<div class="panel-body">
															<?php
																$sql = "SELECT *, pic.section AS pic_section, entry.name AS entry_name, country.name AS country_name ";
																$sql .= " FROM result, pic, entry, country ";
																$sql .= " WHERE result.award_id = '$aID' AND ";
																$sql .= "       result.pic_id = pic.pic_id AND ";
																$sql .= "       pic.entry_id = entry.entry_id AND ";
																$sql .= "       entry.country = country.id ";
																$sql .= " ORDER BY entry_name ";
																$qres = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
																$col = 0;		// Column Counter or multi-column Acceptance display
																while($rres = mysqli_fetch_array($qres)) {
																	$picSection = $rres['pic_section'];
																	$picTitle = $rres['title'];
																	$picOwner = $rres['entry_name'];
																	$picCity = $rres['city'];
																	$picFile = $rres['picfile'];
																	$honors = $rres['honors'];
																	$email = $rres['email'];
																	$phone = $rres['phone'];
																	$club = $rres['club'];
																	$picRanking = $rres['ranking'];
																	$picCountry = $rres['country_name'];
															?>
															<div class="row">
																<div class="col-sm-2 col-md-2 col-lg-2">
																	<img src="/upload/avatar/<?php echo $rres['avatar'];?>" class="img-responsive">
																</div>
																<div class="col-sm-10 col-md-10 col-lg-10">
																	<big class="lead"><?php echo $picOwner . ", " . $picCity . ", " . $picCountry . (($honors == "") ? "" : ('<small>, ' . $honors . '</small>'));?></big><br>
																	<b><?php echo ($club != "") ? "CLUB: " . $club : "";?></b>
																</div>
															</div>
															<h4 class="primary-font"><?php echo $picTitle; ?></h4>
															<img class="img-responsive" src="/upload/<?php echo $picSection . '/' . $picFile;?>" alt="#" />
															
															<div class="divider"></div>
															<?php
																}
															?>
															
														</div>
													</div>
												</div>
											<?php
												}
											?>
											<!-- DISPLAY ACCEPTANCES -->
											<?php
												$sql = "SELECT * FROM award WHERE section = '$curSection' AND level = 99 ORDER BY level, sequence";
												$qaw = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
												while ($raw = mysqli_fetch_array($qaw)) {
													$aID = $raw["award_id"];
											?>
												<div class="panel panel-default">
													<div class="panel-heading">
														<h3 class="primary-font">
															<a data-toggle="collapse" href="#award-<?php echo $aID;?>">
																<?php echo $raw['name'];?>
															</a>
														</h3>
													</div>
													<div id="award-<?php echo $aID;?>" class="panel-collapse collapse">
														<div class="panel-body">
															<?php
																$sql = "SELECT *, pic.section AS pic_section, entry.name AS entry_name, country.name AS country_name ";
																$sql .= " FROM result, pic, entry, country ";
																$sql .= " WHERE result.award_id = '$aID' AND ";
																$sql .= "       result.pic_id = pic.pic_id AND ";
																$sql .= "       pic.entry_id = entry.entry_id AND ";
																$sql .= "       entry.country = country.ID ";
																$sql .= " ORDER BY entry_name ";
																$qres = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
																$col = 0;		// Column Counter or multi-column Acceptance display
																while($rres = mysqli_fetch_array($qres)) {
																	$picSection = $rres['pic_section'];
																	$picTitle = $rres['title'];
																	$picOwner = $rres['entry_name'];
																	$picLocation = $rres['location'];
																	$picFile = $rres['picfile'];
																	$honors = $rres['honors'];
																	$email = $rres['email'];
																	$phone = $rres['phone'];
																	$picRanking = $rres['ranking'];
																	$picCountry = $rres['country_name'];
																	$picCity = $rres['city'];
																	$picState = $rres['state'];
																	$picClub = $rres['club'];
															?>
															<div class="col-sm-3 col-md-3 col-lg-3 thumbnail">
																<div class="caption">
																	<?php echo $picOwner . ", " . $picCity . (($picClub != "" && $picClub != "NO CLUB") ? ", <span style='color:#888;'><small>" . $picClub . "</small></span>" : "");?>
																</div>
																<div style="max-width:100%;" >
																	<a href="/upload/<?php echo $picSection;?>/<?php echo $picFile;?>" 
																			data-lightbox="<?php echo $picSection;?>" 
																			data-title="<?php echo $picTitle;?> by <?php echo $picOwner;?>, <?php echo $picCity;?>" >
																		<div style="max-width:100%;" ><img class="img-responsive" style="margin-left:auto; margin-right:auto;" src="/upload/<?php echo $picSection . '/tn/' . $picFile;?>" ></div>
																	</a>
																</div>
																<div class="caption"><?php echo $picTitle;?></div>
															</div>
															<?php
																	$col++;
																	if ($col % 4 == 0) {
															?>
															<div class="clearfix"></div>
															<?php
																	}
																}
															?>
														</div>  <!-- panel-body -->
													</div>
												</div>
											<?php
												}
											?>
											</div>
										</div>
									</div>
								</div>
							<?php
									}
							?>
							</div>
							<hr>
						</div>
					</div>
				</div>  <!-- Left Column-7 Award Display -->
				<!-- Right Column - Sponsors etc -->
				<div class="col-sm-4 col-md-4 col-lg-4">
					
					<style>
						.carousel-inner img{ max-height:300px !important; }
					</style>
           

					<h3 class="headline text-color"><span class="border-color">Committee</span></h3> 
					<div class="row">
						<div class="col-sm-3 col-md-3 col-lg-3">
							<div style="max-width:100%"><img class="img-responsive img-rounded" style="margin:4px;min-width:20px;min-height:20px;" src="/img/com/satish.jpg" alt="Satish H"></div>
						</div>
						<div class="col-sm-8 col-md-8 col-lg-8">
							<p><b>H Satish</b> <small>MFIAP, MICS, ARPS, Hon.FICS, Hon.YPS, Hon.ECPA</small></p>
							<p><i>YPS President</i></p>
						</div>
					</div>
					<div class="row">
						<div class="col-sm-3 col-md-3 col-lg-3">
							<div style="max-width:100%"><img class="img-responsive img-rounded" style="margin:4px;min-width:20px;min-height:20px;" src="/img/com/vikas.jpg" alt="Chandrasekar Srinvasamurthy"></div>
						</div>
						<div class="col-sm-8 col-md-8 col-lg-8">
							<p><b>Manju Vikas Sastry V</b> <small></small></p>
							<p><i>YPS Secretary</i></p>
						</div>
					</div>
				</div>	<!-- Right Column - Sponsors etc -->
			</div> <!-- / .row -->
			<!-- FOOTER -->
			<div class="row">
				<?php include_once("inc/footer.php") ;?>
			</div>
		</div> <!-- / .container -->
	</div>	<!-- / .wrapper -->
    <!-- Style Toggle -->

  <?php include_once("inc/settingToggle.php") ;?>
    <!-- JavaScript
    ================================================== -->
	<?php include_once("inc/scripts.php"); ?>

	<!-- Form specific scripts -->
    <script src="plugin/lightbox/js/lightbox.min.js"></script>


</body>
</html>
<?php
}
else
{
$_SESSION['err_msg'] = "Use ID with require permission not found !";
header("Location: /index.php");
printf("<script>location.href='/index.php'</script>");
}

?>