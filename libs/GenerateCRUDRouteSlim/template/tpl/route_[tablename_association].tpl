<?php
/**
 * Routes {$table_name_first_part} manipulation - '{$table_name}' table concerned
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
 * Affecte {$table_name_first_part} to {$table_name_second_part}
 * url - /{$table_name_first_part}s_{$table_name_second_part}s
 * method - POST
 * @params id_{$table_name_first_part}, id_{$table_name_second_part}s
 */
$app->post('/{$table_name}s/:id_{$table_name_first_part}', 'authenticate', function($id_{$table_name_first_part}) use ($app, $db, $logManager) {
    verifyRequiredParams(array('{$table_name_second_part}s_id')); // v�rifier les param�tres requises
    global $user_connected;

    //recup�rer les valeurs POST
    $request_params = json_decode($app->request()->getBody(), true);

    $inserted_{$table_name_second_part} = FALSE;

    foreach ($request_params["{$table_name_second_part}s_id"] as ${$table_name_second_part})
    {
        $data = array(
            "{$table_name_first_part}_id" => $id_{$table_name_first_part},
            "{$table_name_second_part}_id" => ${$table_name_second_part}["id"]
        );

        $insert_{$table_name} = $db->entityManager->{$table_name}()->insert($data);

        if($insert_{$table_name} != FALSE || is_array($insert_{$table_name}))
        {
            $inserted_{$table_name_second_part} = TRUE;
            $logManager->setLog($user_connected, buildSqlQueryInsert("{$table_name}", $data), false); //{$table_name} ins�r�e
        }
        else
        if($insert_{$table_name} == FALSE)
        {
            $inserted_{$table_name_second_part} = FALSE;
            $logManager->setLog($user_connected, buildSqlQueryInsert("{$table_name}", $data), true); //{$table_name} non ins�r�e
        }
    }

    if($inserted_{$table_name_second_part} == TRUE)
        echoResponse(201, true, "{$table_name_second_part}s ajoutes", NULL);
    else if($inserted_{$table_name_second_part} == FALSE)
        echoResponse(400, false, "Erreur ajout", NULL);
});