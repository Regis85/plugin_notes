<?php   
/** Vue du module evaluations : action ajoute
 * 
 * Création d'une évaluation
 * 
 * @author Régis Bouguin
 * @package arborescence
 * @subpackage ajoute
 * @todo Gérer Créer le même devoir pour d'autres enseignements
 * @todo Créer un bouton pour ajouter Autocomplete et pouvoir choisir tout, numérique rien
 * 
 */
 

?>
<div id="container">
  <a name='contenu'></a>

  <h2>Ajouter/modifier une évaluation</h2>

  <h3 class="center">
    <?php
    foreach ($classes_groupe['classes'] as $classe) {
      echo $classe['classe']." - ";
    }?>
     <?php echo htmlentities(stripslashes($classes_groupe['description']));?>
    -
    <?php echo nom_trimestre($_SESSION[PREFIXE]['periode_num'])->nom_periode;?> 
  </h3>


  <a id="contenu" class="invisible">Début de la page</a>

  <form enctype="multipart/form-data" action="index.php" id="form_nom" method="post">
    <fieldset>
      <legend>Général</legend>
      
      <input type="hidden" name="action" value="ajoute" />
      <input type="hidden" name="creation" value="evaluation" />
      
    <?php if ($affiche_eval["id_eval"]) {?>
      <input type="hidden" name="id_eval" value='<?php echo $affiche_eval["id_eval"]; ?>' />
    <?php } ?> 
      <label for="nom_eval">* Nom court : </label>
      <input type="text" 
	     name="nom" 
	     id="nom_eval" 
	     value='<?php echo $affiche_eval["nom"]; ?>' 
	     style="width: 20em;"
	     onfocus='javascript:this.select()' />
      <label for="nomComplet">* Nom complet : </label>
      <input type="text" 
	     name="nomComplet" 
	     id="nomComplet" 
	     value="<?php echo $affiche_eval['nomComplet']; ?>" 
	     style="width: 40em;"
	     onfocus='javascript:this.select()' />
      <br />
      <label for="emplacement">* Emplacement :</label>
      <select name="emplacement" id="emplacement">
	<!-- <option value=''>Sélectionnez un emplacement</option> -->
      <?php
      foreach ($sous_matieres as $conteneur) {
      ?>
	<option value='<?php echo $conteneur->id; ?>'
	  <?php if ($affiche_eval['emplacement'] == $conteneur->id) { ?>
	    selected='selected'
	  <?php } ?>>
	  <?php echo htmlentities($conteneur->nom_complet); ?>
	  
	</option>
      <?php
      }
      ?>
      </select>

      <br />

      <label for="evalDescription">Description : </label>
      <textarea id="evalDescription"
		  name="evalDescription"
		  lang="fr"
		  rows ="2"
		  cols="80"
		  style ="vertical-align: middle; width: 80%;"
		  ><?php echo $affiche_eval['evalDescription']; ?></textarea>
      <p>
	* Coefficient de l'évaluation
	<img src="<?php echo CHEMIN_IMAGES; ?>icons/ico_question_petit.png" 
	     alt="Pas d'image en <?php echo CHEMIN_IMAGES; ?>icons/ico_question_petit.png" 
	     title="Précisions" 
	     onclick ="cache_montre_aide_coef();"/>
	<span id="coef_long1">
	  <br />
	  <label for="coefEval1">Valeur de la pondération dans le calcul de la moyenne :</label>
	</span>
	<input type="text" 
	       name="coefEval" 
	       id="coefEval1" value="<?php echo $affiche_eval['coefEval']; ?>" 
	       style="width: 4em;"
	       onkeydown="clavier_3(this.id,event,0,10,0.5);" />
	<span id="coef_long2">
	<br />
	<em>(si 0, la note de l'évaluation n'intervient pas dans le calcul de la moyenne)</em>
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
      <legend>Dates</legend>
      <p>
	<label for="display_date">* Date de l'évaluation (format jj/mm/aaaa) :</label>
	<input type='text'
	       name = 'display_date'
	       id='display_date'
	       size='10'
	       value = "<?php setlocale (LC_TIME, 'fr_FR','fra'); echo trim(strftime("%d/%m/%Y ",$affiche_eval['display_date'])); ?>"
	       onkeydown="clavier_date(this.id,event);" />
	<a href="#calend"
	   onclick="window.open('<?php echo CHEMIN_RACINE; ?>lib/calendrier/pop.calendrier_id.php?frm=form_nom&amp;ch=display_date','calendrier','width=350,height=170,scrollbars=0').focus();">
	  <img src="<?php echo CHEMIN_RACINE; ?>lib/calendrier/petit_calendrier.gif"
	       alt="Petit calendrier" />
	</a>
	<img src="<?php echo CHEMIN_IMAGES; ?>icons/ico_question_petit.png" 
	     alt="Bouton d'affichage" 
	     title="Précisions" 
	     onclick ="cache_montre_aide_display();"/>
	<br />
	<em id="aide_display_date">
	C'est cette date qui est prise en compte pour l'édition 
	des relevés de notes à différentes périodes de l'année.
	</em>
      </p>
      <a name="calend"></a>
      <p>
	<label for="date_ele_resp">* Date de visibilité de l'évaluation pour les élèves et responsables (format jj/mm/aaaa) : </label>
	<input type='text' 
	       name='date_ele_resp' 
	       id='date_ele_resp' 
	       size='10' 
	       value="<?php setlocale (LC_TIME, 'fr_FR','fra'); echo trim(strftime("%d/%m/%Y ",$affiche_eval['date_ele_resp'])); ?>" 
	       onkeydown="clavier_date(this.id,event);" />
	<a href="#calend" onclick="window.open('<?php echo CHEMIN_RACINE; ?>lib/calendrier/pop.calendrier_id.php?frm=form_nom&amp;ch=date_ele_resp','calendrier','width=350,height=170,scrollbars=0').focus();">
	<img src="<?php echo CHEMIN_RACINE; ?>lib/calendrier/petit_calendrier.gif" 
	     alt="Petit calendrier" />
	</a>
	<img src="<?php echo CHEMIN_IMAGES; ?>icons/ico_question_petit.png" 
	     alt="bouton d'affichage" 
	     title="Précisions" 
	     onclick ="cache_montre_aide_parents();"/>
	<br />
	<em id="aide_visible_parent">
	  Remarque : Cette date permet de ne rendre la note visible qu'une fois que le devoir 
	  est corrigé en classe.
	</em>
      </p>
      
    </fieldset>
    
    <fieldset>
      <legend>Prise en compte des notes</legend>
      <p>
	<label for="noteSur">Note sur :</label>
	<input type="text" 
	       name="noteSur" 
	       id="noteSur" 
	       value="<?php echo $affiche_eval['noteSur']; ?>" 
	       style="width: 4em;"
	       onkeydown="clavier_3(this.id,event,0,100,1);" />
	<br />
	<input type="checkbox" 
	       name="noteSur20" 
	       id="noteSur20"
	<?php if ($affiche_eval['noteSur20']) { ?>
	  checked ="checked"
	<?php } ?>
	       style="width: 4em;" />
	<label for="noteSur20">Ramener la note sur 20 lors du calcul de la moyenne :</label>
	<img src="<?php echo CHEMIN_IMAGES; ?>icons/ico_question_petit.png" 
	     alt="bouton d'affichage" 
	     title="Précisions" 
	     onclick ="cache_montre_aide_calcul();"/>
	<br />
	<em id="aide_calcul_note">
	  Exemple avec 3 notes : 18/20 ; 4/10 ; 1/5
	  <br />
	  Case cochée : moyenne = 18/20 + 8/20 + 4/20 = 30/60 = 10/20
	  <br />
	  Case cochée : moyenne = (18 + 4 + 1) / (20 + 10 + 5) = 23/35  &asymp; 13,1/20
	</em>
      </p>
      
      <p>
	<label for="moyenne">Prise en compte dans la moyenne :</label>
	<select name="moyenne" id="moyenne">
	  <option value='O' <?php if ($affiche_eval['moyenne']=='O') {?>
	    selected="selected"
	  <?php } ?>>
	    La note de l'évaluation entre dans le calcul de la moyenne.
	  </option>
	  <option value='B' <?php if ($affiche_eval['moyenne']=='B') {?>
	    selected="selected"
	  <?php } ?>>
	    Seules les notes de l'évaluation supérieures à 10 entrent dans le calcul 
	    de la moyenne.
	  </option>
	  <option value='N' <?php if ($affiche_eval['moyenne']=='N') {?>
	    selected="selected"
	  <?php } ?>>
	    La note de l'évaluation n'entre dans le calcul de la moyenne que si elle améliore 
	    la moyenne.
	  </option>
	</select>
      </p>

    </fieldset>
    
    <fieldset>
      <legend>Affichage sur les relevés</legend>
      <p>
	<input type="checkbox" 
	       name="noteSurReleve" 
	       id="noteSurReleve" 
	       value="1"
	       <?php if ($affiche_eval['noteSurReleve']) echo ' checked="checked"'; ?> />
	<label for="noteSurReleve">Faire apparaître cette évaluation sur le relevé de notes de l'élève</label>
	
	<br />
	<input type="checkbox" 
	       name="appSurReleve" 
	       id="appSurReleve" 
	       value="1"
	       <?php if ($affiche_eval['appSurReleve']) echo ' checked="checked"'; ?> />
	<label for="appSurReleve">L'appréciation de l'évaluation est affichable sur le relevé de notes de l'élève</label>
	<br />
	<em>
	  (si l'option précédente a été validée)
	</em>
	
      </p>

    </fieldset>
    
 <!--   
    <fieldset>
      <legend>Sacoche</legend>
      <p>
      </p>

    </fieldset>  
 -->
    
    <p><?php echo add_token_field(TRUE); ?></p>
    
  </form>
   
 <!--     
  <form enctype="multipart/form-data" id="form_duplique" action="index.php" method="post">
    <fieldset>
      <legend>Recopie</legend>
      <p>
	<input type="hidden" name="mode" value="duplique" />
	<input type="hidden" name="action" value="ajoute" />
	<input type="submit" value="Créer le même devoir pour d'autres enseignements" />
      </p>
    </fieldset> 
  </form>  
 -->
    
  <script type="text/javascript">
   //<![CDATA[ 
     cache_montre_aide();
   //]]>
  </script>

</div>
