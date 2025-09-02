<?php
// Extract Sponsor Data for the current year
// session_start();
include("../inc/session.php");

include("../inc/connect.php");
include("../inc/lib.php");

function safe_name ($str) {
	$ret_str = "";
	for ($i = 0; $i < strlen($str); ++$i) {
		$char = substr($str, $i, 1);
		if ($char == " ")
			$ret_str .= "_";
		else if ( ($char >= "0" && $char <= "9") || ($char >= "A" && $char <="Z") || ($char >= "a" && $char <= "z") )
			$ret_str .= $char;
	}
	return $ret_str;
}

function die_with_error($errmsg) {
	echo $errmsg;
	die();
}

function zip_error($code) {
	switch ($code) {
		case ZipArchive::ER_EXISTS : return "ZIP File already exists";
		case ZipArchive::ER_INCONS : return "Corrupt ZIP File";
		case ZipArchive::ER_INVAL : return "Invalid Arguments";
		case ZipArchive::ER_MEMORY : return "Insufficent Memory to perform the operation";
		case ZipArchive::ER_NOENT : return "File Not Found";
		case ZipArchive::ER_NOZIP : return "Not a ZIP File";
		case ZipArchive::ER_OPEN : return "Unable to open the file";
		case ZipArchive::ER_READ : return "Unable to read data";
		case ZipArchive::ER_SEEK : return "Unable to seek specific file in the ZIP";
		default : return "No Error";
	}
}

function make_path($path) {
	if (! is_dir(dirname($path)))
		make_path(dirname($path));
	if (! is_dir($path))
		mkdir($path);
}

if(isset($_SESSION['admin_yearmonth']) && isset($_SESSION['admin_id']) ) {
    // Set contest
    $yearmonth = $_SESSION["admin_yearmonth"];

	// Create avatars folder
	$target_path = $_SERVER['DOCUMENT_ROOT'] . "/generated/$yearmonth/sponsors";
	if (! file_exists($target_path))
		make_path($target_path);

	$logo_path = $_SERVER['DOCUMENT_ROOT'] . "/res/sponsor";

	$zipfile = "$target_path/sponsors.zip";
	$zip = New ZipArchive;
	if ( ! ($zip_open = $zip->open($zipfile, ZipArchive::CREATE | ZipArchive::OVERWRITE)) )
		die_with_error("Error " . zip_error($zip_open) . " while creating $zipfile");

	// Open CSV File
		// Open output stream for data download
	$csv_file = $target_path . "/sponsors.csv";
	$output = fopen($csv_file, 'w');

	// Write First Line
	fputcsv($output, array('Sponsor#', 'Name', 'Email', 'Phone', 'Website'));

    // Select Rows from sponsor table
    // Get List of Awardees
    $sql  = "SELECT * ";
    $sql .= "  FROM sponsor ";
    $sql .= " WHERE sponsor_id IN (SELECT DISTINCT sponsor_id FROM sponsorship WHERE yearmonth = '$yearmonth') ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
    while ($row = mysqli_fetch_array($query)) {
		// Copy Logo
		if ((! empty($row['sponsor_logo'])) && $row['sponsor_logo'] != 'user.jpg') {
			$sponsor_logo = "../../res/sponsor/" . $row['sponsor_logo'];
			$zip_logo = "logo/" . sprintf("%04d_%s.jpg", $row['sponsor_id'], safe_name($row['sponsor_name']) );
			// Add created image to ZIP File
			if ( ! $zip->addFile($sponsor_logo, $zip_logo) )
				die_with_error("Error adding $zip_logo to ZIP");
		}
		// Write Data
		fputcsv($output, array($row['sponsor_id'], $row['sponsor_name'], $row['sponsor_email'], $row['sponsor_phone'], $row['sponsor_website']));
    }
	// Add CSV File to ZIP
	fclose($output);
	if ( ! $zip->addFile($csv_file, basename($csv_file)) )
		die_with_error("Error adding $csv_file to ZIP");

	// Download ZIP
	$zip->close();
	header('Content-Type: application/zip');
	header('Content-disposition: attachment; filename=' . basename($zipfile));
	header('Content-Length: ' . filesize($zipfile));
	readfile($zipfile);
}
else {
	die_with_error("Invalid Parameters !");
}
?>
