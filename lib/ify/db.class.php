<?php


// THIS CLASS HAS TO BE INJECTION PROOF!!!

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
		$max = 10;

		// Check if it's a relative path or not
		if ($path[0] != "/")
		{
			//Absolute path
			$path = $conf->getApp('root').$path ;
		}
			

		// Check if the dir exists
		if (!is_dir($path))
			l("WARNING", "Directory '".$path."' does not exists");

		// This can be very long:
		set_time_limit ( 0 );

		// Do a recursive scan
		$dir  = new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS);
		if ($conf->get("follow_sym"))
			$dir->setflags(RecursiveDirectoryIterator::FOLLOW_SYMLINKS);
		$files = new RecursiveIteratorIterator($dir, RecursiveIteratorIterator::LEAVES_ONLY);

		$num = count($files);
		echo "Scanning: $path ... <br>$num files found! <br>";
		$i = 0;
		foreach ($files as $file) {
			$i++;
			$infos = pathinfo($file);

			// Check file extension is accepted
			//echo strrchr($file,'.') . " VS " . trim(strrchr($file,'.'), ".") . "</br>";
			if (in_array(strtolower(trim(strrchr($file,'.'), ".")), $conf->get('audio'), ".")) {
				$this->add($file);
			}

			if ($i > $max) 
				return 0;
		}

	}

	public function add($file) {
		
		global $conf;
		$db = $this->_db;

	//echo "<hr>";

		$infos = pathinfo($file);
		
		if (!in_array($infos['extension'], $conf->get('audio'))) {
			l("DEBUG", "File $file is not allowed type, not adding this one to DB");
			return 1;
		}

	//echo "Checking if file is not already added<br>";
		$checkParams = array ($infos['dirname'], $infos['basename']);
		$checkEntry = $db->rawQuery("SELECT * FROM `files` WHERE dir = ? and name = ?", $checkParams );
		if (empty($checkEntry)) {
	//echo "Adding $file <br>";
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
	//echo "Generating uid: $id<br>";
		
	//echo "Getting id3 tags<br>";
		// Get id3 infos
		$tags = music_info($file);


	//echo "Saving into DB<br>";
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


	public function query($columns = "*", $where = "", $order = "tagTitle", $limit = "0, 50", $group = "") {
		
		$db = $this->_db;

		//filter => where, and or
		//what => select *, list, number of results

		// Query template
		$query = "SELECT $columns FROM `files` WHERE $where GROUP BY $group ORDER BY $order LIMIT $limit";
		$query = "SELECT";


		//$params = array ($columns, $where, $group, $order, $limit);
		$params = array ( "tagArtist LIKE The Black eyepeas");

		//l ("Colums are", $columns);
		l ("Params are", $params);

		

		$result = $db->rawQuery('SELECT '.$columns.' FROM `files`  WHERE ? LIMIT 50', $params);
		//l ("INFO", "Result are", $result);

		return $result;

	}

	public function userSearch($string) {
		// Analyse user search query
		
		$db = $this->_db;

		// Build the request
		$start = "SELECT tagArtist, tagTitle, tagAlbum, tagGenre FROM `files` WHERE ";
		$where = "tagArtist LIKE ? OR tagTitle LIKE ? OR tagAlbum LIKE ?";
		$limit = " LIMIT 20";

		$query = $start . $where .$limit;
		//l("INFOS", "Request is: ".$start.$where.' LIMIT 50');

		// Execute the request
		$params = array($string, $string, $string);
		$result = $db->rawQuery($start.$where.' LIMIT 50', $params);

		return $result;

	}
}








?>



