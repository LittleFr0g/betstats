<?php 
require_once('_functions.php'); 

	//Traitement après SUBMIT
	if(isset($_GET['action']) && $_GET['action']=='save' && isset($_POST) && !empty($_POST)){
		connect();
		/*
		echo '<pre>';
			var_dump($_POST);
		echo '</pre>';	
		*/		
		/*
		 * Il faut updater : 
		 * - le pari
		 * - les matchs 
		 * - et si le pari est terminé et gagnant, updater les comptes
		 */
		

		$update_Pari = 'UPDATE PARI ';
		$update_Pari .= 'SET COMMENTAIRE=\''.mysql_real_escape_string($_POST['commentaire']).'\', ';
		if(isset($_POST['reussite_pari'])){
			$update_Pari .= 'REUSSITE=1 , ';
			$reussite_pari = 1;
		}else{
			$update_Pari .= 'REUSSITE=0 , ';
			$reussite_pari = 0;
		}
		if(isset($_POST['termine_pari'])){
			$update_Pari .= 'TERMINE= 1 ';
			$termine_pari = 1;
		}else{
			$update_Pari .= 'TERMINE= 0 ';
			$termine_pari = 0;
		}
		if(!isset($_POST['termine_pari_before'])){
			$termine_pari_before = 0 ;
		}else{
			$termine_pari_before = 1 ;
		}
		
		$update_Pari .= 'WHERE ID_PARI='.$_GET['id_pari'].'';
		echo $update_Pari;
		echo '<br />';
		mysql_query($update_Pari) or die(mysql_error());

		//Update des matchs
		//On recupère les matchs associés à ce pari
		$queryMatch = mysql_query('SELECT ID_MATCH FROM MATCHS WHERE ID_PARI='.$_GET['id_pari']);  
		while($backMatch = mysql_fetch_array($queryMatch)){
			$update_Match = 'UPDATE MATCHS ';
			$update_Match .= 'SET RESULTAT=\''.$_POST['resultat_'.$backMatch['ID_MATCH'].''].'\', ';
			if(isset($_POST['reussite_'.$backMatch['ID_MATCH'].''])){
				$update_Match .= 'REUSSITE=1 ';
			}else{
				$update_Match .= 'REUSSITE=0 ';
			}			
			$update_Match .= 'WHERE ID_MATCH='.$backMatch['ID_MATCH'].'';
			
			echo $update_Match;
			echo '<br />';
			mysql_query($update_Match) or die(mysql_error());
		}


		//UPDATE du crédit si le pari est réussi et qu'il vient de se terminer
		if($termine_pari && !$termine_pari_before && $reussite_pari ){
			$queryCredit = mysql_query('SELECT CREDIT FROM COMPTE WHERE ID_PARI='.$_GET['id_pari']);  
			$backCredit = mysql_fetch_array($queryCredit);
			$credit=$backCredit['CREDIT'];

			$queryPari = mysql_query('SELECT MISE, REUSSITE, COTE, TERMINE, COMMENTAIRE FROM PARI WHERE ID_PARI='.$_GET['id_pari']);  
			$backPari = mysql_fetch_array($queryPari);
			$gain = $backPari['MISE'] * $backPari['COTE'];
			$newCredit = $credit + $gain;

			$update_Credit = 'UPDATE COMPTE SET CREDIT ='.$newCredit.' WHERE ID_PARI='.$_GET['id_pari'].'';
			echo $update_Credit;

			mysql_query($update_Credit) or die(mysql_error());
		}


		deconnect();
		//On redirige sur la page de l'historique
		header('Location: paris_view.php');
		
	}
?>			


<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
    
<head>
	<title>Editer un pari</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <link rel="stylesheet" href="css/betstats.css" type="text/css" media="all" />
    <link rel="stylesheet" href="css/demo_table_jui.css" type="text/css" media="all" /> 
    <link rel="stylesheet" href="css/flick/jquery-ui-1.8.23.custom.css" type="text/css" media="all" />
    <link rel="stylesheet" href="bootstrap/css/bootstrap.css" type="text/css" media="all" />
    
    <script src="jquery/jquery-1.8.0.min.js" type="text/javascript"></script>
	<script src="jquery/jquery-ui-1.8.23.custom.min.js" type="text/javascript"></script>
	<script src="jquery/jquery.dataTables.js" type="text/javascript"></script>
	<script src="bootstrap/js/bootstrap.js" type="text/javascript"></script>
	
</head>
    
<body id="edit_pari">

	<!--Menu Bootstrap-->
	<div class="navbar navbar-inverse">
	  <div class="navbar-inner">
	    <a class="brand" href="paris_view.php">BetStats</a>
	    <ul class="nav">
	      	<li class='active'><a href="paris_view.php">Historique</a></li>
	      	<li><a href="paris.php">Parier</a></li>
	      	<li><a href="statistiques.php">Statistiques</a></li>
	    	<li><a href="gestion.php">Gestion</a></li>
	    </ul>
	  </div>
	</div>
	
	<h2>Editer un pari</h2>
	
	
<?php 
require_once('_functions.php'); 

	$id_pari = $_GET['id_pari'];
	$enum = getEnum('MATCHS','PRONOSTIC');
	
	connect();
	$queryPari = mysql_query('SELECT MISE, REUSSITE, COTE, TERMINE, COMMENTAIRE FROM PARI WHERE ID_PARI='.$id_pari);  
	$backPari = mysql_fetch_array($queryPari);
	$queryMatch = mysql_query('SELECT ID_MATCH,ID_EQUIPE1,ID_EQUIPE2,PRONOSTIC,RESULTAT,REUSSITE FROM MATCHS WHERE ID_PARI='.$id_pari);  
	deconnect();
	

	echo '<form action="edit_pari.php?id_pari='.$_GET['id_pari'].'&action=save" method="post">';
?>

<p>
<table class="display" id="tabMatchs">
	<thead>
		<tr>
			<th>Match(s)</th>
			<th>Resultat</th>
			<th>Réussite</th>
		</tr>
	</thead>
	<tbody>
	
	<?php
		connect();
		while($backMatch = mysql_fetch_array($queryMatch)){
			$queryEquipe1=mysql_query('SELECT LIB_EQUIPE FROM EQUIPE WHERE ID_EQUIPE='.$backMatch['ID_EQUIPE1']); 
			$backEquipe1=mysql_fetch_array($queryEquipe1);
			$equipe1=$backEquipe1['LIB_EQUIPE'];
			$queryEquipe2=mysql_query('SELECT LIB_EQUIPE FROM EQUIPE WHERE ID_EQUIPE='.$backMatch['ID_EQUIPE2']); 
			$backEquipe2=mysql_fetch_array($queryEquipe2);
			$equipe2=$backEquipe2['LIB_EQUIPE'];
				
			echo '<tr>';	
				//Affichage du match avec mon prono
				//L'equipe est en gras si j'ai misé sur elle
				//le match est en italique si j'ai misé le Nul
				//Le texte est normal si c'est un pari 'Autre'	
				echo '<td class="center">';		
					if($backMatch['PRONOSTIC']=='1'){
						echo '<b>'.$equipe1.'</b> - '.$equipe2;
					}else if($backMatch['PRONOSTIC']=='2') {
						echo $equipe1.' - <b>'.$equipe2.'</b>';
					}else if($backMatch['PRONOSTIC']=='N'){
						echo '<em>'.$equipe1.'</em> - <em>'.$equipe2.'</em>';
					}else{
						echo $equipe1.' - '.$equipe2;
					}
				echo '</td>';
				
				//Affichage du resultat, on selectionne par défaut notre pronostic
				echo '<td class="center">';		
					echo '<select name="resultat_'.$backMatch['ID_MATCH'].'">';
					for($i=0;$i<sizeof($enum);$i++){
						echo '<option value="'.str_replace("'","",$enum[$i]).'"';
						if(str_replace("'","",$enum[$i])==$backMatch['PRONOSTIC']){echo 'selected="selected"';}
						echo '>'.str_replace("'","",$enum[$i]);
						echo '</option>';
					}
					echo '</select>';
				echo '</td>';
				
				//Checkbox Reussite
				echo '<td class="center">';
					echo '<input type="checkbox" name="reussite_'.$backMatch['ID_MATCH'].'" value="1"';
					if($backMatch['REUSSITE']==1){echo 'checked="checked"';}
					echo '>';
				echo '</td>';
			echo '</tr>';
		}
		deconnect();
	?>
	</tbody>
</table>	
</p>

		<p><label>Mise : </label> 	
		<?php echo $backPari['MISE'];?>
		</p>
		
		<p><label>Cote : </label> 	
		<?php echo $backPari['COTE'];?>
		</p>
			
		<p><label for="reussite">Réussite : </label> 	
		<?php
			echo '<input type="checkbox" name="reussite_pari" value="1"';
			if($backPari['REUSSITE']==1){echo 'checked="checked"';}
			echo '>';
		?>
		</p>
		
		<p><label for="termine">Pari terminé : </label> 	
		<?php
			echo '<input type="checkbox" name="termine_pari" value="1"';
			if($backPari['TERMINE']==1){echo 'checked="checked"';}
			echo '>';
		?>
		<!-- On met un champ caché pour savoir si ce pari est terminé avant modif pour ne pas
			mettre à jour deux fois le crédit
		-->
		<?php
			echo '<input type="checkbox" name="termine_pari_before" value="1" style="display:none"';
			if($backPari['TERMINE']==1){echo 'checked="checked"';}
			echo '>';
		?>
		</p>
		
		<p>
		<label for="commentaire">Commentaire : </label>
		<textarea name="commentaire"><?php echo $backPari['COMMENTAIRE']?></textarea>
		</p>
	 	
		<p><input type="submit" value="Enregistrer" class="btn" /><input type="reset" value="Supprimer" class="btn"/></p>
	</form>

<script>
	$(document).ready(function(){
		
		$('#tabMatchs').dataTable({
			"bJQueryUI": true,
			"bPaginate": false,
			"bLengthChange": false,
			"bFilter": true,
			"bSort": false,
			"bInfo": false,
			"bSearchable": false,	
			"bAutoWidth": false
		});
	});
</script>


</body>
</html>
