<?php
/*
** New certificate.php
** Generic program to generate certificate as PDF using redefined format specifications using Blocks and Nodes
** July 8, 2020
** Murali Santhanam
**
** Revision : July 29, 2020
** Features : 1. Support for vertical and inverted text
**            2. Make Blocks Generic instead of specifying as award_block etc.
**            3. Assemble Data and try to populate values based on field names
**            4. Add support for conditional blocks
*/
session_save_path(__DIR__ . "/../inc/session");
session_start();
require("../inc/connect.php");
require("../inc/lib.php");
require("../inc/cpdf.php");
include("../inc/certdef.php");

require("../inc/honors.php");

function toConsole($data) {
    // $output = $data;
    // if (is_array($output))
    //    $output = implode(',', $output);
    // echo "<script>console.log('Debug Objects: " . $output . "' );</script>";
}

// Add a printable node to the pdf block
function add_node_to_pdf_block(&$pdf, $block_id, $node) {

	global $target_folder;

	if ($node['nodetype'] == "text") {
		$font = $node['font_family'] . "|" . $node['font_style'] . "|" . $node['font_size'] . "|" . $node['font_color'];
		// $font = 'Arial' . "|" . $node['font_style'] . "|" . $node['font_size'] . "|" . $node['font_color'];
	    toConsole($font);
		$posx = isset($node['posx']) ? $node['posx'] : 0;
		$posy = isset($node['posy']) ? $node['posy'] : 0;
		$pdf->AddTextNode($block_id, $font, $node['align'], $node['value'], $node['line_spacing'], $node['height'], $posx, $posy);
	    toConsole('Added text node');
	}
	else if ($node['nodetype'] == "image") {

		if (isset($node['bordertype']) && $node['bordertype'] != "none") {
			if ($node["bordertype"] == "frame")
				// $border = "frame|" . $node["borderwidth"] . "|" . $target_folder . "/img/" . $node["borderimage"];
				$border = "frame|" . $node["borderwidth"] . "|" . $node["borderimage"];
			else
				$border = "border|" . $node['borderwidth'] . "|" . $node['bordercolor'];
		}
		else
			$border = "";

		$float = isset($node["float"]) ? $node["float"] : "fill";
		$spacing = isset($node["spacing"]) ? $node["spacing"] : 0;

		// Set image location with reference to op folder
		// if ( $node['type'] == "label" && (!empty($node['value'])) && (preg_match("/^\.*\//", $node['value']) == 0) ) {
		// 	$node['value'] = $target_folder . "/img/" . $node['value'];
		// }
		$pdf->AddImageNode($block_id, $node['value'], "", $border, $float, $spacing);
	}
}

function create_pdf_block(&$pdf, $cert, $block_name) {

	global $target_folder;

	$block = $cert->getBlock($block_name);
	if ($block->border_width != 0) {
		$box = $block->fill_color . "|" . $block->border_width . "|" . $block->border_color;
		$margin = ceil($block->border_width);
	}
	else {
		$box = "";
		$margin = 0;
	}

	$orientation =  (isset($block->orientation) ? $block->orientation : "N");
	$block_id = $pdf->CreateBlock($block->type, $block->width, $block->height, $block->x, $block->y, $margin, $box, $orientation);

	if ($block_id) {
		// Add Award Nodes
		$nodes = $cert->getNodesForBlock($block_name);
		// debug_dump("nodes", $nodes, __FILE__, __LINE__);
		foreach ($nodes as $node_name => $node) {
			if (isset($node['value'])) {
				if ($node['type'] == 'label') {
					toConsole('trying to add note to pdf block 0');
					add_node_to_pdf_block($pdf, $block_id, $node);
					toConsole('added note to pdf block 0');
				}
				elseif ($node['type'] == 'field') {
					if (trim($node['value']) == "") {
						if (isset($node["omit_if_empty"]) && $node["omit_if_empty"] == "yes") {
							cert_error("Omitting $node_name for empty value", __FILE__, __LINE__);
						}
						else {
        					toConsole('trying to add note to pdf block 1');
							add_node_to_pdf_block($pdf, $block_id, $node);
							toConsole('added note to pdf block 1');
						}
					}
					else {
    					toConsole('trying to add note to pdf block 2');
						add_node_to_pdf_block($pdf, $block_id, $node);
						toConsole('added note to pdf block 2');
					}
				}
				else {
					cert_error("Unknown node type " . $node['type'], __FILE__, __LINE__);
				}
			}
			else
				cert_error("Omitting $node_name. No value set.", __FILE__, __LINE__);
		}
	}
	return $block_id;
}

function create_pdf_page_from_certificate (&$pdf, $cert, $data) {

    toConsole('inside create_pdf_page_from_certificate');
    
	global $yearmonth;

	// Create a Blank Certificate and add
	$cert_bg = $cert->getTemplate($data['level']);
	toConsole('got template');
	$pdf->CreatePage("../salons/$yearmonth/img/" . $cert_bg);
    toConsole('finished CreatePage');
    
	// Create all the blocks using the values in the certificate
	foreach ($cert->getListOfBlocks() as $block_name => $block) {
	    toConsole('inside for');
		if ($cert->isBlockPrintable($block_name, $data)) {
		    toConsole('finished isBlockPrintable');
			if (! create_pdf_block($pdf, $cert, $block_name)) {
			    toConsole('errors occured while generating certificate');
				cert_error("Certificate design has errors. Printing $block_name Failed. Report to YPS!", __FILE__, __LINE__);
				die();
			}
		}
	}

    toConsole('finished for');
    
	// Done adding - Print the Blocks
	$pdf->PrintAllBlocks();
    toConsole('done 1');
    
	// Remove all the blocks
	$pdf->RemoveAllBlocks();
	toConsole('done 2');

}

function cert_error($err_msg, $file, $line) {
	log_error($err_msg, $file, $line);
	// die();
}

function cert_sql_error($sql, $err_msg, $file, $line) {
	log_sql_error($sql, $err_msg, $file, $line);
	log_error("Data Operation Failed", $file, $line);
	die();
}

// custom sort function by name without salutation
function sort_by_jury_name($jury1, $jury2) {
	$jury1_name = preg_replace("/^([^ ]+) (.*)$/", "$2", $jury1['user_name']);		// name without the salutation
	$jury2_name = preg_replace("/^([^ ]+) (.*)$/", "$2", $jury2['user_name']);		// name without the salutation
	if ($jury1_name == $jury2_name)
		return 0;
	return ($jury1_name < $jury2_name ? -1 : 1);
}

function add_sponsor_details($yearmonth, $award_id, $sponsorship_no, &$cert, &$data) {
	global $DBCON;

	$sql  = "SELECT award_name_suffix, sponsor_name, sponsor_logo, sponsor_website, sponsorship.sponsor_id ";
	$sql .= "  FROM sponsorship, sponsor ";
	$sql .= " WHERE sponsorship.yearmonth = '$yearmonth' ";
	$sql .= "   AND sponsorship.sponsorship_type = 'AWARD' ";
	$sql .= "   AND sponsorship.link_id = '$award_id' ";
	$sql .= "   AND sponsorship.sponsorship_no = '$sponsorship_no' ";
	$sql .= "   AND sponsor.sponsor_id = sponsorship.sponsor_id ";
	$spq = mysqli_query($DBCON, $sql) or cert_sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_num_rows($spq) != 0) {

		$spr = mysqli_fetch_array($spq, MYSQLI_ASSOC);
		$award_name_suffix = $spr['award_name_suffix'];
		$sponsor_name = $spr['sponsor_name'];
		$sponsor_logo = $spr['sponsor_logo'];
		$sponsor_website = $spr['sponsor_website'];
		$sponsor_id = $spr['sponsor_id'];

		// Set Certificate Field Values
		if ($sponsor_logo == "")
			$cert->setNodeValue("sponsor_logo", "");
		else
			$cert->setNodeValue("sponsor_logo", $_SERVER['DOCUMENT_ROOT'] . "/res/sponsor/" . $sponsor_logo);
		$cert->setNodeValue("custom_award_name", $award_name_suffix);
		$cert->setNodeValue("sponsor_name", $sponsor_name);
		$cert->setNodeValue("sponsor_website", $sponsor_website);

		// Set values in award $res row for data match for conditions
		$data = array_merge($data, $spr);
	}
}

function generate_pic_certificate(&$pdf, $yearmonth, $award_param, $profile_param, $pic_param, $award) {
    toConsole('inside generate pic certificate');
	global $DBCON;
	global $table_pic;
	global $table_pic_result;
	global $target_folder;
	global $json;
	global $contest_archived;

	$sql  = "SELECT profile.profile_name, profile.honors, profile.avatar, pic.title, pic.section AS pic_section, pic.picfile, ";
	$sql .= "       pic_result.sponsorship_no ";
	$sql .= "  FROM profile, $table_pic AS pic, $table_pic_result AS pic_result ";
	$sql .= " WHERE pic_result.yearmonth = '$yearmonth' ";
	$sql .= "   AND pic_result.award_id = '" . $award['award_id'] . "' ";
	$sql .= "   AND pic.yearmonth = pic_result.yearmonth ";
	$sql .= "   AND pic.profile_id = pic_result.profile_id ";
	$sql .= "   AND pic.pic_id = pic_result.pic_id ";
	$sql .= "   AND profile.profile_id = pic_result.profile_id ";
	if ($award_param == "PROFILE") {
		$sql .= "   AND pic_result.profile_id = '$profile_param' ";
	}
	elseif ($award_param == "SECTION") {
		// Do nothing - selection already done under award
	}
	else {
		// Certificate for single picture
		$sql .= "   AND pic_result.profile_id = '$profile_param' ";
		$sql .= "   AND pic_result.pic_id = '$pic_param' ";
	}
	// debug_dump("SQL", $sql, __FILE__, __LINE__);

	$query = mysqli_query($DBCON, $sql) or cert_sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	while ( $res = mysqli_fetch_array($query, MYSQLI_ASSOC) ) {
        toConsole('inside while loop');
		$res = array_merge($res, $award);

		$section = ($award_param == "SECTION") ? $profile_param : $res['section'];

		// Add Jury List to the fetched row. No jury is assigned to "CONTEST" section
		$jury_list = [];
		$sql  = "SELECT user_name, honors FROM assignment, user ";
		$sql .= " WHERE assignment.yearmonth = '$yearmonth' ";
		$sql .= "   AND assignment.section = '$section' ";
		$sql .= "   AND user.user_id = assignment.user_id ";
		$jrq = mysqli_query($DBCON, $sql) or cert_sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		while ($jrr = mysqli_fetch_array($jrq, MYSQLI_ASSOC))
			$jury_list[] = $jrr;

		// Sort jury by name
		usort($jury_list, "sort_by_jury_name");

		// Add to the results
		$res['jury_list'] = $jury_list;

		// Create a New Certificate
		$cert = new Certificate($json);

		// Assign Field Values for certificate
		// These are standard set of values associated with predefined names
		$award_section = ($section == "CONTEST" ? "OVERALL" : $section);
		$cert->setNodeValue("author_name", $res['profile_name']);
		$cert->setNodeValue("honors", honors_text($res['honors']));
		$cert->setNodeValue("section_award_name", $award_section . " - " . $award['award_name']);
		$cert->setNodeValue("award_name", $award['award_name']);
		$cert->setNodeValue("award_section", $award_section);
		$cert->setNodeValue("pic_title", $res['title']);
		$cert->setNodeValue("picfile", $target_folder . "/upload/" . $res['pic_section'] . ($contest_archived ? "/ar/" : "/") . $res['picfile']);
        toConsole('done with node values');
		if ($res['avatar'] != "" && $res['avatar'] != "user.jpg" && $res['avatar'] != "user.png")
			$cert->setNodeValue("author_avatar", $_SERVER['DOCUMENT_ROOT'] . "/res/avatar/" . $res['avatar']);

		if ($section != "CONTEST") {
			$cert->setNodeValue("jury_section", "JURY - " . $award_section);
			if (isset($jury_list[0])) {
				$cert->setNodeValue("jury_name_1", $jury_list[0]['user_name']);
				$cert->setNodeValue("jury_honors_1", $jury_list[0]['honors']);
			}
			if (isset($jury_list[1])) {
				$cert->setNodeValue("jury_name_2", $jury_list[1]['user_name']);
				$cert->setNodeValue("jury_honors_2", $jury_list[1]['honors']);
			}
			if (isset($jury_list[2])) {
				$cert->setNodeValue("jury_name_3", $jury_list[2]['user_name']);
				$cert->setNodeValue("jury_honors_3", $jury_list[2]['honors']);
			}
		}

		// Chairman and Secretary Signature
		toConsole($target_folder);
		$cert->setNodeValue("chairman_sig", $target_folder . "/img/com/chairman_sig.png");
		$cert->setNodeValue("secretary_sig", $target_folder . "/img/com/secretary_sig.png");
        toConsole('done with chairman and secretary signature');
        
		// Add Sponsor Details if present
		// Load Sponsorship Information if available
		if ($res['sponsorship_no'] > 0) {
			add_sponsor_details($yearmonth, $award['award_id'], $res['sponsorship_no'], $cert, $res);
		}
		toConsole('done with sponsor details');
		
		// debug_dump("cert", $cert, __FILE__, __LINE__);
		create_pdf_page_from_certificate($pdf, $cert, $res);
		toConsole('finished generating certificate');
	}
}

include_once("../inc/cert_share_image.php");

function generate_entry_certificate(&$pdf, $yearmonth, $award_param, $profile_param, $pic_param, $award) {
	global $DBCON;
	global $table_pic;
	global $table_pic_result;
	global $target_folder;
	global $json;
	global $contest_archived;

	// Get data from entry_result
	$sql  = "SELECT entry_result.profile_id, profile_name, honors, avatar, sponsorship_no ";
	$sql .= "  FROM entry_result, profile ";
	$sql .= " WHERE entry_result.yearmonth = '$yearmonth' ";
	$sql .= "   AND entry_result.award_id = '" . $award['award_id'] . "' ";
	$sql .= "   AND profile.profile_id = entry_result.profile_id ";
	if ($award_param == "PROFILE")
		$sql .= "   AND entry_result.profile_id = '$profile_param' ";
	$sql .= " ORDER BY ranking ";
	// debug_dump("SQL", $sql, __FILE__, __LINE__);
	$query = mysqli_query($DBCON, $sql) or cert_sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	while ( $res = mysqli_fetch_array($query, MYSQLI_ASSOC) ) {
		$res = array_merge($res, $award);

		// Create a New Certificate
		$cert = new Certificate($json);

		// Assign Field Values for certificate
		// These are standard set of values associated with predefined names
		$cert->setNodeValue("author_name", $res['profile_name']);
		$cert->setNodeValue("honors", honors_text($res['honors']));
		$cert->setNodeValue("section_award_name", $award['award_name']);
		$cert->setNodeValue("award_name", $award['award_name']);
		// $cert->setNodeValue("award_section", $award_section);
		// $cert->setNodeValue("pic_title", $res['title']);
		$pic_node = $cert->getNode("picfile");
		$pic_block = (array) $cert->getBlock($pic_node['block']);
		$cert->setNodeValue("picfile", cert_share_image($yearmonth, $res['profile_id'], $pic_block['width'], $pic_block['height']));
		if ($res['avatar'] != "" && $res['avatar'] != "user.jpg" && $res['avatar'] != "user.png")
			$cert->setNodeValue("author_avatar", $_SERVER['DOCUMENT_ROOT'] . "/res/avatar/" . $res['avatar']);

		// Chairman and Secretary Signature
		$cert->setNodeValue("chairman_sig", $target_folder . "/img/com/chairman_sig.png");
		$cert->setNodeValue("secretary_sig", $target_folder . "/img/com/secretary_sig.png");

		// Add Sponsor Details if present
		// Load Sponsorship Information if available
		if ($res['sponsorship_no'] > 0) {
			add_sponsor_details($yearmonth, $award['award_id'], $res['sponsorship_no'], $cert, $res);
		}

		// debug_dump("cert", $cert, __FILE__, __LINE__);
		create_pdf_page_from_certificate($pdf, $cert, $res);
	}
}


// $_REQUEST['cert'] = "202008|2|672|6";	// Asif
// $_REQUEST['cert'] = encode_string_array("202108|2|1242|4");	// FIP Medal for Himadri Bhuyan
// $_REQUEST['cert'] = encode_string_array("202108|201|1242|6");	// Dr. Thomas award for Himadri Bhuyan
// $_REQUEST['cert'] = encode_string_array("202108|1004|1242|13");	// Acceptance for Himadri Bhuyan
// $_REQUEST['cert'] = encode_string_array("202108|SECTION|MONOCHROME|0");	// Awards for Monochrome Section
// $_REQUEST['cert'] = encode_string_array("202108|CONTEST|0|0");	// Contest Level Awards
// $yearmonth = 202108;
// $award_id = 2;
// $profile_id = 1242;
// $pic_id = 4;

if (isset($_REQUEST['cert'])) {
	list($yearmonth, $award_id, $profile_id, $pic_id) = explode("|", decode_string_array($_REQUEST['cert']));
	// 1 - Certificate for 1 picture : all fields to have required values
	// 2 - All Certificates for 1 profile : $award_id = "PROFILE" & $pic_id = "ALL"
	// 3 - All Certificates for 1 section : $award_id = "SECTION" & $profile_id = section & $pic_id's value ignored
	// 4 - All Certificates for 1 section for printing : $award_id = "SECTION" & $profile_id = section & $pic_id = "PRINT"
	if ($award_id == "SECTION")
		$section = $profile_id;
	else
		$section = "";

	// Get contest details
	$sql = "SELECT * FROM contest WHERE yearmonth = '$yearmonth' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$contest = mysqli_fetch_array($query);
	$contest_archived = ($contest['archived'] == '1');

	// Open Certificate Definition
	$target_folder = $_SERVER['DOCUMENT_ROOT'] . "/salons/" . $yearmonth;
	toConsole($target_folder);
	if (file_exists($target_folder . "/blob/certdef.json")) {
	    toConsole('target json exists');
		$json_text = file_get_contents($target_folder . "/blob/certdef.json");
		$json = json_decode($json_text, true); 	// Create as associative array
		toConsole($json);
		if (json_last_error() != JSON_ERROR_NONE) {
			cert_error("Error on Certificate Definition", __FILE__, __LINE__);
			die();
		}
	}
	else {
		cert_error("Unable to find Certification Definition file certdef.json", __FILE__, __LINE__);
		die();
	}

	// Get Award Details
	if ($contest_archived) {
		$table_pic = "ar_pic";
		$table_pic_result = "ar_pic_result";
	}
	else {
		$table_pic = "pic";
		$table_pic_result = "pic_result";
	}
	// Select Awards to be printed
	$sql  = "SELECT award.award_id, level, sequence, section, award_type, award_name, recognition_code, sponsored_awards ";
	$sql .= "  FROM award ";
	$sql .= " WHERE yearmonth = '$yearmonth' ";
	if ($award_id == "SECTION") {
		$sql .= "   AND section = '$section' ";
		if ($section != "CONTEST")
			$sql .= "   AND level < 99 ";				// Only for Awarded Pictures when printing for entire section
	}
	elseif ($award_id == "PROFILE") {
		$sql .= "   AND award_id IN ( ";
		$sql .= "       SELECT pic_result.award_id FROM $table_pic_result AS pic_result ";
		$sql .= "        WHERE pic_result.yearmonth = award.yearmonth ";
		$sql .= "          AND pic_result.profile_id = '$profile_id' ";
		$sql .= "       UNION ";
		$sql .= "       SELECT entry_result.award_id FROM entry_result ";
		$sql .= "        WHERE entry_result.yearmonth = award.yearmonth ";
		$sql .= "          AND entry_result.profile_id = '$profile_id' ";
		$sql .=	"   ) ";
	}
	else {
		$sql .= "   AND award_id = '$award_id' ";
	}
	$sql .= " ORDER BY award_type, section, level, sequence ";
	// debug_dump("SQL", $sql, __FILE__, __LINE__);

	$award_query = mysqli_query($DBCON, $sql) or cert_sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_num_rows($award_query) > 0) {
		// We may have something to print - Create PDF
		$orientation = $json['doc']['page']['page_orientation'];
		$unit = $json['doc']['page']['unit'];
		$width = $json['doc']['page']['page_width'];
		$height = $json['doc']['page']['page_height'];
		$pdf_file_stub = $json['doc']['files']['file_name_stub'];
		$pdf = new cPDF($orientation, $unit, array($width, $height), 0, ($award_id == "SECTION" && $pic_id == "PRINT"));
		$pdf->SetAutoPageBreak(false, 0);

		// Register Fonts
		$font = $json['doc']['font'];
		// foreach ($json['doc']['fonts'] as $font) {
		    $pdf->default_font = $font['font_family'];
			// Regular Font
			$pdf->AddFont($font['font_family'], '', $font['font_regular'], true);
			// Bold Font
			$pdf->AddFont($font['font_family'], 'B', $font['font_bold'], true);
			// Italics
			$pdf->AddFont($font['font_family'], 'I', $font['font_italic'], true);
		// }

        toConsole($font);
        
		// Process each award
		while ($award = mysqli_fetch_array($award_query, MYSQLI_ASSOC)) {
		    toConsole($award);
			if ($award['award_type'] == 'pic') {
			    toConsole('Generating pic certificate');
				generate_pic_certificate($pdf, $yearmonth, $award_id, $profile_id, $pic_id, $award);
			}
			elseif ($award['award_type'] == 'entry') {
			    toConsole('Generating entry certificate');
				generate_entry_certificate($pdf, $yearmonth, $award_id, $profile_id, $pic_id, $award);
			}
		}

		// Generate the Certificate
		ob_end_clean();
		$pdf->Output(sprintf("%s-%s-%s-%s.pdf", $pdf_file_stub, $award_id, $profile_id, $pic_id), "D");
	}
	else
		$_SESSION['err_msg'] = "No Award found for this code";
}
else
	$_SESSION['err_msg'] = "Invalid Parameters";
?>
