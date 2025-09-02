<?php
	$partner = array(
					  array("name" => "Prolab", "website" => "www.prolab.in", "phone" => "+91-98451-40110", "email" => "naveen@prolab.in", "img" => "partner_PRO_LAB.jpg")
					, array("name" => "Book My Lens", "website" => "www.bookmylens.com", "phone" => "1800-121-0446", "email" => "rentals@bookmylens.com", "img" => "partner_Book_My_Lens.jpg")
				);
	// $keys = array(0, 1);
	if (isset($_SESSION['partner_key'])) {
		$_SESSION['partner_key'] += 1;
		if ($_SESSION['partner_key'] >= sizeof($partner))
			$_SESSION['partner_key'] = 0;
	}
	else
		$_SESSION['partner_key'] = rand(0, sizeof($partner)-1);
?>
<h2 class="headline text-color">
	<span class="border-color">Partners</span>
</h2>
<?php
	$idx = $_SESSION['partner_key'];
	// for ($i = 0; $i < sizeof($partner); $i ++) {
		$partner_phone = $partner[$idx]['phone'];
		$partner_website = $partner[$idx]['website'];
		$partner_email = $partner[$idx]['email'];
?>
<h3 class="text-color"><?php echo $partner[$idx]['name'];?></h3>
<div class="row">
	<div class="col-sm-12 thumbnail" style="margin-bottom: 8px;">
<?php
		if ($partner_website != "") {
?>
		<a href="http://<?php echo $partner_website;?>" target="_blank"><img src="/salons/<?= $contest_yearmonth; ?>/res/sponsor/<?php echo $partner[$idx]['img'];?>" style="max-width:300px;"></a>
<?php
		}
		else {
?>
		<img src="/salons/<?= $contest_yearmonth; ?>/res/sponsor/<?php echo $partner[$idx]['img'];?>" style="max-width:300px;">
<?php
		}
?>
	</div>
</div>
<div class="row">
	<div class="col-sm-12">
		<p class="text text-color">
			<span class="pull-left"><i class="fa fa-phone"></i> <b><?php echo $partner_phone;?></b></span>
			<span class="pull-right"><i class="fa fa-at"></i> <a href="mailto:<?php echo $partner_email;?>" ><b><?php echo $partner_email;?></b></a></span>
		</p>
	</div>
</div>
<?php
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
	//	$idx ++;
	//	if ($idx >= sizeof($partner))
	//		$idx = 0;
	//}
?>
