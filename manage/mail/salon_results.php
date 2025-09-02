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
	protected $download_actions;
	protected $update_actions;
	protected $profile_id;
	protected $profile;
	protected $individual_awards;
	protected $picture_awards;
	protected $picture_others;
	protected $money_transfer_required;
	protected $mailing_required;
	protected $has_individual_awards;
	protected $has_special_pic_awards;
	protected $has_picture_acceptances;
	protected $has_picture_awards;

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
		$this->contest_data  = "Thank you for participating in this salon hosted by ";
		$this->contest_data .= "<a href='[yps-website]'>Youth Photographic Society</a>, Bangalore, India. ";
		$this->contest_data .= "The Judging event was held between [judging-start-date] and [judging-end-date] to ";
		$this->contest_data .= "evaluate the pictures and select award winners. The results have been posted on the ";
		$this->contest_data .= "<a href='[salon-website]/results.php'>Salon Website</a>.";
		// if ($admin_yearmonth == '202008' || $admin_yearmonth == '202012' || $admin_yearmonth = '202108') {
		if ($this->salon['judging_mode'] == 'REMOTE') {
			$this->contest_data .= "<br><br>A remote judging model was used for this salon and the ratings were displayed through ";
			$this->contest_data .= "YPS Facebook Page and YPS Youtube Channel. We thank the members of the jury and the hundreds of patrons ";
			$this->contest_data .= "who joined the Live webcasts and made the event a grand success. ";
		}
	}

	function initProfile($profile_id) {
		$this->profile_id = $profile_id;
		$entry_table = ($this->is_salon_archived ? "ar_entry" : "entry");
		$pic_table = ($this->is_salon_archived ? "ar_pic" : "pic");
		$pic_result_table = ($this->is_salon_archived ? "ar_pic_result" : "pic_result");

		// Get Profile Details
		$sql  = "SELECT profile_name, entrant_category, email, uploads, bank_account_number, address_1, address_2, address_3, ";
		$sql .= "       city, state, pin, honors, phone, yps_login_id, profile.club_id, entrant_category, country_name, ";
		$sql .= "       IFNULL(club_name, '') AS club_name ";
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

		// Check Pictures Uploaded
		$sql = "SELECT COUNT(*) AS num_pics FROM $pic_table AS pic ";
		$sql .= " WHERE yearmonth = '$this->yearmonth' AND profile_id = '$profile_id' ";
		$qry = mysqli_query($this->dbcon, $sql)or sql_error($sql, mysqli_error($this->dbcon), __FILE__, __LINE__);
		if ($res = mysqli_fetch_array($qry))
			$num_pics = $res['num_pics'];
		else
			$num_pics = 0;

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
		$this->picture_awards = [];
		$sql = "SELECT * FROM $pic_result_table AS pic_result, award, $pic_table AS pic, section ";
		$sql .= " WHERE pic_result.yearmonth = '$this->yearmonth' ";
		$sql .= "   AND pic_result.profile_id = '$profile_id' ";
		$sql .= "   AND award.yearmonth = pic_result.yearmonth ";
		$sql .= "   AND award.award_id = pic_result.award_id ";
		$sql .= "   AND award.award_type = 'pic' ";
		$sql .= "   AND pic.yearmonth = pic_result.yearmonth ";
		$sql .= "   AND pic.profile_id = pic_result.profile_id ";
		$sql .= "   AND pic.pic_id = pic_result.pic_id ";
		$sql .= "   AND section.yearmonth = pic.yearmonth ";
		$sql .= "   AND section.section = pic.section ";
		$qry = mysqli_query($this->dbcon, $sql)or sql_error($sql, mysqli_error($this->dbcon), __FILE__, __LINE__);
		while ($row = mysqli_fetch_array($qry, MYSQLI_ASSOC))
			$this->picture_awards[] = $row;

		// Get Non-Accepted Pictures
		$this->picture_others = [];
		$sql = "SELECT * FROM $pic_table AS pic, section ";
		$sql .= " WHERE pic.yearmonth = '$this->yearmonth' ";
		$sql .= "   AND pic.profile_id = '$profile_id' ";
		$sql .= "   AND section.yearmonth = pic.yearmonth ";
		$sql .= "   AND section.section = pic.section ";
		$sql .= "   AND pic.pic_id NOT IN ( ";
		$sql .= "       SELECT pic_result.pic_id FROM $pic_result_table AS pic_result ";
		$sql .= "       WHERE pic_result.yearmonth = pic.yearmonth ";
		$sql .= "         AND pic_result.profile_id = pic.profile_id ";
		$sql .= "    ) ";
		$qry = mysqli_query($this->dbcon, $sql)or sql_error($sql, mysqli_error($this->dbcon), __FILE__, __LINE__);
		while ($row = mysqli_fetch_array($qry, MYSQLI_ASSOC))
			$this->picture_others[] = $row;

		// Determine applicable sections
		$this->money_transfer_required = false;
		$this->mailing_required = false;
		$this->has_individual_awards = false;
		$this->has_special_pic_awards = false;
		$this->has_picture_acceptances = false;
		$this->has_picture_awards = false;

		// check for individual entry awards
		$award_money = 0;
		$num_to_mail = 0;
		foreach ($this->individual_awards as $award) {
			$award_money += $award['cash_award'];
			$num_to_mail += $award['has_medal'] + $award['has_pin'] + $award['has_ribbon'] + $award['has_memento'] + $award['has_gift'] + $award['has_certificate'];
		}
		if (! $this->has_individual_awards)
			$this->has_individual_awards = (sizeof($this->individual_awards) > 0);
		if (! $this->money_transfer_required)
			$this->money_transfer_required = ($award_money > 0);
		if (! $this->mailing_required)
			$this->mailing_required = ($num_to_mail > 0);

		// check for special picture awards
		$award_money = 0;
		$num_to_mail = 0;
		$special_pic_awards = 0;
		foreach ($this->picture_awards as $award) {
			if ($award['section'] == 'CONTEST') {
				$award_money += $award['cash_award'];
				$num_to_mail += $award['has_medal'] + $award['has_pin'] + $award['has_ribbon'] + $award['has_memento'] + $award['has_gift'] + $award['has_certificate'];
				++ $special_pic_awards;
			}
		}
		if (! $this->has_special_pic_awards)
			$this->has_special_pic_awards = ($special_pic_awards > 0);
		if (! $this->money_transfer_required)
			$this->money_transfer_required = ($award_money > 0);
		if (! $this->mailing_required)
			$this->mailing_required = ($num_to_mail > 0);

		// check for picture awards
		$award_money = 0;
		$num_to_mail = 0;
		$pic_awards = 0;
		foreach ($this->picture_awards as $award) {
			if ($award['section'] != 'CONTEST' && $award['level'] != 99) {
				$award_money += $award['cash_award'];
				$num_to_mail += $award['has_medal'] + $award['has_pin'] + $award['has_ribbon'] + $award['has_memento'] + $award['has_gift'] + $award['has_certificate'];
				++ $pic_awards;
			}
		}
		if (! $this->has_picture_awards)
			$this->has_picture_awards = ($pic_awards > 0);
		if (! $this->money_transfer_required)
			$this->money_transfer_required = ($award_money > 0);
		if (! $this->mailing_required)
			$this->mailing_required = ($num_to_mail > 0);

		// check for picture acceptances
		$award_money = 0;
		$num_to_mail = 0;
		$pic_acceptances = 0;
		foreach ($this->picture_awards as $award) {
			if ($award['section'] != 'CONTEST' && $award['level'] == 99) {
				$award_money += $award['cash_award'];
				$num_to_mail += $award['has_medal'] + $award['has_pin'] + $award['has_ribbon'] + $award['has_memento'] + $award['has_gift'] + $award['has_certificate'];
				++ $pic_acceptances;
			}
		}
		if (! $this->has_picture_acceptances)
			$this->has_picture_acceptances = ($pic_acceptances > 0);
		if (! $this->money_transfer_required)
			$this->money_transfer_required = ($award_money > 0);
		if (! $this->mailing_required)
			$this->mailing_required = ($num_to_mail > 0);

		// Generate content for [download-actions] tag
		$code = encode_string_array("$this->yearmonth|$profile_id");
		$msg  = "<h3>QUICK LINKS</h3>";
		$msg .= "<br><table class='table-grid'><tr>";
		$msg .= "<td width='50%'><a href='[salon-website]/user_results_from_email.php?code=$code'>";
		$msg .= "<img src='[salon-website]/salons/$this->yearmonth/img/share-results.jpg'></a></td>";
		$msg .= "<td><a href='[salon-website]/user_results_from_email.php?code=$code'>";
		$msg .= "<img src='[salon-website]/salons/$this->yearmonth/img/download-scorecard.jpg'></a></td>";
		$msg .= "</tr></table><br>";
		$this->download_actions = $msg;

		// Generate Congratulatory Message for [update-actions] tag
		$this->update_actions = "";

		if ($this->has_individual_awards || $this->has_special_pic_awards || $this->has_picture_acceptances || $this->has_picture_awards) {
			$update_end_date = strtoupper(date("F d, Y", strtotime($this->salon['update_end_date'])));
			$m  = "<h3>COMPLETE FOLLOWING ACTIONS BEFORE " . $update_end_date . "</h3>";
			$m .= "<table class='table-data'>";
			if ($this->mailing_required) {
				$m .= "<tr><td width='30%'><h4>Verify and update your contact details</h4></td>";
				$m .= "<td>" . $this->generate_profile() . "</td></tr>";
			}
			if ($this->money_transfer_required ) {
				$m .= "<tr><td width='30%'><h4>Update your Account details</h4></td>";
				$m .= "<td><p>Your award amount will be transferred to your account after the exhibition. ";
				$m .= "Login and use Edit Profile option to update your Account Number.</p></td></tr>";
			}
			if ( ($this->has_picture_awards && $this->salon['has_exhibition'] != 0) ) {
				$m .= "<tr><td width='30%'><h4>Upload Full Resolution Picture</h4></td>";
				$m .= "<td><p>We need full resolution JPEG files for pictures that have been awarded medals or ribbons. ";
				$m .= "Use the <b>UPLOAD PICTURE</b> link in the My Pictures section below to upload these files.</p></td></tr>";

				$m .= "<tr><td width='30%'><h4>Send Short Video</h4></td>";
				$m .= "<td><p>We will be happy to host a short video not <b>exceeding 20 seconds</b> explaining the story and the ";
				$m .= "thought process behind each of your awarded pictures. Send the videos to <a href='mailto:salon@ypsbengaluru.in'>salon@ypsbengaluru.in</a></p></td></tr>";
			}
			$m .= "</table>";

			$this->update_actions = $m;
		}


	}

	protected function generate_profile() {

		$template = <<<TEMPLATE
			<p><b>[profile_name]</b></p>
			<p><i>[honors]</i></p>
			<p>[club]</p>
			<p>[address]</p>
			<p>Ph:[phone]</p>
TEMPLATE;

		$address = $this->profile['address_1'];
		if ($this->profile['address_2'] != "")
			$address .= "<br>" . $this->profile['address_2'];
		if ($this->profile['address_3'] != "")
			$address .= "<br>" . $this->profile['address_3'];
		$address .= "<br>" . $this->profile['city'];
		$address .= "<br>" . $this->profile['state'] . " - " . $this->profile['pin'];
		$address .= "<br>" . $this->profile['country_name'];

		$values = array(
						"profile_name" => $this->profile['profile_name'],
						"entrant_category" => $this->profile['entrant_category'],
						"honors" => $this->profile['honors'],
						"club" => ($this->profile['yps_login_id'] == "" ? $this->profile['club_name'] : "Youth Photographic Society"),
						"address" => $address,
						"phone" => $this->profile['phone'] );

		return replace_values($template, $values);
	}

	protected function generate_individual_award() {
		$m = "<h2>Individual Awards</h2>";
		$m .= "<table class='table-data'><tbody>";
		foreach ($this->individual_awards as $award) {
			$m .= "<tr><td>Award Name</td><td><b>" . $award['award_name'] . "</b></td></tr>";
		}
		$m .= "</tbody></table>";
		return $m;
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
		$m = "<br><h2>Special Picture Awards</h2>";
		if ($this->salon['has_exhibition']) {
			$m .= <<<HTML
					<p>YPS requires <b>Full Resolution JPEG files of pictures awarded with medals and ribbons</b>
						for publishing in catalog. They are also used for printing and display at the Salon Exhibition.
						YPS prefers <b>3600 x 5400 pixels sRGB JPEGs</b> for making 12x18 prints at 300 DPI.
						Use the <b>UPLOAD PICTURE</b> link next to each of your awarded pictures
						and upload your high-resolution pictures directly to the Salon website. You can also do this
						from <b>My Results Page</b> on the Salon website. Check salon website for details of
						the exhibition.
					</p>
		HTML;
		}
		$m .= "<table class='table-data'><tbody>";
		foreach ($this->picture_awards as $pic) {
			if ($pic['section'] == 'CONTEST') {
				$upload_code = encode_string_array($this->yearmonth . "|" . $pic['award_id'] . "|" . $this->profile_id . "|" . $pic['pic_id']);
				$upload_link = "";
				if ($pic['section_type'] == "P") {
					$upload_link = "<p style='text-align:center; color: gray;'>NO UPLOAD REQUIRED</p>";
				}
				else {
					if ($pic['full_picfile'] == "")
						$upload_link = "<p style='text-align:center;'><a href='[salon-website]/upload_awarded.php?code=" . $upload_code . "' style='color:red;' >UPLOAD PICTURE</a></p>";
					else
						$upload_link = "<p style='text-align:center; color: gray;'>ALREADY UPLOADED</p>";
				}

				$values = array (
						"thumbnail" => "[salon-website]/salons/$this->yearmonth/upload/" . rawurlencode($pic['section']) . "/tn/" . $pic['picfile'],
						"title" => $pic['title'],
						"award_name" => $pic['award_name'],
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
		$m  = "<br><h3>Pictures Awarded Medals/Ribbons</h3>";
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

	protected function generate_acceptances() {
		$row_template = <<<TEMPLATE
			<tr>
				<td><img src='[thumbnail]' style='width:120px;height:auto' ></td>
				<td><p><b>[title]</b></p><br><p>Total Rating: [total_rating] / 15<p></td>
				<td><p><u>[pic_section]</u></p><p><b>[award_name]</b></p></td>
			</tr>
TEMPLATE;
		$m  = "<br><h3>Pictures with just Acceptances</h3>";
		$m .= "<table class='table-data'><tbody>";

		foreach ($this->picture_awards as $pic) {
			if ($pic['level'] == '99') {
				$values = array (
							"thumbnail" => "[salon-website]/salons/$this->yearmonth/upload/" . rawurlencode($pic['section']) . "/tn/" . $pic['picfile'],
							"title" => $pic['title'],
							"total_rating" => $pic['total_rating'],
							"pic_section" => str_replace(" ", "_", $pic['section']),
							"award_name" => "Salon_Acceptance"
							// "award_name" => str_replace(" ", "_", $pic['award_name'])
						);
				$m .= replace_values($row_template, $values);
			}
		}
		$m .= "</tbody></table>";

		return $m;
	}

	protected function generate_others() {
		$row_template = <<<TEMPLATE
			<tr>
				<td><img src='[thumbnail]' style='width:120px;height:auto' ></td>
				<td>
					<p><u>[pic_section]</u></p>
					<p><b>[title]</b></p><br>
					<p>[rejection_text]</p>
					<p>Total Rating: [total_rating] / 15<p></td>
			</tr>
TEMPLATE;
		if ($this->has_picture_acceptances || $this->has_picture_awards)
			$m = "<br><h3>Total Ratings for your other Pictures</h3>";
		else
			$m = "<br><h3>Total Ratings for your Pictures</h3>";
		$m .= "<p>These pictures are <b>not accepted</b> for exhibition in this salon.</p>";
		$m .= "<table class='table-data'><tbody>";

		foreach ($this->picture_others as $pic) {
			$rejection_text = "";
			if ($pic['notifications'] != "")
				$rejection_text = rejection_text($pic['notifications']);

			$jury_rejection_text = jury_notifications($this->yearmonth, $pic['profile_id'], $pic['pic_id'], $this->is_salon_archived);
			if ($jury_rejection_text != "")
				$rejection_text = ($rejection_text == "" ? "" : ", ") . $jury_rejection_text;

			if ($rejection_text != "")
				$rejection_text = "<b>Rejected :</b> " . $rejection_text;

			$values = array (
						"thumbnail" => "[salon-website]/salons/$this->yearmonth/upload/" . rawurlencode($pic['section']) . "/tn/" . $pic['picfile'],
						"title" => $pic['title'],
						"rejection_text" => $rejection_text,
						"total_rating" => $pic['total_rating'],
						"pic_section" => str_replace(" ", "_", $pic['section'])
					 );
			$m .= replace_values($row_template, $values);
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
		$footer = <<<FOOTER
		</body>
		</html>
FOOTER;
		return $footer;
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
			"download-actions" => $this->download_actions,
			"update-actions" => $this->update_actions,
			"individual-data" => ($this->has_individual_awards ? $this->generate_individual_award() : ""),
			"special-pic-data" => ($this->has_special_pic_awards ? $this->generate_special_pic_award() : ""),
			"awards-data" => ($this->has_picture_awards ? $this->generate_awards() : ""),
			"acceptances-data" => ($this->has_picture_acceptances ? $this->generate_acceptances() : ""),
			"others-data" => $this->generate_others(),
		);
	}
}


?>
