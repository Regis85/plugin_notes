<?php
/** Controleur du module notes : action voir
 * 
 * Afficher et modifier les notes d'une ou plusieurs évaluations
 * 
 * @author Régis Bouguin
 * @package saisie_notes
 * @subpackage voir
 * 
 * @see debug_var()
 * @see check_token()
 * @see charge_message()
 * @see enregistre_notes()
 * @see cacher_eval()
 * @see peut_noter_groupe()
 * @see evaluations_disponibles()
 * @see eval_non_choisies()
 * @see evaluations_modifiables()
 * @see trouveEleves()
 * @see cherche_notes()
 * @see recupere_groupe_actif()
 * @see recupere_periode_active()
 * @see recupere_periodes()
 */

/** 
 * Chargement du modele de la page
 *
 */
include CHEMIN_MODELE.VOIR.'.php';

//==================================
// Décommenter la ligne ci-dessous pour afficher les variables $_GET, $_POST, $_SESSION[PREFIXE] et $_SERVER pour DEBUG:
// $affiche_debug=debug_var();

// On récupère les données passées à la page


if (isset ($_POST['action'])) {
  switch ($_POST['action']) {
    case AJOUTE:
      if (!in_array ($_POST[EVALUATIONS], $_SESSION[PREFIXE]['id_devoir'])) {
	$_SESSION[PREFIXE]['id_devoir'][] = $_POST[EVALUATIONS];
	header("Location: index.php");
	die ();
      }
      
    case VOIR_CARNET:
      $_SESSION[PREFIXE]['contexte_action'] = VOIR;
      header("Location: ".CHEMIN_RACINE."cahier_notes/saisie_notes.php?id_conteneur=".$_SESSION[PREFIXE]['id_racine']);
      die ();
      
    case RETOUR_EVAL:
      echo "Retour à l'affichage des évaluations";
      $_SESSION[PREFIXE]['contexte_module'] = EVALUATIONS;
      $_SESSION[PREFIXE]['contexte_action'] = VOIR;
      header("Location: index.php");
      die ();
      
    case FORCE_ENREGISTRE:
      check_token();
      $donnees = $_POST;
      if (count($donnees)) {
	if (!enregistre_notes($donnees)) {
	  charge_message("Les données n'ont pas été sauvegardées") ;
	  $_SESSION[PREFIXE]['contexte_action'] = VOIR;
	  header("Location: index.php");
	  die ();
	} else {
	  charge_message("Les données ont été sauvegardées") ;	
	  $_SESSION[PREFIXE]["post_reussi"] = TRUE;
	}
	// $_SESSION[PREFIXE]['contexte_action'] = VOIR;
	// header("Location: index.php");
	// die ();
      }  
      break;
      
    default :
      echo $_POST['action'];
      die ();
  }
  
} else if (isset ($_POST[CACHER])){
  // On cache l'évaluation choisie
  cacher_eval($_POST[CACHER]);
  header("Location: index.php");
  die (); 
} else if (isset ($_POST[COLLER])){
  $_SESSION[PREFIXE]['contexte_action']=COLLER;
  $_SESSION[PREFIXE][COLLER]=$_POST[COLLER];
  header("Location: index.php");
  
  die (); 
}

$id_devoir = isset($_POST["id_devoir"]) ? $_POST["id_devoir"] : (isset($_GET["id_devoir"]) ? $_GET["id_devoir"] : NULL);

// remplir un tableau en $_SESSION[PREFIXE] avec les id de devoirs à afficher pour les retrouver à chaque page
if (!isset ($_SESSION[PREFIXE]["id_devoir"]) || ($id_devoir && !in_array ($id_devoir , $_SESSION[PREFIXE]["id_devoir"]))) {
  $_SESSION[PREFIXE]["id_devoir"][]=$id_devoir;
}

// on vérifie qu'il y a bien quelque chose à afficher
if (!isset ($_SESSION[PREFIXE]["id_devoir"])){
    charge_message("Aucun devoir sélectionné"); 
    $_SESSION[PREFIXE]['contexte_module']=EVALUATIONS;
    $_SESSION[PREFIXE]['contexte_action']=VOIR;
    header("Location: index.php");
} else {
  if (!count($_SESSION[PREFIXE]["id_devoir"])){
    charge_message("Vous devez choisir un devoir");
    $_SESSION[PREFIXE]['contexte_module']=EVALUATIONS;
    $_SESSION[PREFIXE]['contexte_action']=VOIR;
    header("Location: index.php");
  } else {
    // on vérifie que le prof peut évaluer le groupe ?
    if (!isset ($_SESSION[PREFIXE]['id_groupe_session']) || !peut_noter_groupe($_SESSION[PREFIXE]['id_groupe_session'])) {     
      charge_message("Vous n'avez pas les droits suffisant sur ce groupe");
      $_SESSION[PREFIXE]['contexte_module']=EVALUATIONS;
      $_SESSION[PREFIXE]['contexte_action']=VOIR;
      header("Location: index.php");
    } else {
      // Récupérer tous les devoirs disponibles
      $eval_possibles = evaluations_disponibles();
      
      // Récupérer les devoirs non choisis  
      $eval_disponibles=eval_non_choisies($eval_possibles);
      
      $tableau_notes = array();
      
      // Récupérer les devoirs qu'on veut modifier
      $eval_valides=evaluations_modifiables();
      
      if ($eval_valides) {
      // Récupérer les éleves du groupe
      $eleves_groupe = trouveEleves();
	if ($eleves_groupe) {
	  $tableau_notes = cherche_notes($eleves_groupe, $eval_valides);
	  // On met le tableau de notes en $_SESSION[PREFIXE]
	  // TODO : effacer ce tableau quand on revient dans le module Evaluation
	  $_SESSION[PREFIXE]['tableau_notes'] = $tableau_notes;
	}  
      }
    }  
  }
}

$id_groupe_actif = $_SESSION[PREFIXE]['id_groupe_session'];
  // On récupère les données du groupe actif  
$group_actif = recupere_groupe_actif($id_groupe_actif) ; 
  // On récupère la période active et on la met dans $_SESSION[PREFIXE]['periode_num']
$id_periode_active = recupere_periode_active() ;    
  // On récupère les périodes du groupe actif
$periodes = recupere_periodes($group_actif) ;

/** 
 * Chargement de la vue de la page
 *
 */
// Affichage des évaluations
include CHEMIN_VUE."/".VOIR.'.php';
  


?>
