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

// Initialise timer
$d->timerStart();
#$d->log(1, "Mon message", $tutu, $titi);

//
// CODE EXPERIMENTATIONS
////////////////////////


$path = $conf->getUser("path");
echo "$path[0]";









// Remind closing the MySQL connection at the end
unset($db);
$d->timerGet();
?>
