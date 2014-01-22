<?php

//Paramètres site
define("SITE_NOM", "YouTubeRank");
define("SITE_SLOGAN", "Classement des vidéos Youtube");
define("SITE_URL", "http://" . $_SERVER["HTTP_HOST"]);
define("SITE_MAIL", "noreply@formulaire-de-contact.net");
define("SITE_MAILADMIN", "lordp.webmaster@gmail.com");

define("YOUTUBE_APIKEY", "AIzaSyC04PkGS3Mp88--S1UDI74cYKteJA1u94M"); //https://code.google.com/apis/console/
define("YOUTUBE_MAXRESULTS", 50); //Le max possible semble être 50.
define("YOUTUBE_FOLDER", "html/img/miniatures");

//Divers
define("PHRASE","Je mange du pain tous les matins.");

//Liste des pages
$pages = array();
$pages[] = array('GÉNÉRAL', '');
$pages[] = array('VUES', 'classement-vues');
$pages[] = array('NOTES', 'classement-liked');
$pages[] = array('COMMENTAIRES', 'classement-commentaires');
$pages[] = array('HISTORIQUE', 'historique');
$pages[] = array('NOUS CONTACTER', 'contact');

//Champs requis dans les formulaires/sessions
define("FORMULAIRE_MAIL", "fdc_mail");
define("FORMULAIRE_SPAM", "fdc_spam");

?>
