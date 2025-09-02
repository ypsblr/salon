<?php
// Display only fully scored images
// session_start();
include("inc/session.php");
include("inc/connect.php");
include("inc/lib.php");

function debug_to_console($data) {
    $output = $data;
    if (is_array($output))
        $output = implode(',', $output);

    echo "<script>console.log('Debug Objects: " . $output . "' );</script>";
}

function array_add_quotes($array) {
	$new_array = array();
	foreach($array AS $key => $value)
		$new_array[$key] = "'" . $value . "'";

	return $new_array;
}

function jury_seq($user_id) {
	global $jury_list;

	foreach($jury_list as $jury)
		if ($jury['user_id'] == $user_id)
			return intval($jury['jurynumber']);

	return false;
}


if(isset($_SESSION['jury_id']) ) {  // to prevent being run from command line and to facilitate session variables

    debug_to_console(1);

	$jury_id = $_SESSION['jury_id'];

    debug_to_console(2);

	// Set session in progress
	$session_set = false;
	
    debug_to_console($_REQUEST['yearmonth']);
    debug_to_console($_REQUEST['section']);
    debug_to_console($_REQUEST['group']);

    debug_to_console(($_SESSION['jury_yearmonth']));
    debug_to_console($_SESSION['section']);
    debug_to_console($_SESSION['award_group']);
    
	if ( isset($_REQUEST['yearmonth']) && isset($_REQUEST['section']) && isset($_REQUEST['group']) ) {
        debug_to_console(3);
        debug_to_console($_REQUEST['yearmonth']);
		$jury_yearmonth = $_REQUEST['yearmonth'];
		$section = $_REQUEST['section'];
		$award_group = $_REQUEST['group'];
		$session_set = true;
	}
	else if (isset($_SESSION['jury_yearmonth']) && isset($_SESSION['section']) && isset($_SESSION['award_group'])) {
	    debug_to_console(4);
		$jury_yearmonth = $_SESSION['jury_yearmonth'];
		
		$section = $_SESSION['section'];
		$award_group = $_SESSION['award_group'];
		$session_set = true;
	}
	else {
	    debug_to_console(5);
		// Determine the contest and section to displayed from jury session settings
		// Jurypanel allows only one section and award_group is enabled at a time
		$sql = "SELECT * FROM jury_session WHERE session_open = '1' AND show_display = '1' order by yearmonth DESC";
		$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		if (mysqli_num_rows($query) == 0)
			die_with_error("No section has been configured for display.", __FILE__, __LINE__, true);

		// Get a list of Jury sessions open so that user can choose from a dialog box
		// Set the first session as the default
		$first = true;
		$js_list = [];
		while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
		    debug_to_console(6);
			$js_list[] = $row;
			if ($first) {
				$jury_yearmonth = $row['yearmonth'];
				$section = $row['section'];
				$award_group = $row['award_group'];
			}
			$first = false;
		}
		$session_set = (sizeof($js_list) == 1);
	}

	$_SESSION['jury_yearmonth'] = $jury_yearmonth;
	$_SESSION['section'] = $section;
	$_SESSION['award_group'] = $award_group;

	// Set entrant_categories for Award Group
	$sql  = "SELECT entrant_category FROM entrant_category ";
	$sql .= " WHERE yearmonth = '$jury_yearmonth' ";
	if ($award_group != "ALL")
		$sql .= "   AND award_group = '$award_group' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$categories = array();
    debug_to_console(7);

    while($row = mysqli_fetch_array($query))
		$categories[] = $row['entrant_category'];
	$_SESSION['categories'] = implode(",", $categories);
	$entrant_filter = " AND entry.entrant_category IN (" . implode(",", array_add_quotes($categories)) . ") ";

	// Load Contest Name
	$sql = "SELECT contest_name FROM contest WHERE yearmonth = '$jury_yearmonth' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$contest = mysqli_fetch_array($query);

	// Load Jury List
	$sql  = "SELECT user.user_id, user_name, avatar, honors, jurynumber FROM assignment, user ";
	$sql .= " WHERE assignment.yearmonth = '$jury_yearmonth' ";
	$sql .= "   AND assignment.section = '$section' ";
	$sql .= "   AND assignment.user_id = user.user_id  ";
	$sql .= " ORDER BY jurynumber";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$num_juries = mysqli_num_rows($query);
	$jury_list = [];
	while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC))
		$jury_list[] = $row;

	// Calculate Number of Pictures
	$sql  = "SELECT COUNT(*) AS total_pics ";
	$sql .= "  FROM pic, entry ";
	$sql .= " WHERE pic.yearmonth = '$jury_yearmonth' ";
	$sql .= "   AND pic.section = '$section' ";
	$sql .= "   AND entry.yearmonth = pic.yearmonth ";
	$sql .= "   AND entry.profile_id = pic.profile_id ";
	$sql .= $entrant_filter;
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$row = mysqli_fetch_array($query);
	$total_pics = $row['total_pics'];
?>
<!DOCTYPE html>
<html>
<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
	<!-- <meta http-equiv="refresh" content="10"> -->

    <!-- Page title -->
    <title>Youth Photographic | Fully scored pictures</title>

    <!-- Place favicon.ico and apple-touch-icon.png in the root directory -->
    <!--<link rel="shortcut icon" type="image/ico" href="favicon.ico" />-->

    <!-- Vendor styles -->
    <link rel="stylesheet" href="plugin/fontawesome/css/font-awesome.css" />
    <link rel="stylesheet" href="plugin/metisMenu/dist/metisMenu.css" />
    <link rel="stylesheet" href="plugin/animate.css/animate.css" />
    <link rel="stylesheet" href="plugin/bootstrap/dist/css/bootstrap.css" />
    <link rel="stylesheet" href="plugin/blueimp-gallery/css/blueimp-gallery.min.css" />
	<link rel="stylesheet" href="plugin/bootstrap-star-rating/css/star-rating.css" />

    <!-- App styles -->
    <!-- <link rel="stylesheet" href="fonts/pe-icon-7-stroke/css/pe-icon-7-stroke.css" /> -->
    <!-- <link rel="stylesheet" href="fonts/pe-icon-7-stroke/css/helper.css" /> -->
    <link rel="stylesheet" href="custom/css/style.css">


<style type="text/css">
	body {
		background-color: black;
	}
	.wrapper {
		border: 0;
	}
	div.centered {
		height: 100%;
		display: flex;
		justify-content: center;
		align-items: center;
	}
	div.history-img {
		border: 1px solid #444;
	}
	#menu {
		background-color: black;
		border: 0;
	}
	.info-carousel {
		border-bottom : 1px solid #444;
	}
</style>

</head>
<body>
	<div class="wrapper">
		<!-- Layer Top -->
		<!-- Left - Main picture Title and Current Picture -->
		<!-- Right - a) Carousel showing Salon details and Jury Info -->
		<!--         b) Individual scores -->
		<!--         c) Total Score -->
		<div class="row" style="height: 85%;">
			<!-- Left - Main Picture -->
			<!-- Display Current Picture -->
			<?php
			    debug_to_console(0);
				$pic_title = "";
				debug_to_console($jury_yearmonth);
				$pic_tn = "/salons/$jury_yearmonth/img/preview.png";
				// $pic_tn = "img/preview.png";
				$total_rating = 0;
				$rating_list = [];
				foreach($jury_list as $jury)
					$rating_list[] = 0;
				// fix row height for each rating display
				$carousel_height = 0.2;		// 20%
				$total_score_height = 0.2;	// 20%
				$individual_score_height = round((1 - $carousel_height - $total_score_height) / $num_juries, 2);
				debug_to_console(1);
			?>
			<div class="col-sm-10" style="height: 100%;" >
				<div class="row" style="width: 100%; height: 8%;">
					<div class="col-sm-12 centered" style="height: 30%;">
						<div style="height: 10px; width: 10px; opacity: 0.1;">
							<img id="next-pic" style="max-width: 100%; max-height: 100%;" >
						</div>
						<div style="color: #888;" id="current-position">0/0/<?= $total_pics;?></div>
						<div class="text-warning text-center lead" style="width: 100%;padding-top:15px;"><?= $contest['contest_name'];?> - <?= $section;?><!-- - <?= $award_group;?>--></div>
					</div>
					<div class="col-sm-12 centered" style="height: 70%;">
						<h1 class="text-info text-center" style="width: 100%;padding-top:15px;"><b><span id="main-pic-title"><?= $pic_title;?></span></b></h1>
					</div>
				</div>
				<div class="row" style="width: 100%; height: 92%;">
					<div class="col-sm-12 centered">
						<img id="main-pic" src="<?= $pic_tn;?>" style="max-width: 95%; max-height: 95%;padding-top:5px;" >
					</div>
				</div>
			</div>
			<!-- Right -->
			<div class="col-sm-2" style="height: 100%; padding-bottom: 20px;" >
				<!-- Information Carousel 20% -->
				<div class="row" style="height: <?= round($carousel_height * 100, 0);?>%; ">
					<div class="col-sm-12 " style="padding: 8px; height: 100%; text-align: center;" >
						<!-- YPS Logo Displaying When Started -->
						<div class="centered info-carousel" id="carousel-yps">
							<div class="row" style="width: 100%; height: 100%; padding-bottom: 15px;">
								<div class="col-sm-12" style="height: 80%;">
									<a href="/jurypanel/index.php">
										<img src="../../img/ypsLogo.png" style="max-width: 100%; max-height: 100%;">
									</a>
								</div>
								<div class="col-sm-12" style="height: 20%;">
									<div class="text-info text-center"><b>53 Years Young</b></div>
								</div>
							</div>
						</div>
						<!-- Jury Profiles -->
						<?php
							for ($i = 0; $i < $num_juries; ++ $i) {
							    debug_to_console(2);
						?>
						<div class="centered info-carousel" id="carousel-<?= $i;?>" style="display: none;" >
							<div class="row" style="width: 100%; height: 100%; padding-bottom: 15px;">
								<div class="col-sm-12" style="height: 80%;">
									<img src="../../res/jury/<?= $jury_list[$i]['avatar'];?>" style="max-width: 80%; max-height: 80%;">
								</div>
								<div class="col-sm-12" style="height: 20%;">
									<div class="text-info text-center"><b><?= $jury_list[$i]['user_name'];?></b></div>
								</div>
							</div>
						</div>
						<?php
							}
						?>
					</div>
				</div>
				<!-- Individual Scores - divide by number of scores -->
				<?php
				    debug_to_console(3);
					for ($i = 0; $i < sizeof($rating_list); ++ $i) {
					    debug_to_console($rating_list[$i]);
						if (isset($rating_list[$i]) && $rating_list[$i] != "") {
							list($jury, $rating) = explode("-", $rating_list[$i]);
							if ($rating == "")
							    $rating = "0";
							$rating_img = "img/score-$rating.png";
						}
						else {
							$rating_img = "img/score-0.png";
						}
				?>
				<div class="row" style="height: <?= round($individual_score_height * 100, 0);?>%; ">
					<div class="col-sm-12" style="padding: 8px; height: 100%; text-align: center;">
						<img id="main-pic-rating-<?= $i;?>" src="<?= $rating_img;?>" style="max-width: 75%; max-height: 75%; opacity: 0.8;" >
					</div>
				</div>
				<?php
					}
				?>
				<!-- Total Score 20% -->
				<div class="row" style="height: <?= round($total_score_height * 100, 0);?>%;">
					<!-- Render Total Score -->
					<div class="col-sm-12" style="padding: 8px; height: 100%; text-align: center; border: 1px solid #aaa;">
						<div style="height:90%;">
							<img id="main-pic-rating-total" src="img/score-<?= $total_rating;?>.png" style="max-width: 100%; max-height: 90%;"  >
						</div>
						<div class="text-info text-center"><b>TOTAL</b></div>
					</div>
				</div>

			</div>
		</div>
		<!-- Display Previous 6 pictures with total scores -->
		<div class="row" style="height: 15%;">
			<?php
				for ($i = 1; $i <= 6; ++ $i) {
				    debug_to_console(4);
					// $pic_tn = "img/preview.png";
					$pic_tn = "/salons/$jury_yearmonth/img/preview.png";
					$total_rating_img = "img/score-0.png";
			?>
			<div class="col-sm-2 history-img" style="height: 100%;">
				<div class="row" style="height: 100%;">
					<div class="col-sm-8 centered" style="padding-left: 10px; padding-right: 10px;">
						<img id="hist-pic-<?= $i;?>" src="<?= $pic_tn;?>" style="max-width: 100%; max-height: 100%;" >
					</div>
					<div class="col-sm-4 centered" style="padding-left: 0px; padding-right: 10px;">
						<img id="hist-pic-rating-<?= $i;?>" src="<?= $total_rating_img;?>" style="max-width: 100%; max-height: 100%;" >
					</div>
				</div>
			</div>
			<?php
				}
			?>
		</div>
	</div>

	<!-- Force selection of section with a Modal Dialog -->
	<?php
		if ( isset($js_list) && sizeof($js_list) > 1 ) {
		    debug_to_console(5);
	?>
	<div class="modal" id="select-session" tabindex="-1" role="dialog">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">Select Jury Session</h5>
				</div>
				<div class="modal-body">
					<h3 class="text-color">Open Sessions</h3>
					<?php
						foreach ( $js_list as $js ) {
					?>
					<div class="row" style="padding-bottom: 15px;">
						<div class="col-sm-4"><?= $js['section'];?></div>
						<div class="col-sm-4"><?= $js['award_group'];?></div>
						<div class="col-sm-4">
							<a class="btn btn-info"
								href="display_remote.php?yearmonth=<?= $js['yearmonth'];?>&section=<?= $js['section'];?>&group=<?= $js['award_group'];?>" >
									Display this
							</a>
						</div>
					</div>
					<?php
						}
					?>
				</div>
			</div>
		</div>
	</div>
	<?php
		}
	?>

	<!-- Instructions -->
	<div class="modal" id="instructions" tabindex="-1" role="dialog">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">Keyboard Controls</h5>
  					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
  					</button>
				</div>
				<div class="modal-body">
					<p class="text-center text-danger" id="data-loading">Loading Data... Please wait...</p>
					<div id="data-loaded" style="display: none;">
	  					<p>
							Display starts in paused mode ready to start from the picture that was displayed last.
							Initially a placeholder preview is shown at the start. Pressing &quot;<b>CTRL-S</b>&quot; starts
							the display rolling.
						</p>
						<p>Press <b>CTRL-S</b> to start from the last displayed picture.</p>
						<p>Press <b>CTRL-I</b> to bring up this instruction page.</p>
						<p>Click on the rolling YPS Logo on the top right to close and exit.</p>
						<p>Other keys that can be used are:</p>
						<div style="width: 100%; padding-left: 30px; padding-right: 30px;">
							<div class="row">
								<div class="col-sm-6"><b>CTRL-P</b> - Pause</div>
								<div class="col-sm-6"><b>CTRL-R</b> - Resume</div>
							</div>
							<div class="row">
								<div class="col-sm-6"><b>CTRL-B</b> - Beginning</div>
								<div class="col-sm-6"><b>CTRL-E</b> - End/Last</div>
							</div>
							<div class="row">
								<div class="col-sm-6"><b>CTRL-&lt;</b> - Back 10 pictures</div>
								<div class="col-sm-6"><b>CTRL-&gt;</b> - Forward 10 pictures</div>
							</div>
							<div class="row">
								<div class="col-sm-6"><b>CTRL-[</b> - Back 50 pictures</div>
								<div class="col-sm-6"><b>CTRL-]</b> - Forward 50 pictures</div>
							</div>
						</div>
						<div style="width: 100%; padding-left: 30px; padding-right: 30px;">
							<br>
							<label>Picture Display Time in Seconds : </label>
							<input type="number" id="display-time" min="3" max="20" value="5" />
							<a id="set-display-time" class="btn btn-info" >SET</a>
						</div>
					</div>
				</div>
				<div class="modal-footer">
  					<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</div>


<!-- Vendor scripts -->
<script src="plugin/jquery/dist/jquery.min.js"></script>
<script src="plugin/slimScroll/jquery.slimscroll.min.js"></script>
<script src="plugin/bootstrap/dist/js/bootstrap.min.js"></script>
<script src="plugin/metisMenu/dist/metisMenu.min.js"></script>
<script src="plugin/iCheck/icheck.min.js"></script>
<script src="plugin/sparkline/index.js"></script>
<script src="plugin/jquery-ui/jquery-ui.min.js"></script>

<!-- App scripts -->
<script src="custom/js/homer.js"></script>
<script src="plugin/sparkline/index.js"></script>
<script src="plugin/jquery-validation/jquery.validate.min.js"></script>

<!--<script>
	// Set wrapper to page height
	$(window).load(function (){
		$("div.wrapper").css("height", $(window).height());
		$("div.wrapper").css("width", $(window).width());
	});
</script>-->


<script>
	// Set wrapper to page height and width on load and resize
	$(window).on("load resize", function (){
		$("div.wrapper").css({
			"height": $(window).height(),
			"width": $(window).width()
		});
	});
</script>

<script>
	// Keep rotating the Carousel every 3 seconds
	var carousel_index = 0;
	var showing_yps = true;
	var carousel_max = <?= $num_juries;?>;
	$(document).ready(function(){
		// Carousel Display of Juries
		setInterval(function() {
			$(".info-carousel").hide();
			if (showing_yps) {
				++ carousel_index;
				if (carousel_index >= carousel_max)
					carousel_index = 0;
				$("#carousel-" + carousel_index).show();
				showing_yps = false;
			}
			else {
				$("#carousel-yps").show();
				showing_yps = true;
			}
		}, 3000);
	});
</script>
<?php
	if ($session_set) {
?>
<script>
	var instructionDisplay = false;			// Instruction is being displayed
	$(document).ready(function(){
		// Display Instructions modal
		$("#instructions").modal();
		instructionDisplay = true;

		// Handler to resume display when the instructions is closed
		$("#instructions").on("hidden.bs.modal", function(){
			instructionDisplay = false;
		});
	});
</script>
<?php
	}
	else {
?>
<script>
	$(document).ready(function(){
		$("#select-session").modal({keyboard: false});
	});
</script>
<?php
	}
?>


<script>
	// New functions - June 27, 2021
	var num_juries = "<?= $num_juries;?>";		// Number of Juries
	const DATA_LOAD_INTERVAL = 2000;			// Every 5 seconds
	const DATA_LOAD_NODATA = 15000;				// Wait for 15 seconds before the next call if there is no new data
	const DATA_ERR_WAIT = 60000;				// Wait for 60 seconds before trying again
	var PAGE_SHOW_INTERVAL = 5000;				// Keep picture display on for 6 seconds

	// Set Page show interval
	$(document).ready(function(){
		$("#set-display-time").click(function(){
			let display_time = $("#display-time").val();
			if (display_time >=3 && display_time <= 15)
				PAGE_SHOW_INTERVAL = display_time * 1000;
		});
	});

	var displayTimer;

	// The following array is initialized from calling display_remote_getratings.php with time set to null. It will also return the currentIndex
	var ratings = []; // array of objects containing ratings
	var load_rating_time = null;
	var load_offset = 0;		// For situations where number of ratings per timestamp is large (due to bulk sync issue)
	var load_limit = 1000;		// 1000 picture details at a time starting with the offset
	var displayStarted = false;				// To prevent starting multiple times
	var displayIndex = ratings.length - 1; // Current Picture
	var displayPaused = true;				// Start with paused display
	var waitingToLoad = false;

	// Session save and restore
	let localdata = window.localStorage;
	function startDisplay() {
		let saved_yearmonth = localdata.getItem("jury_yearmonth");
		let saved_section = localdata.getItem("jury_section");
		let saved_award_group = localdata.getItem("award_group");
		let saved_display_index = localdata.getItem("display_index");

		if ( (saved_yearmonth != null && saved_yearmonth == '<?= $jury_yearmonth;?>' ) &&
			 (saved_section != null && saved_section == '<?= $section;?>' ) &&
			 (saved_award_group != null && saved_award_group == '<?= $award_group;?>') ) {
			if (saved_display_index == null || saved_display_index > (ratings.length -1)) {
				// Position at the end
				displayIndex = ratings.length -2;
				if (displayIndex < -1)
					displayIndex = -1;
			}
			else {
				displayIndex = saved_display_index - 1;
				if (displayIndex < -1)
					displayIndex = -1;
			}
			localdata.setItem("display_index", displayIndex);
		}
		else {
			// Save current information
			localdata.setItem("jury_yearmonth", '<?= $jury_yearmonth;?>');
			localdata.setItem("jury_section", '<?= $section;?>');
			localdata.setItem("award_group", '<?= $award_group;?>');
			localdata.setItem("display_index", displayIndex);
		}
		// if (displayIndex == -1 && ratings.length > '$display_index')
		// 	displayIndex = '$display_index';

		displayStarted  = true;
		displayPaused = false;

	};

	// Keep displaying pictures from ratings buffer
	function showPage() {
		// Check if there is new data
		if ( (! displayPaused) && (! instructionDisplay) && (! waitingToLoad) && displayIndex < (ratings.length - 1) ) {
			// Shift History of Pictures by 1
			// for (let i = 6; i > 1; --i) {
			// 	$("#hist-pic-" + i).attr("src", $("#hist-pic-" + (i - 1).toString()).attr("src"));
			// 	$("#hist-pic-rating-" + i).attr("src", $("#hist-pic-rating-" + (i - 1).toString()).attr("src"));
			// }
			// Move main picture to History
			// $("#hist-pic-1").attr("src", $("#main-pic").attr("src"));
			// $("#hist-pic-rating-1").attr("src", $("#main-pic-rating-total").attr("src"));

			// Render Main Picture from the new rating
			++ displayIndex;
			$("#current-position").html((displayIndex + 1).toString() + "/" + ratings.length + "/<?= $total_pics;?>");
			localdata.setItem("display_index", displayIndex);

			// Change Display Source
			let mainPic = $("#main-pic").attr("src");
			let mainPicRating = $("#main-pic-rating-total").attr("src");
			$("#main-pic").attr("src", ratings[displayIndex].pic_tn);
			waitingToLoad = true;
			$("#main-pic")
				.on("load", function(e) {
					if (this.complete) {
						console.log("Completed loading " + ratings[displayIndex].title);
						// Remove the on load handler
						$(this).off("load");
						// Display and allow picture to advance
						waitingToLoad = false;
						$("#main-pic-title").html(ratings[displayIndex].title);
						for (let i = 0; i < num_juries; ++ i) {
							$("#main-pic-rating-" + i).attr("src", "img/score-" + ratings[displayIndex].rating_list[i] + ".png");
						}
						$("#main-pic-rating-total").attr("src", "img/score-" + ratings[displayIndex].total_rating + ".png");
						// Shift History of Pictures by 1
						for (let i = 6; i > 1; --i) {
							$("#hist-pic-" + i).attr("src", $("#hist-pic-" + (i - 1).toString()).attr("src"));
							$("#hist-pic-rating-" + i).attr("src", $("#hist-pic-rating-" + (i - 1).toString()).attr("src"));
						}
						// Move main picture to History
						$("#hist-pic-1").attr("src", mainPic);
						$("#hist-pic-rating-1").attr("src", mainPicRating);
					}
					else
						console.log("Loading" + ratings[displayIndex].title);
				})
				.on("error", function(){
					console.log("Error Loading" + ratings[displayIndex].title);
					// Remove the on load Handler
					$(this).off("load");
					waitingToLoad = false;	// Skip this image and move to the next
				});

			// Load next picture ahead if available
			if (displayIndex < (ratings.length - 1)) {
				$("#next-pic").attr("src", ratings[displayIndex + 1].pic_tn);
			}
		}
		// Unless display is paused, fire showPage again after an interval
		if (! displayPaused)
			displayTimer = setTimeout(showPage, PAGE_SHOW_INTERVAL);
	}

	// Keep Loading Data into the ratings array in the background
	// Check and push rating data to the end
	function appendToRating(pic) {
		// Find first occurrence for this picture and update if rating has changed
		let index = ratings.length - 1;
		let found = false;
		for (;index >= 0 && ratings[index].rating_time >= pic.rating_time; --index) {
			if (ratings[index].rating_time == pic.rating_time && ratings[index].profile_id == pic.profile_id &&
					ratings[index].pic_id == pic.pic_id) {
				found = true;
				if (ratings[index].total_rating != pic.total_rating) {
					// if yet to be displayed, replace value. If already displayed add
					if (index > displayIndex)
						ratings[index].total_rating = pic.total_rating;
					else
						ratings.push(pic);
				}
			}
		}
		// If there is no rating, add
		if (! found)
			ratings.push(pic);
	}
	// Load Data by invoking ajax service
	function loadData() {
		let param = {
			rating_time : null,
			profile_id : 0,
			pic_id : 0,
			total_rating : 0,
			offset : 0,				// offset for situations when number of
			limit : load_limit,		// Max data per fetch
		};
		if (ratings.length != 0) {
			param = {
				// rating_time : ratings[ratings.length - 1].rating_time,
				rating_time : load_rating_time,
				profile_id : ratings[ratings.length - 1].profile_id,
				pic_id : ratings[ratings.length - 1].pic_id,
				total_rating : ratings[ratings.length - 1].total_rating,
				offset : load_offset,
				limit : load_limit,
				// offset : ratings.filter(function(rating, index) { return rating.rating_time == ratings[ratings.length - 1].rating_time}).length,
			};
		}
		$.ajax("ajax/display_remote_getratings.php", {
				method:		"POST",
				data:		param,
				success:	function (response, status) {
								if (status == "success") {
									let ret = JSON.parse(response);		// save the returned data
									if (ret.status == "OK") {
										if (ret.pic_returned) {
											console.log("@ " + ratings.length + " For " + param.rating_time + "/" + param.profile_id + "/" + param.pic_id + "/" + param.total_rating + " returned " + ret.pics.length + " records");
											// Control Instructions Page Display to help wait for data to load
											if (ret.pics.length < load_limit) {
												$("#data-loading").hide();
												$("#data-loaded").show();
											}

											// Normal operation
											let countBeforeAppend = ratings.length;
											ret.pics.forEach(function(pic){
												appendToRating(pic);
											});

											// Set offset if returned values have the same timestamp at the start and the end
											load_rating_time = ret.pics[ret.pics.length - 1].rating_time;
											if (load_rating_time == ret.pics[0].rating_time)
												load_offset += ret.pics.length;
											else
												load_offset = 0;

											if (countBeforeAppend < ratings.length)
												setTimeout(loadData, DATA_LOAD_INTERVAL);	// Retry after the picture has displayed
											else
												setTimeout(loadData, DATA_LOAD_NODATA);	// Retry after longer wait if previous call did not return data
										}
										else {
											setTimeout(loadData, DATA_LOAD_NODATA);	// Retry after longer wait if previous call dis not return data
										}
									}
									else {
										// Previous operation was not successful. Load after a long time
										setTimeout(loadData, DATA_ERR_WAIT);
									}
								}
								else
									setTimeout(loadData, DATA_ERR_WAIT);
							},
				error:		function () {
								setTimeout(loadData, DATA_ERR_WAIT);		// Give some time to recover from error and load after 15 seconds
							}
		});
	}

	// Kick start display
    $(document).ready(function(){
		// setTimeout(loadCurPic, 1000);		// Load first picture after a second after document loads
		loadData();		// Load first picture after a second after document loads
		// showPage();	// Does not automatically start running
    });

	// Keyboard Functions to control current index
	$(document).ready(function() {
		let ctrlKeyPressed = false;
		// Handle Control Key press
		$(document).keyup(function(e) {
			e.preventDefault();
			if (! instructionDisplay) {
				switch (e.keyCode) {
					// CTRL Key
					case 17 : {
						ctrlKeyPressed = false;
						break;
					}
				}
			}
		});
		// Install keyboard handler
		$(document).keydown(function(e) {
			e.preventDefault();
			if (! instructionDisplay) {
				switch (e.keyCode) {
					// CTRL Key
					case 17 : {
						ctrlKeyPressed = true;
						break;
					}
					// 'I' - Instructions
					case 73 : {
						if (ctrlKeyPressed) {
							$("#instructions").modal();
							instructionDisplay = true;
						}
						break;
					}
					// 'S' - Start from last saved position
					case 83 : {
						if (ctrlKeyPressed) {
							if (! displayStarted) {
								startDisplay();
								showPage();			// Restart showPage loop
							}
						}
						break;
					}
					// 'R' - Resume from current position
					case 82 : {
						if (ctrlKeyPressed) {
							displayPaused = false;
							// $("#current-position").html(">>>");
							$("#current-position").html((displayIndex + 1).toString() + "/" + ratings.length + "/<?= $total_pics;?>");
							showPage();			// Restart showPage loop
						}
						break;
					}
					// 'P' - Pause display
					case 80 : {
						if (ctrlKeyPressed) {
							displayPaused = true;
							$("#current-position").html("PAUSED");
						}
						break;
					}
					// 'B' - Start from beginning
					case 66 : {
						if (ctrlKeyPressed) {
							displayIndex = -1;		// showPage loop is already running
							clearTimeout(displayTimer);
							showPage();
						}
						break;
					}
					// 'E' - Go to the last picture
					case 69 : {
						if (ctrlKeyPressed) {
							displayPaused = false;
							displayIndex = ratings.length - 2;		// showPage loop is already running
							if (displayIndex < -1)
								displayIndex = -1;
							clearTimeout(displayTimer);
							showPage();
						}
						break;
					}
					// '>' - Advance 10 pictures
					case 190 : {
						if (ctrlKeyPressed) {
							displayPaused = false;
							displayIndex += 9;						// Index will be advanced to 10th picture when showPage runs
							if (displayIndex > (ratings.length -2))
								displayIndex = ratings.length -2;
							clearTimeout(displayTimer);
							showPage();
						}
						break;
					}
					// '<' - Rewind 10 pictures
					case 188 : {
						if (ctrlKeyPressed) {
							displayPaused = false;
							displayIndex -= 11;						// Index will be advanced to -10th picture when showPage runs
							if (displayIndex < -1)
								displayIndex = -1;
							clearTimeout(displayTimer);
							showPage();
						}
						break;
					}
					// ']' - Advance 50 Pictures
					case 221 : {
						if (ctrlKeyPressed) {
							displayPaused = false;
							displayIndex += 49;						// Index will be advanced to 50th picture when showPage runs
							if (displayIndex > (ratings.length -2))
								displayIndex = ratings.length -2;
							clearTimeout(displayTimer);
							showPage();
						}
						break;
					}
					// '[' - Rewind 50 pictures
					case 219 : {
						if (ctrlKeyPressed) {
							displayPaused = false;
							displayIndex -= 51;						// Index will be advanced to -50th picture when showPage runs
							if (displayIndex < -1)
								displayIndex = -1;
							clearTimeout(displayTimer);
							showPage();
						}
						break;
					}
				}
			}
		});
	});
</script>


</body>

</html>
<?php
}
else
{
	$_SESSION['err_msg'] = "Session expired. Login again";
	header("Location: index.php");
	printf("<script>location.href='index.php'</script>");
}

?>
