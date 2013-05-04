
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

	xhr.open("POST", "tests/browser.php", true);
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
	this.append = function (folder) {
		this.path = this.path + folder + '/';
		console.log('New path is (append): ' + this.path)
	}
	this.back = function (level){
		array = this.path.split("/");
		array = array.slice(0, array.length - 1);
		this.path = array.join("/");
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
	$('#ui_browser_list').html(list);

	// Set events hook on list elements
	$('#ui_browser_list > tr').on("click", function(e) {
		e.preventDefault(); // Empêche le navigateur de suivre le lien.
		glob_rel_path.append($(this).text());
		req_update(ui_browser_refresh,"action=browse_dir&args=" +  glob_rel_path.path);
		console.log("Pressing " + $(this).index() + ": " +  $(this).text() + "(going to: " + glob_rel_path.path + ")");

	});
}


// UI Requests
//////////////


// Browser: refresh folder list
$('#ui_browser_refresh').bind('click', function () {
	console.log("Refreshing browser list: " + glob_rel_path.path);
	req_update(ui_browser_refresh,"action=browse_dir&args=" + glob_rel_path.path);
})

// Browser: go backward
$('#ui_browser_back').on('click', function (e) {
	glob_rel_path.back();
	console.log("Going backward: " + glob_rel_path.path);
	req_update(ui_browser_refresh,"action=browse_dir&args=" + glob_rel_path.path);	
})


// Initialisation
/////////////////
var glob_rel_path = new Path("") ;

// Code
///////////////
$('#ui_browser_back').click()



