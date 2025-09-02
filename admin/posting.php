<?php
// session_start();
include("inc/session.php");
include("inc/connect.php");
include("inc/lib.php");

$json_file_name = "posting.json";

function toConsole($data) {
    $output = $data;
    if (is_array($output))
        $output = implode(',', $output);

    echo "<script>console.log('Debug Objects: " . $output . "' );</script>";
}

function get_config($yearmonth) {
	global $json_file_name;

	if (file_exists(__DIR__ . "/../salons/$yearmonth/blob/$json_file_name")) {
		// Load Partner Data
		$config = json_decode(file_get_contents(__DIR__ . "/../salons/$yearmonth/blob/$json_file_name"), true);
		if (json_last_error() != JSON_ERROR_NONE) {
			$_SESSION['err_msg'] = "Posting Definition garbled";
			return false;
		}

		return $config;
	}
	else {
		return false;
	}

}

// Labels & Values
// Lists
$couriers = [
			array("id" => "india_post", "name" => "India Posts", "tracking_site" => "https://www.indiapost.gov.in",
							"in_person" => "no", "award" => "yes", "catalog" => "yes", "domestic" => "yes", "international" => "yes"),
			array("id" => "award_ceremony", "name" => "Award Ceremony", "tracking_site" => "",
							"in_person" => "yes", "award" => "yes", "catalog" => "no", "domestic" => "yes", "international" => "no"),
			array("id" => "handed_over", "name" => "Handed Over", "tracking_site" => "",
							"in_person" => "yes", "award" => "yes", "catalog" => "yes", "domestic" => "yes", "international" => "yes")
			];
$remitters = [
			array("id" => "sbi", "name" => "State Bank of India", "domestic" => "yes", "international" => "no"),
			array("id" => "UPISBI", "name" => "SBI-YONO", "domestic" => "yes", "international" => "no"),
			array("id" => "UPIGoogle", "name" => "GPay", "domestic" => "yes", "international" => "no"),
			array("id" => "amazon", "name" => "Amazon India Gift Voucher", "domestic" => "yes", "international" => "no"),
			array("id" => "paypal", "name" => "Paypal", "domestic" => "no", "international" => "yes"),
			array("id" => "cash", "name" => "Paid in Cash", "domestic" => "yes", "international" => "no")
			];
$status_codes = [
			array("id" => "not_sent", "name" => "Yet to send"),
			array("id" => "sent", "name" => "Sent"),
			array("id" => "received", "name" => "Received")
		];


function get_name($array, $id) {
	foreach ($array as $row) {
		if ($row['id'] == $id)
			return $row['name'];
	}
	return $id;
}

function get_row($array, $val) {
	foreach ($array as $row) {
		if ($row['id'] == $val || $row['name'] == $val)
			return $row;
	}
	return false;
}

function get_options($array, $selection = "", $country_id = 0) {
	$html = "";
	foreach ($array as $row) {
		if ( ($country_id == 0) || ($country_id == 101 && $row["domestic"] == "yes") || ($country_id != 101 && $row["international"] == "yes") ) {
			$html .= "<option value= '" . $row['id'] . "' " . ($row['id'] == $selection ? 'selected' : '') . " data-row='" . json_encode($row) . "'>";
			$html .= $row['name'];
			$html .= "</option>";
		}
	}
	return $html;
}

// Add or Update rows based on profile_id, sum up cash_award
function update_remittance_row(&$array, $profile_id, $data) {
    echo "<script type='text/javascript'>alert('test message');</script>";
	$profile_found = false;
	for ($i = 0; $i < sizeof($array); ++ $i) {
		if ($array[$i]['profile_id'] == $profile_id) {
			$profile_found = true;
			$array[$i]['cash_award'] += $data['cash_awards'];
		}
	}
	if (! $profile_found) {
		$array[] = [
					"profile_id" => $data['profile_id'],
					"recipient_name" => $data['profile_name'],
					"recipient_country_id" => $data['country_id'],
					"recipient_country_name" => $data['country_name'],
					"cash_award" => $data['cash_awards'],
					"bank_name" => $data['bank_name'],
					"branch_name" => $data['bank_branch'],
					"ifsc" => $data['bank_ifsc_code'],
					"account_number" => $data['bank_account_number'],
					"date_of_remittance" => date('m-d-Y'),
					"amount_remitted" => $data['cash_awards'],
					"remitted_through" => "UPISBI",
					"status" => "sent",
					"phone" => $data['phone']
					];
	}
}

function update_award_row(&$array, $profile_id, $data) {
	$profile_found = false;
	for ($i = 0; $i < sizeof($array); ++ $i) {
		if ($array[$i]['profile_id'] == $profile_id) {
			$profile_found = true;
			$array[$i]['medals'] += $data['medals'];
			$array[$i]['pins'] += $data['pins'];
			$array[$i]['ribbons'] += $data['ribbons'];
			$array[$i]['mementos'] += $data['mementos'];
			$array[$i]['gifts'] += $data['gifts'];
		}
	}
	if (! $profile_found) {
		$array[] = [
					"profile_id" => $data['profile_id'],
					"recipient_name" => $data['profile_name'],
					"recipient_country_id" => $data['country_id'],
					"recipient_country_name" => $data['country_name'],
					"medals" => $data['medals'],
					"pins" => $data['pins'],
					"ribbons" => $data['ribbons'],
					"mementos" => $data['mementos'],
					"gifts" => $data['gifts'],
					"courier" => "",
					"tracking_site" => "",
					"date_of_posting" => "",
					"tracking_number" => "",
					"handed_over_to" => "",
					"status" => "not_sent"
					];
	}
}

function update_catalog_row(&$array, $profile_id, $data) {
	$profile_found = false;
	for ($i = 0; $i < sizeof($array); ++ $i) {
		if ($array[$i]['profile_id'] == $profile_id) {
			$profile_found = true;
			$catalog_model_found = false;
			for ($x = 0; $x < sizeof($array[$i]['catalogs']); ++ $x) {
				if ($array[$i]['catalogs'][$x]['catalog_model'] == $data['catalog_model']) {
					$catalog_model_found = true;
					$array[$i]['catalogs'][$x]['num_catalogs'] += $data['num_catalogs'];
				}
			}
			if (! $catalog_model_found)
				$array[$i]['catalogs'][] = array("catalog_model" => $data['catalog_model'], "num_catalogs" => $data['num_catalogs']);
		}
	}
	if (! $profile_found) {
		$array[] = [
					"profile_id" => $data['profile_id'],
					"recipient_name" => $row['profile_name'],
					"recipient_country_id" => $row['country_id'],
					"recipient_country_name" => $row['country_name'],
					"catalogs" => [ array("catalog_model" => $row['catalog_model'], "num_catalogs" => $row['num_catalogs'])],
					"courier" => "",
					"tracking_site" => "",
					"date_of_posting" => "",
					"tracking_number" => "",
					"handed_over_to" => "",
					"status" => "not_sent"
					];
	}
}


if ( isset($_SESSION['admin_id']) && isset($_SESSION['admin_yearmonth']) ) {
	// Set up user and contest information
	include ("inc/load_common_data.php");

	// Initialize Empty Salon
	$salon = array(
			"yearmonth" => "", "contest_name" => "", "is_international" => "0", "archived" => "0"
	);

	$is_international = false;
	$is_archived = false;
	$conf = [ "cash_remittances" => [], "award_mailing" => [], "catalog_mailing" => [] ];

	// Load data for the contest
	$yearmonth = $_SESSION['admin_yearmonth'];

	// Set up Salon
	$sql = "SELECT * FROM contest WHERE yearmonth = '$yearmonth' ";
	$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_num_rows($query) == 0)
		$_SESSION['err_msg'] = "Salon for " . $yearmonth . " not found !";
	else {
		$row = mysqli_fetch_array($query);
		foreach ($salon as $field => $value) {
			if (isset($row[$field]))
				$salon[$field] = $row[$field];
		}
	}

	$is_international = ($salon['is_international'] == '1');
	$is_archived = ($salon['archived'] == '1');

	// Get Config
	$cash_awards = [];
	$mailing_awards = [];
	$catalog_orders = [];

	$loaded_cash_remittances = false;
	$loaded_award_mailing = false;
	$loaded_catalog_mailing = false;

	if ($conf = get_config($yearmonth)) {
		$has_conf = true;
		$cash_awards = $conf['cash_remittances'];
		$mailing_awards = $conf['award_mailing'];
		$catalog_orders = $conf['catalog_mailing'];
	}

	if (sizeof($cash_awards) == 0 && sizeof($mailing_awards) == 0) {
		// Check if there is data
		$pic_result_table = $is_archived ? "ar_pic_result" : "pic_result";
		$sql  = "SELECT profile.profile_id, profile_name, profile.country_id, country_name, profile.phone ";
		$sql .= "       bank_name, bank_branch, bank_ifsc_code, bank_account_number, ";
		$sql .= "       SUM(has_medal) AS medals, SUM(has_pin) AS pins, SUM(has_ribbon) AS ribbons, ";
		$sql .= "       SUM(has_memento) AS mementos, SUM(has_gift) AS gifts, SUM(cash_award) AS cash_awards ";
		$sql .= "  FROM $pic_result_table AS pic_result, award, profile, country ";
		$sql .= " WHERE pic_result.yearmonth = '$yearmonth' ";
		$sql .= "   AND award.yearmonth = pic_result.yearmonth ";
		$sql .= "   AND award.award_id = pic_result.award_id ";
		$sql .= "   AND award.level < 99 ";
		$sql .= "   AND profile.profile_id = pic_result.profile_id ";
		$sql .= "   AND country.country_id = profile.country_id ";
		$sql .= " GROUP BY profile_id ";
		$sql .= " ORDER BY profile_name ";
		$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		while ($row = mysqli_fetch_array($query)) {
			if ($row['cash_awards'] > 0) {
				$loaded_cash_remittances = true;
				update_remittance_row($cash_awards, $row['profile_id'], $row);
			}
			if (($row['medals'] + $row['pins'] + $row['ribbons'] + $row['mementos'] + $row['gifts']) > 0) {
				$loaded_award_mailing = true;
				update_award_row($mailing_awards, $row['profile_id'], $row);
			}
		}

		// Generate Posting Data for medals and ribbons - for entry
		$sql  = "SELECT profile.profile_id, profile_name, profile.country_id, country_name, ";
		$sql .= "       bank_name, bank_branch, bank_ifsc_code, bank_account_number, ";
		$sql .= "       SUM(has_medal) AS medals, SUM(has_pin) AS pins, SUM(has_ribbon) AS ribbons, ";
		$sql .= "       SUM(has_memento) AS mementos, SUM(has_gift) AS gifts, SUM(cash_award) AS cash_awards ";
		$sql .= "  FROM entry_result, award, profile, country ";
		$sql .= " WHERE entry_result.yearmonth = '$yearmonth' ";
		$sql .= "   AND award.yearmonth = entry_result.yearmonth ";
		$sql .= "   AND award.award_id = entry_result.award_id ";
		$sql .= "   AND profile.profile_id = entry_result.profile_id ";
		$sql .= "   AND country.country_id = profile.country_id ";
		$sql .= " GROUP BY profile_id ";
		$sql .= " ORDER BY profile_name ";
		$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		while ($row = mysqli_fetch_array($query)) {
			if ($row['cash_awards'] > 0) {
				$loaded_cash_remittances = true;
				update_remittance_row($cash_awards, $row['profile_id'], $row);
			}
			if (($row['medals'] + $row['pins'] + $row['ribbons'] + $row['mementos'] + $row['gifts']) > 0) {
				$loaded_award_mailing = true;
				update_award_row($mailing_awards, $row['profile_id'], $row);
			}
		}
	}

	if (sizeof($catalog_orders) == 0) {
		// Catalog Orders
		$sql  = "SELECT catalog_order.profile_id, catalog_model, profile_name, profile.country_id, country_name, SUM(number_of_copies) AS num_catalogs ";
		$sql .= "  FROM catalog_order, profile, country ";
		$sql .= " WHERE yearmonth = '$yearmonth' ";
		$sql .= "   AND profile.profile_id = catalog_order.profile_id ";
		$sql .= "   AND country.country_id = profile.country_id ";
		$sql .= " GROUP BY profile_id, catalog_model ";
		$sql .= " ORDER BY profile_name ";
		$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		while ($row = mysqli_fetch_array($query)) {
			if ($row['num_catalogs'] > 0) {
				$loaded_catalog_mailing = true;
				update_catalog_row($catalog_orders, $row['profile_id'], $row);
			}
		}

		// Create a new configuration
		$conf = ["cash_remittances" => $cash_awards, "award_mailing" => $mailing_awards, "catalog_mailing" => $catalog_orders];

	}	// No Conf

?>

<!DOCTYPE html>
<html>

<head>
    <!-- Page title -->
	<title>Youth Photographic Society | Salon Management</title>

	<?php include "inc/header.php"; ?>

	<link rel="stylesheet" href="plugin/select2/css/select2.min.css" />
	<link rel="stylesheet" href="plugin/select2/css/select2-bootstrap.min.css" />
	<!-- <link rel="stylesheet" href="plugin/bootstrap-colorpicker/css/bootstrap-colorpicker.css" > -->

	<style>
		div.valid-error {
			font-size: 10px;
			color : red;
		}
	</style>
</head>

<body class="fixed-navbar fixed-sidebar">

	<!-- Simple splash screen-->
	<div class="splash">
		<div class="color-line"></div>
		<div class="splash-title">
			<h1>    YPS SALON MANAGEMENT PANEL  </h1>
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

	<!-- Header -->
<?php
	include "inc/master_topbar.php";
	include "inc/master_sidebar.php";
?>

	<!-- Main Wrapper -->
	<div id="wrapper">
		<div class="normalheader transition animated fadeIn">
			<div class="hpanel">
				<div class="panel-body">
					<a class="small-header-action" href="#">
						<div class="clip-header">
							<i class="fa fa-arrow-up"></i>
						</div>
					</a>
					<h3 class="font-light m-b-xs">
						Update Award Sending Information
					</h3>
					<br>
				</div>
			</div>
		</div>


		<div class="content">
			<form role="form" method="post" id="edit-sending-form" name="edit_sending_form" action="#" enctype="multipart/form-data" >
				<!-- Hidden non-edited fields -->
				<input type="hidden" name="yearmonth" id="yearmonth" value="<?= $yearmonth;?>" >
				<input type="hidden" name="is_international" value="<?= $is_international ? '1' : '0';?>" >

				<!-- <div class="row">
					<div class="col-sm-4">
						<select id="select-table" value="remittance-table">
							<option value="remittance-table">Remittance of Award Money</option>
							<option value="award-table">Mailing of Awards</option>
							<option value="catalog-table">Mailing of Catalogs</option>
						</select>
					</div>
				</div> -->

				<!-- Cash Remittance -->
				<div class="row">
					<div class="col-sm-12">
						<h3 class="text-info">Remittance of Award Money</h3>
						<div class="row">
							<div class="col-sm-6">
							<?php
								if ($loaded_cash_remittances) {
							?>
								<p class="text-danger"><b>Loaded Cash Award Details. Save Data before exiting.</b></p>
							<?php
								}
							?>
							</div>
							<div class="col-sm-6">
								<label>Show : </label>
								<span style="margin-left: 10px;">
									<label><input type="radio" name="remittance_by_country" id="remittance_all" checked > All</label>
								</span>
								<span style="margin-left: 10px;">
									<label><input type="radio" name="remittance_by_country" id="remittance_indian" > Indians</label>
								</span>
								<span style="margin-left: 10px;">
									<label><input type="radio" name="remittance_by_country" id="remittance_foreigner" > Foreigners</label>
								</span>
							</div>
						</div>
						<table id="remittance-table" class="table">
							<thead>
								<tr>
									<th>Name</th>
									<th>Country</th>
									<th>Award Money</th>
									<th>Bank</th>
									<th>Branch</th>
									<th>Account Number</th>
									<th>Amount Remitted</th>
									<th>Date of Remittance</th>
									<th>Status</th>
									<th>Edit</th>
									<th>Delete</th>
							</thead>
							<tbody id="remitance-tbody">
						<?php
							for ($idx = 0; $idx < sizeof($conf['cash_remittances']); ++ $idx) {
								$remittance = $conf['cash_remittances'][$idx];
						?>
								<tr id="remittance-<?= $idx;?>-row"
										class="remittance_all <?= $remittance['recipient_country_id'] == 101 ? 'remittance_indian' : 'remittance_foreigner';?>">
									<!-- Placeholder for Input Fields - will contain json Encoded Text -->
									<input type="hidden" name="remittance_data[]" id="remittance-data-<?= $idx;?>" value='<?= json_encode($remittance);?>' >
									<td><?= $remittance['recipient_name'];?> <br> <?= $remittance['phone'];?></td>
									<td><?= $remittance['recipient_country_name'];?></td>
									<td><?= $remittance['cash_award'];?></td>
									<td><?= $remittance['bank_name'];?></td>
									<td><?= $remittance['branch_name'];?></td>
									<td><?= $remittance['account_number'];?></td>
									<td><?= $remittance['amount_remitted'];?></td>
									<td><?= $remittance['date_of_remittance'];?></td>
									<td><?= get_name($status_codes, $remittance['status']);?></td>

									<td>
										<button id="edit-remittance-<?= $idx;?>" class="btn btn-info edit-remittance-btn"
											    data-idx='<?= $idx;?>' >
											<i class="fa fa-edit"></i> Edit
										</button>
									</td>
									<td>
										<button id="clear-remittance-<?= $idx;?>" class="btn btn-danger clear-remittance-btn"
												data-idx='<?= $idx;?>' >
											<i class="fa fa-eraser"></i> Clear
										</button>
									</td>
								</tr>
						<?php
							}
						?>
								<tr id="end-of-remittance-table" data-idx="<?= $idx;?>">
									<td>-- End of Remittance Data</td>
									<td></td>
									<td></td>
									<td></td>
									<td></td>
									<td></td>
									<td></td>
									<td></td>
									<td></td>
									<td></td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>

				<!-- Award Mailing -->
				<div class="row">
					<div class="col-sm-12">
						<h3 class="text-info">Mailing of Awards</h3>
						<div class="row">
							<div class="col-sm-6">
							<?php
								if ($loaded_award_mailing) {
							?>
								<p class="text-danger"><b>Loaded Award Details. Save Data before exiting.</b></p>
							<?php
								}
							?>
							</div>
							<div class="col-sm-6">
								<label>Show : </label>
								<span style="margin-left: 10px;">
									<label><input type="radio" name="award_by_country" value="award_all" checked > All</label>
								</span>
								<span style="margin-left: 10px;">
									<label><input type="radio" name="award_by_country" value="award_indian" > Indians</label>
								</span>
								<span style="margin-left: 10px;">
									<label><input type="radio" name="award_by_country" value="award_foreigner" > Foreigners</label>
								</span>
							</div>
						</div>
						<table id="award-table" class="table">
							<thead>
								<tr>
									<th>Name</th>
									<th>Country</th>
									<th>Medals</th>
									<th>PINs</th>
									<th>Ribbons</th>
									<th>Mementos</th>
									<th>Gifts</th>
									<th>Courier</th>
									<th>Date of Mailing</th>
									<th>Tracking Number / Handed Over To</th>
									<th>Status</th>
									<th>Edit</th>
									<th>Delete</th>
							</thead>
							<tbody id="award-tbody">
						<?php
							for ($idx = 0; $idx < sizeof($conf['award_mailing']); ++ $idx) {
								$award = $conf['award_mailing'][$idx];
						?>
								<tr id="award-<?= $idx;?>-row"
										class="award_all <?= $award['recipient_country_id'] == 101 ? 'award_indian' : 'award_foreigner';?>">
									<!-- Placeholder for Input Fields - will contain json Encoded Text -->
									<input type="hidden" name="award_data[]" id="award-data-<?= $idx;?>" value='<?= json_encode($award);?>' >
									<td><?= $award['recipient_name'];?></td>
									<td><?= $award['recipient_country_name'];?></td>
									<td><?= $award['medals'];?></td>
									<td><?= $award['pins'];?></td>
									<td><?= $award['ribbons'];?></td>
									<td><?= $award['mementos'];?></td>
									<td><?= $award['gifts'];?></td>
									<td><?= get_name($couriers, $award['courier']);?></td>
									<td><?= $award['date_of_posting'];?></td>
									<td><?= ($award['tracking_number'] != "") ? $award['tracking_number'] : ($award['handed_over_to'] != "" ? $award['handed_over_to'] : "");?></td>
									<td><?= get_name($status_codes, $award['status']);?></td>
									<td>
										<button id="edit-award-<?= $idx;?>" class="btn btn-info edit-award-btn"
											    data-idx='<?= $idx;?>' >
											<i class="fa fa-edit"></i> Edit
										</button>
									</td>
									<td>
										<button id="delete-award-<?= $idx;?>" class="btn btn-danger delete-award-btn"
												data-idx='<?= $idx;?>' >
											<i class="fa fa-eraser"></i> Clear
										</button>
									</td>
								</tr>
						<?php
							}
						?>
								<tr id="end-of-award-table" data-idx="<?= $idx;?>" >
									<td>-- End of Award Mailing</td>
									<td></td>
									<td></td>
									<td></td>
									<td></td>
									<td></td>
									<td></td>
									<td></td>
									<td></td>
									<td></td>
									<td></td>
									<td></td>
									<td></td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>

				<!-- Catalog Mailing -->
				<div class="row">
					<div class="col-sm-12">
						<h3 class="text-info">Mailing of Catalogs</h3>
						<div class="row">
							<div class="col-sm-6">
							<?php
								if ($loaded_catalog_mailing) {
							?>
								<p class="text-danger"><b>Loaded Catalog Order Details. Save Data before exiting.</b></p>
							<?php
								}
							?>
							</div>
							<div class="col-sm-6">
								<label>Show : </label>
								<span style="margin-left: 10px;">
									<label><input type="radio" name="catalog_by_country" id="catalog_all" checked > All</label>
								</span>
								<span style="margin-left: 10px;">
									<label><input type="radio" name="catalog_by_country" id="catalog_indian" > Indians</label>
								</span>
								<span style="margin-left: 10px;">
									<label><input type="radio" name="catalog_by_country" id="catalog_foreigner" > Foreigners</label>
								</span>
							</div>
						</div>
						<table id="catalog-table" class="table">
							<thead>
								<tr>
									<th>Name</th>
									<th>Country</th>
									<th>Catalogs</th>
									<th>Courier</th>
									<th>Date of Mailing</th>
									<th>Tracking Number</th>
									<th>Status</th>
									<th>Edit</th>
									<th>Delete</th>
							</thead>
							<tbody id="catalog-tbody">
						<?php
							for ($idx = 0; $idx < sizeof($conf['catalog_mailing']); ++ $idx) {
								$catalog = $conf['catalog_mailing'][$idx];
								$num_catalogs = 0;
								foreach ($catalog['catalogs'] as $order)
									$num_catalogs += $order['num_catalogs'];
						?>
								<tr id="award-<?= $idx;?>-row"
										class="catalog_all <?= $award['recipient_country_id'] == 101 ? 'catalog_indian' : 'catalog_foreigner';?>">
									<!-- Placeholder for Input Fields - will contain json Encoded Text -->
									<input type="hidden" name="catalog_data[]" id="catalog-data-<?= $idx;?>" value='<?= json_encode($catalog);?>' >
									<td><?= $catalog['recipient_name'];?></td>
									<td><?= $catalog['recipient_country_name'];?></td>
									<td><?= $num_catalogs;?></td>
									<td><?= get_name($couriers, $catalog['courier']);?></td>
									<td><?= $catalog['date_of_posting'];?></td>
									<td><?= ($catalog['tracking_number'] != "") ? $catalog['tracking_number'] : ($catalog['handed_over_to'] != "" ? $catalog['handed_over_to'] : "");?></td>
									<td><?= get_name($status_codes, $catalog['status']);?></td>
									<td>
										<button id="edit-catalog-<?= $idx;?>" class="btn btn-info edit-catalog-btn"
											    data-idx='<?= $idx;?>' >
											<i class="fa fa-edit"></i> Edit
										</button>
									</td>
									<td>
										<button id="delete-catalog-<?= $idx;?>" class="btn btn-danger delete-catalog-btn"
												data-idx='<?= $idx;?>' >
											<i class="fa fa-eraser"></i> Clear
										</button>
									</td>
								</tr>
						<?php
							}
						?>
								<tr id="end-of-catalog-table" data-idx="<?= $idx;?>" >
									<td>-- End of Catalog Mailing</td>
									<td></td>
									<td></td>
									<td></td>
									<td></td>
									<td></td>
									<td></td>
									<td></td>
									<td></td>
									<td></td>
									<td></td>
									<td></td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>

				<!-- Update -->
				<div class="row" style="padding-top: 20px;">
					<div class="col-sm-12">
						<input class="btn btn-primary pull-right" type="submit" name="save-posting-json" value="Save">
					</div>
				</div>
			</form>
		</div>

		<!-- MODAL FORMS -->
		<!-- Cash Remittance Modal Form -->
		<div class="modal" id="edit-remittance-modal" tabindex="-1" role="dialog">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<div class="row">
							<div class="col-sm-10">
								<h4 class="modal-title"><small>Cash Award Remittance Details</small></h4>
							</div>
							<div class="col-sm-2">
								<button type="button" class="close" data-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
						</div>
					</div>
					<div class="modal-body">
						<form role="form" method="post" id="edit-remittance-form" name="edit_remittance_form" action="#" enctype="multipart/form-data" >
							<!-- Hidden non-edited fields -->
							<input type="hidden" name="yearmonth" id="remittance-yearmonth" value="<?= $yearmonth;?>" >
							<input type="hidden" id="remittance-idx" value="0" >
							<input type="hidden" name="profile_id" id="remittance-profile-id" value="0" >
							<input type="hidden" name="recipient_country_id" id="remittance-recipient-country-id" value="0" >

							<!-- Edited Fields -->
							<!-- Display Cash Awad Details -->
							<div class="row form-group">
								<!-- Name -->
								<div class="col-sm-6">
									<label for="remittance-recipient-name">Recipient Name</label>
									<input type="text" id="remittance-recipient-name" name="recipient_name" class="form-control" readonly>
								</div>
								<!-- Country -->
								<div class="col-sm-6">
									<label for="remittance-recipient-country-name">Country</label>
									<input type="text" id="remittance-recipient-country-name" name="recipient_country_name" class="form-control" readonly>
								</div>
								<!-- Award Money -->
								<div class="col-sm-6">
									<label for="remittance-cash-award">Award Money</label>
									<input type="number" id="remittance-cash-award" name="cash_award" class="form-control" readonly>
								</div>
								<!-- Account Number -->
								<div class="col-sm-6">
									<label for="remittance-account-number">Account Number</label>
									<input type="text" id="remittance-account-number" name="account_number" class="form-control" value="" readonly>
								</div>
								<!-- IFSC -->
								<div class="col-sm-6">
									<label for="remittance-ifsc">IFSC</label>
									<input type="text" id="remittance-ifsc" name="ifsc" class="form-control" value="" readonly >
								</div>
								<!-- Bank -->
								<div class="col-sm-6">
									<label for="remittance-bank-name">Bank</label>
									<input type="text" id="remittance-bank-name" name="bank_name" class="form-control" value="" readonly>
								</div>
								<!-- Branch -->
								<div class="col-sm-6">
									<label for="remittance-branch-name">Branch</label>
									<input type="text" id="remittance-branch-name" name="branch_name" class="form-control" value="" readonly >
								</div>
							</div>
							<!-- Edit Remittance Details -->
							<div class="row form-group">
								<div class="col-sm-6">
									<label for="remittance-amount-remitted">Amount Remitted</label>
									<input type="number" id="remittance-amount-remitted" name="amount_remitted" class="form-control" required>
								</div>
								<div class="col-sm-6">
									<label for="remittance-date-of-remittance">Date of Remittance</label>
									<input type="date" id="remittance-date-of-remittance" name="date_of_remittance" class="form-control" required>
								</div>
								<div class="col-sm-6">
									<label for="remittance-remitted-through">Remitted Through</label>
									<select id="remittance-remitted-through" name="remitted_through" class="form-control" required></select>
								</div>
							</div>
							<div class='row form-group'>
								<div class="col-sm-6">
									<label for="remittance-status">Status</label>
									<select id="remittance-status" name="status" class="form-control" required></select>
								</div>
							</div>

							<!-- Update -->
							<div class="row" style="padding-top: 20px;">
								<div class="col-sm-12">
									<input class="btn btn-primary pull-right" type="submit" id="edit-update-remittance" name="edit-update" value="Update">
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>

		<!-- Award Mailing Modal Form -->
		<div class="modal" id="edit-award-modal" tabindex="-1" role="dialog">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<div class="row">
							<div class="col-sm-10">
								<h4 class="modal-title"><small>Award Mailing Details</small></h4>
							</div>
							<div class="col-sm-2">
								<button type="button" class="close" data-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
						</div>
					</div>
					<div class="modal-body">
						<form role="form" method="post" id="edit-award-form" name="edit_award_form" action="#" enctype="multipart/form-data" >
							<!-- Hidden non-edited fields -->
							<input type="hidden" id="award-idx" value="0" >
							<input type="hidden" name="yearmonth" id="award-yearmonth" value="<?= $yearmonth;?>" >
							<input type="hidden" name="profile_id" id="award-profile-id" value="0" >
							<input type="hidden" name="recipient_country_id" id="award-recipient-country-id" value="0" >

							<!-- Edited Fields -->
							<!-- Display Cash Awad Details -->
							<div class="row form-group">
								<!-- Name -->
								<div class="col-sm-6">
									<label for="award-recipient-name">Recipient Name</label>
									<input type="text" id="award-recipient-name" name="recipient_name" class="form-control" readonly>
								</div>
								<!-- Country -->
								<div class="col-sm-6">
									<label for="award-recipient-country-name">Country</label>
									<input type="text" id="award-recipient-country-name" name="recipient_country_name" class="form-control" readonly>
								</div>
								<!-- Medals -->
								<div class="col-sm-4">
									<label for="award-medals">Medals</label>
									<input type="number" id="award-medals" name="medals" class="form-control" readonly>
								</div>
								<!-- Pins -->
								<div class="col-sm-4">
									<label for="award-pins">Pins</label>
									<input type="text" id="award-pins" name="pins" class="form-control" value="" readonly>
								</div>
								<!-- Ribbons -->
								<div class="col-sm-4">
									<label for="award-ribbons">Ribbons</label>
									<input type="text" id="award-ribbons" name="ribbons" class="form-control" value="" readonly >
								</div>
								<!-- Mementos -->
								<div class="col-sm-4">
									<label for="award-mementos">Mementos</label>
									<input type="text" id="award-mementos" name="mementos" class="form-control" value="" readonly>
								</div>
								<!-- Gifts -->
								<div class="col-sm-4">
									<label for="award-gifts">Gifts</label>
									<input type="text" id="award-gifts" name="gifts" class="form-control" value="" readonly >
								</div>
							</div>
							<!-- Edit Remittance Details -->
							<div class="row form-group">
								<div class="col-sm-6">
									<label for="award-courier">Sent through</label>
									<select id="award-courier" name="courier" class="form-control" required></select>
								</div>
								<div class="col-sm-6">
									<label for="award-date-of-posting">Date of sending</label>
									<input type="date" id="award-date-of-posting" name="date_of_posting" class="form-control" required>
								</div>
								<div class="col-sm-6">
									<label for="award-tracking-number">Tracking Number</label>
									<input type="text" id="award-tracking-number" name="tracking_number" class="form-control" >
								</div>
								<div class="col-sm-6">
									<label for="award-tracking-site">Tracking Site</label>
									<input type="text" id="award-tracking-site" name="tracking_site" class="form-control" readonly>
								</div>
								<div class="col-sm-6">
									<label for="award-handed-over-to">Handed over to</label>
									<input type="text" id="award-handed-over-to" name="handed_over_to" class="form-control" >
								</div>
							</div>
							<div class="row form-group">
								<div class="col-sm-6">
									<label for="award-status">Status</label>
									<select id="award-status" name="status" class="form-control" required></select>
								</div>
							</div>

							<!-- Update -->
							<div class="row" style="padding-top: 20px;">
								<div class="col-sm-12">
									<input class="btn btn-primary pull-right" type="submit" id="edit-update-award" name="edit-update" value="Update">
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
		<!-- Catalog Mailing Modal Form -->
		<div class="modal" id="edit-catalog-modal" tabindex="-1" role="dialog">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<div class="row">
							<div class="col-sm-10">
								<h4 class="modal-title"><small>Catalog Mailing Details</small></h4>
							</div>
							<div class="col-sm-2">
								<button type="button" class="close" data-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
						</div>
					</div>
					<div class="modal-body">
						<form role="form" method="post" id="edit-catalog-form" name="edit_catalog_form" action="#" enctype="multipart/form-data" >
							<!-- Hidden non-edited fields -->
							<input type="hidden" id="catalog-idx" value="0" >
							<input type="hidden" name="yearmonth" id="catalog-yearmonth" value="<?= $yearmonth;?>" >
							<input type="hidden" name="profile_id" id="catalog-profile-id" value="0" >
							<input type="hidden" name="recipient_country_id" id="catalog-country-id" value="0" >

							<!-- Edited Fields -->
							<!-- Display Cash Awad Details -->
							<div class="row form-group">
								<!-- Name -->
								<div class="col-sm-6">
									<label for="catalog-recipient-name">Recipient Name</label>
									<input type="text" id="catalog-recipient-name" name="recipient_name" class="form-control" readonly>
								</div>
								<!-- Country -->
								<div class="col-sm-6">
									<label for="catalog-recipient-country-name">Country</label>
									<input type="text" id="catalog-recipient-country-name" name="recipient_country_name" class="form-control" readonly>
								</div>
								<!-- Medals -->
								<div class="col-sm-12">
									<label for="catalog-catalogs-ordered">Catalogs Ordered</label>
									<div class="row" id="catalog-catalogs-ordered"></div>
								</div>
							</div>
							<!-- Edit Remittance Details -->
							<div class="row form-group">
								<div class="col-sm-6">
									<label for="catalog-courier">Sent through</label>
									<select id="catalog-courier" name="courier" class="form-control" required></select>
								</div>
								<div class="col-sm-6">
									<label for="catalog-date-of-posting">Date of sending</label>
									<input type="date" id="catalog-date-of-posting" name="date_of_posting" class="form-control" required>
								</div>
								<div class="col-sm-6">
									<label for="catalog-tracking-number">Tracking Number</label>
									<input type="text" id="catalog-tracking-number" name="tracking_number" class="form-control" >
								</div>
								<div class="col-sm-6">
									<label for="catalog-tracking-site">Tracking Site</label>
									<input type="text" id="catalog-tracking-site" name="tracking_site" class="form-control" readonly>
								</div>
								<div class="col-sm-6">
									<label for="catalog-handed-over-to">Handed over to</label>
									<input type="text" id="catalog-handed-over-to" name="handed_over_to" class="form-control" >
								</div>
							</div>
							<div class="row form-group">
								<div class="col-sm-6">
									<label for="catalog-status">Status</label>
									<select id="catalog-status" name="status" class="form-control" required></select>
								</div>
							</div>

							<!-- Update -->
							<div class="row" style="padding-top: 20px;">
								<div class="col-sm-12">
									<input class="btn btn-primary pull-right" type="submit" id="edit-update-award" name="edit-update" value="Update">
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
		<!-- END OF MODAL FORMS -->

	</div>
<?php
      include("inc/footer.php");
?>

<!-- Form Validation -->
<script src="plugin/validate/js/jquery.validate.min.js"></script>

<!-- Custom Validation Functions -->
<script src="custom/js/validate.js"></script>

<!-- Select 2 for Country Codes -->
<script src="plugin/select2/js/select2.min.js"></script>

<script>
	$(document).ready(function(){
		// Hide content till a salon is selected
		if($("#yearmonth").val() == 0)
			$(".content").hide();

	});
</script>

<!-- Javascript Dropdown Handlers -->
<script>
	var couriers = <?= json_encode($couriers);?>;
	var remitters = <?= json_encode($remitters);?>;
	var status_codes = <?= json_encode($status_codes);?>;

	function get_name(array, id) {
		for (const row of array) {
			if (row.id == id)
				return row.name;
		}
		return id;
	}

	function get_row(array, val) {
		for (const row of array) {
			if (row.id == val || row.name == val)
				return row;
		}
		return false;
	}

	function get_options(array, selection = "", country_id = 0) {
		let html = "";
		for (const row of array) {
			if ( (country_id == 0) || (country_id == 101 && row.domestic == "yes") || (country_id != 101 && row.international == "yes") ) {
				html += "<option value= '" + row.id + "' " + (row.id == selection ? 'selected' : '') + " data-row='" + JSON.stringify(row) + "' >";
				html += row.name;
				html += "</option> ";
			}
		}
		return html;
	}

</script>

<!-- REMITTANCE Handlers -->
<script>

	// Open Edit Remittance Modal Dialog
	function launch_remittance_modal(idx, row) {

        if (row.status == 'sent') {
    		// Set Values
    		$("#remittance-idx").val(idx);
    		$("#remittance-profile-id").val(row.profile_id);
    		$("#remittance-recipient-name").val(row.recipient_name);
    		$("#remittance-recipient-country-id").val(row.recipient_country_id);
    		$("#remittance-recipient-country-name").val(row.recipient_country_name);
    		$("#remittance-cash-award").val(row.cash_award);
    		$("#remittance-account-number").val(row.account_number);
    		$("#remittance-ifsc").val(row.ifsc);
    		$("#remittance-bank-name").val(row.bank_name);
    		$("#remittance-branch-name").val(row.branch_name);
    		$("#remittance-amount-remitted").val(row.amount_remitted);
    		$("#remittance-date-of-remittance").val(row.date_of_remittance);
    		$("#remittance-remitted-through").html(get_options(remitters, row.remitted_through, row.recipient_country_id));
    		$("#remittance-remitted-through").val(row.remitted_through);
    		$("#remittance-status").html(get_options(status_codes, row.status));
    		// $("#remittance-status").val(row.status);
    		$("#remittance-status").val('sent');
        }
        else {
            // Get the current date
            const today = new Date();
            
            // Extract year, month, and day
            const year = today.getFullYear();
            // getMonth() returns 0-11, so add 1 for actual month number
            // padStart(2, '0') ensures two digits (e.g., 06 instead of 6)
            const month = String(today.getMonth() + 1).padStart(2, '0');
            const day = String(today.getDate()).padStart(2, '0');
            
            // Create the YYYY-MM-DD formatted string
            const formattedDate = `${year}-${month}-${day}`;
            
            // Set the value of the date input field
            $("#remittance-date-of-remittance").val(formattedDate);
    		// Set Values
    		$("#remittance-idx").val(idx);
    		$("#remittance-profile-id").val(row.profile_id);
    		$("#remittance-recipient-name").val(row.recipient_name);
    		$("#remittance-recipient-country-id").val(row.recipient_country_id);
    		$("#remittance-recipient-country-name").val(row.recipient_country_name);
    		$("#remittance-cash-award").val(row.cash_award);
    		$("#remittance-account-number").val(row.account_number);
    		$("#remittance-ifsc").val(row.ifsc);
    		$("#remittance-bank-name").val(row.bank_name);
    		$("#remittance-branch-name").val(row.branch_name);
    		$("#remittance-amount-remitted").val(row.cash_award);
            // Set the value of the date input field
            $("#remittance-date-of-remittance").val(formattedDate);
    		$("#remittance-remitted-through").html(get_options(remitters, row.remitted_through, row.recipient_country_id));
    		$("#remittance-remitted-through").val('UPISBI');
    		$("#remittance-status").html(get_options(status_codes, row.status));
    		$("#remittance-status").val('sent');

        }
		// Display Modal
		$("#edit-remittance-modal").modal('show');
	}

	function render_remittance_row(idx, row) {
		let html = "";
		html += "<input type='hidden' name='remittance_data[]' id='remittance-data-" + idx + "' value='" + JSON.stringify(row) + "' > ";
		html += "<td>" + row.recipient_name + "</td> ";
		html += "<td>" + row.recipient_country_name + "</td> ";
		html += "<td>" + row.cash_award + "</td> ";
		html += "<td>" + row.bank_name + "</td> ";
		html += "<td>" + row.branch_name + "</td> ";
		html += "<td>" + row.account_number + "</td> ";
		html += "<td>" + row.amount_remitted + "</td> ";
		html += "<td>" + row.date_of_remittance + "</td> ";
		html += "<td>" + get_name(status_codes, row.status) + "</td> ";
		html += "<td>";
		html += "<button id='edit-remittance-" + idx + "' class='btn btn-info edit-remittance-btn' ";
		html += " data-idx='" + idx + "' > ";
		html += " <i class='fa fa-edit'></i> Edit ";
		html += "</button> ";
		html += "</td> ";
		html += "<td> ";
		html += "<button id='clear-remittance-" + idx + "' class='btn btn-danger clear-remittance-btn' ";
		html += " data-idx='" + idx + "' > ";
		html += " <i class='fa fa-eraser'></i> Clear ";
		html += "</button> ";
		html += "</td> ";

		$("#remittance-" + idx + "-row").html(html);

		// Set handlers for updated buttons
		set_remittance_handlers();
	}

	function update_remittance_row(idx, data) {
		let row = JSON.parse($("#remittance-data-" + idx).val());
		row.amount_remitted = data.get('amount_remitted');
		row.date_of_remittance = data.get("date_of_remittance");
		row.remitted_through = data.get("remitted_through");
		row.status = data.get("status");

		render_remittance_row(idx, row);
	}

	// Clear Remittance Details
	// Handle clear button request
	function clear_remittance(idx) {
		swal({
			title: 'CLEAR Confirmation',
			text:  "Do you want to erase remittance data ?",
			icon: "warning",
			buttons: true,
			dangerMode: true,
		})
		.then(function (erase_confirmed) {
			if (erase_confirmed) {
				let row = JSON.parse($("#remittance-data-" + idx).val());
				row.amount_remitted = 0;
				row.date_of_remittance = "",
				row.status = "not_sent";
				row.remitted_through = "";
				render_remittance_row(idx, row);
			}
		});
	}

	function set_remittance_handlers() {
		// Install handlers for updated buttons
		// Edit button
		$(".edit-remittance-btn").click(function(e){
			e.preventDefault();
			let idx = $(this).attr("data-idx");
			let row = JSON.parse($("#remittance-data-" + idx).val());
			launch_remittance_modal(idx, row);
		});

		// Delete Button
		$(".clear-remittance-btn").click(function(e){
			e.preventDefault();
			let idx = $(this).attr("data-idx");
			clear_remittance(idx);
		});
	}

	$(document).ready(function() {

		// Install handlers for buttons
		set_remittance_handlers();

		// Country Filters
		$("#remittance_all").click(function(){
			// $("#remittance_indian").removeAttr("checked");
			// $("#remittance_foreigner").removeAttr("checked");
			$("tr.remittance_all").show();
		});
		$("#remittance_indian").click(function(){
			// $("#remittance_all").removeAttr("checked");
			// $("#remittance_foreigner").removeAttr("checked");
			$("tr.remittance_all").hide();
			$("tr.remittance_indian").show();
		});
		$("#remittance_foreigner").click(function(){
			// $("#remittance_all").removeAttr("checked");
			// $("#remittance_indian").removeAttr("checked");
			$("tr.remittance_all").hide();
			$("tr.remittance_foreigner").show();
		});

		// Update rules back to the table
		$('#edit-remittance-form').validate({
			rules:{
				"remittance-amount-remitted" : {
					min : 1,
					max : $("#remittance-cash-award").val(),
					required : true,
				},
			},
			messages:{
			},
			errorElement: "div",
			errorClass: "valid-error",
			showErrors: function(errorMap, errorList) {
							$("div.valid-error").remove();		// Remove all error messages to avoid duplicate messages
							this.defaultShowErrors();			// Show Errors
						},
			submitHandler: function(form) {

				let idx = $("#remittance-idx").val();

				// Assemble Data
				let formData = new FormData(form);

				update_remittance_row(idx, formData);

				$("#edit-remittance-modal").modal('hide');
			},
		});
	});
</script>
<!-- End of REMITTANCE Handlers -->

<!-- AWARD Handlers -->
<script>

	// Open Edit Award Modal Dialog
	function launch_award_modal(idx, row) {

		// Set Values
		$("#award-idx").val(idx);
		$("#award-profile-id").val(row.profile_id);
		$("#award-recipient-name").val(row.recipient_name);
		$("#award-recipient-country-id").val(row.recipient_country_id);
		$("#award-recipient-country-name").val(row.recipient_country_name);
		$("#award-medals").val(row.medals);
		$("#award-pins").val(row.pins);
		$("#award-ribbons").val(row.ribbons);
		$("#award-mementos").val(row.mementos);
		$("#award-gifts").val(row.gifts);
		$("#award-courier").html(get_options(couriers, row.courier, row.recipient_country_id) );
		$("#award-courier").val(row.courier);
		$("#award-tracking-site").val(row.tracking_site);
		$("#award-date-of-posting").val(row.date_of_posting);
		$("#award-tracking-number").val(row.tracking_number);
		$("#award-handed-over-to").val(row.handed_over_to);
		$("#award-status").html(get_options(status_codes, row.status));
		$("#award-status").val(row.status);

		$("#award-courier").on("change", function(){
			let courier = JSON.parse($("#award-courier option:selected").attr("data-row"));
			if (courier.in_person == "yes") {
				$("#award-tracking-site").val("");
				$("#award-tracking-number").val("");
			}
			else {
				$("#award-tracking-site").val(courier.tracking_site);
				$("#award-handed-over-to").val("");
			}
		});

		// Display Modal
		$("#edit-award-modal").modal('show');
	}

	function render_award_row(idx, row) {
		let html = "";
		html += "<input type='hidden' name='award_data[]' id='award-data-" + idx + "' value='" + JSON.stringify(row) + "' > ";
		html += "<td>" + row.recipient_name + "</td> ";
		html += "<td>" + row.recipient_country_name + "</td> ";
		html += "<td>" + row.medals + "</td> ";
		html += "<td>" + row.pins + "</td> ";
		html += "<td>" + row.ribbons + "</td> ";
		html += "<td>" + row.mementos + "</td> ";
		html += "<td>" + row.gifts + "</td> ";
		html += "<td>" + get_name(couriers, row.courier) + "</td> ";
		html += "<td>" + row.date_of_posting + "</td> ";
		html += "<td>" + (row.tracking_number == "" ? row.handed_over_to : row.tracking_number) + "</td> ";
		html += "<td>" + get_name(status_codes, row.status) + "</td> ";
		html += "<td>";
		html += "<button id='edit-award-" + idx + "' class='btn btn-info edit-award-btn' ";
		html += " data-idx='" + idx + "' > ";
		html += " <i class='fa fa-edit'></i> Edit ";
		html += "</button> ";
		html += "</td> ";
		html += "<td> ";
		html += "<button id='clear-award-" + idx + "' class='btn btn-danger clear-award-btn' ";
		html += " data-idx='" + idx + "' > ";
		html += " <i class='fa fa-eraser'></i> Clear ";
		html += "</button> ";
		html += "</td> ";

		$("#award-" + idx + "-row").html(html);

		// Set award event handlers
		set_award_handlers();
	}

	function update_award_row(idx, data) {
		let row = JSON.parse($("#award-data-" + idx).val());
		row.courier = data.get('courier');
		row.date_of_posting = data.get("date_of_posting");
		row.tracking_number = data.get("tracking_number");
		row.tracking_site = data.get("tracking_site");
		row.handed_over_to = data.get("handed_over_to");
		row.status = data.get("status");

		render_award_row(idx, row);
	}

	// Clear Remittance Details
	// Handle clear button request
	function clear_award(idx) {
		swal({
			title: 'CLEAR Confirmation',
			text:  "Do you want to erase award mailing data ?",
			icon: "warning",
			buttons: true,
			dangerMode: true,
		})
		.then(function (erase_confirmed) {
			if (erase_confirmed) {
				let row = JSON.parse($("#award-data-" + idx).val());
				row.courier = "";
				row.date_of_posting = "";
				row.tracking_number = "";
				row.tracking_site = "";
				row.handed_over_to = "";
				row.status = "not_sent";
				render_award_row(idx, row);
			}
		});
	}

	// Set event handlers
	function set_award_handlers() {
		// Install handlers for updated buttons
		// Edit button
		$(".edit-award-btn").click(function(e){
			e.preventDefault();
			let idx = $(this).attr("data-idx");
			let row = JSON.parse($("#award-data-" + idx).val());
			launch_award_modal(idx, row);
		});

		// Delete Button
		$(".clear-award-btn").click(function(e){
			e.preventDefault();
			let idx = $(this).attr("data-idx");
			clear_award(idx);
		});

	}

	$(document).ready(function() {

		// Set Award Event handlers
		set_award_handlers();

		// Country Filters
		$("input[name='award_by_country']").click(function(){
			if ($("input[name='award_by_country']:checked").val() == "award_all")
				$("tr.award_all").show();
			else {
				$("tr.award_all").hide();
				$("tr." + $("input[name='award_by_country']:checked").val()).show();
			}
		});
		// $("#award_indian").click(function(){
		// 	// $("#remittance_all").removeAttr("checked");
		// 	// $("#remittance_foreigner").removeAttr("checked");
		// 	$("tr.award_all").hide();
		// 	$("tr.award_indian").show();
		// });
		// $("#award_foreigner").click(function(){
		// 	// $("#remittance_all").removeAttr("checked");
		// 	// $("#remittance_indian").removeAttr("checked");
		// 	$("tr.award_all").hide();
		// 	$("tr.award_foreigner").show();
		// });

		// Update rules back to the table
		$('#edit-award-form').validate({
			rules:{},
			messages:{},
			errorElement: "div",
			errorClass: "valid-error",
			showErrors: function(errorMap, errorList) {
							$("div.valid-error").remove();		// Remove all error messages to avoid duplicate messages
							this.defaultShowErrors();			// Show Errors
						},
			submitHandler: function(form) {

				let idx = $("#award-idx").val();

				// Assemble Data
				let formData = new FormData(form);

				update_award_row(idx, formData);

				$("#edit-award-modal").modal('hide');
			},
		});

	});
</script>
<!-- End of AWARD Handlers -->

<!-- CATALOG Handlers -->
<script>

	// Open Edit Catalog Modal Dialog
	function launch_catalog_modal(idx, row) {

		// Set Values
		$("#catalog-idx").val(idx);
		$("#catalog-profile-id").val(row.profile_id);
		$("#catalog-recipient-name").val(row.recipient_name);
		$("#catalog-recipient-country-id").val(row.recipient_country_id);
		$("#catalog-recipient-country-name").val(row.recipient_country_name);

		let html = "";
		html += "<div class='col-sm-10'><label>Catalog Model</label></div>";
		html += "<div class='col-sm-2'><label># Copies</label></div>";
		html += "<div class='clear-fix'></div>";
		for (let x = 0; x < row.catalogs.length; ++ x) {
			html += "<div class='col-sm-10'><input type='text' class='form-control' disabled value='" + row.catalogs[x].catalog_model + "' ></div>";
			html += "<div class='col-sm-2'><input type='text' class='form-control' disabled value='" + row.catalogs[x].num_catalogs + "' ></div>";
			html += "<div class='clear-fix'></div>";
		}
		$("#catalog-courier").html(get_options(couriers, row.courier, row.recipient_country_id));
		$("#catalog-courier").val(row.courier);
		$("#catalog-date-of-posting").val(row.date_of_posting);
		$("#catalog-tracking-number").val(row.tracking_number);
		$("#catalog-tracking-site").val(row.tracking_site);
		$("#catalog-handed-over-to").val(row.handed_over_to);
		$("#catalog-status").html(get_options(status_codes, row.status));
		$("#catalog-status").val(row.status);

		// Display Modal
		$("#edit-catalog-modal").modal('show');
	}

	function render_catalog_row(idx, row) {
		let num_catalogs = row.catalogs.reduce(function(left, right) { return left.num_catalogs + right.num_catalogs; });

		let html = "";
		html += "<input type='hidden' name='catalog_data[]' id='catalog-data-" + idx + "' value='" + JSON.stringify(row) + "' > ";
		html += "<td>" + row.recipient_name + "</td> ";
		html += "<td>" + row.recipient_country_name + "</td> ";
		html += "<td>" + num_catalogs + "</td> ";
		html += "<td>" + get_name(couriers, row.courier) + "</td> ";
		html += "<td>" + row.date_of_posting + "</td> ";
		html += "<td>" + row.tracking_number + "</td> ";
		html += "<td>" + get_name(status_codes, row.status) + "</td> ";
		html += "<td>";
		html += "<button id='edit-catalog-" + idx + "' class='btn btn-info edit-catalog-btn' ";
		html += " data-idx='" + idx + "' > ";
		html += " <i class='fa fa-edit'></i> Edit ";
		html += "</button> ";
		html += "</td> ";
		html += "<td> ";
		html += "<button id='clear-catalog-" + idx + "' class='btn btn-danger clear-catalog-btn' ";
		html += " data-idx='" + idx + "' > ";
		html += " <i class='fa fa-eraser'></i> Clear ";
		html += "</button> ";
		html += "</td> ";

		$("#award-" + idx + "-row").html(html);

		// Install handlers for updated buttons
		set_catalog_handlers();
	}

	function update_catalog_row(idx, data) {
		let row = JSON.parse($("#award-data-" + idx).val());
		row.courier = data.get('courier');
		row.date_of_posting = data.get("date_of_posting");
		row.tracking_number = data.get("tracking_number");
		row.tracking_site = data.get("tracking_site");
		row.handed_over_to = data.get("handed_over_to");
		row.status = data.get("status");

		render_catalog_row(idx, row);
	}

	// Clear Remittance Details
	// Handle clear button request
	function clear_catalog(idx) {
		swal({
			title: 'CLEAR Confirmation',
			text:  "Do you want to erase award mailing data ?",
			icon: "warning",
			buttons: true,
			dangerMode: true,
		})
		.then(function (erase_confirmed) {
			if (erase_confirmed) {
				let row = JSON.parse($("#award-data-" + idx).val());
				row.courier = "";
				row.date_of_posting = "";
				row.tracking_number = "";
				row.tracking_site = "";
				row.handed_over_to = "";
				row.status = "not_sent";
				render_catalog_row(idx, row);
			}
		});
	}

	function set_catalog_handlers() {
		// Edit button
		$(".edit-catalog-btn").click(function(e){
			e.preventDefault();
			let idx = $(this).attr("data-idx");
			let row = JSON.parse($("#catalog-data-" + idx).val());
			launch_catalog_modal(idx, row);
		});
		// Delete Button
		$(".clear-catalog-btn").click(function(e){
			e.preventDefault();
			let idx = $(this).attr("data-idx");
			clear_catalog(idx);
		});
	}

	$(document).ready(function() {

		// Set Catalog Event Handlers
		set_catalog_handlers();

		// Country Filters
		$("#catalog_all").click(function(){
			// $("#remittance_indian").removeAttr("checked");
			// $("#remittance_foreigner").removeAttr("checked");
			$("tr.catalog_all").show();
		});
		$("#catalog_indian").click(function(){
			// $("#remittance_all").removeAttr("checked");
			// $("#remittance_foreigner").removeAttr("checked");
			$("tr.catalog_all").hide();
			$("tr.catalog_indian").show();
		});
		$("#catalog_foreigner").click(function(){
			// $("#remittance_all").removeAttr("checked");
			// $("#remittance_indian").removeAttr("checked");
			$("tr.catalog_all").hide();
			$("tr.catalog_foreigner").show();
		});

		// Update rules back to the table
		$('#edit-catalog-form').validate({
			rules:{},
			messages:{},
			errorElement: "div",
			errorClass: "valid-error",
			showErrors: function(errorMap, errorList) {
							$("div.valid-error").remove();		// Remove all error messages to avoid duplicate messages
							this.defaultShowErrors();			// Show Errors
						},
			submitHandler: function(form) {

				let idx = $("#award-idx").val();

				// Assemble Data
				let formData = new FormData(form);

				update_catalog_row(idx, formData);

				$("#edit-catalog-modal").modal('hide');
			},
		});

	});
</script>
<!-- End of CATALOG Handlers -->

<!-- Save the Data on the Server -->
<script>
	let vaidator = $('#edit-sending-form').validate({
	rules: {},
	messages:{},
	errorElement: "div",
	errorClass: "valid-error",
	showErrors: function(errorMap, errorList) {
					$("div.valid-error").remove();		// Remove all error messages to avoid duplicate messages
					this.defaultShowErrors();			// Show Errors
				},
	submitHandler: function(form) {
		// Assemble Data
		var formData = new FormData(form);

		$('#loader_img').show();
		$.ajax({
				url: "ajax/save_posting_json.php",
				type: "POST",
				data: formData,
				cache: false,
				processData: false,
				contentType: false,
				success: function(response) {
					$('#loader_img').hide();
					response = JSON.parse(response);
					if(response.success){
						swal({
								title: "Details Saved",
								text: "Sending details have been saved successfully.",
								icon: "success",
								confirmButtonClass: 'btn-success',
								confirmButtonText: 'Great'
						});
					}
					else{
						swal({
								title: "Save Failed",
								text: "Sending details could not be saved: " + response.msg,
								icon: "warning",
								confirmButtonClass: 'btn-warning',
								confirmButtonText: 'OK'
						});
					}
				},
				error: function(jqXHR, textStatus, errorThrown) {
					$('#loader_img').hide();
					swal("Server Error", "Something went wrong at the server. If this repeats please report the problem to YPS", "error");
				}
		});
		return false;
	},
});

</script>


</body>

</html>
<?php
}
else
{
	if (basename($_SERVER['HTTP_REFERER']) == THIS) {
		header("Location: manage_home.php");
		print("<script>location.href='manage_home.php'</script>");
	}
	else {
		header("Location: " . $_SERVER['HTTP_REFERER']);
		print("<script>location.href='".$_SERVER['HTTP_REFERER']."'</script>");
	}
}

?>
