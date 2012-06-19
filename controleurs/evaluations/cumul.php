<?php
/** Controleur du module evaluations : action cumul 
 * 
 * Création d'évaluations cumuls
 * 
 * @author Régis Bouguin
 * @package arborescence
 * @subpackage cumul
 *
 */

// On nettoie puis on appelle l'ancienne page
$_SESSION[PREFIXE]['contexte_module']=MODULE_DEFAUT;
$_SESSION[PREFIXE]['contexte_action']=VOIR;
// header("Location:".CHEMIN_RACINE."cahier_notes/index_cc.php?id_racine=".$_SESSION[PREFIXE]['id_racine']);
// die ();

/** 
 * Chargement du modele de la page
 *
 */
  include CHEMIN_MODELE."/".CUMUL.'.php';
  
  
   
/** 
 * Chargement de la vue de la page
 *
 */
  include CHEMIN_VUE."/".CUMUL.'.php';
  
?>
