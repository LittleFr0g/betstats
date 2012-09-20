<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
    
<head>
	<title>Statistiques</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<link rel="stylesheet" href="css/jquery.jqplot.css" type="text/css" media="all" />
	<link rel="stylesheet" href="css/flick/jquery-ui-1.8.23.custom.css" type="text/css" media="all" />
	<link rel="stylesheet" href="css/demo_table_jui.css" type="text/css" media="all" /> 
	<link rel="stylesheet" href="css/betstats.css" type="text/css" media="all" />
	<link rel="stylesheet" href="bootstrap/css/bootstrap.css" type="text/css" media="all" />

	<script src="jquery/jquery-1.8.0.min.js" type="text/javascript"></script>
	<script src="jquery/jquery-ui-1.8.23.custom.min.js" type="text/javascript"></script>
	<script src="jquery/jqplot/jquery.jqplot.min.js" type="text/javascript"></script>
	<script src="jquery/jqplot/jqplot.highlighter.min.js" type="text/javascript"></script>
	<script src="jquery/jqplot/jqplot.cursor.min.js" type="text/javascript"></script>	
	<script src="jquery/jqplot/jqplot.json2.min.js" type="text/javascript"></script>
	<script src="jquery/jqplot/jqplot.pieRenderer.min.js" type="text/javascript"></script>
	<script src="jquery/jqplot/jqplot.barRenderer.min.js" type="text/javascript"></script>
	<script src="jquery/jqplot/jqplot.canvasTextRenderer.min.js" type="text/javascript"></script>
	<script src="jquery/jqplot/jqplot.canvasAxisTickRenderer.min.js" type="text/javascript"></script>
	<script src="jquery/jqplot/jqplot.categoryAxisRenderer.min.js" type="text/javascript"></script>
	<script src="bootstrap/js/bootstrap.js" type="text/javascript"></script>
	
	<script src="jquery/jquery.dataTables.js" type="text/javascript"></script>


	
</head>
    
<body id="statistiques">

	<!-- Menu Bootstrap -->
	<div class="navbar navbar-inverse">
	  <div class="navbar-inner">
	    <a class="brand" href="paris_view.php">BetStats</a>
	    <ul class="nav">
	      	<li><a href="paris_view.php">Historique</a></li>
	      	<li><a href="paris.php">Parier</a></li>
	      	<li class="active"><a href="statistiques.php">Statistiques</a></li>
			<li><a href="gestion.php">Gestion</a></li>	    
		</ul>
	  </div>
	</div>

<?php
require_once('_functions.php'); 

//Quelques chiffres sur mes paris
connect();
//Nombre de paris
$queryNbParis=mysql_query('SELECT COUNT(ID_PARI) as NB FROM PARI WHERE TERMINE=1');
$backNbParis = mysql_fetch_array($queryNbParis);
$nbParis=$backNbParis['NB'];

//Nombre de matchs
$queryNbMatchs=mysql_query('SELECT COUNT(ID_MATCH) as NB FROM MATCHS WHERE RESULTAT IS NOT NULL');
$backNbMatchs = mysql_fetch_array($queryNbMatchs);
$nbMatchs=$backNbMatchs['NB'];

//Nombre de paris gagnés
$queryNbParisGagnes=mysql_query('SELECT COUNT(ID_PARI) as NB FROM PARI WHERE TERMINE=1 AND REUSSITE=1');
$backNbParisGagnes = mysql_fetch_array($queryNbParisGagnes);
$nbParisGagnes=$backNbParisGagnes['NB'];

//Nombre de paris perdus
$queryNbParisPerdus=mysql_query('SELECT COUNT(ID_PARI) as NB FROM PARI WHERE TERMINE=1 AND REUSSITE=0');
$backNbParisPerdus = mysql_fetch_array($queryNbParisPerdus);
$nbParisPerdus=$backNbParisPerdus['NB'];

//Argent joué, gain potentiel et cote moyenne
$queryArgentJoue=mysql_query('SELECT SUM(MISE) as SOMME, SUM(MISE*COTE) as GAIN, SUM(COTE) AS COTE FROM PARI WHERE TERMINE=1');
$backArgentJoue = mysql_fetch_array($queryArgentJoue);
$argentJoue=$backArgentJoue['SOMME'];
$argentPotentiel=$backArgentJoue['GAIN'];
$coteTotal=$backArgentJoue['COTE'];

//Argent gagné
$queryArgentGagne=mysql_query('SELECT SUM(MISE*COTE) as GAIN FROM PARI WHERE REUSSITE=1');
$backArgentGagne = mysql_fetch_array($queryArgentGagne);
$argentGagne=$backArgentGagne['GAIN'];

$miseMoyenne = $argentJoue/$nbParis;
$gainMoyen = $argentGagne/$nbParis;
$gainPotentielMoyen = $argentPotentiel/$nbParis;
$coteMoyenne = $coteTotal/$nbParis;


deconnect();

echo '<div id="chiffres">';
	echo '<div id="graphCompte" class="moyennes" style="height:400px;width:600px; "></div>';
	echo '<div id="chiffresMoyennes" class="moyennes">';
		echo '<ul>';
			echo '<li>Nombre de paris : <b>'.$nbParis.'</b> (<b>'.$nbMatchs.'</b> Matchs)</li>';
			echo '<li>Paris gagnés : <b>'.$nbParisGagnes.'</b></li>';
			echo '<li>Paris perdus : <b>'.$nbParisPerdus.'</b></li>';
		echo '</ul>';
		
		echo '<ul>';
			echo '<li>Argent joué : <b>'.$argentJoue.' €</b></li>';
			echo '<li>Gain potentiel : <b>'.number_format($argentPotentiel,2).' €</b></li>';
			echo '<li>Argent gagné : <b>'.number_format($argentGagne,2).' €</b></li>';
		echo '</ul>';
		
		echo '<ul>';
			echo '<li>Mise moyenne : <b>'.number_format($miseMoyenne,2).' €</b></li>';
			echo '<li>Cote moyenne : <b>'.number_format($coteMoyenne,2).'</b></li>';
			echo '<li>Gain potentiel moyen : <b>'.number_format($gainPotentielMoyen,2).' €</b></li>';
			echo '<li>Gain moyen : <b>'.number_format($gainMoyen,2).' €</b></li>';
		echo '</ul>';
	echo '</div>';
echo '</div>';

?>


<div id='piesParisMatchs'>
	<div id='pieParis' class='piePariMatch'></div>    
	<div id='pieParisSimples' class='piePariMatch'></div>     
	<div id='pieMatchs' class='piePariMatch'></div>    
</div>

<div id='piesChampionnats'>
	<div id='pieChampionnats' class='pieChampionnat'></div>    
	<div id='barChampionnats' class='barChampionnat'></div>     
</div>	



<p>

<div id="test">
	<table class="display" id="tabGainChampionnats">
		<thead>
			<tr>
				<th>Championnat</th>
				<th>Gain</th>
			</tr>
		</thead>
		<tbody>
			<?php
			connect();

			// Requete pour voir les gains par championnats
			$queryGainChampionnat=mysql_query('SELECT LIB_CHAMPIONNAT, SUM(MISE*COTE) AS GAIN FROM PARI P,CHAMPIONNAT C '.
				'WHERE P.ID_CHAMPIONNAT = C.ID_CHAMPIONNAT AND P.REUSSITE =1 AND P.TERMINE =1 AND P.ID_CHAMPIONNAT IN '.
				'(SELECT ID_CHAMPIONNAT FROM CHAMPIONNAT) GROUP BY LIB_CHAMPIONNAT ORDER BY GAIN DESC');
			while ($back = mysql_fetch_array($queryGainChampionnat)) {
				
				echo '<tr>';
					echo '<td>'.$back['LIB_CHAMPIONNAT'];
					echo '<td>'.number_format($back['GAIN'],2);
				echo '</tr>';		
			}

			deconnect();
			?>
		</tbody>
	</table>	
	</p>

	<div id='barCote' class='barChampionnat'></div>     
</div>



<script>
	$(document).ready(function(){
		
		//Tableau des championnats
		$('#tabGainChampionnats').dataTable({
			"bJQueryUI": true,
			"bPaginate": false,
			"bLengthChange": false,
			"bFilter": true,
			"bSort": false,
			"bInfo": false,
			"bSearchable": false,	
			"bAutoWidth": false
		});
		
		/*
		**********************************************
		**********************************************
		************ Graphe des comptes **************
		**********************************************
		**********************************************
		*/
		//Permet de récupérer un tableau de couples [id_pari,credit] pour construire le graph des comptes
		var getTabCompte = function(){
			var getCompte=getLine('ajax_requetes.php?requete=3');
			var tab = getCompte.split(';');
			var tabCompte=[[]];
			for(var i=0;i<tab.length-1;i++){
				var pari = parseInt(tab[i].split('*')[0]);
				var credit = parseFloat(tab[i].split('*')[1]);
				tabCompte[0].push([pari,credit]);
			}
			return tabCompte;
		};

		//Caractéristiques du graph des comptes
		var plot1 = $.jqplot('graphCompte', [], {
			title: 'Compte',
			axes:{
				xaxis:{
					min:0, 
					showTicks:false,
					tickOptions : {formatString: '%u'}
				},
				yaxis:{
					tickOptions : {formatString: '%3.0f€'}
				}
			},
			series:[{
				lineWidth:1.5,
				markerOptions:{size: 4}
			}],			
			highlighter: {
				show: true,
				sizeAdjust: 7.5,
				tooltipAxes: 'y',
				useAxesFormatters: false,
				tooltipFormatString: '%3.2f€'
			},
			dataRenderer: getTabCompte
			
		});
		
		/*
		**********************************************
		**********************************************
		*** Pie de tous les  paris reussis/perdus ****
		************Paris simples et Matchs***********
		************** Par championnats **************
		**********************************************
		*/
		var getTabParis = function(){
			var getParis=getLine('ajax_requetes.php?requete=4');
			var tab = getParis.split(';');
			var tabParis=[[]];
			for(var i=0;i<tab.length-1;i++){
				var intitule = tab[i].split('*')[0];
				var nb = parseInt(tab[i].split('*')[1]);
				tabParis[0].push([intitule,nb]);
			}
			return tabParis;
		};
		
		var getTabParisSimples = function(){
			var getParis=getLine('ajax_requetes.php?requete=5');
			var tab = getParis.split(';');
			var tabParis=[[]];
			for(var i=0;i<tab.length-1;i++){
				var intitule = tab[i].split('*')[0];
				var nb = parseInt(tab[i].split('*')[1]);
				tabParis[0].push([intitule,nb]);
			}
			return tabParis;
		};
		
		var getTabMatchs = function(){
			var getMatchs=getLine('ajax_requetes.php?requete=6');
			var tab = getMatchs.split(';');
			var tabMatchs=[[]];
			for(var i=0;i<tab.length-1;i++){
				var intitule = tab[i].split('*')[0];
				var nb = parseInt(tab[i].split('*')[1]);
				tabMatchs[0].push([intitule,nb]);
			}
			return tabMatchs;
		};
		
		var getTabChampionnats = function(){
			var getChampionnats=getLine('ajax_requetes.php?requete=7');
			var tab = getChampionnats.split(';');
			var tabChampionnats=[[]];
			for(var i=0;i<tab.length-1;i++){
				var intitule = tab[i].split('*')[0];
				var nb = parseInt(tab[i].split('*')[1]);
				tabChampionnats[0].push([intitule,nb]);
			}
			return tabChampionnats;
		};
		
		var getTabChampionnatsGagnes = function(){
			var getChampionnats=getLine('ajax_requetes.php?requete=8');
			var tab = getChampionnats.split(';');
			var tabChampionnats=[[]];
			for(var i=0;i<tab.length-1;i++){
				var intitule = tab[i].split('*')[0];
				var nb = parseInt(tab[i].split('*')[1]);
				tabChampionnats[0].push([intitule,nb]);
			}
			
			return tabChampionnats;
		};
		
		var getPourcentageCote = function(){
			var getCotes=getLine('ajax_requetes.php?requete=9');
			var tab = getCotes.split(';');
			var tabCotes=[[]];
			for(var i=0;i<tab.length-1;i++){
				var intitule = tab[i].split('*')[0];
				var nb = parseInt(tab[i].split('*')[1]);
				tabCotes[0].push([intitule,nb]);
			}
			
			return tabCotes;
		};
		
		function getLine(url){
			var resultat=null;
			var request = $.ajax({
				url: url,
				data: { },
				success: function(response){resultat=response},
				dataType: 'html',	
				async : false
			});	
			return resultat;
		}
		
		
		// Pie de tous les paris gagnés/perdus
		var plot2 = $.jqplot ('pieParis', [],{ 
			title: 'Tous les paris',
			seriesDefaults: {
			// Make this a pie chart.
			renderer: jQuery.jqplot.PieRenderer, 
			rendererOptions: {
			  // Put data labels on the pie slices.
			  // By default, labels show the percentage of the slice.
			  showDataLabels: true
			}
		  }, 
		  legend: { show:true, location: 'e' },
		  dataRenderer: getTabParis
		});
		
		// Pie des paris simples gagnés/perdus
		var plot3 = $.jqplot ('pieParisSimples', [],{ 
			title: 'Paris simples',
			seriesDefaults: {
			// Make this a pie chart.
			renderer: jQuery.jqplot.PieRenderer, 
			rendererOptions: {
			  // Put data labels on the pie slices.
			  // By default, labels show the percentage of the slice.
			  showDataLabels: true
			}
		  }, 
		  legend: { show:true, location: 'e' },
		  dataRenderer: getTabParisSimples
		});
		
		// Pie des matchs gagnés/perdus
		var plot4 = $.jqplot ('pieMatchs', [],{ 
			title: 'Tous les matchs',
			seriesDefaults: {
			// Make this a pie chart.
			renderer: jQuery.jqplot.PieRenderer, 
			rendererOptions: {
			  // Put data labels on the pie slices.
			  // By default, labels show the percentage of the slice.
			  showDataLabels: true
			}
		  }, 
		  legend: { show:true, location: 'e' },
		  dataRenderer: getTabMatchs
		});
		
		// Pie des paris par championnats
		var plot5 = $.jqplot ('pieChampionnats', [],{ 
			title: 'Paris par championnats',
			seriesDefaults: {
			// Make this a pie chart.
			renderer: jQuery.jqplot.PieRenderer, 
			rendererOptions: {
			  // Put data labels on the pie slices.
			  // By default, labels show the percentage of the slice.
			  showDataLabels: true
			}
		  }, 
		  legend: { show:true, location: 'e' },
		  dataRenderer: getTabChampionnats
		});
		
		// Bar des pourcentages de paris gagnés par championnats
		var plot6 = $.jqplot('barChampionnats', [], {
			title: 'Pourcentage de victoires par championnats',
			series:[{renderer:$.jqplot.BarRenderer}],
			axesDefaults: {
				tickRenderer: $.jqplot.CanvasAxisTickRenderer ,
				tickOptions: {
				  angle: -40,
				  fontSize: '10pt'
				}
			},
			axes: {
			  xaxis: {
				renderer: $.jqplot.CategoryAxisRenderer
			  }
			},
			dataRenderer: getTabChampionnatsGagnes
		  });
		  
		  // Bar des pourcentages de paris gagnés par championnats
		var plot7 = $.jqplot('barCote', [], {
			title: 'Pourcentage de réussite par cote',
			series:[{renderer:$.jqplot.BarRenderer}],
			axesDefaults: {
				tickRenderer: $.jqplot.CanvasAxisTickRenderer ,
				tickOptions: {
				  fontSize: '10pt'
				}
			},
			axes: {
			  xaxis: {
				renderer: $.jqplot.CategoryAxisRenderer
			  }
			},
			dataRenderer: getPourcentageCote
		  });
				
	});

	

	


</script>


</body>
</html>
