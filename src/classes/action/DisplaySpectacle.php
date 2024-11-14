<?php

namespace iutnc\nrv\action;
//affichage d'un spectacle en particulier (détaillé)
use iutnc\nrv\render\SoireeRender;
use iutnc\nrv\render\SpectacleRender;
use iutnc\nrv\repository\NRVRepository;

class DisplaySpectacle extends Action{

    protected function executeGet(): string {
        if (!isset($_GET['id'])){
            return 'Spectacle non trouvé';
        }
        $r = NRVRepository::getInstance();
        $spectacle = $r->getSpectacleFromId($_GET['id']);
        $render = new SpectacleRender($spectacle);
        $html = $render->renderFull();
        // Ajout du formulaire caché pour filtrer par lieu
        $r = NRVRepository::getInstance();
        $infos = $r->getInfosFromSpectacleId($spectacle->idSpectacle);
        $lieu = $infos['idLieu'];
        $date = $infos['dateSoiree'];
        $style = $spectacle->idStyle;

        $r = NRVRepository::getInstance();
        $soiree = $r->getSoireeFromSpectacleId($_GET['id']);
        $soiree = $r->getSoireeFromId($soiree);
        $soiree = new SoireeRender($soiree);
        $soiree = $soiree->renderFull();

        $html .= <<<HTML
            <br>
            <form method="post" action="?action=spectacles" style="display:inline;">
                <input type="hidden" name="lieu" value="{$lieu}">
                <button type="submit" class="button">Voir les spectacles se déroulant au même endroit</button>
            </form>
            <br>
            </br>
            <form method="post" action="?action=spectacles" style="display:inline;">
                <input type="hidden" name="style" value="{$style}">
                <button type="submit" class="button">Voir les spectacles du même style</button>
            </form>
            <br>
            </br>
            <form method="post" action="?action=spectacles" style="display:inline;">
                <input type="hidden" name="date" value="{$date}">
                <button type="submit" class="button">Voir les spectacles à la même date</button>
            </form> 
            <br>
            </br>
                <h2>Détail de la soiree :</h2>
                <p>$soiree</p>                
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