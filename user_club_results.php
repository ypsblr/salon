<?php
include_once("inc/session.php");
include_once("inc/categories.php");
include_once("inc/user_lib.php");

if(empty($_SESSION['USER_ID']))
	handle_error("Must be logged in to use this feature", __FILE__, __LINE__);

if (! $resultsReady)
	handle_error("Results are yet to be published for " . $contestName, __FILE__, __LINE__);

?>
<!DOCTYPE html>
<html lang="en">

<head>
<?php include_once("inc/header.php"); ?>

<style type="text/css">

#div1,#div2,#div3 {
display: none;
}

</style>

<script src="http://code.jquery.com/jquery-1.5.js"></script>

</head>

<body class="<?php echo THEME;?>">
	<?php include_once("inc/navbar.php") ;?>
    <div class="wrapper" style="padding-bottom:0px">
    <?php  include_once("inc/Slideshow.php") ;?>

		<div class="container">
			<div class="row">
				<div class="col-sm-3">
					<?php include("inc/user_sidemenu.php");?>
				</div>

				<div class="col-sm-9" id="myTab">
					<div class="row">
						<div class="col-sm-12">
							<h3 class="text-color" style="padding-bottom: 20px;">My Club Summary</h3>
                            <div class="row">
                                <div class="col-sm-1">
                                    <img class="img-responsive" src="/res/club/<?=$tr_user['club_logo'];?>" >
                                </div>
                                <div class="col-sm-10">
                                    <p class="lead"><?=$tr_user['club_name'];?></p>
                                </div>
                            </div>
                            <div class="row" style="padding-top: 20px;">
                            <?php
                                $sql  = "SELECT COUNT(*) AS participants, SUM(entry.uploads) AS uploads, ";
                                $sql .= "       SUM(entry.awards) AS awards, SUM(entry.hms) AS hms, SUM(entry.acceptances) AS acceptances ";
                                $sql .= "  FROM entry, profile ";
                                $sql .= " WHERE entry.yearmonth = '$contest_yearmonth' ";
                                $sql .= "   AND profile.profile_id = entry.profile_id ";
                                $sql .= "   AND profile.club_id = '" . $tr_user['club_id'] . "' ";
                                $qsum = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
                                $rsum = mysqli_fetch_array($qsum);
                            ?>
                                <table class="table table-striped table-bordered">
                                <tbody>
                                    <tr><td>Number of Participants</td><td><b><?=$rsum['participants'];?> participants</b></td></tr>
                                    <tr><td>Number of Pictures</td><td><b><?=$rsum['uploads'];?> pictures</b></td></tr>
								<?php
                                    if ($rsum['acceptances'] > 0) {
                                ?>
                                    <tr><td>Number of Acceptances</td><td><b><?=$rsum['acceptances'] + $rsum['awards'] + $rsum['hms'];?></b></td></tr>
                                <?php
                                    }
                                ?>
                                <?php
                                    if ($rsum['awards'] > 0) {
                                ?>
                                    <tr><td>Number of Awards</td><td><b><?=$rsum['awards'];?></b></td></tr>
                                <?php
                                    }
                                ?>
                                <?php
                                    if ($rsum['hms'] > 0) {
                                ?>
                                    <tr><td>Number of Honorable Mentions</td><td><b><?=$rsum['hms'];?></b></td></tr>
                                <?php
                                    }
                                ?>
                                </tbody>
                                </table>
                            </div>
                            <!-- Display Awards by Club -->
                            <div class="row">
                            <?php
                                $sql  = "SELECT pic.title, pic.picfile, award.section, award.award_name, ";
                                $sql .= "       profile.profile_name, profile.honors, profile.avatar ";
                                $sql .= "  FROM pic_result, award, pic, profile ";
                                $sql .= " WHERE pic_result.yearmonth = '$contest_yearmonth' ";
                                $sql .= "   AND award.yearmonth = pic_result.yearmonth ";
                                $sql .= "   AND award.award_id = pic_result.award_id ";
                                $sql .= "   AND award.level < 99 ";
                                $sql .= "   AND pic.yearmonth = pic_result.yearmonth ";
                                $sql .= "   AND pic.profile_id = pic_result.profile_id ";
                                $sql .= "   AND pic.pic_id = pic_result.pic_id ";
                                $sql .= "   AND profile.profile_id = pic_result.profile_id ";
                                $sql .= "   AND profile.club_id = '" . $tr_user['club_id'] . "' ";
                                $sql .= " ORDER BY award.level, award.sequence ";
                                $qres = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
                                if (mysqli_num_rows($qres) > 0) {
                            ?>
                                <br>
                                <h3 class="text-color" style="padding-bottom: 20px;">Awards and Honorable Mentions</h3>
                            <?php
                                    while ($rres = mysqli_fetch_array($qres)) {
                            ?>
                                <div class="col-sm-3">
                                    <b><?=$rres['section'];?></b>
                                    <br>
                                    <big><?=$rres['award_name'];?></big>
                                    <br><br>
                                    <i><?=$rres['title'];?></i>
                                </div>
                                <div class="col-sm-3">
                                    <img class="img-responsive" src="/salons/<?=$contest_yearmonth;?>/upload/<?=$rres['section'];?>/tn/<?=$rres['picfile'];?>" >
                                </div>
                                <div class="col-sm-6">
                                    <div class="row">
                                        <div class="col-sm-3"><img class="img-responsive" src="/res/avatar/<?=$rres['avatar'];?>"></div>
                                        <div class="col-sm-9">
                                            <big><?=$rres['profile_name'];?></big>
                                            <br>
                                            <small><?=$rres['honors'];?></small>
                                            <br>
                                        </div>
                                    </div>
                                </div>
								<div class="clearfix"></div>
                            <?php
                                   }
                                }
                            ?>
                            </div>
							<h3 class="text-color" style="padding-bottom: 20px;">Member Contribution</h3>
                            <div class="row">
                                <table class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th width="100"><p>Avatar</p></th>
                                            <th><p>Name</p></th>
                                            <th><p class="text-center">Uploads</p></th>
											<th><p class="text-center">Acceptances</p></th>
                                            <th><p class="text-center">Awards</p></th>
                                            <th><p class="text-center">HMs</p></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                            <?php
                                $sql  = "SELECT profile.profile_name, profile.honors, profile.avatar, entry.uploads, ";
                                $sql .= "       entry.awards, entry.hms, entry.acceptances ";
                                $sql .= "  FROM entry, profile ";
                                $sql .= " WHERE entry.yearmonth = '$contest_yearmonth' ";
                                $sql .= "   AND profile.profile_id = entry.profile_id ";
                                $sql .= "   AND profile.club_id = '" . $tr_user['club_id'] . "' ";
                                $sql .= " ORDER BY profile.profile_name ";
                                $qmem = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
                                while ($rmem = mysqli_fetch_array($qmem)) {
                            ?>
                                        <tr>
                                            <td width="100"><img class="img-responsive"src="/res/avatar/<?=$rmem['avatar'];?>" ></td>
                                            <td><b><?=$rmem['profile_name'];?></b><br><small><?=$rmem['honors'];?></small></td>
                                            <td><p class="text-center"><?=$rmem['uploads'];?></p></td>
											<td><p class="text-center"><?=$rmem['acceptances'] + $rmem['awards'] + $rmem['hms'];?></p></td>
                                            <td><p class="text-center"><?=$rmem['awards'];?></p></td>
                                            <td><p class="text-center"><?=$rmem['hms'];?></p></td>
                                        </tr>
                            <?php
                                }
                            ?>
                                    </tbody>
                                </table>
                            </div>
						</div>
					</div>
				</div>			<!-- My Tab -->
			</div> <!-- / .row -->
		</div> <!-- / .container -->

    <!-- Footer -->
	<?php include_once("inc/footer.php") ;?>

    <!-- Style Toggle -->
	<?php include_once("inc/settingToggle.php") ;?>

    <!-- JavaScript -->
	<?php include_once("inc/scripts.php"); ?>

	<script>
		$(document).ready(function(){
			$('html, body').animate({ scrollTop: $("#myTab").offset().top-100 }, 500);
		});
    </script>
</body>
</html>
