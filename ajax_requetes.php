<?php 	

require_once('_functions.php');
$requete=$_GET['requete'];
connect();

// Requete pour récupérer une énumération d'un champ d'une table
// @table : table de la base de données
// @champ : champ de la table
if($requete==1){
	$table = $_GET['table'];
	$champ = $_GET['champ'];

	$enum = getEnum($table,$champ);

	for($i=0;$i<sizeof($enum);$i++){
		echo str_replace("'","",$enum[$i]).';';
	}	
}

// Requete pour récupérer une liste d'equipes appartenant au championnat que l'on désire
// @id_championnat : l'id du championnat duquel on veut récupérer les équipes
if($requete==2){
	define("CHAMPIONNAT_AUTRE", 9);
	define("CHAMPIONNAT_MIXTE", 10);
	define("CHAMPIONNAT_COUPE", 12);

	$id_championnat = (int)$_GET['id_championnat'];

	connect();
	if(($id_championnat != CHAMPIONNAT_AUTRE) and ($id_championnat != CHAMPIONNAT_MIXTE) and ($id_championnat != CHAMPIONNAT_COUPE)){
		$query=mysql_query('SELECT 	E.ID_EQUIPE, E.LIB_EQUIPE FROM EQUIPE E, EQUIPE_CHAMPIONNAT C WHERE E.ID_EQUIPE=C.ID_EQUIPE AND C.ID_CHAMPIONNAT='.$id_championnat.' ORDER BY LIB_EQUIPE');
	}else{
		$query=mysql_query('SELECT 	E.ID_EQUIPE, E.LIB_EQUIPE FROM EQUIPE E ORDER BY LIB_EQUIPE');
	}

	while ($back = mysql_fetch_array($query)) {
		echo $back['ID_EQUIPE'].'*'.$back['LIB_EQUIPE'].';';
	}
}

// Requete pour faire le graphe des comptes
if($requete==3){
	$query=mysql_query('SELECT 	ID_PARI, CREDIT FROM COMPTE ORDER BY ID_PARI');

	while ($back = mysql_fetch_array($query)) {
		echo $back['ID_PARI'].'*'.$back['CREDIT'].';';
	}
}

// Requete pour voir tous les paris gagnés/perdus
if($requete==4){
	$queryParisGagnes=mysql_query('SELECT COUNT(ID_PARI) as NB FROM PARI WHERE TERMINE=1 AND REUSSITE=1');
	$backParisGagnes = mysql_fetch_array($queryParisGagnes);
	$queryParisPerdus=mysql_query('SELECT COUNT(ID_PARI) as NB FROM PARI WHERE TERMINE=1 AND REUSSITE=0');
	$backParisPerdus = mysql_fetch_array($queryParisPerdus);
	echo 'Paris Réussis*'.$backParisGagnes['NB'].';';
	echo 'Paris Perdus*'.$backParisPerdus['NB'].';';
}

// Requete pour voir tous les paris simples gagnés/perdus
if($requete==5){
	$queryParisGagnes=mysql_query('SELECT COUNT(ID_PARI) as NB FROM PARI WHERE ID_TYPE_PARI=1 AND TERMINE=1 AND REUSSITE=1');
	$backParisGagnes = mysql_fetch_array($queryParisGagnes);
	$queryParisPerdus=mysql_query('SELECT COUNT(ID_PARI) as NB FROM PARI WHERE ID_TYPE_PARI=1 AND TERMINE=1 AND REUSSITE=0');
	$backParisPerdus = mysql_fetch_array($queryParisPerdus);
	echo 'Paris Réussis*'.$backParisGagnes['NB'].';';
	echo 'Paris Perdus*'.$backParisPerdus['NB'].';';
}

// Requete pour voir les matchs gagnés/perdus
if($requete==6){
	$queryMatchsGagnes=mysql_query('SELECT COUNT(ID_MATCH) as NB FROM MATCHS WHERE RESULTAT IS NOT NULL AND REUSSITE=1');
	$backMatchsGagnes = mysql_fetch_array($queryMatchsGagnes);
	$queryMatchsPerdus=mysql_query('SELECT COUNT(ID_MATCH) as NB FROM MATCHS WHERE RESULTAT IS NOT NULL AND REUSSITE=0');
	$backMatchsPerdus = mysql_fetch_array($queryMatchsPerdus);
	echo 'Matchs Réussis*'.$backMatchsGagnes['NB'].';';
	echo 'Matchs Perdus*'.$backMatchsPerdus['NB'].';';
}

// Requete pour voir les championnats sur lesquels je mise
if($requete==7){
	$queryChampionnat=mysql_query('SELECT LIB_CHAMPIONNAT, COUNT( LIB_CHAMPIONNAT ) AS TOTAL FROM PARI P, '.
	'CHAMPIONNAT C WHERE P.ID_CHAMPIONNAT = C.ID_CHAMPIONNAT AND P.TERMINE =1 AND P.ID_CHAMPIONNAT '.
	'IN (SELECT ID_CHAMPIONNAT FROM CHAMPIONNAT) GROUP BY LIB_CHAMPIONNAT');
	while ($back = mysql_fetch_array($queryChampionnat)) {
		echo $back['LIB_CHAMPIONNAT'].'*'.$back['TOTAL'].';';
	}
}

// Requete pour voir le pourcentage de reussite par championnat
if($requete==8){
	$queryChampionnatGagnes=mysql_query('SELECT LIB_CHAMPIONNAT, COUNT( LIB_CHAMPIONNAT ) AS TOTAL FROM PARI P, '.
	'CHAMPIONNAT C WHERE P.ID_CHAMPIONNAT = C.ID_CHAMPIONNAT AND P.REUSSITE =1 AND P.TERMINE =1 AND P.ID_CHAMPIONNAT '.
	'IN (SELECT ID_CHAMPIONNAT FROM CHAMPIONNAT) GROUP BY LIB_CHAMPIONNAT');
	$queryChampionnatNbMatchs=mysql_query('SELECT LIB_CHAMPIONNAT, COUNT( LIB_CHAMPIONNAT ) AS TOTAL FROM PARI P, '.
	'CHAMPIONNAT C WHERE P.ID_CHAMPIONNAT = C.ID_CHAMPIONNAT AND P.TERMINE =1 AND P.ID_CHAMPIONNAT '.
	'IN (SELECT ID_CHAMPIONNAT FROM CHAMPIONNAT) GROUP BY LIB_CHAMPIONNAT');
	
	while ($backGagnes = mysql_fetch_array($queryChampionnatGagnes) and $backNbMatchs = mysql_fetch_array($queryChampionnatNbMatchs)) {
		$pourcentage=($backGagnes['TOTAL']/$backNbMatchs['TOTAL'])*100;
		echo $backGagnes['LIB_CHAMPIONNAT'].'*'.$pourcentage.';';
	}
}

// Requete pour voir le pourcentage de reussite par cote
if($requete==9){
	$queryCote=mysql_query('SELECT COTE FROM PARI WHERE TERMINE=1 AND REUSSITE=1');
	//Je remplis tout d'abord mes intervalles
	// [1;1.5[, [1.5;2[, [2;2.5[, [2.5;3[, [3;3.5[, [3.5;4[, >4
	$intervalles = array(0,0,0,0,0,0,0);
    
	while ($backCote = mysql_fetch_array($queryCote)) {
		$i=min(6,((floor($backCote['COTE']*2)-2)));		
		$intervalles[$i]++;
	}
	
	$borneInf=1;
	for($i=0;$i<sizeof($intervalles);$i++){
		if($borneInf==4){
			$lib_intervalle=' > 4';
			$queryNbPari=mysql_query('SELECT COUNT(COTE) AS NBPARI FROM PARI WHERE COTE > 4');
			$backNbPari = mysql_fetch_array($queryNbPari);
			$nbPari = $backNbPari['NBPARI'];
		}
		else {
			$lib_intervalle='de '.$borneInf.' à '.($borneInf+0.5);
			$queryNbPari=mysql_query('SELECT COUNT(COTE) AS NBPARI FROM PARI WHERE COTE BETWEEN '.$borneInf.' AND '.($borneInf+0.5));
			$backNbPari = mysql_fetch_array($queryNbPari);
			$nbPari = $backNbPari['NBPARI'];
		}
		$intervalles[$i]=($intervalles[$i]/$nbPari)*100;
		echo $lib_intervalle.'*'.$intervalles[$i].';';
		$borneInf+=0.5;
	}
	
}

/** Page de gestion **/
//Requete pour renvoyer la liste de championnats auquelle une équipe appartient
if($requete==10){
	$id_equipe = (int)$_GET['id_equipe'];
	$queryChampionnat=mysql_query('SELECT C.ID_CHAMPIONNAT,LIB_CHAMPIONNAT FROM CHAMPIONNAT C, EQUIPE_CHAMPIONNAT EC WHERE C.ID_CHAMPIONNAT=EC.ID_CHAMPIONNAT and EC.ID_EQUIPE='.$id_equipe);
	while ($back = mysql_fetch_array($queryChampionnat)) {
		echo $back['ID_CHAMPIONNAT'].'*'.$back['LIB_CHAMPIONNAT'].';';
	}
}

//Requete pour renvoyer le nombre de matchs sur lequel on a joué avec cette equipe et le nombre qu'on en a réussi
if($requete==11){
	$id_equipe = (int)$_GET['id_equipe'];
	$queryInfos=mysql_query('SELECT count(id_match) as nbMatchs FROM MATCHS M WHERE M.id_equipe1 ='.$id_equipe.' OR M.id_equipe2 ='.$id_equipe);
	while ($back = mysql_fetch_array($queryInfos)) {
		echo $back['nbMatchs'].'*';
	}

	$queryInfos2=mysql_query('SELECT count(id_match) as nbMatchsReussis	FROM MATCHS M WHERE ((M.id_equipe1 ='.$id_equipe.' OR M.id_equipe2 ='.$id_equipe.')) AND M.reussite=1');
	while ($back = mysql_fetch_array($queryInfos2)) {
		echo $back['nbMatchsReussis'];
	}
}

//Requete pour connaitre les championnats auquels une equipe ne participe pas
if($requete==12){
	$id_equipe = (int)$_GET['id_equipe'];
	$queryChampionnat=mysql_query('SELECT C.ID_CHAMPIONNAT, LIB_CHAMPIONNAT FROM CHAMPIONNAT C WHERE NOT EXISTS (SELECT NULL FROM EQUIPE_CHAMPIONNAT EC WHERE C.ID_CHAMPIONNAT = EC.ID_CHAMPIONNAT AND EC.ID_EQUIPE='.$id_equipe.') ORDER BY LIB_CHAMPIONNAT ');
	while ($back = mysql_fetch_array($queryChampionnat)) {
		echo $back['ID_CHAMPIONNAT'].'*'.$back['LIB_CHAMPIONNAT'].';';
	}
}

//Requete pour insérer une equipe dans un nouveau championnat
if($requete==13){
	$id_equipe = (int)$_GET['id_equipe'];
	$id_championnat = (int)$_GET['id_championnat'];
	
	$insert_equipe = 'INSERT INTO EQUIPE_CHAMPIONNAT(`id_equipe`,`id_championnat`) values('.$id_equipe.','.$id_championnat.')';
	mysql_query($insert_equipe) or die(mysql_error());
}

//Requete pour supprimer une equipe d'un championnat
if($requete==14){
	$id_equipe = (int)$_GET['id_equipe'];
	$id_championnat = (int)$_GET['id_championnat'];
	$delete_equipe = 'DELETE FROM EQUIPE_CHAMPIONNAT WHERE id_equipe='.$id_equipe.' and id_championnat='.$id_championnat;
	mysql_query($delete_equipe) or die(mysql_error());
}

//Requete pour créer une nouvelle équipe
if($requete==15){
	$lib_equipe = $_GET['lib_equipe'];
	$id_championnat = (int)$_GET['id_championnat'];

	$insert_equipe='INSERT INTO `equipe`(`id_equipe`, `lib_equipe`) VALUES (\'\',"'.$lib_equipe.'")';
	mysql_query($insert_equipe) or die(mysql_error());

	//on recupere l'id de la derniere equipe insere pour lui ajouter un championnat
	$queryIdEquipe = mysql_query('SELECT MAX(id_equipe) from EQUIPE') or die(mysql_error());
	$tabIdEquipe = mysql_fetch_array($queryIdEquipe);
	$id_equipe = $tabIdEquipe[0];

	$insert_equipe = 'INSERT INTO EQUIPE_CHAMPIONNAT(`id_equipe`,`id_championnat`) values('.$id_equipe.','.$id_championnat.')';
	mysql_query($insert_equipe) or die(mysql_error());

}

//Requete pour ajouter un nouveau championnat
if($requete==16){
	$lib_championnat = $_GET['lib_championnat'];
	$insert_championnat='INSERT INTO `championnat`(`id_championnat`, `lib_championnat`) VALUES (\'\',"'.$lib_championnat.'")';
	mysql_query($insert_championnat) or die(mysql_error());
}

deconnect();



?>
