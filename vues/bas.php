<?php
/** Pied de page du plugin notes multiples
 * 
 * @author R�gis Bouguin
 * @package global
 * @subpackage affichage 
 */
?>

<!--
	<script type='text/javascript'>
		temporisation_chargement='ok';
	</script>
-->

	<script type='text/javascript'>
	cacher_div('personnes_connectees');
	</script>

<div>
<a name='bas_de_page'></a>
</div>

		<?php
			if ($tbs_microtime!="") {
				echo "
   <p class='microtime'>Page g�n�r�e en ";
   			echo $tbs_microtime;
				echo " sec</p>
   			";
	}
?>

		<?php
			if ($tbs_pmv!="") {
				echo "
	<script type='text/javascript'>
		//<![CDATA[
   			";
				echo $tbs_pmv;
				echo "
		//]]>
	</script>
   			";
		}
?>

</body>
</html>
