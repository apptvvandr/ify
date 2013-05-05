<?php
header("Content-Type: text/plain"); 

//require_once('/vlib/getid3/getid3.php');
include('../getid3/getid3.php');

define( 'ROOT_DIR', dirname(__FILE__) );

// DEBUUUUG
function doLog($text)
{
  // open log file
  $filename = "log.txt";
  $fh = fopen($filename, "a") or die("Could not open log file.");
  fwrite($fh, date("d-m-Y, H:i")." - $text\n") or die("Could not write file!");
  fclose($fh);
}


foreach ( $_POST as $key => $value) {
	doLog("POST -> ".$key.":".$value);
} 



// Config
//global $music_path;
$music_path = "/home/jez/fiji zik/";
$supported_format = array(".mp3",".ogg",".wav",".wma",".aac");

// Main tests
//browse_dir($music_path);
$action = (isset($_POST["action"])) ? $_POST["action"] : "null";
$args = (isset($_POST["args"])) ? $_POST["args"] : "null";

doLog('Lib called: '.$action . ' ' . $args);
switch ($action) {
    case "browse_dir":
	browse_dir( $args);
        break;
    case "browse_files":
	browse_files( $args);
        break;
    case 2:
        echo "i égal 2";
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

// Get ID3 informations
function music_info($file) {

	// Create object
	$getID3 = new getID3;

	// Create an array with all extract infos
	$id3 = $getID3->analyze($file);

	// Create an array with best id3 meta available
	getid3_lib::CopyTagsToComments($id3);

	// Extract needed infos for Ify
	$tags = array (
		"filename"	=> (isset($id3['filename']) ? $id3['filename'] : "-"),
		"title"		=> (isset($id3['comments_html']['title'][0]) ? $id3['comments_html']['title'][0] : "-"),
		"artist"	=> (isset($id3['comments_html']['artist'][0]) ? $id3['comments_html']['artist'][0] : "-"),
		"album"		=> (isset($id3['comments_html']['album'][0]) ? $id3['comments_html']['album'][0] : "-"),
		"year"		=> (isset($id3['comments_html']['year'][0]) ? $id3['comments_html']['year'][0] : "-"),
		"track"		=> (isset($id3['comments_html']['track'][0]) ? $id3['comments_html']['track'][0] : "-"),
		"genre"		=> (isset($id3['comments_html']['genre'][0]) ? $id3['comments_html']['genre'][0] : "-"),
		"length"	=> (isset($id3['playtime_string']) ? $id3['playtime_string'] : "-"),
		"bitrate"	=> (isset($id3['audio']['bitrate']) ? $id3['audio']['bitrate'] : "-"),
		"format"	=> (isset($id3['audio']['dataformat']) ? $id3['audio']['dataformat'] : "-")
	);
	return $tags;
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

