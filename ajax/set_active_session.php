<?php
session_save_path(__DIR__ . "/../inc/session");
session_start();
// sets the active section data so that user will land under the same section
if (isset($_SESSION['USER_ID']) &&
		isset($_REQUEST['active_section_type']) && isset($_REQUEST['active_digital_section']) && isset($_REQUEST['active_print_section']) &&
		isset($_REQUEST['hide_upload_instructions']) && isset($_REQUEST['hide_past_acceptances']) ) {
	$_SESSION['active_section_type'] = $_REQUEST['active_section_type'];
	$_SESSION['active_digital_section'] = $_REQUEST['active_digital_section'];
	$_SESSION['active_print_section'] = $_REQUEST['active_print_section'];
	$_SESSION['hide_upload_instructions'] = $_REQUEST['hide_upload_instructions'];
	$_SESSION['hide_past_acceptances'] = $_REQUEST['hide_past_acceptances'];
}
?>
