<?php

namespace iutnc\nrv\action;

use iutnc\nrv\festival\Soiree;
use iutnc\nrv\render\SoireeRender;
use iutnc\nrv\render\SpectacleRender;
use iutnc\nrv\repository\NRVRepository;

/**
 * Action permettant d'afficher toutes les soirées
 */
class DisplayAllSoirees extends Action
{
    /**
     * methode permettant d'afficher toutes les soirées
     */
    protected function executeGet(): string {
        // On récupère toutes les soirées
        $r = NRVRepository::getInstance();
        $soirees = $r->getAllSoirees();
        // On les affiche toutes avec leur renderer
        $html = "<h1>Soirées</h1>";
        foreach ($soirees as $soiree) {
            // On crée un renderer pour chaque soirée
            $render = new SoireeRender($soiree);
            // On ajoute le html généré par le renderer à la page
            $html .= $render->renderFull();
        }
        return $html;
    }

    /**
     * methode permettant d'afficher toutes les soirées
     */
    protected function executePost(): string {
        // il n'y a pas de post pour cette action donc on appelle la méthode get
        return $this->executeGet();
    }
}