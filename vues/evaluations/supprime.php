<?php
/** Vue du module �valuations : action supprimer
 * 
 * Supprimer un conteneur ou une �valuation
 * 
 * @author R�gis Bouguin
 * @package arborescence
 * @subpackage supprime
 * 
 */
?>

<div id="container">
  <p class="invisible">
    <a name='contenu' id="contenu">D�but de la page</a>
  </p>

<p class="rouge center bold">
  Vous avez demand� la suppression de :
  <br />
  <?php echo $donnees_supprime->type ; ?>
  ->
  <?php echo $donnees_supprime->nom_complet ; ?>
</p>
<p class="rouge center bold">
  �tes-vous s�r ?
</p>

<form enctype="multipart/form-data" id= "form1" action="index.php" method="post">
  <fieldset class='center'>
    <legend>Supprimer</legend>
  <?php echo add_token_field(true) ; ?>
    <input type="hidden" name='module' value='<?php echo EVALUATIONS ; ?>' />
    <input type="hidden" name='action' value='<?php echo SUPPRIME ; ?>' />
    <input type="hidden" name='id_conteneur' value='<?php echo $conteneur ; ?>' />
    <?php if (isset ($evaluation)) { ?>
    <input type="hidden" name='id_devoir' value='<?php echo $evaluation ; ?>' />
    <?php } ?>
    <input type="hidden" name='niveau' value='<?php echo $choix ; ?>' />
    <input type="submit" name='<?php echo 'confirmation_suppr' ; ?>' value="<?php echo SUPPRIMER ; ?>" />
    <input type="submit" name='<?php echo 'confirmation_suppr' ; ?>' value="<?php echo ANNULER ; ?>" />
  </fieldset>
  

</form>

</div>