<?php
	header('Content-Type: text/javascript; charset=utf8');

	// Paramètre d'entrée
	$mot    = $_GET['mot']; 
	$langue = $_GET['langue']; // valeur attendue : mot_corse et mot_francais
	
	// Déclaration des parametres de connexion
	try{
		$bdd = new PDO('mysql:host=10.0.215.24;dbname=adecec_infcor', 'adecec_infcor', 'inf58david11',
		array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
	}catch (Exception $e){
	    die('Erreur : impossible de se connecter à la base de donnée' . $e->getMessage());
	}
	
	// Requete SQL
	$req = $bdd->prepare('SELECT mot FROM '.$langue.' WHERE mot LIKE :motclef ORDER BY mot ASC LIMIT 0,9');
	$req->execute(array('motclef' => $mot.'%'));
	
	$n = 0;
		
	// On enregistre dans un tableau tous les id_definition
	while($data = $req->fetch(PDO::FETCH_ASSOC)){
		$row[$n] = $data['mot'];
		$n++;
	}
			
	// On ferme la connexion
	$req->closeCursor();
		
	// Callback en JSON
	echo json_encode($row);
?>
