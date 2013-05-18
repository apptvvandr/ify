<?php

/* This template helps to develop Ify */


//
// HEADERS
//////////

// Include libs
include('../ify.php');

// Set dynmic output
flush(); @ob_flush();  ## make sure that all output is sent in real-time

// Set time limit
set_time_limit ( 30 );

// Define user context
global $conf;
$conf = new ifyConfig('/var/www/ify/config.ini');
$conf->setUser("jez");

// Initialise DB backend
$db = new ifyDB;



//
// CODE EXPERIMENTATIONS
////////////////////////


$path = $conf->getUser("path");
echo "$path[0]";

$d = new ifyDebug();

$tutu = "my debug value";
$titi = array(
"yesty" => "ho ouii",
"yesty" => "ho ouii",
"yesty" => "ho ouii");


//$d->log("Meggdsffsdf");

echo "teeeest <br>";
$d->log(1, "Mon message", $tutu, $titi);
echo "teeeest <br>";
//$d->log(2, "Meggdsffsdf");
//$d->log(0, "Meggdsffsdf");
//$d->log(3, "Meggdsffsdf");
//$d->log("ERROR", "Meggdsffsdf");
//$d->log("DEBUG", "Meggdsffsdf");
//$d->log("WARNING", "Meggdsffsdf");

//$db->scanDir( $path[0] );












// Remind closing the MySQL connection at the end
unset($db);
?>
