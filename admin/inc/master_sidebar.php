<?php
	$self = basename($_SERVER['PHP_SELF']);

	// Get Section List
	$digital_sections = [];
	$print_sections = [];
	$sql = "SELECT * FROM section WHERE yearmonth = '$admin_yearmonth' ";
// 	$sql  = "SELECT section.section, section.section_type, COUNT(*) AS num_acceptances, SUM(IF(award.level < 99, 1, 0)) AS num_awards ";
// 	if ($contest_archived)
// 		$sql .= "  FROM ar_pic_result AS pic_result, award, section ";
// 	else
// 		$sql .= "  FROM pic_result, award, section ";
// 	$sql .= " WHERE pic_result.yearmonth = '$admin_yearmonth' ";
// 	$sql .= "   AND award.yearmonth = pic_result.yearmonth ";
// 	$sql .= "   AND award.award_id = pic_result.award_id ";
// 	$sql .= "   AND award.section != 'CONTEST' ";
// 	$sql .= "   AND section.yearmonth = award.yearmonth ";
// 	$sql .= "   AND section.section = award.section ";
// 	$sql .= " GROUP BY section.section ";
	$section_query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	while ($section_row = mysqli_fetch_array($section_query, MYSQLI_ASSOC)) {
		if ($section_row['section_type'] == 'P')
			$print_sections[$section_row['section']] = $section_row;
		else
			$digital_sections[$section_row['section']] = $section_row;
	}

	// Determine if sponsorship is applicable to this Salon
	$sql  = "SELECT SUM(sponsored_awards * sponsorship_per_award) AS total_sponsorship FROM award ";
	$sql .= " WHERE yearmonth = '$admin_yearmonth' AND award_type = 'pic' ";
	$award_query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$award_row = mysqli_fetch_array($award_query);
	$contest_has_sponsorship = ($award_row['total_sponsorship'] > 0);

	// Get a list of Patronages
	$sql = "SELECT short_code FROM recognition WHERE yearmonth = '$admin_yearmonth' ";
	$patronage_query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$patronage_list = [];
	while ($patronage_row = mysqli_fetch_array($patronage_query))
		$patronage_list[] = $patronage_row['short_code'];

	$current_menu_section = (! empty($_REQUEST['section'])) ? decode_string_array($_REQUEST['section']) : "";

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
					 src="/salons/<?= $admin_yearmonth;?>/img/com/<?= ($member_avatar == "") ? 'user.jpg' : $member_avatar;?>"  alt="<?= $member_name;?>" >
            </a>

            <div class="stats-label text-color">
                <span class="font-extra-bold font-uppercase"><?php echo $member_name;?></span><br>
                <span class="small"><?php echo $member_role_name;?></span><br>
                <span class="font-extra-bold small"><?php echo $member_honors;?></span>

                <div class="dropdown">
                    <a class="dropdown-toggle" href="javascript:void(0)" data-toggle="dropdown">
                        <small class="text-muted">Admin Panel for YPS Salon<b class="caret"></b></small>
                    </a>
                    <ul class="dropdown-menu animated flipInX m-t-xs">
                        <li><a href="javascript:void(0)" id="sidebar" class="right-sidebar-toggle">Change Password</a></li>
                        <li class="divider"></li>
                        <li><a href="index.php">Logout</a></li>
                    </ul>
                </div>


                <div id="sparkline1" class="small-chart m-t-sm"></div>
            </div>
        </div>

        <ul class="nav" id="side-menu">
			<!-- Anyone who could login - having some permission defined -->
            <li class="<?php echo ($self == 'entry_dashboard.php') ? 'active' : '';?>">
                <a href="entry_dashboard.php"> <span class="nav-label"><i class="fa fa-cubes"></i> Dashboard </span></a>
            </li>

			<!-- Only chairman, secretary, admin -->
			<?php
				if (has_permission($member_permissions, ["admin", "chairman", "secretary", "manager"]) && $admin_contest['results_ready'] == '0') {
			?>
            <li class="<?php echo ($self == 'user_admin.php') ? 'active' : '';?>">
                <a href="user_admin.php"> <span class="nav-label"><i class="fa fa-balance-scale"></i> Jury Assignment </span></a>
            </li>
			<?php
				}
			?>

			<!-- Only chairman, secretary, admin -->
			<?php
				if (has_permission($member_permissions, ["admin", "chairman", "secretary", "manager"])) {
			?>
            <li class="<?php echo ($self == 'create_club_discount.php') ? 'active' : '';?>">
                <a href="create_club_discount.php"> <span class="nav-label"><i class="fa fa-users"></i> Club </span></a>
            </li>
			<?php
				}
			?>

			<!-- Only chairman, secretary, admin -->
			<?php
				if (has_permission($member_permissions, ["admin", "secretary", "manager", "chairman"])) {
			?>
            <li class="<?php echo ($self == 'all_participate.php') ? 'active' : '';?>">
                <a href="all_participate.php"> <span class="nav-label"><i class="fa fa-user"></i> Participants </span></a>
            </li>
			<?php
				}
			?>

			<?php
				if ($contest_has_sponsorship) {
					if (has_permission($member_permissions, ["admin", "chairman", "secretary", "manager", "treasurer"])) {
			?>
			<!-- Anyone with a Permission to login -->
            <li class="<?php echo ($self == 'sponsor_dashboard.php') ? 'active' : '';?>">
                <a href="sponsor_dashboard.php"> <span class="nav-label"><i class="fa fa-exclamation-triangle"></i> Sponsorship </span></a>
            </li>
			<?php
					}
				}
			?>

			<!-- Anyone with permission to login can view Blacklist. But only admin/chairman/secretary can approve exception -->
            <li class="<?php echo ($self == 'blacklist.php') ? 'active' : '';?>">
                <a href="blacklist.php"> <span class="nav-label"><i class="fa fa-home"></i> Blacklist </span></a>
            </li>

			<!-- Review Uploads by people with reviewer permission -->
			<?php
			// if ( sizeof($digital_sections) > 0 && $admin_contest['judging_start_date'] > date("Y-m-d") &&
				if ( sizeof($digital_sections) > 0 &&
					( ( $admin_contest['results_ready'] == '0' && has_permission($member_permissions, ["admin", "chairman", "secretary", "reviewer", "manager"]) ) ||
					has_permission($member_permissions, ["admin"]) ) ) {
			?>
			<li class="<?php echo ($self == 'review_image_dash.php') ? 'active' : '';?>">
                <a href="javascript:void(0)">
					<span class="nav-label"><i class="fa fa-picture-o"></i> Review Digital </span><span class="fa arrow"></span>
				</a>
				<ul class="nav nav-second-level">
					<?php
						foreach ($digital_sections as $menu_section => $section_data) {
							if (can_review($menu_section, $member_sections, $member_permissions)) {
					?>
					<li>
						<a href="review_image_dash.php?section=<?=encode_string_array($menu_section);?>" style="<?= ($menu_section == $current_menu_section) ? 'background-color: #eee8aa;' : '';?>" >
							<span class="nav-label"><i class="fa fa-picture-o"></i> <?=$menu_section;?></span>
						</a>
					</li>
					<?php
							}
						}
					?>
				</ul>
            </li>
			<?php
			   }
			?>

			<!-- Review Prints by people with reviewer permission -->
			<?php
			if ( sizeof($print_sections) > 0 &&
				( ( $admin_contest['results_ready'] == '0' && has_permission($member_permissions, ["admin", "chairman", "secretary", "reviewer", "manager"]) ) ||
				has_permission($member_permissions, ["admin"]) ) ) {
			?>
			<li class="<?php echo ($self == 'review_image_dash.php') ? 'active' : '';?>">
                <a href="javascript:void(0)">
					<span class="nav-label"><i class="fa fa-print"></i> Review Prints </span><span class="fa arrow"></span>
				</a>
				<ul class="nav nav-second-level">
					<?php
						foreach ($print_sections as $menu_section => $section_data) {
							if (can_review($menu_section, $member_sections, $member_permissions)) {
					?>
					<li>
						<a href="review_image_dash.php?section=<?=encode_string_array($menu_section);?>" style="<?= ($menu_section == $current_menu_section) ? 'background-color: #eee8aa;' : '';?>">
							<span class="nav-label"><i class="fa fa-print"></i> <?=$menu_section;?></span>
						</a>
					</li>
					<?php
							}
						}
					?>
				</ul>
            </li>
			<?php
			   }
			?>

			<!-- Track Catalog Orders -->
			<!-- Open to all people having access to admin panel -->
			<?php
				if (has_permission($member_permissions, ["admin"])) {
			?>
            <li class="<?php echo ($self == 'catalog_order_dashboard.php') ? 'active' : '';?>">
                <a href="catalog_order_dashboard.php"> <span class="nav-label"><i class="fa fa-users"></i> Catalog Orders </span></a>
            </li>
			<?php
				}
			?>

			<!-- Update Posting Info -->
			<!-- Open to all people having access to admin panel -->
			<?php
				if ($admin_contest['results_ready'] == '1') {
			?>
            <li class="<?php echo ($self == 'posting.php') ? 'active' : '';?>">
                <a href="posting.php"> <span class="nav-label"><i class="fa fa-send"></i> Update Award Sending Info </span></a>
            </li>
			<?php
				}
			?>

			<!-- Duplicate Profiles -->
			<!-- Open to to admin only -->
			<?php
				if (has_permission($member_permissions, ["admin"])) {
			?>
            <li class="<?php echo ($self == 'merge_profiles.php') ? 'active' : '';?>">
                <a href="merge_profiles.php"> <span class="nav-label"><i class="fa fa-users"></i> Merge Profiles </span></a>
            </li>
			<?php
				}
			?>

			<!-- Reconcile Member Profiles with YPS Member Database -->
			<!-- Open to to admin only -->
			<?php
				if (has_permission($member_permissions, ["admin", "manager"])) {
			?>
            <li class="<?php echo ($self == 'member_reconcile.php') ? 'active' : '';?>">
                <a href="member_reconcile.php"> <span class="nav-label"><i class="fa fa-arrows-h"></i> Reconcile Member Info </span></a>
            </li>
			<?php
				}
			?>

			<!-- Regenerate all or part of statistics - Only by admin -->
			<?php
				if (has_permission($member_permissions, ["admin"])) {
			?>
            <li class="<?php echo ($self == 'run_stats.php') ? 'active' : '';?>">
                <a href="run_stats.php"> <span class="nav-label"><i class="fa fa-users"></i> Statistics </span></a>
            </li>
			<?php
				}
			?>

			<!-- Only chairman, secretary, admin can generate data for reporting - runs only offline -->
			<?php
				// if (preg_match("/localhost/i", $_SERVER['SERVER_NAME']) && has_permission($member_permissions, ["admin"])) {
				if ((! $contest_archived) && has_permission($member_permissions, ["admin", "chairman", "secretary"])) {
			?>
            <li>
				<a href="op/acc_salon_data_for_reporting.php" target="_blank"><span class="nav-label"><i class="fa fa-table"></i> Download Reporting Data </span></a>
            </li>
			<?php
				}
			?>

			<!-- Catalog Support only by admin and runs only offline -->
			<?php
				if ((! $contest_archived) && has_permission($member_permissions, ["admin"]) ) {
			?>
			<li>
				<a href="javascript:void(0)"><span class="nav-label"><i class="fa fa-gears"></i> Catalog </span><span class="fa arrow"></span> </a>
				<ul class="nav nav-second-level">
					<li><a href="javascript:void(0)" onclick="launchPage('op/acc_zip_for_catalog.php', true, true)" ><i class="fa fa-download"></i> Exhibitor Thumbnails for Section</a></li>
					<li>
						<a href="op/acc_salon_zip_for_catalog.php" target="_blank" >
							<span class="nav-label"><i class="fa fa-download"></i> Exhibitor Thumbnails for the Salon </span>
						</a>
					</li>
					<li>
						<a href="op/award_list_for_catalog.php" target="_blank" >
							<span class="nav-label"><i class="fa fa-download"></i> Award List </span>
						</a>
					</li>
					<li>
						<a href="op/acceptance_list_for_catalog.php" target="_blank" >
							<span class="nav-label"><i class="fa fa-download"></i> Acceptance List </span>
						</a>
					</li>
				</ul>
			</li>
			<?php
				}
			?>

			<!-- Functions that execute only on stand alone offline installation (localhost) -->
			<?php
				if ( (! $contest_archived) && has_permission($member_permissions, ["admin"]) ) {
			?>
			<li>
				<a href="javascript:void(0)"><span class="nav-label"><i class="fa fa-gears"></i> Tools </span><span class="fa arrow"></span> </a>
				<ul class="nav nav-second-level">
					<li><a href="javascript:void(0)" onclick="launchSlot('op/copy_accepted_pics.php', 'acceptances', 25)" ><i class="fa fa-download"></i> Accepted PICs</a></li>
					<li><a href="javascript:void(0)" onclick="launchSlot('op/copy_awarded_pics.php', 'awards', 25)" ><i class="fa fa-download"></i> Awarded PICs</a></li>
					<li><a href="javascript:void(0)" onclick="launchSlot('op/copy_fullres_pics.php', 'awards', 5)" ><i class="fa fa-download"></i> Full Res PICs</a></li>
					<li><a href="javascript:void(0)" onclick="launchPage('op/copy_avatars.php', false, true)"><i class="fa fa-download"></i> Avatars</a></li>
					<li><a href="javascript:void(0)" onclick="launchPage('op/sponsor_data.php', false, true)" ><i class="fa fa-download"></i> Sponsor Data</a></li>
				</ul>
			</li>
			<?php
				}
			?>

			<!-- Certificate Generation only by admin -->
			<?php
				if ( (! $contest_archived) && has_permission($member_permissions, ["admin"]) ) {
			?>
			<li>
				<a href="javascript:void(0)"><span class="nav-label"><i class="fa fa-gears"></i> Certificates for Printing </span><span class="fa arrow"></span> </a>
				<ul class="nav nav-second-level">
					<?php
						foreach (array_merge($digital_sections, $print_sections) as $menu_section => $section_data) {
							$cert = encode_string_array("$admin_yearmonth|SECTION|$menu_section|0");
					?>
					<li>
						<a href="../op/certificate.php?cert=<?= $cert;?>" target="_blank" >
							<span class="nav-label"><i class="fa fa-download"></i> <?= $menu_section;?> </span>
						</a>
					</li>
					<?php
						}
						$cert = encode_string_array("$admin_yearmonth|SECTION|CONTEST|0");
					?>
					<li>
						<a href="../op/certificate.php?cert=<?= $cert;?>" target="_blank" >
							<span class="nav-label"><i class="fa fa-download"></i> OVERALL </span>
						</a>
					</li>
				</ul>
			</li>
			<?php
				}
			?>

			<!-- Functions that execute only on stand alone offline installation (localhost) -->
			<?php
				// if ( preg_match("/localhost/i", $_SERVER['SERVER_NAME']) && has_permission($member_permissions, ["admin"]) ) {
				if ( (! $contest_archived) && has_permission($member_permissions, ["admin"]) ) {
			?>
			<li>
				<a href="javascript:void(0)"><span class="nav-label"><i class="fa fa-gears"></i> Download for Printing </span><span class="fa arrow"></span> </a>
				<ul class="nav nav-second-level">
					<!-- <li><a href="javascript:void(0)" onclick="launchPage('op/image_merge.php?yearmonth=<?= $admin_yearmonth;?>&merge=ribbon')">Ribbons</a></li> -->
					<li><a href="javascript:void(0)" onclick="launchPage('op/image_merge_zip.php?yearmonth=<?= $admin_yearmonth;?>&merge=ribbon_holder', true, true)"><i class="fa fa-download"></i>Ribbon Holders</a></li>
					<li><a href="javascript:void(0)" onclick="launchPage('op/image_merge_zip.php?yearmonth=<?= $admin_yearmonth;?>&merge=title_card', true, true)"><i class="fa fa-download"></i>Title Cards</a></li>
					<li><a href="javascript:void(0)" onclick="launchPage('op/image_merge_zip.php?yearmonth=<?= $admin_yearmonth;?>&merge=customs_declaration', false, true)"><i class="fa fa-download"></i>Customs Declarations</a></li>
				</ul>
			</li>
			<?php
				}
			?>

			<!-- Slideshow Generation only by admin on localhost -->
			<?php
				// if ( preg_match("/localhost/i", $_SERVER['SERVER_NAME']) && has_permission($member_permissions, ["admin"]) && file_exists("../salons/$admin_yearmonth/slideshow.php") ) {
				if ( (! $contest_archived) && has_permission($member_permissions, ["admin"]) && file_exists("../salons/$admin_yearmonth/blob/slideshow.json") ) {
			?>
			<li>
				<a href="javascript:void(0)"
						onclick="launchSlot('op/image_merge_zip.php?yearmonth=<?= $admin_yearmonth;?>&merge=slideshow', 'acceptances', 25)">
					<span class="nav-label"><i class="fa fa-television"></i> Download Slideshow Pages </span>
				</a>
			</li>
			<?php
				}
			?>


			<!-- Archive Data and Pictures only by admin on localhost -->
			<?php
				// if ( preg_match("/localhost/i", $_SERVER['SERVER_NAME']) && has_permission($member_permissions, ["admin"]) ) {
				if ( (! $contest_archived) && has_permission($member_permissions, ["admin"]) && $admin_contest['results_ready'] == '1' ) {
			?>
			<li class="<?php echo ($self == 'archive_contest.php') ? 'active' : '';?>">
                <a href="archive_contest.php"> <span class="nav-label"><i class="fa fa-archive"></i> Archive Salon </span></a>
            </li>
			<?php
				}
			?>

			<!-- Download YPS Member List with proper permission -->
			<?php
			if ( ( has_permission($member_permissions, ["admin", "chairman", "secretary", "manager", "admin"]) ) ) {
			?>
			<li class="">
				<li class="">
	                <a href="https://ypsbengaluru.com/svc/yps_member_csv.php?magic=<?= encode_string_array("ypsmagic1971onwards");?>" target="_blank">
						<span class="nav-label"><i class="fa fa-archive"></i> Download YPS Member List </span>
					</a>
	            </li>
            </li>
			<?php
			   }
			?>

			<!-- Upload YPS Member List with proper permission -->
			<?php
			if ( ( has_permission($member_permissions, ["admin", "chairman", "secretary", "manager", "admin"]) ) ) {
			?>
			<li class="">
				<li class="<?php echo ($self == 'upload_yps_member.php') ? 'active' : '';?>">
	                <a href="upload_yps_member.php"> <span class="nav-label"><i class="fa fa-archive"></i> Upload YPS Member List </span></a>
	            </li>
            </li>
			<?php
			   }
			?>
        </ul>
    </div>
</aside>
<!-- Dialog for selecting section -->
<dialog id="select-section-dialog">
	<form method="dialog">
    	<p>
	    <label>Select a Section:</label>
	    <select id="selected-section">
		<?php
			foreach (array_merge($digital_sections, $print_sections) as $section => $data) {
		?>
			<option value="<?= encode_string_array($section);?>"><?= $section;?></option>
		<?php
			}
		?>
        </select>
    	</p>
    	<div>
			<button value="cancel">Cancel</button>
			<button value="launch">Launch</button>
    	</div>
	</form>
</dialog>

<!-- Loading image made visible during processing -->
<script>
	function launchSameTab(page) {
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

	function launchNewTab(page) {
		window.open(page, "_blank");
	}

	function launchPage(page, select_section = false, new_window = false) {
		if (select_section) {

			// Handle Escape press
			$("#select-section-dialog").off("cancel");			// Remove existing handlers
			$("#select-section-dialog").on("cancel", function() {
				this.returnValue = "escape";
			});
			// Handle Dialog Closure
			$("#select-section-dialog").off("close");			// Remove existing handlers
			$("#select-section-dialog").on("close", function() {
				if (this.returnValue == "launch") {
					let section = $("#selected-section").val();
					let launch_page = page + (page.match(/[?]/) == null ? "?section=" + section : "&section=" + section);

					if (new_window)
						launchNewTab(launch_page);
					else
						launchSameTab(launch_page);
				}
			});

			$("#select-section-dialog")[0].showModal();
		}
		else {
			if (new_window)
				launchNewTab(page);
			else
				launchSameTab(page);
		}
	}

</script>

<!-- Dialog for selecting slideshow pages -->
<dialog id="select-slot-dialog">
	<form method="dialog">
		<p><b><u>Select Options</u></b></p>
		<p>
		    <label>Select a Section:</label>
		    <select id="selected-slot-section">
			<?php
				foreach (array_merge($digital_sections, $print_sections) as $section => $data) {
			?>
				<option value="<?= encode_string_array($section);?>"
						data-acceptances="<?= $data['num_acceptances'];?>"
						data-hms="<?= $data['num_hms'];?>"
						data-awards="<?= $data['num_awards'] + $data['num_hms'];?>" >
					<?= $section;?>
				</option>
			<?php
				}
			?>
	        </select>
		</p>
    	<p>
		    <label>Select a Range:</label>
		    <select id="selected-slot">
	        </select>
    	</p>
    	<div>
			<button value="cancel"> Cancel </button>
			<button value="launch"> Download </button>
    	</div>
	</form>
</dialog>

<!-- Loading image made visible during processing -->
<script>

	function slot_options(page, type, size) {

		let html = "";
		let max = 0;

        if (page.includes('image_merge_zip.php')) {
            max = Number($("#selected-slot-section option:selected").attr("data-acceptances")) + Number($("#selected-slot-section option:selected").attr("data-awards"));
        }
        else {
    		if (type == "acceptances") {
    			max = $("#selected-slot-section option:selected").attr("data-acceptances");
    		}
    		else {
    			max = $("#selected-slot-section option:selected").attr("data-awards");
    		}
        }
		for (let start = 0; start < max; start += size) {
			html += "<option value='" + start + "' >Files " + (start + 1) + " to " + ((start + size) <= max ? start + size : max) + "</option> ";
		}
		return html;
	}

	function launchSlot(page, type, size) {
	   // alert(page);
		// Load slot options
		$("#selected-slot").html(slot_options(page, type, size));

		// Handle Change of sections
		$("#selected-slot-section").off("change");		// Remove existing handler
		$("#selected-slot-section").on("change", function(){
			$("#selected-slot").html(slot_options(page, type, size));
		});

		// Handle Escape press
		$("#select-slot-dialog").off("cancel");			// Remove existing handler
		$("#select-slot-dialog").on("cancel", function() {
			this.returnValue = "escape";
		});

		// Handle Dialog Closure
		$("#select-slot-dialog").off("close");			// Remove existing handler
		$("#select-slot-dialog").on("close", function(e) {
			if ( this.returnValue == "launch" ) {
				let section = $("#selected-slot-section").val();
				let start = $("#selected-slot").val();
				let params = "start=" + start + "&size=" + size + "&section=" + section;
				let launch_page = page + (page.match(/[?]/) == null ? "?" + params : "&" + params);
				window.open(launch_page, "_blank");
			}
		});

		$("#select-slot-dialog").get(0).showModal();
	}
</script>
