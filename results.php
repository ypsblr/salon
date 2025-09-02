<?php 
include_once("inc/session.php");
if($resultsReady) {
	header("Location: /salon.php?id=" . $contest_yearmonth);
	printf("<script>location.href='/salon.php?id=" . $contest_yearmonth . "'</script>");
}
else {
	$_SESSION['err_msg'] = "Salon Results yet to be published !";
	header("Location: /index.php");
	printf("<script>location.href='/index.php'</script>");
}

?>
