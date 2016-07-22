<?php
/**
 * Routes travailleur manipulation - 'travailleur' table concerned
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
 * Get all travailleur
 * url - /travailleurs
 * method - GET
 */
$app->get('/travailleurs', 'authenticate', function() use ($app, $db, $logManager) {
    $travailleurs = $db->entityManager->travailleur();
    $travailleurs_array = JSON::parseNotormObjectToArray($travailleurs);

    global $user_connected;

    if(count($travailleurs_array) > 0)
    {
        $data_travailleurs = array();

        foreach ($travailleurs as $travailleur) array_push($data_travailleurs, JSON::removeNode($travailleur, "password_hash"));

        $logManager->setLog($user_connected, (string)$travailleurs, false);
        echoResponse(200, true, "Tous les auteurs retournés", $data_travailleurs);
    }
    else
    {
        $logManager->setLog($user_connected, (string)$travailleurs, false);
        echoResponse(400, true, "Une erreur est survenue.", NULL);
    }

});

/**
* Get one travailleur by id
* url - /travailleurs/:id
* method - GET
*/
$app->get('/travailleurs/:id', 'authenticate', function($id) use ($app, $db, $logManager) {
    $travailleurs = $db->entityManager->travailleur[$id];
    global $user_connected;

    if(count($travailleurs) > 0)
    {
        $logManager->setLog($user_connected, (string)$travailleurs, false);
        echoResponse(200, true, "travailleur est retourné", $travailleurs);
    }
    else
    {
        $logManager->setLog($user_connected, (string)$travailleurs, false);
        echoResponse(400, true, "Une erreur est survenue.", NULL);
    }
});