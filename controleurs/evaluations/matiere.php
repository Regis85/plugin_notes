<?php
/** Controleur du module evaluations : action matiere
 * 
 * Cr�ation de sous-conteneurs
 * 
 * @author R�gis Bouguin
 * @package arborescence
 * @subpackage matiere
 * @see peut_noter_groupe()
 * @see groupe_long()
 * @see conteneurs()
 * @see check_token()
 * @see enregisteConteneur()
 * @see recharge_conteneur()
 * @see recharge_conteneur_defaut()
 * 
 */

/** 
 * Chargement du modele de la page
 *
 */
  include CHEMIN_MODELE."/".MATIERE.'.php';
  
  //==================================
  // D�commenter la ligne ci-dessous pour afficher les variables $_GET, $_POST, $_SESSION[PREFIXE] et $_SERVER pour DEBUG:
  // $affiche_debug=debug_var();
  
  // v�rifier que le prof peut noter le groupe
  if (!peut_noter_groupe($_SESSION[PREFIXE]['id_groupe_session'])) {
    charge_message("Vous n'avez pas le droit de modifier le groupe ".$_SESSION[PREFIXE]['id_groupe_session']) ;
    $_SESSION[PREFIXE]['contexte_action']=VOIR;
    $_SESSION[PREFIXE]['action']=VOIR;
    header("Location: index.php");
    die ();
  }
  
  // r�cup�rer les donn�es du groupe
  $classes_groupe=groupe_long($_SESSION[PREFIXE]['id_groupe_session']);
  
  // r�cup�rer les conteneurs qui peuvent accueillir une �valuation
  $sous_matieres=conteneurs();
  
  // Si on a annuler, revenir � la page de vision de l'arborescence
  if (isset ($_POST['mode']) && $_POST['mode']==ABANDONNER) {
    // on vide les donn�es en m�moire
    unset ($_SESSION[PREFIXE]['add_change_conteneur']);
    // TODO : g�rer le retour
    $_SESSION[PREFIXE]['contexte_action']=VOIR;
    $_SESSION[PREFIXE]['action']=VOIR;
    charge_message("Enregistrement du conteneur abandonn�");
    header("Location: index.php");
    die ();
  } elseif (isset ($_POST['mode']) && $_POST['mode']==ENREGISTRER) {
    // Si on a choisi Enregistrer, on v�rifie check_token
    check_token();
    // v�rifier les champs et enregistrer
    if (enregisteConteneur()) {
      // on vide les donn�es en m�moire
      unset ($_SESSION[PREFIXE]['add_change_conteneur']);
      $_SESSION[PREFIXE]['contexte_action']=VOIR;
      $_SESSION[PREFIXE]['action']=VOIR;
      unset ($_SESSION[PREFIXE]['add_change_conteneur']);
      $_SESSION[PREFIXE]['post_reussi']=TRUE;
      charge_message("Enregistrement du conteneur r�ussi");
      header("Location: index.php");
      die ();
    } else {
      charge_message("Echec de l'enregistrement du conteneur");	
      $affiche_conteneur = $_SESSION[PREFIXE]['add_change_conteneur'];     
    }
    
    
  } else {
    // Sinon
    // si on a des donn�es en $session on les r�cup�re
    if (isset ($_SESSION[PREFIXE]['add_change_conteneur'])) {
      $affiche_conteneur = $_SESSION[PREFIXE]['add_change_conteneur'];    
    } else {
      // Sinon, on v�rifie si on veut modifier un conteneur
      $id_conteneur = isset ($_POST['id_conteneur']) ? $_POST['id_conteneur'] : (isset ($_GET['id_conteneur']) ? $_GET['id_conteneur'] : NULL);
      // Si on passe un Id de conteneur, le charger
      if (!empty ($id_conteneur)) {
	$affiche_conteneur = recharge_conteneur($id_conteneur);
      } else {
	// On charge des donn�es par d�faut
	$affiche_conteneur = recharge_conteneur_defaut();
      }
    }
}
  
// Sinon, s'il y a des donn�es en Session, on les r�cup�re

// Sinon, charger les donn�es par d�faut pour un nouveau conteneur
$affiche_moyenne['releve']=TRUE;
$affiche_moyenne['bulletin']=FALSE;
      
/** 
 * Chargement de la vue de la page
 *
 */
  include CHEMIN_VUE."/".MATIERE.'.php';
?>
