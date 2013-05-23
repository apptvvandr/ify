
var glob_path_sep = "/";

console.log('Script.js loaded! :)')


// Framework Functions
//////////////////////

// This function create the xhr object
function getXMLHttpRequest() {
    var xhr = null;
     
    if (window.XMLHttpRequest || window.ActiveXObject) {
        if (window.ActiveXObject) {
            try {
                xhr = new ActiveXObject("Msxml2.XMLHTTP");
            } catch(e) {
                xhr = new ActiveXObject("Microsoft.XMLHTTP");
            }
        } else {
            xhr = new XMLHttpRequest(); 
        }
    } else {
        alert("Votre navigateur ne supporte pas l'objet XMLHTTPRequest...");
        return null;
    }
     
    return xhr;
}

// this function send request to the lib file
function req_update(callback,args) {
	var xhr = getXMLHttpRequest();

	xhr.open("POST", "lib/ify/browser.php", true);
	xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	xhr.onreadystatechange = function() {
	    if (xhr.readyState == 4 && (xhr.status == 200 || xhr.status == 0)) {
		// We have data and we send it to callback function
		callback(xhr.responseText);
	    }
	};
	// Send the request
	xhr.send(args);
}


// Various
///////////


// Objects
//////////

function Path( path ) {
	this.path = path;
	this.apath = "";
	this.append = function (folder) {
		this.path = this.path + glob_path_sep + folder;
		console.log('New path is (append): ' + this.path)
	}
	this.back = function (level){
		array = this.path.split("/");
		array = array.slice(0, array.length - 1);
		this.path = array.join("/") ;
		console.log('New path is (back): ' + this.path)
	}
	this.init = function() {
		this.path = "";
		console.log('New path is (init): ' + this.path)
	}
}

function Player( object ) {

	console.log( object );
	this.player = {};

	this.init = function ( object ) {
		new mejs.MediaElement(object, {
					// shows debug errors on screen
					enablePluginDebug: true,
					// remove or reorder to change plugin priority
					plugins: ['flash','silverlight'],
					// specify to force MediaElement to use a particular video or audio type
					type: '',
					// path to Flash and Silverlight plugins
					//pluginPath: '/myjsfiles/',
					// name of flash file
					//flashName: 'flashmediaelement.swf',
					// name of silverlight file
					//silverlightName: 'silverlightmediaelement.xap',
					// default if the <video width> is not specified
					//defaultVideoWidth: 480,
					// default if the <video height> is not specified     
					//defaultVideoHeight: 270,
					// overrides <video width>
					//pluginWidth: -1,
					// overrides <video height>       
					//pluginHeight: -1,
					// rate in milliseconds for Flash and Silverlight to fire the timeupdate event
					// larger number is less accurate, but less strain on plugin->JavaScript bridge
					//timerRate: 250,
					// method that fires when the Flash or Silverlight object is ready
					success: function (mediaElement, domObject) { 
						console.log("MediaElement is loaded");
						// Affect the player into variable
						this.player = mediaElement;	 
					},
					// fires when a problem is detected
					error: function () { 
						console.log("MediaElement failed to load :/");
					}
				});
	}

	this.init( object );
}


// NEW VERSION
///////////////

$('#search-bar > input').keypress(
		function (e){
			// If pressing enter, submit the user search string
			if (e.which == 13) {
				e.preventDefault();
				$('#search-bar > .submit').click();
				return false;
			}
		})

$('#search-bar > .submit').on('click',
		function(e) {
					console.log("EVENT  is:");
			$.get('../api.php',
				{ 'f': 'userSearch', 'a': $('#search-bar > input').val()},
				function(data){
					// Return a list of suggestions
					console.log("Return is:");
					console.log(data);
					$('#content-playlist-table').trigger('refresh');
				},
				'json'
				)
		})

$('#content-playlist-table').on('refresh',
		function(e) {
			console.log("Refreshing table");
			$.get('../api.php',
				{'f': 'uiHTMLTable', 'a': $('#search-bar > input').val()},
				function(data) {
					// Update the content of the table
					console.log("Table received:")
					console.log(data)
					$('#content-playlist-table > tbody ').html(data);
				},
				'html'
				)}
			)

// Initialisation
/////////////////
var glob_rel_path = new Path("") ;
var player = new Player(document.getElementById("player1"));

// Code
///////////////




console.log("Everything loaded ;-)")
