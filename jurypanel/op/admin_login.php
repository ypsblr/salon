<?php
// session_start();
include("../inc/session.php");
include("../inc/connect.php");
include("../inc/lib.php");

// debug_dump("POST", $_POST, __FILE__, __LINE__);

function debug_to_console($data) {
    $output = $data;
    if (is_array($output))
        $output = implode(',', $output);

    echo "<script>console.log('Debug Objects: " . $output . "' );</script>";
}

if ( isset($_POST['admin_login_check']) ) {

    debug_to_console(1);
    
	$username = $_POST['username'];
    $password = $_POST['password'];

	// Determine the current Contest
	$sql = "SELECT * FROM contest WHERE judging_in_progress = '1' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_num_rows($query) == 1) {
		$contest = mysqli_fetch_array($query);
		$_SESSION['jury_yearmonth'] = $contest['yearmonth'];
	}

	$sql = "SELECT * FROM user WHERE login = '$username' AND password = '$password' ";
	$qchk = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$count = mysqli_num_rows($qchk);
	// debug_dump("count", $count, __FILE__, __LINE__);

	if ($count > 0) {
		$user = mysqli_fetch_array($qchk);
		$_SESSION['jury_id'] = $user['user_id'];
		$_SESSION['jury_type'] = $user['type'];

		/* Special Logins */
		if($_SESSION['jury_type'] == "PROJECTOR") {	// Judging Projection Control
			printf("<script>location.href='/jurypanel/projector_home.php'</script>");
			header("Location: /jurypanel/projector_home.php");
			die();
		}
		elseif ($_SESSION['jury_type'] == "DISPLAY") {	// Judging Score Display
			if (isset($contest) && $contest['results_ready'] == '0' ) {
				if ($contest['judging_mode'] == "REMOTE") {
					printf("<script>location.href='/jurypanel/display_remote.php'</script>");
					header("Location: /jurypanel/display_remote.php");
					die();
				}
				else {
					printf("<script>location.href='/jurypanel/display.php'</script>");
					header("Location: /jurypanel/display.php");
					die();
				}
			}
			else {
				$_SESSION['err_msg'] = "There are no open salons to display.";
				printf("<script>location.href='/jurypanel/index.php'</script>");
				header("Location: /jurypanel/index.php");
				die();
			}
		}
//		elseif($_SESSION['jury_type'] == "MASTER" || $_SESSION['jury_type'] == "ADMIN")		// Administration Panel
//			printf("<script>location.href='/jurypanel/dashboard.php'</script>");
		else {
			// Regular Jury
			// Check if any contest has been open for judging
			if (! isset($contest)) {
				$_SESSION['err_msg'] = "None of the contests has been opened for judging.";
				printf("<script>location.href='/jurypanel/index.php'</script>");
				header("Location: /jurypanel/index.php");
				die();
			}
			// Determine if the jury has been assigned to any section
			$sql  = "SELECT COUNT(*) AS num_sections FROM assignment ";
			$sql .= " WHERE yearmonth = '" . $contest['yearmonth'] . "' ";
			$sql .= "   AND user_id = '" . $user['user_id'] . "' ";
			// $sql .= "   AND status = 'OPEN' ";
			$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
			$assignment = mysqli_fetch_array($query);
			// Check for assignment
			if ($assignment['num_sections'] == 0) {
				$_SESSION['err_msg'] = "Not assigned to any section(s) under " . $contest['contest_name'] . ".";
				printf("<script>location.href='/jurypanel/index.php'</script>");
				header("Location: /jurypanel/index.php");
				die();
			}

			if ($contest['judging_mode'] == 'REMOTE') {
				printf("<script>location.href='/jurypanel/rating_remote_home.php'</script>"); // Jury Login
				header("Location: /jurypanel/rating_remote_home.php");
				die();
			}
			else {
				if (isset($contest) && $contest['results_ready'] == '0') {
					printf("<script>location.href='/jurypanel/rating_new.php'</script>"); // Jury Login
					header("Location: /jurypanel/rating_new.php");
					die();
				}
				else {
					$_SESSION['err_msg'] = "Rating for " . $contest['contest_name'] . " is closed.";
					printf("<script>location.href='/jurypanel/index.php'</script>");
					header("Location: /jurypanel/index.php");
					die();
				}
			}
		}
	}
	else {
		$_SESSION['err_msg']="Login Failed !!! Check your User Name $username or Password";
		printf("<script>location.href='/jurypanel/index.php'</script>");
		header("Location: /jurypanel/index.php");
		die();
	}
}
else {
	$_SESSION['err_msg'] = "Invalid Request";
	printf("<script>location.href='/jurypanel/index.php'</script>");
	header("Location: /jurypanel/index.php");
}
?>
