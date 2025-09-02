<?php
// session_start();
include("../inc/session.php");
include "../inc/connect.php";
include ("../inc/blacklist_lib.php");
include "ajax_lib.php";

// Exif table sometimes contain array instead of just value
// If it is array return the array field for key "value"
function exif_value($exif_data) {
    if (is_array($exif_data)) {
        if (isset($exif_data['value']))
            return $exif_data['value'];
        else
            return implode("-", $exif_data);
    }
    else
        return $exif_data;
}
// Return Exif Data stored in pic table as string
function exif_str($exif_json) {
    if ($exif_json == "")
		return "NO EXIF";

    try {
        $exif = json_decode($exif_json, true);
        $exif_strings = [];
        if (! empty($exif["camera"]))
            $exif_strings[] = exif_value($exif["camera"]);
        if (! empty($exif["iso"]))
            $exif_strings[] = "ISO " . exif_value($exif["iso"]);
        if (! empty($exif["program"]))
            $exif_strings[] = exif_value($exif["program"]);
        if (! empty($exif["aperture"]))
            $exif_strings[] = exif_value($exif["aperture"]);
        if (! empty($exif["speed"]))
            $exif_strings[] = exif_value($exif["speed"]);

        if (sizeof($exif_strings) > 0)
            return implode(", ", $exif_strings);
        else
            return "NOEXIF";
    }
    catch (Exception $e) {
        // Not a proper exif
        return "";
    }
}


// MAIN
if (isset($_SESSION['admin_id']) && isset($_SESSION['admin_yearmonth']) &&
 	isset($_REQUEST['yearmonth']) && isset($_REQUEST['profile_id']) && isset($_REQUEST['pic_id']) ) {

	$yearmonth = $_REQUEST['yearmonth'];
	$profile_id = $_REQUEST['profile_id'];
	$pic_id = $_REQUEST['pic_id'];

    $sql = "SELECT archived FROM contest WHERE yearmonth = '$yearmonth' ";
    $query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
    $contest = mysqli_fetch_array($query);
    $contest_archived = ($contest['archived'] == 1);

	// Get Picture details
    $sql  = "SELECT pic.*, profile_name, profile.email, profile.phone, blacklist_match, blacklist_exception, IFNULL(member_name, '') AS reviewed_by ";
	if ($contest_archived)
		$sql .= "  FROM profile, ar_pic pic LEFT JOIN team ON team.yearmonth = pic.yearmonth AND team.member_id = pic.reviewer_id ";
	else
		$sql .= "  FROM profile, pic LEFT JOIN team ON team.yearmonth = pic.yearmonth AND team.member_id = pic.reviewer_id ";
	$sql .= " WHERE pic.yearmonth = '$yearmonth' ";
	$sql .= "   AND pic.profile_id = '$profile_id' ";
	$sql .= "   AND pic.pic_id = '$pic_id' ";
	$sql .= "   AND profile.profile_id = pic.profile_id ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_num_rows($query) == 0)
		return_error("Picture not found ($profile_id - $pic_id).", __FILE__, __LINE__);

	$pic = mysqli_fetch_array($query, MYSQLI_ASSOC);
    $pic['exif_str'] = exif_str($pic['exif']);

    if ($pic['blacklist_match'] == "" && $pic['blacklist_exception'] == 0) {
        list($blacklist_match, $blacklist_name) = check_blacklist($pic['profile_name'], $pic['email'], $pic['phone']);
        if ($blacklist_match != "MATCH" || $blacklist_match != "SIMILAR")
            $blacklist_match = "";
        $pic['blacklist_match'] = $blacklist_match;
    }

	echo json_encode(array("success" => true, "pic" => $pic));
}
else {
	//$_SESSION['err_msg'] = "Invalid parameters";
	return_error("Invalid parameters", __FILE__, __LINE__);
}
?>
