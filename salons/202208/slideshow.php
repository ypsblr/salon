<?php
/*
** certdef.php - Contains Print Definitions for the Certificate
** A separate file is created for each Salon depending upon Certificate Design
**
** Certificate Definition for All India Digital Salon 2020
*/
include $_SERVER['DOCUMENT_ROOT'] . "/inc/exif.php";

class ImageMerge {
	// Constants
	// Text Colors
	// const COLOR_LABEL = "927C4D";
	// const COLOR_FIELD = "A93C3A";
	const COLOR_FIELD = "000000";
	const COLOR_SUBDUED = "808080";
	const COLOR_HIGHLIGHT = "CCCCCC";
	const COLOR_GOLD = "A48438";
	const COLOR_WHITE = "FFFFFF";

	// Fonts
	const FONT_REGULAR = "GothamCond-Light.ttf";
	const FONT_BOLD = "GothamCond-Book.ttf";
	const FONT_ITALIC = "GothamCond-LightItalic.ttf";

	// Files and Folders
	const YEARMONTH = "202208";
	const IS_INTERNATIONAL = true;
	const FILE_NAME_STUB = "AIS2022-SS";
	const BASE_IMAGE = "ais2022_slideshow.png";

	// Protected
	protected $salon_folder = "";
	protected $target_folder = "";
	protected $bg_image = "";
	protected $save_file_name = "";
	protected $save_file_type = ".png";
	protected $dbcon = "";
	protected $param = "";
	protected $sql = "";
	protected $query = NULL;
	protected $fields = [
							// Picture
							array ("type" => "image", "field" => "picfile", "value" => "",
									"x" => 320, "y" => 180, "width" => 1600, "height" => 900, "rotate" => 0, "position" => "MM",
									"fill_color" => "", "border_size" => 0, "border_color" => "", "border_gap" => 0),
							// Avatar
							array ("type" => "image", "field" => "avatar", "value" => "",
									"x" => 20, "y" => 760, "width" => 120, "height" => 180, "rotate" => 0, "position" => "LB",
									"fill_color" => "", "border_size" => 0, "border_color" => "", "border_gap" => 0),
							// Profile Name
							array ("type" => "text", "field" => "profile_name", "value" => "",
									"font" => self::FONT_BOLD, "font_size" => 32, "font_color" => self::COLOR_GOLD,
									"x" => 20, "y" => 960, "width" => 280, "height" => 40, "rotate" => 0, "position" => "LT",
									"fill_color" => "", "border_size" => 0, "border_color" => ""),
							// Flag
							array ("type" => "image", "field" => "flag", "value" => "",
									"x" => 20, "y" => 1000, "width" => 32, "height" => 24, "rotate" => 0, "position" => "LB",
									"fill_color" => "", "border_size" => 0, "border_color" => "", "border_gap" => 0),
							// City
							array ("type" => "text", "field" => "city", "value" => "",
									"font" => self::FONT_BOLD, "font_size" => 18, "font_color" => self::COLOR_SUBDUED,
									"x" => 60, "y" => 1000, "width" => 240, "height" => 24, "rotate" => 0, "position" => "LM",
									"fill_color" => "", "border_size" => 0, "border_color" => ""),
							// Honors
							array ("type" => "text", "field" => "honors", "value" => "",
									"font" => self::FONT_ITALIC, "font_size" => 12, "font_color" => self::COLOR_SUBDUED,
									"x" => 20, "y" => 1032, "width" => 280, "height" => 20, "rotate" => 0, "position" => "LT",
									"fill_color" => "", "border_size" => 0, "border_color" => ""),
							// Pic Title
							array ("type" => "text", "field" => "title", "value" => "",
									"font" => self::FONT_BOLD, "font_size" => 32, "font_color" => self::COLOR_WHITE,
									"x" => 320, "y" => 130, "width" => 1600, "height" => 40, "rotate" => 0, "position" => "MB",
									"fill_color" => "", "border_size" => 0, "border_color" => ""),
							// Section
							array ("type" => "text", "field" => "section", "value" => "",
									"font" => self::FONT_BOLD, "font_size" => 80, "font_color" => self::COLOR_SUBDUED,
									"x" => 22, "y" => 290, "width" => 80, "height" => 400, "rotate" => 90, "position" => "LT",
									"fill_color" => "", "border_size" => 0, "border_color" => ""),
							// exif - iso
							// array ("type" => "text", "field" => "iso", "value" => "",
							// 		"font" => self::FONT_BOLD, "font_size" => 18, "font_color" => self::COLOR_SUBDUED,
							// 		"x" => 160, "y" => 180, "width" => 120, "height" => 20, "rotate" => 0, "position" => "LT",
							// 		"fill_color" => "", "border_size" => 0, "border_color" => ""),
							// exif - Aperture
							// array ("type" => "text", "field" => "aperture", "value" => "",
							// 		"font" => self::FONT_BOLD, "font_size" => 18, "font_color" => self::COLOR_SUBDUED,
							// 		"x" => 160, "y" => 210, "width" => 120, "height" => 20, "rotate" => 0, "position" => "LT",
							// 		"fill_color" => "", "border_size" => 0, "border_color" => ""),
							// exif - Shutter Speed
							// array ("type" => "text", "field" => "speed", "value" => "",
							// 		"font" => self::FONT_BOLD, "font_size" => 18, "font_color" => self::COLOR_SUBDUED,
							// 		"x" => 160, "y" => 240, "width" => 120, "height" => 20, "rotate" => 0, "position" => "LT",
							// 		"fill_color" => "", "border_size" => 0, "border_color" => ""),
							// exif - Camera
							// array ("type" => "text", "field" => "camera", "value" => "",
							// 		"font" => self::FONT_BOLD, "font_size" => 18, "font_color" => self::COLOR_SUBDUED,
							// 		"x" => 160, "y" => 270, "width" => 120, "height" => 20, "rotate" => 0, "position" => "LT",
							// 		"fill_color" => "", "border_size" => 0, "border_color" => ""),
						];

	protected $seqno = 0;
	protected $exif;
	protected $completed = false;
	protected $opening = [
							// Section Name
							array ("type" => "text", "field" => "section", "value" => "",
									"font" => self::FONT_BOLD, "font_size" => 96, "font_color" => self::COLOR_GOLD,
									"x" => 320, "y" => 400, "width" => 1600, "height" => 120, "rotate" => 0, "position" => "MM",
									"fill_color" => "", "border_size" => 0, "border_color" => ""),
							// Jury Label
							array ("type" => "text", "field" => "jury_label", "value" => "JURY",
									"font" => self::FONT_BOLD, "font_size" => 32, "font_color" => self::COLOR_SUBDUED,
									"x" => 320, "y" => 600, "width" => 1600, "height" => 40, "rotate" => 0, "position" => "MM",
									"fill_color" => "", "border_size" => 0, "border_color" => ""),
							// Jury 1
							array ("type" => "text", "field" => "jury_1", "value" => "",
									"font" => self::FONT_REGULAR, "font_size" => 48, "font_color" => self::COLOR_HIGHLIGHT,
									"x" => 320, "y" => 680, "width" => 1600, "height" => 60, "rotate" => 0, "position" => "MM",
									"fill_color" => "", "border_size" => 0, "border_color" => ""),
							// Jury 2
							array ("type" => "text", "field" => "jury_2", "value" => "",
									"font" => self::FONT_REGULAR, "font_size" => 48, "font_color" => self::COLOR_HIGHLIGHT,
									"x" => 320, "y" => 760, "width" => 1600, "height" => 60, "rotate" => 0, "position" => "MM",
									"fill_color" => "", "border_size" => 0, "border_color" => ""),
							// Jury 3
							array ("type" => "text", "field" => "jury_3", "value" => "",
									"font" => self::FONT_REGULAR, "font_size" => 48, "font_color" => self::COLOR_HIGHLIGHT,
									"x" => 320, "y" => 840, "width" => 1600, "height" => 60, "rotate" => 0, "position" => "MM",
									"fill_color" => "", "border_size" => 0, "border_color" => ""),
						];
	protected $closing = [
							// Jury Label
							array ("type" => "text", "field" => "label", "value" => "END OF SECTION",
									"font" => self::FONT_BOLD, "font_size" => 32, "font_color" => self::COLOR_SUBDUED,
									"x" => 320, "y" => 480, "width" => 1600, "height" => 40, "rotate" => 0, "position" => "MM",
									"fill_color" => "", "border_size" => 0, "border_color" => ""),
							// Section Name
							array ("type" => "text", "field" => "section", "value" => "",
									"font" => self::FONT_BOLD, "font_size" => 96, "font_color" => self::COLOR_SUBDUED,
									"x" => 320, "y" => 600, "width" => 1600, "height" => 120, "rotate" => 0, "position" => "MM",
									"fill_color" => "", "border_size" => 0, "border_color" => ""),
						];

	// Constructor
	function __construct($dbcon, $param) {
		$this->dbcon = $dbcon;
		$this->param = $param;

		if ( ! isset($param['section']))
			$this->return_error("Section not specified", __FILE__, __LINE__);

		$this->salon_folder = $_SERVER['DOCUMENT_ROOT'] . "/salons/" . self::YEARMONTH;
		$this->target_folder = $_SERVER['DOCUMENT_ROOT'] . "/generated/" . self::YEARMONTH . "/slideshow/" . $param['section'];
		$this->bg_image = $this->salon_folder . "/img/" . self::BASE_IMAGE;

		$this->sql  = "SELECT profile.profile_id, profile.profile_name, country.sortname, country.country_name, honors, avatar, city, state, ";
		$this->sql .= "       title, pic.pic_id, pic.section, picfile, exif, award.award_name, award.level, award.sequence ";
		$this->sql .= "  FROM pic_result, award, pic, profile, country ";
		$this->sql .= " WHERE pic_result.yearmonth = '" . self::YEARMONTH . "' ";
		$this->sql .= "   AND award.yearmonth = pic_result.yearmonth ";
		$this->sql .= "   AND award.award_id = pic_result.award_id ";
		$this->sql .= "   AND award.section = '" . $this->param['section'] . "' ";
		// $this->sql .= "   AND award.level < 99 ";
		$this->sql .= "   AND pic.yearmonth = pic_result.yearmonth ";
		$this->sql .= "   AND pic.profile_id = pic_result.profile_id ";
		$this->sql .= "   AND pic.pic_id = pic_result.pic_id ";
		$this->sql .= "   AND profile.profile_id = pic.profile_id ";
		$this->sql .= "   AND country.country_id = profile.country_id ";
		// $this->sql .= " ORDER BY award.level, award.sequence, profile.profile_name ";
		$this->sql .= " ORDER BY profile.profile_name, award.level, award.sequence ";
		// $this->sql .= " LIMIT 10 ";

	}

	// Internal Methods
	protected function debug_dump($name, $value, $phpfile, $phpline) {
		$log_file = $_SERVER["DOCUMENT_ROOT"] . "/logs/debug.txt";
		file_put_contents($log_file, date("Y-m-d H:i") .": Dump of '$name' requested in line $phpline of '$phpfile'" . chr(13) . chr(10), FILE_APPEND);
		file_put_contents($log_file, print_r($value, true) . chr(13) . chr(10), FILE_APPEND);
	}

	protected function return_error($errmsg, $phpfile, $phpline) {

	    $_SESSION['err_msg'] = $errmsg;

	    $log_file = $_SERVER["DOCUMENT_ROOT"] . "/logs/errlog.txt";

		file_put_contents($log_file, date("Y-m-d H:i") .": Error '$errmsg' reported in line $phpline of '$phpfile'" . chr(13) . chr(10), FILE_APPEND);
		if (isset($_REQUEST)) {
	        file_put_contents($log_file, "Dump of REQUEST:" . chr(13) . chr(10), FILE_APPEND);
			file_put_contents($log_file, print_r($_REQUEST, true) . chr(13) . chr(10), FILE_APPEND);
	    }
		if (isset($_SESSION)) {
	        file_put_contents($log_file, "Dump of SESSION:" . chr(13) . chr(10), FILE_APPEND);
			file_put_contents($log_file, print_r($_SESSION, true) . chr(13) . chr(10), FILE_APPEND);
	    }
		echo $errmsg;
		die();
	}

	protected function sql_error($sql, $errmsg, $phpfile, $phpline) {
		$log_file = $_SERVER["DOCUMENT_ROOT"] . "/logs/sql_errlog.txt";
		file_put_contents($log_file, date("Y-m-d H:i") . ": SQL operation failed with message '$errmsg' in line $phpline of '$phpfile'" . chr(13) . chr(10), FILE_APPEND);
		file_put_contents($log_file, "Failing SQL: " . $sql . chr(13) . chr(10), FILE_APPEND);
		$_SESSION['err_msg'] = "SQL Operation failed. Please report to YPS to check using Contact Us page.";
		echo $_SESSION['err_msg'];
		die();

	}

	protected function setFieldValue(&$set, $field, $value) {
		foreach ($set as $index => $item) {
			if (isset($item['field']) && $item['field'] == $field) {
				$set[$index]["value"] = $value;
				return;
			}
		}
	}

	// Public Methods
	function getBackgroundImage() {
		return $this->bg_image;
	}

	function getMergeData() {
		if ($this->query == NULL) {
			$this->query = mysqli_query($this->dbcon, $this->sql) or $this->sql_error($this->sql, mysqli_error($this->dbcon), __FILE__, __LINE__);

			// Return the opening page to be rendered
			$this->setFieldValue($this->opening, "section", $this->param['section']);

			$sql  = "SELECT user_name FROM assignment, user ";
			$sql .= " WHERE assignment.yearmonth = '" . self::YEARMONTH . "' ";
			$sql .= "   AND assignment.section = '" . $this->param['section'] . "'";
			$sql .= "   AND user.user_id = assignment.user_id ";
			$sql .= " ORDER BY user_name ";
			$subq = mysqli_query($this->dbcon, $sql) or $this->sql_error($this->sql, mysqli_error($this->dbcon), __FILE__, __LINE__);
			$user = mysqli_fetch_array($subq);
			$this->setFieldValue($this->opening, "jury_1", $user['user_name']);
			$user = mysqli_fetch_array($subq);
			$this->setFieldValue($this->opening, "jury_2", $user['user_name']);
			$user = mysqli_fetch_array($subq);
			$this->setFieldValue($this->opening, "jury_3", $user['user_name']);

			$this->save_file_name = sprintf("%s-%04d-%04d-%02d%s", self::FILE_NAME_STUB, $this->seqno, 0, 0, $this->save_file_type);

			return $this->opening;
		}
		elseif ($this->completed) {
			return NULL;
		}
		else {
			// Query has been opened. Return the next record
			if ($row = mysqli_fetch_array($this->query)) {
				$this->save_file_name = sprintf("%s-%04d-%04d-%02d%s", self::FILE_NAME_STUB, ++ $this->seqno, $row['profile_id'], $row['pic_id'], $this->save_file_type);
				// Get Exif
				// $this->exif = exif_data($this->salon_folder . "/upload/" . $row['section'] . "/" . $row['picfile']);
				// $this->no_exif = ($this->exif == false);
				$this->exif = json_decode($row['exif'], true);
				$this->no_exif = ($this->exif['error'] != "");
				// Populate fields
				foreach ($this->fields as $field => $config) {
					// Populate fields
					if ($config['field'] == 'picfile') {
						$this->fields[$field]["value"] = $this->salon_folder . "/upload/" . $row['section'] . "/" . $row['picfile'];
					}
					elseif ($config['field'] == 'avatar') {
						if ($row['avatar'] != ""  && $row['avatar'] != "user.jpg")
							$this->fields[$field]["value"] = $_SERVER['DOCUMENT_ROOT'] . "/res/avatar/" . $row['avatar'];
						else
							$this->fields[$field]["value"] = "";
					}
					elseif ($config['field'] == "title") {
						// $this->fields[$field]["value"] = $row['award_name'] . " - '" . $row['title'] . "' by " . $row['profile_name'] . ", " . (self::IS_INTERNATIONAL ? $row['country_name'] : $row['city']);
						$this->fields[$field]["value"] = "'" . $row['title'] . "' - " . $row['award_name'];
					}
					elseif ($config['field'] == "city") {
						$this->fields[$field]["value"] = (self::IS_INTERNATIONAL) ? $row['city'] . ", " . $row['country_name'] : $row['city'] . ", " . $row['state'];
						// $this->fields[$field]["value"] = $row['city'] . ", " . $row['state'];
					}
					elseif ($config['field'] == "honors") {
						// Use cleaned-up Honors
						$this->fields[$field]["value"] = honors_text($row['honors']);
					}
					elseif (self::IS_INTERNATIONAL && $config['field'] == "flag") {
						// Print Country Flag
						// Create bitmap from vector flags and insert
						// $flag_svg = "../../plugin/flag-icon-css/flags/4x3/" . strtolower($row['sortname']) . ".svg";
						// $flag_svg = $_SERVER['DOCUMENT_ROOT'] . "/plugin/flag-icon-css/flags/4x3/" . strtolower($row['sortname']) . ".svg";
						$flag_svg = $_SERVER['DOCUMENT_ROOT'] . "/res/flag/" . strtolower($row['sortname']) . "_flag.png";
						if (file_exists($flag_svg)) {
							$this->fields[$field]["value"] = $flag_svg;
						}
						else
							$this->fields[$field]["value"] = "";
					}
					// elseif ($config['field'] == "iso") {
					// 	$this->fields[$field]["value"] = (isset($this->exif["iso"]) ? "ISO " . $this->exif["iso"] : "");
					// }
					// elseif ($config['field'] == "aperture") {
					// 	$this->fields[$field]["value"] = (isset($this->exif["aperture"]) ? "Aperture " . $this->exif["aperture"] : "");
					// }
					// elseif ($config['field'] == "speed") {
					// 	$this->fields[$field]["value"] = (isset($this->exif["speed"]) ? "Speed " . $this->exif["speed"] : "");
					// }
					// elseif ($config['field'] == "camera") {
					// 	$this->fields[$field]["value"] = (isset($this->exif["camera"]) ? $this->exif["camera"] : "");
					// }
					else
						$this->fields[$field]["value"] = (isset($row[$config['field']]) ? $row[$config['field']] : "");
				}
				return $this->fields;
			}
			else {
				// Return the closing page to be rendered
				$this->setFieldValue($this->closing, "section", $this->param['section']);
				$this->completed = true;
				$this->save_file_name = sprintf("%s-%04d-%04d-%02d%s", self::FILE_NAME_STUB, 9999, 9999, 99, $this->save_file_type);
				return $this->closing;
			}
		}
	}

	function getSaveFolder() {
		return $this->target_folder;
	}

	function getSaveFileName() {
		return $this->save_file_name;
	}

}
