<?php

class AutreTemplate extends SiteTemplate {
    public function __construct($slug){
        parent::__construct($slug, -1);
		
		$this -> meta_description = "Erreur lors de la soumission du formulaire de contact.";
    }
    
    protected function avantChargement(){}
}

?>
