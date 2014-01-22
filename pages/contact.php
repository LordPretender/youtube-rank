<?php

class AutreTemplate extends SiteTemplate {
    public function __construct($slug){
        parent::__construct($slug, -1);
		
		$this -> meta_description = "Formulaire de contact de YoutubeRank. Utilisez-le si vous souhaitez nous contacter.";
    }
    
    protected function avantChargement(){}
}

?>
