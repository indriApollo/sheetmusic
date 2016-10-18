<?php
require_once("sheets_functions.php");

user_login("sheets_admin.php");

$pdo = db_login();
if (!$pdo) {
	http_response_code("500");
	die("Erreur de connexion à la db");
}

if(!isset($_GET["cmd"])) {
	http_response_code(400);
	die("Missing cmd arg");
}

switch ($_GET["cmd"]) {
	case "get_titles":
		$titles = all_titles($pdo);
		exit(json_encode($titles));
		break;

	case "get_user_sheets":
		if(!isset($_GET["title"])) {
			http_response_code(400);
			die("Missing title arg");
		}

		$sheets = user_sheets($_SESSION["id"],$_GET["title"],$pdo);
		exit(json_encode($sheets));
		break;

	default:
		http_response_code(404);
		die("Unknown cmd");
		break;
}

function all_titles($pdo) {

	$q = "SELECT title FROM music";

	try {
		$stmt = $pdo->prepare($q);
		$stmt->execute();
		$arr = $stmt->fetchAll(PDO::FETCH_COLUMN,"title");
	} catch(Exception $e) {
		error_log("Caught $e");
		http_response_code(500);
		die("Caught $e");
	}
	return $arr;
}

function user_sheets($uid,$title,$pdo) {

	if(!check_title_exists($title,$pdo)) {
		http_response_code(404);
		die("Unknown title");
	}

	$instruments = user_instruments($uid,$pdo);
	$arr = array();

	if(!file_exists("../sheets/$title")) {
		http_response_code(500);
		die("Title doesnt exist on fs");
	}

	foreach (scandir("../sheets/$title") as $filename) {
		//search for pdfs
		if(pathinfo($filename) && pathinfo($filename,PATHINFO_EXTENSION) == "pdf") {
			//filename convention : <instrument>_<*>
			$file_instrument = explode("_", pathinfo($filename,PATHINFO_BASENAME))[0];
			//add to array if file is destined to our user
			if(in_array($file_instrument, $instruments))
				$arr[] = pathinfo($filename,PATHINFO_FILENAME);
		}
	}
	return $arr;
}

?>