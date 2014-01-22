<?php

class BDD {
	private $host, $nom, $user, $pwd, $driver, $options;
	private $connexion = null;
	
	public function __construct(){
		//Configuration de l'accès à la base
		$this -> host = "";
		$this -> nom = "";
		$this -> user = "";
		$this -> pwd = "";
		$this -> driver = "mysql"; //MYSQL (mysql) ou PostgreSQL (pgsql)
		$this -> options = array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8");
		
		//Connexion à la base via PDO
		try {
			$this -> connexion = new PDO($this -> driver . ":dbname=" . $this -> nom . ";host=" . $this -> host, $this -> user, $this -> pwd, $this -> options);
			
			$this -> connexion -> exec("SET CHARACTER SET utf8");
			$this -> connexion -> setAttribute(constant("PDO::ATTR_ERRMODE"), constant("PDO::ERRMODE_EXCEPTION"));
		} catch (PDOException $e){
			echo 'Connexion échouée : ' . $e -> getMessage();
			die;
		}

	}
	
	/*
	 * Exécution d'une requête SQL
	 * @param String $sql Requête à exécuter
	 * @return Array Résultats de la requête SQL exécutée.
	 */
	public function executer($sql){
		//Execution de la requête
		$this -> connexion -> exec("SET NAMES utf8");
		$curseur = $this -> connexion -> query($sql);
		$curseur -> setFetchMode(PDO::FETCH_OBJ);
		
		//Lecture des résultats
		if(strtolower(substr($sql, 0, 6)) == 'select')$résultats = $curseur->fetchAll();
		
		//On ferme le curseur
		$curseur -> closeCursor();
		
		return $résultats;
	}
	
	/*
	 * Exécution d'une requête SQL afin de savoir si la requête renvoi ou non des résultats.
	 * @param String $sql Requête à exécuter
	 * @return Boolean Vrai si la requête retourne des résultats. Faux, sinon.
	 */
	public function executerTrouve($sql){
		$trouvé = false;
		
		//Exécution de la requête
		$résultats = $this -> executer($sql);
		
		//La requête exécutée a des résultats ?
		$trouvé = count($résultats) > 0 ? TRUE : FALSE;
		
		return $trouvé;
	}
	
	/**
	 * Permet d'échapper du contenu pour être utilisé dans une requête SQL.
	 * @param String $element Element à échapper
	 * @param Int $type Type d'élément à échapper :
	 * 		<ul>
	 * 			<li>2 : String</li>
	 * 		</ul>			
	 */
	public function escape($element, $type){
		//Lecture du type
		switch ($type){
			default:
				$type = PDO::PARAM_STR;
		}
		
		return $this -> connexion -> quote($element, $type);
	}
}

?>