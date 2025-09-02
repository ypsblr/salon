<!-- Generate Awards List -->
<?php include_once("inc/categories.php");?>
<?php include_once("inc/sponsor_lib.php");?>

<!-- PHP Functions used for generating Code -->
<?php
// Generates list of Award Groups for generating nav-pill tabs
function awards_generate_ag_list() {
	global $DBCON;
	global $contest_yearmonth;

	// Get a list of Award Goups
	$sql  = "SELECT DISTINCT award_group FROM award ";
	$sql .= " WHERE yearmonth = '$contest_yearmonth' ";
	$sql .= "   AND award_type = 'pic' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$ec_list = array();
	while ($row = mysqli_fetch_array($query)) {
		$ec_list[] = $row['award_group'];
	}

	return $ec_list;
}	//- awards_generate_ag_list
?>

<?php
// Generate Section TAB List
// =========================
// $columns == 0 : Render TABS
// $columns != 0 : Do not Render TABS
function awards_generate_section_list($tab_ag, $columns = 0) {
	global $DBCON;
	global $contest_yearmonth;

	$sql  = "SELECT DISTINCT section ";
	$sql .= "  FROM award ";
	$sql .= " WHERE yearmonth = '$contest_yearmonth' ";
	$sql .= "   AND award_group = '$tab_ag' ";
	$sql .= "   AND award_type = 'pic' ";
	$sql .= "   AND section != 'CONTEST' ";
	$sql .= " ORDER BY section";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$secList = array();
	$first = true;
	while ($row = mysqli_fetch_array($query)) {
		$secList[] = $row['section'];
		if ($columns == 0) {
?>
		<li class="<?php echo $first ? "active" : ""; ?>" id="pill_<?php echo str_nosep($tab_ag . "-" . $row['section']);?>" data-pill="<?=$tab_ag;?>" >
			<a data-toggle="pill" href="#award_<?php echo str_nosep($tab_ag . "-" . $row['section']);?>" ><?=$row['section'];?></a>
		</li>
<?php
		}
		$first = false;
	}
	return $secList;
}
//- awards_generate_section_list
//------------------------------
?>

<?php
// Print Award Name
function awards_name_line($row) {
?>
	<b><big><?= $row['award_name'];?></big></b>
<?php
}
?>

<?php
// Print Sponsorship Link
function awards_sponsor_link($link_id) {
	global $contest_yearmonth;

?>
	<span style="margin-left: 20px;">
		<a style="font-weight: bold;" href="sponsor.php?contest=<?=$contest_yearmonth;?>&awid=<?=$link_id;?>">Sponsor this Award</a>
	</span>
<?php
}
?>

<?php
//
// Print Award Properties as icons
function awards_property_line($row){
	global $DBCON;
	global $contest_yearmonth;
?>
	<span style='padding-left: 20px'>
<?php
		if ($row['number_of_awards'] > 0) {
?>
			<span class="badge" title="<?=$row['number_of_awards'];?> award(s)"><?=$row['number_of_awards'];?></span>
<?php
		}
		if ($row['has_medal'] != 0) {
?>
			<i class="fa fa-certificate" aria-hidden="true" title="Medal" style="margin-left: 8px;"></i>
<?php
		}
		if ($row['has_pin'] != 0) {
?>
			<i class="fa fa-map-pin" aria-hidden="true" title="Pin" style="margin-left: 8px;"></i>
<?php
		}
		if ($row['has_ribbon'] != 0) {
?>
			<i class="fa fa-bookmark" aria-hidden="true" title="Ribbon" style="margin-left: 8px;"></i>
<?php
		}
		if ($row['has_memento'] != 0) {
?>
			<i class="fa fa-trophy" aria-hidden="true" style="margin-left: 8px;" title="Memento"></i>
<?php
		}
		if ($row['has_gift'] != 0) {
?>
			<i class="fa fa-gift" aria-hidden="true" style="margin-left: 8px;" title="Gift"></i>
<?php
		}
		if ($row['has_certificate'] != 0) {
?>
			<i class="fa fa-graduation-cap" aria-hidden="true" style="margin-left: 8px;" title="Certificate"></i>
<?php
		}
		if ($row['cash_award'] != 0) {
?>
			<i class="fa fa-money" aria-hidden="true" style="margin-left: 8px;" title="Award Money"></i> Rs. <?php echo $row['cash_award'];?>/-
<?php
		}
?>
	</span>
<?php
}
//- awards_property_line
?>


<?php
// Get Awards for the section and Award Group List
function awards_get_award_data($tab_section, $tab_ag) {
	global $DBCON;
	global $contest_yearmonth;

	$sql  = "SELECT * FROM award ";
	$sql .= " WHERE yearmonth = '$contest_yearmonth' ";
	$sql .= "   AND section = '$tab_section' ";
	$sql .= "   AND award_group = '$tab_ag' ";
	$sql .= "   AND award_type = 'pic' ";
	$sql .= "   AND section != 'CONTEST' ";
	$sql .= "   AND level < 99 ";
	$sql .= " ORDER BY level, sequence";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	return $query;
}
?>


<?php
// Generate List of Sponsors
function awards_sponsor_lines($award_id) {
	global $DBCON;
	global $contest_yearmonth;

	$sql  = "SELECT * FROM sponsorship, sponsor ";
	$sql .= " WHERE sponsorship.yearmonth = '$contest_yearmonth' ";
	$sql .= "   AND sponsorship.sponsorship_type = 'AWARD' ";
	$sql .= "   AND sponsorship.link_id = '$award_id' ";
	$sql .= "   AND sponsorship.sponsor_id = sponsor.sponsor_id ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	while ($row = mysqli_fetch_array($query)) {
?>
		<div class="row" style="padding-top: 8px;">
		<!-- Sponsor Logo -->
			<div class="col-lg-2 col-md-2 col-sm-2">
<?php
				if (!empty($row['sponsor_logo'])){
?>
					<a href="<?php echo ($row['sponsor_website'] != '') ? $row['sponsor_website'] : '#';?>" target="_blank" >
						<img style="max-width: 100%; max-height: 40px;" src="<?php echo '/res/sponsor/' . $row['sponsor_logo'];?>">
					</a>
<?php
				}
?>
			</div>
			<div class="col-lg-10 col-md-10 col-sm-10" style="padding-left: 8px;">
<?php
				if (! empty($row['award_name_suffix'])) {
?>
					<p class="text text-primary"><?php echo $row['award_name_suffix'];?></p>
<?php
				}
?>
					<p>
						Sponsored by <b><?php echo $row['sponsor_name'];?></b>
						<span class="badge" title="<?php echo $row['number_of_units'];?> award(s)"><?php echo $row['number_of_units'];?></span>
					</p>
			</div>
		</div>
<?php
	}
}
?>

<?php
// Generate content for each Section Tab
//======================================
function awards_generate_section_tab($secList, $tab_ag, $columns) {
	global $DBCON;
	global $contest_yearmonth;

	$first = true;
	$col_width = ($columns == 0) ? 12 : (int) floor(12 / $columns);
	$col_count = 0;
	if ($columns != 0) {
?>
	<div class="row">
<?php
	}
	foreach ($secList AS $tab_section) {
		if ($columns == 0) {
			$class = "tab-pane fade " . ($first ? "in active" : "");
			$style = "";
			$id = "award_" . str_nosep($tab_ag . "-" . $tab_section);
		}
		else {
			$class = "col-sm-" . $col_width;
			$style = "padding-right: 10px;";
			$id = "awcol_" . str_nosep($tab_ag . "-" . $tab_section);
		}
?>
		<div id="<?=$id;?>" class="<?=$class;?>" style="<?=$style;?>" data-tab="<?=$tab_ag;?>" >
			<h5 class="text text-color"><?=$tab_section;?></h5>
<?php
			$query = awards_get_award_data($tab_section, $tab_ag);
			while ($row = mysqli_fetch_array($query)) {
				$award_id = $row['award_id'];

				// Check if there is a Sponsorship Opportunity Available
				$num_opps = $row['sponsored_awards'];
				$num_sponsored = spn_num_awards_sponsored($award_id);
				$is_opportunity_open = ($num_opps > 0 && $num_opps > $num_sponsored);
				$is_sponsored = ($num_sponsored > 0);
?>
				<div class="row" style="padding-bottom: 4px; border-bottom: 1px solid #ccc" >
					<!-- Award Name -->
					<div class="col-lg-12 col-md-12 col-sm-12">
						<?php awards_name_line($row); ?>
					</div>
					<!-- Award Properties -->
					<div class="col-lg-12 col-md-12 col-sm-12">
						<?php awards_property_line($row); ?>
					</div>
<?php
				if ($columns == 0) {
					if ($is_opportunity_open) {
?>
					<!-- Award Links -->
					<div class="col-lg-12 col-md-12 col-sm-12">
						<?php awards_sponsor_link($award_id); ?>
					</div>
<?php
					}
					if ($is_sponsored) {
?>
					<!-- Award Sponsors -->
					<div class="col-lg-12 col-md-12 col-sm-12">
							<?php awards_sponsor_lines($award_id); ?>
					</div>
<?php
					}	// if is_sponsored
				}	// columns == 0
?>
				</div>
<?php
			}	// while $row
?>
		</div>
<?php
		$first = false;
		++ $col_count;
		if ($columns > 0 && $col_count >= $columns) {
?>
		<div class="clearfix"></div>
		<br><br>
<?php
			$col_count = 0;
		}
	}	// foreach
	if ($columns != 0) {
?>
	</div>
<?php
	}
}
//- awards_generate_section_tab
//-----------------------------
?>

<?php
// Generate contents for each Award Group List
// Parameters:
//		$ec_list - List of Award Groups
//		$columns -	0 - Tabbed Format
//					1-12 - Number of columns per Row
function awards_generate_ag_tab($ag_list, $columns = 0) {
	global $DBCON;
	global $contest_yearmonth;

	foreach($ag_list AS $tab_ag) {
?>
		<div class="row">
			<div class="col-sm-12">
				<h4 class="text text-color headline">
					For <?=$tab_ag;?>
					<a href="#" data-toggle="modal" data-target="#ecdsc_<?php echo str_nosep($tab_ag);?>" title="Click for details"><i class="fa fa-question-circle"></i></a>
				</h4>

				<!-- Modal List of Entrant Categories -->
				<div id="ecdsc_<?php echo str_nosep($tab_ag);?>" class="modal fade" role="dialog">
					<div class="modal-dialog">
						<!-- Modal content-->
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal">&times;</button>
								<h4 class="modal-title text-color">Categories of Entrants eligible for <?=$tab_ag;?> Awards</h4>
							</div>
							<div class="modal-body" style="margin-left: 10px; margin-right: 10px;">
								<?php
									$ec_list = ec_get_entrant_category_list($tab_ag);
									ec_generate_ec_description($ec_list);
								?>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
							</div>
						</div>
					</div>
				</div>

				<!-- Generate list of TABS for each section -->
				<ul class="nav nav-pills">
					<?php $secList = awards_generate_section_list($tab_ag, $columns);?>
				</ul>
				<!-- Generate TAB Contents for each section -->
				<div class="tab-content">
					<?php awards_generate_section_tab($secList, $tab_ag, $columns); ?>
				</div>
			</div>
		</div>
		<div class="divider"></div>
<?php
	}
}
//- awards_generate_ag_tab
?>

<?php
// Individual Awards List
function awards_contest_level_list($award_type, $section = "") {
	global $DBCON;
	global $contest_yearmonth;

	$sql  = "SELECT * FROM award ";
	$sql .= " WHERE yearmonth = '$contest_yearmonth' ";
	$sql .= "   AND award_type = '$award_type' ";
	if ($section != "")
		$sql .= "   AND section = '$section' ";
	$sql .= " ORDER BY award_group, level, sequence";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	while ($row = mysqli_fetch_array($query)) {
?>
	<div class="row">
		<div class="col-lg-12 col-md-12 col-sm-12">
			<!-- <h5 class="text text-color">For <?=$row['award_group'];?></h5> -->
<?php
			// Check if there is a Sponsorship Opportunity Available
			$award_id = $row['award_id'];
			$num_opps = $row['sponsored_awards'];
			$num_sponsored = spn_num_awards_sponsored($award_id);
			$is_opportunity_open = ($num_opps > 0 && $num_opps > $num_sponsored);
			$is_sponsored = ($num_sponsored > 0);

?>
			<div class="row" style="padding-bottom: 4px;border-bottom: 1px solid #ccc">
				<!-- Award Name -->
				<div class="col-lg-12 col-md-12 col-sm-12">
					<?php awards_name_line($row); ?>
				</div>
				<!-- Award Properties -->
				<div class="col-lg-12 col-md-12 col-sm-12">
					<?php awards_property_line($row); ?>
				</div>
<?php
			if ($is_opportunity_open) {
?>
				<!-- Award Links -->
				<div class="col-lg-12 col-md-12 col-sm-12">
					<?php awards_sponsor_link($award_id); ?>
				</div>
<?php
			}
			if ($is_sponsored) {
?>
				<!-- Award Sponsors -->
				<div class="col-lg-12 col-md-12 col-sm-12">
					<?php awards_sponsor_lines($award_id); ?>
				</div>
<?php
			}	// if is_sponsored
?>
			</div>
		</div>
	</div>
<?php
	}	// while $row

}
?>
