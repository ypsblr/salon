
<?php
include_once("connect.php");
include_once("lib.php");

error_reporting(-1);
ini_set('display_errors', 'On');
set_error_handler("var_dump");

function debug_to_console($data) {
    $output = $data;
    if (is_array($output))
        $output = implode(',', $output);

    echo "<script>console.log('Debug Objects: " . $output . "' );</script>";
}

$mail_to='gopinath.br@gmail.com,sastry.vikas@gmail.com';
$subject='test subject';
$email_message='Hello! Test email';
$cc_to='gopinath.br@gmail.com';

    // UNCOMMENT THE BELOW CODE WHILE TESTING
    
    // if (send_mail($mail_to, $subject, $email_message, $cc_to)) {
    //     debug_to_console('email sent');
    //     //++ $sent;
    // }
    // else {
    //     log_mail_error($queue_id, $profile_id, "failed", "Mail Send failed");
    //     //++ $failed;
    // }

//if (DEBUG_TRACE) echo "Updated Queue $queue_id. Mails Left $mails_left<br>";

die();

?>
