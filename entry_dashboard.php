<?php 
session_start();
include("inc/connect.php");
include("inc/lib.php");

if (isset($_SESSION['admin_id']) && isset($_SESSION['admin_yearmonth']) ) {
	// Set up user and contest information
	include ("inc/load_common_data.php");
	
	// Prepare List of Entrant Categories
	$entrant_category_list = [];
	$sql = "SELECT * FROM entrant_category WHERE yearmonth = '$admin_yearmonth' ";
	$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	while($row = mysqli_fetch_array($query, MYSQLI_ASSOC))
		$entrant_category_list[$row['entrant_category']] = $row;
	
	// Prepare Data for Entrant Category
	// 1. Number of Participants
	$sql  = "SELECT entrant_category.entrant_category, IFNULL(COUNT(entry.profile_id), 0) AS num_entry ";
	$sql .= "  FROM entrant_category ";
	$sql .= "  LEFT JOIN entry ON entry.yearmonth = entrant_category.yearmonth AND entry.entrant_category = entrant_category.entrant_category ";
	$sql .= " WHERE entrant_category.yearmonth = '$admin_yearmonth' ";
	$sql .= " GROUP BY entrant_category.entrant_category ";
	$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	while ($row = mysqli_fetch_array($query))
		$entrant_category_list[$row['entrant_category']]['entrants'] = $row['num_entry'];
	
	// 2. New Number of Entrants
	$sql  = "SELECT entrant_category.entrant_category, IFNULL(COUNT(A.profile_id), 0) AS num_entry ";
	$sql .= "  FROM entrant_category ";
	$sql .= "  LEFT JOIN entry A ON A.yearmonth = entrant_category.yearmonth AND A.entrant_category = entrant_category.entrant_category ";
	$sql .= "                   AND A.profile_id NOT IN (SELECT B.profile_id FROM entry B WHERE yearmonth != '$admin_yearmonth') ";
	$sql .= " WHERE entrant_category.yearmonth = '$admin_yearmonth' ";
	$sql .= " GROUP BY entrant_category.entrant_category ";
	$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	while ($row = mysqli_fetch_array($query))
		$entrant_category_list[$row['entrant_category']]['entrants_new'] = $row['num_entry'];
	
	// 3. Paid Entrants
	$sql  = "SELECT entrant_category.entrant_category, IFNULL(COUNT(entry.profile_id), 0) AS num_entry, ";
	$sql .= "       IFNULL(SUM(entry.fees_payable), 0.0) AS fees_payable, IFNULL(SUM(entry.discount_applicable), 0.0) AS discount_applicable, ";
	$sql .= "       IFNULL(SUM(entry.payment_received), 0.0) AS payment_received, ";
	$sql .= "       IFNULL(SUM(entry.digital_sections), 0.0) AS digital_sections, IFNULL(SUM(entry.print_sections), 0.0) AS print_sections ";
	$sql .= "  FROM entrant_category ";
	$sql .= "  LEFT JOIN entry ON entry.yearmonth = entrant_category.yearmonth AND entry.entrant_category = entrant_category.entrant_category ";
	$sql .= "                 AND entry.fees_payable > 0.0 ";
	$sql .= " WHERE entrant_category.yearmonth = '$admin_yearmonth' ";
	$sql .= " GROUP BY entrant_category.entrant_category ";
	$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	while ($row = mysqli_fetch_array($query)) {
		$entrant_category_list[$row['entrant_category']]['entrants_opted'] = $row['num_entry'];
		$entrant_category_list[$row['entrant_category']]['fees_payable'] = $row['fees_payable'];
		$entrant_category_list[$row['entrant_category']]['discount_applicable'] = $row['discount_applicable'];
		$entrant_category_list[$row['entrant_category']]['payment_due'] = $row['fees_payable'] - $row['discount_applicable'];
		$entrant_category_list[$row['entrant_category']]['payment_received'] = $row['payment_received'];
		$entrant_category_list[$row['entrant_category']]['payment_pending'] = $row['fees_payable'] - $row['discount_applicable'] - $row['payment_received'];
		$entrant_category_list[$row['entrant_category']]['max_pics'] = ($row['digital_sections'] + $row['print_sections']) * 4;
	}
	
	// 4. Uploads
	// Initialize num_pics
	foreach ($entrant_category_list as $entrant_category => $row) {
		$entrant_category_list[$entrant_category]['num_pics'] = 0;
	}
	
	$sql  = "SELECT entry.entrant_category, IFNULL(COUNT(pic.pic_id), 0) AS num_pics ";
	$sql .= "  FROM entry, pic ";
	$sql .= " WHERE entry.yearmonth = '$admin_yearmonth' ";
	$sql .= "   AND pic.yearmonth = entry.yearmonth ";
	$sql .= "   AND pic.profile_id = entry.profile_id ";
	$sql .= " GROUP BY entry.entrant_category ";
	$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	while ($row = mysqli_fetch_array($query))
		$entrant_category_list[$row['entrant_category']]['num_pics'] = $row['num_pics'];
	
	// List of Rejection Errors
	$rejection_list = [];
	$sql = "SELECT * FROM email_template WHERE template_type = 'user_notification' AND will_cause_rejection = 1 ";
	$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	while ($row = mysqli_fetch_array($query))
		$rejection_list[$row['template_code']]['reason_name'] = $row['template_name'];
	
	// UPLOAD Statistics
	$section_list = [];
	$sql = "SELECT * FROM section WHERE yearmonth = '$admin_yearmonth' ";
	$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	while ($row = mysqli_fetch_array($query)) {
		$section = $row['section'];
		$section_list[$section] = $row;
		
		// Number of uploads
		$sql  = "SELECT COUNT(*) AS num_pics FROM pic ";
		$sql .= " WHERE pic.yearmonth = '$admin_yearmonth' ";
		$sql .= "   AND pic.section = '$section' ";
		$subq = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		$subr = mysqli_fetch_array($subq);
		$section_list[$section]['num_pics'] = $subr['num_pics'];
		
		// Add Columns for each entrant_category
		foreach ($entrant_category_list as $entrant_category => $row)
			$section_list[$section][$entrant_category] = 0;

		$sql  = "SELECT entry.entrant_category, COUNT(*) AS num_pics ";
		$sql .= "  FROM entry, pic ";
		$sql .= " WHERE entry.yearmonth = '$admin_yearmonth' ";
		$sql .= "   AND pic.yearmonth = entry.yearmonth ";
		$sql .= "   AND pic.profile_id = entry.profile_id ";
		$sql .= " GROUP BY entry.entrant_category ";
		$subq = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		while ($subr = mysqli_fetch_array($subq)) {
			$section_list[$section][$subr['entrant_category']] = $subr['num_pics'];
		}
		
		// Add Columns for each reject category
		foreach ($rejection_list as $rejection_code => $data) {
			$section_list[$section][$rejection_code] = 0;
			$sql  = "SELECT COUNT(*) AS num_pics FROM pic ";
			$sql .= " WHERE yearmonth = '$admin_yearmonth' ";
			$sql .= "   AND section = '$section' ";
			$sql .= "   AND notifications LIKE '%$rejection_code%' ";
			$subq = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
			$subr = mysqli_fetch_array($subq);
			$section_list[$section][$rejection_code] = $subr['num_pics'];
		}
		
	}
	
?>



<!DOCTYPE html>
<html>
<head>
    <!-- Page title -->
	<title>Youth Photographic Society | Entry Dashboard</title>

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
    <div class="content animate-panel">
        <div class="row">
            <div class="col-lg-12 text-center m-t-md">
                <h2>
                    Participant Dashboard for <?php echo $admin_contest_name;?>
                </h2>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
				<!-- Participants -->
				<div class="hpanel">
					<div class="panel-heading">
						<span class="lead">Participants</span>
						<a class="showhide"><i class="fa fa-chevron-up"></i></a>
					</div>
					<div class="panel-body">
						<table class="table">
							<thead>
								<tr>
									<th>Category</th><th>Registered</th>
									<th>New</th><th>Entered</th>
									<th>Fees</th><th>Discount</th>
									<th>Due</th><th>Paid</th>
									<th>Max Uploads</th><th>Uploads</th>
								</tr>
							</thead>
							<tbody>
							<?php
								$total_registered = 0;
								$total_new = 0;
								$total_entered = 0;
								$total_fees = (float)0.0;
								$total_discount = 0.0;
								$total_due = 0.0;
								$total_paid = 0.0;
								$max_uploads = 0;
								$actual_uploads = 0;
								foreach($entrant_category_list AS $entrant_category => $data) {
									$total_registered += $data['entrants'];
									$total_new += $data['entrants_new'];
									$total_entered += $data['entrants_opted'];
									$total_fees += $data['fees_payable'];
									$total_discount += $data['discount_applicable'];
									$total_due += $data['payment_due'];
									$total_paid += $data['payment_received'];
									$max_uploads += $data['max_pics'];
									$actual_uploads += $data['num_pics'];
							?>
								<tr>
									<td><?= $entrant_category;?></td><td><?= sprintf("%5d", $data['entrants']);?></td>
									<td><?= sprintf("%5d", $data['entrants_new']);?></td><td><?= sprintf("%5d", $data['entrants_opted']);?></td>
									<td><?= sprintf("%8.0f", $data['fees_payable']);?></td><td><?= sprintf("%8.0f", $data['discount_applicable']);?></td>
									<td><?= sprintf("%8.0f", $data['payment_due']);?></td><td><?= sprintf("%8.0f", $data['payment_received']);?></td>
									<td><?= sprintf("%6d", $data['max_pics']);?></td><td><?= sprintf("%6d", $data['num_pics']);?></td>
								</tr>
							<?php
								}
							?>
							</tbody>
							<tfoot>
								<tr>
									<th>TOTAL</th><th><?= $total_registered;?></th>
									<th><?= $total_new;?></th><th><?= $total_entered;?></th>
									<th><?= $total_fees;?></th><th><?= $total_discount;?></th>
									<th><?= $total_due;?></th><th><?= $total_paid;?></th>
									<th><?= $max_uploads;?></th><th><?= $actual_uploads;?></th>
								</tr>
							</tfoot>
						</table>
					</div>
				</div>
				
				<!-- Section-wise Statistics -->
                <div class="hpanel">
                    <div class="panel-heading">
                        <!-- <div class="panel-tools"> -->
							<span class="lead">Uploads</span>
                            <a class="showhide"><i class="fa fa-chevron-up"></i></a>
                            <!-- <a class="closebox"><i class="fa fa-times"></i></a> -->							
                        <!-- </div> -->
                    </div>
                    <div class="panel-body">
						<table class="table">
							<thead>
								<tr>
									<th rowspan="2">Section</th><th rowspan="2">Uploads</th>
									<th colspan="<?= sizeof($entrant_category_list);?>" style="text-align : center;" >By Participant Category</th>
									<th colspan= "<?= sizeof($rejection_list);?>" style="text-align : center;" >Rejected Pictures</th>
								</tr>
								<tr>
								<?php
									foreach ($entrant_category_list as $entrant_category => $data) {
								?>
									<th><?= $entrant_category;?></th>
								<?php
									}
									foreach ($rejection_list as $rejection_code => $data) {
								?>
									<th><?= $data['reason_name'];?></th>
								<?php
									}
								?>
								</tr>
							</thead>
							<tbody>
							<?php
								// Set up totals
								$total_pics = 0;
								foreach ($entrant_category_list as $entrant_category => $data)
									$entrant_category_list[$entrant_category]['total_pics'] = 0;
	
								foreach ($rejection_list as $rejection_code => $data)
									$rejection_list[$rejection_code]['total_pics'] = 0;

								// Render ROWS
								foreach ($section_list as $section => $data) {
									$total_pics += $data['num_pics'];
							?>
								<tr>
									<td><?= $section;?></td>
									<td><?= $data['num_pics'];?></td>
							<?php
									foreach ($entrant_category_list as $entrant_category => $ec_data) {
										$entrant_category_list[$entrant_category]['total_pics'] += $data[$entrant_category];
							?>
									<td><?= $data[$entrant_category];?></td>
							<?php
									}
									foreach ($rejection_list as $rejection_code => $rej_data) {
										$rejection_list[$rejection_code]['total_pics'] += $data[$rejection_code];
							?>
									<td><?= $data[$rejection_code];?></td>
							<?php
									}
							?>
								</tr>
							<?php
								}
							?>
							</tbody>
							<tfoot>
								<tr>
									<th>TOTAL</th>
									<th><?= $total_pics;?></th>
								<?php
										foreach ($entrant_category_list as $entrant_category => $data) {
								?>
										<th><?= $data['total_pics'];?></th>
								<?php
										}
										foreach ($rejection_list as $rejection_code => $data) {
								?>
										<th><?= $data['total_pics'];?></th>
								<?php
										}
								?>
									
								</tr>
							</tfoot>
						</table>
					</div>
                </div>
				
            </div>
        </div>
    </div>

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
<script src="plugin/swal/js/sweet_alert.js"></script>



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