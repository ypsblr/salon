<?php
date_default_timezone_set("Asia/Kolkata");
include("inc/analyticstracking.php");
include_once("inc/connect.php");
// include("inc/get_contest.php");
include("inc/topbar.php");

$php_file = basename($_SERVER['PHP_SELF']);

// Check if current contest is the latest, if not show an option under Home to switch to latest contest
$sql = "SELECT * FROM contest WHERE yearmonth = (SELECT MAX(yearmonth) FROM contest)";
$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
$row = mysqli_fetch_array($query);

?>
<div class="navbar navbar-static-top navbar-white" role="navigation" style="max-width:1260px">
	<div class="container containerStyle">
		<!-- Navbar Header -->
		<div class="navbar-header">
			<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
        </div> <!-- / Navbar Header -->

		<!-- Logos -->
		<div class="pull-left hidden-xs" style="margin-top: 4px; margin-bottom: 4px;">
			<img src="img/ypsLogo.png" style="margin-left: 0px; height: 80px;">
		</div>
        <!-- Navbar Links -->
        <div class="navbar-collapse collapse">
			<ul class="nav navbar-nav navbar-right">
			<?php
				if ($contest_yearmonth == $row['yearmonth']) {
			?>
				<li><a href="/index.php" class="bg-hover-color">Home</a></li>
			<?php
				}
				else {
			?>
				<li class="dropdown">
					<a href="#" class="dropdown-toggle bg-hover-color" data-toggle="dropdown">Home<b class="caret"></b></a>
					<ul class="dropdown-menu">
						<li><a href="index.php?set_yearmonth=<?= $row['yearmonth']; ?>" class="bg-hover-color">Switch to <?= $row['contest_name'];?></a></li>
						<li><a href="index.php" class="bg-hover-color">Stay with <?= $contestName; ?></a></li>
					</ul>
				</li>
			<?php
				}
			?>
				<li><a href="/awards.php" class="bg-hover-color">Awards</a></li>
				<li><a href="/term_condition.php" class="bg-hover-color">Rules </a></li>
				<li><a href="/jury.php" class="bg-hover-color">Jury</a></li>
			<?php
				if (! $resultsReady) {
			?>
				<li><a href="/status.php" class="bg-hover-color">Status</a></li>
			<?php
				}
			?>
			<?php
				if ($resultsReady) {
			?>
				<li><a href="/salon.php?id=<?=$contest_yearmonth;?>" class="bg-hover-color">Results</a></li>
			<?php
				}
				if(isset($_SESSION['USER_ID'])) {
			?>
				<li><a href="/user_panel.php" class="bg-hover-color">My Page</a></li>
			<?php
				}
				if($contestHasSponsorship) {
			?>
				<li><a href="/sponsor.php" class="bg-hover-color">Sponsor</a></li>
			<?php
				}

			?>
				<li class="dropdown">
					<a href="#" class="dropdown-toggle bg-hover-color" data-toggle="dropdown">Recent Salons<b class="caret"></b></a>
					<ul class="dropdown-menu">
					<?php
						$sql = "SELECT yearmonth, contest_name FROM contest WHERE results_ready > 0 ORDER BY yearmonth DESC";
						$nbq = mysqli_query($DBCON, $sql);
						while ($nbr = mysqli_fetch_array($nbq)) {
					?>
						<li><a href="salon.php?id=<?=$nbr['yearmonth'];?>" class="bg-hover-color"><?=$nbr['contest_name'];?></a></li>
					<?php
						}
					?>
					</ul>
				</li>

				<li><a href="/catalog.php" class="bg-hover-color">Past Catalogs</a></li>

				<li><a href="/contact_us.php" class="bg-hover-color">Contact Us</a></li>
			</ul>

		</div> <!-- / Navbar Links -->
	</div> <!-- / container -->
</div> <!-- / navbar -->
