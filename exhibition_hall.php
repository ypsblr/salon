<?php
include_once("inc/session.php");
include_once("inc/salonlib.php");

if (isset($_REQUEST['contest']) && isset($_SESSION['VISITOR_ID'])) {

	$exhibition_yearmonth = $_REQUEST['contest'];
	$salon = get_contest($exhibition_yearmonth);
	$contest = $salon['contest'];
	$exhibition = $salon['exhibition'];

	// Validate if a virtual exhibition has been configured and is ready
	if (date("Y-m-d") >= $contest['exhibition_start_date'] && date("Y-m-d") <= $contest['exhibition_end_date'] &&
		$exhibition['is_virtual'] == 1 && $exhibition['virtual_tour_ready'] == 1) {
		// Get Visitor Details
		$visitor_id = $_SESSION['VISITOR_ID'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
<?php include_once("inc/header.php"); ?>

</head>

<body class="<?php echo THEME;?>">

    <?php include_once("inc/navbar.php");?>

    <!-- Wrapper -->
    <div class="wrapper">
		<div class="container">
			<div class="row blog-p">
				<div class="col-lg-3 col-md-3 col-sm-3">
					<?php //include("inc/user_sidemenu.php");?>
				</div>
				<div class="col-lg-9 col-md-9 col-sm-9">
					<div id="hasResponse"></div>
					<div id="loader_img" style="display:none;background: rgba(0, 0, 0, 0.5);z-index: 9999;">
						<img alt="Loader Image" title="Loading..." src="img/progress_bar.gif">
					</div>

					<div class="sign-form" id="myTab">
						<h2 class="text-color"><?= $contestName;?></h2>
						<h3 class="first-child text text-color">EXHIBITION HALLS</h3>
						<p class="text-justify">
							The Exhibition has been organized into <b>6 Halls</b>, one for <b>each section</b> and one for <b>Best Club</b> and one for <b>Best Entrant</b>.
							Click on any of the boxes below to enter a Hall.
						</p>
<!--
						<div class="row">
							<div class="col-sm-6">
								<p class="text-center"><b>HALL MODE</b></p>
								<hr>
								<p>Press <b>Hall</b> on-screen button to start Hall mode.</p>
								<p>Roam at will in any direction using on-screen <b>Arrow Buttons</b> or <b>Arrow Keys</b> on keyboard.</p>
								<p>Get closer by using on-screen <b>+ button</b> or <b>+ or Z key</b>.</p>
								<p>Step back by using on-screen <b>- button</b> or <b>- or W key</b>.</p>
							</div>
							<div class="col-sm-6">
								<p class="text-center"><b>PHOTO MODE</b></p>
								<hr>
								<p>Press <b>Photo</b> on-screen button to start Hall mode.</p>
								<p>Move from one photo to the next by pressing <b>Next</b> on-screen button.</p>
							</div>
						</div>
-->
<!--

						<ul>
							<li>You can move through the Hall in two modes - &quot;<b>Photo</b>&quot; mode and &quot;<b>Room</b>&quot; mode.</li>
							<li>When you click on the <b>Photo</b> button, you are taken directly to the first photo. Clicking <b>NEXT</b> takes
								you to the next photo. No other controls will work.</li>
							<li>
								<b>Room</b> mode allows you to navigate through the hall. The following controls will work:
								<ul>
									<li>Zoom in using <b>+</b> button or key</li>
									<li>Zoom out using <b>-</b> button or key</li>
									<li>Turn Left or Right using <b>&lt;</b> and <b>&gt;</b> buttons or left and right keys</li>
									<li>Lift Up or Down using <b>up</b> and <b>down</b> buttons or up and down keys</li>
								</ul>
							</li>
							<li>Click on the "Leave the Room" link at the bottom to exit the room.</li>
						</ul>
-->
						<form role="form" method="post" id="hall-form" name="hall-form" action="op/enter_hall.php" >
							<input type="hidden" name="visitor_id" id="visitor_id" value="<?= $visitor_id;?>" >
							<input type="hidden" name="yearmonth" id="yearmonth" value="<?= $exhibition_yearmonth;?>" >
							<input type="hidden" name="hall" id="hall" value="" >

							<div class="row" style="width: 100%; padding-left: 40px; padding-right: 40px">
								<div class="col-sm-12">
									<?php include "salons/$exhibition_yearmonth/exhibition/layout.html"; ?>
								</div>
							</div>
							<div class="row">
								<div class="col-sm-11" style="text-align: center; padding-top: 15px;">
									<a class="btn btn-color" href="/exhibition.php?contest=<?= $exhibition_yearmonth;?>&leaving">LEAVE EXHIBITION</a>
								</div>
							</div>
						</form>
						<!-- Create a Modal DIV to show instructions -->
						<div class="modal" id="navigation-guide" tabindex="-1" role="dialog" aria-labelledby="modal-header-label">
							<div class="modal-dialog modal-lg" role="document">
								<div class="modal-content">
									<div class="modal-header">
										<button type="button" class="close" data-dismiss="modal" aria-label="Close" >
											<span aria-hidden="true">&times;</span>
										</button>
										<h4 class="modal-title" id="modal-header-label">Navigation Guide</h4>
									</div>
									<div class="modal-body">
										<div class="row">
											<div class="col-sm-12">
												<img src="/salons/<?= $exhibition_yearmonth;?>/img/Navigation_Guide.png" onclick="launchHall()"
													 style="max-width: 100%;">
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>

					</div>
				</div>
			</div> <!-- / .row -->
			<!-- FOOTER -->
			<div class="row">
				<?php include_once("inc/footer.php") ;?>
			</div>
		</div>	<!-- container -->
    </div> <!-- / .wrapper -->

    <!-- Style Toggle -->

	<?php include_once("inc/settingToggle.php") ;?>
    <!-- JavaScript
    ================================================== -->
	<?php include_once("inc/scripts.php"); ?>

	<script>
		$(document).ready(function(){
			$('html, body').animate({ scrollTop: $("#myTab").offset().top-100 }, 500);
		});
	</script>

	<script>
		function enterHall(hall) {
			$("#hall").val(hall);
			$("#navigation-guide").modal('show');
		}
		function launchHall() {
			$("#hall-form").submit();
		}
	</script>

</body>

</html>
<?php
	}	// Virtual Tour open
	else {
		$_SESSION['err_msg'] = "No Virtual exhibitions are open";
		header('Location: /index.php');
		printf("<script>location.href='/index.php'</script>");
	}
} // contest and VISITOR_ID set
else {
	$_SESSION['err_msg'] = "Invalid Access";
	header('Location: /index.php');
	printf("<script>location.href='/index.php'</script>");
}
?>
