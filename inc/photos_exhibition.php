<div class="panel panel-default">
	<div class="panel-heading">
		<h4 class="text-danger">EXHIBITION PHOTOS</h4>
	</div>
	<div class="panel-body">
		<p style="text-align: right; color: #888;"><b>Click on any image to download</b></p><br>
		<div class="main-slideshow">
			<div id="slideshow-exhibition" class="carousel slide" data-ride="carousel">
				<!-- Wrapper for slides -->
				<div class="carousel-inner">
					<?php
						$first = true;
						foreach ($photos as $photo) {
					?>
					<div class="item <?= $first ? 'active' : '';?>">
						<a href="<?= $photo_path . $photo['photo'];?>" download target="_blank">
							<img src="<?= $photo_path . "/download/" . $photo['photo'];?>" class="img-responsive" style="margin: auto;" alt="...">
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
