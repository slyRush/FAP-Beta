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
$app->post('/login/travailleur', function() use ($app, $db, $logManager) {
    verifyRequiredParams(array('email','password')); // vérifier les paramètres requises

    //recupérer les valeurs POST
    $request_params = json_decode($app->request()->getBody(), true);
    $email = $request_params["email"];
    $password = $request_params["password"];

    validateEmail($email); // valider l'adresse email

    $travailleur_query = $db->entityManager->travailleur("email = ?", $email);
    $travailleur = $travailleur_query->fetch();

    if( $travailleur != FALSE ) //false si l'email de l'travailleur n'est pas trouvé
    {
        if (PassHash::check_password($travailleur['password_hash'], $password))
        {
            $travailleur = JSON::removeNode($travailleur, "password_hash"); //remove password_hash column from $user
            $logManager->setLog($travailleur, (string)$travailleur_query, false);
            echoResponse(200, true, "Connexion réussie", $travailleur); // Mot de passe utilisateur est correcte
        }
        else
        {
            $logManager->setLog($travailleur, (string)$travailleur_query, true);
            echoResponseWithLog(200, false, "Mot de passe incorrecte", NULL); // erreur inconnue est survenue
        }
    }
    else
    {
        $logManager->setLog($travailleur, (string)$travailleur_query, true);
        echoResponse(200, false, "Echec de la connexion. identificateurs incorrectes", NULL); // identificateurs de l'utilisateur sont erronés
    }
});

/**
* Enregistrement de l'utilisateur
* url - /register
* methode - POST
* params - name, email, password
*/
$app->post('/register/travailleur', function() use ($app, $db, $logManager) {
    verifyRequiredParams(array('username','email','password', 'forname', 'firstname', 'fournisseur_id')); // vérifier les paramédtres requises

    // lecture des params de post
    $request_params = json_decode($app->request()->getBody(), true);
    $username = $request_params['username'];
    $email = $request_params['email'];
    $password = $request_params['password'];
    $forname = $request_params['forname'];
    $firstname = $request_params['firstname'];
    $fournisseur_id = $request_params['fournisseur_id'];

    validateEmail($email); //valider adresse email

    $travailleur_exist_query = $db->entityManager->travailleur("email = ?", $email);
    $travailleur_exist = $db->entityManager->travailleur("email = ?", $email)->fetch();

    if($travailleur_exist == FALSE) //email travailleur doesn't exist
    {
        $data = array(
            "username"          => $username,
            "email"             => $email,
            "forname"           => $forname,
            "firstname"         => $firstname,
            "fournisseur_id"    => $fournisseur_id,
            "api_key"           => generateApiKey(), // Générer API key
            "password_hash"     => PassHash::hash($password), //Générer un hash de mot de passe
        );

        $insert_travailleur = $db->entityManager->travailleur()->insert($data);

        if($insert_travailleur == FALSE)
        {
            $logManager->setLog(null, (string)$travailleur_exist_query . " / " . buildSqlQueryInsert("travailleur", $data), true);
            echoResponse(400, false, "Oops! Une erreur est survenue lors de l'inscription", NULL);
        }
        else
        {
            if($insert_travailleur != FALSE || is_array($insert_travailleur))
            {
                $logManager->setLog(null, (string)$travailleur_exist_query . " / " . buildSqlQueryInsert("travailleur", $data), false);
                echoResponse(201, true, "travailleur inscrit avec succès", $insert_travailleur);
            }
        }
    }
    else
    {
        if($travailleur_exist != FALSE || count($travailleur_exist) > 1)
        {
            $logManager->setLog(null, (string)$travailleur_exist_query, false);
            echoResponse(400, false, "Désolé, cet E-mail éxiste déja", NULL);
        }
    }
});