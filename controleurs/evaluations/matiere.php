<?php
/** Controleur du module evaluations : action matiere
 * 
 * Création de sous-conteneurs
 * 
 * @author Régis Bouguin
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
  // Décommenter la ligne ci-dessous pour afficher les variables $_GET, $_POST, $_SESSION[PREFIXE] et $_SERVER pour DEBUG:
  // $affiche_debug=debug_var();
  
  // vérifier que le prof peut noter le groupe
  if (!peut_noter_groupe($_SESSION[PREFIXE]['id_groupe_session'])) {
    charge_message("Vous n'avez pas le droit de modifier le groupe ".$_SESSION[PREFIXE]['id_groupe_session']) ;
    $_SESSION[PREFIXE]['contexte_action']=VOIR;
    $_SESSION[PREFIXE]['action']=VOIR;
    header("Location: index.php");
    die ();
  }
  
  // récupérer les données du groupe
  $classes_groupe=groupe_long($_SESSION[PREFIXE]['id_groupe_session']);
  
  // récupérer les conteneurs qui peuvent accueillir une évaluation
  $sous_matieres=conteneurs();
  
  // Si on a annuler, revenir à la page de vision de l'arborescence
  if (isset ($_POST['mode']) && $_POST['mode']==ABANDONNER) {
    // on vide les données en mémoire
    unset ($_SESSION[PREFIXE]['add_change_conteneur']);
    // TODO : gérer le retour
    $_SESSION[PREFIXE]['contexte_action']=VOIR;
    $_SESSION[PREFIXE]['action']=VOIR;
    charge_message("Enregistrement du conteneur abandonné");
    header("Location: index.php");
    die ();
  } elseif (isset ($_POST['mode']) && $_POST['mode']==ENREGISTRER) {
    // Si on a choisi Enregistrer, on vérifie check_token
    check_token();
    // vérifier les champs et enregistrer
    if (enregisteConteneur()) {
      // on vide les données en mémoire
      unset ($_SESSION[PREFIXE]['add_change_conteneur']);
      $_SESSION[PREFIXE]['contexte_action']=VOIR;
      $_SESSION[PREFIXE]['action']=VOIR;
      unset ($_SESSION[PREFIXE]['add_change_conteneur']);
      $_SESSION[PREFIXE]['post_reussi']=TRUE;
      charge_message("Enregistrement du conteneur réussi");
      header("Location: index.php");
      die ();
    } else {
      charge_message("Echec de l'enregistrement du conteneur");	
      $affiche_conteneur = $_SESSION[PREFIXE]['add_change_conteneur'];     
    }
    
    
  } else {
    // Sinon
    // si on a des données en $session on les récupère
    if (isset ($_SESSION[PREFIXE]['add_change_conteneur'])) {
      $affiche_conteneur = $_SESSION[PREFIXE]['add_change_conteneur'];    
    } else {
      // Sinon, on vérifie si on veut modifier un conteneur
      $id_conteneur = isset ($_POST['id_conteneur']) ? $_POST['id_conteneur'] : (isset ($_GET['id_conteneur']) ? $_GET['id_conteneur'] : NULL);
      // Si on passe un Id de conteneur, le charger
      if (!empty ($id_conteneur)) {
	$affiche_conteneur = recharge_conteneur($id_conteneur);
      } else {
	// On charge des données par défaut
	$affiche_conteneur = recharge_conteneur_defaut();
      }
    }
}
  
// Sinon, s'il y a des données en Session, on les récupère

// Sinon, charger les données par défaut pour un nouveau conteneur
$affiche_moyenne['releve']=TRUE;
$affiche_moyenne['bulletin']=FALSE;
      
/** 
 * Chargement de la vue de la page
 *
 */
  include CHEMIN_VUE."/".MATIERE.'.php';
?>
