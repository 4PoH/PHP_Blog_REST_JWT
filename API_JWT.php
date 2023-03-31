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
    require('..\TP3\jwt_utils.php');
    include('..\TP3\connBDD.php');

    ///Préparation de la requête sans les variables (marqueurs : nominatifs)
   

    $username_verified = 'nino';
    $password_verified = 'rigal';
    $id_user_verified = '0123456789';

    /// Paramétrage de l'entête HTTP (pour la réponse au Client)
    header("Content-Type:application/json");

    function get_idrole($username,$password,$linkpdo){
        $requeteGetIdUtilisateur = $linkpdo->prepare("SELECT idRole FROM utilisateur WHERE nom = :p_username AND mdp = :p_password");
            if($requeteGetIdUtilisateur == false) { 
                die('Erreur préparation requête');
            }

            $requeteGetIdUtilisateur->bindParam(':p_username',$username);
            $requeteGetIdUtilisateur->bindParam(':p_password',$password);

            $resExec = $requeteGetIdUtilisateur->execute();

            if(!$resExec){
                die('Erreur exécution requête');
            }

            $matchingData = $requeteGetIdUtilisateur->fetchAll(PDO::FETCH_COLUMN);

            if($matchingData == null){
                throw new Exception('User/password invalid.');
            }
        return $matchingData[0];
    }


    /// Identification du type de méthode HTTP envoyée par le client
    $http_method = $_SERVER['REQUEST_METHOD'];
    switch ($http_method){
        case "POST" :
            /// Récupération des données envoyées par le Client
            $data = (array) json_decode(file_get_contents('php://input'),TRUE);
            if(!is_null($data)) {
                $username = $data['username'];
                $password = $data['password'];
                var_dump($username,$password);
                $time_of_validity = 60 * 20160;
                
                /* Creer une fonction qui recuperer le id user*/
                try {
                    $id_role = get_idrole($username,$password,$linkpdo);
                } catch (Exception $e) {
                    die('Erreur retour requête : ' . $e->getMessage());
                }
                $headers = array('alg'=>'HS256', 'typ'=>'JWT');
                $payload = array('idRole'=> $id_role, 'nom'=> $username, 'exp'=>(time() + $time_of_validity));
                $JWT = generate_jwt($headers,$payload);
                if(is_jwt_valid($JWT)) {
                    deliver_response(201, "Paire identifiant mot de passe valide voici le token", $JWT);
                } else {
                    deliver_response(403, "Paire identifiant mot de passe valide problème à la création du token", $data);
                }
            } else {
                deliver_response(401, "Erreur dans les données transmises (client->serveur)", $data);
            }
        break;

        case "GET":
            /// Récupération des données envoyées par le Client
            $JWT = get_bearer_token();
            // var_dump($JWT);
            $validity = is_jwt_valid($JWT);
            if($validity) {
                deliver_response(201, "Le Token JWT est valide", $validity);
            } else {
                deliver_response(402, "Le Token JWT est invalide", $validity);
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