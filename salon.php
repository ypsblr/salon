<?php
include_once("inc/session.php");
include_once("inc/salonlib.php");

if (isset($_REQUEST['id']) && strlen($_REQUEST['id']) == 6 ) {

$yearmonth = $_REQUEST['id'];

if (! ($salon = get_contest($yearmonth)))
	handle_error("This requested Salon is yet to publish results", __FILE__, __LINE__);

define("CONTEST_ARCHIVED", $salon['contest']['archived'] == '1');

$num_sections = sizeof($salon['sections']);

$video_folder = "";
$member_videos = [];
if (file_exists("salons/$yearmonth/blob/member_video.php")) {
	include("salons/$yearmonth/blob/member_video.php");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php include_once("inc/header.php"); ?>
<style>
.img-responsive {
	min-width: 60px;
	min-height: 60px;
}
.containerBox {
    position: relative;
    display: inline-block;
	width: 100%;
}
.thumbnail a > img {
	min-width: 80px;
	min-height: 80px;
}
.badge-color {
	background-color: #D26C22;
	font-size: 10px;
}
.badge-success {
	background-color: #28A745;
	font-size: 10px;
}
.badge-warning {
	background-color: #FFC107;
	font-size: 10px;
}
.badge-info {
	background-color: #17A2B8;
	font-size: 10px;
}
</style>
</head>

<body class="body-orange">

	<!-- Header -->
	<?php
		include "inc/navbar.php";
	?>

		<!-- Simple splash screen-->
	<div class="splash">
		<div class="color-line"></div>
		<div class="splash-title">
			<h1>LOADING THE PAGE...</h1>
			<p>Please Wait</p>
			<div class="spinner">
				<div class="rect1"></div>
				<div class="rect2"></div>
				<div class="rect3"></div>
				<div class="rect4"></div>
				<div class="rect5"></div>
			</div>
		</div>
	</div>


	<!-- Wrapper -->
	<div id="wrapper">
		<!-- Jumbotron -->
		<?php  include_once("inc/Slideshow.php") ;?>

		<!-- Page Header -->
		<div class="content">
			<div class="row">
			</div>
			<div class="row">
			</div>


			<div class="row" id="myTab">
				<!-- Award Display -->
				<div class="col-sm-9 col-md-9 col-lg-9" style="padding-left: 3%; ">

					<div class="hpanel">	<!-- Overll Results Panel -->
						<div class="panel-body" >

							<!-- 0 PANEL FOR DISPLAYING SALON INFORMATION -->
							<div id="salon-info" class="panel panel-info">
								<!-- SALON NAME -->
								<div class="panel-heading">
									<h2 class="text-center m-t-md">
										<a data-toggle="collapse" data-parent="#salon-info" href="#salon-details"><?=$salon['contest']['contest_name'];?></a>
									</h2>
								</div>
								<div id="salon-details" class="panel-collapse collapse in">
									<div class="panel-body">
										<!-- Salon Description -->
										<div class="row">
											<div class="col-sm-12">
												<?php
													if ($salon['contest']['chairman_message_blob'] != "") {
												?>
												<!-- Chairman Message -->
												<h3 class="headline text-color"><span class="border-color">Message from Chairman</span></h3>
												<p class="text text-justified"><?php echo merge_data($salon['contest']['chairman_message_blob'], $salon['values'], $yearmonth);?></p>
												<br>
												<?php
													}
													if ($salon['contest']['catalog'] != "" && $salon['contest']['catalog_ready'] == '1') {
												?>
												<p class="text-center" >
													<span style="padding-left: 20px; padding-right: 20px;">
														<a href="/catalog/<?=$salon['contest']['catalog_download'];?>" download class="btn btn-color">Download Catalog</a>
													</span>
													<span style="padding-left: 20px; padding-right: 20px;">
														<a href="/viewer/catalog.php?id=<?=$yearmonth;?>&catalog=<?=$salon['contest']['catalog'];?>" target="_blank" class="btn btn-color">View Catalog</a>
													</span>
												</p>
												<?php
													}
													if ( ( $salon['contest']['catalog_order_last_date'] != NULL ) && date("Y-m-d") <= $salon['contest']['catalog_order_last_date'] &&
														($salon['contest']['catalog_price_in_inr'] != "" || $salon['contest']['catalog_price_in_usd'] != "") ) {
												?>
												<p class="text-color"><big>You can order printed catalog</big></p>
												<p class="text text-justified" style="margin-left: 20px; margin-right: 20px;">
													You can order your personal copy of the printed catalog online by logging in. The link for ordering catalog is available
													under &quot;My Page&quot;. The catalog is available in the following formats:
												</p>
												<!-- Catalog in Indian prices -->
												<?php
														if ($salon['contest']['catalog_price_in_inr'] != "") {
												?>
												<ul style="margin-left: 30px; margin-right: 30px;">
												<?php
															$models = json_decode($salon['contest']['catalog_price_in_inr'], true);
															foreach ($models as $model) {
																$model_desc = $model['model'] . " @ Rs." . $model['price'];
																if ($model['postage'] != 0)
																	$model_desc .= " + shipping Rs." . $model['postage'];
												?>
													<li><?= $model_desc;?></li>
												<?php
															}
												?>
												</ul>
												<?php
														}
												?>
												<!-- Catalog in USD prices -->
												<?php
														if ($salon['contest']['catalog_price_in_usd'] != "") {
												?>
												<ul style="margin-left: 30px; margin-right: 30px;">
												<?php
															$models = json_decode($salon['contest']['catalog_price_in_usd'], true);
															foreach ($models as $model) {
																$model_desc = $model['model'] . " @ US$ " . $model['price'];
																if ($model['postage'] != 0)
																	$model_desc .= " + shipping US$ " . $model['postage'];
												?>
													<li><?= $model_desc;?></li>
												<?php
															}
												?>
												</ul>
												<?php
														}
												?>
												<?php
													}
													if (date("Y-m-d") >= $salon['contest']['exhibition_start_date'] && date("Y-m-d") <= $salon['contest']['exhibition_end_date'] &&
														isset($salon['exhibition']) && $salon['exhibition']['is_virtual'] && $salon['exhibition']['virtual_tour_ready']) {
												?>
												<div class="well"  style="background-color: white; padding : 8px;">
													<div class="row">
														<div class="col-sm-3"></div>
														<div class="col-sm-6">
															<a href="exhibition.php?contest=<?=$yearmonth;?>" class="btn btn-color" style="width: 100%; font-size: 1.5em;">Enter Virtual Exhibition</a>
														</div>
														<div class="col-sm-3"></div>
													</div>
												</div>
												<?php
													}
												?>
											</div>
										</div>
										<!-- Recognitions -->
										<div class="row">
											<div class="col-sm-12">
												<h4 class="text-color">Recognitions</h4>
												<?php show_recognitions($yearmonth); ?>
											</div>
										</div>
										<br>
									</div>
								</div>
							</div>

							<!-- 0.5 PANEL FOR DISPLAYING JURY DETAILS -->
							<div id="jury-info" class="panel panel-warning">
								<div class="panel-heading">
									<h2 class="text-center m-t-md">
										<a data-toggle="collapse" data-parent="#jury-info" href="#jury-details">Distinguished Jury</a>
									</h2>
								</div>
								<div id="jury-details" class="panel-collapse collapse">
									<div class="panel-body">
										<?php show_jury($yearmonth, $salon['sections']); ?>
									</div>
								</div>
							</div>

							<!-- 0.6 PANEL FOR DISPLAYING Results Description -->
							<div id="salon-results" class="panel panel-warning">
								<div class="panel-heading">
									<h2 class="text-center m-t-md">
										<a data-toggle="collapse" data-parent="#salon-results" href="#salon-result-details">Salon Results</a>
									</h2>
								</div>
								<div id="salon-result-details" class="panel-collapse collapse">
									<div class="panel-body">
										<?php echo merge_data($salon['contest']['results_description_blob'], $salon['values'], $yearmonth);?>
									</div>
								</div>
							</div>

							<!-- 0.8 PANEL FOR Salon ACCOLADES -->
							<?php
								if (file_exists("salons/$yearmonth/blob/accolades.php")) {
							?>
							<div id="salon-accolades" class="panel panel-warning">
								<div class="panel-heading">
									<h2 class="text-center m-t-md">
										<a data-toggle="collapse" data-parent="#salon-accolades" href="#salon-accolades-details">Accolades</a>
									</h2>
								</div>
								<div id="salon-accolades-details" class="panel-collapse collapse in">
									<div class="panel-body">
										<?php include("salons/$yearmonth/blob/accolades.php");?>
									</div>
								</div>
							</div>
							<?php
								}
							?>

							<!-- 0.7 PANEL FOR Salon STATISTICS -->
							<div id="salon-stats" class="panel panel-warning">
								<div class="panel-heading">
									<h2 class="text-center m-t-md">
										<a data-toggle="collapse" data-parent="#salon-stats" href="#salon-stat-details">Salon Statistics</a>
									</h2>
								</div>
								<div id="salon-stat-details" class="panel-collapse collapse in">
									<div class="panel-body">
										<?php include("inc/statistics.php");?>
									</div>
								</div>
							</div>

							<!-- 1 A PANEL GROUP FOR GROUPING SECTIONS -->
							<div class="panel-group" id="sections">
								<!-- 1.1 SECTION = CONTEST -->
								<div class="panel panel-default">
									<div class="panel-heading">
										<h2 class="primary-font">
											<a data-toggle="collapse" data-parent="#sections" href="#section-contest">OVERALL AWARDS</a>
										</h2>
									</div>
									<div id="section-contest" class="panel-collapse collapse">
										<div class="panel-body">
											<div class="panel-group" id="awards-contest">
												<!-- 1.1.1 BEST ENTRANT AWARDS -->
												<?php
													// $sql = "SELECT COUNT(*) AS num_awards FROM award WHERE yearmonth = '$yearmonth' AND section = 'CONTEST' AND award_type = 'entry' ";
													$sql = "SELECT COUNT(*) AS num_awards FROM award WHERE yearmonth = '$yearmonth' AND award_type = 'entry' ";
													$qaw = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
													$raw = mysqli_fetch_array($qaw);
													if ($raw["num_awards"] > 0)
														show_entry_awards($yearmonth);
												?>
												<!-- 1.1.2 show_special_picture_awards PICTURE AWARDS -->
												<?php
													$sql = "SELECT COUNT(*) AS num_awards FROM award WHERE yearmonth = '$yearmonth' AND section = 'CONTEST' AND award_type = 'pic' ";
													$qaw = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
													$raw = mysqli_fetch_array($qaw);
													if ($raw["num_awards"] > 0) {
												?>
												<div class="panel panel-default">
													<div class="panel-heading">
														<h3 class="primary-font">
															<a data-toggle="collapse" href="#section-CONTEST">Special Picture Awards</a>
														</h3>
													</div>
													<div id="section-CONTEST" class="panel-collapse collapse">
														<div class="panel-body">
															<?php show_special_picture_awards($yearmonth);?>
														</div>
													</div>
												</div>
												<?php
													}
												?>
												<!-- 1.1.3 BEST CLUB AWARDS -->
												<?php
													$sql = "SELECT COUNT(*) AS num_awards FROM award WHERE yearmonth = '$yearmonth' AND section = 'CONTEST' AND award_type = 'club' ";
													$qaw = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
													$raw = mysqli_fetch_array($qaw);
													if ($raw["num_awards"] > 0)
														show_club_awards($yearmonth, 'CONTEST');
												?>
											</div>		<!-- panel-group -->
										</div>			<!-- panel-body -->
									</div>
								</div>
								<!-- END OF SECTION = CONTEST -->
								<!-- 1.2 SECTION-WISE PICTURE AWARDS -->
								<?php
									foreach($salon['sections'] as $section => $section_row) {
										$section_tag = str_replace(" ", "_", $section);
										$sectionNumEntries = $section_row['num_entrants'];
										$sectionNumPictures = $section_row['num_pictures'];
								?>
								<div class="panel panel-default">
									<div class="panel-heading">
										<h2 class="primary-font">
											<a data-toggle="collapse" data-parent="#sections" href="#section-<?=$section_tag;?>"><?=$section;?></a>
											<small><small> ( <?=$sectionNumPictures;?> pictures from <?=$sectionNumEntries;?> participants )</small></small>
										</h2>
									</div>
									<div id="section-<?=$section_tag;?>" class="panel-collapse collapse">
										<div class="panel-body">
											<div class="panel-group" id="awards-<?=$section_tag;?>" >
												<?php show_picture_awards($yearmonth, $section);?>
												<?php show_picture_acceptances($yearmonth, $section);?>
											</div>
										</div>
									</div>
								</div>
								<?php
									}
								?>
							</div>
							<!-- 2 PORTFOLIOS -->
							<div class="panel-group" id="all-winners">
							<?php
								$sql = "SELECT DISTINCT award_group FROM award WHERE yearmonth = '$yearmonth' ";
								$qaw = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
								while ($raw = mysqli_fetch_array($qaw)) {
									$award_group_tag = str_replace(" ", "-", $raw['award_group']);
							?>
								<div id="portfolios-<?= $award_group_tag;?>" class="panel panel-default">
									<div class="panel-heading">
										<h2 class="primary-font">
											<a data-toggle="collapse" data-parent="#all-winners" href="#portfolio-list-<?= $award_group_tag;?>">
												All the Exhibitors in &quot;<?= $raw['award_group'];?>&quot; category
											</a>
										</h2>
									</div>
									<div id="portfolio-list-<?= $award_group_tag;?>" class="panel-collapse collapse">
										<div class="panel-body">
											<div class="row" style="padding-bottom: 15px;">
												<div class="col-sm-9"></div>
												<div class="col-sm-3">
													<a href="javascript:void(0)" class="btn btn-info pull-right print_all_winners"
															style="border-radius: 24px;"
															data-filter="pdf-<?= $award_group_tag;?>"
															data-group="<?= $raw['award_group'];?>" >
														<span style="color: white;"><i class="fa fa-download"></i> Download PDF</span>
													</a>
												</div>
											</div>
											<?php show_portfolios($yearmonth, $raw['award_group']);?>
										</div>
									</div>
								</div>
							<?php
								}
							?>
							</div>
							<hr>
						</div>
					</div>
				</div>  <!-- Left Column-7 Award Display -->
				<!-- Right Column - Sponsors etc -->
				<div class="col-sm-3 col-md-3 col-lg-3">

					<style>
						.carousel-inner img{ max-height:300px !important; }
						.com-avatar {margin: 4px auto; min-width: 40px; min-height: 40px; width:100%; }
					</style>

					<!-- Salon Committee -->
					<h3 class="headline text-color"><span class="border-color">Salon Committee</span></h3>
					<?php
						$sql = "SELECT * FROM team WHERE yearmonth = '$yearmonth' AND ( member_group = '' OR member_group = 'Salon Committee' ) ORDER BY level, sequence";
						$tq = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
						while ($tr = mysqli_fetch_array($tq)) {
					?>
					<div class="row">
						<div class="col-sm-3 col-md-2 col-lg-2">
							<div style="max-width:100%">
								<img class="img-responsive img-rounded com-avatar"
										src="/salons/<?=$yearmonth;?>/img/com/<?=$tr['avatar'];?>" alt="<?=$tr['member_name'];?>">
							</div>
						</div>
						<div class="col-sm-8 col-md-8 col-lg-8">
							<p><b><?php echo $tr['member_name'];?></b> <small><?php echo $tr['honors'];?></small></p>
							<p><i><?php echo $tr['role_name'];?></i></p>
						</div>
					</div>
					<div class="divider" style="margin-top: 4px; margin-bottom: 4px;"></div>
					<?php
						}
					?>

					<!-- External Support -->
					<?php
						$sql = "SELECT * FROM team WHERE yearmonth = '$yearmonth' AND member_group != 'Salon Committee' AND member_group != '' ORDER BY level, sequence";
						$tq = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
						if (mysqli_num_rows($tq) > 0) {
					?>
					<h3 class="headline text-color"><span class="border-color">External Support</span></h3>
					<?php
							while ($tr = mysqli_fetch_array($tq)) {
					?>
					<div class="row">
						<div class="col-sm-3 col-md-2 col-lg-2">
							<div style="max-width:100%">
								<img class="img-responsive img-rounded com-avatar"
										src="/salons/<?=$yearmonth;?>/img/com/<?=$tr['avatar'];?>" alt="<?=$tr['member_name'];?>">
							</div>
						</div>
						<div class="col-sm-8 col-md-8 col-lg-8">
							<p><b><?php echo $tr['member_name'];?></b> <small><?php echo $tr['honors'];?></small></p>
							<p><i><?php echo $tr['role_name'];?></i></p>
						</div>
					</div>
					<div class="divider" style="margin-top: 4px; margin-bottom: 4px;"></div>
					<?php
							}
						}
					?>

				</div>	<!-- Right Column - Sponsors etc -->
			</div> <!-- / .row -->
			<?php
				include("inc/footer.php");
			?>
		</div> <!-- / .container -->
	</div>	<!-- / .wrapper -->
    <!-- Style Toggle -->
    <!-- Style Toggle -->

	<?php include_once("inc/settingToggle.php") ;?>
    <!-- JavaScript
    ================================================== -->
	<?php include_once("inc/scripts.php"); ?>

	<script src="plugin/misc/js/lozad.js"></script>

	<!-- Initialize lozad -->
	<script>
		const observer = lozad();
		observer.observe();
	</script>

	<!-- Delayed loading of thumbails -->
	<script>
		$(document).ready(function(){
			$(".lazy-load").each(function(){
				$(this).attr("src", $(this).data("src"));
			});
		});
	</script>
	<!-- Form specific scripts -->
    <script src="plugin/lightbox/js/lightbox.min.js"></script>
	<script>
		$(document).ready(function(){
			$('html, body').animate({ scrollTop: $("#myTab").offset().top-100 }, 500);
		});

		$(".collapse").on("shown.bs.collapse", function (e) {
			//window.scrollTo(0, $("#sections").get(0).offsetTop);
			if (! $(this).get(0).id.startsWith("acceptance"))
				window.scrollTo(0, $(this).get(0).offsetTop);
			e.stopPropagation();
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

	<!-- Google Charts -->
	<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>	<!-- Graph Generator -->
<?php
	// Acceptance %
	for ($idx = 0; $idx < sizeof($stat_acc_list); ++ $idx) {
		$section = $stat_acc_list[$idx]['section'];
		$stub = $stat_acc_list[$idx]['stub'];
		$color = stub_color($stub);
		$cut_off = $stat_acc_list[$idx]['cut_off'];
		$num_pics = $stat_acc_list[$idx]['num_pics'];
		$num_accepted = $stat_acc_list[$idx]['num_accepted'];
		$percent_accepted = $stat_acc_list[$idx]['percent_accepted'];
?>
	<script>
		google.charts.load("current", {packages:["corechart"]});
		google.charts.setOnLoadCallback(function() {
			let data = google.visualization.arrayToDataTable([["Category", "Pictures"], ["Accepted", <?= $num_accepted;?>], ["Others", <?= $num_pics - $num_accepted;?>]]);
			let options = {
				legend : { position : "none" },
				pieSliceText : "value",
				slices : [{ offset : 0.1, color : '<?= $color;?>'}, {color : '#d7d7d7'}],
			};
			let chart = new google.visualization.PieChart(document.getElementById("stat-acc-<?= $stub;?>"));
			chart.draw(data, options);
		});
	</script>
<?php

	}
?>
<?php
	// Scoring Pattern
	for ($idx = 0; $idx < sizeof($stat_sp_list); ++ $idx) {
		$section = $stat_sp_list[$idx]['section'];
		$stub = $stat_sp_list[$idx]['stub'];
		$cut_off = $stat_sp_list[$idx]['cut_off'];
		$total_pics = $stat_sp_list[$idx]['total_pics'];
		$js_array = $stat_sp_list[$idx]['js_array'];
?>
	<script>
		google.charts.load("current", {packages:["corechart"]});
		google.charts.setOnLoadCallback(function() {
			let data = google.visualization.arrayToDataTable([<?= $js_array;?>]);
			let options = {
				legend : { position : "none" },
				bar : {groupWidth : "80%"},
				hAxis : { ticks : [3, 6, 9, 12, 15], title : "Total Score", },
				vAxis : {title : "# Pictures", },
			};
			let chart = new google.visualization.ColumnChart(document.getElementById("stat-sp-<?= $stub;?>"));
			chart.draw(data, options);
		});
	</script>
<?php
	}
?>

	<script>
		// Load thumbnails of winning pictures for a profile
		function revealWins(profile_id, row_target, column) {
			// Remove existing open section
			$(".album-insert").remove();
			let yearmonth = <?= $yearmonth; ?>;
			$.post("ajax/get_winning_images.php", {yearmonth, profile_id, column}, function (response){
				// response is html to be inserted
				// $("#" + row_target).insertAfter(response);
				$(response).insertAfter("#" + row_target);
				// Set Listeners to play/pause video
				$("[id|=insertvideo]").on('shown.bs.modal', function(){
					$(this).find("video").get(0).play();
				});
				$("[id|=insertvideo]").on('hidden.bs.modal', function(){
					$(this).find("video").get(0).pause();
				});
			});
		}
		// Hide all Wins
		function hideWins() {
			$(".album-insert").remove();
		}
	</script>

	<!-- Generate PDF using the definition created in salonlib.php -->
	<script type="text/javascript" charset="utf-8" src='plugin/pdfmake/pdfmake.min.js'></script>
	<script type="text/javascript" charset="utf-8" src='plugin/pdfmake/vfs_fonts.js'></script>
	<script>
		function generate_body(filter) {
			let columns = 0;
			let rows = [];
			let row = [];
			$(".pdf-cell").filter("[data-filter='" + filter + "']").each(function (){
				row.push(JSON.parse($(this).val()));
				++ columns;
				if (columns == 3) {
					rows.push(row);
					row = [];
					columns = 0;
				}
			});
			if (row.length > 0) {
				// Fill the remaining columns
				for (let i = row.length; i < 3; ++i) {
					row[i] = "";
				}
				rows.push(row);
			}
			return rows;
		}

		$(".print_all_winners").click(function () {
			showLoader(this);
			let award_group = $(this).data("group");
			let group_filter = $(this).data("filter");
			// Cell Layouts for use in Table
			pdfMake.tableLayouts = {
					winner_cell : {
						hLineWidth : () => { return 0.5;},
						vLineWidth : () => { return 0.5;},
						vLineColor : () => { return "#aaa";},
						hLineColor : () => { return "#aaa";},
						paddingLeft : () => { return 4;},
						paddingRight : () => { return 4;},
						paddingTop : () => { return 4;},
						paddingBottom : () => { return 4;},
					},
					header_footer : {
						hLineWidth : () => { return 0.5;},
						vLineWidth : () => { return 0;},
						vLineColor : () => { return "black";},
						hLineColor : () => { return "black";},
						paddingLeft : () => { return 4;},
						paddingRight : () => { return 4;},
						paddingTop : () => { return 8;},
						paddingBottom : () => { return 8;},
					},
			};

			// Gotham font definition
			// pdfMake.fonts = {
			// 	Gotham : {
			// 		normal : "http://salon.localhost/fonts/GothamCond-Book.ttf",
			// 		bold : "http://salon.localhost/fonts/GothamCond-Bold.ttf",
			// 		italics : "http://salon.localhost/fonts/GothamCond-BookItalic.ttf",
			// 		bolditalics : "http://salon.localhost/fonts/GothamCond-BoldItalic.ttf",
			// 	}
			// }

			// Document Definition
			let pdf_def = {
					info : {
						title : "<?= $salon['contest']['contest_name'];?> - List of Exhibitors",
						author : "Youth Photographic Society"
					},
					pageSize : "A4",
					pageOrientation : "portrait",
					pageMargins : [36, 72],
					styles : {
								h1 : { fontSize : 22, bold : true, color : "black" },
								h2 : { fontSize : 18, bold : true, color : "black" },
								h3 : { fontSize : 14, italics : true, bold : true, color : "black" },
								normal : { fontSize : 11, color : "black" },
								bold : { fontSize : 11, color : "black", bold : true },
								name : { fontSize : 10, color : "#007bff", bold : true },
								honors : { fontSize : 6, color : "#aaa", italics : true },
								club : { fontSize : 8, color : "#444" },
								wins : { fontSize : 10, color : "#DC3545", bold : true },
								award : { fontSize : 10, color : "black", bold : true },
								acceptance : { fontSize : 10, color : "#444" },
					},
					defaultStyle : { fontSize : 11, color : "black" },
					header : function(currentPage, pageCount, pageSize) {
							if (currentPage > 1) {
								return [
										{
											columns: [
												{ text: "<?= $salon['contest']['contest_name'];?>", bold: true, border: [false, false, false, true], margin: [36, 36, 0, 0], },
												{
													text: "List of Exhibitors in " + award_group + " Group",
													bold: true, alignment : "right",
													border: [false, false, false, true],
													margin: [0, 36, 36, 0],
												},
											]
										},
										{
											canvas : [
												{ type: 'line', x1: 36, y1: 0, x2: pageSize.width - 36, y2: 0, lineWidth: 1, lineColor: '#aaa' },
											]
										},
								];
							}
							else {
								return null;
							}
					},
					footer : function(currentPage, pageCount, pageSize) {
						return [
							{
								canvas : [
									{ type: 'line', x1: 36, y1: 0, x2: pageSize.width - 36, y2: 0, lineWidth: 1, lineColor: '#aaa' },
								]
							},
							{
								columns: [
									{ text: "Page " + currentPage.toString(), alignment : "right", border: [false, true, false, false], margin: [36, 0, 36, 0], },
								],
							},
						];
					},
					content : [
								{
									columns : [
												{
													width : "auto",
													stack : [
																{ image : "ypslogo", width : 72 },
													],
												},
												{
													width : "*",
													stack : [
																{ text : "Youth Photographic Society", style : "h1" },
																{ text : "<?= $salon['contest']['contest_name'];?>", style : "h2" },
																{ text : "List of Exhibitors in " + award_group + " Group", style : "h2", margin : [0, 4, 0, 8] },
													],
												},
									],
									columnGap : 15,
								},
								{
										layout : "winner_cell",
										table : {
													widths : [165, 165, 165],
													dontBreakRows : true,
													body : generate_body(group_filter),
										},
								},
					],
					images : {
								ypslogo : '<?= http_method() . $_SERVER['SERVER_NAME'] . '/img/ypsLogo.png';?>',
					},
			};
			$(".pdf-avatar").filter("[data-filter='" + group_filter + "']").each(function(index, img){
				pdf_def.images[$(img).data("avatar")] = "<?= http_method() . $_SERVER['SERVER_NAME'];?>" + $(img).attr("data-src");
			});
			pdfMake.createPdf(pdf_def).download("<?= $salon['contest']['contest_name'];?> - " + award_group + " - Exhibitor List");
			hideLoader(this);
		});
	</script>

</body>
</html>
<?php
}
else
{
	header("Location: /index.php");
	printf("<script>location.href='/index.php'</script>");
}

?>
