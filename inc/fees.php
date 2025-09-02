<?php
	// Computations
	// Determine if discounts are available for this Salon
	$sql  = "SELECT * FROM discount ";
	$sql .= " WHERE yearmonth = '$contest_yearmonth' ";
	$sql .= "   AND discount_code = 'CLUB' ";
	$tmpq = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$has_discounts = mysqli_num_rows($tmpq);

	// Get List of Fee Codes for Fist Level Tabs - EARLY BIRD/REGULAR/...
	$sql  = "SELECT DISTINCT fee_code, MIN(fee_start_date) AS fee_start_date, MAX(fee_end_date) AS fee_end_date FROM fee_structure ";
	$sql .= " WHERE yearmonth = '$contest_yearmonth' ";
	$sql .= " GROUP BY fee_code ";
	$tmpq = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$fee_codes = array();
	while ($tmpr = mysqli_fetch_array($tmpq))
		$fee_codes[$tmpr['fee_code']] = array("start_date" => $tmpr['fee_start_date'], "end_date" => $tmpr['fee_end_date']);

	// Get period names for discount display along with number of rowspan
	$discount_period_list = [];
	$sql  = "SELECT fee_code, COUNT(*) AS num_discount_entries ";
	$sql .= "  FROM discount ";
	$sql .= " WHERE yearmonth = '$contest_yearmonth' ";
	$sql .= " GROUP BY fee_code ";
	$tmpq = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	while ($tmpr = mysqli_fetch_array($tmpq))
		$discount_period_list[$tmpr['fee_code']] = $tmpr['num_discount_entries'];

?>
<div class="panel panel-default">
	<div class="panel-heading">
		<h4 class="text-danger">FEE STRUCTURE</h4>
	</div>
	<div class="panel-body">
		<h3 class="headline text-color" id="index-fees">Fees</h3>
		<!-- Generate TAB for each fee_code = REGULAR, EARLY_BIRD -->
		<ul class="nav nav-tabs">
		<?php
				foreach ($fee_codes AS $fee_code => $dates) {
					$start_date = ($dates['start_date'] < $registrationStartDate) ? $registrationStartDate : $dates['start_date'];
					$end_date = ($dates['end_date'] > $registrationLastDate) ? $registrationLastDate : $dates['end_date'];
					$status = (DATE_IN_SUBMISSION_TIMEZONE >= $dates['start_date'] && DATE_IN_SUBMISSION_TIMEZONE <= $dates['end_date']) ? "active" : "";
					$fee_ref = str_replace(" ", "_", $fee_code);
		?>
			<li class="<?=$status;?>">
				<a data-toggle="pill" href="#<?=$fee_ref;?>"  >
					<h4 class="text text-color"><?=$fee_code;?></h4><p><small> From: <?=print_date($start_date);?> to <?=print_date($end_date);?> </small></p>
				</a>
			</li>
		<?php
				}
		?>
		</ul>

		<!-- Generate content for each fee_code tab -->
		<div class="tab-content">
		<?php
				foreach ($fee_codes as $fee_code => $dates) {
					$status = (DATE_IN_SUBMISSION_TIMEZONE >= $dates['start_date'] && DATE_IN_SUBMISSION_TIMEZONE <= $dates['end_date']) ? "active in" : "";
					$fee_ref = str_replace(" ", "_", $fee_code);
		?>
			<!-- EARLY_BIRD or REGULAR -->
			<div id="<?=$fee_ref;?>" class="tab-pane fade <?=$status;?>"  >
		<?php
						// Get a List of Fee & Discount records to show
						$sql  = "SELECT fee_group, currency, COUNT(*) AS num_recs ";
						$sql .= " FROM fee_structure ";
						$sql .= " WHERE yearmonth = '$contest_yearmonth' ";
						$sql .= "   AND fee_code = '$fee_code' ";
						$sql .= " GROUP BY fee_group, currency ";
						$flistq = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
						if (mysqli_num_rows($flistq) > 0) {
		?>
				<div class="row">
		<?php
							while ($flistr = mysqli_fetch_array($flistq)) {
								$fee_group = $flistr['fee_group'];
								$currency = $flistr['currency'];
								// Determine if there are discounts for this fee_group
								$sql  = "SELECT COUNT(*) AS has_discounts FROM discount ";
								$sql .= " WHERE yearmonth = '$contest_yearmonth' ";
								$sql .= "   AND discount_code = 'CLUB' ";
								$sql .= "   AND fee_code = '$fee_code' ";
								$sql .= "   AND discount_group = '$fee_group' ";
								$hdq = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
								$hdr = mysqli_fetch_array($hdq);
								$tab_class = $contestFeeModel == "FEE" ? "col-lg-6 col-md-6 col-sm-6" : "col-lg-6 col-md-6 col-sm-6";
								$heading = $fee_group . " (" . $currency . ")";
		?>
					<div class="<?=$tab_class;?>">
						<p><strong><big><?= $heading; ?></big></strong></p>
						<table class="table table-bordered">
						<!-- Headings -->
						<thead>
							<tr>
								<th>Participation</th>
								<th>Last Date</th>
								<th><?= $fee_code; ?> Fee</th>
		<?php
								if ($contestFeeModel == "FEE" && $hdr['has_discounts'] > 0) {
		?>
								<th>Club Discount</th>
		<?php
								}
		?>
							</tr>
						</thead>
						<!-- Data -->
						<tbody>
		<?php
								$sql  = "SELECT fs.fee_code, fs.fee_group, fs.participation_code, fs.currency, fs.description, fs.digital_sections, fs.print_sections, ";
								$sql .= "       fs.fee_start_date, fs.fee_end_date, fs.fees, discount.discount_group, discount.group_code, discount.minimum_group_size, ";
								$sql .= "       discount.maximum_group_size, discount.discount, discount.discount_percentage ";
								$sql .= "  FROM fee_structure AS fs";
								$sql .= "  LEFT JOIN discount ";
								$sql .= "    ON discount.yearmonth = fs.yearmonth ";
								$sql .= "   AND discount.fee_code = fs.fee_code ";
								$sql .= "   AND discount.discount_code = 'CLUB' ";
								$sql .= "   AND discount.discount_group = fs.fee_group ";
								$sql .= "   AND discount.participation_code = fs.participation_code ";
								$sql .= "   AND discount.currency = fs.currency ";
								$sql .= " WHERE fs.yearmonth = '$contest_yearmonth' ";
								$sql .= "   AND fs.fee_code = '$fee_code' ";
								$sql .= "   AND fs.fee_group = '$fee_group' ";
								$sql .= "   AND fs.currency = '$currency' ";
								$sql .= " ORDER BY digital_sections DESC, print_sections DESC ";

								$fee_query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
								while ($fee_row = mysqli_fetch_array($fee_query)) {
									$digital_sections = $fee_row['digital_sections'];
									$print_sections = $fee_row['print_sections'];
									$sections = "";
									if ($digital_sections > 0)
										$sections .= $digital_sections . " Digital " . ($print_sections > 0 ? "&amp;<br>" : "");
									if ($print_sections > 0)
										$sections .= $fee_row['print_sections'] . " Print" ;
									if ($fee_row['discount'] == null || $fee_row['discount'] == "")
										$discount = 0;
									else
										$discount = $fee_row['discount'];
									$fee_end_date = $fee_row['fee_end_date'];
		?>
							<tr>
								<td><?= $sections; ?></td>
								<td><?= print_date($fee_end_date); ?></td>
								<td><?php echo $fee_row['currency'] . " " . $fee_row['fees'];?></td>
		<?php
									if ($contestFeeModel == "FEE" && $hdr['has_discounts'] > 0) {
		?>
								<td><?php echo $fee_row['currency'] . " " . $discount;?></td>
		<?php
									}
		?>
							</tr>
		<?php
								}	// END fee_structure row
		?>
						</tbody>
						</table>
					</div>
		<?php
							}	// End of while loop of fee lists
		?>
				</div>	<!-- div class='row' -->
				<div class="clearfix"></div>
		<?php
						}		// End of If condition to show fee table
		?>
			</div>	<!-- end of div under tab-content -->
		<?php
				}		// end of foreach fee_codes
		?>
		</div>			<!-- end of tab-contest -->

		<!-- Display Discounts -->
		<!-- ================= -->
		<?php
			if ($contestHasDiscounts) {
				// Fee Table Based Discount
				if ($contestFeeModel == "FEE") {
		?>
		<div class="row">
			<div class="col-sm-12">
				<h4 class="headline text-color" id="index-fees">Club/Group Discount</h4>
				<p class="text text-justify">Club Discount (or Group Discount) is available for Club/Group participants. This Salon offers
					standard rates of discount for members of Club and Group depending on the number of participants. Discounts are available
					for a <b>minimum number group/club size of <?= $discount_min_group_size;?></b> for this Salon.</p>
				<p class="text text-justify">Setting up Club Discount is simple. </p>
				<ol>
					<li>Club co-ordinator registers himself/herself as a participant.</li>
					<li>After logging in, the co-ordinator clicks on "Request Group Discount" option and enters number of promised participants and payment mode (Group/Individual).</li>
					<li>YPS Admin will look at the request and set up a group discount for the club and this will initiate an email to the co-ordinator.</li>
					<li>The co-ordinator will now be able to use "Generate Club Discount" option and enter emails of club members.</li>
					<li>If payment is done individually, Emails with discount codes are sent to each member. When the member registers using the email,
							discounted rates will automatically be applied.</li>
					<li>If payment is done for the entire group by the co-ordinator, he/she can use "Group Payment" option to select number of sections participation by each member and pay the calculated fees.
							Members will not be asked to make a payment.</li>
				</ol>
				<p class="text"><a href="mailto:salon@ypsbengaluru.in">Write to us</a> for any clarifications.</p>
		<?php
					foreach($discount_period_list as $discount_period => $num_entries_for_period) {
		?>
				<p class="text text-color"><b><u><?=$discount_period;?></u></b></p>
				<table class="table table-bordered">
					<thead>
						<tr>
							<th rowspan=2>Category</th>
							<th rowspan=2><div class="text-center">Sections</div></th>
							<th colspan=2><div class="text-center"># participants</div></th>
							<th rowspan=2><div class="text-center">Discount</div></th>
						</tr>
						<tr>
							<th><div class="text-center">Minimum</div></th>
							<th><div class="text-center">Maximum</div></th>
						</tr>
					</thead>
					<tbody>
		<?php
						// Create a list of fee_groups and number of entries
						$discount_group_list = [];
						$sql  = "SELECT discount_group, currency, COUNT(*) AS num_discount_entries FROM discount ";
						$sql .= " WHERE yearmonth = '$contest_yearmonth' ";
						$sql .= "   AND fee_code = '$discount_period' ";
						$sql .= " GROUP BY discount_group, currency ";
						$tmpq = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
						while ($tmpr = mysqli_fetch_array($tmpq))
							$discount_group_list[$tmpr['discount_group'] . "|" . $tmpr['currency']] = $tmpr['num_discount_entries'];

						// Display rows for each fee_group
						foreach ($discount_group_list as $discount_group_currency => $num_discount_entries) {
							list($discount_group, $currency) = explode("|", $discount_group_currency);
							$first = true;
							$sql  = "SELECT * FROM discount, fee_structure ";
							$sql .= " WHERE discount.yearmonth = '$contest_yearmonth' ";
							$sql .= "   AND discount.fee_code = '$discount_period' ";
							$sql .= "   AND discount.discount_group = '$discount_group' ";
							$sql .= "   AND discount.currency = '$currency' ";
							$sql .= "   AND fee_structure.yearmonth = discount.yearmonth ";
							$sql .= "   AND fee_structure.fee_code = discount.fee_code ";
							$sql .= "   AND fee_structure.fee_group = discount.discount_group ";
							$sql .= "   AND fee_structure.participation_code = discount.participation_code ";
							$sql .= "   AND fee_structure.currency = discount.currency ";
							$sql .= " ORDER BY (fee_structure.digital_sections + fee_structure.print_sections) DESC ";
							$discount_query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
							while ($discount_row = mysqli_fetch_array($discount_query)) {
								$participation_sections = "";
								if ($discount_row['digital_sections'] > 0)
									$participation_sections .= $discount_row['digital_sections'] . " Digital " . ($discount_row['print_sections'] > 0 ? "&amp;<br>" : "");
								if ($discount_row['print_sections'] > 0)
									$participation_sections .= $discount_row['print_sections'] . " Print" ;

		?>
						<tr>
		<?php
								if ($first) {
		?>
							<td rowspan="<?=$num_discount_entries;?>"><?=$discount_group;?> - <?=$currency;?></td>
		<?php
									$first = false;
								}
		?>
							<td><div class="text-center"><?=$participation_sections;?></div></td>
							<td><div class="text-center"><?=$discount_row['minimum_group_size'];?></div></td>
							<td><div class="text-center"><?=$discount_row['maximum_group_size'];?></div></td>
							<td>
								<div class="text-center">
		<?php
							 if ($discount_row['discount'] != 0)
								echo $discount_row['currency'] . " " . $discount_row['discount'];
							 else
								echo $discount_row['discount_percentage'] * 100 . " %";
		?>
								</div>
							</td>
						</tr>
		<?php
							}
						}
		?>
					</tbody>
				</table>
		<?php
					}
		?>
			</div>
		</div>
		<?php
				}
				else {
					// Salon POLICY based discount percentages
		?>
		<div class="row">
			<div class="col-sm-12">
				<!-- Discount Procedure -->
				<h4 class="headline text-color" id="index-fees">Club/Group Discount</h4>
				<p>This Salon offers discount to clubs/groups if the <b>number of participants is <?= $discount_min_group_size;?>
					or more</b>. The percentage of discount will be fixed by Salon Committee based on information provided by the Club co-ordinator.
				</p>
				<p class="text text-justify">Setting up Club Discount is simple. </p>
				<ol>
					<li>Club co-ordinator registers himself/herself as a participant.</li>
					<li>After logging in, the co-ordinator clicks on "Request Group Discount" option and enters number of promised participants
						and payment mode (Group/Individual).</li>
					<li>YPS Admin will look at the request and set up a group discount for the club and this will initiate an email to the co-ordinator.</li>
					<li>The co-ordinator will now be able to use "Generate Club Discount" option and enter emails of club members.</li>
					<li>If payment is done individually, Emails with discount codes are sent to each member. When the member registers using the email,
							discounted rates will automatically be applied.</li>
					<li>If payment is done for the entire group by the co-ordinator, he/she can use "Group Payment" option to select number
							of sections participation by each member and pay the calculated fees. Members will not be asked to make a payment.</li>
					<li>The Club co-ordinator will be able to add more participants to the list at any time.</li>
				</ol>
				<p class="text"><a href="mailto:salon@ypsbengaluru.in">Write to us</a> for any clarifications.</p>
			</div>
		</div>
		<?php
				}
			}
		?>
	</div>
</div>
