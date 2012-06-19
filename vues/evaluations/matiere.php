<?php
/** Vue du module evaluations : action matiere
 * 
 * Création de sous-conteneurs
 * 
 * @author Régis Bouguin
 * @package arborescence
 * @subpackage matiere
 * @todo Créer un bouton pour ajouter Autocomplete et pouvoir choisir tout, numérique, rien
 * 
 */

?>
<div id="container">
  <a name='contenu'></a>
  
  <h2>Ajouter/modifier un<?php if (getSettingValue('gepi_denom_boite_genre')=='f') echo "e" ?>
    <?php echo getSettingValue('gepi_denom_boite'); ?></h2>

  <h3 class="center">
    <?php 
    foreach ($classes_groupe['classes'] as $classe) {
      echo $classe['classe']." - ";
    } ?>
     <?php echo htmlentities(stripslashes($classes_groupe['description'])); ?>
    -
    <?php echo nom_trimestre($_SESSION[PREFIXE]['periode_num'])->nom_periode; ?> 
  </h3>
  
  <form enctype="multipart/form-data" action="index.php" id="form_nom" method="post">
    
    <?php if ($affiche_conteneur["id_conteneur"]) {?>
      <input type="hidden" 
	     name="id_conteneur" 
	     value='<?php echo $affiche_conteneur["id_conteneur"]; ?>' />
    <?php } ?> 

    <fieldset>
      <legend>Général</legend>
      
      <label for="nom_eval">* Nom court : </label>
      <input type="text" 
	     name="nom" 
	     id="nom_eval" 
	     value="<?php echo $affiche_conteneur['nomCourt']; ?> "
	     style="width: 20em;"
	     onfocus='javascript:this.select()' />
      
      <label for="nomComplet">* Nom complet : </label>
      <input type="text" 
	     name="nomComplet" 
	     id="nomComplet" 
	     value="<?php echo $affiche_conteneur['nomComplet']; ?>" 
	     style="width: 40em;"
	     onfocus='javascript:this.select()' />
      
      <br />
      
      <label for="emplacement">* Emplacement :</label>
      <select name="emplacement" id="emplacement">
	<!-- <option value=''>Sélectionnez un emplacement</option> -->
      <?php
      /* */
      foreach ($sous_matieres as $conteneur) {
      ?>
	<option value='<?php echo $conteneur->id; ?>'
	  <?php if ($affiche_conteneur['emplacement'] == $conteneur->id) { ?>
	    selected='selected'
	  <?php } ?>>
	  <?php echo htmlentities($conteneur->nom_complet); ?>
	  
	</option>
      <?php
      }
      /* */
      ?>
      </select>

      <br />

      <label for="evalDescription">Description : </label>
      <textarea id="evalDescription"
		  name="description"
		  lang="fr"
		  rows ="2"
		  cols="80"
		  style ="vertical-align: middle; width: 80%;"
		  ><?php echo $affiche_conteneur['description']; ?></textarea>
      
      <p>
	* Coefficient de l<?php if (getSettingValue('gepi_denom_boite_genre')=='f') {
	  echo "a" ;
	} else {
	  echo "e" ;
	} ?>
    <?php echo getSettingValue('gepi_denom_boite'); ?>
	<img src="<?php echo CHEMIN_IMAGES; ?>icons/ico_question_petit.png" 
	     alt="" 
	     title="Précisions" 
	     onclick ="cache_montre_aide_coef();"/>
	<span id="coef_long1">
	  <br />
	  Valeur de la pondération dans le calcul de la moyenne :
	</span>
	<input type="text" 
	       name="coefCont" 
	       id="coefCont1" 
	       value="<?php echo $affiche_conteneur['coefficient']; ?>" 
	       style="width: 4em; text-align: center" />
	<span id="coef_long2">
	<br />
	<em>(si 0, la note de <span class='gras'><?php echo $affiche_conteneur['nomCourt']; ?></span> n'intervient pas dans le calcul de la moyenne)</em>
	</span>
      </p> 
      
      <p class="center">
	<input type="submit" name="mode" value="<?php echo ENREGISTRER; ?>" />
	<input type="submit" name="mode" value="<?php echo ABANDONNER; ?>" />
      </p>
      
      <p class="center rouge">
	Les champs précédés d'un * sont obligatoires
      </p>
      
    </fieldset>
    
    <fieldset>
      <legend>Moyenne</legend>
      
      <label for="arrondi">* Précision du calcul de la moyenne :</label>
      <select name="arrondi" id="arrondi">
	<option value='<?php echo DIXIEME_SUP; ?>'<?php if ($affiche_conteneur['arrondir']==DIXIEME_SUP) echo " selected='selected'"; ?>>
	  Arrondir au dixième de point supérieur
	</option>
	<option value='<?php echo DEMI_SUP; ?>'<?php if ($affiche_conteneur['arrondir']==DEMI_SUP) echo " selected='selected'"; ?>>
	  Arrondir au demi-point supérieur
	</option>
	<option value='<?php echo POINT_SUP; ?>'<?php if ($affiche_conteneur['arrondir']==POINT_SUP) echo " selected='selected'"; ?>>
	  Arrondir au point entier supérieur
	</option>
	<option value='<?php echo DIXIEME_PROCHE; ?>'<?php if ($affiche_conteneur['arrondir']==DIXIEME_PROCHE) echo " selected='selected'"; ?>>
	  Arrondir au dixième de point le plus proche
	</option>
	<option value='<?php echo DEMI_PROCHE; ?>'<?php if ($affiche_conteneur['arrondir']==DEMI_PROCHE) echo " selected='selected'"; ?>>
	  Arrondir au demi-point le plus proche
	</option>
	<option value='<?php echo POINT_PROCHE; ?>'<?php if ($affiche_conteneur['arrondir']==POINT_PROCHE) echo " selected='selected'"; ?>>
	  Arrondir au point entier le plus proche
	</option>
      </select>
      
      
      <p>
	Pondération
	<img src="<?php echo CHEMIN_IMAGES; ?>icons/ico_question_petit.png" 
	     alt="" 
	     title="Précisions" 
	     onclick ="cache_montre_aide_calcul();"/>
	<span id="aide_calcul_note">
	  <br />
	  <em>
	  Pour chaque élève, le coefficient de la meilleure note de 
	  <strong><?php echo $affiche_conteneur['nomCourt']; ?></strong>
	  augmente ou diminue de :
	  </em>
	</span>
	<input type="text" 
	       name="ponderation" 
	       id="ponderation" 
	       value="<?php echo $affiche_conteneur['ponderation']; ?>" 
	       style="width: 4em; text-align: center" />
      </p> 
	
      <p>
	* Mode de calcul
	<img src="<?php echo CHEMIN_IMAGES; ?>icons/ico_question_petit.png" 
	     alt="" 
	     title="Précisions" 
	     onclick ="cache_montre_aide_mode();"/>
	<br />
	<input type='radio' 
	       name='mode_calcul' 
	       id='mode_2' 
	       value='2' 
	       <?php if ($affiche_conteneur['mode_calcul'] == 2) echo 'checked = "checked"'; ?>/>
	Tenir compte des options des <?php echo getSettingValue('gepi_denom_boite'); ?>s
	<em id="mode_calcul_2">
	<br />
	  la moyenne s'effectue sur toutes les notes contenues à la racine de
	  <?php echo 'Nom de la boite'; ?> et 
	  sur les moyennes des <?php echo getSettingValue('gepi_denom_boite'); ?>s, 
	  en tenant compte des options dans celles-ci.
	</em>
	<br />
	<input type='radio' 
	       name='mode_calcul' 
	       id='mode_1' 
	       value='1'
	       <?php if ($affiche_conteneur['mode_calcul'] == 1) echo 'checked = "checked"'; ?>/>
	Ne pas tenir compte des options des <?php echo getSettingValue('gepi_denom_boite'); ?>s
	<em id="mode_calcul_1">
	<br />
	  la moyenne s'effectue sur toutes les notes contenues dans 
	  <?php echo 'Nom de la boite'; ?> et dans ses 
	  <?php echo getSettingValue('gepi_denom_boite'); ?>s, sans tenir compte des options 
	  définies dans cette celles-ci.
	</em>
      
      </p> 
      
    </fieldset>
    
        
    <fieldset>
      <legend>Affichage sur les relevés</legend>
      <p>
	
	<input type="checkbox" 
	       name="noteSurReleve" 
	       id="noteSurReleve" 
	       value="1"
	       <?php if ($affiche_conteneur['noteSurReleve']) echo ' checked="checked"'; ?> />
	Faire apparaître la moyenne sur le relevé de notes destiné aux parents 
	
	<br />
	
	<input type="checkbox" 
	       name="noteSurBulletin" 
	       id="noteSurBulletin" 
	       value="1"
	       <?php if ($affiche_conteneur['noteSurBulletin']) echo ' checked="checked"'; ?> />
	Faire apparaître la moyenne sur le bulletin scolaire.
	<br />
	<em>
	  Si la case ci-dessus est cochée, la moyenne de cette sous-matière apparaît sur le bulletin scolaire, en plus de la moyenne générale, à titre d'information.
	</em>
	
      </p>

    </fieldset>
      
    <p><?php echo add_token_field(TRUE); ?></p>
  </form>  
  
</div>

<script type="text/javascript">
 //<![CDATA[ 
   cache_montre_aide_mat();
 //]]>
</script>
  