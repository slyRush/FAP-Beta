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
$app->post('/login/fournisseur', function() use ($app, $db, $logManager) {
    verifyRequiredParams(array('email','password')); // vérifier les paramètres requises

    //recupérer les valeurs POST
    $request_params = json_decode($app->request()->getBody(), true);
    $email = $request_params["email"]; //$app->request()->post('password');
    $password = $request_params["password"]; //$app->request()->post('email');

    validateEmail($email); // valider l'adresse email

    $fournisseur_query = $db->entityManager->fournisseur("email = ?", $email);
    $fournisseur = $fournisseur_query->fetch();

    if( $fournisseur != FALSE ) //false si l'email de l'fournisseur n'est pas trouvé
    {
        if (PassHash::check_password($fournisseur['password_hash'], $password))
        {
            $fournisseur = JSON::removeNode($fournisseur, "password_hash"); //remove password_hash column from $user
            if($fournisseur["status"] == 0) //fournisseur libre
            {
                $logManager->setLog($fournisseur, (string)$fournisseur_query, false);
                echoResponse(200, true, "Connexion réussie", $fournisseur); // Mot de passe utilisateur est correcte
            }
            else
            {
                $logManager->setLog($fournisseur, (string)$fournisseur_query, true);
                echoResponse(200, true, "Connexion réussie", $fournisseur); // Mot de passe utilisateur est correcte
            }
        }
        else
        {
            $logManager->setLog($fournisseur, (string)$fournisseur_query, true);
            echoResponseWithLog(200, false, "Mot de passe incorrecte", NULL); // erreur inconnue est survenue
        }
    }
    else
    {
        $logManager->setLog($fournisseur, (string)$fournisseur_query, true);
        echoResponse(200, false, "Echec de la connexion. identificateurs incorrectes", NULL); // identificateurs de l'utilisateur sont erronés
    }
});

/**
* Enregistrement de l'utilisateur
* url - /register
* methode - POST
* params - name, email, password
*/
$app->post('/register/fournisseur', function() use ($app, $db, $logManager) {
    verifyRequiredParams(array('email','password', 'username', 'social_name', 'adresse')); // vérifier les paramédtres requises

    // lecture des params de post
    $request_params = json_decode($app->request()->getBody(), true);
    $email = $request_params['email'];
    $password = $request_params['password'];
    $username = $request_params['username'];
    $social_name = $request_params['social_name'];
    $adresse = $request_params['adresse'];

    validateEmail($email); //valider adresse email

    $fournisseur_exist_query = $db->entityManager->fournisseur("email = ?", $email);
    $fournisseur_exist = $db->entityManager->fournisseur("email = ?", $email)->fetch();

    if($fournisseur_exist == FALSE) //email fournisseur doesn't exist
    {
        $data = array(
            "email"             => $email,
            "username"          => $username,
            "social_name"       => $social_name,
            "adresse"           => $adresse,
            "api_key"           => generateApiKey(), // Générer API key
            "password_hash"     => PassHash::hash($password), //Générer un hash de mot de passe
        );

        $insert_fournisseur = $db->entityManager->fournisseur()->insert($data);

        if($insert_fournisseur == FALSE)
        {
            $logManager->setLog(null, (string)$fournisseur_exist_query . " / " . buildSqlQueryInsert("fournisseur", $data), true);
            echoResponse(400, false, "Oops! Une erreur est survenue lors de l'inscription", NULL);
        }
        else
        {
            if($insert_fournisseur != FALSE || is_array($insert_fournisseur))
            {
                $logManager->setLog(null, (string)$fournisseur_exist_query . " / " . buildSqlQueryInsert("fournisseur", $data), false);
                echoResponse(201, true, "fournisseur inscrit avec succès", $insert_fournisseur);
            }
        }
    }
    else
    {
        if($fournisseur_exist != FALSE || count($fournisseur_exist) > 1)
        {
            $logManager->setLog(null, (string)$fournisseur_exist_query, false);
            echoResponse(400, false, "Désolé, cet E-mail éxiste déja", NULL);
        }
    }
});