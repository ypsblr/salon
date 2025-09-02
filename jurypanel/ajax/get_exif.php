<?php
include("../inc/session.php");
include("../inc/connect.php");
include("../inc/lib.php");

require("../inc/exif.php");

if (isset($_REQUEST['picfile']) && isset($_SESSION['jury_yearmonth']) && isset($_SESSION['section'])) {
    $picfile = "../../salons/" . $_SESSION['jury_yearmonth'] . "/upload/" . $_SESSION['section'] . "/" . $_REQUEST['picfile'];
    if (file_exists($picfile)) {
        $exif = exif_data($picfile);
        if ($exif == false)
            echo json_encode(array("status" => "ERROR", "errmsg" => "No EXIF found"));
        else
            echo json_encode(array("status" => "OK", "errmsg" => "", "exif" => $exif));
    }
    else
        echo json_encode(array("status" => "ERROR", "errmsg" => "Picture file missing"));
}
else {
    echo json_encode(array("status" => "ERROR", "errmsg" => "Invalid Request"));
}
?>
