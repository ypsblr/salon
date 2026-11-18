<?php
register_shutdown_function(function () {
	$err = error_get_last();
	if (!$err)
		return;
	$fatal = array(E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR);
	if (!in_array($err['type'], $fatal, true))
		return;
	$dir = $_SERVER["DOCUMENT_ROOT"] . "/logs";
	if (!is_dir($dir))
		@mkdir($dir, 0755, true);
	@file_put_contents(
		$dir . "/debug.txt",
		date("Y-m-d H:i:s") . " clone_salon FATAL: " . $err['message'] . " in " . $err['file'] . ":" . $err['line'] . "\r\n",
		FILE_APPEND
	);
});

include("../inc/session.php");
include "../inc/connect.php";
include_once("ajax_lib.php");

function clone_salon_log($message, $extra = null) {
	$dir = $_SERVER["DOCUMENT_ROOT"] . "/logs";
	if (!is_dir($dir))
		@mkdir($dir, 0755, true);
	$line = date("Y-m-d H:i:s") . " clone_salon: " . $message . "\r\n";
	if ($extra !== null)
		$line .= print_r($extra, true) . "\r\n";
	@file_put_contents($dir . "/debug.txt", $line, FILE_APPEND);
}

function clone_salon_fail($step, $sql, $errmsg) {
	clone_salon_log("FAILED at $step: $errmsg", $sql);
	global $DBCON;
	if (isset($DBCON))
		@mysqli_query($DBCON, "ROLLBACK");
	sql_json_error($sql, $step . ": " . $errmsg, __FILE__, __LINE__);
}

function clone_salon_query($DBCON, $step, $sql) {
	clone_salon_log("start $step");
	try {
		$query = mysqli_query($DBCON, $sql);
	} catch (Throwable $e) {
		clone_salon_fail($step, $sql, get_class($e) . ": " . $e->getMessage());
	}
	if ($query === false)
		clone_salon_fail($step, $sql, mysqli_error($DBCON));
	clone_salon_log("ok $step");
	return $query;
}

function clone_salon_copy($DBCON, $step, $table, $src_yearmonth, $overrides, $extra_where = "") {
	$col_sql = "SHOW COLUMNS FROM `$table`";
	$query = clone_salon_query($DBCON, "$step columns", $col_sql);
	$insert_cols = array();
	$select_exprs = array();
	while ($row = mysqli_fetch_assoc($query)) {
		$col = $row["Field"];
		if (stripos((string) $row["Extra"], "generated") !== false)
			continue;
		$quoted = "`" . str_replace("`", "", $col) . "`";
		$insert_cols[] = $quoted;
		if (array_key_exists($col, $overrides))
			$select_exprs[] = $overrides[$col];
		else
			$select_exprs[] = $quoted;
	}
	if (count($insert_cols) == 0)
		clone_salon_fail($step, $col_sql, "Table $table has no columns");

	$sql  = "INSERT INTO `$table` (" . implode(", ", $insert_cols) . ") ";
	$sql .= "SELECT " . implode(", ", $select_exprs) . " ";
	$sql .= "FROM `$table` WHERE yearmonth = '$src_yearmonth' $extra_where";
	clone_salon_log("$step using " . count($insert_cols) . " columns");
	clone_salon_query($DBCON, $step, $sql);
}

clone_salon_log("request", array(
	"method" => isset($_SERVER["REQUEST_METHOD"]) ? $_SERVER["REQUEST_METHOD"] : "",
	"admin_id_set" => isset($_SESSION['admin_id']) ? "yes" : "no",
	"yearmonth" => isset($_REQUEST['yearmonth']) ? $_REQUEST['yearmonth'] : "",
	"cln_yearmonth" => isset($_REQUEST['cln_yearmonth']) ? $_REQUEST['cln_yearmonth'] : "",
	"cln_contest_name" => isset($_REQUEST['cln_contest_name']) ? $_REQUEST['cln_contest_name'] : "",
	"clone_button" => isset($_REQUEST['clone-salon-action']) ? "yes" : "no",
));

// Main Code
if( isset($_SESSION['admin_id']) && isset($_REQUEST['yearmonth']) && isset($_REQUEST['cln_yearmonth']) && isset($_REQUEST['clone-salon-action']) ) {

	$src_yearmonth = mysqli_real_escape_string($DBCON, $_REQUEST['yearmonth']);	// Source
	$yearmonth = mysqli_real_escape_string($DBCON, $_REQUEST['cln_yearmonth']);	// Target
	$contest_name = mysqli_real_escape_string($DBCON, isset($_REQUEST['cln_contest_name']) ? $_REQUEST['cln_contest_name'] : "");
	clone_salon_log("cloning $src_yearmonth -> $yearmonth ($contest_name)");

	// Check if contests already exists for target yearmonth
	$sql = "SELECT * FROM contest WHERE yearmonth = '$yearmonth' ";
	$query = clone_salon_query($DBCON, "check target contest", $sql);
	if (mysqli_num_rows($query) != 0)
		return_error("A Salon for the yearmonth already exists", __FILE__, __LINE__, true);

	// Start Transaction
	$sql = "START TRANSACTION";
	clone_salon_query($DBCON, "start transaction", $sql);

	$new_yearmonth = "'$yearmonth'";
	clone_salon_copy($DBCON, "insert contest", "contest", $src_yearmonth, array(
		"yearmonth" => $new_yearmonth,
		"contest_name" => "'$contest_name'",
		"judging_in_progress" => "0",
		"results_ready" => "0",
		"certificates_ready" => "0",
		"catalog_release_date" => "NULL",
		"catalog_ready" => "0",
		"catalog" => "''",
		"catalog_download" => "''",
		"catalog_order_last_date" => "NULL",
		"num_entries" => "0",
		"num_women" => "0",
		"num_pictures" => "0",
		"num_awards" => "0",
		"num_hms" => "0",
		"num_acceptances" => "0",
		"num_winners" => "0",
		"archived" => "0",
	));
	clone_salon_copy($DBCON, "insert section", "section", $src_yearmonth, array(
		"yearmonth" => $new_yearmonth,
		"cut_off_score" => "0",
		"num_entrants" => "0",
		"num_pictures" => "0",
		"num_awards" => "0",
		"num_hms" => "0",
		"num_acceptances" => "0",
		"num_winners" => "0",
	));
	clone_salon_copy($DBCON, "insert entrant_category", "entrant_category", $src_yearmonth, array(
		"yearmonth" => $new_yearmonth,
	));
	clone_salon_copy($DBCON, "insert award", "award", $src_yearmonth, array(
		"yearmonth" => $new_yearmonth,
	));
	clone_salon_copy($DBCON, "insert fee_structure", "fee_structure", $src_yearmonth, array(
		"yearmonth" => $new_yearmonth,
	));
	clone_salon_copy($DBCON, "insert discount", "discount", $src_yearmonth, array(
		"yearmonth" => $new_yearmonth,
	), "AND group_code NOT LIKE 'GDC%'");
	clone_salon_copy($DBCON, "insert team", "team", $src_yearmonth, array(
		"yearmonth" => $new_yearmonth,
	), "AND role IN ('Chairman', 'Secretary', 'Webmaster')");

	 $sql = "COMMIT";
	 clone_salon_query($DBCON, "commit", $sql);

	// Create Required Folders
	$salon_folder = "../../salons/$yearmonth";
	if (! is_dir($salon_folder)) mkdir($salon_folder);
	if (! is_dir("$salon_folder/blob")) mkdir("$salon_folder/blob");
	if (! is_dir("$salon_folder/img")) mkdir("$salon_folder/img");
	if (! is_dir("$salon_folder/img/com")) mkdir("$salon_folder/img/com");
	if (! is_dir("$salon_folder/img/recognition")) mkdir("$salon_folder/img/recognition");
	if (! is_dir("$salon_folder/img/sponsor")) mkdir("$salon_folder/img/sponsor");

	 clone_salon_log("clone finished for $yearmonth");
	 $resArray = [];
	 $resArray['success'] = TRUE;
	 $resArray['msg'] = "";
	 echo json_encode($resArray);
	 die();
}
else {
	clone_salon_log("rejected request: missing admin session or yearmonth / cln_yearmonth / clone-salon-action");
	return_error("Invalid Update Request", __FILE__, __LINE__, true);
}

?>
