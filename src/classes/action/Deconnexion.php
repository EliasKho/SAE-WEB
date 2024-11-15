<?php

namespace iutnc\nrv\action;

use iutnc\nrv\action\Action;
use iutnc\nrv\auth\AuthnProvider;
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
        // On déconnecte l'utilisateur
        AuthnProvider::signout();
        // On renvoie un message de déconnexion
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