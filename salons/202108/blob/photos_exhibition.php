<?php
	$photo_path = "/photos/202108/exhibition/";
	if (is_dir($_SERVER['DOCUMENT_ROOT'] . $photo_path)) {
		$photos = [
			// array("photo" => "VCDR.jpg", "caption" => "caption"),
			array("photo" => "VCDR9708.jpg", "caption" => "Sanitization, Declaration and Temperature testing"),
			array("photo" => "VCDR9694.jpg", "caption" => "Awards on display for public"),
			array("photo" => "VCDR9723.jpg", "caption" => "YPS Golden Jubilee commemoration mugs and caps on sale"),
			array("photo" => "VCDR9724.jpg", "caption" => "YPS Golden Jubilee commemoration mugs and caps on sale"),
			array("photo" => "VCDR9716.jpg", "caption" => "YPS President taking Chief Guest hrough a tour of the exhibition"),
			array("photo" => "VCDR9742.jpg", "caption" => "Doordarshan taking an interview"),
			array("photo" => "VCDR9752.jpg", "caption" => "Doordarshan interviewing Young Talent award-winners Param Jain and Shikha"),
			array("photo" => "VCDR9779.jpg", "caption" => "P N Arya and Rajaram proud of coverage in local newspapers"),
			array("photo" => "VCDR9764.jpg", "caption" => "Master of Ceremonies, Girish Ananthamurthy, Salon Committee member"),
			array("photo" => "VCDR9784.jpg", "caption" => "Invocation song by Sunita Rani"),
			array("photo" => "VCDR9797.jpg", "caption" => "Chair Person Prema Kakade acknowledging everyone making the Salon a success"),
			array("photo" => "VCDR9819.jpg", "caption" => "Lamp Lighting by Chief Guest with (L-R) Satish, Krishna Bhat and Prema Kakade"),
			array("photo" => "VCDR9835.jpg", "caption" => "Satish explaining the YPS journey that has put YPS on top among Indian Clubs"),
			array("photo" => "VCDR9856.jpg", "caption" => "Krishna Bhat presenting Salon Report"),
			array("photo" => "VCDR9861.jpg", "caption" => "Release of Catalog by the Chief Guest"),
			array("photo" => "VCDR9870.jpg", "caption" => "Team presenting the international quality catalog to the audience"),
			array("photo" => "VCDR9884.jpg", "caption" => "Passionate address by the Chief Guest Dr. Shivakumar Begar"),
			array("photo" => "VCDR9900.jpg", "caption" => "Anitha Mysore, Salon Committee member leading presentation of awards"),
			array("photo" => "VCDR9907.jpg", "caption" => "Param Jain collecting Young Talent Award for his picture 'Water Splash'"),
			array("photo" => "VCDR9907.jpg", "caption" => "Vinyasa Ubaradka collecting TNA Perumal YPS Golden Jubilee Award for his picture 'Pagoda of Butterflies'"),
			array("photo" => "VCDR9911.jpg", "caption" => "Umashankar B N collecting YPS Honorable Mention for his picture 'Attack'"),
			array("photo" => "VCDR9914.jpg", "caption" => "D C Amithkumar collecting YPS Silvcer medal for his picture 'Dazzling Eyes'"),
			array("photo" => "VCDR9917.jpg", "caption" => "Yukthi Padmakar Reddy collecting the YPS Young Talent Award for her picture 'Black and White at its best'"),
			array("photo" => "VCDR9922.jpg", "caption" => "Ramesh Hoskote collecting FIP Honorable Mention award for his picture 'Ceremonial'"),
			array("photo" => "VCDR9925.jpg", "caption" => "Sharika V collecting Young Talent Award for the picture 'No way'"),
			array("photo" => "VCDR9933.jpg", "caption" => "Dr. Ajit Huilgol collecting B N S Deo YPS Golden Jubilee award for his picture 'Territorial fight 2'"),
			array("photo" => "VCDR9936.jpg", "caption" => "Arjun Haarith collecting FIP Honorable Mention award for his picture 'Weavers devouring Bull'"),
			array("photo" => "VCDR9937.jpg", "caption" => "Udaya Tejaswi Urs collecting FIP Honorable Mention award for his picture 'Muniya with feather'"),
			array("photo" => "VCDR9940.jpg", "caption" => "Chetan Rao Mane collecting YPS Honorable Mention award for his picture 'Role Play'"),
			array("photo" => "VCDR9944.jpg", "caption" => "Dheeraj Rajpal collecting FIP medal for his picture 'Three's Company, Capadoccia'"),
			array("photo" => "VCDR9947.jpg", "caption" => "Sudheendra K P collecting YPS Silver medal for his picture 'Not my control'"),
			array("photo" => "VCDR9950.jpg", "caption" => "Param Jain collecting his second YPS Young Talent award for his picture 'Hori Jump'"),
			array("photo" => "VCDR9954.jpg", "caption" => "Dr. Ajit Huilgol collecting his second award, FIP Honorable Mention, for his picture 'Elephants at Mt. Kilimanjaro'"),
			array("photo" => "VCDR9955.jpg", "caption" => "Sudheendra K P collecting his second award, FIP Honorable Mention, for his picture 'Bull Race in Indonesia'"),
			array("photo" => "VCDR9958.jpg", "caption" => "Yogesh Mokashi collecting his YPS Bronze medal for his picture 'Lone Ranger'"),
			array("photo" => "VCDR0147.jpg", "caption" => "YPS Salon Committee all smiles for having held a top ranking salon with meticulous efforts"),
		];
?>
<div class="panel panel-default">
	<div class="panel-heading">
		<h4 class="text-danger">EVENT PHOTOS</h4>
	</div>
	<div class="panel-body">
		<p style="text-align: right; color: #888;"><b>Click on any image to download</b></p><br>
		<div class="main-slideshow">
			<div id="slideshow-exhibition" class="carousel slide" data-ride="carousel">
				<!-- Wrapper for slides -->
				<div class="carousel-inner">
					<!-- <div class="item active">
						<a href="photos/exhibition/full/HPS_5170-Pano.jpg" download target="_blank"><img src="photos/exhibition/HPS_5170-Pano.jpg" class="img-responsive" alt="...">
						<div class="carousel-caption d-none d-md-block">
							<h5>All Pictures on display at Exhibition Hall</h5>
						</div>
					</div> -->
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
