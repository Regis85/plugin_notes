<?php
/** Vue du module notes : action voir
 * 
 * Afficher et modifier les notes d'une ou plusieurs évaluations
 * 
 * Champs du formulaire form_eval
 * nombre
 * - action[1]
 * - csrf_alea[1]
 * - eleve = array(array(array(note[1] , commentaire[1]))) * nb élèves
 * - 2+(count($_SESSION[plugin_notes][tableau_notes])*((2*count($_SESSION[plugin_notes][tableau_notes][0]['notes']))))
 * 
 * tailles clés
 * - action[6]
 * - csrf_alea[9]
 * - eleve = array(note[18] , commentaire[17])
 * 
 * longueur clés 
 * 
 * @author Régis Bouguin
 * @package saisie_notes
 * @subpackage voir
 * @todo voir les photos en multisites
 * 
 */

if (!$suhosin_bon) {
  charge_message('<span class=\'rouge\'>Les paramètres de votre serveur ne vous permettent pas d\'enregistrer autant d\'évaluations<span/>');
  charge_message('<span class=\'rouge\'>Vous devez fermer une évaluation<span/>');
}

?>

<div id="container">
  <a name='contenu'></a>
  
<?php if (isset ($id_groupe_actif)) { ?>
  <h2>
      Saisie des notes : <?php echo $group_actif["classlist_string"]; ?>
      -
      <?php echo $group_actif["matiere"]["nom_complet"]; ?>
  <?php if ($id_periode_active) { ?>
      (<?php echo $periodes[$id_periode_active-1]['periode_nom']; ?>)
  <?php } ?>
      <img src="<?php echo CHEMIN_IMAGES; ?>icons/histogramme.png"
	   alt="Afficher"
	   title="Afficher/masquer les statistiques"
	   onclick="change_stats()"
	   style="cursor:pointer" 
	   />
  </h2>
<?php } ?>

<!-- Ajout d'évaluations -->
<form enctype="multipart/form-data" id= "form_ajouter" action="index.php" method="post"> 
 
  <p class="colonne"> 
    <input type='hidden' 
	   name="<?php echo AJOUTE ; ?>" 
	   value="<?php echo EVALUATION ; ?>" />
    <select name='<?php echo EVALUATIONS ;?>' 
	    id='creation'
	    onchange="document.getElementById('ajoute_eval').click()" >
      <option value=''>Ajouter une évaluation à noter</option>
      <?php 
      foreach ($eval_disponibles as $eval_dispo) { 
	?>
      <option value='<?php echo $eval_dispo['id'] ?>'>
      <?php 
	echo htmlentities($eval_dispo['conteneur'])." -> ".htmlentities($eval_dispo['nom_court']) ; 
      ?>
      </option>
      <?php } ?>      
    </select>
    
    <input type="submit" id="ajoute_eval" name="action" value="<?php echo AJOUTE ; ?>" />

    
  </p>

<!-- Affichage du carnet de notes --> 
  <p class="colonne">
    <input type='hidden' name="<?php echo VOIR; ?>" value="<?php echo CARNET_NOTES ; ?>" />
    <input type="submit" name="action" value="<?php echo VOIR_CARNET; ?>" />
  </p>

<!-- Retour à la page des évaluations -->
  <p class="colonne">
    <input type='hidden' name="retour" value="<?php echo EVALUATIONS ; ?>" />
    <input type="submit" name="action" value="<?php echo RETOUR_EVAL ; ?>" />
  </p>

<p>
  Taper une note entre 0 et la note maximum de chaque évaliation, pour chaque élève, ou à défaut le code 'a' pour 'absent', 
  le code 'd' pour 'dispensé', le code '-' ou 'n' pour absence de note.
</p>

</form>

<form enctype="multipart/form-data" id= "form_eval" action="index.php" method="post"> 
<!-- -->
<p>
  Vous pouvez également importer directement vos notes par "copier/coller" à partir d'un tableur 
  ou d'une autre application en cliquant sur 
  <img src="<?php echo CHEMIN_IMAGES; ?>icons/copy-16.png"
       alt="icone copier" />.
</p> 
<!-- -->

<!-- Tableau des notes -->
<?php if (count($eval_valides)) { ?>
  <p class="center">
    <input type="submit" name="action" value="<?php echo FORCE_ENREGISTRE; ?>" />
  </p>
<table class="table_note">
  <tr  class="table_note_entete">
    <th id="nom_prenom">
      Nom Prénom
    </th>
  <?php 
  if (count($eval_valides)) {
$num_eval=1;
  foreach ($eval_valides as $affiche_note) { ?>
    <th>
      <span title="<?php echo htmlentities($affiche_note['conteneur']); ?> -> <?php echo $affiche_note['nom_court']; ?>"
	    style="cursor: help;">
      <?php echo $affiche_note['nom_court']; ?>
      </span>
      <br />
      <span style="font-size:smaller;"><?php echo $affiche_note['date']; ?></span>
      <?php if ($affiche_note['display_parents']) { ?>
      <img src="<?php echo CHEMIN_IMAGES; ?>icons/visible.png"
	   alt=""
	   title="Évaluation visible sur le relevé de notes"/>
      <?php } ?>
      <br />
      <span style="font-size:smaller;margin: 0px;">
<?php 
  $note_sur_eval[$num_eval]=$affiche_note['note_sur'];
  $num_eval++;
?>
	/<?php echo $affiche_note['note_sur']; ?>
	-
	coef : <?php echo $affiche_note['coef']; ?>
      </span>
      
      <img src="<?php echo CHEMIN_IMAGES; ?>icons/histogramme.png"
	   alt="Afficher"
	   title="Afficher/masquer la répartition des notes"
	   onclick="change_repartition(<?php echo $affiche_note['id']; ?>)"
	   style="cursor:pointer;" />
      <input type='submit' 
	     class="stop font_zero"
	     style="background:url(<?php echo CHEMIN_IMAGES; ?>icons/copy-16.png) no-repeat; border: none; cursor:pointer;" 
	     value="<?php echo $affiche_note['id']; ?>" 
	     name="<?php echo COLLER; ?>"
	     title="Saisir les notes ou commentaires par copier/coller" />
      <br />
      <input type='submit' 
	     class="stop font_zero" 
	     style="background:url(../../images/bulle_rouge.png) no-repeat center center;"
	     name="<?php echo CACHER; ?>" 
	     value="<?php echo $affiche_note['id']; ?>"
	     title="Cacher cette évaluation" />
    </th>
    <th class="entre_observ">
      Commentaires *
    </th>
  <?php }
  unset ($affiche_note);
}?>
  </tr>
    <?php 
      $ideleve=1;
    ?>
    <?php foreach ($tableau_notes as $eleve) { ?>
  <tr class="entre_nom">
    <td>
      <?php echo $eleve['eleve']['nom']." ".$eleve['eleve']['prenom']; ?>
	<?php // echo $eleve['login'] ?>
      <p class="photos" 
	 id="img_<?php echo $eleve['eleve']['elenoet']; ?>" 
	 style ="text-align:center;">
	<img src="<?php echo nom_photo($eleve['eleve']['elenoet']); ?>" 
	     alt="" 
	     title=""
	     style ="width:20%;"/>
      </p>
    </td>
    <?php 
      $idnote=100;
      $idcomm=200;
      $numNote=1;
    ?>
    <?php foreach ($eleve['notes'] as $notes) { 
      $noteMax=$note_sur_eval[$numNote]; ?>
    <td class="entre_note" id="td_<?php echo $ideleve+$idnote; ?>" style ="text-align:center;">
      <input type="text"
	     name="<?php echo $eleve['index'] ?>_note_<?php echo $notes['id_devoir']; ?>"
	     id="n<?php echo $ideleve+$idnote; ?>" 
	     value="<?php echo $notes['note_devoir']; ?>"
	     onfocus="change_photo(<?php echo $eleve['eleve']['elenoet']; ?>);"
	     class="case_note"
	     onkeydown="clavier(this.id,event);"
	     onchange="verifie_note(<?php echo $ideleve+$idnote; ?>,<?php echo $noteMax; ?>);" />	     
    </td>
    <td class="entre_observ">
      <textarea id="n<?php echo $ideleve+$idcomm; ?>"
		name="<?php echo $eleve['index'] ?>_app_<?php echo $notes['id_devoir']; ?>"
		lang="fr"
		onfocus="change_photo(<?php echo $eleve['eleve']['elenoet']; ?>);"
		rows ="2"
		cols ="30"
		onkeydown="clavier(this.id,event);"><?php echo $notes['comment_devoir']; ?></textarea>
    </td>
    <?php 
      $idnote+=200;
      $idcomm+=200;
      $numNote++;
    ?>
    <?php } ?>
  </tr>
    <?php 
      $ideleve+=1;
    ?>
    <?php } ?>
</table>
  <p class="center">    
    <?php echo add_token_field(true) ; ?> 
    <input type="submit" name="action" value="<?php echo FORCE_ENREGISTRE; ?>" />
  </p>

  <?php 
  }
  ?>
</form>

<p>
* En conformité avec la CNIL, le professeur s'engage à ne faire figurer dans le carnet de notes 
que des notes et commentaires portés à la connaissance de l'élève 
(note et commentaire portés sur la copie, ...).
</p> 

</div>

<?php if (count($eval_valides)) { ?>
<div id="stats">
  <p>
    <img src="<?php echo CHEMIN_IMAGES.'icons/close16.png'; ?>"
	 alt="bouton"
	 title="Masquer le tableau de statistiques"
	 onclick="cache_stats()"/>
  </p>
  <table>
    <caption>Statistiques</caption>
    <tr>
      <th>
	
      </th>
  <?php foreach ($eval_valides as $affiche_note) {  ?>
      <th>
	<?php echo $affiche_note['nom_court'] ; ?>
      </th>
  <?php }
  unset ($affiche_note); ?>
    </tr>
    <tr>
      <td>
	Max
      </td>
  <?php foreach ($eval_valides as $affiche_note) {  ?>
      <td style="text-align: center">
	<?php if (count($affiche_note['tab_notes'])> 1) echo max($affiche_note['tab_notes']); ?>
      </td>
  <?php }
  unset ($affiche_note); ?>
    </tr>
    <tr>
      <td>
	Moyenne
      </td>
  <?php foreach ($eval_valides as $affiche_note) {  ?>
      <td style="text-align: center">
	<?php if (count($affiche_note['tab_notes'])> 1) echo (round(array_sum($affiche_note['tab_notes'])/count($affiche_note['tab_notes']),2)); ?>
      </td>
  <?php }
  unset ($affiche_note); ?>
    </tr>
    <tr>
      <td>
	Min
      </td>
  <?php foreach ($eval_valides as $affiche_note) {  ?>
      <td style="text-align: center">
	<?php if (count($affiche_note['tab_notes'])> 1) echo min($affiche_note['tab_notes']); ?>
      </td>
  <?php }
  unset ($affiche_note); ?>
    </tr>
    <tr>
      <td>
	1er quartile
      </td>
  <?php foreach ($eval_valides as $affiche_note) {  ?>
      <td style="text-align: center">
	<?php if (count($affiche_note['tab_notes'])>=4) { ?>
	<?php echo $affiche_note['tab_notes'][ceil(count($affiche_note['tab_notes'])/4)]; ?>
	<?php } ?>
      </td>
  <?php }
  unset ($affiche_note); ?>
    </tr>
    <tr>
      <td>
	Médiane
      </td>
  <?php foreach ($eval_valides as $affiche_note) {  ?>
      <td style="text-align: center">
  <?php if (count($affiche_note['tab_notes'])>=1) {
  $num_mediane=floor(count($affiche_note['tab_notes'])/2);
  if (count($affiche_note['tab_notes'])%2!=0) {
    echo $affiche_note['tab_notes'][$num_mediane];
  } else {
    echo (($affiche_note['tab_notes'][$num_mediane]+$affiche_note['tab_notes'][$num_mediane-1])/2);
  } ?>
  <?php } ?>
      </td>
  <?php }
  unset ($affiche_note); ?>
    </tr>
    <tr>
      <td>
	3e quartile
      </td>
  <?php foreach ($eval_valides as $affiche_note) {  ?>
      <td style="text-align: center">
	<?php if (count($affiche_note['tab_notes'])>=4) { ?>
	  <?php echo $affiche_note['tab_notes'][ceil(count($affiche_note['tab_notes'])*3/4)]; ?>
	<?php } ?>
      </td>
  <?php }
  unset ($affiche_note); ?>
    </tr>
  </table>
</div>

<?php foreach ($eval_valides as $toutes_notes) { 
    $reparti_note=array("4"=>0, "8"=>0, "12"=>0, "16"=>0, "20"=>0);
    if (count($toutes_notes['tab_notes'])>1) {
      foreach ($toutes_notes['tab_notes'] as $affiche_note) {
        if ($affiche_note <= 4) {
          $reparti_note['4']++;
        } else if ($affiche_note <= 8) {
          $reparti_note['8']++;  
        } else if ($affiche_note <= 12) {
          $reparti_note['12']++;
        } else if ($affiche_note <= 16) {
          $reparti_note['16']++;
        } else if ($affiche_note <= 20) {
          $reparti_note['20']++;
        }      
      } 
    } ?>
  
<div id="repart_<?php echo $toutes_notes['id']; ?>">
  <p>
    <img src="<?php echo CHEMIN_IMAGES.'icons/close16.png'; ?>"
	 alt="bouton"
	 title="Masquer le tableau de répartition"
	 onclick="cache_repartition(<?php echo $toutes_notes['id']; ?>)"/>
  </p>
  <table>
    <caption>Répartition des notes de <?php echo $toutes_notes['nom_court']; ?></caption>
    <tr>
      <th>
	+ de 16 à 20
      </th>
      <td>
	<?php echo $reparti_note['20']; ?>
      </td>
    </tr>
    <tr>
      <th>
	+ de 12 à 16
      </th>
      <td>
	<?php echo $reparti_note['16']; ?>
      </td>
    </tr>
    <tr>
      <th>
	+ de 8 à 12
      </th>
      <td>
	<?php echo $reparti_note['12']; ?>
      </td>
    </tr>
    <tr>
      <th>
	+ de 4 à 8
      </th>
      <td>
	<?php echo $reparti_note['8']; ?>
      </td>
    </tr>
    <tr>
      <th>
	de 0 à 4
      </th>
      <td>
	<?php echo $reparti_note['4']; ?>
      </td>
    </tr>
  </table>
</div>

<script  type="text/javascript">
//<![CDATA[
cache_repartition(<?php echo $toutes_notes['id']; ?>)
//]]>
</script>

<?php } ?>
	
<?php } ?>
<script  type="text/javascript">
//<![CDATA[
cache_photos();
cache_stats();
document.getElementById('ajoute_eval').className='invisible';
ajout_btn_comment()
//]]>
</script>
