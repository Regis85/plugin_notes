<?php

/** Modèle du module evaluations : action modifier
 * 
 * Modifier le trimestre ou le groupe actif
 * 
 * @author Régis Bouguin
 * @package arborescence
 * @subpackage modifier
 */

/** Change le trimestre actif pour une valeur passée en POST ou en GET
 *
 * @param array $valeur_POST
 * @param array $valeur_GET
 * @return bool TRUE si le trimestre à été changé, 
 */
function regle_trimestre($valeur_POST, $valeur_GET) {
  $trimestre = !empty($valeur_POST['periode_num']) ? $valeur_POST['periode_num'] : ((!empty($valeur_GET['periode_num'])) ? $valeur_GET['periode_num'] : NULL) ;
  if ($trimestre) {
    $_SESSION[PREFIXE]['periode_num'] = $trimestre;
    return TRUE;
  }
  return FALSE;
}

/** Change le groupe actif pour une valeur passée en POST ou en GET
 *
 * Si le groupe change, on vide aussi le tableau de note
 * 
 * @param array $valeur_POST
 * @param array $valeur_GET
 * @return bool TRUE si le trimestre à été changé
 * @see efface_notes_session()
 * 
 */
function regle_groupe($valeur_POST, $valeur_GET) {
  $groupe =!empty($valeur_POST['id_groupe']) ? $valeur_POST['id_groupe'] : ((!empty($valeur_GET['id_groupe'])) ? $valeur_GET['id_groupe'] : NULL) ;
  if ($groupe){ 
    // On vérifie si on change de groupe et on vide les tableaux
  if (isset ($_SESSION[PREFIXE]['id_groupe_session']) && $_SESSION[PREFIXE]['id_groupe_session'] != $groupe) {
   efface_notes_session();
  }
       

    
    
    
    $_SESSION[PREFIXE]['id_groupe_session'] = $groupe ;
    return TRUE;
  }
  return FALSE;
}



?>
