<?php

require '.././libs/Slim/Slim.php';

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();

$user_id = NULL; // ID utilisateur - variable globale
$user_connected = NULL; // user connected -- all info

require_once 'routes/route_affichagepoint.php';
require_once 'routes/route_afficheur.php';
require_once 'routes/route_login_register_afficheur.php';
require_once 'routes/route_afficheur_fournisseur.php';
require_once 'routes/route_fournisseur.php';
require_once 'routes/route_login_register_fournisseur.php';
require_once 'routes/route_travailleur.php';
require_once 'routes/route_login_register_travailleur.php';
require_once 'routes/route_travailleur_affichagepoint.php';

$app->run();