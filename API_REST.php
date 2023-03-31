<?php
    // Les défauts de conception de l'API REST
    // A ne pas refaire
    // En PUT ID est dans le body -> il devrait être dans l'URL
    // Le PUT codé comme un PATCH, avec PUT on doit réécrire toute la ressource même l'existant
    // Il faut faire de l'URL rewriting
    // Le DELETE est traiter comme default dans le switchcase
    // Faire un case DELETE et default "Requete HTTP"
    // Mauvaise gestion des retours
    // Quand on fait un post il faut réaliser une transaction
    // Sur le linkpdo on peut faire une transaction


    /// Librairies éventuelles (pour la connexion à la BDD, etc.)
    include('../connBDD.php');
    include('../jwt_utils.php');

    /// Paramétrage de l'entête HTTP (pour la réponse au Client)
    header("Content-Type:application/json");

    ///Préparation de la requête sans les variables (marqueurs : nominatifs)
    //Requete poru le case GET
    $requeteGetAllArt = $linkpdo->prepare(" SELECT * FROM article ORDER BY idArticle");
    $requeteGetArtByID = $linkpdo->prepare(" SELECT * FROM article WHERE article.idArticle = :p_idArticle");

    $requeteGetWhoHaveLike = $linkpdo->prepare("SELECT nom FROM evalue WHERE idArticle = :p_idArticle AND pouceBleu = true");
    $requeteGetWhoHaveDislike = $linkpdo->prepare("SELECT nom FROM evalue WHERE idArticle = :p_idArticle AND pouceBleu = false");

    $requeteGetNumberLike = $linkpdo->prepare("SELECT COUNT(poucebleu) as NbPouceBleu FROM evalue WHERE poucebleu = 1 AND idArticle = :p_idArticle GROUP BY idArticle");
    $requeteGetNumberDislike = $linkpdo->prepare("SELECT COUNT(poucebleu) as NbPouceRouge FROM evalue WHERE poucebleu = 0 AND idArticle = :p_idArticle GROUP BY idArticle");

    //Requete poru le case POST
    $requetePostArt = $linkpdo-> prepare(" INSERT INTO article(nom, contenu) VALUES (:p_Auteur, :p_contenu)");

    //Requete poru le case PUT
    $requetePutArt = $linkpdo-> prepare("UPDATE article SET article.contenu = :p_newcontenu WHERE article.idArticle = :p_idArticle");

    //Requete poru le case DELETE
    $requeteDeleteArt = $linkpdo-> prepare(" DELETE FROM article WHERE article.idArticle = :p_idArticle ");
    $requeteNomAuteur = $linkpdo-> prepare(" SELECT nom FROM article WHERE article.idArticle = :p_idArticle ");

    /// Identification du type de méthode HTTP envoyée par le client
    $http_method = $_SERVER['REQUEST_METHOD'];

    function filter_article_for_not_authentified($result) {
        // On transforme en tableau pour parcourir
        $filtered_data = array();
        foreach ($result as $row) {
            $filtered_row = array();
            $filtered_row['nom'] = $row->nom;
            $filtered_row['datePubli'] = $row->datePubli;
            $filtered_row['contenu'] = $row->contenu;
    
            $filtered_data[] = $filtered_row;
        }
    
        return $filtered_data;
    }
    
    function filter_article_for_publisher($result) {
        // On transforme en tableau pour parcourir
        $filtered_data = array();
        foreach ($result as $row) {
            $filtered_row = array();
            $filtered_row['nom'] = $row->nom;
            $filtered_row['datePubli'] = $row->datePubli;
            $filtered_row['contenu'] = $row->contenu;
            $filtered_row['vote'] = $row->vote;
    
            $filtered_data[] = $filtered_row;
        }
    
        return $filtered_data;
    }
    
    $idRoleUser = 0;
    $nom = "";

    if (!empty(get_bearer_token())){
        $payload = get_payload_token(get_bearer_token());
        $idRoleUser = intval($payload->idRole);
        $nom = $payload->nom;
    }

    switch ($http_method){
        /// Cas de la méthode GET
        case "GET" :
            /// Récupération des critères de recherche envoyés par le Client
            //FIND BY ID
            if (!empty($_GET['id'])){
                $id = $_GET['id'];
                if($requeteGetArtByID == false) { 
                    die('Erreur préparation requête');
                }
                $requeteGetArtByID->bindParam(':p_idArticle',$id);

                $resExec=$requeteGetArtByID->execute();
                if(!$resExec){
                    die('Erreur exécution requête');
                }
                $matchingData = $requeteGetArtByID->fetchAll(PDO::FETCH_CLASS);


                // Gestion des utilisateur qui ont liké
                if($requeteGetWhoHaveLike == false) { 
                    die('Erreur préparation requête');
                }
                $requeteGetWhoHaveLike->bindParam(':p_idArticle',$id);

                $resExec=$requeteGetWhoHaveLike->execute();
                if(!$resExec){
                    die('Erreur exécution requête');
                }
                $whoLike = $requeteGetWhoHaveLike->fetchAll(PDO::FETCH_CLASS);

                // Gestion des utilisateur qui ont disliké
                if($requeteGetWhoHaveDislike == false) { 
                    die('Erreur préparation requête');
                }
                $requeteGetWhoHaveDislike->bindParam(':p_idArticle',$id);

                $resExec=$requeteGetWhoHaveDislike->execute();
                if(!$resExec){
                    die('Erreur exécution requête');
                }
                $whoDislike = $requeteGetWhoHaveDislike->fetchAll(PDO::FETCH_CLASS);

                // Gestion des likes
                if($requeteGetNumberLike == false) { 
                    die('Erreur préparation requête');
                }
                $requeteGetNumberLike->bindParam(':p_idArticle',$id);

                $resExec=$requeteGetNumberLike->execute();
                if(!$resExec){
                    die('Erreur exécution requête');
                }
                $NumberLike = $requeteGetNumberLike->fetchAll(PDO::FETCH_CLASS);

                // Gestion des dislikes
                if($requeteGetNumberDislike == false) { 
                    die('Erreur préparation requête');
                }
                $requeteGetNumberDislike->bindParam(':p_idArticle',$id);

                $resExec=$requeteGetNumberDislike->execute();
                if(!$resExec){
                    die('Erreur exécution requête');
                }
                $NumberDislike = $requeteGetNumberDislike->fetchAll(PDO::FETCH_CLASS);


            } else {

                //GET ALL
                if (empty($_GET['id'])){
                    if($requeteGetAllArt == false) {
                        die('Erreur préparation requête');
                    }
                    $resExec=$requeteGetAllArt->execute();
                    if(!$resExec)
                        die('Erreur exécution requête');
                    $matchingData = $requeteGetAllArt->fetchAll(PDO::FETCH_CLASS);
                    // $matchingData = json_encode($matchingData);
                    /// Envoi de la réponse au Client
                } else {
                    /// Envoi de la réponse au Client
                    deliver_response(401, "Erreur dans les données transmises (client->serveur)", NULL);
                }
            }
            
            switch ($idRoleUser){
                case 0;
                    // Modifier les données pour obtenir que ce qu'il a le droit d'avoir
                    /// Envoi de la réponse au Client
                    deliver_response(201, "Voici tout ce que nous avons", filter_article_for_not_authentified($matchingData));
                    break;

                case 1;
                    // Modifier les données pour obtenir que ce qu'il a le droit d'avoir
                    /// Envoi de la réponse au Client
                    var_dump($matchingData);
                    var_dump($whoLike, $NumberLike, $whoDislike, $NumberDislike);
                    if (!empty($_GET['id'])){
                        // Gestion liste like dislike
                        
                        $whoLikeArray = array_column($whoLike, 'nom');
                        $whoLikeRow = array("Who like" => $whoLikeArray);
                        if (count($NumberLike) > 0) {
                            $matchingData[0]->NumberLike = $NumberLike[0]->{'NbPouceBleu'};
                        } else {
                            $matchingData[0]->NumberLike = null;
                        }

                        // vérifier si le tableau $whoLike est vide
                        if (!empty($whoLike)) {
                        $matchingData[0]->whoLike = array_column($whoLike, 'nom');
                        } else {
                        $matchingData[0]->whoLike = array();
                        }

                        if (count($NumberDislike) > 0) {
                            $matchingData[0]->NumberDislike = $NumberDislike[0]->{'NbPouceRouge'};
                        } else {
                            $matchingData[0]->NumberDislike = null;
                        }

                        // vérifier si le tableau $whoDislike est vide
                        if (!empty($whoDislike)) {
                        $matchingData[0]->whoDislike = array_column($whoDislike, 'nom');
                        } else {
                        $matchingData[0]->whoDislike = array();
                        }

                    }
                    
                    deliver_response(201, "Voici tout ce que nous avons", $matchingData);
                    break;

                case 2;
                    // Modifier les données pour obtenir que ce qu'il a le droit d'avoir
                    /// Envoi de la réponse au Client
                    deliver_response(201, "Voici tout ce que nous avons", filter_article_for_publisher($matchingData));
                    break;
                    
            }
            break;
        
        /// Cas de la méthode POST
            case "POST" :
            if($idRoleUser == 2) {
                /// Récupération des données envoyées par le Client
                $postedData = file_get_contents('php://input');
                $data = json_decode($postedData);
                if(!is_null($data)) {

                    $contenu = $data -> contenu;
                    $Auteur = $nom;

                    $linkpdo->beginTransaction();

                    if($requetePostArt == false) {
                        die('Erreur préparation requête');
                    }
                    
                    var_dump($Auteur);
                    $requetePostArt->bindParam(':p_Auteur', $Auteur);
                    $requetePostArt->bindParam(':p_contenu', $contenu);
                    
                    $resExec = $requetePostArt->execute();

                    if(!$resExec){
                        die('Erreur exécution requête ici');
                    }
                    $id = $linkpdo->lastInsertId();
                    if($requeteGetArtByID == false) {
                        die('Erreur préparation requête');
                    }
                    $requeteGetArtByID->bindParam(':p_idArticle',$id);
                    $resExec = $requeteGetArtByID->execute();
                    if(!$resExec){
                        die('Erreur exécution requête la');
                    }
                    $matchingData = $requeteGetArtByID->fetchAll(PDO::FETCH_CLASS);

                    $linkpdo->commit();

                    deliver_response(201, "L'insertion a fonctionnée voici ce qui a été inséré", $matchingData);
                } else {
                    deliver_response(402, "Erreur dans les données transmises (client->serveur)", $postedData);
                }
            } else {
                deliver_response(401, "Erreur vous n'avez pas le role requis pour cette action", null);
            }
            break;

        /// Cas de la méthode PUT
        case "PUT" :
            /// Récupération des données envoyées par le Client
            if($idRoleUser == 2){
                // Récupération de ce qui a été transmis
                $postedData = file_get_contents('php://input');
                $data = json_decode($postedData);
                if(!is_null($data) && !empty($_GET['id'])) {
                    $id = $_GET['id'];
                    if (property_exists($data, 'contenu')) {
                        $contenu = $data->contenu;
                    }
                    
                    if (property_exists($data, 'vote')) {
                        $vote = $data->vote;
                    }

                    // Si c'est un vote
                    if(!empty($vote)){
                        echo 'here';
                    } else if(!empty($contenu)){
                        if($requeteNomAuteur == false) {
                            die('Erreur préparation requête');
                        }
                        $requeteNomAuteur->bindParam(':p_idArticle',$id);
                        $resExec=$requeteNomAuteur->execute();
                        if(!$resExec)
                            die('Erreur exécution requête ');
                        $matchingData = $requeteNomAuteur->fetchAll(PDO::FETCH_CLASS);
                        var_dump($matchingData);
                        $nomAuteur = $matchingData[0]->nom;
                        if($nom == $nomAuteur) {

                            $linkpdo->beginTransaction();

                            if($requetePutArt == false){
                                die('Erreur préparation requête');
                            }
    
                            $requetePutArt->bindParam(':p_idArticle', $id);
                            $requetePutArt->bindParam(':p_newcontenu', $contenu);
    
                            $resExe=$requetePutArt->execute();
                            if(!$resExe){
                                die('Erreur exécution requête');
                            }
                            $linkpdo->commit();
    
                            deliver_response(201, "La modification a fonctionnée", $matchingData);
                        } else {
                            deliver_response(403, "Erreur vous n'êtes pas l'auteur de cet article", null);
                        }

                    } else {
                        deliver_response(402, "Erreur dans les données transmises (client->serveur)", null);
                    }
                } else {
                    deliver_response(402, "Erreur dans les données transmises (client->serveur)", null);
                }
            } else {
                deliver_response(40, "Erreur vous n'avez pas le role requis pour cette action", null);
            }
        break;
        /// Cas de la méthode DELETE
        case "DELETE" :
            //Vérifier si un id est bien donner en parametre
            if (!empty($_GET['id'])){
                $id = $_GET['id'];
            }

            //On cherche le nom de l'auteur de la publication
            if($requeteNomAuteur == false) {
                die('Erreur préparation requête');
            }
            $requeteNomAuteur->bindParam(':p_idArticle',$id);
            $resExec=$requeteNomAuteur->execute();
            if(!$resExec)
                die('Erreur exécution requête ');
            $matchingData = $requeteNomAuteur->fetchAll(PDO::FETCH_CLASS);
            $nomAuteur = $matchingData[0]->nom;


            //var_dump($idRoleUser,$nom,$nomAuteur);
            if($idRoleUser == 1 || ($nom == $nomAuteur)){
                if (!empty($_GET['id'])){
                    $id = $_GET['id'];
                    if($requeteDeleteArt == false){
                        die('Erreur préparation requête');
                    }
                    $requeteDeleteArt->bindParam(':p_idArticle', $id);
                    
                    $resExec=$requeteDeleteArt->execute();
    
                    if(!$resExec)
                        die('Erreur exécution requête');
                    /// Envoi de la réponse au Client
                    deliver_response(201, "La suppression a fonctionnee", NULL);
                } else {
                    deliver_response(402, "Erreur dans les données transmises (client->serveur)", $postedData);
                }
            } else {
                deliver_response(401, "Vous n'avez pas le droit d'effectuer cette action", null);
            }
        break;
    

        default:
            echo "Erreur cas par défault";
            break;
    }
    /// Envoi de la réponse au Client
    function deliver_response($status, $status_message, $data){
        /// Paramétrage de l'entête HTTP, suite
        header("HTTP/1.1 $status $status_message");
        /// Paramétrage de la réponse retournée
        $response['status'] = $status;
        $response['status_message'] = $status_message;
        $response['data'] = $data;
        /// Mapping de la réponse au format JSON
        $json_response = json_encode($response);
        echo $json_response;
    }
?>