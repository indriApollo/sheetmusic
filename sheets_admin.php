<?php
require_once("inc/sheets_functions.php");

user_login("sheets_admin.php");
if(!$_SESSION["isAdmin"]) {
	http_response_code(403);
	die("Vous n'avez pas les droits d'administration");
}
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
			<li class="nav-item navbar-right"><h3>Bibiothèque musicale | Administration</h3></li>
		</ul>
	</nav>
	<br>
	<div class="card">
		<div class="card-header"><h4>Gérer les utilisateurs</h4></div>
			<div class="card-block">

	<div class="row">
		<div class="veil" id="instruments-veil">
			<div class="loader"></div>
		</div>
		<div class="col-md-5 sidebar">
			<form class="form-inline">
			<input class="form-control" type="text" oninput="filter(this.value,users)" placeholder="chercher">
			</form>
			<ul class="list-unstyled list-selector" id="users"></ul>
		</div>
		
		<div class="col-md-7">
			<ul class="list-unstyled list-selector" id="instruments"></ul>
			<button type="button" class="btn btn-primary" onclick="saveEdit()">sauvegarder</button>
		</div>
	</div>

		</div>
	</div>

	<div class="card">
		<div class="card-header"><h4>Ajouter un morceau</h4></div>
			<div class="card-block">

		<form class="form-inline">
		<input class="form-control" type="text" oninput="filter(this.value,titles)" placeholder="titre" id="musicTitle">
		<button type="button" class="btn btn-primary" onclick="saveMusic()">Créer</button>
		</form>
		<ul class="list-unstyled list-selector" id="titles"></ul>

		</div>
	</div>

	<nav class="navbar nav-center">
		<ul class="nav nav-inline">
			<li class="nav-item"><a class="nav-link" href="index.php">retourner vers la bibliothèque</a></li>
			<li class="nav-item"><a class="nav-link" href="<?php echo LOGOUTURL; ?>">Se déconnecter</a></li>
		</ul>
	</nav>
</div>

<script type="text/javascript">

	var highlightedUser;
	var users = [];
	var instruments = [];
	var titles = [];

	function editUser(el) {

		if(highlightedUser)
			highlightedUser.className = "";

		highlightedUser = el;
		el.className = "highlighted";

		document.getElementById("instruments-veil").style.display = "block";

		var url = "inc/sheets_adminjx.php";
		var args = "cmd=user_get_instruments&email="+el.id;
		xhrequest(url,args,function(status,resp) {
			if(status == 200) {
				var r = JSON.parse(resp);
				for(var i = 0; i < instruments.length; i++) {
					var instrument = instruments[i];
					var el = document.getElementById(instrument+"_checkbox");
					if(r.indexOf(instrument) !== -1)
						el.checked = true;
					else
						el.checked = false;
				}
			} else {
				alert(resp);
				
			}
			document.getElementById("instruments-veil").style.display = "none";
		});
	}

	function saveEdit() {
		if(!highlightedUser)
			return;

		document.getElementById("instruments-veil").style.display = "block";

		var userInstruments = "";
		for(var i = 0; i < instruments.length; i++) {
			var instrument = instruments[i];
			var el = document.getElementById(instrument+"_checkbox");
			if(el.checked)
				if(!userInstruments)
					userInstruments = instrument;
				else
					userInstruments += ","+instrument
		}

		var url = "inc/sheets_adminjx.php";
		var args = "cmd=update_user&email="+highlightedUser.id+"&instruments="+userInstruments;
		xhrequest(url,args,function(status,resp) {
			if(status == 201) {
				alert("Sauvegardé");
			} else {
				alert(resp);
			}
			document.getElementById("instruments-veil").style.display = "none";
		});
	}

	function saveMusic() {
		var title = document.getElementById("musicTitle").value;
		if(!title)
			return;
		title = title.trim();
		var url = "inc/sheets_adminjx.php";
		var args = "cmd=add_music&title="+encodeURIComponent(title);
		xhrequest(url,args,function(status,resp) {
			if(status == 201) {
				alert("Créé");
				for(var i = 0;i < titles.length; i++) {
					if(titles[i].localeCompare(title) == 1) {
						titles.splice(i,0,title) //insert title before the first item coming after title in alphabetical order
						var refnode = document.getElementById(titles[i+1]); //ref to first element coming after title in alphabetical order
						var tr = document.createElement("tr");
						tr.id = title;
						var td = document.createElement("td");
						td.textContent = title;
						tr.appendChild(td);
						refnode.parentElement.insertBefore(tr,refnode);
						break;
					}
				}
			} else {
				alert(resp);
			}
		});
	}

	function loadUsers() {
		var url = "inc/sheets_adminjx.php";
		var args = "cmd=get_users";
		xhrequest(url,args,function(status,resp) {
			if(status == 200) {
				var cont = document.getElementById("users");
				users = JSON.parse(resp);
				for(var i = 0;i < users.length; i++) {
					var email = users[i];
					var li = document.createElement("li");
					li.id = email;
					li.onclick = function() {editUser(this)};
					li.textContent = email;
					cont.appendChild(li);
				}
			} else {
				alert(resp);
			}
		});
	}

	function loadInstruments() {
		var url = "inc/sheets_adminjx.php";
		var args = "cmd=get_instruments";
		xhrequest(url,args,function(status,resp) {
			if(status == 200) {
				var cont = document.getElementById("instruments");
				instruments = JSON.parse(resp);
				for(var i = 0;i < instruments.length; i++) {
					var instrument = instruments[i];
					var li = document.createElement("li");
					li.id = instrument;

					var checkbox = document.createElement("input");
					checkbox.type = "checkbox";
					checkbox.id = instrument+"_checkbox";
					li.appendChild(checkbox);

					var label = document.createElement("label");
					label.textContent = instrument;
					label.setAttribute("for", checkbox.id);
					li.appendChild(label);

					cont.appendChild(li);
				}
			} else {
				alert(resp);
			}
		});
	}

	loadUsers();
	loadInstruments();
	loadTitles();

</script>
</body>
</html>
