<?php

class AutreTemplate extends SiteTemplate {
    public function __construct($slug){
        parent::__construct($slug, -1);
		
		$this -> meta_description = "Historique du classement général.";
    }
    
    protected function avantChargement(){
		$datas = array();
		
		//Lecture des données
		$résultats = $this -> bdd -> executer("SELECT * FROM V_HistoriqueRecap order by title, periode
		");
		
		
		//Initialisation de notre attribut
		foreach($résultats as $key => $ligne){
			$date = new DateTime($ligne -> periode);
			
			//Ajout de la vidéo s'il n'existe pas déjà
			if(!array_key_exists($ligne -> title, $datas))$datas[$ligne -> title] = array();
			
			//Ajout de la période pour la vidéo en cours
			$datas[$ligne -> title][] = "[" . $date->getTimestamp() * 1000 . ", " . $ligne -> Pourcentage . "]";//array($ligne -> periode, $ligne -> Pourcentage);
		}
		
        $this -> tpl -> assign( "evolution_coord", $datas );
	}
}

?>
