<?php
include_once("connect.php");
include_once("lib.php");

// Generator for Salon Opening emails
// Global Variables
class MailGenerator {
	protected $dbcon;
	protected $yearmonth;
	protected $is_salon_archived;
	protected $salon;
	protected $contest_data;
	protected $profile_id;
	protected $profile;
	protected $individual_awards;
	protected $picture_awards;
	protected $has_special_pic_awards;
	protected $update_bank_details;
	protected $upload_fullres_pictures;
	protected $update_actions;
	protected $upload_actions;

	protected $status = "OK";
	protected $errmsg = "";

	function __construct($DBCON, $yearmonth, $is_salon_archived) {
		$this->dbcon = $DBCON;
		$this->yearmonth = $yearmonth;
		$this->is_salon_archived = $is_salon_archived;

		// Get Salon Data
		$sql = "SELECT * FROM contest WHERE yearmonth = '$yearmonth' ";
		$query = mysqli_query($this->dbcon, $sql)or sql_error($sql, mysqli_error($this->dbcon), __FILE__, __LINE__);
		if (mysqli_num_rows($query) == 0) {
			$this->status = "failed";
			$this->errmsg = "Salon not found";
		}
		else {
			$this->salon = mysqli_fetch_array($query, MYSQLI_ASSOC);
		}
		// contest-data
		$this->contest_data  = "Thank you for participating in this Salon hosted by <a href='[yps-website]'>Youth Photographic Society</a>, Bangalore, India.";
	}

	function initProfile($profile_id) {
		$this->profile_id = $profile_id;
		$entry_table = ($this->is_salon_archived ? "ar_entry" : "entry");
		$pic_table = ($this->is_salon_archived ? "ar_pic" : "pic");
		$pic_result_table = ($this->is_salon_archived ? "ar_pic_result" : "pic_result");

		// Get Profile Details
		$sql  = "SELECT profile_name, entrant_category, email, uploads, bank_account_number, bank_account_name, ";
		$sql .= "       bank_account_type, bank_name, bank_branch, bank_ifsc_code, ";
		$sql .= "       address_1, address_2, address_3, city, state, pin, honors, phone, yps_login_id, profile.club_id, ";
		$sql .= "       entrant_category, country_name, IFNULL(club_name, '') AS club_name ";
		$sql .= "  FROM $entry_table AS entry, country, profile ";
		$sql .= "  LEFT JOIN club ON club.club_id = profile.club_id ";
		$sql .= " WHERE profile.profile_id = '$this->profile_id' ";
		$sql .= "   AND entry.yearmonth = '$this->yearmonth' ";
		$sql .= "   AND entry.profile_id = profile.profile_id ";
		$sql .= "   AND entry.uploads > '0' ";
		$sql .= "   AND country.country_id = profile.country_id ";
		$query = mysqli_query($this->dbcon, $sql)or sql_error($sql, mysqli_error($this->dbcon), __FILE__, __LINE__);
		if (mysqli_num_rows($query) == 0) {
			$this->status = "failed";
			$this->errmsg = "Participant not found/registered for salon";
		}
		else {
			$this->profile = mysqli_fetch_array($query, MYSQLI_ASSOC);
		}

		// Check if Bank details need to be updated
		// Get entry_result
		$this->individual_awards = [];
		$sql = "SELECT * FROM award, entry_result ";
		$sql .= " WHERE entry_result.yearmonth = '$this->yearmonth' ";
		$sql .= "   AND entry_result.profile_id = '$profile_id' ";
		$sql .= "   AND award.yearmonth = entry_result.yearmonth ";
		$sql .= "   AND award.award_id = entry_result.award_id ";
		$sql .= "   AND award.award_type = 'entry' ";
		$qry = mysqli_query($this->dbcon, $sql)or sql_error($sql, mysqli_error($this->dbcon), __FILE__, __LINE__);
		while ($row = mysqli_fetch_array($qry, MYSQLI_ASSOC))
			$this->individual_awards[] = $row;

		// Get pic_result
		$this->has_special_pic_awards = false;
		$this->picture_awards = [];
		$sql = "SELECT * FROM $pic_result_table AS pic_result, award, $pic_table AS pic, section ";
		$sql .= " WHERE pic_result.yearmonth = '$this->yearmonth' ";
		$sql .= "   AND pic_result.profile_id = '$profile_id' ";
		$sql .= "   AND award.yearmonth = pic_result.yearmonth ";
		$sql .= "   AND award.award_id = pic_result.award_id ";
		$sql .= "   AND award.award_type = 'pic' ";
		$sql .= "   AND award.level < 99 ";
		$sql .= "   AND pic.yearmonth = pic_result.yearmonth ";
		$sql .= "   AND pic.profile_id = pic_result.profile_id ";
		$sql .= "   AND pic.pic_id = pic_result.pic_id ";
		$sql .= "   AND section.yearmonth = pic.yearmonth ";
		$sql .= "   AND section.section = pic.section ";
		$qry = mysqli_query($this->dbcon, $sql)or sql_error($sql, mysqli_error($this->dbcon), __FILE__, __LINE__);
		while ($row = mysqli_fetch_array($qry, MYSQLI_ASSOC)) {
			$this->picture_awards[] = $row;
			if ($row['section'] == 'CONTEST')
				$this->has_special_pic_awards = true;
		}

		// Make the checks
		// Check for Bank Account Updates
		$this->update_bank_details = false;
		$this->upload_fullres_pictures = false;
		// check for individual entry awards
		$award_money = 0;
		$num_uploads_required = 0;
		foreach ($this->individual_awards as $award) {
			$award_money += $award['cash_award'];
		}
		foreach ($this->picture_awards as $award) {
			$award_money += $award['cash_award'];
			if ($award['full_picfile'] == null) {
				$this->upload_fullres_pictures = true;
				++ $num_uploads_required;
			}
		}
		if ($award_money > 0 && ($this->profile['bank_account_number'] == "" || $this->profile['bank_account_name'] == "" ||
				$this->profile['bank_account_type'] == "" || $this->profile['bank_name'] == "" || $this->profile['bank_branch'] == "" ||
				$this->profile['bank_ifsc_code'] == "")) {
			$this->update_bank_details = true;
		}

		$msg  = "<ul>";
		if ($this->update_bank_details)
			$msg .= "<li>Login to <a href='[salon-website]'>Salon Website</a> and update your Bank Account details. ";

		if ($this->upload_fullres_pictures) {
			$msg .= "<li>You are yet to upload $num_uploads_required full resolution picture(s) of awarded images. ";
			$msg .= "Follow the instructions below and complete the uploads at the earliest.</li>";
			$msg .= "</ul>";
		}
		else
			$msg .= "</ul>";
		$this->update_actions = $msg;

		if ($this->upload_fullres_pictures) {
			$msg  = "<h2>Upload Full-Resolution Pictures</h2>";
			$msg .= "<p>Full Resolution pictures are required for printing catalog and for printing for the exhibition.</p>";
			$msg .= "<p>[special-pic-data]</p>";
			$msg .= "<p>[awards-data]</p>";
			$pairs = array( "special-pic-data" => $this->has_special_pic_awards ? $this->generate_special_pic_award() : "",
							"awards-data" => $this->generate_awards(),
						);
			$this->upload_actions = replace_values($msg, $pairs);
		}
		else
			$this->upload_actions = "";

	}


	protected function generate_special_pic_award() {
		$row_template = <<<TEMPLATE
			<tr>
				<td><img src='[thumbnail]' style='width:120px;height:auto' ></td>
				<td>
					<p><b>[title]</b></p><br>
					<p>has won <b>[award_name]</b></p><br>
					[upload_link]
				</td>
			</tr>
TEMPLATE;
		$m = "<br><h3>Special Picture Awards</h3>";
		$m .= "<table class='table-data'><tbody>";
		foreach ($this->picture_awards as $pic) {
			if ($pic['section'] == 'CONTEST') {
				$upload_code = encode_string_array($this->yearmonth . "|" . $pic['award_id'] . "|" . $this->profile_id . "|" . $pic['pic_id']);
				$upload_link = "";
				// if ($pic['section_type'] == "P") {
				// 	$upload_link = "<p style='text-align:center; color: gray;'>NO UPLOAD REQUIRED</p>";
				// }
				// else {
					if ($pic['full_picfile'] == "")
						$upload_link = "<p style='text-align:center;'><a href='[salon-website]/upload_awarded.php?code=" . $upload_code . "' style='color:red;' >UPLOAD PICTURE</a></p>";
					else
						$upload_link = "<p style='text-align:center; color: gray;'>ALREADY UPLOADED</p>";
				// }

				$values = array (
						"thumbnail" => "[salon-website]/salons/$this->yearmonth/upload/" . rawurlencode($pic['section']) . "/tn/" . $pic['picfile'],
						"title" => $pic['title'],
						"award_name" => str_replace(" ", "_", $pic['award_name']),
						"upload_link" => $upload_link
					);
				$m .= replace_values($row_template, $values);
			}
		}
		$m .= "</tbody></table>";
		return $m;
	}

	protected function generate_awards() {
		$row_template = <<<TEMPLATE
			<tr>
				<td><img src='[thumbnail]' style='width:120px;height:auto' ></td>
				<td>
					<p><b>[title]</b></p><br><p>Total Rating: [total_rating] / 15<p>
				</td>
				<td>
					<p><u>[pic_section]</u></p><p><b>[award_name]</b></p><br>
					[upload_link]
				</td>
			</tr>
TEMPLATE;
		$m  = "<br><h3>Picture Awards</h3>";
		$m .= "<table class='table-data'><tbody>";
		foreach ($this->picture_awards as $pic) {
			if ($pic['level'] != '99') {
				$upload_code = encode_string_array($this->yearmonth . "|" . $pic['award_id'] . "|" . $this->profile_id . "|" . $pic['pic_id']);
				$upload_link = "";
				// if ($pic['section_type'] == "P")
				// 	$upload_link = "<p style='text-align:center; color: gray;'>NO UPLOAD REQUIRED</p>";
				// else {
					if ($pic['full_picfile'] == "")
						$upload_link = "<p style='text-align:center;'><a href='[salon-website]/upload_awarded.php?code=" . $upload_code . "' style='color:red;' >UPLOAD HI-RES PICTURE</a></p>";
					else
						$upload_link = "<p style='text-align:center; color: gray;'>ALREADY UPLOADED</p>";
				// }
				$values = array (
							"thumbnail" => "[salon-website]/salons/$this->yearmonth/upload/" . rawurlencode($pic['section']) . "/tn/" . $pic['picfile'],
							"title" => $pic['title'],
							"total_rating" => $pic['total_rating'],
							"pic_section" => str_replace(" ", "_", $pic['section']),
							"award_name" => str_replace(" ", "_", $pic['award_name']),
							"upload_link" => $upload_link );
				$m .= replace_values($row_template, $values);
			}
		}
		$m .= "</tbody></table>";
		return $m;
	}

	function generateHeader() {
		$header = <<<HEADER
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
					text-align: justify;
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
				h4 {
					color:#555;
					font-size:16px;
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
					padding: 4px;
					border-top: 1px solid #aaa;
					border-bottom: 1px solid #aaa;
				}

				table .partner-data {
					margin: 0;
					width: 100%;
					padding: 8px;
					border-collapse: collapse;
				}

				table .partner-data td, th  {
					padding: 4px;
					border-top: 1px solid #aaa;
					border-bottom: 1px solid #aaa;
				}

				table .table-grid {
					margin : 0;
					width : 100%;
					padding : 8px;
					border : 0;
				}

				table .table-grid td, th {
					padding : 8px;
					border : 0;
				}

				h2.white {
					color:#ffffff;
				}
			</style>
		</head>

		<body style="width: 100%; background-color : #efe6bd;">
HEADER;
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
			"contest-data" => $this->contest_data,
			"action-required-message" => $this->update_actions,
			"complete-uploads-message" => $this->upload_actions,
		);
	}
}


?>
