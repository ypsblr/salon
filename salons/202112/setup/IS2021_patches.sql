--
-- Database Patches to International Salon 2021 database linked to specific issues/decisions
--

--
-- Issue 002 dated 15/09 - Change the last date for submission to 2021-10-24
--
-- 1. contest table
UPDATE `contest`
   SET registration_last_date = '2021-10-24'
 WHERE yearmonth = '202112'
   AND registration_last_date = '2021-10-27';

-- 2. section table
UPDATE `section`
   SET submission_last_date = '2021-10-24'
 WHERE yearmonth = '202112'
   AND submission_last_date  = '2021-10-27';

--
-- Issue 003 - Distinctions of Reviewers to be fixed
--
-- Neelima Reddy - Member ID 5
UPDATE `team`
   SET `honors` = 'EFIP, AFIAP'
 WHERE `yearmonth` = '202112'
   AND `member_id` = '5';
-- C K Subramanya - Member ID 6
UPDATE `team`
  SET `honors` = 'AFIP'
WHERE `yearmonth` = '202112'
  AND `member_id` = '6';
-- Ananth Kamat - Member ID 7, 12
UPDATE `team`
SET `honors` = 'EFIP, cMoL'
  , `member_name` = 'Ananth Kamat'
WHERE `yearmonth` = '202112'
AND `member_id` IN ('7', '12');

--
-- Issue 004 - Disable Profile with name containing special characters - Profile ID 2871
--
UPDATE `profile`
   SET profile_disabled = '1'
 WHERE profile_id = '2871'
   AND email = 'minnkokolay5224@gmail.com';

--
-- Add Section Review Permissions
--
-- Krishna Bhat - All sections
UPDATE team set sections = "COLOR|MONOCHROME|NATURE|TRAVEL" WHERE yearmonth = 202112 AND member_id = '3' AND member_login_id = 'bhat';
-- Chandrashekar - MONOCHROME
UPDATE team set sections = "MONOCHROME" WHERE yearmonth = 202112 AND member_id = '4' AND member_login_id = 'chandru';
-- Neelima - COLOR
UPDATE team set sections = "COLOR" WHERE yearmonth = 202112 AND member_id = '5' AND member_login_id = 'neelima';
-- Subramanya K - NATURE
UPDATE team set sections = "NATURE", member_login_id = 'ceeks', member_password = 'cksub21' WHERE yearmonth = 202112 AND member_id = '6';
-- Ananth Kamat - TRAVEL
UPDATE team set sections = "TRAVEL" WHERE yearmonth = 202112 AND member_id = '7' AND member_login_id = 'ananth';

--
-- Change the description for "multiple_upload_error" to "Similar pictures uploaded"
--
UPDATE `email_template`
   SET template_name = "Similar picture(s) uploaded"
     , short_html = "Similar pictures have been uploaded in this salon under the same section or under multiple sections"
 WHERE template_type = 'user_notification'
   AND template_code = 'multiple_upload_error';

--
-- Issue 005 - Delist Blacklisted people whose term has ended
--
-- 1. Soham Bawaskar
UPDATE `blacklist`
   SET withdrawn = '1'
     , withdrawn_date = '2021-08-31'
     , withdrawn_ref = 'FIP Viewfinder Sep 2021'
 WHERE rec_id = 46
   AND entity_name = "Soham Bawaskar";
-- Profile ID 1756
UPDATE `profile`
   SET blacklist_match = ""
 WHERE profile_id = 1756 AND email = "sohamnbawaskar@gmail.com" AND blacklist_match != "";

-- 2. Sandeep Yadav
UPDATE `blacklist`
  SET withdrawn = '1'
    , withdrawn_date = '2021-08-31'
    , withdrawn_ref = 'FIP Viewfinder Sep 2021'
WHERE rec_id = 47
  AND entity_name = "Sandeep Yadav";
-- Profile ID 1712
UPDATE `profile`
   SET blacklist_match = ""
 WHERE profile_id = 1712 AND email = "sandycinecam@gmail.com" AND blacklist_match != "";

-- 3. J Ramanan
UPDATE `blacklist`
   SET withdrawn = '1'
     , withdrawn_date = '2021-08-31'
     , withdrawn_ref = 'FIP Viewfinder Sep 2021'
 WHERE rec_id = 48
   AND entity_name = "J. Ramanan";

-- 4. Biswajit Mondal
UPDATE `blacklist`
   SET withdrawn = '1'
     , withdrawn_date = '2021-08-31'
     , withdrawn_ref = 'FIP Viewfinder Sep 2021'
 WHERE rec_id = 49
   AND entity_name = "Biswajeet Mondal";
-- Profile ID 1599
UPDATE `profile`
  SET blacklist_match = ""
WHERE profile_id = 1599 AND email = "moni_titli@rediffmail.com" AND blacklist_match != "";

--
-- Add Rajdeep Biswas to No promotion list - Profile ID 379
--
INSERT INTO `noprom` (`profile_id`) VALUES (379);
