<?php
// Main API file to get content from DB

include ("ify.php");

// Get values from HTTP
$function = getData('f', "null");
$args = getData('a');
$output = getData('o', 'json');


/*
 * function: The name of the API to call
 * args: Arguments of this API
 * output: Output format, can be a table, a list, a JSON
 */


// Developement options
///////////////////////

// Define user context
global $conf;
$conf = new ifyConfig("config.php");
$conf->setUser("jez");

// Initialise DB backend

// Action to do
switch ( $function) {
	case "listObject":
		listObject( $args, $output);
		break;
	case "userSearch":
		userSearch( $args);
		break;
	case "uiHTMLTable":
		uiHTMLTable( $args);
		break;
	case "uiBrowse":
		uiBrowse( $args, $output);
		break;
	case "uihColumns":
		uihColumns( $args, $output);
		break;

	case "array1DSimple":
		array1DSimple( $args, $output);
		break;
	case "array2DSimple":
		array2DSimple( $args, $output);
		break;
#	case "array1DAdvanced":
#		array1DAdvanced( $args, $output);
#		break;
#	case "array2DAdvanced":
#		array2DAdvanced( $args, $output);
#		break;

	case "getConf":
		getConf($output);
		break;
	case "confDump":
		confDump($output);
		break;
    default:
		echo "Wrong parameter to call this file: ".$function;
}



// Function: array1DSimple
//////////////////////////
function array1DSimple ($args) {

	// 1. Initialisation
	global $conf;

	$db = new ifyDB($conf);

	$search = getData('search');
	$columns = getData('columns');
	$group = getData('group');
	$limit = getData('limit');
	$order = getData('order');
	$output = getData('o', 'json');


	// 2. Return a php  2D object
	$result = $db->smartQuery($search, $columns, $group, $limit, $order);


	// 3. Convert to simple 1D array
	$data=array();
	foreach ($result as $key=>$value) {
		// Take the first value of the second array
		array_push($data, reset ($value));
	}


	// 4. Create header
	$header=array(
		'query'		=> '',
		'xSize'		=> count($data),
		'ySize'		=> 0,
		'xLabel'	=> array_slice(explode(' ', $columns), 0, 1),
		'yLabel'	=> array(),
		'date'		=> date("c"),
		'type'		=> 'array1DSimple',
		'version'	=> 1
	);	


	// 5. Create answer
	$answer=array(
		"header"		=> $header,
		"data"			=> $data
	);

//print_r($answer);

	printf(json_encode($answer));

}


// Function: array2DSimple
//////////////////////////
function array2DSimple ($args) {

	// 1. Initialisation
	global $conf;

	$db = new ifyDB($conf);

	$search = getData('search');
	$columns = getData('columns');
	$group = getData('group');
	$limit = getData('limit');
	$order = getData('order');
	$output = getData('o', 'json');


	// 2. Return a php  2D object
	$result = $db->smartQuery($search, $columns, $group, $limit, $order);


	// 3. Convert to simple 1D array
	// Convert to simple 2D array
	$data=array();
	foreach ($result as $key=>$value) {
		array_push($data, array_values($value));
	}


	// 4. Create header
	$header=array(
		'query'		=> '',
		'xSize'		=> count($data[0]),
		'ySize'		=> count($data),
		'xLabel'	=> explode(' ', $columns),
		'yLabel'	=> array(),
		'date'		=> date("c"),
		'type'		=> 'array2DSimple',
		'version'	=> 1
	);	


	// 5. Create answer
	$answer=array(
		'header'	=> $header,
		'data'		=> $data
	);

//print_r($answer);

	printf(json_encode($answer));

}





























// This function query the DB and returns music data to hColumns
// V1: accept 1D array only, with header and data!
function uihColumns($args) {
	global $conf;

	$db = new ifyDB($conf);

	$search = getData('search');
	$columns = getData('columns');
	$group = getData('group');
	$limit = getData('limit');
	$order = getData('order');
	$output = getData('o', 'json');

	
	#var_dump($search);
//	echo "test";
//	echo "test";
//	print_r($_GET);
//	echo "test";

	// Return a php  2D object
	$result = $db->smartQuery($search, $columns, $group, $limit, $order);

//echo "<pre>";
//echo "test";
print_r($result);
//echo "</pre>";

	printf(json_encode($result));

}


// This function query the DB and returns music data
function uiBrowse($args) {
	global $conf;

	$db = new ifyDB($conf);

	$search = getData('search');
	$columns = getData('columns');
	$group = getData('group');
	$limit = getData('limit');
	$order = getData('order');
	$output = getData('o', 'json');

	
	#var_dump($search);
//	echo "test";
//	echo "test";
//	print_r($_GET);
//	echo "test";

	// Return a php object
	$result = $db->smartQuery($search, $columns, $group, $limit, $order);

//echo "<pre>";
//echo "test";
#print_r($search);
//echo "</pre>";

	switch ($output) {
		case 'json':
			printf(json_encode($result));
			break;
		
		case 'html-list':
			$return = array();
			foreach ($result as $line){
				array_push($return,implode(', ', $line));
			}
			printf('<li>'.implode("</li><li>", $return).'</li>');
			break;
		
		case 'html-col':
			$result = array_values($result);
			
			$html = "";
			$i = 0;
			foreach ($result as $line) {
				$j = 0;
				foreach ($line as $key => $value) {
					if ($j == 0) {
						$html .= '<tr class="ui-col-entry" data-filter="' . $value . '">';
					}
					$html.='<td>' . htmlspecialchars($value) . '</td>';
					$j += 1;
				}
				$html .= '</tr>';
				$i += 1;
			}
			
			printf($html);
			break;
		
		case 'html-table':
			$result = array_values($result);
			
			$html = "";
			foreach ($result as $line) {
				$html .= '<tr>';
				$i = 0;
				foreach ($line as $key => $value) {
					if ($i == 0) {
						$i += 1;
						$html.='<td data-content="' . $value . '">' . $value . '</td>';
					} else {
						$html.='<td tutu>' . $value . '</td>';
					}
				}
				$html .= '</tr>';
			}
			
			printf($html);
			break;
		
		default:
			// Handle 'php' case
			printf('Error, no valid format defined!');
			return $result;

	}
}

// This function retrieve all config values
function getConf($output) {
	global $conf;

	$option = getData('v');
	$section = getData('s', 'global');

	$result = $conf->get($option, $section);

	printf( json_encode($result));

}


// This function retrieve dump  config values
function confDump($output) {
	global $conf;

	$result = $conf->dump();

	echo  json_encode($result);

}



/// OLD
//
function listObject($args, $output ) {
	global $conf;
	$db = new ifyDB($conf);

	//doLog("$args, 'artists', $output");
	$result = $db->smartQuery($args, 'all', $output);
	return $result;
}


function userSearch($string) {
	$db = new ifyDB;
	$result = $db->userSearch($string);
	echo $result;
}

function uiHTMLTable($string) {
	$db = new ifyDB;
	$result = $db->smartQuery($string, 'all', 'html-table');
	echo $result;
}

// Return a list from
function dataList($string) {
	$db = new ifyDB;
	$result = $db->smartQuery($string, 'all', 'html-table');
	echo $result;
}


function dataTable($string) {
	$db = new ifyDB;
	$result = $db->smartQuery($string, 'all', 'html-table');
	echo $result;
}

// Functions to implement
/*


// Put a path in there
-> browse_files

// Browse artists
-> browse_artist

// Browse_albums
-> browse_album

// l

*/


?>
