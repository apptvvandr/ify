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


// Import test
#$root = $conf->getApp('root');
#
#l("INFO", "Root is: $root");
#
#$path = $conf->getUser("path");
#l("INFO", "Path is", $path[0]);
#$db->scanDir($path[2]);


$search = "a";

// Query test
#$result = $db->userSearch($search, "count");
#l("INFO", "On a trouvé ". $result." résultats!");
#$result = $db->userSearch($search, "all");
#l("INFO", "Result id:", $result);



# Smart query language test

#$search="";
#$result = $db->smartQuery($search, "albums", "html-list");
#//l("INFO", "Resultat de la requete ".$search." est:", $result);
#echo "<ul>".$result."</ul>";
#
$search="p:";
$result = $db->userSearch($search, "html-table");
$result = $db->smartQuery($search,'all',  "html-table");
l("INFO", "Resultat de la requete ".$search." est:", $result);
#echo "<ul>".$result."</ul>";
#
#$search="nevermind";
#$result = $db->smartQuery($search, "album");
#//l("INFO", "Resultat de la requete ".$search." est:", $result);
#echo "<ul>".$result."</ul>";
#
#
#$search='y<2001';
#$result = $db->smartQuery($search, "all");
#//l("INFO", "Resultat de la reuqete $search est:", $result);
#echo "<ul>".$result."</ul>";

//// HTMLeuh Code
?>
    <head>
        <meta charset="utf-8">
        <title>Ify music player</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="">
        <meta name="author" content="">

        <!-- Le styles -->
        <link rel="stylesheet" href="../lib/bootstrap/css/bootstrap.css">
        <link rel="stylesheet" href="../lib/font-awesome/css/font-awesome.min.css">
        <link rel="lib/melement/stylesheet" href="mediaelementplayer.css" />

        <link rel="stylesheet" href="../lib/ify/style2.css">
      </head>
<body>

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
    <script type="text/javascript" src="../lib/bootstrap/js/bootstrap.js"></script>

	<input id="search"/>
	<input id="search2" type="text" data-provide="typeahead" data-source="<?php echo $result; ?>">


	<script type="text/javascript">
	var colors = ["red", "blue", "green", "yellow", "brown", "black"];
	var raw = <?php echo $result;?>;



	$('#search').typeahead({source: function (query, process) {
		var name = [];
		var artists = {};
		
		var data = <?php echo $result;?>;
	
		$.each(data, function (i, line) {
			        artists[line.tagArtist] = line;
					name.push(line.tagArtist);
		});

					console.log("Artist is:" + name)
		process(name);
	
	}});

	$('#search2').typeahead({source: raw})
	</script>



</body>
<?php


// Remind closing the MySQL connection at the end
unset($db);
$d->timerGet();
echo "</body>"
?>
