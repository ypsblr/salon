<?php
// session_start();
include("inc/session.php");
include("inc/connect.php");
include("inc/lib.php");

// debug_dump("SESSION", $_SESSION, __FILE__, __LINE__);

if(isset($_SESSION['jury_id']) && isset($_SESSION['jury_type']) && ($_SESSION['jury_type']=="MASTER" || $_SESSION['jury_type']=="ADMIN"))
{
	// Get list of contests for dropdown box
	$sql  = "SELECT * FROM contest ORDER BY yearmonth DESC";
	//$sql .= " WHERE results_ready = 0 ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$row = mysqli_fetch_array($query, MYSQLI_ASSOC);
	$latest_yearmonth = $row['yearmonth'];

	// Determine current contest eligible for judging
	if (isset($_SESSION['jury_yearmonth'])) {
		$jury_yearmonth = $_SESSION['jury_yearmonth'];
	}
	else {
		// Set to the latest
		$jury_yearmonth = $latest_yearmonth;
		$_SESSION['jury_yearmonth'] = $latest_yearmonth;
	}


	$session_id=$_SESSION['jury_id'];
	$sql = "SELECT * FROM user WHERE user_id = '$session_id'";
	$user_query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$user_row = mysqli_fetch_array($user_query);
	$jury_username = $user_row['login'];
	$jury_name=$user_row['user_name'];
	$jury_pic=$user_row['avatar'];
	$user_type = $user_row['type'];

	// Number of Pictures Per section
	$sectionCount = array();
	if (isset($_SESSION['jury_yearmonth'])) {
		$sql = "SELECT * FROM contest WHERE yearmonth = '" . $_SESSION['jury_yearmonth'] . "' ";
		$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		$jury_contest = mysqli_fetch_array($query);

		$sql  = "SELECT pic.section, stub, COUNT(*) as count FROM pic, section ";
		$sql .= " WHERE pic.yearmonth = '" . $_SESSION['jury_yearmonth'] . "' ";
		$sql .= "   AND section.yearmonth = pic.yearmonth ";
		$sql .= "   AND section.section = pic.section ";
		$sql .= " GROUP BY section, stub ";
		$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		while ($row = mysqli_fetch_array($query))
			$sectionCount[$row["section"]] = $row['stub'] . "|" . $row["count"];
	}
?>



<!DOCTYPE html>
<html>
<head>
    <!-- Page title -->
	<title>Youth Photographic Society | Image Dashboard</title>

	<?php include "inc/header.php"; ?>

</head>
<body class="fixed-navbar fixed-sidebar">

<!-- Simple splash screen-->
<div class="splash"> <div class="color-line"></div><div class="splash-title"><h1>   YPS ADMIN PANEL  </h1><p>Please Wait. </p><div class="spinner"> <div class="rect1"></div> <div class="rect2"></div> <div class="rect3"></div> <div class="rect4"></div> <div class="rect5"></div> </div> </div> </div>

<?php
include "inc/master_topbar.php";
include "inc/master_sidebar.php";
?>

<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-lg-12 text-center m-t-md">
                <h2>
                    Dashboard for <?php echo $jury_contest['contest_name'];?>
                </h2>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="hpanel">
                    <div class="panel-heading">
                        <div class="panel-tools">
                            <a class="showhide"><i class="fa fa-chevron-up"></i></a>
                            <a class="closebox"><i class="fa fa-times"></i></a>
                        </div>
                    </div>
                    <div class="panel-body">
                        <div class="row">
						<?php
							foreach($sectionCount as $section => $text) {
								list($stub, $count) = explode("|", $text);
						?>
							<div class="col-lg-3">
								<div class="hpanel">
									<div class="panel-body text-center h-200">
										<i class="fa fa-4x fa-photo"></i>
										<h3><?php echo $section; ?></h3>
										<h3 class="m-xs font-extra-bold no-margins text-success"> <?php echo $count;?></h3>
										<small>Total Image</small>
						<?php
								$sql  = "SELECT entrant_category, COUNT(*) AS count FROM entry, pic ";
								$sql .= " WHERE pic.yearmonth = '$jury_yearmonth' ";
								$sql .= "   AND pic.yearmonth = entry.yearmonth ";
								$sql .= "   AND pic.profile_id = entry.profile_id ";
								$sql .= "   AND pic.section = '$section' ";
								$sql .= " GROUP BY entrant_category ";
								$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
								$breakup = "";
								while ($row = mysqli_fetch_array($query)) {
									$breakup .= ($breakup == "") ? "" : ", ";
									$breakup .= "<b>" . $row["count"] . "</b> by " . $row["entrant_category"];
								}
						?>
										<h5 class="text-success"><?php echo $breakup; ?></h5>
									</div>
									<div class="panel-footer text-center">
										<a href="all_image.php?sections=<?php echo encode_string_array($section);?>" class="btn btn-info btn-sm">
											View Pictures
										</a>
										<a href="all_image.php?sections=<?php echo encode_string_array($section);?>&last=100" class="btn btn-info btn-sm">
											View Latest 100
										</a>
										<a href="all_image.php?sections=<?php echo encode_string_array($section);?>&last=200" class="btn btn-info btn-sm">
											View Latest 200
										</a>
						<?php
								if ($_SESSION['jury_type'] == "MASTER" && strpos($_SERVER['HTTP_HOST'], "localhost")) {
						?>
										<a href="../../salons/<?= $_SESSION['jury_yearmonth'];?>/certificate_section.php?contest=<?= $_SESSION['jury_yearmonth'];?>&stub=<?= $stub; ?>" class="btn btn-info btn-sm">
											Generate Certificates
										</a>
						<?php
									if (file_exists("../../salons/" . $_SESSION['jury_yearmonth'] . "/slideshow_lock.txt")) {
						?>
										<a href="#" class="btn btn-muted btn-sm" disabled >
											Slideshow Running
										</a>
						<?php
									}
									else {
						?>
										<a class="btn btn-info btn-sm run_slideshow" href="javascript: run_slideshow('<?= $_SESSION['jury_yearmonth'];?>', '<?= $stub;?>')" >
											Generate Slideshow
										</a>
						<?php
									}
								}
						?>
									</div>
								</div>
							</div>
						<?php
							}
						?>
						</div>
					</div>
                    <div class="panel-footer">
						<span class="pull-right">
							<a href="#">Youth Photographic Society Admin Panel</a>
						</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php
	include("inc/profile_modal.php");
?>

</div>

<?php
include("inc/footer.php");
?>

<!-- Vendor scripts -->
<script src="plugin/jquery-flot/jquery.flot.js"></script>
<script src="plugin/jquery-flot/jquery.flot.resize.js"></script>
<script src="plugin/jquery-flot/jquery.flot.pie.js"></script>
<script src="plugin/flot.curvedlines/curvedLines.js"></script>
<script src="plugin/jquery.flot.spline/index.js"></script>
<script src="plugin/peity/jquery.peity.min.js"></script>
<script src="plugin/swal/js/sweetalert.min.js"></script>

<script>
	function run_slideshow(contest, stub) {
		$('.run_slideshow').html("Slideshow Running");
		$('.run_slideshow').attr("disabled", true);
		$.ajax(
			{
				url : "../../salons/" + contest + "/slideshow.php",
				method : 'POST',
				data : { contest, stub },
				timeout : 30 * 60 * 1000,			// 30 minute wait
			}
		)
		.then (function (data, status) {alert(status); document.location.reload(); });
	}
</script>


<?php
	//Show error message set in $_SESSION['err_msg'];
	if (isset($_SESSION['err_msg']) && $_SESSION['err_msg'] != "") {
?>
	<script>
		$(document).ready(function() {
			swal({
				title: 'Error',
				text:  '<?php echo str_replace_quotes($_SESSION['err_msg']); ?>',
				icon: "error",
				button: 'Dismiss'
			});
		});
	</script>
<?php
	}
	unset($_SESSION['err_msg']);
?>



</body>

</html>

<?php
}
else
{
	header("Location: ".$_SERVER['HTTP_REFERER']);
	printf("<script>location.href='".$_SERVER['HTTP_REFERER']."'</script>");
}

?>
