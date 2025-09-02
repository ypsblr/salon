<?php
// Set session saving path
if ( ! is_dir(__DIR__ . "/session") )
    mkdir(__DIR__ . "/session", 0755);

//if ( preg_match("/localhost/i", $_SERVER['SERVER_NAME']) == 0 )
session_save_path(__DIR__ . "/session");

session_start();
// PHP Settings
date_default_timezone_set("Asia/Kolkata");

// Global Constants
define("THIS", basename($_SERVER['PHP_SELF'], ".php"));
?>
