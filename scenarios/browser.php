<?php
header("Content-Type: text/plain"); 

define( 'ROOT_DIR', dirname(__FILE__) );

// DEBUUUUG
//function doLog($message) {
//	$log=false;
//	$print=false;
//	$trace=false;
//
//	if ($log) {
//		$filename = "log.txt";
//		$fh = fopen($filename, "a") or die("Could not open log file.");
//		fwrite($fh, date("d-m-Y, H:i")."\t: $text\n") or die("Could not write file!");
//		fclose($fh);
//	}
//	if ($print) {
//		echo "<pre>";
//		echo "$message";
//		echo "</pre>";
//	}
//	if ($trace) {
//		echo "<pre>";
//		debug_print_backtrace();
//		echo "</pre>";
//	}
//}


foreach ( $_POST as $key => $value) {
	doLog("POST -> ".$key.":".$value);
} 


//include('../getid3/getid3.php');
include('MysqliDb.php');
include('iniConfig.php');
include('../../ify.php');


// Config
//global $music_path;
$music_path = "/home/jez/fiji zik/";
$supported_format = array(".mp3",".ogg",".wav",".wma",".aac");

// Main tests
//browse_dir($music_path);
$action = (isset($_POST["action"])) ? $_POST["action"] : "null";
$action = (isset($_GET["action"])) ? $_GET["action"] : "null";
$args = (isset($_POST["args"])) ? $_POST["args"] : "null";

doLog('Lib called: '.$action . ' ' . $args);
switch ($action) {
    case "browse_dir":
	browse_dir( $args);
        break;
    case "browse_files":
	browse_files( $args);
        break;
    case "download":
	download ( $args);
        break;
    default:
	echo "Error! Mauvais arguemnt pour appeller le script.php!";
}


// Functions
///////////////

// Found here: http://www.php.net/manual/en/function.realpath.php#84012
    function get_absolute_path($path) {
        $path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
        $parts = array_filter(explode(DIRECTORY_SEPARATOR, $path), 'strlen');
        $absolutes = array();
        foreach ($parts as $part) {
            if ('.' == $part) continue;
            if ('..' == $part) {
                array_pop($absolutes);
            } else {
                $absolutes[] = $part;
            }
        }
        //return implode(DIRECTORY_SEPARATOR, $absolutes, DIRECTORY_SEPARATOR);
        return implode(DIRECTORY_SEPARATOR, $absolutes);
    }

// Check_jail: check if a path is not outside a jail
// Path must be absolutes !
function check_jail($root, $path) {

	if(substr($path, 0, strlen($root) ) != $root or !is_dir($path) ) {
		// Path out of jail
		return false;
	} else {
		// Path in jail
		return true;
	}
}


// Ify functions
////////////////

// Give a list of directory
function browse_dir($vpath)
{
	global $music_path;
	$html = "";
	//doLog('browse_dir() vpath = '.$vpath);


	// Security: Avoid browsing outside root directory, but allow to follow symlinks
	$path = $music_path . get_absolute_path($vpath);
	if( !check_jail($music_path, $path) ) {
		// If path outside jail, redifine default pass
		$path = $music_path;
		$vpath = "";
	}

	// DEBUG
	doLog('browse_dir() path  : '.$path);
	doLog('browse_dir() vpath : '.$vpath);


	// Parsing directories
	$array_dir = new ArrayObject( scandir( $path));
	$iterator = $array_dir->getIterator();

	while($iterator->valid()) {
		$dir = $iterator->current();
		// Check if path exists and different from . and .. (for root only)
		if (is_dir($path .DIRECTORY_SEPARATOR . $dir) and $dir != '.' and  !($dir == ".." and $path == $music_path ) ) {
			// Build HTML list
			$html = $html . '<tr><td><a href="#">' . $dir . '</a></td></tr>';
		}
	    $iterator->next();
	}


	// Build JSON array
	$answer = array(
		"path" => $vpath,
		"apath" => get_absolute_path($vpath),
		"results" => $iterator->count(),
		"html" => $html
	);
	
	echo json_encode($answer);

	// Debug
	//doLog('full response :' . implode(", ", $answer));
	//doLog('vpath de retour: ' . $vpath );

}

function browse_files($vpath)
{
	global $music_path;
	global $supported_format;
	$html = "";
	//doLog('browse_dir() vpath = '.$vpath);


	// Security: Avoid browsing outside root directory, but allow to follow symlinks
	$path = $music_path . get_absolute_path($vpath);
	if( !check_jail($music_path, $path) ) {
		// If path outside jail, redifine default pass
		$path = $music_path;
		$vpath = "";
	}

	// DEBUG
	doLog('browse_dir() path  : '.$path);
	doLog('browse_dir() vpath : '.$vpath);


	// Parsing directories
	$tags = array();
	$array_dir = new ArrayObject( scandir( $path));
	$iterator = $array_dir->getIterator();

	while($iterator->valid()) {
		$dir = $iterator->current();

		// Check if file exists and if it is not starting by a dot. Finally check extension
		if (is_file($path .DIRECTORY_SEPARATOR . $dir) and substr($dir,0,1) != '.' and in_array(strrchr($dir,'.'),$supported_format) ) {

			// Extract ID3
			$tag = music_info($path .DIRECTORY_SEPARATOR . $dir);
			$tags[] = $tag;

			// Build HTML list
			$html = $html . '<tr><td><a href="#">' . $tag['filename'] . '</a></td><td>'.$tag['artist'].'</td><td>'.$tag['title'].'</td><td>'.$tag['album'].'</td></tr>';


		}
	    $iterator->next();
	}


	// Build JSON array
	$answer = array(
		"path" => $vpath,
		"apath" => get_absolute_path($vpath),
		"results" => $iterator->count(),
		"html" => $html,
		"tags" => $tags
	);
	
	echo json_encode($answer);

	// Debug
	//doLog('full response :' . implode(", ", $answer));
	//doLog('vpath de retour: ' . $vpath );

}





?>

