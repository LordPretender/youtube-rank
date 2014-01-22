<?php

require_once 'includes/params.php';
require_once 'classes/bdd.php';
require_once 'classes/template.php';
require_once 'classes/youtube.php';

$slug = isset($_GET['id']) ? ($_GET['id']) : "accueil";

$include_file = "pages/$slug.php";
if (file_exists($include_file)){
    require_once $include_file;
    
    $site = new AutreTemplate($slug);
}else{
	switch($slug){
		case "classement-vues":
			$mode = Youtube::MODE_VUES;
			break;
		
		case "classement-liked":
			$mode = Youtube::MODE_LIKED;
			break;
		
		case "classement-commentaires":
			$mode = Youtube::MODE_COMMENTS;
			break;
		
		default:
			$mode = Youtube::MODE_GLOBAL;
	}
	
    $site = new SiteTemplate("accueil", $mode);
	$site -> setSlugVirtuel($slug);
}

//Afficher la page
$site -> Charger();

?>