
<?php

	function connect(){
		mysql_connect("localhost","root","root");
		mysql_select_db("betstats");
		//mysql_select_db("test");
		mysql_query("SET NAMES 'utf8'");
	}

	function deconnect(){
		mysql_close();
	}

	function datefr2en($mydate){
	   @list($jour,$mois,$annee)=explode('/',$mydate);
	   return date('Y-m-d',mktime(0,0,0,$mois,$jour,$annee));
	}
	
	function dateen2fr($mydate){
	   @list($annee,$mois,$jour)=explode('-',$mydate);
	   return date('d/m/Y',mktime(0,0,0,$mois,$jour,$annee));
	}
	
	function getEnum($table,$champ){
		connect();
		$requete = 'SHOW COLUMNS FROM '.$table.' LIKE \''.$champ.'\'';
		$resultat = mysql_query($requete) or die(mysql_error());
		deconnect();
		$ligne = mysql_fetch_row($resultat);
		// La colonne 1 correspond au type du champ.
		$enum = $ligne[1];
		$enum = substr($enum, 5, -1);
		$enum = explode(",", $enum);
		return $enum;
	}
	
?>
