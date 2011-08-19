<?php
/** Controleur du module evaluations : action voir 
 * 
 * Affichage de l'arborescence des boites et des �valuations
 * 
 * @package arborescence
 * @subpackage voir
 * @author R�gis Bouguin
 * 
 * @see traite_groupe()
 * @see recupere_tous_groupes()
 * @see recupere_groupe_actif()
 * @see recupere_periodes()
 * @see recupere_periode_active()
 * @see eval_dispo()
 * @see liens_externes()
 */

/** 
 * Chargement du modele de la page
 *
 */
include CHEMIN_MODELE."/".VOIR.'.php';

//==================================
// D�commenter la ligne ci-dessous pour afficher les variables $_GET, $_POST, $_SESSION[PREFIXE] et $_SERVER pour DEBUG:
// $affiche_debug=debug_var();

// On r�cup�re le groupe actif et on le met dans $_SESSION[PREFIXE]['id_groupe_session']    
$id_groupe_actif = traite_groupe(); 

if ($id_groupe_actif===GROUPE_INTERDIT) {
/** 
 * Appel d'une page sp�ciale lors d'une tentative d'acc�s avec un mauvais groupe
 * 
 * @TODO : G�rer l'acc�s � une page interdite
 *
 */
  include CHEMIN_VUE_GLOBALE."/".'interdit.php';
} else {
  unset ($_SESSION[PREFIXE]['id_groupe_session']);
  $_SESSION[PREFIXE]['id_groupe_session'] = $id_groupe_actif;
      
  // On r�cup�re tous les groupes possibles du prof
  $tous_groupes = recupere_tous_groupes();
  
  $group_actif = array();
  $periodes = array();
  $liens_autres_pages = array();
  
  if ($id_groupe_actif) {  
  // On a un groupe valide, 
  // On r�cup�re les donn�es du groupe actif  
    $group_actif = recupere_groupe_actif($id_groupe_actif) ;
    
  // On r�cup�re les p�riodes du groupe actif
    $periodes = recupere_periodes($group_actif) ;
    
  // On r�cup�re la p�riode active et on la met dans $_SESSION[PREFIXE]['periode_num']
    $id_periode_active = recupere_periode_active() ;
    
    $periode_ouverte=ouverte($_SESSION[PREFIXE]['periode_num']) ;
  
   // Si on a une periode ouverte, on peut afficher les �valuations
   if ($id_periode_active) {     
     $eval_toutes = eval_dispo($periodes) ;
   }
    
   // Liens autres pages
    $liens_autres_pages=liens_externes();
    
    
  }

/** 
 * Chargement de la vue de la page
 *
 */
  include CHEMIN_VUE."/".VOIR.'.php';
  
}
  
?>
