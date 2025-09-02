<?php
// session_start();
include("../inc/session.php");
include ("../inc/connect.php");
include ("ajax_lib.php");

function return_sql_error($sql, $errmsg, $phpfile, $phpline) {
	log_sql_error($sql, $errmsg, $phpfile, $phpline);
	echo json_encode(array("results" => []));
	die();
}

$source = $_REQUEST['source'];
$search = isset($_REQUEST['search']) ? $_REQUEST['search'] : "";
$yearmonth = $_REQUEST['yearmonth'];
$club_id = (isset($_REQUEST['club_id']) ? $_REQUEST['club_id'] : -1);
$date = date("Y-m-d");

switch($source) {
	case 'club' : {
		$sql  = "SELECT club_id, club_name FROM club ";
		$sql .= " WHERE club_name LIKE '%" . $search . "%' ";
		//$sql .= "   AND club_id NOT IN (SELECT club_id FROM club_entry WHERE yearmonth = '$yearmonth' AND group_code != '' )";
		$query = mysqli_query($DBCON, $sql) or return_sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		$club_list = array();
		while ($row = mysqli_fetch_array($query)) {
			$club_list[] = array("id" => $row['club_id'], "text" => $row['club_name']);
		}
		//debug_dump("club_list", $club_list, __FILE__, __LINE__);
		echo json_encode(array("results" => $club_list));
		break;
	}
	case 'profile' : {
		$sql = "SELECT profile_id, email, profile_name FROM profile WHERE club_id = '$club_id' AND email LIKE '%" . $search . "%' ";
		$query = mysqli_query($DBCON, $sql) or return_sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		$profile_list = array();
		while ($row = mysqli_fetch_array($query)) {
			$profile_list[] = array("id" => $row['profile_id'], "text" => $row['profile_name'] . " <" . $row['email'] . ">" );
		}
		echo json_encode(array("results" => $profile_list));
		break;
	}
	case 'group_code' : {
		$entrant_category = isset($_REQUEST['entrant_category']) ? $_REQUEST['entrant_category'] : "";
		$fee_code = isset($_REQUEST['fee_code']) ? $_REQUEST['fee_code'] : "";
		$fee_model = $_REQUEST['fee_model'];
		// Find Group Code
		$sql  = "SELECT group_code, discount.currency, minimum_group_size, maximum_group_size, discount_percentage, discount_end_date ";
		$sql .= "  FROM entrant_category, discount ";
		$sql .= " WHERE entrant_category.yearmonth = '$yearmonth' ";
		$sql .= "   AND entrant_category.entrant_category = '$entrant_category' ";
		$sql .= "   AND discount.yearmonth = entrant_category.yearmonth ";
		$sql .= "   AND discount_code = 'CLUB' ";
		$sql .= "   AND discount.fee_code = '$fee_code' ";
		$sql .= "   AND discount.discount_group = entrant_category.discount_group ";
		$sql .= "   AND discount.participation_code = '$fee_model' ";
		$sql .= "   AND discount.currency = entrant_category.currency ";
		$sql .= "   AND discount_start_date <= '$date' ";
		$sql .= "   AND discount_end_date >= '$date' ";
		$sql .= " ORDER BY discount_percentage ";

		$query = mysqli_query($DBCON, $sql) or return_sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		$group_list = array();
		while ($row = mysqli_fetch_array($query)) {
			$text  = ($row['discount_percentage'] * 100) . "% " . $row['currency'];
			$text .= " [" . $row['minimum_group_size'] . " to " . $row['maximum_group_size'] . " participants]";
			$group_list[] = array("id" => $row['group_code'], "text" => $text );
		}
		echo json_encode(array("results" => $group_list));
		break;
	}
	case 'participation_code' : {
		$entrant_category = isset($_REQUEST['entrant_category']) ? $_REQUEST['entrant_category'] : "";
		$fee_code = isset($_REQUEST['fee_code']) ? $_REQUEST['fee_code'] : "";
		$sql  = "SELECT * FROM fee_structure, entrant_category ";
		$sql .= " WHERE fee_structure.yearmonth = '$yearmonth' ";
		$sql .= "   AND fee_structure.fee_code = '$fee_code' ";
		$sql .= "   AND entrant_category.yearmonth = fee_structure.yearmonth ";
		$sql .= "   AND entrant_category.entrant_category = '$entrant_category' ";
		$sql .= "   AND entrant_category.fee_group = fee_structure.fee_group ";
		$sql .= "   AND fee_structure.fee_start_date <= '$date' ";
		$sql .= "   AND fee_structure.fee_end_date >= '$date' ";

		$query = mysqli_query($DBCON, $sql) or return_sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		$participation_code_list = array();
		while ($row = mysqli_fetch_array($query)) {
			$participation_code_list[] = array("id" => $row['participation_code'], "text" => $row['currency'] . " " . $row['description'] );
		}
		echo json_encode(array("results" => $participation_code_list));
		break;
	}
	default : {
		echo json_encode(array("results" => []));
		break;

	}
}
?>
