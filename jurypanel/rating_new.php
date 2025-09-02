<?php
// session_start();
include("inc/session.php");
include("inc/connect.php");
include("inc/lib.php");

if(isset($_SESSION['jury_id'])) {
	$session_id=$_SESSION['jury_id'];
	$sql = "SELECT * FROM user WHERE user_id = '$session_id'";
	$user_query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$user_row = mysqli_fetch_array($user_query);
	$jury_login = $user_row['login'];
	$jury_name=$user_row['user_name'];
	$jury_pic=$user_row['avatar'];
	$user_type = $user_row['type'];

	// Load assignments
	// $sql  = "SELECT * FROM assignment, ctl ";
	// $sql .= " WHERE assignment.yearmonth = ctl.yearmonth ";
	// $sql .= "   AND assignment.user_id = $session_id";
	// $query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	// $assignment = array();
	// while ($row = mysqli_fetch_array($query))
		// $assignment[$row['section']] = $row['jurynumber'];

?>

<!DOCTYPE html>
<html>
<head>

    <!-- Page title -->
    <title>Youth Photographic Society | Jury Panel</title>

	<?php include "inc/header.php"; ?>


<style type="text/css">
body {
	height: 100%;
	background-color: black;
}
#wrapper {
	margin: 0;
}

#menu {
	background-color: black;
}

#side-menu li:first-child {
	border: 0px;
}

#side-menu li {
	border: 0px;
}

#side-menu li a {
	margin: 0px;
	padding: 0px;
	background-color: black;
}

#side-menu.nav > li > a:hover, #side-menu.nav > li > a:focus {
	background-color: black;
}

.containerBox {
    position: relative;
    display: inline-block;
}
.text-box {
    position: absolute;
    text-align: center;
    width: 100%;
}
.text-box:before {
   content: '';
   display: inline-block;
   height: 100%;
   vertical-align: middle;
}
h4 {
   display: inline-block;
   font-size: 18px; /*or whatever you want*/
   color: #FFF;
}

img {
  display: block;
  max-width: 100%;
  height: auto;
}

.thumbnail {
	background-color: black;
	border-color: #202020;
}

.history-rating {
	font-family: "Arial", sans-serif;
	font-size: 3em;
	font-weight: bold;
	color: yellow;
}

.history-title {
	font-family: "Arial", sans-serif;
	font-size: 1em;
	font-weight: normal;
	color: #a0a0a0;
}

.history-thumbnail {
	width: 100%;
	background-color: black;
}


</style>

</head>
<body>
	<!--[if lt IE 7]>
	<p class="alert alert-danger">You are using an <strong>outdated</strong> browser. Please upgrade your browser to improve your experience.</p>
	<![endif]-->

	<!-- Header -->

	<!-- Navigation -->

	<!-- Audio -->
	<!-- <embed src="/jurypanel/img/button-16.wav" autostart="false" width="0" height="0" id="click_sound" style="display: none;" enablejavascript="true"> -->

	<!-- Main Wrapper -->
	<div id="wrapper" style="background-color: black; border:0;" >
		<div class="content">
			<div class="row">	<!-- Message Row -->
				<div class="col-lg-2 col-md-2 col-sm-3 col-xs-3">
					<div class="row">
						<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
							<img src="/res/jury/<?=$jury_pic;?>" class="img-circle m-b" alt="<?=$jury_name;?>" style="margin:auto; width:30%;">
						</div>
					</div>
				</div>
				<div class="col-lg-9 col-md-9 col-sm-7 col-xs-7">
					<p class="text-muted font-extra-bold font-uppercase"><big><?php echo $jury_name;?></big></p>
					<br>
					<p id="errmsg" style="color:yellow; font-weight: bold;">Hello <?php echo $jury_name; ?></p>
				</div>
				<div class="col-lg-1 col-md-1 col-sm-2 col-xs-2">
					<a href="index.php" class="text-muted">EXIT</a>
				</div>
			</div>
			<div class="row">	<!-- Buttons 1, 2 & 3 -->
				<div class="col-lg-5 col-md-4 col-sm-3 col-xs-0"></div>
				<div class="panel col-lg-3 col-md-4 col-sm-6 col-xs-12" style="background-color: black;">
					<div class="panel-heading"><h3>Click on Rating</h3></div>
					<h2 id="indicator" style="font-weight: bold;"></h2>
					<div class="panel-body">
						<div class="row">
							<div class="col-lg-4 col-md-4 col-sm-4 col-xs-4"><img src="./img/sq4.png" onclick="update_rating(4)"></div>
							<div class="col-lg-4 col-md-4 col-sm-4 col-xs-4"><img src="./img/sq5.png" onclick="update_rating(5)"></div>
							<div class="col-lg-4 col-md-4 col-sm-4 col-xs-4"><img src="./img/sqtag.png" onclick="update_tag()"></div>
						</div>
						<div class="row">
							<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">&nbsp;</div>
						</div>
						<div class="row">
							<div class="col-lg-4 col-md-4 col-sm-4 col-xs-4"><img src="./img/sq1.png" onclick="update_rating(1)"></div>
							<div class="col-lg-4 col-md-4 col-sm-4 col-xs-4"><img src="./img/sq2.png" onclick="update_rating(2)"></div>
							<div class="col-lg-4 col-md-4 col-sm-4 col-xs-4"><img src="./img/sq3.png" onclick="update_rating(3)"></div>
						</div>
					</div>
				</div>
				<div class="col-lg-4 col-md-4 col-sm-3 col-xs-0"></div>
			</div>
			<div class="row">
				<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
					<br><h4 class="text-muted">History of Your Ratings</h4><br>
				</div>
			</div>
			<div class="row">	<!-- History of ratings -->
				<div class="col-lg-2 col-md-2 col-sm-6 col-xs-12 containerBox">
					<div class="row">
						<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 text-primary"><span id="rating-1" class="history-rating"></span></div>
					</div>
					<div class="row">
						<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 thumbnail"><span id="thumbnail-1" class="history-thumbnail"></span></div>
					</div>
					<div class="row">
						<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 text-primary"><span id="title-1" class="history-title"></span></div>
					</div>
				</div>
				<div class="col-lg-2 col-md-2 col-sm-6 col-xs-12 containerBox">
					<div class="row">
						<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 text-primary"><span id="rating-2" class="history-rating"></span></div>
					</div>
					<div class="row">
						<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 thumbnail"><span id="thumbnail-2" class="history-thumbnail"></span></div>
					</div>
					<div class="row">
						<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 text-primary"><span id="title-2" class="history-title"></span></div>
					</div>
				</div>
				<div class="col-lg-2 col-md-2 col-sm-6 col-xs-12 containerBox">
					<div class="row">
						<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 text-primary"><span id="rating-3" class="history-rating"></span></div>
					</div>
					<div class="row">
						<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 thumbnail"><span id="thumbnail-3" class="history-thumbnail"></span></div>
					</div>
					<div class="row">
						<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 text-primary"><span id="title-3" class="history-title"></span></div>
					</div>
				</div>
				<div class="col-lg-2 col-md-2 col-sm-6 col-xs-12 containerBox">
					<div class="row">
						<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 text-primary"><span id="rating-4" class="history-rating"></span></div>
					</div>
					<div class="row">
						<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 thumbnail"><span id="thumbnail-4" class="history-thumbnail"></span></div>
					</div>
					<div class="row">
						<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 text-primary"><span id="title-4" class="history-title"></span></div>
					</div>
				</div>
				<div class="col-lg-2 col-md-2 col-sm-6 col-xs-12 containerBox">
					<div class="row">
						<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 text-primary"><span id="rating-5" class="history-rating"></span></div>
					</div>
					<div class="row">
						<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 thumbnail"><span id="thumbnail-5" class="history-thumbnail"></span></div>
					</div>
					<div class="row">
						<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 text-primary"><span id="title-5" class="history-title"></span></div>
					</div>
				</div>
				<div class="col-lg-2 col-md-2 col-sm-6 col-xs-12 containerBox">
					<div class="row">
						<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 text-primary"><span id="rating-6" class="history-rating"></span></div>
					</div>
					<div class="row">
						<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 thumbnail"><span id="thumbnail-6" class="history-thumbnail"></span></div>
					</div>
					<div class="row">
						<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 text-primary"><span id="title-6" class="history-title"></span></div>
					</div>
				</div>
			</div>		<!-- End of History of ratings -->
		</div>			<!-- End of content -->
	</div>


<?php include "inc/footer.php";?>


<!-- Local scripts to handle rating updates -->
<script>
// var click_sound;
/*
function PlaySound(soundObj) {
  var sound = document.getElementById(soundObj);
  sound.Play();
}
*/

// Javascript to refresh page
function updateRatingHistory(data, status) {
	$("#indicator").html("");
	if(status == "success") {
		// First handle errors
		if (data.status == "ERR") {		// In case of error, reload page to disable operations
			$("#errmsg").html(data.errmsg);
		}
		else {			// Update screen only if there is a change
			$("#errmsg").html("<?php echo $jury_name;?>");

			// Play Sound
			// PlaySound("click_sound");

			// Update Screen - History
			// Push all existing history records down
			$("#rating-6").html($("#rating-5").html());
			$("#thumbnail-6").html($("#thumbnail-5").html());
			$("#title-6").html($("#title-5").html());

			$("#rating-5").html($("#rating-4").html());
			$("#thumbnail-5").html($("#thumbnail-4").html());
			$("#title-5").html($("#title-4").html());

			$("#rating-4").html($("#rating-3").html());
			$("#thumbnail-4").html($("#thumbnail-3").html());
			$("#title-4").html($("#title-3").html());

			$("#rating-3").html($("#rating-2").html());
			$("#thumbnail-3").html($("#thumbnail-2").html());
			$("#title-3").html($("#title-2").html());

			$("#rating-2").html($("#rating-1").html());
			$("#thumbnail-2").html($("#thumbnail-1").html());
			$("#title-2").html($("#title-1").html());

			// Insert the latest rating on top
			$("#rating-1").html(data.cur_pic_rating + " " + data.cur_pic_tags);
			$("#thumbnail-1").html("<img src='../salons/" + data.jury_yearmonth + "/upload/" + data.cur_pic_section + "/tn/" + data.cur_pic_file + "' />");
			$("#title-1").html("[ " + data.cur_pic_eseq +" ]");
		}
	}
}

function update_rating(pic_rating) {
	if (pic_rating == 1) {
		swal({
			title: 'Reject Picture',
			text:  'Do you want to reject picture for non-conformance to Salon Rules ?',
			imageUrl: '../img/question.png',
			button: "Yes",
		})
		.then(function() {
			$("#indicator").html("Updating");
			$.post("ajax/rating_update_new.php", {rating: pic_rating}, updateRatingHistory, "json");
		});
	}
	else {
		$("#indicator").html("Updating");
		$.post("ajax/rating_update_new.php", {rating: pic_rating}, updateRatingHistory, "json");
	}
}

function update_tag() {
	$("#indicator").html("Updating");
	$.post("ajax/rating_update_new.php", {tag: 'TAG'}, updateRatingHistory, "json");
}

</script>
<script>

	// Install Keyboard handler once the document is loaded
	$(document).ready(function(){
		$(document).keydown(function(e) {
			if(e.keyCode >= 49 && e.keyCode <= 53) 	// Handle press of 1-5 keys
				update_rating(e.keyCode - 48);
			else if (e.keyCode >= 97 && e.keyCode <= 101)	// Number 1-5 in numeric keypad
				update_rating(e.keyCode - 96);
			else if (e.keyCode == 84 || e.keyCode == 56 || e.keyCode == 106)				// Handle pressing of 't' or '8' or '*' key to TAG the picture
				update_tag();
		});
		// Load the Click Sound
		// click_sound = new Audio("/jurypanel/img/button-16.wav");
	});
</script>


</body>

</html>
<?php
}
else
{
header("Location: home.php");
printf("<script>location.href='home.php'</script>");
}

?>
