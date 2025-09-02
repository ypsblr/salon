<?php
	$topPictures = array(
					"slide-1.jpg", "slide-2.jpg", "slide-3.jpg", "slide-4.jpg", "slide-5.jpg",
					"slide-6.jpg", "slide-7.jpg", "slide-8.jpg", "slide-9.jpg", "slide-10.jpg",
					"slide-11.jpg", "slide-12.jpg", "slide-13.jpg", "slide-14.jpg", "slide-15.jpg",
					"slide-16.jpg", "slide-17.jpg", "slide-18.jpg", "slide-19.jpg", "slide-20.jpg",
					"slide-21.jpg", "slide-22.jpg", "slide-23.jpg", "slide-24.jpg", "slide-25.jpg",
					"slide-26.jpg", "slide-27.jpg", "slide-28.jpg", "slide-29.jpg", "slide-30.jpg",
					"slide-31.jpg", "slide-32.jpg", "slide-33.jpg", "slide-34.jpg", "slide-35.jpg",
					"slide-36.jpg", "slide-37.jpg", "slide-38.jpg", "slide-39.jpg", "slide-40.jpg",
					"slide-41.jpg", "slide-42.jpg", "slide-43.jpg", "slide-44.jpg", "slide-45.jpg",
					"slide-46.jpg", "slide-47.jpg", "slide-48.jpg", "slide-49.jpg", "slide-50.jpg",
					"slide-51.jpg", "slide-52.jpg", "slide-53.jpg", "slide-54.jpg", "slide-55.jpg",
					"slide-56.jpg", "slide-57.jpg", "slide-58.jpg", "slide-59.jpg", "slide-60.jpg",
					"slide-61.jpg", "slide-62.jpg", "slide-63.jpg", "slide-64.jpg", "slide-65.jpg",
					"slide-66.jpg", "slide-67.jpg", "slide-68.jpg", "slide-69.jpg", "slide-70.jpg",
					"slide-71.jpg", "slide-72.jpg", "slide-73.jpg", "slide-74.jpg", "slide-75.jpg",
					"slide-76.jpg", "slide-77.jpg", "slide-78.jpg", "slide-79.jpg", "slide-80.jpg",
					"slide-81.jpg", "slide-82.jpg", "slide-83.jpg", "slide-84.jpg", "slide-85.jpg",
					"slide-86.jpg", "slide-87.jpg", "slide-88.jpg", "slide-89.jpg", "slide-90.jpg"
	);
	$idx = rand(0, sizeof($topPictures)-1);
	$topPicture = $topPictures[$idx];
?>
<div class="container" style="margin: 0; padding: 0;">
	<div class="row" style="background-image: url('/img/banner/<?php echo $topPicture;?>'); margin-left: 0; margin-right: 0; margin-bottom: 20px; width:1260px; height:500px;">
		<div class="col=sm-1"></div>
		<div class="col-sm-10"><h1 class="animated slideInRight" style="color:yellow"> <?php echo $contestName;?> </h1></div>
		<div class="col-sm-1"></div>
	</div>
</div>
