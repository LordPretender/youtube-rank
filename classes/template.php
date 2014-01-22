<?php

require_once 'includes/rain.tpl.class.php';

class SiteTemplate {
    /**
     * Instance de la classe RainTPL, moteur de template utilisé.
     * @var RainTPL
     */
    protected $tpl;
    	
	protected $bdd;
	
	private $slug;
	
	protected $slug_virtuel = '';
	protected $titre = '';
	protected $meta_description;
	
	protected $youtube;
	
    /**
     * Initialisation du template HTML qui sera affiché à l'utilisateur
     */
    public function __construct($slug, $mode){
		$this -> slug = $slug;
		$this -> bdd = new BDD();
		$this -> youtube = new Youtube($this -> bdd, $mode);
		
        //Chargement + configuration du moteur de template
        $this -> initMoteurTPL();
		
		$this -> meta_description = SITE_SLOGAN;
    }

    /**
     * Chargement du moteur de template
     */
    private function initMoteurTPL(){
        raintpl::$tpl_dir = "html/"; // template directory
        raintpl::$cache_dir = "tmp/"; // cache directory
        raintpl::configure( 'path_replace', false );
        
        $this -> tpl = new raintpl(); //instance de classe
    }

    /**
     * Méthode à utiliser pour ajouter d'autres variables nécessaires dans les classes filles.
     */
    protected function avantChargement(){
        $this -> tpl -> assign( "youtube_categories", $this -> youtube -> getCatégories() );
        $this -> tpl -> assign( "youtube_vidéos", $this -> youtube -> getVidéos() );
	}
	
    /**
     * Méthode finale qui permet de charger la page à partir du fichier fourni avec les variables définies.
     */
    public function Charger(){
		global $pages;
		
        $this ->avantChargement();
        
        //HEAD
        $this -> tpl -> assign( "site_titre", SITE_NOM );
        $this -> tpl -> assign( "meta_description", $this -> meta_description );
        $this -> tpl -> assign( "site_keywords", SITE_KEYS );
        $this -> tpl -> assign( "site_tpl", SITE_URL . "/" . raintpl::$tpl_dir);
		
		//Liste des pages
        $this -> tpl -> assign( "pages", $pages);
		
		//Page en cours (titre + html à inclure dans la structure)
        $this -> tpl -> assign( "page_slug", $this -> slug_virtuel == '' ? $this -> slug : $this -> slug_virtuel);
        $this -> tpl -> assign( "page_titre", $this -> getTitre());
        $this -> tpl -> assign( "page_contenu", "page_" . $this -> slug );
		
        $this -> tpl -> assign( "youtube_cron", $this -> getLastExecution() );
		
		//Chargement
        $this -> tpl -> draw("structure");
    }
	
    /**
     * Lecture en base à la recherche de la date de dernière exécution du Cron.
	 * @return String Date de dernière exécution du cron.
     */
	public function getLastExecution(){
		$résultats = $this -> bdd ->executer("SELECT valeur FROM configuration WHERE nom = 'lastExecution'");
		return $résultats[0] -> valeur;
	}
	
	public function getTitre(){
		global $pages;
		$titre = $this -> titre;
		
		//On passe en revue toutes les pages
		foreach($pages as $key => $tabPages){
			//Si le slug en cours correspond au slug de la page, on récupère le titre
			if($tabPages[1] == $this -> slug){
				$titre = $tabPages[0];
				break;
			}
		}
		
		//Si titre vide... on récupère celui de la page d'accueil
		if($titre == "")$titre = $pages[0][0];
		
		return $titre;
	}
	
	public function setSlugVirtuel($slug){
		$this -> slug_virtuel = $slug;
	}
}

?>