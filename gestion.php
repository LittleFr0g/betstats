<?php
	require_once('_functions.php');
?>


<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
    
<head>
	<title>Gestion</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	
	<link rel="stylesheet" href="css/flick/jquery-ui-1.8.23.custom.css" type="text/css" media="all" />
	<link rel="stylesheet" href="css/betstats.css" type="text/css" media="all" />
	<link rel="stylesheet" href="bootstrap/css/bootstrap.css" type="text/css" media="all" />

	<script src="jquery/jquery-1.8.0.min.js" type="text/javascript"></script>
	<script src="jquery/jquery-ui-1.8.23.custom.min.js" type="text/javascript"></script>
	<script src="bootstrap/js/bootstrap.js" type="text/javascript"></script>

</head>
    
<body id="page_gestion">

	<?php 
	
	define("CHAMPIONNAT_AUTRE", 9);
	define("CHAMPIONNAT_MIXTE", 10);
	define("CHAMPIONNAT_COUPE", 12); 
	?>

	<!-- Menu Bootstrap -->
	<div class="navbar navbar-inverse">
	  <div class="navbar-inner">
	    <a class="brand" href="paris_view.php">BetStats</a>
	    <ul class="nav">
	      	<li><a href="paris_view.php">Historique</a></li>
	      	<li><a href="paris.php">Parier</a></li>
	      	<li><a href="statistiques.php">Statistiques</a></li>
			<li class="active"><a href="gestion.php">Gestion</a></li>	    
		</ul>
	  </div>
	</div>

	<h2>Gestion</h2>

	<div id="divButtons">
		<button id='addEquipe' class='btn'>Ajouter une équipe</button>
		<button id='addChampionnat' class='btn'>Ajouter un championnat</button>
	</div>
	
	<div id="gestion">

		<div id="accordeon_championnat">
			<div id="accordion">
				<?php
					connect();
					$queryChampionnat = mysql_query('SELECT ID_CHAMPIONNAT, LIB_CHAMPIONNAT FROM CHAMPIONNAT ORDER BY LIB_CHAMPIONNAT');
						while ($backChampionnat = mysql_fetch_array($queryChampionnat)) {
							$id_championnat = (int)$backChampionnat['ID_CHAMPIONNAT'];
							if($id_championnat == CHAMPIONNAT_MIXTE) {
								continue;
							}
							else if($id_championnat == CHAMPIONNAT_AUTRE){
								$queryEquipes=mysql_query('SELECT E.ID_EQUIPE, E.LIB_EQUIPE FROM EQUIPE E WHERE NOT EXISTS 
									(SELECT NULL FROM EQUIPE_CHAMPIONNAT EC WHERE E.ID_EQUIPE = EC.ID_EQUIPE ) ORDER BY LIB_EQUIPE');
							}
							else if($id_championnat == CHAMPIONNAT_COUPE){
								$queryEquipes=mysql_query('SELECT E.ID_EQUIPE, E.LIB_EQUIPE FROM EQUIPE E WHERE EXISTS 
									(SELECT NULL FROM EQUIPE_CHAMPIONNAT EC WHERE E.ID_EQUIPE = EC.ID_EQUIPE ) ORDER BY LIB_EQUIPE');
							}
							else{								
								$queryEquipes=mysql_query('SELECT 	E.ID_EQUIPE, E.LIB_EQUIPE FROM EQUIPE E, EQUIPE_CHAMPIONNAT C WHERE E.ID_EQUIPE=C.ID_EQUIPE AND C.ID_CHAMPIONNAT='.$id_championnat.' ORDER BY LIB_EQUIPE');
							}
							echo '<h3><a href="#">'.$backChampionnat['LIB_CHAMPIONNAT'].'</h3>';							
							echo '<div id="draggable">';
								echo '<ul>';
								while ($backEquipes = mysql_fetch_array($queryEquipes)) {
									echo '<li class="equipe" value='.$backEquipes['ID_EQUIPE'].'>';
										echo '<a href="#">'.$backEquipes['LIB_EQUIPE'].'</a>';
									echo '</li>';
								}
								echo '</ul>';
							echo '</div>';
						}
					deconnect();
				?>
			</div>
		</div>

		

		<div id="edit_equipe">
			<input type="text" id="id_equipe" style="display:none"></input>
			<h3 id="h3LibelleEquipe"></h3>
			<ul id="listChampionnats"></ul>
			<div id="listChampionnatsToAdd"></div>
			<div id="infos_equipe">
				<ul id="listInfosParisEquipe"></ul>
			</div>
		</div>

		<!-- Popup d'insertion d'une equipe-->
		<div id="dialog-equipe" title="Ajouter une équipe">
			<form>
				<fieldset>
					<p>
						<label for="name">Nom</label>
						<input type="text" name="name" id="name" class="text ui-widget-content ui-corner-all" />
					</p>
					<p>
						<label>Championnat : </label> 
						<select name="championnat" id="championnat">
						<option value=""></option>
						<?php
							connect();
							$query = mysql_query('SELECT ID_CHAMPIONNAT, LIB_CHAMPIONNAT FROM CHAMPIONNAT ORDER BY LIB_CHAMPIONNAT');
							while ($back = mysql_fetch_array($query)) {
								echo '<option value="'.$back['ID_CHAMPIONNAT'].'" ';
								echo '>'.$back['LIB_CHAMPIONNAT'].'</option>';
							}
							deconnect();
						?>
						</select>
					</p>
				</fieldset>
			</form>
		</div>

		<!-- Popup d'insertion d'un championnat-->
		<div id="dialog-championnat" title="Ajouter un championnat">
			<form>
				<fieldset>
					<p>
						<label for="name">Nom</label>
						<input type="text" name="name" id="name" class="text ui-widget-content ui-corner-all" />
					</p>
				</fieldset>
			</form>
		</div>


		
	</div>

	<script>
	$(function() {
		$("#accordion").accordion({
			collapsible: true,
			autoHeight: false,
			navigation: true,
			active:false
		});

		$( "#draggable li" ).draggable({ 
			revert: "valid" 
		});
		
		$( "#edit_equipe").droppable({
			accept: "#draggable li",
			drop: function( event, ui ) {
				clean();
				$( this ).find( "#id_equipe" ).text(ui.draggable.val());
				$( this ).find( "#h3LibelleEquipe" ).text(ui.draggable.text());
				$( this ).find( "#h3LibelleEquipe" ).attr('class','h3LibelleEquipe');

				//On affiche tout d'abord la liste des championnats auquelle appartient cette équipe
				$.ajax({
					url: 'ajax_requetes.php',
					data: { id_equipe:ui.draggable.val() , requete:10} ,
					success: function(response){buildListeChampionnats(response)},
					dataType: 'html'
				});

				//On ajoute ce qu'il faut pour qu'on puisse ajouter cette équipe à un championnat
				$.ajax({
					url: 'ajax_requetes.php',
					data: { id_equipe:ui.draggable.val() , requete:12} ,
					success: function(response){buildListeChampionnatsToAdd(response)},
					dataType: 'html'
				});

				//On ajoute maintenant les infos sur les paris qu'on a fait sur cette équipe
				$.ajax({
					url: 'ajax_requetes.php',
					data: { id_equipe:ui.draggable.val() , requete:11} ,
					success: function(response){buildInfosPariEquipe(response)},
					dataType: 'html'
				});
			}
		});

		//Popup equipe
		$( "#dialog-equipe" ).dialog({
			autoOpen: false,
			height: 280,
			width: 300,
			modal: true,
			closeOnEscape: true,
			resizable: false,
			buttons: {
				"Ajouter l'équipe": function() {
					if(($( this ).find('#name').val().length > 0) && ($( this ).find('#championnat').val() != "")){
						$.ajax({
							url: 'ajax_requetes.php',
							data: { lib_equipe:$( this ).find('#name').val() , id_championnat:$( this ).find('#championnat').val() , requete:15} ,
						});
					}
					$( this ).dialog( "close" )		
				},
				"Annuler": function() {
					$( this ).dialog( "close" );
				}
			}
		});

		//Popup championnat
		$( "#dialog-championnat" ).dialog({
			autoOpen: false,
			height: 200,
			width: 300,
			modal: true,
			closeOnEscape: true,
			resizable: false,
			buttons: {
				"Ajouter le championnat": function() {
					if($( this ).find('#name').val().length > 0){
						$.ajax({
							url: 'ajax_requetes.php',
							data: { lib_championnat:$( this ).find('#name').val() , requete:16} ,
						});
					}
					$( this ).dialog( "close" )		
				},
				"Annuler": function() {
					$( this ).dialog( "close" );
				}
			}
		});

		//ouverture de la popup ce création d'équipe
		$( "#addEquipe" ).button().click(function() {
				$( "#dialog-equipe" ).dialog( "open" );
		});

		//ouverture de la popup ce création d'équipe
		$( "#addChampionnat" ).button().click(function() {
				$( "#dialog-championnat" ).dialog( "open" );
		});

		function clean(){
			$('#listInfosParisEquipe').empty();
			$('#saveTeam').remove();
			$('#infos_equipe').find('h4').remove();
		}


		function buildListeChampionnats(listChampionnats){
			$('#listChampionnats').empty();
			var championnats = listChampionnats.split(';');

			for(j=0;j<championnats.length-1;j++){
				var id_championnat = championnats[j].split('*')[0];
				var lib_championnat = championnats[j].split('*')[1];
				var li = $('<li>').attr('value',id_championnat).attr('class','championnat').html(lib_championnat);
				var a = $('<a>').attr('href','#').attr('class','lienSupprimer');
				a.bind('click', function(e){
					//On supprime l'effet de remonter dans la page quand on supprime un championnat à une equipe
					e.preventDefault();

					//On supprime l'equipe du championnat choisi
					$.ajax({
						url: 'ajax_requetes.php',
						data: { id_equipe:$('#id_equipe').text() , id_championnat:$(this).parent().val(), requete:14} ,
					});
					$(this).parent().remove();

					//On recharge la liste des championnats auquelle on peut ajouter l'equipe
					$.ajax({
						url: 'ajax_requetes.php',
						data: { id_equipe:$('#id_equipe').text() , requete:12} ,
						success: function(response){buildListeChampionnatsToAdd(response)},
						dataType: 'html'
					});	
				});
				var img = $('<img>').attr('src','images/fermer.png').attr('alt','Supprimer');
				a.append(img);
				li.append(a);
				$("#listChampionnats").append(li);
			}
		}

		function buildListeChampionnatsToAdd(listChampionnats){
			$('#listChampionnatsToAdd').empty();
			var h4=$('<h4>').html('Ajouter un championnat').attr('class','h4ChampionnatToAdd');
			var championnats = listChampionnats.split(';');

			var select = $('<select>').attr('name','championnatToAdd').attr('id','championnatToAdd');
			var optionVide = $('<option>').attr('value',0);
			select.append(optionVide);

			for(j=0;j<championnats.length-1;j++){
				var id_championnat = championnats[j].split('*')[0];
				var lib_championnat = championnats[j].split('*')[1];
				var option = $('<option>').attr('value',id_championnat).html(lib_championnat);
				select.append(option);
			}

			var buttonAdd = $('<input>').attr('name','addChampionnat').attr('id','addChampionnat').attr('type','button').attr('value','Ajouter').attr('class','btn');
			
			//Action de clic sur le bouton d'ajout à un championnat
			buttonAdd.bind('click', function(){
				if($('#championnatToAdd').val() != 0){
					$.ajax({
						url: 'ajax_requetes.php',
						data: { id_equipe:$('#id_equipe').text(), id_championnat:$('#championnatToAdd').val() , requete:13}
					});
				}
				//On recharge la liste des championnats auquelle l'equipe appartient
				$.ajax({
					url: 'ajax_requetes.php',
					data: { id_equipe:$('#id_equipe').text() , requete:10} ,
					success: function(response){buildListeChampionnats(response)},
					dataType: 'html'
				});
				//On recharge la liste des championnats auquelle on peut ajouter l'equipe
				$.ajax({
					url: 'ajax_requetes.php',
					data: { id_equipe:$('#id_equipe').text() , requete:12} ,
					success: function(response){buildListeChampionnatsToAdd(response)},
					dataType: 'html'
				});
			});

			var div = $('<div>').attr('id','divAddChampionnat');
			var divSelect = $('<div>').attr('id','divSelect');
			var divButtonAdd = $('<div>').attr('id','divButtonAdd');
			divSelect.append(select);
			divButtonAdd.append(buttonAdd);
			div.append(divSelect);
			div.append(divButtonAdd);
			$('#listChampionnatsToAdd').append(h4);
			$('#listChampionnatsToAdd').append(div);
		}

		function buildInfosPariEquipe(infosMatchs){
			var h4=$('<h4>').html('Statistiques').attr('class','h4ChampionnatToAdd');
			var nbMatchs = infosMatchs.split('*')[0];
			var nbMatchsReussis = infosMatchs.split('*')[1];
			if(nbMatchs != 0) var pourcentageReussite = ((nbMatchsReussis/nbMatchs) * 100).toFixed(2);
			else var pourcentageReussite = 0;
			var liNbMatchs = $('<li>').html('Nombre de matchs : '+nbMatchs);
			var liNbMatchsReussis = $('<li>').html('Nombre de matchs réussis : '+nbMatchsReussis);
			var liPourcentage = $('<li>').html('Pourcentage de réussite : '+pourcentageReussite+' %');

			$('#infos_equipe').prepend(h4);
			$('#listInfosParisEquipe').append(liNbMatchs);
			$('#listInfosParisEquipe').append(liNbMatchsReussis);
			$('#listInfosParisEquipe').append(liPourcentage);

		}
	});


	</script>


</body>
</html>
