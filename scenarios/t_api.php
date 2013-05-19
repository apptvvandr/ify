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


// Import test
#$root = $conf->getApp('root');
#
#l("INFO", "Root is: $root");
#
#$path = $conf->getUser("path");
#l("INFO", "Path is", $path[0]);
#$db->scanDir($path[2]);


$search = "blin";

// Query test
//$result = $db->userSearch($search, "count");
//l("INFO", "On a trouvé ". $result." résultats!");
//$result = $db->userSearch($search, "all");
//l("INFO", "Result id:", $result);



# Smart query language test

$search="tututu toto";
$result = $db->smartQuery($search);
l("INFO", "Resultat de la reuqete $search est:", $result);

$search="p!tututu toto";
$result = $db->smartQuery($search);
l("INFO", "Resultat de la reuqete $search est:", $result);

$search="t!'tututu toto'";
$result = $db->smartQuery($search);
l("INFO", "Resultat de la reuqete $search est:", $result);


$search='y[10:45';
$result = $db->smartQuery($search);
l("INFO", "Resultat de la reuqete $search est:", $result);



// Remind closing the MySQL connection at the end
unset($db);
$d->timerGet();
echo "</body>"
?>
