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
if ( isset($_SESSION['admin_id']) && isset($_REQUEST['yearmonth']) && (isset($_REQUEST['release_catalog']) || isset($_REQUEST['unpublish_catalog'])) ) {

	// Assemble Data
	$yearmonth = $_REQUEST['yearmonth'];
	$sql = "SELECT * FROM contest WHERE yearmonth = '$yearmonth' ";
	$query = mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_num_rows($query) == 0)
		return_error("There is no Salon for $yearmonth ", __FILE__, __LINE__, true);
	$salon = mysqli_fetch_array($query, MYSQLI_ASSOC);
	$is_archived = ($salon['archived'] == '1');

	if (isset($_REQUEST['release_catalog'])) {
		// Get Data to populate Catalog Table
		// 1 . Patronage
		$sql = "SELECT * FROM recognition WHERE yearmonth = '$yearmonth' ";
		$query = mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		$patronage = "";
		while ($row = mysqli_fetch_array($query))
			$patronage .= ($patronage == "" ? "" : ", ") . $row['short_code'] . " " . $row['recognition_id'];
		$patronage = mysqli_real_escape_string($DBCON, $patronage);

		// 2. Chairman and Secretary
		$sql = "SELECT member_name, role FROM team WHERE yearmonth = '$yearmonth' ";
		$query = mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		$chairman = "";
		$secretary = "";
		while ($row = mysqli_fetch_array($query)) {
			if ($row['role'] == "Chairman")
				$chairman = $row['member_name'];
			else if ($row['role'] == 'Secretary')
				$secretary = $row['member_name'];
		}

		// 3. Jury names
		$sql  = "SELECT DISTINCT user_name FROM user, assignment ";
		$sql .= " WHERE assignment.yearmonth = '$yearmonth' ";
		$sql .= "   AND user.user_id = assignment.user_id ";
		$query = mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		$jury = "";
		while ($row = mysqli_fetch_array($query))
			$jury .= ($jury == "" ? "" : ", ") . $row['user_name'];
		$jury = mysqli_real_escape_string($DBCON, $jury);

		// 4. Exhibition Data
		$salon_name = $salon['contest_name'];
		$exhibition_venue = $salon['exhibition_venue'];
		$exhibition_from = null_safe_date($salon['exhibition_start_date']);
		$exhibition_to = null_safe_date($salon['exhibition_end_date']);

		// Assemble values
		$catalog_release_date = null_safe_date($_REQUEST['catalog_release_date']);
		$catalog_ready = isset($_REQUEST['is_catalog_ready']) ? '1' : '0';
		$certificates_ready = isset($_REQUEST['is_certificates_ready']) ? '1' : '0';
		$catalog_order_last_date = null_safe_date($_REQUEST['catalog_order_last_date']);
		$catalog_img = $_REQUEST['catalog_img'];
		$catalog_name = $_REQUEST['catalog_name'];
		$catalog_download_name = $_REQUEST['catalog_download_name'];

		// Uploaded Catalog Front Page Image
		if (isset($_FILES)) {
			// Invitation Card Image
			if (isset($_FILES['catalog_img_upload']['name'])) {
				if ($_FILES['catalog_img_upload']['error'] != UPLOAD_ERR_NO_FILE) {
					if ($_FILES['catalog_img_upload']['error'] != UPLOAD_ERR_OK) {
						return_error("Error in uploading Catalog Front Page Image. Try again", __FILE__, __LINE__, true);
					}
					else {
						$target_file = "../../catalog/img/" . $catalog_img;
						if (! move_uploaded_file($_FILES['catalog_img_upload']['tmp_name'], $target_file))
							return_error("Error in copying Catalog Front Page Image", __FILE__, __LINE__, true);
					}
				}
			}
		}

		// Check if required files are there
		if (! file_exists("../../catalog/img/" . $catalog_img))
			return_error("Catalog Front Page Image not uploaded. Upload and try again", __FILE__, __LINE__, true);
		if (! file_exists("../../catalog/" . $catalog_name))
			return_error("Viewable Catalog PDF not uploaded. Upload and try again", __FILE__, __LINE__, true);
		if (! file_exists("../../catalog/" . $catalog_download_name))
			return_error("Downloadable High-res Catalog PDF not uploaded. Upload and try again", __FILE__, __LINE__, true);

		// Build Indian Rupee Price Structure for Catalog
		$catalog_price_in_inr = "";
		// Check if any price has been set
		if (isset($_REQUEST['catalog_price_inr']) && sizeof($_REQUEST['catalog_price_inr']) > 0) {
			$catalog_model = $_REQUEST['catalog_model_inr'];
			$catalog_price = $_REQUEST['catalog_price_inr'];
			$catalog_postage = $_REQUEST['catalog_postage_inr'];
			$inr_prices = [];
			for ($idx = 0; $idx < sizeof($catalog_model); ++ $idx) {
				if ($catalog_price[$idx] > 0) {
					$inr_prices[] = array("model" => $catalog_model[$idx], "price" => $catalog_price[$idx], "postage" => $catalog_postage[$idx]);
				}
			}
			if (sizeof($inr_prices) > 0)
				$catalog_price_in_inr = mysqli_real_escape_string($DBCON, json_encode($inr_prices));
		}

		// Build USD Price Structure for Catalog
		$catalog_price_in_usd = "";
		// Check if any price has been set
		if (isset($_REQUEST['catalog_price_usd']) && sizeof($_REQUEST['catalog_price_usd']) > 0) {
			$catalog_model = $_REQUEST['catalog_model_usd'];
			$catalog_price = $_REQUEST['catalog_price_usd'];
			$catalog_postage = $_REQUEST['catalog_postage_usd'];
			$usd_prices = [];
			for ($idx = 0; $idx < sizeof($catalog_model); ++ $idx) {
				if ($catalog_price[$idx] > 0) {
					$usd_prices[] = array("model" => $catalog_model[$idx], "price" => $catalog_price[$idx], "postage" => $catalog_postage[$idx]);
				}
			}
			if (sizeof($usd_prices) > 0)
				$catalog_price_in_usd = mysqli_real_escape_string($DBCON, json_encode($usd_prices));
		}

		if ($catalog_price_in_inr == "" && $catalog_price_in_usd == "")
			$catalog_order_last_date = "NULL";

		// Start db updates
		$sql = "START TRANSACTION";
		mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

		// Update contest
		$sql  = "UPDATE contest ";
		$sql .= "   SET catalog_release_date = $catalog_release_date ";
		$sql .= "     , catalog_order_last_date = $catalog_order_last_date ";
		$sql .= "     , catalog_ready = '1' ";
		$sql .= "     , certificates_ready = '1' ";
		$sql .= "     , catalog = '$catalog_name' ";
		$sql .= "     , catalog_download = '$catalog_download_name' ";
		$sql .= "     , catalog_price_in_inr = '$catalog_price_in_inr' ";
		$sql .= "     , catalog_price_in_usd = '$catalog_price_in_usd' ";
		$sql .= " WHERE yearmonth = '$yearmonth' ";
		$query = mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

		// Check if Catalog row exists
		$sql = "SELECT * FROM catalog WHERE yearmonth = '$yearmonth' ";
		$query = mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		if (mysqli_num_rows($query) == 0) {
			$sql  = "INSERT INTO catalog (yearmonth, salon_name, patronage, chairman, secretary, jury, ";
			$sql .= "            exhibition_venue, exhibition_from, exhibition_to, catalog_img, ";
			$sql .= "            catalog_view, catalog_download) ";
			$sql .= " VALUES('$yearmonth', '$salon_name', '$patronage', '$chairman', '$secretary', '$jury', ";
			$sql .= "        '$exhibition_venue', $exhibition_from, $exhibition_to, '$catalog_img', ";
			$sql .= "        '$catalog_name', '$catalog_download_name') ";
			$query = mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		}
		else {
			$sql  = "UPDATE catalog ";
			$sql .= "   SET catalog_img = '$catalog_img' ";
			$sql .= "     , catalog_view = '$catalog_name' ";
			$sql .= "     , catalog_download = '$catalog_download_name' ";
			$sql .= " WHERE yearmonth = '$yearmonth' ";
			$query = mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		}

		$sql = "COMMIT";
		mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	}

	if (isset($_REQUEST['unpublish_catalog'])) {
		// Start db updates
		$sql = "START TRANSACTION";
		mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

		// Update contest with values
		$sql  = "UPDATE contest ";
		$sql .= "   SET catalog_release_date = NULL ";
		$sql .= "     , catalog_ready = '0' ";
		$sql .= "     , certificates_ready = '0' ";
		$sql .= "     , catalog = '' ";
		$sql .= "     , catalog_download = '' ";
		$sql .= "     , catalog_order_last_date = NULL ";
		$sql .= "     , catalog_price_in_inr = '' ";
		$sql .= "     , catalog_price_in_usd = '' ";
		$sql .= " WHERE yearmonth = '$yearmonth' ";
		mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

		$sql = "DELETE FROM catalog WHERE yearmonth = '$yearmonth' ";
		mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

		$sql = "COMMIT";
		mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	}

	$resArray = [];
	$resArray['success'] = TRUE;
	$resArray['msg'] = "";
	echo json_encode($resArray);
	die();
}
else
	return_error("Invalid Update Request", __FILE__, __LINE__, true);

?>
