<!DOCTYPE html>
<html>
  <head>
    <title>Ify</title>
    <!-- Bootstrap -->
	<link href="lib/bootstrap/css/bootstrap.css" rel="stylesheet" >
	<link href="lib/ify/style.css" rel="stylesheet" type="text/css">

	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>

	<!-- script src="jquery.js"></script -->
	<script src="lib/melement/mediaelement-and-player.min.js"></script>
	<link rel="lib/melement/stylesheet" href="mediaelementplayer.css" />

  </head>

  <body>


<div id="ui_main"  class="container-fluid">


<!-- video src="zik2.mp3" width="320" height="240"></video -->


<div class="audio-player">
    <h1>Demo - Preview Song</h1>
    <img id="cover" src="img/cover.png" alt="">
    <audio id="me_player" src="zik.mp3" type="audio/mp3" controls="controls"></audio>
</div>


<div class="btn-group">
  <button id="ui_play" class="btn">Play</button>
  <button id="ui_pause" class="btn">Pause</button>
  <button class="btn">Right</button>
</div>




<!-- START: Left pane -->
<div class="span4">


<div class="btn-toolbar">
	<div class="btn-group pull-left">	
	<a class="btn dropdown-toggle" data-toggle="dropdown" href="#">Browse<span class="caret"></span></a>
	<ul class="dropdown-menu">
	<!-- dropdown menu links -->
		<li>Files</li>
		<li>Artists</li>
		<li>Albums</li>
	</ul>
	</div>

	<div class="btn-group pull-right">	
	<a class="btn" href="#" id="ui_browser_back"><i class="icon-arrow-up"></i></a>
	<a class="btn" href="#" id="ui_browser_refresh"><i class="icon-refresh"></i></a>
	</div>
</div>


<table class="table table-hover table-condensed">
	<tbody id="ui_browser_list">
		<tr><td><a href="#">test1</a></td></tr>
		<tr><td><a href="#">test2</a></td></tr>
		<tr><td><a href="#">test3</a></td></tr>
		<tr><td><a href="#">test4</a></td></tr>
		<tr><td><a href="#">test5</a></td></tr>
	</tbody>
</table>

</div>
<!-- END: Left pane -->


<!-- START: Right pane -->
<div class="span8">
	<ul class="breadcrumb pull-left" id="ui_breadcrumbs">
				<li><a href="#">Home</a> <span class="divider">/</span></li>
				<li><a href="#">Library</a> <span class="divider">/</span></li>
				<li class="active">Data</li>
	</ul> 
	<table class="table .table-striped .table-hover .table-condensed" id="ui_list">
  <caption>Music files</caption>
  <thead>
    <tr>
      <th>file</th>
      <th>artist</th>
      <th>song</th>
      <th>album</th>
    </tr>
  </thead>

  <tbody id="ui_browser_files">
    <tr>
      <td>col1</td>
      <td>col1</td>
      <td>col2</td>
      <td>col2</td>
    </tr>
    <tr>
      <td>col1</td>
      <td>col1</td>
      <td>col2</td>
      <td>col2</td>
    </tr>
    <tr>
      <td>col1</td>
      <td>col1</td>
      <td>col2</td>
      <td>col2</td>
    </tr>
  </tbody>
		
	</table>

</div>
<!-- END: Right pane -->


</div>





<!-- Javascript -->
<script type="text/javascript" src="lib/bootstrap/js/bootstrap.js"></script>
<script type="text/javascript" src="lib/ify/scripts.js"></script>


<script type="text/javascript">
//	$('video,audio').mediaelementplayer(/* Options */);	
</script>


  </body>
</html>


