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
$conf = new ifyConfig();
$conf->setUser("jez");

// Initialise DB backend
$db = new ifyDB;

// Initialise timer
$d->timerStart();
#$d->log(1, "Mon message", $tutu, $titi);



//
// CODE EXPERIMENTATIONS
////////////////////////


$path = $conf->getUser('path');
echo "Path to scan: " . $path[0] . "<br>";
echo "Absolute path to scan: " . $conf->getApp('root') . $path[0] . "<br>";

$db->scanDir($path[0]);



// Remind closing the MySQL connection at the end
unset($db);
$d->timerGet();
echo "</body>"
?>
