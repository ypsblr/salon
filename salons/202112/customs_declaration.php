<?php
/*
** customs_declaration.php - Contains Print Definitions for generating CN23 Customs Declaration Forms
** A separate file is created for each person
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

	// Fonts
	const FONT_REGULAR = "GothamCond-Book.ttf";
	const FONT_BOLD = "GothamCond-Medium.ttf";
	const FONT_ITALIC = "GothamCond-LightItalic.ttf";
	const FONT_SIZE = 28;

	// Files and Folders
	const YEARMONTH = "202112";
	const INDIA = "101";
	const FILE_NAME_STUB = "IS2021-CN23";
	const BASE_IMAGE = "customs_declaration.jpg";

	const FROM_NAME = "C K Subramanya";
	const FROM_BUSINESS = "Not Applicable";
	const FROM_STREET = "153K, 19A Main, Rajaji Nagar I Blk";
	const FROM_CITY = "BANGALORE";
	const FROM_PIN = "560010";
	const FROM_PHONE = "+91-97400-22868";
	const FROM_COUNTRY = "INDIA";

	// Protected
	protected $salon_folder = "";
	protected $target_folder = "";
	protected $bg_image = "";
	protected $save_file_name = "";
	protected $save_file_type = ".jpg";
	protected $dbcon = "";
	protected $param = "";
	protected $sql = "";
	protected $query = NULL;
	protected $fields = [
							// Sender details
							array ("type" => "text", "field" => "from_name", "value" => self::FROM_NAME,
									"font" => self::FONT_BOLD, "font_size" => self::FONT_SIZE, "font_color" => self::COLOR_FIELD,
									"x" => 330, "y" => 95, "width" => 700, "height" => 45, "rotate" => 0, "position" => "LB",
									"fill_color" => "", "border_size" => 0, "border_color" => ""),

							array ("type" => "text", "field" => "from_business", "value" => self::FROM_BUSINESS,
									"font" => self::FONT_BOLD, "font_size" => self::FONT_SIZE, "font_color" => self::COLOR_FIELD,
									"x" => 365, "y" => 155, "width" => 700, "height" => 45, "rotate" => 0, "position" => "LB",
									"fill_color" => "", "border_size" => 0, "border_color" => ""),

							array ("type" => "text", "field" => "from_street", "value" => self::FROM_STREET,
									"font" => self::FONT_BOLD, "font_size" => self::FONT_SIZE, "font_color" => self::COLOR_FIELD,
									"x" => 325, "y" => 215, "width" => 500, "height" => 45, "rotate" => 0, "position" => "LB",
									"fill_color" => "", "border_size" => 0, "border_color" => ""),
							array ("type" => "text", "field" => "from_phone", "value" => self::FROM_PHONE,
									"font" => self::FONT_BOLD, "font_size" => self::FONT_SIZE, "font_color" => self::COLOR_FIELD,
									"x" => 935, "y" => 210, "width" => 340, "height" => 45, "rotate" => 0, "position" => "LB",
									"fill_color" => "", "border_size" => 0, "border_color" => ""),

							array ("type" => "text", "field" => "from_pin", "value" => self::FROM_PIN,
									"font" => self::FONT_BOLD, "font_size" => self::FONT_SIZE, "font_color" => self::COLOR_FIELD,
									"x" => 365, "y" => 270, "width" => 190, "height" => 45, "rotate" => 0, "position" => "LB",
									"fill_color" => "", "border_size" => 0, "border_color" => ""),
							array ("type" => "text", "field" => "from_city", "value" => self::FROM_CITY,
									"font" => self::FONT_BOLD, "font_size" => self::FONT_SIZE, "font_color" => self::COLOR_FIELD,
									"x" => 625, "y" => 270, "width" => 675, "height" => 45, "rotate" => 0, "position" => "LB",
									"fill_color" => "", "border_size" => 0, "border_color" => ""),

							array ("type" => "text", "field" => "from_country", "value" => self::FROM_COUNTRY,
									"font" => self::FONT_BOLD, "font_size" => self::FONT_SIZE, "font_color" => self::COLOR_FIELD,
									"x" => 345, "y" => 335, "width" => 950, "height" => 45, "rotate" => 0, "position" => "LB",
									"fill_color" => "", "border_size" => 0, "border_color" => ""),

							// Recipient Details
							array ("type" => "text", "field" => "profile_name", "value" => "",
									"font" => self::FONT_BOLD, "font_size" => self::FONT_SIZE, "font_color" => self::COLOR_FIELD,
									"x" => 330, "y" => 400, "width" => 700, "height" => 45, "rotate" => 0, "position" => "LB",
									"fill_color" => "", "border_size" => 0, "border_color" => ""),

							array ("type" => "text", "field" => "to_business", "value" => self::FROM_BUSINESS,
									"font" => self::FONT_BOLD, "font_size" => self::FONT_SIZE, "font_color" => self::COLOR_FIELD,
									"x" => 365, "y" => 465, "width" => 700, "height" => 45, "rotate" => 0, "position" => "LB",
									"fill_color" => "", "border_size" => 0, "border_color" => ""),

							array ("type" => "text", "field" => "address", "value" => "",
									"font" => self::FONT_BOLD, "font_size" => self::FONT_SIZE, "font_color" => self::COLOR_FIELD,
									"x" => 325, "y" => 520, "width" => 500, "height" => 45, "rotate" => 0, "position" => "LB",
									"fill_color" => "", "border_size" => 0, "border_color" => ""),
							array ("type" => "text", "field" => "phone", "value" => "",
									"font" => self::FONT_BOLD, "font_size" => self::FONT_SIZE, "font_color" => self::COLOR_FIELD,
									"x" => 935, "y" => 520, "width" => 340, "height" => 45, "rotate" => 0, "position" => "LB",
									"fill_color" => "", "border_size" => 0, "border_color" => ""),

							array ("type" => "text", "field" => "pin", "value" => "",
									"font" => self::FONT_BOLD, "font_size" => self::FONT_SIZE, "font_color" => self::COLOR_FIELD,
									"x" => 365, "y" => 585, "width" => 190, "height" => 45, "rotate" => 0, "position" => "LB",
									"fill_color" => "", "border_size" => 0, "border_color" => ""),
							array ("type" => "text", "field" => "city", "value" => "",
									"font" => self::FONT_BOLD, "font_size" => self::FONT_SIZE, "font_color" => self::COLOR_FIELD,
									"x" => 625, "y" => 585, "width" => 675, "height" => 45, "rotate" => 0, "position" => "LB",
									"fill_color" => "", "border_size" => 0, "border_color" => ""),

							array ("type" => "text", "field" => "country_name", "value" => "",
									"font" => self::FONT_BOLD, "font_size" => self::FONT_SIZE, "font_color" => self::COLOR_FIELD,
									"x" => 345, "y" => 645, "width" => 950, "height" => 45, "rotate" => 0, "position" => "LB",
									"fill_color" => "", "border_size" => 0, "border_color" => ""),

							// Items
							// Certificates
							array ("type" => "text", "field" => "item_certificates", "value" => "CERTIFICATE(S)",
									"font" => self::FONT_BOLD, "font_size" => self::FONT_SIZE, "font_color" => self::COLOR_FIELD,
									"x" => 230, "y" => 820, "width" => 575, "height" => 45, "rotate" => 0, "position" => "LM",
									"fill_color" => "", "border_size" => 0, "border_color" => ""),
							array ("type" => "text", "field" => "certificates", "value" => "",
									"font" => self::FONT_BOLD, "font_size" => self::FONT_SIZE, "font_color" => self::COLOR_FIELD,
									"x" => 850, "y" => 820, "width" => 240, "height" => 45, "rotate" => 0, "position" => "MM",
									"fill_color" => "", "border_size" => 0, "border_color" => ""),

							// Medals
							array ("type" => "text", "field" => "item_medals", "value" => "MEDAL(S)",
									"font" => self::FONT_BOLD, "font_size" => self::FONT_SIZE, "font_color" => self::COLOR_FIELD,
									"x" => 230, "y" => 880, "width" => 575, "height" => 45, "rotate" => 0, "position" => "LM",
									"fill_color" => "", "border_size" => 0, "border_color" => ""),
							array ("type" => "text", "field" => "awards", "value" => "",
									"font" => self::FONT_BOLD, "font_size" => self::FONT_SIZE, "font_color" => self::COLOR_FIELD,
									"x" => 850, "y" => 880, "width" => 240, "height" => 45, "rotate" => 0, "position" => "MM",
									"fill_color" => "", "border_size" => 0, "border_color" => ""),

							// Ribbons
							array ("type" => "text", "field" => "item_hms", "value" => "RIBBON(S)",
									"font" => self::FONT_BOLD, "font_size" => self::FONT_SIZE, "font_color" => self::COLOR_FIELD,
									"x" => 230, "y" => 940, "width" => 575, "height" => 45, "rotate" => 0, "position" => "LM",
									"fill_color" => "", "border_size" => 0, "border_color" => ""),
							array ("type" => "text", "field" => "hms", "value" => "",
									"font" => self::FONT_BOLD, "font_size" => self::FONT_SIZE, "font_color" => self::COLOR_FIELD,
									"x" => 850, "y" => 940, "width" => 240, "height" => 45, "rotate" => 0, "position" => "MM",
									"fill_color" => "", "border_size" => 0, "border_color" => ""),

							// Gift Check Mark
							array ("type" => "text", "field" => "gift_checkbox", "value" => "X",
									"font" => self::FONT_BOLD, "font_size" => 48, "font_color" => self::COLOR_FIELD,
									"x" => 210, "y" => 1190, "width" => 45, "height" => 45, "rotate" => 0, "position" => "MM",
									"fill_color" => "", "border_size" => 0, "border_color" => ""),

						];


	// Constructor
	function __construct($dbcon, $param) {
		$this->dbcon = $dbcon;
		$this->param = $param;

		$this->salon_folder = $_SERVER['DOCUMENT_ROOT'] . "/salons/" . self::YEARMONTH;
		$this->target_folder = $_SERVER['DOCUMENT_ROOT'] . "/generated/" . self::YEARMONTH . "/customs_declarations";
		$this->bg_image = $this->salon_folder . "/img/" . self::BASE_IMAGE;

		$this->sql  = "SELECT profile.profile_id, profile_name, phone, address_1, address_2, address_3, city, state, pin, ";
		$this->sql .= "       country_name, awards, hms ";
		$this->sql .= "  FROM entry, profile, country ";
		$this->sql .= " WHERE entry.yearmonth = '" . self::YEARMONTH ."' ";
		$this->sql .= "   AND profile.profile_id = entry.profile_id ";
		$this->sql .= "   AND country.country_id = profile.country_id ";
		$this->sql .= "   AND profile.country_id != '" . self::INDIA . "' ";
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
			$this->save_file_name = self::FILE_NAME_STUB . "-" . $row['profile_id'] . $this->save_file_type;

			$row['certificates'] = $row['awards'] + $row['hms'];
			$row['address'] = $row['address_1'];

			foreach ($this->fields as $field => $config) {
				if (isset($row[$config['field']])) {
					if ($config['field'] == 'picfile' && isset($row['section']))
						$this->fields[$field]["value"] = $this->salon_folder . "/upload/" . $row['section'] . "/" . $row['picfile'];
					else
						$this->fields[$field]["value"] = $row[$config['field']];
				}
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
