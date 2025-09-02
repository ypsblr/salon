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

	// Get List of Award Groups
	$ag_list = [];
	$sql = "SELECT DISTINCT award_group FROM award WHERE yearmonth = '$admin_yearmonth' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	while ( $row = mysqli_fetch_array($query) )
		$ag_list[] = $row['award_group'];

	$ag_list_csv = implode(",", $ag_list);

	// Get existing Stats Definitions
	$sd_list = [];
	// INIT
	$sd_list[] = array( "category" => "INIT", "award_group" => "GENERAL", "segment" => "Prepare pic & entry", "sequence" => "0", "routine" => "prepare_pic_entry");
	// PARTICIPATION & TOP
	foreach ($ag_list as $award_group) {
		// PARTICIPATION
		$sd_list[] = array( "category" => "PARTICIPATION", "award_group" => "$award_group", "segment" => "By Section", "sequence" => "1", "routine" => "participation_by_section");
		$sd_list[] = array( "category" => "PARTICIPATION", "award_group" => "$award_group", "segment" => "By Country", "sequence" => "2", "routine" => "participation_by_country");
		$sd_list[] = array( "category" => "PARTICIPATION", "award_group" => "$award_group", "segment" => "By Club", "sequence" => "3", "routine" => "participation_by_club");
		$sd_list[] = array( "category" => "PARTICIPATION", "award_group" => "$award_group", "segment" => "By Gender", "sequence" => "4", "routine" => "participation_by_gender");
		// TOP
		$sd_list[] = array( "category" => "TOP", "award_group" => "$award_group", "segment" => "Top [category] entrants", "sequence" => "5", "routine" => "top_entrants");
	}
	// INIT
	$sd_list[] = array( "category" => "BITS", "award_group" => "GENERAL", "segment" => "Statistical Bits", "sequence" => "0", "routine" => "stats_bits");

	// Check if there are awards without results
	$no_result_list = "";
	// PIC Awards
	$sql  = "SELECT award.award_id, section, award_group, award_name, number_of_awards, COUNT(pic_result.award_id) AS number_of_results ";
	if ($contest_archived)
		$sql .= "  FROM award LEFT JOIN ar_pic_result pic_result ";
	else
		$sql .= "  FROM award LEFT JOIN pic_result ";
	$sql .= "                  ON (pic_result.yearmonth = award.yearmonth AND pic_result.award_id = award.award_id) ";
	$sql .= " WHERE award.yearmonth = '$admin_yearmonth' ";
	$sql .= "   AND award.award_type = 'pic' ";
	$sql .= " GROUP BY award_id, section, award_group, award_name, number_of_awards ";
	$sql .= "HAVING number_of_results < number_of_awards ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	while ( $row = mysqli_fetch_array($query) )
		$no_result_list .= ($no_result_list == "" ? "" : ",") . $row['award_name'] . "/" . $row['section'];

	if ($no_result_list != "")
		$_SESSION['info_msg'] = "Results not published for " . $no_result_list;


?>
<!DOCTYPE html>
<html>

<head>
    <!-- Page title -->
	<title>Youth Photographic Society | Statistics Administration Panel</title>

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
								Run Statistics for <?= $admin_contest_name;?>
							</h3>
							<hr>
							<!-- Top Form for generating Discount Options -->
							<form role="form" method="post" action="op/generate_stats.php" id="stats-form" name="stats-form" enctype="multipart/form-data">
								<table class="table">
									<tbody>
										<tr>
											<th>Category</th><th>Segment</th><th>Award Group</th><th>Select</th>
										</tr>
									<?php
										foreach ( $sd_list as $statdef ) {
											// Check if statistics exists
											$stats_exists = false;
									?>
										<tr>
											<td><?= $statdef['category'];?></td>
											<td><?= $statdef['segment'];?></td>
											<td><?= $statdef['award_group'];?></td>
											<td>
													<label>
														<input type="checkbox" name="run[]" value="<?= implode("|", $statdef);?>" >
														Run
													</label>
											</td>
										</tr>
									<?php
										}
									?>
									</tbody>
								</table>

								<div class="form-group">
									<div class="row">
										<div class="col-sm-12">
											<br>
											<button type="submit" class="btn btn-info pull-right" name="generate_stats" >Generate Statistics</button>
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
