<?php 
require_once('_functions.php'); 


	if(isset($_GET['action']) && $_GET['action']=='save' && isset($_POST) && !empty($_POST)){
		connect();

		echo '<pre>';
			var_dump($_POST);
		echo '</pre>';	
		
		/*
		 * Il faut updater le pari
		 * les matchs 
		 * et si le pari est terminé et gagnant, updater les comptes
		 */
		

		$update_Pari = 'UPDATE PARI ';
		$update_Pari .= 'SET COMMENTAIRE=\''.mysql_real_escape_string($_POST['commentaire']).'\', ';
		if(isset($_POST['reussite_pari'])){
			$update_Pari .= 'SET REUSSITE= 1, ';
		}else{
			$update_Pari .= 'SET REUSSITE= 0, ';
		}
		if(isset($_POST['termine_pari'])){
			$update_Pari .= 'SET TERMINE= 1 ';
		}else{
			$update_Pari .= 'SET TERMINE= 0 ';
		}
		$update_Pari .= 'WHERE ID_PARI='.$_GET['id_pari'].'';
		echo $update_Pari;
		//mysql_query($update_Pari) or die(mysql_error());
		
		//header('Location: paris_view.php');
		
		
	}
?>			


<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
    
<head>
	<title>Editer un pari</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <link rel="stylesheet" href="css/betstats.css" type="text/css" media="all" />
    <link rel="stylesheet" href="css/demo_table_jui.css" type="text/css" media="all" /> 
    <link rel="stylesheet" href="css/flick/jquery-ui-1.8.16.custom.css" type="text/css" media="all" /> 	
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js" type="text/javascript"></script>
	<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/jquery-ui.min.js" type="text/javascript"></script>
	<script src="jquery/jquery.dataTables.js" type="text/javascript"></script>
</head>
    
<body id="edit_pari">
	
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
					echo '<select name="pronostic_'.$backMatch['ID_MATCH'].'">';
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
		</p>
		
		<p>
		<label for="commentaire">Commentaire : </label>
		<textarea name="commentaire"><?php echo $backPari['COMMENTAIRE']?></textarea>
		</p>
		
		<p><input type="submit" value="Enregistrer" /><input type="reset" value="Supprimer" /></p>
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
