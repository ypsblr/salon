<?php
include("inc/session.php");
include("inc/connect.php");
include("inc/lib.php");

// Since there are many columns with Null value, here is a safe way to show null
function safe($str, $default = "") {
	if (is_null($str))
		return $default;
	else
		return $str;
}

function email_filter_from_data ($list) {
	$email_list = [];
	foreach ($list as $item) {
		list ($email, $items, $mailing_date, $tracking_no, $notes) = $item;
		$email_list[] = "'" . $email . "'";
	}
	return implode(",", $email_list);
}

function isJson($string) {
   json_decode($string);
   return json_last_error() === JSON_ERROR_NONE;
}

if ( isset($_SESSION['admin_id']) ) {
	// Set up user and contest information
	include ("inc/load_common_data.php");
    toconsole(1);
    
	// Initialize Empty Salon
	$salon = array (
			"yearmonth" => "", "contest_name" => "", "is_international" => 0, "archived" => 0, "catalog_ready" => 0, "certificates_ready" => 0,
			"exhibition_start_date" => NULL, "catalog_release_date" => NULL, "catalog" => "", "catalog_download" => "",
			"catalog_order_last_date" => NULL, "catalog_price_in_inr" => "", "catalog_price_in_usd" => "",
	);
	$yearmonth = 0;
	$is_catalog_ready = false;
	$is_certificates_ready = false;
	$is_international = false;
    toconsole(2);
    
	// Fill $salon, if yearmonth passed
	if (isset($_REQUEST['yearmonth'])) {
	    toconsole(3);
		$yearmonth = $_REQUEST['yearmonth'];
		$sql = "SELECT * FROM contest WHERE yearmonth = '$yearmonth' ";
		$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		if (mysqli_num_rows($query) == 0)
			$_SESSION['err_msg'] = "Salon for " . $yearmonth . " not found !";
		else {
		    toconsole("4.else");
			$row = mysqli_fetch_array($query, MYSQLI_ASSOC);
			foreach ($salon as $field => $value) {
				if (isset($row[$field])) {			// Handle NULLs if exhibition has not yet been setup
					if (is_null($row[$field])) {
						if (is_string($salon[$field]))
							$salon[$field] = "";
						else if (is_numeric($salon[$field]))
							$salon[$field] = 0;
						else if (substr($field, -4) == "date")
							$salon[$field] = NULL;
					}
					else {
						$salon[$field] = $row[$field];
					}
				}
			}
		}
		toconsole(5);
		$is_catalog_ready = ($salon['catalog_ready'] == '1' );
		$is_certificates_ready = ($salon['certificates_ready'] == '1' );
		$is_international = ($salon['is_international'] == '1' );
		// Create Price Lists INR
		$there_is_inr_price = false;
		$price_list_inr[0] = (object) array("model" => $salon['contest_name'] . " Catalog",
							   "price" => 0, "postage" => 0, "total" => 0 );
		if ($salon['catalog_price_in_inr'] != "") {
		    toconsole(6);
			$price_list_inr = json_decode($salon['catalog_price_in_inr'], false);
			if (json_last_error() == JSON_ERROR_NONE) {
			    toconsole("7.if");
				$there_is_inr_price = true;
				// Format numbers
				toconsole(sizeof($price_list_inr));
				for ($idx = 0; $idx < sizeof($price_list_inr); ++$idx) {
				    // toconsole($price_list_inr[$idx]->price);
				    
				    $a = $price_list_inr[$idx]->postage[0];
				    toconsole("if.1");
				    $b = $price_list_inr[$idx]->postage[1];
				    toconsole("if.2");
				    // var_dump($a);
				    toconsole("if.3");
				    // var_dump($b);
				    toconsole("if.4");

				    // 	$price_list_inr[$idx]->total = $price_list_inr[$idx]->price + $price_list_inr[$idx]->postage;
					
				// 	var_dump($price_list_inr);

                    if (isset($price_list_inr[$idx]->total)) {
    					if (isset($price_list_inr[$idx]->price) && $price_list_inr[$idx]->price !== null &&
                                isset($price_list_inr[$idx]->postage) && $price_list_inr[$idx]->postage !== null) {
        					$price_list_inr[$idx]->total = $price_list_inr[$idx]->price + $price_list_inr[$idx]->postage;
                        }
                        else {
                            $price_list_inr[$idx]->total = 0;
                        }
                    }
                    else {
                        if (!is_array($price_list_inr[$idx]->postage)) {
                            toconsole("not array");
                            $price_list_inr[$idx]->total = $price_list_inr[$idx]->price + $price_list_inr[$idx]->postage;
                        }
                        else {
                            toconsole("is array");
                            $price_list_inr[$idx]->postage = 0;
                            $price_list_inr[$idx]->total = $price_list_inr[$idx]->price + $price_list_inr[$idx]->postage;
                        }
                    }
				    toconsole("for...");
				}
				toconsole(7.1);
			}
			else {
			    toconsole("7.else");
				// Try to map simple price model
				 list($price, $postage) = explode("|", $salon['catalog_price_in_inr']);
				 $price = (isset($price) ? $price : 0);
				 $postage = (isset($postage) ? $postage : 0);
				 if ($price != 0) {
					 $price_list_inr[0] = (object) array("model" => $salon['contest_name'] . " Catalog",
					 						"price" => $price, "postage" => $postage, "total" => $price + $postage );
					 $there_is_inr_price = true;
				 }
			}
		}
		toconsole(7.2);
		// Create Price Lists USD
		$there_is_usd_price = false;
		$price_list_usd[0] = (object) array("model" => $salon['contest_name'] . " Catalog",
							   "price" => 0, "postage" => 0, "total" => 0 );
		toconsole(7.3);
		if ($salon['catalog_price_in_usd'] != "") {
		    toconsole(8);
			$price_list_usd = json_decode($salon['catalog_price_in_usd'], false);
			if (json_last_error() == JSON_ERROR_NONE) {
			    toconsole("9.if");
				$there_is_usd_price = true;
				// Format numbers
				for ($idx = 0; $idx < sizeof($price_list_usd); ++$idx) {
					$price_list_usd[$idx]->total = $price_list_usd[$idx]->price + $price_list_usd[$idx]->postage;
				}
			}
			else {
			    toconsole("9.else");
				// Try to map simple price model
				 list($price, $postage) = explode("|", $salon['catalog_price_in_usd']);
				 $price = (isset($price) ? $price : 0);
				 $postage = (isset($postage) ? $postage : 0);
				 if ($price != 0) {
					 $price_list_usd[0] = (object) array("model" => $salon['contest_name'] . " Catalog",
					 						"price" => $price, "postage" => $postage, "total" => $price + $postage );
					 $there_is_usd_price = true;
				 }
			}
		}
		toconsole("inb 9 and 10");
		// Determine Catalog Names
		$base_year = 2020;
		$base_ais_no = 38;
		$base_is_no = 11;
		$current_ais_no = $base_ais_no + (substr($yearmonth, 0, 4) - $base_year);
		$current_is_no = $base_is_no + (substr($yearmonth, 0, 4) - $base_year);
		$current_file_base = "YPS_" . substr($yearmonth, 0, 4) . "_" . ($is_international ? "IS" : "AIS") . "_" . sprintf("%03d", ($is_international ? $current_is_no : $current_ais_no));
		$catalog_name = $current_file_base . ".pdf";
		$catalog_download_name = $current_file_base . "_D.pdf";
		$view_catalog_exists = file_exists("../catalog/" . $catalog_name);
		$download_catalog_exists = file_exists("../catalog/" . $catalog_download_name);
		$catalog_img = $current_file_base . ".jpg";
		$salon['catalog_img'] = "/catalog/img/" . $catalog_img;
		toconsole(10);
	}

?>

<!DOCTYPE html>
<html>

<head>
    <!-- Page title -->
	<title>Youth Photographic Society | Salon Management</title>

	<?php include "inc/header.php"; ?>

    <link rel="stylesheet" href="plugin/datatable/css/dataTables.bootstrap.min.css" />

	<style>
		div.filter-button {
			display:inline-block;
			margin-right: 15px;
		}
	</style>
</head>

<body class="fixed-navbar fixed-sidebar">

	<!-- Simple splash screen-->
	<div class="splash">
		<div class="color-line"></div>
		<div class="splash-title">
			<h1>    YPS SALON MANAGEMENT PANEL  </h1>
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

	<!-- Header -->
<?php
	include "inc/master_topbar.php";
	include "inc/master_sidebar.php";
?>

	<!-- Main Wrapper -->
	<div id="wrapper">
		<div class="normalheader transition animated fadeIn">
			<div class="hpanel">
				<div class="panel-body">
					<a class="small-header-action" href="#">
						<div class="clip-header">
							<i class="fa fa-arrow-up"></i>
						</div>
					</a>
					<h3 class="font-light m-b-xs">
						<?= $yearmonth == 0 ? "Select a Salon" : "Exhibirion Details for " . $salon['contest_name'];?>
					</h3>
					<br>
					<form role="form" method="post" id="select-salon-form" name="select-salon-form" action="release_catalog.php" enctype="multipart/form-data" >
						<div class="row form-group">
							<div class="col-sm-8">
								<label for="yearmonth">Select Salon</label>
								<div class="input-group">
									<select class="form-control" name="yearmonth" id="select-yearmonth" value="<?= $yearmonth;?>">
									<?php
										$sql = "SELECT yearmonth, contest_name FROM contest ORDER BY yearmonth DESC ";
										$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
										while ($row = mysqli_fetch_array($query)) {
									?>
										<option value="<?= $row['yearmonth'];?>" <?= ($row['yearmonth'] == $yearmonth) ? "selected" : "";?>><?= $row['contest_name'];?></option>
									<?php
										}
									?>
									</select>
									<span class="input-group-btn">
										<button type="submit" class="btn btn-info pull-right" name="edit-contest-button" id="edit-contest-button" ><i class="fa fa-edit"></i> EDIT </a>
									</span>
								</div>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>

		<div class="content">
			<?php
				if ($yearmonth != 0) {
			?>
			<form role="form" method="post" id="edit_contest_form" name="edit_contest_form" action="#" enctype="multipart/form-data" >
				<!-- Hidden non-edited fields -->
				<input type="hidden" name="yearmonth" id="yearmonth" value="<?= $yearmonth;?>" >
				<input type="hidden" name="exhibition_start_date" id="exhibition_start_date" value="<?= $salon['exhibition_start_date'];?>" >
				<input type="hidden" name="catalog_name" id="catalog_name" value="<?= $catalog_name;?>" >
				<input type="hidden" name="catalog_download_name" id="catalog_download_name" value="<?= $catalog_download_name;?>" >
				<input type="hidden" name="catalog_img" id="catalog_img" value="<?= $catalog_img;?>" >
				<input type="hidden" id="is_international" value="<?= $salon['is_international'];?>" >

				<!-- Edited Fields -->
				<!-- Exhibition dates -->
				<div class="row form-group">
					<div class="col-sm-3">
						<label for="catalog_release_date">Catalog Release Date</label>
						<input type="date" name="catalog_release_date" class="form-control" id="catalog_release_date" value="<?= $salon['catalog_release_date'];?>" >
					</div>
					<div class="col-sm-3">
						<label>Catalog Ready ?</label>
						<div class="input-group">
							<span class="input-group-addon">
								<input type="checkbox" name="catalog_ready" id="catalog_ready" value="1" <?= $is_catalog_ready ? "checked" : "";?> >
							</span>
							<input type="text" class="form-control" readonly value="Catalog Ready" >
						</div>
					</div>
					<div class="col-sm-3">
						<label>Certificates Ready ?</label>
						<div class="input-group">
							<span class="input-group-addon">
								<input type="checkbox" name="certificates_ready" id="certificates_ready" value="1" <?= $is_certificates_ready ? "checked" : "";?> >
							</span>
							<input type="text" class="form-control" readonly value="Certificates Ready" >
						</div>
					</div>
					<div class="col-sm-3">
						<label for="catalog_order_last_date">Catalog Order Last Date</label>
						<input type="date" name="catalog_order_last_date" class="form-control" id="catalog_order_last_date" value="<?= $salon['catalog_order_last_date'];?>" >
					</div>
				</div>
				<div class="row form-group">
					<div class="col-sm-3">
						<label for="catalog_img_upload">Catalog Front Page Image</label>
						<p><img src="<?= $salon['catalog_img'];?>" class="text-center" id="img_catalog" style="max-width: 120px" ></p>
						<input type="file" name="catalog_img_upload" class="form-control img-file" id="catalog_img_upload" data-img="img_catalog" >
					</div>
					<div class="col-sm-3">
						<label for="catalog">Upload Viewable Catalog</label>
						<?php
							if ($salon['catalog'] != "") {
						?>
						<p class='text-info'>Existing upload <?= $salon['catalog'];?> </p>
						<?php
							}
						?>
						<input type="file" name="catalog" class="form-control catalog-file" id="catalog" >
						<br>
						<span class="text-danger" id="view-catalog-status"><?= $view_catalog_exists ? $catalog_name . " exists." : "Upload catalog file";?></span>
						<br>
						<a class="btn btn-info pull-right upload-button" data-catalog-type="catalog" data-catalog-name="catalog_name" ><i class="fa fa-upload"></i> Upload</a>
					</div>
					<div class="col-sm-3">
						<label for="catalog_download">Upload Downloadable Catalog</label>
						<?php
							if ($salon['catalog_download'] != "") {
						?>
						<p class='text-info'>Existing upload <?= $salon['catalog_download'];?> </p>
						<?php
							}
						?>
						<input type="file" name="catalog_download" class="form-control catalog-file" id="catalog_download" >
						<br>
						<span class="text-danger" id="download-catalog-status"><?= $download_catalog_exists ? $catalog_download_name . " exists." : "Upload downloadable catalog file";?></span>
						<br>
						<a class="btn btn-info pull-right upload-button" data-catalog-type="catalog_download" data-catalog-name="catalog_download_name" ><i class="fa fa-upload"></i> Upload</a>
					</div>
				</div>

				<!-- Catalog Price in INR -->
				<h3 class="text-color">Catalog in Indian Rupees</h3>
				<div class="row form-group">
					<div class="col-sm-4">
						<label for="catalog_model_inr">Catalog Model</label>
					</div>
					<div class="col-sm-2">
						<label for="catalog_price_inr">Price</label>
					</div>
					<div class="col-sm-2">
						<label for="catalog_postage_inr">Postage</label>
					</div>
					<div class="col-sm-2">
						<label for="catalog_total_inr">Total Price</label>
					</div>
					<div class="col-sm-1">
						<label>Add</label>
					</div>
					<div class="col-sm-1">
						<label>Del</label>
					</div>
				</div>
				<?php
					for ($idx = 0; $idx < sizeof($price_list_inr); ++$idx) {
				?>
				<div class="row form-group" id="price_inr_<?= $idx;?>">
					<div class="col-sm-4">
						<input type="text" class="form-control" name="catalog_model_inr[]" id="catalog_model_inr_<?= $idx;?>"
								value="<?= $there_is_inr_price ? $price_list_inr[$idx]->model : $salon['contest_name'] . ' Catalog';?>" >
					</div>
					<div class="col-sm-2">
						<input type="number" class="form-control price" name="catalog_price_inr[]" id="catalog_price_inr_<?= $idx;?>"
								value="<?= $there_is_inr_price ? $price_list_inr[$idx]->price : "0";?>" data-idx="<?= $idx;?>" >
					</div>
					<div class="col-sm-2">
						<input type="number" class="form-control price" name="catalog_postage_inr[]" id="catalog_postage_inr_<?= $idx;?>"
								value="<?= $there_is_inr_price ? $price_list_inr[$idx]->postage : "0";?>" data-idx="<?= $idx;?>" >
					</div>
					<div class="col-sm-2">
						<input type="number" readonly class="form-control" id="catalog_total_inr_<?= $idx;?>"
								value="<?= $there_is_inr_price ? $price_list_inr[$idx]->total : "0";?>" >
					</div>
					<div class="col-sm-1">
						<a class="add_inr btn btn-info" data-idx="<?= $idx;?>" onclick="add_inr(<?= $idx;?>)" ><i class="fa fa-plus"></i> Add</a>
					</div>
					<?php
						if (sizeof($price_list_inr) > 1) {
					?>
					<div class="col-sm-1">
						<a class="del_inr btn btn-danger" data-idx="<?= $idx;?>" onclick="del_inr(<?= $idx;?>)" ><i class="fa fa-trash"></i> Del</a>
					</div>
					<?php
						}
					?>
				</div>
				<?php
					}
				?>
				<div id="end_inr"></div>

				<!-- Catalog Price in INR -->
				<?php
					if ($is_international) {
				?>
				<h3 class="text-color">Catalog in US Dollars</h3>
				<div class="row form-group">
					<div class="col-sm-4">
						<label for="catalog_model_usd">Catalog Model</label>
					</div>
					<div class="col-sm-2">
						<label for="catalog_price_usd">Price</label>
					</div>
					<div class="col-sm-2">
						<label for="catalog_postage_usd">Postage</label>
					</div>
					<div class="col-sm-2">
						<label for="catalog_total_usd">Total Price</label>
					</div>
					<div class="col-sm-1">
						<label>Add</label>
					</div>
					<div class="col-sm-1">
						<label>Del</label>
					</div>
				</div>
				<?php
						for ($idx = 0; $idx < sizeof($price_list_usd); ++$idx) {
				?>
				<div class="row form-group" id="price_usd_<?= $idx;?>">
					<div class="col-sm-4">
						<input type="text" class="form-control" name="catalog_model_usd[]" id="catalog_model_usd_<?=$idx;?>"
									value="<?= $there_is_usd_price ? $price_list_usd[$idx]->model : $salon['contest_name'] . ' Catalog';?>" >
					</div>
					<div class="col-sm-2">
						<input type="number" class="form-control price" name="catalog_price_usd[]" id="catalog_price_usd_<?=$idx;?>"
									value="<?= $there_is_usd_price ? $price_list_usd[$idx]->price : "0";?>" data-idx="<?= $idx;?>" >
					</div>
					<div class="col-sm-2">
						<input type="number" class="form-control price" name="catalog_postage_usd[]" id="catalog_postage_usd_<?=$idx;?>"
									value="<?= $there_is_usd_price ? $price_list_usd[$idx]->postage : "0";?>" data-idx="<?= $idx;?>" >
					</div>
					<div class="col-sm-2">
						<input type="number" readonly class="form-control" id="catalog_total_usd_<?=$idx;?>"
									value="<?= $there_is_usd_price ? $price_list_usd[$idx]->total : "0";?>" >
					</div>
					<div class="col-sm-1">
						<a class="add_usd btn btn-info" data-idx="<?= $idx;?>" onclick="add_usd(<?= $idx;?>)" ><i class="fa fa-plus"></i> Add</a>
					</div>
					<?php
						if (sizeof($price_list_usd) > 1) {
					?>
					<div class="col-sm-1">
						<a class="del_usd btn btn-danger" data-idx="<?= $idx;?>" onclick="del_usd(<?= $idx;?>)" ><i class="fa fa-trash"></i> Del</a>
					</div>
					<?php
						}
					?>
				</div>
				<?php
						}
				?>
				<div id="end_usd"></div>
				<?php
					}
				?>

				<!-- share image templates -->
				<h4 class="text-info">Files Required</h4>
				<div class="row">
					<?php
						$catalog_view_thumbnail_name = "catalog_mail_view.png";
						$catalog_view_thumbnail = "/salons/$yearmonth/img/$catalog_view_thumbnail_name";
						if ( ! file_exists(".." . $catalog_view_thumbnail))
							$catalog_view_thumbnail = "/img/preview.png";
						$catalog_download_thumbnail_name = "catalog_mail_download.png";
						$catalog_download_thumbnail = "/salons/$yearmonth/img/$catalog_download_thumbnail_name";
						if ( ! file_exists(".." . $catalog_download_thumbnail))
							$catalog_download_thumbnail = "/img/preview.png";
						$catalog_order_thumbnail_name = "catalog_mail_order.png";
						$catalog_order_thumbnail = "/salons/$yearmonth/img/$catalog_order_thumbnail_name";
						if ( ! file_exists(".." . $catalog_order_thumbnail))
							$catalog_order_thumbnail = "/img/preview.png";
						$download_certificates_thumbnail_name = "catalog_mail_certificates.png";
						$download_certificates_thumbnail = "/salons/$yearmonth/img/$download_certificates_thumbnail_name";
						if ( ! file_exists(".." . $download_certificates_thumbnail))
							$download_certificates_thumbnail = "/img/preview.png";
					?>
					<!-- Catalog View Thumbnail -->
					<div class="col-sm-4" style="padding: 4px; border : 1px solid #aaa; border-radius : 8px;">
						<div class="row">
							<div class="col-sm-4 form-group">
								<img id="catalog-view-thumbnail-disp" src="<?= $catalog_view_thumbnail;?>" style="width: 100%;" >
							</div>
							<div class="col-sm-8">
								<label>Upload Catalog View Thumbnail</label>
								<input type="file" name="catalog_view_thumbnail" id="catalog-view-thumbnail"
										class="form-control img-file" data-img="catalog-view-thumbnail-disp" ><br>
								<button id="upload-catalog-view-thumbnail" class="btn btn-info pull-right upload-graphics"
								 		data-disp="catalog-view-thumbnail-disp"
										data-input="catalog-view-thumbnail"
										data-file="<?= $catalog_view_thumbnail_name;?>" >
									<i class="fa fa-upload"></i> Upload
								</button>
							</div>
						</div>
					</div>
					<!-- Catalog Download Thumbnail -->
					<div class="col-sm-4" style="padding: 4px; border : 1px solid #aaa; border-radius : 8px;">
						<div class="row">
							<div class="col-sm-4 form-group">
								<img id="catalog-download-thumbnail-disp" src="<?= $catalog_download_thumbnail;?>" style="width: 100%;" >
							</div>
							<div class="col-sm-8">
								<label>Upload Catalog Download Thumbnail</label>
								<input type="file" name="catalog_download_thumbnail" id="catalog-download-thumbnail"
										class="form-control img-file" data-img="catalog-download-thumbnail-disp" ><br>
								<button id="upload-catalog-download-thumbnail" class="btn btn-info pull-right upload-graphics"
										data-disp="catalog-download-thumbnail-disp"
										data-input="catalog-download-thumbnail"
										data-file="<?= $catalog_download_thumbnail_name;?>" >
									<i class="fa fa-upload"></i> Upload
								</button>
							</div>
						</div>
					</div>
					<!-- Catalog Order Thumbnail -->
					<div class="col-sm-4" style="padding: 4px; border : 1px solid #aaa; border-radius : 8px;">
						<div class="row">
							<div class="col-sm-4 form-group">
								<img id="catalog-order-thumbnail-disp" src="<?= $catalog_order_thumbnail;?>" style="width: 100%;" >
							</div>
							<div class="col-sm-8">
								<label>Upload Catalog Order Thumbnail</label>
								<input type="file" name="catalog_order_thumbnail" id="catalog-order-thumbnail"
										class="form-control img-file" data-img="catalog-order-thumbnail-disp" ><br>
								<button id="upload-catalog-order-thumbnail" class="btn btn-info pull-right upload-graphics"
										data-disp="catalog-order-thumbnail-disp"
										data-input="catalog-order-thumbnail"
										data-file="<?= $catalog_order_thumbnail_name;?>" >
									<i class="fa fa-upload"></i> Upload
								</button>
							</div>
						</div>
					</div>
					<div class="clearfix"></div>
					<!-- Download Certificates Thumbnail -->
					<div class="col-sm-4" style="padding: 4px; border : 1px solid #aaa; border-radius : 8px;">
						<div class="row">
							<div class="col-sm-4 form-group">
								<img id="download-certificates-thumbnail-disp" src="<?= $download_certificates_thumbnail;?>" style="width: 100%;" >
							</div>
							<div class="col-sm-8">
								<label>Download Certificates Thumbnail</label>
								<input type="file" name="download_certificates_thumbnail" id="download-certificates-thumbnail"
										class="form-control img-file" data-img="download-certificates-thumbnail-disp" ><br>
								<button id="upload-download-certificates-thumbnail" class="btn btn-info pull-right upload-graphics"
										data-disp="download-certificates-thumbnail-disp"
										data-input="download-certificates-thumbnail"
										data-file="<?= $download_certificates_thumbnail_name;?>" >
									<i class="fa fa-upload"></i> Upload
								</button>
							</div>
						</div>
					</div>
				</div>

				<!-- Update -->
				<br><br>
				<div class="row form-group">
					<div class="col-sm-6">
						<input class="btn btn-primary pull-right" type="submit" id="unpublish_catalog" name="unpublish_catalog" value="Unpublish Catalog">
					</div>
					<div class="col-sm-3">
						<input class="btn btn-primary pull-right" type="submit" id="release_catalog" name="release_catalog" value="Release Catalog">
					</div>
				</div>
			</form>
			<?php
				}
			?>
		</div>

	</div>
<?php
      include("inc/footer.php");
?>

<!-- Form Validation -->
<script src="plugin/validate/js/jquery.validate.min.js"></script>

<!-- Custom Validation Functions -->
<script src="custom/js/validate.js"></script>

<!-- Find JSON String in a response -->
<script>
	function json_str(resp) {
		let start = resp.indexOf("{");
		let end = resp.indexOf("}");
		if (start == -1 || end == -1 || end <= start)
			return "";

		return resp.substr(start, end - start + 1);
	}
</script>

<!-- Action Handlers -->
<script>
	// Global variables to save judging venue details

	$(document).ready(function(){
		// Hide Form till a salon is loaded
		if($("#yearmonth").val() == 0)
			$(".content").hide();

	});
</script>

<!-- Custom Validation Functions -->
<script>
jQuery.validator.addMethod(
	"yearmonth",
	function(value, element, param) {
		let year = value.substr(0, 4);
		let month = value.substr(4);
		if (year >= "1980" && year <= "2099" && month >= "01" && month <= "12")
			return true;
		else
			return this.optional(element);
	},
	"Must have valid value in YYYYMM format"
);

</script>

<!-- Load Picture into view when selected -->
<script>
	$(document).ready(function(){
		// Load picture into view
		$(".img-file").on("change", function(){
			let input = $(this).get(0);
			let target = $(this).attr("data-img");
			if (input.files && input.files.length > 0 && input.files[0] != "") {
				// Phase 1 Check Size & Dimensions
				var file_size = input.files[0].size;
				var reader = new FileReader();
				// Handler for file read completion
				reader.onload = function (e) {
					$("#" + target).attr("src", e.target.result);
				}
				// Handler for File Read Error
				reader.onerror = function(e) {
					alert("Unable to open selected picture");
				}

				// Perform File Read
				reader.readAsDataURL(input.files[0]);

			}
		});
	});
</script>

<script>
	$(".upload-graphics").click(function(e){
		e.preventDefault();
		if ($("#" + $(this).attr("data-input")).val() == "") {
			swal("Select an Image !", "Please select a PNG image to be used as Quick Link Thumbnail.", "warning");
		}
		else {
			let yearmonth = "<?= $yearmonth;?>";
			let disp = $(this).attr("data-disp");
			let input = $(this).attr("data-input");
			let file_name = $(this).attr("data-file");
			let file = $("#" + input)[0].files[0];
			let stub = $("#" + input).attr("name");
			let formData = new FormData();
			formData.append("yearmonth", yearmonth);
			formData.append("file_name", file_name);
			formData.append(stub, file);

			$('#loader_img').show();
			$.ajax({
					url: "ajax/upload_salon_img.php",
					type: "POST",
					data: formData,
					cache: false,
					processData: false,
					contentType: false,
					success: function(response) {
						$('#loader_img').hide();
						response = JSON.parse(response);
						if(response.success){
							$("#" + disp).attr("src", "/salons/" + yearmonth + "/img/" + file_name);
							$("#" + input).val(null);
							swal({
									title: "Image Saved",
									text: "Image has been uploaded and saved.",
									icon: "success",
									confirmButtonClass: 'btn-success',
									confirmButtonText: 'Great'
							});
						}
						else{
							swal({
									title: "Upload Failed",
									text: "Uploaded image could not be saved: " + response.msg,
									icon: "warning",
									confirmButtonClass: 'btn-warning',
									confirmButtonText: 'OK'
							});
						}
					},
					error: function(jqXHR, textStatus, errorThrown) {
						$('#loader_img').hide();
						swal("Server Error", "Something went wrong at the server. If this repeats please report the problem to YPS", "error");
					}
			});
		}
	});		// upload image click
</script>

<!-- Handle Computing of total -->
<script>
	function update_total(idx) {
		$("#catalog_total_inr_" + idx).val( Number($("#catalog_price_inr_" + idx).val()) + Number($("#catalog_postage_inr_" + idx).val()) );
		if ($("#is_international").val() == "1")
			$("#catalog_total_usd_" + idx).val( Number($("#catalog_price_usd_" + idx).val()) + Number($("#catalog_postage_usd_" + idx).val()) );
	}
	$(document).ready(function() {
		$("input.price").on("change", function(){
			let idx = $(this).attr("data-idx");
			update_total(idx);
		});
	});
</script>

<!-- Handle Add / Del Price -->
<script>
	function del_inr(idx) {
		$("#price_inr_" + idx).remove();
	}

	function add_inr(idx) {

		++idx;

		let html = '<div class="row form-group" id="price_inr_' + idx + '" >';
		html += '  <div class="col-sm-4">';
		html += '    <input type="text" class="form-control" name="catalog_model_inr[]" id="catalog_model_inr_' + idx + '" value="" >';
		html +=	'  </div>';
		html += '  <div class="col-sm-2">';
		html += '    <input type="number" class="form-control price" name="catalog_price_inr[]" id="catalog_price_inr_' + idx + '" value="0" data-idx="' + idx + '" >';
		html += '  </div>';
		html += '  <div class="col-sm-2">';
		html += '    <input type="number" class="form-control price" name="catalog_postage_inr[]" id="catalog_postage_inr_' + idx + '" value="0" data-idx="' + idx + '" >';
		html += '  </div>';
		html += '  <div class="col-sm-2">';
		html += '    <input type="number" readonly class="form-control" id="catalog_total_inr_' + idx + '" value="0" >';
		html += '  </div>';
		html += '  <div class="col-sm-1">';
		html += '    <a class="add_inr btn btn-info" data-idx="' + idx + '" onclick="add_inr(' + idx + ')" ><i class="fa fa-plus"></i> Add</a>';
		html += '  </div>';
		html += '  <div class="col-sm-1">';
		html += '    <a class="del_inr btn btn-danger" data-idx="' + idx + '" onclick="del_inr(' + idx + ')" ><i class="fa fa-trash"></i> Del</a>';
		html += '  </div>';
		html += '</div>';

		$(html).insertBefore("#end_inr");

		$("input.price").on("change", function(){
			let idx = $(this).attr("data-idx");
			update_total(idx);
		});
	}

	function del_usd(idx) {
		$("#price_usd_" + idx).remove();
	}

	function add_usd(idx) {

		++idx;

		let html = '<div class="row form-group" id="price_usd_' + idx + '" >';
		html += '  <div class="col-sm-4">';
		html += '    <input type="text" class="form-control" name="catalog_model_usd[]" id="catalog_model_usd_' + idx + '" value="" >';
		html +=	'  </div>';
		html += '  <div class="col-sm-2">';
		html += '    <input type="number" class="form-control price" name="catalog_price_usd[]" id="catalog_price_usd_' + idx + '" value="0" data-idx="' + idx + '" >';
		html += '  </div>';
		html += '  <div class="col-sm-2">';
		html += '    <input type="number" class="form-control price" name="catalog_postage_usd[]" id="catalog_postage_usd_' + idx + '" value="0" data-idx="' + idx + '" >';
		html += '  </div>';
		html += '  <div class="col-sm-2">';
		html += '    <input type="number" readonly class="form-control" id="catalog_total_usd_' + idx + '" value="0" >';
		html += '  </div>';
		html += '  <div class="col-sm-1">';
		html += '    <a class="add_usd btn btn-info" data-idx="' + idx + '" onclick="add_usd(' + idx + ')" ><i class="fa fa-plus"></i> Add</a>';
		html += '  </div>';
		html += '  <div class="col-sm-1">';
		html += '    <a class="del_usd btn btn-danger" data-idx="' + idx + '" onclick="del_usd(' + idx + ')" ><i class="fa fa-trash"></i> Del</a>';
		html += '  </div>';
		html += '</div>';

		$(html).insertBefore("#end_usd");

		$("input.price").on("change", function(){
			let idx = $(this).attr("data-idx");
			update_total(idx);
		});
	}
</script>

<!-- Upload Catalog Files -->
<script>
	$(document).ready(function(){
		$(".upload-button").click(function(e) {
			e.preventDefault();
			let status_target = "";
			if ($(this).attr("data-catalog-type") == "catalog")
				status_target = "view-catalog-status";
			else
				status_target = "download-catalog-status";

			let yearmonth = "<?= $yearmonth;?>";
			let catalog_id = $(this).attr("data-catalog-type");
			let catalog_name_id = $(this).attr("data-catalog-name");
			let file = $("#" + catalog_id)[0].files[0];
			let stub = $("#" + catalog_name_id).val();
			let formData = new FormData();
			formData.append("yearmonth", yearmonth);
			formData.append("file_name", $("#" + catalog_name_id).val());
			formData.append(stub, file);

			$('#loader_img').show();
			$.ajax({
					url: "ajax/upload_catalog.php",
					type: "POST",
					data: formData,
					cache: false,
					processData: false,
					contentType: false,
					success: function(response) {
						$('#loader_img').hide();
						response = JSON.parse(json_str(response));
						if(response.success){
							// Clear the file input so that it does not upload again
							$("#" + catalog_id).val(null);
							$("#" + status_target).html("Catalog file uploaded.");
							swal({
									title: "Uploaded",
									text: "The Catalog has been uploaded and saved.",
									icon: "success",
									confirmButtonClass: 'btn-success',
									confirmButtonText: 'Great'
							});
						}
						else{
							swal({
									title: "Upload Failed",
									text: "Uploaded catalog could not be saved: " + response.msg,
									icon: "warning",
									confirmButtonClass: 'btn-warning',
									confirmButtonText: 'OK'
							});
						}
					},
					error: function(jqXHR, textStatus, errorThrown) {
						$('#loader_img').hide();
						swal("Server Error", "Something went wrong at the server. If this repeats please report the problem to YPS", "error");
					}
			});
		});		// upload file click
	});		// document ready
</script>


<!-- Edit Contest -->
<script>
	$(document).ready(function(){
		let vaidator = $('#edit_contest_form').validate({
			rules:{
				catalog_release_date : {
					date_min : "#exhibition_start_date",
				},
				catalog_order_last_date : {
					date_min : "#catalog_release_date",
				},
			},
			messages:{
				catalog_release_date : "Catalog Release Date cannot be before Exhibition Start Date",
				catalog_order_last_date : "Catalog Order Last Date cannot be before Catalog Release Date",
			},
			errorElement: "div",
			errorClass: "valid-error",
			showErrors: function(errorMap, errorList) {
							$("div.valid-error").remove();		// Remove all error messages to avoid duplicate messages
							this.defaultShowErrors();			// Show Errors
						},
			submitHandler: function(form) {
				// Assemble Data
				var formData = new FormData(form);

				$('#loader_img').show();
				$.ajax({
						url: "ajax/release_catalog.php",
						type: "POST",
						data: formData,
						cache: false,
						processData: false,
						contentType: false,
						success: function(response) {
							$('#loader_img').hide();
							response = JSON.parse(json_str(response));
							if(response.success){
								swal({
										title: "Details Saved",
										text: "Catalog released successfully.",
										icon: "success",
										confirmButtonClass: 'btn-success',
										confirmButtonText: 'Great'
								});
								}
							else{
								swal({
										title: "Save Failed",
										text: "Catalog could not be released: " + response.msg,
										icon: "warning",
										confirmButtonClass: 'btn-warning',
										confirmButtonText: 'OK'
								});
							}
						},
						error: function(jqXHR, textStatus, errorThrown) {
							$('#loader_img').hide();
							swal("Server Error", "Something went wrong at the server. If this repeats please report the problem to YPS", "error");
						}
				});
				return false;
			},
		});
	});
</script>

</body>

</html>
<?php
}
else
{
	if (basename($_SERVER['HTTP_REFERER']) == THIS) {
		header("Location: manage_home.php");
		print("<script>location.href='manage_home.php'</script>");

	}
	else {
		header("Location: " . $_SERVER['HTTP_REFERER']);
		print("<script>location.href='".$_SERVER['HTTP_REFERER']."'</script>");
	}
}

?>
