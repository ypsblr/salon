<?php
include_once("connect.php");
include_once("lib.php");

// Generator for Salon Opening emails
// Global Variables
class MailGenerator {
	protected $dbcon;
	protected $yearmonth;
	protected $is_salon_archived;
	protected $profile_id;
	protected $profile;
	protected $status = "OK";
	protected $errmsg = "";

	function __construct($DBCON, $yearmonth, $is_salon_archived) {
		$this->dbcon = $DBCON;
		$this->yearmonth = $yearmonth;
		$this->is_salon_archived = $is_salon_archived;
	}

	function initProfile($profile_id) {
		$this->profile_id = $profile_id;
		$entry_table = ($this->is_salon_archived ? "ar_entry" : "entry");
		// Get Profile Details
		$sql  = "SELECT * FROM profile, $entry_table AS entry ";
		$sql .= " WHERE profile.profile_id = '$this->profile_id' ";
		$sql .= "   AND entry.yearmonth = '$this->yearmonth' ";
		$sql .= "   AND entry.profile_id = profile.profile_id ";
		$query = mysqli_query($this->dbcon, $sql)or sql_error($sql, mysqli_error($this->dbcon), __FILE__, __LINE__);
		if (mysqli_num_rows($query) == 0) {
			$this->status = "failed";
			$this->errmsg = "Participant not found/registered for salon";
		}
		else {
			$this->profile = mysqli_fetch_array($query, MYSQLI_ASSOC);
		}
	}

	function generateHeader() {
		$header = <<<HTML
		<html>
		<head>
		<style type="text/css">

		.tableContent img {
			border: 0 !important;
			display: block !important;
			outline: none !important;
		}
		p, ul, li {
			color:#5C5A5A;
			font-size:14px;
			line-height:21px;
			margin:0;
			padding: 8px;
		}
		p a {
			color:#1f8544;
			font-size:14px;
			line-height:21px;
			margin:0;
			padding: 8px;
		}
		h2,h1 {
			color:#555;
			font-size:24px;
			line-height:30px;
			font-weight:bold;
			margin: 8px 0px 4px 0px;
		}
		h3 {
			color:#555;
			font-size:18px;
			line-height:24px;
			font-weight:bold;
			margin: 8px 0px 4px 0px;
		}

		table .table-data {
			margin: 0;
			width: 100%;
			padding: 8px;
			border-collapse: collapse;
		}

		table .table-data td, th  {
			padding: 8px;
			border-top: 1px solid #aaa;
			border-bottom: 1px solid #aaa;
		}

		table .table-data td img {
			padding-right:10px;
			padding-left: 10px;
			max-width: 80px;
		}

		h2.white {
			color:#ffffff;
		}

		img.img-avatar {
			width : 120px;
		}

		</style>
		</head>

		<body style="width: 100%; background-color : #efe6bd;">
HTML;
		return $header;
	}

	function generateFooter() {
		return "</body></html>";
	}

	function getStatus() {
		return [$this->status, $this->errmsg];
	}

	function mailTo() {
		return $this->profile['email'];
	}

	function generatorPairs() {
		return array(
			"participant-name" => $this->profile['profile_name'],
			"phone" => $this->profile['pbone'],
			"avatar" => "[salon-website]/res/avatar/" . $this->profile['avatar'],
			"digital-sections" => $this->profile['digital_sections'],
			"print-sections" => $this->profile['print_sections'],
			"uploads" => $this->profile['uploads'],
			"medals" => $this->profile['awards'],
			"honorable-mentions" => $this->profile['hms'],
			"acceptances" => $this->profile['awards'] + $this->profile['hms'] + $this->profile['acceptances'],
			// "banner-image" => http_method() . SERVER_ADDRESS . "/salons/" . $this->yearmonth . "/img/salon_mail_banner.jpg",
		);
	}
}


?>
