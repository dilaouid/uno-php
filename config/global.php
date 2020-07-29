<?php
    include('Class/Lib.php');
	include('Class/Room.php');
	include('Class/Game.php');

	if (!isset($_COOKIE['player']) || strlen($_COOKIE['player']) < 22) {
		setcookie('player', bin2hex(random_bytes(11)), 0);
	}

    date_default_timezone_set('Europe/Paris');

    $DB_DSN = "mysql:dbname=uno;host=localhost";
    $USER_DB = "root";
    $PASSWORD_DB = "";
	
    if (!isset($DB_DSN) || !isset($USER_DB) || !isset($PASSWORD_DB)) {
		try {
		    throw new Exception("Les identifiants de la base de données ne sont pas correctement saisis. Merci de vérifier le fichier de configuration.");
		} catch(PDOException $ex) {
		    echo $e->getMessage();
		    die();
		}
	}

	try {
	    $DB = new PDO($DB_DSN, $USER_DB, $PASSWORD_DB);
		$DB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	} catch(PDOException $ex) {
		throw new Exception("Les identifiants de la base de données sont incorrects. Merci de les corriger dans le fichier de configuration.");
		echo $e->getMessage();
	    die();
    }

	$URL = 'http://localhost/';

	$alert = [
		"joinRoom" => null,
		"createRoom" => null
	];

	$Lib = new \Uno\Lib($DB);

?>