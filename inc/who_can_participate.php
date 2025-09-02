<?php include_once("inc/categories.php");?>
<div class="panel panel-default">
	<div class="panel-heading">
		<h4 class="text-danger">WHO CAN PARTICIPATE ?</h4>
	</div>
	<div class="panel-body">
		<h3 class="headline text-color" id="index-sections">Participant Categories</h3>
		<p>The following categories of participants can participate in this Salon.</p>
		<table class="table table-bordered">
		<thead>
			<tr><th>Category</th><th>Eligibility & Features</th></tr>
		<?php
			foreach ($contestEntrantCategoryList AS $tmp_ec_name => $tmp_ec) {
		?>
			<tr>
				<td><b><?=$tmp_ec_name;?></b></td>
				<td><?php ec_print_ec_details($tmp_ec);?></td>
			</tr>
		<?php
			}
		?>
		</table>
	</div>
</div>