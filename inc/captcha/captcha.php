<?php
	// session_start();				// Disabled to avoid session being started with a different setting
	date_default_timezone_set("Asia/Kolkata");
	if (session_status() == PHP_SESSION_NONE) {
	    // Set session saving path
	    if ( ! is_dir(__DIR__ . "/../session") )
	        mkdir(__DIR__ . "/../session", 0755);

	    //if ( preg_match("/localhost/i", $_SERVER['SERVER_NAME']) == 0 )
	    session_save_path(__DIR__ . "/../session");

	    session_start();
	}

	include("./phptextClass.php");

	/*create class object*/
	$phptextObj = new phptextClass();
	/*phptext function to genrate image with text*/
	$phptextObj->phpcaptcha('#162453','#fff',120,40,10,25);
 ?>
