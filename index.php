<?php
// on déclare le typage strict
declare (strict_types = 1);
// on utilise l'autoloader
require_once 'loader/vendor/autoload.php';
// on démarre la session
session_start();
// on charge la configuration de la base de données
iutnc\nrv\repository\NRVRepository::setConfig( 'config.db.ini' );

// on crée une instance de Dispatcher et on lance la méthode run
$dispatcher = new iutnc\nrv\dispatcher\Dispatcher();
$dispatcher->run();