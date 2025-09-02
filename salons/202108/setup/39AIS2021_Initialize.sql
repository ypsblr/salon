-- phpMyAdmin SQL Dump
-- version 4.7.4
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Sep 15, 2018 at 07:25 AM
-- Server version: 5.7.19
-- PHP Version: 5.6.31

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `yps_salondb`
--

-- --------------------------------------------------------
-- Data Initialization for 39th YPS ALL INDIA SALON 2021
-- --------------------------------------------------------
--
-- CONTEST DEFINITION
-- ==================
--
-- TABLE `contest`
--

--
-- Data for table `contest`
--
-- 34th All India Salon
--

INSERT INTO `contest` (`yearmonth`, `contest_name`) VALUES (202108, 'YPS All India Digital Salon 2021');


UPDATE contest
SET is_salon = '1',
	is_international = '0',
	is_no_to_past_acceptance = '1',
	contest_description_blob = 'contest_description.htm',
	terms_conditions_blob = 'terms_conditions.htm',
	contest_announcement_blob = '',
	fee_structure_blob = 'fee_structure.php',
	discount_structure_blob = '',
	registration_start_date = '2021-04-23',
	registration_last_date = '2021-07-14',
	submission_timezone = 'Asia/Kolkata',
	submission_timezone_name = 'India',
	judging_start_date = '2021-07-17',
	judging_end_date = '2021-07-19',
	results_date = '2021-07-23',
	update_start_date = '2021-07-23',
	update_end_date = '2021-08-05',
	exhibition_start_date = '2021-08-21',
	exhibition_end_date = '2021-08-31',
	has_judging_event = '1',
	has_exhibition = '1',
	has_catalog = '1',
	judging_in_progress = '0',
	judging_mode = 'REMOTE',
	judging_description_blob = 'judging_description.htm',
	judging_venue = 'Online Judging',
	judging_venue_address = '',
	judging_venue_location_map = '',
	judging_report_blob = '',
	judging_photos_php = '',
	results_ready = '0',
	certificates_ready = '0',
	results_description_blob = '',
	exhibition_name = 'Virtual Exhibition',
	exhibition_description_blob = 'exhibition_description.htm',
	exhibition_venue = 'Virtual through Salon Website',
	exhibition_venue_address = '',
	exhibition_venue_location_map = '',
	exhibition_report_blob = '',
	exhibition_photos_php = '',
	inauguration_photos_php = '',
	catalog_release_date = '2021-08-21',
	catalog_ready = '0',
	catalog = '',
	catalog_download = '',
	catalog_order_last_date = '2021-09-08',
	catalog_price_in_inr = '',
	catalog_price_in_usd = '',
	chairman_message_blob = '',
	max_pics_per_entry = '16',
	max_width = "1920",
	max_height = "1080",
	max_file_size_in_mb = "4",
	fee_model = 'POLICY',
	num_entries = '0',
	num_women = '0',
	num_pictures = '0',
	num_awards = '0',
	num_hms = '0',
	num_acceptances = '0',
	num_winners = '0'
WHERE yearmonth = '202108';


-- /contest -----------------------------------------------------------

--
-- DATA for TABLE `section`
--

INSERT INTO `section` (`yearmonth`, `section`, `section_type`, `section_sequence`, `stub`,
					   `description`, `rules`, `rules_blob`,
					   `submission_last_date`, `max_pics_per_entry`,
					   `num_entrants`, `num_pictures`, `num_awards`, `num_hms`, `num_acceptances`, `num_winners`) VALUES
(202108, 'COLOR', 'D', 2, 'COD',
 		'', '', 'section_color_rules.htm',
 		'2021-07-14', 4, 0, 0, 0, 0, 0, 0),
(202108, 'MONOCHROME', 'D', 1, 'MOD',
 		'', '', 'section_monochrome_rules.htm',
 		'2021-07-14', 4, 0, 0, 0, 0, 0, 0),
(202108, 'NATURE', 'D', 3, 'ND',
		'', '', 'section_nature_rules.htm',
		'2021-07-14', 4, 0, 0, 0, 0, 0, 0),
(202108, 'TRAVEL', 'D', 4, 'TD',
		'', '', 'section_travel_rules.htm',
		'2021-07-14', 4, 0, 0, 0, 0, 0, 0);
 		-- '<p>NATURE photography is restricted to the use of the photographic process to depict all branches of natural
		-- 	history, except anthropology and archeology, in such a fashion that a well-informed person will be
		-- 	able to identify the subject material and certify its honest presentation.\r\n</p>',
 		-- '<ul>
		-- 	<li><b>NATURE section accommodates controlled environment</b> - Images entered in NATURE section meeting
		-- 		the Nature Photography Definition above can have landscapes, geological formations, weather phenomena,
		-- 		and extant organisms as the primary subject matter. This includes images taken with subjects in
		-- 		controlled conditions, such as zoos, game farms, botanical gardens, aquariums and any enclosure where
		-- 		the subjects are totally dependent on man for food.</li>
		-- 	<li><b>WILDLIFE under NATURE should be presented in uncontrolled free environment</b> - WILDLIFE Images
		-- 		entered under NATURE sections meeting the Nature Photography Definition above are further defined
		-- 		as one or more extant zoological or botanical organisms free and unrestrained in a natural or
		-- 		adopted habitat. Photographs of zoo animals or game farm animals, or of any extant zoological or
		-- 		botanical species taken under controlled conditions are not eligible for WILDLIFE awards in
		-- 		NATURE section.</li>
		-- 	<li><b>What is WILDLIFE</b> - WILDLIFE is not limited to mammals, birds and insects. Marine subjects
		-- 		and botanical subjects (including fungi and algae) taken in the wild are suitable wildlife subjects,
		-- 		as are carcasses of extant species.</b></li>
		-- 	<li><b>NO man-made elements</b> - Human or man-made elements shall not be present in the picture, except
		-- 		where those elements are integral parts of the story of the picture. An example would be a picture
		-- 		of barn owls or other birds living in an environment modified by humans after having adapted their
		-- 		life to such an environment. Another exception would be inclusion of humans, human/man-made elements
		-- 		in situations depicting natural forces, such as hurricanes or tidal waves.</li>
		-- 	<li><b>NO tags or chains</b> - Pictures of animals/birds with scientific bands, scientific tags or radio
		-- 		collars or other restraints such as chain are NOT permissible.</li>
		-- 	<li><b>NO domesticated subjects</b> - Photographs of human created hybrid plants, cultivated plants,
		-- 		feral animals, domestic animals and mounted specimens are NOT permissible.</li>
		-- 	<li><b>NO Infrared</b> - Infrared images, either direct-captures or derivations, are not allowed.</li>
		-- 	<li><b>NO Digital Creation Pictures</b> - Digital Creation pictures are NOT allowed under this section.</li>
		-- 	<li><b>MONOCHROME allowed</b> - Color images can be converted to grayscale monochrome.</li>
		-- 	<li><b>Post-processing for Natural Presentation permitted</b> - Techniques that enhance the presentation of
		-- 		the photograph while preserving the nature story, original scene and the pictorial content are permitted.
		-- 		This includes basic post-processing techniques such as Cropping, Burning, Dodging, Exposure correction,
		-- 		hue/saturation adjustments, sharpening and noise reduction, and advanced techniques such as spot removal,
		-- 		HDR, Luminosity Masks and Focus Stacking. Final image presented must appear natural.</li>
		-- 	<li><b>Post-processing that changes the scene are not permitted</b> - Techniques that add, relocate, replace,
		-- 		or remove pictorial elements (except by cropping) are NOT permitted. Stitching and making composite images,
		-- 		not falling under the advanced techniques listed above, are NOT permitted</li>
		-- </ul>',
 		-- '<p>A TRAVEL image expresses the characteristic features or culture of a land as they are found naturally.</p>',
 		-- '<ul>
		-- 	<li><b>Can be from anywhere</b> - There are no geographic limitations. Pictures could have been shot anywhere
		-- 		on earth or in space.</li>
		-- 	<li><b>No images from organized events</b> - Images from events or activities arranged specifically for
		-- 		photography, or of subjects directed or hired for photography are NOT allowed.</li>
		-- 	<li><b>Portraits must depict features or culture</b> - Portraits of people or objects must depict features
		-- 		that provide information about the land.</li>
		-- 	<li><b>Only Post-processing for Factual Presentation permitted</b> - Techniques that add, relocate, replace
		-- 		or remove any element of the original image (except by cropping) are not permitted. Techniques that result
		-- 		in restoration of the appearance of the original scene are permitted. This includes basic post-processing
		-- 		techniques such as Cropping, Burning, Dodging, Exposure correction, hue/saturation adjustments, sharpening
		-- 		and noise reduction, and advanced techniques such as spot removal, HDR, Luminosity Masks and Focus Stacking.
		-- 		Final image presented must appear natural.</li>
		-- 	<li><b>MONOCHROME allowed</b> - Color images can be converted to grayscale monochrome.</li>
		-- 	<li><b>NO Infrared</b> - Infrared images, either direct-captures or derivations, are not allowed.</li>
		-- </ul>',

-- /section --------------------------------------------------------------

--
-- DATA for recognition
--
INSERT INTO `recognition` (`yearmonth`, `short_code`, `organization_name`, `website`, `recognition_id`, `small_logo`, `logo`, `notification`, `description`) VALUES
(202108, 'FIP', 'Federation of Indian Photography', 'http://www.fip.org.in', 'APPLIED', '', 'fip_logo.png', '', '');

-- /recognition ------------------------------------------------------------


--
-- DATA for TABLE `entrant_category`
--
INSERT INTO `entrant_category` (`yearmonth`, `entrant_category`, `entrant_category_name`, `yps_membership_required`, `yps_member_prefixes`,
								`gender_must_match`, `gender_match`, `age_within_range`, `age_minimum`, `age_maximum`,
								`country_must_match`, `country_codes`, `state_must_match`, `state_names`,
								`currency`, `can_create_club`, `fee_waived`, `acceptance_reported`, `award_group`, `fee_group`,
								`default_participation_code`, `default_digital_sections`, `default_print_sections`, `discount_group`) VALUES
(202108, 'ADULT', 'Participant above 18 years of age', 0, '',
 		0, '', 1, 18, 120,
 		1, '101', 0, '',
 		'INR', 1, 1, 1, 'ALL_AGES', 'GENERAL',
 		'DIGITAL_ALL', 4, 0, 'NONE'),
(202108, 'ADULT_YPS', 'Participant above 18 years of age', 1, 'LM,IM,JA',
 		0, '', 1, 18, 120,
 		1, '101', 0, '',
 		'INR', 0, 1, 1, 'ALL_AGES', 'GENERAL',
 		'DIGITAL_ALL', 4, 0, 'NONE'),
(202108, 'YOUTH', 'Participants below 18 years of age', 0, '',
 		0, '', 1, 8, 18,
 		1, '101', 0, '',
 		'INR', 0, 1, 1, 'ALL_AGES', 'YOUTH',
 		'DIGITAL_ALL', 4, 0, 'NONE'),
(202108, 'YOUTH_YPS', 'Participants below 18 years of age', 1, 'JA',
 		0, '', 1, 8, 18,
 		1, '101', 0, '',
 		'INR', 0, 1, 1, 'ALL_AGES', 'YOUTH',
 		'DIGITAL_ALL', 4, 0, 'NONE');


-- /entrant_category ---------------------------------------------------


--
-- DATA for TABLE `award`
--

-- Special awards against names of Legends
INSERT INTO `award` (`yearmonth`, `award_id`, `level`, `sequence`, `section`, `award_group`, `award_type`, `award_name`, `description`,
					 `number_of_awards`, `has_medal`, `has_pin`, `has_ribbon`, `has_memento`, `has_gift`, `has_certificate`, `cash_award`,
					 `sponsored_awards`, `sponsorship_per_award`, `partial_sponsorship_permitted`, `sponsorship_last_date`
) VALUES
(202108, 201, 20, 1, 'CONTEST', 'ALL_AGES', 'pic', 'Dr. G Thomas YPS Golden Jubilee Award - Monochrome', '',
 			1, 0, 0, 0, 1, 0, 1, 0,
 			0, 0, 0, '2021-06-30'
),
(202108, 202, 20, 2, 'CONTEST', 'ALL_AGES', 'pic', 'C Rajagopal YPS Golden Jubilee Award - Monochrome', '',
 			1, 0, 0, 0, 1, 0, 1, 0,
 			0, 0, 0, '2021-06-30'
),
(202108, 203, 20, 3, 'CONTEST', 'ALL_AGES', 'pic', 'T N A Perumal YPS Golden Jubilee Award - Color', '',
 			1, 0, 0, 0, 1, 0, 1, 0,
 			0, 0, 0, '2021-06-30'
),
(202108, 204, 20, 4, 'CONTEST', 'ALL_AGES', 'pic', 'M Y Ghorpade YPS Golden Jubilee Award - Color', '',
 			1, 0, 0, 0, 1, 0, 1, 0,
 			0, 0, 0, '2021-06-30'
),
(202108, 205, 20, 5, 'CONTEST', 'ALL_AGES', 'pic', 'E Hanumantha Rao YPS Golden Jubilee Award - Nature', '',
 			1, 0, 0, 0, 1, 0, 1, 0,
 			0, 0, 0, '2021-06-30'
),
(202108, 206, 20, 6, 'CONTEST', 'ALL_AGES', 'pic', 'B N S Deo YPS Golden Jubilee Award - Nature', '',
 			1, 0, 0, 0, 1, 0, 1, 0,
 			0, 0, 0, '2021-06-30'
),
(202108, 207, 20, 7, 'CONTEST', 'ALL_AGES', 'pic', 'O C Edwards YPS Golden Jubilee Award - Travel', '',
 			1, 0, 0, 0, 1, 0, 1, 0,
 			0, 0, 0, '2021-06-30'
),
(202108, 208, 20, 8, 'CONTEST', 'ALL_AGES', 'pic', 'Dr. D V Rao YPS Golden Jubilee Award - Travel', '',
 			1, 0, 0, 0, 1, 0, 1, 0,
 			0, 0, 0, '2021-06-30'
);

-- FIP Gold
INSERT INTO `award` (`yearmonth`, `award_id`, `level`, `sequence`, `section`, `award_group`, `award_type`, `award_name`, `description`,
					 `number_of_awards`, `has_medal`, `has_pin`, `has_ribbon`, `has_memento`, `has_gift`, `has_certificate`, `cash_award`,
					 `sponsored_awards`, `sponsorship_per_award`, `partial_sponsorship_permitted`, `sponsorship_last_date`
) VALUES
(202108, 1, 1, 1, 'MONOCHROME', 'ALL_AGES', 'pic', 'FIP Gold', '',
 			1, 1, 0, 0, 0, 0, 1, 5000,
 			1, 6000, 0, '2021-06-30'
),
(202108, 2, 1, 1, 'COLOR', 'ALL_AGES', 'pic', 'FIP Gold', '',
 			1, 1, 0, 0, 0, 0, 1, 5000,
 			1, 6000, 0, '2021-06-30'
),
(202108, 3, 1, 1, 'NATURE', 'ALL_AGES', 'pic', 'FIP Gold', '',
 			1, 1, 0, 0, 0, 0, 1, 5000,
 			1, 6000, 0, '2021-06-30'
),
(202108, 4, 1, 1, 'TRAVEL', 'ALL_AGES', 'pic', 'FIP Gold', '',
 			1, 1, 0, 0, 0, 0, 1, 5000,
 			1, 6000, 0, '2021-06-30'
);
-- YPS Gold
INSERT INTO `award` (`yearmonth`, `award_id`, `level`, `sequence`, `section`, `award_group`, `award_type`, `award_name`, `description`,
					 `number_of_awards`, `has_medal`, `has_pin`, `has_ribbon`, `has_memento`, `has_gift`, `has_certificate`, `cash_award`,
					 `sponsored_awards`, `sponsorship_per_award`, `partial_sponsorship_permitted`, `sponsorship_last_date`
) VALUES
(202108, 11, 2, 1, 'MONOCHROME', 'ALL_AGES', 'pic', 'YPS Gold', '',
 			1, 1, 0, 0, 0, 0, 1, 3000,
 			1, 4000, 0, '2021-06-30'
),
(202108, 12, 2, 1, 'COLOR', 'ALL_AGES', 'pic', 'YPS Gold', '',
 			1, 1, 0, 0, 0, 0, 1, 3000,
 			1, 4000, 0, '2021-06-30'
),
(202108, 13, 2, 1, 'NATURE', 'ALL_AGES', 'pic', 'YPS Gold', '',
 			1, 1, 0, 0, 0, 0, 1, 3000,
 			1, 4000, 0, '2021-06-30'
),
(202108, 14, 2, 1, 'TRAVEL', 'ALL_AGES', 'pic', 'YPS Gold', '',
 			1, 1, 0, 0, 0, 0, 1, 3000,
 			1, 4000, 0, '2021-06-30'
);
-- YPS Silver
INSERT INTO `award` (`yearmonth`, `award_id`, `level`, `sequence`, `section`, `award_group`, `award_type`, `award_name`, `description`,
					 `number_of_awards`, `has_medal`, `has_pin`, `has_ribbon`, `has_memento`, `has_gift`, `has_certificate`, `cash_award`,
					 `sponsored_awards`, `sponsorship_per_award`, `partial_sponsorship_permitted`, `sponsorship_last_date`
) VALUES
(202108, 21, 3, 1, 'MONOCHROME', 'ALL_AGES', 'pic', 'YPS Silver', '',
 			1, 1, 0, 0, 0, 0, 1, 2000,
 			1, 3000, 0, '2021-06-30'
),
(202108, 22, 3, 1, 'COLOR', 'ALL_AGES', 'pic', 'YPS Silver', '',
 			1, 1, 0, 0, 0, 0, 1, 2000,
 			1, 3000, 0, '2021-06-30'
),
(202108, 23, 3, 1, 'NATURE', 'ALL_AGES', 'pic', 'YPS Silver', '',
 			1, 1, 0, 0, 0, 0, 1, 2000,
 			1, 3000, 0, '2021-06-30'
),
(202108, 24, 3, 1, 'TRAVEL', 'ALL_AGES', 'pic', 'YPS Silver', '',
 			1, 1, 0, 0, 0, 0, 1, 2000,
 			1, 3000, 0, '2021-06-30'
);
-- YPS Bronze
INSERT INTO `award` (`yearmonth`, `award_id`, `level`, `sequence`, `section`, `award_group`, `award_type`, `award_name`, `description`,
					 `number_of_awards`, `has_medal`, `has_pin`, `has_ribbon`, `has_memento`, `has_gift`, `has_certificate`, `cash_award`,
					 `sponsored_awards`, `sponsorship_per_award`, `partial_sponsorship_permitted`, `sponsorship_last_date`
) VALUES
(202108, 31, 4, 1, 'MONOCHROME', 'ALL_AGES', 'pic', 'YPS Bronze', '',
 			1, 1, 0, 0, 0, 0, 1, 1500,
 			1, 2500, 0, '2021-06-30'
),
(202108, 32, 4, 1, 'COLOR', 'ALL_AGES', 'pic', 'YPS Bronze', '',
 			1, 1, 0, 0, 0, 0, 1, 1500,
 			1, 2500, 0, '2021-06-30'
),
(202108, 33, 4, 1, 'NATURE', 'ALL_AGES', 'pic', 'YPS Bronze', '',
 			1, 1, 0, 0, 0, 0, 1, 1500,
 			1, 2500, 0, '2021-06-30'
),
(202108, 34, 4, 1, 'TRAVEL', 'ALL_AGES', 'pic', 'YPS Bronze', '',
 			1, 1, 0, 0, 0, 0, 1, 1500,
 			1, 2500, 0, '2021-06-30'
);
-- YPS Young Talent Award
INSERT INTO `award` (`yearmonth`, `award_id`, `level`, `sequence`, `section`, `award_group`, `award_type`, `award_name`, `description`,
					 `number_of_awards`, `has_medal`, `has_pin`, `has_ribbon`, `has_memento`, `has_gift`, `has_certificate`, `cash_award`,
					 `sponsored_awards`, `sponsorship_per_award`, `partial_sponsorship_permitted`, `sponsorship_last_date`
) VALUES
(202108, 41, 5, 1, 'MONOCHROME', 'ALL_AGES', 'pic', 'YPS Young Talent Award', '',
 			1, 0, 0, 0, 0, 1, 1, 0,
 			1, 3000, 0, '2021-06-30'
),
(202108, 42, 5, 1, 'COLOR', 'ALL_AGES', 'pic', 'YPS Young Talent Award', '',
 			1, 0, 0, 0, 0, 1, 1, 0,
 			1, 3000, 0, '2021-06-30'
),
(202108, 43, 5, 1, 'NATURE', 'ALL_AGES', 'pic', 'YPS Young Talent Award', '',
 			1, 1, 0, 0, 0, 1, 1, 0,
 			1, 3000, 0, '2021-06-30'
),
(202108, 44, 5, 1, 'TRAVEL', 'ALL_AGES', 'pic', 'YPS Young Talent Award', '',
 			1, 1, 0, 0, 0, 1, 1, 0,
 			1, 3000, 0, '2021-06-30'
);
-- FIP Ribbon
INSERT INTO `award` (`yearmonth`, `award_id`, `level`, `sequence`, `section`, `award_group`, `award_type`, `award_name`, `description`,
					 `number_of_awards`, `has_medal`, `has_pin`, `has_ribbon`, `has_memento`, `has_gift`, `has_certificate`, `cash_award`,
					 `sponsored_awards`, `sponsorship_per_award`, `partial_sponsorship_permitted`, `sponsorship_last_date`
) VALUES
(202108, 51, 9, 1, 'MONOCHROME', 'ALL_AGES', 'pic', 'FIP Ribbon', '',
 			5, 0, 0, 1, 0, 0, 1, 500,
 			5, 1000, 0, '2021-06-30'
),
(202108, 52, 9, 1, 'COLOR', 'ALL_AGES', 'pic', 'FIP Ribbon', '',
 			5, 0, 0, 1, 0, 0, 1, 500,
 			5, 1000, 0, '2021-06-30'
),
(202108, 53, 9, 1, 'NATURE', 'ALL_AGES', 'pic', 'FIP Ribbon', '',
  			5, 0, 0, 1, 0, 0, 1, 500,
 			5, 1000, 0, '2021-06-30'
),
(202108, 54, 9, 1, 'TRAVEL', 'ALL_AGES', 'pic', 'FIP Ribbon', '',
  			5, 0, 0, 1, 0, 0, 1, 500,
 			5, 1000, 0, '2021-06-30'
);
-- YPS Ribbon
INSERT INTO `award` (`yearmonth`, `award_id`, `level`, `sequence`, `section`, `award_group`, `award_type`, `award_name`, `description`,
					 `number_of_awards`, `has_medal`, `has_pin`, `has_ribbon`, `has_memento`, `has_gift`, `has_certificate`, `cash_award`,
					 `sponsored_awards`, `sponsorship_per_award`, `partial_sponsorship_permitted`, `sponsorship_last_date`
) VALUES
(202108, 61, 9, 1, 'MONOCHROME', 'ALL_AGES', 'pic', 'YPS Ribbon', '',
 			5, 0, 0, 1, 0, 0, 1, 500,
 			5, 1000, 0, '2021-06-30'
),
(202108, 62, 9, 1, 'COLOR', 'ALL_AGES', 'pic', 'YPS Ribbon', '',
 			5, 0, 0, 1, 0, 0, 1, 500,
 			5, 1000, 0, '2021-06-30'
),
(202108, 63, 9, 1, 'NATURE', 'ALL_AGES', 'pic', 'YPS Ribbon', '',
  			5, 0, 0, 1, 0, 0, 1, 500,
 			5, 1000, 0, '2021-06-30'
),
(202108, 64, 9, 1, 'TRAVEL', 'ALL_AGES', 'pic', 'YPS Ribbon', '',
  			5, 0, 0, 1, 0, 0, 1, 500,
 			5, 1000, 0, '2021-06-30'
);
-- Contest Level Awards
INSERT INTO `award` (`yearmonth`, `award_id`, `level`, `sequence`, `section`, `award_group`, `award_type`, `award_name`, `description`,
					 `number_of_awards`, `has_medal`, `has_pin`, `has_ribbon`, `has_memento`, `has_gift`, `has_certificate`, `cash_award`,
					 `sponsored_awards`, `sponsorship_per_award`, `partial_sponsorship_permitted`, `sponsorship_last_date`
) VALUES
(202108, 91, 9, 1, 'CONTEST', 'ALL_AGES', 'entry', 'Best Participant Award', '',
 			1, 0, 0, 0, 1, 0, 0, 0,
 			0, 0, 0, '2020-07-31'
),
(202108, 92, 9, 1, 'CONTEST', 'ALL_AGES', 'club', 'Best Participating Club Award', '',
 			1, 0, 0, 0, 1, 0, 0, 0,
 			0, 0, 0, '2020-07-31'
);
-- Acceptance
INSERT INTO `award` (`yearmonth`, `award_id`, `level`, `sequence`, `section`, `award_group`, `award_type`, `award_name`, `description`,
					 `number_of_awards`, `has_medal`, `has_pin`, `has_ribbon`, `has_memento`, `has_gift`, `has_certificate`, `cash_award`,
					 `sponsored_awards`, `sponsorship_per_award`, `partial_sponsorship_permitted`, `sponsorship_last_date`
) VALUES
(202108, 1001, 99, 99, 'MONOCHROME', 'ALL_AGES', 'pic', 'Acceptance', '',
 			0, 0, 0, 0, 0, 0, 1, 0,
 			0, 0, 0, '2020-07-31'
),
(202108, 1002, 99, 99, 'COLOR', 'ALL_AGES', 'pic', 'Acceptance', '',
 			0, 0, 0, 0, 0, 0, 1, 0,
 			0, 0, 0, '2020-07-31'
),
(202108, 1003, 99, 99, 'NATURE', 'ALL_AGES', 'pic', 'Acceptance', '',
 			0, 0, 0, 0, 0, 0, 1, 0,
 			0, 0, 0, '2020-07-31'
),
(202108, 1004, 99, 99, 'TRAVEL', 'ALL_AGES', 'pic', 'Acceptance', '',
 			0, 0, 0, 0, 0, 0, 1, 0,
 			0, 0, 0, '2020-07-31'
);

-- /award ------------------------------------------------------------------------------------------


--
-- DATA for `team`
--
ALTER TABLE `team` ADD role_name varchar(255) NOT NULL DEFAULT "" AFTER `role`;

INSERT INTO `team` (`yearmonth`, `member_id`, `member_login_id`, `member_password`,
					`level`, `sequence`, `role`, `role_name`, `member_name`, `honors`,
					`phone`, `email`, `profile`, `avatar`,
					`permissions`, `is_reviewer`, `is_print_coordinator`, `allow_downloads`)
VALUES
(202108, 1, 'prema', 'pk2101',
			1, 1, 'Chairman', 'Chairperson', 'Ms. Prema Kakade', 'EFIAP, EFIP, cMoL, A.CPE, Hon.PESGSPC, GPA.PESGSPC, Hon.CPE',
			'+91-94489-56495', 'pmkakade@gmail.com', '', 'prema.png',
			'chairman', '0', '0', '1'),
(202108, 2, 'bhat', 'kb2102',
			1, 2, 'Secretary', 'Secretary', 'Mr. Krishna Bhat', 'EFIAP/s, EFIP, EPSA, cMOL, GPA.PESGSPC, Hon.PESGSPC, Hon.CPE, Hon.GM.GNG, Hon.APF, Grad.PSA Image Analysis',
			'+91-99453-36316', 'dinaabaa@gmail.com', '', 'bhat.png',
			'secretary', '0', '0', '1'),
(202108, 3, 'madhu', 'mk2103',
			5, 1, 'Treasurer', 'Treasurer', 'Mr. Madhu Kakade', '',
			'+91-90039-30673', 'mskakade3@gmail.com', '', 'mskakade.png',
			'reviewer', '0', '0', '0'),
(202108, 4, '', '',
			5, 2, 'Communication', 'Campaign & Communication', 'Mr. Ananth Kamat', 'AFIP',
			'+91-98809-87247', '', '', 'ananth_kamat.png',
			'', '0', '0', '0'),
(202108, 5, 'vikas', '1.vikas',
			5, 3, 'Sponsorship', 'Sponsorship & Participant Suport', 'Mr. Manju Vikas Sastry V', 'AFIP, Hon.APF, Hon.FGNG',
			'+91-95139-77257', 'sastry.vikas@gmail.com', '', 'vikas.png',
			'', '0', '0', '0'),
(202108, 6, '', '',
			5, 4, 'Exhibition', 'Exhibition In-charge', 'Mr. K S Manju Mohan', 'AFIAP, AFIP, PPSA, AAPS, cMoL, GPU-CR2, Hon.CPE',
			'+91-99800-60494', 'ksmm1967@gmail.com', '', 'manju_mohan.png',
			'', '0', '0', '0'),
(202108, 7, '', '',
			5, 5, 'Support', 'Salon Support', 'Mr. Nandan Hegde', 'EFIAP, QPSA, EFIP, cMoL',
			'+91-77953-38538', 'nandanhegde91@gmail.com', '', 'nandan.png',
			'reviewer', '0', '0', '0'),
(202108, 8, '', '',
			5, 6, 'Reviewer-1', 'Reviewer (TRAVEL)', 'Mrs. Anitha Mysore', 'EFIAP/b, MPSA, EIUP, c**MoL, GPU CR-4, AAPS, EFIP, EFIP/g, EFIP/g (Nature), A.CPE, Hon. PESGSPC, GPA, PESGSPC, G. APS, ES.CPE',
			'+91 98451 80061', 'anithamysore2020@gmail.com', '', 'anitha.png',
			'reviewer', '0', '0', '0'),
(202108, 9, 'chandru', 'cs2110',
			5, 7, 'Reviewer-2', 'Reviewer (MONOCHROME)', 'Mr. Chandrashekar Srinivasamurthy', 'AFIAP',
			'+91-98440-98288', 'scshekar9@gmail.com', '', 'chandru.png',
			'reviewer', '0', '0', '0'),
(202108, 10, 'girish', 'ga2109',
			5, 8, 'Reviewer-3', 'Reviewer (COLOR)', 'Mr. Girish Ananthamurthy', 'EFIAP, EFIP, Hon.PESGSPC, GPA PESGSPC',
			'+91-94490-06221', 'Jeechu1970@gmail.com', '', 'girish.png',
			'reviewer', '0', '0', '0'),
(202108, 11, '', '',
			5, 9, 'Reviewer-4', 'Reviewer (NATURE)', 'Mr. Nandan Hegde', 'EFIAP, QPSA, EFIP, cMoL',
			'+91-77953-38538', 'nandanhegde91@gmail.com', '', 'nandan.png',
			'reviewer', '0', '0', '0'),
(202108, 12, '', '',
			5, 10, 'Catalog', 'Catalog Design', 'Mr. Rajasimha S', 'AFIP, AFIAP, CMoL',
			'+91-99723-99096', '', '', 'rajasimha.jpg',
			'', '0', '0', '0'),
(202108, 13, '', '',
			5, 11, 'Media', 'Media & Campaign', 'Mr. Hardik P Shah', '',
			'+91-99723-99096', '', '', 'hardik.png',
			'', '0', '0', '0'),
(202108, 14, '', '',
			5, 12, 'Design', 'Designs', 'Mr. Chetan Rao Mane', 'AFIP, AFIAP',
			'+91-98800-34075', '', '', 'chetan_rao_mane.png',
			'', '0', '0', '0'),
(202108, 15, 'murali', 'murali!@#',
			5, 13, 'Webmaster', 'Web Master', 'Mr. Murali Santhanam', '',
			'+91-94813-69204', 'murali.santhanam@yahoo.co.in', '', 'murali.png',
			'admin', '0', '0', '1'),
(202108, 16, '', '',
			9, 1, 'Advisor', 'Special Advisor', 'Mr. H Satish', 'MFIAP, MICS, ARPS, cMoL, GPA.PESGSPC, Hon.FICS, Hon.CPE, Hon. PESGSPC, Hon.YPS, Hon. ECPA,Hon. FLAS, Hon.FWPAI , Hon.FSAP, Hon.PSP, Patron LMG. Hon.GMTPAS',
			'+91-94486-87595', 'satishtusker@gmail.com', '', 'satish.png',
			'reviewer', '0', '0', '0');



--
-- SPONSORSHIP section
-- ===================
--
--
-- DATA for TABLE `opportunity`
--
INSERT INTO `opportunity` (`yearmonth`, `opportunity_id`, `opportunity_type`, `opportunity_description`, `number_of_sponsors`, `sponsorship_amount_fixed`, `sponsorship_amount`, `opportunity_last_date`) VALUES
(202108, 82, 'SPONSOR', 'General Sponsorship', 10, 0, 0.00, '2020-12-31');


-- /opportunity ----------------------------------------------------

--
-- DATA for user
--
--
-- Digwas Honors
UPDATE `user`
   SET honors = 'EFIAP/b, EPSA, EFIP, EIUP, Hon.MoL, A.CPE, Hon.CPE, Hon.WPG, Hon. AvTvISO. Hon.PESGSPC, GPA.PESGSPC, Hon.FPPS, Hon.GNG'
     , avatar = 'digwas.png'
 WHERE user_id = 17
   AND login = 'digwas';
-- A G Gangadhar - Honors and email
UPDATE `user`
  SET honors = 'FRPS, EFIAP, EFIP, cMOL, Hon.MFIP(Nature), Hon.FBCA, Hon.G.APS'
    , email = 'aggangadhar.ga@gmail.com'
	, avatar = 'gangadhar.png'
WHERE user_id = 12
  AND login = 'gangadhar';
-- A G Lakshminarayan - Honors and email
UPDATE `user`
SET honors = 'EFIAP. FFIP. Hon. PESGSPC. GPA.PESGSPC'
  , email = 'aglnarayan@gmail.com'
WHERE user_id = 39
AND login = 'aglnarayan';

INSERT INTO `user` (`user_id`, `user_name`, `login`, `password`, `type`, `avatar`, `title`, `honors`, `email`, `profile`, `profile_file`, `status`) VALUES
(89, 'MR. HIRA PUNJABI', 'phira', 'jury123', 'JURY', 'hira_punjabi.png', '', 'HON.PSI', 'hirawildlife@gmail.com', '', 'hira_punjabi.htm', 'ACTIVE'),
(90, 'MR. RAVINDRANATH MALLULA', 'mrabi', 'jury123', 'JURY', 'ravindranath_m.png', '', 'AFIAP, Hon. EUPSA, FBDSA, FFIP', 'nrmallula@gmail.com', '', 'ravindranath_m.htm', 'ACTIVE'),
(91, 'MR. SUDHIR SAXENA', 'ssaxena', 'jury123', 'JURY', 'sudhir_saxena.png', '', 'ARPS, EFIAP/g, EFIP, EFIP/g (Nature), Hon.FICS', 'email', '', 'sudhir_saxena.htm', 'ACTIVE'),
(92, 'MR. VAIBHAV SHRIKANT JAGUSTE', 'jvaibhav', 'jury123', 'JURY', 'vaibhav_jaguste.png', '', 'EFIAP,AIIPC, FFIP,APSI, AICS, GPA Hon, PESGSPC ,Hon SWAN, Hon CPC, Hon APF', 'jaguste.vaibhav@gmail.com', '', 'vaibhav_jaguste.htm', 'ACTIVE');



-- /user --------------------------------------------------------------

--
-- DATA for assignment`
--
-- A G Gangadhar - 12
-- A G Lakshminarayan - 39
-- Digwas - 17
-- Hira Punjabi - 89
-- Rabindranath M - 90
-- Sudhir Saxena - 91
-- Vaibhav Jaguste - 92
--
-- COLOR OPEN - Vaibhav Jaguste, A G Lakshminarayan, Sudhir Saxena
-- MONO OPEN - Digwas Bellemane, Rabindranath M, A G Lakshminarayan
-- NATURE - Vaibhav Jaguste, A G Gangadhar, Hira Punjabi
-- TRAVEL - Digwas Bellemane, A G Lakshminarayan, Sudhir Saxena
--
INSERT INTO `assignment` (`yearmonth`, `section`, `user_id`, `jurynumber`) VALUES
(202108, 'COLOR', 92, 1),
(202108, 'COLOR', 39, 2),
(202108, 'COLOR', 91, 3),
(202108, 'MONOCHROME', 17, 1),
(202108, 'MONOCHROME', 90, 2),
(202108, 'MONOCHROME', 39, 3),
(202108, 'NATURE', 92, 1),
(202108, 'NATURE', 12, 2),
(202108, 'NATURE', 89, 3),
(202108, 'TRAVEL', 17, 1),
(202108, 'TRAVEL', 39, 2),
(202108, 'TRAVEL', 91, 3);


-- /assignment --------------------------



--
-- Black List Updates
--

-- Updates based on Red List Published in June 2020
INSERT INTO `blacklist` (entity_name, entity_type, email, phone, issuer, reference, expiry_date) VALUES
('Debashis Ganguly', 'INDIVIDUAL', '', '', 'FIP', 'FIP Viewfinder April 2021', '2099-12-31');

--
-- Updates for Remote judging
--

INSERT INTO `jury_session` (`yearmonth`, `section`, `award_group`) VALUES
('202108', 'COLOR', 'ALL_AGES'),
('202108', 'MONOCHROME', 'ALL_AGES'),
('202108', 'TRAVEL', 'ALL_AGES'),
('202108', 'NATURE', 'ALL_AGES');

--
-- After Judging
--

-- Statistics Definitions
INSERT INTO `stats_def` (`yearmonth`, `stat_category`, `award_group`, `stat_segment`, `stat_segment_sequence`, `stat_routine`, `stat_refresh`) VALUES
(202108, 'BITS', 'GENERAL', 'Statistical Bits', 0, 'stats_bits', 0),
(202108, 'INIT', 'GENERAL', 'Prepare pic & entry', 0, 'prepare_pic_entry', 1),
(202108, 'PARTICIPATION', 'ALL_AGES', 'By Club', 3, 'participation_by_club', 1),
(202108, 'PARTICIPATION', 'ALL_AGES', 'By Country', 2, 'participation_by_country', 1),
(202108, 'PARTICIPATION', 'ALL_AGES', 'By Gender', 4, 'participation_by_gender', 1),
(202108, 'PARTICIPATION', 'ALL_AGES', 'By Section', 1, 'participation_by_section', 1),
(202108, 'PARTICIPATION', 'YOUTH', 'By Club', 3, 'participation_by_club', 1),
(202108, 'PARTICIPATION', 'YOUTH', 'By Country', 2, 'participation_by_country', 1),
(202108, 'PARTICIPATION', 'YOUTH', 'By Gender', 4, 'participation_by_gender', 1),
(202108, 'PARTICIPATION', 'YOUTH', 'By Section', 1, 'participation_by_section', 1),
(202108, 'TOP', 'ALL_AGES', 'Top [category] Entrants', 5, 'top_entrants', 1),
(202108, 'TOP', 'YOUTH', 'Top [category] Entrants', 5, 'top_entrants', 1);

--
-- Add support for Virtual Exhibition and Guest Book
--

INSERT INTO `exhibition` (`yearmonth`, `is_virtual`, `virtual_tour_ready`) VALUES ("202108", "1", "1");


UPDATE exhibition
SET dignitory_roles = "Chief Guest|Guest of Honor",
    dignitory_names = "Shri. K Srinivas, IAS|Shri. Anup Sah",
	dignitory_positions = "Commissioner, Department of Youth Empowerment & Sports, Govt. of Karnataka|Mountaineer, Photographer of Himalayan Life, Nainital, Recipient of Padma Shri award",
	dignitory_avatars = "ksrinivas.png|anupsah.jpg"
WHERE yearmonth = 202108;



/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
