<?php include_once(__DIR__ . "/../inc/club.php"); ?>
<div class="mini-navbar mini-navbar-dark" >
	<div class="container">
		<div class="row">
			<?php
				if(isset($_SESSION['signin_msg'])) {
			?>
			<div class="col-sm-5">
				<i class="fa fa-warning"></i> <?php echo $_SESSION['signin_msg'];?>
			</div>
			<?php
					unset($_SESSION['signin_msg']);
				}
				else {
			?>
			<div class="col-sm-8 hidden-xs">
				<a href="mailto:<?php echo salon_email_html(); ?>" class="first-child"><i class="fa fa-envelope"></i> Email<span class="hidden-sm">: <?php echo salon_email_html(); ?></span></a>
				<span class="phone"><i class="fa fa-phone-square"></i> Phone.: +91-9513-<?php echo club_short_name_html(); ?>-BLR (+91-9513-977-257)</span>
			</div>
			<?php
				}
			?>
		</div>
	</div>
</div>
