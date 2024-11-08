<?php

namespace iutnc\nrv\action;
//affichage d'un spectacle en particulier (détaillé)
use iutnc\nrv\render\SpectacleRender;
use iutnc\nrv\repository\NRVRepository;

class DisplaySpectacle extends Action{

    protected function executeGet(): string {
        if (!isset($_GET['id'])){
            return 'Spectacle non trouvé';
        }
        $r = NRVRepository::getInstance();
        $spectacle = $r->getSpectacleById($_GET['id']);
        $render = new SpectacleRender($spectacle);
        return $render->renderFull();
    }

    protected function executePost(): string{
        return $this->executeGet();
    }
}