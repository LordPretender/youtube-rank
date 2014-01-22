<?php

header('Content-Type: text/html;charset=UTF-8');

$requestLiked = json_decode(file_get_contents("https://www.googleapis.com/youtube/v3/videos?key=AIzaSyC04PkGS3Mp88--S1UDI74cYKteJA1u94M&part=snippet,statistics&fields=items&id=9bZkp7q19f0"), true);

require_once 'classes/bdd.php';
$bdd = new BDD();

$title = $requestLiked["items"][0]["snippet"]["title"];
$title = $bdd -> escape($title,2);
$resultat = $bdd -> executer("UPDATE video SET title = $title WHERE videoID = '9bZkp7q19f0'");

echo "2";


?>