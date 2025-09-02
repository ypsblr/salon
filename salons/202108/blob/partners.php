<?php
	if (file_exists(__DIR__ . "/partner_data.php")) {
		include(__DIR__ . "/partner_data.php");
		if (isset($partners) && sizeof($partners) > 0) {
			if (isset($_SESSION['partner_key'])) {
				$_SESSION['partner_key'] += 1;
				if ($_SESSION['partner_key'] >= sizeof($partners))
					$_SESSION['partner_key'] = 0;
			}
			else
				$_SESSION['partner_key'] = 0;
?>
<h2 class="headline text-color">
	<span class="border-color">Partners</span>
</h2>
<?php
			$idx = $_SESSION['partner_key'];
			$partner_img = $partners[$idx]['img'];
			$partner_phone = $partners[$idx]['phone'];
			$partner_website = $partners[$idx]['website'];
			$partner_email = $partners[$idx]['email'];
?>
<h3 class="text-color"><?php echo $partners[$idx]['name'];?></h3>
<div class="row">
	<div class="col-sm-12 thumbnail" style="margin-bottom: 8px;">
<?php
			if ($partner_img != "") {
				if ($partner_website != "") {
?>
		<a href="http://<?php echo $partner_website;?>" target="_blank"><img src="/salons/<?= $contest_yearmonth; ?>/img/sponsor/<?php echo $partners[$idx]['img'];?>" style="max-width:300px;"></a>
<?php
				}
				else {
?>
		<img src="/salons/<?= $contest_yearmonth; ?>/img/sponsor/<?php echo $partners[$idx]['img'];?>" style="max-width:300px;">
<?php
				}
			}
			else if ($partners[$idx]["text"] != "") {
?>
		<p><b><?= $partners[$idx]["text"];?></b></p>
<?php
			}
?>
	</div>
</div>
<?php
			if ($partner_phone != "") {
?>
<div class="row">
	<div class="col-sm-12">
		<p class="text text-color">
			<span class="pull-left"><i class="fa fa-phone"></i> <b><?php echo $partner_phone;?></b></span>
			<span class="pull-right"><i class="fa fa-at"></i> <a href="mailto:<?php echo $partner_email;?>" ><b><?php echo $partner_email;?></b></a></span>
		</p>
	</div>
</div>
<?php
			}
			if ($partner_website != "") {
?>
<div class="row">
	<div class="col-sm-12">
		<p class="text text-color text-center">
			<i class="fa fa-globe"></i> <a href="http://<?php echo $partner_website;?>" target="_blank" ><b><?php echo $partner_website;?></b></a>
		</p>
	</div>
</div>
<?php
			}
?>
<div class="divider"></div>
<?php
		}
	}
?>
