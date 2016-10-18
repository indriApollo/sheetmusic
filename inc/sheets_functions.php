<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/wp-load.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/wp-includes/wp-db.php");

define(LOGOUTURL, wp_logout_url());
define(WP,$GLOBALS["wpdb"]->prefix);


function db_login() {

	try {
		$pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASSWORD);
		$pdo ->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
	} catch(Exception $e) {
		error_log($e->getMessage());
		return false;
	}
	return $pdo;
}

function user_login($page) {

	if(!is_user_logged_in()) {
		header('Location: '.wp_login_url( site_url("/sheetmusic/".$page)) );
		die();
	}

	session_start();
	if (!$_SESSION) {
		session_regenerate_id();
		$cu = wp_get_current_user();
		$_SESSION = array("id" => $cu->ID, "email" => $cu->user_email, "isAdmin" => in_array("administrator", $cu->roles));
	}
}

function user_emails($pdo) {

	$q = "SELECT user_email FROM ".WP."users";

	try {
		$stmt = $pdo->prepare($q);
		$stmt->execute();
		$arr = $stmt->fetchAll(PDO::FETCH_COLUMN,"user_email");

	} catch(Exception $e) {
		error_log("Caught $e");
		http_response_code(500);
		die("Caught $e");
	}

	return $arr;
}

function all_instruments($pdo) {

	$q = "SELECT instrument FROM instruments";

	try {
		$stmt = $pdo->prepare($q);
		$stmt->execute();
		$arr = $stmt->fetchAll(PDO::FETCH_COLUMN,"instrument");

	} catch(Exception $e) {
		error_log("Caught $e");
		http_response_code(500);
		die("Caught $e");
	}

	return $arr;
}

function user_instruments($uid,$pdo) {

	$q = "SELECT instrument FROM instruments,users_instruments
			WHERE instruments.instrument_id = users_instruments.instrument_id
			AND users_instruments.user_id = :uid";

	try {
		$stmt = $pdo->prepare($q);
		$stmt->bindParam(":uid",$uid,PDO::PARAM_INT);
		$stmt->execute();
		$arr = $stmt->fetchAll(PDO::FETCH_COLUMN,"instruments");

	} catch(Exception $e) {
		error_log("Caught $e");
		http_response_code(500);
		die("Caught $e");
	}

	return $arr;
}

function check_title_exists($title,$pdo) {

	//http://stackoverflow.com/a/2021729
	if(mb_ereg("([^\w\s\d\-_~,;\[\]\(\).])", $title)) {
		http_response_code(400);
		die("Title contains illegal characters");
	}

	$q = "SELECT COUNT(*) FROM music WHERE title = :title LIMIT 1";

	try {
		$stmt = $pdo->prepare($q);
		$stmt->bindParam(":title",$title,PDO::PARAM_STR);
		$stmt->execute();
	} catch(Exception $e) {
		error_log("Caught $e");
		http_response_code(500);
		die("Caught $e");
	}

	if($stmt->fetchColumn() > 0)
		return true;
	else
		return false;
}
?>