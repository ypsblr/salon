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
	const SAVE_FILE_TYPE = ".png";

	// Protected
	protected $yearmonth = "";
	protected $section = "";
	protected $start = 0;
	protected $size = 0;
	protected $salon_folder = "";
	protected $zip_folder = "";
	protected $zip_name = "slides.zip";
	protected $zip_file_name = "";
	protected $target_folder = "";
	protected $save_file_name = "";

	protected $conf;
	protected $fonts;
	protected $colors;

	protected $exif;

	protected $dbcon = "";
	protected $param = "";
	protected $sql = "";
	protected $query = NULL;

	protected $seqno = 0;
	protected $completed = false;

	// Constructor
	function __construct($dbcon, $param) {
		$this->dbcon = $dbcon;
		// $this->param = $param;

		if ( ! isset($param['yearmonth']))
			$this->return_error("Salon not specified", __FILE__, __LINE__);

		if ( ! isset($param['section']))
			$this->return_error("Section not specified", __FILE__, __LINE__);

		$this->yearmonth = $param['yearmonth'];
		$this->section = decode_string_array($param['section']);
		$this->start = $param['start'];
		$this->size = $param['size'];
		$this->salon_folder = $_SERVER['DOCUMENT_ROOT'] . "/salons/" . $this->yearmonth;
		$this->target_folder = $_SERVER['DOCUMENT_ROOT'] . "/generated/" . $this->yearmonth . "/slideshow/" . $this->section . "/slides";
		$this->zip_folder = $_SERVER['DOCUMENT_ROOT'] . "/generated/" . $this->yearmonth . "/slideshow/" . $this->section;

		$this->seqno = $this->start;

		// Clean existing slides from target folder
		array_map("unlink", glob($this->target_folder . "/*"));

		// Load Config JSON File
		if (file_exists($this->salon_folder . "/blob/slideshow.json")) {
			$this->conf = json_decode(file_get_contents($this->salon_folder . "/blob/slideshow.json"), true);
			if (json_last_error() != JSON_ERROR_NONE)
				$this->return_error("Slideshow Configuration Corrupt. Use Salon Manage to create a Slideshow Configuration.", __FILE__, __LINE__);
			if ($this->yearmonth != $this->conf['yearmonth'])
				$this->return_error("Slideshow Configuration File does not relate to this contest.", __FILE__, __LINE__);
		}
		else
			$this->return_error("Slideshow Configuration File ($this->salon_folder/blob/slideshow.json) not found. Use Salon Manage to create a Slideshow Configuration.", __FILE__, __LINE__);

		if (! file_exists($this->salon_folder . "/img/" . $this->conf['slideshow_design']))
			$this->return_error("Slideshow Background Design Image File not found.", __FILE__, __LINE__);

		// Prepare Color Array and Font array
		foreach ($this->conf as $key => $val) {
			if (substr($key, 0, 5) == "font_")
				$this->fonts[substr($key, 5)] = $val;
			if (substr($key, 0, 6) == "color_")
				$this->colors[$key] = $val;	// Skip # in color definition
		}

		// Substitute Fonts & Colors
		foreach (['section_opening', 'section_closing', 'picture'] as $def) {
			if (isset($this->conf[$def])) {
				foreach($this->conf[$def] as $idx => $field) {
					if (isset($field['font'])) {
						if (isset($this->fonts[$field['font']]))
							$this->conf[$def][$idx]['font'] = $this->fonts[$field['font']];
					}
					if (isset($field['font_color'])) {
						if (isset($this->colors[$field['font_color']]))
							$this->conf[$def][$idx]['font_color'] = $this->colors[$field['font_color']];
					}
					if (isset($field['fill_color'])) {
						if (isset($this->colors[$field['fill_color']]))
							$this->conf[$def][$idx]['fill_color'] = $this->colors[$field['fill_color']];
					}
					if (isset($field['border_color'])) {
						if (isset($this->colors[$field['border_color']]))
							$this->conf[$def][$idx]['border_color'] = $this->colors[$field['border_color']];
					}
				}
			}
		}

		$this->sql  = "SELECT profile.profile_id, profile.profile_name, country.sortname, country.country_name, honors, avatar, city, state, ";
		$this->sql .= "       title, pic.pic_id, pic.section, picfile, exif, award.award_name, award.level, award.sequence ";
		$this->sql .= "  FROM pic_result, award, pic, profile, country ";
		$this->sql .= " WHERE pic_result.yearmonth = '" . $this->yearmonth . "' ";
		$this->sql .= "   AND award.yearmonth = pic_result.yearmonth ";
		$this->sql .= "   AND award.award_id = pic_result.award_id ";
		$this->sql .= "   AND award.section = '" . $this->section . "' ";
		$this->sql .= "   AND pic.yearmonth = pic_result.yearmonth ";
		$this->sql .= "   AND pic.profile_id = pic_result.profile_id ";
		$this->sql .= "   AND pic.pic_id = pic_result.pic_id ";
		$this->sql .= "   AND profile.profile_id = pic.profile_id ";
		$this->sql .= "   AND country.country_id = profile.country_id ";
		$this->sql .= " ORDER BY profile.profile_name, award.level, award.sequence ";
		$this->sql .= " LIMIT " . $this->start . ", " . $this->size . " ";

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
		return $this->salon_folder . "/img/" . $this->conf['slideshow_design'];
	}

	function getMergeData() {
		if ($this->query == NULL) {
			$this->query = mysqli_query($this->dbcon, $this->sql) or $this->sql_error($this->sql, mysqli_error($this->dbcon), __FILE__, __LINE__);

			// Return the opening page to be rendered
			$this->setFieldValue($this->conf['section_opening'], "section", $this->section);

			$sql  = "SELECT user_name FROM assignment, user ";
			$sql .= " WHERE assignment.yearmonth = '" . $this->yearmonth . "' ";
			$sql .= "   AND assignment.section = '" . $this->section . "'";
			$sql .= "   AND user.user_id = assignment.user_id ";
			$sql .= " ORDER BY user_name ";
			$subq = mysqli_query($this->dbcon, $sql) or $this->sql_error($this->sql, mysqli_error($this->dbcon), __FILE__, __LINE__);
			$user = mysqli_fetch_array($subq);
			$this->setFieldValue($this->conf['section_opening'], "jury_1_name", $user['user_name']);
			$user = mysqli_fetch_array($subq);
			$this->setFieldValue($this->conf['section_opening'], "jury_2_name", $user['user_name']);
			$user = mysqli_fetch_array($subq);
			$this->setFieldValue($this->conf['section_opening'], "jury_3_name", $user['user_name']);

			$this->save_file_name = sprintf("%s-%04d-%04d-%02d%s", $this->conf['file_name_stub'], 0, 0, 0, self::SAVE_FILE_TYPE);
			$this->zip_file_name = "slides/" . $this->save_file_name;

			return $this->conf['section_opening'];
		}
		elseif ($this->completed) {
			return NULL;
		}
		else {
			// Query has been opened. Return the next record
			if ($row = mysqli_fetch_array($this->query)) {
				$this->save_file_name = sprintf("%s-%04d-%04d-%02d%s", $this->conf['file_name_stub'], ++ $this->seqno, $row['profile_id'], $row['pic_id'], self::SAVE_FILE_TYPE);
				$this->zip_file_name = "slides/" . $this->save_file_name;
				// Get Exif
				// $this->exif = exif_data($this->salon_folder . "/upload/" . $row['section'] . "/" . $row['picfile']);
				// $this->no_exif = ($this->exif == false);
				$this->exif = json_decode($row['exif'], true);
				$this->no_exif = ($this->exif['error'] != "");

				// Populate fields
				foreach ($this->conf['picture'] as $field => $config) {
					// Populate fields
					if ($config['field'] == 'picfile') {
						$this->conf['picture'][$field]["value"] = $this->salon_folder . "/upload/" . $row['section'] . "/" . $row['picfile'];
					}
					elseif ($config['field'] == 'avatar') {
						if ($row['avatar'] != ""  && $row['avatar'] != "user.jpg")
							$this->conf['picture'][$field]["value"] = $_SERVER['DOCUMENT_ROOT'] . "/res/avatar/" . $row['avatar'];
						else
							$this->conf['picture'][$field]["value"] = "";
					}
					elseif ($config['field'] == "title") {
						// $this->fields[$field]["value"] = $row['award_name'] . " - '" . $row['title'] . "' by " . $row['profile_name'] . ", " . (self::IS_INTERNATIONAL ? $row['country_name'] : $row['city']);
						$this->conf['picture'][$field]["value"] = "'" . $row['title'] . "' - " . $row['award_name'];
					}
					elseif ($config['field'] == "city") {
						$this->conf['picture'][$field]["value"] = ($this->conf['is_international'] == '1') ? $row['city'] . ", " . $row['country_name'] : $row['city'] . ", " . $row['state'];
						// $this->fields[$field]["value"] = $row['city'] . ", " . $row['state'];
					}
					elseif ($config['field'] == "honors") {
						// Use cleaned-up Honors
						$this->conf['picture'][$field]["value"] = honors_text($row['honors']);
					}
					elseif ($this->conf['is_international'] == '1' && $config['field'] == "flag") {
						// Print Country Flag
						// Create bitmap from vector flags and insert
						// $flag_svg = "../../plugin/flag-icon-css/flags/4x3/" . strtolower($row['sortname']) . ".svg";
						// $flag_svg = $_SERVER['DOCUMENT_ROOT'] . "/plugin/flag-icon-css/flags/4x3/" . strtolower($row['sortname']) . ".svg";
						$flag_svg = $_SERVER['DOCUMENT_ROOT'] . "/res/flag/" . strtolower($row['sortname']) . "_flag.png";
						if (file_exists($flag_svg)) {
							$this->conf['picture'][$field]["value"] = $flag_svg;
						}
						else
							$this->conf['picture'][$field]["value"] = "";
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
						$this->conf['picture'][$field]["value"] = (isset($row[$config['field']]) ? $row[$config['field']] : "");
				}
				return $this->conf['picture'];
			}
			else {
				// Return the closing page to be rendered
				$this->setFieldValue($this->conf['section_closing'], "section", $this->section);
				$this->completed = true;
				$this->save_file_name = sprintf("%s-%04d-%04d-%02d%s", $this->conf['file_name_stub'], 9999, 9999, 99, self::SAVE_FILE_TYPE);
				$this->zip_file_name = "slides/" . $this->save_file_name;
				return $this->conf['section_closing'];
			}
		}
	}

	function getZipFolder() {
		return $this->zip_folder;
	}

	function getZipName() {
		return sprintf("%04d_%s", $this->start + 1, $this->zip_name);
	}

	function getZipFileName() {
		return $this->zip_file_name;
	}

	function getSaveFolder() {
		return $this->target_folder;
	}

	function getSaveFileName() {
		return $this->save_file_name;
	}

}
