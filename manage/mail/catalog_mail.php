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
	protected $exhibition;
	protected $chair_exists;
	protected $inr_price_exists;
	protected $inr_prices;
	protected $usd_price_exists;
	protected $usd_prices;
	protected $is_participant_indian;
	protected $is_participant_foreigner;
	protected $has_individual_awards;
	protected $has_picture_awards;
	protected $award_money;
	protected $num_mementos;
	protected $num_certificates;

	protected $status = "OK";
	protected $errmsg = "";

	function __construct($DBCON, $yearmonth, $is_salon_archived) {
		$this->dbcon = $DBCON;
		$this->yearmonth = $yearmonth;
		$this->is_salon_archived = $is_salon_archived;

		// Get Exhibition-details
		$sql = "SELECT * FROM exhibition WHERE yearmonth = '$this->yearmonth' ";
		$query = mysqli_query($this->dbcon, $sql)or sql_error($sql, mysqli_error($this->dbcon), __FILE__, __LINE__);
		$this->exhibition = mysqli_fetch_array($query, MYSQLI_ASSOC);
		$dignitory_names = explode("|", $this->exhibition['dignitory_names']);
		$this->chair_exists = (! empty($dignitory_names[0]));

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

		$this->award_money = 0;
		$this->num_mementos = 0;
		$this->num_certificates = 0;
		$this->has_individual_awards = false;
		$this->has_picture_awards = false;

		// Check if there is award money to be remitted to the participant
		$sql  = "SELECT COUNT(*) AS individual_awards, SUM(cash_award) AS award_money, SUM(has_certificate) AS num_certificates, ";
		$sql .= "       SUM(has_memento) AS num_mementos ";
		$sql .= " FROM award, entry_result ";
		$sql .= " WHERE entry_result.yearmonth = '$this->yearmonth' ";
		$sql .= "   AND entry_result.profile_id = '$this->profile_id' ";
		$sql .= "   AND award.yearmonth = entry_result.yearmonth ";
		$sql .= "   AND award.award_id = entry_result.award_id ";
		$sql .= "   AND award.award_type = 'entry' ";
		$query = mysqli_query($this->dbcon, $sql)or sql_error($sql, mysqli_error($this->dbcon), __FILE__, __LINE__);
		if ($res = mysqli_fetch_array($query)) {
			if ($res['individual_awards'] > 0)
				$this->has_individual_awards = true;
			$this->award_money += $res['award_money'];
			$this->num_mementos += $res['num_mementos'];
			$this->num_certificates += $res['num_certificates'];
		}

		// check for picture awards
		$sql  = "SELECT COUNT(*) AS picture_awards, SUM(cash_award) AS award_money, SUM(has_certificate) AS num_certificates, ";
		$sql .= "       SUM(has_memento) AS num_mementos ";
		$sql .= "  FROM $pic_result_table AS pic_result, award ";
		$sql .= " WHERE pic_result.yearmonth = '$this->yearmonth' ";
		$sql .= "   AND pic_result.profile_id = '$this->profile_id' ";
		$sql .= "   AND award.yearmonth = pic_result.yearmonth ";
		$sql .= "   AND award.award_id = pic_result.award_id ";
		$sql .= "   AND award.award_type = 'pic' ";
		$sql .= "   AND award.level != 99 ";
		$query = mysqli_query($this->dbcon, $sql)or sql_error($sql, mysqli_error($this->dbcon), __FILE__, __LINE__);
		if ($res = mysqli_fetch_array($query)) {
			if ($res['picture_awards'] > 0)
				$this->has_picture_awards = true;
			$this->award_money += $res['award_money'];
			$this->num_mementos += $res['num_mementos'];
			$this->num_certificates += $res['num_certificates'];
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
			text-align: left;
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
			$m  = "<h3>Ordering Printed Catalogs</h3>";
			$m .= "<p>You can receive any of the printed catalogs listed below by ordering the catalog using the link provided below.</p>";
			$m .= "<table class='table-data'>";
			$m .= "<tr><th>Catalog Type</th><th>Price</th><th>Postage</th></tr>";
			foreach ($catalogs as $catalog) {
				$m .= "<tr>";
				$m .= "<td width='70%'>" . $catalog['model'] . "</td>";
				$m .= "<td>" . $catalog['price'] . " " . $currency . "</td>";
				if ($catalog['postage'] == 0)
					$m .= "<td>Included in the price</td>";
				else
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

	protected function generate_account_data() {
		$m = "";
		if ($this->award_money > 0) {
			$m .= "<h3>Remittance of Award Money</h3>";
			$m .= "<p style='text-align: justify;'>One or more of your pictures has Cash Award. ";
			$m .= "Cash Award will be distributed by money transfer to the Bank Account you have specified in your profile. </p>";
			if ($this->profile['bank_account_number'] == "") {
				$m .= "<p><b>YOU ARE YET TO UPDATE BANK ACCOUNT DETAILS</b> on the Salon web-site. Please send the following details ";
				$m .= "by email to <a href='mailto:salon@ypsbengaluru.in'>salon@ypsbengaluru.in</a> for updating the account.";
				$m .= "</p>";
				$m .= "<ul>";
				$m .= "<li>Name as in the Account</li>";
				$m .= "<li>Account Number</li>";
				$m .= "<li>Account Type (Saving/Current)</li>";
				$m .= "<li>Bank Name</li>";
				$m .= "<li>Branch Name</li>";
				$m .= "<li>IFSC Code</li>";
				$m .= "</ul>";
			}
			else {
				$m .= "Reproducing the Bank Account Details updated by you for verification:</p>";
				$m .= "<table class='table-data'>";
				$m .= "<tr><td><p>Name as in the Account</p></td><td><p>" . $this->profile['bank_account_name'] . "</p></td></tr>";
				$m .= "<tr><td><p>Account Number</p></td><td><p>..." . $this->profile['bank_account_number'] . "</p></td></tr>";
				$m .= "<tr><td><p>Account Type</p></td><td><p>" . $this->profile['bank_account_type'] . "</p></td></tr>";
				$m .= "<tr><td><p>Bank</p></td><td><p>" . $this->profile['bank_name'] . "</p></td></tr>";
				$m .= "<tr><td><p>Branch</p></td><td><p>" . $this->profile['bank_branch'] . "</p></td></tr>";
				$m .= "<tr><td><p>IFSC Code</p></td><td><p>" . $this->profile['bank_ifsc_code'] . "</p></td></tr>";
				$m .= "</table>";
				$m .= "<p>If any of these need to be corrected, please send an email to <a href='mailto:salon@ypsbengaluru.in'>salon@ypsbengaluru.in</a> immediately.</p>";
			}
		}
		return $m;
	}

	protected function generate_mailing_data() {
		$m  = "<h3>Mailing of Awards</h3>";
		$m .= "<p>You have one or more awards or honorable mentions in this salon. ";
		$m .= "We have commenced mailing of medals and ribbons to the postal address ";
		$m .= "specified by you in your profile. <b>Login and verify the address in your profile ";
		$m .= "for completeness and correctness</b>.</p>";
		return $m;
	}

	protected function generate_salon_actions() {
		$cert_link = "[salon-website]/op/certificate.php?cert=" . encode_string_array($this->yearmonth . "|PROFILE|" . $this->profile_id . "|ALL");
		$order_link = "[salon-website]/catalog_order.php?code=" . encode_string_array($this->yearmonth . "|" . $this->profile_id);

		$m  = "<table>";
		// Row 1
		$m .= "<tr>";
		// View Catalog
		$m .= "<td>";
		$m .= "<a href='[salon-website]/viewer/catalog.php?id=[yearmonth]&catalog=[catalog-file-view]'>";
		$m .= "<img src='[salon-website]/salons/[yearmonth]/img/catalog_mail_view.png' alt='View Catalog'>";
		$m .= "</a>";
		$m .= "</td>";
		// Download Catalog
		$m .= "<td>";
		$m .= "<a href='[salon-website]/catalog/[catalog-file-download]' download >";
		$m .= "<img src='[salon-website]/salons/[yearmonth]/img/catalog_mail_download.png' alt='Download Catalog'>";
		$m .= "</a>";
		$m .= "</td>";

		$m .= "</tr>";

		// Row 2
		$m .= "<tr>";
		$cells = 0;
		if ( ($this->is_participant_indian && $this->inr_price_exists) || ($this->is_participant_foreigner && $this->usd_price_exists) ) {
			$m .= "<td>";
			$m .= "<a href='" . $order_link. "'>";
			$m .= "<img src='[salon-website]/salons/[yearmonth]/img/catalog_mail_order.png' alt='Ordering Printed Catalog'>";
			$m .= "</a>";
			$m .= "</td>";
			++ $cells;
		}
		if ( ($this->profile['awards'] + $this->profile['hms'] + $this->profile['acceptances']) > 0 && $this->salon['certificates_ready'] == '1') {
			$m .= "<td>";
			$m .= "<a href='" . $cert_link . "' download>";
			$m .= "<img src='[salon-website]/salons/[yearmonth]/img/catalog_mail_certificates.png' alt='Download Certificates' >";
			$m .= "</a>";
			$m .= "</td>";
			++ $cells;
		}
		$m .= "</tr>";
		$m .= "</table>";

		return $m;
	}

	function mailTo() {
		return $this->profile['email'];
	}

	function generatorPairs() {
		$pairs = array(
			"participant-name" => $this->profile['profile_name'],
			"is-exhibition-virtual" => ($this->exhibition['is_virtual'] == '1'),
			"is-exhibition-venue" => ($this->exhibition['is_virtual'] != '1'),
			"can-order-catalog" => ($this->inr_price_exists || $this->usd_price_exists),
			"catalog-order-last-date" => print_date($this->salon['catalog_order_last_date']),
			"is-participant-indian" => $this->is_participant_indian,
			"is-participant-foreigner" => $this->is_participant_foreigner,
			"catalog-list" => $this->generate_catalog_list(),
			"catalog-file-view" => $this->salon['catalog'],
			"catalog-file-download" => $this->salon['catalog_download'],
			"salon-actions" => $this->generate_salon_actions(),
			"account-data" => $this->generate_account_data(),
			"mailing-data" => ($this->has_picture_awards || $this->has_individual_awards) ? $this->generate_mailing_data() : "",
		);
		return $pairs;
	}
}


?>
