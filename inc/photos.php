<?php
function sort_by_sequence ($a, $b) {
	if ($a["sequence"] == $b["sequence"])
		return 0;
	return ($a["sequence"] < $b["sequence"]) ? -1 : 1;	// ascending order
}
function photo_slideshow($contest, $event) {
	$home = $_SERVER['DOCUMENT_ROOT'];
	$csvpath = "$home/salons/$contest/blob/photos.csv";
	$photospath = "$home/photos/$contest/$event";
	if (file_exists($csvpath) && is_dir($photospath)) {
		$event_photos = [];
		// CSV format - event, sequence, file_name, description
		$csvfile = fopen($csvpath, "r");
		while ($row = fgetcsv($csvfile)) {
			if ($row[0] == $event && file_exists($photospath . "/" . $row[2])) {
				$event_photos[] = array("sequence" => $row[1], "photo" => $row[2], "caption" => $row[3]);
			}
		}
		usort($event_photos, "sort_by_sequence");
		if (sizeof($event_photos) > 0) {
?>
	<div class="panel panel-default">
		<div class="panel-heading">
			<h4 class="text-danger"><?= strtoupper($event);?> PHOTOS</h4>
		</div>
		<div class="panel-body">
			<p style="text-align: right; color: #888;"><b>Click on any image to download</b></p><br>
			<div class="main-slideshow">
				<div id="slideshow-<?= $event;?>" class="carousel slide" data-ride="carousel">
					<!-- Wrapper for slides -->
					<div class="carousel-inner">
					<?php
						$first = true;
						foreach ($event_photos as $photo) {
					?>
						<div class="item <?= $first ? 'active' : '';?>">
							<a href="<?= "/photos/$contest/$event/download/" . $photo['photo'];?>" download target="_blank">
								<img src="<?= "/photos/$contest/$event/" . $photo['photo'];?>" class="img-responsive" style="margin: auto;" alt="...">
						</a>
						<div class="carousel-caption d-none d-md-block">
							<h5><?= $photo['caption'];?></h5>
						</div>
					</div>
					<?php
							$first = false;
						}
					?>
				</div>
				<!-- Controls -->
				<a class="slideshow-arrow slideshow-arrow-prev bg-hover-color" href="#slideshow-exhibition" data-slide="prev">
				  <i class="fa fa-angle-left"></i>
				</a>
				<a class="slideshow-arrow slideshow-arrow-next bg-hover-color" href="#slideshow-exhibition" data-slide="next">
				  <i class="fa fa-angle-right" ></i>
				</a>
			</div>
		</div>
<?Php
		}
?>
	</div>
</div>
<?php
	}
}
?>
