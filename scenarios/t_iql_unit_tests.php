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
//$d->log(1, "Mon message", $tutu, $titi);

// Initialise Debug object


//
// EPERIMENTAL SETUP
////////////////////////

$where="  not artist :testString and not  album:tutulalbum    ";
$where="  not artist :nirvana or artist:blink    ";
$where="artist:nivrana";

# Basic
$where="b=toto AND  NOT  a:'red%' AND y=208 OR  g:metal";

# Auto logical defualt
$where="nirvana";




$unit_select=array(
#	"*",
#	"all",
#	"* ",
#	"all",
#	" * ",
#	" all ",
#	"    * ",
#	"    all ",
	"",
	"*",
	"all ",
	"c_all ",
	"c_* ",
	"all ",
	"artist",
	"c_album track",
	"album          track",
	"c_album          c_track",
	"a t b"
);

$unit_select_fail=array(
	" all ",
	" album track ",
	" * ",
	"artistall",
	"* artist",
	"all artist",
	"artist * ",
	"artist all ",
	" artist* ",
	" artistall ",
	"    *artist ",
	"    allartist "
);



//
// CODE INIT & CONFIG
////////////////////////


// Initialise object
$obj=new ifyIQL();
//$obj->updateArgs($columns, $where);


#//
#// RUN UNIT TESTS: SELECT
#/////////////////////////
#
#
#$d->log(2, "<br>=============<br>SELECT : SUCCESS UNIT TESTS<br>=============<br>");
##$d->log(3, "Arguments are:", $columns, $where);
#
#foreach ($unit_select as $columns) {
#	try {
#		$obj->updateArgs($columns, $where);
#		$query=$obj->buildSQL();
#		$d->log(3, "Arguments was: <pre>'$columns'</pre>Answer is:<pre>$query </pre>");
#	}
#	catch (Exception $e){
#		$d->log("ERROR", "Arguments was:", $columns, $e);
#	}
#}
#
#
#$d->log(2, "<br>=============<br>SELECT : FAIL UNIT TESTS<br>=============<br>");
##$d->log(3, "Arguments are:", $columns, $where);
#
#foreach ($unit_select_fail as $columns) {
#	try {
#		$obj->updateArgs($columns, $where);
#		$query=$obj->buildSQL();
#		$d->log("ERROR", "This query works but it shouldn't! Arguments was:", $columns, $where);
#	}
#	catch (Exception $e){
#		$d->log(3, "Arguments was: <pre>'$columns'</pre>Answer is:<pre>$e </pre>");
#	}
#}



//
// RUN UNIT TESTS: LIMIT
/////////////////////////


$d->log(2, "<br>=============<br>SELECT : SUCCESS UNIT TESTS<br>=============<br>");
#$d->log(3, "Arguments are:", $columns, $where);

foreach ($unit_select as $columns) {
	try {
		$obj->updateArgs($columns, $where);
		$query=$obj->buildSQL();
		$d->log(3, "Arguments was: <pre>'$columns'</pre>Answer is:<pre>$query </pre>");
	}
	catch (Exception $e){
		$d->log("ERROR", "Arguments was:", $columns, $e);
	}
}


$d->log(2, "<br>=============<br>SELECT : FAIL UNIT TESTS<br>=============<br>");
#$d->log(3, "Arguments are:", $columns, $where);

foreach ($unit_select_fail as $columns) {
	try {
		$obj->updateArgs($columns, $where);
		$query=$obj->buildSQL();
		$d->log("ERROR", "This query works but it shouldn't! Arguments was:", $columns, $where);
	}
	catch (Exception $e){
		$d->log(3, "Arguments was: <pre>'$columns'</pre>Answer is:<pre>$e </pre>");
	}
}



// Remind closing the MySQL connection at the end
unset($db);
$d->timerGet();
echo "</body>"
?>
