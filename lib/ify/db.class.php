<?php


// THIS CLASS HAS TO BE INJECTION PROOF!!!

class ifyDB {

	private $_prefix;
	private $_db;
	private $_conf;

	public function __construct (&$conf) {
		
		// Get global config
		$this->_conf = $conf;
		$this->_db = $conf->getMySQLObject();
	}

	public function scanDir( $path ) {

		$conf = $this->_conf;
		$fullPath = $conf->getConst("root") . $path;
		$max = 1000;

		// Check if it's a relative path or not
		//if ($path[0] != "/")
		//{
		//	//Absolute path
		//	$path = $conf->getConst('root').$path ;
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

		$conf = $this->_conf;

		static $absJail;
		$absJail = $conf->getConst('root') . DIRECTORY_SEPARATOR . $conf->get('jail_path');

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
		$conf = $this->_conf;
		$db = $this->_db;

		//echo "teststst";
		//$db->where('id', $id);
		//$result = $db->get('files', 'id');
		$result = $db->rawQuery("SELECT fileDir, fileName FROM `files` WHERE id = ? ", array($id));

		if (count($result) == 1) {
			// Object found, make the path
			$result = $result[0];
			return $conf->getConst('root') . DIRECTORY_SEPARATOR . $conf->get('jail_path') . $result['fileDir'] . DIRECTORY_SEPARATOR  . $result['fileName'];
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

	// this function is ugly and simple function which does a basic search
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


	// This function is a method to query DB from IQL to MySQL
	public function smartQuery($filter, $columns="all", $group="", $limit="", $order="") {
		
		// Initialisation
		$limit = "";


		// WHERE PART (filter)
		/////////////
		$params = array();
		$where = array();

		if (empty($filter) || $filter == '*' || $filter == 'all') {
			// Select all elements coz no filter
			$where = '';
			$params = array();
		} else {
			// Determine statement type
			$result = $this->buildSentence($filter);
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
				l("ERROR", "This search query is not possible: $filter");
			}
		}
#var_dump($params);
#var_dump($where);
		if (!empty($where)) {
			$where = ' WHERE ' . $where;
		} else { 
			$where = '';
		}


		// SELECT part (columns)
		//////////////
		$select = array();
		if (empty($columns) || $columns == '*') {
			$columns = 'all';
		}
		$columns = explode(' ', $columns);
		foreach ($columns as $value) {

			switch ($value) {
				case "all":
					array_push($select, 'id', 'tagArtist', 'tagTitle', 'tagAlbum', 'tagGenre', 'tagYear');
					$limit = " LIMIT 30";
					break;

				case "artist":
					array_push($select, 'tagArtist');
					break;
				case "count-artist":
					array_push($select, 'COUNT(DISTINCT(tagArtist))');
					break;

				case "album":
					array_push($select, 'tagAlbum');
					break;
				case "count-album":
					array_push($select, 'COUNT(DISTINCT(tagAlbum))');
					break;

				case "genre":
					array_push($select, 'tagGenre');
					break;
				case "count-genre":
					array_push($select, 'COUNT(DISTINCT(tagGenre))');
					break;

				case "title":
					array_push($select, 'tagTitle');
					break;
				case "count-title":
					array_push($select, 'COUNT(tagTitle)');
					break;

				case "year":
					array_push($select, 'tagGenre');
					break;

				//default:
				//	array_push($select, 'id', 'tagArtist', 'tagTitle', 'tagAlbum', 'tagGenre');
				//	$limit = " LIMIT 30";
			}
		}
		$select = "SELECT " . implode(', ', $select);


		// GROUP BY part (group)
		////////////////////////
		switch ($group) {
			case 'artist';
				$group = '`tagArtist`';
				break;
			case 'album';
				$group = '`tagAlbum`';
				break;
			case 'title';
				$group = '`tagTitle`';
				break;
			case 'year';
				$group = '`tagYear`';
				break;
			case 'genre';
				$group = '`tagGenre`';
				break;
			default;
				$group = '';
		}
		if (!empty($group)) {
			$group = " GROUP BY " . $group;
		}


		// LIMIT part (limit)
		/////////////
		// (not implemented yet)
		$limit = '';


		// Database Query
		/////////////////
		$query = $select." FROM `files`".$where.$limit.$group;

//		echo "<pre>";
//		var_dump($query);
//		var_dump($params);
//		echo "</pre>";

		// Execute the request
		$db = $this->_db;
		if (empty($params)) {
			$result = $db->query($query);
		} else {
			$result = $db->rawQuery($query, $params);
		}

		// Return a standard 2D array
		return $result;
	}

	// This function identifies statements and build a complete MySQL request
	function buildSentence($string) {

#echo "<pre>";
#var_dump($string);
#echo "</pre>";

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

		// Exit if parentheses are wrong
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
		$regex = '!(AND|OR|\(|\)|([^\s\(\)]{1,3}\"[^\"]*\")|([^\s\(\)]{1,3}\'[^\']*\')|(\"[^\"]*\")|(\'[^\']*\')|[^\s]*)*!';
		$match = array();

	
		preg_match_all ( $regex, $string, $match );
		$match = $match[0];

#echo "<pre>";
#var_dump($string);
#var_dump($match);
#echo "</pre>";
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


	// This function analyses one statement and convert it to MySQL syntax
	private function buildStatement($string) {
//echo "buildStatement executed";

		//Simplified regex
		// ([patgyln]) ([=:><!]) (("[^"]*")|('[^']*')|([^/s]*))
        // ^key        ^op       ^value

		// Define variables
		$query = "";
		$result = array (0, "");
		$match = array();
		$regex = '/([abtgyln])((<=)|(>=)|(!=)|[=:><!\]\[])(("[^\"]*")|(\'[^\']*\')|([^\s]*))/';

#echo "<pre> string";
#var_dump($string);
#echo "</pre>";
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
#echo "<pre>";
#var_dump($match);
#echo "</pre>";
		if (count($match) > 6) {
			$key = $match[1];
			$op = $match[2];
			$value = $match[6];
//l("INFO","Smart statement detecte: $key $op $value OU ".implode($match, ' '));
		} else {
			//Build a query which will search a string in any tables

			$result = array();


			$regex='/(\'|\")*"/';
			$string = preg_replace($regex, "", $string);
var_dump($string);

			if (empty($string) || $string == '*' || $string == '%' || $string == 'all') {
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
			case "a":
				$query .= "tagArtist";
				break;
			case "b":
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
			$regex='/^(\'*|"*)|(\'|")$/';
			$value = preg_replace($regex, "", $value);
			$query .= ' ?';
#echo "<pre>";
#var_dump($value);
#echo "</pre>";
			if (empty($value) || $value == 'all' || $value == '*') {
				$params[0] = "%";
			} else {
				$params[0] = "%".$value."%";
			}

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
			// Not possible body, error case
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
#echo "<pre>";
#var_dump($result);
#echo "</pre>";
		return $result;
	}



	// This function helps to convert IQLv2 to SQL syntax
	public function IQLParse($string) {
		

		$query = array();

		// Extract words from string
		# http://stackoverflow.com/questions/2202435/php-explode-the-string-but-treat-words-in-quotes-as-a-single-word
		preg_match_all('/"(?:\\\\.|[^\\\\"])*"|\S+/', $string, $query);
		$query = $query[0];


		// Counts number of parenteheses
		$count = 0;
		foreach ($query as $value) {
			if ($value == '('){
				$count++;
			} elseif ($value == ')') {
				$count--;
			}
			if ($count < 0) {
				break;
			}
		}
		if ($count > 0) {
			$result = array("status" =>1, 'msg' => "IQL: Too many opening parenthesis!");
			return $result;
		} elseif ($count < 0) {
			$result = array("status" =>1, 'msg' => "IQL: Missing opening parenthesis!");
			return $result;
		}

		// Syntax Mapping
		$operator = array(
			'!='		=>	array('string', '!=', 'string'),
			'='			=>	array('string', '=', 'string'),
			'LIKE'		=>	array('string', 'LIKE', 'string'),
			'NOT LIKE'	=>	array('string', 'NOT LIKE', 'string'),
			'<='		=>	array('int', '<=', 'int'),
			'>='		=>	array('int', '>=', 'int'),
			'<'			=>	array('int', '<', 'int'),
			'>'			=>	array('int', '>', 'int'),
			'['			=>	array('int', 'BETWEEN', 'int', 'AND', 'int'),
			']'			=>	array('int', 'NOT BETWEEN', 'int', 'AND', 'int')
		);
		$field = array(
			'ARTIST'	=>	array(
							'type'	=>	'string',
							'sql'		=>	'tagArtist'
							),
			'ALBUM'		=>	array(
							'type'	=>	'string',
							'sql'		=>	'tagAlbum'
							),
			'TITLE'		=>	array(
							'type'	=>	'string',
							'sql'		=>	'tagTitle'
							),
			'GENRE'		=>	array(
							'type'	=>	'string',
							'sql'		=>	'tagGenre'
							),
			'YEAR'		=>	array(
							'type'	=>	'int',
							'sql'		=>	'tagYear'
							),
			'LENGHT'	=>	array(
							'type'	=>	'int',
							'sql'		=>	'tagLenght'
							),
			'TRACK'		=>	array(
							'type'	=>	'int',
							'sql'		=>	'tagTrack'
							),
			'-?[0-9]+'		=>	array(
							'type'	=>	'int',
							'sql'		=>	''
							),
			'.*'		=>	array(
							'type'	=>	'string',
							'sql'		=>	''
							)
		);

		$sentence = array();

		foreach ( $query as $value) {
			array_push($sentence, array(
				'operator'		=>	array_key_exists($string, $operator),
				'field'			=>	array_key_exists($string, $field),
				'int'			=>	preg_match('/-?[0-9]+/', $value) || false,
				'string'		=>	$value
				));
		}


		// Detect operators
		for ($i = 0; $i < count($query); $i++) {
			

echo "<pre>";
var_dump($query[$i]);
echo "TEST";
var_dump($operator);
echo "</pre>";

			// Check of the key is an operator
			if (array_key_exists($query[$i], $operator)) {
				// Map to the existing scheme

				echo "OKKKK JEZ";
				$sheme = $operator[$query[$i]];

				for ($j=0; $j < count($operator[$query[$i]]); $j++) {
					$k = $i -1;
					$word = $query[$k];
					$pattern = $scheme($j);

echo "<pre>";
var_dump($word);
var_dump($pattern);
echo "</pre>";
				}








#		// Detect operators
#		for ($i = 0; $i < count($query); $i++) {
#			$string = $query[$i];
#			$expression = '';
#			if (array_key_exists($string, $operator)) {
#				// This string is an otherator
#				$sheme = $operator[$string];
#
#				// Rule: Only one argument before operator, as much as you want space separated argument after operator
#				for ($j = 0; $j < count($sheme); $j++){
#						} 
#					// Analyse the previous element
#					if (isset($query[$i - 1 + $j])){
#						if ( array_key_exists($query[$i - 1 + $j], $field) ){
#							// Previous value is a field \o/
#							$expression = $query[$i--];
#						} 
#
#
#						// Analyse the field type
#						if (array_key_exists($query[$i--], $field)) {
#							// Previous value is a field \o/
#							$expression = $query[$i--];
#						} elseif (preg_match('/-?[0-9]+/')) {
#							$query[$i--]
#							// Previous value is a number \o/
#							$expression = $query[$i--];
#						} else {
#							// Previous value is obviously a string \o/
#							$expression = $query[$i--];
#						}
#					} else {
#						$result = array("status" =>1, 'msg' => "IQL: Missing first field for '$string' operator!");
#						return $result;
#					}


echo "<pre>";
var_dump($string);
var_dump($operator[$string]);
var_dump($sheme);
echo "</pre>";

			}
		}

		$result = array("status" =>0, 'msg' => $query);
		return $result;

echo "<pre>";
var_dump($query);
echo "</pre>";

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



