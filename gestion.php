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

	<h2>TO DO</h2>

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
								$queryEquipes=mysql_query('SELECT E.ID_EQUIPE, E.LIB_EQUIPE FROM EQUIPE E ORDER BY LIB_EQUIPE');
							}
							else{								
								$queryEquipes=mysql_query('SELECT 	E.ID_EQUIPE, E.LIB_EQUIPE FROM EQUIPE E, EQUIPE_CHAMPIONNAT C WHERE E.ID_EQUIPE=C.ID_EQUIPE AND C.ID_CHAMPIONNAT='.$id_championnat.' ORDER BY LIB_EQUIPE');
							}
							echo '<h3><a href="#">'.$backChampionnat['LIB_CHAMPIONNAT'].'</h3>';							
							echo '<div id="draggable">';
								echo '<ul>';
								while ($backEquipes = mysql_fetch_array($queryEquipes)) {
									echo '<li class="equipe">';
										echo '<a href="gestion.php?equipe='.$backEquipes['ID_EQUIPE'].'">'.$backEquipes['LIB_EQUIPE'].'</a>';
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
			<h3 id="h3LibelleEquipe"></h3>

			
		</div>

		
	</div>

	<script>
	$(function() {
		$("#accordion").accordion({
			collapsible: true,
			autoHeight: false,
			navigation: true
		});
		$( "#draggable li" ).draggable({ 
			revert: "invalid" 
		});
		
		$( "#edit_equipe").droppable({
			drop: function( event, ui ) {
				$( this ).find( "#h3LibelleEquipe" ).text(ui.draggable.text());

				
			}
		});
	});


	</script>




</body>
</html>
