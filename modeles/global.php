<?php
/** Fonctions utilis�es dans plusieurs pages du plugin notes multiples
 * 
 * @author R�gis Bouguin
 * @package global
 * 
 */

/** V�rifie qu'un enseignant peut noter le groupe
 *
 * @param int $id_groupe Id du groupe
 * @return bool TRUE si l'enseignant peut noter, FALSE sinon 
 */
function peut_noter_groupe($id_groupe) {
  $sql="SELECT 1=1  FROM j_groupes_professeurs
              WHERE login = '".$_SESSION["login"]."'
                AND id_groupe = '".$id_groupe."'";
  $query = mysql_query($sql);
  if(mysql_num_rows($query)==1) {
    mysql_free_result($query);
    return TRUE;
  }
  mysql_free_result($query);
  $_SESSION[PREFIXE]['tbs_msg'] ="Vous ne pouvez pas noter ce groupe : ".mysql_num_rows($query)." renvoy� par <br />".$sql;
  return FALSE; 
}

/**Retourne les donn�es du groupe actif
 * 
 * Retourne les donn�es du groupe actif dans un tableau en utilisant la fonction get_group()
 * 
 * @param int ID du groupe actif
 * @return array Tableaux imbriqu�s des informations du groupe
 * @see get_group()
 */
function recupere_groupe_actif($id_groupe_actif) {
  $groupe = get_group($id_groupe_actif) ;
  $groupe["matiere"]["nom_complet"] = htmlentities($groupe["matiere"]["nom_complet"],ENT_COMPAT) ;
  return $groupe ; 
}

/** Renvoie un tableau avec des informations sur les �l�ves
 * 
 * Renvoie un tableau avec pour chaque �l�ves les champs nom, prenom, login, elenoet
 * 
 * Charge un message d'erreur avec charge_message() en cas d'�chec
 * 
 * @return mixed Tableau des donn�es des �l�ves ou False en cas d'�chec
 * @see charge_message()
 */
function trouveEleves() {
  $table_eleves = FALSE ;
  if (!isset ($_SESSION[PREFIXE]['periode_num'])) {
    charge_message("ERREUR : La p�riode n'est pas d�finie !") ;
    return FALSE;
  }
  if (!isset ($_SESSION[PREFIXE]['id_groupe_session'])) {
    charge_message("ERREUR : Le groupe n'est pas d�fini !") ;
    return FALSE;
  }
  
  $sql_eleves = "SELECT ut.nom, ut.prenom, ut.login, el.elenoet FROM eleves ut, j_eleves_groupes gr, eleves el
	       WHERE gr.id_groupe = '".$_SESSION[PREFIXE]['id_groupe_session']."'
		 AND gr.login = ut.login
		 AND gr.login = el.login
		 AND gr.periode = '".$_SESSION[PREFIXE]['periode_num']."'
	       ORDER BY ut.nom, ut.prenom
	;";
  $query_eleves = mysql_query($sql_eleves);
  if(0 != mysql_num_rows($query_eleves)) {	
    while ($row = mysql_fetch_array($query_eleves, MYSQL_ASSOC)) {
       $table_eleves[]=array('nom' => $row['nom'],
			     'prenom' => $row['prenom'],
			     'login' => $row['login'],
			     'elenoet' => $row['elenoet']);
    }
  }
  
  mysql_free_result($query_eleves);
  return $table_eleves ;
  
}

/** Renvoie un tableau de donn�es sur un groupe
 * 
 * Renvoie un tableau avec les champs id, name, description de la table 'groupes'
 * et un champ classes obtenu avec la fonction classe_groupe()
 * 
 * @param int Id du groupe
 * @return array|bool Le tableau de donn�es si le groupe existe, FALSE sinon
 * @see classe_groupe()
 */
function groupe_long($id_groupe){
  $sql="SELECT gr.* FROM groupes gr
              WHERE gr.id = '".$id_groupe."'
    ";
  $res_test=mysql_query($sql);
  if(mysql_num_rows($res_test)==1){
    $row=mysql_fetch_array($res_test);
    $classes=classe_groupe($id_groupe);
    $groupes=array('id'=> $id_groupe,
	'name'=> $row["name"], 
	'description'=> $row["description"], 
	'classes'=> $classes);
    return $groupes;
  }
  return FALSE;
}

/**Renvoie un tableau de donn�es sur un groupe
 * 
 * Renvoie un tableau avec les champs classe, nom_complet de la table 'classes',
 * le champ id_classe de la table 'j_groupes_classes' pour chaque classe du groupe
 * 
 * @param int Id du groupe
 * @return array|bool Le tableau de donn�es si le groupe existe, FALSE sinon
 */
function classe_groupe($id_groupe){
  $classes=array();
  $sql="SELECT cl.classe, cl.nom_complet, gr.id_classe
              FROM j_groupes_classes gr, classes cl
              WHERE gr.id_groupe = '".$id_groupe."'
		AND gr.id_classe = cl.id
        ORDER BY cl.classe
    ";
  $res_test=mysql_query($sql);
  if (mysql_num_rows($res_test)!=0) {
    while($row=mysql_fetch_array($res_test)) {
      $classes[]=$row; 
    } 
    return $classes;
  }
  return FALSE;
}

/* ===== Conteneurs ===== */

/** Renvoie tous les conteneurs d'un groupe
 * 
 * Combine les fonctions cahier_notes_object() et toutes_matieres_cnotes() 
 * pour construire l'arborescence des enseignements d'un groupe
 * 
 * @return array|bool Tableau d'objets de tous les sous-conteneurs du conteneur, FALSE si aucun conteneur
 * @see cahier_notes_object()
 * @see toutes_matieres_cnotes()
 * @see cree_carnet_notes()
 */
function conteneurs() {
  
  // d�terminer le cahier de texte 
  $cn_cahier_texte = cahier_notes_object(); 
  if (!$cn_cahier_texte) {
    // Le cahier de texte n'existe pas on le cr�e
    $cn_cahier_texte = cree_carnet_notes($_SESSION[PREFIXE]['id_groupe_session']);
    if (!$cn_cahier_texte) {
      return FALSE;
    } else {
      $cn_cahier_texte = cahier_notes_object(); 
    }
  }
  
  // trouver les conteneurs qui ont pour id_racine le cahier de texte
  $matieres = toutes_matieres_cnotes($cn_cahier_texte->id_cahier_notes); 
  if (!$matieres) {
    return FALSE;
  }
  return $matieres;
}


/** Retourne les donn�es de cn_cahier_notes
 * 
 * Retourne les donn�es de la table cn_cahier_notes dans un objet en utilisant 
 * $_SESSION[PREFIXE]['id_groupe_session'] et $_SESSION[PREFIXE]['periode_num'] 
 * pour s�lectionner les carnets de notes � utiliser
 * 
 * @return object|bool donn�es du cahier de texte, FALSE
 */
function cahier_notes_object() {
  $sql="SELECT cn.* FROM cn_cahier_notes cn
              WHERE cn.id_groupe = '".$_SESSION[PREFIXE]['id_groupe_session']."'
		AND cn.periode = '".$_SESSION[PREFIXE]['periode_num']."'
    ";
    
    $res_test=mysql_query($sql);
    if(mysql_num_rows($res_test)!=0){
      $cahier=mysql_fetch_object($res_test); 
      return $cahier ;
    }
  return FALSE;
}

/** Cr�e un carnet de notes pour un groupe
 * 
 * Cr�e un carnet de notes pour un groupe en enregistrant dans cn_cahier_notes un nouvel enregistrement
 * et en cr�ant un enregistrement dans cn_conteneurs
 * 
 * @return bool TRUE si le carnet de note est cr��, FALSE sinon
 * @see charge_message()
 * @see get_group()
 * 
 */
function cree_carnet_notes($id_groupe) {
  $current_group = get_group($id_groupe);
  $nom_complet_matiere = $current_group["matiere"]["nom_complet"];
  $nom_court_matiere = $current_group["matiere"]["matiere"];
  $reg = mysql_query("INSERT INTO cn_conteneurs SET id_racine='', nom_court='".$current_group["description"]."', nom_complet='".$nom_complet_matiere."', description = '', mode = '2', coef = '1.0', arrondir = 's1', ponderation = '0.0', display_parents = '0', display_bulletin = '1', parent = '0'");
  if ($reg) {
    $id_racine = mysql_insert_id();
    $reg = mysql_query("UPDATE cn_conteneurs SET id_racine='$id_racine', parent = '0' WHERE id='$id_racine'");
    $_SESSION[PREFIXE]['id_racine'] = $id_racine ;
		if ($reg) {
			$reg = mysql_query("INSERT INTO cn_cahier_notes SET id_groupe = '$id_groupe', periode = '".$_SESSION[PREFIXE]['periode_num']."', id_cahier_notes='$id_racine'");
			if ($reg) {
				return (TRUE);
			} else  {
				charge_message("�chec lors de la cr�ation du carnet de notes dans la base");
				return FALSE;
			}
		} else  {
		  charge_message("�chec de la cr�ation du carnet de notes dans la base lors de la mise � jour du conteneur");
		  return FALSE;
		}
  } else  {
    charge_message("�chec de la cr�ation du carnet de notes dans la base lors de la cr�ation du conteneur");
    return FALSE;
  }
}

/** retourne les mati�res du cahier de texte
 * 
 * Retourne les donn�es de la table cn_conteneurs dans un tableau
 * 
 * @param $int Id du conteneur racine
 * @return array Tableau d'objets de tous les sous-conteneurs du conteneur
 */
function toutes_matieres_cnotes($conteneur) {
  $matieres=array(); 
  $sql="SELECT co.* FROM cn_conteneurs co
              WHERE co.id_racine = '".$conteneur."'
		ORDER BY co.parent
    ";
  
    $res_test=mysql_query($sql);
    if(mysql_num_rows($res_test)!=0){
      while($row=mysql_fetch_object($res_test)) {
	$matieres[]=$row;
      }
      return $matieres;
    }
  return FALSE;
}

/* ===== Trimestres ===== */

/** Retourne les donn�es du trimestre
 * 
 * Retourne les champs de la table 'periodes' dans un objet, pour un trimestre,
 * en utilisant classe_groupe($_SESSION[PREFIXE]['id_groupe_session']) 
 * et $_SESSION[PREFIXE]['periode_num'] par d�faut
 * 
 * @param int Id du trimestre
 * @param int Id du groupe
 * @return object $row Les donn�es du trimestre
 * @see classe_groupe()
 */
function nom_trimestre($id_trim = FALSE, $id_classe = FALSE) {
  if (!$id_trim) $id_trim =$_SESSION[PREFIXE]['periode_num'];
  if (!$id_classe) {
    $classe=classe_groupe($_SESSION[PREFIXE]['id_groupe_session']);
    $id_classe = $classe[0]['id_classe'];
  }
  $classe=classe_groupe($_SESSION[PREFIXE]['id_groupe_session']);
  $sql="SELECT * FROM periodes
              WHERE id_classe = '".$id_classe."'
		AND num_periode = '".$id_trim."'
    ";
  $res_test=mysql_query($sql);
  if (mysql_num_rows($res_test)!=0) {
    $row=mysql_fetch_object($res_test);
    return $row;
  }
  return FALSE;
}

/** D�termine la p�riode active
 * 
 * Si $_POST["periode_num"] ou $_GET["periode_num"] sont renseign�s, 
 * les mets dans $_SESSION[PREFIXE]['periode_num']
 * 
 * Utilise $_SESSION[PREFIXE]['periode_num'] pour renvoyer la p�riode active
 * 
 * @return int Le num�ro de la p�riode 
 */
function recupere_periode_active() {
  $id_periode = isset($_POST["periode_num"]) ? $_POST["periode_num"] : (isset($_GET["periode_num"]) ? $_GET["periode_num"] : NULL);
  
  if (!$id_periode) {
    if (isset ($_SESSION[PREFIXE]['periode_num'])) {
      $id_periode = $_SESSION[PREFIXE]['periode_num'];
    }
  }
  $_SESSION[PREFIXE]['periode_num']=$id_periode;
  
  return $id_periode;
}

/** Renvoie les p�riodes d'un groupe
 * 
 * Renvoie un tableau de toutes les p�riodes d'un groupe
 * 
 * Chaque ligne contient les champs  periode_num, periode_nom, periode_close
 *
 * @param array un groupe format� avec recupere_groupe_actif()
 * @return array Les donn�es de la p�riode
 * @see recupere_groupe_actif()
 */
function recupere_periodes($current_group){
  $retour=array();
  $i="1";

  // On v�rifie si la p�riode est ouverte
  while ($i < ($current_group["nb_periode"])) {
    $periode_close=FALSE;
    $sql="SELECT * FROM periodes WHERE num_periode='$i' 
		   AND id_classe='".$current_group["classes"]["list"][0]."' 
		   AND verouiller='N'";
    $res_test=mysql_query($sql);
    if(mysql_num_rows($res_test)==0){
      $periode_close=TRUE ;
    }
    
    $retour[]=array("periode_num" => $i, 
		    "periode_nom" => ucfirst($current_group["periodes"][$i]["nom_periode"]),
		    "periode_close" => $periode_close) ;
    $i++;
  } 
  return $retour;
}

/**Supprime $_SESSION[PREFIXE]['id_devoir'], $_SESSION[PREFIXE]['id_racine'], $_SESSION[PREFIXE]['tableau_notes']
 * 
 */
function efface_notes_session() {
  unset ($_SESSION[PREFIXE]['id_devoir']);
  unset ($_SESSION[PREFIXE]['id_racine']);
  unset ($_SESSION[PREFIXE]['tableau_notes']);
}

/**Charge un message dans $_SESSION[PREFIXE]['tbs_msg']
 * 
 * Ajoute un texte � $_SESSION[PREFIXE]['tbs_msg'] pour qu'il soit afficher dans la zone de messages 
 * en haut de la page
 *
 * @param text Le texte � affich� dans la zone message 
 */
function charge_message($ajoute_message) {
  if (isset ($_SESSION[PREFIXE]['tbs_msg']) && $_SESSION[PREFIXE]['tbs_msg']!="") {
    $_SESSION[PREFIXE]['tbs_msg'] .= "<br />" ;
  } else if (!isset ($_SESSION[PREFIXE]['tbs_msg'])) {
    $_SESSION[PREFIXE]['tbs_msg']="" ;
  }
  $_SESSION[PREFIXE]['tbs_msg'] .= $ajoute_message ;
  
}

/** Pr�pare le texte pour �tre pass� dans une requ�te SQL
 *
 * @param textarea $val Le texte � v�rifier
 * @return textarea Le texte format�
 */
function prepare_sql($val) {
  $texte = corriger_caracteres($val);
  
  return $texte;
}

?>
