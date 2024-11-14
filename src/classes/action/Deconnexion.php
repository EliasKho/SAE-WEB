<?php

namespace iutnc\nrv\action;

use iutnc\nrv\action\Action;
use iutnc\nrv\repository\NRVRepository;

/**
 * Classe Deconnexion : permet de se déconnecter
 */
class Deconnexion extends Action
{
    /**
     *  déconnecte l'utilisateur
     */
    protected function executeGet(): string
    {
        // On détruit la session
        session_unset();
        session_destroy();
        // On redirige vers la page d'accueil
        return "Déconnexion avec succès";
    }

    /**
     * déconnecte l'utilisateur
     */
    protected function executePost(): string
    {
        // aucune methode post donc on renvoie le get
        return $this->executeGet();
    }
}