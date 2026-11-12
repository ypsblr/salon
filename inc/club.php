<?php
if (!function_exists("club_config")) {
	function club_config_path() {
		return __DIR__ . "/../club.config";
	}

	function club_config_keys() {
		return array(
			"club_name",
			"club_short_name",
			"yps_website_without_www",
			"yps_website_with_www",
			"contact_email",
			"salon_email",
			"contest_email",
			"club_contact1_label",
			"club_contact1_name",
			"club_contact1_email",
			"club_contact1_phone",
			"club_contact2_label",
			"club_contact2_name",
			"club_contact2_email",
			"club_contact2_phone",
			"address_line1",
			"address_line2",
			"city",
			"state",
			"country",
			"pincode",
		);
	}

	function club_config_ini_line($key, $value) {
		if ($value === "")
			return $key . " = ";
		$escaped = str_replace(array("\\", "\""), array("\\\\", "\\\""), $value);
		return $key . ' = "' . $escaped . '"';
	}

	function club_config_save($values) {
		$lines = array(
			"; Club identity for the salon application.",
			"; Change club_name here when this system is used by another club.",
			"",
		);
		foreach (club_config_keys() as $key) {
			$val = isset($values[$key]) ? $values[$key] : "";
			$lines[] = club_config_ini_line($key, trim($val));
		}
		$path = club_config_path();
		$written = file_put_contents($path, implode("\n", $lines) . "\n", LOCK_EX);
		return $written !== false;
	}

	function club_config($key = null) {
		static $config = null;
		static $config_mtime = null;
		$path = club_config_path();
		$mtime = is_file($path) ? filemtime($path) : 0;
		if ($config === null || $config_mtime !== $mtime) {
			$parsed = parse_ini_file($path);
			$config = is_array($parsed) ? $parsed : array();
			if (empty($config["club_name"]))
				$config["club_name"] = "Youth Photographic Society";
			$config_mtime = $mtime;
		}
		if ($key === null)
			return $config;
		return isset($config[$key]) ? $config[$key] : "";
	}

	function club_name() {
		return club_config("club_name");
	}

	function club_name_html() {
		return htmlspecialchars(club_name(), ENT_QUOTES, "UTF-8");
	}

	function club_name_upper_html() {
		return htmlspecialchars(strtoupper(club_name()), ENT_QUOTES, "UTF-8");
	}

	function club_short_name() {
		$name = club_config("club_short_name");
		if ($name === "")
			$name = "YPS";
		return $name;
	}

	function club_short_name_html() {
		return htmlspecialchars(club_short_name(), ENT_QUOTES, "UTF-8");
	}

	function yps_website_without_www() {
		$url = club_config("yps_website_without_www");
		if ($url === "")
			$url = "https://ypsbengaluru.com";
		return rtrim($url, "/");
	}

	function yps_website_with_www() {
		$url = club_config("yps_website_with_www");
		if ($url === "")
			$url = "https://www.ypsbengaluru.com";
		return rtrim($url, "/");
	}

	function yps_website_without_www_html() {
		return htmlspecialchars(yps_website_without_www(), ENT_QUOTES, "UTF-8");
	}

	function yps_website_with_www_html() {
		return htmlspecialchars(yps_website_with_www(), ENT_QUOTES, "UTF-8");
	}

	function contact_email() {
		$email = club_config("contact_email");
		if ($email === "")
			$email = "contactus@ypsbengaluru.com";
		return $email;
	}

	function contact_email_html() {
		return htmlspecialchars(contact_email(), ENT_QUOTES, "UTF-8");
	}

	function salon_email() {
		$email = club_config("salon_email");
		if ($email === "")
			$email = "salon@ypsbengaluru.in";
		return $email;
	}

	function salon_email_html() {
		return htmlspecialchars(salon_email(), ENT_QUOTES, "UTF-8");
	}

	function contest_email() {
		$email = club_config("contest_email");
		if ($email === "")
			$email = "contest@ypsbengaluru.com";
		return $email;
	}

	function contest_email_html() {
		return htmlspecialchars(contest_email(), ENT_QUOTES, "UTF-8");
	}

	function club_contact1_label() {
		return club_config("club_contact1_label");
	}

	function club_contact1_label_html() {
		return htmlspecialchars(club_contact1_label(), ENT_QUOTES, "UTF-8");
	}
	
	function club_contact1_name() {
		return club_config("club_contact1_name");
	}

	function club_contact1_name_html() {
		return htmlspecialchars(club_contact1_name(), ENT_QUOTES, "UTF-8");
	}
	
	
	function club_contact1_email() {
		return club_config("club_contact1_email");
	}

	function club_contact1_email_html() {
		return htmlspecialchars(club_contact1_email(), ENT_QUOTES, "UTF-8");
	}
	
	
	function club_contact1_phone() {
		return club_config("club_contact1_phone");
	}

	function club_contact1_phone_html() {
		return htmlspecialchars(club_contact1_phone(), ENT_QUOTES, "UTF-8");
	}
	
	
	function club_contact2_label() {
		return club_config("club_contact2_label");
	}

	function club_contact2_label_html() {
		return htmlspecialchars(club_contact2_label(), ENT_QUOTES, "UTF-8");
	}
	
	
	function club_contact2_name() {
		return club_config("club_contact2_name");
	}

	function club_contact2_name_html() {
		return htmlspecialchars(club_contact2_name(), ENT_QUOTES, "UTF-8");
	}
	
	
	function club_contact2_email() {
		return club_config("club_contact2_email");
	}

	function club_contact2_email_html() {
		return htmlspecialchars(club_contact2_email(), ENT_QUOTES, "UTF-8");
	}

	function club_contact2_phone() {
		return club_config("club_contact2_phone");
	}

	function club_contact2_phone_html() {
		return htmlspecialchars(club_contact2_phone(), ENT_QUOTES, "UTF-8");
	}
	
	
	function address_line1() {
		return club_config("address_line1");
	}

	function address_line1_html() {
		return htmlspecialchars(address_line1(), ENT_QUOTES, "UTF-8");
	}
	
	
	function address_line2() {
		return club_config("address_line2");
	}

	function address_line2_html() {
		return htmlspecialchars(address_line2(), ENT_QUOTES, "UTF-8");
	}
	
	
	function city() {
		return club_config("city");
	}

	function city_html() {
		return htmlspecialchars(city(), ENT_QUOTES, "UTF-8");
	}
	
	
	function state() {
		return club_config("state");
	}

	function state_html() {
		return htmlspecialchars(state(), ENT_QUOTES, "UTF-8");
	}
	
	
	function country() {
		return club_config("country");
	}

	function country_html() {
		return htmlspecialchars(country(), ENT_QUOTES, "UTF-8");
	}
	
	
	function pincode() {
		return club_config("pincode");
	}

	function pincode_html() {
		return htmlspecialchars(pincode(), ENT_QUOTES, "UTF-8");
	}
	
}
