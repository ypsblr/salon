<?php
	$partners = array(
					array("name" => "Technova Novalife", "website" => "www.technovaworld.com", "phone" => "1800-267-7474",
							"email" => "corp@technovaworld.com", "logo" => "technova_logo.png", "img" => "technova_novalife.gif", "text" => ""),
					array("name" => "Technova Photo Select", "website" => "photoselect.technovaworld.com", "phone" => "1800-267-7474",
							"email" => "pss@technovaindia.com", "logo" => "technova_photoselect_logo.png", "img" => "technova_photo_select.gif", "text" => ""),
					array("name" => "YPS Salon Group", "website" => "", "phone" => "",
							"email" => "salongroup@ypsbengaluru.com", "logo" => "ypsLogo.png", "img" => "salon_group.png", "text" => "Contributions from members of Salon Participation Group - Winners of over 230 Best Club Awards"),
					array("name" => "Mahesh Viswanadha", "website" => "", "phone" => "",
							"email" => "", "logo" => "mahesh_viswanadha_logo.png", "img" => "mahesh_viswanadha.png", "text" => "In memory of Raja Dhanrajgirji Bahadur, Hyderabad"),
					// , array("name" => "Prolab", "website" => "www.prolab.in", "phone" => "+91-98451-40110",
					// 			"email" => "naveen@prolab.in", "img" => "partner_PRO_LAB.jpg")
				);

	function partner_email_footer($yearmonth) {
		global $partners;

		$html = "";
		if (! empty($partners) && sizeof($partners) > 0) {
			$html .= "<table width='100%' cellpadding='8' style='border-bottom: 1px solid #d0d0d0; border-top: 1px solid #d0d0d0; border-collapse: collapse;'>";
			foreach ($partners as $partner) {
				$logo = http_method() . $_SERVER['HTTP_HOST'] . "/salons/" . $yearmonth . "/img/sponsor/" . $partner['logo'];
				$html .= "<tr>";
				$html .= "<td width='96' style='border-bottom: 1px solid #d0d0d0; border-collapse: collapse;'>";
				if (! empty($partner['logo']) && file_exists(__DIR__ . "/../img/sponsor/" . $partner['logo'])) {
					if ( ! empty($partner['website']))
						$html .= "<a href='" . $partner['website'] . "'><img style='max-width:80px; max-height:80px;' src='" . $logo . "' ></a>";
					else
						$html .= "<img style='max-width:80px; max-height:80px;' src='" . $logo . "' >";
				}
				$html .= "</td>";
				$html .= "<td style='border-bottom: 1px solid #d0d0d0; border-collapse: collapse;'>";
				$html .= "<b>" . $partner['name'] . "</b>";
				if (!empty($partner['text']))
					$html .= "<br><i>" . $partner['text'] . "</i>";
				if (!empty($partner['website']))
					$html .= "<br><a href='http://" . $partner['website'] . "'>" . $partner['website'] . "</a>";
				if (!empty($partner['email']))
					$html .= "<br><a href='mailto:" . $partner['email'] . "'>" . $partner['email'] . "</a>";
				if (!empty($partner['phone']))
					$html .= "<br>Phone: " . $partner['phone'];
				$html .= "</td>";
				$html .= "</tr>";
			}
			$html .= "</table>";
		}

		return $html;
	}
?>
