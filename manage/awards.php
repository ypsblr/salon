<?php
include("inc/session.php");
include("inc/connect.php");
include("inc/lib.php");

// Since there are many columns with Null value, here is a safe way to show null
function safe($str, $default = "") {
	if (is_null($str))
		return $default;
	else
		return $str;
}

function isJson($string) {
   json_decode($string);
   return json_last_error() === JSON_ERROR_NONE;
}

function null_safe_date($date) {
	if ($date == NULL || $date == "")
		return "NULL";
	else
		return "'" . $date . "'";
}


if ( isset($_SESSION['admin_id']) ) {
	// Set up user and contest information
	include ("inc/load_common_data.php");

	// Initialize Empty Salon
	$award = array(
			"yearmonth" => "", "contest_name" => "", "is_international" => "", "registration_last_date" => "",
			"registration_start_date" => "", "results_ready" => "", "section" => "",
	);
	$yearmonth = 0;
	$section = "";

	// Load details for the contest
	if (isset($_REQUEST['yearmonth']) && isset($_REQUEST['section'])) {
		$yearmonth = $_REQUEST['yearmonth'];
		$section = $_REQUEST['section'];

		// Set up Salon
		$sql = "SELECT * FROM contest WHERE yearmonth = '$yearmonth' ";
		$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		if (mysqli_num_rows($query) == 0)
			$_SESSION['err_msg'] = "Salon for " . $yearmonth . " not found !";
		else {
			$row = mysqli_fetch_array($query);
			foreach ($award as $field => $value) {
				if (isset($row[$field]))
					$award[$field] = $row[$field];
			}
		}

		$award["section"] = $section;

		if ($award["is_international"] == "0")
			$recogniton_code_list = ["FIP", "YPS"];
		else
			$recogniton_code_list = ["FIAP", "PSA", "ICS", "MOL", "GPU", "FIP", "YPS"];

		// Create Section List
		$sql = "SELECT section, stub FROM section WHERE yearmonth = '$yearmonth' ";
		$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		$section_list = [];
		while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC))
			$section_list[$row['section']] = $row['stub'];

		$section_list["CONTEST"] = "";
		$award['section_list'] = $section_list;

		// Copy Award List
		// $sql = "SELECT * FROM award WHERE yearmonth = '$yearmonth' AND section = '$section' ";
		// $query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		// $award_list = [];
		// while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC))
		// 	$award_list[] = $row;
		// $award["award_list"] = $award_list;

		// Create various categories of awards
		$award_config["FIAP"] = [
						"1" => array ( "name" => "FIAP Gold", "weight" => "1", "sequence" => "1",
										"has_medal" => "1", "has_pin" => "0", "has_ribbon" => "0", "has_memento" => "0",
										"has_gift" => "0", "has_certificate" => "1" ),
						"2" => array ( "name" => "FIAP Silver", "weight" => "1",  "sequence" => "1",
										"has_medal" => "1", "has_pin" => "0", "has_ribbon" => "0", "has_memento" => "0",
										"has_gift" => "0", "has_certificate" => "1" ),
						"3" => array ( "name" => "FIAP Bronze", "weight" => "1",  "sequence" => "1",
										"has_medal" => "1", "has_pin" => "0", "has_ribbon" => "0", "has_memento" => "0",
										"has_gift" => "0", "has_certificate" => "1" ),
						"9" => array ( "name" => "FIAP Honorable Mention", "weight" => "1", "sequence" => "1",
										"has_medal" => "0", "has_pin" => "0", "has_ribbon" => "1", "has_memento" => "0",
										"has_gift" => "0", "has_certificate" => "1" ),
					];
		$award_config["PSA"] = [
						"1" => array ( "name" => "PSA Gold", "weight" => "1", "sequence" => "2",
										"has_medal" => "1", "has_pin" => "0", "has_ribbon" => "0", "has_memento" => "0",
										"has_gift" => "0", "has_certificate" => "1" ),
						"2" => array ( "name" => "PSA Silver", "weight" => "1",  "sequence" => "2",
										"has_medal" => "1", "has_pin" => "0", "has_ribbon" => "0", "has_memento" => "0",
										"has_gift" => "0", "has_certificate" => "1" ),
						"3" => array ( "name" => "PSA Bronze", "weight" => "1",  "sequence" => "2",
										"has_medal" => "1", "has_pin" => "0", "has_ribbon" => "0", "has_memento" => "0",
										"has_gift" => "0", "has_certificate" => "1" ),
						"9" => array ( "name" => "PSA Honorable Mention", "weight" => "1",  "sequence" => "2",
										"has_medal" => "0", "has_pin" => "0", "has_ribbon" => "1", "has_memento" => "0",
										"has_gift" => "0", "has_certificate" => "1" ),
					];
		$award_config["ICS"] = [
						"1" => array ( "name" => "ICS Gold", "weight" => "1", "sequence" => "3",
										"has_medal" => "1", "has_pin" => "0", "has_ribbon" => "0", "has_memento" => "0",
										"has_gift" => "0", "has_certificate" => "1" ),
						"2" => array ( "name" => "ICS Silver", "weight" => "1", "sequence" => "3",
										"has_medal" => "1", "has_pin" => "0", "has_ribbon" => "0", "has_memento" => "0",
										"has_gift" => "0", "has_certificate" => "1" ),
						"3" => array ( "name" => "ICS Bronze", "weight" => "1", "sequence" => "3",
										"has_medal" => "1", "has_pin" => "0", "has_ribbon" => "0", "has_memento" => "0",
										"has_gift" => "0", "has_certificate" => "1" ),
						"9" => array ( "name" => "ICS Honorable Mention", "weight" => "1", "sequence" => "3",
										"has_medal" => "0", "has_pin" => "0", "has_ribbon" => "1", "has_memento" => "0",
										"has_gift" => "0", "has_certificate" => "1" ),
					];
		$award_config["MOL"] = [
						"1" => array ( "name" => "MOL Gold", "weight" => "1", "sequence" => "4",
										"has_medal" => "1", "has_pin" => "0", "has_ribbon" => "0", "has_memento" => "0",
										"has_gift" => "0", "has_certificate" => "1" ),
						"2" => array ( "name" => "MOL Silver", "weight" => "1", "sequence" => "4",
										"has_medal" => "1", "has_pin" => "0", "has_ribbon" => "0", "has_memento" => "0",
										"has_gift" => "0", "has_certificate" => "1" ),
						"3" => array ( "name" => "MOL Bronze", "weight" => "1", "sequence" => "4",
										"has_medal" => "1", "has_pin" => "0", "has_ribbon" => "0", "has_memento" => "0",
										"has_gift" => "0", "has_certificate" => "1" ),
						"9" => array ( "name" => "MOL Honorable Mention", "weight" => "1", "sequence" => "4",
										"has_medal" => "0", "has_pin" => "0", "has_ribbon" => "1", "has_memento" => "0",
										"has_gift" => "0", "has_certificate" => "1" ),
					];
		$award_config["GPU"] = [
						"1" => array ( "name" => "GPU Gold", "weight" => "1", "sequence" => "5",
										"has_medal" => "1", "has_pin" => "0", "has_ribbon" => "0", "has_memento" => "0",
										"has_gift" => "0", "has_certificate" => "1" ),
						"2" => array ( "name" => "GPU Silver", "weight" => "1", "sequence" => "5",
										"has_medal" => "1", "has_pin" => "0", "has_ribbon" => "0", "has_memento" => "0",
										"has_gift" => "0", "has_certificate" => "1" ),
						"3" => array ( "name" => "GPU Bronze", "weight" => "1", "sequence" => "5",
										"has_medal" => "1", "has_pin" => "0", "has_ribbon" => "0", "has_memento" => "0",
										"has_gift" => "0", "has_certificate" => "1" ),
						"9" => array ( "name" => "GPU Honorable Mention", "weight" => "1", "sequence" => "5",
										"has_medal" => "0", "has_pin" => "0", "has_ribbon" => "1", "has_memento" => "0",
										"has_gift" => "0", "has_certificate" => "1" ),
					];
		$award_config["FIP"] = [
						"1" => array ( "name" => "FIP Gold", "weight" => ($award['is_international'] == '1' ? "1" : "7" ), "sequence" => "8",
										"has_medal" => "1", "has_pin" => "0", "has_ribbon" => "0", "has_memento" => "0",
										"has_gift" => "0", "has_certificate" => "1" ),
						"2" => array ( "name" => "FIP Silver", "weight" => ($award['is_international'] == '1' ? "1" : "6" ), "sequence" => "8",
										"has_medal" => "1", "has_pin" => "0", "has_ribbon" => "0", "has_memento" => "0",
										"has_gift" => "0", "has_certificate" => "1" ),
						"3" => array ( "name" => "FIP Bronze", "weight" => ($award['is_international'] == '1' ? "1" : "5" ), "sequence" => "8",
										"has_medal" => "1", "has_pin" => "0", "has_ribbon" => "0", "has_memento" => "0",
										"has_gift" => "0", "has_certificate" => "1" ),
						"9" => array ( "name" => "FIP Honorable Mention", "weight" => ($award['is_international'] == '1' ? "1" : "3" ), "sequence" => "8",
										"has_medal" => "0", "has_pin" => "0", "has_ribbon" => "1", "has_memento" => "0",
										"has_gift" => "0", "has_certificate" => "1" ),
					];
		$award_config["YPS"] = [
					"1" => array ( "name" => "YPS Gold", "weight" => ($award['is_international'] == '1' ? "1" : "6" ), "sequence" => "9",
									"has_medal" => "1", "has_pin" => "0", "has_ribbon" => "0", "has_memento" => "0",
									"has_gift" => "0", "has_certificate" => "1" ),
					"2" => array ( "name" => "YPS Silver", "weight" => ($award['is_international'] == '1' ? "1" : "5" ), "sequence" => "9",
									"has_medal" => "1", "has_pin" => "0", "has_ribbon" => "0", "has_memento" => "0",
									"has_gift" => "0", "has_certificate" => "1" ),
					"3" => array ( "name" => "YPS Bronze", "weight" => ($award['is_international'] == '1' ? "1" : "4" ), "sequence" => "9",
									"has_medal" => "1", "has_pin" => "0", "has_ribbon" => "0", "has_memento" => "0",
									"has_gift" => "0", "has_certificate" => "1" ),
					"9" => array ( "name" => "YPS Honorable Mention", "weight" => ($award['is_international'] == '1' ? "1" : "2" ), "sequence" => "9",
									"has_medal" => "0", "has_pin" => "0", "has_ribbon" => "1", "has_memento" => "0",
									"has_gift" => "0", "has_certificate" => "1" ),
				];


		$award["medals"] = $award_config;
		$award["acceptance"] = array ( "name" => "Acceptance", "weight" => "1", "sequence" => "1",
										"has_medal" => "0", "has_pin" => "0", "has_ribbon" => "0", "has_memento" => "0",
										"has_gift" => "0", "has_certificate" => "1" );

		// Load Awards
		$sql  = "SELECT short_code, organization_name, recognition_id FROM recognition ";
		$sql .= " WHERE yearmonth = '$yearmonth' ";
		$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		$recognition_list = [];
		while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
			$recognition_list[$row['short_code']] = $row;
		}
		$award["recognition_list"] = $recognition_list;


		// Do not Copy Rules
		// Set submission_last_date to registration_last_date of target salon
		if (isset($_REQUEST['clonefrom']) && isset($_REQUEST['clonesection'])) {
			$clonefrom = $_REQUEST['clonefrom'];
			$clonesection = $_REQUEST['clonesection'];
			$yearmonth = $_REQUEST['yearmonth'];
			$section = $_REQUEST['section'];

			// Find the last award id
			$sql = "SELECT MAX(award_id) AS last_award_id FROM award WHERE yearmonth = '$yearmonth' ";
			$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
			$row = mysqli_fetch_array($query);
			$award_id = ceil($row['last_award_id'] / 100) * 100;

			$sql = "SELECT * FROM award WHERE yearmonth = '$clonefrom' AND section = '$clonesection' ";
			$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
			while ($row = mysqli_fetch_array($query)) {
				$level = $row['level'];
				$sequence = (isset($sequence_list[$row['recognition_code']]) ? $sequence_list[$row['recognition_code']] : "99");
				$award_group = $row['award_group'];
				$award_type = $row['award_type'];
				$award_name = $row['award_name'];
				$recognition_code = $row['recognition_code'];
				$description = mysqli_real_escape_string($DBCON, $row['description']);
				$number_of_awards = $row['number_of_awards'];
				$award_weight = $row['award_weight'];
				$has_medal = $row['has_medal'];
				$has_pin = $row['has_pin'];
				$has_ribbon = $row['has_ribbon'];
				$has_memento = $row['has_memento'];
				$has_gift = $row['has_gift'];
				$has_certificate = $row['has_certificate'];
				$cash_award = $row['cash_award'];
				$sponsored_awards = $row['sponsored_awards'];
				$sponsorship_per_award = $row['sponsorship_per_award'];
				$partial_sponsorship_permitted = $row['partial_sponsorship_permitted'];

				// Apply standards
				if ($clonesection == "CONTEST" ) {
					$level = 999;
					$award_weight = 0;
				}
				elseif ($level == 99) {
					// Acceptance
					$acceptance = $award['acceptance'];

					$sequence = $acceptance['sequence'];
					$award_name = $acceptance['name'];
					$award_weight = $acceptance['weight'];
					$has_medal = $acceptance['has_medal'];
					$has_pin = $acceptance['has_pin'];
					$has_ribbon = $acceptance['has_ribbon'];
					$has_memento = $acceptance['has_memento'];
					$has_gift = $acceptance['has_gift'];
					$has_certificate = $acceptance['has_certificate'];
					$cash_award = 0;
					$sponsored_awards = 0;
					$sponsorship_per_award = 0;
					$partial_sponsorship_permitted = 0;
				}
				elseif (isset($award['medals'][$recognition_code][$level])) {
					// Medals
					$medal = $award['medals'][$recognition_code][$level];

					$sequence = $medal['sequence'];
					$award_name = $medal['name'];
					$award_weight = $medal['weight'];
					$has_medal = $medal['has_medal'];
					$has_pin = $medal['has_pin'];
					$has_ribbon = $medal['has_ribbon'];
					$has_memento = $medal['has_memento'];
					$has_gift = $medal['has_gift'];
					$has_certificate = $medal['has_certificate'];
				}

				$sponsorship_last_date = $award['registration_last_date'];
				++ $award_id;

				// Insert data into current salon
				$sql  = "INSERT INTO award (yearmonth, award_id, level, sequence, section, award_group, award_type, award_name, ";
				$sql .= "            recognition_code, description, number_of_awards, award_weight, has_medal, has_pin, has_ribbon, ";
				$sql .= "            has_memento, has_gift, has_certificate, cash_award, sponsored_awards, sponsorship_per_award, ";
				$sql .= "            partial_sponsorship_permitted, sponsorship_last_date) ";
				$sql .= "VALUES ('$yearmonth', '$award_id', '$level', '$sequence', '$section', '$award_group', '$award_type', '$award_name', ";
				$sql .= "       '$recognition_code', '$description', '$number_of_awards', '$award_weight', '$has_medal', '$has_pin', '$has_ribbon', ";
				$sql .= "       '$has_memento', '$has_gift', '$has_certificate', '$cash_award', '$sponsored_awards', '$sponsorship_per_award', ";
				$sql .= "       '$partial_sponsorship_permitted', '$sponsorship_last_date') ";
				mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
			}
		}

		// Replicate to other sections in the salon
		if (isset($_REQUEST['replicate_award'])) {

			$sql = "START TRANSACTION";
			mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

			// Delete awards defined in other sections
			$sql = "DELETE FROM award WHERE yearmonth = '$yearmonth' AND section != '$section' AND section != 'CONTEST' ";
			mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

			// Get list of sections
			$sql = "SELECT section FROM section WHERE yearmonth = '$yearmonth' AND section != '$section' ";
			$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
			while ($row = mysqli_fetch_array($query)) {
				$target_section = $row['section'];
				// Get award_id
				$sql = "SELECT MAX(award_id) AS last_award_id FROM award WHERE yearmonth = '$yearmonth' ";
				$subq = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
				$subr = mysqli_fetch_array($subq);
				$award_id = ceil($subr['last_award_id'] / 100) * 100;

				// Get a list of awards to copy
				$sql = "SELECT * FROM award WHERE yearmonth = '$yearmonth' AND section = '$section' ";
				$subq = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
				while ($subr = mysqli_fetch_array($subq)) {
					++ $award_id;
					$level = $subr['level'];
					$sequence = $subr['sequence'];
					$award_group = $subr['award_group'];
					$award_type = $subr['award_type'];
					$award_name = $subr['award_name'];
					$recognition_code = $subr['recognition_code'];
					$description = mysqli_real_escape_string($DBCON, $subr['description']);
					$number_of_awards = $subr['number_of_awards'];
					$award_weight = $subr['award_weight'];
					$has_medal = $subr['has_medal'];
					$has_pin = $subr['has_pin'];
					$has_ribbon = $subr['has_ribbon'];
					$has_memento = $subr['has_memento'];
					$has_gift = $subr['has_gift'];
					$has_certificate = $subr['has_certificate'];
					$cash_award = $subr['cash_award'];
					$sponsored_awards = $subr['sponsored_awards'];
					$sponsorship_per_award = $subr['sponsorship_per_award'];
					$partial_sponsorship_permitted = $subr['partial_sponsorship_permitted'];
					$sponsorship_last_date = null_safe_date($subr['sponsorship_last_date']);

					$sql  = "INSERT INTO award (yearmonth, award_id, level, sequence, section, award_group, award_type, award_name, ";
					$sql .= "            recognition_code, description, number_of_awards, award_weight, has_medal, has_pin, has_ribbon, ";
					$sql .= "            has_memento, has_gift, has_certificate, cash_award, sponsored_awards, sponsorship_per_award, ";
					$sql .= "            partial_sponsorship_permitted, sponsorship_last_date) ";
					$sql .= "VALUES ('$yearmonth', '$award_id', '$level', '$sequence', '$target_section', '$award_group', '$award_type', '$award_name', ";
					$sql .= "       '$recognition_code', '$description', '$number_of_awards', '$award_weight', '$has_medal', '$has_pin', '$has_ribbon', ";
					$sql .= "       '$has_memento', '$has_gift', '$has_certificate', '$cash_award', '$sponsored_awards', '$sponsorship_per_award', ";
					$sql .= "       '$partial_sponsorship_permitted', $sponsorship_last_date) ";
					mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
				}
			}
			$sql = "COMMIT";
			mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

			$_SESSION['success_msg'] = "Awards successfully replicated to other sections";
		}

		// Load Awards
		$sql  = "SELECT * FROM award ";
		$sql .= " WHERE yearmonth = '$yearmonth' ";
		$sql .= "   AND section = '$section' ";
		$sql .= " ORDER BY level, sequence ";
		$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		$award_list = [];
		while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
			$award_list[$row['award_id']] = $row;
		}
		$award["award_list"] = $award_list;
	}

?>

<!DOCTYPE html>
<html>

<head>
    <!-- Page title -->
	<title>Youth Photographic Society | Salon Management</title>

	<?php include "inc/header.php"; ?>

    <link rel="stylesheet" href="plugin/datatable/css/dataTables.bootstrap.min.css" />

	<style>
		div.filter-button {
			display:inline-block;
			margin-right: 15px;
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
						MANAGE AWARDS - <?= $yearmonth == 0 ? "" : "FOR " . $award['contest_name'];?>
					</h3>
					<br>
					<form role="form" method="post" name="select-salon-form" action="awards.php" enctype="multipart/form-data" >
						<div class="row form-group">
							<div class="col-sm-6">
								<label for="yearmonth">Select Salon</label>
								<select class="form-control" name="yearmonth" id="select-yearmonth" value="<?= $yearmonth;?>">
									<option value="0" <?= ($row['yearmonth'] == $yearmonth) ? "selected" : "";?>>None</option>
								<?php
									$sql = "SELECT yearmonth, contest_name FROM contest ORDER BY yearmonth DESC ";
									$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
									while ($row = mysqli_fetch_array($query)) {
								?>
									<option value="<?= $row['yearmonth'];?>" <?= ($row['yearmonth'] == $yearmonth) ? "selected" : "";?>><?= $row['contest_name'];?></option>
								<?php
									}
								?>
								</select>
							</div>
							<div class="col-sm-4">
								<label for="section">Select Section</label>
								<select class="form-control" name="section" id="select-section" value="<?= $section;?>" >
									<!-- to be filled by ajax call -->
								<?php
									if ($section != "" && sizeof($section_list) > 0) {
										foreach($section_list as $section_option => $stub) {
								?>
									<option value="<?= $section_option;?>" <?= ($section_option == $section) ? "selected" : "";?>><?= $section_option;?></option>
								<?php
										}
									}
								?>
									<option value="CONTEST" <?= ($section == "CONTEST") ? "selected" : "";?>>CONTEST</option>
								</select>
							</div>
							<div class="col-sm-2">
								<button type="submit" class="btn btn-info pull-right" name="select-contest-button" ><i class="fa fa-edit"></i> EDIT </a>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>


		<div class="content">
			<?php
				if ($yearmonth != 0 && sizeof($award_list) == 0) {
			?>
			<!-- Select a Salon to clone from if current list of recognitions is empty -->
			<h3 class="text-info">Copy Awards from another Salon</h3>
			<form role="form" method="post" name="copy-awards-form" id="copy-awards-form" action="awards.php" enctype="multipart/form-data" >
				<input type="hidden" name="yearmonth" id="clone_yearmonth" value="<?= $yearmonth;?>" >
				<input type="hidden" name="section" id="clone_section" value="<?= $section;?>" >
				<div class="row form-group">
					<div class="col-sm-6">
						<label for="clonefrom">Select Salon</label>
						<select class="form-control" name="clonefrom" id="clone-yearmonth">
						<?php
							$sql  = "SELECT contest.yearmonth, contest_name, (COUNT(*) / COUNT(DISTINCT section)) AS num_awards_per_section ";
							$sql .= "  FROM contest, award ";
							$sql .= " WHERE award.yearmonth = contest.yearmonth ";
							if ($section == "CONTEST") {
								$sql .= "   AND section = 'CONTEST' ";
							}
							else {
								$sql .= "   AND award_type = 'pic' ";
								$sql .= "   AND section != 'CONTEST' ";
								$sql .= "   AND section LIKE '%NATURE%' ";
							}
							$sql .= " GROUP BY award.yearmonth, award.section ";
							$sql .= " ORDER BY yearmonth DESC ";
							$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
							while ($row = mysqli_fetch_array($query)) {
						?>
							<option value="<?= $row['yearmonth'];?>" ><?= $row['contest_name'] . " (" . $row['num_awards_per_section'] * 1 . " awards)";?></option>
						<?php
							}
						?>
						</select>
					</div>
					<div class="col-sm-4">
						<label for="section">Select Section</label>
						<select class="form-control" name="clonesection" id="clone-section" value="<?= $section;?>" >
							<!-- to be filled by ajax call -->
						</select>
					</div>
					<div class="col-sm-2">
						<span class="input-group-btn">
							<button type="submit" class="btn btn-info pull-right" name="clone-recognitions-button" ><i class="fa fa-copy"></i> COPY </a>
						</span>
					</div>
				</div>
			</form>
			<?php
				}
			?>
			<?php
				if ($award['results_ready'] == "0") {
			?>
			<h3 class="text-info">Award Add/Edit</h3>
			<form role="form" method="post" id="award_details_form" name="award_details_form" action="#" enctype="multipart/form-data" >
				<!-- Hidden non-edited fields -->
				<input type="hidden" name="is_international" id="is_international" value="<?= $award['is_international'];?>">
				<input type="hidden" name="registration_start_date" id="registration_start_date" value="<?= $award['registration_start_date'];?>">
				<input type="hidden" name="registration_last_date" id="registration_last_date" value="<?= $award['registration_last_date'];?>">
				<input type="hidden" name="award_id" id="award_id" value="0" >
				<input type="hidden" name="is_edit_award" id="is_edit_award" value="0" >

				<!-- Edited Fields -->
				<div class="row form-group">
					<div class="col-sm-2">
						<label for="yearmonth">Salon ID</label>
						<input type="number" name="yearmonth" class="form-control" id="yearmonth" value="<?= $award['yearmonth'];?>" readonly >
					</div>
					<div class="col-sm-6">
						<label for="contest_name">Contest Name</label>
						<input type="text" name="contest_name" class="form-control" id="contest_name" value="<?= $award['contest_name'];?>" readonly >
					</div>
					<div class="col-sm-4">
						<label for="section">Section</label>
						<input type="text" name="section" class="form-control" id="section" value="<?= $section;?>" readonly >
					</div>
				</div>

				<!-- Collect Awaard Name -->
				<div class="row form-group">
					<div class="col-sm-3">
						<label for="short_code">Patronage</label>
						<select class="form-control" name="recognition_code" id="recognition_code" >
						<?php
							$sql  = "SELECT short_code, organization_name ";
							$sql .= "  FROM recognition ";
							$sql .= " WHERE yearmonth = '$yearmonth' ";
							$sql .= " ORDER BY organization_name DESC ";
							$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
							while ($row = mysqli_fetch_array($query)) {
						?>
							<option value="<?= $row['short_code'];?>" ><?= $row['organization_name'];?></option>
						<?php
							}
						?>
						</select>
					</div>
					<div class="col-sm-3">
						<label for="level">Level</label>
						<select class="form-control" name="level" id="level" >
						</select>
					</div>
					<div class="col-sm-3">
						<label for="sequence">Sequence</label>
						<input type="number" name="sequence" class="form-control" id="sequence" >
					</div>
					<div class="col-sm-3">
						<label for="award_weight">Weight</label>
						<input type="number" name="award_weight" class="form-control" id="award_weight" >
					</div>
				</div>

				<!-- Collect Awaard Name -->
				<div class="row form-group">
					<div class="col-sm-6">
						<label for="award_name">Award Name</label>
						<input type="text" name="award_name" class="form-control" id="award_name" >
					</div>
					<div class="col-sm-3">
						<label for="award_group">Group</label>
						<input type="text" name="award_group" class="form-control" id="award_group" >
					</div>
					<div class="col-sm-3">
						<label for="award_type">Type</label>
						<select class="form-control" name="award_type" id="award_type" >
						<?php
							if ($section == "CONTEST") {
						?>
							<option value="entry">Participant Award</option>
							<option value="club">Club Award</option>
							<option value="pic">Picture Award</option>
						<?php
							}
							else {
						?>
							<option value="pic">Picture Award</option>
						<?php
							}
						?>
						</select>
					</div>
				</div>

				<div class="row form-group">
					<div class="col-sm-4">
						<label for="number_of_awards"># Awards</label>
						<input type="text" name="number_of_awards" class="form-control" id="number_of_awards" >
					</div>
					<div class="col-sm-4">
						<label for="cash_award">Cash Award</label>
						<input type="number" name="cash_award" class="form-control" id="cash_award" >
					</div>
				</div>

				<!-- Sponsorship -->
				<div class="row form-group">
					<div class="col-sm-4">
						<label for="sponsored_awards">Awards Sponsored</label>
						<input type="number" name="sponsored_awards" class="form-control" id="sponsored_awards" >
					</div>
					<div class="col-sm-4">
						<div class="row">
							<div class="col-sm-12">
								<label for="sponsorship_per_award">Sponsorship per award</label>
								<input type="number" name="sponsorship_per_award" class="form-control" id="sponsorship_per_award" >
							</div>
						</div>
						<div class="row">
							<div class="col=sm-12">
								<div class="input-group form-control">
									<span class="input-group-addon">
										<input type="checkbox" name="partial_sponsorship_permitted" id="partial_sponsorship_permitted" value="1" >
									</span>
									<input type="text" class="form-control" readonly value="Allow Partial Sponsorship" >
								</div>
							</div>
						</div>
					</div>
					<div class="col-sm-4">
						<label for="sponsorship_last_date">Sponsorship Last Date</label>
						<input type="date" name="sponsorship_last_date" class="form-control" id="sponsorship_last_date" >
					</div>
				</div>

				<!-- Flags -->
				<div class="row form-group">
					<div class="col-sm-6">
						<div class="row">
							<div class="col=sm-12">
								<div class="input-group form-control">
									<span class="input-group-addon">
										<input type="checkbox" name="has_medal" id="has_medal" value="1" >
									</span>
									<input type="text" class="form-control" readonly value="Has Medal" >
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col=sm-12">
								<div class="input-group form-control">
									<span class="input-group-addon">
										<input type="checkbox" name="has_ribbon" id="has_ribbon" value="1" >
									</span>
									<input type="text" class="form-control" readonly value="Has Ribbon" >
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col=sm-12">
								<div class="input-group form-control">
									<span class="input-group-addon">
										<input type="checkbox" name="has_memento" id="has_memento" value="1" >
									</span>
									<input type="text" class="form-control" readonly value="Has Memento" >
								</div>
							</div>
						</div>
					</div>
					<div class="col-sm-6">
						<div class="row">
							<div class="col=sm-12">
								<div class="input-group form-control">
									<span class="input-group-addon">
										<input type="checkbox" name="has_pin" id="has_pin" value="1" >
									</span>
									<input type="text" class="form-control" readonly value="Has FIAP Pin" >
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col=sm-12">
								<div class="input-group form-control">
									<span class="input-group-addon">
										<input type="checkbox" name="has_certificate" id="has_certificate" value="1" >
									</span>
									<input type="text" class="form-control" readonly value="Has Certificate" >
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col=sm-12">
								<div class="input-group form-control">
									<span class="input-group-addon">
										<input type="checkbox" name="has_gift" id="has_gift" value="1" >
									</span>
									<input type="text" class="form-control" readonly value="Has Gift" >
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="row form-group">
					<div class="col-sm-8">
						<label>Award Description</label>
						<textarea name="description" id="description" rows="4"></textarea>
					</div>
				</div>

				<!-- Update -->
				<br>
				<div class="row form-group">
					<div class="col-sm-9">
						<button type="submit" class="btn btn-info" id="update_award" name="update_award">
							<span id="update_button"><i class="fa fa-plus"></i> Add</span>
						</button>
					</div>
				</div>
			</form>
			<hr>
			<?php
				}	// if results_ready = 0
			?>

			<!-- Award List -->
			<div id="award_list">
				<div class="row">
					<div class="col-sm-6">
						<h3 class="text-info">List of Awards</h3>
					</div>
					<div class="col-sm-6">
						<?php
							if ($award['results_ready'] == "0") {
						?>
						<form role="form" method="post" name="award_replicate_form" action="awards.php" enctype="multipart/form-data" >
							<!-- Hidden non-edited fields -->
							<input type="hidden" name="yearmonth" value="<?= $yearmonth;?>" >
							<input type="hidden" name="section" value="<?= $section;?>" >
							<div class="row form-group">
								<div class="col-sm-12">
									<button type="submit" class="btn btn-info pull-right" name="replicate_award">
										<i class="fa fa-copy"></i> Replicate to all other sections
									</button>
								</div>
							</div>
						</form>
						<?php
							}
						?>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-12">
						<table class="table">
							<thead>
								<tr>
									<th>Award Name</th>
									<th>Patronage</th>
									<th># Awards</th>
									<th># Sponsored</th>
									<th>Cash Award</th>
									<th>Sponsorship per Award</th>
									<th>Award Group</th>
								</tr>
							</thead>
							<tbody>
							<?php
								if (isset($award_list)) {
									foreach ($award_list as $award_id => $award_data) {
							?>
								<tr id="<?= $award_id;?>-row">
									<td><?= $award_data['award_name'];?></td>
									<td><?= $award_data['recognition_code'];?></td>
									<td><?= $award_data['number_of_awards'];?></td>
									<td><?= $award_data['sponsored_awards'];?></td>
									<td><?= $award_data['cash_award'];?></td>
									<td><?= $award_data['sponsorship_per_award'];?></td>
									<td><?= $award_data['award_group'];?></td>
									<td>
										<?php
											if ($award['results_ready'] == "0") {
										?>
										<button class="btn btn-info award-edit-button"
												data-award-id='<?= $award_data['award_id'];?>'
												data-award='<?= json_encode($award_data, JSON_FORCE_OBJECT);?>' >
											<i class="fa fa-edit"></i>
										</button>
										<?php
											}
										?>
									</td>
									<td>
										<?php
											if ($award['results_ready'] == "0") {
										?>
										<button class="btn btn-danger award-delete-button"
												data-award-id='<?= $award_data['award_id'];?>'
												data-award='<?= json_encode($award_data, JSON_FORCE_OBJECT);?>' >
											<i class="fa fa-trash"></i>
										</button>
										<?php
											}
										?>
									</td>
								</tr>
							<?php
									}
								}
							?>
								<tr id="end_of_award_list">
									<th><?= isset($award_list) ? sizeof($award_list) : 0;?></th>
									<th>End of List</th>
									<th></th>
									<th></th>
									<th></th>
									<th></th>
									<th></th>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>

	</div>
<?php
      include("inc/footer.php");
?>

<!-- Form Validation -->
<script src="plugin/validate/js/jquery.validate.min.js"></script>

<!-- Custom Validation Functions -->
<script src="custom/js/validate.js"></script>

<!-- Include Tiny MCE Editor for notices -->
<!-- tinymce editor -->
<script src='plugin/tinymce/tinymce.min.js'></script>
<script src='plugin/tinymce/plugins/link/plugin.min.js'></script>
<script src='plugin/tinymce/plugins/lists/plugin.min.js'></script>
<script src='plugin/tinymce/plugins/image/plugin.min.js'></script>
<script src='plugin/tinymce/plugins/table/plugin.min.js'></script>


<!-- Action Handlers -->
<script>
	$(document).ready(function(){
		// Hide content till a salon is selected
		if($("#yearmonth").val() == 0)
			$(".content").hide();

		// Init Tinymce
		tinymce.init({
			selector: '#description',
			height: 400,
			plugins : 'link lists image table',
		});

		// Get a list of sections
		function get_section_list(yearmonth, target) {
			$('#loader_img').show();
			$.post("ajax/get_section_options.php", {yearmonth}, function(response) {
				$('#loader_img').hide();
				response = JSON.parse(response);
				if(response.success){
					$(target).html(response.section_options);
				}
				else{
					$(target).html("<option>Unable to retrieve sections for the salon</option>");
				}
			});
		}

		$("#select-yearmonth").on("change", function(){
			get_section_list($("#select-yearmonth").val(), "#select-section");
		});

		$("#clone-yearmonth").on("change", function(){
			get_section_list($("#clone-yearmonth").val(), "#clone-section");
		});
	});
</script>

<!-- Add / Update Award -->
<script>
	$(document).ready(function(){
		let vaidator = $('#award_details_form').validate({
			rules:{
				level : { required : true, },
				sequence : { required : true, },
				award_group : { required : true, },
				award_type : { required : true, },
				award_name : { required : true, },
				recognition_code : { required : true, },
				number_of_awards : {
					required : true,
					min : 1,
					max : 100,
				},
				award_weight : {
					required : true,
					min : 0,
					max : 100,
				},
				cash_award : {
					required : true,
					min : 0
				},
				sponsored_awards : {
					required : true,
					min : 0,
				},
				sponsorship_per_award : {
					required : function(){return $("#sponsored_awards").val() > 0;},
					min : 0,
				},
				sponsorship_last_date : {
					required : function(){return $("#sponsored_awards").val() > 0;},
					date_min : "#registration_start_date",
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
				// Set ext area with value from tinymce
				$("#description").text(tinymce.get("description").getContent());

				// Assemble Data
				var formData = new FormData(form);

				$('#loader_img').show();
				$.ajax({
						url: "ajax/save_award.php",
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
										text: "Award data has been saved successfully.",
										icon: "success",
										confirmButtonClass: 'btn-success',
										confirmButtonText: 'Great'
								});
								// Remove existing table row
								$("#" + $("#award_id").val() + "-row").remove();
								// Add to table at the end
								$(response.row_html).insertBefore("#end_of_award_list");
								// Re-install handlers
								$(".award-edit-button").click(function(){
									edit_award(this);
								});
								$(".award-delete-button").click(function(){
									delete_award(this);
								});
								// Reset Form fields to default
								$("#is_edit_award").val("0");
								$("#award_id").val("0");
								$("#level").val("0");
								$("#sequence").val("0");
								$("#award_group").val("");
								$("#award_type").val("");
								$("#award_name").val("");
								$("#recognition_code").val("");
								$("#description").text("");
								tinymce.get("description").setContent("");
								$("#number_of_awards").val("0");
								$("#award_weight").val("0");
								$("#has_medal").prop("checked", false);
								$("#has_pin").prop("checked", false);
								$("#has_ribbon").prop("checked", false);
								$("#has_memento").prop("checked", false);
								$("#has_gift").prop("checked", false);
								$("#has_certificate").prop("checked", false);
								$("#cash_award").val("0");
								$("#sponsored_awards").val("0");
								$("#sponsorship_per_award").val("0");
								$("#partial_sponsorship_permitted").prop("checked", false);
								$("#sponsorship_last_date").val($("#registration_last_date").val());
								$("#update_button").html("<i class='fa fa-plus'></i> Add");
							}
							else{
								swal({
										title: "Save Failed",
										text: "Patronage could not be saved: " + response.msg,
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
	});
</script>

<!-- Handle Delete Award -->
<script>
	// Handle delete button request
	function delete_award(button) {
		let award_id = $(button).attr("data-award-id");
		let award = JSON.parse($(button).attr("data-award"));
		let yearmonth = $("#yearmonth").val();
		swal({
			title: 'DELETE Confirmation',
			text:  "Do you want to delete the award " + award.award_name + " ?",
			icon: "warning",
			buttons: true,
			dangerMode: true,
		})
		.then(function (delete_award) {
			if (delete_award) {
				$('#loader_img').show();
				$.post("ajax/delete_award.php", {yearmonth, award_id}, function(response) {
					$('#loader_img').hide();
					response = JSON.parse(response);
					if(response.success){
						$("#" + award_id + "-row").remove();
						swal({
								title: "Removed",
								text: "Award " + award.award_name + " has been removed successfully.",
								icon: "success",
								confirmButtonClass: 'btn-success',
								confirmButtonText: 'Great'
						});
					}
					else{
						swal({
								title: "Remove Failed",
								text: "Unable to remove patronage: " + response.msg,
								icon: "warning",
								confirmButtonClass: 'btn-warning',
								confirmButtonText: 'OK'
						});
					}
				});
			}
		});
	}

	// Register Click Handler
	$(document).ready(function(){
		$(".award-delete-button").click(function(){
			delete_award(this);
		});
	});
</script>

<!-- Handle Edit Award -->
<script>
	// Global Variable - Award Config
	<?php
		if (isset($award_config)) {
	?>
	var award_config = JSON.parse('<?= json_encode($award_config, JSON_FORCE_OBJECT);?>');
	<?php
		}
	?>


	function edit_award(button) {
		let award_id = $(button).attr("data-award-id");
		let award = JSON.parse($(button).attr("data-award"));
		// Set up Levels for the recognition_code
		if (award.section == "CONTEST") {
			$("#level").html("<option value='999' selected>OVERALL</option>");
		}
		else if ( award_config[award.recognition_code] == undefined ) {
			$("#level").html("<option>Award Configuration Missing</option>");
		}
		else {
			let levels = award_config[award.recognition_code];
			let html = "";
			for (let level in levels) {
				html += "<option value='" + level + "'" + (award.level == level? "selected" : "") + ">" + levels[level].name + "</option>";
			}
			$("#level").html(html);
		}
		// Fill the form with Details
		$("#is_edit_award").val("1");
		$("#award_id").val(award_id);
		$("#level").val(award.level);
		$("#sequence").val(award.sequence);
		$("#award_group").val(award.award_group);
		$("#award_type").val(award.award_type);
		$("#award_name").val(award.award_name);
		$("#recognition_code").val(award.recognition_code);
		$("#description").text(award.description);
		tinymce.get("description").setContent(award.description);
		$("#number_of_awards").val(award.number_of_awards);
		if (award.section == "CONTEST") {
			$("#award_weight").val("0");
			$("#award_weight").attr("readonly", true);
		}
		else {
			$("#award_weight").val(award.section == "CONTEST" ? "0" : award.award_weight);
			$("#award_weight").removeAttr("readonly");
		}
		$("#has_medal").prop("checked", award.has_medal == '1');
		$("#has_pin").prop("checked", award.has_pin == '1');
		$("#has_ribbon").prop("checked", award.has_ribbon == '1');
		$("#has_memento").prop("checked", award.has_memento == '1');
		$("#has_gift").prop("checked", award.has_gift == '1');
		$("#has_certificate").prop("checked", award.has_certificate == '1');
		$("#cash_award").val(award.cash_award);
		$("#sponsored_awards").val(award.sponsored_awards);
		$("#sponsorship_per_award").val(award.sponsorship_per_award);
		$("#partial_sponsorship_permitted").prop("checked", award.partial_sponsorship_permitted == '1');
		$("#sponsorship_last_date").val(award.sponsorship_last_date);
		$("#update_button").html("<i class='fa fa-edit'></i> Update");

		swal({
				title: "Edit",
				text: "Details of the award " + award.award_name + " have been copied to the form. Edit the details and click on the Update button.",
				icon: "success",
				confirmButtonClass: 'btn-success',
				confirmButtonText: 'Great'
		});
	}

	$(document).ready(function(){
		$(".award-edit-button").click(function(){
			edit_award(this);
		});

		$("#recognition_code").on("change", function(){
			if ($("#section").val() == "CONTEST") {
				$("#level").html("<option value='999' selected>OVERALL</option>");
				$("#award_weight").val("0");
			}
			else if (! (award_config[$("#recognition_code").val()] == undefined) ) {

				// Change the options for levels
				let levels = award_config[$("#recognition_code").val()];
				let html = "";
				for (let lvl in levels) {
					html += "<option value='" + lvl + "' " + ($("#level").val() == lvl ? "selected" : "") + ">" + levels[lvl].name + "</option>";
				}
				$("#level").html(html);
				// Set the other values based on level
				let level = $("#level").val();
				$("#sequence").val(levels[level].sequence);
				$("#award_name").val(levels[level].name);
				$("#award_weight").val(levels[level].weight);
				$("#has_medal").attr("checked", (levels[level].has_medal == "1"));
				$("#has_pin").attr("checked", (levels[level].has_pin == "1"));
				$("#has_ribbon").attr("checked", (levels[level].has_ribbon == "1"));
				$("#has_memento").attr("checked", (levels[level].has_memento == "1"));
				$("#has_gift").attr("checked", (levels[level].has_gift == "1"));
				$("#has_certificate").attr("checked", (levels[level].has_certificate == "1"));
			}
		});

		$("#level").on("change", function(){
			if ($("#section").val() == "CONTEST") {
				$("#level").html("<option value='999' selected>OVERALL</option>");
				$("#award_weight").val("0");
			}
			else if (! (award_config[$("#recognition_code").val()] == undefined) ) {
				let levels = award_config[$("#recognition_code").val()];
				let level = $("#level").val();
				$("#sequence").val(levels[level].sequence);
				$("#award_name").val(levels[level].name);
				$("#award_weight").val(levels[level].weight);
				$("#has_medal").attr("checked", (levels[level].has_medal == "1"));
				$("#has_pin").attr("checked", (levels[level].has_pin == "1"));
				$("#has_ribbon").attr("checked", (levels[level].has_ribbon == "1"));
				$("#has_memento").attr("checked", (levels[level].has_memento == "1"));
				$("#has_gift").attr("checked", (levels[level].has_gift == "1"));
				$("#has_certificate").attr("checked", (levels[level].has_certificate == "1"));
			}
		});
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
