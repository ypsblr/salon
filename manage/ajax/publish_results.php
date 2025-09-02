<?php
include("../inc/session.php");
include "../inc/connect.php";
include_once("ajax_lib.php");

function null_safe_date($date) {
	if ($date == NULL || $date == "")
		return "NULL";
	else
		return "'" . $date . "'";
}

function sort_on_price($catalog_list) {
	if (sizeof($catalog_list) <= 1)
		return $catalog_list;		// Nothing to sort

	// Do Bubble Sort on Price till no records are swapped
	do {
		$swaps = 0;
		for ($i = 1; $i < sizeof($catalog_list); ++ $i) {
			if ($catalog_list[$i]["price"] < $catalog_list[$i-1]["price"]) {
				$temp = $catalog_list[$i];
				$catalog_list[$i] = $catalog_list[$i-1];
				$catalog_list[$i-1] = $temp;
				++ $swaps;
			}
		}
	} while ($swaps > 0);

	return $catalog_list;
}


// Main Code
if ( isset($_SESSION['admin_id']) && isset($_REQUEST['yearmonth']) && isset($_REQUEST['publish_results']) ) {

	// Assemble Data
	$yearmonth = $_REQUEST['yearmonth'];
	$sql = "SELECT * FROM contest WHERE yearmonth = '$yearmonth' ";
	$query = mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_num_rows($query) == 0)
		return_error("There is no Salon for $yearmonth ", __FILE__, __LINE__, true);

	// Assemble values
	$results_date = null_safe_date($_REQUEST['results_date']);
	if (isset($_REQUEST['results_ready'])){
		$results_ready = '1';
		$review_in_progress = '0';
		$judging_in_progress = '0';
	}
	else {
		$results_ready = '0';
		$review_in_progress = '1';
		$judging_in_progress = '1';
	}
	$update_start_date = null_safe_date($_REQUEST['update_start_date']);
	$update_end_date = null_safe_date($_REQUEST['update_end_date']);
	$judging_report_blob = $_REQUEST['judging_report_blob'];
	$results_description_blob = $_REQUEST['results_description_blob'];
	$chairman_message_blob = $_REQUEST['chairman_message_blob'];

	// debug_dump("cut_off", $_REQUEST['cut_off'], __FILE__, __LINE__);

	// Start Transaction
	$sql = "START TRANSACTION";
	mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Insert if New
	// *** NOTE - Do not place date variables under quotes - they are already quoted by null_safe_date function
	$sql  = "UPDATE contest ";
	$sql .= "   SET results_date = $results_date ";
	$sql .= "     , results_ready = '$results_ready' ";
	$sql .= "     , review_in_progress = '$review_in_progress' ";
	$sql .= "     , judging_in_progress = '$judging_in_progress' ";
	$sql .= "     , update_start_date = $update_start_date ";
	$sql .= "     , update_end_date = $update_end_date ";
	$sql .= "     , judging_report_blob = '$judging_report_blob' ";
	$sql .= "     , results_description_blob = '$results_description_blob' ";
	$sql .= "     , chairman_message_blob = '$chairman_message_blob' ";
	$sql .= " WHERE yearmonth = '$yearmonth' ";
	mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Update Cut-off scores into section
	if (isset($_REQUEST['cut_off']) && is_array($_REQUEST['cut_off'])) {
		foreach($_REQUEST['cut_off'] as $section => $cut_off) {
			$sql  = "UPDATE section ";
			$sql .= "   SET cut_off_score = '$cut_off' ";
			$sql .= " WHERE yearmonth = '$yearmonth' ";
			$sql .= "   AND section = '$section' ";
			mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		}
	}

	$sql = "COMMIT";
	mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Create sharedef.json
	// Save current version
	if (file_exists("../../salons/$yearmonth/blob/sharedef.json"))
		rename("../../salons/$yearmonth/blob/sharedef.json", "../../salons/$yearmonth/blob/sharedef" . "_" . date("YmdHi") . ".json");

	// Assemble data
	$json_data = array(
			"page" => array("width" => $_REQUEST['page-width'], "height" => $_REQUEST['page-height'], "gap" => $_REQUEST['gap']),
			"blocks" => array(
					"thumbnails" => array(	"x" => $_REQUEST['thumbnails-x'],
											"y" => $_REQUEST['thumbnails-y'],
											"width" => $_REQUEST['thumbnails-width'],
											"height" => $_REQUEST['thumbnails-height']
										),
					"profile" => array(		"x" => $_REQUEST['profile-x'],
											"y" => $_REQUEST['profile-y'],
											"width" => $_REQUEST['profile-width'],
											"height" => $_REQUEST['profile-height']
										),
					"wins" => array(		"x" => $_REQUEST['wins-x'],
											"y" => $_REQUEST['wins-y'],
											"width" => $_REQUEST['wins-width'],
											"height" => $_REQUEST['wins-height']
										),
			),
			"fields" => array(
					"name" => array(		"font" => $_REQUEST['name-font'],
											"font_size" => $_REQUEST['name-font-size'],
											"color" => substr($_REQUEST['name-color'], 1)
					),
					"honors" => array(		"font" => $_REQUEST['honors-font'],
											"font_size" => $_REQUEST['honors-font-size'],
											"color" => substr($_REQUEST['honors-color'], 1)
					),
					"pic_wins" => array(	"font" => $_REQUEST['picwins-font'],
											"font_size" => $_REQUEST['picwins-font-size'],
											"color" => substr($_REQUEST['picwins-color'], 1)
					),
					"spl_wins" => array(	"font" => $_REQUEST['splwins-font'],
											"font_size" => $_REQUEST['splwins-font-size'],
											"color" => substr($_REQUEST['splwins-color'], 1)
					),
			)
	);
	file_put_contents("../../salons/$yearmonth/blob/sharedef.json", json_encode($json_data));

	$resArray = [];
	$resArray['success'] = TRUE;
	$resArray['msg'] = "";
	echo json_encode($resArray);
	die();

}
else
	return_error("Invalid Update Request", __FILE__, __LINE__, true);

?>
