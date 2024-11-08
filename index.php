<?php

declare (strict_types = 1);
require_once 'loader/vendor/autoload.php';
session_start();
iutnc\nrv\repository\NRVRepository::setConfig( 'config.db.ini' );

$dispatcher = new iutnc\nrv\dispatcher\Dispatcher();
$dispatcher->run();