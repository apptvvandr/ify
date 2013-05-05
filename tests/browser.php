<?php
header("Content-Type: text/plain"); 

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


// Main tests
//browse_dir($music_path);
$action = (isset($_POST["action"])) ? $_POST["action"] : "null";
$args = (isset($_POST["args"])) ? $_POST["args"] : "null";

doLog('Lib called: '.$action . ' ' . $args);
switch ($action) {
    case "browse_dir":
	browse_dir( $args);
        break;
    case "browse_dir2":
	browse_dir( $args);
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
			$html = $html . '<tr><td><a href="#">' . $iterator->current() . '</a></td></tr>';
		}
	    $iterator->next();
	}


	// Build JSON array
	$answer = array(
		"path" => $vpath,
		"results" => $iterator->count(),
		"html" => $html
	);
	
	echo json_encode($answer);

	// Debug
	//doLog('full response :' . implode(", ", $answer));
	//doLog('vpath de retour: ' . $vpath );

}


// Giv a list of audio files in a directory
function dir_list($path) {
	doLog('dir_list(): '.$path);

        $files = scandir ( $path) ;

        $array_dir = new ArrayObject($files);

        $iterator = $array_dir->getIterator();

	//is_dir()
	

}


?>

