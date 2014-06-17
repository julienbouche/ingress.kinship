<?php 

$serveur = "localhost";
$login = "ingresskinship";
$mdp = "1234";

function db_connect(){
		
	global $serveur, $login, $mdp;
 
	$bdname = "ingresskinship";

	//connexion  au serveur sql
	$db = mysql_connect($serveur, $login, $mdp)
		or die("Une erreur de connexion au serveur de base de donn&eacute;es est survenue.<br/>Merci de r&eacute;essayer plus tard.");

	mysql_select_db($bdname, $db);
}




?>
