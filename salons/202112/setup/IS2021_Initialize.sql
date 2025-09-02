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

INSERT INTO `contest` (`yearmonth`, `contest_name`) VALUES (202112, 'YPS International Digital Salon 2021');


UPDATE contest
SET is_salon = '1',
	is_international = '1',
	is_no_to_past_acceptance = '1',
	contest_description_blob = 'contest_description.htm',
	terms_conditions_blob = 'terms_conditions.htm',
	contest_announcement_blob = '',
	fee_structure_blob = 'fee_structure.php',
	discount_structure_blob = '',
	registration_start_date = '2021-08-15',
	registration_last_date = '2021-10-27',
	submission_timezone = 'America/Anchorage',
	submission_timezone_name = 'Alaska',
	judging_start_date = '2021-10-30',
	judging_end_date = '2021-11-02',
	results_date = '2021-11-05',
	update_start_date = '2021-11-05',
	update_end_date = '2021-11-18',
	exhibition_start_date = '2021-12-04',
	exhibition_end_date = '2021-12-10',
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
	catalog_release_date = '2021-12-04',
	catalog_ready = '0',
	catalog = '',
	catalog_download = '',
	catalog_order_last_date = '2021-12-18',
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
WHERE yearmonth = '202112';


-- /contest -----------------------------------------------------------

--
-- DATA for TABLE `section`
--

INSERT INTO `section` (`yearmonth`, `section`, `section_type`, `section_sequence`, `stub`,
					   `description`, `rules`, `rules_blob`,
					   `submission_last_date`, `max_pics_per_entry`,
					   `num_entrants`, `num_pictures`, `num_awards`, `num_hms`, `num_acceptances`, `num_winners`) VALUES
(202112, 'COLOR', 'D', 2, 'COD',
 		'', '', 'section_color_rules.htm',
 		'2021-10-27', 4, 0, 0, 0, 0, 0, 0),
(202112, 'MONOCHROME', 'D', 1, 'MOD',
 		'', '', 'section_monochrome_rules.htm',
 		'2021-10-27', 4, 0, 0, 0, 0, 0, 0),
(202112, 'NATURE', 'D', 3, 'ND',
		'', '', 'section_nature_rules.htm',
		'2021-10-27', 4, 0, 0, 0, 0, 0, 0),
(202112, 'TRAVEL', 'D', 4, 'TD',
		'', '', 'section_travel_rules.htm',
		'2021-10-27', 4, 0, 0, 0, 0, 0, 0);

-- /section --------------------------------------------------------------

--
-- DATA for recognition
--
INSERT INTO `recognition` (`yearmonth`, `short_code`, `organization_name`, `website`, `recognition_id`, `small_logo`, `logo`, `notification`, `description`, `notice`, `rules`) VALUES
(202112, 'FIP', 'Federation of Indian Photography', 'http://www.fip.org.in', '2021/FIP/197/2021', '', 'fip_logo.png', 'fip_notification.jpg', '', '', NULL),
(202112, 'FIAP', 'Federation Internationale de l\'Art Photographique', 'http://www.fiap.net', '2021/533', '', 'fiap_logo.jpg', 'fiap_notification.jpg', '', '&quot;I hereby expressly agree to FIAP document 018/2017 Conditions and regulations for FIAP Patronage and FIAP document \r\n017/2017 Sanctions for breaching FIAP regulations and the red list. I am particularly aware of chapter II Regulations for International photographic events under \r\nFIAP patronage of FIAP document 018/2017, dealing under Section II.2 and II.3 with the FIAP participation rules, the sanctions for breaching FIAP regulations and \r\nthe red list.&quot;', NULL),
(202112, 'GPU', 'Global Photographic Union', 'http://www.gpuphoto.com', 'L210169', '', 'gpu_logo.jpg', 'gpu_notification.jpg', '', '', NULL),
(202112, 'ICS', 'The Image Colleague Society', 'http://www.icsphoto.us', '2021/132', '', 'ics_logo.jpg', 'ics_notification.png', '', '', NULL),
(202112, 'MOL', 'Master of Light Photographic Association', 'http://www.masteroflight.org', '2021/88', '', 'mol_logo.png', 'mol_notification.jpg', '', '', NULL),
(202112, 'PSA', 'Photographic Society of America', 'http://psa-photo.org', '2021-1608', '', 'psa_logo.png', 'psa_notification.jpg', '', '', NULL),
(202112, 'YPS', 'Youth Photographic Society', 'https://www.ypsbengaluru.com', '2021/001', '', 'yps_patronage_logo.jpg', 'yps_notification.jpg', '', '', NULL);


-- /recognition ------------------------------------------------------------


--
-- DATA for TABLE `entrant_category`
--
INSERT INTO `entrant_category` (`yearmonth`, `entrant_category`, `entrant_category_name`, `yps_membership_required`, `yps_member_prefixes`,
								`gender_must_match`, `gender_match`, `age_within_range`, `age_minimum`, `age_maximum`,
								`country_must_match`, `country_codes`, `state_must_match`, `state_names`,
								`currency`, `can_create_club`, `fee_waived`, `acceptance_reported`, `award_group`, `fee_group`,
								`default_participation_code`, `default_digital_sections`, `default_print_sections`, `discount_group`) VALUES
(202112, 'GLOBAL', 'Participants from all countries', 0, '',
 		0, '', 1, 8, 120,
 		0, '', 0, '',
 		'INR', 1, 1, 1, 'ALL_PARTICIPANTS', 'GENERAL',
 		'DIGITAL_ALL', 4, 0, 'NONE'),
(202112, 'YPS_MEMBER', 'YPS Members', 1, 'LM,IM,JA',
 		0, '', 1, 8, 120,
 		0, '', 0, '',
 		'INR', 0, 1, 1, 'ALL_PARTICIPANTS', 'GENERAL',
 		'DIGITAL_ALL', 4, 0, 'NONE');


-- /entrant_category ---------------------------------------------------


--
-- DATA for TABLE `award`
--

-- -- Special awards against names of Legends
-- INSERT INTO `award` (`yearmonth`, `award_id`, `level`, `sequence`, `section`, `award_group`, `award_type`, `award_name`, `description`,
-- 					 `number_of_awards`, `has_medal`, `has_pin`, `has_ribbon`, `has_memento`, `has_gift`, `has_certificate`, `cash_award`,
-- 					 `sponsored_awards`, `sponsorship_per_award`, `partial_sponsorship_permitted`, `sponsorship_last_date`
-- ) VALUES
-- (202112, 201, 20, 1, 'CONTEST', 'ALL_PARTICIPANTS', 'pic', 'Dr. G Thomas YPS Golden Jubilee Award - Monochrome', '',
--  			1, 0, 0, 0, 1, 0, 1, 0,
--  			0, 0, 0, '2021-06-30'
-- ),
-- (202112, 202, 20, 2, 'CONTEST', 'ALL_PARTICIPANTS', 'pic', 'C Rajagopal YPS Golden Jubilee Award - Monochrome', '',
--  			1, 0, 0, 0, 1, 0, 1, 0,
--  			0, 0, 0, '2021-06-30'
-- ),
-- (202112, 203, 20, 3, 'CONTEST', 'ALL_PARTICIPANTS', 'pic', 'T N A Perumal YPS Golden Jubilee Award - Color', '',
--  			1, 0, 0, 0, 1, 0, 1, 0,
--  			0, 0, 0, '2021-06-30'
-- ),
-- (202112, 204, 20, 4, 'CONTEST', 'ALL_PARTICIPANTS', 'pic', 'M Y Ghorpade YPS Golden Jubilee Award - Color', '',
--  			1, 0, 0, 0, 1, 0, 1, 0,
--  			0, 0, 0, '2021-06-30'
-- ),
-- (202112, 205, 20, 5, 'CONTEST', 'ALL_PARTICIPANTS', 'pic', 'E Hanumantha Rao YPS Golden Jubilee Award - Nature', '',
--  			1, 0, 0, 0, 1, 0, 1, 0,
--  			0, 0, 0, '2021-06-30'
-- ),
-- (202112, 206, 20, 6, 'CONTEST', 'ALL_PARTICIPANTS', 'pic', 'B N S Deo YPS Golden Jubilee Award - Nature', '',
--  			1, 0, 0, 0, 1, 0, 1, 0,
--  			0, 0, 0, '2021-06-30'
-- ),
-- (202112, 207, 20, 7, 'CONTEST', 'ALL_PARTICIPANTS', 'pic', 'O C Edwards YPS Golden Jubilee Award - Travel', '',
--  			1, 0, 0, 0, 1, 0, 1, 0,
--  			0, 0, 0, '2021-06-30'
-- ),
-- (202112, 208, 20, 8, 'CONTEST', 'ALL_PARTICIPANTS', 'pic', 'Dr. D V Rao YPS Golden Jubilee Award - Travel', '',
--  			1, 0, 0, 0, 1, 0, 1, 0,
--  			0, 0, 0, '2021-06-30'
-- );

--
-- Best Participant Scoring
--
-- The Best Participant Award will be the FIAP Blue Pin. The award will be conferred on the participant with highest number of acceptances
-- to conform to rule II.7 of FIAP Conditions and Regulations for FIAP Patronage - FIAP DOC 018 2017 E
-- Hence all awards and acceptances are configured with a weight of '1'
--

--
-- PICTURE AWARDS
--

--
-- GOLD MEDALS
--
-- FIAP Gold
INSERT INTO `award` (`yearmonth`, `award_id`, `level`, `sequence`, `section`, `award_group`, `award_type`, `award_name`, `recognition_code`, `description`,
					 `number_of_awards`, `award_weight`, `has_medal`, `has_pin`, `has_ribbon`, `has_memento`, `has_gift`, `has_certificate`, `cash_award`
) VALUES
(202112, 1, 1, 1, 'MONOCHROME', 'ALL_PARTICIPANTS', 'pic', 'FIAP Gold', 'FIP', '',
 			1, 1, 1, 0, 0, 0, 0, 1, 0
),
(202112, 2, 1, 1, 'COLOR', 'ALL_PARTICIPANTS', 'pic', 'FIAP Gold', 'FIP', '',
 			1, 1, 1, 0, 0, 0, 0, 1, 0
),
(202112, 3, 1, 1, 'NATURE', 'ALL_PARTICIPANTS', 'pic', 'FIAP Gold', 'FIP', '',
 			1, 1, 1, 0, 0, 0, 0, 1, 0
),
(202112, 4, 1, 1, 'TRAVEL', 'ALL_PARTICIPANTS', 'pic', 'FIAP Gold', 'FIP', '',
 			1, 1, 1, 0, 0, 0, 0, 1, 0
);
-- PSA Gold
INSERT INTO `award` (`yearmonth`, `award_id`, `level`, `sequence`, `section`, `award_group`, `award_type`, `award_name`, `recognition_code`, `description`,
					 `number_of_awards`, `award_weight`, `has_medal`, `has_pin`, `has_ribbon`, `has_memento`, `has_gift`, `has_certificate`, `cash_award`
) VALUES
(202112, 6, 1, 2, 'MONOCHROME', 'ALL_PARTICIPANTS', 'pic', 'PSA Gold', 'PSA', '',
 			1, 1, 1, 0, 0, 0, 0, 1, 0
),
(202112, 7, 1, 2, 'COLOR', 'ALL_PARTICIPANTS', 'pic', 'PSA Gold', 'PSA', '',
 			1, 1, 1, 0, 0, 0, 0, 1, 0
),
(202112, 8, 1, 2, 'NATURE', 'ALL_PARTICIPANTS', 'pic', 'PSA Gold', 'PSA', '',
 			1, 1, 1, 0, 0, 0, 0, 1, 0
),
(202112, 9, 1, 2, 'TRAVEL', 'ALL_PARTICIPANTS', 'pic', 'PSA Gold', 'PSA', '',
 			1, 1, 1, 0, 0, 0, 0, 1, 0
);
-- ICS Gold
INSERT INTO `award` (`yearmonth`, `award_id`, `level`, `sequence`, `section`, `award_group`, `award_type`, `award_name`, `recognition_code`, `description`,
					 `number_of_awards`, `award_weight`, `has_medal`, `has_pin`, `has_ribbon`, `has_memento`, `has_gift`, `has_certificate`, `cash_award`
) VALUES
(202112, 11, 1, 3, 'MONOCHROME', 'ALL_PARTICIPANTS', 'pic', 'ICS Gold', 'ICS', '',
 			1, 1, 1, 0, 0, 0, 0, 1, 0
),
(202112, 12, 1, 3, 'COLOR', 'ALL_PARTICIPANTS', 'pic', 'ICS Gold', 'ICS', '',
 			1, 1, 1, 0, 0, 0, 0, 1, 0
),
(202112, 13, 1, 3, 'NATURE', 'ALL_PARTICIPANTS', 'pic', 'ICS Gold', 'ICS', '',
 			1, 1, 1, 0, 0, 0, 0, 1, 0
),
(202112, 14, 1, 3, 'TRAVEL', 'ALL_PARTICIPANTS', 'pic', 'ICS Gold', 'ICS', '',
 			1, 1, 1, 0, 0, 0, 0, 1, 0
);
-- MoL Gold
INSERT INTO `award` (`yearmonth`, `award_id`, `level`, `sequence`, `section`, `award_group`, `award_type`, `award_name`, `recognition_code`, `description`,
					 `number_of_awards`, `award_weight`, `has_medal`, `has_pin`, `has_ribbon`, `has_memento`, `has_gift`, `has_certificate`, `cash_award`
) VALUES
(202112, 16, 1, 4, 'MONOCHROME', 'ALL_PARTICIPANTS', 'pic', 'MoL Gold', 'MOL', '',
 			1, 1, 1, 0, 0, 0, 0, 1, 0
),
(202112, 17, 1, 4, 'COLOR', 'ALL_PARTICIPANTS', 'pic', 'MoL Gold', 'MOL', '',
 			1, 1, 1, 0, 0, 0, 0, 1, 0
),
(202112, 18, 1, 4, 'NATURE', 'ALL_PARTICIPANTS', 'pic', 'MoL Gold', 'MOL', '',
 			1, 1, 1, 0, 0, 0, 0, 1, 0
),
(202112, 19, 1, 4, 'TRAVEL', 'ALL_PARTICIPANTS', 'pic', 'MoL Gold', 'MOL', '',
 			1, 1, 1, 0, 0, 0, 0, 1, 0
);
-- GPU Gold
INSERT INTO `award` (`yearmonth`, `award_id`, `level`, `sequence`, `section`, `award_group`, `award_type`, `award_name`, `recognition_code`, `description`,
					 `number_of_awards`, `award_weight`, `has_medal`, `has_pin`, `has_ribbon`, `has_memento`, `has_gift`, `has_certificate`, `cash_award`
) VALUES
(202112, 21, 1, 5, 'MONOCHROME', 'ALL_PARTICIPANTS', 'pic', 'GPU Gold', 'GPU', '',
 			1, 1, 1, 0, 0, 0, 0, 1, 0
),
(202112, 22, 1, 5, 'COLOR', 'ALL_PARTICIPANTS', 'pic', 'GPU Gold', 'GPU', '',
 			1, 1, 1, 0, 0, 0, 0, 1, 0
),
(202112, 23, 1, 5, 'NATURE', 'ALL_PARTICIPANTS', 'pic', 'GPU Gold', 'GPU', '',
 			1, 1, 1, 0, 0, 0, 0, 1, 0
),
(202112, 24, 1, 5, 'TRAVEL', 'ALL_PARTICIPANTS', 'pic', 'GPU Gold', 'GPU', '',
 			1, 1, 1, 0, 0, 0, 0, 1, 0
);
-- FIP Gold
INSERT INTO `award` (`yearmonth`, `award_id`, `level`, `sequence`, `section`, `award_group`, `award_type`, `award_name`, `recognition_code`, `description`,
					 `number_of_awards`, `award_weight`, `has_medal`, `has_pin`, `has_ribbon`, `has_memento`, `has_gift`, `has_certificate`, `cash_award`
) VALUES
(202112, 26, 1, 6, 'MONOCHROME', 'ALL_PARTICIPANTS', 'pic', 'FIP Medal', 'FIP', '',
 			1, 1, 1, 0, 0, 0, 0, 1, 0
),
(202112, 27, 1, 6, 'COLOR', 'ALL_PARTICIPANTS', 'pic', 'FIP Medal', 'FIP', '',
 			1, 1, 1, 0, 0, 0, 0, 1, 0
),
(202112, 28, 1, 6, 'NATURE', 'ALL_PARTICIPANTS', 'pic', 'FIP Medal', 'FIP', '',
 			1, 1, 1, 0, 0, 0, 0, 1, 0
),
(202112, 29, 1, 6, 'TRAVEL', 'ALL_PARTICIPANTS', 'pic', 'FIP Medal', 'FIP', '',
 			1, 1, 1, 0, 0, 0, 0, 1, 0
);
-- YPS Gold
INSERT INTO `award` (`yearmonth`, `award_id`, `level`, `sequence`, `section`, `award_group`, `award_type`, `award_name`, `recognition_code`, `description`,
					 `number_of_awards`, `award_weight`, `has_medal`, `has_pin`, `has_ribbon`, `has_memento`, `has_gift`, `has_certificate`, `cash_award`
) VALUES
(202112, 31, 1, 7, 'MONOCHROME', 'ALL_PARTICIPANTS', 'pic', 'YPS Gold', 'YPS', '',
 			1, 1, 1, 0, 0, 0, 0, 1, 0
),
(202112, 32, 1, 7, 'COLOR', 'ALL_PARTICIPANTS', 'pic', 'YPS Gold', 'YPS', '',
 			1, 1, 1, 0, 0, 0, 0, 1, 0
),
(202112, 33, 1, 7, 'NATURE', 'ALL_PARTICIPANTS', 'pic', 'YPS Gold', 'YPS', '',
 			1, 1, 1, 0, 0, 0, 0, 1, 0
),
(202112, 34, 1, 7, 'TRAVEL', 'ALL_PARTICIPANTS', 'pic', 'YPS Gold', 'YPS', '',
 			1, 1, 1, 0, 0, 0, 0, 1, 0
);

--
-- SILVER MEDALS
--
-- PSA Silver
INSERT INTO `award` (`yearmonth`, `award_id`, `level`, `sequence`, `section`, `award_group`, `award_type`, `award_name`, `recognition_code`, `description`,
					 `number_of_awards`, `award_weight`, `has_medal`, `has_pin`, `has_ribbon`, `has_memento`, `has_gift`, `has_certificate`, `cash_award`
) VALUES
(202112, 106, 2, 2, 'MONOCHROME', 'ALL_PARTICIPANTS', 'pic', 'PSA Silver', 'PSA', '',
 			1, 1, 1, 0, 0, 0, 0, 1, 0
),
(202112, 107, 2, 2, 'COLOR', 'ALL_PARTICIPANTS', 'pic', 'PSA Silver', 'PSA', '',
 			1, 1, 1, 0, 0, 0, 0, 1, 0
),
(202112, 108, 2, 2, 'NATURE', 'ALL_PARTICIPANTS', 'pic', 'PSA Silver', 'PSA', '',
 			1, 1, 1, 0, 0, 0, 0, 1, 0
),
(202112, 109, 2, 2, 'TRAVEL', 'ALL_PARTICIPANTS', 'pic', 'PSA Silver', 'PSA', '',
 			1, 1, 1, 0, 0, 0, 0, 1, 0
);
-- ICS SILVER
INSERT INTO `award` (`yearmonth`, `award_id`, `level`, `sequence`, `section`, `award_group`, `award_type`, `award_name`, `recognition_code`, `description`,
					 `number_of_awards`, `award_weight`, `has_medal`, `has_pin`, `has_ribbon`, `has_memento`, `has_gift`, `has_certificate`, `cash_award`
) VALUES
(202112, 111, 2, 3, 'MONOCHROME', 'ALL_PARTICIPANTS', 'pic', 'ICS Silver', 'ICS', '',
 			1, 1, 1, 0, 0, 0, 0, 1, 0
),
(202112, 112, 2, 3, 'COLOR', 'ALL_PARTICIPANTS', 'pic', 'ICS Silver', 'ICS', '',
 			1, 1, 1, 0, 0, 0, 0, 1, 0
),
(202112, 113, 2, 3, 'NATURE', 'ALL_PARTICIPANTS', 'pic', 'ICS Silver', 'ICS', '',
 			1, 1, 1, 0, 0, 0, 0, 1, 0
),
(202112, 114, 2, 3, 'TRAVEL', 'ALL_PARTICIPANTS', 'pic', 'ICS Silver', 'ICS', '',
 			1, 1, 1, 0, 0, 0, 0, 1, 0
);
-- MoL Silver
INSERT INTO `award` (`yearmonth`, `award_id`, `level`, `sequence`, `section`, `award_group`, `award_type`, `award_name`, `recognition_code`, `description`,
					 `number_of_awards`, `award_weight`, `has_medal`, `has_pin`, `has_ribbon`, `has_memento`, `has_gift`, `has_certificate`, `cash_award`
) VALUES
(202112, 116, 2, 4, 'MONOCHROME', 'ALL_PARTICIPANTS', 'pic', 'MoL Silver', 'MOL', '',
 			1, 1, 1, 0, 0, 0, 0, 1, 0
),
(202112, 117, 2, 4, 'COLOR', 'ALL_PARTICIPANTS', 'pic', 'MoL Silver', 'MOL', '',
 			1, 1, 1, 0, 0, 0, 0, 1, 0
),
(202112, 118, 2, 4, 'NATURE', 'ALL_PARTICIPANTS', 'pic', 'MoL Silver', 'MOL', '',
 			1, 1, 1, 0, 0, 0, 0, 1, 0
),
(202112, 119, 2, 4, 'TRAVEL', 'ALL_PARTICIPANTS', 'pic', 'MoL Silver', 'MOL', '',
 			1, 1, 1, 0, 0, 0, 0, 1, 0
);
-- YPS Silver
INSERT INTO `award` (`yearmonth`, `award_id`, `level`, `sequence`, `section`, `award_group`, `award_type`, `award_name`, `recognition_code`, `description`,
					 `number_of_awards`, `award_weight`, `has_medal`, `has_pin`, `has_ribbon`, `has_memento`, `has_gift`, `has_certificate`, `cash_award`
) VALUES
(202112, 131, 2, 7, 'MONOCHROME', 'ALL_PARTICIPANTS', 'pic', 'YPS Silver', 'YPS', '',
 			1, 1, 1, 0, 0, 0, 0, 1, 0
),
(202112, 132, 2, 7, 'COLOR', 'ALL_PARTICIPANTS', 'pic', 'YPS Silver', 'YPS', '',
 			1, 1, 1, 0, 0, 0, 0, 1, 0
),
(202112, 133, 2, 7, 'NATURE', 'ALL_PARTICIPANTS', 'pic', 'YPS Silver', 'YPS', '',
 			1, 1, 1, 0, 0, 0, 0, 1, 0
),
(202112, 134, 2, 7, 'TRAVEL', 'ALL_PARTICIPANTS', 'pic', 'YPS Silver', 'YPS', '',
 			1, 1, 1, 0, 0, 0, 0, 1, 0
);


--
-- BRONZE MEDALS
--
-- PSA Bronze
INSERT INTO `award` (`yearmonth`, `award_id`, `level`, `sequence`, `section`, `award_group`, `award_type`, `award_name`, `recognition_code`, `description`,
					 `number_of_awards`, `award_weight`, `has_medal`, `has_pin`, `has_ribbon`, `has_memento`, `has_gift`, `has_certificate`, `cash_award`
) VALUES
(202112, 206, 3, 2, 'MONOCHROME', 'ALL_PARTICIPANTS', 'pic', 'PSA Bronze', 'PSA', '',
 			1, 1, 1, 0, 0, 0, 0, 1, 0
),
(202112, 207, 3, 2, 'COLOR', 'ALL_PARTICIPANTS', 'pic', 'PSA Bronze', 'PSA', '',
 			1, 1, 1, 0, 0, 0, 0, 1, 0
),
(202112, 208, 3, 2, 'NATURE', 'ALL_PARTICIPANTS', 'pic', 'PSA Bronze', 'PSA', '',
 			1, 1, 1, 0, 0, 0, 0, 1, 0
),
(202112, 209, 3, 2, 'TRAVEL', 'ALL_PARTICIPANTS', 'pic', 'PSA Bronze', 'PSA', '',
 			1, 1, 1, 0, 0, 0, 0, 1, 0
);
-- ICS Bronze
INSERT INTO `award` (`yearmonth`, `award_id`, `level`, `sequence`, `section`, `award_group`, `award_type`, `award_name`, `recognition_code`, `description`,
					 `number_of_awards`, `award_weight`, `has_medal`, `has_pin`, `has_ribbon`, `has_memento`, `has_gift`, `has_certificate`, `cash_award`
) VALUES
(202112, 211, 3, 3, 'MONOCHROME', 'ALL_PARTICIPANTS', 'pic', 'ICS Bronze', 'ICS', '',
 			1, 1, 1, 0, 0, 0, 0, 1, 0
),
(202112, 212, 3, 3, 'COLOR', 'ALL_PARTICIPANTS', 'pic', 'ICS Bronze', 'ICS', '',
 			1, 1, 1, 0, 0, 0, 0, 1, 0
),
(202112, 213, 3, 3, 'NATURE', 'ALL_PARTICIPANTS', 'pic', 'ICS Bronze', 'ICS', '',
 			1, 1, 1, 0, 0, 0, 0, 1, 0
),
(202112, 214, 3, 3, 'TRAVEL', 'ALL_PARTICIPANTS', 'pic', 'ICS Bronze', 'ICS', '',
 			1, 1, 1, 0, 0, 0, 0, 1, 0
);
-- MoL Bronze
INSERT INTO `award` (`yearmonth`, `award_id`, `level`, `sequence`, `section`, `award_group`, `award_type`, `award_name`, `recognition_code`, `description`,
					 `number_of_awards`, `award_weight`, `has_medal`, `has_pin`, `has_ribbon`, `has_memento`, `has_gift`, `has_certificate`, `cash_award`
) VALUES
(202112, 216, 3, 4, 'MONOCHROME', 'ALL_PARTICIPANTS', 'pic', 'MoL Bronze', 'MOL', '',
 			1, 1, 1, 0, 0, 0, 0, 1, 0
),
(202112, 217, 3, 4, 'COLOR', 'ALL_PARTICIPANTS', 'pic', 'MoL Bronze', 'MOL', '',
 			1, 1, 1, 0, 0, 0, 0, 1, 0
),
(202112, 218, 3, 4, 'NATURE', 'ALL_PARTICIPANTS', 'pic', 'MoL Bronze', 'MOL', '',
 			1, 1, 1, 0, 0, 0, 0, 1, 0
),
(202112, 219, 3, 4, 'TRAVEL', 'ALL_PARTICIPANTS', 'pic', 'MoL Bronze', 'MOL', '',
 			1, 1, 1, 0, 0, 0, 0, 1, 0
);
-- YPS Bronze
INSERT INTO `award` (`yearmonth`, `award_id`, `level`, `sequence`, `section`, `award_group`, `award_type`, `award_name`, `recognition_code`, `description`,
					 `number_of_awards`, `award_weight`, `has_medal`, `has_pin`, `has_ribbon`, `has_memento`, `has_gift`, `has_certificate`, `cash_award`
) VALUES
(202112, 231, 3, 7, 'MONOCHROME', 'ALL_PARTICIPANTS', 'pic', 'YPS Bronze', 'YPS', '',
 			1, 1, 1, 0, 0, 0, 0, 1, 0
),
(202112, 232, 3, 7, 'COLOR', 'ALL_PARTICIPANTS', 'pic', 'YPS Bronze', 'YPS', '',
 			1, 1, 1, 0, 0, 0, 0, 1, 0
),
(202112, 233, 3, 7, 'NATURE', 'ALL_PARTICIPANTS', 'pic', 'YPS Bronze', 'YPS', '',
 			1, 1, 1, 0, 0, 0, 0, 1, 0
),
(202112, 234, 3, 7, 'TRAVEL', 'ALL_PARTICIPANTS', 'pic', 'YPS Bronze', 'YPS', '',
 			1, 1, 1, 0, 0, 0, 0, 1, 0
);

--
-- RIBBONS
--
-- FIAP Ribbon  - 8
INSERT INTO `award` (`yearmonth`, `award_id`, `level`, `sequence`, `section`, `award_group`, `award_type`, `award_name`, `recognition_code`, `description`,
					 `number_of_awards`, `award_weight`, `has_medal`, `has_pin`, `has_ribbon`, `has_memento`, `has_gift`, `has_certificate`, `cash_award`
) VALUES
(202112, 501, 9, 1, 'MONOCHROME', 'ALL_PARTICIPANTS', 'pic', 'FIAP Ribbon', 'FIP', '',
 			2, 1, 1, 0, 0, 0, 0, 1, 0
),
(202112, 502, 9, 1, 'COLOR', 'ALL_PARTICIPANTS', 'pic', 'FIAP Ribbon', 'FIP', '',
 			2, 1, 1, 0, 0, 0, 0, 1, 0
),
(202112, 503, 9, 1, 'NATURE', 'ALL_PARTICIPANTS', 'pic', 'FIAP Ribbon', 'FIP', '',
 			2, 1, 1, 0, 0, 0, 0, 1, 0
),
(202112, 504, 9, 1, 'TRAVEL', 'ALL_PARTICIPANTS', 'pic', 'FIAP Ribbon', 'FIP', '',
 			2, 1, 1, 0, 0, 0, 0, 1, 0
);
-- MoL Gold Ribbon - 4
INSERT INTO `award` (`yearmonth`, `award_id`, `level`, `sequence`, `section`, `award_group`, `award_type`, `award_name`, `recognition_code`, `description`,
					 `number_of_awards`, `award_weight`, `has_medal`, `has_pin`, `has_ribbon`, `has_memento`, `has_gift`, `has_certificate`, `cash_award`
) VALUES
(202112, 506, 9, 2, 'MONOCHROME', 'ALL_PARTICIPANTS', 'pic', 'MoL Gold Ribbon', 'MOL', '',
 			1, 1, 1, 0, 0, 0, 0, 1, 0
),
(202112, 507, 9, 2, 'COLOR', 'ALL_PARTICIPANTS', 'pic', 'MoL Gold Ribbon', 'MOL', '',
 			1, 1, 1, 0, 0, 0, 0, 1, 0
),
(202112, 508, 9, 2, 'NATURE', 'ALL_PARTICIPANTS', 'pic', 'MoL Gold Ribbon', 'MOL', '',
 			1, 1, 1, 0, 0, 0, 0, 1, 0
),
(202112, 509, 9, 2, 'TRAVEL', 'ALL_PARTICIPANTS', 'pic', 'MoL Gold Ribbon', 'MOL', '',
 			1, 1, 1, 0, 0, 0, 0, 1, 0
);
-- MoL Silver Ribbon - 4
INSERT INTO `award` (`yearmonth`, `award_id`, `level`, `sequence`, `section`, `award_group`, `award_type`, `award_name`, `recognition_code`, `description`,
					 `number_of_awards`, `award_weight`, `has_medal`, `has_pin`, `has_ribbon`, `has_memento`, `has_gift`, `has_certificate`, `cash_award`
) VALUES
(202112, 511, 9, 3, 'MONOCHROME', 'ALL_PARTICIPANTS', 'pic', 'MoL Silver Ribbon', 'MOL', '',
 			1, 1, 1, 0, 0, 0, 0, 1, 0
),
(202112, 512, 9, 3, 'COLOR', 'ALL_PARTICIPANTS', 'pic', 'MoL Silver Ribbon', 'MOL', '',
 			1, 1, 1, 0, 0, 0, 0, 1, 0
),
(202112, 513, 9, 3, 'NATURE', 'ALL_PARTICIPANTS', 'pic', 'MoL Silver Ribbon', 'MOL', '',
 			1, 1, 1, 0, 0, 0, 0, 1, 0
),
(202112, 514, 9, 3, 'TRAVEL', 'ALL_PARTICIPANTS', 'pic', 'MoL Silver Ribbon', 'MOL', '',
 			1, 1, 1, 0, 0, 0, 0, 1, 0
);
-- MoL Bronze Ribbon - 4
INSERT INTO `award` (`yearmonth`, `award_id`, `level`, `sequence`, `section`, `award_group`, `award_type`, `award_name`, `recognition_code`, `description`,
					 `number_of_awards`, `award_weight`, `has_medal`, `has_pin`, `has_ribbon`, `has_memento`, `has_gift`, `has_certificate`, `cash_award`
) VALUES
(202112, 516, 9, 4, 'MONOCHROME', 'ALL_PARTICIPANTS', 'pic', 'MoL Bronze Ribbon', 'MOL', '',
 			1, 1, 1, 0, 0, 0, 0, 1, 0
),
(202112, 517, 9, 4, 'COLOR', 'ALL_PARTICIPANTS', 'pic', 'MoL Bronze Ribbon', 'MOL', '',
 			1, 1, 1, 0, 0, 0, 0, 1, 0
),
(202112, 518, 9, 4, 'NATURE', 'ALL_PARTICIPANTS', 'pic', 'MoL Bronze Ribbon', 'MOL', '',
 			1, 1, 1, 0, 0, 0, 0, 1, 0
),
(202112, 519, 9, 4, 'TRAVEL', 'ALL_PARTICIPANTS', 'pic', 'MoL Bronze Ribbon', 'MOL', '',
 			1, 1, 1, 0, 0, 0, 0, 1, 0
);
-- GPU Ribbon - 16 (8 new + 8 old)
INSERT INTO `award` (`yearmonth`, `award_id`, `level`, `sequence`, `section`, `award_group`, `award_type`, `award_name`, `recognition_code`, `description`,
					 `number_of_awards`, `award_weight`, `has_medal`, `has_pin`, `has_ribbon`, `has_memento`, `has_gift`, `has_certificate`, `cash_award`
) VALUES
(202112, 521, 9, 5, 'MONOCHROME', 'ALL_PARTICIPANTS', 'pic', 'GPU Ribbon', 'GPU', '',
 			4, 1, 1, 0, 0, 0, 0, 1, 0
),
(202112, 522, 9, 5, 'COLOR', 'ALL_PARTICIPANTS', 'pic', 'GPU Ribbon', 'GPU', '',
 			4, 1, 1, 0, 0, 0, 0, 1, 0
),
(202112, 523, 9, 5, 'NATURE', 'ALL_PARTICIPANTS', 'pic', 'GPU Ribbon', 'GPU', '',
 			4, 1, 1, 0, 0, 0, 0, 1, 0
),
(202112, 524, 9, 5, 'TRAVEL', 'ALL_PARTICIPANTS', 'pic', 'GPU Ribbon', 'GPU', '',
 			4, 1, 1, 0, 0, 0, 0, 1, 0
);
-- FIP Ribbons - 40 (20 new + 20 old)
INSERT INTO `award` (`yearmonth`, `award_id`, `level`, `sequence`, `section`, `award_group`, `award_type`, `award_name`, `recognition_code`, `description`,
					 `number_of_awards`, `award_weight`, `has_medal`, `has_pin`, `has_ribbon`, `has_memento`, `has_gift`, `has_certificate`, `cash_award`
) VALUES
(202112, 526, 9, 6, 'MONOCHROME', 'ALL_PARTICIPANTS', 'pic', 'FIP Ribbon', 'FIP', '',
 			10, 1, 1, 0, 0, 0, 0, 1, 0
),
(202112, 527, 9, 6, 'COLOR', 'ALL_PARTICIPANTS', 'pic', 'FIP Ribbon', 'FIP', '',
 			10, 1, 1, 0, 0, 0, 0, 1, 0
),
(202112, 528, 9, 6, 'NATURE', 'ALL_PARTICIPANTS', 'pic', 'FIP Ribbon', 'FIP', '',
 			10, 1, 1, 0, 0, 0, 0, 1, 0
),
(202112, 529, 9, 6, 'TRAVEL', 'ALL_PARTICIPANTS', 'pic', 'FIP Ribbon', 'FIP', '',
 			10, 1, 1, 0, 0, 0, 0, 1, 0
);
-- YPS Ribbons - 20
INSERT INTO `award` (`yearmonth`, `award_id`, `level`, `sequence`, `section`, `award_group`, `award_type`, `award_name`, `recognition_code`, `description`,
					 `number_of_awards`, `award_weight`, `has_medal`, `has_pin`, `has_ribbon`, `has_memento`, `has_gift`, `has_certificate`, `cash_award`
) VALUES
(202112, 531, 9, 7, 'MONOCHROME', 'ALL_PARTICIPANTS', 'pic', 'YPS Ribbon', 'YPS', '',
 			5, 1, 1, 0, 0, 0, 0, 1, 0
),
(202112, 532, 9, 7, 'COLOR', 'ALL_PARTICIPANTS', 'pic', 'YPS Ribbon', 'YPS', '',
 			5, 1, 1, 0, 0, 0, 0, 1, 0
),
(202112, 533, 9, 7, 'NATURE', 'ALL_PARTICIPANTS', 'pic', 'YPS Ribbon', 'YPS', '',
 			5, 1, 1, 0, 0, 0, 0, 1, 0
),
(202112, 534, 9, 7, 'TRAVEL', 'ALL_PARTICIPANTS', 'pic', 'YPS Ribbon', 'YPS', '',
 			5, 1, 1, 0, 0, 0, 0, 1, 0
);

--
-- Acceptance
--
INSERT INTO `award` (`yearmonth`, `award_id`, `level`, `sequence`, `section`, `award_group`, `award_type`, `award_name`, `recognition_code`, `description`,
					 `number_of_awards`, `award_weight`, `has_medal`, `has_pin`, `has_ribbon`, `has_memento`, `has_gift`, `has_certificate`, `cash_award`
) VALUES
(202112, 901, 99, 1, 'MONOCHROME', 'ALL_PARTICIPANTS', 'pic', 'Acceptance', 'YPS', '',
 			1, 1, 0, 0, 0, 0, 0, 1, 0
),
(202112, 902, 99, 1, 'COLOR', 'ALL_PARTICIPANTS', 'pic', 'Acceptance', 'YPS', '',
 			1, 1, 0, 0, 0, 0, 0, 1, 0
),
(202112, 903, 99, 1, 'NATURE', 'ALL_PARTICIPANTS', 'pic', 'Acceptance', 'YPS', '',
 			1, 1, 0, 0, 0, 0, 0, 1, 0
),
(202112, 904, 99, 1, 'TRAVEL', 'ALL_PARTICIPANTS', 'pic', 'Acceptance', 'YPS', '',
 			1, 1, 0, 0, 0, 0, 0, 1, 0
);

--
-- PARTICIPANT AWARDS
--
-- FIAP Light Blue Pin
--
INSERT INTO `award` (`yearmonth`, `award_id`, `level`, `sequence`, `section`, `award_group`, `award_type`, `award_name`, `recognition_code`, `description`,
					 `number_of_awards`, `award_weight`, `has_medal`, `has_pin`, `has_ribbon`, `has_memento`, `has_gift`, `has_certificate`, `cash_award`
) VALUES
(202112, 1001, 1, 1, 'CONTEST', 'ALL_PARTICIPANTS', 'entry', 'FIAP Best Participant - Blue Pin', 'FIAP', '',
 			1, 0, 0, 1, 0, 0, 0, 0, 0
);

-- YPS Memento
INSERT INTO `award` (`yearmonth`, `award_id`, `level`, `sequence`, `section`, `award_group`, `award_type`, `award_name`, `recognition_code`, `description`,
					 `number_of_awards`, `award_weight`, `has_medal`, `has_pin`, `has_ribbon`, `has_memento`, `has_gift`, `has_certificate`, `cash_award`
) VALUES
(202112, 1002, 1, 2, 'CONTEST', 'ALL_PARTICIPANTS', 'entry', 'Best Indian Participant', 'YPS', '',
 			1, 0, 0, 0, 0, 1, 0, 0, 0
);

-- ICS Best Entrant under each section
INSERT INTO `award` (`yearmonth`, `award_id`, `level`, `sequence`, `section`, `award_group`, `award_type`, `award_name`, `recognition_code`, `description`,
					 `number_of_awards`, `award_weight`, `has_medal`, `has_pin`, `has_ribbon`, `has_memento`, `has_gift`, `has_certificate`, `cash_award`
) VALUES
(202112, 1006, 1, 6, 'CONTEST', 'ALL_PARTICIPANTS', 'entry', 'ICS Best Participant - Monochrome Section', 'ICS', '',
 			1, 0, 1, 0, 0, 0, 0, 0, 0
),
(202112, 1007, 1, 7, 'CONTEST', 'ALL_PARTICIPANTS', 'entry', 'ICS Best Participant - Color Section', 'ICS', '',
 			1, 0, 1, 0, 0, 0, 0, 0, 0
),
(202112, 1008, 1, 8, 'CONTEST', 'ALL_PARTICIPANTS', 'entry', 'ICS Best Participant - Nature Section', 'ICS', '',
 			1, 0, 1, 0, 0, 0, 0, 0, 0
),
(202112, 1009, 1, 9, 'CONTEST', 'ALL_PARTICIPANTS', 'entry', 'ICS Best Participant - Travel Section', 'ICS', '',
 			1, 0, 1, 0, 0, 0, 0, 0, 0
);

--
-- CLUB AWARDS
--
INSERT INTO `award` (`yearmonth`, `award_id`, `level`, `sequence`, `section`, `award_group`, `award_type`, `award_name`, `recognition_code`, `description`,
					 `number_of_awards`, `award_weight`, `has_medal`, `has_pin`, `has_ribbon`, `has_memento`, `has_gift`, `has_certificate`, `cash_award`
) VALUES
(202112, 2001, 1, 1, 'CONTEST', 'ALL_PARTICIPANTS', 'club', 'Best Participating Club', 'YPS', '',
 			1, 0, 0, 0, 0, 1, 0, 0, 0
);





-- /award ------------------------------------------------------------------------------------------


--
-- DATA for `team`
--
ALTER TABLE `team` ADD `group` varchar(50) NOT NULL DEFAULT "" AFTER `role_name`;

INSERT INTO `team` (`yearmonth`, `member_id`, `member_login_id`, `member_password`,
					`level`, `sequence`, `role`, `role_name`, `group`,
					`member_name`, `honors`,
					`phone`, `email`, `profile`, `avatar`,
					`permissions`, `is_reviewer`, `is_print_coordinator`, `allow_downloads`)
VALUES
--
-- Salon Committee
--
-- Satish
(202112, 1, 'satish', 'sh2101',
			1, 1, 'Chairman', 'Chairman', 'Salon Committee',
			'Mr. Satish Hanumantharao', 'MFIAP, MICS, ARPS, cMoL, GPA.PESGSPC, Hon.FICS, Hon.CPE, Hon. PESGSPC, Hon.YPS, Hon. ECPA,Hon. FLAS, Hon.FWPAI , Hon.FSAP, Hon.PSP, Patron LMG. Hon.GMTPAS',
			'+91-94486-87595', 'satishtusker@gmail.com', '', 'satish.png',
			'chairman', '0', '0', '1'),
-- Vikas
(202112, 2, 'vikas', '1.vikas',
			1, 2, 'Secretary', 'Secretary', 'Salon Committee',
			'Mr. Manju Vikas Sastry V', 'AFIP, Hon.APF, Hon.FGNG',
			'+91-95139-77257', 'sastry.vikas@gmail.com', '', 'vikas.png',
			'secretary', '0', '0', '1'),
-- Krishna Bhat
(202112, 3, 'bhat', 'krisb21',
			5, 1, 'Reviewer', 'Review Lead', 'Salon Committee',
			'Mr. Krishna Bhat', 'EFIAP/s, EFIP, EPSA, cMOL, GPA.PESGSPC, Hon.PESGSPC, Hon.CPE, Hon.APF, Grad.PSA Image Analysis',
			'+91-99453-36316', 'dinaabaa@gmail.com', '', 'bhat.png',
			'secretary', '0', '0', '1'),
-- Chandrashekar
(202112, 4, 'chandru', 'srini21',
			5, 2, 'Reviewer-2', 'Reviewer (MONOCHROME)', 'Salon Committee',
			'Mr. Chandrashekar Srinivasamurthy', 'AFIAP',
			'+91-98440-98288', 'scshekar9@gmail.com', '', 'chandru.png',
			'reviewer', '0', '0', '0'),
-- Neelima Reddy
(202112, 5, 'neelima', 'nreddy21',
			5, 3, 'Reviewer-3', 'Reviewer (COLOR)', 'Salon Committee',
			'Ms. Neelima Reddy', 'AFIAP, AFIP',
			'+91-99801-07987', 'neereddy1008@gmail.com', '', 'neelima.png',
			'reviewer', '0', '0', '0'),
-- Subramanya C K - IM-0571
(202112, 6, '', '',
			5, 4, 'Reviewer-4', 'Reviewer (NATURE)', 'Salon Committee',
			'Mr. Subramanya C K', 'EFIAP, QPSA, EFIP, cMoL',
			'+91-97400-22868', 'cks3976@gmail.com', '', 'subramanya.png',
			'reviewer', '0', '0', '0'),
-- Ananth Kamath
(202112, 7, 'ananth', 'kamath21',
			5, 5, 'Reviewer-1', 'Reviewer (TRAVEL)', 'Salon Committee',
			'Mr. Ananth Kamath', 'AFIP',
			'+91-98809-87247', 'infinitykamath@gmail.com', '', 'ananth_kamat.png',
			'reviewer', '0', '0', '0'),
-- Murali Santhanam
(202112, 9, 'murali', 'murali!@#',
			5, 9, 'Webmaster', 'Web Master', 'Salon Committee',
			'Mr. Murali Santhanam', '',
			'+91-94813-69204', 'murali.santhanam@yahoo.co.in', '', 'murali.png',
			'admin', '0', '0', '1'),
--
-- External Support
--
-- Madhu Kakade
(202112, 11, 'madhu', 'kakade21',
			9, 1, 'Treasurer', 'Treasurer', 'External Support',
			'Mr. Madhu Kakade', '',
			'+91-90039-30673', 'mskakade3@gmail.com', '', 'mskakade.png',
			'reviewer', '0', '0', '0'),
-- Ananth Kamath
(202112, 12, '', '',
			9, 2, 'Communication', 'Media and Communication', 'External Support',
			'Mr. Ananth Kamath', 'AFIP',
			'+91-98809-87247', 'infinitykamath@gmail.com', '', 'ananth_kamat.png',
			'reviewer', '0', '0', '0'),
(202112, 13, '', '',
			9, 3, 'Creatives', 'Creative Designs', 'External Support',
			'Mr. Chetan Rao Mane', 'AFIP, AFIAP',
			'+91-98800-34075', '', '', 'chetan_rao_mane.png',
			'', '0', '0', '0'),
-- Rajasimha S
(202112, 14, '', '',
			9, 4, 'Catalog', 'Catalog Design', 'External Support',
			'Mr. Rajasimha S', 'AFIP, AFIAP, CMoL',
			'+91-99723-99096', 'rajasimha.s@gmail.com', '', 'rajasimha.jpg',
			'', '0', '0', '0'),
-- Manju Mohan
(202112, 15, '', '',
			9, 5, 'Exhibition', 'Exhibition In-charge', 'External Support',
			'Mr. K S Manju Mohan', 'AFIAP, AFIP, PPSA, AAPS, cMoL, GPU-CR2, Hon.CPE',
			'+91-99800-60494', 'ksmm1967@gmail.com', '', 'manju_mohan.png',
			'', '0', '0', '0'),
-- Girish Ananthamurthy
(202112, 16, '', '',
			9, 6, 'Media', 'Press Publicity Support', 'External Support',
			'Mr. Girish Ananthamurthy', 'EFIAP, EFIP, Hon.PESGSPC, GPA PESGSPC',
			'+91-94490-06221', 'Jeechu1970@gmail.com', '', 'girish.png',
			'reviewer', '0', '0', '0');




--
-- DATA for user/jury
--
-- Updates for Anil Risal Singh
UPDATE user
   SET avatar = 'anil_risal_singh.png'
     , honors = 'MFIAP, ARPS, Hon.FIP, Hon.LCC, Hon.FSoF, Hon.FPAC, Hon.TPAS, Hon.FSAP, Hon.FICS,  Hon.PSGSPC, Hon.FPSNJ, GA.PSGSPC'
     , profile_file = 'anilrisal2021.htm'
 WHERE user_id = 60 AND login = 'anil';

 -- Ioannis Lykouris
 INSERT INTO `user` (`user_id`, `user_name`, `login`, `password`, `type`, `avatar`, `title`, `honors`, `email`, `profile`, `profile_file`, `status`) VALUES
 (93, 'MR. IOANNIS LYKOURIS', 'lyon', 'jury123', 'JURY', 'ioannis_lykouris.png', '', 'HON.EFIAP', 'ioannis18531@gmail.com', '', 'ioannis_lykouris.htm', 'ACTIVE');
-- David Poey Cher Tay
INSERT INTO `user` (`user_id`, `user_name`, `login`, `password`, `type`, `avatar`, `title`, `honors`, `email`, `profile`, `profile_file`, `status`) VALUES
(94, 'MR. DAVID POEY CHER TAY', 'tdavid', 'jury123', 'JURY', 'david_tay.png', '', 'MFIAP, HonEFIAP, FRPS, APSA, GMPSA', 'davidtaypc@yahoo.com', '', 'david_tay.htm', 'ACTIVE');
-- Mohammed Ali Salim
INSERT INTO `user` (`user_id`, `user_name`, `login`, `password`, `type`, `avatar`, `title`, `honors`, `email`, `profile`, `profile_file`, `status`) VALUES
(95, 'MR. MOHAMMED ALI SALIM', 'msalim', 'jury123', 'JURY', 'mohammed_ali_salim.png', '', 'APSA, GMPSA, GPSA, MFIAP, EFIAP/d2, ARPS', 'mas3218@gmail.com', '', 'mohammed_ali_salim.htm', 'ACTIVE');
-- Nils Erik Jerelmar
INSERT INTO `user` (`user_id`, `user_name`, `login`, `password`, `type`, `avatar`, `title`, `honors`, `email`, `profile`, `profile_file`, `status`) VALUES
(96, 'MR. NILS-ERIK JERLEMAR', 'nerik', 'jury123', 'JURY', 'nils_erik_jerlemar.png', '', 'GMPSA, EFIAP/d3', 'nejerlemar@gmail.com', '', 'nils_erik_jerlemar.htm', 'ACTIVE');
-- Gunther Riehle
INSERT INTO `user` (`user_id`, `user_name`, `login`, `password`, `type`, `avatar`, `title`, `honors`, `email`, `profile`, `profile_file`, `status`) VALUES
(97, 'MR. GUNTHER RIEHLE', 'griehle', 'jury123', 'JURY', 'gunther_riehle.png', '', 'GMPSA/p', 'gunther.riehle@suntory.com', '', 'gunther_riehle.htm', 'ACTIVE');
-- Ricardo Busi
INSERT INTO `user` (`user_id`, `user_name`, `login`, `password`, `type`, `avatar`, `title`, `honors`, `email`, `profile`, `profile_file`, `status`) VALUES
(98, 'MR. RICARDO BUSI', 'rbusi', 'jury123', 'JURY', 'ricardo_busi.png', '', 'MFIAP', 'busi.fiap@gmail.com', '', 'ricardo_busi.htm', 'ACTIVE');
-- Roy Killen
INSERT INTO `user` (`user_id`, `user_name`, `login`, `password`, `type`, `avatar`, `title`, `honors`, `email`, `profile`, `profile_file`, `status`) VALUES
(99, 'MR. ROY KILLEN', 'kroy', 'jury123', 'JURY', 'roy_killen.png', '', 'GMPSA/b, EFIAP', 'roykillen@mac.com', '', 'roy_killen.htm', 'ACTIVE');
-- Luis Franke
INSERT INTO `user` (`user_id`, `user_name`, `login`, `password`, `type`, `avatar`, `title`, `honors`, `email`, `profile`, `profile_file`, `status`) VALUES
(100, 'MR. LUIS ALBERTO FRANKE', 'aluis', 'jury123', 'JURY', 'luis_franke.png', '', 'MFIAP, GMPSA', 'luisfranke@live.com.ar', '', 'luis_franke.htm', 'ACTIVE');
-- Sanjoy Sengupta
INSERT INTO `user` (`user_id`, `user_name`, `login`, `password`, `type`, `avatar`, `title`, `honors`, `email`, `profile`, `profile_file`, `status`) VALUES
(101, 'MR. SANJOY SENGUPTA', 'ssen', 'jury123', 'JURY', 'sanjoy_sengupta.png', '', 'GMPSA/b, BPSA,   EFIAP/Silver, LRPS, GPU CR4, cMoL, SSS/B, Hon. PESGSPC, GMUPHK, MIUP, MFMPA, Hon. FSWAN, FICS, EUSPA, GPU HERMES, RISF10, MERIT-ER-ISF, BEPSS, FPSI, FFIP', 'ssg99_99@yahoo.com', '', 'sanjoy_sengupta.htm', 'ACTIVE');
-- Agatha Anne Bunanta
INSERT INTO `user` (`user_id`, `user_name`, `login`, `password`, `type`, `avatar`, `title`, `honors`, `email`, `profile`, `profile_file`, `status`) VALUES
(102, 'MS. AGATHA ANNE BUNANTA', 'aanne', 'jury123', 'JURY', 'agatha_bunanta.png', '', 'FPSA, GMPSA, GPSA, EFIAP/p, ARPS, GPU-Cr4, GPU-VIP3', 'agathabunanta@gmail.com', '', 'agatha_bunanta.htm', 'ACTIVE');
-- Madhu Sarkar
INSERT INTO `user` (`user_id`, `user_name`, `login`, `password`, `type`, `avatar`, `title`, `honors`, `email`, `profile`, `profile_file`, `status`) VALUES
(103, 'MR. MADHU SARKAR', 'smadhu', 'jury123', 'JURY', 'madhu_sarkar.png', '', 'MFIAP, FAPU, Hon. FBPS, Hon. FPSG, Hon. TAMA, Hon. PESGSPC, Hon. EUSPA, Hon. F.ICS, M.USPA', 'nap03in@yahoo.co.in', '', 'madhu_sarkar.htm', 'ACTIVE');




-- /user --------------------------------------------------------------

--
-- DATA for assignment`
--
INSERT INTO `assignment` (`yearmonth`, `section`, `user_id`, `jurynumber`) VALUES
-- COLOR Section
-- David Tay
(202112, 'COLOR', 94, 1),
-- Mohammed Ali Salim
(202112, 'COLOR', 95, 2),
-- Nils-Erik Jerelmar
(202112, 'COLOR', 96, 3),

-- MONOCHROME Section
-- Anil Risal Singh
(202112, 'MONOCHROME', 60, 1),
-- Madhu Sarkar
(202112, 'MONOCHROME', 103, 2),
-- Ioannis Lykouris
(202112, 'MONOCHROME', 93, 3),

-- NATURE Section
-- Gunther Riehle
(202112, 'NATURE', 97, 1),
-- Ricardo Busi
(202112, 'NATURE', 98, 2),
-- Roy Killen
(202112, 'NATURE', 99, 3),

-- TRAVEL Section
-- Luis Alberto Franke
(202112, 'TRAVEL', 100, 1),
-- Sanjoy Sengupta
(202112, 'TRAVEL', 101, 2),
-- Agatha Anne Bunanta
(202112, 'TRAVEL', 102, 3);


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
('202112', 'COLOR', 'ALL_PARTICIPANTS'),
('202112', 'MONOCHROME', 'ALL_PARTICIPANTS'),
('202112', 'TRAVEL', 'ALL_PARTICIPANTS'),
('202112', 'NATURE', 'ALL_PARTICIPANTS');

--
-- After Judging
--

--
-- Entrant Category descriptions to be made unique
--
UPDATE entrant_category SET entrant_category_name = "ADULT GENERAL" WHERE yearmonth = 202112 AND entrant_category = "ADULT";
UPDATE entrant_category SET entrant_category_name = "ADULT YPS" WHERE yearmonth = 202112 AND entrant_category = "ADULT_YPS";
UPDATE entrant_category SET entrant_category_name = "YOUTH GENERAL" WHERE yearmonth = 202112 AND entrant_category = "YOUTH";
UPDATE entrant_category SET entrant_category_name = "YOUTH YPS" WHERE yearmonth = 202112 AND entrant_category = "YOUTH_YPS";
--
-- Add support for Virtual Exhibition and Guest Book
--

INSERT INTO `exhibition` (`yearmonth`, `is_virtual`, `virtual_tour_ready`) VALUES ("202112", "1", "1");


UPDATE exhibition
SET dignitory_roles = "Chief Guest|Guest of Honor",
    dignitory_names = "Shri. K Srinivas, IAS|Shri. Anup Sah",
	dignitory_positions = "Commissioner, Department of Youth Empowerment & Sports, Govt. of Karnataka|Mountaineer, Photographer of Himalayan Life, Nainital, Recipient of Padma Shri award",
	dignitory_avatars = "ksrinivas.png|anupsah.jpg"
WHERE yearmonth = 202112;



/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;


--
-- Setup Award Weights for the Salon, in line with FIP Guidelines
--
-- FIP Stipulated weights
-- Acceptance : 1
-- YPS - HM : 2
-- FIAP, PSA, MoL, ICS, GPU, FIP - HM : 3
-- YPS - Bronze : 3
-- FIAP, PSA, MoL, ICS, GPU, FIP - Bronze : 4
-- YPS - Silver : 4
-- FIAP, PSA, MoL, ICS, GPU, FIP - Silver : 5
-- YPS - Gold : 5
-- FIAP, PSA, MoL, ICS, GPU, FIP - Gold : 6
--
-- FIAP GOLD
UPDATE award SET award_weight = '6' WHERE yearmonth = '202112' AND award_id IN (1, 2, 3, 4);
-- PSA GOLD
UPDATE award SET award_weight = '6' WHERE yearmonth = '202112' AND award_id IN (6, 7, 8, 9);
-- ICS GOLD
UPDATE award SET award_weight = '6' WHERE yearmonth = '202112' AND award_id IN (11, 12, 13, 14);
-- MoL GOLD
UPDATE award SET award_weight = '6' WHERE yearmonth = '202112' AND award_id IN (16, 17, 18, 19);
-- GPU GOLD
UPDATE award SET award_weight = '6' WHERE yearmonth = '202112' AND award_id IN (21, 22, 23, 24);
-- FIP MEDAL
UPDATE award SET award_weight = '6' WHERE yearmonth = '202112' AND award_id IN (26, 27, 28, 29);
-- YPS GOLD
UPDATE award SET award_weight = '5' WHERE yearmonth = '202112' AND award_id IN (31, 32, 33, 34);

-- PSA SILVER
UPDATE award SET award_weight = '5' WHERE yearmonth = '202112' AND award_id IN (106, 107, 108, 109);
-- ICS Silver
UPDATE award SET award_weight = '5' WHERE yearmonth = '202112' AND award_id IN (111, 112, 113, 114);
-- MoL Silver
UPDATE award SET award_weight = '5' WHERE yearmonth = '202112' AND award_id IN (116, 117, 118, 119);
-- YPS Silver
UPDATE award SET award_weight = '4' WHERE yearmonth = '202112' AND award_id IN (131, 132, 133, 134);


-- PSA BRONZE
UPDATE award SET award_weight = '4' WHERE yearmonth = '202112' AND award_id IN (206, 207, 208, 209);
-- ICS Bronze
UPDATE award SET award_weight = '4' WHERE yearmonth = '202112' AND award_id IN (211, 212, 213, 214);
-- MoL Bronze
UPDATE award SET award_weight = '4' WHERE yearmonth = '202112' AND award_id IN (216, 217, 218, 219);
-- YPS Bronze
UPDATE award SET award_weight = '3' WHERE yearmonth = '202112' AND award_id IN (231, 232, 233, 234);

-- FIAP Ribbon
UPDATE award SET award_weight = '3' WHERE yearmonth = '202112' AND award_id IN (501, 502, 503, 504);
-- MoL Gold Ribbon
UPDATE award SET award_weight = '3' WHERE yearmonth = '202112' AND award_id IN (506, 507, 508, 509);
-- MoL Silver Ribbon
UPDATE award SET award_weight = '3' WHERE yearmonth = '202112' AND award_id IN (511, 512, 513, 514);
-- MoL Bronze Ribbon
UPDATE award SET award_weight = '3' WHERE yearmonth = '202112' AND award_id IN (516, 517, 518, 519);
-- GPU Ribbon
UPDATE award SET award_weight = '3' WHERE yearmonth = '202112' AND award_id IN (521, 522, 523, 524);
-- FIP Ribbon
UPDATE award SET award_weight = '3' WHERE yearmonth = '202112' AND award_id IN (526, 527, 528, 529);
-- YPS Ribbon
UPDATE award SET award_weight = '2' WHERE yearmonth = '202112' AND award_id IN (531, 532, 533, 534);

-- Acceptance
UPDATE award SET award_weight = '1' WHERE yearmonth = '202112' AND award_id IN (901, 902, 903, 904);

-- Individual Awards - award_weight is 0
UPDATE award SET award_weight = '0' WHERE yearmonth = '202112' AND award_id IN (1001, 1002, 1006, 1007, 1008, 1009);

-- Club Awards - award_weight 0
UPDATE award SET award_weight = '0' WHERE yearmonth = '202112' AND award_id IN (2001);

--
-- Set passwords for Jury Members
--
UPDATE user SET password = "is21060ars" WHERE user_id = '60' AND login = 'anil';
UPDATE user SET password = "is21093lyk" WHERE user_id = '93' AND login = 'lyon';
UPDATE user SET password = "is21094dct" WHERE user_id = '94' AND login = 'tdavid';
UPDATE user SET password = "is21095mas" WHERE user_id = '95' AND login = 'msalim';
UPDATE user SET password = "is21096njr" WHERE user_id = '96' AND login = 'nerik';
UPDATE user SET password = "is21097gnr" WHERE user_id = '97' AND login = 'griehle';
UPDATE user SET password = "is21098rdb" WHERE user_id = '98' AND login = 'rbusi';
UPDATE user SET password = "is21099drk" WHERE user_id = '99' AND login = 'kroy';
UPDATE user SET password = "is21100laf" WHERE user_id = '100' AND login = 'aluis';
UPDATE user SET password = "is21101ssg" WHERE user_id = '101' AND login = 'ssen';
UPDATE user SET password = "is21102abn" WHERE user_id = '102' AND login = 'aanne';
UPDATE user SET password = "is21103msr" WHERE user_id = '103' AND login = 'smadhu';

--
-- Add PSA Ribbons to award list
--
--PSA Ribbon  - 8
INSERT INTO `award` (`yearmonth`, `award_id`, `level`, `sequence`, `section`, `award_group`, `award_type`, `award_name`, `recognition_code`, `description`,
					 `number_of_awards`, `award_weight`, `has_medal`, `has_pin`, `has_ribbon`, `has_memento`, `has_gift`, `has_certificate`, `cash_award`
) VALUES
(202112, 536, 9, 2, 'MONOCHROME', 'ALL_PARTICIPANTS', 'pic', 'PSA Ribbon', 'PSA', '',
 			2, 3, 0, 0, 1, 0, 0, 1, 0
),
(202112, 537, 9, 2, 'COLOR', 'ALL_PARTICIPANTS', 'pic', 'PSA Ribbon', 'PSA', '',
 			2, 3, 0, 0, 1, 0, 0, 1, 0
),
(202112, 538, 9, 2, 'NATURE', 'ALL_PARTICIPANTS', 'pic', 'PSA Ribbon', 'PSA', '',
 			2, 3, 0, 0, 1, 0, 0, 1, 0
),
(202112, 539, 9, 2, 'TRAVEL', 'ALL_PARTICIPANTS', 'pic', 'PSA Ribbon', 'PSA', '',
 			2, 3, 0, 0, 1, 0, 0, 1, 0
);

--
-- Update award sequence for other ribbons and also correct the ribbons flag
--
-- FIAP Ribbon
UPDATE award SET has_medal = '0', has_ribbon = '1' WHERE yearmonth = '202112' AND award_id IN (501, 502, 503, 504);
-- MoL Gold Ribbon
UPDATE award SET has_medal = '0', has_ribbon = '1', sequence = 3 WHERE yearmonth = '202112' AND award_id IN (506, 507, 508, 509);
-- MoL Silver Ribbon
UPDATE award SET has_medal = '0', has_ribbon = '1', sequence = 4 WHERE yearmonth = '202112' AND award_id IN (511, 512, 513, 514);
-- MoL Bronze Ribbon
UPDATE award SET has_medal = '0', has_ribbon = '1', sequence = 5 WHERE yearmonth = '202112' AND award_id IN (516, 517, 518, 519);
-- GPU Ribbon
UPDATE award SET has_medal = '0', has_ribbon = '1', sequence = 6 WHERE yearmonth = '202112' AND award_id IN (521, 522, 523, 524);
-- FIP Ribbon
UPDATE award SET has_medal = '0', has_ribbon = '1', sequence = 7 WHERE yearmonth = '202112' AND award_id IN (526, 527, 528, 529);
-- YPS Ribbon
UPDATE award SET has_medal = '0', has_ribbon = '1', sequence = 8 WHERE yearmonth = '202112' AND award_id IN (531, 532, 533, 534);
