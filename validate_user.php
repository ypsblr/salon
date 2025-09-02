<?php
$fp = fopen("errors.txt", "w");

    // Invoke YPS Authentication Service
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://www.ypsbengaluru.com/svc/getuserbyemail.php");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    $post_fields = array();
    $post_fields['email'] = "IM-0997";
    $post_fields["magic"] = "ypsmagic1971onwards";
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
    

    $result = curl_exec($ch);
if(curl_error($ch)) {
    fwrite($fp, curl_error($ch));
}
else {
    fwrite($fp, $result);
}

$json = json_decode($result, true);
$juser = $json["user"];
var_dump($json);

// echo nl2br($json["status"]);
echo nl2br($juser["first_name"]);
echo nl2br($juser["gender"]);

curl_close($ch);
fclose($fp);
?>