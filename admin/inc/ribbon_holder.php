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
	const RIBBON_AWARD = '9';

	// Protected
	protected $yearmonth = "";
	protected $section = "";
	protected $salon_folder = "";
	protected $zip_folder = "";
	protected $zip_name = "ribbon_holders.zip";
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

    function toConsole($data) {
        $output = $data;
        if (is_array($output))
            $output = implode(',', $output);
        echo "<script>console.log('Debug Objects: " . $output . "' );</script>";
    }

	// Constructor
	function __construct($dbcon, $param) {
		$this->dbcon = $dbcon;
		// $this->param = $param;

		if ( ! isset($param['yearmonth']))
			$this->return_error("Salon not specified", __FILE__, __LINE__);

		if (isset($param['section']))
			$this->section = decode_string_array($param['section']);

		$this->yearmonth = $param['yearmonth'];
		$this->salon_folder = $_SERVER['DOCUMENT_ROOT'] . "/salons/" . $this->yearmonth;

		$this->zip_folder = $_SERVER['DOCUMENT_ROOT'] . "/generated/" . $this->yearmonth . "/ribbon_holders";

		// Load Config JSON File
		if (file_exists($this->salon_folder . "/blob/ribbon_holder.json")) {
			$this->conf = json_decode(file_get_contents($this->salon_folder . "/blob/ribbon_holder.json"), true);
			if (json_last_error() != JSON_ERROR_NONE)
				$this->return_error("Ribbon Holder Configuration Corrupt. Use Salon Manage to create a Ribbon Holder Configuration.", __FILE__, __LINE__);
				
			//$this->toConsole($this->yearmonth);
			//$this->toConsole($this->conf['yearmonth']);
			
			if ($this->yearmonth != $this->conf['yearmonth'])
				$this->return_error("Ribbon Holder Configuration File does not relate to this contest.", __FILE__, __LINE__);
		}
		else
			$this->return_error("Ribbon Holder Configuration File ($this->salon_folder/blob/ribbon_holder.json) not found. Use Salon Manage to create a Ribbon Holder Configuration.", __FILE__, __LINE__);

		if (! file_exists($this->salon_folder . "/img/" . $this->conf['design']))
			$this->return_error("Ribbon Holder Background Design Image File not found.", __FILE__, __LINE__);

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
				if (isset($field['x'])) {
					if (isset($this->conf['bleed']))
						$this->conf['picture'][$idx]['x'] += $this->conf['bleed'];
				}
				if (isset($field['y'])) {
					if (isset($this->conf['bleed']))
						$this->conf['picture'][$idx]['y'] += $this->conf['bleed'];
				}
			}
		}

		$this->sql  = "SELECT award.section, award_name, recognition_code, profile.profile_id, profile_name, honors, pic_result.pic_id, picfile, title ";
		$this->sql .= "  FROM award, pic_result, profile, pic ";
		$this->sql .= " WHERE award.yearmonth = '" . $this->yearmonth ."' ";
		$this->sql .= "   AND award.award_type = 'pic' ";
		$this->sql .= "   AND award.level = '" . self::RIBBON_AWARD . "' ";
		$this->sql .= "   AND award.section != 'CONTEST' ";
		if ($this->section != "")
			$this->sql .= "   AND award.section = '" . $this->section . "' ";
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

	protected function format_title($title) {
		return ucwords(strtolower($title));
	}

	protected function honors_text($honors, $profile_id = 0) {
		$list_of_honors = [
		// RPS
		array("orgn" => "RPS", "group" => "A", "pattern" => "FRPS", "distinction" => "FRPS"),
		array("orgn" => "RPS", "group" => "A", "pattern" => "ARPS", "distinction" => "ARPS"),
		array("orgn" => "RPS", "group" => "A", "pattern" => "LRPS", "distinction" => "LRPS"),

		// PSA
		array("orgn" => "PSA", "group" => "B", "pattern" => "Hon[-.: ]*FPSA", "distinction" => "HonFPSA"),
		array("orgn" => "PSA", "group" => "B", "pattern" => "Hon[-.: ]*PSA", "distinction" => "HonPSA"),
		array("orgn" => "PSA", "group" => "B", "pattern" => "FPSA", "distinction" => "FPSA"),
		array("orgn" => "PSA", "group" => "B", "pattern" => "APSA", "distinction" => "APSA"),
		array("orgn" => "PSA", "group" => "A", "pattern" => "GMPSA[-.: ]*pl", "distinction" => "GMPSA/P"),
		array("orgn" => "PSA", "group" => "A", "pattern" => "GMPSA\/p", "distinction" => "GMPSA/P"),
		array("orgn" => "PSA", "group" => "A", "pattern" => "GMPSA[-.: ]*go", "distinction" => "GMPSA/G"),
		array("orgn" => "PSA", "group" => "A", "pattern" => "GMPSA\/g", "distinction" => "GMPSA/G"),
		array("orgn" => "PSA", "group" => "A", "pattern" => "GMPSA[-.: ]*si", "distinction" => "GMPSA/S"),
		array("orgn" => "PSA", "group" => "A", "pattern" => "GMPSA\/s", "distinction" => "GMPSA/S"),
		array("orgn" => "PSA", "group" => "A", "pattern" => "GMPSA[-.: ]*br", "distinction" => "GMPSA/B"),
		array("orgn" => "PSA", "group" => "A", "pattern" => "GMPSA\/b", "distinction" => "GMPSA/B"),
		array("orgn" => "PSA", "group" => "A", "pattern" => "GMPSA", "distinction" => "GMPSA"),
		array("orgn" => "PSA", "group" => "A", "pattern" => "MPSA2", "distinction" => "MPSA2"),
		array("orgn" => "PSA", "group" => "A", "pattern" => "MPSA", "distinction" => "MPSA"),
		array("orgn" => "PSA", "group" => "A", "pattern" => "EPSA", "distinction" => "EPSA"),
		array("orgn" => "PSA", "group" => "A", "pattern" => "PPSA", "distinction" => "PPSA"),
		array("orgn" => "PSA", "group" => "A", "pattern" => "QPSA", "distinction" => "QPSA"),

		// FIAP
		array("orgn" => "FIAP", "group" => "C", "pattern" => "Hon[-. ]*EFIAP", "distinction" => "HonEFIAP"),
		array("orgn" => "FIAP", "group" => "B", "pattern" => "ES[-. ]*FIAP", "distinction" => "ESFIAP"),
		array("orgn" => "FIAP", "group" => "A", "pattern" => "MFIAP", "distinction" => "MFIAP"),
		array("orgn" => "FIAP", "group" => "A", "pattern" => "EFIAP[-.:\/ ]*di[^, ]*3", "distinction" => "EFIAP/d3"),
		array("orgn" => "FIAP", "group" => "A", "pattern" => "EFIAP[-.:\/ ]*d3", "distinction" => "EFIAP/d3"),
		array("orgn" => "FIAP", "group" => "A", "pattern" => "EFIAP[-.:\/ ]*di[^, ]*2", "distinction" => "EFIAP/d2"),
		array("orgn" => "FIAP", "group" => "A", "pattern" => "EFIAP[-.:\/ ]*d2", "distinction" => "EFIAP/d2"),
		array("orgn" => "FIAP", "group" => "A", "pattern" => "EFIAP[-.:\/ ]*di[^, ]*1", "distinction" => "EFIAP/d1"),
		array("orgn" => "FIAP", "group" => "A", "pattern" => "EFIAP[-.:\/ ]*d1", "distinction" => "EFIAP/d1"),
		array("orgn" => "FIAP", "group" => "A", "pattern" => "EFIAP[-.:\/ ]*d[^123]", "distinction" => "EFIAP/d"),
		array("orgn" => "FIAP", "group" => "A", "pattern" => "EFIAP[-.:\/ ]*pl", "distinction" => "EFIAP/p"),
		array("orgn" => "FIAP", "group" => "A", "pattern" => "EFIAP[-.:\/ ]*p", "distinction" => "EFIAP/p"),
		array("orgn" => "FIAP", "group" => "A", "pattern" => "EFIAP[-.:\/ ]*go", "distinction" => "EFIAP/g"),
		array("orgn" => "FIAP", "group" => "A", "pattern" => "EFIAP[-.:\/ ]*g", "distinction" => "EFIAP/g"),
		array("orgn" => "FIAP", "group" => "A", "pattern" => "EFIAP[-.:\/ ]*si", "distinction" => "EFIAP/s"),
		array("orgn" => "FIAP", "group" => "A", "pattern" => "EFIAP[-.:\/ ]*s", "distinction" => "EFIAP/s"),
		array("orgn" => "FIAP", "group" => "A", "pattern" => "EFIAP[-.:\/ ]*br", "distinction" => "EFIAP/b"),
		array("orgn" => "FIAP", "group" => "A", "pattern" => "EFIAP[-.:\/ ]*b", "distinction" => "EFIAP/b"),
		array("orgn" => "FIAP", "group" => "A", "pattern" => "EFIAP", "distinction" => "EFIAP"),
		array("orgn" => "FIAP", "group" => "A", "pattern" => "AFIAP", "distinction" => "AFIAP"),

		// FIP
		array("orgn" => "FIP", "group" => "B", "pattern" => "ESEFIP", "distinction" => "ESEFIP"),
		array("orgn" => "FIP", "group" => "B", "pattern" => "Hon[-. ]*FIP", "distinction" => "Hon.FIP"),
		array("orgn" => "FIP", "group" => "A", "pattern" => "EFIP[-.: ]*pl[-.:\/ ]*n", "distinction" => "EFIP/p(Nature)"),
		array("orgn" => "FIP", "group" => "A", "pattern" => "EFIP\/p[-.:\/ ]*n", "distinction" => "EFIP/p(Nature)"),
		array("orgn" => "FIP", "group" => "A", "pattern" => "EFIP[-.: ]*go[-.:\/ ]*n", "distinction" => "EFIP/g(Nature)"),
		array("orgn" => "FIP", "group" => "A", "pattern" => "EFIP\/g[-.:\/ ]*n", "distinction" => "EFIP/g(Nature)"),
		array("orgn" => "FIP", "group" => "A", "pattern" => "MFIP[-.:\/ ]*n", "distinction" => "MFIP Nature"),
		array("orgn" => "FIP", "group" => "A", "pattern" => "MFIP", "distinction" => "MFIP"),
		array("orgn" => "FIP", "group" => "A", "pattern" => "EFIP[-.: ]*pl[^n]*[,; ]", "distinction" => "EFIP/p"),
		array("orgn" => "FIP", "group" => "A", "pattern" => "EFIP\/p", "distinction" => "EFIP/p"),
		array("orgn" => "FIP", "group" => "A", "pattern" => "EFIP[-.: ]*go[^n]*[,; ]", "distinction" => "EFIP/g"),
		array("orgn" => "FIP", "group" => "A", "pattern" => "EFIP\/g", "distinction" => "EFIP/g"),
		array("orgn" => "FIP", "group" => "A", "pattern" => "EFIP[-.: ]*si", "distinction" => "EFIP/s"),
		array("orgn" => "FIP", "group" => "A", "pattern" => "EFIP\/s", "distinction" => "EFIP/s"),
		array("orgn" => "FIP", "group" => "A", "pattern" => "EFIP[-.: ]*br", "distinction" => "EFIP/b"),
		array("orgn" => "FIP", "group" => "A", "pattern" => "EFIP\/b", "distinction" => "EFIP/b"),
		array("orgn" => "FIP", "group" => "A", "pattern" => "EFIP", "distinction" => "EFIP"),
		array("orgn" => "FIP", "group" => "A", "pattern" => "AFIP", "distinction" => "AFIP"),
		array("orgn" => "FIP", "group" => "A", "pattern" => "FFIP", "distinction" => "FFIP"),

		// ICS
		array("orgn" => "ICS", "group" => "C", "pattern" => "HON[-.: ]*PIPICS", "distinction" => "HON.PIPICS"),
		array("orgn" => "ICS", "group" => "B", "pattern" => "HON[-.: ]*MICS", "distinction" => "HON.MICS"),
		array("orgn" => "ICS", "group" => "B", "pattern" => "HON[-.: ]*FICS", "distinction" => "HON.FICS"),
		array("orgn" => "ICS", "group" => "A", "pattern" => "GWP[-.: ]*ICS", "distinction" => "GWP.ICS"),
		array("orgn" => "ICS", "group" => "A", "pattern" => "ICS[-.: ]*SAFIIRI", "distinction" => "ICS.SAFIIRI"),
		array("orgn" => "ICS", "group" => "A", "pattern" => "GM[-.: ]*ICS", "distinction" => "GM.ICS"),
		array("orgn" => "ICS", "group" => "A", "pattern" => "BP[-.: ]*ICS", "distinction" => "BP.ICS"),
		array("orgn" => "ICS", "group" => "A", "pattern" => "HON[-.: ]*EICS", "distinction" => "HON.EICS"),
		array("orgn" => "ICS", "group" => "A", "pattern" => "MICS\/g", "distinction" => "MICS/g"),
		array("orgn" => "ICS", "group" => "A", "pattern" => "MICS\/s", "distinction" => "MICS/s"),
		array("orgn" => "ICS", "group" => "A", "pattern" => "MICS\/b", "distinction" => "MICS/b"),
		array("orgn" => "ICS", "group" => "A", "pattern" => "MICS", "distinction" => "MICS"),
		array("orgn" => "ICS", "group" => "A", "pattern" => "FICS", "distinction" => "FICS"),
		array("orgn" => "ICS", "group" => "A", "pattern" => "AICS", "distinction" => "AICS"),

		// GPU
		array("orgn" => "GPU", "group" => "H", "pattern" => "GPU.*ze.*", "distinction" => "GPU.ZEUS"),
		array("orgn" => "GPU", "group" => "G", "pattern" => "GPU.*her.*", "distinction" => "GPU.HERMES"),
		array("orgn" => "GPU", "group" => "F", "pattern" => "GPU.*aph.*", "distinction" => "GPU.APHRODITE"),

		array("orgn" => "GPU", "group" => "B", "pattern" => "GPU.*vip.*5", "distinction" => "GPU.VIP5"),
		array("orgn" => "GPU", "group" => "B", "pattern" => "GPU.*vip.*4", "distinction" => "GPU.VIP4"),
		array("orgn" => "GPU", "group" => "B", "pattern" => "GPU.*vip.*3", "distinction" => "GPU.VIP3"),
		array("orgn" => "GPU", "group" => "B", "pattern" => "GPU.*vip.*2", "distinction" => "GPU.VIP2"),
		array("orgn" => "GPU", "group" => "B", "pattern" => "GPU.*vip.*1", "distinction" => "GPU.VIP1"),
		// array("orgn" => "GPU", "pattern" => "GPU.*vip[^12345]*", "distinction" => "GPU.VIP1"),

		array("orgn" => "GPU", "group" => "A", "pattern" => "GPU.*cr.*5", "distinction" => "GPU.CR5"),
		array("orgn" => "GPU", "group" => "A", "pattern" => "GPU.*cr.*4", "distinction" => "GPU.CR4"),
		array("orgn" => "GPU", "group" => "A", "pattern" => "GPU.*cr.*3", "distinction" => "GPU.CR3"),
		array("orgn" => "GPU", "group" => "A", "pattern" => "GPU.*cr.*2", "distinction" => "GPU.CR2"),
		array("orgn" => "GPU", "group" => "A", "pattern" => "GPU.*cr.*1", "distinction" => "GPU.CR1"),
		// array("orgn" => "GPU", "pattern" => "GPU.*cr[^12345]*", "distinction" => "GPU.CR1"),

		// MOL
		array("orgn" => "GPU", "group" => "B", "pattern" => "[^c*]MoL(\*){3}", "distinction" => "MoL***"),
		array("orgn" => "GPU", "group" => "B", "pattern" => "[^c*]MoL(\*){2}", "distinction" => "MoL**"),
		array("orgn" => "GPU", "group" => "B", "pattern" => "[^c*]MoL(\*){1}", "distinction" => "MoL*"),
		array("orgn" => "GPU", "group" => "B", "pattern" => "[^c*]MoL", "distinction" => "MoL"),
		array("orgn" => "GPU", "group" => "A", "pattern" => "c(\*){3}MoL", "distinction" => "c***MoL"),
		array("orgn" => "GPU", "group" => "A", "pattern" => "c(\*){2}MoL", "distinction" => "c**MoL"),
		array("orgn" => "GPU", "group" => "A", "pattern" => "c(\*){1}MoL", "distinction" => "c*MoL"),
		array("orgn" => "GPU", "group" => "A", "pattern" => "cMoL", "distinction" => "cMoL"),

		// IUP
		array("orgn" => "IUP", "group" => "A", "pattern" => "GAIUP", "distinction" => "GAIUP"),
		array("orgn" => "IUP", "group" => "A", "pattern" => "HIUP", "distinction" => "HIUP"),
		array("orgn" => "IUP", "group" => "A", "pattern" => "MIUP", "distinction" => "MIUP"),
		array("orgn" => "IUP", "group" => "A", "pattern" => "EIUP", "distinction" => "EIUP"),

		];

		$honors_exception_list = [];

		if ($profile_id != 0 && isset($honors_exception_list[$profile_id]))
	        return $honors_exception_list[$profile_id];

	    $hlist = [];
	    $cur_orgn = "";
	    $cur_group = "";
	    $match_found = false;
	    foreach ($list_of_honors as $hon) {
	        if ($hon["orgn"] == $cur_orgn && $hon["group"] == $cur_group && $match_found)
	            continue;
	        if ($hon["orgn"] != $cur_orgn || $hon["group"] != $cur_group) {
	            $cur_orgn = $hon["orgn"];
	            $cur_group = $hon["group"];
	            $match_found = false;
	        }
	        if (preg_match("/[,; ]*" . $hon["pattern"] . "[,; ]*/i", $honors)) {
	            $hlist[] = $hon["distinction"];
	            $match_found = true;
	        }
	    }
	    return implode(", ", $hlist);
	}

	protected function safe_name ($str) {
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

	// Public Methods
	function getBackgroundImage() {
		return $this->salon_folder . "/img/" . $this->conf['design'];
	}

	function getMergeData() {
		if ($this->query == NULL)
			$this->query = mysqli_query($this->dbcon, $this->sql) or $this->sql_error($this->sql, mysqli_error($this->dbcon), __FILE__, __LINE__);
		if ($row = mysqli_fetch_array($this->query)) {

			$this->target_folder = $this->zip_folder . "/" . $row['section'];
			$this->save_file_name = $this->conf['file_name_stub'] . "-" . $row['recognition_code'] . "-" . $row['profile_id'] . "-" . $row['pic_id'] . self::SAVE_FILE_TYPE;
			$this->zip_file_name = $row['section'] . "/" . $this->save_file_name;

			foreach ($this->conf['picture'] as $index => $config) {
				if ($config['field'] == 'picfile' && isset($row['section']))
					$this->conf['picture'][$index]["value"] = $this->salon_folder . "/upload/" . $row['section'] . "/" . $row['picfile'];
				elseif ($config['field'] == 'profile_name')
					$this->conf['picture'][$index]["value"] = $this->format_name($row['profile_name']);
				elseif ($config['field'] == 'honors')
					$this->conf['picture'][$index]["value"] = $this->honors_text($row['honors']);
				elseif ($config['field'] == 'title')
					$this->conf['picture'][$index]["value"] = $this->format_title($row['title']);
				// elseif (isset($config["function"]) && $config["function"] != "") {
				// 	$function = $config["function"];
				// 	$this->conf['picture'][$field]["value"] = $function($row[$config['field']]);
				// }
				else
					$this->conf['picture'][$index]["value"] = isset($row[$config['field']]) ? $row[$config['field']] : "";
			}
			return $this->conf['picture'];
		}
		return NULL;
	}

	function getZipFolder() {
		return $this->zip_folder;
	}

	function getZipName() {
		if ($this->section == "")
			return $this->zip_name;
		else
			return $this->safe_name($this->section) . "_" . $this->zip_name;
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
