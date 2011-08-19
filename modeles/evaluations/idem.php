<?php
/** Modele du module evaluations : action idem
 * 
 * Dupliquer l'organisation d'une période
 * 
 * @author Régis Bouguin
 * @package arborescence
 * @subpackage idem
 * 
 */

/** Renvoie l'organisation des conteneurs passé/présent/futur
 * 
 * Récupère l'organisation de la période précédente, la compare avec celle de la période active
 * et crée une organisation qui combine les deux
 * 
 * @return array|bool Tous les conteneurs de la période et de la période précédente,
 * l'arborescence à venir, FALSE + un message sinon
 */
  function charge_arborescence(){
    $trim_precedent=array();
    $trim_actuel=array();
    $trim_futur=array();
    $arborescences=array();
    
    // on charge l'arborescence de la période précédente
    
    $num_trim=$_SESSION[PREFIXE]['periode_num']-1;
    if (0==$num_trim) {
      // On est en première période
      charge_message("Vous êtes en première période, il n'y a pas de données précédentes");
      return FALSE;
    }
    
    // On charge l'id du cahier de notes
    $sql="SELECT `id_cahier_notes` FROM `cn_cahier_notes`
            WHERE `id_groupe`='".$_SESSION[PREFIXE]['id_groupe_session']."'
	    AND `periode`='".$num_trim."'";
    $result=mysql_query($sql);
    if ($result) {
      $id_trim_precedent = mysql_result($result, 0, 'id_cahier_notes');
    } else {
      // On n'a pas trouvé de conteneur pour la période précédente
      charge_message("Aucun cahier de texte trouvé pour la période précédente");
      return FALSE;
    }
    
    $trim_precedent= charge_conteneur($id_trim_precedent);
    if (!$trim_precedent) {
      charge_message("Échec lors de la récupération de la période précédente");
      return FALSE;
    }
    
    // on charge l'arborescence du trimestre
    $trim_actuel= charge_conteneur($_SESSION[PREFIXE]['id_racine']);
    if (!$trim_actuel) {
      charge_message("Échec lors de la récupération de la période courante");
      return FALSE;
    }
    
    // On lit les arborescences pour créer la future arborescence
    // On charge déjà la structure actuelle
    $trim_futur = $trim_actuel;
    
    $trim_futur = futur_sous_conteneur($trim_precedent,$trim_futur);
        
    // On met le tout dans un tableau
    $arborescences['precedent'] = $trim_precedent;
    $arborescences['actuel'] = $trim_actuel;
    $arborescences['futur'] = $trim_futur;
    
    return $arborescences;
    
  }

/** Ajoute au conteneur actuel l'organistion d'un autre conteneur
 * 
 * @param array $sous_conteneur l'organisation de conteneur à ajouter
 * @param array $conteneur_actuel l'organisation du conteneur tel qu'elle est actuellement
 * @return array|bool mixed L'organisation du conteneur, FALSE + un message sinon
 */
  function futur_sous_conteneur($sous_conteneur, $conteneur_actuel) {
    $tab_sous_conteneur=array();
    
    foreach ($conteneur_actuel['sous_conteneur'] as $conteneur) {
      $tab_sous_conteneur[]=array($conteneur['nom_court'],$conteneur['nom_complet']);
    }
    unset ($conteneur);
    
    foreach ($sous_conteneur['sous_conteneur'] as $conteneur) {
      if (in_array(array($conteneur['nom_court'],$conteneur['nom_complet']), $tab_sous_conteneur)) {
	// on cherche si un sous-conteneur est à ajouter
	// on récupère le sous-conteneur actuel
	foreach ($conteneur_actuel['sous_conteneur'] as &$cherche_conteneur) {
	  if ((trim($conteneur['nom_court'])==trim($cherche_conteneur['nom_court'])) && (trim($conteneur['nom_complet'])==trim($cherche_conteneur['nom_complet']))) {
	    $cherche_conteneur=futur_sous_conteneur($conteneur, $cherche_conteneur);
	  }
	}
      } else {
	// on ajoute un marqueur pour retrouver plus vite lors de l'enregistrement
	$conteneur["nouveau"]=TRUE;
	$conteneur_actuel['sous_conteneur'][]=$conteneur;
      }
    }
    unset ($conteneur);
    
    return ($conteneur_actuel);
  }

/** Renvoie un tableau avec l'organisation d'une période
 * 
 * id => $id_conteneur,
 * id_racine=>cn_conteneurs.display_parents'),
 * nom_complet=>cn_conteneurs.nom_complet,
 * nom_court=>cn_conteneurs.nom_court,
 * description=>cn_conteneurs.description,
 * mode=>cn_conteneurs.mode,
 * coef=>cn_conteneurs.coef,
 * arrondir=>cn_conteneurs.arrondir,
 * ponderation=>cn_conteneurs.ponderation,
 * display_parents =>cn_conteneurs.display_parents,
 * display_bulletin => cn_conteneurs.display_bulletin,
 * sous_conteneur => sous_modules_conteneur($id_conteneur))
 *
 * @param array $id_conteneur l'Id du conteneur
 * @return array|bool l'organistion d'un conteneur ou FALSE en cas de problème
 * @see sous_modules_conteneur()
 */
  function charge_conteneur($id_conteneur) {
    $sql="SELECT * FROM `cn_conteneurs` WHERE `id`= '".$id_conteneur."'";
    $result = mysql_query($sql);
    if ($result) {
      if(mysql_num_rows($result)!=0){
        $nom_complet = trim(htmlentities(mysql_result($result, 0, 'nom_complet'),ENT_COMPAT));
        $nom_court= trim(htmlentities(mysql_result($result, 0, 'nom_court'),ENT_COMPAT));

        // on récupère les conteneurs enfants
        $sous_elements=sous_modules_conteneur($id_conteneur);

        $conteneur=array('id' => $id_conteneur,
	                 'id_racine'=>mysql_result($result, 0, 'display_parents'),
                     'nom_complet'=>$nom_complet,
                     'nom_court'=>$nom_court,
	                 'description'=>mysql_result($result, 0, 'description'),
	                 'mode'=>mysql_result($result, 0, 'mode'),
                      'coef'=>mysql_result($result, 0, 'coef'),
	                 'arrondir'=>mysql_result($result, 0, 'arrondir'),
	                 'ponderation'=>mysql_result($result, 0, 'ponderation'),
                     'display_parents'=>mysql_result($result, 0, 'display_parents'),
                     'display_bulletin'=>mysql_result($result, 0, 'display_bulletin'),
                     'sous_conteneur'=>$sous_elements);	
        return $conteneur;
      }      
    } else {
      charge_message("Échec lors de la récupération des données de ".$id_conteneur." dans la base");
      return FALSE;  
    }
    return FALSE;
  }
  
/** Renvoie un tableau avec les données du sous-conteneur
 * 
 *Crée un tableau avec les données du conteneur dans cn_conteneurs et s'appelle récursivement au besoin
 * 
 * 'id' => cn_conteneurs.id,
 * 'nom_complet'=>cn_conteneurs.nom_complet,
 * 'nom_court'=>cn_conteneurs.nom_court,
 * 'id_racine'=>cn_conteneurs.display_parents,
 * 'description'=>cn_conteneurs.description,
 * 'mode'=>cn_conteneurs.mode,
 * 'sous_conteneur'=>sous_modules(cn_conteneurs.id),
 * 'coef'=>cn_conteneurs.coef,
 * 'arrondir'=>cn_conteneurs.arrondir,
 * 'ponderation'=>cn_conteneurs.ponderation,
 * 'display_parents'=>cn_conteneurs.display_parents,
 * 'display_bulletin'=>cn_conteneurs.display_bulletin);
 * 
 * @param int $id L'id du conteneur parent
 * @return array Les données du conteneur
 * @todo vérifier si c'est différent de sous_modules
 */
  function sous_modules_conteneur($id) {
    $sous_conteneur=array();
    $sql="SELECT co.* 
             FROM cn_conteneurs co
             WHERE co.parent='".$id."'
             ORDER BY co.nom_complet " ;

    $res_test=mysql_query($sql);

    if(mysql_num_rows($res_test)!=0){
      while($row=mysql_fetch_array($res_test)) {

        $id = $row['id'];
        $nom_complet = $row['nom_complet'];
        $nom_court= $row['nom_court'];
        $sous_elements=sous_modules_conteneur($row['id']);
        $coef=$row['coef'];
        $display_parents=$row['display_parents'];
        $display_bulletin=$row['display_bulletin'];

        $sous_conteneur[]=array('id' => $id,
                                'nom_complet'=>$nom_complet, 
                                'nom_court'=>$nom_court,
                                'id_racine'=>$row['display_parents'],
                                'description'=>$row['description'],
                                'mode'=>$row['mode'],
                                'sous_conteneur'=>$sous_elements,
                                'coef'=>$coef,
                                'arrondir'=>$row['arrondir'],
                                'ponderation'=>$row['ponderation'],
                                'display_parents'=>$display_parents,
                                'display_bulletin'=>$display_bulletin);
      }

    }

    return $sous_conteneur;
    
  }
  
/** Enregistre l'arborescence
 *
 * @return bool  
 */
  function enregistre_arbo() {
    if (cherche_nouveau($_SESSION[PREFIXE]['arborescence'],$_SESSION[PREFIXE]['id_racine'])) {
      return TRUE;
    } else {
      return FALSE;
    }
    
  } 
  
/** Enregistre les nouvelles entrées d'un conteneur dans la base
 *
 * @param int $conteneur Les données du conteneur
 * @param int $id_parent L'Id du conteneur parent
 * @return bool TRUE si tout c'est bien passé, FALSE sinon 
 */
  function cherche_nouveau($conteneur,$id_parent) {
    foreach ($conteneur as $ligne) {
      if (isset ($ligne["nouveau"]) && $ligne["nouveau"]) {
        echo $ligne["nom_complet"].' '.$ligne['nom_court'].' '.$ligne['description'].' '.$ligne['mode'].' '.$ligne['coef'].' '.$ligne['arrondir'].' '.$ligne['ponderation'].' '.$ligne['display_parents'].' '.$ligne['display_bulletin'].' '.$id_parent.'<br />';
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
                VALUES ('".$_SESSION[PREFIXE]['id_racine']."',
                  '".$ligne['nom_court']."',
                  '".$ligne['nom_complet']."',
                  '".$ligne['description']."',
                  '".$ligne['mode']."',
                  '".$ligne['coef']."',
                  '".$ligne['arrondir']."',
                  '".$ligne['ponderation']."',
                  '".$ligne['display_parents']."',
                  '".$ligne['display_bulletin']."',
                  '".$id_parent."')";
          
        if (!mysql_query($sql)) {
          return FALSE;
        }
        
      } else {
        if (count($ligne['sous_conteneur'])) {
          //echo 'On a des sous conteneurs <br />';
          cherche_nouveau($ligne['sous_conteneur'],$ligne['id']);
        }
      }
    }
    unset ($ligne);
    return TRUE;
  }

?>
