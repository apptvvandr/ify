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
		$fullPath = $conf->getApp("root") . $path;
		$max = 10;

		// Check if it's a relative path or not
		//if ($path[0] != "/")
		//{
		//	//Absolute path
		//	$path = $conf->getApp('root').$path ;
		//}
			

		// Check if the dir exists
		if (!is_dir($fullPath))
			l("WARNING", "Directory '".$fullPath."' does not exists");

		// This can be very long:
		set_time_limit ( 0 );

		// Do a recursive scan
		$dir  = new RecursiveDirectoryIterator($fullPath, RecursiveDirectoryIterator::SKIP_DOTS);
		if ($conf->get("follow_sym"))
			$dir->setflags(RecursiveDirectoryIterator::FOLLOW_SYMLINKS);
		$files = new RecursiveIteratorIterator($dir, RecursiveIteratorIterator::LEAVES_ONLY);

		echo "Scanning: $fullPath ... <br>";
		$i = 0;
		foreach ($files as $file) {
			$i++;
			$infos = pathinfo($file);

			// Check file extension is accepted
			//echo strrchr($file,'.') . " VS " . trim(strrchr($file,'.'), ".") . "</br>";
			if (in_array(strtolower(trim(strrchr($file,'.'), ".")), $conf->get('audio'), ".")) {
				$this->addFileToDb($file);
				echo "Adding file" . $file. "<br>";
			}

			if ($max != 0 && $i > $max) 
				return 0;
		}
		echo "$i files found! <br>";

	}

	public function addFileToDB($file) {
		
		global $conf;
		static $jail;
		$prefix = strlen($conf->getApp('root') . DIRECTORY_SEPARATOR . $conf->get('jail_path'));
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
			'id'		=> IfyId(12),
			'tagTitle'	=> $tags['title'],
			'tagArtist'	=> $tags['artist'],
			'tagAlbum'	=> $tags['album'],
			'tagYear'	=> $tags['year'],
			'tagTrack'	=> $tags['track'],
			'tagGenre'	=> $tags['genre'],
			'fileDir'		=> substr($infos['dirname'], $prefix),
			'fileName'		=> $infos['basename'],
			'fileBitrate'	=> $tags['bitrate'],
			'fileLenght'	=> $tags['lenght']
		);
		l("INFO", "Infos pour $file", $insertData);

		return 0;
	//	$db->insert('files', $insertData);
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

		$result = $this->smartQuery($string, "all", "php");

		$return = array();
		foreach ($result as $line) {
			array_push($return, $line['tagArtist'], $line['tagAlbum'], $line['tagTitle']);
		}

		$return = array_unique($return, SORT_LOCALE_STRING);
		//var_dump($return);

		return json_encode(array_values($return));
	}

	public function smartQuery($string, $select="all", $output="php") {
		
		// Initialisation
		$where = "";
		$group = "";
		$limit = "";
		$group = "";
		$params = array ();

		// WHERE PART
		/////////////
		if (empty($string) || $string == "*") {
			// Select all elements coz no filter
			$where = "";
			$params = array();
		} else {
			// Determine statement type
			$result = $this->smartStatement($string);

			if ($result['status'] == 0) {
				// This is a smart query
				$where = $result['msg'];
				$params = $result['params'];
			} elseif ($result['status'] == 1) {
				// This is a smart query but not properly formatted
				$return = array(
					'status' => 1,
					'msg' => $result['msg']
					);
				return $return['msg'];
			} elseif ($result['status'] == 2 ){
				// This is not a smart query
				$result = $this->metaStatement($string);
				$where = $result['msg'];
				$params = $result['params'];
			} else {
				l("ERROR", "This search query is not possilbe: $tring");
			}
		}
		if (!empty($where)) $where = "WHERE ".$where;


		// SELECT part
		//////////////
		switch ($select) {
			case "all":
				$select = "id, tagArtist, tagTitle, tagAlbum, tagGenre, tagYear";
				$limit = " LIMIT 30";
				break;
			case "search":
				$select = "tagArtist, tagTitle, tagAlbum";
				$limit = " LIMIT 30";
				break;
			case "artists":
				$select = "tagArtist, COUNT(tagArtist)";
				$group = "tagArtist";
				break;
			case "albums":
				$select = "tagAlbum, COUNT(tagAlbum)";
				$group = "tagAlbum";
				break;
			case "genre":
				$select = "tagGenre, COUNT(tagGenre)";
				$group = "tagGenre";
				break;
			case "year":
				$select = "tagYear, COUNT(tagYear)";
				$group = "tagYear";
				break;
			case "titles":
				$select = "tagTitle";
				$limit = "";
				break;
			case "count":
				$select = "COUNT(id)";
				$limit = "";
				break;
			default:
				$select = "id, tagArtist, tagTitle, tagAlbum, tagGenre";
				$limit = " LIMIT 30";
		}
		$select = "SELECT ".$select;
		if (!empty($group)) $group = "GROUP BY ".$group;
		$limit = "";


		// Database Query
		/////////////////
		$query = $select." FROM `files` ".$where.$limit.$group;

		//echo "<pre>";
		//var_dump($query);
		//var_dump($params);
		//echo "</pre>";

		// Execute the request
		$db = $this->_db;
		if (empty($params)) {
			$result = $db->query($query);
		} else {
			$result = $db->rawQuery($query, $params);
		}

		// Make the output
		//////////////////
		//print_r($result);
		if ($output == "php") {
			return $result;
		} elseif ($output == "json") {
			// Return 2D array
			return json_encode($result);
		} elseif ($output == "json-list") {
			// return 1D array
			return json_encode($result);
		} elseif ($output == "html-table") {
			// Return 2D array
			$result = array_values($result);

			$html = "";
			foreach ($result as $line) {
				$line = implode("</td><td>",array_values($line));
				$html .= "<tr><td>".$line."</td></tr>";
			}

			return $html;
		} elseif ($output == 'html-list') {
			// Return 1D li
			$return = array();
			foreach ($result as $line){
				array_push($return,implode(', ', $line));
			}
			return '<li>'.implode("</li><li>", $return).'</li>';
		}


	}

	private function metaStatement($string) {
		// Analyse user search query
		
		$result = array();
		$db = $this->_db;
		if ($string == '*' || $string == '%') {
			$where = "";
			$string = "";
			$params = array();
		} else {
			$where = "tagArtist LIKE ? OR tagTitle LIKE ? OR tagAlbum LIKE ?";
			$string = "%".$string."%";
			$params = array($string, $string, $string);
		}

		$result = array(
			'status'	=> 0,			// OK, everything's good
			'msg'		=> $where,		// WHERE query to inject
			'params'	=> $params		// Parameters in array
			);

		return $result;
	}

	private function smartStatement($string) {

		//Simplified regex
		// ([patgyln]) ([=:><!]) (("[^"]*")|('[^']*')|([^/s]*))
        // ^key        ^op       ^value

		// Define variables
		$query = "";
		$result = array (0, "");
		$match = array();
		$regex = '/([patgyln])((<=)|(>=)|(!=)|[=:><!\]\[])(("[^"]*")|(\'[^\']*\')|([^\s]*))/';

		preg_match($regex, $string, $match);
		
		#echo "Raw match: <pre>";
		#var_dump($match);
		#echo "</pre>";

		/*
		Be aware that all indexes directly depends of the regex.
		If you want to implement new fields, you may have to change
		them in way get the correct fields. The var dump over may help
		you to identify witch index is corresponding to you match ;-)
		*/
		if (count($match) > 6) {
			$key = $match[1];
			$op = $match[2];
			$value = $match[6];
			//l("INFO","Smart statement detecte: $key $op $value OU ".implode($match));
		} else {
			//l("DEBUG","Not a smart statement: ".$string);
			$result = array('status' => 2, 'msg' => "IQL: Not a smart query: ".$string);
			return $result;
		}


		// Query builder
		switch ($key) {
			case "p":
				$query .= "tagArtist";
				break;
			case "a":
				$query .= "tagAlbum";
				break;
			case "t":
				$query .= "tagTitle";
				break;
			case "g":
				$query .= "tagGenre";
				break;
			case "y":
				$query .= "tagYear";
				break;
			case "l":
				$query .= "tagLenght";
				break;
			case "n":
				$query .= "tagTrack";
				break;
			default:
				$result = array("status" =>1, 'msg' => "IQL: key '".$key."' is not recognised!");
				return $result;
		}

		if (is_int(strpos("patg",$key))) {
			// this is a string
			switch ($op) {
				case '!=':
					$query .= ' !=';
					break;
				case '=':
					$query .= ' =';
					break;
				case ':':
					$query .= ' LIKE';
					break;
				case '!':
					$query .= ' NOT LIKE';
					break;
				default:
					$result = array(
						'status' => 1, 
						'msg' => "IQL: Operator '".$op."' is not available for '".$key."' string type"
					);
					return $result;
			}
		} elseif (is_int(strpos("yln",$key))) {
			switch ($op) {
				case '!=':
					$query .= ' !=';
					break;
				case '=':
					$query .= ' =';
					break;
				case '<':
					$query .= ' <';
					break;
				case '>':
					$query .= ' >';
					break;
				case '<=':
					$query .= ' <=';
					break;
				case '>=':
					$query .= ' >=';
					break;
				case '[':
					$query .= ' BETWEEN';
					break;
				case ']':
					$query .= ' NOT BETWEEN';
					break;
				default:
					$result = array(
						'status' => 1, 
						'msg' =>  "IQL: Operator '".$op."' is not available for '".$key."' number type"
					);
					return $result;
			}
		} else {
			$result = array(
				'status' => 1, 
				'msg' => "IQL: Operator '".$op."' is not recognised"
			);
			return $result;
		}

		//var_dump($key);
		//echo "<br>";


		// Checking values
		
		// Looking for a number, or a string or a <num>:<num>
		$params = array();

		if (is_int(strpos("patg",$key))) {
			// Looking for a string, and clean it
			$regex='/^(\'|")|(\'|")$/';
			$value = preg_replace($regex, "", $value);
			$query .= ' ?';
			$params[0] = "%".$value."%";

		} elseif (!is_int(strpos("[]",$op))) {
			// Looking for a number
			$regex = "/^(\d)+$/";
			$match = preg_match($regex, $value);

			if (count($match) != 1) {
				$result = array(
					'status' =>1,
					'msg' => "IQL: '".$value."' is not a number"
				);
				return $result;
			} else {
				$query .= ' ?';
				$params[0] = $value;
			}

		} elseif (is_int(strpos("[]",$op))) {
			// Looking for interval
			// <num>:<num>

			$regex = "/^(\d+):(\d+)$/";
			$match = array ();
			$count = preg_match($regex, $value, $match);

			if (count($match) != 3 ) {
				$result = array(
					'status' => 1,
					'msg' => "IQL: '".$value."' is not an interval"
				);
				return $result;
			} elseif ( $match[1] >= $match[2]) {
				$result = array(
					'status' => 1, 
					'msg' => "IQL: First number cannot be bigger than second number"
				);
				return $result;
			} else {
				//$query .= ' '.$match[1].' AND '.$match[2];
				$query .= ' ? AND ?';
				$params[0] = $match[1];
				$params[1] = $match[2];
			}
		} else {
			// Not possible body
			$result = array(
				'status' => 1,
				'msg' => "IQL: '".$value."' is not an acceptable value"
			);
			return $result;
		}

		// We finaly succeed all tests, we built the statement
		$result = array(
			'status' => 0,
			'msg' => $query,
			'params' => $params
		);
		return $result;
	}
}


/*

	#
	# Ify Query Language Documentation
	##################################
	
	1) General syntax
	=================
	
	Regex: '/([patgyln])((<=)|(>=)|(!=)|[=:><!\]\[])(("[^"]*")|(\'[^\']*\')|([^\s]*))/';
	Match: 1,2 and 6 for <field>, <operator> and <value>
	

	2) Statements
	=============
	<field><operator><value>

	Where <field> can be:
		p: Artist (string)
		a: Album (string)
		t: Title (string)
		g: Genre (string)
		y: Year (number)
		l: Lenght (number)
		t: Track number (number)

	Where <operator> can be:
		valid for strings:
			:		: Like
			=		: Strictly equal
			!		: Not like
			!=		: Strictly not equal
		valid for numbers:
			=		: Strictly equal
			!=		: Strictly not equal
			>, >=	: Lesser or strictly lesser than
			<, <=	: Bigger or strictly bigger than
			]		: Included between, <value> must be an interval
			[		: Excluded outside, <value> must be an interval

	Where <value> can be:
		valid for (string):
			<str>	: Any string
			'<str>'	: Any string
			"<str>"	: Any string
		valid for (number)
			<num>	: Any positive number
		valid for (interval):
			<num>:<num>	: Two positive number separated by a semicolon (:)

	3) Parsing methods:
	===================


*/

?>



