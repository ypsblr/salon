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
		<table class="table table-bordered">
			<thead>
				<tr>
					<th rowspan="2" colspan="2" ><div class="align-middle"># Sections</div></th>
					<th colspan="2" ><div class="text-center align-middle">Early Bird<br><small>(till <?=$early_bird_end_date;?>)</small></div></th>
					<th colspan="2" ><div class="text-center align-middle">Regular</div></th>
				</tr>
				<tr>
					<th><div class="text-center">Indian Rupees</div></th><th><div class="text-center">USD</div></th>
					<th><div class="text-center">Indian Rupees</div></th><th><div class="text-center">USD</div></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td colspan="2">ALL Print and Digital Sections</td>
					<td><div class="text-center">1500</div></td><td><div class="text-center">25</div></td>
					<td><div class="text-center">2000</div></td><td><div class="text-center">30</div></td>
				</tr>
				<tr>
					<td rowspan="3"><div style="text-align : center;">DIGITAL</div></td>
					<td>All 4 Sections</td>
					<td><div class="text-center">1000</div></td><td><div class="text-center">18</div></td>
					<td><div class="text-center">1500</div></td><td><div class="text-center">23</div></td>					
				</tr>
				<tr>
					<td>2 Sections</td>
					<td><div class="text-center">700</div></td><td><div class="text-center">12</div></td>
					<td><div class="text-center">800</div></td><td><div class="text-center">14</div></td>					
				</tr>
				<tr>
					<td>1 Section</td>
					<td><div class="text-center">400</div></td><td><div class="text-center">7</div></td>
					<td><div class="text-center">600</div></td><td><div class="text-center">10</div></td>					
				</tr>
				<tr>
					<td rowspan="2"><div style="text-align : center;">PRINT</div></td>
					<td>All 2 Sections</td>
					<td><div class="text-center">700</div></td><td><div class="text-center">12</div></td>
					<td><div class="text-center">800</div></td><td><div class="text-center">14</div></td>					
				</tr>
				<tr>
					<td>1 Section</td>
					<td><div class="text-center">400</div></td><td><div class="text-center">7</div></td>
					<td><div class="text-center">600</div></td><td><div class="text-center">10</div></td>					
				</tr>
			</tbody>
		</table>
	</div>
</div>
