
<?php
/** Vue du module evaluations : action idem
 * 
 * Dupliquer l'organisation d'une p�riode
 * 
 * @author R�gis Bouguin
 * @package arborescence
 * @subpackage idem
 * 
 */

/** Affichage r�cursif des sous-conteneurs
 *
 * @param type $sous_eval 
 * @todo v�rifier si c'est diff�rent de affiche_sous_conteneur
 */
function vue_sous_conteneur($sous_eval) {?>
  <li>
    <?php echo $sous_eval['nom_court']; ?>
  </li> 
    <?php if (!empty($sous_eval['sous_conteneur'])){  ?>
  <li style="list-style-type: none;">
    <ul>
      <?php foreach($sous_eval['sous_conteneur'] as $sous_eval_2) {  ?>
	<?php vue_sous_conteneur($sous_eval_2)  ?>
      <?php } ?>
    </ul>
  </li> 
  <?php } ?> 
    <?php } 

?>
  
<div id="container">
  <a name='contenu'></a>

<h2>Copie de la structure du trimestre pr�c�dent.</h2>
  
   <!-- Carnet de notes -->
  <?php if (isset ($_SESSION[PREFIXE]['id_groupe_session'])) { ?>
  <p class="center">
      <?php echo $group_actif["classlist_string"]; ?>
      -
      <?php echo $group_actif["matiere"]["nom_complet"]; ?>
  <?php if ($_SESSION[PREFIXE]['periode_num']>0) { ?>
      -
      <?php echo $periodes[$_SESSION[PREFIXE]['periode_num']-1]['periode_nom']; ?>
  <?php } ?>
  </p>
  <?php } ?>

<p class="center">
  Vous avez demand� la recopie de la structure de <?php echo getSettingValue('gepi_denom_boite'); ?>s 
  de la p�riode pr�c�dente. 
</p>
<p class="center">
  Si des <?php echo getSettingValue('gepi_denom_boite'); ?>s existent d�j�, 
  <?php if (getSettingValue('gepi_denom_boite_genre')=='f') {
    echo "elles";
  } else {
    echo "ils";
  }
?> ne seront pas supprim�<?php if (getSettingValue('gepi_denom_boite_genre')=='f') echo "e"; ?>s.
</p>

<form enctype="multipart/form-data" action="index.php" id="form_nom" method="post">
  
  <fieldset>
    <legend>Cliquez sur <strong><?php echo ENREGISTRER ?></strong> pour valider</legend>
    
    <p class="center">
      <input type="submit" name="mode" value="<?php echo ENREGISTRER; ?>" />
      <input type="submit" name="mode" value="<?php echo ABANDONNER; ?>" />
    </p>
    
    <h3>Future structure</h3>
    

<ul>
    <?php vue_sous_conteneur($arborescence['futur']); ?>
</ul>
    
    <h3>Structure de la p�riode pr�c�dente</h3>
    
<ul>
    <?php vue_sous_conteneur($arborescence['precedent']); ?>
</ul>
    
    
    <h3>Structure de la p�riode actuelle</h3>
    
<ul>
    <?php vue_sous_conteneur($arborescence['actuel']); ?>
</ul>
    
  </fieldset>
  
  <p><?php echo add_token_field(TRUE); ?></p>

</form> 

</div>