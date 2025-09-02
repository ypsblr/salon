<?php
	// Computations
	// Check to see whether sponsorship opportunities are open
	$sql = "SELECT COUNT(*) AS number_of_opportunities FROM opportunity WHERE yearmonth = '$contest_yearmonth' ";
	$oppq = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$oppr = mysqli_fetch_array($oppq);
	$number_of_opportunities = $oppr['number_of_opportunities'];
	// Load Team info
	$sql = "SELECT * FROM team WHERE yearmonth = '$contest_yearmonth' ORDER BY role_name ";
	$teamq = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$salon_team = [];
	$salon_team_chairman = [];
	$salon_team_secretary = [];
	while ($row = mysqli_fetch_array($teamq, MYSQLI_ASSOC)) {
		$salon_team[] = $row;
		if ($row['role'] == 'Chairman')
			$salon_team_chairman = $row;
		if ($row['role'] == 'Secretary')
			$salon_team_secretary = $row;
	}

?>
<div class="panel panel-default">
	<div class="panel-heading">
		<h4 class="text-danger">QUICK GUIDE to <?=$contestName;?></h4>
	</div>
	<div class="panel-body">
		<div class="row">
			<div class="col-sm-8">
				<p>If you had participated in the YPS Salon in the past, your profile must already be available. You can login using your email and your last
					registered password. If you do not know the password, you can enter your email ID and click on <i>Reset Password</i> button.</p>
				<p>Members of Youth Photographic Society should login using email registered with YPS or using the YPS Member ID along with YPS Member password entered on YPS Website.
					If you do not remember the YPS Member password, you can visit <a href="https://www.ypsbengaluru.com" target="_blank">YPS Website</a>,
					reset member password using the <a href="https://www.ypsbengaluru.com/membership-login/password-reset/" target="_blank">Forgot Password?</a> link,
					and then login here using the password sent to you through email.</p>
				<p>The following restrictions are enforced on uploaded pictures.</p>
				<ol>
					<li>The <b>Maximum Width or Breadth or Horizontal Side or X Axis is <?= MAX_PIC_WIDTH;?> pixels</b></li>
					<li>The <b>Maximum Height or Vertical Side or Y Axis is <?= MAX_PIC_HEIGHT;?> pixels</b></li>
					<li>The <b>Maximum File Size is <?= MAX_PIC_FILE_SIZE_IN_MB;?> MB</b></li>
					<li>There are no specific DPI requirements.</li>
				</ol>
				<p>Registration closes at Midnight <?php echo $submissionTimezoneName;?> on <b><?php echo date("M j, Y", strtotime($registrationLastDate)); ?></b>. </p><br>
				<p>Please read <a href="term_condition.php">Terms &amp; Conditions</a> for restrictions related to uploaded pictures.</p>
				<p><b>Take me to</b></p>
				<ul class="list-group list-inline">
					<!-- <li class="list-group-item"><a href="#entrant_categories">Who can participate?</a></li> -->
					<li class="list-group-item"><a href="#index-sections">Sections</a></li>
					<li class="list-group-item"><a href="#index-dates">Calendar</a></li>
					<li class="list-group-item"><a href="jury.php">Jury</a></li>
					<li class="list-group-item"><a href="awards.php">Awards</a></li>
				<?php
					if ($number_of_opportunities > 0) {
				?>
					<li class="list-group-item"><a href="sponsor.php">Sponsorship Opportunities</a></li>
				<?php
					}
				?>
					<li class="list-group-item"><a href="term_condition.php">Terms &amp; Conditions</a></li>
					<li class="list-group-item"><a href="https://www.ypsbengaluru.com/membership-join/" target="_blank">Join YPS</a></li>
				</ul>
			</div>
			<div class="col-sm-4">
				<p><big><b>Video Guide for All</b></big></p>
				<a data-toggle="modal" data-target="#video-non-member" href="#">
					<img src="/video/video_non_member.gif" style="max-width: 100%;">
				</a>
				<hr>
				<p><big><b>Video Guide for YPS Members</b></big></p>
				<a data-toggle="modal" data-target="#video-member" href="#">
					<img src="/video/video_member.gif" style="max-width: 100%;">
				</a>
				<!-- How-to Video - Members -->
				<div class="modal" id="video-member" tabindex="-1" role="dialog" aria-labelledby="video-header-label">
					<div class="modal-dialog modal-lg" role="document">
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="pauseVideo('video-member')" >
									<span aria-hidden="true">&times;</span>
								</button>
								<h4 class="modal-title">Quick Guide for YPS Members</h4>
							</div>
							<div class="modal-body">
								<div class="embed-responsive embed-responsive-16by9">
									<video controls>
										<source src="/video/video_member.mp4" type="video/mp4">
										Your browser does not support mp4 video
									</video>
								</div>
							</div>
						</div>
					</div>
				</div>
				<!-- How-to Video - Non-Members -->
				<div class="modal" id="video-non-member" tabindex="-1" role="dialog" aria-labelledby="video-header-label">
					<div class="modal-dialog modal-lg" role="document">
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="pauseVideo('video-non-member')" >
									<span aria-hidden="true">&times;</span>
								</button>
								<h4 class="modal-title">Quick Guide for General Participants</h4>
							</div>
							<div class="modal-body">
								<div class="embed-responsive embed-responsive-16by9">
									<video controls>
										<source src="/video/video_non_member.mp4" type="video/mp4">
										Your browser does not support mp4 video
									</video>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- Chairman & Secretary -->
		<div class="row">
			<div class="col-sm-6" style="padding-right: 8px;">
				<div class="row">
					<p style="padding-left: 15px;"><b><?=$salon_team_chairman['role_name'];?>:</b></p>
					<div class="col-sm-2">
						<img src="/salons/<?=$contest_yearmonth;?>/img/com/<?=$salon_team_chairman['avatar'];?>" style="height: 60px; border: 1px solid #CCC;">
					</div>
					<div class="col-sm-10">

						<div style="margin-left: 20px;">
							<?=$salon_team_chairman['member_name'];?>
							<?php echo ($salon_team_chairman['honors'] != "") ? "<br><i><small>" . $salon_team_chairman['honors'] . "</small></i>" : "";?>
							<?php echo ($salon_team_chairman['phone'] != "") ? "<br><i class='fa fa-phone'></i> " . $salon_team_chairman['phone'] : "";?>
							<?php echo ($salon_team_chairman['email'] != "") ? "<br><i class='fa fa-at'></i> " . $salon_team_chairman['email'] : "";?>
						</div>
					</div>
				</div>
			</div>
			<div class="col-sm-6" style="padding-left: 8px;">
				<div class="row">
					<p style="padding-left: 15px;"><b><?=$salon_team_secretary['role_name'];?>:</b></p>
					<div class="col-sm-2">
						<img src="/salons/<?=$contest_yearmonth;?>/img/com/<?=$salon_team_secretary['avatar'];?>" style="height: 60px; border: 1px solid #CCC;">
					</div>
					<div class="col-sm-10">
						<div style="margin-left: 20px;">
							<?=$salon_team_secretary['member_name'];?>
							<?php echo ($salon_team_secretary['honors'] != "") ? "<br><i><small>" . $salon_team_secretary['honors'] . "</small></i>" : "";?>
							<?php echo ($salon_team_secretary['phone'] != "") ? "<br><i class='fa fa-phone'></i> " . $salon_team_secretary['phone'] : "";?>
							<?php echo ($salon_team_secretary['email'] != "") ? "<br><i class='fa fa-at'></i> " . $salon_team_secretary['email'] : "";?>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Salon Committee -->
		<div class="row">
			<p>
  				<a data-toggle="collapse" href="#salon-committee" aria-expanded="false" aria-controls="salon-committee" style="padding-left: 15px;">
    				<b><span class="text-info">Salon Committee &gt;</span></b>
  				</a>
			</p>
			<div class="collapse" id="salon-committee">
			<?php
				$idx = 0;
				foreach($salon_team as $salon_team_member) {
					if ($salon_team_member['member_group'] == "Salon Committee") {
						if ($salon_team_member['role'] != "Chairman" && $salon_team_member['role'] != "Secretary") {
			?>
				<div class="col-sm-4" style="margin-top: 15px;">
					<div class="row">
						<div class="col-sm-3">
							<img src="/salons/<?=$contest_yearmonth;?>/img/com/<?=$salon_team_member['avatar'];?>" style="height: 40px; border: 1px solid #CCC;">
						</div>
						<div class="col-sm-9">
							<div>
								<b><?= $salon_team_member['role_name']; ?></b><br>
								<?= $salon_team_member['member_name'];?>
								<?= ($salon_team_member['honors'] != "") ? "<br><i><small>" . $salon_team_member['honors'] . "</small></i>" : "";?>
							</div>
						</div>
					</div>
				</div>
			<?php
							++ $idx;
							if (($idx % 3) == 0) {
			?>
				<div class="clearfix"></div>
			<?php
							}
						}
					}
				}
			?>
			</div>
		</div>


		<!-- External Support -->
		<div class="row">
			<p>
  				<a data-toggle="collapse" href="#salon-support" aria-expanded="false" aria-controls="salon-support" style="padding-left: 15px;">
    				<b><span class="text-info">External Support &gt;</span></b>
  				</a>
			</p>
			<div class="collapse" id="salon-support">
			<?php
				$idx = 0;
				foreach($salon_team as $salon_team_member) {
					if ($salon_team_member['member_group'] == "External Support") {
						if ($salon_team_member['role'] != "Chairman" && $salon_team_member['role'] != "Secretary") {
			?>
				<div class="col-sm-4" style="margin-top: 15px;">
					<div class="row">
						<div class="col-sm-3">
							<img src="/salons/<?=$contest_yearmonth;?>/img/com/<?=$salon_team_member['avatar'];?>" style="height: 40px; border: 1px solid #CCC;">
						</div>
						<div class="col-sm-9">
							<div>
								<b><?= $salon_team_member['role_name']; ?></b><br>
								<?= $salon_team_member['member_name'];?>
								<?= ($salon_team_member['honors'] != "") ? "<br><i><small>" . $salon_team_member['honors'] . "</small></i>" : "";?>
							</div>
						</div>
					</div>
				</div>
			<?php
							++ $idx;
							if (($idx % 3) == 0) {
			?>
				<div class="clearfix"></div>
			<?php
							}
						}
					}
				}
			?>
			</div>
		</div>

	</div>
</div>
