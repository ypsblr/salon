<?php
	if(!isset($_SESSION['USER_ID'])) {
?>
<div class="well well-sm" style="width:100%;">
	<ul class="nav nav-tabs">
		<li class="active">
			<a class="active" data-toggle="tab" href="#login_tab">Login</a>
		</li>
		<li>
			<a data-toggle="tab" href="#signup_tab">Sign Up</a>
		</li>
		<li >
			<a data-toggle="tab" href="#reset_tab">Reset Password</a>
		</li>
	</ul>
	<div class="tab-content" >
		<!-- Login Form -->
		<div class="tab-pane fade in active" id="login_tab">
			<form role="form" method="post" action="#" id="login_form" style="width:100%">
				<div class="row">
					<div class="col-sm-12 col-md-12 col-lg-12">
						<div class="form-group">
							<label for="login_id">Email (<small>or YPS Member ID for YPS Members</small>) *</label>
							<input type="text" name="login_login_id" class="form-control" placeholder="Email or YPS-MEMBER-ID"  value="nosuch@email.com">
							<input type="text" name="check_it" class="form-control" autocomplete="off" required>
						</div>
					</div>
					<div class="col-sm-12 col-md-12 col-lg-12">
						<div class="form-group">
							<label for="password">PASSWORD</label>
							<input type="password" name="login_password" class="form-control" id="login_password" placeholder="Password" autocomplete="off" required>
						</div>
					</div>
				</div>
				<br>
				<div class="row">
					<div class="col-sm-8 col-md-8 col-lg-8">
					</div>
					<div class="col-sm-4 col-md-4 col-lg-4">
						<button type="submit" class="btn btn-color pull-right" style="margin-top: 0;" name="login_check" id="login_check" ><i class="fa fa-unlock"></i> Login</button>
					</div>
				</div>
			</form>
		</div>
		<!-- Signup Form -->
		<div class="tab-pane fade" id="signup_tab">
			<form role="form" method="post" action="#" id="signup_form" style="width:100%">
				<div class="row">
					<div class="col-sm-12 col-md-12 col-lg-12">
						<div class="form-group">
							<p>To Sign up, enter your Email ID (or YPS Member ID for YPS Members) and click on the [Register as new User] button.</p>
							<label for="login_id">Email (<small>or YPS Member ID for YPS Members</small>) *</label>
							<input type="text" name="login_login_id" class="form-control" placeholder="Email or YPS-MEMBER-ID"  value="nosuch@email.com" >
							<input type="text" name="check_it" class="form-control" autocomplete="off" required>
						</div>
					</div>
					<div class="col-sm-12 col-md-12 col-lg-12">
						<div class="form-group">
							<label for="login_phone">Phone Number *</label>
							<input type="number" name="call_it" class="form-control" autocomplete="off" placeholder="Phone No" required>
						</div>
					</div>
				</div>
				<br>
				<div class="row">
					<div class="col-sm-4 col-md-4 col-lg-4">
					</div>
					<div class="col-sm-8 col-md-8 col-lg-8">
						<button type="submit" class="btn btn-info pull-right" style="margin-top: 0; width: 80%;" name="sign_up" id="sign_up" ><i class="fa fa-user-plus"></i> Register as new User</button>
					</div>
				</div>
			</form>
		</div>
		<!-- Reset Password Form -->
		<div class="tab-pane fade" id="reset_tab">
			<form role="form" method="post" action="#" id="reset_form" style="width:100%">
				<div class="row">
					<div class="col-sm-12 col-md-12 col-lg-12">
						<p>
							NOTE: YPS Members should click on this <a href="https://www.ypsbengaluru.com/membership-login/password-reset/" target="_blank">YPS Password Reset Link</a> to reset password.
						</p>
						<div class="form-group">
							<label for="login_id">Email *</label>
							<input type="text" name="login_login_id" class="form-control" placeholder="Email" value="nosuch@email.com" required>
							<input type="text" name="check_it" class="form-control" autocomplete="off" required>
						</div>
					</div>
				</div>
				<br>
				<div class="row">
					<div class="col-sm-4 col-md-4 col-lg-4">
					</div>
					<div class="col-sm-8 col-md-8 col-lg-8">
						<button type="submit" class="btn btn-warning pull-right" style="margin-top: 0; width: 80%;" name="login_reset_password" id="login_reset_password" ><i class="fa fa-user-plus"></i> Reset Password</button>
					</div>
				</div>
			</form>
		</div>
	</div>
	<p id="login_err_msg" class="text-danger"></p>
	<hr>
	<p class="text-color"><a href="https://www.ypsbengaluru.com/membership-join/" target="_blank">Become a YPS Member</a></p>
	<!-- Form that will be used to submit data -->
	<form id="submission_form" name="submission_form" action="op/login.php" method="post">
		<input type="hidden" name="ypsd" id="ypsd" value="" >
	</form>
</div>
<?php
	}
	else {
?>
<div class="well well-sm" style="width:100%;">
	<div class="row">
		<div class="col-sm-4 col-md-4 col-lg-4">
			<img src="/res/avatar/<?php echo $_SESSION['USER_AVATAR'];?>" style="max-height: 120px; max-width: 120px; border: solid 1px #ccc;">
		</div>
		<div class="col-sm-8 col-md-8 col-lg-8">
			Logged in as <?php echo $_SESSION["LOGIN_ID"] . (($tr_user['yps_login_id'] != "") ? " (" . $tr_user['yps_login_id'] . ")" : "");?><br><br>
			<strong><?php echo $_SESSION['USER_NAME'];?></strong><br>
			<a class="btn btn-color" href="user_panel.php" ><i class="fa fa-user"></i> My Page</a><br>
			<a href="op/logout.php" class="pull-right"><i class="fa fa-sign-out"></i> Logout</a>
		</div>
	</div>
</div>
<?php
	}
?>
