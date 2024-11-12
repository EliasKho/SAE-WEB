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
        $html = $render->renderFull();
        // Ajout du formulaire caché pour filtrer par lieu
        $r = NRVRepository::getInstance();
        $lieu = $r->getLieuFromSpectacle($spectacle->idSpectacle);
        $html .= <<<HTML
            <br>
            <form method="post" action="?action=spectacles" style="display:inline;">
                <input type="hidden" name="lieu" value="{$lieu}">
                <button type="submit">Voir les spectacles se déroulant au même endroit</button>
            </form>
        HTML;

        $html .= "</div>";
        return $html;
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