<?php
/**
 * Routes afficheur manipulation - 'afficheur_fournisseur' table concerned
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
 * Affecte afficheur to fournisseur
 * url - /afficheurs_fournisseurs
 * method - POST
 * @params id_afficheur, id_fournisseurs
 */
$app->post('/afficheur_fournisseurs/:id_afficheur', 'authenticate', function($id_afficheur) use ($app, $db, $logManager) {
    verifyRequiredParams(array('fournisseurs_id')); // vérifier les paramétres requises
    global $user_connected;

    //recupérer les valeurs POST
    $request_params = json_decode($app->request()->getBody(), true);

    $inserted_fournisseur = FALSE;

    foreach ($request_params["fournisseurs_id"] as $fournisseur)
    {
        $data = array(
            "afficheur_id" => $id_afficheur,
            "fournisseur_id" => $fournisseur["id"]
        );

        $insert_afficheur_fournisseur = $db->entityManager->afficheur_fournisseur()->insert($data);

        if($insert_afficheur_fournisseur != FALSE || is_array($insert_afficheur_fournisseur))
        {
            $inserted_fournisseur = TRUE;
            $logManager->setLog($user_connected, buildSqlQueryInsert("afficheur_fournisseur", $data), false); //afficheur_fournisseur insérée
        }
        else
        if($insert_afficheur_fournisseur == FALSE)
        {
            $inserted_fournisseur = FALSE;
            $logManager->setLog($user_connected, buildSqlQueryInsert("afficheur_fournisseur", $data), true); //afficheur_fournisseur non insérée
        }
    }

    if($inserted_fournisseur == TRUE)
        echoResponse(201, true, "fournisseurs ajoutes", NULL);
    else if($inserted_fournisseur == FALSE)
        echoResponse(400, false, "Erreur ajout", NULL);
});