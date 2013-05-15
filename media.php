<?php

include ("ify.php");


// _POST Management
$function = (isset($_POST["f"])) ? $_POST["f"] : "null";
$args = (isset($_POST["a"])) ? $_POST["a"] : "null";

// _GET Management
$function = (isset($_GET["f"])) ? $_GET["f"] : "null";
$args = (isset($_GET["a"])) ? $_GET["a"] : "null";


// Action to do
switch ( $function) {
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

	// Check input

	// Convert ID to File

	serveFile( $file, $name, true);
}

?>
