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
	const FROM_COUNTRY_CODE = "101";

	// Protected
	protected $yearmonth = "";
	protected $section = "";
	protected $salon_folder = "";
	protected $zip_folder = "";
	protected $zip_name = "customs_declarations.zip";
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

		if (isset($param['section']))
			$this->section = $param['section'];
		// else
		// 	$this->return_error("Section not specified", __FILE__, __LINE__);

		$this->yearmonth = $param['yearmonth'];
		$this->salon_folder = $_SERVER['DOCUMENT_ROOT'] . "/salons/" . $this->yearmonth;
		$this->target_folder = $_SERVER['DOCUMENT_ROOT'] . "/generated/" . $this->yearmonth . "/customs_declarations/cd";
		$this->zip_folder = $_SERVER['DOCUMENT_ROOT'] . "/generated/" . $this->yearmonth . "/customs_declarations";

		// Load Config JSON File
		if (file_exists($this->salon_folder . "/blob/customs_declaration.json")) {
			$this->conf = json_decode(file_get_contents($this->salon_folder . "/blob/customs_declaration.json"), true);
			if (json_last_error() != JSON_ERROR_NONE)
				$this->return_error("Configuration Corrupt. Use Salon Manage to create a Customs Declaration Configuration.", __FILE__, __LINE__);
			if ($this->yearmonth != $this->conf['yearmonth'])
				$this->return_error("Customs Declaration Configuration File does not relate to this contest.", __FILE__, __LINE__);
		}
		else
			$this->return_error("Customs Declaration Configuration File ($this->salon_folder/blob/customs_declaration.json) not found. Use Salon Manage to create a Customs Declaration Configuration.", __FILE__, __LINE__);

		if (! file_exists($this->salon_folder . "/img/" . $this->conf['design']))
			$this->return_error("Customs Declaration Background Design Image File not found.", __FILE__, __LINE__);

		// Prepare Color Array and Font array
		foreach ($this->conf as $key => $val) {
			if (substr($key, 0, 5) == "font_")
				$this->fonts[substr($key, 5)] = $val;
			if (substr($key, 0, 6) == "color_")
				$this->colors[$key] = $val;	// Skip # in color definition
		}

		// Substitute Fonts & Colors, Adjust X & Y with Bleed
		if (isset($this->conf['picture'])) {
			foreach($this->conf['picture'] as $idx => $field) {
				if (isset($field['font'])) {
					if (isset($this->fonts[$field['font']]))
						$this->conf['picture'][$idx]['font'] = $this->fonts[$field['font']];
				}
				if (isset($field['font_color'])) {
					if (isset($this->colors[$field['font_color']]))
						$this->conf['picture'][$idx]['font_color'] = $this->colors[$field['font_color']];
				}
				if (isset($field['fill_color'])) {
					if (isset($this->colors[$field['fill_color']]))
						$this->conf['picture'][$idx]['fill_color'] = $this->colors[$field['fill_color']];
				}
				if (isset($field['border_color'])) {
					if (isset($this->colors[$field['border_color']]))
						$this->conf['picture'][$idx]['border_color'] = $this->colors[$field['border_color']];
				}
			}
		}

		$this->sql  = "SELECT profile.profile_id, profile_name, phone, address_1, address_2, address_3, city, state, pin, ";
		$this->sql .= "       country_name, awards, hms ";
		$this->sql .= "  FROM entry, profile, country ";
		$this->sql .= " WHERE entry.yearmonth = '" . $this->yearmonth ."' ";
		$this->sql .= "   AND profile.profile_id = entry.profile_id ";
		$this->sql .= "   AND country.country_id = profile.country_id ";
		$this->sql .= "   AND profile.country_id != '" . self::FROM_COUNTRY_CODE . "' ";
		$this->sql .= "   AND (awards + hms) > 0 ";
		// $this->sql .= " LIMIT 5 ";

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

	protected function format_name($name) {
		$parts = preg_split("/\s+/", $name, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
		for ($i = 0; $i < sizeof($parts); ++ $i) {
			if (strlen($parts[$i]) == 1 || strlen($parts[$i]) > 3)
				$parts[$i] = ucfirst(strtolower($parts[$i]));
			else {
				// If the name comtains vowel after the first letter, it could be a proper name
				if (preg_match("/[aeiouy]/i", $parts[$i]))
					$parts[$i] = ucfirst(strtolower($parts[$i]));
				else
					$parts[$i] = strtoupper($parts[$i]);
			}
		}
		return implode(" ", $parts);
	}


	// Public Methods
	function getBackgroundImage() {
		return $this->salon_folder . "/img/" . $this->conf['design'];
	}

	function getMergeData() {
		if ($this->query == NULL)
			$this->query = mysqli_query($this->dbcon, $this->sql) or $this->sql_error($this->sql, mysqli_error($this->dbcon), __FILE__, __LINE__);
		if ($row = mysqli_fetch_array($this->query)) {
			$this->save_file_name = $this->conf['file_name_stub'] . "-" . $row['profile_id'] . self::SAVE_FILE_TYPE;
			$this->zip_file_name = "cd/" . $this->save_file_name;

			$row['certificates'] = $row['awards'] + $row['hms'];
			$row['address'] = $row['address_1'];

			foreach ($this->conf['picture'] as $idx => $config) {
				// If the field is part of configuration, use it
				if (isset($this->conf[$config['field']]))
					$this->conf['picture'][$idx]["value"] = $this->conf[$config['field']];
				else if (isset($row[$config['field']])) {
					// Otherwise check if field is found in row data fetched, use it
					$this->conf['picture'][$idx]["value"] = $row[$config['field']];
				}
			}
			return $this->conf['picture'];
		}
		return NULL;
	}

	function getZipFolder() {
		return $this->zip_folder;
	}

	function getZipName() {
		return $this->zip_name;
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
