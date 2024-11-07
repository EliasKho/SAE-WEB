<?php

namespace iutnc\nrv\action;

class DefaultAction extends Action{
    public function executeGet() : string{
        return "<p>Bienvenue sur l'application NRV !</p>";
    }

    public function executePost() : string{
        return $this->executeGet();
    }
}