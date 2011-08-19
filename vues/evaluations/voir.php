<?php 
/** Vue du module evaluations : action voir 
 * 
 * Affichage de l'arborescence des boites et des évaluations
 * 
 * @package arborescence
 * @subpackage voir
 * @author Régis Bouguin
 * 
 */

/** Affichage des conteneurs
 * 
 * Construit l'arborescence des conteneurs 
 * et appelle affiche_evaluations() pour afficher les évaluations
 * 
 * @see affiche_evaluations()
 */
function affiche_sous_conteneur($sous_eval) {?>
  <li>
    <?php echo $sous_eval['nom_court']; ?>
    -
    <a href='<?php echo CHEMIN_RACINE ?>cahier_notes/saisie_notes.php?id_conteneur=<?php echo $sous_eval["id"]; ?>'>
      Visualisation
    </a>
    -
    <a href='?id_conteneur=<?php echo $sous_eval["id"]; ?>&amp;action=matiere&amp;<?php echo CREATION ;?>=<?php echo MATIERE ;?>'>
      Configuration
    </a>
     (<?php 
      echo $sous_eval["coef"]; 
      if ($sous_eval["display_bulletin"]) { ?>
	<img src='../../images/icons/visible.png' 
	     width='19' height='16' 
	     title='visible sur le bulletin' 
	     alt='visible sur le bulletin' />
     <?php
      } else { ?>
	<img src='../../images/icons/invisible.png' 
	     width='19' height='16'
	     title='non visible sur le bulletin'
	     alt='non visible sur le bulletin' />
     <?php
      }
    ?>)
    
    <?php if (empty($sous_eval['sous_conteneur']) && empty($sous_eval['evaluation'])){  ?>
    -
    <a href='index.php?action=<?php echo SUPPRIME ?>&amp;niveau=<?php echo MATIERE; ?>&amp;id_conteneur=<?php echo $sous_eval["id"].add_token_in_url(); ?>'>
      Suppression
    </a>
    
    <?php } ?> 
  </li> 
    <?php if (!empty($sous_eval['sous_conteneur'])){  ?>
  <li style="list-style-type: none;">
    <ul>
      <?php foreach($sous_eval['sous_conteneur'] as $sous_eval_2) {  ?>
	<?php affiche_sous_conteneur($sous_eval_2)  ?>
      <?php } ?>
    </ul>
  </li>  
  <?php } ?> 
  
  <!-- On affiche les évaluations -->
   
    <?php if (!empty($sous_eval['evaluation'])){  ?>
  <li style="list-style-type: none;">
    <ul>
      <?php foreach($sous_eval['evaluation'] as $sous_eval_3) {  ?>	
      <?php affiche_evaluations($sous_eval_3); ?>
      <?php } ?>
    </ul>
  </li>  
    <?php } 
}

/** Affichage des évaluations
 * 
 * Ajoute les évaluations à l'arborescence des conteneurs 
 * 
 */
function affiche_evaluations($sous_eval) { ?>
 
  <li>
    <span style='color:green;'><?php echo $sous_eval['nom_court']; ?></span>
    -
    <a href='?module=notes&amp;id_conteneur=<?php echo $sous_eval["id_conteneur"]; ?>&amp;id_devoir=<?php echo $sous_eval["id"]; ?>'>
      Saisie
    </a>
    (<?php 
      if ($sous_eval["nb_notes"] == $sous_eval["nb_eleves"]) { ?>
    <span class ="vert">
      <?php } else { ?>
    <span class ="rouge">
      <?php }
    echo $sous_eval["nb_notes"]?>/<?php echo $sous_eval["nb_eleves"]
    ?></span>)
    -
    <a href='?id_conteneur=<?php echo $sous_eval["id_conteneur"]; ?>&amp;id_devoir=<?php echo $sous_eval["id"]; ?>&amp;action=<?php echo AJOUTE; ?>&amp;creation=<?php echo EVALUATION; ?>'>
      Configuration
    </a>
    (<?php 
      echo $sous_eval["coef"]; 
      if ($sous_eval["display_parents"]) { ?>
	<img src='<?php echo CHEMIN_RACINE ?>images/icons/visible.png' 
	     width='19' height='16' 
	     title='Évaluation visible sur le bulletin' 
	     alt='Évaluation visible sur le bulletin' />
     <?php
      } else { ?>
	<img src='<?php echo CHEMIN_RACINE ?>images/icons/invisible.png' 
	     width='19' height='16'
	     title='Évaluation non visible sur le bulletin'
	     alt='Évaluation non visible sur le bulletin' />
     <?php
      }
    ?>)
  <?php if ($sous_eval["nb_notes"]==0) { ?>
    -
    <a href='?action=<?php echo SUPPRIME ?>&amp;id_conteneur=<?php echo $sous_eval["id_conteneur"]; ?>&amp;niveau=<?php echo EVALUATION; ?>&amp;id_devoir=<?php echo $sous_eval["id"].add_token_in_url(); ?>'>
      Suppression
    </a>
  <?php } ?>
  </li> 
<?php   
}

?>

<div id="container">

  <!-- Autres liens --> 
<?php if(isset ($_SESSION[PREFIXE]['id_groupe_session']) && isset ($_SESSION[PREFIXE]['periode_num'])) {?>
 <div class='div_tableau'>
    <!-- Import/Export de fichiers --> 
    <div class="colonne ie_gauche">
      <form enctype="multipart/form-data" id= "form2" action="index.php" method="post">
	<fieldset>
	  <legend>Import/Export</legend>
	  <input type="hidden" name="action" value="modifie" />
	  <label for="import_export" class="invisible">Choix d'une action à effectuer</label>
	  <select name='<?php echo IMPORT ?>' id='import_export' onchange="this.form.submit();">
	    <option value=''>Choisir une action</option>
	    <option value='<?php echo EXPORTER ?>'>- Exporter les notes</option>
  <?php if ($periode_ouverte) {?>
	    <option value='<?php echo IMPORTER ?>'>- Importer les notes</option>
  <?php } ?>
	  </select>
	  <input type="submit"
		 value="Éxecuter"
		 id="btn_save_export"/>
	  <script type="text/javascript">
//<![CDATA[
  document.getElementById('btn_save_export').className='invisible';
//]]>
	  </script>
	</fieldset>
      </form>
    </div>
    
    <!-- Créer la structure --> 
    <div class="colonne ie_centre">
  <?php if ($periode_ouverte) {?>
      <form enctype="multipart/form-data" id= "form3" action="index.php" method="post">
	<fieldset>
	  <legend>Créer</legend>
	  <input type="hidden" name="action" value="ajoute" />
	  <label for="creation" class="invisible">Choix d'un conteneur à créer</label>
	  <select name='<?php echo CREATION ;?>' id='creation' onchange="this.form.submit();"> 
	    <option value=''>Sélectionner le type de conteneur...</option>
	    <option value='<?php echo EVALUATION ;?>'>- Évaluation</option>
	    <option value='<?php echo CUMUL ;?>'>- Évaluation-cumul</option>
	    <option value='<?php echo MATIERE ;?>'>- <?php echo ucfirst (getSettingValue('gepi_denom_boite')); ?></option>
	    <?php if ($_SESSION[PREFIXE]['periode_num']>1) { ;?>
	    <option value='<?php echo IDEM ;?>'>- Structure identique à la période précédente</option>
	    <?php } ;?>
	  </select>
	  <input type="submit"
		 value="Éxecuter"
		 id="btn_save_creer"/>
	  <script type="text/javascript">
//<![CDATA[
  document.getElementById('btn_save_creer').className='invisible';
//]]>
	  </script>
	</fieldset>
      </form>
  <?php } ?>
    </div>
    
    <!-- Signaler des erreurs --> 
    <div class="colonne ie_droite">
      <form enctype="multipart/form-data" id= "form4" action="index.php" method="post">
	<fieldset>
	  <legend>Signaler</legend>
	  <input type="hidden" name="action" value="modifie" />
	  
	  <input type="hidden" name="<?php echo SIGNALER ?>" value="<?php echo AFFECTATION ?>" />
	  <input type="submit" value="Erreurs d'affectation" />

	</fieldset>
      </form>
    </div>
    
  <?php } ?>
  
  </div>
 
  <form enctype="multipart/form-data" id= "form1" action="index.php" method="post">
    <fieldset class='center'>
      <legend>Enseignements :</legend>
  <!-- Groupes d'enseignements -->     
  <?php if (empty($tous_groupes)) { ?>
      Aucun cahier de notes n'est disponible.
    <?php } else { ?>
      <label for="choix_groupe" >Groupe :</label>
      <select name='id_groupe' id="choix_groupe" onchange="this.form.submit();"> 
	<option value=''>Sélectionnez un groupe</option>
      <?php foreach($tous_groupes as $group) { ?>
	<option value='<?php echo $group["id"]; ?>'<?php 
		if( isset ($_SESSION[PREFIXE]['id_groupe_session']) && $group["id"]==$_SESSION[PREFIXE]['id_groupe_session']) {?>
		selected='selected'
		<?php } ?> >
	  <?php echo $group["classlist_string"]." : ".stripslashes($group["description"]); ?>
	</option>	
      <?php }?>
      </select>
  <?php } ?>
    
	  <input type="submit"
		 value="Sélectionner"
		 id="btn_choix_groupe"/>
	  <script type="text/javascript">
//<![CDATA[
  document.getElementById('btn_choix_groupe').className='invisible';
//]]>
	  </script>
    
  <!-- Périodes d'enseignements -->  
  <?php if (!empty($periodes)) { ?> 
      <label for="periode_num" >Période :</label>
      <select name='periode_num' id='periode_num' onchange="this.form.submit();"> 
	<option value=''>Sélectionnez une période</option>
      <?php foreach($periodes as $periode) { ?>
	<option value='<?php echo $periode["periode_num"]; ?>'
		<?php if( isset ($_SESSION[PREFIXE]['periode_num']) && $periode["periode_num"]==$_SESSION[PREFIXE]['periode_num']) {?>
		selected='selected'
		<?php } ?>
		>
	  <?php echo $periode["periode_nom"]; ?>
	  <?php if ($periode["periode_close"]===TRUE) { ?>
	      (période close)
	  <?php }?>
	</option>
      <?php }?>
      </select>
      
      <input type="submit"
	     value="Sélectionner"
	     id="btn_choix_periode"/>
      <script type="text/javascript">
//<![CDATA[
  document.getElementById('btn_choix_periode').className='invisible';
//]]>
      </script>
    
  <?php } ?>
    </fieldset>
  </form>
  
  <p class="invisible">
    <a name='contenu' id="contenu">Début de la page</a>
  </p>
  
   <!-- Carnet de notes -->
  <?php if (isset ($id_groupe_actif)) { ?>
  <h2>
      Carnet de notes : <?php echo $group_actif["classlist_string"]; ?>
      -
      <?php echo $group_actif["matiere"]["nom_complet"]; ?>
  <?php if ($id_periode_active) { ?>
      -
      <?php echo $periodes[$id_periode_active-1]['periode_nom']; ?>
  <?php } ?>
  </h2>
  <?php } ?>
      
      
  <!-- Évaluations -->        
   
    <?php if (!empty($eval_toutes)) { ?>
    <h3>Liste des évaluations du carnet de notes</h3>
    <ul>
    <?php foreach($eval_toutes as $evaluation) {  ?>
      <li>
      <?php echo $evaluation['nom_complet']; ?>
      <?php if ($evaluation['close']) { ?>
	(période close)
	<a href='<?php echo CHEMIN_RACINE ?>cahier_notes/saisie_notes.php?id_conteneur=<?php echo $evaluation["id"]; ?>'>
	  Visualisation
	</a>
      <?php } else { ?>
	<a href='<?php echo CHEMIN_RACINE ?>cahier_notes/saisie_notes.php?id_conteneur=<?php echo $evaluation["id"]; ?>'>
	  Visualisation
	</a>
	-
	<!-- <a href='<?php //echo CHEMIN_RACINE ?>cahier_notes/add_modif_conteneur.php?id_conteneur=<?php //echo $evaluation["id"]; ?>'> -->
	<a href='?id_conteneur=<?php echo $evaluation["id"]; ?>&amp;action=matiere&amp;<?php echo CREATION ;?>=<?php echo MATIERE ;?>'>
	  Configuration
	</a>
	<ul>
	<?php if (!empty($evaluation['sous_conteneur'])){  ?>
	  <?php foreach($evaluation['sous_conteneur'] as $sous_eval) {  ?>
	    <?php affiche_sous_conteneur($sous_eval) ?>
	  <?php } ?>
	<?php } ?>
	<?php if (!empty($evaluation['evaluation'])){  ?>
	  <?php foreach($evaluation['evaluation'] as $sous_eval) {  ?>	
	  <?php affiche_evaluations($sous_eval); ?>
	  <?php } ?>
	<?php } ?>
	</ul>
	
      <?php } ?>
      </li>
    <?php } ?>
    </ul>
    <?php } ?>    
  
    
  <?php if (!empty($liens_autres_pages)) { ?>
    <h3>Autres liens</h3>
    
    <ul>
	<?php foreach($liens_autres_pages as $liens) { ?>
      <li>
	<a href='<?php echo $liens['adresse'] ; ?>'>
	  <?php echo $liens['titre'] ; ?>
	  <?php if (isset($liens['autre'])) { ?>
	  <span class='rouge'>(<?php echo $liens['autre'] ; ?>)</span>
	  <?php } ?>
	</a>
      </li>
	<?php } ?>
    </ul>
  <?php } ?>
</div>