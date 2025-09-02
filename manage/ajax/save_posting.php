<?php
include("../inc/session.php");
include "../inc/connect.php";
// include("../inc/dindent/Indenter.php");

include_once("ajax_lib.php");


// Main Code
if( isset($_SESSION['admin_id']) && isset($_REQUEST['posting_yearmonth']) ) {

	$yearmonth = $_REQUEST['posting_yearmonth'];
	$profile_id = $_REQUEST['posting_profile_id'];
	$posting_type = $_REQUEST['posting_type'];
	$posting_operation = $_REQUEST['posting_operation'];
	$posting_date = $_REQUEST['posting_date'];
	if ($posting_type == "CASH") {
		$bank_account = $_REQUEST['bank_account'];
		$currency = $_REQUEST['posting_currency'];
		$cash_award = $_REQUEST['cash_award'];
		$tracking_no = "";
		$tracking_website = "";
	}
	else {
		$bank_account = "";
		$currency = "INR";
		$cash_award = 0;
		$tracking_no = $_REQUEST['tracking_no'];
		$tracking_website = $_REQUEST['tracking_website'];
	}
	$post_operator = $_REQUEST['post_operator'];

	if ($posting_operation == "add") {
		$sql  = "INSERT INTO postings (yearmonth, profile_id, posting_type, posting_date, bank_account, currency, cash_award, ";
		$sql .= "                      tracking_no, tracking_website, post_operator) ";
		$sql .= " VALUES('$yearmonth', '$profile_id', '$posting_type', '$posting_date', '$bank_account', '$currency', '$cash_award', ";
		$sql .= "        '$tracking_no', '$tracking_website', '$post_operator') ";
		mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		if (mysqli_affected_rows($DBCON) != 1)
			return_error("Unable to create posting data.", __FILE__, __LINE__);
	}
	else {
		$sql  = "UPDATE postings ";
		$sql .= "   SET posting_date = '$posting_date' ";
		$sql .= "     , bank_account = '$bank_account' ";
		$sql .= "     , currency = '$currency' ";
		$sql .= "     , cash_award = '$cash_award' ";
		$sql .= "     , tracking_no = '$tracking_no' ";
		$sql .= "     , tracking_website = '$tracking_website' ";
		$sql .= "     , post_operator = '$post_operator' ";
		$sql .= " WHERE yearmonth = '$yearmonth' ";
		$sql .= "   AND profile_id = '$profile_id' ";
		$sql .= "   AND posting_type = '$posting_type' ";
		mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		if (mysqli_affected_rows($DBCON) != 1)
			return_error("Unable to create posting data.", __FILE__, __LINE__);
	}

	$resArray = [];
	$resArray['success'] = TRUE;
	$resArray['msg'] = "";
	echo json_encode($resArray);
	die();
}
else
	return_error("Invalid Request", __FILE__, __LINE__, true);

?>
