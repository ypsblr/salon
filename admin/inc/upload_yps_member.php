<?php
// session_start();
include("inc/session.php");
include("inc/connect.php");
include("inc/lib.php");

// Minimal Validations
if (isset($_SESSION['admin_id']) && isset($_SESSION['admin_yearmonth']) ) {
	// Set up user and contest information
	include ("inc/load_common_data.php");

	$session_id = $_SESSION['admin_id'];
	$admin_yearmonth = $_SESSION['admin_yearmonth'];
	$sql = "SELECT * FROM contest WHERE yearmonth = '$admin_yearmonth'";
	$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$contest = mysqli_fetch_array($query);
	$contest_archived = ($contest['archived'] == '1');




?>
<!DOCTYPE html>
<html>

<head>
    <!-- Page title -->
	<title>Youth Photographic Society | Administration Panel</title>

	<?php include "inc/header.php"; ?>

    <!-- App styles -->
    <link rel="stylesheet" href="plugin/blueimp-gallery/css/blueimp-gallery.min.css" />

</head>
<body class="fixed-navbar fixed-sidebar">
	<!-- Simple splash screen-->
	<div class="splash">
		<div class="color-line"></div>
		<div class="splash-title">
			<h1>    YPS MASTER PANEL  </h1>
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

<?php
	include "inc/master_topbar.php";
	include "inc/master_sidebar.php";
?>

    <div id="wrapper">
		<div class="normalheader transition animated fadeIn">
			<div class="row">
				<div class="col-sm-6">
					<div class="hpanel">
						<div class="panel-body">
							<a class="small-header-action" href="#">
								<div class="clip-header">
									<i class="fa fa-arrow-up"></i>
								</div>
							</a>
							<h3 class="font-light m-b-xs">
								Upload YPS Member List
							</h3>
							<hr>
							<!-- Top Form for generating Discount Options -->
							<form role="form" method="post" action="op/upload_yps_member.php" id="stats-form" name="stats-form" enctype="multipart/form-data">
								<input type="hidden" name="yearmonth" value="<?= $admin_yearmonth;?>" >
								<div class="row">
									<div class="col-sm-4">
										<label for="member-file">Upload YPS Member CSV File</label>
										<input type="file" name="member-file" id="member-file" >
									</div>
								</div>

								<div class="form-group">
									<div class="row">
										<div class="col-sm-12">
											<br>
											<button type="submit" class="btn btn-info pull-right" name="upload_yps_members" > Upload </button>
										</div>
									</div>
								</div>

							</form>

						</div>	<!-- / .panel-body -->
					</div>		<!-- / .panel -->
				</div>			<!-- Left Half -->
			</div>				<!-- row -->
		</div> <!-- / header -->
		<!-- Footer -->
		<?php include_once("inc/profile_modal.php");?>
	</div>		<!-- / .wrapper -->
	<?php include("inc/footer.php");?>

</body>

</html>
<?php
}
else {
	$_SESSION['signin_msg'] = "Use ID with require permission not found !";
	header("Location: index.php");
	printf("<script>location.href='index.php'</script>");
}
?>
