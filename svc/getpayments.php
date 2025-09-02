<?php
// This service is used to generate JSON data of Awards (level < 99) or Acceptances (level = 99)
// for import into Google Sheet

include_once("../op/connect.php");

function clean($string) {
   $string = str_replace('', '-', $string); // Replaces all spaces with hyphens.
   return preg_replace('/[^a-zA-Z0-9\s]/', '', $string); // Removes special chars.
}

function json_error($errmsg) {
	$errors = array('status' => 'ERR', 'errmsg' => $errmsg);
	return json_encode(array('errors' => $errors));
}

if(isset($_GET['auth']) &&  $_GET['auth'] == "AX100RBK" ) {
	$sql  = "SELECT	entry_id, entrant_category, salutation, name, bank_account_name, bank_account_number, bank_account_type, bank_name, bank_branch, bank_ifsc_code, SUM(cash_award) AS total_award_money ";
	$sql .= "FROM acceptance ";
	$sql .= "WHERE cash_award > 0 ";
	$sql .= "GROUP BY entry_id, entrant_category, salutation, name, bank_account_name, bank_account_number, bank_account_type, bank_name, bank_branch, bank_ifsc_code ";
	$sql .= "ORDER BY name ";

	$query = mysql_query($sql) or die(json_error(mysql_error()));
	$data = array();
	while ($row = mysql_fetch_array($query, MYSQL_ASSOC)) {
		// Check if there are CONTEST Level Individual Awards for this person
		// and add any amount applicable
		$sql  = "SELECT SUM(cash_award) AS cash_award ";
		$sql .= "FROM award, result ";
		$sql .= "WHERE award.award_id = result.award_id ";
		$sql .= "  AND award.type = 'entry' ";
		$sql .= "  AND result.pic_id = '" . $row['entry_id'] . "' ";
		$res = mysql_query($sql) or die(json_error(mysql_error()));
		$rr = mysql_fetch_array($res);
		$row['total_award_money'] += $rr['cash_award'];
		
		// Add to result
		$data[] = $row;

	}
	
	echo json_encode(array($_GET['level'] => $data));
}
else
	echo json_error('Invalid Parameters');

?>
