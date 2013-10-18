

jQuery(document).ready(function($) {  

	console.log("Processing initialisation ...");

	// Define main namespaces
	/////////////////////////


	// API namespace
	//##############
	var api = {
		url : 'api.php',
		query : function (args, callback, type, async) {
			
			// Define default arguments
			args = args || {};
			type = type || 'json';
			if (typeof callback != 'function') {
				callback =  function() {};
				console.warn('No callback function set!', args);
				console.trace();
			}
			if (typeof async != 'boolean') {
				 async = true;
			}

			// Build request
			request = {
				type: 'POST',
				url : api.url,
				data : args,
				async: async,
				success : function(data){
					//console.log("Call back from api.query!", data);
					//console.trace();
					callback(data);
				},
				error : function(data, error, text){
					console.log('API call failed!', data);
					console.trace();
				},
				dataType: type
			};

			// Execute the request
			$.ajax(request);
		}
	};


	// Conf namespace
	//###############
	var conf = {

		db : {},
		initDB : function(){
			api.query({'f' : 'confDump'}, conf.cb, 'json', false);
		},
		cb : function(data) {
			conf.db = data;
			console.log('Conf loaded!');
			console.log(data);
			console.log(conf.db);
		},
		navLevel : function(level) {
			console.log(conf);
			nav = $.parseJSON(conf.db.global.nav)
			select = 0

			return nav[select].nav[level]
		}
	}



	// UI namespace
	//#############
	var ui = {

		// Define UI elements
		Window : $(window),
		Wrapper : $('#ui-wrapper'),
		Header : $('header'),
		Nav : $('nav'),
		Aside : $('aside'),
		Footer : $('footer'),

		// Define 4 panel behaviour
		updateLayout : function() {
			ui.Wrapper.layout({resize: false, type: 'border', vgap: 0, hgap: 8});
			ui.Aside.css("max-width", ui.Wrapper.width() - 50 + 'px');
			ui.updateContent();
		},
		updateContent : function() {
			ui.Nav.css('width', (ui.Wrapper.width() - ui.Aside.width() + 'px'));
		},


		// Define Browser behavior
		browser : {

			// Internal variables
			level : 0,

			// This function initialise the browser
			init : function() {
				
				// Create the div to host the browser
				browser = ui.Nav.prepend('<div id="ui-nav-browser"></div>');

				// Configure the browser
				$(browser).hColumns({
					nodeSource: function(node_id, callback) {

						console.log("Node_id = ", node_id )

						// Load Nav pattern
						var pattern = JSON.parse(conf.db.global.nav)[0].nav;
						

						// Initial load
						if (node_id === null) {
							var level = ui.browser.level;
							var currentPattern = pattern[level];
							console.log("Browser level" , level);
							console.log("Browser settings" , currentPattern);

							// Query
							apiQuery = {
								'f': 'array1DSimple','o': 'json',
								'search': 'all', 'columns': currentPattern.columns , 'group' : currentPattern.group
							};
							console.log("Browser query API" , apiQuery);

							// Callback function
							apiCallback = function(data) {
								console.log("CALLBACK : data", data);
								console.log("header");
								console.log(data['header']);

								// Generate data
								var hColumnsData = [];
								for (var i=0; i < data["header"].xSize; i++) {
									hColumnsData[i] = { id : {string : data.data[i], level:0 }, label: data.data[i], type : 'folder'}
								}

								console.log("hColumns data: ", hColumnsData);
								return callback(null, hColumnsData);
							}
						} else {
							level = node_id.level + 1;
							var currentPattern = pattern[level]
							console.log("Browser level" , level);
							console.log("Browser settings" , currentPattern);

							// Query
							apiQuery = {
								'f': 'array1DSimple','o': 'json',
								'search': currentPattern.query.replace('?s', node_id.string), 'columns': currentPattern.columns , 'group' : currentPattern.group
							};
							console.log("Browser query API" , apiQuery);

							// Callback function
							apiCallback = function(data) {
								console.log("CALLBACK : data", data);
								console.log("header");
								console.log(data['header']);

								// Generate data
								var hColumnsData = [];
								for (var i=0; i < data["header"].xSize; i++) {
									hColumnsData[i] = { id : {string : data.data[i], level: level }, label: data.data[i], type : 'folder'}
								}
								console.log("hColumns data: ", hColumnsData);
								return callback(null, hColumnsData);
							}
						}	

							// Do the call
							api.query( apiQuery, apiCallback);
					}
				})
			}
		}
	}


	// Initialisation
	/////////////////
	conf.initDB();

    //setTimeout("alert ('called from setTimeout()');",4000);

	ui.updateLayout();
	//console.log();
	$().delay(1000);
	//console.log();
	ui.browser.init();
	//ui.browser.append(0, "all");
	//ui.browser.append(1);
	//ui.browser.append(2);
	//ui.browser();
	//ui.browser();
	//ui.browser();

	// Define UI events
	/////////////////////

	// When panel are resized
	ui.Aside.resizable({
		handles: 'w',
		stop: ui.updateContent(),
		resize: ui.updateContent() 
	});

	// When window is resized
	$(window).resize(ui.updateLayout);



	console.log("Initialised!");
});


//
//function() {
//
//
//// UI Functions Namespace
//
//	this.conf = function() {
//		this.db = {},
//		
//		this.init = function(data) {
//			ify.conf.db = data;
//			console.log('Conf loaded!');
//			console.log(data);
//		};
//
//		this.getNav = function(level, dataType) {
//			db = ify.conf.db;
//			level = level || 0;
//			dataType = dataType || 'group';
//
//			//this.db.user.path[0] = this.db.user.path[0] || "ERROR";
//			console.log("Asking Conf")
//			console.log(ify.conf.db);
//
//			//if (typeof db.global.nav[0] != 'undefined')
//			nav =  db.global.nav[0];
//
//			nav = nav.split(',');
//			nav = nav[level];
//			if (dataType == 'columns') {
//				return nav;
//			} else {
//				nav = nav.split(' ');
//				return nav[0]
//			};
//
//		}
//		};
//
//	this.ui = function() {
//		this.updateLayout = function() {
//			
//			ify.ui.Wrapper.layout({resize: false, type: 'border', vgap: 0, hgap: 8});
//			ify.ui.Aside.css("max-width", ify.ui.Wrapper.width() - 50 + 'px');
//			ify.ui.updateContent();
//		};
//
//		this.updateContent = function() {
//			ify.ui.Nav.css('width', (ify.ui.Wrapper.width() - ify.ui.Aside.width() + 'px'));
//		};
//	
//		this.createBrowser = function(level) {
//			ify.ui.Nav.prepend('<div data-level=' + level + ' class="ui-col" ><table></table></div>');
//			ify.conf.getNav();
//
//			console.log("Coucou");
//			// Get artist list
//			ify.api.request({'f': 'uiBrowse','o': 'html-col', 'search': 'all', 'columns': 'genre count-album', 'group' : 'genre'},
//				function (data) {
//					console.log("Table received:")
//					console.log(data)
//					ify.ui.Nav.find('table').prepend(data)
//					ify.ui.Nav.find('table tr').on('click', function(e){
//						ify.ui.createBrowser
//						})
//
//				},
//				'html'
//			)
//		};
//
//		this.browser = function() {
//			this.that = this;
//
//			this.init = function() {
//				level = 0;
//				ify.ui.Nav.prepend('<div data-level=' + level + ' class="ui-col" ><table></table></div>');
//
//				ify.api.request({'f': 'uiBrowse','o': 'html-col', 'search': 'all', 'columns': this.conf.getNav(level, 'columns'), 'group' : this.conf.getNav(level, 'group')},
//					function (data) {
//						ify.ui.Nav.find('table').prepend(data)
//						ify.ui.Nav.find('table tr').on('click', function(e){
//							that.browser
//							})
//					},
//					'html'
//				)
//			};
//
//			this.click = function(){
//				
//			};
//		}
//		
//	};
//
//	this.api = function() {
//		this.url = 'api.php';
//
//		// Main request
//		this.request = function (args, callback, type) {
//			
//			// Define default arguments
//			args = args || {};
//			callback = callback || function() {};
//			type = type || 'json';
//
//			// Do the request
//			$.get(this.url,
//				args,
//				function(data){
//					//console.log("Answer received: ", data);
//					console.log("Call pack from API");
//					callback(data);
//				},
//				type);
//		};
//
//		this.arequest = function (args, callback, type) {
//			
//			// Define default arguments
//			args = args || {};
//			callback = callback || function() {};
//			type = type || 'json';
//
//			// Do the request
//			$.ajax({
//				type: 'GET',
//				url : this.url,
//				data : args,
//				success : function(data){
//					//console.log("Answer received: ", data);
//					console.log("Call pack from API");
//					callback(data);
//				},
//				async: false,
//				dataType: type}
//			);
//		};
//
//		this.importConfig = function() {
//			this.arequest({'f' : 'confDump'}, ify.conf.init, 'json');
//		};
//	}
//
//
//// Initialisation
//    this.init = function() {
//
//	// Define main UI elements
//	this.ui.Window = $(window);
//	this.ui.Wrapper = $('#ui-wrapper');
//	this.ui.Header = $('header');
//	this.ui.Nav = $('nav');
//	this.ui.Aside = $('aside');
//	this.ui.Footer = $('footer');
//
//
//	// Define main UI elements behaviour
//
//	this.ui.Aside.resizable({
//		handles: 'w',
//		stop: this.ui.updateContent(),
//		resize: this.ui.updateContent() 
//	});
//
//
//	// Initialisation
//	this.api.importConfig();
//	this.ui.updateLayout();
//	//this.ui.createBrowser(0);
//	this.ui.browser.init(0);
//
//	// Events
//	$(window).resize(this.ui.updateLayout);
//
//
//	console.log("Initialised!");
//
//	}
// 
//};







