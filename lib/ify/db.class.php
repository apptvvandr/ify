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
		$max = 1000;

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
				//echo "Adding file " . $file. "<br>";
				$this->addFileToDb($file);
			}

			if ($max != 0 && $i > $max) 
				return 0;
		}
		echo "$i files found! <br>";

	}

	public function addFileToDB($path) {

		global $conf;

		static $absJail;
		$absJail = $conf->getApp('root') . DIRECTORY_SEPARATOR . $conf->get('jail_path');

		$file = pathinfo($path);
		$relPath = substr($file['dirname'], strlen($absJail));
		$fileName = $file['basename'];

		// Needed variables
		// $absJail: The absolute path of root music directory of the user (the Jail), static
		// $path: Absolute music path with filename.
		// $relPath: The relative directory path of the song from the Jail.
		// $fileName: Full file name of the file, with extension :)

		$db = $this->_db;
	//echo "<hr>";
		
		// Check this kind of file is allowed
		if (!in_array($file['extension'], $conf->get('audio'))) {
			l("DEBUG", "File $file is not allowed type, not adding this one to DB");
			return 1;
		}

	//echo "Checking if file is not already added<br>";
		// Check if the file is not already in DB
		$checkParams = array ($relPath, $file['basename']);
		$checkEntry = $db->rawQuery("SELECT * FROM `files` WHERE fileDir = ? and fileName = ?", $checkParams );
		if (empty($checkEntry)) {
			//echo "Adding $file , ". var_dump($checkEntry) . " <br>";
		} else {
			doLog("INFO", "The file $file is already in DB");
			return 2;
		}


	//doLog("DEBUG: Searching for an uniq ID. While loop running !");
		// Generate uniq ID and check if it does not already exists
		$i = 0;
		do {
			$id = IfyId(12);
			$db->where('id', $id);
			$check = $db->get('files', 'id');
		} while ( !empty($check) );
	//echo "Generating uid: $id<br>";
		
	//echo "Getting id3 tags<br>";
		// Get id3 infos
		$tags = music_info($path);


	//echo "Saving into DB<br>";
		// Create array to save in DB
		$insertData = array(
			'id'		=> $id,
			'lib'		=> $conf->getUser('login'),
			'tagTitle'	=> $tags['title'],
			'tagArtist'	=> $tags['artist'],
			'tagAlbum'	=> $tags['album'],
			'tagYear'	=> $tags['year'],
			'tagTrack'	=> $tags['track'],
			'tagGenre'	=> $tags['genre'],
			'fileDir'		=> $relPath,
			'fileName'		=> $file['basename'],
			'fileBitrate'	=> $tags['bitrate'],
			'fileLenght'	=> $tags['lenght']
		);
	//l("INFO", "Infos pour $file", $insertData);

		// Import in DB and check if everything's OK
		if($db->insert('files', $insertData)) {
			echo 'FAIL! <br>';
			doLog("WARNING", "Couldn't insert $file informations in DB");
			return 3;
		} else {
			return 0;
		}

	}

	//Return the full file path from DB
	public function getFilePath($id) {
		global $conf;
		$db = $this->_db;

		//echo "teststst";
		//$db->where('id', $id);
		//$result = $db->get('files', 'id');
		$result = $db->rawQuery("SELECT fileDir, fileName FROM `files` WHERE id = ? ", array($id));

		if (count($result) == 1) {
			// Object found, make the path
			$result = $result[0];
			return $conf->getApp('root') . DIRECTORY_SEPARATOR . $conf->get('jail_path') . $result['fileDir'] . DIRECTORY_SEPARATOR  . $result['fileName'];
		} elseif (count($result) > 1) {
			// Duplicate entry, something's really wrong in DB :/
			doLog("ERROR", "Duplicate entry in DB for id $id. Something's really wrong in your DB :/");
			return 2;
		} else {
			// Nothing found
			echo "FAILLL";
			doLog("INFO", "No  entry in DB for id $id");
			return 1;
		}

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
			$result = $this->buildSentence($string);
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
			} else {
				l("ERROR", "This search query is not possible: $string");
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


	function buildSentence($string) {


		// Counts number of parenteheses
		$count = 0;
		$arrayOfString = str_split($string);
		foreach ($arrayOfString as $char) {
			if ($char == '(')
				$count++;
			elseif ($char == ')')
				$count--;
			if ($count < 0)
				break;
		}

		// Exits if parentheses are wrong
		if ($count < 0 ) {
			$result = array(
				'status' => 1, 
				'msg' =>  "IQL: Missing ".-$count." opening parenthese(s)"
			);
			return $result;
		}
		elseif ($count > 0) {
			$result = array(
				'status' => 1, 
				'msg' =>  "IQL: Missing ".$count." closing parenthese(s)"
			);
			return $result;
		}


		// Delete spaces in parentheses
		$string = preg_replace('!\s*\(\s*!', ' ( ', $string);
		$string = preg_replace('!\s*\)\s*!', ' ) ', $string);
		// Delete duplicate spaces
		$string = preg_replace('!\s+!', ' ', $string);
		// Delete spaces at the beginning and the end of the string
		$string = preg_replace('!(^\s+)|(\s+$)!', '', $string);

		// Parsing sentence

		/*
		This regex matches these strings: AND, OR, (, ), <smartStmt>, <generalStmt>
		*/
		$regex = '!(AND|OR|\(|\)|([^\s\(\)]{1,3}\"[^\"]*\")|([^\s\(\)]{1,3}\'[^\']*\')|[^\s]*)*!';
		$match = array();

	
		preg_match_all ( $regex, $string, $match );
		$match = $match[0];

		$result = array() ;
		$params = array() ;
		foreach ($match as $word) {

			if (in_array($word, array('', ' ', 'AND', 'OR', '(', ')')) == false) {
				$stmt = $this->buildStatement($word);

				if ($stmt['status'] == 0 ) {
					array_push($result, $stmt['msg']);
					$params = array_merge($params, $stmt['params']);
				}
			} elseif (empty($word)) {
				// Empty lines not pushed
			} elseif (in_array($word, array('AND', 'OR', '(', ')')) != false) {
				array_push($result, $word);
			} else {
				l("ERROR", "This caracter is not allowed: $word");
			}

		}	

		$result = array(
			'status' => 0, 
			'msg' =>  implode( ' ', $result),
			'params' => $params
		);
		return $result;

	}

	private function buildStatement($string) {

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
			//Build a query which will search a string in any tables

			$result = array();
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



