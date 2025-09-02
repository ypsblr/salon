<?php
$user_id = $_SESSION['jury_id'];
$sql = "SELECT * FROM user WHERE user_id = '$user_id'";
$qry = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
$user = mysqli_fetch_array($qry);
?>

<div id="right-sidebar" class="animated fadeInRight">
	<div class="p-m">
		<button id="sidebar-close" class="right-sidebar-toggle sidebar-button btn btn-default m-b-md"><i class="pe pe-7s-close"></i></button>
		<div>
			<span class="font-bold no-margins"> <h2>Update Profile</h2> </span>
		</div>
	</div>
	<div class="p-m bg-light border-bottom border-top" style="padding: 13px !important;">
		<form novalidate="novalidate" role="form" id="form" method="post" action="op/update_info.php">
			<div class="form-group">
				<label>Login</label> 
				<input placeholder="login" class="form-control" name="login" type="text" value="<?php echo $user['login'];?>" disabled>
			</div>
			<div class="form-group">
				<label>Name</label> 
				<input aria-required="true" placeholder="Name" class="form-control" required name="name" type="text" value="<?php echo $user['user_name'];?>">
			</div>
			<div class="form-group">
				<label>Old Password</label> 
				<input placeholder="Password" class="form-control" name="opassword" type="password">
			</div>
			<div class="form-group">
				<label>New Password</label> 
				<input placeholder="Password" class="form-control" name="npassword" type="password">
			</div>
			<div>
				<button class="btn btn-sm btn-primary m-t-n-xs" type="submit" name="update_profile"><strong>Update</strong></button>
			</div>
		</form>
	</div>
</div>