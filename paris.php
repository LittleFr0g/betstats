<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
    
<head>
	<title>Paris</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        
    <link rel="stylesheet" href="css/flick/jquery-ui-1.8.16.custom.css" type="text/css" media="all" />
    <link rel="stylesheet" href="css/betstats.css" type="text/css" media="all" />
    <link rel="stylesheet" href="jquery/uniform/css/uniform.default.css" type="text/css" media="screen" charset="utf-8" />
    <link rel="stylesheet" href="bootstrap/css/bootstrap.css" type="text/css" media="all" />
    
	<script src="jquery/jquery1.8.1.min.js" type="text/javascript"></script>
	<script src="jquery/jqueryui1.8.16.min.js" type="text/javascript"></script>
	<script src="jquery/jquery.ui.datepicker-fr.js" type="text/javascript"></script>
	<script src="jquery/uniform/jquery.uniform.js" type="text/javascript"></script>
	<script src="bootstrap/js/bootstrap.js" type="text/javascript"></script>
	

</head>
    
<body id='page_pari'>

<?php 
require_once('_functions.php'); 
define("CHAMPIONNAT_MIXTE", 10);


if(isset($_POST) && !empty($_POST)) {
	
	connect();
	//Insérer un pari puis insérer les matchs associés
	$insert_Pari = 'INSERT INTO PARI(`id_pari`,`date`,`id_type_pari`,`mise`,`cote`,`commentaire`,`id_championnat`) values(\'\',';
	$insert_Pari .= '\''.datefr2en($_POST['datepicker']).'\',';	
	$insert_Pari .= $_POST['type_pari'].',';	
	$insert_Pari .= $_POST['mise'].',';	
	$insert_Pari .= $_POST['cote'].',';	
	$insert_Pari .= '\''.mysql_real_escape_string($_POST['commentaire']).'\',';
	$insert_Pari .= $_POST['championnat'].',';	
	$insert_Pari = substr_replace($insert_Pari ,");",-1);
	mysql_query($insert_Pari) or die(mysql_error());
	
	//On a inséré le pari, il faut maintenant insérer les matchs et mettre à jour le compte
	$queryIdPari = mysql_query('SELECT MAX(id_pari) from PARI') or die(mysql_error());
	$tabIdPari = mysql_fetch_array($queryIdPari);
	$idPari = $tabIdPari[0];
	
	//Je recupere le solde de mon compte pour le mettre à jour
	$querySolde = mysql_query('SELECT ID_PARI, CREDIT FROM COMPTE ORDER BY ID_PARI DESC') or die(mysql_error());
	$tabSolde = mysql_fetch_array($querySolde);
	$solde = $tabSolde['CREDIT'];
	$credit = $solde - $_POST['mise'];
	$insert_compte = 'INSERT INTO COMPTE(`id_pari`,`credit`) values('.$idPari.','.$credit.')';
	mysql_query($insert_compte) or die(mysql_error());
	
	//On insere maintenant les matchs
	$nbMatchs = (sizeof($_POST)-6)/3;
	for($i=1;$i<=$nbMatchs;$i++){
		$insert_Match = 'INSERT INTO MATCHS(`id_match`,`id_pari`,`id_equipe1`,`id_equipe2`,`pronostic`) values(\'\','.$idPari.','.$_POST['equipe'.$i.'a'].','.$_POST['equipe'.$i.'b'].',\''.$_POST['pronostic'.$i].'\')';
		mysql_query($insert_Match) or die(mysql_error());
	}
	
	
	deconnect();
	
	
}


?>
	<!--Menu Bootstrap-->
	<div class="navbar navbar-inverse">
	  <div class="navbar-inner">
	    <a class="brand" href="paris_view.php">BetStats</a>
	    <ul class="nav">
	      <li><a href="paris_view.php">Historique</a></li>
	      <li class="active"><a href="paris.php">Parier</a></li>
	      <li><a href="statistiques.php">Statistiques</a></li>
	    </ul>
	  </div>
	</div>

	<h2>Cr&eacute;er un nouveau pari : </h2>
    
    <form action="paris.php" method="post">
		<p><label for="datepicker">Date : </label> <input type="text" id="datepicker" name="datepicker" required /></p>
		
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
		
		<p id="type_pari">
		<label>Type : </label> 
		<?php
			connect();
			$query = mysql_query('SELECT ID_TYPE_PARI, LIB_TYPE_PARI FROM TYPE_PARI');
			while ($back = mysql_fetch_array($query)) {
				echo '<input type="radio" name="type_pari" value="'.$back['ID_TYPE_PARI'].'" id="'.$back['ID_TYPE_PARI'].'" ';
				echo '/> <label for="'.$back['ID_TYPE_PARI'].'" class="inline">'.$back['LIB_TYPE_PARI'].'</label>';
			}
			deconnect();
		?>
		</p>
		
		<p id="par_equipes">
		</p>
		
		<p><label for="mise">Mise : </label><input type="number" id="mise" name="mise"	min="1" value="1" required /></p>
		<p><label for="cote">Cote : </label><input type="text" id="cote" name="cote" required /></p>
		<p><label for="commentaire">Commentaire : </label><textarea name="commentaire"></textarea></p>
		<p><input type="submit" value="Enregistrer" /></p>
		</p>
    </form>
    
   
   
   <script>
   
	$(function() {
		$( "#datepicker" ).datepicker();
	});
	
	$(document).ready(function() {
		
		$("select, input:checkbox, input:radio, input:text, textarea, input:button, input:submit").uniform();
		
		
		$('select#championnat').change(function(){
			getEquipesAndBuildLists($(this).val());
		});
		
		$('input[name="type_pari"]').change(function(){
			if($(this).val()==2 || $(this).val()==4){
				/* 
				* On a choisit Combiné / Combiné Live
				* Il faut ajouter des select et un bouton pour ajouter des matchs
				*/
				getEquipesAndBuildLists( $('select#championnat').val());
			}else{
				/*
				* On a choisi le type Simple / Simple Live 
				* Il ne faut qu'un match
				*/			
				getEquipesAndBuildLists( $('select#championnat').val(),true);
			}
		});
		
		function getEquipesAndBuildLists(id, empty){
			if(empty===undefined){
				empty=false;
			}
			
			$.ajax({
					url: 'ajax_requetes.php',
					data: { id_championnat:id , requete:2} ,
					success: function(response){createListEquipes(response, empty)},
					dataType: 'html'
				});
		}
		
		function getEnumAndBuildList(table,champ){
			var resultat=null;
			var request = $.ajax({
				url: 'ajax_requetes.php',
				data: { table:table , champ:champ , requete:1 },
				success: function(response){resultat=response},
				dataType: 'html',	
				async : false
			});	
			return resultat;
		}
		
		/*
		* Fonction qui cree le paragraphe avec les selects selon le type de pari et le championnat sélectionné
		* Ajoute un bouton qui permet de rajouter un match si on est en config 'Combiné'
		* listEquipes = liste des equipes
		*/
		function createListEquipes(listEquipes, empty){
			if(empty){
				$('#par_equipes').empty();
			}
			
			var nbMatchs = $('p.ligne_match').size()+1;	
			var selectA = $('<select>').attr('name','equipe'+nbMatchs+'a').append('<option>');
			var selectB = $('<select>').attr('name','equipe'+nbMatchs+'b').append('<option>');
			var selectPronostic = $('<select>').attr('name','pronostic'+nbMatchs).append('<option>');
			var listPronostics = getEnumAndBuildList('MATCHS','PRONOSTIC');
			var tabPronostics = listPronostics.split(';');
			
			var buttonDelete = $('<input>').attr('name','deleteMatch').attr('type','button').attr('value','Supprimer');
			buttonDelete.bind('click', function(){
				$(this).parent().remove();	
				majAddMatchButton();
			});
			
			var equipes = listEquipes.split(';');

			//Construction des select d'equipes
			for(j=0;j<equipes.length-1;j++){
				var id_equipe = equipes[j].split('*')[0];
				var equipe = equipes[j].split('*')[1];
				var optionA = $('<option>').attr('value',id_equipe).html(equipe);
				var optionB = $('<option>').attr('value',id_equipe).html(equipe);
				selectA.append(optionA);
				selectB.append(optionB);
			}
			
			//Constrution du select des pronostics
			for(k=0;k<tabPronostics.length-1;k++){
				var optionEnum = $('<option>').attr('value',tabPronostics[k]).html(tabPronostics[k]);
				selectPronostic.append(optionEnum);
			}
			
			var pMatch = $('<p>').attr('class','ligne_match');
			pMatch.append($('<label>').html('Équipes :'));
			pMatch.append(selectA);
			pMatch.append(' VS ');
			pMatch.append(selectB);
			pMatch.append(selectPronostic);
			pMatch.append(buttonDelete);
			
			$('#par_equipes').append(pMatch);
			
			majAddMatchButton();
			majDeleteButton();
			
		}
		'N' ; '1' ;
		function majAddMatchButton(){
			$('input[name="addMatch"]').remove();
			var nbMatchs = $('p.ligne_match').size();
			// On ajoute un bouton si on est en config Combiné
			if(nbMatchs >= 2) {
				var button = $('<input>').attr('name','addMatch').attr('type','button').attr('value','Ajouter un match');
				button.bind('click', function(){
					getEquipesAndBuildLists($('select#championnat').val());
				});
				$('#par_equipes').after(button);
			}
		}
		
		function majDeleteButton(){
			$('input[name="deleteMatch"]').show();
			var nbMatchs = $('p.ligne_match').size();
			if(nbMatchs < 3) $('input[name="deleteMatch"]').hide();
			else {
				$('input[name="deleteMatch"]').slice(0,2).hide();
			}
		}
		
	});
	
	</script>

</body>
</html>
