<?php
include "inc/awards_lib.php";
?>
<!-- Display Awards in a Column using Tabs -->
<!-- PICTURE Awards Tabs -->
<?php
	$sql = "SELECT * FROM award WHERE yearmonth = '$contest_yearmonth' AND award_type = 'pic' AND section != 'CONTEST' ";
	$awq = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_num_rows($awq) > 0) {
?>
<div class="row">
	<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
		<h2 class="text text-info headline">Picture Awards</h4>
		<!-- Generate List of Award Groups -->
		<?php $ag_list = awards_generate_ag_list(); ?>
		<!-- Content for each each Entrant Category -->
		<?php awards_generate_ag_tab($ag_list); ?>
	</div>
</div>
<?php
	}
?>

<!-- ENTRY AWARDS -->
<?php
	// $sql = "SELECT * FROM award WHERE yearmonth = '$contest_yearmonth' AND award_type = 'entry' AND section = 'CONTEST' ";
	$sql = "SELECT * FROM award WHERE yearmonth = '$contest_yearmonth' AND award_type = 'entry' ";
	$awq = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_num_rows($awq) > 0) {
?>
<div class="row">
	<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
		<h2 class="text text-info headline">Individual Awards</h4>
		<!-- Generate List of Individual Awards -->
		<?php awards_contest_level_list('entry');?>
	</div>
</div>
<div class="divider"></div>
<?php
	}
?>

<!-- SPL PICTURE AWARDS -->
<?php
	$sql = "SELECT * FROM award WHERE yearmonth = '$contest_yearmonth' AND award_type = 'pic' AND section = 'CONTEST' ";
	$awq = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_num_rows($awq) > 0) {
?>
<div class="row">
	<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
		<h2 class="text text-info headline">Spl. Picture Awards</h4>
		<!-- Generate List of Individual Awards -->
		<?php awards_contest_level_list('pic', 'CONTEST');?>
	</div>
</div>
<div class="divider"></div>
<?php
	}
?>

<!-- CLUB AWARDS -->
<?php
	$sql = "SELECT * FROM award WHERE yearmonth = '$contest_yearmonth' AND award_type = 'club' ";
	$awq = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_num_rows($awq) > 0) {
?>
<div class="row">
	<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
		<h2 class="text text-info headline">Club Awards</h4>
		<!-- Generate List of Individual Awards -->
		<?php awards_contest_level_list('club');?>
	</div>
</div>
<?php
	}
?>
