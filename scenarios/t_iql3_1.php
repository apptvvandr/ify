<?php

/* This template helps to develop Ify */


//
// HEADERS
//////////

// Include CSS
echo '<header><link rel="stylesheet" href="../lib/ify/style2.css"></header><body>';

// Include libs
include('../ify.php');

// Set dynmic output
flush(); @ob_flush();  ## make sure that all output is sent in real-time

// Set time limit
set_time_limit ( 30 );

// Define user context
global $conf;
//$conf = new ifyConfig();
//$conf->setUser("jez");

// Initialise DB backend
//$db = new ifyDB($conf);

// Initialise timer
$d->timerStart();
#$d->log(1, "Mon message", $tutu, $titi);



//
// EPERIMENTAL SETUP
////////////////////////

echo "test";

$string="artist = 'ro__b i%n'";
$string="artist :'12:34'";
#$string="artist = t";

$iqlGrammar = new Grammar(
	"iql",
	array(

		#
		# Language basics
		#
		

		// Fields
		"f_string"	=> new LazyAltParser(
			array(
				new StringParser("artist", function() {return "tagArtist"; }) ,
				new StringParser("album", function() {return "tagAlbum"; }) ,
				new StringParser("title", function() {return "tagTitle"; }) ,
				new StringParser("genre", function() {return "tagGenre"; }) ,
				new StringParser("a", function() {return "tagArtist"; }) ,
				new StringParser("b", function() {return "tagAlbum"; }) ,
				new StringParser("t", function() {return "tagTitle"; }) ,
				new StringParser("g", function() {return "tagGenre"; })
			)
		),
		"f_num"		=> new LazyAltParser(
			array(
				new StringParser("year", function() {return "tagYear"; }) ,
				new StringParser("lenght", function() {return "lenghtLenght"; }) ,
				new StringParser("track", function() {return "tagTrack"; }) ,
				new StringParser("y", function() {return "tagYear"; }) ,
				new StringParser("l", function() {return "fileLenght"; }) ,
				new StringParser("n", function() {return "tagTrack"; })
			)
		),


		// Operators
		"o_string"	=> new LazyAltParser(
			array(
				new StringParser("=", function() {return " = ";}) ,
				new StringParser(":", function() {return " LIKE ";}) ,
				new StringParser("!=", function() {return " != ";}) ,
				new StringParser("!:", function() {return " NOT LIKE ";})
			)
		),
		"o_num"	=> new LazyAltParser(
			array(
				new StringParser("=", function() {return " = ";}) ,
				new StringParser(":", function() {return " LIKE ";}) ,
				new StringParser("!=", function() {return " != ";}) ,
				new StringParser("!:", function() {return " NOT LIKE ";}) ,
				new StringParser("<=", function() {return " <= ";}) ,
				new StringParser(">=", function() {return " >= ";}) ,
				new StringParser("<", function() {return " < ";}) ,
				new StringParser(">", function() {return " > ";})
			)
		),
		"o_2num"	=> new LazyAltParser(
			array(
				new StringParser("]", function() {return " BETWEEN ";}) ,
				new StringParser("[", function() {return " NOT BETWEEN ";})
			)
		),


		// Values
		"v_string"	=> new LazyAltParser(
			array(
				new RegexParser("/^[\w_%]+/") ,
				new RegexParser("/^'([^']+)'/", function($match0, $match1) { return $match1; }) ,
				new RegexParser("/^\"([^\"]+)\"/", function($match0, $match1) { return $match1; })
			),
			function($value) {return "'" . $value . "'";}
		),
		"v_num"		=> new  RegexParser("/^([+-]?\d+)/", function($match0, $match1) { return $match1; }),
		"v_2num"	=> new ConcParser(
			array(
				"v_num",
				new StringParser(":", function() {return " AND ";}),
				"v_num"
			),
			function ($val1, $op, $val2) {return $val1.$op.$val2;}
		),


		// Logical
		"l_ao"		=> new LazyAltParser(
			array(
				new RegexParser("/^and/i", function() { return " AND "; }),
				new RegexParser("/^or/i", function() { return " OR "; }),
				new EmptyParser(function() { return " AND ";})
			)
		),
		"l_not"		=> new LazyAltParser(
			array(
				new RegexParser("/^not/i", function() { return "NOT "; }),
				new EmptyParser()
			)
		),


		// Misc
		"m_sep"		=> new RegexParser("/^\s*/",function() { return null; }) ,
		"m_meta"	=> new ConcParser(
			array(
				"v_string"
			),
			function($value) { $value = substr($value, 1, -1); return "( tagArtist LIKE '%".$value."%' OR tagAlbum LIKE '%".$value."%' OR tagTitle LIKE '%".$value."%' )"; }
		),
		

		#
		# Language expressions
		#

		"expression"	=> new LazyAltParser(
			array(
				// String
				new ConcParser(
					array(
						"m_sep",
						"l_not",
						"m_sep",
						"f_string",
						"m_sep",
						"o_string",
						"m_sep",
						"v_string",
						"m_sep"
					)
				),
				// Numerical
				new ConcParser(
					array(
						"m_sep",
						"l_not",
						"m_sep",
						"f_num",
						"m_sep",
						"o_num",
						"m_sep",
						"v_num",
						"m_sep"
					)
				),
				// Numerical (2 numbers)
				new ConcParser(
					array(
						"m_sep",
						"l_not",
						"m_sep",
						"f_num",
						"m_sep",
						"o_2num",
						"m_sep",
						"v_2num",
						"m_sep"
					)
				),
				// Meta search (default)
				new ConcParser(
					array(
						"m_sep",
						"l_not",
						"m_sep",
						"m_meta",
						"m_sep"
					)
				)
			),
			//function($match) {var_dump($match); return implode("", $match);}
			function($match) { return implode("", $match);}
		),
		"logical"		=>	new LazyAltParser(
			array(
				new ConcParser(
					array(
						"expression",
						"l_ao",
						"expression",
						"l_ao",
						"expression",
						"l_ao",
						"expression"
					)
				),
				new ConcParser(
					array(
						"expression",
						"l_ao",
						"expression",
						"l_ao",
						"expression"
					)
				),
				new ConcParser(
					array(
						"expression",
						"l_ao",
						"expression"
					)
				),
				new ConcParser(
					array(
						"expression"
					)
				)
			),
			//function($match) {var_dump($match); return implode("", $match);}
			function($match) { return implode("", $match);}
		),
		"iql"		=>	new ConcParser(
			array(
				"expression",
				new GreedyMultiParser(
					new ConcParser(
						array(
							"m_sep",
							"l_ao",
							"m_sep",
							"expression",
							"m_sep"
						)
					),
					0,
					null
				)
			),
			function($first) {
				// Test if there are more than one expression
				$other=func_get_args();
				if (empty($other)) {
					return $first;
				}
				else {
					$first=array($first);
					$other=$other[1];

					foreach ($other as $value) {
						#echo "tata";
						#var_dump($value); 
						array_push($first, implode($value));
					}

					$first=implode($first);
					return $first;
				}
			}
		)
	)
);




//
// CODE EXPERIMENTATIONS
////////////////////////



$string="  not artist :testString and not  album:tutulalbum    ";
$string="  not artist :nirvana or artist:blink    ";
$string="artist:nivrana";

# Basic
$string="b=toto AND  NOT  a:'red%' AND y=208 OR  g:metal";

# Auto logical defualt
$string="b=toto or a:'red%' y>203 and not t=34 not tut";


echo "<pre>";
echo ">> String is:\n";
var_dump($string);
echo "\n";
$query=$iqlGrammar->parse($string);
echo ">> Result:\n";
var_dump($query);
echo "\n>> Requete finale:\nSELECT * FROM files WHERE " . $query. ";";
echo "</pre>";



// Remind closing the MySQL connection at the end
unset($db);
$d->timerGet();
echo "</body>"
?>
