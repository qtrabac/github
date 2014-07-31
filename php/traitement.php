<?php
	header('Content-Type: text/json; charset=utf8');

	// Paramètre d'entrée
	$mot    = $_GET['mot']; 
	$langue = $_GET['langue']; // valeur attendue : mot_corse et mot_francais
	$param  = $_GET['param'];  // valeur attendue : ex (FRANCESE TALIANU ...)
	
	// On compte le nombre de mot
	$nb_param  = str_word_count($param);
	
	// On enregistre dans un array les mots
	$tab_param = str_word_count($param,1);
	
	// Déclaration des parametres de connexion
	try{
		$bdd = new PDO('mysql:host=10.0.215.24;dbname=adecec_infcor', 'adecec_infcor', 'inf58david11',
		array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
	}catch (Exception $e){
	    die('Erreur : impossible de se connecter à la base de donnée' . $e->getMessage());
	}
	
	// Requete SQL
	$req = $bdd->prepare('SELECT id_definition FROM '.$langue.' WHERE mot LIKE :motclef');
	$req->execute(array('motclef' => $mot));
	
	$n = 0;
		
	// On enregistre dans un tableau tous les id_definition
	while($data = $req->fetch(PDO::FETCH_ASSOC)){
		$row[$n] = $data['id_definition'];
		$n++;
	}
			
	// On ferme la connexion
	$req->closeCursor();
	
	if($n != '0'){
		// On supprime tous les doublons du tableau
		$result = array_unique($row);
		
		// On refait l'indexation du tableau
		$row2 = array_values($result);
		
		// Calcul du nombre de resultat
		$total = count($row2);
	}else{
		$total = 0;
	}
	
	// On crée un tableau
	$resultats = array();
	
	// Traitement de la recherche
	for($i = 0; $i < $total; $i++){
		$req2 = $bdd->prepare('SELECT DISTINCT * FROM definition WHERE id = :id');
		$req2->execute(array('id' => $row2[$i]));
			
		while($data2 = $req2->fetch(PDO::FETCH_ASSOC)){
			// ID1 
			$var1 = $data2['ID1'];
			// On remplace les ' ' par - pour liès les mots composés
			$rav1 = str_replace(' ','-', $var1);
			$rav1 = strtr($rav1,'àéèòìù','aeeoiu');
			
			// VARIANTESD
			$var2 = $data2['VARIANTESD'];
			// On remplace les ' ' par - pour liès les mots composés
			$rav2 = str_replace(' ','-', $var2);
			$rav2 = strtr($rav2,'àéèòìù','aeeoiu');
			
			// On compte le nombre de mot contenu dans la chaine
			$nbMot1 = str_word_count($rav1, 0);
			$nbMot2 = str_word_count($rav2, 0);
			
			// Calcul de la taille max de la boucle for
			$nb1 = $nbMot1;
			$nb2 = $nbMot2;
			
			$limite1 = $nbMot1 - 1;
			$limite2 = $nbMot2 - 1;
			
			// On stock les mots dans un tableau
			$str1 = explode(",", $var1);
			$str2 = explode(",", $var2);
			
			// ***** Affichage ID1 & VarianteSD	
			for($x = 0; $x < $nb1; $x++){
				$resultats[$i]['id'] = $resultats[$i]['id'].$str1[$x];
			}
			if(($var1 != '') && ($var2 != ''))
				$resultats[$i]['id'] = $resultats[$i]['id'].',&nbsp;';
			for($w = 0; $w < $nb2; $w++){
				$resultats[$i]['id'] = $resultats[$i]['id'].$str2[$w];
			}
			// Pkoi une balise b ?
			$resultats[$i]['id'] = '<b>'.$resultats[$i]['id'].'</b>';
			// *****
			
			// On génère le resultat pour chaque paramètre coché par l'utilisateur
			for($v=0;$v<$nb_param;$v++){
				$var = $tab_param[$v];
				$resultats[$i][$var] = '<b>'.strtolower($var).'</b> : '.$data2[$var];
			}
		}
		// On ferme la connection
		$req2->closeCursor();
	}
	
	// Callback en JSON
	echo json_encode($resultats);
	
	/* Exemple fichier JSON attendue
	{ 
  		"resultats": {
  			"0", 
 				"FRANCESE": [ {
          			"bonjour", 
      			} ], 
      		"1", 
 				"TALIANU": [ {
         			"buongiorno, bongiorno",
      			} ]
      	}
	} 
	*/
?>
