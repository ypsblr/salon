<?php
include ("../lib/connect.php");
include ("../lib/pdflib.php");
include ("../lib/lib.php");

class RPDF extends PDF {
	protected $page_no = 0;
	protected $current_x = 0;
	protected $current_y = 0;

	// Custom Constructor that sets paper size and margins
	function __construct($orientation='P', $unit='pt', $size='A4', $margin=72) {
		parent::__construct($orientation, $unit, $size, $margin);
	}

	function Footer() {
		$this->page_no += 1;

		// Draw Line
		$x = $this->GetMargin();
		$y = $this->GetPageHeight() - $this->GetMargin();
		$x_to = $this->GetPageWidth() - $this->GetMargin();
		$this->SetLineWidth(0.5);
		$this->SetDrawColor(0, 0, 0);
		$this->Line($x, $y, $x_to, $y);

		// Print Report Name on Left
		$y += 4;
		$width = $this->GetPageWidth() - (2 * $this->GetMargin());
		$this->PrintFiller("Oswald", 10, 0, $x, $y, $width, 12, "L", "AORA4U CASEBOOK"  );
		$this->PrintFiller("Oswald", 10, 0, $x, $y, $width, 12, "R", "Page : " . $this->page_no  );

	}

	function PrintReportHeader($from_date, $to_date) {
		$margin = $this->GetMargin();
		$img_width = 60;

		// AORA LOGO on the left - 0.5 in or 36 points
		$x = $margin;
		$y = $margin;
		$this->PrintThumbnail($x, $y, "./img/aora.png", $img_width, false, "MM", "https://aoraindia.com");

		// AORA4U Logo on the right
		$x = $this->GetPageWidth() - $margin - $img_width;
		$this->PrintThumbnail($x, $y, "./img/aora4u.png", $img_width, false, "MM", "https://aoraindia.com");

		// AORA India Name
		$x = $margin + $img_width + 2;
		$width = $this->GetPageWidth() - (2 * $margin) - ( 2 * ($img_width+2));
		$height = $img_width;
		$this->PrintFiller("Oswald", 18, 0, $x, $y, $width, 20, "C", "Academy of Regional Anaesthesia of India");
		$this->PrintFiller("Oswald", 20, 0, $x, $this->GetY(), $width, 22, "C", "Monthly Casebook");
		$this->PrintFiller("Oswald", 12, 0, $x, $this->GetY(), $width, 14, "C", date("M j, Y", strtotime($from_date)) . " - " . date("M j, Y", strtotime($to_date))  );

		// Bottom Line
		$x = $margin;
		$y = $margin + $img_width + 2;
		$x_to = $this->GetPageWidth() - $margin;
		$this->SetLineWidth(0.5);
		$this->SetDrawColor(0, 0, 0);
		$this->Line($x, $y, $x_to, $y);

		$this->current_x = 0;
		$this->current_y = $y;
	}

	function PrintProfile($profile) {
		$y = $this->current_y + 8;
		$x = $this->GetMargin();
		$width = $this->GetPageWidth() - (2 * $this->GetMargin());
		$this->PrintFiller("Oswald", 20, 0, $x, $y, $width, 22, "L", "Dr. " . $profile['user_name']);
		$this->PrintFiller("Oswald", 10, 0, $x, $this->GetY(), $width, 12, "L", $profile['credentials']);
		$this->PrintFiller("Oswald", 10, 0, $x, $this->GetY(), $width, 12, "L", "AORA Member : " . $profile['mem_no']);
		if ($profile['apprentice_type'] != "")
		$this->PrintFiller("Oswald", 10, 0, $x, $this->GetY(), $width, 12, "L", $profile['apprentice_type'] . " Apprentice");

		$hospital_str = "Hospitals : ";
		foreach($profile['hospital_list'] as $hospital) {
			$hospital_str .= $hospital['hospital_name'] . ", " . $hospital['city_name'] . "(" . $hospital['num_cases'] . " cases); ";
		}
		$this->PrintFiller("Oswald", 10, 0, $x, $this->GetY(), $width, 12, "L", $hospital_str);

		$this->current_x = $this->GetMargin();
		$this->current_y = $this->GetY();
	}

	function PrintStats($stats) {
		$y = $this->current_y + 8;
		$x = $this->GetMargin();
		$width = $this->GetPageWidth() - (2 * $this->GetMargin());
		$this->PrintFiller("Oswald", 20, 0, $x, $y, $width, 22, "L", "Top Stats");

		$y = $this->GetY()+2;
		$x = $this->GetMargin();
		$block_id = $this->CreateBlock($width, $x, $y);

		// Add Table Titles
		$widths = [];
		$texts = [];
		$aligns = [];
		$fonts = [];
		$boxes = [];
		foreach ($stats as $stat_key => $stat_list) {
			$widths[] = round(100/sizeof($stats), 0);
			$texts[] = $stat_key;
			$aligns[] = "LM";
			$fonts[] = "Oswald||10|0xffffff";
			$boxes[] = "0x404040||";
		}
		$this->AddRowNode ($block_id, sizeof($stats), $widths, $texts, $aligns, $fonts, $boxes, $column_spacing = 2, $row_spacing = 2, $line_spacing = 1.2);

		// Add Table Rows
		for ($i = 0; $i < 3; ++ $i ) {
			$texts = [];
			$fonts = [];
			$boxes = [];
			foreach ($stats as $stat_key => $stat_list) {
				$fonts[] = "Oswald||10|0x0";
				$boxes[] = "0xe0e0e0||";
				if (isset($stat_list[$i])) {
					$stat_text  = (isset($stat_list[$i]['item_name'])) ? $stat_list[$i]['item_name'] : "";
					$stat_text .= (isset($stat_list[$i]['num_items'])) ? " - " . $stat_list[$i]['num_items'] : "";
					$texts[] = $stat_text;
				}
				else
					$texts[] = "";
			}
			$this->AddRowNode ($block_id, sizeof($stats), $widths, $texts, $aligns, $fonts, $boxes, $column_spacing = 2, $row_spacing = 2, $line_spacing = 1.2);
		}





		// foreach ($stats as $stat_key => $stat_list) {
		// 	$stat_text = $stat_key . " : ";
		// 	foreach ($stat_list as $stat_line) {
		// 		$stat_text .= (isset($stat_line['item_name']) ? $stat_line['item_name'] : "" );
		// 		$stat_text .= "(" . $stat_line['num_items'] . " cases); ";
		// 	}
		// 	$this->PrintFiller("Oswald", 10, 0, $x, $y, $width, 12, "L", $stat_text);
		// 	$y += 12;
		// }

		$blocks = array($block_id);
		$y = $this->PositionAndPrintBlocks($blocks);
		$y += 2;
		$x = $this->GetMargin();
		$this->RemoveAllBlocks();

		$this->current_x = $x;
		$this->current_y = $y;
	}

	function PrintLogHeader () {
		$y = $this->current_y + 8;
		$x = $this->GetMargin();
		$width = $this->GetPageWidth() - (2 * $this->GetMargin());
		$this->PrintFiller("Oswald", 20, 0, $x, $y, $width, 22, "L", "MY CASEBOOK");
		$this->current_x = $this->GetMargin();
		$this->current_y = $this->GetY();
	}

	function PrintNoLogsMessage () {
		$y = $this->current_y + 4;
		$x = $this->GetMargin();
		$width = $this->GetPageWidth() - (2 * $this->GetMargin());
		$this->PrintFiller("Oswald", 12, 0, $x, $y, $width, 22, "C", "NO ANAESTHESIA LOGS FOUND FOR THE PERIOD");
		$this->current_x = $this->GetMargin();
		$this->current_y = $this->GetY();
	}

	function PrintCaseTitle($userlog, $seq_no) {
		$y = $this->current_y + 4;
		$x = $this->GetMargin();

		$block_id = $this->CreateBlock($this->GetPageWidth() - 2 * $this->margin, $x, $y, 4, "0xe0e0e0|0.5|0x404040");

		$width = $this->GetPageWidth() - (2 * $this->GetMargin());
		$text = $seq_no . ". " . date("M j,Y", strtotime($userlog['log_date']));
		$text .= "( " . $userlog['time_start'] . " - " . $userlog['time_end'] . ")";
		$text .= " @ " . $userlog['hospital']['hospital_name'] . ", " . $userlog['hospital']['city']['city_name'];
		$this->AddTextNode($block_id, "Oswald||14|0x0", "L", $text);

		$text = "";
		$operation_names = [];
		foreach($userlog['operation_list'] as $operation) {
			if ($operation['operation'] != null) {
				$operation_names[] = $operation['operation']['operation_name'] . "(" . $operation['operation']['specialty']['specialty_name'] . ")";
			}
			else {
				$operation_names[] = $operation['my_operation']['refvalue'] . "(" . $operation['my_specialty']['specialty_name'] . ")";
			}
		}
		$text = "         " . implode(" & ", $operation_names);
		$y = $this->GetY() + 2;
		$this->AddTextNode($block_id, "Oswald||16|0x0", "L", $text);

		$y = $this->PositionAndPrintBlocks(array($block_id));
		$this->RemoveAllBlocks();

		$this->current_x = $this->GetMargin();
		$this->current_y = $y;
	}

	function CreateSurgeryBlock ($userlog, $block_width, $x, $y, $margin) {
		//
		// Print Surgery Details
		//

		$block_id = $this->CreateBlock($block_width, $x, $y, $margin);
		$this->AddTextNode($block_id, "Oswald||18|0xF3C76F", "C", "SURGERY");

		// Common Row Parameters
		$columns = 2;
		$widths = array(25, 75);
		$aligns = array("LM", "LM");
		$fonts = array("Oswald||10|0xa0a0a0", "Oswald||10|0x0");
		$boxes = array("||", "0xe0e0e0||");

		// Supervision Details
		if ($userlog['supervision_type'] != "") {
			// Add Supervision Type
			$texts[0] = "Supervision";
			$texts[1] = $userlog['supervision_type'];
			$this->AddRowNode ($block_id, $columns, $widths, $texts, $aligns, $fonts, $boxes);

			if ($userlog['supervision_type'] != "No Supervision") {
				// Add Supervision Level
				$texts[0] = "Level";
				$texts[1] = $userlog['supervision_level'];
				$this->AddRowNode ($block_id, $columns, $widths, $texts, $aligns, $fonts, $boxes);

			}
		}
		// Add texts related to surgeries performed
		$texts[0] = "Surgeries";
		$operation_names = [];
		foreach($userlog['operation_list'] as $operation) {
			if ($operation['operation'] != null) {
				$operation_names[] = $operation['operation']['operation_name'] . "(" . $operation['operation']['specialty']['specialty_name'] . ")";
			}
			else {
				$operation_names[] = $operation['my_operation']['refvalue'] . "(" . $operation['my_specialty']['specialty_name'] . ")";
			}
		}

		$texts[1] = implode("\r\n", $operation_names);

		$this->AddRowNode ($block_id, $columns, $widths, $texts, $aligns, $fonts, $boxes);

		// Add Operation Details
		$texts[0] = "Details";
		$texts[1] = $userlog['operation_details'];
		$this->AddRowNode ($block_id, $columns, $widths, $texts, $aligns, $fonts, $boxes);

		// Add Priority
		$texts[0] = "Priority";
		$texts[1] = $userlog['priority'];
		$this->AddRowNode ($block_id, $columns, $widths, $texts, $aligns, $fonts, $boxes);

		// Add ASA Grade
		$texts[0] = "ASA Grade";
		$texts[1] = $userlog['asa_grade'];
		$this->AddRowNode ($block_id, $columns, $widths, $texts, $aligns, $fonts, $boxes);

		// Add Operation Details
		$texts[0] = "Notes";
		$texts[1] = $userlog['user_notes'];
		$this->AddRowNode ($block_id, $columns, $widths, $texts, $aligns, $fonts, $boxes);

		$texts[0] = "Approach";
		$texts[1] = $userlog['anaesthesia_type'];
		$this->AddRowNode ($block_id, $columns, $widths, $texts, $aligns, $fonts, $boxes);

		$texts[0] = "Sedation";
		$text = "";
		foreach ($userlog['sedation_list'] as $sedation) {
			if ($sedation['sedation'] != null)
				$text .= $sedation['sedation']['sedation_name'] . "\r\n";
			else
				$text .= $sedation['my_sedation']['refvalue'] . "\r\n";

		}
		$texts[1] = $text;
		$this->AddRowNode ($block_id, $columns, $widths, $texts, $aligns, $fonts, $boxes);

		return $block_id;
	}

	function CreatePatientBlock ($userlog, $block_width, $x, $y, $margin) {

		$patient = $userlog["patient"];

		$block_id = $this->CreateBlock($block_width, $x, $y, $margin);
		$this->AddTextNode($block_id, "Oswald||18|0xF3C76F", "C", "PATIENT");

		// Common Row Parameters
		$columns = 2;
		$widths = array(25, 75);
		$aligns = array("LM", "LM");
		$fonts = array("Oswald||10|0xa0a0a0", "Oswald||10|0x0");
		$boxes = array("||", "0xe0e0e0||");

		$texts[0] = "Hospital Ref";
		$texts[1] = $patient['patient_ref'];
		$this->AddRowNode ($block_id, $columns, $widths, $texts, $aligns, $fonts, $boxes);

		$texts[0] = "Gender";
		$texts[1] = $patient['patient_gender'];
		$this->AddRowNode ($block_id, $columns, $widths, $texts, $aligns, $fonts, $boxes);

		$texts[0] = "Age";
		$texts[1] = $patient['patient_age'];
		$this->AddRowNode ($block_id, $columns, $widths, $texts, $aligns, $fonts, $boxes);

		$texts[0] = "Weight";
		$texts[1] = $patient['patient_weight'] . " KGs";
		$this->AddRowNode ($block_id, $columns, $widths, $texts, $aligns, $fonts, $boxes);

		$texts[0] = "Notes";
		$texts[1] = $patient['patient_notes'];
		$this->AddRowNode ($block_id, $columns, $widths, $texts, $aligns, $fonts, $boxes);

		// Co-morbidity
		if (isset($patient['patient_ailment_list'])){
			$texts[0] = "Co-morbidity";
			$text = "";
			foreach($patient['patient_ailment_list'] as $pa) {
				$text .= $pa['ailment']['ailment_name'];
				if ($pa['pa_name'] != "")
					$text .= "(" . $pa['pa_name'] . ")";
				if ($pa['pa_years'] > 0)
					$text .= " for " . $pa['pa_years'] . " years\r\n";
				$text .= $pa['pa_control'] . "\r\n";
				if ($pa['medication'] != "")
					$text .= "Taking " . $pa['pa_medication'];
				if ($pa['pa_notes'] != "")
					$text .= "(Notes: " . $pa['pa_notes'] . ")";
				$text .= "\r\n";
			}
			$texts[1] = $text;
			$this->AddRowNode ($block_id, $columns, $widths, $texts, $aligns, $fonts, $boxes);
		}

		return $block_id;

	}

	function CreateRABlock ($rap, $rac, $block_width, $x, $y, $margin) {

		$block_id = $this->CreateBlock($block_width, $x, $y, $margin);
		$this->AddTextNode($block_id, "Oswald||18|0xF3C76F", "C", $rap['block_type'] == 'RESCUE_BLOCK' ? "RESCUE BLOCK" : "RA BLOCK");

		// Common Row Parameters
		$columns = 2;
		$widths = array(25, 75);
		$aligns = array("LM", "LM");
		$fonts = array("Oswald||10|0xa0a0a0", "Oswald||10|0x0");
		$boxes = array("||", "0xe0e0e0||");

		$texts[0] = "Procedure";
		if ($rap['procedure'] != null)
			$texts[1] = $rap['procedure']['procedure_name'];
		else if ($rap['my_procedure'] != null)
			$texts[1] = $rap['my_procedure']['refvalue'];
		else
			$texts[1] = "Not specified";
		$this->AddRowNode ($block_id, $columns, $widths, $texts, $aligns, $fonts, $boxes);

		$texts[0] = "Landmark";
		$texts[1] = ($rap['is_landmark']) ? $rap['landmark_used'] : "None";
		$this->AddRowNode ($block_id, $columns, $widths, $texts, $aligns, $fonts, $boxes);

		$texts[0] = "Probe";
		$texts[1] = ($rap['is_ultrasound']) ? $rap['ultrasound_probe'] : "None";
		$this->AddRowNode ($block_id, $columns, $widths, $texts, $aligns, $fonts, $boxes);

		$texts[0] = "PNS";
		$texts[1] = ($rap['is_pns']) ? $rap['pns_current'] . "mA" : "None";
		$this->AddRowNode ($block_id, $columns, $widths, $texts, $aligns, $fonts, $boxes);

		$texts[0] = "Drugs";
		$text = "";
		foreach ($rap['ra_drug_list'] as $drug) {
			$text .= $drug['drug_volume'] . "ml of " . $drug['drug']['drug_name'] . " @ " . $drug['drug_concentration'] . "%\r\n";
		}
		$texts[1] = $text;
		$this->AddRowNode ($block_id, $columns, $widths, $texts, $aligns, $fonts, $boxes);

		$texts[0] = "Adjuvant";
		if ($rap['adjuvant']['adjuvant_id'] == 0)
			$texts[1] = "No Adjuvants";
		else if ($rap['adjuvant_volume'] > 1)
			$texts[1] = round($rap['adjuvant_volume'], 3) . "mg of " . $rap['adjuvant']['adjuvant_name'];
		else
			$texts[1] = round($rap['adjuvant_volume'] * 1000, 3) . "mcg of " . $rap['adjuvant']['adjuvant_name'];
		$this->AddRowNode ($block_id, $columns, $widths, $texts, $aligns, $fonts, $boxes);

		if ($rap['indwelling_catheter'] != "") {
			$texts[0] = "Indwelling Catheter";
			$texts[1] = $rap['indwelling_catheter'];
			$this->AddRowNode ($block_id, $columns, $widths, $texts, $aligns, $fonts, $boxes);
		}

		// Get Closure Details
		if ($rac != null) {
			$texts[0] = "Block Closure";
			$text = "Duration : " . $rac['block_duration'] . "Hrs\r\n";
			if ($rac['catheter_removed_on'] != null) {
				$text .= "Catheter Removed on : " . date("M j,Y", strtotime($rac['catheter_removed_on'])) . "\r\n";
			}
			if ($rac['postop_regimen'] == "Infusion") {
				$text .= "Infusion : " . $rac['infusion_drug_rate'] . "ml/Hr of " . $rac['infusion_drug']['drug_name'];
				$text .= " @ " . $rac['infusion_drug_concentration'] . "%\r\n";
			}
			else if ($rac['postop_regimen'] == "Bolus") {
				$text .= "Bolus : " . $rac['bolus_drug_volume'] . "ml of " . $rac['bolus_drug']['drug_name'];
				$text .= " @ " . $rac['bolus_drug_concentration'] . "% every " . $rac['bolus_interval'] . " " . $rac['bolus_interval_unit'];
				$text .= " for " . $rac['bolus_days'] . " days\r\n";
			}
			$texts[1] = $text;
			$this->AddRowNode ($block_id, $columns, $widths, $texts, $aligns, $fonts, $boxes);

		}

		return $block_id;
	}

	function CreatePOBlock ($userlog, $block_width, $x, $y, $margin) {

		$po = $userlog['post_operative'];

		$block_id = $this->CreateBlock($block_width, $x, $y, $margin);
		$this->AddTextNode($block_id, "Oswald||18|0xF3C76F", "C", "POST OPERATIVE");

		// Common Row Parameters
		$columns = 2;
		$widths = array(25, 75);
		$aligns = array("LM", "LM");
		$fonts = array("Oswald||10|0xa0a0a0", "Oswald||10|0x0");
		$boxes = array("||", "0xe0e0e0||");

		// $texts[0] = "Catheter Removed On";
		// $texts[1] = $po['catheter_removed_on'];
		// $this->AddRowNode ($block_id, $columns, $widths, $texts, $aligns, $fonts, $boxes);

		$texts[0] = "Notes";
		$texts[1] = $po['post_operative_notes'];
		$this->AddRowNode ($block_id, $columns, $widths, $texts, $aligns, $fonts, $boxes);

		$texts[0] = "Complications";
		$text = "";
		foreach ($po['complication_list'] as $complication) {
			$text .= $complication['complication_type'] . ": ";
			if ($complication['complication'] != "") {
				if ($complication['my_complication'] != null)
					$text .= $complication['my_complication']['refvalue'];
				else
					$text .= $complication['complication'];
			}
		}
		$texts[1] = $text;
		$this->AddRowNode ($block_id, $columns, $widths, $texts, $aligns, $fonts, $boxes);

		return $block_id;
	}

	function PrintLog($userlog, $seq_no) {
		$this->PrintCaseTitle($userlog, $seq_no);

		$blocks = [];
		$num_blocks = 0;
		$block_spacing = 8;
		$block_width = round(($this->GetPageWidth() - (2 * $this->GetMargin()) - (1 * $block_spacing)) / 2, 0);

		// Surgery Details
		$y = $this->current_y + 2;
		$x = $this->GetMargin();
		$blocks[0] = $this->CreateSurgeryBlock($userlog, $block_width, $x, $y, 4);
		$x += $block_width + $block_spacing;

		// PATIENT DETAILS
		$blocks[1] = $this->CreatePatientBlock($userlog, $block_width, $x, $y, 4);

		$y = $this->PositionAndPrintBlocks($blocks);
		$y += 2;
		$x = $this->GetMargin();
		$this->RemoveAllBlocks();
		$blocks = [];

		// RA Blocks
		foreach ($userlog["ra_procedure_list"] as $rap) {
			if ($rap["block_type"] == "RA_BLOCK") {
				$rac = null;
				foreach ($userlog['ra_closure_list'] as $rac)
					if ($rac['ra_id'] == $rap['ra_id'])
						break;
				$blocks[] = $this->CreateRABlock($rap, $rac, $block_width, $x, $y, 4);
				if (sizeof($blocks) == 2) {
					$y = $this->PositionAndPrintBlocks($blocks);
					$y += 2;
					$x = $this->GetMargin();
					$this->RemoveAllBlocks();
					$blocks = [];
				}
				else {
					$x += $block_width + $block_spacing;
				}
			}
		}

		// RESCUE Blocks
		foreach ($userlog["ra_procedure_list"] as $rap) {
			if ($rap["block_type"] == "RESCUE_BLOCK") {
				$rac = null;
				foreach ($userlog['ra_closure_list'] as $rac)
					if ($rac['ra_id'] == $rap['ra_id'])
						break;
				$blocks[] = $this->CreateRABlock($rap, $rac, $block_width, $x, $y, 4);
				if (sizeof($blocks) == 2) {
					$y = $this->PositionAndPrintBlocks($blocks);
					$y += 2;
					$x = $this->GetMargin();
					$this->RemoveAllBlocks();
					$blocks = [];
				}
				else {
					$x += $block_width + $block_spacing;
				}
			}
		}

		// Post Operative & Complications
		if ($userlog['post_operative'] != null)	{
			$blocks[] = $this->CreatePOBlock($userlog, $block_width, $x, $y, 4);

			$y = $this->PositionAndPrintBlocks($blocks);
			$this->RemoveAllBlocks();
			$blocks = [];
		}
		else {
			if (sizeof($blocks) > 0) {
				$y = $this->PositionAndPrintBlocks($blocks);
				$this->RemoveAllBlocks();
				$blocks = [];
			}
		}
		$this->current_x = $this->GetMargin();
		$this->current_y = $y + 16;	// leave some footer space

	}

}

// Global Definitions
// ==================
function load_message ($file, $pairs) {
	$message = file_get_contents($file);
	if ($message == "")
		return "";

	$message = replace_values($message, $pairs);

	return $message;
}

function send_mail($to, $subject, $plain_text, $html_message, $attachment_name, $attachment) {
	global $counterr;
	global $mail_failed;
	global $mail_suffix;

	// Produce output as a string and encode it for sending
	$pdfdoc = chunk_split(base64_encode($attachment));

	// Send Email
	// Note to be able to add attachment, this is being sent as multipart email.
	// Multipart emails require a boundary string to be defined and used for each section.
	// It also requires additional headers to be passed as part of the body with proper line
	// separations (with empty lines) between header and mail content.
	// Refer RFC specification at https://www.w3.org/Protocols/rfc1341/7_2_Multipart.html for more information
	// Prepare headers to send with attachment
	$boundary_mail = md5("ypsbengaluru.com/mail"); 		// define boundary with a md5 hashed value
	$boundary_message = md5("ypsbengaluru.com/message");

	// $to = "aora4u@aoraindia.com";

	$headers  = "MIME-Version: 1.0\r\n";
	$headers .= "From: <aora4u@aoraindia.com>\r\n";
	$headers .= "CC: <aora4u@aoraindia.com>\r\n";			// Will turn it off after some time
	$headers .= "Content-type:multipart/mixed;\r\n";
	$headers .= "    boundary=\"$boundary_mail\"";

	// Prepare Body Message in Plain Text as well as in HTML
	$body  = "\r\n--$boundary_mail\r\n";
	$body .= "Content-type: multipart/alternative;\r\n";
	$body .= "   boundary=\"$boundary_message\"\r\n\r\n";
	// Plain Text
	$body .= "--$boundary_message\r\n";
	$body .= "Content-type: text/plain; charset=UTF-8\r\n\r\n";
	$body .= $plain_text . "\r\n\r\n";
	// HTML Text
	$body .= "--$boundary_message\r\n";
	$body .= "Content-type: text/html; charset=UTF-8\r\n\r\n";
	$body .= $html_message . "\r\n\r\n";
	$body .= "--$boundary_message--\r\n\r\n";
	// Attachment
	$body .= "--$boundary_mail\r\n";
	$body .="Content-type: application/pdf; name=" . $attachment_name . "\r\n";
	$body .="Content-Disposition: attachment; filename=" . $attachment_name . "\r\n";
	$body .="Content-Transfer-Encoding: base64\r\n";
	$body .="X-Attachment-Id: " . rand(1000, 99999) . "\r\n\r\n";
	$body .= $pdfdoc . "\r\n\r\n"; // Attaching the encoded file with email

	$body .= "--$boundary_mail--\r\n";


	// debug_dump("HTTP_HOST", $_SERVER['HTTP_HOST'], __FILE__, __LINE__);

	if (strpos($_SERVER['HTTP_HOST'], "localhost") == false && strpos($_SERVER['HTTP_HOST'], "10.0.2.2") == false) {
		// debug_dump("MAIL", "Sending Mail Message", __FILE__, __LINE__);
		// Send Email
		$sentMailResult = mail($to, $subject, $body, $headers);

		if( ! $sentMailResult) {
			debug_dump("Mail Send Failure", $to, __FILE__, __LINE__);
			die_with_error("Error sending the email. Please try again!");
		}
	}
	else {
		if (! file_exists("mails"))
			mkdir("mails");
		// debug_dump("MAIL", "Saving HTML Mail Message", __FILE__, __LINE__);
		file_put_contents("mails/mail_" . date("Y_m_d_H_i_s") . sprintf("_%04d", ++ $mail_suffix) . ".htm", $html_message);
	}
}


$_REQUEST = json_decode(file_get_contents('php://input'), true);
// debug_dump("REQUEST", $_REQUEST, __FILE__, __LINE__);

if (isset($_REQUEST['yearmonth']) && isset($_REQUEST['profile_id']) && isset($_REQUEST['output']) ) {

    $yearmonth = $_REQUEST['yearmonth'];
    $profile_id = $_REQUEST['profile_id'];

	$sql = "SELECT * FROM contest WHERE yearmonth = '$yearmonth' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$contest = mysqli_fetch_array($query);
	$contest_archived = ($contest['archived'] == '1');

	// Validate Participation and load profile details
	if ($contest_archived)
		$sql  = "SELECT * FROM ar_entry entry, profile ";
	else
		$sql  = "SELECT * FROM entry, profile ";
	$sql .= " WHERE yearmonth = '$yearmonth' AND entry.profile_id = '$profile_id' AND profile.profile_id = entry.profile_id ";
	$query = mysqli_query($DBCON, $sql)  or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
    if (mysqli_num_rows($query) <= 0) {
        echo encodeError("User not found");
        exit;
    }
	$entry = mysqli_fetch_array($query);

	// Load number of jury and total rating per picture
	$sql = "SELECT section, count(*) AS num_juries FROM assignment WHERE yearmonth = '$yearmonth' GROUP BY section ";
	$juries_per_section_list = [];
	$query = mysqli_query($DBCON, $sql)  or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	while ($row = mysqli_fetch_array($query))
		$juries_per_section_list[$row['section']] = $row['num_juries'];

	// Get Picture Results Array
	$sql  = "SELECT pic.section, pic.pic_id, title, picfile, pic.tags, IFNULL(level, 999) AS level, IFNULL(award_name, '') AS award_name, ";
	$sql .= "       IFNULL(has_medal, 0) AS has_medal, IFNULL(has_pin, 0) AS has_pin, IFNULL(has_ribbon, 0) AS has_ribbon, ";
	$sql .= "       IFNULL(has_memento, 0) AS has_memento, IFNULL(has_gift, 0) AS has_gift, IFNULL(has_certificate, 0) AS has_certificate, ";
	$sql .= "       IFNULL(cash_award, 0.0) AS cash_award, SUM(rating) AS total_rating ";
	if ($contest_archived) {
		$sql .= "  FROM ar_rating rating, ";
		$sql .= "       ar_pic pic LEFT JOIN (ar_pic_result pic_result INNER JOIN award) ";
	}
	else {
		$sql .= "  FROM rating, ";
		$sql .= "       pic LEFT JOIN (pic_result INNER JOIN award) ";
	}
	$sql .= "           ON pic_result.yearmonth = pic.yearmonth ";
	$sql .= "           AND pic_result.profile_id = pic.profile_id ";
	$sql .= "           AND pic_result.pic_id = pic.pic_id ";
	$sql .= "           AND award.yearmonth = pic_result.yearmonth ";
	$sql .= "           AND award.award_id = pic_result.award_id ";
	$sql .= "           AND award.section = 'CONTEST' ";
	$sql .= " WHERE pic.yearmonth = '$yearmonth' ";
	$sql .= "   AND pic.profile_id = '$profile_id' ";
	$sql .= "   AND rating.yearmonth = pic.yearmonth ";
	$sql .= "   AND rating.profile_id = pic.profile_id ";
	$sql .= "   AND rating.pic_id = pic.pic_id ";
	$sql .= " GROUP BY section, pic_id, title, picfile, tags, level, award_name, has_medal, has_pin, has_ribbon, has_memento, ";
	$sql .= "          has_gift, has_certificate, cash_award ";



	$sql .= " WHERE pic_result.yearmonth = '$jury_yearmonth' ";
	$sql .= "   AND pic_result.profile_id = '$profile_id' ";
	$sql .= "   AND award.yearmonth = pic_result.yearmonth ";
	$sql .= "   AND award.award_id = pic_result.award_id ";
	$sql .= "   AND award.section != 'CONTEST' ";
	$sql .= "   AND award.award_type = 'pic' ";
	$sql .= "   AND award.level != 99 ";
	$sql .= "   AND pic.yearmonth = pic_result.yearmonth ";
	$sql .= "   AND pic.profile_id = pic_result.profile_id ";
	$sql .= "   AND pic.pic_id = pic_result.pic_id ";
	$sql .= "   AND section.yearmonth = pic.yearmonth ";
	$sql .= "   AND section.section = pic.section ";
	$sql .= "   AND profile.profile_id = pic_result.profile_id ";
	$sql .= " ORDER BY award.section, award.level ";

		// GLOBAL
		$report_user_id = $user['user_id'];
		$report_user_name = $user['user_name'];
		$report_user_email = $user['email'];

			// Get Profile
		$sql  = "SELECT hospital_name, city_name, pin, IFNULL(COUNT(*), 0) AS num_cases ";
		$sql .= "  FROM city, hospital ";
		$sql .= "  LEFT JOIN userlog ON ";
		$sql .= "            userlog.user_id = '$report_user' ";
		$sql .= "        AND userlog.hospital_id = hospital.hospital_id ";
		$sql .= "        AND userlog.log_date BETWEEN '$report_from' AND '$report_to' ";
		$sql .= " WHERE hospital.hospital_id IN (SELECT hospital_id FROM user_hospital WHERE user_id = '$report_user_id' ) ";
		$sql .= "   AND city.city_id = hospital.city_id ";
		$sql .= " GROUP BY hospital_name, city_name, pin ";
		$sql .= " ORDER BY hospital_name ";

		$hq = mysqli_query($A4UDB, $sql)  or sql_error($sql, mysqli_error($A4UDB), __FILE__, __LINE__);
		$hospitals = [];
		while ($hr = mysqli_fetch_array($hq))
			$hospitals[] = $hr;
		$user['hospital_list'] = $hospitals;


		// Create a PDF Document in A5 format
		$rpdf = new RPDF("P", "pt", "A4", 72);	// 1 inch margin

		// Set Font
		$font = "Oswald";
		//$font_php = "Oswald.php";
		$font_php = "Oswald-Regular.ttf";
		// $pdf->AddFont($font, '', $font_php);
		$rpdf->AddFont($font, '', $font_php, true);

		// Set Page Layout
		$rpdf->AddPage();

		// Write Report Header
		$rpdf->PrintReportHeader($report_from, $report_to);

		// Write User Profile
		$rpdf->PrintProfile($user);

		// Top 3 Items
		$rpdf->PrintStats(get_top3_stats($report_user, $report_from, $report_to));

		//
		// Procedures Dump
		//
		$rpdf->PrintLogHeader();

		$sql = "SELECT log_id FROM userlog WHERE user_id = '$report_user' AND log_date BETWEEN '$report_from' AND '$report_to' ORDER BY log_date, time_start ";
		// debug_dump("SQL", $sql, __FILE__, __LINE__);
		$logq = mysqli_query($A4UDB, $sql)  or sql_error($sql, mysqli_error($A4UDB), __FILE__, __LINE__);
		if (mysqli_num_rows($logq) == 0) {
			$rpdf->PrintNoLogsMessage();
		}
		else {
			$seq_no = 0;
			while ($logr = mysqli_fetch_array($logq)) {
				$userlog = get_userlog($logr['log_id']);
				// debug_dump("userlog", $userlog, __FILE__, __LINE__);
				++ $seq_no;
				$rpdf->PrintLog($userlog, $seq_no);
				//break;
			}
		}

		// Ready to send as email attachment
		// Prepare Subject and Message
		$subject = "AORA - Your Monthly Casebook for " . date("F Y", strtotime($report_from));
		$replace_values = array("[url]" => "http://aoraindia.com", "[member-name]" => $user["user_name"],
								"[month-name]" => date("F Y", strtotime($report_from)));
		$html = load_message("./monthly_report.htm", $replace_values);

		// Prepare plain text and html messages
		$plain_text  = "Dear " . $user['user_name'] . ",\r\n\r\n";
		$plain_text .= "Monthly Case Book for " . date("F Y", strtotime($report_from)) . "\r\n";
		$plain_text .= "===========================================================\r\n\r\n";
		$plain_text .= "Please find attached your monthly casebook for " . date("F Y", strtotime($report_from)) . ".\r\n\r\n";
		$plain_text .= "AORA INDIA\r\n";
		$plain_text .= "Web: http://aoraindia.com\r\n";


		$attachment_name = "AORA4U_CaseBook_" . date("M_Y", strtotime($report_from)) . ".pdf";

		send_mail($user['email'], $subject, $plain_text, $html, $attachment_name, $rpdf->Output("", "S"));

	}

}
else
	echo encodeError("Invalid Parameter");
?>
