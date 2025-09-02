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
	// const COLOR_LABEL = "927C4D";
	// const COLOR_FIELD = "A93C3A";
	const COLOR_FIELD = "000000";
	const COLOR_SUBDUED = "808080";
	const COLOR_HIGHLIGHT = "F02020";
	const COLOR_GOLD = "A48438";
	const COLOR_WHITE = "FFFFFF";
	const COLOR_RED = "EE3537";
	const COLOR_MASK = "EFEFEF";
	// const COLOR_MASK = "ECEDEE";

	// Fonts
	const FONT_REGULAR = "GothamCond-Book.ttf";
	const FONT_BOLD = "GothamCond-Medium.ttf";
	const FONT_ITALIC = "GothamCond-LightItalic.ttf";

	// Files and Folders
	const YEARMONTH = "202208";
	const FILE_NAME_STUB = "AIS2022-TITLE-CARD";
	const BASE_IMAGE = "ais2022_titlecard.png";		// 1800w x 1200h - 6x3 inch card

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
							// Reference Image
							array ("type" => "image", "field" => "picfile", "value" => "",
									"x" => 0, "y" => 420, "width" => 240, "height" => 360, "rotate" => 0, "position" => "MM",
									"fill_color" => "", "border_size" => 0, "border_color" => "", "border_gap" => 0),
							// Avatar Box
							array ("type" => "box", "field" => "avatar", "value" => "",
									"x" => 350, "y" => 400, "width" => 380, "height" => 380, "rotate" => 0, "position" => "MM",
									"fill_color" => self::COLOR_MASK, "border_size" => 0, "border_color" => "", "border_gap" => 0),
							// Avatar Image
							array ("type" => "image", "field" => "avatar", "value" => "", "grow" => 2,
									"x" => 365, "y" => 415, "width" => 350, "height" => 350, "rotate" => 0, "position" => "MM",
									"fill_color" => "", "border_size" => 0, "border_color" => "", "border_gap" => 0),
							// array ("type" => "image", "field" => "avatar", "value" => "",
							// 		"x" => 1150, "y" => 450, "width" => 380, "height" => 380, "rotate" => 0, "position" => "MM",
							// 		"fill_color" => "", "border_size" => 0, "border_color" => "", "border_gap" => 0),
							// Profile Name
							array ("type" => "text", "field" => "profile_name", "value" => "",
									"font" => self::FONT_BOLD, "font_size" => 64, "font_color" => self::COLOR_FIELD,
									"x" => 865, "y" => 400, "width" => 800, "height" => 80, "rotate" => 0, "position" => "LT",
									"fill_color" => "", "border_size" => 0, "border_color" => ""),
							// Honors
							array ("type" => "text", "field" => "honors", "value" => "",
									"font" => self::FONT_ITALIC, "font_size" => 32, "font_color" => self::COLOR_SUBDUED,
									"x" => 865, "y" => 480, "width" => 800, "height" => 40, "rotate" => 0, "position" => "LT",
									"fill_color" => "", "border_size" => 0, "border_color" => ""),
							// Award Name
							array ("type" => "text", "field" => "award_name", "value" => "",
									"font" => self::FONT_BOLD, "font_size" => 48, "font_color" => self::COLOR_GOLD,
									"x" => 865, "y" => 560, "width" => 800, "height" => 60, "rotate" => 0, "position" => "LT",
									"fill_color" => "", "border_size" => 0, "border_color" => ""),
							// Picture Title
							array ("type" => "text", "field" => "title", "value" => "",
									"font" => self::FONT_BOLD, "font_size" => 64, "font_color" => self::COLOR_FIELD,
									"x" => 865, "y" => 640, "width" => 800, "height" => 80, "rotate" => 0, "position" => "LT",
									"fill_color" => "", "border_size" => 0, "border_color" => self::COLOR_GOLD, "border_gap" => 0),
							// Section
							array ("type" => "text", "field" => "section", "value" => "",
									"font" => self::FONT_BOLD, "font_size" => 80, "font_color" => self::COLOR_RED,
									"x" => 865, "y" => 760, "width" => 800, "height" => 100, "rotate" => 0, "position" => "LT",
									"fill_color" => "", "border_size" => 0, "border_color" => ""),
						];


	// Constructor
	function __construct($dbcon, $param) {
		$this->dbcon = $dbcon;
		$this->param = $param;

		$this->salon_folder = $_SERVER['DOCUMENT_ROOT'] . "/salons/" . self::YEARMONTH;
		$this->target_folder = $_SERVER['DOCUMENT_ROOT'] . "/generated/" . self::YEARMONTH . "/titlecards";
		$this->bg_image = $this->salon_folder . "/img/" . self::BASE_IMAGE;

		$this->sql  = "SELECT award.section, IFNULL(stub, 'SPL') AS stub, award_name, profile.profile_id, profile_name, ";
		$this->sql .= "       honors, avatar, pic_result.pic_id, picfile, title ";
		$this->sql .= "  FROM pic_result, profile, pic, award LEFT JOIN section ON section.yearmonth = award.yearmonth AND section.section = award.section ";
		$this->sql .= " WHERE award.yearmonth = '" . self::YEARMONTH ."' ";
		$this->sql .= "   AND award.award_type = 'pic' ";
		$this->sql .= "   AND award.level < 99 ";
		// $this->sql .= "   AND award.section != 'CONTEST' ";
		$this->sql .= "   AND pic_result.yearmonth = award.yearmonth ";
		$this->sql .= "   AND pic_result.award_id = award.award_id ";
		$this->sql .= "   AND profile.profile_id = pic_result.profile_id ";
		$this->sql .= "   AND pic.yearmonth = pic_result.yearmonth ";
		$this->sql .= "   AND pic.profile_id = pic_result.profile_id ";
		$this->sql .= "   AND pic.pic_id = pic_result.pic_id ";
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
			$this->save_file_name = sprintf("%s-%s-%04d-%02d%s", self::FILE_NAME_STUB, $row['stub'], $row['profile_id'], $row['pic_id'], $this->save_file_type);
			foreach ($this->fields as $field => $config) {
				if ($config['field'] == 'picfile' && isset($row['section'])) {
					$picfile = $this->salon_folder . "/upload/" . $row['section'] . "/" . $row['picfile'];
					$this->fields[$field]["value"] = $picfile;
					// list ($width, $height) = getimagesize($picfile);
					// if ($width > $height)  // Horizonal image - Rotate 90 degrees
					// 	$this->fields[$field]["rotate"] = 90;
					// else
					// 	$this->fields[$field]["rotate"] = 0;		// Vertical image

				}
				elseif ($config['field'] == 'avatar') {
					if ($row['avatar'] == "" || $row['avatar'] == "user.jpg")
						$this->fields[$field]["value"] = "";
					else {
						$avatar = $_SERVER['DOCUMENT_ROOT'] . "/res/avatar/" . $row['avatar'];
						$this->fields[$field]["value"] = $avatar;
					}
				}
				elseif ($config['field'] == "title")
					$this->fields[$field]["value"] = "'" . $row["title"] . "'";
				else
					$this->fields[$field]["value"] = isset($row[$config['field']]) ? $row[$config['field']] : "";
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
