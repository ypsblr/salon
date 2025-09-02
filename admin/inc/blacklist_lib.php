<?php

// Blacklist check functions
function strip_vowels($a) {
	$a = strtolower($a);
	// Remove Vowels and pseudo-vowels except when it is the first letter
	$strip_chars = "aeiouhy";
	$str = "";
	
	// Ignore the first letter of the word
	for ($i = 0; $i < strlen($a); ++$i) {
		if ($i == 0)
			$str .= $a[$i];
		else if (strpos($strip_chars, $a[$i]) == false)
			$str .= $a[$i];
	}
	return $str;
}

// match name after breaking into words
function processed_words($a) {
	$words = [];
	$word = strtok($a, " ");
	while ($word) {
		$words[] = $word;
		$word = strtok(" ");
	}
	
	// Combine adjascent single-letter words (initials)
	$fused_words = [];
	$long_words = [];
	$fused_index = -1;
	$prev_single_letter = false;
	for ($i = 0; $i < sizeof($words); ++ $i) {
		if (strlen($words[$i]) == 1) {
			if (! $prev_single_letter)
				++ $fused_index;
			$fused_words[$fused_index] = $words[$i];
			$prev_single_letter = true;
		}
		else {
			$prev_single_letter = false;
			$long_words[] = strip_vowels($words[$i]);
		}
	}
	
	$words = array_merge($fused_words, $long_words);
	sort($words);
	return $words;
}

function partial_name_match($a, $b) {
	$a_words = processed_words($a);
	$b_words = processed_words($b);
	if (sizeof($a_words) != sizeof($b_words))
		return false;

	for ($i = 0; $i < sizeof($a_words); ++ $i) {
		$a_word = $a_words[$i];
		$b_word = $b_words[$i];
		$matches = similar_text($a_word, $b_word);
		// OK to have 1 mis-matched letter
		if ($matches < (max(strlen($a_word), strlen($b_word)) -1))
			return false;
	}
	return true;
}

// match name after breaking into words
function words($a) {
	$words = [];
	$word = strtok($a, " ");
	while ($word) {
		$words[] = $word;
		$word = strtok(" ");
	}
	return $words;
}

function name_match($a, $b) {
	$a_words = words($a);
	$b_words = words($b);
	if (sizeof($a_words) != sizeof($b_words))
		return false;

	for ($i = 0; $i < sizeof($a_words); ++ $i) {
		if (strtolower($a_words[$i]) != strtolower($b_words[$i]))
			return false;
	}
	return true;
}

function get_number($a) {
	$b = "";
	for ($i = strlen($a) - 1; $i > 0; -- $i)
		$b = ($a[$i] >= '0' && $a[$i] <= '9') ? $a[$i] . $b : "";
	
	return $b;
}

// Compare last 10 digits only
function phone_match($a, $b) {
	$a_ten = substr(get_number($a), -10);
	$b_ten = substr(get_number($b), -10);
	if ($a_ten == "" || $b_ten == "")
		return false;
	// Strip non-numerics
	return ($a_ten == $b_ten);
}

// Convert to lower case and match
function email_match($a, $b) {
	// don't match empty strings
	if ($a == "" || $b == "")
		return false;
	return (strtolower($a) == strtolower($b));
}

// Check if an exception has been recorded
function check_exception($email) {
	global $DBCON;
	
	if ($email == "")
		return false;
	$sql = "SELECT * FROM blacklist_exception WHERE email = '$email' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	return (mysqli_num_rows($query) > 0);
}

// Main routine, runs the details through blacklist
function check_blacklist($name, $email = "", $phone = "") {
	global $DBCON;
	
	$sql  = "SELECT * FROM blacklist ";
	$sql .= " WHERE entity_type = 'INDIVIDUAL' ";
	$sql .= "   AND withdrawn = '0' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	while ($row = mysqli_fetch_array($query)) {
		if (email_match($email, $row['email']) || phone_match($phone, $row['phone']) || name_match($name, $row['entity_name'])) {
			return array("MATCH", $row['entity_name']);
		}
		if (partial_name_match($name, $row['entity_name'])) {
			return array("SIMILAR", $row['entity_name']);
		}
	}
	return array("", "");
}

// Set Blacklist flags in profile
function mark_blacklist($profile_id, $match) {
	global $DBCON;
	
	$sql = "UPDATE profile SET blacklist_match = '$match', blacklist_exception = '0' WHERE profile_id = '$profile_id' ";
	mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
}



?>