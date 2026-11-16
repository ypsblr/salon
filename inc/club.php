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

	function club_logo_filename() {
		return "ypsLogo.png";
	}

	function club_logo_path() {
		return __DIR__ . "/../img/" . club_logo_filename();
	}

	function club_logo_exists() {
		return is_file(club_logo_path());
	}

	function club_logo_web_path() {
		return "img/" . club_logo_filename();
	}

	function club_logo_version_suffix() {
		if (!club_logo_exists())
			return "";
		return "?v=" . (int) filemtime(club_logo_path());
	}

	function club_logo_url_root() {
		return "/" . club_logo_web_path();
	}

	/** Site-root path for &lt;img src&gt; (e.g. /img/ypsLogo.png?v=…). */
	function club_logo_src() {
		return club_logo_url_root() . club_logo_version_suffix();
	}

	function club_logo_src_html() {
		return htmlspecialchars(club_logo_src(), ENT_QUOTES, "UTF-8");
	}

	/** Absolute URL for payment gateways, PDF builders, etc. */
	function club_logo_url() {
		if (!function_exists("http_method"))
			return club_logo_src();
		return http_method() . $_SERVER["SERVER_NAME"] . club_logo_src();
	}

	function club_logo_url_html() {
		return htmlspecialchars(club_logo_url(), ENT_QUOTES, "UTF-8");
	}

	/** Mail/HTML template token replacements for club logo. */
	function club_logo_mail_pairs() {
		return array("club-logo-url" => club_logo_url());
	}

	/**
	 * Save uploaded club logo to img/ypsLogo.png (used site-wide).
	 * Accepts PNG, JPEG, or GIF; non-PNG formats are converted when GD is available.
	 *
	 * @param array $file One entry from $_FILES
	 * @return true on success, false on failure; true if no file was uploaded
	 */
	function site_favicon_filename() {
		return "favicon.png";
	}

	function site_favicon_path() {
		return __DIR__ . "/../img/" . site_favicon_filename();
	}

	function site_favicon_exists() {
		return is_file(site_favicon_path());
	}

	function site_favicon_web_path() {
		return "img/" . site_favicon_filename();
	}

	function site_favicon_version_suffix() {
		if (!site_favicon_exists())
			return "";
		return "?v=" . (int) filemtime(site_favicon_path());
	}

	function site_favicon_url_root() {
		return "/" . site_favicon_web_path();
	}

	function site_favicon_src() {
		return site_favicon_url_root() . site_favicon_version_suffix();
	}

	function site_favicon_src_html() {
		return htmlspecialchars(site_favicon_src(), ENT_QUOTES, "UTF-8");
	}

	/**
	 * Save uploaded site favicon to img/favicon.png.
	 * Accepts PNG, JPEG, or GIF; non-PNG formats are converted when GD is available.
	 *
	 * @param array $file One entry from $_FILES
	 * @return true on success, false on failure; true if no file was uploaded
	 */
	function site_favicon_save_upload($file) {
		if (!isset($file["error"]) || $file["error"] === UPLOAD_ERR_NO_FILE)
			return true;
		if ($file["error"] !== UPLOAD_ERR_OK)
			return false;
		if (!is_uploaded_file($file["tmp_name"]))
			return false;

		$img_dir = __DIR__ . "/../img";
		if (!is_dir($img_dir) && !@mkdir($img_dir, 0775, true))
			return false;

		$name = isset($file["name"]) ? strtolower($file["name"]) : "";
		if (preg_match('/\.ico$/', $name)) {
			$dest = $img_dir . "/favicon.ico";
			if (@move_uploaded_file($file["tmp_name"], $dest)) {
				if (is_file(site_favicon_path()))
					@unlink(site_favicon_path());
				return true;
			}
			return false;
		}

		$info = @getimagesize($file["tmp_name"]);
		if ($info === false)
			return false;

		$dest = site_favicon_path();
		$type = $info[2];

		if (is_file($img_dir . "/favicon.ico"))
			@unlink($img_dir . "/favicon.ico");

		if ($type === IMAGETYPE_PNG)
			return move_uploaded_file($file["tmp_name"], $dest);

		if (!function_exists("imagepng"))
			return false;

		$src = null;
		if ($type === IMAGETYPE_JPEG && function_exists("imagecreatefromjpeg"))
			$src = @imagecreatefromjpeg($file["tmp_name"]);
		elseif ($type === IMAGETYPE_GIF && function_exists("imagecreatefromgif"))
			$src = @imagecreatefromgif($file["tmp_name"]);
		if ($src === false || $src === null)
			return false;

		imagealphablending($src, false);
		imagesavealpha($src, true);
		$ok = imagepng($src, $dest);
		imagedestroy($src);
		return $ok;
	}

	/** Path for &lt;link rel="icon"&gt; (prefers img/favicon.ico if present). */
	function site_favicon_link_href() {
		$ico = __DIR__ . "/../img/favicon.ico";
		if (is_file($ico))
			return "/img/favicon.ico?v=" . (int) filemtime($ico);
		return site_favicon_src();
	}

	function site_favicon_link_href_html() {
		return htmlspecialchars(site_favicon_link_href(), ENT_QUOTES, "UTF-8");
	}

	function site_favicon_link_type() {
		$ico = __DIR__ . "/../img/favicon.ico";
		if (is_file($ico))
			return "image/x-icon";
		return "image/png";
	}

	function site_favicon_link_type_html() {
		return htmlspecialchars(site_favicon_link_type(), ENT_QUOTES, "UTF-8");
	}

	function club_logo_save_upload($file) {
		if (!isset($file["error"]) || $file["error"] === UPLOAD_ERR_NO_FILE)
			return true;
		if ($file["error"] !== UPLOAD_ERR_OK)
			return false;
		if (!is_uploaded_file($file["tmp_name"]))
			return false;

		$info = @getimagesize($file["tmp_name"]);
		if ($info === false)
			return false;

		$img_dir = __DIR__ . "/../img";
		if (!is_dir($img_dir))
			return false;

		$dest = club_logo_path();
		$type = $info[2];

		if ($type === IMAGETYPE_PNG)
			return move_uploaded_file($file["tmp_name"], $dest);

		if (!function_exists("imagepng"))
			return false;

		$src = null;
		if ($type === IMAGETYPE_JPEG && function_exists("imagecreatefromjpeg"))
			$src = @imagecreatefromjpeg($file["tmp_name"]);
		elseif ($type === IMAGETYPE_GIF && function_exists("imagecreatefromgif"))
			$src = @imagecreatefromgif($file["tmp_name"]);
		if ($src === false || $src === null)
			return false;

		imagealphablending($src, false);
		imagesavealpha($src, true);
		$ok = imagepng($src, $dest);
		imagedestroy($src);
		return $ok;
	}

	function salon_logo_filename() {
		return "salon_logo.jpg";
	}

	function salon_logo_yearmonth_valid($yearmonth) {
		$ym = (string) $yearmonth;
		return preg_match('/^\d{6}$/', $ym) === 1;
	}

	function salon_logo_dir($yearmonth) {
		return __DIR__ . "/../salons/" . $yearmonth . "/img";
	}

	function salon_logo_path($yearmonth) {
		return salon_logo_dir($yearmonth) . "/" . salon_logo_filename();
	}

	function salon_logo_exists($yearmonth) {
		if (!salon_logo_yearmonth_valid($yearmonth))
			return false;
		return is_file(salon_logo_path($yearmonth));
	}

	function salon_logo_web_path($yearmonth) {
		return "salons/" . $yearmonth . "/img/" . salon_logo_filename();
	}

	function salon_logo_url_root($yearmonth) {
		return "/" . salon_logo_web_path($yearmonth);
	}

	function salon_logo_version_suffix($yearmonth) {
		if (!salon_logo_exists($yearmonth))
			return "";
		return "?v=" . (int) filemtime(salon_logo_path($yearmonth));
	}

	/** Site-root path for &lt;img src&gt; (e.g. /salons/202208/img/salon_logo.jpg?v=…). */
	function salon_logo_src($yearmonth) {
		return salon_logo_url_root($yearmonth) . salon_logo_version_suffix($yearmonth);
	}

	function salon_logo_src_html($yearmonth) {
		return htmlspecialchars(salon_logo_src($yearmonth), ENT_QUOTES, "UTF-8");
	}

	/** Relative path from manage/ for preview images. */
	function salon_logo_manage_src($yearmonth) {
		return "../" . salon_logo_web_path($yearmonth) . salon_logo_version_suffix($yearmonth);
	}

	function salon_logo_ensure_dir($yearmonth) {
		if (!salon_logo_yearmonth_valid($yearmonth))
			return false;
		$base = __DIR__ . "/../salons/" . $yearmonth;
		if (!is_dir($base) && !@mkdir($base, 0775, true))
			return false;
		$blob = $base . "/blob";
		if (!is_dir($blob) && !@mkdir($blob, 0775, true))
			return false;
		$img = salon_logo_dir($yearmonth);
		if (!is_dir($img) && !@mkdir($img, 0775, true))
			return false;
		return is_dir($img);
	}

	/**
	 * Save uploaded salon logo to salons/{yearmonth}/img/salon_logo.jpg.
	 * Accepts PNG, JPEG, or GIF; non-JPEG formats are converted when GD is available.
	 *
	 * @param string|int $yearmonth Salon ID (YYYYMM)
	 * @param array $file One entry from $_FILES
	 * @return true on success, false on failure; true if no file was uploaded
	 */
	function salon_logo_save_upload($yearmonth, $file) {
		if (!isset($file["error"]) || $file["error"] === UPLOAD_ERR_NO_FILE)
			return true;
		if ($file["error"] !== UPLOAD_ERR_OK)
			return false;
		if (!salon_logo_yearmonth_valid($yearmonth))
			return false;
		if (!is_uploaded_file($file["tmp_name"]))
			return false;

		$info = @getimagesize($file["tmp_name"]);
		if ($info === false)
			return false;

		if (!salon_logo_ensure_dir($yearmonth))
			return false;

		$dest = salon_logo_path($yearmonth);
		$type = $info[2];

		if ($type === IMAGETYPE_JPEG)
			return move_uploaded_file($file["tmp_name"], $dest);

		if (!function_exists("imagejpeg"))
			return false;

		$src = null;
		if ($type === IMAGETYPE_PNG && function_exists("imagecreatefrompng"))
			$src = @imagecreatefrompng($file["tmp_name"]);
		elseif ($type === IMAGETYPE_GIF && function_exists("imagecreatefromgif"))
			$src = @imagecreatefromgif($file["tmp_name"]);
		elseif ($type === IMAGETYPE_JPEG && function_exists("imagecreatefromjpeg"))
			$src = @imagecreatefromjpeg($file["tmp_name"]);
		if ($src === false || $src === null)
			return false;

		if ($type === IMAGETYPE_PNG) {
			imagealphablending($src, true);
			imagesavealpha($src, true);
		}

		$ok = imagejpeg($src, $dest, 90);
		imagedestroy($src);
		return $ok;
	}

}
