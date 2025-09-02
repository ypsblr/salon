<?php
include_once("connect.php");
include_once("lib.php");

// Generator for reminding replacement of rejected pictures
// Global Variables
class MailGenerator {
	protected $dbcon;
	protected $yearmonth;
	protected $is_salon_archived;
	protected $contest;
	protected $profile_id;
	protected $profile;
	protected $pic_notifications;
	protected $status = "OK";
	protected $errmsg = "";

	function __construct($DBCON, $yearmonth, $is_salon_archived) {
		$this->dbcon = $DBCON;
		$this->yearmonth = $yearmonth;
		$this->is_salon_archived = $is_salon_archived;

		// Get Contest details
		$sql = "SELECT submission_timezone, submission_timezone_name FROM contest WHERE yearmonth = '$yearmonth' ";
		$query = mysqli_query($this->dbcon, $sql)or sql_error($sql, mysqli_error($this->dbcon), __FILE__, __LINE__);
		$this->contest = mysqli_fetch_array($query, MYSQLI_ASSOC);
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

		// Determine if there are pics rejected
		$this->pic_notifications = "";

		$pic_table = ($this->is_salon_archived ? "ar_pic" : "pic");
		$sql  = "SELECT * FROM $pic_table AS pic, section ";
		$sql .= " WHERE pic.yearmonth = '$this->yearmonth' ";
		$sql .= "   AND pic.profile_id = '$this->profile_id' ";
		$sql .= "   AND pic.notifications != '' ";
		$sql .= "   AND section.yearmonth = pic.yearmonth ";
		$sql .= "   AND section.section = pic.section ";
		$query = mysqli_query($this->dbcon, $sql)or sql_error($sql, mysqli_error($this->dbcon), __FILE__, __LINE__);

		if (mysqli_num_rows($query) > 0) {
			while($pic = mysqli_fetch_array($query)) {
				$rejection_text = $this->rejection_text($pic['notifications']);
				// Compile HTML showing rejected pictures
				if ($rejection_text == "") {
					$this->status = "skipped";
					$this->errmsg = "Participant does not have any pictures that will be rejected";
				}
				else {
					$this->generate_notifications($pic, $rejection_text);
				}		// $rejection_text != ""
			}	// while there are pics with Notifications
			if ($this->pic_notifications != "")
				$this->pic_notifications .= "<hr>";
		}	// if there are pictures with notifications
	}

	protected function rejection_text($pic_notifications) {
		static $rejection_reasons = [];
		if (sizeof($rejection_reasons) == 0) {
			$sql  = "SELECT template_code, template_name ";
			$sql .= "  FROM email_template ";
			$sql .= " WHERE template_type = 'user_notification' ";
			$sql .= "   AND will_cause_rejection = '1' ";
			$qntf = mysqli_query($this->dbcon, $sql)or sql_error($sql, mysqli_error($this->dbcon), __FILE__, __LINE__);
			while ($rntf = mysqli_fetch_array($qntf)) {
				$rejection_reasons[$rntf['template_code']] = $rntf['template_name'];
			}		// while there are notifications
		}

		// Match Notifications
		$notification_list = explode("|", $pic_notifications);
		$rejection_text = "";
		foreach ($notification_list AS $notification) {
			if ($notification != "") {
				list($notification_date, $notification_code_str) = explode(":", $notification);
				$notification_codes = explode(",", $notification_code_str);
				foreach ($notification_codes as $notification_code) {
					if (isset($rejection_reasons[$notification_code])) {
						$rejection_text .= (($rejection_text == "") ? "" : ",") . $rejection_reasons[$notification_code];
					}
				}
			}
		}

		return $rejection_text;
	}

	protected function generate_notifications($pic, $rejection_text) {
		// First time there is something to generate
		if ($this->pic_notifications == "")
			$this->pic_notifications .= "<h2>Pictures rejected during YPS reviews</h2> ";

		// Generate Notifications
		$this->pic_notifications .= "<hr>";
		$this->pic_notifications .= "<h3>" . $pic['title'] . "</h3>";
		$this->pic_notifications .= "<h4>Section : " . $pic['section'] . "</h4>";
		$this->pic_notifications .= "<p>Code : <b>" . $pic['eseq'] . "</b></p>";
		$embed = "[salon-website]/salons/[yearmonth]/upload/" . $pic['section'] . "/tn/" . $pic['picfile'];
		$this->pic_notifications .= "<p><img src='$embed' style='max_width: 120px;' alt='" . $pic['title'] .= "' ></p><br>";
		if ( date_tz("now", $this->contest['submission_timezone']) > date("Y-m-d", strtotime($pic['submission_last_date'] . " +1 day")) ) {
			$this->pic_notifications .= "<p>The picture has been marked as rejected by YPS reviewers for the following reasons :</p>";
			$this->pic_notifications .= "<p>\t\t<b>" . $rejection_text . "</b></p>";
			$this->pic_notifications .= "<p>Please note that the last date for replacing the picture is over. This is for your Information.</p>";
		}
		else {
			$rectify_link = encode_string_array(implode("|", [$this->yearmonth, $this->profile_id, $pic['pic_id'], $this->profile['profile_name']]));
			$this->pic_notifications .= "<p>The picture has been marked as rejected by YPS reviewers for the following reasons :</p>";
			$this->pic_notifications .= "<p>\t\t<b>" . $rejection_text . "</b></p>";
			$this->pic_notifications .= "<br><p><a href='[salon-website]/upload_rectified.php?code=" . $rectify_link . " '>";
			$this->pic_notifications .= "Click to Replace Picture</a></p>";
			$this->pic_notifications .= "<p>(Valid for one time upload on or before ";
			$this->pic_notifications .= date("Y-m-d", strtotime("+1 day", strtotime($pic['submission_last_date'])));
			$this->pic_notifications .= " " . $this->contest['submission_timezone_name'] . " time";
			$this->pic_notifications .= ")</p><br>";
		}

	}


	function generateHeader() {
		return "";
	}

	function generateFooter() {
		return "";
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
			"pic-notifications" => $this->pic_notifications,
		);
	}
}


?>
