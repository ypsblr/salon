<?php

function sort_partner_by_sequence($a, $b){
	if ($a['sequence'] == $b['sequence'])
		return 0;

	if ($a['sequence'] < $b['sequence'])
	 	return -1;
	else
	 	return 1;
}

function get_partner_data($yearmonth) {

	if (file_exists(__DIR__ . "/../salons/$yearmonth/blob/partners.json")) {
		// Load Partner Data
		$partner_data = json_decode(file_get_contents(__DIR__ . "/../salons/$yearmonth/blob/partners.json"), true);
		if (json_last_error() != JSON_ERROR_NONE) {
			$_SESSION['err_msg'] = "Partner Definition garbled";
			return false;
		}
		if (sizeof($partner_data['partners']) == 0)
			return false;		// Nothing to do

		// Sort Partners by Sequence
		usort($partner_data['partners'], "sort_partner_by_sequence");

		return $partner_data;
	}
	else {
		return false;
	}

}

function partner_display($yearmonth) {
	if ($partner_data = get_partner_data($yearmonth)) {
		// There are partners. Let us start display
		// Set up session variable for rotating display
		if (isset($_SESSION['partner_key'])) {
			$_SESSION['partner_key'] += 1;
			if ($_SESSION['partner_key'] >= sizeof($partner_data['partners']))
				$_SESSION['partner_key'] = 0;
		}
		else
			$_SESSION['partner_key'] = 0;

		// Validate Data
		$idx = $_SESSION['partner_key'];
		$partner_name = $partner_data['partners'][$idx]['name'];
		$partner_img = $partner_data['partners'][$idx]['img'];
		$partner_text = $partner_data['partners'][$idx]['text'];
		$partner_tagline = $partner_data['partners'][$idx]['tagline'];
		// Name, Image and Text are minimum requirements to show
		if ($partner_name == "" || $partner_img == "" || $partner_text == "")
			return;
		// Check for image
		if (! file_exists(__DIR__ . "/../salons/$yearmonth/img/sponsor/$partner_img"))
			return;
		list($partner_img_width, $partner_img_height) = getimagesize(__DIR__ . "/../salons/$yearmonth/img/sponsor/$partner_img");
		// Other fields if they exist
		$partner_website = isset($partner_data['partners'][$idx]['website']) ? $partner_data['partners'][$idx]['website'] : "";
		$partner_phone = isset($partner_data['partners'][$idx]['phone']) ? $partner_data['partners'][$idx]['phone'] : "";
		$partner_email = isset($partner_data['partners'][$idx]['email']) ? $partner_data['partners'][$idx]['email'] : "";
		$partner_logo = isset($partner_data['partners'][$idx]['logo']) ? $partner_data['partners'][$idx]['logo'] : "";

?>
<h2 class="headline text-color">
	<span class="border-color">Partners</span>
</h2>
<?php
?>
<h3 class="text-color">
<?php
	if ($partner_logo != "") {
?>
	<div style='display: inline-block;'><img src="/salons/<?= $yearmonth;?>/img/sponsor/<?= $partner_logo;?>" style="max-width: 80px;" /></div>
<?php
	}
?>
	<div style='display: inline-block; vertical-align: middle;'>
		<?= $partner_name;?>
		<?= ($partner_tagline != "") ? "<br><span style='color: #888;'><i><small>" . $partner_tagline . "</small></i></span>" : "";?>
	</div>
</h3>
<div class="row">
	<div class="col-sm-12 thumbnail" style="margin-bottom: 8px;">
<?php
		if ($partner_website != "") {
?>
		<a href="<?= $partner_website;?>" target="_blank">
			<img src="/salons/<?= $yearmonth;?>/img/sponsor/<?= $partner_img;?>" style="max-width:300px;">
		</a>
<?php
		}
		else {
?>
		<img src="/salons/<?= $yearmonth;?>/img/sponsor/<?= $partner_img;?>" style="max-width:300px;">
<?php
		}
?>
		<p class="text-color text-center"><b><?= $partner_text;?></b></p>
<?php
		if ($partner_phone != "" || $partner_email != "") {
?>
		<div class="row">
			<div class="col-sm-12">
				<p class="text text-color">
<?php
			if ($partner_phone != "") {
?>
					<span class="pull-left"><i class="fa fa-phone"></i> <b><?= $partner_phone;?></b></span>
<?php
			}
			if ($partner_email != "") {
?>
					<span class="pull-right"><i class="fa fa-at"></i> <a href="mailto:<?= $partner_email;?>" ><b><?= $partner_email;?></b></a></span>
<?php
			}
?>
				</p>
			</div>
		</div>
	</div>
</div>
<?php
		}
	}
}

function partner_email_footer($yearmonth) {

	if (isset($_SERVER['HTTPS']) && (! empty($_SERVER['HTTPS'])) && $_SERVER['HTTPS'] != "off")
		$site_url = "https://" . $_SERVER['SERVER_NAME'];
	else
		$site_url = "http://" . $_SERVER['SERVER_NAME'];

	$html = "";
	if ($partner_data = get_partner_data($yearmonth)) {
		$html .= "<table width='100%' cellpadding='8' style='border-bottom: 1px solid #d0d0d0; border-top: 1px solid #d0d0d0; border-collapse: collapse;'>";
		foreach ($partner_data['partners'] as $partner) {
			$logo = $site_url . "/salons/" . $yearmonth . "/img/sponsor/" . rawurlencode($partner['logo']);
			$html .= "<tr>";
			$html .= "<td width='120' style='border-bottom: 1px solid #d0d0d0; border-collapse: collapse;'>";
			if (! empty($partner['logo']) && file_exists(__DIR__ . "/../salons/$yearmonth/img/sponsor/$logo")) {
				if ( ! empty($partner['website']))
					$html .= "<a href='" . $partner['website'] . "'><img style='max-width:120px; max-height:120px;' src='" . $logo . "' ></a>";
				else
					$html .= "<img style='max-width:120px; max-height:120px;' src='" . $logo . "' >";
			}
			$html .= "</td>";
			$html .= "<td style='border-bottom: 1px solid #d0d0d0; border-collapse: collapse; vertical-align:top;'>";
			$html .= "<h4 style='margin: 2px 0px 4px 0px;'>" . $partner['name'] . "</h4>";
			if (! empty($partner['tagline']))
				$html .= "<br><span style='color: #444;'><i>" . $partner['tagline'] . "</i></span>";
			if (!empty($partner['text']))
				$html .= "<br><i>" . $partner['text'] . "</i>";
			if (!empty($partner['website']))
				$html .= "<br><a href='http://" . $partner['website'] . "'>" . $partner['website'] . "</a>";
			if (!empty($partner['email']))
				$html .= "<br><a href='mailto:" . $partner['email'] . "'>" . $partner['email'] . "</a>";
			if (!empty($partner['phone']))
				$html .= "<br>Contact_No: " . $partner['phone'];
			$html .= "</td>";
			$html .= "</tr>";
		}
		$html .= "</table>";
	}
	return $html;
}
?>
