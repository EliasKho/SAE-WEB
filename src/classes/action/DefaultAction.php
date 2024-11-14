<?php

namespace iutnc\nrv\action;

/**
 * Action par défaut
 */
class DefaultAction extends Action{
    /**
     * affiche un message de bienvenue
     */
    public function executeGet() : string{
        // on retourne un message de bienvenue
        return "<p>Bienvenue sur l'application NRV !</p>";
    }

    /**
     * affiche un message de bienvenue
     */
    public function executePost() : string{
        // on retourne la methode GET
        return $this->executeGet();
    }
}