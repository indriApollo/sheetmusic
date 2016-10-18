<?php
require_once("inc/sheets_functions.php");
user_login("index.php");
?>
<!DOCTYPE html>
<head>
<meta charset="utf-8">
<title>RHJ sheetmusic DB</title>

<!-- BOOTSTRAP V4 ALPHA & DEPENDENCIES -->
<!-- JQUERY 3.1.1 -->
<script src="https://code.jquery.com/jquery-3.1.1.min.js" integrity="sha256-hVVnYaiADRTO2PzUGmuLJr8BLUSjGIZsDYGmIJLv2b8=" crossorigin="anonymous"></script>
<!-- BOOTSTRAP CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.4/css/bootstrap.min.css" integrity="sha384-2hfp1SzUoho7/TsGGGDaFdsuuDL0LX2hnUp6VkX3CUQ2K4K+xjboZdsXyp4oUHZj" crossorigin="anonymous">
<!-- BOOTSTRAP JS -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.4/js/bootstrap.min.js" integrity="sha384-VjEeINv9OSwtWFLAtmc4JCtEJXXBub00gtSnszmspDLCtC0I4z4nqz7rEFbIZLLU" crossorigin="anonymous"></script>
<!-- BOOTSTRAP & FRIENDS LOADED \[T]/ -->

<script src="sheets.js"></script>
<link rel="stylesheet" href="sheets.css">

<meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>

<div class="container">
	<nav class="navbar">
		<ul class="nav nav-inline">
			<li class="nav-item navbar-left"><img height="70" src="logo.svg" alt="logo"></li>
			<li class="nav-item navbar-right"><h3>Bibiothèque musicale | <?php echo $_SESSION["email"]; ?></h3></li>
		</ul>
	</nav>
	<br>
	<div class="row">
		<div class="col-md-4 sidebar">
			<form class="form-inline">
			<input class="form-control" type="text" oninput="filter(this.value,titles)" placeholder="chercher">
			</form>
			<ul class="list-unstyled list-selector" id="titles">
			</ul>
		</div>

		<div class="col-md-8">
			<div class="loader" id="sheets-loader"></div>
			<div class="list-group" id="sheets">
				<a href="#" class="list-group-item list-group-item-action list-group-item-info">Sélectionnez un morceau</a>
			</div>
		</div>
	</div>
	<br>
	<nav class="navbar nav-center">
		<ul class="nav nav-inline">
			<li class="nav-item"><a class="nav-link" href="sheets_admin.php">administration</a></li>
			<li class="nav-item"><a class="nav-link" href="<?php echo LOGOUTURL; ?>">Se déconnecter</a></li>
		</ul>
	</nav>
</div>

<script type="text/javascript">

	var highlightedTitle;
	var titles = [];

	function loadSheets(el) {

		if(highlightedTitle)
			highlightedTitle.className = "";

		highlightedTitle = el;
		el.className = "highlighted";

		var cont = document.getElementById("sheets");
		cont.innerHTML = ""; //clear previous

		var loader = document.getElementById("sheets-loader");
		loader.style.display = "block";

		var title = el.id;
		var url = "inc/sheets_jx.php";
		var args = "cmd=get_user_sheets&title="+encodeURIComponent(title);
		xhrequest(url,args,function(status,resp) {
			if(status == 200) {
				var sheets = JSON.parse(resp);
				for(var i = 0;i < sheets.length; i++) {
					var sheet = sheets[i];
					var a = document.createElement("a");
					a.className = "list-group-item list-group-item-action";
					a.setAttribute("href","/sheetmusic/inc/sheets_dl.php?title="+encodeURIComponent(title)+"&sheet="+sheet);
					a.textContent = sheet.replace(/_/g," "); //replace all _ with spaces


					loader.style.display = ""; //hide loader
					cont.appendChild(a);
				}
			} else {
				console.log(resp);
				var a = document.createElement("a");
				a.className = "list-group-item list-group-item-action list-group-item-danger";
				a.setAttribute("href","#");
				a.textContent = "Erreur serveur : "+resp+" ("+status+")";

				loader = ""; //hide loader
				cont.appendChild(a);
			}
		});
	}

	loadTitles();

</script>
</body>
</html>
