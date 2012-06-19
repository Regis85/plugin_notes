<?php
/** Modèle du module evaluations : action voir 
 * 
 * Affichage de l'arborescence des boites et des évaluations
 * 
 * @package arborescence
 * @subpackage voir
 * @author Régis Bouguin
 * 
 */

/** Renvoie l'Id du groupe actif
 * 
 * Récupère l'Id du groupe s'il est passé en $_POST["id_groupe"] ou $_GET["id_groupe"]
 * et le met en $_SESSION[PREFIXE]['id_groupe_session']
 * 
 * Vide le tableau de notes si on change de groupe avec efface_notes_session()
 * 
 * @return int NULL si aucun groupe, GROUPE_INTERDIT (-1) si le prof n'a pas de droit dessus, l'id du groupe sinon
 * @see efface_notes_session()
 * 
 */
function traite_groupe(){
  
  $id_groupe = isset($_POST["id_groupe"]) ? $_POST["id_groupe"] : (isset($_GET["id_groupe"]) ? $_GET["id_groupe"] : NULL);
  if ($id_groupe == "no_group") {
    $id_groupe = NULL;
    $_SESSION[PREFIXE]['id_groupe_session'] = "";
    efface_notes_session();
    return $id_groupe;
  }
   
  // Si on change de groupe, on vide le tableau de devoirs // 
  if (isset ($_SESSION[PREFIXE]['id_groupe_session']) && $id_groupe && $_SESSION[PREFIXE]['id_groupe_session'] != $id_groupe) {
   efface_notes_session();
  }
  // on met le groupe dans la session, pour naviguer entre absence, cahier de texte et autres
  if ($id_groupe != NULL) {
      $_SESSION[PREFIXE]['id_groupe_session'] = $id_groupe;
  } else if (isset($_SESSION[PREFIXE]['id_groupe_session']) && $_SESSION[PREFIXE]['id_groupe_session'] != "") {
       $id_groupe = $_SESSION[PREFIXE]['id_groupe_session'];
  }
    
  if (is_numeric($id_groupe) && $id_groupe > 0) {
    // on vérifie que le prof a accès au groupe
    $sql="SELECT 1=1 FROM j_groupes_professeurs WHERE id_groupe='$id_groupe' AND login='".$_SESSION['login']."';";
    $test_prof_groupe=mysql_query($sql);
    if(mysql_num_rows($test_prof_groupe)==0) {
      $id_groupe=GROUPE_INTERDIT;
    }

  } else {
    $id_groupe = NULL;
  }
  
  return $id_groupe;

}

/** Renvoie les enseignements d'un groupe
 * 
 * Récupère les informations des groupes d'un enseignant à partir de $_SESSION['login']
 * en utilisant get_groups_for_prof() et met en forme la description
 *
 * @return array Le tableau issu de get_groups_for_prof()
 * @see get_groups_for_prof()
 */ 
function recupere_tous_groupes() {
  $groupes = get_groups_for_prof($_SESSION['login'],"classe puis matière") ;
  foreach ($groupes as &$matiere) {
    $matiere["description"] = htmlentities($matiere["description"],ENT_COMPAT) ;
  }
  return $groupes ;
}

/** Renvoie l'arborescence du groupe
 * 
 * Construit l'arborescence du carnet de notes pour le groupe en $_SESSION[PREFIXE]['id_groupe_session'] 
 * et la période $_SESSION[PREFIXE]['periode_num']
 * 
 * Charge l'Id du carnet de notes dans $_SESSION[PREFIXE]['id_racine']
 * 
 * Utilise les fonctions sous_modules() pour récupérer les boites incluses
 * et eval_conteneur() pour récupérer les évaluations
 *
 * @param recupere_periodes
 * @return array Le tableau organisé des boites et évaluations 
 * @see recupere_periodes()
 * @see sous_modules()
 * @see eval_conteneur()
 */
function eval_dispo($periodes) {
  
  $conteneur=array();
  
  // On récupère le conteneur du trimestre
  $sql="SELECT co.id , co.nom_court , co.nom_complet 
	           FROM cn_cahier_notes cn, cn_conteneurs co
                   WHERE cn.periode='".$_SESSION[PREFIXE]['periode_num']."' 
		      AND cn.id_groupe='".$_SESSION[PREFIXE]['id_groupe_session']."' 
		      AND co.id=cn.id_cahier_notes
	           ORDER BY co.nom_complet " ;
  $res_test=mysql_query($sql);
  /* *
   * Si on a un conteneur
  /* */ 
  if(mysql_num_rows($res_test)!=0){
  /* *
   * Si la période est close ne récupérer que le conteneur du trimestre
  /* */
      $nom_complet = htmlentities(mysql_result($res_test, 0, 'co.nom_complet'),ENT_COMPAT);
      $nom_court= htmlentities(mysql_result($res_test, 0, 'co.nom_court'),ENT_COMPAT);
    if ($periodes[$_SESSION[PREFIXE]['periode_num']-1]['periode_close']) {     
      $conteneur[]=array('id'=>mysql_result($res_test, 0, 'co.id'),
			 'nom_complet'=>$nom_complet,
	                 'nom_court'=>$nom_court, 
			 'sous_conteneur'=>array(),
			 'evaluation'=>array(),
			 'close'=>TRUE);

  /* sinon récupérer les sous-conteneurs et les évaluations */
    } else {
      $id = mysql_result($res_test, 0, 'co.id');
      $nom_complet = htmlentities(mysql_result($res_test, 0, 'co.nom_complet'),ENT_COMPAT);
      $nom_court= htmlentities(mysql_result($res_test, 0, 'co.nom_court'),ENT_COMPAT);
      $sous_elements=sous_modules($id);
      $evaluations=eval_conteneur($id);
      $conteneur[]=array('id' => $id,
			 'nom_complet'=>$nom_complet,
	                  'nom_court'=>$nom_court,
			 'sous_conteneur'=>$sous_elements,
			 'evaluation'=>$evaluations,
			 'close'=>FALSE);
      $_SESSION[PREFIXE]['id_racine'] = $id ;
    }
  }
  return $conteneur;
}

/** Récupère les sous-conteneurs d'un conteneur
 * 
 * Renvoie un tableau organisé pour représenté l'arborescence du conteneur 
 * en s'appelant récursivement puis en appelant eval_conteneur()
 *
 * @param int Id du conteneur parent
 * @return array Le tableau représentant le conteneur
 * @see eval_conteneur()
 */
function sous_modules($id) {
  $sous_conteneur=array();
  $sql="SELECT co.* 
	       FROM cn_conteneurs co
	       WHERE co.parent='".$id."'
	       ORDER BY co.nom_complet " ;
  
  $res_test=mysql_query($sql);
  
  if(mysql_num_rows($res_test)!=0){
    
   while($row=mysql_fetch_array($res_test)) {
            
      $id = $row['id'];
      $nom_complet = htmlentities($row['nom_complet'],ENT_COMPAT);
      $nom_court= htmlentities($row['nom_court'],ENT_COMPAT);
      $sous_elements=sous_modules($row['id']);
      $evaluations=eval_conteneur($id);
      $coef=$row['coef'];
      $display_parents=$row['display_parents'];
      $display_bulletin=$row['display_bulletin'];
      
      $sous_conteneur[]=array('id' => $id,
			      'nom_complet'=>$nom_complet, 
	                      'nom_court'=>$nom_court,
			      'sous_conteneur'=>$sous_elements,
			      'evaluation'=>$evaluations,
			      'coef'=>$coef,
			      'display_parents'=>$display_parents,
			      'display_bulletin'=>$display_bulletin,
			      'close'=>FALSE);
   }
    
  }
  
  return $sous_conteneur;
  
}

/** Renvoie les évaluations d'un conteneur
 *
 * Renvoie tous les champs de la table cn_devoirs dans un tableau pour un conteneur
 * 
 * @param int Id du conteneur
 * @return array Les données de l'évaluation 
 */
function eval_conteneur($id) {
  $evaluations=array(); 
  $sql="SELECT ev.*
	       FROM  cn_devoirs ev
	       WHERE ev.id_conteneur='".$id."'" ;
  
  $res_test=mysql_query($sql);
  
  if(mysql_num_rows($res_test)!=0){
    
    $nb_eleves = 0;
    
   while($row=mysql_fetch_array($res_test)) {
     $id_eval = $row['id'];
     $nom_complet = $row['nom_complet'];
     $nom_court = $row['nom_court'];
     $id_conteneur = $row['id_conteneur'];
     $id_racine = $row['id_racine'];
     $display_parents = $row['display_parents'];
     $coef = $row['coef'];
     // Nombre de notes
     $sql_notes = "SELECT 1=1
             FROM  cn_notes_devoirs
	     WHERE id_devoir = '".$id_eval."'
	       AND (statut != 'v'
	         AND statut != '-')" ;
     $res_test_notes = mysql_query($sql_notes);
     if ($res_test_notes) {
       $nb_notes = mysql_num_rows($res_test_notes) ;
     } else {
       $nb_notes = 0 ;
     }
     // Nombre d'élèves
     if (0 == $nb_eleves) {
       $sql_eleves = "SELECT DISTINCT el.*
	       FROM  j_eleves_groupes el , cn_cahier_notes no
	       WHERE el.id_groupe = no.id_groupe
		 AND (no.id_cahier_notes = '".$id_racine."'
		 AND el.periode = '".$_SESSION[PREFIXE]['periode_num']."')" ;     
       $res_test_eleves = mysql_query($sql_eleves);
       $nb_eleves = mysql_num_rows($res_test_eleves) ;
     }
  
     $evaluations[] = array('id' => $id_eval,
			    'nom_complet'=>$nom_complet, 
	                    'nom_court'=>$nom_court,
	                    'id_conteneur'=>$id_conteneur,
	                    'display_parents'=>$display_parents,
	                    'nb_notes'=>$nb_notes,
	                    'nb_eleves'=>$nb_eleves,
	                    'coef'=>$coef);
   }
  } 
  return $evaluations;
}

/** Construit les liens vers les bulletins et les évaluations de l'année
 *
 * @return array un tableau titre text, adresse lien, autre text
 * @see moyenne_existe
 * @see appreciation_existe()
 * @see ouverte()
 */
function liens_externes(){
  $liens=array();
  if (isset ($_SESSION[PREFIXE]['id_groupe_session'])){
    $liens[]=array("titre" => "Voir toutes les évaluations de l'année",
	         "adresse" => CHEMIN_RACINE.TOUTES_LES_NOTES."?id_groupe=".$_SESSION[PREFIXE]['id_groupe_session']);
    if (isset ($_SESSION[PREFIXE]['periode_num']) && ouverte($_SESSION[PREFIXE]['periode_num'])){
      $parametres="id_groupe=".$_SESSION[PREFIXE]['id_groupe_session']."&amp;periode_cn=".$_SESSION[PREFIXE]['periode_num'];
      
      $moyenne_vide = !moyenne_existe($_SESSION[PREFIXE]['periode_num']) ? "actuellement vide" : NULL ;
      $appreciation_vide = !appreciation_existe($_SESSION[PREFIXE]['periode_num']) ? "actuellement vide" : NULL ;
      
      $liens[]=array("titre" => "Saisie des moyennes trimestrielles",
 	             "adresse" => CHEMIN_RACINE.SAISIE_MOYENNES."?".$parametres,
	             "autre" => $moyenne_vide);
      $liens[]=array("titre" => "Saisie des appréciations trimestrielles",
 	             "adresse" => CHEMIN_RACINE.SAISIE_APPRECIATION."?".$parametres,
	             "autre" => $appreciation_vide);
    }
  }
  return $liens;
}

/** Vérifie si une moyenne existe pour une période
 * 
 * Vérifie dans matieres_notes si une moyenne existe pour une période 
 * et le groupe contenu dans $_SESSION[PREFIXE]['id_groupe_session']
 *
 * @param int Le numéro de la période
 * @return bool TRUE si la période existe, FALSE sinon
 */
function moyenne_existe($num_periode){
  if (isset ($_SESSION[PREFIXE]['id_groupe_session'])){
    $sql="SELECT * 
            FROM matieres_notes
	    WHERE `id_groupe` = '".$_SESSION[PREFIXE]['id_groupe_session']."'
            AND `periode` = '".$num_periode."'
      ";
    $res_test=mysql_query($sql);
    if(mysql_num_rows($res_test)!=0){
      return TRUE ;
    }
  }
  return FALSE;
}

/** Vérifie si une appréciation existe pour une période
 * 
 * Vérifie dans matieres_appreciations si une appréciation existe pour une période 
 * et le groupe contenu dans $_SESSION[PREFIXE]['id_groupe_session']
 *
 * @param int $num_periode
 * @return bool TRUE si la période existe, FALSE sinon
 */
function appreciation_existe($num_periode){
  if (isset ($_SESSION[PREFIXE]['id_groupe_session'])){
    $sql="SELECT * 
            FROM matieres_appreciations
	    WHERE `id_groupe` = '".$_SESSION[PREFIXE]['id_groupe_session']."'
            AND `periode` = '".$num_periode."'
      ";
    $res_test=mysql_query($sql);
    if(mysql_num_rows($res_test)!=0){
      return TRUE ;
    }
  }
  return FALSE;
}

/** Vérifie si une période est ouverte
 * 
 * Vérifie dans la base si une période est ouverte
 * pour le groupe contenu dans $_SESSION[PREFIXE]['id_groupe_session']
 *
 * @param int $num_periode
 * @return bool TRUE si la période existe, FALSE sinon
 */
function ouverte($num_periode){
  $id_groupe = $_SESSION[PREFIXE]['id_groupe_session']; //pas bon
  $sql="SELECT * FROM periodes p, j_groupes_classes cl
                 WHERE p.num_periode='".$num_periode."' 
		   AND p.verouiller='N' 
		   AND p.id_classe=cl.id_classe
		   AND cl.id_groupe='".$_SESSION[PREFIXE]['id_groupe_session']."'" ;
    $res_test=mysql_query($sql);
    if(mysql_num_rows($res_test)!=0){
      return TRUE ;
    }
  return FALSE;
}

?>