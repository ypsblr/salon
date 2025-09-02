<?php
//
// Merge one profile with another to eliminate duplicates
// Open only to ADMIN
//
// session_start();
include("inc/session.php");
include("inc/connect.php");
include("inc/lib.php");

function sql_in_list($list_text) {
	$in_list = explode(",", $list_text);
	if (sizeof($in_list) == 0)
		return 'dummy_list_item';
	else {
		for ($i = 0; $i < sizeof($in_list); ++ $i) {
			$in_list[$i] = "'" . $in_list[$i] . "'";
		}
		return implode(",", $in_list);
	}
}

if (isset($_SESSION['admin_id']) && isset($_SESSION['admin_yearmonth']) ) {
	// Set up user and contest information
	include ("inc/load_common_data.php");

	// Check for option to match pofiles after excluding phone number from match
	$nophonematch = isset($_REQUEST['nophone']);
?>
<!DOCTYPE html>
<html>
<head>
    <!-- Page title -->
	<title>Youth Photographic Society | Merge Profiles</title>

	<?php include "inc/header.php"; ?>

	<style>
		table.table th, table.table th.right {
			text-align: right;
		}
		table.table th.left {
			text-align: left;
		}
		table.table th.center {
			text-align: center;
		}
		table.table td, table.table td.right {
			text-align : right;
		}
		table.table td.left {
			text-align : left;
		}
		table.table td.center {
			text-align : center;
		}
	</style>

</head>
<body class="fixed-navbar fixed-sidebar">

<!-- Simple splash screen-->
<div class="splash">
	<div class="color-line"></div>
	<div class="splash-title">
		<h1>   YPS ADMIN PANEL  </h1>
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
    <div class="content">
        <div class="row">
            <div class="col-lg-12 text-center m-t-md">
                <h2>
                    Merge Duplicate Profiles
                </h2>
            </div>
        </div>
		<div class="row" style="margin-top: 15px; margin-bottom: 15px;">
			<div class="col-sm-12">
				<span class="text-danger lead text-center"><b>TAKE DATABASE BACKUP BEFORE MERGING PROFILES</b></span>
			</div>
		</div>
        <div class="row">
            <div class="col-sm-12">
				<!-- Catalog Orders -->
				<div class="hpanel">
					<div class="panel-heading">
						<span class="lead">Potential Duplicates</span>
						<a class="showhide"><i class="fa fa-chevron-up"></i></a>
					</div>
					<div class="panel-body">
						<p><a href="merge_profiles.php?nophone"><span class="text-info"><b>Show matches excluding Phone Number</b></span></a></p>
						<table class="table">
							<thead>
								<tr>
									<th class="left">ID</th>
									<th class="left">Merged with</th>
									<th class="left">Name</th>
									<th class="left">Email</th>
									<th class="center">Phone</th>
									<th class="left">Address</th>
									<th class="left">History</th>
									<th class="center"></th>
								</tr>
							</thead>
							<tbody>
							<?php
								$match_list = [];

								$sql  = "SELECT profile.profile_id, profile_name, phone, email, address_1, address_2, address_3, city, state, pin, ";
								$sql .= "       profile.country_id, country_name, profile_disabled, profile_merged_with, ";
								$sql .= "       IFNULL(excluded_profiles, '') AS excluded_profiles ";
								$sql .= "  FROM profile LEFT JOIN exclude_match ON exclude_match.profile_id = profile.profile_id, country  ";
								$sql .= " WHERE country.country_id = profile.country_id ";
								$sql .= " ORDER BY profile.profile_id DESC ";
								$target_query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

								while ($target = mysqli_fetch_array($target_query)) {
									// Do not process profiles already identified as matches
									if (in_array($target['profile_id'], $match_list))
										continue;

									// Check for duplicates matching phone, city, pin, country
									$sql  = "SELECT profile.profile_id, profile_name, phone, email, address_1, address_2, address_3, city, state, pin, ";
									$sql .= "       profile.country_id, country_name, profile_disabled, profile_merged_with, ";
									$sql .= "       IFNULL(excluded_profiles, '') AS excluded_profiles ";
									$sql .= "  FROM profile LEFT JOIN exclude_match ON exclude_match.profile_id = profile.profile_id, country ";
									$sql .= " WHERE country.country_id = profile.country_id ";
									$sql .= "   AND profile.profile_id < '" . $target['profile_id'] . "' ";
									// $sql .= "   AND profile_disabled = '0' ";
									$sql .= "   AND ( ";
									$sql .= "           profile_name SOUNDS LIKE '" . mysqli_real_escape_string($DBCON, $target['profile_name']) . "' ";
									if (! $nophonematch)
										$sql .= "        OR phone = '" . $target['phone'] . "' ";
									$sql .= "       ) ";
									$sql .= "   AND pin = '" . $target['pin'] . "' ";
									$sql .= "   AND profile.country_id = '" . $target['country_id'] . "' ";
									// $sql .= "   AND profile.profile_id NOT IN (" . sql_in_list($target['excluded_profiles']) . ") ";
									$sql .= " ORDER BY profile.profile_id ";
									$match_query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
									if (mysqli_num_rows($match_query) > 0) {
										// Find Participation History for the target
										$sql  = "SELECT CONCAT(yearmonth, ':', COUNT(*)) AS participation FROM pic WHERE profile_id = '" . $target['profile_id'] . "' GROUP BY yearmonth ";
										$sql .= " UNION ";
										$sql .= "SELECT CONCAT(yearmonth, ':', COUNT(*)) AS participation FROM ar_pic WHERE profile_id = '" . $target['profile_id'] . "' GROUP BY yearmonth ";
										$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
										$contests = [];
										while ($row = mysqli_fetch_array($query))
											$contests[] = $row['participation'];
										$target_contests = implode(", ", $contests);
										$exclude_text = ( $target['excluded_profiles'] == "" ) ? "" : "XCLD:" . $target['excluded_profiles'];
							?>
								<tr>
									<th class="left"><b><?= $target['profile_id'];?></b></th>
									<th class="left"><b><?= ($target['profile_merged_with'] == '0') ? $exclude_text : $target['profile_merged_with'];?></b></th>
									<th class="left"><b><?= $target['profile_name'];?></b></th>
									<th class="left"><b><?= $target['email'];?></b></th>
									<th class="center"><b><?= $target['phone'];?></b></th>
									<th class="left"><b><?= implode(", ", array($target['address_1'], $target['address_2'], $target['address_3'], $target['city'], $target['pin'], $target['country_name']));?></b></th>
									<th class="left"><b><?= $target_contests;?></b></th>
									<th class="center"></th>
								</tr>
							<?php
										while ($match = mysqli_fetch_array($match_query)) {

											$match_list[] = $match['profile_id'];

											$sql  = "SELECT CONCAT(yearmonth, ':', COUNT(*)) AS participation FROM pic WHERE profile_id = '" . $match['profile_id'] . "' GROUP BY yearmonth ";
											$sql .= " UNION ";
											$sql .= "SELECT CONCAT(yearmonth, ':', COUNT(*)) AS participation FROM ar_pic WHERE profile_id = '" . $match['profile_id'] . "' GROUP BY yearmonth ";
											$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
											$contests = [];
											while ($row = mysqli_fetch_array($query))
												$contests[] = $row['participation'];
											$match_contests = implode(", ", $contests);
											if ( in_array($target['profile_id'], explode(",", $match['excluded_profiles'])) )
												$exclude_text = "EXCLUDE";
											else
												$exclude_text = "";

							?>
								<tr>
									<td class="left"><i><?= $match['profile_id'];?></i></td>
									<td class="left"><i><?= ($match['profile_merged_with'] == '0') ? $exclude_text : $match['profile_merged_with'];?></i></td>
									<td class="left"><i><?= $match['profile_name'];?></i></td>
									<td class="left"><i><?= $match['email'];?></i></td>
									<td class="center"><i><?= $match['phone'];?></i></td>
									<td class="left"><i><?= implode(", ", array($match['address_1'], $match['address_2'], $match['address_3'], $match['city'], $match['pin'], $match['country_name']));?></i></td>
									<td class="left"><i><?= $match_contests;?></i></td>
									<td class="center">
										<?php
											if ( (! in_array($target['profile_id'], explode(",", $match['excluded_profiles']))) &&
												 (! in_array($match['profile_id'], explode(",", $target['excluded_profiles']))) ) {

												if ( $match['profile_disabled'] == '0' && $target['profile_disabled'] == '0' ) {
										?>
										<p><a href= "op/match_to_target.php?target=<?= $target['profile_id'];?>&match=<?= $match['profile_id'];?>"
											class="btn btn-info" >Merge with above</a></p>
										<p><a href= "op/match_to_target.php?target=<?= $match['profile_id'];?>&match=<?= $target['profile_id'];?>"
											class="btn btn-success" >Merge above with this</a></p>
										<p><a href= "op/exclude_match.php?target=<?= $target['profile_id'];?>&match=<?= $match['profile_id'];?>"
											class="btn btn-warning" >Exclude Match</a></p>
										<?php
												}
												else {
													if ( $match['profile_disabled'] == '1' && $match['profile_merged_with'] == $target['profile_id'] ) {
										?>
										<p><a href= "op/rollback_merge.php?target=<?= $target['profile_id'];?>&match=<?= $match['profile_id'];?>"
											class="btn btn-info" >Rollback Merge</a></p>
										<?php
													}
													else if ( $target['profile_disabled'] == '1' && $target['profile_merged_with'] == $match['profile_id'] ) {
										?>
										<p><a href= "op/rollback_merge.php?target=<?= $match['profile_id'];?>&match=<?= $target['profile_id'];?>"
											class="btn btn-info" >Rollback Merge</a></p>
										<?php
													}
												}
											}
											else {
										?>
										<p><a href= "op/exclude_match.php?target=<?= $target['profile_id'];?>&match=<?= $match['profile_id'];?>&remove=1"
											class="btn btn-warning" >Remove Exclusion</a></p>
										<?php
											}
										?>
									</td>
									<td class="center">
									</td>
								</tr>
							<?php
										} // $match = mysqli_fetch_array($match_query)
									} // mysqli_num_rows($match_query) > 0
								}	// $target = mysqli_fetch_array($target_query
							?>
							</tbody>
						</table>
					</div>
				</div>
				<!-- END OF Catalog Orders -->

            </div>
        </div>
    </div>
	<?php include "inc/profile_modal.php";?>

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



</body>

</html>

<?php
}
else
{
	$_SESSION['signin_msg'] = "Use ID with require permission not found !";
	header("Location: index.php");
	printf("<script>location.href='index.php'</script>");
}

?>
