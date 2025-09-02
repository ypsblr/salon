<?php
if (isset($admin_id) && isset($admin_yearmonth)) {
?>

<div id="right-sidebar" class="animated fadeInRight">
	<div class="p-m">
		<button id="sidebar-close" class="right-sidebar-toggle sidebar-button btn btn-default m-b-md"><i class="pe pe-7s-close"></i></button>
		<div>
			<span class="font-bold no-margins"> <h2>Change Password</h2> </span>
		</div>
	</div>
	<div class="p-m bg-light border-bottom border-top" style="padding: 13px !important;">
		<form novalidate="novalidate" role="form" id="form" method="post" action="op/update_password.php">
			<div class="form-group">
				<label>Login</label> 
				<input class="form-control" name="member_login_id" type="text" value="<?= $admin_id;?>" readonly>
			</div>
			<div class="form-group">
				<label>Name</label> 
				<input class="form-control" required name="member_name" type="text" value="<?= $member_name;?>" readonly>
			</div>
			<div class="form-group">
				<label>Current Password</label> 
				<input placeholder="Current Password" class="form-control" name="member_password" type="password">
			</div>
			<div class="form-group">
				<label>New Password</label> 
				<input placeholder="New Password" class="form-control" name="member_password_new" type="password">
			</div>
			<div class="form-group">
				<label>New Password Confirm</label> 
				<input placeholder="Confirm Password" class="form-control" name="member_password_confirm" type="password">
			</div>
			<div>
				<button class="btn btn-sm btn-primary m-t-n-xs" type="submit" name="update_profile"><strong>Change</strong></button>
			</div>
		</form>
	</div>
</div>
<?php
}
?>