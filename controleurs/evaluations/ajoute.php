<?php   
/** Controleur du module evaluations : action ajoute
 * 
 * Création d'une évaluation
 * 
 * @author Régis Bouguin
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
  // Décommenter la ligne ci-dessous pour afficher les variables $_GET, $_POST, $_SESSION[PREFIXE] et $_SERVER pour DEBUG:
   $affiche_debug=debug_var();

  // vérifier que le prof peut noter le groupe
  
  
  $mode_creation= isset ($_POST['creation']) ? $_POST['creation'] : (isset ($_GET['creation']) ? $_GET['creation'] : NULL);
  // réorienter vers le bon mode de création/modification
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

  // récupérer les données du groupe
  $classes_groupe=groupe_long($_SESSION[PREFIXE]['id_groupe_session']);

  // récupérer les conteneurs qui peuvent accueillir une évaluation
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
  
  // Vérifier si on veut abandonner 
  if (isset ($_POST['mode']) && $_POST['mode']==ABANDONNER) {
    // on vide les données en mémoire
    unset ($_SESSION[PREFIXE]['add_change_eval']);
    // TODO : gérer le retour
    $_SESSION[PREFIXE]['contexte_action']=VOIR;
    $_SESSION[PREFIXE]['action']=VOIR;
    charge_message("Enregistrement de l'évaluation abandonné");
    header("Location: index.php");
    die ();
    
  }
  // Vérifier si on veut enregistrer 
  if (isset ($_POST['mode']) && $_POST['mode']==ENREGISTRER) {
    check_token();
    // on vide les données en mémoire
    unset ($_SESSION[PREFIXE]['add_change_eval']);
    if(enregisteEval()) {
    // TODO : gérer le retour
      $_SESSION[PREFIXE]['contexte_module']=MODULE_DEFAUT;
      $_SESSION[PREFIXE]['contexte_action']=VOIR;
      $_SESSION[PREFIXE]['action']=VOIR;
      unset ($_SESSION[PREFIXE]['add_change_eval']);
      $_SESSION[PREFIXE]['post_reussi']=TRUE;
      charge_message("Enregistrement de l'évaluation réussie");
      header("Location: index.php");
      die ();

    } else {
      charge_message("Echec de l'enregistrement de l'évaluation");	   
    }
  }
  
  // On n'a pas enregistré
  if (isset ($_SESSION[PREFIXE]['add_change_eval'])) {
    // si on a des données en $session on les récupère
    $affiche_eval = $_SESSION[PREFIXE]['add_change_eval'];
    
  } else {
    // Sinon, on vérifie si on veut modifier une évaluation
    $id_devoir = isset ($_POST['id_devoir']) ? $_POST['id_devoir'] : (isset ($_GET['id_devoir']) ? $_GET['id_devoir'] : NULL);
    if (!empty ($id_devoir)) {
    // si on passe un id, on récupère l'évaluation
      $affiche_eval = recharge_id($id_devoir);
    } else {
    // Sinon on initialise avec des données par défaut
      $affiche_eval = recharge_defaut();
    }
  }
    
/** 
 * Chargement de la vue de la page
 *
 */  
  include CHEMIN_VUE."/".AJOUTE.'.php';

?>
