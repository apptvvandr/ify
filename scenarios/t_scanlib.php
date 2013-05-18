<?php

// Old unsuccessfull test with MysqliDb
include('MysqliDb.php');
//include('mysql.class.php');

include('iniConfig.php');
//include('browser.php');
include('../../ify.php');

//header("Content-type: text/plain");
flush(); @ob_flush();  ## make sure that all output is sent in real-time

set_time_limit ( 0 );

global $conf;
$conf = new ifyConfig('/var/www/ify/config.ini');
$conf->setUser("jez");


$path = $conf->getUser("path");

$test = new ifyDB;

$test->scanDir( $path[0] );
//$test->add( "/var/www/ify/tests/zik/Air/Moon Safari/05 - Talisman.mp3"  );

unset($test);

class ifyDB {

	private $_prefix;
	private $_db;

	public function __construct () {
		
		// Get global config
		global $conf;
		
		// Get table prefix, (TODO: load from config params)
		$_prefix = "";
		
		// Initialise MySQL connection
		//$this->_db = new db('mysql:host=localhost,dbname=ify', 'ify', 'OmVogOvCav8');
		$this->_db = new Mysqlidb('localhost' ,'ify', 'OmVogOvCav8', 'ify');
	}

	public function scanDir( $path ) {

	global $conf;

		// Check if the dir exists

		// Do a recursive scan
		$dir  = new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS);
		$files = new RecursiveIteratorIterator($dir, RecursiveIteratorIterator::LEAVES_ONLY);

		echo "Scanning: $path ...<br>";
		foreach ($files as $file) {
			$infos = pathinfo($file);

			// Check file extension is accepted
			//echo strrchr($file,'.') . " VS " . trim(strrchr($file,'.'), ".") . "</br>";
			if (in_array(strtolower(trim(strrchr($file,'.'), ".")), $conf->get('audio'), ".")) {
				$this->add($file);
			}
		}

	}

	public function add($file) {
		
		global $conf;
		$db = $this->_db;

		echo "<hr>";

		$infos = pathinfo($file);
		
		if (!in_array($infos['extension'], $conf->get('audio'))) {
			doLog("DEBUG: File $file is not allowed type, not adding this one to DB");
			return 1;
		}

		echo "Checking if file is not already added<br>";
		$checkParams = array ($infos['dirname'], $infos['basename']);
		$checkEntry = $db->rawQuery("SELECT * FROM `files` WHERE dir = ? and name = ?", $checkParams );
		if (empty($checkEntry)) {
			echo "Adding $file <br>";
		} else {
			doLog("INFO: the file $file is already in DB");
			return 1;
		}


		// Generate uniq ID and check if it does not already exists
		//doLog("DEBUG: Searching for an uniq ID. While loop running !");
		$i = 0;
		do {
			$id = rand(1,99999);
			$db->where('id', $id);
			$check = $db->get('files', 'id');
		} while ( !empty($check) );
		echo "Generating uid: $id<br>";
		
		echo "Getting id3 tags<br>";
		// Get id3 infos
		$tags = music_info($file);


		echo "Saving into DB<br>";
		// Generate uniq ID
		$insertData = array(
			'id'		=> $id,
			'dir'		=> $infos['dirname'],
			'name'		=> $infos['basename'],
			'tagTitle'	=> $tags['title'],
			'tagArtist'	=> $tags['artist'],
			'tagAlbum'	=> $tags['album'],
			'tagYear'	=> $tags['year'],
			'tagTrack'	=> $tags['track'],
			'tagGenre'	=> $tags['genre']
		);

		$db->insert('files', $insertData);
//		if($db->insert('files', $insertData)) {
//			echo 'FAIL! <br>';
//		} else {
//			echo 'success!<br>';
//		}

	}

}

?>



