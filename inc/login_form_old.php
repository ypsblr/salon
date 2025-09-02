<?php 
	if(!isset($_SESSION['USER_ID'])) { 
?>
<div class="well well-sm" style="width:100%;"> 
	<form role="form" method="post" action="#" id="login_form" style="width:100%">
		<div class="row">
			<div class="col-sm-12 col-md-12 col-lg-12">
				<div class="form-group">
					<label for="login_id">Email or YPS Member ID*</label>
					<input type="text" name="login_login_id" class="form-control" id="login_login_id" placeholder="Email or YPS-MEMBER-ID"  value="nosuch@email.com" required>
					<input type="text" name="check_it" class="form-control" id="check_it" required>
				</div>
			</div>
			<div class="col-sm-12 col-md-12 col-lg-12">
				<div class="form-group">
					<label for="password">PASSWORD</label>
					<input type="password" name="login_password" class="form-control" id="login_password" placeholder="Password">
				</div>
			</div>
		</div>
		<br>
		<div class="row">
			<div class="col-sm-8 col-md-8 col-lg-8">
				<button type="submit" class="btn btn-info" style="margin-top: 0; width: 80%;" name="sign_up" ><i class="fa fa-user-plus"></i> Register as new User</button>
			</div>
			<div class="col-sm-4 col-md-4 col-lg-4">
				<button type="submit" class="btn btn-color pull-right" style="margin-top: 0;" name="login_check" ><i class="fa fa-unlock"></i> Login</button>
			</div>
		</div>
		<div class="row">
			<div class="col-sm-8 col-md-8 col-lg-8">
				<button type="submit" class="btn btn-default" style="border: 0;" name="login_reset_password" id="login_reset_password" onclick="return resetConfirmation()" ><span class="small text-muted"><i class="fa fa-refresh"></i> Reset Password</span></button>
			</div>
		</div>
	</form>
	<p class="text-color"><a href="http://www.ypsbengaluru.com/membership-join/" target="_blank">Become a YPS Member</a></p>
	<!-- Form that will be used to submit data -->
	<form id="submission_form" name="submission_form" action="op/login.php" method="post">
		<input type="hidden" name="ypsc" id="ypsc" value="<?= $key;?>" >
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
			<img src="/img/avatar/<?php echo $_SESSION['USER_AVATAR'];?>" style="max-height: 120px; max-width: 120px; border: solid 1px #ccc;">
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
