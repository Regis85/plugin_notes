<?php

/** Controleur du module evaluations : action modifier
 * 
 * Modifier le trimestre ou le groupe actif
 * 
 * @author Régis Bouguin
 * @package arborescence
 * @subpackage modifier
 * @see regle_trimestre()
 * @see regle_groupe()
 * 
 */
 
 
  
/**
 * On a des erreurs d'affectation, on appelle la page 
 */
if (isset($_POST['signaler']) && $_POST['signaler']=='affectation') {
	/** 
		echo $module.$action.'.php';
		echo "<br /><br />";
		echo "../../groupes/signalement_eleves.php?id_groupe=".$_SESSION['plugin_notes']['id_groupe_session']."&chemin_retour=../mod_plugins/plugin_notes/index.php";
		echo "<br /><br />";
		$affiche_debug=debug_var();
		$affiche_debug=debug_var();
	die();
	*/
 
	$groupe_session = $_SESSION['plugin_notes']['id_groupe_session'];
	$periode_num = $_SESSION['plugin_notes']['periode_num'];
	unset ($_SESSION['plugin_notes']);
	$_SESSION['plugin_notes']['id_groupe_session'] = $groupe_session;
	$_SESSION['plugin_notes']['periode_num'] = $periode_num;
	//header("Location: ../../groupes/signalement_eleves.php?id_groupe=".$groupe_session."&chemin_retour=../accueil.php");
	header("Location: ../../groupes/signalement_eleves.php?id_groupe=".$groupe_session."&chemin_retour=../mod_plugins/plugin_notes/index.php");
	die();
 
}

 
/** 
 * Chargement du modele de la page
 *
 */
include CHEMIN_MODELE."/".MODIFIE.'.php';
   
  die();
$trimestre = regle_trimestre($_POST, $_GET);

$groupe = regle_groupe($_POST, $_GET);

// Import/Export
if (!empty($_POST[IMPORT])) {
  $idRacine = $_SESSION[PREFIXE]['id_racine'];
  if (IMPORTER==$_POST[IMPORT]) {    
    header("Location: ".CHEMIN_RACINE."cahier_notes/import_cahier_notes.php?id_racine=".$idRacine);
    die;
  } else {
    header("Location: ".CHEMIN_RACINE."cahier_notes/export_cahier_notes.php?id_racine=".$idRacine);
    die;
  }
}

// Signaler
if (!empty($_POST[SIGNALER])) {
  
  $_SESSION[PREFIXE]['chemin_retour'] = "../mod_plugins/plugin_notes/index.php?id_groupe=".$_SESSION[PREFIXE]['id_groupe_session'];
  header("Location: ".CHEMIN_RACINE."groupes/signalement_eleves.php?id_groupe=".$_SESSION[PREFIXE]['id_groupe_session']);
  
  die;
}

//==================================
// Décommenter les 2 lignes ci-dessous pour afficher les variables $_GET, $_POST, $_SESSION[PREFIXE] et $_SERVER pour DEBUG:
// $affiche_debug=debug_var();
// die;

header("Location: index.php");

?>
