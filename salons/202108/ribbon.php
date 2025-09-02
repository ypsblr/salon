<?php
/*
** certdef.php - Contains Print Definitions for the Certificate
** A separate file is created for each Salon depending upon Certificate Design
**
** Certificate Definition for All India Digital Salon 2020
*/
class ImageMerge {
	// Constants
	// Text Colors
	const COLOR_LABEL = "927C4D";
	// const COLOR_FIELD = "A93C3A";
	// const COLOR_FIELD = "000000";
	const COLOR_FIELD = "EE3537";		// Red color from logo
	const COLOR_SUBDUED = "808080";
	const COLOR_HIGHLIGHT = "F02020";

	// Fonts
	const FONT_REGULAR = "GothamCond-Book.ttf";
	const FONT_BOLD = "GothamCond-Medium.ttf";
	const FONT_ITALIC = "GothamCond-LightItalic.ttf";

	// Files and Folders
	const YEARMONTH = "202108";
	const FILE_NAME_STUB = "AIS2021-RIBBON";
	const BASE_IMAGE = "ais2021_ribbon.png";
	const RIBBON_AWARD = 9;
	const AWARD_ID_LIST = "'61', '62', '63', '64'";

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
							array ("type" => "text", "field" => "profile_name", "value" => "",
									"font" => self::FONT_BOLD, "font_size" => 64, "font_color" => self::COLOR_FIELD,
									"x" => 20, "y" => 780, "width" => 560, "height" => 560, "rotate" => 45, "position" => "MM",
									"fill_color" => "", "border_size" => 0, "border_color" => "")
						];


	// Constructor
	function __construct($dbcon, $param) {
		$this->dbcon = $dbcon;
		$this->param = $param;

		$this->salon_folder = $_SERVER['DOCUMENT_ROOT'] . "/salons/" . self::YEARMONTH;
		$this->target_folder = $_SERVER['DOCUMENT_ROOT'] . "/generated/" . self::YEARMONTH . "/ribbons";
		$this->bg_image = $this->salon_folder . "/img/" . self::BASE_IMAGE;

		$this->sql  = "SELECT award.section, profile.profile_id, profile_name, pic_result.pic_id ";
		$this->sql .= "  FROM award, pic_result, profile ";
		$this->sql .= " WHERE award.yearmonth = '" . self::YEARMONTH ."' ";
		$this->sql .= "   AND award.award_type = 'pic' ";
		$this->sql .= "   AND award.level = '" . self::RIBBON_AWARD . "' ";
		$this->sql .= "   AND award.award_id IN (" . self::AWARD_ID_LIST . ")";
		$this->sql .= "   AND award.section != 'CONTEST' ";
		if (isset($this->param['section']))
			$this->sql .= "   AND award.section = '" . $this->param['section'] . "' ";
		$this->sql .= "   AND pic_result.yearmonth = award.yearmonth ";
		$this->sql .= "   AND pic_result.award_id = award.award_id ";
		$this->sql .= "   AND profile.profile_id = pic_result.profile_id ";
		// $this->sql .= " LIMIT 1 ";
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
		die();
	}

	protected function sql_error($sql, $errmsg, $phpfile, $phpline) {
		$log_file = $_SERVER["DOCUMENT_ROOT"] . "/logs/sql_errlog.txt";
		file_put_contents($log_file, date("Y-m-d H:i") . ": SQL operation failed with message '$errmsg' in line $phpline of '$phpfile'" . chr(13) . chr(10), FILE_APPEND);
		file_put_contents($log_file, "Failing SQL: " . $sql . chr(13) . chr(10), FILE_APPEND);
		$_SESSION['err_msg'] = "SQL Operation failed. Please report to YPS to check using Contact Us page.";
		die();

	}

	// Public Methods
	function getBackgroundImage() {
		return $this->bg_image;
	}

	function getMergeData() {
		if ($this->query == NULL)
			$this->query = mysqli_query($this->dbcon, $this->sql) or $this->sql_error($this->sql, mysqli_error($this->dbcon), __FILE__, __LINE__);
		if ($row = mysqli_fetch_array($this->query)) {
			$this->save_file_name = self::FILE_NAME_STUB . "-" . $row['profile_id'] . "-" . $row['pic_id'] . $this->save_file_type;
			foreach ($this->fields as $field => $config) {
				if (isset($row[$config['field']]))
					$this->fields[$field]["value"] = $row[$config['field']];
			}
			return $this->fields;
		}
		return NULL;
	}

	function getSaveFolder() {
		return $this->target_folder;
	}

	function getSaveFileName() {
		return $this->save_file_name;
	}

}
