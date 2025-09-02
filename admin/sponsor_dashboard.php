<?php
// session_start();
include("inc/session.php");
include("inc/connect.php");
include("inc/lib.php");
include("inc/contest_lib.php");
include("inc/sponsorlib.php");

if (isset($_SESSION['admin_id']) && isset($_SESSION['admin_yearmonth']) ) {
	// Set up user and contest information
	include ("inc/load_common_data.php");

	// Prepare List of Sections
	$section_list = get_section_list($admin_yearmonth);

	// Prepare List of Sponsors who had sponsored
	$sponsor_list = sponsor_list($admin_yearmonth);


?>
<!DOCTYPE html>
<html>
<head>
    <!-- Page title -->
	<title>Youth Photographic Society | Entry Dashboard</title>

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
<div class="splash"> <div class="color-line"></div><div class="splash-title"><h1>   YPS ADMIN PANEL  </h1><p>Please Wait. </p><div class="spinner"> <div class="rect1"></div> <div class="rect2"></div> <div class="rect3"></div> <div class="rect4"></div> <div class="rect5"></div> </div> </div> </div>
<!--[if lt IE 7]>
<p class="alert alert-danger">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
<![endif]-->

<?php
include "inc/master_topbar.php";
include "inc/master_sidebar.php";
?>

<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-lg-12 text-center m-t-md">
                <h2>
                    Sponsorship Dashboard for <?php echo $admin_contest_name;?>
                </h2>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
				<!-- Participants -->
				<div class="hpanel">
					<div class="panel-heading">
						<span class="lead">Awards</span>
						<a class="showhide"><i class="fa fa-chevron-up"></i></a>
					</div>
					<div class="panel-body">
					<?php
						// Set Grang totals
						$gt_num_awards = 0;
						$gt_num_awards_closed = 0;
						$gt_num_awards_open = 0;
						$gt_amount_awards = 0.0;
						$gt_amount_awards_open = 0.0;
						$gt_num_awards_sponsored = 0;
						$gt_amount_sponsored = 0.0;
						$gt_amount_paid = 0.0;
						$gt_amount_outstanding = 0.0;

						// Picture Awards
						foreach ($section_list as $section => $data) {
					?>
						<h4 class="text-info"><?= $section;?></h4>
						<table class="table">
							<thead>
								<tr>
									<th class="left">Award</th><th>Amt. per Award</th>
									<th>Awards</th><th>Closed</th><th>Open</th><th>Open Amount</th>
									<th class="left">Sponsor</th><th>Sponsored</th>
									<th>Sponsorship Amount</th><th>Amount Paid</th>
									<th>Outstanding</th>
								</tr>
							</thead>
							<tbody>

					<?php
							$sql  = "SELECT award_id, award_group, award_name, sponsored_awards, sponsorship_per_award, ";
							$sql .= "       IFNULL(COUNT(number_of_units), 0) AS num_sponsors, IFNULL(SUM(number_of_units), 0.0) AS num_sponsored ";
							$sql .= "  FROM award ";
							$sql .= "  LEFT JOIN sponsorship ";
							$sql .= "         ON sponsorship.yearmonth = award.yearmonth ";
							$sql .= "        AND sponsorship_type = 'AWARD' ";
							$sql .= "        AND sponsorship.link_id = award.award_id ";
							$sql .= " WHERE award.yearmonth = '$admin_yearmonth' ";
							$sql .= "   AND section = '$section' ";
							$sql .= "   AND award_type = 'pic' ";
							$sql .= "   AND sponsored_awards > 0 ";
							$sql .= " GROUP BY award_id, award_group, award_name, sponsored_awards, sponsorship_per_award ";
							$sql .= " ORDER BY award_group, level, sequence ";
							$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

							// Set totals
							$num_awards = 0;
							$num_awards_closed = 0;
							$num_awards_open = 0;
							$amount_awards = 0.0;
							$amount_awards_open = 0.0;

							$num_awards_sponsored = 0;
							$amount_sponsored = 0.0;
							$amount_paid = 0.0;
							$amount_outstanding = 0.0;
							$prev_award_group = "";
							while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
								$award_id = $row['award_id'];
								$num_awards += $row['sponsored_awards'];
								$amount_awards += $row['sponsored_awards'] * $row['sponsorship_per_award'];
								$num_awards_closed += $row['num_sponsored'];
								$num_awards_open += $row['sponsored_awards'] - $row['num_sponsored'];
								$amount_awards_open += ($row['sponsored_awards'] - $row['num_sponsored']) * $row['sponsorship_per_award'];
								$num_rows = ($row['num_sponsors'] == 0) ? 1 : $row['num_sponsors'];

								if ($row['award_group'] != $prev_award_group) {
									$prev_award_group = $row['award_group'];
					?>
								<tr><td colspan="11" class="left"><span class="lead"><?= $row['award_group'];?></span></td></tr>
					<?php
								}
								if ($row['num_sponsored'] == 0) {
					?>
								<tr>
									<td rowspan="<?= $num_rows;?>" class="left"><?= $row['award_name'];?></td>
									<td rowspan="<?= $num_rows;?>"><?= sprintf("%.02f", floatval($row['sponsorship_per_award']));?></td>
									<td rowspan="<?= $num_rows;?>"><?= sprintf("%d", intval($row['sponsored_awards']));?></td>
									<td rowspan="<?= $num_rows;?>"><?= sprintf("%d", intval($row['num_sponsored']));?></td>
									<td rowspan="<?= $num_rows;?>"><?= sprintf("%d", intval($row['sponsored_awards'] - $row['num_sponsored']));?></td>
									<td rowspan="<?= $num_rows;?>"><?= sprintf("%.02f", floatval(($row['sponsored_awards'] - $row['num_sponsored']) * $row['sponsorship_per_award']));?></td>
									<td></td><td>0</td><td>0.00</td><td>0.00</td><td>0.00</td>
								</tr>
					<?php
								}
								else {
									// Fetch List of Sponsors
									$sql  = "SELECT sponsor_name, number_of_units, total_sponsorship_amount, payment_received ";
									$sql .= "  FROM sponsorship, sponsor ";
									$sql .= " WHERE sponsorship.yearmonth = '$admin_yearmonth' ";
									$sql .= "   AND sponsorship_type = 'AWARD' ";
									$sql .= "   AND sponsorship.link_id = '$award_id' ";
									$sql .= "   AND sponsor.sponsor_id = sponsorship.sponsor_id ";
									$subq = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
									$first_row = true;
									while ($subr = mysqli_fetch_array($subq, MYSQLI_ASSOC)) {
										$num_awards_sponsored += $subr['number_of_units'];
										$amount_sponsored += $subr['total_sponsorship_amount'];
										$amount_paid += $subr['payment_received'];
										$amount_outstanding += ($subr['total_sponsorship_amount'] - $subr['payment_received']);
					?>
								<tr>
					<?php
										if ($first_row) {
											$first_row = false;
					?>
									<td rowspan="<?= $num_rows;?>" class="left"><?= $row['award_name'];?></td>
									<td rowspan="<?= $num_rows;?>"><?= sprintf("%.02f", floatval($row['sponsorship_per_award']));?></td>
									<td rowspan="<?= $num_rows;?>"><?= sprintf("%d", intval($row['sponsored_awards']));?></td>
									<td rowspan="<?= $num_rows;?>"><?= sprintf("%d", intval($row['num_sponsored']));?></td>
									<td rowspan="<?= $num_rows;?>"><?= sprintf("%d", intval($row['sponsored_awards'] - $row['num_sponsored']));?></td>
									<td rowspan="<?= $num_rows;?>"><?= sprintf("%.02f", floatval(($row['sponsored_awards'] - $row['num_sponsored']) * $row['sponsorship_per_award']));?></td>

					<?php
										}
					?>
									<td class="left"><?= (empty($subr)) ? "" : $subr['sponsor_name'];?></td>
									<td><?= sprintf("%d", intval($subr['number_of_units']));?></td>
									<td><?= sprintf("%.02f", floatval($subr['total_sponsorship_amount']));?></td>
									<td><?= sprintf("%.02f", floatval($subr['payment_received']));?></td>
									<td><?= sprintf("%.02f", floatval($subr['total_sponsorship_amount'] - $subr['payment_received']));?></td>
								</tr>
					<?php
									} // award sponsors
								}	// else
							} // for each award
							// Compute Grand Totals
							$gt_num_awards += $num_awards;
							$gt_num_awards_closed += $num_awards_closed;
							$gt_num_awards_open += $num_awards_open;
							$gt_amount_awards += $amount_awards;
							$gt_amount_awards_open += $amount_awards_open;
							$gt_num_awards_sponsored += $num_awards_sponsored;
							$gt_amount_sponsored += $amount_sponsored;
							$gt_amount_paid += $amount_paid;
							$gt_amount_outstanding += $amount_outstanding;
							// Print Totals for the section
					?>
							<tfoot>
								<tr>
									<th class="left">TOTAL</th><th><?= sprintf("%.02f", floatval($amount_awards));?></th>
									<th><?= sprintf("%d", intval($num_awards));?></th><th><?= sprintf("%d", intval($num_awards_closed));?></th>
									<th><?= sprintf("%d", intval($num_awards_open));?></th><th><?= sprintf("%.02f", floatval($amount_awards_open));?></th>
									<th class="left"></th><th><?= sprintf("%d", intval($num_awards_sponsored));?></th>
									<th><?= sprintf("%.02f", floatval($amount_sponsored));?></th><th><?= sprintf("%.02f", floatval($amount_paid));?></th>
									<th><?= sprintf("%.02f", floatval($amount_outstanding));?></th>
								</tr>
							</tfoot>
						</table>
						<hr>
					<?php
						} // for each section
					?>
						<!-- Entry & Club Awards -->
					<?php
						$sql  = "SELECT award_id, award_group, award_name, sponsored_awards, sponsorship_per_award, ";
						$sql .= "       IFNULL(COUNT(number_of_units), 0) AS num_sponsors, IFNULL(SUM(number_of_units), 0.0) AS num_sponsored ";
						$sql .= "  FROM award ";
						$sql .= "  LEFT JOIN sponsorship ";
						$sql .= "         ON sponsorship.yearmonth = award.yearmonth ";
						$sql .= "        AND sponsorship_type = 'AWARD' ";
						$sql .= "        AND sponsorship.link_id = award.award_id ";
						$sql .= " WHERE award.yearmonth = '$admin_yearmonth' ";
						$sql .= "   AND (award_type = 'entry' OR award_type = 'club') ";
						$sql .= "   AND sponsored_awards > 0 ";
						$sql .= " GROUP BY award_id, award_group, award_name, sponsored_awards, sponsorship_per_award ";
						$sql .= " ORDER BY award_group, level, sequence ";
						$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
						if (mysqli_num_rows($query) > 0) {
					?>
					<h4 class="text-info">INDIVDUAL AWARDS</h4>
					<table class="table">
						<thead>
							<tr>
								<th class="left">Award</th><th>Amt. per Award</th>
								<th>Awards</th><th>Closed</th><th>Open</th><th>Open Amount</th>
								<th class="left">Sponsor</th><th>Sponsored</th>
								<th>Sponsorship Amount</th><th>Amount Paid</th>
								<th>Outstanding</th>
							</tr>
						</thead>
						<tbody>
					<?php
							// Set totals
							$num_awards = 0;
							$num_awards_closed = 0;
							$num_awards_open = 0;
							$amount_awards = 0.0;
							$amount_awards_open = 0.0;

							$num_awards_sponsored = 0;
							$amount_sponsored = 0.0;
							$amount_paid = 0.0;
							$amount_outstanding = 0.0;
							$prev_award_group = "";
							while ($row = mysqli_fetch_array($query)) {
								$award_id = $row['award_id'];
								$num_awards += $row['sponsored_awards'];
								$amount_awards += $row['sponsored_awards'] * $row['sponsorship_per_award'];
								$num_awards_closed += $row['num_sponsored'];
								$num_awards_open += $row['sponsored_awards'] - $row['num_sponsored'];
								$amount_awards_open += ($row['sponsored_awards'] - $row['num_sponsored']) * $row['sponsorship_per_award'];
								$num_rows = ($row['num_sponsors'] == 0) ? 1 : $row['num_sponsors'];
								if ($row['award_group'] != $prev_award_group) {
									$prev_award_group = $row['award_group'];
					?>
								<tr><td colspan="11" class="left"><span class="lead"><?= $row['award_group'];?></span></td></tr>
					<?php
								}
								if ($row['num_sponsored'] == 0) {
					?>
								<tr>
									<td rowspan="<?= $num_rows;?>" class="left"><?= $row['award_name'];?></td>
									<td rowspan="<?= $num_rows;?>"><?= sprintf("%.02f", floatval($row['sponsorship_per_award']));?></td>
									<td rowspan="<?= $num_rows;?>"><?= sprintf("%d", intval($row['sponsored_awards']));?></td>
									<td rowspan="<?= $num_rows;?>"><?= sprintf("%d", intval($row['num_sponsored']));?></td>
									<td rowspan="<?= $num_rows;?>"><?= sprintf("%d", intval($row['sponsored_awards'] - $row['num_sponsored']));?></td>
									<td rowspan="<?= $num_rows;?>"><?= sprintf("%.02f", floatval(($row['sponsored_awards'] - $row['num_sponsored']) * $row['sponsorship_per_award']));?></td>
									<td></td><td>0</td><td>0.00</td><td>0.00</td><td>0.00</td>
								</tr>
					<?php
								}
								else {
									// Fetch List of Sponsors
									$sql  = "SELECT sponsor_name, number_of_units, total_sponsorship_amount, payment_received ";
									$sql .= "  FROM sponsorship, sponsor ";
									$sql .= " WHERE sponsorship.yearmonth = '$admin_yearmonth' ";
									$sql .= "   AND sponsorship_type = 'AWARD' ";
									$sql .= "   AND sponsorship.link_id = '$award_id' ";
									$sql .= "   AND sponsor.sponsor_id = sponsorship.sponsor_id ";
									$subq = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
									$first_row = true;
									while ($subr = mysqli_fetch_array($subq, MYSQLI_ASSOC)) {
										$num_awards_sponsored += $subr['number_of_units'];
										$amount_sponsored += $subr['total_sponsorship_amount'];
										$amount_paid += $subr['payment_received'];
										$amount_outstanding += ($subr['total_sponsorship_amount'] - $subr['payment_received']);
					?>
								<tr>
					<?php
										if ($first_row) {
											$first_row = false;
					?>
									<td rowspan="<?= $num_rows;?>" class="left"><?= $row['award_name'];?></td>
									<td rowspan="<?= $num_rows;?>"><?= sprintf("%.02f", floatval($row['sponsorship_per_award']));?></td>
									<td rowspan="<?= $num_rows;?>"><?= sprintf("%d", intval($row['sponsored_awards']));?></td>
									<td rowspan="<?= $num_rows;?>"><?= sprintf("%d", intval($row['num_sponsored']));?></td>
									<td rowspan="<?= $num_rows;?>"><?= sprintf("%d", intval($row['sponsored_awards'] - $row['num_sponsored']));?></td>
									<td rowspan="<?= $num_rows;?>"><?= sprintf("%.02f", floatval(($row['sponsored_awards'] - $row['num_sponsored']) * $row['sponsorship_per_award']));?></td>

					<?php
										}
					?>
									<td class="left"><?= (empty($subr)) ? "" : $subr['sponsor_name'];?></td>
									<td><?= sprintf("%d", intval($subr['number_of_units']));?></td>
									<td><?= sprintf("%.02f", floatval($subr['total_sponsorship_amount']));?></td>
									<td><?= sprintf("%.02f", floatval($subr['payment_received']));?></td>
									<td><?= sprintf("%.02f", floatval($subr['total_sponsorship_amount'] - $subr['payment_received']));?></td>
								</tr>
					<?php
									} // award sponsors
								}	// else

							}	// while $row = fetch row
							// Compute Grand Totals
							$gt_num_awards += $num_awards;
							$gt_num_awards_closed += $num_awards_closed;
							$gt_num_awards_open += $num_awards_open;
							$gt_amount_awards += $amount_awards;
							$gt_amount_awards_open += $amount_awards_open;
							$gt_num_awards_sponsored += $num_awards_sponsored;
							$gt_amount_sponsored += $amount_sponsored;
							$gt_amount_paid += $amount_paid;
							$gt_amount_outstanding += $amount_outstanding;
							// Print Totals for the section
					?>
							<tfoot>
								<tr>
									<th class="left">TOTAL</th><th><?= sprintf("%.02f", floatval($amount_awards));?></th>
									<th><?= sprintf("%d", intval($num_awards));?></th><th><?= sprintf("%d", intval($num_awards_closed));?></th>
									<th><?= sprintf("%d", intval($num_awards_open));?></th><th><?= sprintf("%.02f", floatval($amount_awards_open));?></th>
									<th class="left"></th><th><?= sprintf("%d", intval($num_awards_sponsored));?></th>
									<th><?= sprintf("%.02f", floatval($amount_sponsored));?></th><th><?= sprintf("%.02f", floatval($amount_paid));?></th>
									<th><?= sprintf("%.02f", floatval($amount_outstanding));?></th>
								</tr>
							</tfoot>
						</table>
						<hr>
					<?php
						}	// If there are sponsored entry awards
					?>
						<!-- Grand Totals -->
						<h4 class='text-info'><?= $admin_contest_name;?></h4>
						<table class="table">
							<thead>
								<tr>
									<th class="left">Award</th><th>Amt. per Award</th>
									<th>Awards</th><th>Closed</th><th>Open</th><th>Open Amount</th>
									<th class="left">Sponsor</th><th>Sponsored</th>
									<th>Sponsorship Amount</th><th>Amount Paid</th>
									<th>Outstanding</th>
								</tr>
							</thead>
							<tbody></tbody>
							<tfoot>
								<tr>
									<th class="left">GRAND TOTAL</th><th><?= sprintf("%.02f", floatval($gt_amount_awards));?></th>
									<th><?= sprintf("%d", intval($gt_num_awards));?></th><th><?= sprintf("%d", intval($gt_num_awards_closed));?></th>
									<th><?= sprintf("%d", intval($gt_num_awards_open));?></th><th><?= sprintf("%.02f", floatval($gt_amount_awards_open));?></th>
									<th class="left"></th><th><?= sprintf("%d", intval($gt_num_awards_sponsored));?></th>
									<th><?= sprintf("%.02f", floatval($gt_amount_sponsored));?></th><th><?= sprintf("%.02f", floatval($gt_amount_paid));?></th>
									<th><?= sprintf("%.02f", floatval($gt_amount_outstanding));?></th>
								</tr>
							</tfoot>
						</table>
					</div>
				</div>

				<!-- Sponsor-wise Statistics -->
                <div class="hpanel">
                    <div class="panel-heading">
                        <!-- <div class="panel-tools"> -->
							<span class="lead">Sponsors</span>
                            <a class="showhide"><i class="fa fa-chevron-up"></i></a>
                            <!-- <a class="closebox"><i class="fa fa-times"></i></a> -->
                        <!-- </div> -->
                    </div>
                    <div class="panel-body">
						<table class='table'>
							<thead>
								<tr>
									<th class="left">Sponsor</th>
									<th class="left">Email</th>
									<th class="center">Phone</th>
									<th class="left">Section</th>
									<th class="left">Target</th>
									<th class="left">Award Name</th>
									<th>Award Money</th>
									<th># Awards</th>
									<th>Sponsored Amt.</th>
									<th>Amount Paid</th>
									<th>Due</th>
                                    <th>Action</th>
								</tr>
							</thead>
							<tbody>
							<?php
								// Some Grand Totals
								$amount_sponsored = 0.0;
								$amount_paid = 0.0;
								$amount_due = 0.0;
								$sponsor_list = sponsor_list($admin_yearmonth);
								foreach ($sponsor_list as $sponsor) {
								    
								    // var_dump($sponsor_list);
                                    
									$rows = $sponsor['num_sponsorships'] + 1;
									$amount_sponsored += $sponsor['total_sponsorship_amount'];
									$amount_paid += $sponsor['payment_received'];
									$amount_due += ($sponsor['total_sponsorship_amount'] - $sponsor['payment_received']);
							?>
								<tr>
									<td rowspan="<?= $rows;?>" class="left" ><b><?= $sponsor['sponsor_name'];?></b></td>
									<td rowspan="<?= $rows;?>" class="left" ><b><?= $sponsor['sponsor_email'];?></b></td>
									<td rowspan="<?= $rows;?>" class="center" ><b><?= $sponsor['sponsor_phone'];?></b></td>
									<td></td><td></td><td></td><td></td>
									<td><b><?= sprintf("%d", intval($sponsor['num_awards_sponsored']));?></b></td>
									<td><b><?= sprintf("%.02f", floatval($sponsor['total_sponsorship_amount']));?></b></td>
									<td><b><?= sprintf("%.02f", floatval($sponsor['payment_received']));?></b></td>
									<td><b><?= sprintf("%.02f", floatval($sponsor['total_sponsorship_amount'] - $sponsor['payment_received']));?></b></td>
                                    
                                    <td>
                                        <b>
                                            <?php if(($sponsor['total_sponsorship_amount'] - $sponsor['payment_received']) > 0): ?>
                                                <a href="process_sponsorship.php?link_id=<?= $sponsor['sponsor_id'];?>
                                                    &yearmonth=<?= $admin_yearmonth;?>
                                                    &sponsor_name=<?= urlencode($sponsor['sponsor_name']);?>
                                                    &sponsor_email=<?= urlencode($sponsor['sponsor_email']);?>
                                                    &sponsor_phone=<?= urlencode($sponsor['sponsor_phone']);?>
                                                    &total_sponsorship_amount=<?= $sponsor['total_sponsorship_amount'] - $sponsor['payment_received'];?>">Update Payment
                                                </a>
                                            <?php endif; ?>
                                        </b>
                                    </td>
								</tr>
							<?php
									$sponsor_id = $sponsor['sponsor_id'];

									$sql  = "SELECT section, award_group, award_name, sponsorship_per_award, number_of_units, total_sponsorship_amount, ";
									$sql .= "       payment_received ";
									$sql .= "  FROM sponsorship, award ";
									$sql .= " WHERE sponsorship.yearmonth = '$admin_yearmonth' ";
									$sql .= "   AND sponsorship_type = 'AWARD' ";
									$sql .= "   AND sponsor_id = '$sponsor_id' ";
									$sql .= "   AND award.yearmonth = sponsorship.yearmonth ";
									$sql .= "   AND award.award_id = sponsorship.link_id ";
									$sql .= " ORDER BY award.section, award.award_group, award.level, award.sequence ";
									$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
									while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
							?>
								<tr>
									<td class="left"><?= $row['section'];?></td>
									<td class="left"><?= $row['award_group'];?></td>
									<td class="left"><?= $row['award_name'];?></td>
									<td><?= sprintf("%.02f", floatval($row['sponsorship_per_award']));?></td>
									<td><?= sprintf("%d", intval($row['number_of_units']));?></td>
									<td><?= sprintf("%.02f", floatval($row['total_sponsorship_amount']));?></td>
									<td><?= sprintf("%.02f", floatval($row['payment_received']));?></td>
									<td><?= sprintf("%.02f", floatval($row['total_sponsorship_amount'] - $row['payment_received']));?></td>
								</tr>
							<?php
									} // Each award for a sponsor
								} // Each Sponsor
							?>
							</tbody>
							<tfoot>
								<tr>
									<th></th><th></th><th></th><th></th>
									<th></th><th></th><th></th><th></th>
									<th><?= sprintf("%.02f", floatval($amount_sponsored));?></th>
									<th><?= sprintf("%.02f", floatval($amount_paid));?></th>
									<th><?= sprintf("%.02f", floatval($amount_due));?></th>
								</tr>
							</tfoot>
						</table>
					</div>
                </div>

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
header("Location: ".$_SERVER['HTTP_REFERER']);
printf("<script>location.href='".$_SERVER['HTTP_REFERER']."'</script>");
}

?>
