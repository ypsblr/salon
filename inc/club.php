<?php
if (!function_exists("club_config")) {
	function club_config($key = null) {
		static $config = null;
		if ($config === null) {
			$parsed = parse_ini_file(__DIR__ . "/../club.config");
			$config = is_array($parsed) ? $parsed : array();
			if (empty($config["club_name"]))
				$config["club_name"] = "Youth Photographic Society";
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
}
