<div class="panel panel-default">
	<div class="panel-heading">
		<h4 class="text-danger">SALON SECTIONS</h4>
	</div>
	<div class="panel-body">
		<h3 class="headline text-color" id="index-sections">Sections</h3>
		<p>Please read the description of each section to understand  the award categories under each section while uploading images to maximize your win chances. Please refer to the 
			<a href="term_condition.php">Terms and Conditions</a> for restrictions on the content and post-processing for each section. </p>
		<table class="table table-bordered">
			<thead>
				<tr><th>Section</th><th>Submission Type</th><th>Max Upload</th><th>Last Upload Date</th><th>Rules</th></tr>
			</thead>
			<tbody>
			<?php
				foreach ($contestSectionList AS $tmp_section => $tmp_details) {
					if ($tmp_details['rules_blob'] == "") {
						$rules  = "<p><b>Definition</b></p>";
						$rules .= "<p>" . $tmp_details['definition'] . "</p>";
						$rules .= "<br>";
						$rules .= "<p><b>Rules</b></p>";
						$rules .= "<p>" . $tmp_details['rules'] . "</p>";
					}
					else
						$rules = file_get_contents("salons/$contest_yearmonth/blob/" . $tmp_details['rules_blob']);
			?>
				<tr>
					<td><b><?=$tmp_section;?></b></td>
					<td><span class="text-center"><?php echo ($tmp_details['section_type'] == 'P') ? "PRINT" : "DIGITAL";?></span></td>
					<td><span class="text-center"><?=$tmp_details['max_pics_per_entry'];?></span></td>
					<td><span class="text-center"><?php echo print_date($tmp_details['submission_last_date']);?></span></td>
					<td>
						<a href="#" data-toggle="modal" data-target="#section_<?php echo str_nosep($tmp_section);?>">View Rules</a>
						<div id="section_<?php echo str_nosep($tmp_section);?>" class="modal fade" role="dialog">
							<div class="modal-dialog">
								<div class="modal-content">
									<div class="modal-header">
										<button type="button" class="close" data-dismiss="modal">&times;</button>
										<h5 class="text-color">Rules for <?= $tmp_section;?></h5>
									</div>
									<div class="modal-body">
										<p class="text text-justified"><?= $rules;?></p>
									</div>
								</div>
							</div>
						</div>
					</td>
				</tr>
			<?php
				}
			?>
			</tbody>
		</table>
	</div>
</div>