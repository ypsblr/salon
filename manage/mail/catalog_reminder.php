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
	protected $salon;
	protected $has_not_ordered_catalog;
	protected $inr_price_exists;
	protected $inr_prices;
	protected $usd_price_exists;
	protected $usd_prices;
	protected $is_participant_indian;
	protected $is_participant_foreigner;

	protected $status = "OK";
	protected $errmsg = "";

	function __construct($DBCON, $yearmonth, $is_salon_archived) {
		$this->dbcon = $DBCON;
		$this->yearmonth = $yearmonth;
		$this->is_salon_archived = $is_salon_archived;

		// Get Catalog Data
		$sql  = "SELECT certificates_ready, catalog_release_date, catalog_ready, catalog, catalog_download, catalog_order_last_date, ";
		$sql .= "       catalog_price_in_inr, catalog_price_in_usd ";
		$sql .= " FROM contest WHERE yearmonth = '$this->yearmonth' ";
		$query = mysqli_query($this->dbcon, $sql)or sql_error($sql, mysqli_error($this->dbcon), __FILE__, __LINE__);
		$this->salon = mysqli_fetch_array($query, MYSQLI_ASSOC);
		// Build Pricing Tables
		$this->inr_price_exists = false;
		$this->inr_prices = [];
		$this->usd_price_exists = false;
		$this->usd_prices = [];
		if ($this->salon['catalog_price_in_inr'] != "") {
			$this->inr_price_exists = true;
			if (substr($this->salon['catalog_price_in_inr'], 0, 1) == "[") {
				// JSON List
				$this->inr_prices = json_decode($this->salon['catalog_price_in_inr'], true);
			}
			else {
				// Simple Prices
				list($price, $postage) = explode("|", $this->salon['catalog_price_in_inr']);
				$this->inr_prices = array("model" => "Salon Catalog", "price" => $price, "postage" => $postage);
			}
		}
		if ($this->salon['catalog_price_in_usd'] != "") {
			$this->usd_price_exists = true;
			if (substr($this->salon['catalog_price_in_usd'], 0, 1) == "[") {
				// JSON List
				$this->usd_prices = json_decode($this->salon['catalog_price_in_usd'], true);
			}
			else {
				// Simple Prices
				list($price, $postage) = explode("|", $this->salon['catalog_price_in_usd']);
				$this->usd_prices = array("model" => "Salon Catalog", "price" => $price, "postage" => $postage);
			}
		}

	}

	function initProfile($profile_id) {
		$this->profile_id = $profile_id;
		$entry_table = ($this->is_salon_archived ? "ar_entry" : "entry");
		$pic_table = ($this->is_salon_archived ? "ar_pic" : "pic");
		$pic_result_table = ($this->is_salon_archived ? "ar_pic_result" : "pic_result");

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
			$this->is_participant_indian = ($this->profile['country_id'] == '101');
			$this->is_participant_foreigner = ($this->profile['country_id'] != '101');
		}

		$sql  = "SELECT * FROM catalog_order WHERE yearmonth = '$this->yearmonth' AND profile_id = '$profile_id' ";
		$query = mysqli_query($this->dbcon, $sql)or sql_error($sql, mysqli_error($this->dbcon), __FILE__, __LINE__);
		$this->has_not_ordered_catalog = (mysqli_num_rows($query) == 0);
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

	protected function generate_catalog_list() {
		if ($this->inr_price_exists || $this->usd_price_exists) {
			$catalogs = ($this->is_participant_indian ? $this->inr_prices : $this->usd_prices);
			$currency = ($this->is_participant_indian ? "INR" : "USD");
			$m  = "<h3>List of Printed Catalogs</h3>";
			$m .= "<p>Printed Catalogs can be ordered from <b>My Page</b> on the Salon website or by using ";
			$m .= "<a href='" . $this->generate_catalog_order_link() . "'>this direct link</a>.</p>";
			$m .= "<table class='table-data'>";
			$m .= "<tr><th>Catalog Type</th><th>Catalog</th><th>Postage</th></tr>";
			foreach ($catalogs as $catalog) {
				$m .= "<tr>";
				$m .= "<td width='70%''>" . $catalog['model'] . "</td>";
				$m .= "<td>" . $catalog['price'] . " " . $currency . "</td>";
				$m .= "<td>" . $catalog['postage'] . " " . $currency . "</td>";
				$m .= "</tr>";
			}
			$m .= "</table>";
			return $m;
		}
		else {
			return "";
		}
	}

	protected function generate_catalog_order_link() {
		return "[salon-website]/catalog_order.php?code=" . encode_string_array($this->yearmonth . "|" . $this->profile_id);
	}

	function mailTo() {
		return $this->profile['email'];
	}

	function generatorPairs() {
		$pairs = array(
			"participant-name" => $this->profile['profile_name'],
			"can-order-catalog" => ($this->has_not_ordered_catalog && ($this->inr_price_exists || $this->usd_price_exists)),
			"catalog-order-last-date" => print_date($this->salon['catalog_order_last_date']),
			"catalog-list" => $this->generate_catalog_list(),
			"catalog-order-link" => $this->generate_catalog_order_link(),
			"catalog-file-view" => $this->salon['catalog'],
			"catalog-file-download" => $this->salon['catalog_download'],
		);
		return $pairs;
	}
}


?>
