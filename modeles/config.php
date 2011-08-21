<?php
/** Constantes du plugin notes multiples
 * 
 * Initialisation des constantes et du module à utiliser
 * 
 * @author Régis Bouguin
 * @package global
 * @donnees $_SESSION['contexte_module'] Initialisation du module à utiliser
 *
 */

/** 
 */
define('CHEMIN_VUE_GLOBALE',       'vues/');
define('CHEMIN_MODELE_GLOBAL',     'modeles/');
define('CHEMIN_CONTROLEUR_GLOBAL', 'controleurs/');
define('CHEMIN_LIB',               'libs/');

define('PREFIXE',                  'plugin_notes');

// Constantes des actions possibles
define('MODULE_DEFAUT', 'evaluations');
define('EVALUATIONS',   'evaluations');
define('CARNET_NOTES',  'carnet_notes');
define('VOIR',		"voir");
define('ENREGISTRE',    "enregistre");
define('NOUVEAU',       "nouveau");
define('MODIFIE',       "modifie");
define('SUPPRIME',      "supprime");
define('SUPPRIMER',      "supprimer");
define('AJOUTE',        "ajoute");
define('ANNULE',        "annule");
define('ANNULER',        "annuler");
define('CACHER',        "cacher");
define('ABANDONNER',    "Abandonner");
define('ENREGISTRER',   "Enregistrer");
define('VERIFIER',      "Vérifier");

if (!empty($_GET['module'])) {
  if (is_dir(CHEMIN_CONTROLEUR_GLOBAL.$_GET['module'])) {
  // Module specifié ? On le récupère !
    $_SESSION[PREFIXE]['contexte_module']=$_GET['module'];     
  } else {
    $_SESSION[PREFIXE]['contexte_module']=MODULE_DEFAUT;
    //unset ($_GET['module']);
  }

} else {
  if (empty($_SESSION[PREFIXE]['contexte_module'])) {
    $_SESSION[PREFIXE]['contexte_module'] = MODULE_DEFAUT;
    $_SESSION[PREFIXE]['contexte_action'] = VOIR;
  }
  
}

$module = $_SESSION[PREFIXE]['contexte_module'];

define('CHEMIN_VUE',        'vues/'.$module.'/');
define('CHEMIN_MODELE',     'modeles/'.$module.'/');
define('CHEMIN_CONTROLEUR', 'controleurs/'.$module.'/');

// Chemins dossiers spécifiques
define('CHEMIN_RACINE',  '../../');
define('CHEMIN_CAHIER_NOTES',  '../../cahier_notes/');
define('CHEMIN_IMAGES',   CHEMIN_RACINE.'images/');
define('CHEMIN_PLUGIN',   CHEMIN_RACINE.'mod_plugins/');
define('CHEMIN_GESTION',  CHEMIN_RACINE.'gestion/');
define('CHEMIN_GABARITS', CHEMIN_RACINE.'templates/origine/');

// Identification de l'établissement
define('ETABLISSEMENT',  getSettingValue("gepiSchoolName"));
define('RNE',            getSettingValue("gepiSchoolRne"));
define('ANNEE',          getSettingValue("gepiYear"));


// Constantes de la page
define('TITRE_PAGE', "Saisie des notes");
define('GROUPE_INTERDIT', -1);
define('ABSENT', "abs");
define('DISPENSE', "disp");
define('NON_NOTE', "-");
define('VIDE', "v");
define('NOTE', "");

// Affichage des évaluations
define('CREATION',"creation");
define('EVALUATION',"evaluation");
define('CUMUL',"cumul");
define('MATIERE','matiere');
define('IDEM','idem');
define('IMPORT',"import_export");
define('EXPORTER','exporter');
define('IMPORTER','importer');
define('SIGNALER',"signaler");
define('AFFECTATION',"affectation");
define('COLLER',"coller");
define('DUPLIQUE',"duplique");
 
// Actions de la page de saisie des notes
define('VOIR_CARNET','Voir le carnet de notes');
define('RETOUR_EVAL','Retour aux évaluations');
define('FORCE_ENREGISTRE','Enregistrer');

// Arrondi des moyennes
define('DIXIEME_SUP','s1');
define('DEMI_SUP','s5');
define('POINT_SUP','se');
define('DIXIEME_PROCHE','p1');
define('DEMI_PROCHE','p5');
define('POINT_PROCHE','pe');
define('MODE_DEFAUT_CONTENEUR','2');

// Liens externes
define('TOUTES_LES_NOTES', "cahier_notes/toutes_notes.php");
define('SAISIE_MOYENNES', "saisie/saisie_notes.php");
define('SAISIE_APPRECIATION', "saisie/saisie_appreciations.php");
?>
