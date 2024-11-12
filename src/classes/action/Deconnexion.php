<?php

namespace iutnc\nrv\action;

use iutnc\nrv\action\Action;
use iutnc\nrv\repository\NRVRepository;

class Deconnexion extends Action
{

    protected function executeGet(): string
    {
        session_unset();
        session_destroy();
        return "Déconnexion avec succès";
    }

    protected function executePost(): string
    {
        return $this->executeGet();
    }
}