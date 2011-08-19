<?php
/** Modele du module evaluations : action matiere
 * 
 * Création de sous-conteneurs
 * 
 * @author Régis Bouguin
 * @package arborescence
 * @subpackage matiere
 * 
 */

/** Enregistre les données du conteneur dans la base
 * 
 * @return bool TRUE si l'enregistrement s'est bien passé, FALSE + un message sinon
 * @see traitement_magic_quotes()
 * @see getSettingValue()
 * @see charge_message()
 * 
 */
function enregisteConteneur() {
  // On vide les messages
  unset ($_SESSION[PREFIXE]['tbs_msg']);
  
  // On récupère en session les données
  $donnees_passees = array();
  $donnees_passees['nomCourt']  = !empty ($_POST['nom']) ? traitement_magic_quotes($_POST['nom']) : (!empty ($_SESSION[PREFIXE]['nomCourt']) ? $_SESSION[PREFIXE]['nomCourt'] : getSettingValue('gepi_denom_boite'));
  $donnees_passees['nomComplet'] = !empty ($_POST['nomComplet']) ? $_POST['nomComplet'] : (!empty ($_SESSION[PREFIXE]['nomComplet']) ? $_SESSION[PREFIXE]['nomComplet'] : "");
  $donnees_passees['emplacement'] = !empty ($_POST['emplacement']) ? $_POST['emplacement'] : (!empty ( $_SESSION[PREFIXE]['emplacement']) ? $_SESSION[PREFIXE]['emplacement'] : "");
  $donnees_passees['coefficient'] = !empty ($_POST['coefCont']) ? $_POST['coefCont'] : (!empty ( $_SESSION[PREFIXE]['coefficient']) ? $_SESSION[PREFIXE]['coefficient'] : "");
  $donnees_passees['arrondir'] =    !empty ($_POST['arrondi']) ? $_POST['arrondi'] : (!empty ( $_SESSION[PREFIXE]['arrondir']) ? $_SESSION[PREFIXE]['arrondir'] : "");
  $donnees_passees['mode_calcul'] = !empty ($_POST['mode_calcul']) ? $_POST['mode_calcul'] : (!empty ( $_SESSION[PREFIXE]['mode_calcul']) ? $_SESSION[PREFIXE]['mode_calcul'] : 2);
  $donnees_passees['id_racine'] = !empty ( $_SESSION[PREFIXE]['id_racine']) ? $_SESSION[PREFIXE]['id_racine'] : "";
  
  if (!empty ($_POST['description'])) {
    $donnees_passees['description'] = $_POST['description'];
  } else {
    $donnees_passees['description'] = "";
  }

  if (!empty ($_POST['ponderation'])) {
    $donnees_passees['ponderation'] = $_POST['ponderation'];
  } else {
    $donnees_passees['ponderation'] = 0;
  }

  if (!empty ($_POST['noteSurReleve'])) {
    $donnees_passees['noteSurReleve'] = $_POST['noteSurReleve'];
  } else {
    $donnees_passees['noteSurReleve'] = 0;
  }

  if (!empty ($_POST['noteSurBulletin'])) {
    $donnees_passees['noteSurBulletin'] = $_POST['noteSurBulletin'];   
  } else {
    $donnees_passees['noteSurBulletin'] = 0;
  }
  
  if(isset ($_POST['id_conteneur'])) {
    $donnees_passees['id_conteneur'] = $_POST['id_conteneur'];    
    // Si on modifie le parent, il faut remplir 
    // $_POST['emplacement'], $donnees_passees['emplacement'] à 0
    if ($_POST['id_conteneur'] == $donnees_passees['id_racine']) {
      $donnees_passees['emplacement'] = "0";
      $_POST['emplacement'] = "racine";
      echo 'coucou';
    }
  } else {
    $donnees_passees['id_conteneur'] = NULL;
  }

  $_SESSION[PREFIXE]['add_change_conteneur'] = $donnees_passees;
    
  // On teste si les champs obligatoires sont bien remplis  
  
  if (!empty ($_POST['nom']) 
      && !empty ($_POST['nomComplet']) 
      && (!empty ($_POST['emplacement']))
      && !empty ($_POST['coefCont'])
      && !empty ($_POST['arrondi'])
      && !empty ($_POST['mode'])) {
  
    if($donnees_passees['id_conteneur']) {
      // on a un id, on est en train de modifier un conteneur
      $sql="UPDATE `cn_conteneurs`
	      SET `id_racine` = '".$donnees_passees['id_racine']."',
	          `nom_court` = '".$donnees_passees['nomCourt']."',
	          `nom_complet` = '".html_entity_decode($donnees_passees['nomComplet'])."',
	          `description` = '".$donnees_passees['description']."',
	          `mode` = '".$donnees_passees['mode_calcul']."',
	          `coef` = '".$donnees_passees['coefficient']."',
	          `arrondir` = '".$donnees_passees['arrondir']."',
	          `ponderation` = '".$donnees_passees['ponderation']."',
	          `display_parents` = '".$donnees_passees['noteSurReleve']."',
	          `display_bulletin` = '".$donnees_passees['noteSurBulletin']."',
	          `parent` = '".$donnees_passees['emplacement']."'
	      WHERE id = '".$donnees_passees['id_conteneur']."'";
    } else {
      // on n'a pas d'id, on est en train de créer un conteneur
      $sql="INSERT INTO `cn_conteneurs` 
	      (id_racine,
	          nom_court,
	          nom_complet,
	          description,
	          mode,
	          coef,
	          arrondir,
	          ponderation,
	          display_parents,
	          display_bulletin,
	          parent)
	VALUES ('".$donnees_passees['id_racine']."',
	        '".$donnees_passees['nomCourt']."',
	        '".$donnees_passees['nomComplet']."',
	        '".$donnees_passees['description']."',
	        '".$donnees_passees['mode_calcul']."',
	        '".$donnees_passees['coefficient']."',
	        '".$donnees_passees['arrondir']."',
	        '".$donnees_passees['ponderation']."',
	        '".$donnees_passees['noteSurReleve']."',
	        '".$donnees_passees['noteSurBulletin']."',
	        '".$donnees_passees['emplacement']."')";
    }
    //echo $sql.'<br />';
    if (mysql_query($sql)) {
      return TRUE;
    } else {
      charge_message("Échec lors de l'enregistrement dans la base<br />".$sql);
      return FALSE;
    }
    
  }
  
  charge_message("Des champs obligatoires n'ont pas été renseignés");
  return FALSE;
}

/** Renvoie dans un tableau, les données de la base pour un conteneur à partir de son Id
 * 
 * id_conteneur = cn_conteneurs.id,
 * id_racine = cn_conteneurs.id_racine,
 * nom_court = cn_conteneurs.nom_court,
 * nom_complet = cn_conteneurs.nom_complet,
 * description = cn_conteneurs.description,
 * mode_calcul = cn_conteneurs.mode,
 * coefficient = cn_conteneurs.coef,
 * arrondir = cn_conteneurs.arrondir,
 * ponderation = cn_conteneurs.ponderation,
 * noteSurReleve = cn_conteneurs.display_parents,
 * noteSurBulletin = cn_conteneurs.display_bulletin,
 * emplacement'] = cn_conteneurs.parent
 * 
 * @param int $id_conteneur L'id du conteneur
 * @return tableau mixed Les données du conteneur enregistrées dans la base
 * 
 */
function recharge_conteneur($id_conteneur){
  
  $conteneur = array ();
  // On récupère les données du conteneur
  $sql="SELECT * FROM `cn_conteneurs`
          WHERE `id` ='".$id_conteneur."' ";
  $result = mysql_query($sql);
  if ($result) {
    $donnee_recu = mysql_fetch_array($result);
    $conteneur['id_conteneur'] = $donnee_recu['id'];
    $conteneur['id_racine'] = $donnee_recu['id_racine'];
    $conteneur['nomCourt'] = $donnee_recu['nom_court'];
    $conteneur['nomComplet'] = $donnee_recu['nom_complet'];
    $conteneur['description'] = $donnee_recu['description'];
    $conteneur['mode_calcul'] = $donnee_recu['mode'];
    $conteneur['coefficient'] = $donnee_recu['coef'];
    $conteneur['arrondir'] = $donnee_recu['arrondir'];
    $conteneur['ponderation'] = $donnee_recu['ponderation'];
    $conteneur['noteSurReleve'] = $donnee_recu['display_parents'];
    $conteneur['noteSurBulletin'] = $donnee_recu['display_bulletin'];
    $conteneur['emplacement'] = $donnee_recu['parent']; 
  } else {
    mysql_free_result($result); 
    charge_message("Échec lors de la récupération dans la base");
    return FALSE;
  }
  
  mysql_free_result($result); 
  return $conteneur;
}

/** Renvoie dans un tableau, les données pour un nouveau conteneur
 * 
 * @return tableau  mixed Des données par défaut pour un nouveau conteneur
 * 
 */
function recharge_conteneur_defaut(){
  
  $conteneur = array ();
  
  $conteneur['id_conteneur'] = NULL;
  $conteneur['nomCourt'] = getSettingValue('gepi_denom_boite');
  $conteneur['nomComplet'] = "nom long";
  $conteneur['emplacement'] = "";
  $conteneur['description'] = "";
  $conteneur['coefficient'] = 1;
  $conteneur['arrondir'] = DIXIEME_SUP;
  $conteneur['ponderation'] = 0;
  $conteneur['noteSurReleve'] = 1;
  $conteneur['noteSurBulletin'] = 0;
  $conteneur['mode_calcul'] = MODE_DEFAUT_CONTENEUR;
  
  return $conteneur;
}

?>
