<?php
/**
 * Routes travailleur manipulation - 'travailleur_affichagepoint' table concerned
 * ----------- METHODES avec authentification ----------
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
 * Affecte travailleur to affichagepoint
 * url - /travailleurs_affichagepoints
 * method - POST
 * @params id_travailleur, id_affichagepoints
 */
$app->post('/travailleur_affichagepoints/:id_travailleur', 'authenticate', function($id_travailleur) use ($app, $db, $logManager) {
    verifyRequiredParams(array('affichagepoints_id')); // vérifier les paramétres requises
    global $user_connected;

    //recupérer les valeurs POST
    $request_params = json_decode($app->request()->getBody(), true);

    $inserted_affichagepoint = FALSE;

    foreach ($request_params["affichagepoints_id"] as $affichagepoint)
    {
        $data = array(
            "travailleur_id" => $id_travailleur,
            "affichagepoint_id" => $affichagepoint["id"]
        );

        $insert_travailleur_affichagepoint = $db->entityManager->travailleur_affichagepoint()->insert($data);

        if($insert_travailleur_affichagepoint != FALSE || is_array($insert_travailleur_affichagepoint))
        {
            $inserted_affichagepoint = TRUE;
            $logManager->setLog($user_connected, buildSqlQueryInsert("travailleur_affichagepoint", $data), false); //travailleur_affichagepoint insérée
        }
        else
        if($insert_travailleur_affichagepoint == FALSE)
        {
            $inserted_affichagepoint = FALSE;
            $logManager->setLog($user_connected, buildSqlQueryInsert("travailleur_affichagepoint", $data), true); //travailleur_affichagepoint non insérée
        }
    }

    if($inserted_affichagepoint == TRUE)
        echoResponse(201, true, "affichagepoints ajoutes", NULL);
    else if($inserted_affichagepoint == FALSE)
        echoResponse(400, false, "Erreur ajout", NULL);
});