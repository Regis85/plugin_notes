<?php
/** Controleur du module �valuations : action supprimer
 * 
 * Supprimer un conteneur ou une �valuation
 * 
 * @author R�gis Bouguin
 * @package arborescence
 * @subpackage supprime
 */


/** 
 * Chargement du modele de la page
 *
 */
include CHEMIN_MODELE."/".SUPPRIME.'.php';

// D�commenter la ligne ci-dessous pour afficher les variables $_GET, $_POST, $_SESSION[PREFIXE] et $_SERVER pour DEBUG:
//  $affiche_debug=debug_var();

$choix = isset($_POST['niveau']) ? $_POST['niveau'] : (isset($_GET['niveau']) ? $_GET['niveau'] : NULL) ;
if (!$choix) {
  // Affichage de la page des �valuations
  $_SESSION[PREFIXE]['contexte_action']=VOIR;
  header("Location: index.php");
  die (); 
} else if($choix==MATIERE) {

  $conteneur = isset($_POST['id_conteneur']) ? $_POST['id_conteneur'] : (isset($_GET['id_conteneur']) ? $_GET['id_conteneur'] : NULL) ;
  if (!$conteneur) {
    // Affichage de la page des �valuations
    $_SESSION[PREFIXE]['contexte_action']=VOIR;
    header("Location: index.php");
    die ();
  }

  $continue = peut_supprimer_conteneur($conteneur);

  if (!$continue) {
    $_SESSION[PREFIXE]["tbs_msg"] = "Vous n'avez pas les droits pour supprimer le conteneur";
    $_SESSION[PREFIXE]['contexte_action']=VOIR;
    header("Location: index.php");
    die ();
  }

  $confirmation_suppr = isset($_POST['confirmation_suppr']) ? $_POST['confirmation_suppr'] : (isset($_GET['confirmation_suppr']) ? $_GET['confirmation_suppr'] : NULL) ;

  if ($confirmation_suppr==SUPPRIMER) {
    // on v�rifie qu'il y a bien le CRSF_alea
    check_token();
    $_SESSION[PREFIXE]['contexte_action']=VOIR;
    // supprimer le conteneur
    if (!supprime_conteneur($conteneur)) {
      $_SESSION[PREFIXE]["tbs_msg"] = "Echec lors de la tentative de suppression du conteneur";
      die ();
    } else {
      $_SESSION[PREFIXE]["tbs_msg"] = "Suppression r�ussie";
    }
    // et retourner � la page de  visualisation
    header("Location: index.php");
    die ();
  } else if ($confirmation_suppr) {
    $_SESSION[PREFIXE]['contexte_action']=VOIR;
    header("Location: index.php");
    die (); 
  }

  // On recherche le nom de l'�valuation
   $donnees_supprime = charge_module($conteneur);

} else if($choix==EVALUATION) {
  
  $conteneur = isset($_POST['id_conteneur']) ? $_POST['id_conteneur'] : (isset($_GET['id_conteneur']) ? $_GET['id_conteneur'] : NULL) ;
  
  $evaluation = isset($_POST['id_devoir']) ? $_POST['id_devoir'] : (isset($_GET['id_devoir']) ? $_GET['id_devoir'] : NULL) ;
  
  // aucune �valuation pass�e
  if (!$evaluation) {
    $_SESSION[PREFIXE]["tbs_msg"] = "Vous n'avez pas choisi d'�valuation";
    $_SESSION[PREFIXE]['contexte_action']=VOIR;
    // Affichage de la page des �valuations
    header("Location: index.php");
    die ();
  }
  
  $continue = peut_supprimer_evaluation($evaluation);
  if (!$continue) {
    charge_message("Vous n'avez pas les droits pour supprimer l'�valuation");
    $_SESSION[PREFIXE]['contexte_action']=VOIR;
    header("Location: index.php");
    die ();
  }
  
  $continue = evaluation_vide($evaluation);
  if (!$continue) {
    charge_message("ERREUR : L'�valuation n'est pas vide");
    charge_message("Vous devez supprimer les notes avant de supprimer l'�valuation");
    $_SESSION[PREFIXE]['contexte_action']=VOIR;
    header("Location: index.php");
    die ();
  }
  
  $confirmation_suppr = isset($_POST['confirmation_suppr']) ? $_POST['confirmation_suppr'] : (isset($_GET['confirmation_suppr']) ? $_GET['confirmation_suppr'] : NULL) ;

  if ($confirmation_suppr==SUPPRIMER) {
    // supprimer le conteneur
    if (!supprime_evaluation($evaluation)) {
      // TODO : renvoyer un message
      $_SESSION[PREFIXE]["tbs_msg"] = "Echec lors de la suppression de l'�valuation";
      $_SESSION[PREFIXE]['contexte_action']=VOIR;
      header("Location: index.php");
      die (); // � supprimer quand le message sera construit
    }
    // mettre � jour le tableau des �valuations visibles
    verifie_eval_visibles();
    // et retourner � la page de  visualisation
    $_SESSION[PREFIXE]["tbs_msg"] = "L'�valuation a �t� supprim�e";
    $_SESSION[PREFIXE]['contexte_action']=VOIR;
    header("Location: index.php");
    die ();
  } else if ($confirmation_suppr) {
    $_SESSION[PREFIXE]["tbs_msg"] = "Abandon de la suppression de l'�valuation";
    $_SESSION[PREFIXE]['contexte_action']=VOIR;
    header("Location: index.php");
    die (); 
  }

  // On recherche le nom de l'�valuation
   $donnees_supprime = charge_evaluation($evaluation);


}

/** 
 * Chargement de la vue de la page
 *
 */
  include CHEMIN_VUE."/".SUPPRIME.'.php'; 
  
?>
