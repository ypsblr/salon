<?php
include ("../inc/connect.php");
include ("./ajax_lib.php");

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
	return (
				preg_match("/" . strip_string(basename($pa_submittedfile)) . "/i", strip_string($submitted_file)) == 1 ||
				preg_match("/" . strip_string(basename($submitted_file)) . "/i", strip_string($pa_submittedfile)) == 1
			);
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


// Get Past Acceptances
if (isset($_REQUEST['yearmonth']) && isset($_REQUEST['profile_id']) ) {
	$jury_yearmonth = $_REQUEST['yearmonth'];
	$profile_id = $_REQUEST['profile_id'];
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

	// Getthe list for contests in non-archived tables
	$sql  = "SELECT contest.yearmonth AS yearmonth, contest_name, contest.archived, contest.web_pics, ";
	$sql .= "       pic.section AS section, pic.title, pic.submittedfile, pic.picfile, ";
	$sql .= "       award.level AS award_level, award_name, archived ";
	$sql .= "  FROM pic, pic_result, award, contest ";
	$sql .= " WHERE contest.yearmonth != '$jury_yearmonth' ";		// Other than this contest
	$sql .= "   AND pic_result.yearmonth = contest.yearmonth ";
	$sql .= "   AND pic_result.profile_id = '$profile_id' ";
	$sql .= "   AND award.yearmonth = pic_result.yearmonth ";
	$sql .= "   AND award.award_id = pic_result.award_id ";
	$sql .= "   AND award.award_type = 'pic' ";
	$sql .= "   AND pic.yearmonth = pic_result.yearmonth ";
	$sql .= "   AND pic.profile_id = pic_result.profile_id ";
	$sql .= "   AND pic.pic_id = pic_result.pic_id ";
	$sql .= "   AND award.award_group IN ( ";
	$sql .= "            SELECT entrant_category.award_group FROM entrant_category ";
	$sql .= "             WHERE entrant_category.yearmonth = award.yearmonth ";
	$sql .= "               AND entrant_category.acceptance_reported = '1' ) ";

	$sql .= " UNION ";

	$sql .= "SELECT contest.yearmonth AS yearmonth, contest_name, contest.archived, contest.web_pics, ";
	$sql .= "       pic.section AS section, pic.title, pic.submittedfile, pic.picfile, ";
	$sql .= "       award.level AS award_level, award_name, archived ";
	$sql .= "  FROM ar_pic pic, ar_pic_result pic_result, award, contest ";
	$sql .= " WHERE contest.yearmonth != '$jury_yearmonth' ";		// Other than this contest
	$sql .= "   AND pic_result.yearmonth = contest.yearmonth ";
	$sql .= "   AND pic_result.profile_id = '$profile_id' ";
	$sql .= "   AND award.yearmonth = pic_result.yearmonth ";
	$sql .= "   AND award.award_id = pic_result.award_id ";
	$sql .= "   AND award.award_type = 'pic' ";
	$sql .= "   AND pic.yearmonth = pic_result.yearmonth ";
	$sql .= "   AND pic.profile_id = pic_result.profile_id ";
	$sql .= "   AND pic.pic_id = pic_result.pic_id ";
	$sql .= "   AND award.award_group IN ( ";
	$sql .= "            SELECT entrant_category.award_group FROM entrant_category ";
	$sql .= "             WHERE entrant_category.yearmonth = award.yearmonth ";
	$sql .= "               AND entrant_category.acceptance_reported = '1' ) ";

	$sql .= "ORDER BY yearmonth DESC, section ASC, award_level ASC ";

	$pa_query = mysqli_query($DBCON, $sql)or return_sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_num_rows($pa_query) == 0)
		echo "NO PAST ACCEPTANCES";
	else {
?>
		<div class='row' style='margin-left:10px; margin-right: 10px; margin-bottom: 10px;'>
<?php
		$index = 0;
		while ($pa_res = mysqli_fetch_array($pa_query, MYSQLI_ASSOC)) {
			if ($pa_res['archived'] == '1') {
				list($pic_path, $tn_path) = explode(",", $pa_res['web_pics']);
				$pic_path .= "/";
				$tn_path .= "/";
			}
			else {
				$pic_path = "";
				$tn_path = "tn/";
			}

			// Check if picture has won award, if so give yellow background
			if ($pa_res['award_level'] == 99)
				$highlight = "";
			else
				$highlight = "background-color: #ffd700;";

			// Image Tooltip
			$tooltip = "Won '" . $pa_res['section'] . " - " . $pa_res['award_name'] . "' in " . $pa_res['contest_name'];
?>
			<div class='<?=$col_spec;?>' style='padding: 0px 4px;'>
				<div class='thumbnail' style='width: 100%; <?= $highlight;?> '>
					<a href="/salons/<?=$pa_res['yearmonth'];?>/upload/<?=$pa_res['section'];?>/<?=$pic_path . $pa_res['picfile'];?>"
							data-lightbox="PALB-<?=$profile_id;?>"
							data-title="<?= $tooltip;?>" >
						<img class='img-responsive' style='margin-left:auto; margin-right:auto;'
								src='/salons/<?=$pa_res['yearmonth'];?>/upload/<?=$pa_res['section'];?>/<?= $tn_path . $pa_res['picfile'];?>'
								data-toggle='tooltip' title="<?= $tooltip;?>" >
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
	echo "Invalid parameters !";
?>
