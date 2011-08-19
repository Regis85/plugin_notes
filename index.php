<?php
/** FrontEnd du plugin notes multiples
 * 
 * Gestion de l'arborescence du carnet de notes
 * 
 * Saisie des notes et des commentaires de 1 ou plusieurs �valuations
 *  
 * @author R�gis Bouguin
 * @version 0.2.2
 * @package global
 * 
 * @todo V�rifier les pages pour remplacer htmlentities par traitement_magic_quotes()
 * @todo Faire un r�adressage d'adresse dans tous les r�pertoires
 * 
 */
 
$niveau_arbo = "2";
 
/** 
 * Initialisation de Propel
 *
 */
include("../../lib/initialisationsPropel.inc.php");
/** 
 * Initialisation de la Session
 *
 */
include("../../lib/initialisations.inc.php");

// Resume session
$resultat_session = $session_gepi->security_check();
if ($resultat_session == 'c') {
    header("Location: ../../utilisateurs/mon_compte.php?change_mdp=yes");
    die();
} else if ($resultat_session == '0') {
    header("Location: ../../logout.php?auto=1");
    die();
}
/** 
 * V�rification des droits
 *
 */
include("../plugins.class.php");

// Il faut adapter cette ligne au statut des utilisateurs qui auront acc�s � cette page, par d�faut des utilisateurs professionnels
$utilisateur = UtilisateurProfessionnelPeer::retrieveByPk($_SESSION['login']);
$user_auth = new gepiPlugIn("plugin_notes");
$user_auth->verifDroits();


/****************************************************************
			FIN HAUT DE PAGE
****************************************************************/

if (getSettingValue("active_carnets_notes")!='y') {
    die("Le module n'est pas activ�.");
} else {

/** 
 * Initialisation de la page
 *
 */
  include 'modeles/init.php';

  // D�but de la tamporisation de sortie
  ob_start();
  
// TODO : g�rer l'acc�s en admin et scolarit� -> ils se connectent pourquoi ?
  
  /* */

  $module = dirname(__FILE__).'/controleurs/'.$_SESSION[PREFIXE]['contexte_module'].'/';
  
  // Si l'action existe, on l'ex�cute
  if (is_file($module.$action.'.php')) {
    /** 
     * Chargement de la page
     * 
     *  @donnees $_SESSION[PREFIXE]['contexte_module'] le module utilis� (arborescence, saisie de notes...)
     * 
     *  @donnees $_SESSION[PREFIXE]['contexte_action'] l'action � effectuer (voir, supprimer ...)
     */
    include $module.$action.'.php';

  } else {

    /** 
     * Par d�faut, chargement de la page de vision
     *
     */
    include $module.VOIR.'.php';

  }
  
  if (!empty ($_SESSION[PREFIXE]["post_reussi"])) {
    $post_reussi = $_SESSION[PREFIXE]["post_reussi"];
    unset ($_SESSION[PREFIXE]["post_reussi"]);
  }
  
  // Fin de la tamporisation de sortie
  $contenu = ob_get_clean();

  // D�but du code HTML
  
/** 
 * D�claration du DOCTYPE de la page
 *
 */
  include CHEMIN_VUE_GLOBALE.'haut.php';
/** 
 * Cr�ation du Header de la page
 *
 */
  include CHEMIN_GABARITS.'header_template.php';
/** 
 * Affichage des style sp�cifiques
 *
 */  
  include CHEMIN_VUE_GLOBALE.'fin_header.php';
  
  // On affiche les messages �ventuels
  if (isset ($_SESSION[PREFIXE]['tbs_msg'])) {
    $tbs_msg=$_SESSION[PREFIXE]['tbs_msg'];
    unset ($_SESSION[PREFIXE]['tbs_msg']);
  }

/** 
 * Affichage du bandeau
 *
 */  
  include CHEMIN_GABARITS.'bandeau_template.php';
  
  echo $contenu;
  
/** 
 * Affichage du pied de page
 *
 */  
  include CHEMIN_VUE_GLOBALE.'bas.php';

}
?>
