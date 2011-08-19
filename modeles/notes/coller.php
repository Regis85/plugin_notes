<?php

/** Modele du module notes : action coller 
 * 
 * Remplir une �valuation par copier/coller
 * 
 * @author R�gis Bouguin
 * @package saisie_notes
 * @subpackage coller
 * @todo Effacer le saut de ligne de fin quand on colle depuis un tableur pour pouvoir tester aussi si on a trop de notes
  
*/

/** Les donn�es de l'�valuation prises dans cn_devoirs
 * 
 * @param int $id_eval L'id de l'�valuation
 * @return array mixed Les donn�es de l'�valuation
 * @see peut_noter_groupe()
 */
function donnee_evaluation($id_eval) {
  
  // L'enseignant fait parti du groupe ?
  if (!peut_noter_groupe($_SESSION[PREFIXE]['id_groupe_session'])) {
    return FALSE;
  }
  
  $table_eval=array();
  
  $sql="SELECT de.*
          FROM cn_devoirs de
          WHERE de.id ='".$id_eval."' ";
  $query_devoirs=mysql_query($sql);
  if(0 != mysql_num_rows($query_devoirs)) {
    $table_eval = mysql_fetch_array($query_devoirs, MYSQL_ASSOC);
  }  
  mysql_free_result($query_devoirs);
  
  return $table_eval;
}

/** Charge les notes coll�es dans un tableau
 * 
 * Recherche le caract�re de fin de ligne dans la chaine puis la d�coupe dans un tableau 
 * gr�ce � lui en v�rifiant que les notes sont correctes
 * 
 * 
 * @param string $colle_notes Les notes coll�es
 * @param int $id_eval id de l'�valuation
 * @return array|bool Les notes v�rifi�es et le statut ou FALSE
 * @see charge_message()
 * @see note_valide()
 */
function colle_notes($colle_notes,$id_eval) {
  // TODO : Effacer le saut de ligne de fin quand on colle depuis un tableur pour pouvoir tester aussi si on a trop de notes
  
  $sql="SELECT note_sur FROM cn_devoirs WHERE id = '".$id_eval."'";
  $query = mysql_query($sql);
  if(0 == mysql_num_rows($query)) {
    charge_message("ERREUR : L'�valuation n'a pas de note maximale de r�f�rence") ;
    mysql_free_result($query);
    return FALSE;	  
  } else {
    $note_max = mysql_fetch_row($query);
  }
  
  $notes=array();
  $finLigne=FALSE;
  
  if (strlen($colle_notes))  {
    
    if (preg_match("#\\\\r\\\\n#", $colle_notes)) {
      $finLigne="\\\\r\\\\n";
    } elseif (preg_match("#\\\\n#", $colle_notes)) {
      $finLigne="\\\\n";  
    } elseif (preg_match("#\\\\r#", $colle_notes)) {
      $finLigne="\\\\r";
    } else {
      $notes[]=$colle_notes;
    }
    if ($finLigne) {
      $notes=mb_split($finLigne, $colle_notes);
      foreach ($notes as $note) {
        $retour_note=note_valide($note, $note_max[0]);
        if ( $retour_note!= FALSE) {
          $table_notes[]=$retour_note;
        }
      }
      if (count($table_notes) != count($notes)){
        return FALSE;
      }
    }
  }
  
  return $table_notes;
  
}

/** retourne dans un tableau le statut et la note si elle est valide
 * 
 * @param int $val L'entr�e � v�rifier 
 * @param int $note_max La note maximale de l'�valuation
 * @return array|bool Le tableau si la note est valide, FALSE sinon
 */
function note_valide($val, $note_max) { 
  
  if (is_numeric($val)) {

    if($val > $note_max) {
      charge_message("ERREUR : Un �l�ve � une note en dehors du r�f�rentiel (".$val.") !") ;
      return FALSE;
    } else {
      $note = $val;
      $statut = "";
    }
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
  
  $retour=array("note"=>$note,"statut"=>$statut);
  
  return $retour;
}

/** Traite les commentaires
 * 
 * Recherche le caract�re de fin de ligne dans la chaine puis la d�coupe dans un tableau gr�ce � lui
 * 
 * @param string $colle_comments Les commentaires coll�s
 * @return text|bool Les commentaires v�rifi�s ou FALSE
 * @todo g�rer les ' dans les commentaires
 */
function colle_comments($colle_comments) {
  // TODO : Effacer le saut de ligne de fin quand on colle depuis un tableur pour pouvoir tester aussi si on a trop de commentaires
  $comments=array();
  $finLigne=FALSE;
  
  if (strlen($colle_comments))  {
    if (preg_match("#\\\\r\\\\n#", $colle_comments)) {
      $finLigne="\\\\r\\\\n";
    } elseif (preg_match("#\\\\n#", $colle_comments)) {
      $finLigne="\\\\n";  
    } elseif (preg_match("#\\\\r#", $colle_comments)) {
      $finLigne="\\\\r";
    } else {
      $comments[]=$colle_comments;
    }
    if ($finLigne) {
      $comments=mb_split($finLigne, $colle_comments);
      return $comments;
    }
  }
  
  return FALSE;
  
}

/** Enregistre les donn�es coll�es dans la base
 * 
 * Enregistre les donn�es en v�rifiant si c'est une mise � jour
 * 
 * met � jour les moyennes de conteneurs
 * 
 * @return bool TRUE si les donn�es ont �t� enregistr�es, FALSE sinon
 * @see charge_message()
 * @see mise_a_jour_moyennes_conteneurs()
 */
function enregistre_colle() {
  
  // echo $_SESSION[PREFIXE]['eval_colle'].'<br />';
  
  foreach ($_SESSION[PREFIXE]['tableau_colle'] as $eleve) {
    if (isset($eleve['commentaire'])) {
      $comment = $eleve['commentaire'];
    } else {
      $comment = '';
    }
    if (isset($eleve['note'])) {
      $note = $eleve['note'];
    } else {
      $note = '';
    }
    if (isset($eleve['statut'])) {
      $statut = $eleve['statut'];
    } else {
      $statut = '';
    }
      
    // On cherche s'il y a d�j� un enregistrement
    $sql= "SELECT 1=1 FROM cn_notes_devoirs 
             WHERE login = '".$eleve['login']."' 
             AND id_devoir = '".$_SESSION[PREFIXE]['eval_colle']."'";
    $query = mysql_query($sql);
    if (0 == mysql_num_rows($query)) {
      // On a pas d'enregistrement, on le cr�e
      $sql_table="INSERT INTO cn_notes_devoirs (login, id_devoir, note, comment, statut)
		   VALUES ('".$eleve['login']."', '".$_SESSION[PREFIXE]['eval_colle']."', '".$note."', '".$comment."', '".$statut."')";
    } else {
      // On a un enregistrement on le met � jour
      $envoi="";
      if ($note != '' || $statut != '') {
        $envoi="note = '".$eleve['note']."', statut= '".$eleve['statut']."'" ;
      }
      if ($comment != '') {
        if ($envoi != '') {
          $envoi .= ', ';
	    }
	    $envoi .= "comment= '".$eleve['commentaire']."'";
      }
      $sql_table="UPDATE cn_notes_devoirs
		            SET ".$envoi."
		            WHERE login = '".$eleve['login']."'
                      AND id_devoir = '".$_SESSION[PREFIXE]['eval_colle']."'";
      
    }
    
    if (!mysql_query($sql_table)) {
	charge_message("ERREUR : Echec de l'enregistrement dans la base ! (".$eleve['nom']." ".$eleve['prenom'].")") ;
	charge_message("<bold>Collez � nouveau vos donn�es et v�rifier les puis enregistrez � nouveau</bold>") ;
	mysql_free_result($sql_table);	
	return FALSE;	
    }
    
    
    // on met � jour les moyennes de conteneurs
    $_current_group["eleves"][$_SESSION[PREFIXE]['periode_num']]["list"][] = $eleve['login'];
    $arret='no';
    $sql_conteneur= "SELECT id_conteneur FROM cn_devoirs WHERE id = '".$_SESSION[PREFIXE]['eval_colle']."'";  
    $query_conteneur = mysql_query($sql_conteneur);  
    if (!$query_conteneur) {
      charge_message("ERREUR : Echec de la mise � jour des conteneurs") ;
      mysql_free_result($query_conteneur);
      return FALSE;	
    }
    $conteneur=mysql_fetch_object($query_conteneur);
    mysql_free_result($query_conteneur);
    mise_a_jour_moyennes_conteneurs($_current_group, $_SESSION[PREFIXE]['periode_num'],$_SESSION[PREFIXE]['id_racine'],$conteneur->id_conteneur,$arret);
  
  }
 
  return TRUE;
  
}

?>
