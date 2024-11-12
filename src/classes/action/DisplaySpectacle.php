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

    protected function executePost(): string {
        if (isset($_POST['annuler_spectacle']) && isset($_POST['idSpectacle'])) {
            $idSpectacle = $_POST['idSpectacle'];
            $r = NRVRepository::getInstance();
            $r->annulerSpectacle($idSpectacle);
            return 'Spectacle annulé avec succès.';
        }
        return $this->executeGet();
    }
}