<?php

namespace iutnc\nrv\action;
//affichage d'un spectacle en particulier (détaillé)
use iutnc\nrv\render\SoireeRender;
use iutnc\nrv\render\SpectacleRender;
use iutnc\nrv\repository\NRVRepository;

class DisplaySpectacle extends Action{

    protected function executeGet(): string {
        // Récupération du spectacle passé dans l'URL
        if (!isset($_GET['id'])){
            return 'Spectacle non trouvé';
        }
        // Récupération du spectacle avec l'id passé en paramètre
        $r = NRVRepository::getInstance();
        $spectacle = $r->getSpectacleFromId($_GET['id']);
        // on crée un objet SpectacleRender pour afficher le spectacle
        $render = new SpectacleRender($spectacle);
        $html = $render->renderFull();

        // on récupère les infos du spectacle pour afficher les boutons de recherche
        $infos = $r->getInfosFromSpectacleId($spectacle->idSpectacle);
        $lieu = $infos['idLieu'];
        $date = $infos['dateSoiree'];
        $style = $spectacle->idStyle;

        // on récupère la soirée du spectacle pour l'afficher
        $soiree = $r->getSoireeFromSpectacleId($_GET['id']);
        $soiree = $r->getSoireeFromId($soiree);
        $soiree = new SoireeRender($soiree);
        $soiree = $soiree->renderFull();

        // on utilise des formulaires cachés pour envoyer recherche des spectacles avec les mêmes infos
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

    /**
     * Methode post de la classe ne fait rien
     */
    protected function executePost(): string {
        // on redirige vers la page page get
        return $this->executeGet();
    }
}