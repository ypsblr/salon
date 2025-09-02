<?php
	$partners = array(
					array("name" => "Book My Lens", "website" => "www.bookmylens.com", "phone" => "1800-121-0446",
							"email" => "rentals@bookmylens.com", "logo" => "bookmylens_logo.jpg", "img" => "partner_Book_My_Lens.jpg", "text" => ""),
					array("name" => "Dr. M P Somaprasad", "website" => "", "phone" => "",
							"email" => "", "logo" => "somprasad.jpg", "img" => "somprasad.jpg", "text" => "Life Member, YPS")
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
