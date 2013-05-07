<!DOCTYPE html>
<html lang="en">

	<head>
		<meta charset="utf-8">
		<title>Ify music player</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="">
		<meta name="author" content="">

		<!-- Le styles -->
		<link rel="stylesheet" href="lib/bootstrap/css/bootstrap.css">
		<link rel="stylesheet" href="lib/font-awesome/css/font-awesome.min.css">
		<link rel="lib/melement/stylesheet" href="mediaelementplayer.css" />

		<link rel="stylesheet" href="lib/ify/style2.css">


		<!-- Le fav and touch icons 
		<link href="assets/css/bootstrap-responsive.css" rel="stylesheet">
		<link rel="shortcut icon" href="assets/ico/favicon.ico">
		<link rel="apple-touch-icon-precomposed" sizes="144x144" href="assets/ico/apple-touch-icon-144-precomposed.png">
		<link rel="apple-touch-icon-precomposed" sizes="114x114" href="assets/ico/apple-touch-icon-114-precomposed.png">
		<link rel="apple-touch-icon-precomposed" sizes="72x72" href="assets/ico/apple-touch-icon-72-precomposed.png">
		<link rel="apple-touch-icon-precomposed" href="assets/ico/apple-touch-icon-57-precomposed.png">
		-->
	  </head>




  <body><div id="ify">

<!-- Header: Begin  -->
  <header class="row-fluid navbar-fixed-top" id="ui_Player">
    		<div class="span12" id="ui_Header">
    			<div class="row-fluid">
    				<div class="span8" id="btn_control">
					<div id="btn_backward" class="btn_controls">
						<i class="icon-backward icon-2x"></i>
					</div>
					<div id="btn_play" class="btn_controls">
						<i class="icon-play icon-3x"></i>
					</div>
					<div class="btn_controls" id="btn_forward">
						<i class="icon-forward icon-2x"></i>
					</div>
					<div class="btn_controls" id="btn_vol_down">
						<i class="icon-volume-down"></i>
					</div>

					<div class="btn_controls">
    						<div class="progress active" id="btn_vol">
    						<div class="bar" style="width: 50%" ></div>
    						</div>
    					</div>

					<div class="btn_controls" id="btn_vol_down">
						<i class="icon-volume-up"></i>
					</div>

    					<p class="btn_controls">Listening: Hadouk Trio</p>
    				</div>
    				<div class="span4">
    					<p>User actions</p>
    				</div>
    			</div>
    		</div>
  </header>

<!-- Main: Begin  -->
    	<div class="row-fluid" id="ui_Main">

	<!-- ui_Browser: BEGIN -->
    			<div class="span4" id="ui_Browser">

	<!-- ui_Browser_button: BEGIN -->
    				<div class="row-fluid">
					<div class="span6">
						<select class="pull-left">
							<option value="pizza">
								Pizza
							</option>
							<option value="salad">
								Salad
							</option>
							<option value="pizzasalad">
								Pizza and Salad
							</option>
						</select>
					</div>

					<div class="span6" style="display: block;">
						<div class="btn-group pull-right">
							<a href="#" class="btn" id="ui_Browser_back"><i class="icon-arrow-left"></i></a>
							<a href="#" class="btn" id="ui_Browser_refresh"><i class="icon-refresh"></i></a>
						</div>
					</div>
				</div>


	<!-- ui_Browser_list: BEGIN -->
				<div class="row-fluid" id="ui_Browser_content">
					<div class="span12">
						<table class="table">
							<tbody id="ui_Browser_list">
								<tr>
									<td>
										List
									</td>
									<td >
										Action
									</td>
									
									
								</tr>
							</tbody>
						</table>
					</div>
				</div>


	<!-- ui_Browser_bottom: BEGIN -->
    				<div class="row-fluid">
    					<div class="span6">
    						<p class="pull-left">
    						Left buttons
    						</p>
    					</div>
    					<div class="span6" style="display: block;">
    						<p class="pull-right">
    							Right buttons
    						</p>
    					</div>
    				</div>

			</div>

<!-- ui_Playlist: BEGIN -->
    		<div class="span8" id="ui_Music">
    			<div class="row-fluid">
    				<div class="span8" style="display: block;">
    					<ul class="breadcrumb" id="ui_breadcrumbs">
    						<li>
    							<span class="divider">/</span>
    						</li>
    					</ul>
    				</div>
    				<div class="span4" style="display: block;">
    					<div class="btn-toolbar">
    						<div class="btn-group">
    							<a href="#" class="btn">Play All</a>
    							<a href="#" class="btn">pl<br></a>
    						</div>
    						<div class="btn-group">
    							<a href="#" class="btn">del</a>
    						</div>
    					</div>
    				</div>
    			</div>
    			<div class="row-fluid">
    				<div class="span4" style="display: none;">
    					<h3>
    						Span 4
    					</h3>
    					<p>
    						Content
    					</p>
    				</div>
    			</div>
    			<table class="table">
    				<tbody>
    					<tr>
    					</tr>
    				</tbody>
    			</table>
	<!-- ui_Playlist_files: BEGIN -->
    			<div class="row-fluid">
    				<div class="span12" style="display: block;">
    					<table class="table">
						<thead>
							<tr>
							<th>file</th>
							<th>artist</th>
							<th>song</th>
							<th>album</th>
							</tr>
						</thead>
    						<tbody id="ui_Browser_files">
    							<tr>
    								<td>
    									File
    								</td>
    								<td>
    									Artist
    								</td>
    								<td>
    									Title
    								</td>
    								<td>
    									Album
    								</td>
    							</tr>
    						</tbody>
    					</table>
    				</div>
    			</div>
    			<div class="row-fluid">
    				<div class="span12" style="display: block;">
    				</div>
    			</div>
    			<div class="row-fluid">
    				<div class="span8" style="display: block;">
    					<p class="">
    						Infos on selected files
    					</p>
    				</div>
    				<div class="span4 text-right" style="display: block;">
    					bouttons
    				</div>
    			</div>
    		</div>

    	</div>
<!-- ui_Footer: BEGIN -->
    	<footer class="row-fluid">
    		<div class="span12">
    			<p class="">
    				Footer :-)
    			</p>
    		</div>
    	</footer>
    </div>

		<!-- Scripts -->
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
		<script type="text/javascript" src="lib/bootstrap/js/bootstrap.js"></script>
		<script src="lib/melement/mediaelement-and-player.min.js"></script>

		<script type="text/javascript" src="lib/ify/scripts2.js"></script>


  </div></body>
</html>

