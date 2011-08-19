<?php
/** Modèles du module evaluations : action ajoute
 * 
 * Création d'une évaluation
 * 
 * @author Régis Bouguin
 * @package arborescence
 * @subpackage ajoute
 * 
 */


/**
 * Fonction enregisteEval()
 * 
 * Enregistre les données de l'évaluation dans la base
 * 
 * @return bolean int TRUE si l'enregistrement s'est bien passé, FALSE + un message sinon
 */
function enregisteEval() {
  // On vide les messages
  unset ($_SESSION[PREFIXE]['tbs_msg']);
  // On teste si les champs obligatoires sont bien remplis
  if (!empty ($_POST['nom']) 
	  && !empty ($_POST['nomComplet']) 
	  && !empty ($_POST['emplacement']) 
	  && !empty ($_POST['coefEval'])
	  && !empty ($_POST['display_date'])
	  && !empty ($_POST['date_ele_resp'])) {
    
    // On récupère en session les données
    $donnees_passees = array();
    $donnees_passees['nomComplet'] = traitement_magic_quotes($_POST['nomComplet']);
    $donnees_passees['nom'] = traitement_magic_quotes($_POST['nom']);
    $donnees_passees['emplacement'] = $_POST['emplacement'];
    $donnees_passees['coefEval'] = $_POST['coefEval'];
    $donnees_passees['display_date'] = $_POST['display_date'];
    $donnees_passees['date_ele_resp'] = $_POST['date_ele_resp'];
    
    // ramener_sur_referentiel - noteSur20 (V ou F)
    If(isset ($_POST['noteSur20']) && $_POST['noteSur20']) {
      $donnees_passees['noteSur20'] = "V";
    } else {
      $donnees_passees['noteSur20'] = "F";
    }
    
    // display_parents - noteSurReleve = 1
    If(isset ($_POST['noteSurReleve'])) {
      $donnees_passees['noteSurReleve'] = $_POST['noteSurReleve'];
    } else {
      $donnees_passees['noteSurReleve'] = 0;
    }
    
    // display_parents_app - appSurReleve
    If(isset ($_POST['appSurReleve'])) {
      $donnees_passees['appSurReleve'] = $_POST['appSurReleve'];
    } else {
      $donnees_passees['appSurReleve'] = 0;
    }
    
    // facultatif moyenne
    //            - O entre dans le calcul
    //            - B supérieures à 10 entrent dans le calcul
    //            - N entre dans le calcul de la moyenne que si elle améliore la moyenne
    If(isset ($_POST['moyenne'])) {
      $donnees_passees['moyenne'] = $_POST['moyenne'];
    } else {
      charge_message("Vous n'avez pas défini la manière de prendre en compte la note dans la moyenne !");
    }
      
    
    // date - display_date
    If(isset ($_POST['display_date'])) {
      $donnees_passees['display_date'] = prepare_date ($_POST['display_date']);
    } else {
      charge_message("Vous n'avez pas défini la date d'affichage de l'évaluation !");
    }
    
    // coef - coefEval
    If(isset ($_POST['coefEval'])) {
      $donnees_passees['coefEval'] = $_POST['coefEval'];
    } else {
      charge_message("Vous n'avez pas défini le coefficient de l'évaluation !");
    }
    
    // note_sur - noteSur
    If(isset ($_POST['noteSur'])) {
      $donnees_passees['noteSur'] = $_POST['noteSur'];
    } else {
      charge_message("Vous n'avez pas défini la note maximale de l'évaluation !");
    }
    
    // date_ele_resp - date_ele_resp
    If(isset ($_POST['date_ele_resp'])) {
      $donnees_passees['date_ele_resp'] = prepare_date ($_POST['date_ele_resp']);
    } else {
      charge_message("Vous n'avez pas défini la date à laquelle les parents peuvent voir l'évaluation !");
    }
    
    // description - evalDescription    
    If(isset ($_POST['evalDescription'])) {
      $donnees_passees['evalDescription'] = $_POST['evalDescription'];
    } else {
      $donnees_passees['evalDescription'] = "";
    }
    
    // S'il y a des messages, c'est qu'on a des erreurs
    if (!empty ($_SESSION[PREFIXE]['tbs_msg'])) {
      return FALSE;    
    }
    
    $_SESSION[PREFIXE]['add_change_eval'] = $donnees_passees;
    
    if(isset ($_POST['id_eval'])) {
      $donnees_passees['id_eval'] = $_POST['id_eval'];
      // on a un id, on est en train de modifier une évaluation
      $sql="UPDATE cn_devoirs 
	      SET id_conteneur = '".$donnees_passees['emplacement']."',
	          id_racine = '".$_SESSION[PREFIXE]['id_racine']."',
		  nom_court = '".$donnees_passees['nom']."',
		  nom_complet = '".$donnees_passees['nomComplet']."',
		  description = '".$donnees_passees['evalDescription']."',
		  facultatif = '".$donnees_passees['moyenne']."',
		  date = '".$donnees_passees['display_date']."',
		  coef = '".$donnees_passees['coefEval']."',
		  note_sur = '".$donnees_passees['noteSur']."',
		  ramener_sur_referentiel = '".$donnees_passees['noteSur20']."',
		  display_parents = '".$donnees_passees['noteSurReleve']."',
		  display_parents_app = '".$donnees_passees['appSurReleve']."',
		  date_ele_resp = '".$donnees_passees['date_ele_resp']."'
	      WHERE id = '".$donnees_passees['id_eval']."'";
    } else {
      // on n'a pas d'id, on est en train de créer une évaluation
      $sql="INSERT INTO cn_devoirs (`id_conteneur`,`id_racine`,`nom_court`,`nom_complet`,`description`,`facultatif`,`date`,`coef`,`note_sur`,`ramener_sur_referentiel`,`display_parents`,`display_parents_app`,`date_ele_resp`)
	VALUES ('".$donnees_passees['emplacement']."','".$_SESSION[PREFIXE]['id_racine']."','".$donnees_passees['nom']."','".$donnees_passees['nomComplet']."','".$donnees_passees['evalDescription']."','".$donnees_passees['moyenne']."','".$donnees_passees['display_date']."','".$donnees_passees['coefEval']."','".$donnees_passees['noteSur']."','".$donnees_passees['noteSur20']."','".$donnees_passees['noteSurReleve']."','".$donnees_passees['appSurReleve']."','".$donnees_passees['date_ele_resp']."')";
    }
    if (mysql_query($sql)) {
      return TRUE;
    } else {
      charge_message("Échec lors de l'enregistrement dans la base");
      return FALSE;
    }
  }
  
  charge_message("Des champs obligatoires n'ont pas été renseignés");
  return FALSE;
}
  
/**
 * Fonction prepare_date
 * 
 * @param $traite_date text une date au format jj/mm/aaa
 * @return Date Date Une date au bon format pour être enregistrée dans la base
 * 
 */
function prepare_date ($traite_date) {
  if (my_ereg("([0-9]{2})/([0-9]{2})/([0-9]{4})", $traite_date)) {
    $annee = substr($traite_date,6,4);
    $mois = substr($traite_date,3,2);
    $jour = substr($traite_date,0,2);
  } else {
    $annee = strftime("%Y");
    $mois = strftime("%m");
    $jour = strftime("%d");
  }
  $date = $annee."-".$mois."-".$jour." 00:00:00";
  return $date;
}

/**
 * Fonction recharge_id($id_eval)
 * 
 * @param $id_eval int L'id de l'évaluation
 * @return Tableau mixed Les données de l'évaluation enregistrées dans la base
 * 
 */
function recharge_id($id_eval) {
  $donnees_passees['id_eval'] = $id_eval;
  $sql = "SELECT * FROM cn_devoirs WHERE `id` = '".$donnees_passees['id_eval']."' ";
  $result = mysql_query($sql);
  if ($result) {
    $row=mysql_fetch_object($result);
    $donnees_passees['emplacement'] = $row->id_conteneur;
    $donnees_passees['nom'] = $row->nom_court;
    $donnees_passees['nomComplet'] = $row->nom_complet;
    $donnees_passees['evalDescription'] = $row->description;
    $donnees_passees['moyenne'] = $row->facultatif;
    // mktime(0, 0, 0, 7, 1, 2000)
    $donnees_passees['display_date'] = strtotime($row->date);
    $donnees_passees['coefEval'] = $row->coef;
    $donnees_passees['noteSur'] = $row->note_sur;
    $donnees_passees['noteSur20'] = $row->ramener_sur_referentiel;
    $donnees_passees['noteSurReleve'] = $row->display_parents;
    $donnees_passees['appSurReleve'] = $row->display_parents_app;
    $donnees_passees['date_ele_resp'] = strtotime($row->date_ele_resp);	 	
  }
  return ($donnees_passees);
}

/**
 * Fonction recharge_defaut()
 * 
 * 
 * @return Tableau mixed Des données par défaut pour une nouvelle évaluation
 * 
 */
function recharge_defaut() {
  $donnees_passees['id_eval'] = FALSE;
  $donnees_passees['emplacement'] = "";
  $donnees_passees['nom'] = "Nouvelle évaluation";
  $donnees_passees['nomComplet'] = "Nouvelle évaluation - nom long";
  $donnees_passees['evalDescription'] = "";
  $donnees_passees['moyenne'] = "O";
  $donnees_passees['display_date'] = time();
  $donnees_passees['coefEval'] = "1";
  $donnees_passees['noteSur'] = 20;
  $donnees_passees['noteSur20'] = "V";
  $donnees_passees['noteSurReleve'] = 1;
  $donnees_passees['appSurReleve'] = 0;
  $donnees_passees['date_ele_resp'] = time();
  
  return ($donnees_passees);
  
}

?>
