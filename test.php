<?php
function hex2str($hex) {
    $str = '';
    for($i=0;$i<strlen($hex);$i+=2) $str .= chr(hexdec(substr($hex,$i,2)));
    return $str;
}

include "inc/connect.php";

$sql = "SELECT * FROM yps_user WHERE yps_login_id = 'LM-193' ";
$query = mysqli_query($DBCON, $sql) or die(mysqli_error($DBCON));
$row = mysqli_fetch_array($query);
echo "Password on DB = " . $row['password'] . "<br>";

include "inc/phpass.php";
$hasher = new PasswordHash(8, true);
    
// $output = $hasher->HashPassword($hashed);

if ($hasher->CheckPassword("2bRnot2b", $row['password']))
    echo "Correct Password !";
else
    echo "Incorrect Password. Try again";
    
// for ($i = 1; $i < 4; ++ $i) {
    // $i = 24;
    // set_time_limit(1200);
    //$hasher = new PasswordHash($i, true);
    
    //$hashed = $hasher->HashPassword("2bRnot2b");
	//$output .= md5("2bRnot2b");
    
    // if ($hashed == $row['password'])
    //   echo "<b>Match found</b><br>";

    // echo "Hashed Password for " . $i . " iterations = " . $hashed  . "<br>";
    
// }
?>
