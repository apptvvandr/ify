<?php

include ("ify.php");


// _POST Management
$media = (isset($_POST["m"])) ? $_POST["m"] : "null";
$args = (isset($_POST["a"])) ? $_POST["a"] : "null";

// _GET Management
$media = (isset($_GET["m"])) ? $_GET["m"] : "null";
$args = (isset($_GET["a"])) ? $_GET["a"] : "null";


$conf = new ifyConfig();

// Action to do
switch ( $media) {
	case "as":
	case "audio_stream":
		audio_stream( $args);
		break;
	case "ad":
	case "audio_download":
		audio_download( $args);
		break;
	case "at":
	case "audio_test":
		audio_test();
		break;
    default:
	throw New \Exception(sprintf('Error: Wrong argument when calling media.php'));
}

function audio_test () {
	serveFile('/var/www/ify/tests/zik/Air/Moon Safari/03 - All I Need.mp3','muziq.mp3', 'audio/mpeg');
}

function audio_stream ( $args) {
	$db = new ifyDB;

	// Check input

	// Convert ID to File
	$file = $db->getFilePath($args);

	if ($file != 1 && $file != 2)
		serveFile( $file, pathinfo($file, PATHINFO_BASENAME), true);
}

?>
