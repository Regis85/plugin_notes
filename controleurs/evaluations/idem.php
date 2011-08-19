<?php
/** Controleur du module evaluations : action idem
 * 
 * Dupliquer l'organisation d'une p�riode
 * 
 * @author R�gis Bouguin
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

  
  // On a annuler, on efface les donn�es en Session puis on retourne � la page de visualisation
  if(isset ($_POST['mode']) && $_POST['mode']==ABANDONNER) {
    if (isset ($_SESSION[PREFIXE]['arborescence'])) {
      unset($_SESSION[PREFIXE]['arborescence']);
    }
    $_SESSION[PREFIXE]['contexte_module']=MODULE_DEFAUT;
    $_SESSION[PREFIXE]['contexte_action']=VOIR;
    charge_message("Recopie de la structure pr�c�dente abandonn�e");
    header("Location: index.php");
    die ();
  }
  
  // On a valider, on enregistre, on efface les donn�es en Session puis on retourne 
  // � la page de visualisation
  if(isset ($_POST['mode']) && $_POST['mode']==ENREGISTRER) {
    // Si on a choisi Enregistrer, on v�rifie check_token
    check_token();
    // Si l'enregistrement ce passe bien, on efface les donn�es en Session puis on retourne � la page de visualisation
    if (enregistre_arbo()) {
      unset($_SESSION[PREFIXE]['arborescence']);
      $_SESSION[PREFIXE]['contexte_module']=MODULE_DEFAUT;
      $_SESSION[PREFIXE]['contexte_action']=VOIR;
      $_SESSION[PREFIXE]['post_reussi']=TRUE;
      charge_message("Recopie des ".getSettingValue('gepi_denom_boite')."s r�ussie");
      header("Location: index.php");
      die ();   
    } else {
    // Sinon on r�affiche les donn�es
    charge_message("Echec de la copie de la structure dans la base");
      
      
    }
    
  }
  
  // On arrive pour la premi�re fois, on r�cup�re les donn�es puis on affiche la page
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
  
// On r�cup�re les donn�es du groupe actif  
  $group_actif = recupere_groupe_actif($_SESSION[PREFIXE]['id_groupe_session']) ;

// On r�cup�re les p�riodes du groupe actif
  $periodes = recupere_periodes($group_actif) ;
    
  //==================================
  // D�commenter la ligne ci-dessous pour afficher les variables $_GET, $_POST, $_SESSION[PREFIXE] et $_SERVER pour DEBUG:
  // $affiche_debug=debug_var();
    
/** 
 * Chargement de la vue de la page
 *
 */ 
  include CHEMIN_VUE."/".IDEM.'.php';
  
?>
