<?php
/** Page de style sp�cifique du plugin notes multiples
 * 
 * @author R�gis Bouguin
 * @package global
 * @subpackage affichage 
 */
?>

<link rel="stylesheet" type="text/css" href="./templates/origine/css/bandeau.css" media="screen" />

<!-- corrections internet Exploreur -->
  <!--[if lte IE 7]>
    <link title='bandeau' rel='stylesheet' type='text/css' href='./templates/origine/css/bandeau_ie.css' media='screen' />
  <![endif]-->

<!-- Style_screen_ajout.css -->
<?php
  if (count($Style_CSS)) {
    foreach ($Style_CSS as $value) {
      if ($value!="") {
	echo "<link rel=\"$value[rel]\" type=\"$value[type]\" href=\"$value[fichier]\" media=\"$value[media]\" />\n";
      }
    }
    unset($value);
  }
?>

<!-- Fin des styles -->


</head>

<!-- ******************************************** -->

<!-- ************************* -->
<!-- D�but du corps de la page -->
<!-- ************************* -->
<body onload="show_message_deconnexion();<?php if($tbs_charger_observeur) echo $tbs_charger_observeur;?>">
