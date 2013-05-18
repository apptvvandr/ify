<?php

//include('../getid3/getid3.php');
include('lib/getid3/getid3.php');

// Include classes
include('lib/ify/debug.class.php');
include('lib/ify/ini.class.php');
include('lib/ify/mysql.class.php');
include('lib/ify/db.class.php');



// Get ID3 informations
function music_info($file) {

	// Create object
	$getID3 = new getID3;

	// Create an array with all extract infos
	$id3 = $getID3->analyze($file);

	// Create an array with best id3 meta available
	getid3_lib::CopyTagsToComments($id3);

	// Extract needed infos for Ify
	$tags = array (
		"filename"	=> (isset($id3['filename']) ? $id3['filename'] : "-"),
		"title"		=> (isset($id3['comments_html']['title'][0]) ? $id3['comments_html']['title'][0] : "-"),
		"artist"	=> (isset($id3['comments_html']['artist'][0]) ? $id3['comments_html']['artist'][0] : "-"),
		"album"		=> (isset($id3['comments_html']['album'][0]) ? $id3['comments_html']['album'][0] : "-"),
		"year"		=> (isset($id3['comments_html']['year'][0]) ? $id3['comments_html']['year'][0] : "-"),
		"track"		=> (isset($id3['comments_html']['track'][0]) ? $id3['comments_html']['track'][0] : "-"),
		"genre"		=> (isset($id3['comments_html']['genre'][0]) ? $id3['comments_html']['genre'][0] : "-"),
		"length"	=> (isset($id3['playtime_string']) ? $id3['playtime_string'] : "-"),
		"bitrate"	=> (isset($id3['audio']['bitrate']) ? $id3['audio']['bitrate'] : "-"),
		"format"	=> (isset($id3['audio']['dataformat']) ? $id3['audio']['dataformat'] : "-")
	);
	return $tags;
}


// This function help to download or stream a file without
// showing the source file.
// Sources from: https://github.com/pomle/php-serveFilePartial/blob/master/ServeFilePartial.inc.php

function serveFile($fileName, $fileTitle = null, $stream = true, $contentType = null)
{
	// Initialisation
	/////////////////

	// Do some basic checks
	if( !file_exists($fileName) )
		throw New \Exception(sprintf('File not found: %s', $fileName));

	if( !is_readable($fileName) )
		throw New \Exception(sprintf('File not readable: %s', $fileName));


	// Guess the file name if not indicated
	if( !$fileTitle ) {
		$fileTitle = basename ( $fileName );
		#$fileTitle = date("Y-m-d_H:i:s");
	}

	// Guess the MIME type
	if ( !$contentType ) {
		$contentType = mime_content_type ( $fileName );
	}


	// Default to send entire file
	$byteOffset = 0;
	$byteLength = $fileSize = filesize($fileName);


	// Header manipulations
	///////////////////////

	// Remove unecessary headers
	header_remove('Cache-Control');
	header_remove('Pragma');


	// Set header
	header('Accept-Ranges: bytes', true);
	header(sprintf('Content-Type: %s', $contentType), true);


	// Select proper header to stream or no the file
	if ( $stream ) {
		header(sprintf('Content-Disposition: inline; filename="%s"', $fileTitle));
	} else {
		header(sprintf('Content-Disposition: attachment; filename="%s"', $fileTitle));
	}


	// Parse Content-Range header for byte offsets, looks like "bytes=11525-" OR "bytes=11525-12451"
	if( isset($_SERVER['HTTP_RANGE']) && preg_match('%bytes=(\d+)-(\d+)?%i', $_SERVER['HTTP_RANGE'], $match) )
	{
		// Offset signifies where we should begin to read the file
		$byteOffset = (int)$match[1];

		// Length is for how long we should read the file according to the browser, and can never go beyond the file size
		if( isset($match[2]) )
			$byteLength = min( (int)$match[2], $byteLength - $byteOffset);

		// Set proper header to be able to stream
		header("HTTP/1.1 206 Partial content");

		// Decrease by 1 on byte-length since this definition is zero-based index of bytes being sent
		header(sprintf('Content-Range: bytes %d-%d/%d', $byteOffset, $byteLength - 1, $fileSize));
	}


	// Bits manipulations
	$byteRange = $byteLength - $byteOffset;
	$buffer = ''; 	### Variable containing the buffer
	$bufferSize = 512 * 16; ### Just a reasonable buffer size
	$bytePool = $byteRange; ### Contains how much is left to read of the byteRange


	// Last headers definitions
	header(sprintf('Content-Length: %d', $byteRange));
	header(sprintf('Expires: %s', date('D, d M Y H:i:s', time() + 60*60*24*90) . ' GMT'));


	// Error management
	if( !$handle = fopen($fileName, 'r') )
		throw New \Exception(sprintf("Could not get handle for file %s", $fileName));

	if( fseek($handle, $byteOffset, SEEK_SET) == -1 )
		throw New \Exception(sprintf("Could not seek to byte offset %d", $byteOffset));


	// Read the file to output
	//////////////////////////

	while( $bytePool > 0 )
	{
		// How many bytes we request on this iteration
		$chunkSizeRequested = min($bufferSize, $bytePool);

		// Try readin $chunkSizeRequested bytes from $handle and put data in $buffer
		$buffer = fread($handle, $chunkSizeRequested);

		// Store how many bytes were actually read
		$chunkSizeActual = strlen($buffer);

		// If we didn't get any bytes that means something unexpected has happened since $bytePool should be zero already
		if( $chunkSizeActual == 0 )
		{
			// For production servers this should go in your php error log, since it will break the output
			trigger_error('Chunksize became 0', E_USER_WARNING);
			break;
		}

		// Decrease byte pool with amount of bytes that were read during this iteration
		$bytePool -= $chunkSizeActual;

		// Write the buffer to output
		print $buffer;

		// Try to output the data to the client immediately
		flush();
	}

	exit();
}
