<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<title>Ify music player</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="">
	<meta name="author" content="">

	<!-- Le styles -->
	<link rel="stylesheet" href="../lib/bootstrap/css/bootstrap.css">
	<link rel="stylesheet" href="../lib/font-awesome/css/font-awesome.min.css">
	<link rel="../lib/melement/stylesheet" href="mediaelementplayer.css" />

	<link rel="stylesheet" href="../lib/ify/style3.css">
</head>


<body><div id="ify">

<!-- Header: Begin  -->
	<header class="row-fluid navbar-fixed-top" id="ui_Player">
		<div class="span12" id="ui_Header">
			<div class="row-fluid">

					<div class="btn-group">
						<a href="#" class="btn"><i class="icon-backward"></i></a>
						<a href="#" class="btn"><i class="icon-play"></i></a>
						<a href="#" class="btn"><i class="icon-forward"></i></a>
					</div>

					<div class="input-prepend form-search input-append" id="search-bar">
						<button class="btn">Global</button>
						<input type="text" data-provide="typeahead">
						<button type="submit" class="btn submit"><i class="icon-search"></i></button>
					</div>
					
					<audio id="player1" src="../media/AirReview-Landmarks-02-ChasingCorporate.mp3" type="audio/mp3"></audio>

			</div>
		</div>
	</header>

<!-- Main: Begin  -->
    	<div class="row-fluid" id="main-wrapper">

			<nav>
<!-- Pane: Left -->
			Left Pane
			</nav><content>
<!-- Pane: Right -->
				<table class="table" id="content-playlist-table">
					<thead>
						<tr>
							<th>ID</th>
							<th>Title</th>
							<th>Artist</th>
							<th>Album</th>
							<th>Genre</th>
							<th>Year</th>
						</tr>
					</thead>

					<tbody>
						<tr>
							<td>Content</td>
							<td>Content</td>
							<td>Content</td>
							<td>Content</td>
							<td>Content</td>
						</tr>
					</tbody>
				</table>
			</content>

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
		<script src="http://code.jquery.com/jquery-1.9.1.min.js"></script>
		<script type="text/javascript" src="../lib/bootstrap/js/bootstrap.js"></script>
		<script type="text/javascript" src="../lib/melement/mediaelement.js"></script>

		<script type="text/javascript" src="../lib/ify/scripts3.js"></script>

		<script type="text/javascript">
			
		</script>

</div></body>
</html>

