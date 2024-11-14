<?php

namespace iutnc\nrv\action;

use iutnc\nrv\festival\Soiree;
use iutnc\nrv\render\SoireeRender;
use iutnc\nrv\render\SpectacleRender;
use iutnc\nrv\repository\NRVRepository;

class DisplayAllSoirees extends Action
{
    protected function executeGet(): string {
        $r = NRVRepository::getInstance();
        $soirees = $r->getAllSoirees();
        $html = "<h1>Soirées</h1>";
        foreach ($soirees as $soiree) {
            $render = new SoireeRender($soiree);
            $html .= $render->renderFull();
        }
        return $html;
    }

    protected function executePost(): string {
        return $this->executeGet();
    }
}