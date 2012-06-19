<?php
/** Modele du module évaluations : action supprimer
 * 
 * Supprimer un conteneur ou une évaluation
 * 
 * @author Régis Bouguin
 * @package arborescence
 * @subpackage supprime
 * 
 */

/** Détermine si on peut supprimer un conteneur
 * 
 * Vérifie que le conteneur est vide et que l'utilisateur est bien enseignant du groupe avec
 * les fonctions est_enseignant() et est_dans_groupe()
 * 
 * @param int L'id du conteneur
 * @return bool TRUE si on peut supprimer le conteneur, FALSE sinon
 * @see est_enseignant()
 * @see est_dans_groupe()
 */
function peut_supprimer_conteneur($conteneur) {

  // Il n'y a pas de sous-conteneur ?
  $sql = "SELECT 1=1 FROM `cn_conteneurs` WHERE `parent` = '".$conteneur."'";
  $test_sous_conteneur=mysql_query($sql);
  if(mysql_num_rows($test_sous_conteneur)!=0) {
    return FALSE ;
  }
  // Il n'y a pas d'évaluation ?
  $sql = "SELECT 1=1 FROM `cn_devoirs` WHERE `id_conteneur` = '".$conteneur."'";
  $test_evaluation=mysql_query($sql);
  if(mysql_num_rows($test_evaluation)!=0) {
    return FALSE ;
  }
  
  if (!est_enseignant($_SESSION['login'])) {
    return FALSE ;
  }
  
  if (!est_dans_groupe($conteneur, $_SESSION['login'])) {
    return FALSE ;
  }
  
   return TRUE ;
    
}

/** Récupère les données d'un conteneur
 *  
 * Récupère les données d'un conteneur dans cn_conteneurs + 
 * l'appelation choisie pour les boites
 * 
 * @param int L'id du conteneur
 * @return object $retour Les données enregistrées dans les tables, FALSE sinon
 * @see getSettingValue()
 */
function charge_module($conteneur) {
  $sql = "SELECT * FROM `cn_conteneurs`
            WHERE `id` = '".$conteneur."'
	    ";
  $result=mysql_query($sql);
  if(mysql_num_rows($result)==0) {
    return FALSE ;
  }
  
  if(mysql_num_rows($result)>1) {
    echo 'on ne devrait jamais avoir 2 conteneurs avec le même ID';
    die ();
  }
  
  while ($row = mysql_fetch_object($result)) {
     $retour = $row ;
     $retour->type = getSettingValue('gepi_denom_boite') ;
  }
  
  return $retour ;
  
}

/** Supprimer un conteneur à partir de son Id
 * 
 * @param int L'id du conteneur
 * @return bool TRUE si le conteneur a été supprimé de la table, FALSE sinon
 * 
 */
function supprime_conteneur($conteneur) {
  $sql = "DELETE FROM `cn_conteneurs`
            WHERE `id` = '".$conteneur."'
	    ";
  $result=mysql_query($sql);
  if($result) {
    return TRUE;
  }
  return FALSE ;
}

/** Vérifie qu'un utilisateur est enseignant
 * 
 * @param int Le login de l'utilisateur
 * @return bool TRUE si l'utilisateur est enseignant, FALSE sinon
 * 
 */
function est_enseignant($enseignant) {
  // l'utilisateur est enseignant ?
  $sql = "SELECT 1=1 FROM `utilisateurs` 
            WHERE `login` = '".$enseignant."'
	      AND `statut` = 'professeur'";
  $test_enseignant=mysql_query($sql);
  if(mysql_num_rows($test_enseignant)==0) {
    return FALSE ;
  } 
  return TRUE;
}
 
/** Détermine si un enseignant fait parti du groupe
 * 
 * @param int L'id du conteneur
 * @param text Le login de l'enseignant
 * @return bool TRUE si l'enseignant est dans le groupe, FALSE sinon
 * 
 */
function est_dans_groupe($conteneur,$enseignant) {
  // l'utilisateur fait parti du groupe de l'évaluation ?
  $sql = "SELECT 1=1 FROM `cn_conteneurs` cn, `cn_cahier_notes` no, `j_groupes_professeurs` po
            WHERE cn.id = '".$conteneur."'
	      AND cn.id_racine = no.id_cahier_notes
	      AND po.id_groupe = no.id_groupe
	      AND po.login = '".$enseignant."'
	    ";
  $test_groupe=mysql_query($sql);
  if(mysql_num_rows($test_groupe)==0) {
    return FALSE ;
  }
  return TRUE;
}

/** Renvoie les données d'une évaluation
 * 
 * Renvoie un objet contenant les données d'une évaluation contenue cn_devoirs
 * dans  + un champ 'type' à Évaluation
 * 
 * @param int L'id de l'évaluation
 * @return object $retour Les données enregistrées dans cn_devoirs, FALSE sinon
 *  
 */
function charge_evaluation($evaluation)  {
  $sql = "SELECT * FROM cn_devoirs
            WHERE id ='".$evaluation."'
	    ";
  $result=mysql_query($sql);
  if(mysql_num_rows($result)==0) {
    return FALSE ;
  }
  if(mysql_num_rows($result)>1) {
    echo 'on ne devrait jamais avoir 2 évaluations avec le même ID';
    die ();
  }
  while ($row = mysql_fetch_object($result)) {
     $retour = $row ;
     $retour->type = 'Évaluation' ;
  }
  return $retour ;
 }

/** Détermine si un enseignant peut supprimer une évaluation
 * 
 * Récupère les données de l"évaluation avec charge_evaluation() et
 * vérifie que l'enseignant fait pati du bon groupe avec est_dans_groupe()
 * 
 * @param int L'id de l'évaluation
 * @return bool TRUE si l'enseignant peut supprimer l'évaluation, FALSE sinon
 * @see charge_evaluation()
 * @see est_dans_groupe()
 * 
 */
function peut_supprimer_evaluation($evaluation) {
  $donnees_evaluation = charge_evaluation($evaluation) ;
  if (est_dans_groupe($donnees_evaluation->id_conteneur,$_SESSION['login'])) {
    return TRUE;
  }
  return FALSE; 
}

/** Détermine si une évaluation contient des notes
 * 
 * @param int L'id de l'évaluation
 * @return bool TRUE si l'évaluation n'a pas de note, FALSE sinon
 * 
 */
function evaluation_vide($evaluation) {
  $sql="SELECT 1=1 FROM cn_notes_devoirs 
	  WHERE id_devoir='".$evaluation."'
	    AND note != ''";
  $result=mysql_query($sql);
  if(mysql_num_rows($result) != 0) {
    mysql_free_result($result);
    return FALSE;
  }
  mysql_free_result($result);
  return TRUE; 
}

/** Supprime une évaluation de la table cn_devoirs
 * 
 * Supprime l'évaluation de la table cn_devoirs 
 * et les enregistrement éventuels dans cn_notes_devoirs
 * 
 * @param int L'id de l'évaluation
 * @return bool TRUE si l'évaluation n'a pas de note, FALSE sinon
 * 
 */
function supprime_evaluation($evaluation) {
  $sql = "DELETE FROM `cn_devoirs`
            WHERE `id` = '".$evaluation."'
	    ";
  $result=mysql_query($sql);
  if($result) {
    // on supprime les enregistrements dans la table des notes
    $sql="DELETE FROM cn_notes_devoirs WHERE id_devoir='".$evaluation."'";
    $result=mysql_query($sql);
    return TRUE;
  }
  return FALSE ;
}
 
/** Vérifie que les évaluations sont valides
 * 
 * Vérifie que les évaluations stockées dans $_SESSION[PREFIXE]['id_devoir']
 * existent bien dans la table cn_devoirs
 * 
 * Supprime au besoin les évaluations inconnues dans $_SESSION[PREFIXE]['id_devoir']
 * 
 */ 
function verifie_eval_visibles() {
  $table_valide=array();
  foreach ($_SESSION[PREFIXE]['id_devoir'] as $devoir) {
    $sql = "SELECT 1=1 FROM cn_devoirs
	      WHERE id ='".$devoir."'
	      ";
    $result=mysql_query($sql);
    if(mysql_num_rows($result)==1) {
      $table_valide[] = $devoir;
    }   
  }
  unset ($_SESSION[PREFIXE]['id_devoir']);
  $_SESSION[PREFIXE]['id_devoir'] = $table_valide;
}
  
?>
