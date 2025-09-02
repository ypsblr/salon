<?php
// Query to generate the custom list
// SELECT CONCAT('"', profile.profile_id, '" => array("profile_id" => "', profile.profile_id, '", "profile_name" => "' , profile.profile_name, '", "actions" => [$video_action',  IF(profile.avatar = 'user.jpg', ', $avatar_action', ''), ']),')
//   FROM profile
//  WHERE profile_id IN (
//        SELECT DISTINCT profile_id FROM pic_result, award
//         WHERE pic_result.yearmonth = '202112'
//           AND award.yearmonth = pic_result.yearmonth
//           AND award.award_id = pic_result.award_id
//           AND award.level < 99
//        )
//  ORDER BY profile_id


// Custom list of actions for each award winner
$action_email = "<a href='mailto:salon@ypsbengaluru.in'>salon@ypsbengaluru.in</a>";
// $full_file_action = "We are yet to receive your high resolution files of awarded pictures. Please send the same by email to $action_email.";
$avatar_action = "You have not updated your profile picture (Avatar) on your profile page. Please update your profile with the latest photo of yourselves. " .
                " The Certificates and Slideshows will carry your profile picture. ";
$video_action = "YPS salon website offers an opportunity to the authors to share their ideas and experience in making their award winning pictures. The author can " .
                "shoot a short video, not exceeding 20 seconds, for each of his/her picture winning a medal or ribbon. Pick up your phone and shoot the video and " .
                "mail to $action_email. The videos will be published on the Results page after the award function. ";
// $acct_action = "We need your account number details to be able to remit your award money. Please send the details to $action_email.";
// $credit_action  = "Please check your account. If money is not credited by ";
// $credit_action .= "Saturday, Sep 25th, please write to $action_email.";

$action_list = array(
    // "207" => array("profile_id" => "207", "profile_name" => "Kajari Dasgupta", "actions" => [$video_action, $avatar_action]),
    // "229" => array("profile_id" => "229", "profile_name" => "KOSHAL BASU", "actions" => [$video_action]),
    // "267" => array("profile_id" => "267", "profile_name" => "Manu Reghuarajan", "actions" => [$video_action]),
    // "327" => array("profile_id" => "327", "profile_name" => "PINKU DEY", "actions" => [$video_action]),
    // "333" => array("profile_id" => "333", "profile_name" => "PRABIR KUMAR DAS", "actions" => [$video_action]),
    // "467" => array("profile_id" => "467", "profile_name" => "SHRAVAN BM", "actions" => [$video_action]),
    // "487" => array("profile_id" => "487", "profile_name" => "Soumen Kumar Ghosh", "actions" => [$video_action, $avatar_action]),
    // "737" => array("profile_id" => "737", "profile_name" => "AJIT HUILGOL", "actions" => [$video_action]),
    // "810" => array("profile_id" => "810", "profile_name" => "Partha Roy", "actions" => [$video_action]),
    // "850" => array("profile_id" => "850", "profile_name" => "Balasubramanian  G V", "actions" => [$video_action]),
    // "868" => array("profile_id" => "868", "profile_name" => "Abdul Baqi", "actions" => [$video_action]),
    // "1036" => array("profile_id" => "1036", "profile_name" => "JINESH PRASAD", "actions" => [$video_action]),
    // "1086" => array("profile_id" => "1086", "profile_name" => "SUBHAMOY CHAKI", "actions" => [$video_action]),
    // "1169" => array("profile_id" => "1169", "profile_name" => "Subhasish Bhandari", "actions" => [$video_action, $avatar_action]),
    // "1218" => array("profile_id" => "1218", "profile_name" => "CYRIL BOYD", "actions" => [$video_action, $avatar_action]),
    // "1270" => array("profile_id" => "1270", "profile_name" => "Thirumalai Sheerapathi", "actions" => [$video_action]),
    // "1286" => array("profile_id" => "1286", "profile_name" => "Sanjay Joshi", "actions" => [$video_action, $avatar_action]),
    // "1297" => array("profile_id" => "1297", "profile_name" => "Larry Cowles", "actions" => [$video_action, $avatar_action]),
    // "1329" => array("profile_id" => "1329", "profile_name" => "SUBRATA BYSACK", "actions" => [$video_action]),
    // "1339" => array("profile_id" => "1339", "profile_name" => "SOHEL PARVEZ HAQUE", "actions" => [$video_action, $avatar_action]),
    // "1378" => array("profile_id" => "1378", "profile_name" => "Mehmet Gokyigit", "actions" => [$video_action]),
    // "1417" => array("profile_id" => "1417", "profile_name" => "Jan Thomas Stake", "actions" => [$video_action, $avatar_action]),
    // "1418" => array("profile_id" => "1418", "profile_name" => "ANKIT PORWAL", "actions" => [$video_action]),
    // // "1645" => array("profile_id" => "1645", "profile_name" => "ANIRBAN ASH", "actions" => [$video_action]),
    // "1713" => array("profile_id" => "1713", "profile_name" => "ANIL  KUMAR DESHPANDE", "actions" => [$video_action]),
    // "1772" => array("profile_id" => "1772", "profile_name" => "MARIAN PLAINO", "actions" => [$video_action, $avatar_action]),
    // "1781" => array("profile_id" => "1781", "profile_name" => "Graeme Watson", "actions" => [$video_action, $avatar_action]),
    // "1799" => array("profile_id" => "1799", "profile_name" => "Barry Wong", "actions" => [$video_action]),
    // "1801" => array("profile_id" => "1801", "profile_name" => "FLORENTINO MOLERO GUTIERREZ", "actions" => [$video_action, $avatar_action]),
    // "1804" => array("profile_id" => "1804", "profile_name" => "BALACHANDDER SK", "actions" => [$video_action]),
    // "1813" => array("profile_id" => "1813", "profile_name" => "DANIEL LYBAERT", "actions" => [$video_action, $avatar_action]),
    // "1825" => array("profile_id" => "1825", "profile_name" => "Ole Suszkiewicz", "actions" => [$video_action, $avatar_action]),
    // "1832" => array("profile_id" => "1832", "profile_name" => "MRINAL SEN", "actions" => [$video_action, $avatar_action]),
    // "1877" => array("profile_id" => "1877", "profile_name" => "MALAY BASU", "actions" => [$video_action]),
    // "1912" => array("profile_id" => "1912", "profile_name" => "Grace Lee", "actions" => [$video_action]),
    // "1947" => array("profile_id" => "1947", "profile_name" => "SHERMAN CHEANG", "actions" => [$video_action, $avatar_action]),
    // "1950" => array("profile_id" => "1950", "profile_name" => "Anupam Chakraborty", "actions" => [$video_action]),
    // "1953" => array("profile_id" => "1953", "profile_name" => "thippeswamy s", "actions" => [$video_action, $avatar_action]),
    // "1961" => array("profile_id" => "1961", "profile_name" => "Aage Madsen", "actions" => [$video_action, $avatar_action]),
    // "1994" => array("profile_id" => "1994", "profile_name" => "Bob Goode", "actions" => [$video_action, $avatar_action]),
    // "1998" => array("profile_id" => "1998", "profile_name" => "sinclair adair", "actions" => [$video_action]),
    // "1999" => array("profile_id" => "1999", "profile_name" => "BARBARA TASKO", "actions" => [$video_action, $avatar_action]),
    // "2071" => array("profile_id" => "2071", "profile_name" => "PRASENJIT BASU", "actions" => [$video_action, $avatar_action]),
    // "2087" => array("profile_id" => "2087", "profile_name" => "SADIQUR RAHMAN", "actions" => [$video_action, $avatar_action]),
    // "2089" => array("profile_id" => "2089", "profile_name" => "ARABINDA BASAK", "actions" => [$video_action, $avatar_action]),
    // "2092" => array("profile_id" => "2092", "profile_name" => "SANDIP PRAMANICK", "actions" => [$video_action]),
    // "2097" => array("profile_id" => "2097", "profile_name" => "Jayanta Guha", "actions" => [$video_action]),
    // "2126" => array("profile_id" => "2126", "profile_name" => "SUVANKAR BAGCHI", "actions" => [$video_action, $avatar_action]),
    // "2175" => array("profile_id" => "2175", "profile_name" => "DIPANJAN NATH", "actions" => [$video_action]),
    // "2180" => array("profile_id" => "2180", "profile_name" => "ASIM KUMAR BHATTACHARJEE", "actions" => [$video_action, $avatar_action]),
    // "2183" => array("profile_id" => "2183", "profile_name" => "BIDHAN DEBNATH", "actions" => [$video_action]),
    // "2232" => array("profile_id" => "2232", "profile_name" => "ANIRBAN CHAKRABARTI", "actions" => [$video_action]),
    // "2258" => array("profile_id" => "2258", "profile_name" => "PARAG BANERJEE", "actions" => [$video_action, $avatar_action]),
    // "2323" => array("profile_id" => "2323", "profile_name" => "BINOY BIKASH GOGOI ", "actions" => [$video_action, $avatar_action]),
    // "2327" => array("profile_id" => "2327", "profile_name" => "CHINMOY BHATTACHARJEE", "actions" => [$video_action]),
    // "2352" => array("profile_id" => "2352", "profile_name" => "SANTANU BOSE", "actions" => [$video_action, $avatar_action]),
    // "2390" => array("profile_id" => "2390", "profile_name" => "Buddhadeb Adhikary", "actions" => [$video_action]),
    // "2421" => array("profile_id" => "2421", "profile_name" => "ANITA BASAK", "actions" => [$video_action, $avatar_action]),
    // "2494" => array("profile_id" => "2494", "profile_name" => "Rana Jabeen Nawab", "actions" => [$video_action]),
    // "2541" => array("profile_id" => "2541", "profile_name" => "SUBHANKAR BARDHAN", "actions" => [$video_action]),
    // "2557" => array("profile_id" => "2557", "profile_name" => "DEEP BHATIA", "actions" => [$video_action]),
    // "2561" => array("profile_id" => "2561", "profile_name" => "PRASENJIT SANYAL", "actions" => [$video_action]),
    // "2657" => array("profile_id" => "2657", "profile_name" => "JAYENDRA BABUBHAI KAMDAR", "actions" => [$video_action]),
    // "2737" => array("profile_id" => "2737", "profile_name" => "PRASENJIT DAS", "actions" => [$video_action]),
    // "2747" => array("profile_id" => "2747", "profile_name" => "SP NAGENDRA SP", "actions" => [$video_action]),
    // "2847" => array("profile_id" => "2847", "profile_name" => "Yasser Alaa Mobarak", "actions" => [$video_action]),
    // "2851" => array("profile_id" => "2851", "profile_name" => "Wendy Irwin", "actions" => [$video_action, $avatar_action]),
    // "2868" => array("profile_id" => "2868", "profile_name" => "RAJU PONAGANTI", "actions" => [$video_action]),
    // "2880" => array("profile_id" => "2880", "profile_name" => "FEDAI COSKUN", "actions" => [$video_action, $avatar_action]),
    // "2902" => array("profile_id" => "2902", "profile_name" => "Sugiarto Widodo", "actions" => [$video_action, $avatar_action]),
    // "2924" => array("profile_id" => "2924", "profile_name" => "Ricardo Q T Rodrigues", "actions" => [$video_action]),
    // "2946" => array("profile_id" => "2946", "profile_name" => "DUONG VU", "actions" => [$video_action]),
    // "2950" => array("profile_id" => "2950", "profile_name" => "MIHAI ROMEO BOGDAN", "actions" => [$video_action, $avatar_action]),
    // "2951" => array("profile_id" => "2951", "profile_name" => "SATYAJIT DAS", "actions" => [$video_action]),
    // "2956" => array("profile_id" => "2956", "profile_name" => "Slobodanse Cavic", "actions" => [$video_action]),
    // "2980" => array("profile_id" => "2980", "profile_name" => "Seham Mohammed", "actions" => [$video_action]),
    // "2984" => array("profile_id" => "2984", "profile_name" => "Veysi ARCAGOK", "actions" => [$video_action]),
    // "2992" => array("profile_id" => "2992", "profile_name" => "mohammad esteki", "actions" => [$video_action, $avatar_action]),
    // "3007" => array("profile_id" => "3007", "profile_name" => "Nguyen Van Hai", "actions" => [$video_action]),
    // "3015" => array("profile_id" => "3015", "profile_name" => "BHRIGU KUMAR BAYAN", "actions" => [$video_action]),
    // "3022" => array("profile_id" => "3022", "profile_name" => "Phoe Char", "actions" => [$video_action]),
    // "3051" => array("profile_id" => "3051", "profile_name" => "Saydam Soy", "actions" => [$video_action]),
    // "3062" => array("profile_id" => "3062", "profile_name" => "SUPRATIM ROY", "actions" => [$video_action, $avatar_action]),
    // "3070" => array("profile_id" => "3070", "profile_name" => "wei lian", "actions" => [$video_action, $avatar_action]),
    // "3071" => array("profile_id" => "3071", "profile_name" => "HUY NGUYEN QUOC", "actions" => [$video_action]),
    // "3079" => array("profile_id" => "3079", "profile_name" => "ozgur secmen", "actions" => [$video_action, $avatar_action]),
    // "3099" => array("profile_id" => "3099", "profile_name" => "ROBIN LUO", "actions" => [$video_action, $avatar_action]),
    // "3107" => array("profile_id" => "3107", "profile_name" => "Suresh Govindaraghavan", "actions" => [$video_action]),
    // "3110" => array("profile_id" => "3110", "profile_name" => "Adrian Whear", "actions" => [$video_action, $avatar_action]),
    // "3111" => array("profile_id" => "3111", "profile_name" => "PHYOE ZAW", "actions" => [$video_action, $avatar_action]),
    // "3112" => array("profile_id" => "3112", "profile_name" => "JAYESH SURENDRAN", "actions" => [$video_action]),
    // "3114" => array("profile_id" => "3114", "profile_name" => "Wade Buchan", "actions" => [$video_action, $avatar_action]),
    // "3122" => array("profile_id" => "3122", "profile_name" => "Hla Moe Naing", "actions" => [$video_action]),
    // "3163" => array("profile_id" => "3163", "profile_name" => "Chen You Li", "actions" => [$video_action, $avatar_action]),
    // "3171" => array("profile_id" => "3171", "profile_name" => "Sharma B C", "actions" => [$video_action]),
    // "3192" => array("profile_id" => "3192", "profile_name" => "jurong Yu", "actions" => [$video_action, $avatar_action]),
    // "3193" => array("profile_id" => "3193", "profile_name" => "Jim van Iterson", "actions" => [$video_action, $avatar_action]),
    // "3198" => array("profile_id" => "3198", "profile_name" => "ANUP MAJUMDAR", "actions" => [$video_action, $avatar_action]),
    // // "3202" => array("profile_id" => "3202", "profile_name" => "Mikhail Bondar", "actions" => [$video_action]),
    // "3217" => array("profile_id" => "3217", "profile_name" => "Mary Pears", "actions" => [$video_action, $avatar_action]),
    // "3221" => array("profile_id" => "3221", "profile_name" => "Saikat Chattopadhya", "actions" => [$video_action]),
    // "3223" => array("profile_id" => "3223", "profile_name" => "Md Asker Ibne Firoz", "actions" => [$video_action]),
    // "3224" => array("profile_id" => "3224", "profile_name" => "KHORSHED ALAM SAGOR", "actions" => [$video_action]),
    // "3229" => array("profile_id" => "3229", "profile_name" => "Xiaoying Shi", "actions" => [$video_action, $avatar_action]),
    // "3248" => array("profile_id" => "3248", "profile_name" => "Janez Podnar", "actions" => [$video_action, $avatar_action]),
    // "3250" => array("profile_id" => "3250", "profile_name" => "Barbara Schmidt", "actions" => [$video_action]),
    // "3253" => array("profile_id" => "3253", "profile_name" => "Noel Fenech", "actions" => [$avatar_action]),        // Has sent video
    // "3260" => array("profile_id" => "3260", "profile_name" => "Avik Datta", "actions" => [$video_action, $avatar_action]),
    // "3268" => array("profile_id" => "3268", "profile_name" => "Shirley Gillitt", "actions" => [$video_action, $avatar_action]),
    // "3270" => array("profile_id" => "3270", "profile_name" => "Howard Gillitt", "actions" => [$video_action, $avatar_action]),
    // "3277" => array("profile_id" => "3277", "profile_name" => "Stephen Kangisser", "actions" => [$video_action, $avatar_action]),
    // "3278" => array("profile_id" => "3278", "profile_name" => "Sebestyen Cirkos", "actions" => [$video_action, $avatar_action]),
    // "3285" => array("profile_id" => "3285", "profile_name" => "YONGXIONG LING", "actions" => [$video_action, $avatar_action]),
    // "3287" => array("profile_id" => "3287", "profile_name" => "Sau Hung Li", "actions" => [$video_action, $avatar_action]),
    // "3290" => array("profile_id" => "3290", "profile_name" => "John Jiang", "actions" => [$video_action, $avatar_action]),
    // "3300" => array("profile_id" => "3300", "profile_name" => "Vnh Le Van", "actions" => [$video_action, $avatar_action]),
    // "3336" => array("profile_id" => "3336", "profile_name" => "Qingshun Liu", "actions" => [$video_action, $avatar_action]),
    // "3341" => array("profile_id" => "3341", "profile_name" => "Zhimin Zhang", "actions" => [$video_action, $avatar_action]),
    // "3356" => array("profile_id" => "3356", "profile_name" => "MALABIKA ROY", "actions" => [$video_action, $avatar_action]),
    // "3373" => array("profile_id" => "3373", "profile_name" => "Kim Yiang CHNG", "actions" => [$video_action]),
    // "3382" => array("profile_id" => "3382", "profile_name" => "Xiao Xiao", "actions" => [$video_action, $avatar_action]),
    // "3396" => array("profile_id" => "3396", "profile_name" => "Piyali Mitra", "actions" => [$video_action, $avatar_action]),
    // "3405" => array("profile_id" => "3405", "profile_name" => "ALEKSANDAR TOMULIC", "actions" => [$video_action, $avatar_action]),
    // "3421" => array("profile_id" => "3421", "profile_name" => "MERETI SOMARAJU", "actions" => [$video_action, $avatar_action]),
    // "3423" => array("profile_id" => "3423", "profile_name" => "David Wheeler", "actions" => [$video_action]),
    // "3426" => array("profile_id" => "3426", "profile_name" => "Priyanka Kar", "actions" => [$video_action, $avatar_action]),
    // "3431" => array("profile_id" => "3431", "profile_name" => "Faisal Khalaf", "actions" => [$video_action]),
    // "3434" => array("profile_id" => "3434", "profile_name" => "Isa Ebrahim", "actions" => [$video_action]),
    // "3438" => array("profile_id" => "3438", "profile_name" => "Percy Mitchell", "actions" => [$video_action]),
    // "3448" => array("profile_id" => "3448", "profile_name" => "Min Maung Maung Myo", "actions" => [$video_action]),
    // "3461" => array("profile_id" => "3461", "profile_name" => "Tibor Torok", "actions" => [$video_action, $avatar_action]),
    // "3469" => array("profile_id" => "3469", "profile_name" => "Graham Pears", "actions" => [$video_action, $avatar_action]),
    // "3471" => array("profile_id" => "3471", "profile_name" => "Anita Tandari", "actions" => [$video_action, $avatar_action]),
    // "3477" => array("profile_id" => "3477", "profile_name" => "Grzegorz Lewandowski", "actions" => [$video_action, $avatar_action]),
    // "3479" => array("profile_id" => "3479", "profile_name" => "Janos Danis", "actions" => [$video_action, $avatar_action]),
	// "3495" => array("profile_id" => "3495", "profile_name" => "Yogesh B", "actions" => [$video_action]),
	"868" => array("profile_id" => "868", "profile_name" => "Abdul Baqi", "actions" => []),
	"1378" => array("profile_id" => "1378", "profile_name" => "Mehmet Gokyigit", "actions" => []),
	"3051" => array("profile_id" => "3051", "profile_name" => "Saydam Soy", "actions" => []),
	"1772" => array("profile_id" => "1772", "profile_name" => "Marian Plaino", "actions" => []),
	"2950" => array("profile_id" => "2950", "profile_name" => "Mihai Romeo Bogdan", "actions" => []),
    "2956" => array("profile_id" => "2956", "profile_name" => "Slobodanse Cavic", "actions" => []),
            );

function is_action_pending($profile_id) {
    global $action_list;

    return isset($action_list[$profile_id]);
}

function get_action_description($profile_id) {
    global $action_list;

    if (isset($action_list[$profile_id]) && sizeof($action_list[$profile_id]['actions']) > 0) {
        return "<p>We just completed mailing of awards to the participants of the salon through India Posts. " .
                "We would like to inform you that presently India Posts is not accepting any mails addressed to your country. " .
				"<b>Hence your awards could not be sent at present</b>. " .
                "We will check for availability of services to your country after a couple of months. </p>";
		// return "<p>Preparations to hold a physical Salon Exhibition between December 10 and 12 are in progress. " .
        //         "We are also in the process of designing the Salon Catalog. We need some inputs from you to help us make these world class. " .
        //         "Please attend to the following requests by November 30th:</p>";
        // return "<p>We have completed transfer of all award moneys through Bank transfer on Sep 21st.</p>";
    }
    else
		return "<p>We just completed mailing of awards to the participants of the salon through India Posts. " .
				"We would like to inform you that presently India Posts is not accepting any mails addressed to your country. " .
				"Hence <b>your awards could not be sent at present</b>. " .
				"We will check for availability of services to your country after a couple of months. </p>";
        // return "<p>There are no pending actions from your end.</p>";
}

function get_action_list($profile_id) {
    global $action_list;

    if (isset($action_list[$profile_id]) && sizeof($action_list[$profile_id]['actions']) > 0) {
        $html = "<ul>";
        foreach ($action_list[$profile_id]['actions'] as $action)
            $html .= "<li>$action</li>";
        $html .= "</ul>";
        return $html;
    }
    else
        return "";
}

?>
