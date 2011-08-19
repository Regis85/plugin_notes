<?php
/** Vue du module notes : action coller
 * 
 * Remplir une �valuation par copier/coller
 * 
 * @author R�gis Bouguin
 * @package saisie_notes
 * @subpackage coller
 * 
 */
?>

<div id="container">
  <a name='contenu'></a>

<h2>
  Copier/Coller des notes ou des commentaires dans
  <br />
  <?php foreach ($classes as $classe) {
  echo $classe['classe']." "; 
  } 
unset ($classe) ?>
  : <?php  echo $evaluation['nom_complet']; ?>
</h2>

<p class="center">
  On utilise les sauts de lignes pour diff�rencier les donn�es de chaque �l�ve 
  aussi vous ne pouvez pas importer des commentaires avec saut de ligne.
</p>
<p class="center">
  Si vous avez plus de donn�es que d'�l�ves, les donn�es en trop sont ignor�es 
  mais aucun message d'alerte n'est affich�.
</p>
<p class="center">
  Si votre import provoque une erreur, les donn�es ne sont pas conserv�es.
</p>
<p class="center gras">
  V�rifiez bien vos donn�es avant d'enregistrer, cette page �crase les anciennes valeurs sans faire de test.
</p>

  <form enctype="multipart/form-data" 
	id= "form_coller" 
	action="index.php" 
	method="post"
	style="text-align: left;">
    <p class="center">
    <?php if ($comments_copier || $notes_copier) { ?>
      <input type="submit" name="enregistrer" value="<?php  echo ENREGISTRER; ?>" />
    <?php echo add_token_field(true) ; ?> 
    <?php } ?>
      <input type="submit" name="enregistrer" value="<?php  echo VERIFIER; ?>" />
      <input type="submit" name="enregistrer" value="<?php  echo ABANDONNER; ?>" />
    </p>
  <table id="tb_copie" class="colonne" style="text-align: left; width: 45%;">
  <caption>Liste des �l�ves</caption>
  <tr>
    <th>
      Nom Pr�nom
    </th>
    <?php if ($notes_copier) { ?>
    <th>
      Notes
    </th>
    <?php } ?>
    <?php if ($comments_copier) { ?>
    <th>
      Commentaires
    </th>
    <?php } ?>
  </tr>
    <?php foreach ($eleves as $elv) { ?>
  <tr>
    <td>
      <?php echo $elv['nom'].' '.$elv['prenom']; ?>
    </td>
    <?php if ($notes_copier) { 
      if (is_numeric($elv['note'])) {?>
    <td>
      <?php echo $elv['note']; ?>
    </td>
    <?php } else { ?>
    <td> 
      <?php if ($elv['statut'] !="v") echo $elv['statut']; ?>
    </td>
    <?php } ?>
    <?php } ?>
    <?php if ($comments_copier) { ?>
    <td>
      <?php echo $elv['commentaire']; ?>
    </td>
    <?php } ?>
  </tr>
<?php } 
unset ($elv); ?>
  </table>
   
  <p class="colonne" style="width: 15%;">
    <label for="colle_notes">Copier les notes ici</label>
    <br />
    <textarea name="colle_notes" 
      id="colle_notes"
      rows="<?php echo count($eleves); ?>"
      cols="12"
      style="vertical-align: top; font-size: 13pt;"
      ></textarea>
  </p>
  
    <p class="colonne" style="width: 40%;">
      <label for="colle_comment">Copier les commentaires ici</label>
      <br />
      <textarea name="colle_comment" 
		id="colle_comment"
		rows="<?php echo count($eleves); ?>"
		cols="40"
		style="vertical-align: top; font-size: 13pt;"
		></textarea>
    </p>
    
  </form>

</div>