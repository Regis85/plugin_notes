<?php
/** Initialisation du plugin notes multiples
 * 
 * Initialise les constantes et les fonctions
 * 
 * Charge le fichier CSS sp�cifique
 * 
 * Charge le fichier JS sp�cifique
 * 
 * Initialise la derni�re connexion avec last_connection()
 * 
 * Initialise le fil d'Arianne avec suivi_ariane()
 * 
 * Renseigne $_SESSION[PREFIXE]['contexte_action'] avec l'action � effectuer si elle est pass�e 
 * 
 * en $_POST ou $_GET 
 * 
 * Initialise l'action � effectuer
 * 
 * @author R�gis Bouguin
 * @package global
 * @see last_connection()
 * @see suivi_ariane()
 * @donnees $_SESSION[PREFIXE]['contexte_action'] Initialisation de l'action � utiliser
 * @todo V�rifier ce qui se passe quand on passe d'un classe � 3 p�riodes � une classe � 2 p�riodes
 * @todo si on enregistre une note apr�s avoir ouvert une autre fen�tre sur une autre classe,
 * les notes s'enregistre bien mais on part sur l'autre classe, ce serait bien de pouvoir 
 * saisir des notes pour plusieurs classes sans perdre le fil
 */

/** 
 * Inclusion du fichier de constantes
 */
include_once 'modeles/config.php';

// Au premi�r passage, on v�rifie si on a des donn�es en $_SESSION
// et on r�cup�re au besoin $_SESSION['id_groupe_session']
if(!isset ($_SESSION[PREFIXE]['id_groupe_session']) && isset ($_SESSION['id_groupe_session'])) {
  $_SESSION[PREFIXE]['id_groupe_session']=$_SESSION['id_groupe_session'];
} else if ((isset ($_SESSION['id_groupe_session'])) && ($_SESSION[PREFIXE]['id_groupe_session'] != $_SESSION['id_groupe_session'])) {
  $_SESSION['id_groupe_session']=$_SESSION[PREFIXE]['id_groupe_session'];
}



/** 
 * Inclusion du fichier de fonctions
 */
include_once 'modeles/global.php';

// fichier CSS g�n�ral
$tbs_CSS[]=array("rel" => 'stylesheet',
                 "type" => 'text/css',
                 "fichier" => 'vues/global.css',
                 "media" => 'screen');
$tbs_librairies[]='libs/global.js';

$titre_page = 'Saisie des notes';

/** 
 * Inclusion du fichier de cr�ation du header
 */
include CHEMIN_RACINE.'lib/header_template.inc';
$tbs_CSS[]=array("fichier"=>CHEMIN_RACINE."templates/origine/css/bandeau.css"  , "rel"=>"stylesheet" , "type"=>"text/css" , "media"=>"screen" , "title"=>"");

//=== Derni�re connexion ===
$tbs_last_connection="";
if (isset($affiche_connexion)) {
	$tbs_last_connection=last_connection();
}

//=== D'autres donn�es � r�cup�rer ===
$tbs_microtime="";  // TODO : � r�cup�rer
$tbs_pmv="";	    // TODO : � r�cup�rer

if (!suivi_ariane($_SERVER['PHP_SELF'],$titre_page)) 
	echo "erreur lors de la cr�ation du fil d'ariane";


  // Si l'action est specifi�e, on l'utilise, sinon, on tente une action par d�faut

if (isset($_POST['action']) || isset($_GET['action'])) {
  $_SESSION[PREFIXE]['contexte_action'] = !empty($_POST['action']) ? $_POST['action'] : ((!empty($_GET['action'])) ? $_GET['action'] : VOIR) ;
} else {
  if(empty($_SESSION[PREFIXE]['contexte_action'])) {
    $_SESSION[PREFIXE]['contexte_action'] = VOIR;
  }
}
  

$action = $_SESSION[PREFIXE]['contexte_action'];

?>
