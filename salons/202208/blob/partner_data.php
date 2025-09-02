<?php
	$partners = array(
					array("name" => "ZEISS", "website" => "www.zeiss.com/photo", "phone" => "9289776525",
						"email" => "dl.cop-support.in@zeiss.com", "logo" => "zeiss-logo-tagline_rgb.png", "img" => "ZEISS.gif", "text" => "Exhibition Partner"),
							// "email" => "dl.cop-support.in@zeiss.com", "logo" => "zeiss-logo-tagline_rgb.png", "img" => "Exhibition_Partner_ZEISS.jpg", "text" => "Exhibition Partner"),
					// array("name" => "Technova Photo Select", "website" => "photoselect.technovaworld.com", "phone" => "1800-267-7474",
					// 		"email" => "pss@technovaindia.com", "logo" => "technova_photoselect_logo.png", "img" => "technova_photo_select.gif", "text" => ""),
					// array("name" => "YPS Salon Group", "website" => "", "phone" => "",
					// 		"email" => "salongroup@ypsbengaluru.com", "logo" => "ypsLogo.png", "img" => "salon_group.png", "text" => "Contributions from members of Salon Participation Group - Winners of over 230 Best Club Awards"),
					// array("name" => "Mahesh Viswanadha", "website" => "", "phone" => "",
					// 		"email" => "", "logo" => "mahesh_viswanadha_logo.png", "img" => "mahesh_viswanadha.png", "text" => "In memory of Raja Dhanrajgirji Bahadur, Hyderabad"),
					// , array("name" => "Prolab", "website" => "www.prolab.in", "phone" => "+91-98451-40110",
					// 			"email" => "naveen@prolab.in", "img" => "partner_PRO_LAB.jpg")
				);

	function partner_email_footer($yearmonth, $partners, $site_url) {

		// debug_dump("partners", $partners, __FILE__, __LINE__);

		$html = "";
		if ((! empty($partners)) && sizeof($partners) > 0) {
			$html .= "<table width='100%' cellpadding='8' style='border-bottom: 1px solid #d0d0d0; border-top: 1px solid #d0d0d0; border-collapse: collapse;'>";
			foreach ($partners as $partner) {
				$logo = $site_url . "/salons/" . $yearmonth . "/img/sponsor/" . rawurlencode($partner['logo']);
				$html .= "<tr>";
				$html .= "<td width='120' style='border-bottom: 1px solid #d0d0d0; border-collapse: collapse;'>";
				if (! empty($partner['logo']) && file_exists(__DIR__ . "/../img/sponsor/" . $partner['logo'])) {
					if ( ! empty($partner['website']))
						$html .= "<a href='" . $partner['website'] . "'><img style='max-width:120px; max-height:120px;' src='" . $logo . "' ></a>";
					else
						$html .= "<img style='max-width:120px; max-height:120px;' src='" . $logo . "' >";
				}
				$html .= "</td>";
				$html .= "<td style='border-bottom: 1px solid #d0d0d0; border-collapse: collapse; vertical-align:top;'>";
				$html .= "<h4 style='margin: 2px 0px 4px 0px;'>" . $partner['name'] . "</h4>";
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
