<?php
include_once("inc/session.php");
?>

<!DOCTYPE html>
<html lang="en">

<head>
<?php include_once("inc/header.php"); ?>
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
						<span class="border-color">Distinguished Jury</span>
					</h2>
					<?php  include_once("inc/juryProfile.php") ;?>

					<!-- <h2 class="headline first-child text-color">
						<span class="border-color">Back to Judging at Venue</span>
					</h2>
					<p style="text-align:justify;">
						With the pandemic declassified, YPS will get back to Open Judging at Venue. This brings the
						best of both worlds. Participants in and around Bangalore who can visit the venue will have
						the pleasure of meeting the members of jury and other participants and enjoy YPS hospitality.
						They will also have the privilege of witnessing the selection of pictures for awards and
						the discussions around the reasons for the picture being selected. The live scoring will
						still be available through webcast. Details will be published aroud the time of judging.
					</p> -->
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
	<script>
		$(document).ready(function() {
			$("#login_login_id").hide();
			$("#check_it").attr("placeholder", "Email (or YPS Member ID)");
		});
	</script>
    <script>
        function showJury(jury_id) {
            window.scrollTo(0, $("#jury-" + jury_id).get(0).offsetTop + $("#jury-" + jury_id).get(0).offsetHeight);
        }
    </script>

</body>

</html>
