
var glob_path_sep = "/";

console.log('Script.js loadad! :)')


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


// UI Answer
////////////

// Update browser list
function ui_browser_refresh(list) {
	console.log(list);
	$('#ui_Browser_list').html(list);
}

// Update music list
function ui_music_refresh(files) {
	$('#ui_Browser_files').html(files);
}

function ui_refresh_breadcrumbs(path) {
	console.log(path)
	array = path.split("/");
	console.log(array)
	path = "";
	jQuery.each(array, function(key, value) {
		//console.log('key: '+ key + "value: "+value)
		path = path + '<li><a href="#">' + value + '</a> <span class="divider">/</span></li>' ;
	})
	
	$('#ui_breadcrumbs').html(path);
	
}

// UI Requests (Static)
//////////////

// Set events hook on list elements
$('#ui_Browser_list').on("click", "tr", function(e) {
	e.preventDefault(); // Empêche le navigateur de suivre le lien.
	glob_rel_path.append($(this).text());

	// Refresh browser list	
	var req = {action:"browse_dir", args:glob_rel_path.path};
	$.post( "lib/ify/browser.php", req, function(data){
		data = jQuery.parseJSON(data);
		//console.log(data);
		glob_rel_path.path = data.path;
		glob_rel_path.apath = data.apath;
		ui_browser_refresh(data.html);}
	)
	.fail(function() { console.log("Maj de la list failed, going backward"); })


	// Refresh music list
	//console.log('refresh music list')
	var req = {action:"browse_files", args:glob_rel_path.path};
	$.post( "lib/ify/browser.php", req, function(data){
		console.log(data);
		data = jQuery.parseJSON(data);
		glob_rel_path.path = data.path;
		glob_rel_path.apath = data.apath;

		ui_music_refresh(data.html);
		ui_refresh_breadcrumbs(data.apath);
	;})
	.fail(function() { console.log("Maj de la liste failed"); })

});

// Set events on music list
$('#ui_Browser_files').on("click", "tr", function(e) {
	console.log( 'music/' + glob_rel_path.apath + '/' + $(this).children('td:first').text());
	path =  'music/' + glob_rel_path.apath + '/' + $(this).children('td:first').text();
	// glob_rel_path.apath + '/' + 
	$('#me_player')[0].player.setSrc(path);
	$('#me_player')[0].player.play();

});

// Browser: refresh folder list
$('#ui_Browser_refresh').on('click', function () {


	// Refresh browser
	console.log('refresh browser list')
	var req = {action:"browse_dir", args:glob_rel_path.path};
	$.post( "lib/ify/browser.php", req, function(data){
		//console.log(data);
		data = jQuery.parseJSON(data);
		glob_rel_path.path = data.path;
		ui_browser_refresh(data.html);}
	)
	.fail(function() { console.log("Maj du browser failed"); })

})


// Browser: go backward
$('#ui_Browser_back').on('click', function (e) {
	glob_rel_path.back();
	var req = {action:"browse_dir", args:glob_rel_path.path};
	$.post( "lib/ify/browser.php", req, function(data){
		data = jQuery.parseJSON(data);
		glob_rel_path.path = data.path;
		console.log(data);
		ui_browser_refresh(data.html);}
	)
	.fail(function() { console.log("Maj de la list failed, going backward"); })
})

$('#ui_play').on('click', function (e) {
	console.log("play");
	$('#me_player')[0].player.play();
	
});


// Initialisation
/////////////////
var glob_rel_path = new Path("") ;

// Code
///////////////
$('#ui_Browser_refresh').click()




$('#me_player').mediaelementplayer({
            //alwaysShowControls: true,
            //features: ['playpause','volume','progress'],
            features: ['playpause'],
            //audioVolume: 'horizontal',
            //audioWidth: 0,
            //audioHeight: 0
        });

console.log($('#me_player'));

//var player = new MediaElementPlayer('#me_player',/* Options */);
