<?php
	if ($catalogReady && date("Y-m-d") > $catalogReleaseDate) {
?>
<!-- View / Download Catalog -->
<div class="row">
<h3 class="headline text-color">Catalog</h3>
	<div class="col-sm-8">
		<p>The <?php echo $contestName;?> Catalog was released on <?php echo date(DATE_FORMAT, strtotime($catalogReleaseDate)); ?>.
			You can download the high resolution Salon Catalog as personal souvenir or flip through
			the catalog like a physical book by clicking on <b>View</b> button.</p>
	</div>
	<div class="col-sm-4">
		<a href="/viewer/catalog.php?id=<?=$contest_yearmonth;?>&catalog=<?=$contestCatalog;?>" target="_blank" class="btn btn-color" style="width: 100%;">View</a>
		<br>
		<a href="/catalog/<?=$contestCatalogDownload;?>" download class="btn btn-color" style="width: 100%;">Download</a>
	</div>
</div>
<?php
	}
?>
