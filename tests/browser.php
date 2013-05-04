<?php
header("Content-Type: text/plain"); 

// Initialising log file:
function doLog($text)
{
  // open log file
  $filename = "log.txt";
  $fh = fopen($filename, "a") or die("Could not open log file.");
  fwrite($fh, date("d-m-Y, H:i")." - $text\n") or die("Could not write file!");
  fclose($fh);
}


// Config
$music_path = "/home/jez/fiji zik/";


// Main tests
//browse_dir($music_path);
$action = (isset($_POST["action"])) ? $_POST["action"] : "null";
$args = (isset($_POST["args"])) ? $_POST["args"] : "null";

doLog('Lib called: '.$action . ' ' . $args);
switch ($action) {
    case "browse_dir":
	browse_dir($music_path . $args);
        break;
    case 1:
        echo "i égal 1";
        break;
    case 2:
        echo "i égal 2";
        break;
    default:
	echo "Error! Mauvais arguemnt pour appeller le script.php!";
}


if ($action) {
    // Faire quelque chose...
    //echo "OK";
} else {
    echo "FAIL";
}

// Functions
///////////////


// Give a list of directory
function browse_dir($path)
{
	
	doLog('browse_dir(): '.$path);

	$dir_list = scandir ( $path) ;

	$array_dir = new ArrayObject($dir_list);

	$iterator = $array_dir->getIterator();

	while($iterator->valid()) {
	    echo '<tr><td><a href="#">' . $iterator->current() . '</a></td></tr>';

	    $iterator->next();
	}

}



?>

