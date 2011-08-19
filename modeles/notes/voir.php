<?php
/** Mod�le du module notes : action voir
 * 
 * Afficher et modifier les notes d'une ou plusieurs �valuations
 * 
 * @author R�gis Bouguin
 * @package saisie_notes
 * @subpackage voir
 *  
 */

/** Renvoie les �valuations de la p�riode
 * 
 * Renvoie un tableau des �valuations de la p�riode avec les champs
 * 
 * id (cn_devoirs.id), nom_court (cn_devoirs.nom_court), periode (cn_cahier_notes.periode),
 *  conteneur (cn_conteneurs.nom_court)
 * 
 * @return array  
 * @see peut_noter_groupe()
 * @see 
 */
function evaluations_disponibles() {  
  // L'enseignant fait parti du groupe ?
  if (!peut_noter_groupe($_SESSION[PREFIXE]['id_groupe_session'])) {
    return FALSE;
  } 
  $table_eval=array(); 
  $sql="SELECT de.id , de.nom_court , cn.periode , co.nom_court AS conteneur
          FROM cn_cahier_notes cn , cn_devoirs de , cn_conteneurs co
          WHERE cn.periode = '".$_SESSION[PREFIXE]['periode_num']."'
            AND cn.id_groupe = '".$_SESSION[PREFIXE]['id_groupe_session']."'
            AND cn.id_cahier_notes = de.id_racine
            AND de.id_conteneur = co.id ";
  $query_devoirs=mysql_query($sql);
  if(0 != mysql_num_rows($query_devoirs)) {
    while ($row = mysql_fetch_array($query_devoirs, MYSQL_ASSOC)) {
       $table_eval[] = $row;
    }
  }  
  mysql_free_result($query_devoirs);
  return $table_eval;
}

/** Renvoie les �valuations qui n'ont pas �t� choisies
 *
 * @param array $evaluations tableau de toutes les �valuations
 * @return array tableau des �valuations non choisies
 */
function eval_non_choisies($evaluations) {
  $eval_possibles=array();
  foreach ($evaluations as $eval) {
    if(!in_array($eval['id'], $_SESSION[PREFIXE]['id_devoir'])) {
      $eval_possibles[]=$eval;
    }
  }
  unset ($eval);
  return $eval_possibles;
}

/** V�rifie que les �valuations appartiennent bien au groupe
 *
 * @param type $tableau_id_devoir tableau des Id de devoirs � v�rifier
 * @param type $id_groupe L'identifiant du groupe
 * @return array|bool Les devoirs du groupe ou FALSE si une �valuation n'appartient pas au groupe
 */
function eval_du_groupe($tableau_id_devoir, $id_groupe) {
  // Les devoirs appartiennent-ils aux groupes ? 
  foreach ($tableau_id_devoir as $devoir) {
    $sql_devoirs = "SELECT 1=1 
		       FROM cn_devoirs de, cn_cahier_notes cn
		       WHERE de.id = '".$devoir."'
                 AND de.id_racine = cn.id_cahier_notes
                 AND cn.id_groupe = '".$id_groupe."'";
    $query_devoirs=mysql_query($sql_devoirs);
    if (mysql_num_rows($query_devoirs)!=0) {
      $devoirs_groupe[] = $devoir;
    }
    mysql_free_result($query_devoirs);
  }
  unset ($devoir);
  return $devoirs_groupe;
}

/** V�rifie que des �valuations sont bien dans une p�riode
 *
 * @param array $tableau_id_devoir tableau des Id des devoirs � v�rifier
 * @param type $periode la p�riode de r�f�rence
 * @return  array tableau des Id des devoirs valides
 */
function eval_dans_periode($tableau_id_devoir , $periode) {
  foreach ($tableau_id_devoir as $devoir) {
    $sql_devoirs_ouverts="SELECT 1=1
	       FROM periodes pe, j_groupes_classes cl, cn_devoirs de, cn_cahier_notes cn
	       WHERE de.id = '$devoir'
             AND de.id_racine = cn.id_cahier_notes
             AND cn.id_groupe = cl.id_groupe
             AND pe.id_classe = cl.id_classe
             AND pe.num_periode = '$periode'
             AND pe.verouiller = 'N' ;";
    
    $query_devoirs_ouverts = mysql_query($sql_devoirs_ouverts);
    if(mysql_num_rows($query_devoirs_ouverts)==0) {
      charge_message("Le devoir ".$devoir." n'est pas modifiable");
    } else {
      $devoirs_groupe[]=$devoir; 
    }
  mysql_free_result($query_devoirs_ouverts);
  }
  return $devoirs_groupe;
}

/** Renvoie les �valuations modifiables
 * 
 * Une �valuation est modifiable si : C'est le groupe actif, elle est dans une p�riode ouverte, 
 * l'enseignant peut la noter
 * 
 * Renvoie un tableau des �valuation valides
 * 
 *'id', 'nom_court', 'referentiel', 'date', 'coef', 'display_parents', 'conteneur', 'tab_notes', 'note_sur'
 * SELECT cn_devoirs.id, cn_devoirs.date, cn_devoirs.nom_court, cn_devoirs.ramener_sur_referentiel, 
 * cn_conteneurs.nom_court AS conteneur, cn_devoirs.display_parents, cn_devoirs.coef, cn_devoirs.note_sur
 * 
 * @return array les �valuation valides
 * @see eval_du_groupe()
 * @see eval_dans_periode()
 * @see statistique()
 */
function evaluations_modifiables() {
  
  // L'enseignant fait parti du groupe ?
  if (!peut_noter_groupe($_SESSION[PREFIXE]['id_groupe_session'])) {
    charge_message("Vous n'appartenez pas au groupe ".$_SESSION[PREFIXE]['id_groupe_session']);
    return FALSE;
  }
    
  $table_evaluations=array(); // tableau de toutes les �valuations valides
  $periode_ouverte=FALSE;
  $devoirs_groupe=array();
      
  $devoirs_groupe = eval_du_groupe($_SESSION[PREFIXE]["id_devoir"],$_SESSION[PREFIXE]['id_groupe_session']);
  
  if (!$devoirs_groupe)  {
    charge_message("Vous avez s�lectionn� des devoirs qui n'appartiennent pas � ce groupe
      <br />ceci ne devrait jamais arriv�");
    charge_message("groupe : ".$_SESSION[PREFIXE]['id_groupe_session']);
    charge_message("devoir :");
    foreach ($_SESSION[PREFIXE]["id_devoir"] as $montre_devoir) {
      charge_message($montre_devoir);
    }
    return FALSE;
  }
  
  unset ($_SESSION[PREFIXE]["id_devoir"]);
  
  if (!count($devoirs_groupe)) {
    charge_message("Aucun devoir disponible");
    return FALSE;
  } else {
    $_SESSION[PREFIXE]["id_devoir"] = $devoirs_groupe;
  }
  
  // On recherche les p�riodes ouvertes des classes du groupe
  $sql_periodes="SELECT cl.id_classe, pe.nom_periode, pe.verouiller, UNIX_TIMESTAMP(pe.date_verrouillage) AS date_verrouillage
               FROM periodes pe, j_groupes_classes cl
               WHERE cl.id_groupe = '".$_SESSION[PREFIXE]['id_groupe_session']."'
                 AND pe.id_classe = cl.id_classe
                 AND pe.verouiller = 'N'
               ORDER BY cl.id_classe, pe.num_periode ;";
  $query_periodes = mysql_query($sql_periodes);
  
  if(mysql_num_rows($query_periodes)==0) {
    // il n'y a pas de p�riode ouverte
    charge_message("Il n'y a pas de p�riode ouverte");
    mysql_free_result($query_periodes);
    return FALSE; 
  }
  mysql_free_result($query_periodes);
  
  unset ($devoirs_groupe);
  $devoirs_groupe=array();

  // On v�rifie que les devoirs sont bien dans la p�riode ouverte
  $devoirs_groupe = eval_dans_periode($_SESSION[PREFIXE]["id_devoir"], $_SESSION[PREFIXE]['periode_num']);    
  if (!$devoirs_groupe) {
    charge_message("Aucun devoir choisi n'est dans la p�riode ouverte");
    return FALSE;
  }
  
  // On a des devoirs, le prof peut noter, on renvoie les id_devoir
  unset ($_SESSION[PREFIXE]["id_devoir"]);
  if (!count($devoirs_groupe)) {
    charge_message("Aucun devoir disponible");
    return FALSE;
  } else {
    $_SESSION[PREFIXE]["id_devoir"] = $devoirs_groupe;
  }

  if (isset ($_SESSION[PREFIXE]["id_devoir"]) && count($_SESSION[PREFIXE]["id_devoir"]) > 0) {
    // On renvoie les infos des �valuations
    foreach ($_SESSION[PREFIXE]["id_devoir"] as $devoir) {
      $sql_eval="SELECT de.id, UNIX_TIMESTAMP(de.date) as date, 
                        de.nom_court, 
                        de.ramener_sur_referentiel AS referentiel, 
                        cn.nom_court AS conteneur, 
                        de.display_parents, 
                        de.coef, 
                        de.note_sur
                 FROM cn_devoirs de , cn_conteneurs cn
                 WHERE de.id = '".$devoir."'
                   AND de.id_conteneur = cn.id ;";
      $query_eval = mysql_query($sql_eval);
      if(0 != mysql_num_rows($query_eval)) {
        while ($row = mysql_fetch_array($query_eval, MYSQL_ASSOC)) {
          $date=date("d/m/Y",$row['date']);
          $stat=statistique($row['id']);
          $table_evaluations[]=array('id' => $row['id'],
				     'nom_court' => $row['nom_court'],
				     'referentiel' => $row['referentiel'],
				     'date' => $date,
				     'coef' => $row['coef'],
				     'display_parents' => $row['display_parents'],
				     'conteneur' => $row['conteneur'],
				     'tab_notes' =>$stat,
				     'note_sur' => $row['note_sur']);
        }
      }
      mysql_free_result($query_eval);
    }
    unset ($devoir);
  }
  return $table_evaluations;
}

/** Renvoie les notes pour un groupe d'�l�ves
 *
 * $tableau_notes[$index] = array('login' =>$eleves['login'], 'index' => $index, 'eleve' => $eleves, 'notes' => $notes_eleves);
 * 
 * $note : soit la note, soit abs, disp, - ou vide en fonction de cn_notes_devoirs.statut
 * 
 * $index : Ligne du tableau
 * 
 * $eleves : les donn�es d'un �l�ves pass�e dans $groupe_eleves
 * 
 * $notes_eleves[cn_notes_devoirs.id_devoir] = array('id_devoir' => cn_notes_devoirs.id_devoir, 'note_devoir' => $note, 'comment_devoir' => cn_notes_devoirs.comment, 'statut' => cn_notes_devoirs.statut, 'new_note' => TRUE/FALSE)
 * 
 * @param array $groupe_eleves un tableau d'�l�ves
 * @param array $eval_valides un tableau d'�valuations
 * @return array Le tableau de notes notes 
 * @see evaluations_modifiables()
 * @see trouveEleves()
 */
function cherche_notes($groupe_eleves, $eval_valides) {
  // Tableau des �l�ves et pour chacun, de ses notes
  $tableau_notes=array();
  
  foreach ($groupe_eleves as $eleves) {
    $notes_eleves=array();
    // on recherche toutes les notes de l'�l�ve
    foreach ($eval_valides as $evaluation) {
      $sql_notes = "SELECT * FROM cn_notes_devoirs 
                      WHERE login = '".$eleves['login']."'
                        AND id_devoir = '".$evaluation['id']."' ;"; 
      $query_notes = mysql_query($sql_notes);
      if(1 == mysql_num_rows($query_notes)) {
        while ($row = mysql_fetch_array($query_notes, MYSQL_ASSOC)) {
          switch ($row['statut']) {
            case ABSENT:
              $note = ABSENT;
              break;
            case DISPENSE:
              $note = DISPENSE;
              break;
            case NON_NOTE:
              $note = NON_NOTE;
              break;
            case VIDE:
              $note = "";
              break;
            default :
              $note = $row['note'];
          }

          $notes_eleves[$row ['id_devoir']] = array('id_devoir' => $row ['id_devoir'], 'note_devoir' => $note, 'comment_devoir' => $row['comment'], 'statut' => $row['statut'], 'new_note' => FALSE);
        }
      } else {
        $notes_eleves[$evaluation['id']] = array('id_devoir' => $evaluation['id'], 'note_devoir' => "", 'comment_devoir' => "" , 'statut' => VIDE , 'new_note' => TRUE);
      }
    }
    
    mysql_free_result($query_notes);
    
    $index = count($tableau_notes);
    $tableau_notes[$index] = array('login' =>$eleves['login'], 'index' => $index, 'eleve' => $eleves,'notes' => $notes_eleves);
    unset ($evaluation);
  }
  unset ($eleves);
  
  return $tableau_notes;
 
}

/** Enl�ve une �valuation du tableau de notes modifiables
 * 
 * @param int $evaluation id de l'�valuation � cacher
 */
function cacher_eval($evaluation) {
  $table_eval=array();
  foreach ($_SESSION[PREFIXE]['id_devoir'] as $eval) {
    if ($eval != $evaluation) {
      $table_eval[] = $eval;
    }
  }
  unset ($_SESSION[PREFIXE]['id_devoir']);
  $_SESSION[PREFIXE]['id_devoir'] = $table_eval;
}

/** * Enregistre les notes dans la base
 * 
 * @param array $donnees Les donn�es � enregistrer
 * @return bool TRUE si les donn�es ont �t� enregistr�es
 * @see charge_message()
 * @see prepare_sql()
 */
function enregistre_notes($donnees) {
  $tableau_notes=$_SESSION[PREFIXE]['tableau_notes'];
    
  // on recherche les notes maxi pour toutes les �valuations
  $elv_deja_note=array();
  foreach ($_SESSION[PREFIXE]["id_devoir"] as $id_devoir) {
    
    $sql_devoir="SELECT note_sur, id_conteneur FROM cn_devoirs 
	     WHERE id = '".$id_devoir."'";
    $query_devoir = mysql_query($sql_devoir);
    $eval=mysql_fetch_object($query_devoir);
    $notes_max[$id_devoir]['note_sur']=$eval->note_sur;
    $notes_max[$id_devoir]['id_conteneur']=$eval->id_conteneur;
    
    mysql_free_result($query_devoir);
    
  }
  unset ($id_devoir);
      
  while (list($key, $val) = each($donnees)) {
    if(mb_ereg("_note_", $key)) {
      $index=mb_strstr ( $key , "_note_" , TRUE );
      $id_eval = mb_strcut (mb_strstr ( $key , "_note_" , FALSE ),6);
      
      // $comment = "";

      if (is_numeric($val)) {
	if($val > $notes_max[$id_eval]['note_sur']) {
	  charge_message("ERREUR : Un �l�ve � une note en dehors du r�f�rentiel ! (".$login.")") ;
	  return FALSE;	  
	}
	$note = $val;
	$statut = "";
      } elseif (empty ($val)) {
	$note = "";
	$statut = VIDE;
      } else {
	switch ($val) {
	  case "a":
	  case "A":
	  case "abs":
	  case "ABS":
	  case ABSENT:
	    $note = "";
	    $statut = ABSENT;
	    break;
	  case "d":
	  case "D":
	  case "disp":
	  case "Disp":
	  case DISPENSE:
	    $note = "";
	    $statut = DISPENSE;
	    break;
	  case "-":
	  case "n":
	  case "N":
	  case "nn":
	  case "NN":
	  case "nN":
	  case "Nn":
	  case NON_NOTE:
	    $note = "";
	    $statut = NON_NOTE;
	    break; 
	  case "":
	  default :
	    $note = "";
	    $statut = VIDE;
	}
      }
      
      // On met � jour $tableau_notes
      $tableau_notes[$index]['notes'][$id_eval]['note_devoir'] = $note;
      $tableau_notes[$index]['notes'][$id_eval]['statut'] = $statut;
          
    } elseif (mb_ereg("_app_", $key) && $val!='') {
      $index=mb_strstr ( $key , "_app_" , TRUE );
      $id_eval = mb_strcut (mb_strstr ( $key , "_app_" , FALSE ),5);
      $val = prepare_sql($val);
      $tableau_notes[$index]['notes'][$id_eval]['comment_devoir'] = $val;

    }
  }  
  
  foreach ($tableau_notes as $ligne_tableau) {
    foreach ($_SESSION[PREFIXE]["id_devoir"] as $id_eval) {
      if ($ligne_tableau['notes'][$id_eval]['new_note']) {
       // on cr�e une entr�e
       $sql_table="INSERT INTO cn_notes_devoirs (login, id_devoir, note, comment, statut)
                    VALUES ('".$ligne_tableau['login']."',
                      '".$ligne_tableau['notes'][$id_eval]['id_devoir']."',
                      '".$ligne_tableau['notes'][$id_eval]['note_devoir']."',
                      '".$ligne_tableau['notes'][$id_eval]['comment_devoir']."',
                      '".$ligne_tableau['notes'][$id_eval]['statut']."')";
      } else {
	// on met � jour
	$sql_table="UPDATE cn_notes_devoirs
                  SET note = '".$ligne_tableau['notes'][$id_eval]['note_devoir']."',
                    statut= '".$ligne_tableau['notes'][$id_eval]['statut']."',
                    comment = '".$ligne_tableau['notes'][$id_eval]['comment_devoir']."'
                  WHERE login = '".$ligne_tableau['login']."'
                    AND id_devoir = '".$ligne_tableau['notes'][$id_eval]['id_devoir']."'";
      }
      $query_table = mysql_query($sql_table);
       if (!$query_table) {
	 charge_message("ERREUR : Erreur lors de l'enregistrement dans la base ! (".$index.")") ;
	 charge_message("<strong>V�rifiez vos donn�es puis enregistrez � nouveau</strong>") ;
	 return FALSE;	
       }
    }
    unset ($id_eval);
  }
  unset ($ligne_tableau);
  
  // Si on modifie un devoir alors que des notes ont �t� report�es sur le bulletin, il faut penser � mettre � jour la recopie vers le bulletin.
    $sql="SELECT 1=1 FROM matieres_notes 
            WHERE periode='".$_SESSION[PREFIXE]['periode_num']."'
              AND id_groupe='".$_SESSION[PREFIXE]['id_groupe_session']."';";
    $test_bulletin=mysql_query($sql);
    if(mysql_num_rows($test_bulletin)>0) {
      charge_message("ATTENTION: Des notes sont pr�sentes sur le bulletin.<br />Si vous avez modifi� ou ajout� des notes, pensez � mettre � jour la recopie vers le bulletin.") ;
    }
    mysql_free_result($test_bulletin);
  
  return TRUE;
  
}

/** V�rifie si une �valuation est d�j� not� pour un utilisateur
 *
 * @param text $login le login de l'utilisateur
 * @param int $id_eval Id de l"�valuation
 * @return bool TRUE si l'�valuation est d�j� not�e, FALSE sinon 
 */
function note_existe($login , $id_eval) {
  // On recherche si l'enregistrement existe
  $sql="SELECT 1=1 FROM cn_notes_devoirs 
          WHERE login = '".$login."'
            AND id_devoir = '".$id_eval."'";
  $query = mysql_query($sql);
  if(0 == mysql_num_rows($query)) {
    // elle n'existe pas
    mysql_free_result($query);
    return FALSE;
  }else {
    // on met � jour
    mysql_free_result($query);
    return TRUE;
  }
      

}

/** R�cup�re les notes d'une �valuation dans la base
 * 
 * @param int $id_evaluation L'id de l'�valuation
 * @return array|bool Toutes les notes d'une �valuation, FALSE + un message sinon
 */
function statistique($id_evaluation) {
  $tab_notes=array();
  $sql="SELECT `note` FROM `cn_notes_devoirs` 
           WHERE `id_devoir` = '".$id_evaluation."'
             AND `statut` = ''";
  $result = mysql_query($sql);
  if(0 == mysql_num_rows($result)) {
    // Il n'y a pas de notes
    $stat=FALSE;
  } else {
    while ($row = mysql_fetch_assoc($result)) {
      if (is_numeric($row['note'])) {
        $stat[]=$row['note'];
      }
    }
    sort($stat);
  }
  mysql_free_result($result);
  
  return $stat;
}

/**
 * V�rifie suhosin.post.max_vars, suhosin.post.max_totalname_length
 * @return boolean TRUE si les valeurs de $_POST sont inf�rieures � celle de suhosin
 */
function verifie_suhosin () {
  $nb_eleves = count($_SESSION['plugin_notes']['tableau_notes']);
  $nb_notes = count($_SESSION['plugin_notes']['id_devoir']);
  $long_maxi_cle = 18;
  $nb_vars = 2+($nb_eleves*((2*$nb_notes)));
  
  $suhosin_actif_1 = verifie_cle_suhosin ('suhosin.post.max_vars',$nb_vars);
  $suhosin_actif_2 = verifie_cle_suhosin ('suhosin.post.max_totalname_length',$long_maxi_cle);
  if ($suhosin_actif_1 && $suhosin_actif_2) { 
    return TRUE;
  }  
  return FALSE;
}

/**
 * V�rifie si suhosin est activ�
 * 
 * Si suhosin est activ�, v�rifie que la valeur de la cl� est compatible
 * 
 * @param string $suhosin_cle Cl� � tester
 * @param int $taille_post Taille envoy�e
 * @return boolean TRUE si suhosin est activ�
 */
function verifie_cle_suhosin ($suhosin_cle, $taille_post) {
  //$tableau_suhosin = charge_tableau_suhosin();
  //if ($tableau_suhosin) {
  $val_suhosin=ini_get($suhosin_cle);
  if($val_suhosin!='') {
    $cle_request = mb_ereg_replace('post', 'request', $suhosin_cle);
    $cle_request = mb_ereg_replace('get', 'request', $cle_request);
    $min_suhosin_cle = min($val_suhosin,ini_get($cle_request));
    
    if ($taille_post > $min_suhosin_cle) {
    // d�commenter pour voir les valeurs de suhosin
    /* *
      charge_message($suhosin_cle.': '.$tableau_suhosin[$suhosin_cle].' - '.$cle_request.': '.$tableau_suhosin[$cle_request].' - '.$min_suhosin_cle) ;
      switch ($cle_request) {
        case 'suhosin.request.max_totalname_length':
          charge_message('Taille maximale des indices pass�s en $_POST : '.$taille_post);
          break;
        case 'suhosin.request.max_value_length':
          charge_message('Taille des champs pass�s en $_POST : '.$taille_post);
          break;
        default :
          charge_message('variables pass�es en $_POST : '.count($_POST).' - '.$taille_post);
      }    
    /* */
      return FALSE;
    }
            
    return TRUE;
  }
  return TRUE;
}

/**
 * Renvoie des informations sur suhosin
 * 
 * tableau des valeurs ou FALSE si suhosin n'est pas activ�
 * 
 * $tab_suhosin=array('suhosin.cookie.max_totalname_length','suhosin.get.max_totalname_length','suhosin.post.max_totalname_length','suhosin.post.max_value_length','suhosin.request.max_totalname_length','suhosin.request.max_value_length','suhosin.request.max_vars');
 * - suhosin.cookie.max_totalname_length : longueur maximale du nom de la variable dans le cookie
 * - suhosin.get.max_totalname_length : longueur maximale du nom de la variable lorsqu'il est enregistr� par l'URL
 * - suhosin.get.max_value_length : D�finit la longueur maximale d'une variable qui est enregistr� par l' URL
 * - suhosin.get.max_vars : D�finit le nombre maximum de variables qui peuvent �tre enregistr�s par l' URL
 * - suhosin.post.max_totalname_length : longueur maximale du nom de la variable lorsqu'il est enregistr� par une requ�te POST
 * - suhosin.post.max_value_length : D�finit la longueur maximale d'une variable qui est enregistr� par le biais d'une requ�te POST
 * - suhosin.post.max_vars : D�finit le nombre maximum de variables qui peuvent �tre enregistr�s via une requ�te POST
 * - suhosin.request.max_totalname_length : D�finit la longueur maximale des noms de variables pour les variables enregistr�es dans le cookie, l' URL ou via une requ�te POST
 * - suhosin.request.max_value_length : (caract�res) D�finit la longueur maximale d'une variable qui est enregistr� par le biais du cookie, l'URL ou via une requ�te POST
 * - suhosin.request.max_vars : D�finit le nombre maximum de variables qui peuvent �tre enregistr�s par le cookie, le URL ou via une requ�te POST
 * 
 * @return string|boolean le tableau ou false
 * @link http://www.hardened-php.net/suhosin/configuration.html
 */
function charge_tableau_suhosin() { 
  $suhosin_post_max_totalname_length=ini_get('suhosin.post.max_totalname_length');
  if($suhosin_post_max_totalname_length!='') {
    $tab_suhosin=array('suhosin.cookie.max_totalname_length' => ini_get('suhosin.cookie.max_totalname_length'), 
      'suhosin.get.max_totalname_length' => ini_get('suhosin.get.max_totalname_length'), 
      'suhosin.get.max_value_length' => ini_get('suhosin.post.max_value_length'),    
      'suhosin.get.max_vars' => ini_get('suhosin.get.max_value_length'),  
      'suhosin.post.max_totalname_length' => ini_get('suhosin.post.max_totalname_length'), 
      'suhosin.post.max_value_length' => ini_get('suhosin.post.max_value_length'),  
      'suhosin.post.max_vars' => ini_get('suhosin.post.max_value_length'),  
      'suhosin.request.max_totalname_length' => ini_get('suhosin.request.max_totalname_length'), 
      'suhosin.request.max_value_length' => ini_get('suhosin.request.max_value_length'), 
      'suhosin.request.max_vars' => ini_get('suhosin.request.max_vars'));
      return $tab_suhosin;
  } else {
    return FALSE;
  }
  
  
}

?>
