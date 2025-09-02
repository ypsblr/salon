<?php
/*** UNCOMMENT THIS LINE TO DISPLAY ALL PHP ERRORS ***/
//error_reporting(E_ALL);

session_start();

/*** LOADING WORDPRESS LIBRARIES ***/   
define('WP_USE_THEMES', false);
require_once("../wp-load.php");

if (isset($_REQUEST['magic']) && $_REQUEST['magic'] == "ypsmagic1971onwards") {

	$args = array(
				"fields" => "login,first_name,last_name,email",
				"orderby" => "login",
				"order" => "ASC",
				"role__not_in" => "Administrator"
			);
	$userlist = get_users($args);
	
	$data = (object) array();
	if (sizeof($userlist) == 0) {
		$data->status = "ERR";
		$data->errmsg = "YPS Member List returned empty !";
		echo json_encode($data);
	}
	else {
		$return_list = array();
		foreach ($userlist as $user) {
			$swpm_user = SwpmMemberUtils::get_user_by_user_name($user->login);
			if($swpm_user->account_state == "active") {
				$return_user = (object) array();
				$return_user->login = $user->login;
				$return_user->first_name = $user->first_name;
				$return_user->last_name = $user->last_name;
				$return_user->email = $user->email;
				$return_list[] = $return_user;
			}
		}
		$data->status = "OK";
		$data->errmsg = "";
		$data->user_list = $return_list;
		echo json_encode($data);
	}
}
else {
	$data = (object) array();
	$data->status = "ERR";
	$data->errmsg = "Invalid/Unauthorized Request";
	echo json_encode($data);
}


?>