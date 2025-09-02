<?php
include ("../inc/connect.php");
include ("ajax_lib.php");

// Strip String of special characters and space and force lower case
function strip_string($str) {
	$ret_str = "";
	$str = strtolower($str);
	for ($i = 0; $i < strlen($str); ++$i) {
		if ( ($str[$i] >= 'a' && $str[$i] <= 'z') || ($str[$i] >= '0' && $str[$i] <= '9') )
			$ret_str .= $str[$i];
	}

	return $ret_str;
}
// Compare two strings for a match after removing space and special characters and comparing equal case
function match_strings ($str1, $str2) {

	$str1 = strip_string($str1);
	$str2 = strip_string($str2);

	return ($str1 == $str2);

}
//
function match_titles($pa_title, $pic_titles) {
	if (is_array($pic_titles)) {
		foreach($pic_titles as $pic_title) {
			if (match_strings($pa_title, $pic_title))
				return true;
		}
	}

	return false;
}

function match_file_names($pa_submittedfile, $submitted_file) {
	return ( preg_match("/" . preg_quote(basename($pa_submittedfile), "/") . "/i", $submitted_file) == 1 ||
			 preg_match("/" . preg_quote(basename($submitted_file), "/") . "/i", $pa_submittedfile) == 1 );
}

function match_submittedfiles($pa_submittedfile, $submitted_files) {
	if (is_array($submitted_files)) {
		foreach($submitted_files as $submitted_file) {
			if (match_file_names($pa_submittedfile, $submitted_file))
				return true;
		}
	}

	return false;
}

function return_sql_error($sql, $errmsg, $phpfile, $phpline) {
	log_sql_error($sql, $errmsg, $phpfile, $phpline);
	echo "Error accessing data. Try again !";
	die();
}


// Get Uploads in other sections
if ( isset($_REQUEST['yearmonth']) && isset($_REQUEST['profile_id']) && isset($_REQUEST['pic_id']) && isset($_REQUEST['section']) ) {
	$jury_yearmonth = $_REQUEST['yearmonth'];
	$profile_id = $_REQUEST['profile_id'];
	$pic_id = $_REQUEST['pic_id'];
	$section = $_REQUEST['section'];
	if (isset($_REQUEST['columns'])) {
		$columns = $_REQUEST['columns'];
		switch($columns) {
			case 1 : { $col_spec = "col-sm-12"; break; }
			case 2 : { $col_spec = "col-sm-6"; break; }
			case 3 : { $col_spec = "col-sm-4"; break; }
			case 4 : { $col_spec = "col-sm-3"; break; }
			case 6 : { $col_spec = "col-sm-2"; break; }
			case 12 : { $col_spec = "col-sm-1"; break; }
			default : { $columns = 6; $col_spec = "col-sm-2"; break; }
		}
	}
	else {
		$columns = 6;
		$col_spec = "col-sm-2";
	}

	// Get Archive status
	$sql = "SELECT * FROM contest WHERE yearmonth = '$jury_yearmonth' ";
	$pa_query = mysqli_query($DBCON, $sql) or return_sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$contest = mysqli_fetch_array($pa_query);
	$contest_archived = ($contest['archived'] == '1');

	// Get the list
	$sql  = "SELECT pic.pic_id, pic.section, pic.title, pic.submittedfile, pic.picfile, pic.notifications, pic.reviewed, pic.no_accept ";
	if ($contest_archived)
		$sql .= "FROM ar_pic pic ";
	else
		$sql .= "FROM pic ";
	$sql .= "WHERE pic.yearmonth = '$jury_yearmonth' ";
	$sql .= "  AND pic.profile_id = '$profile_id' ";
	$sql .= "  AND pic.pic_id != '$pic_id' ";
	// $sql .= "  AND pic.section != '$section' ";
	$sql .= "ORDER BY pic.section ASC, pic.title ASC ";

	$pa_query = mysqli_query($DBCON, $sql) or return_sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_num_rows($pa_query) == 0)
		echo "NO PICTURES IN OTHER SECTIONS";
	else {
?>
		<div class='row' style='margin-left:10px; margin-right: 10px; margin-bottom: 10px;'>
<?php
		$index = 0;
		while ($pa_res = mysqli_fetch_array($pa_query, MYSQLI_ASSOC)) {
			$tooltip = "Uploaded under " . $pa_res['section'] . " section";
?>
			<div class='<?=$col_spec;?>' style='padding: 0px 4px;'>
				<div class='thumbnail' style='width: 100%;' id="oth-upload-pic-id-<?= $pa_res['pic_id'];?>">
					<a href="/salons/<?=$jury_yearmonth;?>/upload/<?=$pa_res['section'];?>/<?= $pa_res['picfile'];?>"
							data-lightbox="OSLB-<?=$profile_id;?>"
							data-title="<?= $tooltip;?>" >
						<img class='img-responsive' style='margin-left:auto; margin-right:auto;'
								src='/salons/<?=$jury_yearmonth;?>/upload/<?=$pa_res['section'];?>/tn/<?= $pa_res['picfile'];?>'
								data-toggle='tooltip' title='<?= $tooltip;?>' >
					</a>
				</div>
			</div>
<?php
			++ $index;
			if ($index % $columns == 0) {
?>
			<div class="clearfix"></div>
<?php
			}
		}
?>
		</div>
<?php
	}	// sizeor($accepted_pics > 0
}
else
	echo "Invalid parameters. Login again and try !";
?>
