<?php
/** Modele du module �valuations : action supprimer
 * 
 * Supprimer un conteneur ou une �valuation
 * 
 * @author R�gis Bouguin
 * @package arborescence
 * @subpackage supprime
 * 
 */

/** D�termine si on peut supprimer un conteneur
 * 
 * V�rifie que le conteneur est vide et que l'utilisateur est bien enseignant du groupe avec
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
  // Il n'y a pas d'�valuation ?
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

/** R�cup�re les donn�es d'un conteneur
 *  
 * R�cup�re les donn�es d'un conteneur dans cn_conteneurs + 
 * l'appelation choisie pour les boites
 * 
 * @param int L'id du conteneur
 * @return object $retour Les donn�es enregistr�es dans les tables, FALSE sinon
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
    echo 'on ne devrait jamais avoir 2 conteneurs avec le m�me ID';
    die ();
  }
  
  while ($row = mysql_fetch_object($result)) {
     $retour = $row ;
     $retour->type = getSettingValue('gepi_denom_boite') ;
  }
  
  return $retour ;
  
}

/** Supprimer un conteneur � partir de son Id
 * 
 * @param int L'id du conteneur
 * @return bool TRUE si le conteneur a �t� supprim� de la table, FALSE sinon
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

/** V�rifie qu'un utilisateur est enseignant
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
 
/** D�termine si un enseignant fait parti du groupe
 * 
 * @param int L'id du conteneur
 * @param text Le login de l'enseignant
 * @return bool TRUE si l'enseignant est dans le groupe, FALSE sinon
 * 
 */
function est_dans_groupe($conteneur,$enseignant) {
  // l'utilisateur fait parti du groupe de l'�valuation ?
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

/** Renvoie les donn�es d'une �valuation
 * 
 * Renvoie un objet contenant les donn�es d'une �valuation contenue cn_devoirs
 * dans  + un champ 'type' � �valuation
 * 
 * @param int L'id de l'�valuation
 * @return object $retour Les donn�es enregistr�es dans cn_devoirs, FALSE sinon
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
    echo 'on ne devrait jamais avoir 2 �valuations avec le m�me ID';
    die ();
  }
  while ($row = mysql_fetch_object($result)) {
     $retour = $row ;
     $retour->type = '�valuation' ;
  }
  return $retour ;
 }

/** D�termine si un enseignant peut supprimer une �valuation
 * 
 * R�cup�re les donn�es de l"�valuation avec charge_evaluation() et
 * v�rifie que l'enseignant fait pati du bon groupe avec est_dans_groupe()
 * 
 * @param int L'id de l'�valuation
 * @return bool TRUE si l'enseignant peut supprimer l'�valuation, FALSE sinon
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

/** D�termine si une �valuation contient des notes
 * 
 * @param int L'id de l'�valuation
 * @return bool TRUE si l'�valuation n'a pas de note, FALSE sinon
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

/** Supprime une �valuation de la table cn_devoirs
 * 
 * Supprime l'�valuation de la table cn_devoirs 
 * et les enregistrement �ventuels dans cn_notes_devoirs
 * 
 * @param int L'id de l'�valuation
 * @return bool TRUE si l'�valuation n'a pas de note, FALSE sinon
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
 
/** V�rifie que les �valuations sont valides
 * 
 * V�rifie que les �valuations stock�es dans $_SESSION[PREFIXE]['id_devoir']
 * existent bien dans la table cn_devoirs
 * 
 * Supprime au besoin les �valuations inconnues dans $_SESSION[PREFIXE]['id_devoir']
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
