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

		<!-- Intro Text -->
		<div class="container-fluid intro">
			<div class="row">
				<div class="col-sm-12 col-md-12 col-lg-12" style="padding-left:3%">

                    <!-- Patronage Section -->			    
    				<div class="panel-group" id="sections">
    					<div class="panel panel-default">
    						<div class="panel-heading">
    							<h2 class="primary-font">
    								<a data-toggle="collapse" data-parent="#sections" href="#section-contest">Patronage</a>
    							</h2>
    						</div>
    						<!--<div id="section-contest" class="panel-collapse collapse in">-->
    						<div id="section-contest" class="panel-collapse collapse">
    							<div class="panel-body">
                					<?php require("inc/recognitions.php");?>
    							</div>
    						</div>
    					</div>
    				</div>


					<!-- Awards by Section -->
					<h2 class="headline first-child text-color">
						<span class="border-color">Awards</span>
					</h2>
					<!-- Generate Awards List -->
					<?php require("inc/awards_lib.php"); ?>
					<!-- PICTURE Awards Tabs -->
					<div class="row">
						<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
							<h3 class="text text-info headline">Picture Awards</h3>
							<!-- Generate List of Tabs for each category group -->
							<ul class="nav nav-pills">
								<?php $ag_list = awards_generate_ag_list(); ?>
							</ul>
							<!-- Content for each each Entrant Category -->
							<div class="tab-content">
								<!-- 3 Sections per Row -->
								<?php awards_generate_ag_tab($ag_list, 5); ?>
							</div>
						</div>
					</div>
					<!--<div class="divider"></div>-->

					<!-- ENTRY AWARDS -->
					<div class="row">
						<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
							<h3 class="text text-info headline">Individual Awards</h3>
							<!-- Generate List of Individual Awards -->
							<?php awards_contest_level_list('entry');?>
						</div>
					</div>
					<!--<div class="divider"></div>-->

                    <?php
                    $spl_count = awards_contest_level_count("pic", "CONTEST");
                    if ($spl_count > 0) {
                    ?>
    					<!-- SPL PIC AWARDS -->
    					<div class="row">
    						<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
    							<h3 class="text text-info headline">Special Picture Awards</h3>
    							<!-- Generate List of Special Picture Awards -->
    							<?php awards_contest_level_list('pic', 'CONTEST');?>
    						</div>
    					</div>
    					<!--<div class="divider"></div>-->
                    <?php
                    }
                    ?>

					<!-- CLUB AWARDS -->
					<div class="row">
						<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
							<h3 class="text text-info headline">Club Awards</h3>
							<!-- Generate List of Individual Awards -->
							<?php awards_contest_level_list('club');?>
						</div>
					</div>
					
					<br>

					<!--<h2 class="headline text-color">-->
					<!--	<span class="border-color">Patronage</span>-->
					<!--</h2>-->
					<!-- ?php require("inc/recognitions.php");?>-->

			    </div>
			    <!-- END OF LEFT COLUMN -->
				<!-- RIGHT COLUMN -->
				<!--<div class="col-sm-4 col-md-4 col-lg-4">-->
					<!-- Show Login Form -->
					<!-- ?php include("inc/login_form.php");?>-->

					<!-- Start Count Down One Week before the last date -->
					<!-- ?php include("inc/countdown.php");?>-->

					<!-- Partners -->
					<!-- ?php
						if (file_exists("./salons/$contest_yearmonth/blob/partners.php"))
							include("./salons/$contest_yearmonth/blob/partners.php");
					?-->

					<!-- Show Catalog Download / View Links after results are published -->
					<!-- ?php include("inc/catalogview.php");? -->

		            <!-- Image Carousel -->
					<!--<style>-->
					<!--.carousel-inner img{ max-height:300px !important; }-->
					<!--</style>-->
					<!--
					<h3 class="headline text-color">
						<span class="border-color">Slideshows</span>
					</h3>
					-->
			        <!-- ?php //include_once("inc/partners.php") ;?>
				</div>
				<!-- END OF RIGHT SIDE -->
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

		<script>
		$(document).ready(function() {
			$("#login_login_id").hide();
			$("#check_it").attr("placeholder", "Email (or YPS Member ID)");
		});
	</script>


</body>

</html>
