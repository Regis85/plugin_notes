<?php

/** 
 * Controleur du module evaluations : action duplique
 * 
 * Duplique une évaluation dans un autre groupe
 * @author Régis Bouguin
 * @package arborescence
 * @subpackage duplique
 */

/** 
 * Chargement du modele de la page
 *
 */
  include CHEMIN_MODELE."/".DUPLIQUE.'.php';

  //==================================
  // Décommenter la ligne ci-dessous pour afficher les variables $_GET, $_POST, $_SESSION[PREFIXE] et $_SERVER pour DEBUG:
   $affiche_debug=debug_var();

$id_conteneur = isset ($_GET['id_conteneur']) ? $_GET['id_conteneur'] : NULL;
$id_devoir = isset ($_GET['id_devoir']) ? $_GET['id_devoir'] : NULL;

echo 'conteneur : '.$id_conteneur.'<br />devoir : '.$id_devoir;

/** 
 * Chargement de la vue de la page
 *
 */  
  include CHEMIN_VUE."/".DUPLIQUE.'.php';

?>
