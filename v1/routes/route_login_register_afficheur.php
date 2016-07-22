<?php
/**
 * Routes login manipulation - 'users' table concerned
 * ----------- METHODES sans authentification---------------------------------
 */

include_once dirname(__DIR__)  . '/includes/functions/set_headers.php';

require_once dirname(__DIR__)  . '/includes/functions/utils.php';
require_once dirname(__DIR__)  . '/includes/functions/json.php';
require_once dirname(__DIR__)  . '/includes/functions/security_api.php';
require_once dirname(__DIR__)  . '/includes/db_manager/dbManager.php';
require_once dirname(__DIR__)  . '/includes/pass_hash.php';
require_once dirname(__DIR__)  . '/includes/Log.class.php';

global $app;
$db = new DBManager();
$logManager = new Log();

/**
 * Login Utilisateur
 * url - /login
 * method - POST
 * @params email, password
 */
$app->post('/login/afficheur', function() use ($app, $db, $logManager) {
    verifyRequiredParams(array('email','password')); // vérifier les paramètres requises

    //recupérer les valeurs POST
    $request_params = json_decode($app->request()->getBody(), true);
    $email = $request_params["email"]; //$app->request()->post('password');
    $password = $request_params["password"]; //$app->request()->post('email');

    validateEmail($email); // valider l'adresse email

    $afficheur_query = $db->entityManager->afficheur("email = ?", $email);
    $afficheur = $afficheur_query->fetch();

    if( $afficheur != FALSE ) //false si l'email de l'afficheur n'est pas trouvé
    {
        if (PassHash::check_password($afficheur['password_hash'], $password))
        {
            $afficheur = JSON::removeNode($afficheur, "password_hash"); //remove password_hash column from $user
            $logManager->setLog($afficheur, (string)$afficheur_query, false);
            echoResponse(200, true, "Connexion réussie", $afficheur); // Mot de passe utilisateur est correcte
        }
        else
        {
            $logManager->setLog($afficheur, (string)$afficheur_query, true);
            echoResponseWithLog(200, false, "Mot de passe incorrecte", NULL); // erreur inconnue est survenue
        }
    }
    else
    {
        $logManager->setLog($afficheur, (string)$afficheur_query, true);
        echoResponse(200, false, "Echec de la connexion. identificateurs incorrectes", NULL); // identificateurs de l'utilisateur sont erronés
    }
});

/**
* Enregistrement de l'utilisateur
* url - /register
* methode - POST
* params - name, email, password
*/
$app->post('/register/afficheur', function() use ($app, $db, $logManager) {
    verifyRequiredParams(array('email','password', 'username', 'forname', 'firstname', 'party')); // vérifier les paramédtres requises

    // lecture des params de post
    $request_params = json_decode($app->request()->getBody(), true);
    $email = $request_params['email'];
    $password = $request_params['password'];
    $username = $request_params['username'];
    $forname = $request_params['forname'];
    $firstname = $request_params['firstname'];
    $party = $request_params['party'];

    validateEmail($email); //valider adresse email

    $afficheur_exist_query = $db->entityManager->afficheur("email = ?", $email);
    $afficheur_exist = $db->entityManager->afficheur("email = ?", $email)->fetch();

    if($afficheur_exist == FALSE) //email afficheur doesn't exist
    {
        $data = array(
            "email"             => $email,
            "username"          => $username,
            "forname"           => $forname,
            "firstname"         => $firstname,
            "party"             => $party,
            "api_key"           => generateApiKey(), // Générer API key
            "password_hash"     => PassHash::hash($password), //Générer un hash de mot de passe
        );

        $insert_afficheur = $db->entityManager->afficheur()->insert($data);

        if($insert_afficheur == FALSE)
        {
            $logManager->setLog(null, (string)$afficheur_exist_query . " / " . buildSqlQueryInsert("afficheur", $data), true);
            echoResponse(400, false, "Oops! Une erreur est survenue lors de l'inscription", NULL);
        }
        else
        {
            if($insert_afficheur != FALSE || is_array($insert_afficheur))
            {
                $logManager->setLog(null, (string)$afficheur_exist_query . " / " . buildSqlQueryInsert("afficheur", $data), false);
                echoResponse(201, true, "afficheur inscrit avec succès", $insert_afficheur);
            }
        }
    }
    else
    {
        if($afficheur_exist != FALSE || count($afficheur_exist) > 1)
        {
            $logManager->setLog(null, (string)$afficheur_exist_query, false);
            echoResponse(400, false, "Désolé, cet E-mail éxiste déja", NULL);
        }
    }
});