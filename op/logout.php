<?php
session_save_path(__DIR__ . "/../inc/session");
session_start();
include_once("../inc/lib.php");

// Remove Session Variables
delete_session_variables();

// Destroy any saved cookies
delete_cookies();

session_destroy();

header("Location: /index.php");
printf("<script>location.href='/index.php'</script>");

?>
