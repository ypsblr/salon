<?php
// session_start();
include("inc/session.php");

if (isset($_SESSION['err_msg'])) {
	$err_msg = $_SESSION['err_msg'];
	unset($_SESSION['err_msg']);
}
else
	$err_msg = "";
if (isset($_SESSION['admin_id']))
	unset($_SESSION['admin_id']);

// Destroy existing Session - We do not need a session variable in index.php
session_unset();
session_destroy();

include "inc/connect.php";
include "inc/lib.php";
include "inc/contest_lib.php";

?>
<!DOCTYPE html>
<html>
<head>
<?php include "inc/header.php"; ?>
</head>
<body class="blank">

	<!-- Simple splash screen-->
	<div class="splash">
		<div class="color-line"></div>
		<div class="splash-title">
			<h1>Youth Photographic Society || Salon Management Panel</h1>
			<div class="spinner">
				<div class="rect1"></div>
				<div class="rect2"></div>
				<div class="rect3"></div>
				<div class="rect4"></div>
				<div class="rect5"></div>
			</div>
		</div>
	</div>

	<div class="color-line"></div>

	<div class="login-container">

		<div class="row">
			<div class="col-md-12">
				<div class="text-center m-b-md">
					<h3>LOGIN TO ADMIN PANEL</h3>
					<small>Login for Salon Management</small>
				</div>
				<div class="hpanel">
					<div class="panel-body">
                        <form action="op/admin_login.php" id="loginForm" method="post">
                            <div class="form-group">
                                <label class="control-label" for="admin_id">User ID</label>
                                <input type="text" placeholder="Admin ID" title="Please enter you Admin ID" required
										name="admin_id" id="admin_id" class="form-control">
                            </div>
                            <div class="form-group">
                                <label class="control-label" for="password">Password</label>
                                <input type="password" title="Please enter your password" placeholder="******" required
										name="password" id="password" class="form-control">
                                <!-- <span class="help-block small">Your strong password</span> -->
                            </div>
                            <button class="btn btn-success btn-block form-control" type="submit" name="admin_login_check">Login</button>
							<br><p class="text-danger"><small><?= $err_msg;?></small></p>
                        </form>
					</div>
				</div>
			</div>
		</div>

		<div class="row">
			<div class="col-md-12 text-center">
				<strong>Youth Photographic Society</strong> <br/>
			</div>
		</div>
	</div>  <!-- Container -->

<?php include "inc/footer.php"; ?>

</body>
</html>
