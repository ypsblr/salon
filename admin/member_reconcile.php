<?php
//
// Reconcile Member Details with YPS records and update
//
include("inc/session.php");
include("inc/connect.php");
include("inc/lib.php");

set_time_limit(300);


// YPS Get list of Users
// Returns an array of login, first_name, last_name, email
function yps_users() {

	global $CON;

	$yps_users = array();

	// Invoke YPS Authentication Service
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "http://ypsbengaluru.com/svc/getuserlist.php");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HEADER, false);
	$post_fields = array();
	$post_fields["magic"] = "ypsmagic1971onwards";
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);

	$result = json_decode(curl_exec($ch), true);
	debug_dump("Members", $result, __FILE__, __LINE__);
	debug_dump("Error", curl_error($ch), __FILE__, __LINE__);

	if ($result && $result['status'] == "OK") {
		$err_msg = "";
		$user_list = $result['user_list'];
	}
	else {
		$err_msg = ($result ? $result['errmsg'] : "Data Fetch failed");
		$user_list = array();
	}

	return array($err_msg, $user_list);
}

function make_login_list($member_list) {
	$login_list = [];

	foreach ($member_list as $member)
		$login_list[] = "'" . strtoupper($member['login']) . "'";

	if (sizeof($login_list) == 0)
		return "NOTHING_TO_MATCH";
	else
		return (implode(",", $login_list));
}

function find_login_id($email, $member_list) {
	foreach ($member_list as $member) {
		if (strtolower($member['email']) == strtolower($email))
			return $member['login'];
	}
	return false;
}

if ( ! empty($_SESSION['admin_id']) && ! empty($_SESSION['admin_yearmonth']) ) {
	// Set up user and contest information
	include ("inc/load_common_data.php");

	// Get list of YPS Users
	list ($errmsg, $member_list) = yps_users();
	if ($errmsg != "")
		return_error($errmsg, __FILE__, __LINE__);

	// Prepare Quoted login_id list for reverse comparison
	$yps_login_list = make_login_list($member_list);

?>
<!DOCTYPE html>
<html>
<head>
    <!-- Page title -->
	<title>Youth Photographic Society | Member Reconciliation</title>

	<style>
		.group-heading, .column-heading {
			border-bottom : 1px solid #ddd;
			text-align : center;
		}
		.value-changed {
			font-weight: bold;
		}
	</style>
	<?php include "inc/header.php"; ?>

</head>
<body class="fixed-navbar fixed-sidebar">

<!-- Simple splash screen-->
<div class="splash">
	<div class="color-line"></div>
	<div class="splash-title">
		<h1>   YPS ADMIN PANEL  </h1>
		<p>Please Wait. </p>
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
    <div class="content">
        <div class="row">
            <div class="col-lg-12 text-center m-t-md">
                <h2>
                    Member Reconciliation <?= $admin_contest_name;?>
                </h2>
            </div>
        </div>

		<!-- Header Line 1 -->
		<div class="row">
			<div class="col-sm-2"></div>
			<div class="col-sm-2 group-heading"><b>Membership Change</b></div>
			<div class="col-sm-4 group-heading"><b>Email Change</b></div>
			<div class="col-sm-2">Phone Change</div>
			<div class="col-sm-2"></div>
		</div>
		<!-- Header Line 2 -->
		<div class="row">
			<div class="col-sm-2 column-heading" style="text-align: left;">Profile Name</div>
			<div class="col-sm-1 column-heading"><b>YPS</b></div>
			<div class="col-sm-1 column-heading"><b>Salon</b></div>
			<div class="col-sm-2 column-heading"><b>YPS</b></div>
			<div class="col-sm-2 column-heading"><b>Salon</b></div>
			<div class="col-sm-1 column-heading"><b>YPS</b></div>
			<div class="col-sm-1 column-heading"><b>Salon</b></div>
			<div class="col-sm-1 column-heading"><b>Actions</b></div>
			<div class="col-sm-1 column-heading"><b>Status</b></div>
		</div>
		<!-- Data Rows -->
		<?php
			// Process each member received from YPS and compare with profiles
			foreach( $member_list as $member ) {
				$member_email = $member['email'];
				$member_login_id = $member['login'];
				$member_phone = $member['phone'];
				$sql  = "SELECT profile_id, profile_name, email, phone, yps_login_id ";
				$sql .= "  FROM profile ";
				$sql .= " WHERE (email = '$member_email' ";
				$sql .= "        OR phone = '$member_phone' ";
				$sql .= "        OR yps_login_id = '" . strtoupper($member_login_id) . "') ";
				$sql .= "   AND profile_disabled = '0' ";
				$sql .= "   AND profile_merged_with = '0' ";
				$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
				if (mysqli_num_rows($query) == 1) {
					$row = mysqli_fetch_array($query);
					$id_changed = strtoupper($row['yps_login_id']) != strtoupper($member_login_id);
					$email_changed = strtolower($row['email']) != strtolower($member_email);
					$phone_changed = strtolower($row['phone']) != strtolower($member_phone);
					$id_class = $id_changed ? "value-changed" : "";
					$email_class = $email_changed ? "value-changed" : "";
					$phone_class = $phone_changed ? "value-changed" : "";
					if ( $id_changed || $email_changed ) {
		?>
						<div class="row" style="margin-bottom: 8px;">
							<div class="col-sm-2"><?= $row['profile_name'];?> (<?= $row['profile_id'];?>)</div>
							<div class="col-sm-1 <?= $id_class;?>"><?= $member_login_id;?></div>
							<div class="col-sm-1 <?= $id_class;?>"><?= $row['yps_login_id'];?></div>
							<div class="col-sm-2 <?= $email_class;?>"><?= $member_email;?></div>
							<div class="col-sm-2 <?= $email_class;?>"><?= $row['email'];?></div>
							<div class="col-sm-1 <?= $email_class;?>"><?= $member_phone;?></div>
							<div class="col-sm-1 <?= $email_class;?>"><?= $row['phone'];?></div>
							<div class="col-sm-1">
								<a class="btn btn-info" href="javascript:void(0)"
									onclick="update_profile('<?= $row["profile_id"];?>', '<?= strtoupper($member_login_id);?>', '<?= $member_email;?>')"
								>Update Salon</a>
							</div>
							<div class="col-sm-1">
								<span id="status-<?= $row['profile_id'];?>"></span>
							</div>
						</div>
		<?php
					}	// if ID or email chaged
				}	// if profile found
			}	// for each member

			// Check for profiles with yps_login_id that is no more in the yps list (possibly account is no more active)
			$sql  = "SELECT profile_id, profile_name, email, phone, yps_login_id ";
			$sql .= "  FROM profile ";
			$sql .= " WHERE yps_login_id != '' ";
			$sql .= "   AND yps_login_id NOT IN (" . $yps_login_list . ") ";
			$sql .= "   AND profile_disabled = '0' ";
			$sql .= "   AND profile_merged_with = '0' ";
			$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
			while ($row = mysqli_fetch_array($query)) {
				// Check if the Member ID has changed (e.g. has become Life Member)
				if ( ! ($new_yps_login_id = find_login_id($row['email'], $member_list))) {
		?>
			<div class="row" style="margin-bottom: 8px;">
				<div class="col-sm-2"><?= $row['profile_name'];?> (<?= $row['profile_id'];?>)</div>
				<div class="col-sm-1 value-changed">Expired</div>
				<div class="col-sm-1 value-changed"><?= $row['yps_login_id'];?></div>
				<div class="col-sm-2"></div>
				<div class="col-sm-2 value-changed"><?= $row['email'];?></div>
				<div class="col-sm-1"></div>
				<div class="col-sm-1 value-changed"><?= $row['phone'];?></div>
				<div class="col-sm-1">
					<a class="btn btn-warning" href="javascript:void(0)"
						onclick="update_profile('<?= $row["profile_id"];?>', 'NONE', 'NONE')"
					>Make General</a>
				</div>
				<div class="col-sm-1">
					<span id="status-<?= $row['profile_id'];?>"></span>
				</div>
			</div>
		<?php
				}	// if there is no changed member id
			}	// while there are profiles with yps_login_id that are no more active
		?>
    </div>
	<?php include "inc/profile_modal.php";?>

</div>

<?php
include("inc/footer.php");
?>

<!-- Vendor scripts -->
<script src="plugin/jquery-flot/jquery.flot.js"></script>
<script src="plugin/jquery-flot/jquery.flot.resize.js"></script>
<script src="plugin/jquery-flot/jquery.flot.pie.js"></script>
<script src="plugin/flot.curvedlines/curvedLines.js"></script>
<script src="plugin/jquery.flot.spline/index.js"></script>
<script src="plugin/peity/jquery.peity.min.js"></script>
<script src="plugin/swal/js/sweetalert.min.js"></script>

<!-- Update Profile -->
<script>

function update_profile(profile_id, yps_login_id, email) {

	$("#status-" + profile_id).html("");

	$.post(
		"ajax/reconcile_update.php",
		{ profile_id, yps_login_id, email },
		function (response) {
			let data = JSON.parse(response);
			if (data.success) {
				$("#status-" + profile_id).html("DONE");
			}
			else {
				$("#status-" + profile_id).html(data.msg);
			}
		}
	)
}

</script>


</body>

</html>

<?php
}
else
{
	$_SESSION['err_msg'] = "Use ID with require permission not found !";
	header("Location: index.php");
	printf("<script>location.href='index.php'</script>");
}

?>
