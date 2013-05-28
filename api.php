<?php
// Main API file to get content from DB

include ("ify.php");

// _POST Management
$function = (isset($_POST["f"])) ? $_POST["f"] : "null";
$args = (isset($_POST["a"])) ? $_POST["a"] : "null";

// _GET Management
$function = (isset($_GET["f"])) ? $_GET["f"] : "null";
$args = (isset($_GET["a"])) ? $_GET["a"] : "null";

// Developement options
///////////////////////

// Define user context
global $conf;
$conf = new ifyConfig("config.ini");
$conf->setUser("jez");

// Initialise DB backend


// Action to do
switch ( $function) {
	case "userSearch":
		userSearch( $args);
		break;
	case "uiHTMLTable":
		uiHTMLTable( $args);
		break;
    default:
		echo "Wrong parameter to call this file";
}

function userSearch($string) {
	$db = new ifyDB;
	$result = $db->userSearch($string);
	echo $result;
}

function uiHTMLTable($string) {
	$db = new ifyDB;
	$result = $db->smartQuery($string, 'all', 'html-table');
	echo $result;
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
