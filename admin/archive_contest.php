<?php
// session_start();
include("inc/session.php");
include("inc/connect.php");
include("inc/lib.php");


if ( ! empty($_SESSION['admin_id']) && ! empty($_SESSION['admin_yearmonth']) ) {
	// Set up user and contest information
	include ("inc/load_common_data.php");

	// $contest_archived = $admin_contest['archived'];

	// Sections
	$section_list = [];
	$sql  = "SELECT section.section, stub, COUNT(*) AS num_pics FROM section, pic ";
	$sql .= " WHERE section.yearmonth = '$admin_yearmonth' ";
	$sql .= "   AND pic.yearmonth = section.yearmonth ";
	$sql .= "   AND pic.section = section.section ";
	$sql .= " GROUP BY section.section, stub ";
	$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$section_being_archived = "";
	$stub_being_archived = "";
	$num_pics_being_archived = 0;
	$pics_archival_in_progress = false;
	$contest_ready_for_data_archive = true;
	while ($row = mysqli_fetch_array($query)) {
		$section = $row['section'];
		$section_list[$section] = $row;

		$lock_file = "ajax/" . $row['stub'] . ".lck";
		if (isset($_REQUEST['reset'])) {
			unlink($lock_file);
		}
		else if (file_exists($lock_file)) {
			$pics_archival_in_progress = true;
			$section_being_archived = $section;
			$stub_being_archived = $row['stub'];
			$num_pics_being_archived = $row['num_pics'];
		}

		$ar_folder = "../salons/$admin_yearmonth/upload/$section/ar";
		if ( is_dir($ar_folder) )
			$section_list[$section]['pics_archived'] = sizeof(scandir($ar_folder)) - 2;	// Number of files excluding . & ..
		else
			$section_list[$section]['pics_archived'] = 0;

		$contest_ready_for_data_archive = ( $section_list[$section]['pics_archived'] == $section_list[$section]['num_pics'] ? $contest_ready_for_data_archive : false );
	}

?>
<!DOCTYPE html>
<html>
<head>
    <!-- Page title -->
	<title>Youth Photographic Society | Archiving</title>

	<?php include "inc/header.php"; ?>

</head>
<body class="fixed-navbar fixed-sidebar">

<!-- Simple splash screen-->
<div class="splash">
	<div class="color-line"></div>
	<div class="splash-title">
		<h1>   YPS ADMIN PANEL  </h1>
		<p>Please Wait. </p>
		<div class="spinner">
			<div class="rect1"></div>
			<div class="rect2"></div>
			<div class="rect3"></div>
			<div class="rect4"></div>
			<div class="rect5"></div>
		</div>
	</div>
</div>

<?php
include "inc/master_topbar.php";
include "inc/master_sidebar.php";
?>

<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-lg-12 text-center m-t-md">
                <h2>
                    Archive <?php echo $admin_contest_name;?>
                </h2>
            </div>
        </div>

		<!-- Status of Archival of Pictures -->
		<?php
			if (file_exists("../generated/$admin_yearmonth")) {
		?>
		<div class="row generated-files-section" style="margin-bottom: 15px;">
			<div class="col-sm-3"></div>
			<div class="col-sm-6 text-center lead">Remove Generated Files</div>
            <div class="col-sm-3"></div>
		</div>
		<div class="row generated-files-section">
			<div class="col-sm-3"></div>
			<div class="col-sm-3">
				<p><b>Remove Generated Files</b></p>
				<p><small>
					Generated files include Salon Data, Catalog Data, Ribbon Holders, Tent Cards,
					Certificates, Slideshow files etc.
				</small></p>
			</div>
			<div class="col-sm-1"></div>
			<div class="col-sm-1 text-center" >
				<button class="btn btn-info" id="remove-generated-files" >Run</button>
			</div>
			<div class="col-sm-4"></div>
		</div>
		<?php
			}
		?>

		<!-- Archiving Pictures -->
		<div class="row" style="margin-bottom: 15px;">
			<div class="col-sm-3"></div>
			<div class="col-sm-6 text-center lead">Archiving Pictures</div>
            <div class="col-sm-3"></div>
		</div>
		<?php
			foreach( $section_list as $section => $row ) {
				$percent = round($row['pics_archived'] / $row['num_pics'] * 100, 0);
		?>
		<div class="row" style="margin-bottom: 15px;">
			<div class="col-sm-3"></div>
			<div class="col-sm-3">
				<b><?= "Archiving pictures in " . $section; ?></b><br>
				<span class="text-danger small" id="err-<?= $row['stub'];?>"></span>
			</div>
			<div class="col-sm-1" id="pc-<?= $row['stub'];?>"><?= $percent . "%";?></div>
			<div class="col-sm-1 text-center" >
				<button class="btn btn-info run-pic-archive"
						data-section="<?= $section; ?>" data-stub="<?= $row['stub']; ?>"
						data-num-pics="<?= $row['num_pics']; ?>"
						<?= $pics_archival_in_progress ? "disabled" : ""; ?> >Run</button>
			</div>
            <div class="col-sm-4"></div>
		</div>
		<?php
			}
		?>
		<div class="row">
			<div class="col-sm-3"></div>
			<div class="col-sm-5"><hr></div>
            <div class="col-sm-4"></div>
		</div>

		<!-- Status of Archival of Data -->
		<div class="row">
			<div class="col-sm-3"></div>
			<div class="col-sm-3 lead">
				<span style="color:red;"><b>Export Data before Archiving Data</b></span>
			</div>
			<div class="col-sm-1"></div>
			<div class="col-sm-1" ></div>
            <div class="col-sm-4"></div>
		</div>
		<div class="row">
			<div class="col-sm-3"></div>
			<div class="col-sm-3">
				<b>Archiving Data</b><br>
				<span class="text-danger small" id="err-data"></span>
			</div>
			<div class="col-sm-1" id="pc-data"><?= ($contest_archived) ? "100%" : "0%"; ?></div>
			<div class="col-sm-1 text-center" >
				<button class="btn btn-info" id="run-data-archive" <?= $contest_ready_for_data_archive ? "" : "disabled"; ?> >Run</button>
			</div>
            <div class="col-sm-4"></div>
		</div>
		<div class="row">
			<div class="col-sm-3"></div>
			<div class="col-sm-3">
				<a href="archive_contest.php?reset"><span class="text-info">Reset</span></a>
			</div>
			<div class="col-sm-1"></div>
			<div class="col-sm-1"></div>
            <div class="col-sm-4"></div>
		</div>
		<hr>
    </div>
	<?php include "inc/profile_modal.php";?>

</div>

<?php
include("inc/footer.php");
?>

<!-- Vendor scripts -->
<script src="plugin/jquery-flot/jquery.flot.js"></script>
<script src="plugin/jquery-flot/jquery.flot.resize.js"></script>
<script src="plugin/jquery-flot/jquery.flot.pie.js"></script>
<script src="plugin/flot.curvedlines/curvedLines.js"></script>
<script src="plugin/jquery.flot.spline/index.js"></script>
<script src="plugin/peity/jquery.peity.min.js"></script>
<script src="plugin/swal/js/sweetalert.min.js"></script>

<!-- Run Picture Archival -->
<script>

	$(".run-pic-archive").click(function (){

		let yearmonth = "<?= $admin_yearmonth;?>";
		let section = $(this).data("section");
		let stub = $(this).data("stub");
		let num_pics = $(this).data("num-pics");
		$("#pc-" + stub).html("Running");
		$(this).attr("disabled", "true");
		button = $(this);

		$.ajax({
				url: "ajax/archive_pics.php",
				type: "POST",
				data: { yearmonth, section, stub },
				cache: false,
				//contentType: false,
				xhr : function() {
						// Add a hook to monitor upload progress
						let xhr = $.ajaxSettings.xhr();

						xhr.onprogress = function(e) {
							let files_archived = e.target.responseText.length;
							let pct_complete = (files_archived / num_pics * 100).toFixed(0);
							$("#pc-" + stub).html(pct_complete + "%");
						}

						// return xhr with custom hook to show progress
						return xhr;
				},
				success: function(response) {
				    console.log(response);
					if (response.includes("|")) {
						// Error Message Following "|" returned
						let errmsg = response.match(/\|.*$/)[0].substr(1);
						$("#pc-" + stub).html("Error");
						$("#err-" + stub).html(errmsg);
					}
					else {
						if (response.length < num_pics || response.includes("E")) {
							$("#pc-" + stub).html("<span class='text-danger'>Errors</span>");
							$("#err-" + stub).html("Not all files could be archived. Check copy_errors.txt file");
						}
						else {
							$("#pc-" + stub).html("100%");
							$("#err-" + stub).html("");
						}
					}
					button.removeAttr("disabled");
				},
				error : function(xHr, status, error) {
					$("#pc-" + stub).html("<span class='text-danger'>Failed</span>");
					$("#err-" + stub).html(status + "-" + error);
					button.removeAttr("disabled");
				}
		});

	});

</script>

<!-- Remove Generated Files -->
<script>

	$("#remove-generated-files").click(function (){

		let yearmonth = "<?= $admin_yearmonth;?>";
		$(this).attr("disabled", "true");
		button = $(this);

		$.ajax({
				url: "ajax/remove_generated.php",
				type: "POST",
				data: { yearmonth },
				cache: false,
				success: function(response) {
					let data = JSON.parse(response);
					if (data.success) {
						swal("Messages", "Generated Files Removed", "info");
						$(".generated-files-section").hide();
					}
					button.removeAttr("disabled");
				},
				error : function(xHr, status, error) {
					$("#pc-" + stub).html("<span class='text-danger'>Failed</span>");
					$("#err-" + stub).html(status + "-" + error);
					button.removeAttr("disabled");
				}
		});

	});

</script>

<!-- Move selective data to archive tables -->
<script>

$("#run-data-archive").click(function(){
	let yearmonth = "<?= $admin_yearmonth;?>";
	$.post(
		"ajax/archive_data.php",
		{ yearmonth },
		function (response) {
			let data = JSON.parse(response);
			if (data.success) {
				$("#pc-data").html("100%");
				$("#err-data").html("");
			}
			else {
				$("#pc-data").html("<span class='text-danger'>Failed</span>");
				$("#err-data").html(data.msg);
			}
		}
	)
});

</script>


</body>

</html>

<?php
}
else
{
	$_SESSION['err_msg'] = "Use ID with require permission not found !";
	header("Location: index.php");
	printf("<script>location.href='index.php'</script>");
}

?>
