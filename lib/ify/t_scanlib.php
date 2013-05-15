<?php
include('MysqliDb.php');

$test = new ifyDB;

$test->scanDir( "/var/www/ify/tests/zik/" );



class ifyDB {

public function __construct () {
	// Initialise MySQL connection
	$db = new Mysqlidb('localhost', 'ify', 'OmVogOvCav8', 'ify');
}

public function scanDir( $path ) {

	// Check if the dir exists

	// Do a recursive scan

	//echo $path;
	$dir  = new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS);
	$files = new RecursiveIteratorIterator($dir, RecursiveIteratorIterator::LEAVES_ONLY);

echo "[$path]<br>";
foreach ($files as $file) {
    $indent = str_repeat('   ', $files->getDepth());
    echo $indent, " ├ $file<br>";
}

}


}

?>



