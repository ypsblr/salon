<?php
	// Computations
	$sql  = "SELECT MAX(fee_end_date) AS early_bird_end_date FROM fee_structure ";
	$sql .= " WHERE yearmonth = '$contest_yearmonth' ";
	$sql .= "   AND fee_code = 'EARLY BIRD' ";
	$tmpq = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$tmpr = mysqli_fetch_array($tmpq);
	$early_bird_end_date = $tmpr['early_bird_end_date'];
?>
<div class="panel panel-default">
	<div class="panel-heading">
		<h4 class="text-danger">FEE STRUCTURE</h4>
	</div>
	<div class="panel-body">
		<h3 class="headline text-color" id="index-fees">Fees</h3>
		<p class="text text-justify">This Salon offers a simple and low rate structure for easy participation. You can also upgrade your
			participation option any time till the last date and increase your chances of winning.</p>
		<table class="table table-bordered">
			<thead>
				<tr>
					<th rowspan="2" ><div class="align-middle"># Sections</div></th>
					<th colspan="2" ><div class="text-center align-middle">Fees</div></th>
				</tr>
				<tr>
					<th><div class="text-center">Indian Rupees</div></th><th><div class="text-center">USD</div></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>ALL Digital Sections</td>
					<td><div class="text-center">860</div></td><td><div class="text-center">10</div></td>
				</tr>
				<tr>
					<td>TWO Digital Sections</td>
					<td><div class="text-center">430</div></td><td><div class="text-center">5</div></td>
				</tr>
			</tbody>
		</table>
	</div>
</div>
