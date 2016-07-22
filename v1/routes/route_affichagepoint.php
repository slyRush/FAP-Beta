<?php
/**
 * Routes affichagepoint manipulation - 'affichagepoint' table concerned
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
 * Get all affichagepoint
 * url - /affichagepoints
 * method - GET
 */
$app->get('/affichagepoints', 'authenticate', function() use ($app, $db, $logManager) {
    $affichagepoints = $db->entityManager->affichagepoint();
    $affichagepoints_array = JSON::parseNotormObjectToArray($affichagepoints);
    global $user_connected;

    if(count($affichagepoints_array) > 0)
    {
        $data_affichagepoints = array();
        foreach ($affichagepoints as $affichagepoint) array_push($data_affichagepoints, $affichagepoint);

        $logManager->setLog($user_connected, (string)$affichagepoints, false);
        echoResponse(200, true, "Tous les affichagepoints retournés", $data_affichagepoints);
    }
    else
    {
        $logManager->setLog($user_connected, (string)$affichagepoints, true);
        echoResponse(400, false, "Une erreur est survenue.", NULL);
    }
});

/**
* Get one affichagepoint by id
* url - /affichagepoints/:id
* method - GET
*/
$app->get('/affichagepoints/:id', 'authenticate', function($id) use ($app, $db, $logManager) {
    $affichagepoint = $db->entityManager->affichagepoint[$id];
    global $user_connected;

    if(count($affichagepoint) > 0)
    {
        $logManager->setLog($user_connected, (string)$affichagepoint, false);
        echoResponse(200, true, "affichagepoint retourné(e)", $affichagepoint);
    }
    else
    {
        $logManager->setLog($user_connected, (string)$affichagepoint, true);
        echoResponse(400, false, "Une erreur est survenue.", NULL);
    }
});

/**
* Create new affichagepoint
* url - /affichagepoints/
* method - POST
* @params name
*/
$app->post('/affichagepoints', 'authenticate', function() use ($app, $db, $logManager) {
    verifyRequiredParams(array('title','description','latitude','longitude')); // vérifier les paramédtres requises
    global $user_connected;

    //recupérer les valeurs POST
    $request_params = json_decode($app->request()->getBody(), true);
    $name_affichagepoint = $request_params["name"];

    $data = array(
        "name" => $name_affichagepoint
    );

    $insert_affichagepoint = $db->entityManager->affichagepoint()->insert($data);

    if($insert_affichagepoint == FALSE)
    {
        $logManager->setLog($user_connected, buildSqlQueryInsert("affichagepoint", $data), true);
        echoResponse(400, false, "Oops! Une erreur est survenue lors de l'insertion du affichagepoint", NULL);
    }
    else
    if($insert_affichagepoint != FALSE || is_array($insert_affichagepoint))
    {
        $logManager->setLog($user_connected, buildSqlQueryInsert("affichagepoint", $data), false);
        echoResponse(201, true, "affichagepoint ajouté(e) avec succès", $insert_affichagepoint);
    }
});

/**
* Update one affichagepoint
* url - /affichagepoints/:id
* method - PUT
* @params name
*/
$app->put('/affichagepoints/:id', 'authenticate', function($id) use ($app, $db, $logManager) {
    verifyRequiredParams(array('title','description','latitude','longitude')); // vérifier les paramètres requises
    global $user_connected;

    //recupérer les valeurs POST
    $request_params = json_decode($app->request()->getBody(), true);
    $name_affichagepoint = $request_params["name"];

    $affichagepoint = $db->entityManager->affichagepoint[$id];
    if($affichagepoint)
    {
        $testSameData = isSameData($affichagepoint, $request_params);

        if(!in_array("FALSE", $testSameData)) //c'est la même data, pas de changement
        {
            $logManager->setLog($user_connected, (string)$affichagepoint, false);
            echoResponse(200, true, "affichagepoint mis à jour avec succès. Id : $id", NULL);
        }
        else
        {
            $update_affichagepoint = $affichagepoint->update(array("name" => $name_affichagepoint));

            if($update_affichagepoint == FALSE)
            {
                $logManager->setLog($user_connected, (string)$affichagepoint, true);
                echoResponse(400, false, "Oops! Une erreur est survenue lors de la mise à jour du affichagepoint", NULL);
            }
            else
            if($update_affichagepoint != FALSE || is_array($update_affichagepoint))
            {
                $logManager->setLog($user_connected, (string)$affichagepoint, false);
                echoResponse(201, true, "affichagepoint mis à jour avec succès", NULL);
            }
        }
    }
    else
    {
        $logManager->setLog($user_connected, (string)$affichagepoint, true);
        echoResponse(400, false, "affichagepoint inexistant !!", NULL);
    }

});

/**
* Delete one affichagepoint
* url - /affichagepoints/:id
* method - DELETE
* @params name
*/
$app->delete('/affichagepoints/:id', 'authenticate', function($id) use ($app, $db, $logManager) {
    $affichagepoint = $db->entityManager->affichagepoint[$id];
    global $user_connected;

    if($db->entityManager->application_affichagepoint("affichagepoint_id", $id)->delete())
    {
        if($affichagepoint && $affichagepoint->delete())
        {
            $logManager->setLog($user_connected, (string)$affichagepoint, false);
            echoResponse(200, true, "affichagepoint id : $id supprimé avec succès", NULL);
        }
        else
        {
            $logManager->setLog($user_connected, (string)$affichagepoint, true);
            echoResponse(200, false, "affichagepoint id : $id pas supprimé. Erreur !!", NULL);
        }
    }
    else
    {
        $logManager->setLog($user_connected, (string)$affichagepoint, true);
        echoResponse(400, false, "Erreur lors de la suppression de la affichagepoint ayant l'id $id : affichagepoint inexistant !!", NULL);
    }
});