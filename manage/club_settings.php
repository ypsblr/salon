<?php
include("inc/session.php");
include("inc/connect.php");
include("inc/lib.php");

if (!isset($_SESSION['admin_id'])) {
	header("Location: index.php");
	die();
}

include("inc/load_common_data.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_club_settings'])) {
	$values = array();
	foreach (club_config_keys() as $key) {
		$values[$key] = isset($_POST[$key]) ? $_POST[$key] : "";
	}
	if (club_config_save($values))
		$_SESSION['success_msg'] = "Club settings saved.";
	else
		$_SESSION['err_msg'] = "Could not write club.config. Check file permissions on the server.";
	header("Location: club_settings.php");
	die();
}

$config = club_config();
?>
<!DOCTYPE html>
<html>
<head>
	<title><?php echo club_name_html(); ?> | Club Settings</title>
	<?php include "inc/header.php"; ?>
</head>
<body class="fixed-navbar fixed-sidebar">

	<div class="splash">
		<div class="color-line"></div>
		<div class="splash-title">
			<h1><?php echo club_short_name_html(); ?> SALON MANAGEMENT PANEL</h1>
			<p>Please Wait.</p>
			<div class="spinner">
				<div class="rect1"></div>
				<div class="rect2"></div>
				<div class="rect3"></div>
				<div class="rect4"></div>
				<div class="rect5"></div>
			</div>
		</div>
	</div>

<?php
	include "inc/master_topbar.php";
	include "inc/master_sidebar.php";
?>

	<div id="wrapper">
		<div class="normalheader transition animated fadeIn">
			<div class="hpanel">
				<div class="panel-body">
					<h3 class="font-light m-b-xs">Club Settings</h3>
					<p class="text-muted">Values are stored in <code>club.config</code> at the site root and apply across the salon application.</p>
				</div>
			</div>
		</div>

		<div class="content">
			<form role="form" method="post" action="club_settings.php" id="club_settings_form">
				<input type="hidden" name="save_club_settings" value="1">

				<div class="hpanel">
					<div class="panel-heading"><h4>Identity</h4></div>
					<div class="panel-body">
						<div class="row form-group">
							<div class="col-sm-6">
								<label for="club_name">Club name</label>
								<input type="text" class="form-control" name="club_name" id="club_name"
									value="<?php echo club_name_html(); ?>">
							</div>
							<div class="col-sm-6">
								<label for="club_short_name">Short name (abbreviation)</label>
								<input type="text" class="form-control" name="club_short_name" id="club_short_name"
									value="<?php echo club_short_name_html(); ?>">
							</div>
						</div>
					</div>
				</div>

				<div class="hpanel">
					<div class="panel-heading"><h4>Websites</h4></div>
					<div class="panel-body">
						<div class="row form-group">
							<div class="col-sm-6">
								<label for="yps_website_without_www">Website (no www)</label>
								<input type="url" class="form-control" name="yps_website_without_www" id="yps_website_without_www"
									value="<?php echo yps_website_without_www_html(); ?>">
							</div>
							<div class="col-sm-6">
								<label for="yps_website_with_www">Website (with www)</label>
								<input type="url" class="form-control" name="yps_website_with_www" id="yps_website_with_www"
									value="<?php echo yps_website_with_www_html(); ?>">
							</div>
						</div>
					</div>
				</div>

				<div class="hpanel">
					<div class="panel-heading"><h4>Email addresses</h4></div>
					<div class="panel-body">
						<div class="row form-group">
							<div class="col-sm-4">
								<label for="contact_email">Contact email</label>
								<input type="email" class="form-control" name="contact_email" id="contact_email"
									value="<?php echo contact_email_html(); ?>">
							</div>
							<div class="col-sm-4">
								<label for="salon_email">Salon email</label>
								<input type="email" class="form-control" name="salon_email" id="salon_email"
									value="<?php echo salon_email_html(); ?>">
							</div>
							<div class="col-sm-4">
								<label for="contest_email">Contest email</label>
								<input type="email" class="form-control" name="contest_email" id="contest_email"
									value="<?php echo contest_email_html(); ?>">
							</div>
						</div>
					</div>
				</div>

				<div class="hpanel">
					<div class="panel-heading"><h4>Primary contact</h4></div>
					<div class="panel-body">
						<div class="row form-group">
							<div class="col-sm-3">
								<label for="club_contact1_label">Label</label>
								<input type="text" class="form-control" name="club_contact1_label" id="club_contact1_label"
									value="<?php echo club_contact1_label_html(); ?>">
							</div>
							<div class="col-sm-5">
								<label for="club_contact1_name">Name / honors line</label>
								<input type="text" class="form-control" name="club_contact1_name" id="club_contact1_name"
									value="<?php echo club_contact1_name_html(); ?>">
							</div>
							<div class="col-sm-2">
								<label for="club_contact1_email">Email</label>
								<input type="email" class="form-control" name="club_contact1_email" id="club_contact1_email"
									value="<?php echo club_contact1_email_html(); ?>">
							</div>
							<div class="col-sm-2">
								<label for="club_contact1_phone">Phone</label>
								<input type="text" class="form-control" name="club_contact1_phone" id="club_contact1_phone"
									value="<?php echo club_contact1_phone_html(); ?>">
							</div>
						</div>
					</div>
				</div>

				<div class="hpanel">
					<div class="panel-heading"><h4>Secondary contact</h4></div>
					<div class="panel-body">
						<div class="row form-group">
							<div class="col-sm-3">
								<label for="club_contact2_label">Label</label>
								<input type="text" class="form-control" name="club_contact2_label" id="club_contact2_label"
									value="<?php echo club_contact2_label_html(); ?>">
							</div>
							<div class="col-sm-5">
								<label for="club_contact2_name">Name / honors line</label>
								<input type="text" class="form-control" name="club_contact2_name" id="club_contact2_name"
									value="<?php echo club_contact2_name_html(); ?>">
							</div>
							<div class="col-sm-2">
								<label for="club_contact2_email">Email</label>
								<input type="email" class="form-control" name="club_contact2_email" id="club_contact2_email"
									value="<?php echo club_contact2_email_html(); ?>">
							</div>
							<div class="col-sm-2">
								<label for="club_contact2_phone">Phone</label>
								<input type="text" class="form-control" name="club_contact2_phone" id="club_contact2_phone"
									value="<?php echo club_contact2_phone_html(); ?>">
							</div>
						</div>
					</div>
				</div>

				<div class="hpanel">
					<div class="panel-heading"><h4>Postal address</h4></div>
					<div class="panel-body">
						<div class="row form-group">
							<div class="col-sm-6">
								<label for="address_line1">Address line 1</label>
								<input type="text" class="form-control" name="address_line1" id="address_line1"
									value="<?php echo address_line1_html(); ?>">
							</div>
							<div class="col-sm-6">
								<label for="address_line2">Address line 2</label>
								<input type="text" class="form-control" name="address_line2" id="address_line2"
									value="<?php echo address_line2_html(); ?>">
							</div>
						</div>
						<div class="row form-group">
							<div class="col-sm-3">
								<label for="city">City</label>
								<input type="text" class="form-control" name="city" id="city"
									value="<?php echo city_html(); ?>">
							</div>
							<div class="col-sm-3">
								<label for="state">State</label>
								<input type="text" class="form-control" name="state" id="state"
									value="<?php echo state_html(); ?>">
							</div>
							<div class="col-sm-3">
								<label for="country">Country</label>
								<input type="text" class="form-control" name="country" id="country"
									value="<?php echo country_html(); ?>">
							</div>
							<div class="col-sm-3">
								<label for="pincode">Pincode</label>
								<input type="text" class="form-control" name="pincode" id="pincode"
									value="<?php echo pincode_html(); ?>">
							</div>
						</div>
					</div>
				</div>

				<div class="row form-group">
					<div class="col-sm-12">
						<button type="submit" class="btn btn-primary pull-right"><i class="fa fa-save"></i> Save settings</button>
					</div>
				</div>
			</form>
		</div>
	</div>

<?php include "inc/footer.php"; ?>

</body>
</html>
