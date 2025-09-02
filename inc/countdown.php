<?php
	$oneWeekFromNow = date("Y-m-d", strtotime("+1 Week"));
	if ($oneWeekFromNow > $registrationLastDate && ! $resultsReady) {
?>
<h3 class="headline text-color">Time Left to Register</h3>
<table class="table">
	<tr>
		<td align="right" width="35%"><h3>Ends at Midnight (<?php echo $submissionTimezoneName;?> Time) of <?php echo date("F j, Y", strtotime($registrationLastDate));?> </h3></td>
		<td align="center" style="width: 60px; margin: auto;" ><img src="img/flashing_star_wht.gif"></td>
		<td align="center" valign="center"><h1 style="color: red;" id="countdown"></h1></td>
	</tr>
</table>
<?php
	}
?>
