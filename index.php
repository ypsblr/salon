<?php
include_once("inc/session.php");
$yps_age = date_diff(date_create("1971-09-01"), date_create());
?>
<!DOCTYPE html>
<html lang="en">

<head>
<?php include_once("inc/header.php"); ?>
<style>
table th, td {
	font-size: 14px;
	line-height: 20px;
}
</style>
</head>

<body class="<?php echo THEME;?>">

    <?php include_once("inc/navbar.php") ;?>
    <div class="wrapper">
		<!-- Jumbotron -->
		<?php  include_once("inc/Slideshow.php") ;?>
		<!-- Slideshow -->

		<!-- Intro Text -->
		<div class="container-fluid intro">
			<div class="row">
				<!-- LEFT COLUMN -->
				<div class="col-sm-8 col-md-8 col-lg-8" style="padding-left:3%">
					<h2 class="headline first-child text-color">
						<span class="border-color">Welcome to <?=$contestName;?></span>
					</h2>
					<?php
						if (file_exists("salons/$contest_yearmonth/blob/$contestAnnouncementBlob"))
							include("salons/$contest_yearmonth/blob/$contestAnnouncementBlob");
					?>
					<p style="text-align:justify;">
						<b>Youth Photographic Society</b> (YPS) is one of the oldest photo clubs in India with <?= $yps_age->format("%y");?>
						successful years behind it. Over the last few years, YPS has gone through a digital transformation modernizing its services.
						Visit <a href="http://ypsbengaluru.com" target="_blank">YPS Website</a> to know more. We are pleased to welcome you
						to <b><?=$contestName;?></b>. Find more details of the salon below. We aim to make your participation experience seamless
						and memorable.
					</p>
					<p><b><i>- <?=$contestName;?> Committee</i></b></p>
					<div class="well"  style="background-color: white; padding : 8px;">
						<div class="row">
						<?php
							$sql = "SELECT * FROM recognition WHERE yearmonth = '$contest_yearmonth' ORDER BY short_code";
							$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
							while ($row = mysqli_fetch_array($query)) {
						?>
							<div class="col-sm-2">
								<div class="thumbnail" style="border:0; margin-bottom: 0px;"><img src="/salons/<?= $contest_yearmonth;?>/img/recognition/<?=$row['logo'];?>" style="width: 60px; "></div>
								<div class="text-center small"><?=$row['recognition_id'];?></div>
							</div>
						<?php
							}
						?>
						</div>
					</div>

					<!-- Display Results if available -->
					<?php
						if ($resultsReady) {
							echo merge_data($resultsDescriptionBlob, $contest_values);
						}
					?>

					<!-- Next Event -->
					<!-- 1. Submission Phase. Display Quick Guide, Salon Description and Salon Fees -->
					<?php
					    // var_dump($contestFeeStructureBlob);
						// SALON OPEN FOR ENTRY - Guide, Sections, Fee Structure
						if (DATE_IN_SUBMISSION_TIMEZONE >= $registrationStartDate && DATE_IN_SUBMISSION_TIMEZONE <= $registrationLastDate) {
							include ("inc/guide.php");
							echo merge_data($contestDescriptionBlob, $contest_values);
							//include ("inc/who_can_participate.php");
							include ("inc/sections.php");
							if ($contestFeeStructureBlob == "")
								include ("inc/fees.php");
							else {
								include ("salons/" . $contest_yearmonth . "/blob/" . $contestFeeStructureBlob);
								if ($contestHasDiscounts) {
									if ($contestDiscountStructureBlob != "")
										include ("salons/" . $contest_yearmonth . "/blob/" . $contestDiscountStructureBlob);
								}
							}
						}

						// JUDGING INFORMATION -3 DAYS TO END DATE
						if (INDIA_DATE >= THREE_DAYS_TO_JUDGING && INDIA_DATE <= $judgingEndDate ) {
							echo merge_data($judgingDescriptionBlob, $contest_values);
						}

						// EXHIBITION - AFTER JUDGING AND EXHIBITION ANNONCED
						if (INDIA_DATE >= $judgingEndDate && INDIA_DATE <= $exhibitionEndDate && $exhibitionScheduleBlob != "" ) {
							// Enable Virtual Exhibition When opened
							if (date("Y-m-d") >= $exhibitionStartDate && date("Y-m-d") <= $exhibitionEndDate &&
								$exhibitionSet && $exhibitionIsVirtual && $exhibitionVirtualTourReady) {
					?>
					<div class="well"  style="background-color: white; padding : 8px;">
						<div class="row">
							<div class="col-sm-3"></div>
							<div class="col-sm-6">
								<a href="exhibition.php" class="btn btn-color" style="width: 100%; font-size: 1.5em;">Enter Virtual Exhibition</a>
							</div>
							<div class="col-sm-3"></div>
						</div>
					</div>
					<?php
							}
							// Process Data for Exhibition
							$exhibition_merge_data = [];
							for ($idx = 0; $idx < sizeof($exhibitionDignitoryRoles); ++ $idx) {
								$base = str_replace(" ", "-", strtolower($exhibitionDignitoryRoles[$idx]));
								$exhibition_merge_data[$base . "-role"] = $exhibitionDignitoryRoles[$idx];
								$exhibition_merge_data[$base . "-name"] = $exhibitionDignitoryNames[$idx];
								$exhibition_merge_data[$base . "-position"] = $exhibitionDignitoryPositions[$idx];
								$exhibition_merge_data[$base . "-avatar"] = "/salons/$contest_yearmonth/img/" . $exhibitionDignitoryAvatars[$idx];
							}
							echo merge_data($exhibitionScheduleBlob, array_merge($contest_values, $exhibition_merge_data));
						}
					?>

					<!-- Reports -->
					<?php
						include_once("inc/photos.php");

						// Judging Report
						if ($judgingReportBlob && file_exists($_SERVER['DOCUMENT_ROOT'] . "/salons/$contest_yearmonth/blob/$judgingReportBlob"))
							echo merge_data($judgingReportBlob, $contest_values);
						// Judging Photos
						photo_slideshow($contest_yearmonth, "judging");
						// if ($judgingPhotosPhp && file_exists($_SERVER['DOCUMENT_ROOT'] . "/salons/$contest_yearmonth/blob/$judgingPhotosPhp")) {
						// 	include($_SERVER['DOCUMENT_ROOT'] . "/salons/$contest_yearmonth/blob/$judgingPhotosPhp");
						// 	include("inc/photos_judging.php");
						// }
						// Exhibition Report
						if ($exhibitionReportBlob && file_exists($_SERVER['DOCUMENT_ROOT'] . "/salons/$contest_yearmonth/blob/$exhibitionReportBlob"))
							echo merge_data($exhibitionReportBlob, $contest_values);
						// Inauguration Photos
						photo_slideshow($contest_yearmonth, "inauguration");
						// if ($inaugurationPhotosPhp && file_exists($_SERVER['DOCUMENT_ROOT'] . "/salons/$contest_yearmonth/blob/$inaugurationPhotosPhp")) {
						// 	include($_SERVER['DOCUMENT_ROOT'] . "/salons/$contest_yearmonth/blob/$inaugurationPhotosPhp");
						// 	include("inc/photos_inauguration.php");
						// }
						// Exhibition Photos
						photo_slideshow($contest_yearmonth, "exhibition");
						// if ($exhibitionPhotosPhp && file_exists($_SERVER['DOCUMENT_ROOT'] . "/salons/$contest_yearmonth/blob/$exhibitionPhotosPhp")) {
						// 	include($_SERVER['DOCUMENT_ROOT'] . "/salons/$contest_yearmonth/blob/$exhibitionPhotosPhp");
						// 	include("inc/photos_exhibition.php");
						// }
					?>

					<br><br>
					<div class="panel panel-default">
						<div class="panel-heading">
							<h4 class="text-danger">Calendar</h4>
						</div>
						<div class="panel-body">
							<h3 class="headline text-color" id="index-dates"><span class="border-color">Salon Calendar</span></h3>
							<table class="table table-bordered">
								<tbody>
								<tr>
									<td>Last Date for Registration</td>
									<td width="25%"><?php echo print_date($registrationLastDate);?></td>
									<td>
										Registration will automatically cease at midnight <?php echo $submissionTimezoneName;?> time.
									</td>
								</tr>
								<tr>
									<td>Last Date(s) for Upload</td>
									<td width="25%">
										<?php
											foreach($submissionLastDates AS $submission_last_date => $submission_sections) {
										?>
										<p><b><?php echo print_date($submission_last_date);?></b> for:</p>
										<ul>
										<?php
												foreach(explode(",", $submission_sections) AS $submission_section) {
										?>
											<li><?=$submission_section;?></li>
										<?php
												}
										?>
										</ul>
										<?php
											}
										?>
									</td>
									<td>
										Upload will automatically cease at midnight <?php echo $submissionTimezoneName;?> time for the respective sections.
										Please check the information on Section for last date for Upload.
										Submissions through email, CDs etc. will not be judged.
									</td>
								</tr>
								<tr>
									<td>Judging Date</td>
									<td><?php echo ($judgingStartDate == $judgingEndDate) ? date(DATE_FORMAT, strtotime($judgingStartDate)) : date("F j - ", strtotime($judgingStartDate)) . date("F j, Y", strtotime($judgingEndDate)) ;?></td>
									<td>
										<?php
											if ($judgingVenue != "") {
										?>
										Venue: <b><?=$judgingVenue;?></b>,<br><?=$judgingVenueAddress;?>
										<?php
											}
										?>
									</td>
								</tr>
								<tr>
									<td>Announcement of Results</td>
									<td><?php echo date(DATE_FORMAT, strtotime($resultsDate));?></td>
									<td>The results will be posted on the salon website. Each entrant would also receive an email intimation of the same.</td>
								</tr>
								<tr>
									<td><?=$exhibitionName;?></td>
									<td><?php echo ($exhibitionStartDate == $exhibitionEndDate) ? date(DATE_FORMAT, strtotime($exhibitionStartDate)) : date("D, F j - ", strtotime($exhibitionStartDate)) . date("F j, Y", strtotime($exhibitionEndDate)) ;?></td>
									<td>
										<?php
											if ($exhibitionVenue != "") {
										?>
										Venue: <b><?=$exhibitionVenue; ?></b>,<br> <?=$exhibitionVenueAddress;?>
										<?php
											}
										?>
									</td>
								</tr>
								<?php
									if (! empty($catalogReleaseDate)) {
								?>
								<tr>
									<td>Catalog Release</td>
									<td><?php echo date(DATE_FORMAT, strtotime($catalogReleaseDate));?></td>
									<td>A digital catalog will be hosted on the website.</td>
								</tr>
								<?php
									}
								?>
								</tbody>
							</table>
						</div>
					</div>
			    </div>    <!-- END OF LEFT COLUMN -->
				<!-- RIGHT COLUMN -->
				<div class="col-sm-4 col-md-4 col-lg-4"  >
					<!-- Show Login Form -->
					<?php include("inc/login_form.php");?>

					<!-- Start Count Down One Week before the last date -->
					<?php include("inc/countdown.php");?>

					<!-- Partners -->
					<?php
						include("inc/partnerlib.php");
						partner_display($contest_yearmonth);
						// if (file_exists("./salons/$contest_yearmonth/blob/partners.php"))
						// 	include("./salons/$contest_yearmonth/blob/partners.php");
					?>

					<!-- Show Catalog Download / View Links after results are published -->
					<?php if ($contestHasCatalog && ! is_null($catalogReleaseDate) && DATE_IN_SUBMISSION_TIMEZONE >= $catalogReleaseDate && $catalogReady)
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
			        <?php include_once("inc/awards_column.php") ;?>
				</div>		<!-- END OF RIGHT SIDE -->
			</div>	<!-- row -->
			<!-- FOOTER -->
			<div class="row">
				<?php include_once("inc/footer.php") ;?>
			</div>
        </div>		<!-- container -->
	</div>			<!-- wrapper -->


    <!-- Style Toggle -->

	<?php include_once("inc/settingToggle.php") ;?>
    <!-- JavaScript
    ================================================== -->
	<?php include_once("inc/scripts.php"); ?>
	<!-- Crypto routines -->



	<script>
		// sprintf ---
		function format_float(number, width, decimal) {
			var tmp = Math.round(number * Math.pow(10, decimal)).toString();		// Multiply to make it a whole number and convert to string
			var zero = "0";
			if (tmp.length <= decimal)	// Probably just "0"
				tmp = zero.repeat(decimal - tmp.length + 1).concat(tmp);
			var str = tmp.substr(0, tmp.length - decimal).concat(".", tmp.substr(decimal * -1));
			var space = " ";
			if (str.length < width)
				return space.repeat(width - str.length).concat(str);
			else
				return str;
		}

		// Function fee_calculator
		function fee_calculator() {
			var entrant_category = "YPS";
			var currency = "INR";
			var digital_sections = <?php echo isset($max_digital_sections) ? $max_digital_sections : 0;?>;
			var print_sections = <?php echo isset($max_print_sections) ? $max_print_sections : 0;?>;

			entrant_category = $("#fc-is-yps-member").prop("checked") ? "YPS" : "GENERAL";
			currency = $("#fc-is-an-indian").prop("checked") ? "INR" : "USD";
			digital_sections = $("#fc-digital-sections").val();
			print_sections = $("#fc-print-sections").val();

			if (entrant_category == "YPS") {
				$("#fc-is-an-indian").prop("checked", "checked");
				$("#fc-is-non-indian").prop("disabled", "disabled");
				currency = "INR";
			}
			else {
				$("#fc-is-non-indian").prop("disabled","");
			}

			// Call server for details
			$.post("ajax/fee_calculator.php",
					{"entrant_category" : entrant_category, "currency" : currency, "digital_sections" : digital_sections, "print_sections" : print_sections},
					function(response) {
						response = JSON.parse(response);
						if(response.success){
							$("#fc-eb-nd-txt").html(currency + " " + format_float(response.early_bird_no_discount, 7, 2));
							if (response.early_bird_with_discount == response.early_bird_no_discount)
								$("#fc-eb-cd-txt").html("- NA -");
							else
								$("#fc-eb-cd-txt").html(currency + " " + format_float(response.early_bird_with_discount, 7, 2));
							$("#fc-rg-nd-txt").html(currency + " " + format_float(response.regular_no_discount, 7, 2));
							if (response.regular_with_discount == response.regular_no_discount)
								$("#fc-rg-cd-txt").html("- NA -");
							else
								$("#fc-rg-cd-txt").html(currency + " " + format_float(response.regular_with_discount, 7, 2));
							$("#fc-digital-sections").val(response.digital_sections);
							$("#fc-print-sections").val(response.print_sections);
							$("#fc-msg").html(response.msg);
						}
					}
			);
		}
		// Install Listeners to recalculate fees
		$(document).ready(function(){
			$("#fee-calculator input,select").each(function(){
				$(this).change(fee_calculator);
			});
		});
	</script>


	<script>
		function pauseVideo(div) {
			$("#" + div + " video").get(0).pause();
		}
		$("[id|=video]").on('shown.bs.modal', function(){
			$(this).find("video").get(0).play();
		});
		$("[id|=video]").on('hidden.bs.modal', function(){
			$(this).find("video").get(0).pause();
		});
	</script>

</body>

</html>
