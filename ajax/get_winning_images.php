<?php
session_save_path(__DIR__ . "/../inc/session");
session_start();
include("../inc/connect.php");
include("ajax_lib.php");

function return_error($errmsg) {
	global $column;
?>
	<div class="col-sm-12 album-insert" style="padding-top: 8px; background-color : #fcf8e3; border: 0.5px solid #faebcc; border-radius: 8px; " >
		<div class="row">
			<div class="col-sm-4 text-center"><?= $column == 0 ? "<i class='fa fa-chevron-up lead text-color'></i>" : "";?></div>
			<div class="col-sm-4 text-center"><?= $column == 1 ? "<i class='fa fa-chevron-up lead text-color'></i>" : "";?></div>
			<div class="col-sm-4 text-center"><?= $column == 2 ? "<i class='fa fa-chevron-up lead text-color'></i>" : "";?></div>
		</div>
		<div style="height: 20px; text-align: center;" class="lead"><?= $errmsg;?></div>
	</div>
	<div class="clearfix album-insert">
<?php
	die();
}

function return_sql_error($sql, $errmsg, $phpfile, $phpline) {
	log_sql_error($sql, $errmsg, $phpfile, $phpline);
	return_error("Data operation failed. Report to YPS");
}

function video_file($profile_id, $pic_id) {
	global $video_folder;
	global $member_videos;

	foreach ($member_videos as $video) {
		if ($video['profile_id'] == $profile_id && $video['pic_id'] == $pic_id) {
			return $video_folder . $video['video'];
		}
	}
	return false;
}


if (isset($_REQUEST['yearmonth']) && isset($_REQUEST['profile_id']) && isset($_REQUEST['column']) ) {

	$yearmonth = $_REQUEST['yearmonth'];
	$profile_id = $_REQUEST['profile_id'];
	$column = $_REQUEST['column'];

	$member_videos = [];
	if (file_exists("../salons/$yearmonth/blob/member_video.php"))
		include("../salons/$yearmonth/blob/member_video.php");

	$sql = "SELECT * FROM contest WHERE yearmonth = '$yearmonth' ";
	$query = mysqli_query($DBCON, $sql) or return_sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$contest = mysqli_fetch_array($query);
	$contest_archived = ($contest['archived'] == '1');

	$sql = "SELECT * FROM exhibition WHERE yearmonth = '$yearmonth' ";
	$query = mysqli_query($DBCON, $sql) or return_sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$exhibition = mysqli_fetch_array($query);

	$sql  = "SELECT pic.profile_id, pic.pic_id, pic.section, title, picfile, level, award_name ";
	if ($contest_archived)
		$sql .= "  FROM ar_pic_result pic_result, award, ar_pic pic ";
	else
		$sql .= "  FROM pic_result, award, pic ";
	$sql .= " WHERE pic_result.yearmonth = '$yearmonth' ";
	$sql .= "   AND pic_result.profile_id = '$profile_id' ";
	$sql .= "   AND pic.yearmonth = pic_result.yearmonth ";
	$sql .= "   AND pic.profile_id = pic_result.profile_id ";
	$sql .= "   AND pic.pic_id = pic_result.pic_id ";
	$sql .= "   AND award.yearmonth = pic_result.yearmonth ";
	$sql .= "   AND award.award_id = pic_result.award_id ";
	$sql .= " ORDER BY award.level ";
	$query = mysqli_query($DBCON, $sql) or return_sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_num_rows($query) > 0) {
?>
	<div class="col-sm-12 album-insert" style="padding-top: 8px; background-color : #fcf8e3; border: 0.5px solid #faebcc; border-radius: 8px; " >
		<div class="row">
			<div class="col-sm-4 text-center"><a onclick="hideWins()"><?= $column == 0 ? "<i class='fa fa-chevron-up lead text-color'></i>" : "";?></a></div>
			<div class="col-sm-4 text-center"><a onclick="hideWins()"><?= $column == 1 ? "<i class='fa fa-chevron-up lead text-color'></i>" : "";?></a></div>
			<div class="col-sm-4 text-center"><a onclick="hideWins()"><?= $column == 2 ? "<i class='fa fa-chevron-up lead text-color'></i>" : "";?></a></div>
		</div>
		<div class="row">
<?php
		for ($idx = 0; $row = mysqli_fetch_array($query, MYSQLI_ASSOC); ++$idx) {
			$pic_path = "/salons/$yearmonth/upload/" . $row['section'] . "/" . $row['picfile'];
			$tn_path = "/salons/$yearmonth/upload/" . $row['section'] . "/tn/" . $row['picfile'];
			$profile_id = $row['profile_id'];
			$pic_id = $row['pic_id'];
?>
			<div class="col-sm-3 thumbnail">
<?php
	if ($exhibition != false && $exhibition['virtual_tour_ready'] == 1 && ($video = video_file($profile_id, $pic_id))) {
?>
			<div class="caption" data-toggle="modal" data-target="#insertvideo-<?= $profile_id;?>-<?= $pic_id;?>"  style="width: 100%;">
				<?= $row['title'];?><div class="pull-right text-color"><i class="fa fa-video-camera"></i></div>
			</div>
			<!-- Modal Video -->
			<div class="modal" id="insertvideo-<?= $profile_id;?>-<?= $pic_id;?>" tabindex="-1" role="dialog" aria-labelledby="video-header-label">
				<div class="modal-dialog modal-lg" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="pauseVideo('insertvideo-<?= $profile_id;?>-<?= $pic_id;?>')" >
								<span aria-hidden="true">&times;</span>
							</button>
							<h4 class="modal-title" id="video-header-label"><i>On my picture</i> &quot;<?= $row['title'];?>&quot;</h4>
						</div>
						<div class="modal-body">
							<div class="embed-responsive embed-responsive-16by9">
								<video controls>
									<source src="<?= $video;?>" type="video/mp4">
									<source src="<?= $video;?>" type="video/mov">
									Your browser does not support mp4 video
								</video>
							</div>
						</div>
					</div>
				</div>
			</div>
<?php
	}
	else {
?>
				<div class="caption"><?=$row['title'];?></div>
<?php
	}
?>
				<div style="max-width:100%;" >
					<a href="<?=$pic_path;?>"
							data-lightbox="pic-insert"
							data-title='"<?=$row['title'];?>" winning <?= $row['section'];?> - <?= $row['award_name'];?>' >
						<img class="img-responsive" style="margin-left:auto; margin-right:auto;" src="<?= $tn_path;?>" >
					</a>
				</div>
				<div style="width: 100%; padding-left: 9px; color: #fff; background-color: #5bc0de;">
					<?= $row['section'] . " - " . $row['award_name'];?>
				</div>
			</div>
<?php
			if ((($idx + 1) % 4) == 0) {
?>
			<div class="clearfix"></div>
<?php
			}
		}
?>
		</div>
	</div>
	<div class="clearfix album-insert"></div>
<?php
	}
	else {
		return_error("No winning images found");
	}
}
else {
	return_error("Invalid Request");
}
?>
