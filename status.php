<?php
include_once("inc/session.php");

function rejection_text($notifications) {
	global $DBCON;
	static $rejection_reasons = [];

	if (sizeof($rejection_reasons) == 0) {
		$sql  = "SELECT template_code, template_name ";
		$sql .= "  FROM email_template ";
		$sql .= " WHERE template_type = 'user_notification' ";
		$sql .= "   AND will_cause_rejection = '1' ";
		$qntf = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		$rejection_reasons = array();
		while ($rntf = mysqli_fetch_array($qntf))
			$rejection_reasons[$rntf['template_code']] = $rntf['template_name'];

	}

	$notification_list = explode("|", $notifications);
	$rejection_text = "";
	foreach ($notification_list AS $notification) {
		if ($notification != "") {
			list($notification_date, $notification_code_str) = explode(":", $notification);
			$notification_codes = explode(",", $notification_code_str);
			$rejected = false;
			foreach ($notification_codes as $notification_code)
				if (isset($rejection_reasons[$notification_code])) {
					$rejection_text .= (($rejection_text == "") ? "" : ",") . $rejection_reasons[$notification_code];
				}
		}
	}
	return $rejection_text;
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include_once("inc/header.php"); ?>
    <link rel="stylesheet" href="plugin/datatable/css/dataTables.bootstrap.min.css" />
</head>

<body class="<?php echo THEME;?>">

    <?php include_once("inc/navbar.php") ;?>
    <div class="wrapper">
		<!-- Jumbotron -->
		<?php  include_once("inc/Slideshow.php") ;?>
		<!-- Slideshow -->

		<div class="container-fluid intro">
			<div class="row">
				<!-- LEFT COLUMN -->
				<div class="col-sm-8 col-md-8 col-lg-8" style="padding-left:3%">
					<h2 class="headline first-child text-color">
						<span class="border-color">Status of Submissions</span>
					</h2>
                    <!-- Summary -->
                    <div class="row">
                        <div class="col-sm-12" style="padding-left: 40px;">
                            <h3 class="headline text-color">Participation Summary</h3>
                            <table class="table table-striped table-bordered">
                                <tr><th>Section</th><th>Participants</th><th>Uploads</th></tr>
                    <?php
                        // Compute Totalsst
                        $sql  = "SELECT pic.section, COUNT(DISTINCT profile_id) AS num_participants, COUNT(*) AS num_uploads ";
                        $sql .= "  FROM pic, section ";
                        $sql .= " WHERE pic.yearmonth = '$contest_yearmonth' ";
                        $sql .= "   AND section.yearmonth = pic.yearmonth ";
                        $sql .= "   AND section.section = pic.section ";
                        $sql .= " GROUP BY pic.section ";
                        $sql .= " ORDER BY section_sequence ";
                        $query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
                        while ($row = mysqli_fetch_array($query)) {
                    ?>
                                <tr><td><?= $row['section'];?></td><td><?= $row['num_participants'];?></td><td><?= $row['num_uploads'];?></td></tr>
                    <?php
                        }
                        // Compute Totals
                        $sql  = "SELECT COUNT(DISTINCT profile_id) AS num_participants, COUNT(*) AS num_uploads ";
                        $sql .= "  FROM pic ";
                        $sql .= " WHERE yearmonth = '$contest_yearmonth' ";
                        $query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
                        $row = mysqli_fetch_array($query);
                    ?>
                                <tr><td><b>TOTAL</b></td><td><b><?= $row['num_participants'];?></b></td><td><b><?= $row['num_uploads'];?></b></td></tr>
                    <?php
                    ?>
                            </table>
                        </div>
                    </div>
                    <!-- List -->
                    <div class="row">
                        <div class="col-sm-12" style="padding-left: 40px;">
                            <h3 class="headline text-color">Participant Status</h3>
                            <table id="status-table" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th rowspan="2">PARTICIPANT</th>
                                    <th rowspan="2">COUNTRY</th>
                                    <th colspan="<?= sizeof($contestSectionList);?>" class="text-center">UPLOADS</th>
                                    <th rowspan="2" class="text-center">**PENDING<br>ACTIONS? <!--<span class="small text-seconday">*</span>--></th></tr>
                                <tr>
                                <?php
                                    $sql = "SELECT section FROM section WHERE yearmonth = '$contest_yearmonth' ORDER BY section_sequence";
                                    $query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
                                    while ($section = mysqli_fetch_array($query)) {
                                ?>
                                    <th class="text-center"><?= $section['section'];?></th>
                                <?php
                                    }
                                ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    $sql  = "SELECT profile.profile_id, profile_name, honors, yps_login_id, IFNULL(club_name, '') AS club_name, country.country_name as country ";
                                    $sql .= "  FROM profile LEFT JOIN club ON club.club_id = profile.club_id ";
                                    $sql .= "LEFT JOIN country ON country.country_id = profile.country_id ";
                                    $sql .= " WHERE profile.profile_id IN (SELECT DISTINCT profile_id FROM pic WHERE yearmonth = '$contest_yearmonth') ";
                                    $sql .= " ORDER BY profile_name ";
                                    $query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
                                    while ($pr = mysqli_fetch_array($query)) {
                                        $name = $pr['profile_name'];
                                        if ($pr['honors'] != "")
                                            $name = $name . "<br><span class='text-muted'><small><small>" . $pr['honors'] . "</small></small></span>";
                                        if ($pr['yps_login_id'] != "")
                                            $name = $name . "<br><span class='text-muted'><small><small>" . "Club: Youth Photographic Society (YPS)" . "</small></small></span>";
                                        elseif ($pr['club_name'] != "")
                                            $name = $name . "<br><span class='text-muted'><small><small>" . "Club: " . $pr['club_name'] . "</small></small></span>";
                                ?>
                                <tr>
                                    <td>
                                        <?= $name;?>
                                    </td>
                                    <td><?= $pr['country'];?></td>
                                    <?php
                                        $profile_id = $pr['profile_id'];
                                        $sql  = "SELECT section.section, IFNULL(COUNT(pic.pic_id), 0) AS num_uploads ";
                                        // $sql .= "       SUM(CASE WHEN pic.notifications = '' OR pic.notifications IS NULL THEN 0 ELSE 1 END) AS num_notifications ";
                                        $sql .= "  FROM section LEFT JOIN pic ";
                                        $sql .= "        ON pic.yearmonth = section.yearmonth ";
                                        $sql .= "       AND pic.section = section.section ";
                                        $sql .= "       AND pic.profile_id = '$profile_id' ";
                                        $sql .= " WHERE section.yearmonth = '$contest_yearmonth' ";
                                        $sql .= " GROUP BY section.section ";
                                        $sql .= " ORDER BY section_sequence ";
                                        $subq = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
                                        while ($pic = mysqli_fetch_array($subq)) {
                                            //$notifications += $pic['num_notifications'];
                                    ?>
                                    <td class="text-center"><?= $pic['num_uploads'] == 0 ? "" : $pic['num_uploads'];?></td>
                                    <?php
                                        }
										// Check for Notificatios
										$notifications = 0;
										$sql  = "SELECT notifications FROM pic ";
										$sql .= " WHERE yearmonth = '$contest_yearmonth' ";
										$sql .= "   AND profile_id = '$profile_id' ";
										$subq = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
										while ($pic = mysqli_fetch_array($subq)) {
											$notifications +=  (rejection_text($pic['notifications']) == "" ? 0 : 1);
										}
                                    ?>
                                    <td class="text-center"><?= $notifications == 0 ? "" : "Yes";?></td>
                                </tr>
                                <?php
                                    }
                                ?>
                            </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <div style="text-align: justify; margin: 15px;">
                                <b><span class='text-danger'>PENDING ACTIONS</span></b> - Pictures uploaded are reviewed by YPS Review team
                                to help the participants submit qualifying pictures. If the review team identifies any compliance issues or
                                opportunities for improvement with the pictures uploaded, an email notification is generated to the
                                participant. If the participant does not address the issue by replacing the picture / title, the picture may
                                get rejected during the judging. If this column shows &quot;YES&quot;, it means that emails have been sent to
                                the participnt notifying corrective actions required. Please check your emails. Please write to us if you need
                                any assistance.
                            </div>
                        </div>
                    </div>
				</div>
				<!-- END OF LEFT COLUMN -->

				<!-- RIGHT COLUMN -->
				<div class="col-sm-4 col-md-4 col-lg-4"  >
					<!-- Show Login Form -->
					<?php include("inc/login_form.php");?>

					<!-- Start Count Down One Week before the last date -->
					<?php include("inc/countdown.php");?>

					<!-- Partners -->
					<?php
						if (file_exists("./salons/$contest_yearmonth/blob/partners.php"))
							include("./salons/$contest_yearmonth/blob/partners.php");
					?>

					<!-- Show Catalog Download / View Links after results are published -->
					<?php if ($contestHasCatalog && ! is_null($catalogReleaseDate) && DATE_IN_SUBMISSION_TIMEZONE >= $catalogReleaseDate)
							include("inc/catalogview.php");
					?>

		            <!-- Image Carousel -->
					<style>
					.carousel-inner img{ max-height:300px !important; }
					</style>
					<!--
					<h3 class="headline text-color">
						<span class="border-color">Slideshows</span>
					</h3>
					-->
			        <?php //include_once("inc/partners.php") ;?>
			        <?php include_once("inc/awards_column.php") ;?>
				</div>		<!-- END OF RIGHT SIDE -->
			</div>	<!-- row -->
			<!-- FOOTER -->
			<div class="row">
				<?php include_once("inc/footer.php") ;?>
			</div>
		</div> <!-- / .container -->
    </div> <!-- / .wrapper -->

    <!-- Style Toggle -->

	<?php include_once("inc/settingToggle.php") ;?>
    <!-- JavaScript
    ================================================== -->
	<?php include_once("inc/scripts.php"); ?>

    <!-- DataTables -->
    <script src="plugin/datatable/js/jquery.dataTables.min.js"></script>
    <script src="plugin/datatable/js/dataTables.bootstrap.min.js"></script>

	<script>
		$(document).ready(function() {

            $('#status-table').DataTable({
                ordering : false,
                pageLength : 25,
            });

			$("#login_login_id").hide();
			$("#check_it").attr("placeholder", "Email (or YPS Member ID)");
		});
	</script>
</body>

</html>
