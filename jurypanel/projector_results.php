<?php
// session_start();
include("inc/session.php");
include("inc/connect.php");
include("inc/lib.php");
?>
<?php
function display_full($rres, $award_name, $max_awards, $current_award_number) {
	global $jury_yearmonth;
?>
	<div class="col-sm-12 col-md-12 col-lg-12">
		<div class="row">
<?php
	if ($rres['avatar'] != "" && $rres['avatar'] != "user.jpg") {
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
					if ($rres['yps_login_id'] != "")
						echo "<br><i><span style='font-size: 0.7em; color: #aaa;'>YOUTH PHOTOGRAPHIC SOCIETY</span></i>";
					else if ($rres['club_name'] != "")
					 	echo "<br><i><span style='font-size: 0.7em; color: #aaa;'>" . $rres['club_name'] . "</span></i>";
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
	if ($max_awards > 1)
	 	echo " <small>(" . $award_name . " - " . $current_award_number . "/" . $max_awards . ")</small>";
?>
		</h4>
		<img class="img-responsive" style="max-width: 800px; max-height: 800px;" src="/salons/<?=$jury_yearmonth;?>/upload/<?=$rres['section'];?>/<?=$rres['picfile'];?>" alt="#" />
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
	$sql .= "       profile.profile_name, profile.city, profile.state, country.country_name, profile.honors, profile.email, ";
	$sql .= "       profile.phone, profile.avatar, profile.yps_login_id, ";
	$sql .= "       pic_result.ranking, club.club_name ";
	$sql .= "  FROM pic_result, pic, country, profile LEFT JOIN club ON club.club_id = profile.club_id ";
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
		$max_awards = mysqli_num_rows($qres);
		$col = 0;
		while($rres = mysqli_fetch_array($qres)) {
			if ($thumbnails)
				display_thumbnails($rres);
			else
				display_full($rres, $award_name, $max_awards, $col + 1);
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

if(isset($_SESSION['jury_id']) && isset($_SESSION['jury_yearmonth']) ) {

	$jury_yearmonth = $_SESSION['jury_yearmonth'];

	// Determine the section to display results
	if (isset($_REQUEST['sections'])) {
		$sections = $_REQUEST['sections'];
		$sections = str_replace(" ", "", $sections);
    	$sections = decode_string_array($sections);
	}

	// Check if Results are revealed
	// First get a list of Award Groups
	$sql = "SELECT DISTINCT award_group FROM entrant_category WHERE yearmonth = '$jury_yearmonth' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	while ($row = mysqli_fetch_array($query)) {
		$sql  = "SELECT * FROM jury_session ";
		$sql .= " WHERE yearmonth = '$jury_yearmonth' ";
		$sql .= "   AND section = '$sections' ";
		$sql .= "   AND award_group = '" . $row['award_group'] . "' ";
		$sql .= "   AND result_ready = '0' ";
		$subq = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		if (mysqli_num_rows($subq) != 0) {
			$errmsg = "Results for " . $row['award_group'] . " under $sections has not yet been released";
			$_SESSION['err_msg'] = $errmsg;
			log_error($errmsg, __FILE__, __LINE__);
			header("Location: /jurypanel/projector_home.php");
			print("<script>location.href='/jurypanel/projector_home.php'</script>");
		}
	}

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
	<div id="header">
		<div class="color-line"></div>
		<div id="logo" class="light-version">
			<span>Youth Photographic Society</span>
		</div>
		<nav role="navigation">
			<div class="header-link hide-menu"><i class="fa fa-bars"></i></div>
<!--
			<div class="small-logo">
				<span class="text-primary">PROJECTION APP</span>
			</div>
-->
			<form role="search" class="navbar-form-custom" method="post" action="#">
				<div class="form-group">
					<br>
					<label class="form-group">Results Panel</label>
					<!-- <input type="text" placeholder="Search something special" class="form-control" name="search"> -->
				</div>
			</form>
<!--
			<div class="mobile-menu">
				<button type="button" class="navbar-toggle mobile-menu-toggle" data-toggle="collapse" data-target="#mobile-collapse">
					<i class="fa fa-edit"></i>
				</button>
				<div class="collapse mobile-navbar" id="mobile-collapse">
					<ul class="nav navbar-nav">
						<li>
							<a class="" href="index.php">Logout</a>
						</li>
						<li>
							<a data-toggle="modal" data-target="#myModal">Profile</a>
						</li>
					</ul>
				</div>
			</div>
-->
		</nav>
	</div>

	<?php include("inc/projector_sidebar.php");?>

	<!-- Wrapper -->
	<div id="wrapper">

		<!-- Page Header -->
		<div class="content">
			<div class="row">
				<div class="col-lg-12 text-center m-t-md">
					<h2>
						Results for <?=$sections;?>
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

						<!-- Display Awards by Section -->
							<?php
								$sql = "SELECT * FROM section WHERE yearmonth = '$jury_yearmonth' AND section = '$sections'";
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
									$curSection = $rsec['section'];
									$sql = "SELECT * FROM award WHERE yearmonth = '201812' AND section = '$curSection' AND level = 99 ORDER BY sequence";
									$qaw = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
									if (mysqli_num_rows($qaw) > 0) {
										while ($raw = mysqli_fetch_array($qaw)) {
											$award_id = $raw["award_id"];
											display_awards($award_id, $raw['award_name'], true);
										} // Next Award
									}  // There are awards under this section
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
	$_SESSION['signin_msg'] = "Use ID with require permission not found !";
	header("Location: /jurypanel/index.php");
	printf("<script>location.href='/jurypanel/index.php'</script>");
}
?>
