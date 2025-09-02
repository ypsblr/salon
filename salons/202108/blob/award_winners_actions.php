<?php
// Custom list of actions for each award winner
$action_email = "<a href='mailto:salon@ypsbengaluru.in'>salon@ypsbengaluru.in</a>";
$full_file_action = "We are yet to receive your high resolution files of awarded pictures. Please send the same by email to $action_email.";
$avatar_action = "Please send a printable version of your latest profile picture (your portrait) to $action_email.";
$video_action = "YPS hosts author video, on the results page, explaining how the awarded picture was shot. " .
                "If you are in a position to send a 20 second video we will be happy to publish it on the result page " .
                "after the award function. Please send your video to $action_email.";
$acct_action = "We need your account number details to be able to remit your award money. Please send the details to $action_email.";

$credit_action  = "Please check your account. If money is not credited by ";
$credit_action .= "Saturday, Sep 25th, please write to $action_email.";

$action_list = array(
                "62" => array('profile_id' => '62','profile_name' => 'ARJUN HAARITH', 'actions' => [$credit_action]),
                "88" => array('profile_id' => '88','profile_name' => 'BARUN SINHA', 'actions' => [$credit_action]),
                "143" => array('profile_id' => '143','profile_name' => 'ENAMUL KABIR', 'actions' => [$credit_action]),
                "251" => array('profile_id' => '251','profile_name' => 'MALABIKA ROY', 'actions' => [$credit_action]),
                "341" => array('profile_id' => '341','profile_name' => 'PRAMOD GOVIND SHANBHAG', 'actions' => [$credit_action]),
                "361" => array('profile_id' => '361','profile_name' => 'PUNEET VERMA', 'actions' => [$credit_action]),
                "383" => array('profile_id' => '383','profile_name' => 'RAKESH  RAWAL', 'actions' => [$credit_action]),
                // "470" => array('profile_id' => '470','profile_name' => 'SHREYAS RAO', 'actions' => [$credit_action]),    // YPS Golden Jubilee Award
                "527" => array('profile_id' => '527','profile_name' => 'SUSHANTA SUSHANTA  DAS', 'actions' => [$credit_action]),
                // "588" => array('profile_id' => '588','profile_name' => 'YUKTHI PADMAKAR', 'actions' => [$credit_action]),    // Youth Participant
                "698" => array('profile_id' => '698','profile_name' => 'SUDHEENDRA K P', 'actions' => [$credit_action]),
                "737" => array('profile_id' => '737','profile_name' => 'AJIT HUILGOL', 'actions' => [$credit_action]),
                "744" => array('profile_id' => '744','profile_name' => 'UDAYA THEJASWI URS', 'actions' => [$credit_action]),
                "751" => array('profile_id' => '751','profile_name' => 'DHEERAJ RAJPAL', 'actions' => [$credit_action]),
                "895" => array('profile_id' => '895','profile_name' => 'VIJAY RAWALE', 'actions' => [$credit_action]),
                "1106" => array('profile_id' => '1106','profile_name' => 'DURBA MAZUMDAR', 'actions' => [$credit_action]),    // unable to send video
                "1165" => array('profile_id' => '1165','profile_name' => 'Umashankar BN', 'actions' => [$credit_action]),
                "1191" => array('profile_id' => '1191','profile_name' => 'D C AMITHKUMAR', 'actions' => [$credit_action]),
                "1192" => array('profile_id' => '1192','profile_name' => 'ABHIJEET KUMAR BANERJEE', 'actions' => [$credit_action]), // Confirmed credit
                "1242" => array('profile_id' => '1242','profile_name' => 'HIMADRI BHUYAN', 'actions' => [$credit_action]),
                "1322" => array('profile_id' => '1322','profile_name' => 'CHETHAN RAO MANE', 'actions' => [$credit_action]),
                "1418" => array('profile_id' => '1418','profile_name' => 'ANKIT PORWAL', 'actions' => [$credit_action]),
                "1456" => array('profile_id' => '1456','profile_name' => 'YOGESH MOKASHI', 'actions' => [$credit_action]),
                "1464" => array('profile_id' => '1464','profile_name' => 'SURESH BANGERA', 'actions' => [$credit_action]),
                "1500" => array('profile_id' => '1500','profile_name' => 'SANDEEP KAMATH', 'actions' => [$credit_action]),
                // "1652" => array('profile_id' => '1652','profile_name' => 'PARAM JAIN', 'actions' => [$credit_action]),   // Youth Participant
                // "1653" => array('profile_id' => '1653','profile_name' => 'Sharika V', 'actions' => [$credit_action]),    // Youth Participant
                // "1763" => array('profile_id' => '1763','profile_name' => 'VINYASA UBARADKA', 'actions' => [$credit_action]), // YPS Golden Jubilee Award
                "1832" => array('profile_id' => '1832','profile_name' => 'MRINAL SEN', 'actions' => [$credit_action]),
                "1838" => array('profile_id' => '1838','profile_name' => 'DEBASIS SAHA', 'actions' => [$credit_action]),
                "1922" => array('profile_id' => '1922','profile_name' => 'LIPY DAS', 'actions' => [$credit_action]),
                "1923" => array('profile_id' => '1923','profile_name' => 'SHYAMAL KUMAR CHAKRABORTY', 'actions' => [$credit_action]),
                "1984" => array('profile_id' => '1984','profile_name' => 'KABITA ROY', 'actions' => [$credit_action]),
                "2040" => array('profile_id' => '2040','profile_name' => 'PAISA DHEERAJ', 'actions' => [$credit_action]),
                "2110" => array('profile_id' => '2110','profile_name' => 'AVRA GHOSH', 'actions' => [$credit_action]),
                "2114" => array('profile_id' => '2114','profile_name' => 'Surajit  Roy Chowdhury', 'actions' => [$credit_action]),
                "2129" => array('profile_id' => '2129','profile_name' => 'KOLA VENKATESWARLU', 'actions' => [$credit_action]),
                "2133" => array('profile_id' => '2133','profile_name' => 'PABITRA SEN SHARMA', 'actions' => [$credit_action]),
                "2153" => array('profile_id' => '2153','profile_name' => 'Shridhar Palange', 'actions' => [$credit_action]),
                "2159" => array('profile_id' => '2159','profile_name' => 'RAVALNATH JOSHI', 'actions' => [$credit_action]),
                // "2191" => array('profile_id' => '2191','profile_name' => 'DWIPARNA KUMAR DATTA', 'actions' => [$credit_action]), // YPS Golden Jubilee Award
                "2248" => array('profile_id' => '2248','profile_name' => 'RAMESH HOSKOTE', 'actions' => [$credit_action]),
                "2341" => array('profile_id' => '2341','profile_name' => 'ARPAN KALITA', 'actions' => [$credit_action]),
                "2371" => array('profile_id' => '2371','profile_name' => 'PADMANAVA SANTRA', 'actions' => [$credit_action]),
                "2453" => array('profile_id' => '2453','profile_name' => 'Shashank  Ranjit', 'actions' => [$credit_action]),
                "2478" => array('profile_id' => '2478','profile_name' => 'VIVEK KALLA', 'actions' => [$credit_action]),
                "2491" => array('profile_id' => '2491','profile_name' => 'SOMNATH PAL', 'actions' => [$credit_action]),
                "2494" => array('profile_id' => '2494','profile_name' => 'RANAJABEEN NAWAB', 'actions' => [$credit_action]),
                "2496" => array('profile_id' => '2496','profile_name' => 'DEBARGHYA MUKHERJEE', 'actions' => [$credit_action]),
                "2541" => array('profile_id' => '2541','profile_name' => 'SUBHANKAR BARDHAN', 'actions' => [$credit_action]),
                "2557" => array('profile_id' => '2557','profile_name' => 'DEEP BHATIA', 'actions' => [$credit_action]),
                "2594" => array('profile_id' => '2594','profile_name' => 'DEBASISH BARUA', 'actions' => [$credit_action]),
                // "2657" => array('profile_id' => '2657','profile_name' => 'JAYENDRA BABUBHAI KAMDAR', 'actions' => [$credit_action]),    // YPS Golden Jubilee Award
                "2682" => array('profile_id' => '2682','profile_name' => 'JOYDEEP DEB', 'actions' => [$credit_action]),
                "2731" => array('profile_id' => '2731','profile_name' => 'RAJ KUMAR SOM', 'actions' => [$credit_action]),
                // "2737" => array('profile_id' => '2737','profile_name' => 'PRASENJIT DAS', 'actions' => [$credit_action]),    // YPS Golden Jubilee Award
                "2740" => array('profile_id' => '2740','profile_name' => 'DEBRAJ CHAKRABORTY', 'actions' => [$credit_action]),
                "2765" => array('profile_id' => '2765','profile_name' => 'ARPAN DUTTA CHOWDHURY', 'actions' => [$credit_action]),
                // "2768" => array('profile_id' => '2768','profile_name' => 'Mukesh Srivastava', 'actions' => [$credit_action]) // YPS Golden Jubilee Award
            );

function is_action_pending($profile_id) {
    global $action_list;

    return isset($action_list[$profile_id]);
}

function get_action_description($profile_id) {
    global $action_list;

    if (isset($action_list[$profile_id]) && sizeof($action_list[$profile_id]['actions']) > 0) {
        // return "<p>Preparations for the Salon Exhibition are in progress. We need some inputs from you to complete the same. " .
        //         "We request you to complete the following actions by 13th August:</p>";
        return "<p>We have completed transfer of all award moneys through Bank transfer on Sep 21st.</p>";
    }
    else
        return "<p>There are no pending actions from your end.</p>";
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
