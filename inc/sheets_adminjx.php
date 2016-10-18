<?php
require_once("sheets_functions.php");

user_login("sheets_admin.php");
if(!$_SESSION["isAdmin"]) {
	http_response_code(403);
	die("Vous n'avez pas les droits d'administration");
}

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
	case "update_user":
		if(!isset($_GET["email"])) {
			http_response_code(400);
			die("Missing email arg");
		}
		$email = $_GET["email"];
		$uid = getUidFromEmail($email,$pdo);
		
		if(!isset($_GET["instruments"])) {
			http_response_code(400);
			die("Missing instruments arg");
		}

		$iids = array();
		if($_GET["instruments"]) {
			$instruments = explode(",", $_GET["instruments"]);
			foreach ($instruments as $instrument) {
				$iids[] = getIidFromInstrument($instrument,$pdo);
			}
		}

		add_iids_to_uid($uid,$iids,$pdo);
		http_response_code(201);
		exit("updated");
		break;

	case "add_music":
		if(!isset($_GET["title"])) {
			http_response_code(400);
			die("Missing title arg");
		}
		$title = trim($_GET["title"]);
		if(!$title) {
			http_response_code(400);
			die("Empty title arg");
		}
		add_title($title,$pdo);
		http_response_code(201);
		exit("add");
		break;

	case "get_users":
		$emails = user_emails($pdo);
		exit(json_encode($emails));
		break;

	case "get_instruments":
		$instruments = all_instruments($pdo);
		exit(json_encode($instruments));
		break;

	case "user_get_instruments":
		if(!isset($_GET["email"])) {
			http_response_code(400);
			die("Missing email arg");
		}
		$email = $_GET["email"];
		$uid = getUidFromEmail($email,$pdo);

		$ui = user_instruments($uid,$pdo);
		exit(json_encode($ui));
		break;

	default:
		http_response_code(404);
		die("Unknown cmd");
		break;
}

function getUidFromEmail($email,$pdo) {

	$q = "SELECT ID FROM ".WP."users WHERE user_email = :email LIMIT 1";

	try {
		$stmt = $pdo->prepare($q);
		$stmt->bindparam(":email",$email,PDO::PARAM_STR);
		$stmt->execute();
	} catch(Exception $e) {
		error_log("Caught $e");
		http_response_code(500);
		die("Caught $e");
	}

	if(!$row = $stmt->fetch(PDO::FETCH_ASSOC)) {
		http_response_code(404);
		die("Unknown email $email");
	}
	return $row["ID"];
}

function getIidFromInstrument($instrument,$pdo) {

	$q = "SELECT instrument_id FROM instruments WHERE instrument = :instrument LIMIT 1";

	try {
		$stmt = $pdo->prepare($q);
		$stmt->bindParam(":instrument",$instrument,PDO::PARAM_STR);
		$stmt->execute();
	} catch(Exception $e) {
		error_log("Caught $e");
		http_response_code(500);
		die("Caught $e");
	}

	if(!$row = $stmt->fetch(PDO::FETCH_ASSOC)) {
		http_response_code(404);
		die("Unknown iid $iid");
	}
	return $row["instrument_id"];
}

function add_iids_to_uid($uid,$iids,$pdo) {

	$q1 = "DELETE FROM users_instruments WHERE user_id = :uid";

	$q2 = "INSERT INTO users_instruments(user_id, instrument_id) VALUES (:uid,:iid)";

	try {
		$pdo->beginTransaction();

		$stmt1 = $pdo->prepare($q1);
		$stmt1->bindParam(":uid",$uid,PDO::PARAM_INT);
		$stmt1->execute();

		$stmt2 = $pdo->prepare($q2);
		$stmt2->bindParam(":uid",$uid,PDO::PARAM_INT);
		$stmt2->bindParam(":iid",$iid,PDO::PARAM_INT);
		foreach ($iids as $iid) {
			$stmt2->execute();
		}

		$pdo->commit();

	} catch(Exception $e) {
		$pdo->rollBack();
		error_log("Caught $e");
		http_response_code(500);
		die("Caught $e");
	}
}

function add_title($title,$pdo) {

	if(check_title_exists($title,$pdo)) {
		http_response_code(400);
		die("Title already exists");
	}

	$q = "INSERT INTO music(music_id,title) VALUES (0,:title)";

	try {
		$pdo->beginTransaction();

		$stmt = $pdo->prepare($q);
		$stmt->bindParam(":title",$title,PDO::PARAM_STR);
		$stmt->execute();	

		if(!mkdir("../sheets/".$title))
			throw new Exception("Could not create directory");
		//mkdir doesnt set permissions when asked to create a SUBdirectory
		chmod("../sheets/".$title, 0777);

		$pdo->commit();

	} catch(Exception $e) {
		$pdo->rollback();
		error_log("Caught $e");
		http_response_code(500);
		die("Caught $e");
	}
}

?>