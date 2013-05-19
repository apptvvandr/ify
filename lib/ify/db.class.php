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

	public function userSearch($string, $output="all") {
		// Analyse user search query
		
		$db = $this->_db;
		$string = "%".$string."%";


		// Build the request
		if ($output == "count") {
			$start = "SELECT COUNT(id) FROM `files` WHERE ";
		} else {
			$start = "SELECT id, tagArtist, tagTitle, tagAlbum, tagGenre FROM `files` WHERE ";
		}
		$where = "tagArtist LIKE ? OR tagTitle LIKE ? OR tagAlbum LIKE ?";
		$limit = " LIMIT 20";

		$query = $start . $where .$limit;
		//l("INFOS", "Request is: ".$start.$where.' LIMIT 50');

		// Execute the request
		$params = array($string, $string, $string);
		$result = $db->rawQuery($start.$where.' LIMIT 50', $params);

		//print_r($result);

		if ($output == "count") {
			return $result[0]["COUNT(id)"];
		} else {
			return $result;
		}

	}



	public function smartQuery($string) {

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
			$result = array(2, "IQL: Not a smart query: ".$string);
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
				$result = array(1, "IQL: key '".$key."' is not recognised!");
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
					$result = array(1,"IQL: Operator '".$op."' is not available for '".$key."' string type");
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
					$result = array(1, "IQL: Operator '".$op."' is not available for '".$key."' number type");
					return $result;
			}
		} else {
			$result = array(1, "IQL: Operator '".$op."' is not recognised");
		}

		//var_dump($key);
		//echo "<br>";

		// Checking values
		
		// Looking for a number, or a string or a <num>:<num>

		if (is_int(strpos("patg",$key))) {
			// Looking for a string, and clean it
			$regex='/^(\'|")|(\'|")$/';
			$value = preg_replace($regex, "", $value);
			$query .= " '".$value."'";

		} elseif (!is_int(strpos("[]",$op))) {
			// Looking for a number
			$regex = "/^[:digit:]+$/";
			$match = preg_match($regex, $value);

			if (!$match) {
				$result = array(1, "IQL: '".$value."' is not a number");
				return $result;
			} else {
				$query .= ' '.$value;
			}

		} elseif (is_int(strpos("[]",$op))) {
			// Looking for interval
			// <num>:<num>

			$regex = "/^(\d+):(\d+)$/";
			$match = array ();
			$count = preg_match($regex, $value, $match);

			if (count($match) != 3 ) {
				$result = array(1, "IQL: '".$value."' is not an interval");
				return $result;
			} elseif ( $match[1] >= $match[2]) {
				$result = array(1, "IQL: First number cannot be bigger than second number");
			} else {
				$query .= ' '.$match[1].' AND '.$match[2];
			}
		} else {
			// Not possible body
			$result = array(1, "IQL: '".$value."' is not acceptable value");
			return $result;
		}

		// We finaly succeed all tests, we built the statement
		$result = array(0, $query);
		return $result;
	}
}




/*

Main query tool model:
========================

SELECT: what kind of output do we need

> All Album list

> All Artist list

> All Song list

> Count of then


FILTER: which elements do we need to get

> Search Album, Artist, title

> Search Artist

> Search Album

> Search Song


Examples:
======================
> p:Nirvana [OR] a:Nevermind

> p:Niravana [OR] p:Johnson [OR] l>3m

> (p:Niravana l>10m) [OR] p:Johnson l>7m


>>>> Rules
###########
-> Comparing two elements of same type must be interpreted like OR



( stmt [AND] stmt [AND] stmt) 


*/




?>



