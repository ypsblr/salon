<?php
	$self = basename($_SERVER['PHP_SELF']);

?>
<style>

#loader_img {
	float: left;
	text-align: center;
	width: 100%;
}
div#loader_img:after {
    display: inline-block;
    vertical-align: middle;
    content: "";
    height: 100%;
}
div#loader_img img {
    display: inline-block;
    vertical-align: middle;
}
#loader_img {
    display: inline-block;
    text-align: center;
    width: auto;
    position: fixed;
    top: 0px;
    left: 0px;
    right: 0px;
    bottom: 0px;
    margin: auto;
	z-index: 9;
}

</style>
<div id="loader_img" style="display:none; background: rgba(0, 0, 0, 0.5); z-index: 9999;">
	<img alt="Loader Image" title="Loading..." src="img/progress_bar.gif">
</div>
<!-- Navigation -->
<aside id="menu">
    <div id="navigation">
        <div class="profile-picture">
            <a href="javascript:void(0)" style="text-align: center; display: flex; justify-content: center; align-items: center;">
                <img class="img-circle m-b" style="max-width: 80px; max-height: 80px;"
					 src="/res/jury/<?= $admin_avatar;?>"  alt="<?= $admin_name;?>" >
            </a>

            <div class="stats-label text-color">
                <span class="font-extra-bold font-uppercase"><?php echo $admin_name;?></span><br>
                <span class="small"><?php echo $admin_role;?></span><br>

                <div class="dropdown">
                    <a class="dropdown-toggle" href="javascript:void(0)" data-toggle="dropdown">
                        <small class="text-muted">Manage Panel for YPS Salon<b class="caret"></b></small>
                    </a>
                    <ul class="dropdown-menu animated flipInX m-t-xs">
                        <li><a href="index.php">Logout</a></li>
                    </ul>
                </div>


                <div id="sparkline1" class="small-chart m-t-sm"></div>
            </div>
        </div>

        <ul class="nav" id="side-menu">
			<!-- Anyone who could login - having some permission defined -->
            <li class="<?= ($self == 'manage_home.php') ? 'active' : '';?>">
                <a href="manage_home.php"> <span class="nav-label"><i class="fa fa-home"></i> Home </span></a>
            </li>

			<li class="<?= ($self == 'backup_db.php') ? 'active' : '';?>">
				<li><a href="javascript:void(0)" onclick="launchPage('op/backup_db.php', true)" ><i class="fa fa-database"></i> Backup Database </a></li>
            </li>

            <li class="<?= in_array($self, ['new_salon.php', 'salon_details.php', 'setup_judging.php', 'publish_results.php',
											'setup_exhibition.php', 'release_catalog.php', 'salon_dates.php', 'salon_blobs.php', 'edit_salon.php']) ? 'active' : '';?>" >
				<a href="#"><span class="nav-label"><i class="fa fa-picture-o"></i> Salon</span><span class="fa arrow"></span> </a>
				<ul class="nav nav-second-level">
					<li class="<?= ($self == 'new_salon.php') ? 'active' : '';?>" ><a href="new_salon.php"><i class="fa fa-star"></i> Create Salon</a></li>
					<li class="<?= ($self == 'salon_details.php') ? 'active' : '';?>" ><a href="salon_details.php"><i class="fa fa-pencil-square"></i> Salon Details</a></li>
					<li class="<?= ($self == 'setup_judging.php') ? 'active' : '';?>" ><a href="setup_judging.php"><i class="fa fa-balance-scale"></i> Set up Judging</a></li>
					<li class="<?= ($self == 'publish_results.php') ? 'active' : '';?>" ><a href="publish_results.php"><i class="fa fa-bullseye"></i> Release Results</a></li>
					<li class="<?= ($self == 'setup_exhibition.php') ? 'active' : '';?>" ><a href="setup_exhibition.php"><i class="fa fa-photo"></i> Set up Exhibition</a></li>
					<li class="<?= ($self == 'release_catalog.php') ? 'active' : '';?>" ><a href="release_catalog.php"><i class="fa fa-book"></i> Release Catalog</a></li>
					<li class="<?= ($self == 'salon_dates.php') ? 'active' : '';?>" ><a href="salon_dates.php"><i class="fa fa-calendar"></i> Update Dates</a></li>
					<li class="<?= ($self == 'salon_blobs.php') ? 'active' : '';?>" ><a href="salon_blobs.php"><i class="fa fa-file-code-o"></i> Update Descriptions</a></li>
					<li class="<?= ($self == 'edit_salon.php') ? 'active' : '';?>" ><a href="edit_salon.php"><i class="fa fa-edit"></i> Edit Salon</a></li>
					<li class="<?= ($self == 'partners.php') ? 'active' : '';?>" ><a href="partners.php"><i class="fa fa-money"></i> Partners</a></li>
				</ul>
            </li>

            <li class="<?= ($self == 'sections.php') ? 'active' : '';?>">
                <a href="sections.php"> <span class="nav-label"><i class="fa fa-th-large"></i> Sections </span></a>
            </li>

            <li class="<?= ($self == 'patronage.php') ? 'active' : '';?>">
                <a href="patronage.php"> <span class="nav-label"><i class="fa fa-registered"></i> Patronage </span></a>
            </li>

            <li class="<?php echo ($self == 'awards.php') ? 'active' : '';?>">
                <a href="awards.php"> <span class="nav-label"><i class="fa fa-trophy"></i> Awards </span></a>
            </li>

            <li class="<?php echo ($self == 'fees.php') ? 'active' : '';?>">
                <a href="fees.php"> <span class="nav-label"><i class="fa fa-money"></i> Fees </span></a>
            </li>

            <li class="<?php echo ($self == 'discount.php') ? 'active' : '';?>">
                <a href="discount.php"> <span class="nav-label"><i class="fa fa-money"></i> Discount </span></a>
            </li>

            <li class="<?= ($self == 'categories.php') ? 'active' : '';?>">
                <a href="categories.php"> <span class="nav-label"><i class="fa fa-tags"></i> Categories </span></a>
            </li>

            <li class="<?php echo ($self == 'team.php') ? 'active' : '';?>">
                <a href="team.php"> <span class="nav-label"><i class="fa fa-slideshare"></i> Committee </span></a>
            </li>

            <li class="<?php echo ($self == 'jury.php') ? 'active' : '';?>">
                <a href="jury.php"> <span class="nav-label"><i class="fa fa-users"></i> Jury </span></a>
            </li>

            <li class="<?php echo ($self == 'assign.php') ? 'active' : '';?>">
                <a href="assign.php"> <span class="nav-label"><i class="fa fa-user-plus"></i> Jury Assign </span></a>
            </li>

            <li class="<?= ($self == 'salon_mail.php') ? 'active' : '';?>">
                <a href="salon_mail.php"> <span class="nav-label"><i class="fa fa-envelope-o"></i> Send Mails </span></a>
            </li>

			<li class="<?php echo ($self == 'posting.php') ? 'active' : '';?>">
                <a href="posting.php"> <span class="nav-label"><i class="fa fa-send-o"></i> Posting </span></a>
            </li>

            <li class="<?php echo ($self == 'photos.php') ? 'active' : '';?>">
                <a href="photos.php"> <span class="nav-label"><i class="fa fa-upload"></i> Photos </span></a>
            </li>

			<li class="<?= in_array($self, ['certificate.php', 'slideshow.php', 'ribbon_holder.php', 'tent_card.php']) ? 'active' : '';?>" >
				<a href="#"><span class="nav-label"><i class="fa fa-picture-o"></i> Design</span><span class="fa arrow"></span> </a>
				<ul class="nav nav-second-level">
					<li class="<?= ($self == 'certificate.php') ? 'active' : '';?>" ><a href="certificate.php"><i class="fa fa-star"></i> Certificate</a></li>
					<li class="<?= ($self == 'slideshow.php') ? 'active' : '';?>" ><a href="slideshow.php"><i class="fa fa-video-camera"></i> Slideshow</a></li>
					<li class="<?= ($self == 'ribbon_holder.php') ? 'active' : '';?>" ><a href="ribbon_holder.php"><i class="fa fa-book"></i> Ribbon Holder</a></li>
					<li class="<?= ($self == 'title_card.php') ? 'active' : '';?>" ><a href="title_card.php"><i class="fa fa-address-card-o"></i> Title Card</a></li>
					<li class="<?= ($self == 'customs_declaration.php') ? 'active' : '';?>" ><a href="customs_declaration.php"><i class="fa fa-tag"></i> Customs Declaration</a></li>
				</ul>
            </li>

        </ul>
    </div>
</aside>
<!-- Loading image made visible during processing -->
<script>
	function launchPage(page, new_window = false) {
		if (new_window) {
			window.open(page, "_blank");
		}
		else {
			$('#loader_img').show();
			$.post(page)
				.done(function(messages){
					$("#loader_img").hide();
					swal("Messages", messages, "info");
				})
				.always(function(){
					$("#loader_img").hide();
				});
		}
	}
</script>
