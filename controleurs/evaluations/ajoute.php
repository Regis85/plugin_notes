<?php   
/** Controleur du module evaluations : action ajoute
 * 
 * Cr�ation d'une �valuation
 * 
 * @author R�gis Bouguin
 * @package arborescence
 * @subpackage ajoute
 * 
 */

/** 
 * Chargement du modele de la page
 *
 */
  include CHEMIN_MODELE."/".AJOUTE.'.php';

  //==================================
  // D�commenter la ligne ci-dessous pour afficher les variables $_GET, $_POST, $_SESSION[PREFIXE] et $_SERVER pour DEBUG:
   $affiche_debug=debug_var();

  // v�rifier que le prof peut noter le groupe
  
  
  $mode_creation= isset ($_POST['creation']) ? $_POST['creation'] : (isset ($_GET['creation']) ? $_GET['creation'] : NULL);
  // r�orienter vers le bon mode de cr�ation/modification
  switch ($mode_creation) {
    case MATIERE:
      $_SESSION[PREFIXE]['contexte_action']=MATIERE;
      header("Location: index.php");
      die ();
    case EVALUATION:
      break;
    case DUPLIQUE:
      $_SESSION[PREFIXE]['contexte_action']=DUPLIQUE;
      header("Location: index.php?id_devoir=".$_POST['id_eval']."&id_conteneur=".$_POST['id_conteneur']."");
      die ();
    case CUMUL:
      $_SESSION[PREFIXE]['contexte_action']=CUMUL;
      header("Location: index.php");
      die ();
    case IDEM:
      $_SESSION[PREFIXE]['contexte_action']=IDEM;
      header("Location: index.php");
      die ();
    default :
      $_SESSION[PREFIXE]['contexte_module']=MODULE_DEFAUT;
      $_SESSION[PREFIXE]['contexte_action']=VOIR;
      // break;
      header("Location: index.php");
      die ();
  }

  // r�cup�rer les donn�es du groupe
  $classes_groupe=groupe_long($_SESSION[PREFIXE]['id_groupe_session']);

  // r�cup�rer les conteneurs qui peuvent accueillir une �valuation
  $sous_matieres=conteneurs();

  if (empty ($sous_matieres)) {
		$sous_matieres=cree_carnet_notes($_SESSION[PREFIXE]['id_groupe_session']);
		if (!$sous_matieres) {
			$_SESSION[PREFIXE]['contexte_module']=MODULE_DEFAUT;
			$_SESSION[PREFIXE]['contexte_action']=VOIR;
			header("Location: index.php");
			die ();
		} else {
			$sous_matieres=conteneurs();
		}
  }
  
  // V�rifier si on veut abandonner 
  if (isset ($_POST['mode']) && $_POST['mode']==ABANDONNER) {
    // on vide les donn�es en m�moire
    unset ($_SESSION[PREFIXE]['add_change_eval']);
    // TODO : g�rer le retour
    $_SESSION[PREFIXE]['contexte_action']=VOIR;
    $_SESSION[PREFIXE]['action']=VOIR;
    charge_message("Enregistrement de l'�valuation abandonn�");
    header("Location: index.php");
    die ();
    
  }
  // V�rifier si on veut enregistrer 
  if (isset ($_POST['mode']) && $_POST['mode']==ENREGISTRER) {
    check_token();
    // on vide les donn�es en m�moire
    unset ($_SESSION[PREFIXE]['add_change_eval']);
    if(enregisteEval()) {
    // TODO : g�rer le retour
      $_SESSION[PREFIXE]['contexte_module']=MODULE_DEFAUT;
      $_SESSION[PREFIXE]['contexte_action']=VOIR;
      $_SESSION[PREFIXE]['action']=VOIR;
      unset ($_SESSION[PREFIXE]['add_change_eval']);
      $_SESSION[PREFIXE]['post_reussi']=TRUE;
      charge_message("Enregistrement de l'�valuation r�ussie");
      header("Location: index.php");
      die ();

    } else {
      charge_message("Echec de l'enregistrement de l'�valuation");	   
    }
  }
  
  // On n'a pas enregistr�
  if (isset ($_SESSION[PREFIXE]['add_change_eval'])) {
    // si on a des donn�es en $session on les r�cup�re
    $affiche_eval = $_SESSION[PREFIXE]['add_change_eval'];
    
  } else {
    // Sinon, on v�rifie si on veut modifier une �valuation
    $id_devoir = isset ($_POST['id_devoir']) ? $_POST['id_devoir'] : (isset ($_GET['id_devoir']) ? $_GET['id_devoir'] : NULL);
    if (!empty ($id_devoir)) {
    // si on passe un id, on r�cup�re l'�valuation
      $affiche_eval = recharge_id($id_devoir);
    } else {
    // Sinon on initialise avec des donn�es par d�faut
      $affiche_eval = recharge_defaut();
    }
  }
    
/** 
 * Chargement de la vue de la page
 *
 */  
  include CHEMIN_VUE."/".AJOUTE.'.php';

?>
