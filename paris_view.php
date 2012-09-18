<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
    
<head>
	<title>Paris - Historique</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <link rel="stylesheet" href="css/demo_table_jui.css" type="text/css" media="all" />   
    <link rel="stylesheet" href="css/betstats.css" type="text/css" media="all" />   
    <link rel="stylesheet" href="css/flick/jquery-ui-1.8.16.custom.css" type="text/css" media="all" /> 
    <link rel="stylesheet" href="bootstrap/css/bootstrap.css" type="text/css" media="all" />   
    
	<script src="jquery/jquery1.8.1.min.js" type="text/javascript"></script>
	<script src="jquery/jqueryui1.8.16.min.js" type="text/javascript"></script>
	<script src="jquery/jquery.dataTables.js" type="text/javascript"></script>
	<script src="bootstrap/js/bootstrap.js" type="text/javascript"></script>
	
	

		
		
</head>
    
<body>

	<!-- Menu Bootstrap -->
	<div class="navbar navbar-inverse">
	  <div class="navbar-inner">
	    <a class="brand" href="paris_view.php">BetStats</a>
	    <ul class="nav">
	      <li class="active"><a href="paris_view.php">Historique</a></li>
	      <li><a href="paris.php">Parier</a></li>
	      <li><a href="statistiques.php">Statistiques</a></li>
	    </ul>
	  </div>
	</div>


<?php require_once('_functions.php'); ?>

<?php
	connect();
	$queryNbParisEnCours = mysql_query('SELECT COUNT(ID_PARI) AS NB, SUM(MISE) AS MISE, SUM(MISE*COTE) AS GAIN FROM PARI WHERE TERMINE=0');
	$backNbParisEnCours = mysql_fetch_array($queryNbParisEnCours);
	$nbParisEncours = $backNbParisEnCours['NB'];
	$mise = $backNbParisEnCours['MISE'];
	$gainPotentiel = number_format($backNbParisEnCours['GAIN'],2);
	deconnect();
	
	echo '<h3 id="h3NbParis">'.$nbParisEncours.' paris en cours - Mise totale : '.$mise.'€ - Gain possible : '.$gainPotentiel.'€</h3>';
?>			
			

<table class="display" id="tabview" style="width:980px">
	<thead>
		<tr>
			<th>Date</th>
			<th>Type de Pari</th>
			<th>Championnat</th>
			<th>Match(s)</th>
			<th>Mise</th>
			<th>Cote</th>
			<th>Commentaire</th>
			<th>Terminé</th>
			<th>Réussite</th>
			<th></th>
		</tr>
	</thead>
	<tbody>
		<?php
			connect();
			$query = mysql_query('SELECT P.ID_PARI, P.DATE, P.MISE, P.COTE, P.COMMENTAIRE, P.TERMINE, P.REUSSITE, T.LIB_TYPE_PARI, C.LIB_CHAMPIONNAT '.
			' FROM PARI P ,TYPE_PARI T, CHAMPIONNAT C WHERE P.ID_TYPE_PARI=T.ID_TYPE_PARI AND P.ID_CHAMPIONNAT=C.ID_CHAMPIONNAT');
			while ($back = mysql_fetch_array($query)) {				
				$query2 = mysql_query('SELECT M.ID_MATCH, M.ID_EQUIPE1, M.ID_EQUIPE2, M.PRONOSTIC, M.RESULTAT, M.REUSSITE FROM MATCHS M WHERE ID_PARI='.$back['ID_PARI']);


				echo '<tr>';
					echo '<td class="center">'.dateen2fr($back['DATE']).'</td>';
					echo '<td class="center">'.$back['LIB_TYPE_PARI'].'</td>';
					echo '<td class="center">'.$back['LIB_CHAMPIONNAT'].'</td>';
					echo '<td>';
						echo '<ul>';
						while ($match = mysql_fetch_array($query2)){
							$queryEquipe1 = mysql_query('SELECT ID_EQUIPE, LIB_EQUIPE FROM EQUIPE WHERE ID_EQUIPE='.$match['ID_EQUIPE1']); 
							$equipe1 = mysql_fetch_array($queryEquipe1);
							$queryEquipe2 = mysql_query('SELECT ID_EQUIPE,LIB_EQUIPE FROM EQUIPE WHERE ID_EQUIPE='.$match['ID_EQUIPE2']); 
							$equipe2 = mysql_fetch_array($queryEquipe2);		
							echo '<li ';
								//J'affiche le résultat si le match est terminé
								if($match['RESULTAT'] != ''){
									if($match['REUSSITE'] == 1){
										echo 'class="match_success"';
									}else{
										echo 'class="match_fail"';
									}
								}
							echo '>'; 
								//Affichage du match avec mon prono
								//L'equipe est en gras si j'ai misé sur elle
								//le match est en italique si j'ai misé le Nul
								//Le texte est normal si c'est un pari 'Autre'								
								if($match['PRONOSTIC']=='1'){
									echo '<b>'.$equipe1['LIB_EQUIPE'].'</b> - '.$equipe2['LIB_EQUIPE'];
								}else if($match['PRONOSTIC']=='2') {
									echo $equipe1['LIB_EQUIPE'].' - <b>'.$equipe2['LIB_EQUIPE'].'</b>';
								}else if($match['PRONOSTIC']=='N'){
									echo '<em>'.$equipe1['LIB_EQUIPE'].'</em> - <em>'.$equipe2['LIB_EQUIPE'].'</em>';
								}else{
									echo $equipe1['LIB_EQUIPE'].' - '.$equipe2['LIB_EQUIPE'];
								}
							echo '</li>';
						}
						echo '</ul>';
					echo '</td>';
					echo '<td class="center">'.$back['MISE'].'</td>';
					echo '<td class="center">'.$back['COTE'].'</td>';
					echo '<td>'.$back['COMMENTAIRE'].'</td>';
					
					echo '<td class="center"> <img src=';
					if($back['TERMINE']==1){
						echo '"images/termine.gif" alt="terminé">';
					}else{
						echo '"images/enCours1.gif" alt="En cours">';
					}
					echo '</td>';
					

					echo '<td class="center">';
					if($back['REUSSITE']==1){
						echo '<img src="images/success.png" alt="Succes">';
					}else if($back['TERMINE']==1){
						echo '<img src="images/fail.png" alt="Echec">';
					}
					echo '</td>';

					echo '<td> <a href=\'edit_pari.php?id_pari='.$back['ID_PARI'].'\'><img src="images/edit.png" alt="editer" /></a></td>';

				echo '</tr>';
			}
			deconnect();
		?>
	</tbody>
</table>




<script>
$(document).ready(function(){
	
		
	jQuery.fn.dataTableExt.oSort['uk_date-asc']  = function(a,b) {
		var ukDatea = a.split('/');
		var ukDateb = b.split('/');
		 
		var x = (ukDatea[2] + ukDatea[1] + ukDatea[0]) * 1;
		var y = (ukDateb[2] + ukDateb[1] + ukDateb[0]) * 1;
		 
		return ((x < y) ? -1 : ((x > y) ?  1 : 0));
	};
	 
	jQuery.fn.dataTableExt.oSort['uk_date-desc'] = function(a,b) {
		var ukDatea = a.split('/');
		var ukDateb = b.split('/');
		 
		var x = (ukDatea[2] + ukDatea[1] + ukDatea[0]) * 1;
		var y = (ukDateb[2] + ukDateb[1] + ukDateb[0]) * 1;
		 
		return ((x < y) ? 1 : ((x > y) ?  -1 : 0));
	};
	
	
	
	
    $('#tabview').dataTable({
		"aaSorting": [[0, "desc"]],
		"bJQueryUI": true,
		"sPaginationType": "full_numbers",
		"oLanguage": {
			"sProcessing":   "Traitement en cours...",
			"sLengthMenu":   "Afficher _MENU_ éléments",
			"sZeroRecords":  "Aucun élément à afficher",
			"sInfo":         "Affichage de l'élement _START_ à _END_ sur _TOTAL_ éléments",
			"sInfoEmpty":    "Affichage de l'élement 0 à 0 sur 0 éléments",
			"sInfoFiltered": "(filtré de _MAX_ éléments au total)",
			"sInfoPostFix":  "",
			"sSearch":       "Rechercher :",
			"sUrl":          "",
			"oPaginate": {
				"sFirst":    "Premier",
				"sPrevious": "Précédent",
				"sNext":     "Suivant",
				"sLast":     "Dernier"
			}
		},
		"aoColumns": [
            { "sType": "uk_date" },
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null
            ]
	});

});
</script>


</body>

</html>
