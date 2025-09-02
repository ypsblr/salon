<?php
include_once("inc/session.php");
include_once("inc/categories.php");
include_once("inc/user_lib.php");

function rejection_text($notifications) {
	global $rejection_reasons;

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

function jury_notifications($profile_id, $pic_id) {
	global $DBCON;
	global $contest_yearmonth;
	global $contest_archived;

	$sql  = "SELECT yearmonth, MIN(rating) AS min_score, GROUP_CONCAT(DISTINCT tags SEPARATOR '|') AS jury_notifications ";
	if ($contest_archived)
		$sql .= "  FROM ar_rating rating ";
	else
		$sql .= "  FROM rating ";
	$sql .= " WHERE yearmonth = '$contest_yearmonth' ";
	$sql .= "   AND profile_id = '$profile_id' ";
	$sql .= "   AND pic_id = '$pic_id' ";
	$sql .= " GROUP BY yearmonth ";

	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if ($row = mysqli_fetch_array($query)) {
		if ($row['min_score'] == '1') {
			$notifications = explode("|", $row['jury_notifications']);
			$notification_list = [];
			foreach ($notifications as $notification) {
				if ($notification != "" && (! isset($notification_list[$notification])))
					$notification_list[$notification] = $notification;
			}
			return implode(", ", $notification_list);
		}
	}
	return "";
}


if(empty($_SESSION['USER_ID']))
	handle_error("Must be logged in to use this feature", __FILE__, __LINE__);

if (! $resultsReady)
	handle_error("Results are yet to be published for " . $contestName, __FILE__, __LINE__);

// GENERATE Share Image if within a month from date of announcement of results
// if ( date("Y-m-d") <= date("Y-m-d", strtotime($resultsDate . " +1 month")) && file_exists("salons/$contest_yearmonth/generate_share_image.php") ) {
if ( date("Y-m-d") <= date("Y-m-d", strtotime($resultsDate . " +1 month")) && file_exists("salons/$contest_yearmonth/blob/sharedef.json") ) {
	try {
		$si_yearmonth = $contest_yearmonth;
		$si_profile_id = $tr_user['profile_id'];
		include_once "inc/generate_share_image.php";
	}
	catch (Exception $e) {
		debug_dump("ShareImage Exception for " . $si_profile_id, $e, __FILE__, __LINE__);
	}
}

// Gather List of Rejection Reasons
// Get Notifications List
$sql  = "SELECT template_code, template_name ";
$sql .= "  FROM email_template ";
$sql .= " WHERE template_type = 'user_notification' ";
$qntf = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
$rejection_reasons = array();
while ($rntf = mysqli_fetch_array($qntf))
	$rejection_reasons[$rntf['template_code']] = $rntf['template_name'];

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

<script type="text/javascript">
/***
function showHide(elem) {
        document.getElementById('div1').style.display = 'block';
}

window.onload = function() {
    //get the divs to show/hide
    divsO = document.getElementById("frmMyform").getElementsByTagName('div');
}
***/
</script>

<!-- <script src="http://code.jquery.com/jquery-1.5.js"></script> -->

<script>
function countChar(val) {
	var len = val.value.length;
	if (len >= 30) {
		val.value = val.value.substring(0, 30);
	}
	else {
		$('#charNum').text(30 - len);
	}
}
</script>
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
					<!-- SHARE IMAGE -->
					<?php
						$sql  = "SELECT COUNT(*) AS num_results ";
						if ($contest_archived)
							$sql .= "  FROM ar_pic_result pic_result ";
						else
							$sql .= "  FROM pic_result ";
						$sql .= " WHERE yearmonth = '$contest_yearmonth' ";
						$sql .= "   AND profile_id = '" . $tr_user['profile_id'] . "' ";
						$qprs = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
						$rprs = mysqli_fetch_array($qprs);
						$poster_file = "salons/" . $contest_yearmonth . "/upload/share/" . sprintf("yps-poster-%04d.jpg", $tr_user['profile_id']);
						// $poster_generator = "salons/" . $contest_yearmonth . "/generate_share_image.php";
						if (file_exists($poster_file)) {
					?>
					<div class="row">
						<div class="col-sm-2"></div>
						<div class="col-sm-8">
							<div id="share-picture" style="text-align: center;">
								<h3 class="text-info text-left">Share on Social Media</h3>
								<a href="<?= $poster_file;?>" target="_blank" download>
									<img style="max-width: 100%; max-height: 100%;" src="<?= $poster_file;?>" >
								</a>
								<h3 class="text-info text-left">Remember to tag @ypsbengaluru</h3>
								<p class="pull-right text-color">Click on the image to download</p>
							</div>
						</div>
					</div>
					<?php
						}
					?>

					<!-- PATRONAGE DATA -->
					<?php
					// Generate table of patronage for PDF
						$sql = "SELECT * FROM recognition WHERE yearmonth = '$contest_yearmonth' ";
						$qry = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
						while ($recn = mysqli_fetch_array($qry)) {
					?>
						<input type="hidden" class="patronage-data"
								data-short-code="<?= $recn['short_code'];?>"
								data-organization-name="<?= $recn['organization_name'];?>"
								data-website="<?= $recn['website'];?>"
								data-recognition-id="<?= $recn['recognition_id'];?>"
								data-logo="<?= $recn['logo'];?>"  >
					<?php
						}
					?>

					<div class="row">
						<div class="col-sm-6"></div>
						<div class="col-sm-3">
						<?php
							// if ($rprs['num_results'] > 0 && $certificatesReady && file_exists("salons/" . $contest_yearmonth . "/certdef.php") ) {
							if ($rprs['num_results'] > 0 && $certificatesReady && file_exists("salons/" . $contest_yearmonth . "/blob/certdef.json") ) {
								$upload_code = encode_string_array($contest_yearmonth . "|PROFILE|" . $tr_user['profile_id'] . "|ALL");
						?>
							<a href="op/certificate.php?cert=<?=$upload_code;?>" class="btn btn-info pull-right" style="border-radius: 24px;" >
								<span style="color: white;"><i class="fa fa-download"></i> Download All Certificates</span>
							</a>
						<?php
							}
						?>
						</div>
						<div class="col-sm-3">
							<a href="javascript:void(0)" id="download_results" class="btn btn-info pull-right" style="border-radius: 24px;" >
								<span style="color: white;"><i class="fa fa-download"></i> Download Results</span>
							</a>
						</div>
					</div>

					<!-- Overall Results -->
					<!-- Check for contest level Awards -->
					<?php
						// Check for Entry Awards
						$sql  = "SELECT * FROM entry_result, award ";
						$sql .= " WHERE entry_result.yearmonth = '$contest_yearmonth' ";
						$sql .= "   AND entry_result.profile_id = '" . $tr_user['profile_id'] . "' ";
						$sql .= "   AND award.yearmonth = entry_result.yearmonth ";
						$sql .= "   AND award.award_id = entry_result.award_id ";
						$sql .= "   AND award.award_type = 'entry' ";
						// $sql .= "   AND award.section = 'CONTEST' ";
						$erqry = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
						$has_entry_result = (mysqli_num_rows($erqry) != 0);

						// Check for Special Picture Awards
						if ($contest_archived)
							$sql  = "SELECT * FROM ar_pic_result pic_result, award, ar_pic pic ";
						else
							$sql  = "SELECT * FROM pic_result, award, pic ";
						$sql .= " WHERE pic_result.yearmonth = '$contest_yearmonth' ";
						$sql .= "   AND pic_result.profile_id = '" . $tr_user['profile_id'] . "' ";
						$sql .= "   AND award.yearmonth = pic_result.yearmonth ";
						$sql .= "   AND award.award_id = pic_result.award_id ";
						$sql .= "   AND award.award_type = 'pic' ";
						$sql .= "   AND award.section = 'CONTEST' ";
						$sql .= "   AND pic.yearmonth = pic_result.yearmonth ";
						$sql .= "   AND pic.profile_id = pic_result.profile_id ";
						$sql .= "   AND pic.pic_id = pic_result.pic_id ";
						$spqry = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
						$has_special_pic_result = (mysqli_num_rows($spqry) != 0);

						// Check for Club Awards
						if ($tr_user['yps_login_id'] == "") {
							$sql  = "SELECT * FROM club_result, award ";
							$sql .= " WHERE club_result.yearmonth = '$contest_yearmonth' ";
							$sql .= "   AND club_result.club_id = '" . $tr_user['club_id'] . "' ";
							$sql .= "   AND award.yearmonth = club_result.yearmonth ";
							$sql .= "   AND award.award_id = club_result.award_id ";
							$sql .= "   AND award.award_type = 'club' ";
							$sql .= "   AND award.section = 'CONTEST' ";
							$bcqry = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
							$has_club_result = (mysqli_num_rows($bcqry) != 0);
						}
						else
							$has_club_result = false;

						// Render details if any of the above results is true
						if ($has_entry_result || $has_special_pic_result || $has_club_result) {
					?>
					<div class="row">
						<div class="col-sm-12">
							<h2 class="text-color">Special Awards</h2>
							<ul class="list-group">
							<?php
								if ($has_entry_result) {
									while ($clrow = mysqli_fetch_array($erqry)) {
							?>
								<li class="list-group-item"><h3 class="text-info pdf-spl-award"><?= $clrow['award_name'];?></h3></li>
							<?php
									}
								}
								if ($has_special_pic_result) {
									while ($clrow = mysqli_fetch_array($spqry)) {
							?>
								<li class="list-group-item"><h3 class="text-info pdf-spl-award"><?= $clrow['award_name'];?> for the picture '<?= $clrow['title'];?>'</h3></li>
							<?php
									}
								}
								if ($has_club_result) {
									while ($clrow = mysqli_fetch_array($bcqry)) {
							?>
								<li class="list-group-item"><h3 class="text-info pdf-spl-award">Member of <?= $tr_user['club_name'];?> winning <?= $clrow['award_name'];?> award</h3></li>
							<?php
									}
								}
							?>
							</ul>
						</div>
					</div>
					<?php
						}
					?>

					<!-- Picture Results -->
					<div class="row">
						<div class="col-sm-12 shopping-cart user-cart">
							<h2 class="text-color">My Pictures</h2>
						<?php
						if ($contest_archived)
							$sql  = "SELECT * FROM ar_pic pic, section ";
						else
							$sql  = "SELECT * FROM pic, section ";
						$sql .= " WHERE pic.yearmonth = '$contest_yearmonth' ";
						$sql .= "   AND pic.profile_id = '" . $tr_user['profile_id'] . "' ";
						$sql .= "   AND section.yearmonth = pic.yearmonth ";
						$sql .= "   AND section.section = pic.section ";
						$sql .= " ORDER BY pic.section, total_rating DESC";
						$qpic = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
						$pic_section_list = [];
						$total_uploads = 0;
						$total_acceptances = 0;
						$total_awards = 0;
						$total_hms = 0;
						$total_rejections = 0;
						while ($rpic = mysqli_fetch_array($qpic)) {
							$pic_id = $rpic['pic_id'];
							$pic_section = $rpic['section'];
							$pic_section_list[] = $pic_section;
							++ $total_uploads;
							$rejection_text = rejection_text($rpic['notifications']);
							$jury_rejection_text = jury_notifications($tr_user['profile_id'], $pic_id);
							if ($jury_rejection_text != "")
								$rejection_text = ($rejection_text == "" ? "" : ", ") . $jury_rejection_text;
							if ($rejection_text != "")
								++ $total_rejections;
							// Check for rejections by jury
							$thumbnail = "/salons/$contest_yearmonth/upload/$pic_section/tn/" . $rpic['picfile'];
						?>
							<div class="panel">
								<div class="panel-body">
									<div class="col-sm-3 col-md-3 col-lg-3 thumbnail">
										<img src="<?= $thumbnail;?>" >
									</div>
									<!-- Get Total Rating -->
									<div class="col-sm-4 col-md-4 col-lg-4 ">
										<h4 class="text-muted"><?php echo $rpic['section'];?></h4>
										<h3 class="text-primary" style="margin-bottom: 0px;" id="title-display-<?= $pic_id;?>"><?php echo $rpic['title'];?></h3>
						<?php
							if ($updateEndDate >= date("Y-m-d")) {
						?>
										<div class="row" style="width: 100%; margin-bottom: 10px;">
											<div class="col-sm-12">
												<a href="javascript:void(0)" class="text-info" onclick="show_title_input(<?= $pic_id;?>)">Edit Title</a>
											</div>
											<div class="col-sm-12" id="title-input-<?= $pic_id;?>" style="display: none;">
												<div class="form-group" style="width: 100%;">
													<label class="control-label" for="pic_title">New Title</label>
													<input type="text" title="Please enter new title" required maxlength="35"
															name="new_title" id="new_title_<?= $pic_id;?>" class="form-control"
															value="<?= $rpic['title'];?>" style="width: 100%;" >
												</div>
												<div class="form-group">
													<a class="btn btn-info pull-right" style="color: white;"
														onclick="update_title(<?= $contest_yearmonth;?>, <?= $rpic['profile_id'];?>, <?= $pic_id;?>)">Update</a>
												</div>
											</div>
										</div>
						<?php
							}
						?>
						<?php
							$sql  = "SELECT IFNULL(SUM(rating), 0) AS rating, IFNULL(COUNT(*) * 5, 0) AS total ";
							if ($contest_archived)
								$sql .= "  FROM ar_rating rating ";
							else
								$sql .= "  FROM rating ";
							$sql .= " WHERE yearmonth = '$contest_yearmonth' ";
							$sql .= "   AND rating.profile_id = '" . $tr_user['profile_id'] . "' ";
							$sql .= "   AND rating.pic_id = '$pic_id' ";
							$qrtg = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
							$rrtg = mysqli_fetch_array($qrtg);
						?>
										<p><big>Total Score = <?=$rrtg['rating'];?> / <?=$rrtg['total'];?></big></p>
										<p class="text-danger"><small><?= $rejection_text;?></small></p>
									</div>
									<div class="col-sm-4 col-md-4 col-lg-4 ">
						<?php
							if ($contest_archived)
								$sql  = "SELECT * FROM ar_pic_result pic_result, award ";
							else
								$sql  = "SELECT * FROM pic_result, award ";
							$sql .= " WHERE pic_result.yearmonth = '$contest_yearmonth' ";
							$sql .= "   AND pic_result.profile_id = '" . $tr_user['profile_id'] . "' ";
							$sql .= "   AND pic_result.pic_id = '$pic_id' ";
							$sql .= "   AND pic_result.yearmonth = award.yearmonth ";
							$sql .= "   AND pic_result.award_id = award.award_id ";
							$sql .= "   AND award.section != 'CONTEST' ";
							$qawd = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
							$accepted = (mysqli_num_rows($qawd) == 0) ? "No" : "Yes";
							if ($rawd = mysqli_fetch_array($qawd)) {
								// Compute totals
								++ $total_acceptances;
								if ($rawd['level'] < 9)
								 	++ $total_awards;
								else if ($rawd['level'] == 9)
									++ $total_hms;

								$upload_code = encode_string_array($contest_yearmonth . "|" . $rawd['award_id'] . "|" . $tr_user['profile_id'] . "|" . $pic_id);
						?>
								<p class="text-center text-color lead">Accepted</p>
						<?php
								if ($rawd['level'] < 99) {
						?>
										<p class="text-center"><img src="img/flashing_star_wht.gif"></p>
						<?php
								}
						?>
										<h4 class="text-center text-primary"><b><?php echo $rawd['award_name'];?></b></h4>
										<br>
						<?php
								// if ($rawd['has_certificate'] && $certificatesReady && file_exists("salons/" . $contest_yearmonth . "/certdef.php") ) {
								if ($rawd['has_certificate'] && $certificatesReady && file_exists("salons/" . $contest_yearmonth . "/blob/certdef.json") ) {
						?>
										<p class="text-center"><a href="op/certificate.php?cert=<?=$upload_code;?>" class="btn btn-default">Download Certificate</a></p>
						<?php
								}
								if ($rawd['level'] != 99 && $updateEndDate >= date("Y-m-d") && $rpic['section_type'] != "P" ) {
						?>
										<br>
										<p class="text-center" style="color:<?=empty($rpic['full_picfile']) ? 'red' : 'green';?>;">
											<?= empty($rpic['full_picfile']) ? "Upload full resolution image for Exhibition Printing" : "You have already uploaded a file for printing";?>
										</p>
										<p class="text-center"><a href="/upload_awarded.php?code=<?=$upload_code;?>" class="btn btn-default"><?= empty($rpic['full_picfile']) ? 'Upload' : 'Replace';?></a></p>
						<?php
								}
							}
							else {
						?>
										<p class="text-center text-color"><b>Not Accepted</b></p>
						<?php
							}
						?>
										<span class="pdf-pic-data"
											  data-section="<?= $rpic['section'];?>"
											  data-title="<?= $rpic['title'];?>"
											  data-thumbnail="<?= $thumbnail;?>"
											  data-thumbnail-id="TN_<?= $rpic['pic_id'];?>"
											  data-total-score="<?= $rrtg['rating'];?>"
											  data-accepted="<?= $accepted;?>"
											  data-award-name="<?= ($accepted == "Yes" && $rawd['level'] < 99) ? $rawd['award_name'] : "None";?>"
											  data-rejected="<?= ($rejection_text == "") ? "No" : "Yes";?>"
											  data-rejection-text="<?= $rejection_text;?>"
											  >
										</span>
									</div>
								</div>
							</div>
						<?php
						} // while
						?>
						</div>
					</div>
				</div>			<!-- My Tab -->
			</div> <!-- / .row -->
		</div> <!-- / .container -->


    <!-- Footer -->
	<?php include_once("inc/footer.php") ;?>
	</div>	<!-- / .wrapper -->

    <!-- Style Toggle -->
	<?php include_once("inc/settingToggle.php") ;?>

    <!-- JavaScript -->
	<?php include_once("inc/scripts.php"); ?>
	<script>
		// Show Title Input fields
		function show_title_input(pic_id) {
			$("#title-input-" + pic_id).show();
		}

		// Update Title Input
		function update_title(yearmonth, profile_id, pic_id) {
			let new_title = $("#new_title_" + pic_id).val();
			if (new_title != "" && new_title.length <= 35) {
				$.post("ajax/update_pic_title.php", {yearmonth, profile_id, pic_id, new_title}, function(response) {
					let data = JSON.parse(response);
					if (data.success) {
						$("#title-display-" + pic_id).html(new_title);
						$("#title-input-" + pic_id).hide();					}
				});
			}
		}
	</script>

	<!-- Generate PDF using the definition created in salonlib.php -->
	<script type="text/javascript" charset="utf-8" src='plugin/pdfmake/pdfmake.min.js'></script>
	<script type="text/javascript" charset="utf-8" src='plugin/pdfmake/vfs_fonts.js'></script>
	<!-- Print Results Sheet -->
	<script>
		// Some constants
		const pageWidth = 595;		// In points
		const cellPadding = 4;
		const hMargin = 36;
		const vMargin = 48;

		// generate cells for patronage
		function generate_patronage_table() {
			let table = [];
			let row_data = [];
			$(".patronage-data").each(function(index, info){
				if ( index != 0 && ((index % 3) == 0) ) {
					table.push(row_data);
					row_data = [];
				}
				row_data.push({
					columns : [
						{
							width : 'auto',
							stack : [ { image : $(info).data("short-code"), width : 24, }, ],
						},
						{
							width : '*',
							stack : [
								{ text : $(info).data("organization-name"), style : "patronage_org", },
								{ text : $(info).data("website"), style : "patronage_www", link : $(info).data("website"), },
								{ text : $(info).data("recognition-id"), style : "patronage_id",  },
							],
						},
					],
					columnGap : 8,
				});
			});
			if (row_data.length > 0) {
				while (row_data.length < 3) {
					row_data.push("");
				}
				table.push(row_data);
			}
			return table;
		}

		// Generate Special Awards as a list
		function generate_special_awards() {
			let spl_award_result = {
				stack : [],
			};

			let spl_award_list = {
				ul : [],
			};
			$(".pdf-spl-award").each(function(index, award) {
				spl_award_list.ul.push({ text : $(award).html(), style : "special_award", margin : [4, 4, 0, 0], });
			});

			if (spl_award_list.ul.length > 0) {
				spl_award_result.stack.push({text : "Special Awards :", style : "h3", margin : [4, 4, 0, 0]});
				spl_award_result.stack.push(spl_award_list);
			}

			return spl_award_result;
		}

		// Generate results for a section
		function generate_section(section) {
			// Compute Dimensions
			const tnWidth = 136;
			const tnCellWidth = tnWidth;
			const txtCellWidth = (pageWidth - (hMargin * 2) - (tnCellWidth * 2) - (cellPadding * 4 * 2) - 1 ) / 2;

			let output = {
				stack : [],
			};

			// Add section heading
			output.stack.push( { text : section, style : "section_heading", margin : [0, 16, 0, 4], headlineLevel : "section-heading" } );

			// Add section table
			let results = {
				layout : "normal_cell",
				table : {
					widths : [ tnCellWidth, txtCellWidth, tnCellWidth, txtCellWidth ],
					dontBreakRows : true,
					body : [],
				},
			}

			let count = 0;
			let cols = [];
			$(".pdf-pic-data").each(function(index, pic){
				if (count == 2) {
					results.table.body.push(cols);
					cols = [];
					count = 0;
				}
				if ($(pic).data("section") == section) {
					++ count;
					// Column 1 - Thumbnail
					// cols.push({ image : $(pic).data("thumbnail-id"), fit : [tnWidth, tnWidth], alignment : 'center', });
					let img = {
                        stack : [
                            { image : $(pic).data("thumbnail-id"), fit : [tnWidth, tnWidth], alignment : 'center', },
                            { text : $(pic).data("title").toString(), style : "pic_title", margin : [0, 4, 0, 8], },
                        ]
                    }
                    cols.push(img);

					// Column 2 - details
					let tmp = {
						stack : [
							{text : "Total Score", style : "cell_heading", margin : [0, 4, 0, 0], },
							{text : $(pic).data("total-score").toString(), style : "cell_content", margin : [0, 4, 0, 8], },
							{canvas : [{ type: 'line', x1: 0, y1: 0, x2: txtCellWidth, y2: 0, lineWidth: 0.5, lineColor: '#aaa' },]},
							{text : "Accepted ?", style : "cell_heading", margin : [0, 4, 0, 0], },
							{text : $(pic).data("accepted"), style : "cell_content", margin : [0, 4, 0, 8], },
							{canvas : [{ type: 'line', x1: 0, y1: 0, x2: txtCellWidth, y2: 0, lineWidth: 0.5, lineColor: '#aaa' },]},
							{text : "Award/Honorable Mention", style : "cell_heading", margin : [0, 4, 0, 0], },
							{text : $(pic).data("award-name"), style : "cell_content", margin : [0, 4, 0, 8], },
						],
//						layout : "band_cell",
//						table : {
//							body : [
//								[ {stack : [ {text : "Total Score", style : "cell_heading"}, {text : $(pic).data("total-score").toString(), style : "cell_content"} ]} ],
//								[ {stack : [ {text : "Accepted ?", style : "cell_heading"}, {text : $(pic).data("accepted"), style : "cell_content"} ]} ],
//								[ {stack : [ {text : "Award ?", style : "cell_heading"}, {text : $(pic).data("award-name"), style : "cell_content"} ]} ],
//							],
//							widths : ['*'],
//						},
//						dontBreakRows : true,
					};
					if ($(pic).data("rejected") == "Yes") {
						tmp.stack.push({canvas : [{ type: 'line', x1: 0, y1: 0, x2: txtCellWidth, y2: 0, lineWidth: 0.5, lineColor: '#aaa' },]});
						tmp.stack.push({text : "Rejected for", style : "cell_heading", margin : [0, 4, 0, 0], });
						tmp.stack.push({text : $(pic).data("rejection-text"), style : "rejection_text", margin : [0, 4, 0, 8], });
					}

					// Add column to the table row
					cols.push(tmp);
				}
			});
			// Add any orphan row after filling incomplete columns
			if (count > 0) {
				if (count < 2) {
					// Add 2 columns
					cols.push("");
					cols.push("");
				}
				results.table.body.push(cols);
			}

			// Add table to output
			output.stack.push(results);

			// Return output
			return output;
		}

		// Generate the main results
		function generate_results() {
			// Create a list of sections
			let sections = [];
			$(".pdf-pic-data").each(function (index, pic) {
				if ( ! sections.includes( $(pic).data("section") ) )
					sections.push($(pic).data("section"));
			});

			// Create result by section
			let result = {
				stack : [],
			};

			sections.forEach(function(section) {
				result.stack.push(generate_section(section));
			});

			return result;
		}

		// Generate PDF
		$("#download_results").click(function() {

			const patColumnWidth = (pageWidth - (hMargin * 2) - (cellPadding * 6) - 1) / 3;
			const summaryColumnWidth = (pageWidth - (hMargin * 2) - 1) / 2;

			// Cell Layouts for use in Table
			pdfMake.tableLayouts = {
					normal_cell : {
						hLineWidth : () => { return 0.5;},
						vLineWidth : () => { return 0.5;},
						vLineColor : () => { return "#aaa";},
						hLineColor : () => { return "#aaa";},
						paddingLeft : () => { return cellPadding;},
						paddingRight : () => { return cellPadding;},
						paddingTop : () => { return cellPadding;},
						paddingBottom : () => { return cellPadding;},
					},
					band_cell : {
						hLineWidth : () => { return 0.25;},
						vLineWidth : () => { return 0;},
						vLineColor : () => { return "#aaa";},
						hLineColor : () => { return "#aaa";},
						paddingLeft : () => { return cellPadding;},
						paddingRight : () => { return cellPadding;},
						paddingTop : () => { return cellPadding;},
						paddingBottom : () => { return cellPadding;},
					},
					tight_cell : {
						hLineWidth : () => { return 0.5;},
						vLineWidth : () => { return 0.5;},
						vLineColor : () => { return "#aaa";},
						hLineColor : () => { return "#aaa";},
						paddingLeft : () => { return 0;},
						paddingRight : () => { return 0;},
						paddingTop : () => { return 0;},
						paddingBottom : () => { return 0;},
					},
			};
			// Print Definition
			let pdf_def = {
					info : {
						title : "<?= $contestName;?> - Salon Results of <?= ucwords(strtolower($tr_user['profile_name']));?>",
						author : "Youth Photographic Society"
					},
					pageSize : "A4",
					pageOrientation : "portrait",
					pageMargins : [hMargin, vMargin],
					styles : {
						h1 : { fontSize : 22, bold : true, color : "black" },
						h2 : { fontSize : 18, bold : true, color : "black" },
						h3 : { fontSize : 14, italics : true, bold : true, color : "black" },
						normal : { fontSize : 11, color : "black" },
						bold : { fontSize : 11, color : "black", bold : true },
						name : { fontSize : 18, color : "#007bff", bold : true },
						honors : { fontSize : 8, color : "#aaa", italics : true },
						club : { fontSize : 10, color : "#444" },
						patronage_org : { fontSize : 10, color : 'black', bold : true },
						patronage_id : { fontSize : 12, color : "#007bff", bold : true },
						special_award : { fontSize : 12, color : "#007bff", bold : true },
						patronage_www : { fontSize : 8, color : "#888", italics : true, underline : true },
						section_heading : { fontSize : 14, color : 'black', bold : true, },
						cell_heading : { fontSize : 8, color : "#888", bold : true, alignment : "left" },
						cell_content : { fontSize : 12, color : "#007bff", alignment : "center", },
						pic_title : { fontSize : 8, color : "#444", bold : true, alignment : "center", },
						rejection_text : { fontSize : 10, color : "#dc3545", alignment : "center", },
					},
					defaultStyle : { fontSize : 11, color : "black" },
					content : [
						[
							{ image : "report_banner", width : pageWidth - (hMargin * 2) },
							{
								layout : "normal_cell",
								table : {
									widths : [ patColumnWidth, patColumnWidth, patColumnWidth ],
									body : generate_patronage_table(),
								},
							},
						],
						[ { text : "Summary of Results for <?= $contestName;?>", style : "h3", margin : [0, 8, 0, 4] } ],
						[
							{
								layout : 'tight_cell',
								table : {
									widths : [ summaryColumnWidth, summaryColumnWidth ],
									body : [
										[
											{
												stack : [
													{ text : "<?= ucwords(strtolower($tr_user['profile_name']));?>", style : "name", margin : [4, 4, 0, 0]},
													{ text : "<?= $tr_user['honors'];?>", style : "honors", margin : [4, 4, 0, 0] },
													{ text : "<?= $tr_user['club_name'];?>", style : "club", margin : [4, 4, 0, 0] },
													generate_special_awards(),
												],
											},
											{
												layout : "band_cell",
												table : {
													widths : [ 'auto', '*' ],
													body : [
														[ { text : "Number of Sections", style : "normal"}, { text : "<?= sizeof(array_unique($pic_section_list));?>", style : "bold", alignment : "center" } ],
														[ { text : "Number of Uploads", style : "normal"}, { text : "<?= $total_uploads;?>", style : "bold", alignment : "center"  } ],
														[ { text : "Number of Acceptances", style : "normal"}, { text :  "<?= $total_acceptances;?>", style : "bold", alignment : "center"  } ],
														[ { text : "Number of Awards", style : "normal"}, { text :  "<?= $total_awards;?>", style : "bold", alignment : "center"  } ],
														[ { text : "Number of Honorable Mentions", style : "normal"}, { text :  "<?= $total_hms;?>", style : "bold", alignment : "center"  } ],
													],
												},
											},
										],
									],
								},
							},
						],
					],
					pageBreakBefore : function(currentNode, followingNodesOnPage, nodesOnNextPage, previousNodesOnPage) {
						return (currentNode.headlineLevel == "section-heading" && currentNode.startPosition.top > 640);

					},
					images : {
								ypslogo : '<?= http_method() . $_SERVER['SERVER_NAME'] . '/img/ypsLogo.png';?>',
								report_banner : '<?= http_method() . $_SERVER['SERVER_NAME'] . "/salons/$contest_yearmonth/img/results_banner.jpg";?>',
					},
			};
			// Add Patronage house logos (same name as short_code)
			$(".patronage-data").each(function(index, recn){
				pdf_def.images[$(recn).data("short-code")] = "<?= http_method() . $_SERVER['SERVER_NAME'] . "/salons/$contest_yearmonth/img/recognition/";?>" + $(recn).data("logo");
			});
			// Add Picture Thumbnails to image list
			$(".pdf-pic-data").each(function(index, pic){
				pdf_def.images[$(pic).data("thumbnail-id")] = "<?= http_method() . $_SERVER['SERVER_NAME'];?>" + $(pic).data("thumbnail");
			});

			// Generate Thumbnails and Scores
			pdf_def.content.push(generate_results());

			pdfMake.createPdf(pdf_def).download("My_Results_<?= $contestName;?>");
			hideLoader(this);

		});
	</script>
</body>
</html>
