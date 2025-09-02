<?php
//
// Reconcile Member Details with YPS records and update
//
include("inc/session.php");
include("inc/connect.php");
include("inc/lib.php");

// YPS Get list of Users
// Returns an array of login, first_name, last_name, email
	$yps_users = array();

	// Invoke YPS Authentication Service
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "https://www.ypsbengaluru.com/svc/hello.php");
	// curl_setopt($ch, CURLOPT_URL, "https://www.ypsbengaluru.com/svc/getuserlist.php");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HEADER, false);
	$post_fields = array();
	$post_fields["magic"] = "ypsmagic1971onwards";
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);

	$result = json_decode(curl_exec($ch), true);
	echo "Error:" . curl_error($ch);
    echo "No of members : " . sizeof($result);
?>
