<?php
// session_start();
include("inc/session.php");

$err_msg = isset($_SESSION['err_msg']) ? $_SESSION['err_msg'] : "";

if (isset($_SESSION['jury_id'])) unset($_SESSION['jury_id']);
if (isset($_SESSION['jury_type'])) unset($_SESSION['jury_type']);
if (isset($_SESSION['jury_yearmonth'])) unset($_SESSION['jury_yearmonth']);

session_destroy();

// Start a New session
session_start();

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
			<h1>Youth Photographic || Jury Panel</h1>
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
					<h3>LOGIN TO JURY PANEL</h3>
					<small>Login for Judging &amp; Administration</small>
				</div>
				<div class="hpanel">
					<div class="panel-body">
                        <form action="op/admin_login.php" id="loginForm" method="post">
                            <div class="form-group">
                                <label class="control-label" for="username">Username</label>
                                <input type="text" placeholder="username" title="Please enter you username" required
										name="username" id="username" class="form-control">
                            </div>
                            <div class="form-group">
                                <label class="control-label" for="password">Password</label>
                                <input type="password" title="Please enter your password" placeholder="******" required
										name="password" id="password" class="form-control">
                                <span class="help-block small">Your strong password</span>
                            </div>
                            <!-- <div class="checkbox" style="margin-top:10px;background-color: #fff;color:#6a6c6f;">
                                <input type="checkbox" class="i-checks">
								Remember login
                                <p class="help-block small">(if this is a private computer)</p>
                            </div> -->
                            <button class="btn btn-success btn-block form-control" type="submit" name="admin_login_check">Login</button>
                            <!-- <a class="btn btn-default btn-block" href="password_recovery.php">Password Recover</a> -->
                        </form>
						<p class="text-danger small"><?= $err_msg;?></p>
					</div>
				</div>
			</div>
		</div>

		<div class="row">
			<div class="col-md-12 text-center">
				<strong>Youth Photographic Society</strong> <br/>
				&copy; YPS 2K16 | Design &amp; Developed by <a href="http://www.imcanny.com/" target="_blank">www.imcanny.com</a>
			</div>
		</div>
	</div>  <!-- Container -->

<?php include "inc/footer.php"; ?>

</body>
</html>
