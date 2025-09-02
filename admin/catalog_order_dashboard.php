<?php
// session_start();
include("inc/session.php");
include("inc/connect.php");
include("inc/lib.php");

function currency_accumulate(& $currency_list, $currency, $property, $value) {
	if (isset($currency_list[$currency][$property]))
		$currency_list[$currency][$property] += $value;
	else
		$currency_list[$currency][$property] = $value;
}

if (isset($_SESSION['admin_id']) && isset($_SESSION['admin_yearmonth']) ) {
	// Set up user and contest information
	include ("inc/load_common_data.php");

	// Create Currency list
	$currency_list = [];
?>
<!DOCTYPE html>
<html>
<head>
    <!-- Page title -->
	<title>Youth Photographic Society | Catalog Order Dashboard</title>

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
                    Catalog Order Dashboard for <?php echo $admin_contest_name;?>
                </h2>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
				<!-- Catalog Orders -->
				<div class="hpanel">
					<div class="panel-heading">
						<span class="lead">Catalog Orders</span>
						<a class="showhide"><i class="fa fa-chevron-up"></i></a>
					</div>
					<div class="panel-body">
						<table class="table">
							<thead>
								<tr>
									<th class="left">#</th>
									<th class="left">Ordered By</th>
									<th class="center">Copies</th><th>Price</th>
									<th>Postage</th><th>Order Value</th>
									<th>Paid</th><th>Gateway</th><th>Payment Due</th>
								</tr>
							</thead>
							<tbody>
							<?php
								$sql  = "SELECT catalog_order.profile_id, profile_name, phone, email, ";
								$sql .= "       catalog_order.currency, IFNULL(gateway, '') AS gateway, ";
								$sql .= "       SUM(number_of_copies) AS copies, SUM(catalog_price) AS price, ";
								$sql .= "       SUM(catalog_postage) AS postage, SUM(order_value) AS order_value, ";
								$sql .= "       IFNULL(SUM(payment.amount), 0) AS payment_received ";
								$sql .= "  FROM profile, catalog_order LEFT JOIN payment ";
								$sql .= "       ON payment.yearmonth = catalog_order.yearmonth AND payment.account = 'CTG' ";
								$sql .= "       AND payment.link_id = catalog_order.profile_id ";
								$sql .= " WHERE catalog_order.yearmonth = '$admin_yearmonth' ";
								$sql .= "   AND profile.profile_id = catalog_order.profile_id ";
								$sql .= " GROUP BY catalog_order.profile_id, profile_name, phone, email, catalog_order.currency, gateway ";
								$sql .= " ORDER BY profile_name ";
								$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

								$total_copies = 0;
								$slno = 0;
								while ($row = mysqli_fetch_array($query)) {
									++ $slno;
									currency_accumulate($currency_list, $row['currency'], 'total_copies', $row['copies']);
									currency_accumulate($currency_list, $row['currency'], 'total_price', $row['price'] * $row['copies']);
									currency_accumulate($currency_list, $row['currency'], 'total_postage', $row['postage'] * $row['copies']);
									currency_accumulate($currency_list, $row['currency'], 'total_order_value', $row['order_value']);
									currency_accumulate($currency_list, $row['currency'], 'total_payment_received', $row['payment_received']);
							?>
								<tr>
									<td class="left"><?= $slno;?></td>
									<td class="left">
										<?= $row['profile_name'] . " (" . $row['profile_id'] . ")<br>" . $row['phone'] . ", " . $row['email'];?>
									</td>
									<td class="center"><?= $row['copies'];?></td>
									<td><?= sprintf("%.02f %s", floatval($row['price'] * $row['copies']), $row['currency']);?></td>
									<td><?= sprintf("%.02f %s", floatval($row['postage'] * $row['copies']), $row['currency']);?></td>
									<td><?= sprintf("%.02f %s", floatval($row['order_value']), $row['currency']);?></td>
									<td><?= sprintf("%.02f %s", floatval($row['payment_received']), $row['currency']);?></td>
									<td><?= $row['gateway'];?></td>
									<td><?= sprintf("%.02f %s", floatval($row['order_value'] - $row['payment_received']), $row['currency']);?></td>
								</tr>
							<?php
								}
							?>
							</tbody>
							<tfoot>
								<tr>
									<th class="left">TOTAL</th>
									<th></th>
									<th class="center">
										<?php
											foreach ($currency_list as $currency => $data)
												echo $data['total_copies'] . " " . $currency . "<br>";
										?>
									</th>
									<th>
										<?php
											foreach ($currency_list as $currency => $data)
												echo sprintf("%.02f %s<br>", $data['total_price'], $currency);
										?>
									</th>
									<th>
										<?php
											foreach ($currency_list as $currency => $data)
												echo sprintf("%.02f %s<br>", $data['total_postage'], $currency);
										?>
									</th>
									<th>
										<?php
											foreach ($currency_list as $currency => $data)
												echo sprintf("%.02f %s<br>", $data['total_order_value'], $currency);
										?>
									</th>
									<th>
										<?php
											foreach ($currency_list as $currency => $data)
												echo sprintf("%.02f %s<br>", $data['total_payment_received'], $currency);
										?>
									</th>
									<th></th>
									<th>
										<?php
											foreach ($currency_list as $currency => $data)
												echo sprintf("%.02f %s<br>", $data['total_order_value'] - $data['total_payment_received'], $currency);
										?>
									</th>
								</tr>
							</tfoot>
						</table>
					</div>
					<div class="panel-footer">
						<div class="row">
							<div class="col-sm-12">
								<div class="pull-right">
									<div style="padding-left: 15px; padding-right: 15px; display: inline-block;">
										<?php
											if (has_permission($member_permissions, ["admin", "chairman", "secretary"])) {
										?>
										<a href="op/catalog_mailing_table.php" class="btn btn-info" ><i class="fa fa-download"></i> Catalog Mailing List</a>
										<?php
											}
										?>
									</div>
								</div>
							</div>
						</div>
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
