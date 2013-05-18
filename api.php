<?php
// Main API file to get content from DB

include ("ify.php");


// _POST Management
$function = (isset($_POST["f"])) ? $_POST["f"] : "null";
$args = (isset($_POST["a"])) ? $_POST["a"] : "null";

// _GET Management
$function = (isset($_GET["f"])) ? $_GET["f"] : "null";
$args = (isset($_GET["a"])) ? $_GET["a"] : "null";


// Action to do
switch ( $function) {
	case "audio_stream":
		audio_stream( $args);
		break;
    default:
	throw New \Exception(sprintf('Error: Wrong argument when calling media.php'));
}


// Functions to implement
/*


// Put a path in there
-> browse_files

// Browse artists
-> browse_artist

// Browse_albums
-> browse_album

// l

*/


?>
