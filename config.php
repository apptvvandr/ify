<?php

/*
 * Hard coded option file
 * This fils must be kept secret, and should be set write-only.
 */

// MySQL OPTIONS
define("IFY_MYSQL_HOST", "localhost");
define("IFY_MYSQL_USER", "ify");
define("IFY_MYSQL_PASSWORD", "OmVogOvCav8");
define("IFY_MYSQL_DB", "ify");
define("IFY_MYSQL_PREFIX", "");

$mysql_host='localhost';
$mysql_user='ify';
$mysql_password='OmVogOvCav8';
$mysql_db='ify';
$mysql_prefix='';


// Password security settings
define("PBKDF2_HASH_ALGORITHM", "sha256");
define("PBKDF2_ITERATIONS", 1000);
define("PBKDF2_SALT_BYTES", 24);
define("PBKDF2_HASH_BYTES", 24);

define("HASH_SECTIONS", 4);
define("HASH_ALGORITHM_INDEX", 0);
define("HASH_ITERATION_INDEX", 1);
define("HASH_SALT_INDEX", 2);
define("HASH_PBKDF2_INDEX", 3);

// IQL definition
//define("IFY_IQL_FIELDS", array(
//	'a'	=> 'tagArtist',
//	'b'	=> 'tagAlbum',
//	't'	=> 'tagTitle',
//	'g'	=> 'tagGenre',
//	'y'	=> 'tagYear',
//	'l'	=> 'fileLenght',
//	't'	=> 'tagTrack',
//	'artist'	=> 'tagArtist',
//	'album'		=> 'tagAlbum',
//	'title'		=> 'tagTitle',
//	'genre'		=> 'tagGenre',
//	'year'		=> 'tagYear',
//	'lenght'	=> 'fileLenght',
//	'ttrack'	=> 'tagTrack'
//	));
//define("IFY_IQL_OP", array(
//	'!='	=> '!=',
//	'>='	=> '>=',
//	'<='	=> '<=',
//	'='	=> '=',
//	':'	=> 'LIKE',
//	'!'	=> 'NOT LIKE',
//	'>'	=> '>',
//	'<'	=> '<',
//	'['	=> 'NOT BETWEEN ? AND ?',
//	']'	=> 'BETWEEN ? AND ?'
//));
