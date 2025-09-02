<style>
.leftImg{
	float: left;
	padding-right: 15px;
	padding-bottom: 6px;
}
</style>
<?php
	// custom sort function by name without salutation
	function sort_by_jury_name($jury1, $jury2) {
		// $jury1_name = preg_replace("/^([^ ]+) (.*)$/", "$2", $jury1['jury_name']);		// name without the salutation
		// $jury2_name = preg_replace("/^([^ ]+) (.*)$/", "$2", $jury2['jury_name']);		// name without the salutation
		echo $jury1_name;
		echo $jury2_name;
		if ($jury1_name == $jury2_name)
			return 0;
		return ($jury1_name < $jury2_name ? -1 : 1);
	}

	// Find section-wise number of jury and Maximum Jury per section
	$sql  = "SELECT COUNT(*) AS num_jury FROM assignment ";
	$sql .= " WHERE yearmonth = '$contest_yearmonth' GROUP BY section ";
	$sql .= " ORDER BY num_jury DESC LIMIT 1 ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$row = mysqli_fetch_array($query);
	$max_jury_per_section = $row['num_jury'];

	// Prepare Jury Information
	$sql  = "SELECT * FROM assignment, section, user ";
	$sql .= " WHERE assignment.yearmonth = '$contest_yearmonth' ";
	$sql .= "   AND section.yearmonth = '$contest_yearmonth' ";
	$sql .= "   AND assignment.section = section.section ";
	$sql .= "   AND assignment.user_id = user.user_id ";
	$sql .= " ORDER BY assignment.section, user.user_name ";
	// $sql .= "ORDER BY assignment.section, assignment.jurynumber ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$jury_matrix = array();
	while($jury = mysqli_fetch_array($query)) {
		$section = $jury['section'];
		$jury_matrix[$section][] = Array ("user_id" => $jury['user_id'], "avatar" => $jury['avatar'], "jury_name" => $jury['user_name'],
											"title" => $jury['title'], "honors" => $jury['honors'], "profile_file" => $jury['profile_file']);
	}
	// Sort by name
	foreach ($jury_matrix as $section => $jury_list) {
		usort($jury_matrix[$section], "sort_by_jury_name");
	}
?>

	<div class="row">
		<div class="col-sm-12">
			<table class="table table-bordered">
				<!-- Header row -->
				<thead>
					<tr bgcolor="#e4edf5">
						<th>Section Name</th>
						<?php
							for ($i = 0; $i < $max_jury_per_section; ++ $i) {
						?>
						<th>Jury <?= $i + 1;?></th>
						<?php
							}
						?>
					</tr>
				</thead>
				<tbody>
				<?php
					foreach ($jury_matrix AS $jury_section => $jury_list) {
				?>
				<tr>
					<td><?php echo $jury_section;?></td>
					<?php
						for ($i = 0; $i < $max_jury_per_section; ++$i) {
							$jnum = $i + 1;		// Jury Numbers are from 1
					?>
					<td>
						<?php
							if (isset($jury_list[$i])) {
						?>
						<a href="javascript:void(0)" onclick="showJury('<?= $jury_list[$i]['user_id'];?>')"
								data-toggle="tooltip" title="<?= $jury_list[$i]['honors'];?>">
							<?= $jury_list[$i]['jury_name'];?></a>
						<?php
							}
						?>
					</td>
					<?php
						}
					?>
				</tr>
				<?php
					}
				?>
				</tbody>
			</table>
		</div>
	</div>

	<div class="row">
		<div class="col-sm-12" >
			<?php
				$sql  = "SELECT * FROM user WHERE user_id IN (SELECT DISTINCT user_id FROM assignment WHERE yearmonth = '$contest_yearmonth') ORDER BY MID(user_name, 5)";
				$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
				while ($jury = mysqli_fetch_array($query)) {
			?>
			<div class="row" id="<?= "jury-" . $jury['user_id'];?>" >
				<div class="col-sm-12">
					<h3 class="text-color"><?php echo $jury['user_name'];?></h3>
					<p><b><i><?php echo $jury['honors'];?></i></b></p>
					<div class="leftImg"><img src="res/jury/<?php echo $jury['avatar'];?>" class="profilePic"></div>
					<?php
						echo file_get_contents("blob/jury/" . $jury['profile_file']);
					?>
				</div>
			</div>
			<hr>
			<?php
				}
			?>
		</div>
	</div>
