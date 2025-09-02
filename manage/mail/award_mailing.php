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
	protected $award_postings;
	protected $posting_data;
	protected $status = "OK";
	protected $errmsg = "";
	protected $couriers = [
				array("id" => "india_post", "name" => "India Posts", "tracking_site" => "https://www.indiapost.gov.in",
								"in_person" => "no", "award" => "yes", "catalog" => "yes", "domestic" => "yes", "international" => "yes"),
				array("id" => "award_ceremony", "name" => "Award Ceremony", "tracking_site" => "",
								"in_person" => "yes", "award" => "yes", "catalog" => "no", "domestic" => "yes", "international" => "no"),
				array("id" => "handed_over", "name" => "Handed Over", "tracking_site" => "",
								"in_person" => "yes", "award" => "yes", "catalog" => "yes", "domestic" => "yes", "international" => "yes")
				];
	protected $remitters = [
				array("id" => "sbi", "name" => "State Bank of India", "domestic" => "yes", "international" => "no"),
				array("id" => "amazon", "name" => "Amazon India Gift Voucher", "domestic" => "yes", "international" => "no"),
				array("id" => "paypal", "name" => "Paypal", "domestic" => "no", "international" => "yes"),
				array("id" => "cash", "name" => "Paid in Cash", "domestic" => "yes", "international" => "no")
				];
	protected $status_codes = [
				array("id" => "not_sent", "name" => "Yet to send"),
				array("id" => "sent", "name" => "Sent"),
				array("id" => "received", "name" => "Received")
			];

	function __construct($DBCON, $yearmonth, $is_salon_archived) {
		$this->dbcon = $DBCON;
		$this->yearmonth = $yearmonth;
		$this->is_salon_archived = $is_salon_archived;

		if (file_exists("../../salons/$yearmonth/blob/posting.json")) {
			$json = json_decode(file_get_contents("../../salons/$yearmonth/blob/posting.json"), true);
			$this->award_postings = $json['award_mailing'];
		}
		else {
			$this->status = "failed";
			$this->errmsg = "Postings information not available.";
		}
	}

	private function get_name($array, $id) {
		foreach ($array as $row) {
			if ($row['id'] == $id)
				return $row['name'];
		}
		return $id;
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

		// Get Posting Data
		foreach ($this->award_postings as $posting) {
			if ($posting['profile_id'] == $profile_id) {
				if ($posting['status'] != 'not_sent' && $posting['tracking_number'] != "" && $posting['handed_over_to'] == "") {
					$this->posting_data = $posting;
				}
				else {
					$this->status = "skipped";
					$this->errmsg = "Tracking data is not available";
				}
				return;
			}
		}

		$this->status = "failed";
		$this->errmsg = "No posting data available";
		// $sql  = "SELECT * FROM postings ";
		// $sql .= " WHERE yearmonth = '$this->yearmonth' ";
		// $sql .= "   AND profile_id = '$this->profile_id' ";
		// $sql .= "   AND posting_type = 'AWARD' ";
		// $query = mysqli_query($this->dbcon, $sql)or sql_error($sql, mysqli_error($this->dbcon), __FILE__, __LINE__);
		// if (mysqli_num_rows($query) == 0) {
		// 	$this->status = "skipped";
		// 	$this->errmsg = "No posting data available";
		// }
		// else {
		// 	$this->posting_data = mysqli_fetch_array($query, MYSQLI_ASSOC);
		// }
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
			"posting-operator" => $this->get_name($this->couriers, $this->posting_data['courier']),
			"posting-date" => print_date($this->posting_data['date_of_posting']),
			"tracking-number" => $this->posting_data['tracking_number'],
			"tracking-website" => $this->posting_data['tracking_site']
		);
	}
}

?>
