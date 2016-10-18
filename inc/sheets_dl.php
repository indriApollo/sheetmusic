<?php
require_once("sheets_functions.php");

user_login("sheets_admin.php");
$uid = $_SESSION["id"];

$pdo = db_login();
if (!$pdo) {
	http_response_code("500");
	die("Erreur de connexion à la db");
}

if(!isset($_GET["title"])) {
	http_response_code(400);
	die("Missing title arg");
}
$title = $_GET["title"];

if(!check_title_exists($title,$pdo)) {
	http_response_code(404);
	die("Unknown title");
}

if(!isset($_GET["sheet"])) {
	http_response_code(400);
	die("Missing sheet arg");
}
$sheet = $_GET["sheet"];

$instruments = user_instruments($uid,$pdo);
$file_instrument = explode("_", pathinfo($sheet,PATHINFO_BASENAME))[0];

if(!in_array($file_instrument, $instruments)) {
	http_response_code(403);
	die("You don't have access to this file");
}

$file = "../sheets/".$title."/".$sheet.".pdf";

if (file_exists($file)) {
    header('Content-Description: File Transfer');
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="'.$title."-".basename($file).'"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Content-Length: ' . filesize($file));
    readfile($file);
    exit;
} else {
	http_response_code(404);
	die("Unknown file");
}
?>