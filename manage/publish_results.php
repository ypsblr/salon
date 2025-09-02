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

function email_filter_from_data ($list) {
	$email_list = [];
	foreach ($list as $item) {
		list ($email, $items, $mailing_date, $tracking_no, $notes) = $item;
		$email_list[] = "'" . $email . "'";
	}
	return implode(",", $email_list);
}

function isJson($string) {
   json_decode($string);
   return json_last_error() === JSON_ERROR_NONE;
}

// Loads list of ttf font files
function filter_font_files($filename) {
	return preg_match("/\.ttf/", $filename);
}
function ttf_list() {
	$font_folder = "../PHPImageWorkshop/font";
	return array_filter(scandir($font_folder), "filter_font_files");
}
function font_options_list($selected = "") {
	$opt = "";
	foreach (ttf_list() as $font) {
		$opt .= "<option value='$font' " . ($font == $selected ? "selected" : "") . " >$font</option>";
	}
	return $opt;
}


if ( isset($_SESSION['admin_id']) ) {
	// Set up user and contest information
	include ("inc/load_common_data.php");

	// Initialize Empty Salon with default values where applicable
	$salon = array (
			"yearmonth" => "", "contest_name" => "",
			"judging_end_date" => NULL, "results_date" => NULL,
			"results_ready" => '0', "review_in_progress" => '0', "judging_in_progress" => '0', "judging_report_blob" => "judging_report.htm",
			"results_description_blob" => "results_description.htm", "chairman_message_blob" => "chairman_message.htm", "archived" => '0',
			"update_start_date" => NULL, "update_end_date" => NULL,
	);
	$yearmonth = 0;
	$is_contest_archived = false;

	// Fill $salon, if yearmonth passed
	if (isset($_REQUEST['yearmonth'])) {
		$yearmonth = $_REQUEST['yearmonth'];
		$sql = "SELECT * FROM contest WHERE yearmonth = '$yearmonth' ";
		$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		if (mysqli_num_rows($query) == 0)
			$_SESSION['err_msg'] = "Salon for " . $yearmonth . " not found !";
		else {
			$row = mysqli_fetch_array($query);
			foreach ($salon as $field => $value) {
				if (isset($row[$field]) && $row[$field] != $salon[$field])
					$salon[$field] = $row[$field];
			}
		}
		$is_contest_archived = ($salon['archived'] == '1' );
	}

	// Check if results for all awards have been published
	$results_missing = [];
	if ($yearmonth != 0) {
		// pic_result
		$pic_result_table = ($is_contest_archived ? "ar_pic_result" : "pic_result");
		$sql  = "SELECT award.award_id, award_name, section, award_type, number_of_awards, IFNULL(COUNT(pic_result.award_id), '0') AS awarded ";
		$sql .= "  FROM award LEFT JOIN $pic_result_table AS pic_result ";
		$sql .= "    ON pic_result.yearmonth = award.yearmonth AND pic_result.award_id = award.award_id ";
		$sql .= " WHERE award.yearmonth = '$yearmonth' ";
		$sql .= "   AND award.award_type = 'pic' ";
		$sql .= "   AND award.level < 99 ";
		$sql .= "   AND award.section != 'CONTEST' ";
		$sql .= " GROUP BY award.award_id ";
		$sql .= "HAVING number_of_awards != awarded ";
		$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		while($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
			$results_missing[$row['award_id']] = $row;
		}
		// pic_result - special picture award
		$pic_result_table = ($is_contest_archived ? "ar_pic_result" : "pic_result");
		$sql  = "SELECT award.award_id, award_name, section, award_type, number_of_awards, IFNULL(COUNT(pic_result.award_id), '0') AS awarded ";
		$sql .= "  FROM award LEFT JOIN $pic_result_table AS pic_result ";
		$sql .= "    ON pic_result.yearmonth = award.yearmonth AND pic_result.award_id = award.award_id ";
		$sql .= " WHERE award.yearmonth = '$yearmonth' ";
		$sql .= "   AND award.award_type = 'pic' ";
		$sql .= "   AND award.section = 'CONTEST' ";
		$sql .= " GROUP BY award.award_id ";
		$sql .= "HAVING number_of_awards != awarded ";
		$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		while($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
			$results_missing[$row['award_id']] = $row;
		}
		// entry result
		$sql  = "SELECT award.award_id, award_name, section, award_type, number_of_awards, IFNULL(COUNT(entry_result.award_id), '0') AS awarded ";
		$sql .= "  FROM award LEFT JOIN entry_result ";
		$sql .= "    ON entry_result.yearmonth = award.yearmonth AND entry_result.award_id = award.award_id ";
		$sql .= " WHERE award.yearmonth = '$yearmonth' ";
		$sql .= "   AND award.award_type = 'entry' ";
		$sql .= " GROUP BY award.award_id ";
		$sql .= "HAVING number_of_awards != awarded ";
		$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		while($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
			$results_missing[$row['award_id']] = $row;
		}
		// club result
		$sql  = "SELECT award.award_id, award_name, section, award_type, number_of_awards, IFNULL(COUNT(club_result.award_id), '0') AS awarded ";
		$sql .= "  FROM award LEFT JOIN club_result ";
		$sql .= "    ON club_result.yearmonth = award.yearmonth AND club_result.award_id = award.award_id ";
		$sql .= " WHERE award.yearmonth = '$yearmonth' ";
		$sql .= "   AND award.award_type = 'club' ";
		$sql .= " GROUP BY award.award_id ";
		$sql .= "HAVING number_of_awards != awarded ";
		$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		while($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
			$results_missing[$row['award_id']] = $row;
		}
		// Acceptances finalized
		$sql  = "SELECT award.award_id, award_name, section, award_type, number_of_awards, IFNULL(COUNT(pic_result.award_id), '0') AS awarded ";
		$sql .= "  FROM award LEFT JOIN $pic_result_table AS pic_result ";
		$sql .= "    ON pic_result.yearmonth = award.yearmonth AND pic_result.award_id = award.award_id ";
		$sql .= " WHERE award.yearmonth = '$yearmonth' ";
		$sql .= "   AND award.award_type = 'pic' ";
		$sql .= "   AND award.level = 99 ";
		$sql .= " GROUP BY award.award_id ";
		$sql .= "HAVING awarded = 0 ";
		$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		while($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
			$results_missing[$row['award_id']] = $row;
		}
	}

	// Check for Statistics
	$stats_missing = [];
	if ($yearmonth != 0) {
		// Check if Statistical Participation Data is available for all the categories
		$sql  = "SELECT award.award_group, stat_category, IFNULL(COUNT(stats_participation.stat_category), 0) AS num_stats ";
		$sql .= "  FROM award LEFT JOIN stats_participation ";
		$sql .= "    ON stats_participation.yearmonth = award.yearmonth AND stats_participation.award_group = award.award_group ";
		$sql .= " WHERE award.yearmonth = '$yearmonth' ";
		$sql .= " GROUP BY award.award_group, stat_category ";
		$sql .= " HAVING num_stats = 0 ";
		$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
			$stats_missing[] = "Missing statistics for category " . $row['stat_category'] . " for " . $row['award_group'];
		}
		// Stats Bits
		$sql = "SELECT COUNT(*) AS num_bits FROM stats_bits WHERE yearmonth = '$yearmonth' ";
		$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		$row = mysqli_fetch_array($query);
		if ($row['num_bits'] == 0) {
			$stats_missing[] = "Statsistical Bits missing";
		}
	}

	// Load sharedef.json
	$page = [];
	$blocks = [];
	$fields = [];

	if (file_exists("../salons/$yearmonth/blob/sharedef.json"))
		$sharedef_json = file_get_contents("../salons/$yearmonth/blob/sharedef.json");
	elseif (file_exists("template/blob/sharedef.json"))
		$sharedef_json = file_get_contents("template/blob/sharedef.json");

	$sharedef = json_decode($sharedef_json, true);
	if (json_last_error() != JSON_ERROR_NONE)
		$_SESSION['err_msg'] = "Share Image Definition garbled";

	if (isset($sharedef["page"]))
		$page = $sharedef["page"];

	if (isset($sharedef["blocks"]))
		$blocks = $sharedef["blocks"];

	if (isset($sharedef["fields"]))
		$fields = $sharedef["fields"];

	if (sizeof($page) == 0 || sizeof($blocks) == 0 || sizeof($fields) == 0)
		$_SESSION['err_msg'] = "Page Definition corrupted";

?>

<!DOCTYPE html>
<html>

<head>
    <!-- Page title -->
	<title>Youth Photographic Society | Salon Management</title>

	<?php include "inc/header.php"; ?>

    <link rel="stylesheet" href="plugin/datatable/css/dataTables.bootstrap.min.css" />
	<link rel="stylesheet" href="plugin/bootstrap-colorpicker/css/bootstrap-colorpicker.css" >
	<link rel="stylesheet" href="plugin/select2/css/select2.min.css" />
	<link rel="stylesheet" href="plugin/select2/css/select2-bootstrap.min.css" />

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
						<?= $yearmonth == 0 ? "Select a Salon" : "Details of Results for " . $salon['contest_name'];?>
					</h3>
					<br>
					<form role="form" method="post" id="select-salon-form" name="select-salon-form" action="publish_results.php" enctype="multipart/form-data" >
						<div class="row form-group">
							<div class="col-sm-8">
								<label for="yearmonth">Select Salon</label>
								<div class="input-group">
									<select class="form-control" name="yearmonth" id="select-yearmonth" value="<?= $yearmonth;?>">
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
									<span class="input-group-btn">
										<button type="submit" class="btn btn-info pull-right" name="edit-contest-button" id="edit-contest-button" ><i class="fa fa-edit"></i> EDIT </a>
									</span>
								</div>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>


		<div class="content">
			<?php
				if ($yearmonth != 0) {
			?>
			<form role="form" method="post" id="edit_contest_form" name="edit_contest_form" action="#" enctype="multipart/form-data" >
				<!-- Hidden non-edited fields -->
				<input type="hidden" name="yearmonth" id="yearmonth" value="<?= $yearmonth;?>" >

				<!-- Warnings -->
				<?php
					if (sizeof($results_missing) > 0 || sizeof($stats_missing) > 0) {
				?>
				<div class="row">
					<div class="col-sm-12">
					<?php
						if (sizeof($results_missing) > 0) {
					?>
						<p class="text-danger"><b>Results for some awards have not been declared</b></p>
						<ul>
					<?php
							foreach($results_missing AS $award_id => $missing) {
					?>
							<li><?= $missing['section'];?> : <?= $missing['award_name'];?> - Only <?= $missing['awarded'];?> of <?= $missing['number_of_awards'];?> awards has results</li>
					<?php
							}
					?>
						</ul>
					<?php
						}
					?>
					<?php
						if (sizeof($stats_missing) > 0) {
					?>
							<p class="text-danger"><b>Statistics is not fully compiled</b></p>
							<ul>
					<?php
							foreach($stats_missing as $missing) {
					?>
								<li><?= $missing;?></li>
					<?php
							}
					?>
							</ul>
					<?php
						}
					?>
					</div>
				</div>
				<?php
					}
				?>
				<!-- Edited Fields -->
				<!-- Judging & Results dates -->
				<div class="row form-group">
					<div class="col-sm-3">
						<label for="results_date">Results Date</label>
						<input type="date" name="results_date" class="form-control" id="results_date" value="<?= $salon['results_date'];?>" >
					</div>
					<div class="col-sm-3">
						<label for="update_start_date">Update Start Date</label>
						<input type="date" name="update_start_date" class="form-control" id="update_start_date" value="<?= $salon['update_start_date'];?>" >
					</div>
					<div class="col-sm-3">
						<label for="update_end_date">Update End Date</label>
						<input type="date" name="update_end_date" class="form-control" id="update_end_date" value="<?= $salon['update_end_date'];?>" >
					</div>
					<div class="col-sm-3">
						<label>Results Ready to Publish ?</label>
						<div class="input-group">
							<span class="input-group-addon">
								<input type="checkbox" name="results_ready" id="results_ready" value="1" <?= $salon['results_ready'] == "0" ? "" : "checked";?> >
							</span>
							<input type="text" class="form-control" readonly value="Results are ready" >
						</div>
					</div>
				</div>

				<!-- Section Cut-off-marks -->
				<div class="row form-group">
					<h4 class="text-info">Update Cut-off scores</h4>
				<?php
					$sql  = "SELECT section.section, stub, cut_off_score, COUNT(user_id) AS num_jury ";
					$sql .= "  FROM section, assignment ";
					$sql .= " WHERE section.yearmonth = '$yearmonth' ";
					$sql .= "   AND assignment.yearmonth = section.yearmonth ";
					$sql .= "   AND assignment.section = section.section ";
					$sql .= " GROUP BY section.section ";
					$sql .= " ORDER BY section_sequence ";
					$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
					while ($section = mysqli_fetch_array($query)) {
						$section_name = $section['section'];
						$field_name = "cut_off[" . $section['section'] . "]";
						$field_id = "cut_off_" . $section['stub'];
						$cut_off = $section['cut_off_score'];
						$num_jury = $section['num_jury'];
				?>
					<div class="col-sm-2">
						<label for="cut_off"><?= $section_name;?></label>
						<input type="number" class="form-control" min="<?= $num_jury;?>" max="<?= $num_jury * 5;?>"
								name="<?= $field_name;?>" id="<?= $field_id;?>" value="<?= $cut_off;?>" >
					</div>
				<?php
					}
				?>
					<div class="clearfix"></div>
					<div class="col-sm-12">
						<br>
						<span class="text-danger"><i class="fa fa-warning"></i>
							After updating section cut-offs <b>run Statistics</b> once again from Admin panel before publishing results.
						</span>
					</div>
				</div>

				<!-- Salon Blobs -->
				<div class="row form-group">
					<div class="col-sm-4">
						<label for="judging_report_blob">Judging Report</label>
						<div class="input-group">
							<input type="text" class="form-control" name="judging_report_blob" readonly value="<?= $salon['judging_report_blob'];?>" >
							<span class="input-group-btn">
								<a class="btn btn-info pull-right edit-blob"
									data-yearmonth="<?= $yearmonth;?>"
									data-blob-type="judging_report"
									data-blob="<?= $salon['judging_report_blob'];?>" ><i class="fa fa-edit"></i> EDIT</a>
							</span>
						</div>
					</div>
					<div class="col-sm-4">
						<label for="judging_description_blob">Results Description</label>
						<div class="input-group">
							<input type="text" class="form-control" name="results_description_blob" readonly value="<?= $salon['results_description_blob'];?>" >
							<span class="input-group-btn">
								<a class="btn btn-info pull-right edit-blob"
									data-yearmonth="<?= $yearmonth;?>"
									data-blob-type="results_description"
									data-blob="<?= $salon['results_description_blob'];?>" ><i class="fa fa-edit"></i> EDIT</a>
							</span>
						</div>
					</div>
					<div class="col-sm-4">
						<label for="chairman_message_blob">Chairman Message</label>
						<div class="input-group">
							<input type="text" class="form-control" name="chairman_message_blob" readonly value="<?= $salon['chairman_message_blob'];?>" >
							<span class="input-group-btn">
								<a class="btn btn-info pull-right edit-blob"
									data-yearmonth="<?= $yearmonth;?>"
									data-blob-type="chairman_message"
									data-blob="<?= $salon['chairman_message_blob'];?>" ><i class="fa fa-edit"></i> EDIT</a>
							</span>
						</div>
					</div>
				</div>

				<!-- Share Image Definition -->
				<!-- Overall Page -->
				<h4 class="text-info">Share Page Definition</h4>
				<p style="color: #aaa;">Sizes and Positions are in pixels. Font sizes are in points.</p>
				<div class="row form-group">
					<div class="col-sm-2">
						<label for="page-width">Width</label>
						<input type="number" class="form-control" name="page-width" value="<?= isset($page['width']) ? $page['width'] : 1080;?>" >
					</div>
					<div class="col-sm-2">
						<label for="page-height">Height</label>
						<input type="number" class="form-control" name="page-height" value="<?= isset($page['height']) ? $page['height'] : 1080;?>" >
					</div>
					<div class="col-sm-4">
						<label for="gap">Gap between thumbnails</label>
						<input type="number" class="form-control" name="gap" value="<?= isset($page['gap']) ? $page['gap'] : 2;?>" >
					</div>
				</div>

				<!-- Structure -->
				<div class="row">
					<!-- Thumbnail Area -->
					<div class="col-sm-2" style="border: solid 1px #aaa;">
						<div class="row">
							<div class="col-sm-12" style="text-align: center; background-color: #ddd; border: solid 1px #888;">
								<span class="text-info"><big><b>THUMBNAIL</b></big></span>
							</div>
						</div>
						<div class="row">
							<div class="col-sm-4"><label>X</label></div>
							<div class="col-sm-8">
								<input type="number" class="form-control" name="thumbnails-x" value="<?= isset($blocks['thumbnails']['x']) ? $blocks['thumbnails']['x'] : 15;?>" >
							</div>
						</div>
						<div class="row">
							<div class="col-sm-4"><label>Y</label></div>
							<div class="col-sm-8">
								<input type="number" class="form-control" name="thumbnails-y" value="<?= isset($blocks['thumbnails']['x']) ? $blocks['thumbnails']['y'] : 15;?>" >
							</div>
						</div>
						<div class="row">
							<div class="col-sm-4"><label>WIDTH</label></div>
							<div class="col-sm-8">
								<input type="number" class="form-control" name="thumbnails-width" value="<?= isset($blocks['thumbnails']['width']) ? $blocks['thumbnails']['width'] : 1050;?>" >
							</div>
						</div>
						<div class="row">
							<div class="col-sm-4"><label>HEIGHT</label></div>
							<div class="col-sm-8">
								<input type="number" class="form-control" name="thumbnails-height" value="<?= isset($blocks['thumbnails']['height']) ? $blocks['thumbnails']['height'] : 780;?>" >
							</div>
						</div>
					</div>

					<!-- Profile Area -->
					<div class="col-sm-5" style="border: solid 1px #aaa;">
						<div class="row">
							<div class="col-sm-12" style="text-align: center; background-color: #ddd; border: solid 1px #888;">
								<span class="text-info" ><big><b>PROFILE</b></big></span>
							</div>
						</div>
						<div class="row">
							<div class="col-sm-2"><label>X</label></div>
							<div class="col-sm-4">
								<input type="number" class="form-control" name="profile-x" value="<?= isset($blocks['profile']['x']) ? $blocks['profile']['x'] : 15;?>" >
							</div>
							<div class="col-sm-2"><label>Y</label></div>
							<div class="col-sm-4">
								<input type="number" class="form-control" name="profile-y" value="<?= isset($blocks['profile']['x']) ? $blocks['profile']['y'] : 950;?>" >
							</div>
						</div>
						<div class="row" style="border-bottom: solid 1px #888">
							<div class="col-sm-2"><label>WIDTH</label></div>
							<div class="col-sm-4">
								<input type="number" class="form-control" name="profile-width" value="<?= isset($blocks['profile']['width']) ? $blocks['profile']['width'] : 550;?>" >
							</div>
							<div class="col-sm-2"><label>HEIGHT</label></div>
							<div class="col-sm-4">
								<input type="number" class="form-control" name="profile-height" value="<?= isset($blocks['profile']['height']) ? $blocks['profile']['height'] : 120;?>" >
							</div>
						</div>
						<div class="row">
							<div class="col-sm-2"></div>
							<div class="col-sm-5" style="border: solid 1px #888; text-align: center;"><label>Name</label></div>
							<div class="col-sm-5" style="border: solid 1px #888; text-align: center;"><label>Honors</label></div>
						</div>
						<div class="row">
							<div class="col-sm-2"><label>Font</label></div>
							<div class="col-sm-5">
								<select class="form-control font-name" name="name-font" id="name-font" value="<?= isset($fields['name']['font']) ? $fields['name']['font'] : "PetitaBold.ttf";?>">
								<?= font_options_list(isset($fields['name']['font']) ? $fields['name']['font'] : "PetitaBold.ttf"); ?>
								</select>
							</div>
							<div class="col-sm-5">
								<select class="form-control font-name" name="honors-font" id="honors-font" value="<?= isset($fields['honors']['font']) ? $fields['honors']['font'] : "PetitaMedium.ttf";?>">
								<?= font_options_list(isset($fields['honors']['font']) ? $fields['honors']['font'] : "PetitaMedium.ttf"); ?>
								</select>
							</div>
						</div>
						<div class="row">
							<div class="col-sm-2"><label>Size</label></div>
							<div class="col-sm-5">
								<input type="number" class="form-control" name="name-font-size" value="<?= isset($fields['name']['font_size']) ? $fields['name']['font_size'] : 36;?>" >
							</div>
							<div class="col-sm-5">
								<input type="number" class="form-control" name="honors-font-size" value="<?= isset($fields['honors']['font_size']) ? $fields['honors']['font_size'] : 16;?>" >
							</div>
						</div>
						<div class="row">
							<div class="col-sm-2"><label>Color</label></div>
							<div class="col-sm-5">
								<div id="name-color" class="input-group color-picker-component">
									<input type="type" class="form-control" name="name-color" value="#<?= isset($fields['name']['color']) ? $fields['name']['color'] : "000000";?>" >
									<span class="input-group-addon"><i></i></span>
								</div>
							</div>
							<div class="col-sm-5">
								<div id="honors-color" class="input-group color-picker-component">
									<input type="type" class="form-control" name="honors-color" value="#<?= isset($fields['honors']['color']) ? $fields['honors']['color'] : "000000";?>" >
									<span class="input-group-addon"><i></i></span>
								</div>
							</div>
						</div>
					</div>

					<!-- Wins Area -->
					<div class="col-sm-5" style="border: solid 1px #aaa;">
						<div class="row">
							<div class="col-sm-12" style="text-align: center; background-color: #ddd; border: solid 1px #888;">
								<span class="text-info"><big><b>WINS</b></big></span>
							</div>
						</div>
						<div class="row">
							<div class="col-sm-2"><label>X</label></div>
							<div class="col-sm-4">
								<input type="number" class="form-control" name="wins-x" value="<?= isset($blocks['wins']['x']) ? $blocks['wins']['x'] : 40;?>" >
							</div>
							<div class="col-sm-2"><label>Y</label></div>
							<div class="col-sm-4">
								<input type="number" class="form-control" name="wins-y" value="<?= isset($blocks['wins']['y']) ? $blocks['wins']['y'] : 420;?>" >
							</div>
						</div>
						<div class="row" style="border-bottom: solid 1px #888">
							<div class="col-sm-2"><label>WIDTH</label></div>
							<div class="col-sm-4">
								<input type="number" class="form-control" name="wins-width" value="<?= isset($blocks['wins']['width']) ? $blocks['wins']['width'] : 1000;?>" >
							</div>
							<div class="col-sm-2"><label>HEIGHT</label></div>
							<div class="col-sm-4">
								<input type="number" class="form-control" name="wins-height" value="<?= isset($blocks['wins']['height']) ? $blocks['wins']['height'] : 200;?>" >
							</div>
						</div>
						<div class="row">
							<div class="col-sm-2"></div>
							<div class="col-sm-5" style="border: solid 1px #888; text-align: center;"><label>Picture Awards</label></div>
							<div class="col-sm-5" style="border: solid 1px #888; text-align: center;"><label>Other Awards</label></div>
						</div>
						<div class="row">
							<div class="col-sm-2"><label>Font</label></div>
							<div class="col-sm-5">
								<select class="form-control font-name" name="picwins-font" id="picwins-font" value="<?= isset($fields['pic_wins']['font']) ? $fields['pic_wins']['font'] : "PetitaBold.ttf";?>">
								<?= font_options_list(isset($fields['pic_wins']['font']) ? $fields['pic_wins']['font'] : "PetitaBold.ttf"); ?>
								</select>
							</div>
							<div class="col-sm-5">
								<select class="form-control font-name" name="splwins-font" id="splwins-font" value="<?= isset($fields['spl_wins']['font']) ? $fields['spl_wins']['font'] : "PetitaBold.ttf";?>">
								<?= font_options_list(isset($fields['spl_wins']['font']) ? $fields['spl_wins']['font'] : "PetitaBold.ttf"); ?>
								</select>
							</div>
						</div>
						<div class="row">
							<div class="col-sm-2"><label>Size</label></div>
							<div class="col-sm-5">
								<input type="number" class="form-control" name="picwins-font-size" value="<?= isset($fields['pic_wins']['font_size']) ? $fields['pic_wins']['font_size'] : 48;?>" >
							</div>
							<div class="col-sm-5">
								<input type="number" class="form-control" name="splwins-font-size" value="<?= isset($fields['spl_wins']['font_size']) ? $fields['spl_wins']['font_size'] : 48;?>" >
							</div>
						</div>
						<div class="row">
							<div class="col-sm-2"><label>Color</label></div>
							<div class="col-sm-5">
								<div id="picwins-color" class="input-group color-picker-component">
									<input type="type" class="form-control" name="picwins-color" value="#<?= isset($fields['pic_wins']['color']) ? $fields['pic_wins']['color'] : "FFFFFF";?>" >
									<span class="input-group-addon"><i></i></span>
								</div>
							</div>
							<div class="col-sm-5">
								<div id="splwins-color" class="input-group color-picker-component">
									<input type="type" class="form-control" name="splwins-color" value="#<?= isset($fields['spl_wins']['color']) ? $fields['spl_wins']['color'] : "FFFFFF";?>" >
									<span class="input-group-addon"><i></i></span>
								</div>
							</div>
						</div>
					</div>
				</div>


				<!-- share image templates -->
				<h4 class="text-info">Files Required</h4>
				<div class="row">
					<!-- Winner Poster -->
					<?php
						$participant_poster = "/salons/$yearmonth/img/participant_poster.png";
						if ( ! file_exists(".." . $participant_poster))
							$participant_poster = "/img/preview.png";
						$winner_poster = "/salons/$yearmonth/img/winner_poster.png";
						if ( ! file_exists(".." . $winner_poster))
							$winner_poster = "/img/preview.png";
						$results_banner = "/salons/$yearmonth/img/results_banner.jpg";
						if ( ! file_exists(".." . $results_banner))
							$results_banner = "/img/preview.png";
						$share_ql = "/salons/$yearmonth/img/share-results.jpg";
						if ( ! file_exists(".." . $share_ql))
							$share_ql = "/img/preview.png";
						$download_ql = "/salons/$yearmonth/img/download-scorecard.jpg";
						if ( ! file_exists(".." . $download_ql))
							$download_ql = "/img/preview.png";
					?>
					<div class="col-sm-4" style="padding: 4px; border : 1px solid #aaa; border-radius : 8px;">
						<div class="row">
							<div class="col-sm-4 form-group">
								<img id="winner-poster-disp" src="<?= $winner_poster;?>" style="width: 100%;" >
							</div>
							<div class="col-sm-8">
								<label>Upload Winner Poster Template</label>
								<input type="file" name="winner_poster" id="winner-poster" class="form-control" ><br>
								<button id="upload-winner-poster" class="btn btn-info pull-right" >
									<i class="fa fa-upload"></i> Upload
								</button>
							</div>
						</div>
					</div>
					<!-- Proud Participant Poster -->
					<div class="col-sm-4" style="padding: 4px; border : 1px solid #aaa; border-radius : 8px;">
						<div class="row">
							<div class="col-sm-4 form-group">
								<img id="participant-poster-disp" src="<?= $participant_poster;?>" style="width: 100%;" >
							</div>
							<div class="col-sm-8">
								<label>Upload Proud Participant Poster Template</label>
								<input type="file" name="participant_poster" id="participant-poster" class="form-control" ><br>
								<button id="upload-participant-poster" class="btn btn-info pull-right" >
									<i class="fa fa-upload"></i> Upload
								</button>
							</div>
						</div>
					</div>
					<!-- Results Banner -->
					<div class="col-sm-4" style="padding: 4px; border : 1px solid #aaa; border-radius : 8px;">
						<div class="row">
							<div class="col-sm-4 form-group">
								<img id="results-banner-disp" src="<?= $results_banner;?>" style="width: 100%;" >
							</div>
							<div class="col-sm-8">
								<label>Upload Results Banner</label>
								<input type="file" name="results_banner" id="results-banner" class="form-control" ><br>
								<button id="upload-results-banner" class="btn btn-info pull-right" >
									<i class="fa fa-upload"></i> Upload
								</button>
							</div>
						</div>
					</div>
					<div class="clearfix"></div>
					<!-- Share Results Quick Link -->
					<div class="col-sm-4" style="padding: 4px; border : 1px solid #aaa; border-radius : 8px;">
						<div class="row">
							<div class="col-sm-4 form-group">
								<img id="share-ql-disp" src="<?= $share_ql;?>" style="width: 100%;" >
							</div>
							<div class="col-sm-8">
								<label>Upload Share Results Quick Link Graphics</label>
								<input type="file" name="share_ql" id="share-ql" class="form-control" ><br>
								<button id="upload-share-ql" class="btn btn-info pull-right" >
									<i class="fa fa-upload"></i> Upload
								</button>
							</div>
						</div>
					</div>
					<!-- Download Scorecard Quick Link -->
					<div class="col-sm-4" style="padding: 4px; border : 1px solid #aaa; border-radius : 8px;">
						<div class="row">
							<div class="col-sm-4 form-group">
								<img id="download-ql-disp" src="<?= $download_ql;?>" style="width: 100%;" >
							</div>
							<div class="col-sm-8">
								<label>Upload Download Scorecard Quick Link Graphics</label>
								<input type="file" name="download_ql" id="download-ql" class="form-control" ><br>
								<button id="upload-download-ql" class="btn btn-info pull-right" >
									<i class="fa fa-upload"></i> Upload
								</button>
							</div>
						</div>
					</div>
				</div>


				<!-- Update -->
				<br><br>
				<div class="row form-group">
					<div class="col-sm-9">
						<input class="btn btn-primary pull-right" type="submit" id="publish-results" name="publish_results" value="Update">
					</div>
				</div>
			</form>
			<?php
				}
			?>
		</div>

		<!-- MODAL Forms -->
		<?php include("inc/blob_modal_html.php");?>
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

<!-- Color Picker -->
<script src="plugin/bootstrap-colorpicker/js/bootstrap-colorpicker.js"></script>

<?php include("inc/blob_modal_script.php");?>

<!-- Action Handlers -->
<script>
	// Global variables to save judging venue details

	$(document).ready(function(){
		// Hide Form till a salon is loaded
		if($("#yearmonth").val() == 0)
			$(".content").hide();

		// Attach Color Picker
        $('#name-color').colorpicker();
		$('#honors-color').colorpicker();
		$('#picwins-color').colorpicker();
		$('#splwins-color').colorpicker();

		$(".font-name").select2();
	});
</script>

<!-- Custom Validation Functions -->
<script>
jQuery.validator.addMethod(
	"yearmonth",
	function(value, element, param) {
		let year = value.substr(0, 4);
		let month = value.substr(4);
		if (year >= "1980" && year <= "2099" && month >= "01" && month <= "12")
			return true;
		else
			return this.optional(element);
	},
	"Must have valid value in YYYYMM format"
);

</script>

<!-- Image Handlers -->
<script>
	// Get Description Text from server
	$(document).ready(function(){

		// Load pictures into view
		$("#winner-poster").on("change", function(){
			let input = $(this).get(0);
			if (input.files && input.files.length > 0 && input.files[0] != "") {
				// Phase 1 Check Size & Dimensions
				// var file_size = input.files[0].size;
				var reader = new FileReader();
				// Handler for file read completion
				reader.onload = function (e) {
					$("#winner-poster-disp").attr("src", e.target.result);
				}
				// Handler for File Read Error
				reader.onerror = function(e) {
					alert("Unable to open selected picture");
				}

				// Perform File Read
				reader.readAsDataURL(input.files[0]);

			}
		});

		$("#participant-poster").on("change", function(){
			let input = $(this).get(0);
			if (input.files && input.files.length > 0 && input.files[0] != "") {
				// Phase 1 Check Size & Dimensions
				// var file_size = input.files[0].size;
				var reader = new FileReader();
				// Handler for file read completion
				reader.onload = function (e) {
					$("#participant-poster-disp").attr("src", e.target.result);
				}
				// Handler for File Read Error
				reader.onerror = function(e) {
					alert("Unable to open selected picture");
				}

				// Perform File Read
				reader.readAsDataURL(input.files[0]);

			}
		});

		$("#results-banner").on("change", function(){
			let input = $(this).get(0);
			if (input.files && input.files.length > 0 && input.files[0] != "") {
				// Phase 1 Check Size & Dimensions
				// var file_size = input.files[0].size;
				var reader = new FileReader();
				// Handler for file read completion
				reader.onload = function (e) {
					$("#results-banner-disp").attr("src", e.target.result);
				}
				// Handler for File Read Error
				reader.onerror = function(e) {
					alert("Unable to open selected picture");
				}

				// Perform File Read
				reader.readAsDataURL(input.files[0]);

			}
		});

		$("#download-ql").on("change", function(){
			let input = $(this).get(0);
			if (input.files && input.files.length > 0 && input.files[0] != "") {
				// Phase 1 Check Size & Dimensions
				// var file_size = input.files[0].size;
				var reader = new FileReader();
				// Handler for file read completion
				reader.onload = function (e) {
					$("#download-ql-disp").attr("src", e.target.result);
				}
				// Handler for File Read Error
				reader.onerror = function(e) {
					alert("Unable to open selected picture");
				}

				// Perform File Read
				reader.readAsDataURL(input.files[0]);

			}
		});

		$("#download-ql").on("change", function(){
			let input = $(this).get(0);
			if (input.files && input.files.length > 0 && input.files[0] != "") {
				// Phase 1 Check Size & Dimensions
				// var file_size = input.files[0].size;
				var reader = new FileReader();
				// Handler for file read completion
				reader.onload = function (e) {
					$("#download-ql-disp").attr("src", e.target.result);
				}
				// Handler for File Read Error
				reader.onerror = function(e) {
					alert("Unable to open selected picture");
				}

				// Perform File Read
				reader.readAsDataURL(input.files[0]);

			}
		});

		// Ajax Upload images
		$("#upload-winner-poster").click(function(e){
			e.preventDefault();
			if ($("#winner-poster").val() == "") {
				swal("Select a Winner Poster Image !", "Please select a Winner Poster image to upload.", "warning");
			}
			else {
				let yearmonth = "<?= $yearmonth;?>";
				let file_name = "winner_poster.png";
				let file = $("#winner-poster")[0].files[0];
				let stub = $("#winner-poster").attr("name");
				let formData = new FormData();
				formData.append("yearmonth", yearmonth);
				formData.append("file_name", file_name);
				formData.append(stub, file);

				$('#loader_img').show();
				$.ajax({
						url: "ajax/upload_salon_img.php",
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
										title: "Image Saved",
										text: "Image has been uploaded and saved.",
										icon: "success",
										confirmButtonClass: 'btn-success',
										confirmButtonText: 'Great'
								});
							}
							else{
								swal({
										title: "Upload Failed",
										text: "Uploaded image could not be saved: " + response.msg,
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
			}
		});		// upload image click

		$("#upload-participant-poster").click(function(e){
			e.preventDefault();
			if ($("#participant-poster").val() == "") {
				swal("Select a Participant Poster Image !", "Please select a Participant Poster image to upload.", "warning");
			}
			else {
				let yearmonth = "<?= $yearmonth;?>";
				let file_name = "participant_poster.png";
				let file = $("#participant-poster")[0].files[0];
				let stub = $("#participant-poster").attr("name");
				let formData = new FormData();
				formData.append("yearmonth", yearmonth);
				formData.append("file_name", file_name);
				formData.append(stub, file);

				$('#loader_img').show();
				$.ajax({
						url: "ajax/upload_salon_img.php",
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
										title: "Image Saved",
										text: "Image has been uploaded and saved.",
										icon: "success",
										confirmButtonClass: 'btn-success',
										confirmButtonText: 'Great'
								});
							}
							else{
								swal({
										title: "Upload Failed",
										text: "Uploaded image could not be saved: " + response.msg,
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
			}
		});		// upload image click

		$("#upload-results-banner").click(function(e){
			e.preventDefault();
			if ($("#results-banner").val() == "") {
				swal("Select a Results Banner Image !", "Please select a Results Banner image to upload.", "warning");
			}
			else {
				let yearmonth = "<?= $yearmonth;?>";
				let file_name = "results_banner.jpg";
				let file = $("#results-banner")[0].files[0];
				let stub = $("#results-banner").attr("name");
				let formData = new FormData();
				formData.append("yearmonth", yearmonth);
				formData.append("file_name", file_name);
				formData.append(stub, file);

				$('#loader_img').show();
				$.ajax({
						url: "ajax/upload_salon_img.php",
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
										title: "Image Saved",
										text: "Image has been uploaded and saved.",
										icon: "success",
										confirmButtonClass: 'btn-success',
										confirmButtonText: 'Great'
								});
							}
							else{
								swal({
										title: "Upload Failed",
										text: "Uploaded image could not be saved: " + response.msg,
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
			}
		});		// upload image click

		$("#upload-share-ql").click(function(e){
			e.preventDefault();
			if ($("#share-ql").val() == "") {
				swal("Select an Image !", "Please select a JPEG image to be used as Share Results Quick Link.", "warning");
			}
			else {
				let yearmonth = "<?= $yearmonth;?>";
				let file_name = "share-results.jpg";
				let file = $("#share-ql")[0].files[0];
				let stub = $("#share-ql").attr("name");
				let formData = new FormData();
				formData.append("yearmonth", yearmonth);
				formData.append("file_name", file_name);
				formData.append(stub, file);

				$('#loader_img').show();
				$.ajax({
						url: "ajax/upload_salon_img.php",
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
										title: "Image Saved",
										text: "Image has been uploaded and saved.",
										icon: "success",
										confirmButtonClass: 'btn-success',
										confirmButtonText: 'Great'
								});
							}
							else{
								swal({
										title: "Upload Failed",
										text: "Uploaded image could not be saved: " + response.msg,
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
			}
		});		// upload image click

		$("#upload-download-ql").click(function(e){
			e.preventDefault();
			if ($("#download-ql").val() == "") {
				swal("Select an Image !", "Please select a JPEG image to be used as Download Scorecard Quick Link.", "warning");
			}
			else {
				let yearmonth = "<?= $yearmonth;?>";
				let file_name = "download-scorecard.jpg";
				let file = $("#download-ql")[0].files[0];
				let stub = $("#download-ql").attr("name");
				let formData = new FormData();
				formData.append("yearmonth", yearmonth);
				formData.append("file_name", file_name);
				formData.append(stub, file);

				$('#loader_img').show();
				$.ajax({
						url: "ajax/upload_salon_img.php",
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
										title: "Image Saved",
										text: "Image has been uploaded and saved.",
										icon: "success",
										confirmButtonClass: 'btn-success',
										confirmButtonText: 'Great'
								});
							}
							else{
								swal({
										title: "Upload Failed",
										text: "Uploaded image could not be saved: " + response.msg,
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
			}
		});		// upload image click

	});		// document.ready

</script>


<!-- Edit Contest -->
<script>
	$(document).ready(function(){
		let vaidator = $('#edit_contest_form').validate({
			rules:{
				results_date : {
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
				// Assemble Data
				var formData = new FormData(form);

				$('#loader_img').show();
				$.ajax({
						url: "ajax/publish_results.php",
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
										text: "Contest Results have been published successfully.",
										icon: "success",
										confirmButtonClass: 'btn-success',
										confirmButtonText: 'Great'
								});
								$("#edit-contest-modal").modal('hide');
							}
							else{
								swal({
										title: "Save Failed",
										text: "Contest Results could not be published: " + response.msg,
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
