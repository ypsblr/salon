<?php
/*
** certdef.php - Contains Print Definitions for the Certificate
** A separate file is created for each Salon depending upon Certificate Design
**
** Certificate Definition for All India Digital Salon 2020
*/
class Certificate {
	// Constants
	// Text Colors
	const COLOR_LABEL = 0x927C4D;
	const COLOR_FIELD = 0xA93C3A;
	const COLOR_SUBDUED = 0x808080;
	const COLOR_HIGHLIGHT = 0xF02020;

	const COLOR_LOGO_RED = 0xFF0000;
	// const COLOR_LOGO_GRAY = 0x96999C;
	// const COLOR_LOGO_BLUE = 0x4285F4;
	// const COLOR_LOGO_GREEN = 0x34A853;
	// const COLOR_LOGO_YELLOW = 0xFEBC11;
	// const COLOR_LOGO_RED = 0xEE3537;
	const COLOR_LOGO_GRAY = 0x96999C;
	const COLOR_LOGO_BLUE = 0x5D82C1;
	const COLOR_LOGO_GREEN = 0x44AC5B;
	const COLOR_LOGO_YELLOW = 0xFDBE30;
	const COLOR_GOLD = 0xA48438;
	const COLOR_BLACK = 0x404040;

	// Dimensions
	const UNIT = "pt";
	// const WIDTH = 595;	// A4
	// const HEIGHT = 842;	// A4
	const WIDTH = 599;	// A4
	const HEIGHT = 871;	// A4
	const ORIENTATION = (self::WIDTH >= self::HEIGHT) ? "L" : "P";

	// Fonts
	const FONT_FAMILY = "Gotham Condensed";
	const FONT_REGULAR = "GothamCond-Light.ttf";
	const FONT_BOLD = "GothamCond-Book.ttf";
	// const FONT_BOLD = "GothamCond-Medium.ttf";
	const FONT_ITALIC = "GothamCond-LightItalic.ttf";

	// Other Constants
	const FILE_NAME_STUB = "YPS-ALL-INDIA-SALON-2020";

	const LOG_ERROR = "no";

	// Protected
	// protected $templates = array ("1" => "IS_2020_CERT.jpg", "2" => "IS_2020_CERT.jpg", "3" => "IS_2020_CERT.jpg",
	// 								"4" => "IS_2020_CERT.jpg", "5" => "IS_2020_CERT.jpg", "9" => "IS_2020_CERT.jpg",
	// 								"99" => "IS_2020_CERT.jpg");
	// protected $templates_cm = array ("1" => "AIS_2020_CERT_FIP_GOLD_CM.jpg", "2" => "AIS_2020_CERT_YPS_GOLD_CM.jpg", "3" => "AIS_2020_CERT_YPS_SILVER_CM.jpg",
	// 								"4" => "AIS_2020_CERT_YPS_BRONZE_CM.jpg", "5" => "AIS_2020_CERT_YPS_YOUTH_CM.jpg", "9" => "AIS_2020_CERT_FIP_HM_CM.jpg",
	// 								"99" => "AIS_2020_CERT_ACCEPTANCE_CM.jpg");
	protected $template_award = "ais2021_cert_award.png";
	protected $template_acceptance = "ais2021_cert_acceptance.png";
	protected $template_cm = "ais2021_cert.png";

	protected $blocks = array (
							// Picture & Title
							"pic_block" => array("type" => "list", "x" => 40, "y" => 470, "width" => 220, "height" => 160, "orientation" => "N",
														"fill_color" => "", "border_width" => "0", "border_color" => "",
														"print" => "no" ),
							"pic_title_block" => array("type" => "list", "x" => 40, "y" => 635, "width" => 220, "height" => 20, "orientation" => "N",
														"fill_color" => "", "border_width" => "0", "border_color" => "",
														"print" => "no" ),
							// Avatar, Authir Name, Award, Section
							"avatar_block" => array("type" => "tile", "x" => 285, "y" => 530, "width" => 45, "height" => 45, "orientation" => "N",
														"fill_color" => "", "border_width" => "0", "border_color" => "",
														"print" => "no" ),
							"award_block" => array("type" => "list", "x" => 340, "y" => 530, "width" => 215, "height" => 110, "orientation" => "N",
														"fill_color" => "", "border_width" => "0", "border_color" => "",
														"print" => "no" ),

							// Jury List
							"jury_block" => array("type" => "list", "x" => 35, "y" => 160, "width" => 160, "height" => 70, "orientation" => "F",
														"fill_color" => "", "border_width" => "0", "border_color" => "",
														"print" => "no" ),
							// Sponsor
							"sponsor_logo_block" => array("type" => "tile", "x" => 505, "y" => 160, "width" => 55, "height" => 70, "orientation" => "F",
														"fill_color" => "", "border_width" => "0", "border_color" => "",
														"if" => array("field" => ["sponsorship_no"], "match" => [], "notmatch" => ["0"]),
														"print" => "no" ),
							"sponsor_block" => array("type" => "list", "x" => 200, "y" => 160, "width" => 290, "height" =>70, "orientation" => "F",
														"fill_color" => "", "border_width" => "0", "border_color" => "",
														"if" => array("field" => ["sponsorship_no"], "match" => [], "notmatch" => ["0"]),
														"print" => "no" ),
							// Special Awards
							// Dr. G Thomas
							"thomas_logo_block" => array("type" => "tile", "x" => 505, "y" => 160, "width" => 55, "height" => 70, "orientation" => "F",
														"fill_color" => "", "border_width" => "0", "border_color" => "",
														"if" => array("field" => ["award_id"], "match" => ["201"], "notmatch" => []),
														"print" => "yes" ),
							"thomas_block" => array("type" => "list", "x" => 200, "y" => 160, "width" => 290, "height" =>70, "orientation" => "F",
														"fill_color" => "", "border_width" => "0", "border_color" => "",
														"if" => array("field" => ["award_id"], "match" => ["201"], "notmatch" => []),
														"print" => "yes" ),

							// C Rajagopal
							"craj_logo_block" => array("type" => "tile", "x" => 505, "y" => 160, "width" => 55, "height" => 70, "orientation" => "F",
														"fill_color" => "", "border_width" => "0", "border_color" => "",
														"if" => array("field" => ["award_id"], "match" => ["202"], "notmatch" => []),
														"print" => "yes" ),
							"craj_block" => array("type" => "list", "x" => 200, "y" => 160, "width" => 290, "height" =>70, "orientation" => "F",
														"fill_color" => "", "border_width" => "0", "border_color" => "",
														"if" => array("field" => ["award_id"], "match" => ["202"], "notmatch" => []),
														"print" => "yes" ),
							// TNA Perumal
							"tnap_logo_block" => array("type" => "tile", "x" => 505, "y" => 160, "width" => 55, "height" => 70, "orientation" => "F",
														"fill_color" => "", "border_width" => "0", "border_color" => "",
														"if" => array("field" => ["award_id"], "match" => ["203"], "notmatch" => []),
														"print" => "yes" ),
							"tnap_block" => array("type" => "list", "x" => 200, "y" => 160, "width" => 290, "height" =>70, "orientation" => "F",
														"fill_color" => "", "border_width" => "0", "border_color" => "",
														"if" => array("field" => ["award_id"], "match" => ["203"], "notmatch" => []),
														"print" => "yes" ),
							// M Y Ghorpade
							"myg_logo_block" => array("type" => "tile", "x" => 505, "y" => 160, "width" => 55, "height" => 70, "orientation" => "F",
														"fill_color" => "", "border_width" => "0", "border_color" => "",
														"if" => array("field" => ["award_id"], "match" => ["204"], "notmatch" => []),
														"print" => "yes" ),
							"myg_block" => array("type" => "list", "x" => 200, "y" => 160, "width" => 290, "height" => 70, "orientation" => "F",
														"fill_color" => "", "border_width" => "0", "border_color" => "",
														"if" => array("field" => ["award_id"], "match" => ["204"], "notmatch" => []),
														"print" => "yes" ),
							// E Hanumantharao
							"hrao_logo_block" => array("type" => "tile", "x" => 505, "y" => 160, "width" => 55, "height" => 70, "orientation" => "F",
														"fill_color" => "", "border_width" => "0", "border_color" => "",
														"if" => array("field" => ["award_id"], "match" => ["205"], "notmatch" => []),
														"print" => "yes" ),
							"hrao_block" => array("type" => "list", "x" => 200, "y" => 160, "width" => 290, "height" => 70, "orientation" => "F",
														"fill_color" => "", "border_width" => "0", "border_color" => "",
														"if" => array("field" => ["award_id"], "match" => ["205"], "notmatch" => []),
														"print" => "yes" ),
							// BNS Deo
							"bnsd_logo_block" => array("type" => "tile", "x" => 505, "y" => 160, "width" => 55, "height" => 70, "orientation" => "F",
														"fill_color" => "", "border_width" => "0", "border_color" => "",
														"if" => array("field" => ["award_id"], "match" => ["206"], "notmatch" => []),
														"print" => "yes" ),
							"bnsd_block" => array("type" => "list", "x" => 200, "y" => 160, "width" => 290, "height" =>70, "orientation" => "F",
														"fill_color" => "", "border_width" => "0", "border_color" => "",
														"if" => array("field" => ["award_id"], "match" => ["206"], "notmatch" => []),
														"print" => "yes" ),
							// O C Edwards
							"oced_logo_block" => array("type" => "tile", "x" => 505, "y" => 160, "width" => 55, "height" => 70, "orientation" => "F",
														"fill_color" => "", "border_width" => "0", "border_color" => "",
														"if" => array("field" => ["award_id"], "match" => ["207"], "notmatch" => []),
														"print" => "yes" ),
							"oced_block" => array("type" => "list", "x" => 200, "y" => 160, "width" => 290, "height" =>70, "orientation" => "F",
														"fill_color" => "", "border_width" => "0", "border_color" => "",
														"if" => array("field" => ["award_id"], "match" => ["207"], "notmatch" => []),
														"print" => "yes" ),
							// D V Rao
							"dvrao_logo_block" => array("type" => "tile", "x" => 505, "y" => 160, "width" => 55, "height" => 70, "orientation" => "F",
														"fill_color" => "", "border_width" => "0", "border_color" => "",
														"if" => array("field" => ["award_id"], "match" => ["208"], "notmatch" => []),
														"print" => "yes" ),
							"dvrao_block" => array("type" => "list", "x" => 200, "y" => 160, "width" => 290, "height" =>70, "orientation" => "F",
														"fill_color" => "", "border_width" => "0", "border_color" => "",
														"if" => array("field" => ["award_id"], "match" => ["208"], "notmatch" => []),
														"print" => "yes" ),
							// Acceptance
							"accept_logo_block" => array("type" => "tile", "x" => 505, "y" => 160, "width" => 55, "height" => 70, "orientation" => "F",
														"fill_color" => "", "border_width" => "0", "border_color" => "",
														"if" => array("field" => ["level"], "match" => ["99"], "notmatch" => []),
														"print" => "yes" ),
							"accept_block" => array("type" => "list", "x" => 200, "y" => 160, "width" => 290, "height" =>70, "orientation" => "F",
														"fill_color" => "", "border_width" => "0", "border_color" => "",
														"if" => array("field" => ["level"], "match" => ["99"], "notmatch" => []),
														"print" => "yes" ),
							);

	protected $nodes = array (
						// Picture Node
						"picfile" => array("nodetype" => "image", "type" => "field", "block" => "pic_block", "sequence" => 1,
											 "float" => "fill", "bordertype" => "none", "borderwidth" => 4, "spacing" => 0,
											 "borderimage" => "" ),
						// Picture Title
						"title" => array("nodetype" => "text", "type" => "field", "block" => "pic_title_block", "sequence" => 2,
										  	"font_family" => self::FONT_FAMILY, "font_style" => "B", "font_size" => 14, "font_color" => self::COLOR_LOGO_GRAY,
									    	"align" => "C", "line_spacing" => 1.2, "height" => 0 ),
						// Avatar
						"avatar" => array("nodetype" => "image", "type" => "field", "block" => "avatar_block", "sequence" => 1,
											 "float" => "fill", "bordertype" => "none", "borderwidth" => 4, "spacing" => 0,
											 "borderimage" => "" ),

						// For Award Block
						"author_name" => array("nodetype" => "text", "type" => "field", "block" => "award_block", "sequence" => 1,
										"font_family" => self::FONT_FAMILY, "font_style" => "B", "font_size" => 18, "font_color" => self::COLOR_LOGO_RED,
									    "align" => "L", "line_spacing" => 1.0, "height" => 0 ),

						"honors" => array("nodetype" => "text", "type" => "field", "block" => "award_block", "sequence" => 2, "omit_if_empty" => "yes",
										  "font_family" => self::FONT_FAMILY, "font_style" => "B", "font_size" => 10, "font_color" => self::COLOR_LOGO_GRAY,
									    "align" => "L", "line_spacing" => 1.1, "height" => 0 ),

						"award_blank_1" => array("nodetype" => "text", "type" => "label", "value" => "", "block" => "award_block", "sequence" => 3,
										  	"font_family" => self::FONT_FAMILY, "font_style" => "", "font_size" => 8, "font_color" => self::COLOR_LABEL,
									    	"align" => "L", "line_spacing" => 1, "height" => 0 ),

						"award_label_1" => array("nodetype" => "text", "type" => "label", "value" => "on winning", "block" => "award_block", "sequence" => 4,
										  	"font_family" => self::FONT_FAMILY, "font_style" => "", "font_size" => 12, "font_color" => self::COLOR_LOGO_GRAY,
									    	"align" => "L", "line_spacing" => 1.2, "height" => 0, "group" => "award" ),

						"award_name_alone" => array("nodetype" => "text", "type" => "field", "block" => "award_block", "sequence" => 5,
										  	"font_family" => self::FONT_FAMILY, "font_style" => "B", "font_size" => 14, "font_color" => self::COLOR_LOGO_RED,
									    	"align" => "L", "line_spacing" => 1.2, "height" => 0, "group" => "award" ),

						"award_section" => array("nodetype" => "text", "type" => "field", "template" => "in [value] section", "block" => "award_block", "sequence" => 7,
										  	"font_family" => self::FONT_FAMILY, "font_style" => "B", "font_size" => 12, "font_color" => self::COLOR_LOGO_GRAY,
									    	"align" => "L", "line_spacing" => 1.2, "height" => 0, "group" => "title", ),

						// Jury Block
						"jury_section" => array("nodetype" => "text", "type" => "field", "template" => "JURY - [value]", "block" => "jury_block", "sequence" => 1,
										  	"font_family" => self::FONT_FAMILY, "font_style" => "B", "font_size" => 14, "font_color" => self::COLOR_LOGO_RED,
									    	"align" => "R", "line_spacing" => 1.0, "height" => 0 ),

						"jury_blank_1" => array("nodetype" => "text", "type" => "label", "value" => "", "block" => "jury_block", "sequence" => 2,
										  	"font_family" => self::FONT_FAMILY, "font_style" => "", "font_size" => 8, "font_color" => self::COLOR_LOGO_RED,
									    	"align" => "L", "line_spacing" => 1.0, "height" => 0, "group" => "", ),

						"jury_name_1" => array("nodetype" => "text", "type" => "field", "block" => "jury_block", "sequence" => 3,
										  "font_family" => self::FONT_FAMILY, "font_style" => "B", "font_size" => 12, "font_color" => self::COLOR_BLACK,
									    "align" => "R", "line_spacing" => 1.1, "height" => 0 ),

						"jury_name_2" => array("nodetype" => "text", "type" => "field", "block" => "jury_block", "sequence" => 4,
										  "font_family" => self::FONT_FAMILY, "font_style" => "B", "font_size" => 12, "font_color" => self::COLOR_BLACK,
									    "align" => "R", "line_spacing" => 1.1, "height" => 0 ),

						"jury_name_3" => array("nodetype" => "text", "type" => "field", "block" => "jury_block", "sequence" => 5,
										  "font_family" => self::FONT_FAMILY, "font_style" => "B", "font_size" => 12, "font_color" => self::COLOR_BLACK,
									    "align" => "R", "line_spacing" => 1.1, "height" => 0 ),

						// For Sponsor Block
						"sponsor_logo" => array("nodetype" => "image", "type" => "field", "block" => "sponsor_logo_block", "sequence" => 1,
											   "float" => "right", "bordertype" => "border", "borderwidth" => 0.5, "bordercolor" => self::COLOR_BLACK,
											   "spacing" => 0, "border_image" => "" ),

						"sponsor_label_1" => array("nodetype" => "text", "type" => "label", "value" => "Award Sponsor", "block" => "sponsor_block", "sequence" => 2,
										  	"font_family" => self::FONT_FAMILY, "font_style" => "B", "font_size" => 14, "font_color" => self::COLOR_LOGO_RED,
									    	"align" => "L", "line_spacing" => 1.0, "height" => 0, "group" => "", ),

						"sponsor_blank_1" => array("nodetype" => "text", "type" => "label", "value" => "", "block" => "sponsor_block", "sequence" => 3,
										  	"font_family" => self::FONT_FAMILY, "font_style" => "", "font_size" => 8, "font_color" => self::COLOR_LOGO_RED,
									    	"align" => "L", "line_spacing" => 1.0, "height" => 0, "group" => "", ),

						"sponsor_name" => array("nodetype" => "text", "type" => "field", "block" => "sponsor_block", "sequence" => 4,
										  	"font_family" => self::FONT_FAMILY, "font_style" => "B", "font_size" => 14, "font_color" => self::COLOR_BLACK,
									    	"align" => "L", "line_spacing" => 1.1, "height" => 0, "group" => "", ),

						"custom_award_name" => array("nodetype" => "text", "type" => "field", "block" => "sponsor_block", "sequence" => 5, "omit_if_empty" => "yes",
										  	"font_family" => self::FONT_FAMILY, "font_style" => "I", "font_size" => 10, "font_color" => self::COLOR_LOGO_GRAY,
									    	"align" => "L", "line_spacing" => 1.2, "height" => 0, "group" => "" ),

						"sponsor_website" => array("nodetype" => "text", "type" => "field", "block" => "sponsor_block", "sequence" => 6, "omit_if_empty" => "yes",
										  	"font_family" => self::FONT_FAMILY, "font_style" => "B", "font_size" => 8, "font_color" => self::COLOR_LOGO_BLUE,
									    	"align" => "L", "line_spacing" => 1.0, "height" => 0, "group" => "", ),

						// Special Award Blocks
						// Dr. G Thomas
						"thomas_avatar" => array("nodetype" => "image", "type" => "label", "value" => __DIR__ . "/img/thomas.jpg",
												"block" => "thomas_logo_block", "sequence" => 1,
											   	"align" => "left", "bordertype" => "border", "borderwidth" => 0.5, "bordercolor" => self::COLOR_BLACK,
											   	"float" => "right" ),

						"thomas_name" => array("nodetype" => "text", "type" => "label", "value" => "Dr. G Thomas", "block" => "thomas_block", "sequence" => 1,
										  	"font_family" => self::FONT_FAMILY, "font_style" => "B", "font_size" => 14, "font_color" => self::COLOR_LOGO_RED,
									    	"align" => "L", "line_spacing" => 1.0, "height" => 0, "group" => "", ),

						"thomas_honors" => array("nodetype" => "text", "type" => "label",
											"value" => "FPSA, FRPS, Hon.FPSA, Hon.FRPS, Hon.EFIAP, Hon.FNPAS, Hon.PSI, Hon.YPS",
											"block" => "thomas_block", "sequence" => 2,
										  	"font_family" => self::FONT_FAMILY, "font_style" => "I", "font_size" => 8, "font_color" => self::COLOR_LOGO_GRAY,
									    	"align" => "L", "line_spacing" => 1.1, "height" => 0, "group" => "" ),

						"thomas_blank_1" => array("nodetype" => "text", "type" => "label", "value" => "", "block" => "thomas_block", "sequence" => 3,
										  	"font_family" => self::FONT_FAMILY, "font_style" => "", "font_size" => 6, "font_color" => self::COLOR_LOGO_RED,
									    	"align" => "L", "line_spacing" => 1.0, "height" => 0, "group" => "", ),

						"thomas_about" => array("nodetype" => "text", "type" => "label",
											"value" => "Dr. G Thomas (1907-1993) steered the growth of photography in India through 40 years serving as " .
											 			"the Secretary General of the Federation of Indian Photography. His masterpieces were created in the " .
														"rural ambience of places he travelled for medical practice. Dr. Thomas was honored by all the top " .
														"photographic organizations for his masterpieces. Download and read Drsti June 2020 from " .
														"ypsbengaluru.com for a full account of this legend and his works.",
											"block" => "thomas_block", "sequence" => 4,
										  	"font_family" => self::FONT_FAMILY, "font_style" => "", "font_size" => 8, "font_color" => self::COLOR_BLACK,
									    	"align" => "J", "line_spacing" => 1.0, "height" => 0, "group" => "" ),

						// C Rajagopal
						"craj_avatar" => array("nodetype" => "image", "type" => "label", "value" => __DIR__ . "/img/crajagopal.jpg",
												"block" => "craj_logo_block", "sequence" => 1,
											   	"align" => "left", "bordertype" => "border", "borderwidth" => 0.5, "bordercolor" => self::COLOR_BLACK,
											   	"float" => "right" ),

						"craj_name" => array("nodetype" => "text", "type" => "label", "value" => "C Rajagopal",
											"block" => "craj_block", "sequence" => 1,
										  	"font_family" => self::FONT_FAMILY, "font_style" => "B", "font_size" => 14, "font_color" => self::COLOR_LOGO_RED,
									    	"align" => "L", "line_spacing" => 1.0, "height" => 0, "group" => "", ),

						"craj_honors" => array("nodetype" => "text", "type" => "label",
											"value" => "MFIAP, FRPS, FPSA, Hon.FRPS, Hon.FPSA, Hon.EFIAP, Hon.YPS",
											"block" => "craj_block", "sequence" => 2,
										  	"font_family" => self::FONT_FAMILY, "font_style" => "I", "font_size" => 8, "font_color" => self::COLOR_LOGO_GRAY,
									    	"align" => "L", "line_spacing" => 1.1, "height" => 0, "group" => "" ),

						"craj_blank_1" => array("nodetype" => "text", "type" => "label", "value" => "", "block" => "craj_block", "sequence" => 3,
										  	"font_family" => self::FONT_FAMILY, "font_style" => "", "font_size" => 6, "font_color" => self::COLOR_LOGO_RED,
									    	"align" => "L", "line_spacing" => 1.0, "height" => 0, "group" => "", ),

						"craj_about" => array("nodetype" => "text", "type" => "label",
											"value" => "Sri Chakravarty Rajagopal (1926-2005) is a master of photography techniques and a pictorial artist. " .
											 			"He produced some of the most creative and poetic works of art marking a glorious chapter in Indian " .
														"photography. Sri Rajagopal took to the art of photography at the age of 20, a legacy from his father. " .
														"He excelled in producing early morning scenes showing rays of light. He called it the 'Line of Light'.  " .
														"Check Drsti August 2020 edition at ypsbengaluru.com for a full account of this legend and his works.",
											"block" => "craj_block", "sequence" => 4,
										  	"font_family" => self::FONT_FAMILY, "font_style" => "", "font_size" => 8, "font_color" => self::COLOR_BLACK,
									    	"align" => "J", "line_spacing" => 1.0, "height" => 0, "group" => "" ),

						// T N A Perumal
						"tnap_avatar" => array("nodetype" => "image", "type" => "label", "value" => __DIR__ . "/img/tnaperumal.jpg",
												"block" => "tnap_logo_block", "sequence" => 1,
											   	"align" => "left", "bordertype" => "border", "borderwidth" => 0.5, "bordercolor" => self::COLOR_BLACK,
											   	"float" => "right" ),

						"tnap_name" => array("nodetype" => "text", "type" => "label", "value" => "T N A Perumal",
											"block" => "tnap_block", "sequence" => 1,
										  	"font_family" => self::FONT_FAMILY, "font_style" => "B", "font_size" => 14, "font_color" => self::COLOR_LOGO_RED,
									    	"align" => "L", "line_spacing" => 1.0, "height" => 0, "group" => "", ),

						"tnap_honors" => array("nodetype" => "text", "type" => "label",
											"value" => "MFIAP, FRPS, Hon.FIP, Hon.FIIPC, Hon.YPS",
											"block" => "tnap_block", "sequence" => 2,
										  	"font_family" => self::FONT_FAMILY, "font_style" => "I", "font_size" => 8, "font_color" => self::COLOR_LOGO_GRAY,
									    	"align" => "L", "line_spacing" => 1.1, "height" => 0, "group" => "" ),

						"tnap_blank_1" => array("nodetype" => "text", "type" => "label", "value" => "", "block" => "tnap_block", "sequence" => 3,
										  	"font_family" => self::FONT_FAMILY, "font_style" => "", "font_size" => 6, "font_color" => self::COLOR_LOGO_RED,
									    	"align" => "L", "line_spacing" => 1.0, "height" => 0, "group" => "", ),

						"tnap_about" => array("nodetype" => "text", "type" => "label",
											"value" => "Sri Thanjavur Nateshachary Ayyamperumal (1932-2017) was a hunter turned wildlife photographer. His " .
											 			"accidental meeting with Mr. O C Edwards changed his life so much that he gave up his job as a Radio " .
														"Engineer and became Chief Photographer with Mr. M Y Ghorpade. He served on the board of Mysore Photography " .
														"Club. He was also the Secretary of FIP Nature Division in the early 70s. Enjoy the articles on his works " .
														"in Drsti February 2021 edition at ypsbengaluru.com.",
											"block" => "tnap_block", "sequence" => 4,
										  	"font_family" => self::FONT_FAMILY, "font_style" => "", "font_size" => 8, "font_color" => self::COLOR_BLACK,
									    	"align" => "J", "line_spacing" => 1.0, "height" => 0, "group" => "" ),

						// M Y Ghorpade
						"myg_avatar" => array("nodetype" => "image", "type" => "label", "value" => __DIR__ . "/img/ghorpade.jpg",
												"block" => "myg_logo_block", "sequence" => 1,
											   	"align" => "left", "bordertype" => "border", "borderwidth" => 0.5, "bordercolor" => self::COLOR_BLACK,
											   	"float" => "right" ),

						"myg_name" => array("nodetype" => "text", "type" => "label", "value" => "M Y Ghorpade",
											"block" => "myg_block", "sequence" => 1,
										  	"font_family" => self::FONT_FAMILY, "font_style" => "B", "font_size" => 14, "font_color" => self::COLOR_LOGO_RED,
									    	"align" => "L", "line_spacing" => 1.0, "height" => 0, "group" => "", ),

						"myg_honors" => array("nodetype" => "text", "type" => "label",
											"value" => "MFIAP, FRPS",
											"block" => "myg_block", "sequence" => 2,
										  	"font_family" => self::FONT_FAMILY, "font_style" => "I", "font_size" => 8, "font_color" => self::COLOR_LOGO_GRAY,
									    	"align" => "L", "line_spacing" => 1.1, "height" => 0, "group" => "" ),

						"myg_blank_1" => array("nodetype" => "text", "type" => "label", "value" => "", "block" => "myg_block", "sequence" => 3,
										  	"font_family" => self::FONT_FAMILY, "font_style" => "", "font_size" => 6, "font_color" => self::COLOR_LOGO_RED,
									    	"align" => "L", "line_spacing" => 1.0, "height" => 0, "group" => "", ),

						"myg_about" => array("nodetype" => "text", "type" => "label",
											"value" => "Sri Murarirao Yeshwantrao Ghorpade (1931-2011) is a direct decendant of the royal family of Sandur in " .
											 			"Bellary district. His photograph 'Tusker in Rain' set the world of wildlife photography on fire and " .
														"became an aspiration of many photographers. He penned his experiences in the wild in his book 'Sunlight " .
														"and Shadows: an Indian Wildlife Photographer's Diary' published by Penguin Books. Meet the royal " .
														"photographer in Drsti December 2020 edition at ypsbengaluru.com.",
											"block" => "myg_block", "sequence" => 4,
										  	"font_family" => self::FONT_FAMILY, "font_style" => "", "font_size" => 8, "font_color" => self::COLOR_BLACK,
									    	"align" => "J", "line_spacing" => 1.0, "height" => 0, "group" => "" ),

						// E Hanumantha Rao
						"hrao_avatar" => array("nodetype" => "image", "type" => "label", "value" => __DIR__ . "/img/hanumantharao.jpg",
												"block" => "hrao_logo_block", "sequence" => 1,
											   	"align" => "left", "bordertype" => "border", "borderwidth" => 0.5, "bordercolor" => self::COLOR_BLACK,
											   	"float" => "right" ),

						"hrao_name" => array("nodetype" => "text", "type" => "label", "value" => "E Hanumantha Rao",
											"block" => "hrao_block", "sequence" => 1,
										  	"font_family" => self::FONT_FAMILY, "font_style" => "B", "font_size" => 14, "font_color" => self::COLOR_LOGO_RED,
									    	"align" => "L", "line_spacing" => 1.0, "height" => 0, "group" => "", ),

						"hrao_honors" => array("nodetype" => "text", "type" => "label",
											"value" => "AFIAP, Hon.YPS",
											"block" => "hrao_block", "sequence" => 2,
										  	"font_family" => self::FONT_FAMILY, "font_style" => "I", "font_size" => 8, "font_color" => self::COLOR_LOGO_GRAY,
									    	"align" => "L", "line_spacing" => 1.1, "height" => 0, "group" => "" ),

						"hrao_blank_1" => array("nodetype" => "text", "type" => "label", "value" => "", "block" => "hrao_block", "sequence" => 3,
										  	"font_family" => self::FONT_FAMILY, "font_style" => "", "font_size" => 6, "font_color" => self::COLOR_LOGO_RED,
									    	"align" => "L", "line_spacing" => 1.0, "height" => 0, "group" => "", ),

						"hrao_about" => array("nodetype" => "text", "type" => "label",
											"value" => "Sri Eshwar Hanumantha Rao (1930-2004) is one of the commercially successful photographers. He bought " .
											 			"his first camera while he was still in school. His association with Mysore Photographic Society " .
														"turned him into a wildlife photographer. He has contributed pictures to more than 1400 books and " .
														"publications including the National Geographic. Despite his success, he was a down-to-earth team-man. Find a " .
														"coverage of Sri Hanumantha Rao in Drsti January 2021 edition at ypsbengaluru.com.",
											"block" => "hrao_block", "sequence" => 4,
										  	"font_family" => self::FONT_FAMILY, "font_style" => "", "font_size" => 8, "font_color" => self::COLOR_BLACK,
									    	"align" => "J", "line_spacing" => 1.0, "height" => 0, "group" => "" ),

						// B N S Deo
						"bnsd_avatar" => array("nodetype" => "image", "type" => "label", "value" => __DIR__ . "/img/bnsdeo.jpg",
												"block" => "bnsd_logo_block", "sequence" => 1,
											   	"align" => "left", "bordertype" => "border", "borderwidth" => 0.5, "bordercolor" => self::COLOR_BLACK,
											   	"float" => "right" ),

						"bnsd_name" => array("nodetype" => "text", "type" => "label", "value" => "B N S Deo",
											"block" => "bnsd_block", "sequence" => 1,
										  	"font_family" => self::FONT_FAMILY, "font_style" => "B", "font_size" => 14, "font_color" => self::COLOR_LOGO_RED,
									    	"align" => "L", "line_spacing" => 1.0, "height" => 0, "group" => "", ),

						"bnsd_honors" => array("nodetype" => "text", "type" => "label",
											"value" => "FRPS, AFIAP",
											"block" => "bnsd_block", "sequence" => 2,
										  	"font_family" => self::FONT_FAMILY, "font_style" => "I", "font_size" => 8, "font_color" => self::COLOR_LOGO_GRAY,
									    	"align" => "L", "line_spacing" => 1.1, "height" => 0, "group" => "" ),

						"bnsd_blank_1" => array("nodetype" => "text", "type" => "label", "value" => "", "block" => "bnsd_block", "sequence" => 3,
										  	"font_family" => self::FONT_FAMILY, "font_style" => "", "font_size" => 6, "font_color" => self::COLOR_LOGO_RED,
									    	"align" => "L", "line_spacing" => 1.0, "height" => 0, "group" => "", ),

						"bnsd_about" => array("nodetype" => "text", "type" => "label",
											"value" => "Sri Bhupendra Narayansingh Deo (1922-1995) was the Maharaja of Koriya, Chattisgarh prior to India's " .
											 			"Independence. He followed the footsteps of Mr. O C Edwards to become a wildlife photographer. His " .
														"understanding of wildlife developed during many hunting expeditions with his father helped him " .
														"capture nature at its best. Find an in-depth coverage of Sri Deo's life and works in the November " .
														"2020 edition of Drsti at ypsbengaluru.com.",
											"block" => "bnsd_block", "sequence" => 4,
										  	"font_family" => self::FONT_FAMILY, "font_style" => "", "font_size" => 8, "font_color" => self::COLOR_BLACK,
									    	"align" => "J", "line_spacing" => 1.0, "height" => 0, "group" => "" ),

						// O C Edwards
						"oced_avatar" => array("nodetype" => "image", "type" => "label", "value" => __DIR__ . "/img/ocedwards.jpg",
												"block" => "oced_logo_block", "sequence" => 1,
											   	"align" => "left", "bordertype" => "border", "borderwidth" => 0.5, "bordercolor" => self::COLOR_BLACK,
											   	"float" => "right" ),

						"oced_name" => array("nodetype" => "text", "type" => "label", "value" => "O C Edwards",
											"block" => "oced_block", "sequence" => 1,
										  	"font_family" => self::FONT_FAMILY, "font_style" => "B", "font_size" => 14, "font_color" => self::COLOR_LOGO_RED,
									    	"align" => "L", "line_spacing" => 1.0, "height" => 0, "group" => "", ),

						"oced_honors" => array("nodetype" => "text", "type" => "label",
											"value" => "ARPS, EFIAP",
											"block" => "oced_block", "sequence" => 2,
										  	"font_family" => self::FONT_FAMILY, "font_style" => "I", "font_size" => 8, "font_color" => self::COLOR_LOGO_GRAY,
									    	"align" => "L", "line_spacing" => 1.1, "height" => 0, "group" => "" ),

						"oced_blank_1" => array("nodetype" => "text", "type" => "label", "value" => "", "block" => "oced_block", "sequence" => 3,
										  	"font_family" => self::FONT_FAMILY, "font_style" => "", "font_size" => 6, "font_color" => self::COLOR_LOGO_RED,
									    	"align" => "L", "line_spacing" => 1.0, "height" => 0, "group" => "", ),

						"oced_about" => array("nodetype" => "text", "type" => "label",
											"value" => "Mr. Oswald Carnac Edwards (1907-1988) carries credit for bringing bird photography to Karnataka. " .
											 			"A mathematcis teacher by profession, he was one of the founder members of Mysore Photography Club. " .
														"The 'Viewfinder' magazine started by him at MYPS continues to be published by FIP. " .
														"Many leading photographers credit Mr. Edwards for taking up wildlife photography. Get a " .
														"glimpse of this great man in Drsti September 2020 edition at ypsbengaluru.com.",
											"block" => "oced_block", "sequence" => 4,
										  	"font_family" => self::FONT_FAMILY, "font_style" => "", "font_size" => 8, "font_color" => self::COLOR_BLACK,
									    	"align" => "J", "line_spacing" => 1.0, "height" => 0, "group" => "" ),

						// D V Rao
						"dvrao_avatar" => array("nodetype" => "image", "type" => "label", "value" => __DIR__ . "/img/dvrao.jpg",
												"block" => "dvrao_logo_block", "sequence" => 1,
											   	"align" => "left", "bordertype" => "border", "borderwidth" => 0.5, "bordercolor" => self::COLOR_BLACK,
											   	"float" => "right" ),

						"dvrao_name" => array("nodetype" => "text", "type" => "label", "value" => "D V Rao",
											"block" => "dvrao_block", "sequence" => 1,
										  	"font_family" => self::FONT_FAMILY, "font_style" => "B", "font_size" => 14, "font_color" => self::COLOR_LOGO_RED,
									    	"align" => "L", "line_spacing" => 1.0, "height" => 0, "group" => "", ),

						"dvrao_honors" => array("nodetype" => "text", "type" => "label",
											"value" => "ESFIAP, Hon.EFIAP, FPSA, FNPAS, Hon.YPS",
											"block" => "dvrao_block", "sequence" => 2,
										  	"font_family" => self::FONT_FAMILY, "font_style" => "I", "font_size" => 8, "font_color" => self::COLOR_LOGO_GRAY,
									    	"align" => "L", "line_spacing" => 1.1, "height" => 0, "group" => "" ),

						"dvrao_blank_1" => array("nodetype" => "text", "type" => "label", "value" => "", "block" => "dvrao_block", "sequence" => 3,
										  	"font_family" => self::FONT_FAMILY, "font_style" => "", "font_size" => 6, "font_color" => self::COLOR_LOGO_RED,
									    	"align" => "L", "line_spacing" => 1.0, "height" => 0, "group" => "", ),

						"dvrao_about" => array("nodetype" => "text", "type" => "label",
											"value" => "Dr Dodderi Venkatagiri Rao (1913-2004) is a medical professional, a social activist and a distinguished " .
											 			"photographer. He was a recipient of Karnataka Rajya Prasasti Award, 1982 for his social work and a " .
														"recipient of Karnataka Lalitakala Academy Award, 1996 for his contributions to the field of photography.  " .
														"He founded the Sagara Photographic Society in 2000. Find more about the inspiring life of Dr. D V Rao " .
														"in Drsti July 2020 edition available at ypsbengaluru.com.",
											"block" => "dvrao_block", "sequence" => 4,
										  	"font_family" => self::FONT_FAMILY, "font_style" => "", "font_size" => 8, "font_color" => self::COLOR_BLACK,
									    	"align" => "J", "line_spacing" => 1.0, "height" => 0, "group" => "" ),

						// Accept
						"accept_avatar" => array("nodetype" => "image", "type" => "label", "value" => __DIR__ . "/img/theprofilelogo.jpg",
												"block" => "accept_logo_block", "sequence" => 1,
											   	"align" => "left", "bordertype" => "border", "borderwidth" => 0, "bordercolor" => self::COLOR_BLACK,
											   	"float" => "right" ),

						"accept_title" => array("nodetype" => "text", "type" => "label", "value" => "The New YPS Logo",
											"block" => "accept_block", "sequence" => 1,
										  	"font_family" => self::FONT_FAMILY, "font_style" => "B", "font_size" => 14, "font_color" => self::COLOR_LOGO_RED,
									    	"align" => "L", "line_spacing" => 1.0, "height" => 0, "group" => "", ),

						"accept_blank_1" => array("nodetype" => "text", "type" => "label", "value" => "", "block" => "accept_block", "sequence" => 3,
										  	"font_family" => self::FONT_FAMILY, "font_style" => "", "font_size" => 6, "font_color" => self::COLOR_LOGO_RED,
									    	"align" => "L", "line_spacing" => 1.0, "height" => 0, "group" => "", ),

						"accept_about" => array("nodetype" => "text", "type" => "label",
											"value" => "July 11, 2021 was an important milestone in the history of YPS. After 45+ years of being the identity " .
											 			"of Youth Photographic Society, the logo was replaced by a new and contemporary logo that you see " .
														"on the left. The new logo is based on the photographer's action of framing using both hands. A " .
														"a few more lines and a diamond was added to make the lines come to life with the letter YPS. The " .
														"new logo was designed by Life Member Mr. Rajasimha Sathyanarayana. The logo was launched on ".
														"July 11th with Shri. Adit Agarawla, President as Chief Guest, FIP and Dr. Barun K Sinha, " .
														"as Guest of Honor.",
											"block" => "accept_block", "sequence" => 4,
										  	"font_family" => self::FONT_FAMILY, "font_style" => "", "font_size" => 8, "font_color" => self::COLOR_BLACK,
									    	"align" => "J", "line_spacing" => 1.0, "height" => 0, "group" => "" ),


						);


	// Protected Methods
	protected function log_error($errmsg, $phpfile, $phpline, $context = NULL) {
		if (self::LOG_ERROR == "yes") {
			$log_file = $_SERVER["DOCUMENT_ROOT"] . "/logs/errlog.txt";
			file_put_contents($log_file, date("Y-m-d H:i") .": Error '$errmsg' reported in line $phpline of '$phpfile'" . chr(13) . chr(10), FILE_APPEND);
			if ($context != NULL) {
		        file_put_contents($log_file, "Context:" . chr(13) . chr(10), FILE_APPEND);
				file_put_contents($log_file, print_r($context, true) . chr(13) . chr(10), FILE_APPEND);
		    }
		}
	}

	// Methods
	function getTemplate($level, $cutting_marks = false) {
		if ($level == 99)
			return $this->template_acceptance;
		else
			return $this->template_award;
		// if ($cutting_marks) {
		// 	if (! empty($this->templates_cm[$level]))
		// 		return $this->templates_cm[$level];
		// 	else
		// 		return false;
		// }
		// else {
		// 	if (! empty($this->templates[$level]))
		// 		return $this->templates[$level];
		// 	else
		// 		return false;
		// }
	}

	function getNodeValue($node_name) {
		if (isset($this->nodes[$node_name]) && isset($this->nodes[$node_name]["value"]))
			return $this->nodes[$node_name]["value"];
		else {
			$this->log_error("getNodeValue failed for $node_name", __FILE__, __LINE__);
			return false;
		}
	}

	function setNodeValue($node_name, $value) {

		if (isset($this->nodes[$node_name]) && isset($this->nodes[$node_name]["type"]) && $this->nodes[$node_name]["type"] == "field") {

			if (isset($this->nodes[$node_name]['template']))
				$this->nodes[$node_name]["value"] = str_replace("[value]", $value, $this->nodes[$node_name]['template']);
			else
				$this->nodes[$node_name]["value"] = $value;

			if ( isset($this->nodes[$node_name]["block"]) ) {

				if ( trim($this->nodes[$node_name]["value"]) == "" ) {

					if ( isset($this->nodes[$node_name]["omit_if_empty"]) && $this->nodes[$node_name]["omit_if_empty"] == "yes" ) {
						$this->log_error("setNodeValue omitting $node_name with empty value ($value)", __FILE__, __LINE__, $this->nodes[$node_name]);
						return false;
					}
					else {
						$this->printEnable($this->nodes[$node_name]["block"]);
						return true;
					}

				}
				else {
					$this->printEnable($this->nodes[$node_name]["block"]);
					return true;
				}

			}
			else {
				$this->log_error("setNodeValue missing block in $node_name", __FILE__, __LINE__, $this->nodes[$node_name]);
				return false;
			}
		}
		else {
			$this->log_error("setNodeValue failed to set $value for $node_name", __FILE__, __LINE__);
			return false;
		}
	}

	function deleteNodeValue($node_name) {
		if (isset($this->nodes[$node_name]) && isset($this->nodes[$node_name]["type"]) && $this->nodes[$node_name]["type"] == "field" &&
		   										isset($this->nodes[$node_name]["value"]) ) {
			unset($this->nodes[$node_name]["value"]);
			return true;
		}
		else {
			$this->log_error("deleteNodeValue failed for $node_name", __FILE__, __LINE__);
			return false;
		}
	}

	function getBlock($block_name) {
		if (isset($this->blocks[$block_name]))
			return (object) $this->blocks[$block_name];
		else {
			$this->log_error("getBlock unable to find $block_name", __FILE__, __LINE__);
			return false;
		}
	}

	function getListOfBlocks() {
		$block_list = [];
		foreach($this->blocks as $block_name => $block) {
			$block_list[$block_name] = $block;
		}
		return $block_list;
	}

	function printEnable($block_name) {
		if (isset($this->blocks[$block_name]))
			$this->blocks[$block_name]["print"] = "yes";
		else
			$this->log_error("printEnable unable to find $block_name", __FILE__, __LINE__);
	}

	// $data is an associate array of database values
	function isBlockPrintable($block_name, $data = []) {
		// Check if there is a condition associated with the block
		if (isset($this->blocks[$block_name]["if"])) {
			if ( isset($this->blocks[$block_name]["if"]["field"]) && sizeof($this->blocks[$block_name]["if"]["field"]) > 0 ) {
				// Try to match each field with a match or no match
				for ($i = 0; $i < sizeof($this->blocks[$block_name]["if"]["field"]); ++$i) {

					$field = $this->blocks[$block_name]["if"]["field"][$i];

					// If the data does not contain the field, conditions cannot be verified and hence block cannot be printed
					if (isset($data[$field])) {
						$value = $data[$field];
						$filename = basename($data[$field]);		// If the data contains path
					}
					else {
						$this->log_error("isBlockPrintable omitting $block_name as field [$field] has no data to match", __FILE__, __LINE__, $this->blocks[$block_name]);
						return false;
					}

					// Positive Match value
					if (isset($this->blocks[$block_name]["if"]["match"][$i]))
						$match = $this->blocks[$block_name]["if"]["match"][$i];
					else
						$match = NULL;
					// Negative Match value
					if (isset($this->blocks[$block_name]["if"]["notmatch"][$i]))
						$nomatch = $this->blocks[$block_name]["if"]["notmatch"][$i];
					else
						$nomatch = NULL;

					// If there is a positive match or a negative match, continue matching next field
					if ( ($match != NULL && ($match == $value || $match == $filename)) || ($nomatch != NULL && ($nomatch != $value && $nomatch != $filename)) )
						continue;
					else {
						$this->log_error("isBlockPrintable omitting $block_name value [$value] of [$field] does not have a match with [$match] or non-match with [$nomatch]", __FILE__, __LINE__, $this->blocks[$block_name]);
						return false;
					}
				}
			}
			else {
				$this->log_error("isBlockPrintable has incorrect condition config for $block_name", __FILE__, __LINE__, $this->blocks[$block_name]);
			}
		}

		// Conditions did not exist or conditions matched
		// Return if print flag is set to yes
		if (isset($this->blocks[$block_name]["print"]) && $this->blocks[$block_name]["print"] == "yes" )
			return true;
		else {
			$this->log_error("isBlockPrintable omitting $block_name based on value or 'print' setting", __FILE__, __LINE__, $this->blocks[$block_name]);
			return false;
		}
	}

	function getNode($node_name) {
		if (isset($this->nodes[$node_name]))
			return $this->nodes[$node_name];
		else {
			$this->log_error("getNode unable to find $node_name", __FILE__, __LINE__);
			return false;
		}
	}

	function getNodesForBlock($block_name) {
		if (isset($this->blocks[$block_name])) {
			$node_list = [];
			foreach ($this->nodes as $nodename => $node) {
				if (isset($node['block']) && $node['block'] == $block_name)
					$node_list[$nodename] = $node;
			}
			return $node_list;
		}
		else {
			$this->log_error("getNodesForBlock unable to find $block_name", __FILE__, __LINE__);
			return false;
		}
	}
}
