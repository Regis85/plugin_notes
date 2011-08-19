<?php
/** Controleur du module evaluations : action idem
 * 
 * Dupliquer l'organisation d'une période
 * 
 * @author Régis Bouguin
 * @package arborescence
 * @subpackage idem
 * 
 * @see charge_message()
 * 
 * @see check_token()
 * @see enregistre_arbo()
 * @see getSettingValue()
 * 
 * @see charge_arborescence()
 * 
 * @see recupere_groupe_actif()
 * @see recupere_periodes()
 * 
 */

/** 
 * Chargement du modele de la page
 *
 */
  include CHEMIN_MODELE."/".IDEM.'.php';

  
  // On a annuler, on efface les données en Session puis on retourne à la page de visualisation
  if(isset ($_POST['mode']) && $_POST['mode']==ABANDONNER) {
    if (isset ($_SESSION[PREFIXE]['arborescence'])) {
      unset($_SESSION[PREFIXE]['arborescence']);
    }
    $_SESSION[PREFIXE]['contexte_module']=MODULE_DEFAUT;
    $_SESSION[PREFIXE]['contexte_action']=VOIR;
    charge_message("Recopie de la structure précédente abandonnée");
    header("Location: index.php");
    die ();
  }
  
  // On a valider, on enregistre, on efface les données en Session puis on retourne 
  // à la page de visualisation
  if(isset ($_POST['mode']) && $_POST['mode']==ENREGISTRER) {
    // Si on a choisi Enregistrer, on vérifie check_token
    check_token();
    // Si l'enregistrement ce passe bien, on efface les données en Session puis on retourne à la page de visualisation
    if (enregistre_arbo()) {
      unset($_SESSION[PREFIXE]['arborescence']);
      $_SESSION[PREFIXE]['contexte_module']=MODULE_DEFAUT;
      $_SESSION[PREFIXE]['contexte_action']=VOIR;
      $_SESSION[PREFIXE]['post_reussi']=TRUE;
      charge_message("Recopie des ".getSettingValue('gepi_denom_boite')."s réussie");
      header("Location: index.php");
      die ();   
    } else {
    // Sinon on réaffiche les données
    charge_message("Echec de la copie de la structure dans la base");
      
      
    }
    
  }
  
  // On arrive pour la première fois, on récupère les données puis on affiche la page
  $arborescence=charge_arborescence();
  if (!$arborescence) {
    if (isset ($_SESSION[PREFIXE]['arborescence'])) {
      unset($_SESSION[PREFIXE]['arborescence']);
    }
    $_SESSION[PREFIXE]['contexte_module']=MODULE_DEFAUT;
    $_SESSION[PREFIXE]['contexte_action']=VOIR;
    charge_message("Echec de la recopie de la structure");
    header("Location: index.php");
    die ();
  }
  
// On passe le tableau en $_SESSION[PREFIXE]
  $_SESSION[PREFIXE]['arborescence']=$arborescence;
  
// On récupère les données du groupe actif  
  $group_actif = recupere_groupe_actif($_SESSION[PREFIXE]['id_groupe_session']) ;

// On récupère les périodes du groupe actif
  $periodes = recupere_periodes($group_actif) ;
    
  //==================================
  // Décommenter la ligne ci-dessous pour afficher les variables $_GET, $_POST, $_SESSION[PREFIXE] et $_SERVER pour DEBUG:
  // $affiche_debug=debug_var();
    
/** 
 * Chargement de la vue de la page
 *
 */ 
  include CHEMIN_VUE."/".IDEM.'.php';
  
?>
