<?php

//https://developers.google.com/youtube/v3/?hl=fr
class Youtube {
	private $catégories = array();
	private $vidéos = array();
	
	private $bdd;
	
	private $mode;
	
	const MODE_CRON		= 0;
	const MODE_GLOBAL	= 1;
	const MODE_VUES		= 2;
	const MODE_LIKED	= 3;
	const MODE_COMMENTS	= 4;
	
	public function __construct($bdd, $mode){
		$this -> bdd = $bdd;
		$this -> mode = $mode;
		
		//Lecture des vidéos, actives ou non
		$this -> initialiserVidéos();
		
		//Initialisation des catégories
		$this -> initialiserCatégories();
	}
	
	/*
	 * Lecture dans la BDD afin de lire les viéos actives ou non.
	 */
	private function initialiserVidéos(){
		if($this -> mode == Youtube::MODE_CRON){
			//Lecture de toutes les vidéos
			$résultats = $this -> bdd -> executer("SELECT videoID, actif FROM video");
			
			//Initialisation de notre attribut
			foreach($résultats as $key => $ligne){
				$this -> vidéos[$ligne -> videoID] = intval($ligne -> actif);
			}
		}else{
			//Sélection d'une vue ou d'une table, en fonction du mode.
			switch($this -> mode){
				case Youtube::MODE_GLOBAL:
					$table = "V_ClassementGlobal";
					break;
				
				case Youtube::MODE_VUES:
					$table = "V_ClassementVues";
					break;
				
				case Youtube::MODE_LIKED:
					$table = "V_ClassementLiked";
					break;
				
				case Youtube::MODE_COMMENTS:
					$table = "V_ClassementComments";
					break;
				
				default:
					$table = "video";
			}
			
			//Lecture des vidéos
			$résultats = $this -> bdd -> executer("SELECT * FROM $table");
			
			//Initialisation de notre attribut
			foreach($résultats as $key => $ligne){
				$videoID = $ligne -> videoID;
				$publishedAt = $ligne -> publishedAt;
				$title = $ligne -> title;
				$categoryID = $ligne -> categoryID;
				$categoryTitle = $ligne -> categoryTitle;
				$viewCount = number_format($ligne -> viewCount, 0, '.', ' ');
				$likeCount = number_format($ligne -> likeCount, 0, '.', ' ');
				$commentCount = number_format($ligne -> commentCount, 0, '.', ' ');
				
				//Initialisation des catégories
				$this -> catégories[$categoryID] = $categoryTitle;
				
				//Initialisation des vidéos
				$this -> vidéos[$videoID] = array($publishedAt, $title, $categoryID, $categoryTitle, $viewCount, $likeCount, $key +1, $commentCount);
			}
		}
	}
	
	/*
	 * Demande à Youtube d'avoir les vidéos les plus vues et les plus notées'
	 */
	public function requestVidéosClassées(){
		$api_key = YOUTUBE_APIKEY;
		$maxResults = YOUTUBE_MAXRESULTS;
		
		//Lecture des vidéos les plus notées
		$requestLiked = json_decode(file_get_contents("https://www.googleapis.com/youtube/v3/search?key=$api_key&part=snippet&maxResults=$maxResults&order=rating&type=video&fields=items%2Fid%2FvideoId"), true);

		//Lecture des vidéos les plus vues
		$requestViewed = json_decode(file_get_contents("https://www.googleapis.com/youtube/v3/search?key=$api_key&part=snippet&maxResults=$maxResults&order=viewCount&type=video&fields=items%2Fid%2FvideoId"), true);
		
		//Lecture des résultats et MAJ de la liste des vidéos
		foreach(array_merge($requestLiked["items"], $requestViewed["items"]) as $key => $value){
			//Si la vidéo existe déjà, ce sera un update, sinon, un insert
			if(array_key_exists($value["id"]["videoId"], $this -> vidéos)){
				$this -> vidéos[$value["id"]["videoId"]] = 1;
			}else $this -> vidéos[$value["id"]["videoId"]] = 2;
		}
		//$this -> vidéos["9bZkp7q19f0"] = 2;
		
		//On découpe la liste, des vidéos actives uniquement, en plusieurs morceaux et pour chacuns d'entre eux...
		foreach(array_chunk($this -> getActifs(), $maxResults) as $key => $tableaux){
			//Lecture du détail des vidéos
			$tab = json_decode(file_get_contents("https://www.googleapis.com/youtube/v3/videos?key=$api_key&part=snippet,statistics&fields=items&id=" . implode(",", $tableaux)), true);
			
			foreach($tab["items"] as $key => $value){
				$ID = $value["id"];
				
				$ID_BDD = $this -> bdd -> escape($ID,2);
				$publishedAt = $this -> bdd -> escape($value["snippet"]["publishedAt"],2);
				$title = $this -> bdd -> escape($value["snippet"]["title"],2);
				$categoryId = $value["snippet"]["categoryId"]; //https://www.googleapis.com/youtube/v3/videoCategories?key=AIzaSyC04PkGS3Mp88--S1UDI74cYKteJA1u94M&part=snippet&id=10,23
				$viewCount = $value["statistics"]["viewCount"];
				$likeCount = $value["statistics"]["likeCount"];
				$dislikeCount = $value["statistics"]["dislikeCount"];
				$commentCount = $value["statistics"]["commentCount"];
				
				//Ajout de la catégorie si cette dernière n'existe pas
				$this -> ajouterCatégorie($categoryId);
				
				switch($this -> vidéos[$ID]){
					//La vidéo avait déjà fait partie du classement et existe donc déjà. Un update sera suffisant.
					case 1:
						$this -> bdd -> executer("UPDATE video SET actif = 1, viewCount = $viewCount, likeCount = $likeCount, dislikeCount = $dislikeCount, commentCount = $commentCount WHERE videoID = $ID_BDD");
						break;
					
					//La vidéo est nouvelle au classement. On insert.
					case 2:
						$this -> bdd -> executer("INSERT INTO video(videoID, publishedAt, title, viewCount, likeCount, dislikeCount, categoryID, commentCount)VALUES($ID_BDD, $publishedAt, $title, $viewCount, $likeCount, $dislikeCount, $categoryId, $commentCount)");
						break;
				}
			}
		}
	}
	
	/*
	 * Lecture dans la BDD afin de lire les catégories actuelles
	 */
	private function initialiserCatégories(){
		//Lecture des catégories uniquement si ça n'a pas déjà été fait
		if(count($this -> catégories) <= 0){
			//Lecture de toutes les catégories
			$résultats = $this -> bdd -> executer("SELECT id, title FROM categorie");
			
			//Initialisation de notre attribut
			foreach($résultats as $key => $ligne){
				$this -> catégories[$ligne -> id] = $ligne -> title;
			}
		}
	}
	
	/*
	 * Création ou non d'une catégorie si cette dernière n'existe pas déjà.
	 * @param String $categoryID ID d'une catégorie.
	 */
	private function ajouterCatégorie($categoryID){
		$api_key = YOUTUBE_APIKEY;
		
		//On ne fait rien si la catégorie existe déjà
		if(!array_key_exists($categoryID, $this -> catégories)){
			//On demande à youtube des infos sur la catégorie que nous ne connaissons pas encore
			$tab = json_decode(file_get_contents("https://www.googleapis.com/youtube/v3/videoCategories?key=$api_key&part=snippet&hl=fr_FR&id=$categoryID"), true);
			
			//La demande a bien été traitée et renvoie quelque chose ?
			if(count($tab["items"]) > 0){
				$id = $tab["items"][0]["id"];
				$title = $this -> bdd -> escape($tab["items"][0]["snippet"]["title"],2);
				
				//Ajout en base
				$this -> bdd -> executer("INSERT INTO categorie(id, title)VALUES($id, $title)");
				
				//Ajout dans la liste des catégories
				$this -> catégories[$tab["items"][0]["id"]] = $tab["items"][0]["snippet"]["title"];
			}
		}
	}
	
	/*
	 * Création de l'historique sur le classement général de la veille, uniquement les 10 premiers.
	 */
	public function créerHistorique(){
		//Quel jour étions nous hier ?
		$hier = date("Y-m-d", time() - 3600 * 24);

		//L'historique de la veille a déjà été fait ?
		if(!$this -> bdd -> executerTrouve("SELECT 1 FROM historique WHERE periode = '$hier'")){
			//Création de l'historique à partir des 10 premiers du classement général
			$résultats = $this -> bdd -> executer("
				INSERT INTO historique(periode, globalCount, videoID)
				SELECT '$hier', V.total, V.videoID from V_ClassementGlobal AS V limit 10
			");
		}
	}
	
	/*
	 * Les vidéos qui ne sont plus classées mais qui l'ont été sont archivés (actif modifié à 0).
	 */
	public function créerArchive(){
		$this -> bdd -> executer("
			UPDATE 
				video v
				INNER JOIN V_HorsClassement hc on v.videoID = hc.videoID
				LEFT JOIN V_HistoriqueVideos h ON h.videoID = hc.videoID
			SET v.actif = 0
			WHERE h.videoID IS NOT NULL
		");
	}
	
	/*
	 * Les vidéos qui ne sont plus classées et qui ne l'ont jamais été sont supprimés. On évite ainsi de faire des requêtes trop importantes à Youtube.
	 */
	public function supprimerVidéos(){
		$this -> bdd -> executer("DELETE V.* FROM video V INNER JOIN V_HorsClassement HC ON HC.videoID = V.videoID WHERE HC.actif = 1");
	}
	
	/*
	 * On récupère toutes les vidéos actifs dans la base (ou celles qui viennent d'être ajoutées).
	 * @return Array Vidéos actives
	 */
	public function getActifs(){
		$vidéos = array();
		
		//On passe en revue toutes les vidéos
		foreach($this -> vidéos as $videoID => $statut){
			//Seules les vidéos actives sont gardées
			if($statut > 0)$vidéos[] = $videoID;
		}
		
		return $vidéos;
	}
	
	public function getCatégories(){
		return $this -> catégories;
	}
	
	public function getVidéos(){
		return $this -> vidéos;
	}
}

?>