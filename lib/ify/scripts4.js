
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



function IfyPlayer () {
	var instance;
	


	this.init = function () {
		// Initialize the library
		var parentObject = this;

		soundManager.setup({
			url: 'http://alpha/ify-dev/lib/sm2/soundmanager2.swf',
		  // optional: use 100% HTML5 mode where available
			preferFlash: false,
			debugMode: true,
			onready: function() {
				console.log("soundManager's Ready :)")
				parentObject.instance = soundManager.createSound({
					id: "player1"
					}
				)

			},
			ontimeout: function() {
				console.log("Le soundManager a fait timeout")
			}
		});

	}
	
	this.play = function (url) {
		console.log(this.instance.paused)
		if (url != undefined) {
			console.log ('Mon url: ' + url)
			this.setSrc(url)
			this.instance.stop()
			this.instance.play()
		} else if (this.instance.paused) {
			this.instance.play()
		}
	}

	this.pause = function () {
		if (this.instance.paused) {
			this.instance.pause()
		}
	}

	this.setSrc = function (url) {
		this.instance.url = url
	}

	this.playPause = function () {
		this.instance.togglePause()
	}

	this.init()
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

$('#but-play').on('click', 
		function (e){
			console.log("Play pressed")
			console.log(player)
			player.playPause()
		}
	)

$('#but-next').on('click', 
		function (e){
			console.log("Next pressed")
			console.log(player)
			player.pause()
		}
	)


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
				'html')}
		)


$('#content-playlist-table > tbody').on('click', 'tr',
		function (e) {
			console.log("URL is: " + "../media.php?m=as&a=" + $(this).children("td").html());
			console.log(player)
			player.play("../media.php?m=as&a=" + $(this).children("td").html())

			}
		)

// Initialisation
/////////////////
var glob_rel_path = new Path("") ;
var player;


//$(document).ready(function (){
	player = new IfyPlayer ();
//})



// Code
///////////////

console.log('Player is: ')
console.log(player);



console.log("Everything loaded ;-)")
