<!-- Generate Recognitions List -->

<div class="row">
	<div class="col-lg-12 col-md-12 col-sm-12" >
		<p>This Salon enjoys patronage from the following, internationally well-known, organization(s) offering certifications and honors to their members.
			All <b>Acceptances</b> in this Salon will be eligible for claiming honors from these organization(s).
		</p>
		<!-- Generate list of TABS -->
		<ul class="nav nav-pills">
		<?php
			$sql = "SELECT * FROM recognition WHERE yearmonth = '$contest_yearmonth' ORDER BY short_code";
			$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
			$recognition_list = array();
			$first = true;
			while ($row = mysqli_fetch_array($query)) {
				$recognition_list[] = $row['short_code'];
		?>
			<li class="<?php echo $first ? 'active' : ''; ?>" id="recognition_pill_<?=$row['short_code'];?>" >
				<a data-toggle="pill" href="#recognition_fill_<?php echo $row['short_code'];?>" >
					<div class="thumbnail" style="border:0;"><img src="salons/<?=$contest_yearmonth;?>/img/recognition/<?=$row['logo'];?>" style="width: 80px; "></div>
					<p class="text-center"><strong><?=$row['recognition_id'];?></strong></p>
				</a>
			</li>
		<?php
				$first = false;
			}
		?>
		</ul>

		<!-- Generate TAB Contents/Fills -->
		<div class="tab-content">
		<?php
			$first = true;
			for ($i = 0; $i < sizeof($recognition_list); $i++) {
				$short_code = $recognition_list[$i];
		?>
			<div id="recognition_fill_<?=$short_code;?>" class="tab-pane fade <?php echo $first ? 'in active' : '';?> " >
		<?php
				$sql  = "SELECT * FROM recognition ";
				$sql .= " WHERE yearmonth = '$contest_yearmonth' ";
				$sql .= "   AND short_code = '$short_code' ";
				$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
				if ($row = mysqli_fetch_array($query)) {
		?>
				<div class="row">
					<div class="col-sm-4 col-md-4 col-lg-4"></div>
					<div class="col-sm-4 col-md-4 col-lg-4 thumbnail" style="border:0;">
						<a href="<?php echo $row['website'];?>" target="_blank"><img src="salons/<?= $contest_yearmonth;?>/img/recognition/<?=$row['logo'];?>" style="max-width: 180px;"></a><br>
						<p class="text-center"><big><b><?=$row['short_code'];?> - <?=$row['recognition_id'];?></b></big></p>
						<p class="text-center"><a href="<?=$row['website'];?>" target="_blank"><?=$row['organization_name'];?></a></p>
					</div>
					<div class="col-sm-4 col-md-4 col-lg-4"></div>
				</div>
		<?php
					if ($row['notification'] != "") {
		?>
				<div class="row">
					<div class="col-sm-12 col-md-12 col-lg-12 thumbnail" style="border:0;">
						<a href="<?=$row['website'];?>" target="_blank"><img src="salons/<?=$contest_yearmonth;?>/img/recognition/<?=$row['notification'];?>" style="max-height: 480px;"></a>
					</div>
				</div>
		<?php
					}
				}
		?>
			</div>
		<?php
				$first = false;
			}
		?>
		</div>  <!-- tab-content -->
	</div> <!-- col -->
</div>  <!-- row -->
