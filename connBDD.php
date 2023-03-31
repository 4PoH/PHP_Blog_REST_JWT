<?php
    //Variables pour les données de connexion à la base de donnée
    $server = 'localhost';
    $db = 'api_php_blog';
    $login = 'root';
    $mdp = '';

///Connexion au serveur MySQL
    try {
        $linkpdo = new PDO("mysql:host=$server;dbname=$db", $login, $mdp);
        //La ligne en dessous là
        $linkpdo -> exec("set names utf8");
    }
    catch (Exception $e) {
        die('Erreur : ' . $e->getMessage());
    }

    /// Récupère le mot de passe pour un identifiant donner
    function get_user_password($linkpdo,$username) {
        $matchingData = '';
        $requeteGetPassword = $linkpdo->prepare("SELECT mdp FROM utilisateur WHERE nom = :p_nom");
        // if($requeteGetPassword == false) {
        //     die('Erreur Préparation Requête : requeteGetPassword'.$e->getMessage());
        // }
        $requeteGetPassword->bindParam(':p_nom', $username);
        $resExec = $requeteGetPassword->execute();
        if(!$resExec){
            die('Erreur Exécution Requête : requeteGetPassword'.$e->getMessage());
        } else {
            $matchingData = $requeteGetPassword->fetchAll();
            $matchingData = json_decode($matchingData);
        }
        /// Envoi de la réponse au Client
        deliver_response(201, "Voici ce que nous avons pour cet ID", $matchingData);
    
    }
?>