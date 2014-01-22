<?php

header('Content-Type: text/html;charset=UTF-8');

require_once 'includes/params.php';
require_once 'classes/bdd.php';
require_once 'classes/youtube.php';

$bdd = new BDD();
$youtube = new Youtube($bdd, Youtube::MODE_CRON);

//Exécution de la mise à jour du classement
$youtube -> requestVidéosClassées();

//Historisation...
$youtube -> créerHistorique();

//Archivage
$youtube -> créerArchive();

//Suppression des vidéos hors classement
$youtube -> supprimerVidéos();

//On sauvegarde la dernière execution du cron.
$current = $bdd -> escape(date("d/m/Y à H:i"), 2);
$bdd -> executer("UPDATE configuration SET valeur = $current WHERE nom = 'lastExecution'");

?>