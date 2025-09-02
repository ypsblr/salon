<?php
// session_start();
include("inc/session.php");
include("inc/connect.php");
include("inc/lib.php");
include("inc/blacklist_lib.php");

// blacklist functions
if (isset($_SESSION['admin_id']) && isset($_SESSION['admin_yearmonth']) ) {
	// Set up user and contest information
	include ("inc/load_common_data.php");
?>
<!DOCTYPE html>
<html>

<head>
    <!-- Page title -->
	<title>Youth Photographic Society | Admin Panel</title>

	<?php include "inc/header.php"; ?>

	<style>
		table.table th, table.table th.left {
			text-align: left;
		}
		table.table th.right {
			text-align: right;
		}
		table.table th.center {
			text-align: center;
		}
		table.table td, table.table td.left {
			text-align : left;
		}
		table.table td.right {
			text-align : right;
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
	<!--[if lt IE 7]>
		<p class="alert alert-danger">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
	<![endif]-->

	<!-- Header -->
	<?php
		include "inc/master_topbar.php";
		include "inc/master_sidebar.php";
	?>
	<!-- Main Wrapper -->
	<div id="wrapper">
		<div class="content">
			<div class="row">
				<!-- JURY ADD/EDIT SCREEN -->
				<div class="col-md-12 col-lg-12">
					<div class="hpanel">
						<div class="panel=head">
							<h3>Blacklist Table</h3>
						</div>
						<div class="panel-body">
							<table class="table">
								<thead>
									<th>Profile</th><th>Email</th><th>Phone</th><th>Issuer</th><th>Reference</th><th>Sactioned Till</th><th>Withdrawn On</th>
								</thead>
								<tbody>
								<?php
									$sql = "SELECT * FROM blacklist ORDER BY entity_type, entity_name ";
									$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
									$prev_entity_type = "";
									while ($bl = mysqli_fetch_array($query)) {
										if ($bl['entity_type'] != $prev_entity_type) {
											$prev_entity_type = $bl['entity_type'];
								?>
									<tr>
										<th colspan="7"><?= $bl['entity_type'];?></th>
									</tr>
								<?php
										}
								?>
									<tr>
										<td><span style="text-decoration : <?= ($bl['withdrawn'] == '1') ? 'line-through' : 'none';?>;"><?= $bl['entity_name'];?></span></td>
										<td><?= $bl['email'];?></td>
										<td><?= $bl['phone'];?></td>
										<td><?= $bl['issuer'];?></td>
										<td><?= $bl['reference'];?></td>
										<td><?= $bl['expiry_date'];?></td>
										<td><?= ($bl['withdrawn_date'] == null) ? "" : date("d-m-Y", strtotime($bl['withdrawn_date']));?></td>
									</tr>
								<?php
									}
								?>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>	<!-- END of JURY ADD/EDIT Screen -->

			<!-- JURY LIST -->
			<div class="row">
				<div class="col-md-12 col-lg-12">
					<div class="hpanel">
						<div class="panel-head">
							<h3>Potential Profiles &amp; Exceptions</h3>
						</div>
						<div class="panel-body">
							<table class="table">
							<thead>
								<tr>
									<th colspan="5">Potential Profiles</th><th></th><th colspan="2" class="center">Exception</th>
								</tr>
								<tr>
									<th>Name</th>
									<th>Email</th>
									<th>Phone</th>
									<th class="center">Match</th>
									<th class="center">
										<?php
											if (has_permission($member_permissions, ["admin", "chairman", "secretary"])) {
										?>
										<a class="btn btn-warning" id="match-and-mark">Match &amp; Mark All</a><br>
										<?php
											}
										?>
										Marked
									</th>
									<th>|</th>
									<th class="center">ADDED ON</th>
									<th class="center">ADD</th>
									<th class="center">DEL</th>
								</tr>
							</thead>
							<tbody>
							<?php
								$sql = "SELECT profile_id, profile_name, email, phone, blacklist_match, blacklist_exception FROM profile";
								$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
								while($pr = mysqli_fetch_array($query)) {
									$profile_id = $pr['profile_id'];
									$profile_name = $pr['profile_name'];
									$email = $pr['email'];
									$phone = $pr['phone'];
									$blacklist_match = $pr['blacklist_match'];
									$blacklist_exception = $pr['blacklist_exception'];
									$blacklist_marked = ($blacklist_match == "") ? "" : "YES";
 									if ($blacklist_match == "" && $blacklist_exception == 0) {
										list($blacklist_match, $blacklist_name) = check_blacklist($profile_name, $email, $phone);
										// if ($blacklist_match != "MATCH" && $blacklist_match != "SIMILAR")
										//	$blacklist_match = "";
									}
									$exception_date = "";
									if ($blacklist_match != "" && $blacklist_exception != 0) {
										$sql = "SELECT * FROM blacklist_exception WHERE email = '$email' ";
										$beq = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
										if (mysqli_num_rows($beq) > 0) {
											$ber = mysqli_fetch_array($beq);
											$exception_date = date("d-m-Y", strtotime($ber['approval_date']));
										}
									}
									if ($blacklist_match != "") {
							?>
								<tr>
									<td><?= $profile_name;?></td>
									<td><?= $email;?></td>
									<td><?= $phone;?></td>
									<td class="center"><?= $blacklist_match;?></td>
									<td class="center"><?= $blacklist_marked;?></td>
									<td>|</td>
									<td><?= $exception_date;?></td>
									<td class="center">
										<?php
											if (has_permission($member_permissions, ["admin", "chairman", "secretary"])) {
										?>
										<a class="btn btn-info" href="op/exception.php?add=<?= $profile_id;?>&match=<?= $blacklist_match;?>">Add Exception</a>
										<?php
											}
										?>
									</td>
									<td class="center">
										<?php
											if (has_permission($member_permissions, ["admin", "chairman", "secretary"])) {
										?>
										<a class="btn btn-warning" href="op/exception.php?del=<?= $profile_id;?>">Del Exception</a>
										<?php
											}
										?>
									</td>
								</tr>
							<?php
									}
								}
							?>
							</tbody>
							</table>
						</div>
					</div>
				</div>		<!-- END OF JURY LIST -->
			</div>
		</div>

	<?php
		//include("inc/footer.php");
		include("inc/profile_modal.php");
	?>

	</div>

	<?php include("inc/footer.php"); ?>

	<!-- Vendor scripts -->
	<!-- DataTables -->
	<script src="plugin/datatable/js/jquery.dataTables.min.js"></script>
	<script src="plugin/datatable/js/dataTables.bootstrap.min.js"></script>
	<!-- DataTables buttons scripts -->
	<script src="plugin/pdfmake/build/pdfmake.min.js"></script>
	<script src="plugin/pdfmake/build/vfs_fonts.js"></script>
	<script src="plugin/datatables.net-buttons/js/buttons.html5.min.js"></script>
	<script src="plugin/datatables.net-buttons/js/buttons.print.min.js"></script>
	<script src="plugin/datatables.net-buttons/js/dataTables.buttons.min.js"></script>
	<script src="plugin/datatables.net-buttons-bs/js/buttons.bootstrap.min.js"></script>
	<script src="plugin/swal/js/sweetalert.min.js"></script>

	<!-- Dummy form for submission -->
	<form name="match-and-mark-form" id="match-and-mark-form" action="op/blacklist_update.php" method="post"></form>
<script>

    $(function () {
        $('.table').dataTable();
    });

	$("#match-and-mark").click(function(){
		swal(
			{
            	title: 'Mark ALL',
            	text:  'This will re-run match against all the profiles and Mark/Unmark them based on current blacklist. Do you want to continue ?',
				icon: "warning",
				buttons: true,
				dangerMode: true,
			}
		)
		.then(function (matchandmark) {
			if (matchandmark)
				$("#match-and-mark-form").submit();
		});
	})

</script>



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
