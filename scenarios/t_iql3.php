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

$iqlGrammar = new Grammar(
	"iql",
	array(

		// Fields
		"f_artist"	=> new StringParser("artist") ,
		"f_album"	=> new StringParser("album") ,
		"f_song"	=> new StringParser("song") ,
		"f_genre"	=> new StringParser("genre") ,
		"f_year"	=> new StringParser("year") ,
		"f_length"	=> new StringParser("lenght") ,
		"f_track"	=> new StringParser("track") ,

		// Operators
		"o_neq"	=> new StringParser("!=") ,
		"o_lte"	=> new StringParser("<=") ,
		"o_gte"	=> new StringParser(">=") ,
		"o_eq"	=> new StringParser("=") ,
		"o_lt"	=> new StringParser("<") ,
		"o_gt"	=> new StringParser(">") ,

		// Logicals
		"l_op"	=> new StringParser("(") ,
		"l_cp"	=> new StringParser(")") ,
		"l_and"	=> new StringParser("and") ,
		"l_or"	=> new StringParser("or") ,
		"l_not"	=> new StringParser("not") ,
		"l_sep"	=> new RegexParser("/^\s*/",function() { return null; }) ,

		// Values
		"v_word"	=> new RegexParser("/^\w+/") ,
		"v_sqword"	=> new RegexParser("/^'[^']+'/") ,
		"v_dqword"	=> new RegexParser("/^\"[^\"]+\"/") ,
		"v_numeric"	=> new RegexParser("/^\d+/") ,

		// Assocation set
		"af_string"	=> new LazyAltParser(
			array (
				"f_artist",
				"f_album",
				"f_song",
				"f_genre"
			)
		),
		"af_num"	=> new LazyAltParser(
			array(
				"f_year",
				"f_length",
				"f_track"
			)
		),
		"av_string"	=> new LazyAltParser(
			array(
				"v_word",
				"v_sqword",
				"v_dqword"
			)
		),
		"av_num"	=> new LazyAltParser(
			array(
				"v_numeric"
			)
		),
		"ao_string"	=> new LazyAltParser(
			array(
				"o_eq",
				"o_neq"
			)
		),
		"ao_num"	=> new LazyAltParser(
			array(
				"o_eq",
				"o_neq",
				"o_lt",
				"o_gt",
				"o_lte",
				"o_gte"
			)
		),

		// Expressions
		"e_string"	=> new ConcParser(
			array(
				"af_string",
				"l_sep",
				"ao_string",
				"l_sep",
				"av_string"
			)
		),
		"e_num"	=> new ConcParser(
			array(
				"af_num",
				"l_sep",
				"ao_num",
				"l_sep",
				"av_num"
			)
		),

		// Root definition
		"iql"	=> new LazyAltParser(
			array(
				"e_string",
				"e_num"
			)
		)


	)
);




//
// CODE EXPERIMENTATIONS
////////////////////////

echo "test2";



#$query = $db->IQLParse ('ARTIST IS "Blink 182" OR     ( ALBUM IS Nevermind  OR ARTIST HAS "red \"hot\" ) \'chili ( pepper\'" ) AND    ( YEAR > 2000 OR YEAR >   1998   )');

echo "<pre>";
$query=$iqlGrammar->parse('song  = "tut"');
var_dump($query);
echo "</pre>";



// Remind closing the MySQL connection at the end
unset($db);
$d->timerGet();
echo "</body>"
?>
