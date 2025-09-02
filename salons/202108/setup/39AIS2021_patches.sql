-- All Data Patches applied for 39th All India Salon 2021
--

--
-- Issue 001 - 24/04 - Sponsorship Payments not getting updated. Issue fixed. Manually insert payments.
--
INSERT INTO `payment` (`yearmonth`, `account`, `link_id`, `datetime`, `purpose`, `amount`, `currency`, `gateway`, `request_id`, `payment_ref`, `status`, `modified_date`) VALUES
(202108, 'SPN', 2, '202104241302', 'SPONSORSHIP', 18500.00, 'INR', 'Instamojo', '-MANUAL-', 'MOJO1424705D66845090', 'PAID', '2021-04-24 13:02:00'),
(202108, 'SPN', 16, '202104241321', 'SPONSORSHIP', 10000.00, 'INR', 'Instamojo', '-MANUAL-', 'MOJO1424T05N66846052', 'PAID', '2021-04-24 13:21:00'),
(202108, 'SPN', 4, '202104241343', 'SPONSORSHIP', 3000.00, 'INR', 'Instamojo', '-MANUAL-', 'MOJO1424X05Q66847084', 'PAID', '2021-04-24 13:43:00');

-- Prasanna Venkatesh (Sponsor ID 16)
UPDATE `sponsorship`
   SET `payment_received` = `total_sponsorship_amount`
 WHERE `yearmonth` = '202108'
   AND `sponsor_id` = '16';

-- Chandrashekar Srinivasamurthy (Sponsor ID 4)
UPDATE `sponsorship`
  SET `payment_received` = `total_sponsorship_amount`
WHERE `yearmonth` = '202108'
  AND `sponsor_id` = '4';

-- Anitha Mysore (Sponsor ID 2)
UPDATE `sponsorship`
SET `payment_received` = `total_sponsorship_amount`
WHERE `yearmonth` = '202108'
AND `sponsor_id` = '2';


--
-- Issue 002 - 24/04 - Rajasimha had sponsored for 2 Honorable Mentions to be reverted so that Rajasimha can opt for a Bronze award
-- Sponsor ID 62; Award IDs 51 & 52
--
-- INSERT INTO `sponsorship` (`yearmonth`, `sponsorship_type`, `link_id`, `sponsorship_no`, `number_of_units`, `total_sponsorship_amount`, `award_name_suffix`, `sponsor_id`, `sponsorship_sparkler`, `sponsorship_advt`, `sponsorship_link`, `payment_received`, `modified_date`) VALUES
-- (202108, 'AWARD', 51, 1, 1, 1000.00, '', 62, '', '', '', 1000.00, '2021-04-24 11:42:42'),
-- (202108, 'AWARD', 52, 1, 1, 1000.00, '', 62, '', '', '', 1000.00, '2021-04-24 11:42:42');
--
DELETE FROM `sponsorship`
 WHERE `yearmonth` = '202108'
   AND `sponsor_id` = '62';

DELETE FROM `payment`
WHERE `yearmonth` = '202108'
  AND `account` = 'SPN'
  AND `link_id` = '62'
  AND `datetime` = '202104241712';

--
-- Issue 003 - Two Sponsor IDs created for Lakshmi Narasimha Murthy (61 & 73)
-- Two COLOR YPS Ribbon sponsorships also created, one each against 61 & 73
-- Remove Sponsor Record 73 & sponsorship against 73
DELETE FROM `sponsor` WHERE sponsor_id = '73';
DELETE FROM `sponsorship` WHERE yearmonth = '202108' AND sponsor_id = '73';
-- Make email unique
-- Following sponsor records have no email
-- sponsor_id	sponsor_name	sponsor_logo	sponsor_email	sponsor_phone	sponsor_website	modified_date
-- 21	Sri. E Hanumantha Rao Memorial					2018-09-24 04:06:43
-- 22	Mr. M Anantha Murthy					2018-09-24 04:06:43
-- 24	Mr. D N Suresh kumar					2018-09-24 04:06:43
-- 25	Mr. Manjunath B L					2018-09-24 04:06:43
-- 26	Mr. Mahesh					2018-09-24 04:06:43
-- 27	Mr. Kalgundi Naveen					2018-09-24 04:06:43
-- 28	Mr. S R Jayaprakash					2018-09-24 04:06:43
-- 29	Mr. Madhusudhan					2018-09-24 04:06:43
-- 30	Indigo Multimedia					2018-09-24 04:06:43
-- 31	Sri. Raja Gopal Memorial					2018-09-24 04:06:43
-- 32	Smt. Savitramma Memorial					2018-09-24 04:06:43
-- 33	Sri. Ramesh Bhalse Memorial					2018-09-24 04:06:43
-- 34	Sri. C S Anantha Rao Memorial					2018-09-24 04:06:43
-- 35	Sri. D Ramachandra Rao Memorial					2018-09-24 04:06:43
-- 36	Smt. R Nagarathnamma Memorial					2018-09-24 04:06:43
-- 37	Smt. S D Vasantha Kumari Memorial					2018-09-24 04:06:43
-- 38	Sri. K G Hiriyannaiah Memorial					2018-09-24 04:06:43
--
-- Following Sponsorship records need to be fixed before making sponsor_email UNIQUE
-- yearmonth	sponsorship_type	link_id	sponsorship_no	number_of_units	total_sponsorship_amount	award_name_suffix	sponsor_id	sponsorship_sparkler	sponsorship_advt	sponsorship_link	payment_received	modified_date
-- 201610	AWARD	3	94	1	1500.00	Sri. Raja Gopal Memorial	31				1500.00	2018-10-19 01:57:40
-- 201610	AWARD	4	95	1	1000.00	Smt. Savitramma Memorial	32				1000.00	2018-10-19 01:57:40
-- 201610	AWARD	22	100	1	2000.00	Sri. Ramesh Bhalse Memorial	33				2000.00	2018-10-19 01:57:40
-- 201610	AWARD	23	101	1	1500.00	Sri. C S Anantha Rao Memorial	34				1500.00	2018-10-19 01:57:40
-- 201610	AWARD	24	102	1	1000.00	Sponsored by Sri. D N Suresh Kumar	24				1000.00	2018-10-19 01:57:40
-- 201610	AWARD	25	103	1	1000.00	Sponsored by Sri. Manjunath B L	25				1000.00	2018-10-19 01:57:40
-- 201610	AWARD	26	104	1	1000.00	Sri. Ramesh Bhalse Memorial	33				1000.00	2018-10-19 01:57:40
-- 201610	AWARD	31	106	1	3000.00	Sri. E Hanumantha Rao (AFIAP, Hon.YPS) Memorial	21				3000.00	2018-10-19 01:57:40
-- 201610	AWARD	35	108	1	1000.00	Sponsored by Sri. M Anantha Murthy	22				1000.00	2018-10-19 01:57:40
-- 201610	AWARD	36	109	1	1000.00	Sri. D Ramachandra Rao Memorial	35				1000.00	2018-10-19 01:57:40
-- 201610	AWARD	42	114	1	2000.00	Sponsored by Sri. Mahesh	26				2000.00	2018-10-19 01:57:40
-- 201610	AWARD	43	115	1	1500.00	Sri. Ramesh Bhalse Memorial	33				1500.00	2018-10-19 01:57:40
-- 201610	AWARD	44	116	1	1000.00	Smt. R Nagarathnamma Memorial	36				1000.00	2018-10-19 01:57:40
-- 201610	AWARD	46	118	1	1000.00	Sponsored by Sri. D N Suresh Kumar	24				1000.00	2018-10-19 01:57:40
-- 201610	AWARD	51	119	1	3000.00	Smt. S D Vasantha Kumari Memorial	37				3000.00	2018-10-19 01:57:40
-- 201610	AWARD	53	121	1	1500.00	Sponsored by Sri. M Anantha Murthy	22				1500.00	2018-10-19 01:57:40
-- 201610	AWARD	54	122	1	1000.00	Best Environmental Story	27				1000.00	2018-10-19 01:57:40
-- 201610	AWARD	55	123	1	1000.00	Best Human Emotion	28				1000.00	2018-10-19 01:57:40
-- 201610	AWARD	56	124	1	1000.00	Best Sports Event	36				1000.00	2018-10-19 01:57:40
-- 201610	AWARD	57	125	1	1000.00	Best Story telling	38				1000.00	2018-10-19 01:57:40
-- 201610	AWARD	92	127	1	2000.00	Best YPS Member Entrant (Overall points)	29				2000.00	2018-10-19 01:57:40
-- 201610	AWARD	93	128	1	2000.00	Best Youth Award (For participants below 25 Years ...	30				2000.00	2018-10-19 01:57:40
-- 201610	AWARD	95	130	1	3500.00	Best Nature Entrant	21				3500.00	2018-10-19 01:57:40
--
UPDATE `sponsor` SET sponsor_email = "nosuch1@email.com" WHERE sponsor_id = '21';
UPDATE `sponsor` SET sponsor_email = "nosuch2@email.com" WHERE sponsor_id = '22';
UPDATE `sponsor` SET sponsor_email = "nosuch3@email.com" WHERE sponsor_id = '24';
UPDATE `sponsor` SET sponsor_email = "nosuch4@email.com" WHERE sponsor_id = '25';
UPDATE `sponsor` SET sponsor_email = "nosuch5@email.com" WHERE sponsor_id = '26';
UPDATE `sponsor` SET sponsor_email = "nosuch6@email.com" WHERE sponsor_id = '27';
UPDATE `sponsor` SET sponsor_email = "nosuch7@email.com" WHERE sponsor_id = '28';
UPDATE `sponsor` SET sponsor_email = "nosuch8@email.com" WHERE sponsor_id = '29';
UPDATE `sponsor` SET sponsor_email = "nosuch9@email.com" WHERE sponsor_id = '30';
UPDATE `sponsor` SET sponsor_email = "nosuch10@email.com" WHERE sponsor_id = '31';
UPDATE `sponsor` SET sponsor_email = "nosuch11@email.com" WHERE sponsor_id = '32';
UPDATE `sponsor` SET sponsor_email = "nosuch12@email.com" WHERE sponsor_id = '33';
UPDATE `sponsor` SET sponsor_email = "nosuch13@email.com" WHERE sponsor_id = '34';
UPDATE `sponsor` SET sponsor_email = "nosuch14@email.com" WHERE sponsor_id = '35';
UPDATE `sponsor` SET sponsor_email = "nosuch15@email.com" WHERE sponsor_id = '36';
UPDATE `sponsor` SET sponsor_email = "nosuch16@email.com" WHERE sponsor_id = '37';
UPDATE `sponsor` SET sponsor_email = "nosuch17@email.com" WHERE sponsor_id = '38';
--
-- Some more non-unique emails
-- mukta@accordconsultants.co.in - ID 11 (Vivek R Sinha), ID 12 (Arati Sinha)
-- Sponsor ID 12 has no sponsorship records. Update the email
-- scshekar9@gmail.com - Sponsor ID 4 & 48
-- No sponsorship records agains ID 48. Can be deleted.
--
UPDATE `sponsor` SET `sponsor_email` = "mukta1@accordconsultants.co.in" WHERE sponsor_id = '12';
DELETE FROM `sponsor` WHERE sponsor_id = '48';

-- Make sponsor_email UNIQUE
ALTER TABLE `sponsor` ADD UNIQUE( `sponsor_email`);

--
-- 30/4/2021
-- Provide permission to vikas
-- Provide treasurer permission to Madhu Kakade
UPDATE team SET permissions = "manager" WHERE yearmonth = "202108" AND member_id = "5" AND member_login_id = "vikas";
UPDATE team SET permissions = "reviewer,treasurer" WHERE yearmonth = "202108" AND member_id = "3" AND member_login_id = "madhu";

--
-- 2/5/21
-- 004 - LM-280 unable to access
-- Previous Member ID IM-0275 - Profile exists with ID 1440
-- No auto update possible
-- Update member id in profile with LM-280
UPDATE `profile` SET yps_login_id = 'LM-280' WHERE profile_id = '1440';

--
-- Updated the following records using Admin Panel Tools->Member Reconcile option
--
-- MAMATHA S (600)IM-0033 changed to LM-262 mammusrivathsa@gmail.com
-- RATHISH M V (916)IM-0126 changed to LM-285 2rathishmv@gmail.com
-- HAYATH MOHAMMED (633)IM-0213 changed to LM-287 hayath.m@gmail.com
-- KIRAN KUMAR R (606)IM-0071 changed to LM-292 kiran8229@yahoo.com
-- Lakshmi Narasimha Murthy (1156)IM-0402 changed to LM-294 murthycop55@gmail.com
-- AMITH BHAVIKATTI (42)IM-0383 changed to LM-295 bn.amith@rediffmail.com
-- SRINATH NARAYAN (1251)IM-0344 changed to LM-296 srinath.narayan@me.com
-- Neelima Reddy (1267)IM-0442 changed to LM-297 neereddy1008@gmail.com
-- RAGHAVENDRA JOSHI (750)IM-0311 changed to LM-298 eciragh@gmail.com

--
-- Update Reviewers
--
-- Nandan Hegde - Remove reviewer permission from "Salon Support" role
UPDATE team
   SET permissions = ""
 WHERE yearmonth = 202108
   AND member_id = 7;
--
-- Madhu Kakade is no more a reviewer
UPDATE team
   SET permissions = "treasurer"
 WHERE yearmonth = 202108
   AND member_id = 3;
--
-- Update Login for Anitha Mysore
UPDATE team
   SET member_login_id = "anitha"
     , member_password = "am2108"
 WHERE yearmonth = 202108
   AND member_id = 8;
--
-- Update Login for Chandrashekar
UPDATE team
  SET member_login_id = "chandru"
    , member_password = "cs2109"
WHERE yearmonth = 202108
  AND member_id = 9;
--
-- Update Login for Girish
UPDATE team
 SET member_login_id = "girish"
   , member_password = "ga2110"
WHERE yearmonth = 202108
 AND member_id = 10;
 --
 -- Update Login for Nandan Hegde
 UPDATE team
    SET member_login_id = "nandan"
      , member_password = "nh2111"
  WHERE yearmonth = 202108
    AND member_id = 11;

--
-- Implement facility to track of reviewed profiles on review_image.php
--
ALTER TABLE `team` ADD `reviewed` text AFTER `allow_downloads`;

--
-- UPDATE FIP recognition
--
UPDATE `recognition` SET recognition_id = '2021/FIP/127/2021' WHERE yearmonth = '202108' AND short_code = 'FIP';

--
-- Decided to offer a special award in memory of Neginal, sponsored by Dr. Shanbagh for Rs.5000
--
INSERT INTO `award` (`yearmonth`, `award_id`, `level`, `sequence`, `section`, `award_group`, `award_type`, `award_name`, `description`,
					 `number_of_awards`, `has_medal`, `has_pin`, `has_ribbon`, `has_memento`, `has_gift`, `has_certificate`, `cash_award`,
					 `sponsored_awards`, `sponsorship_per_award`, `partial_sponsorship_permitted`, `sponsorship_last_date`
) VALUES
(202108, 93, 9, 1, 'CONTEST', 'ALL_AGES', 'entry', 'Best of Nature in Environment', '',
 			1, 0, 0, 0, 1, 0, 0, 5000,
 			1, 5000, 0, '2021-06-30'
);

--
-- Some awards have last_sponsorship_date as 2020-07-31. They should be updated to "2121-06-30"
--
UPDATE `award` SET sponsorship_last_date = "2021-06-30"
WHERE yearmonth = 202108 AND sponsorship_last_date = "2020-07-31";

--
-- Add certificate to Award 93
--
UPDATE `award`
   SET has_certificate = '1'
 WHERE yearmonth = 202108
   AND award_id = 93;

--
-- Fix unnecessary award_suffix "Sponsored by ..." in the award_table
--
-- SELECT yearmonth, sponsorship_type, link_id, award_name_suffix, sponsor_name
-- FROM `sponsorship`, sponsor
-- WHERE yearmonth = 202108 AND award_name_suffix LIKE "sponsored%" AND sponsor.sponsor_id = sponsorship.sponsor_id
UPDATE sponsorship SET award_name_suffix = '' WHERE yearmonth = '202108' AND sponsorship_type = 'AWARD' AND link_id = '4'; -- 	Sponsored by Anitha Mysore
UPDATE sponsorship SET award_name_suffix = '' WHERE yearmonth = '202108' AND sponsorship_type = 'AWARD' AND link_id = '14'; -- 	Sponsored by Anitha Mysore
UPDATE sponsorship SET award_name_suffix = '' WHERE yearmonth = '202108' AND sponsorship_type = 'AWARD' AND link_id = '24'; -- 	Sponsored by Anitha Mysore
UPDATE sponsorship SET award_name_suffix = '' WHERE yearmonth = '202108' AND sponsorship_type = 'AWARD' AND link_id = '34'; -- 	Sponsored by Anitha Mysore
UPDATE sponsorship SET award_name_suffix = '' WHERE yearmonth = '202108' AND sponsorship_type = 'AWARD' AND link_id = '44'; -- 	Sponsored by Anitha Mysore
UPDATE sponsorship SET award_name_suffix = '' WHERE yearmonth = '202108' AND sponsorship_type = 'AWARD' AND link_id = '54'; -- 	Sponsored by Anitha Mysore
UPDATE sponsorship SET award_name_suffix = '' WHERE yearmonth = '202108' AND sponsorship_type = 'AWARD' AND link_id = '64'; -- 	Sponsored by Anitha Mysore

UPDATE sponsorship SET award_name_suffix = '' WHERE yearmonth = '202108' AND sponsorship_type = 'AWARD' AND link_id = '41'; -- 	Sponsored by K S Manju Mohan


--
-- Combine Anita Mysore's multiple awards into one award for ribbons
--
DELETE FROM sponsorship WHERE yearmonth = '202108' AND sponsorship_type = 'AWARD' AND link_id IN (54, 64) AND sponsorship_no IN (2,3,4,5) AND sponsor_id = 2;

UPDATE sponsorship
   SET number_of_units = '5'
 WHERE yearmonth = '202108' AND sponsorship_type = 'AWARD' AND link_id IN (54, 64) AND sponsorship_no = '1' AND sponsor_id = 2;

UPDATE sponsorship
   SET total_sponsorship_amount = '5000', payment_received = '5000'
 WHERE yearmonth = '202108' AND sponsorship_type = 'AWARD' AND link_id IN (54, 64) AND sponsorship_no = '1' AND sponsor_id = 2;


--
-- Move all emails to salon@ypsbengaluru.in
--
-- Update cc_to column in email_format
--
UPDATE `email_template`
   SET cc_to = 'salon@ypsbengaluru.in'
 WHERE cc_to = 'salon@ypsbengaluru.com';

--
-- ISSUE 005 - New Club with empty name created every time a YPS user updates the profile
--
-- SELECT profile_id, profile_name, yps_login_id, profile.club_id, club_name
-- FROM `profile` LEFT JOIN `club` ON club.club_id = profile.club_id
-- WHERE yps_login_id != "" AND profile.club_id != 0
-- ORDER BY profile.club_id
--
-- profile_id   profile_name	    yps_login_id	club_id   	club_name
-- 762          PRASHANT VAIDYA	    IM-0613	        3           FOTORBIT INDIA
-- 942	        SHAJIN NAMBIAR	    IM-0651         35	        TGIS
-- 933	        TUSHAR SINGH	    IM-0375	        53	        PHOTOWALK BENGALURU
-- 509	        SUHAS MUTHMURDU	    IM-0366	        112	        SAGARA PHOTOGRAPHIC SOCIETY
-- 508          SUGUMAR CHETTIAR	IM-0681	        227
-- 498	        SUBASH BAHADUR	    IM-0536	        228
-- 1436	        SANDEEP DATTARAJU	IM-0623	        229
-- 698	        SUDHEENDRA K P	    LM-232	        233
-- 1282	        RANGANATH C	        IM-0498	        234
-- 683	        MURALI SANTHANAM	LM-193	        235
-- 598	        KRISHNA BHAT	    LM-261	        236
-- 980	        ANANTH KAMAT	    IM-0388	        238
-- 74	        ASHOK VISWANATHAN	IM-0674	        239
-- 675	        RAJU J PURANIK	    LM-142	        240
-- 1440	        LOKESH K C	        LM-280	        241
-- 771	        LOKANATH M	        LM-281	        243
-- 1763	        VINYASA UBARADKA	IM-0581	        246
--
-- Data Fix
--
UPDATE `profile` SET club_id = '0' WHERE yps_login_id != "" AND club_id != "0";

DELETE FROM `club`
 WHERE club_name = ""
   AND club_type = ""
   AND last_updated_by  IN (508, 498, 1436, 698, 1282, 683, 598, 980, 74, 675, 1440, 771, 1763);

-- Madhusudan Varadaraj
UPDATE `profile` SET club_id = '0' WHERE profile_id = 2066 AND club_id = 246;
DELETE FROM `club` WHERE club_id = 246 AND last_updated_by = 2066;


--
-- Issue 006 - 10/054 - Sponsorship Payments received through IMPS from KMB Prasad
-- Sponsor ID - Two IDs 50, 63 - Delete 50 - Update sponsor id to 63 where it is 50
-- Award ID - 3
--
UPDATE sponsorship SET sponsor_id = 63 WHERE sponsor_id = 50;
-- No payment record for 201812 / sponsor_id = 52
-- DELETE Old Sponsorship record
DELETE from sponsor WHERE sponsor_id = 50;

-- Current Year IMPS Payment
INSERT INTO `payment` (`yearmonth`, `account`, `link_id`, `datetime`, `purpose`, `amount`, `currency`, `gateway`, `request_id`, `payment_ref`, `status`, `modified_date`) VALUES
(202108, 'SPN', 63, '202105101300', 'SPONSORSHIP', 6000.00, 'INR', 'IMPS', 'IMPS112807249921/9481904226', '-- IMPS to SBI ACCT--', 'PAID', '2021-05-10 13:00:00');

-- Prasanna Venkatesh (Sponsor ID 16)
UPDATE `sponsorship`
   SET `payment_received` = `total_sponsorship_amount`
 WHERE `yearmonth` = '202108'
   AND `sponsor_id` = '63';
