<?php

/** Controleur du module notes : action coller 
 * 
 * Remplir une évaluation par copier/coller
 * 
 * @author Régis Bouguin
 * @package saisie_notes
 * @subpackage coller
 * 
*/

/** 
 * Chargement du modele de la page
 *
 */
include CHEMIN_MODELE.COLLER.'.php';

//==================================
// Décommenter la ligne ci-dessous pour afficher les variables $_GET, $_POST, $_SESSION[PREFIXE] et $_SERVER pour DEBUG:
// $affiche_debug=debug_var();

$notes_copier = $comments_copier = FALSE;

if (isset ($_POST['enregistrer'])) {
  switch ($_POST['enregistrer']) {
    case VERIFIER:
      if ($_POST['colle_notes'] != "") {
	$notes_copier=colle_notes($_POST['colle_notes'],$_SESSION[PREFIXE]['coller']); 
      }
      if ($_POST['colle_comment'] != "") {
	$comments_copier=colle_comments($_POST['colle_comment']);
      }
      break;
    case ENREGISTRER:
      check_token();
      if (enregistre_colle()) {
	$_SESSION[PREFIXE]['contexte_action'] = VOIR;
	unset ($_SESSION[PREFIXE]['tableau_colle']);
	unset ($_SESSION[PREFIXE]['eval_colle']);
	unset ($_SESSION[PREFIXE]['coller']);	
	$_SESSION[PREFIXE]["post_reussi"] = TRUE;
	charge_message("Enregistrement des données réussi !") ;
	header("Location: index.php");
	exit;   
      } else  {
	charge_message("Erreur lors de l'enregistrement des données en copier/coller !") ;
      }
      break;
    case ABANDONNER:
      $_SESSION[PREFIXE]['contexte_action'] = VOIR;
      header("Location: index.php");
      exit;
  }
}

$classes = classe_groupe($_SESSION[PREFIXE]['id_groupe_session']);
$eleves=trouveEleves();

// TODO : mettre dans le module
if ($notes_copier) {
  if (count($notes_copier) < count($eleves)){
    charge_message("ERREUR : Le nombre de notes (".count($notes_copier).") ne correspond pas au nombre d'élèves (".count($eleves).") !") ;
    $notes_copier = FALSE;
  } else {
    $i=0;
    foreach ($eleves as &$eleve) {
      $eleve['note'] = $notes_copier[$i]['note'];
      $eleve['statut'] = $notes_copier[$i]['statut'];
      $i++;
    }
    unset ($eleve);   
  }
}

if ($comments_copier){     
  if (count($comments_copier) < count($eleves)){
    charge_message("ERREUR : Le nombre de commentaires (".count($comments_copier).") ne correspond pas au nombre d'élèves (".count($eleves).") !") ;
    $comments_copier = FALSE;
  } else {
    $i=0;
    foreach ($eleves as &$eleve) {
      $eleve['commentaire'] = $comments_copier[$i];
      $i++;
    }
    unset ($eleve);  
  }
}
// fin TODO

$evaluation = donnee_evaluation($_SESSION[PREFIXE][COLLER]);

// on passe $eleves en $_SESSION[PREFIXE] pour pouvoir le récupérer lorsqu'on enregistre
$_SESSION[PREFIXE]['tableau_colle']=$eleves;
$_SESSION[PREFIXE]['eval_colle']=$evaluation['id'];


/** 
 * Chargement de la vue de la page
 *
 */
include CHEMIN_VUE."/".COLLER.'.php';


?>
