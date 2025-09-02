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
				<a href="mailto:salon@ypsbengaluru.in" class="first-child"><i class="fa fa-envelope"></i> Email<span class="hidden-sm">: salon@ypsbengaluru.in</span></a>
				<span class="phone"><i class="fa fa-phone-square"></i> Phone.: +91-9513-YPS-BLR (+91-9513-977-257)</span>
			</div>
			<?php
				}
			?>
		</div>
	</div>
</div>
