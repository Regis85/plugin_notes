<?php
/** Initialisation du plugin notes multiples
 * 
 * Initialise les constantes et les fonctions
 * 
 * Charge le fichier CSS spécifique
 * 
 * Charge le fichier JS spécifique
 * 
 * Initialise la dernière connexion avec last_connection()
 * 
 * Initialise le fil d'Arianne avec suivi_ariane()
 * 
 * Renseigne $_SESSION[PREFIXE]['contexte_action'] avec l'action à effectuer si elle est passée 
 * 
 * en $_POST ou $_GET 
 * 
 * Initialise l'action à effectuer
 * 
 * @author Régis Bouguin
 * @package global
 * @see last_connection()
 * @see suivi_ariane()
 * @donnees $_SESSION[PREFIXE]['contexte_action'] Initialisation de l'action à utiliser
 * @todo Vérifier ce qui se passe quand on passe d'un classe à 3 périodes à une classe à 2 périodes
 * @todo si on enregistre une note après avoir ouvert une autre fenêtre sur une autre classe,
 * les notes s'enregistre bien mais on part sur l'autre classe, ce serait bien de pouvoir 
 * saisir des notes pour plusieurs classes sans perdre le fil
 */

/** 
 * Inclusion du fichier de constantes
 */
include_once 'modeles/config.php';

// Au premièr passage, on vérifie si on a des données en $_SESSION
// et on récupère au besoin $_SESSION['id_groupe_session']
if(!isset ($_SESSION[PREFIXE]['id_groupe_session']) && isset ($_SESSION['id_groupe_session'])) {
  $_SESSION[PREFIXE]['id_groupe_session']=$_SESSION['id_groupe_session'];
} else if ((isset ($_SESSION['id_groupe_session'])) && ($_SESSION[PREFIXE]['id_groupe_session'] != $_SESSION['id_groupe_session'])) {
  $_SESSION['id_groupe_session']=$_SESSION[PREFIXE]['id_groupe_session'];
}



/** 
 * Inclusion du fichier de fonctions
 */
include_once 'modeles/global.php';

// fichier CSS général
$tbs_CSS[]=array("rel" => 'stylesheet',
                 "type" => 'text/css',
                 "fichier" => 'vues/global.css',
                 "media" => 'screen');
$tbs_librairies[]='libs/global.js';

$titre_page = 'Saisie des notes';

/** 
 * Inclusion du fichier de création du header
 */
include CHEMIN_RACINE.'lib/header_template.inc';
$tbs_CSS[]=array("fichier"=>CHEMIN_RACINE."templates/origine/css/bandeau.css"  , "rel"=>"stylesheet" , "type"=>"text/css" , "media"=>"screen" , "title"=>"");

//=== Dernière connexion ===
$tbs_last_connection="";
if (isset($affiche_connexion)) {
	$tbs_last_connection=last_connection();
}

//=== D'autres données à récupérer ===
$tbs_microtime="";  // TODO : à récupérer
$tbs_pmv="";	    // TODO : à récupérer

if (!suivi_ariane($_SERVER['PHP_SELF'],$titre_page)) 
	echo "erreur lors de la création du fil d'ariane";


  // Si l'action est specifiée, on l'utilise, sinon, on tente une action par défaut

if (isset($_POST['action']) || isset($_GET['action'])) {
  $_SESSION[PREFIXE]['contexte_action'] = !empty($_POST['action']) ? $_POST['action'] : ((!empty($_GET['action'])) ? $_GET['action'] : VOIR) ;
} else {
  if(empty($_SESSION[PREFIXE]['contexte_action'])) {
    $_SESSION[PREFIXE]['contexte_action'] = VOIR;
  }
}
  

$action = $_SESSION[PREFIXE]['contexte_action'];

?>
