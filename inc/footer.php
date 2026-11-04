<?php
include_once(__DIR__ . "/club.php");
    //$soc_title=urlencode('Share On Facebook');
    //$soc_url=urlencode('http://salon.ypsbengaluru.in/social-buttons-jquery-popup-dialog.php');
    //$soc_summary=urlencode('34th YPS All India Digital Salon Of Photography 2016');
    //$soc_image=urlencode('http://salon.ypsbengaluru.in/img/share_logo.jpg');
?>
<style type="text/css">
.white{
color:#fff !important;
}
</style>
<footer class="footer-dark">
	<div class="container">
		<div class="row">
			<!-- Contact Us -->
			<div class="col-sm-6">
				<h3 class="text-color white"><span class="border-color">Contacts</span></h3>
				<div class="content">
					<p>
						<b><u><?php echo club_short_name_html(); ?> President</u></b>: Mr. Manju Vikas Sastry V, ESFIP, AFIP | sastry.vikas@gmail.com<br>
						<b><u><?php echo club_short_name_html(); ?> Secretary</u></b>: Ms. Prema Kakade, ESFIP, EFIAP, EFIP, cMoL, E.CPE | pmkakade@gmail.com<br><br>
						<strong><?php echo club_name_html(); ?> Bengaluru</strong><br/>
						TT Hall, 3rd Floor,<br/>
						State Youth Centre of Karnataka, <br/>
						Nrupathanga Road, Bengaluru - 560001.<br/>
						Email: <a href="mailto:<?php echo salon_email_html(); ?>" class="white"><?php echo salon_email_html(); ?></a><br/>
						Phone: +91-9513-<?php echo club_short_name_html(); ?>-BLR (+91-9513-977-257)
						<br><br>
						<a href="<?php echo yps_website_without_www_html(); ?>" target="_blank"  class="white"><i class="fa fa-globe"></i> ypsbengaluru.com</a><br/>
						<a href="https://www.facebook.com/ypsbengaluru/" target="_blank" class="white"><i class="fa fa-facebook-square"></i> /ypsbengaluru</a><br/>
						<a href="<?php echo yps_website_with_www_html(); ?>/membership-join/" target="_blank" class="white"><i class="fa fa-globe"></i> Become a Member of <?php echo club_short_name_html(); ?></a>
					</p>
				</div>
				<div class="content social">
					<div class="clearfix"></div>
				</div>
			</div>
			<!-- Subscribe -->
			<div class="col-sm-6">
				<a href="<?php echo yps_website_without_www_html(); ?>" target="_blank"><h3 class="text-color white"><span><?php echo club_name_html(); ?></span></h3></a>
				<div class="content">
					<p><a href="<?php echo yps_website_without_www_html(); ?>" target="_blank" class="white"><?php echo club_name_html(); ?> (<?php echo club_short_name_html(); ?>) Bengaluru</a>,
						the hosts for this contest, is a not-for-profit organization started in 1971 to further the Art of Photography.
						With over 600 members, <?php echo club_short_name_html(); ?> is among the largest Photography Clubs in India and also boasts of hosting 50 National and
						International Salons. It has been nurtured by doyens of Indian Photography like Late Mr. T N A Perumal,
						Late Mr. E Hanumantha Rao and Late Mr. C Rajagopal. The club conducts regular slide-shows on every first and
						third Saturday of the month, alongside exhibitions and workshops, for the benefit of its members.
					</p>
				</div>
			</div>
        </div>
        <div class="row">
			<div class="col-sm-12">
				<hr>
			</div>
        </div>
        <!-- Copyrights -->
        <div class="row">
			<div class="col-sm-12">
				<p>
					&copy; <?php echo club_short_name_html(); ?> 2016. <a href="term_condition.php" target="_blank" class="white">Terms &amp; Conditions</a>
					 | Design &amp; Developed by <?php echo club_short_name_html(); ?> Bangalore in consultation with <a href="http://www.imcanny.com/"  target="_blank" class="white">www.imcanny.com</a>
				</p>
			</div>
        </div>
	</div>
</footer> <!-- Style Toggle -->
