<?php

namespace iutnc\nrv\action;

use iutnc\nrv\render\SpectacleRender;
use iutnc\nrv\repository\NRVRepository;

class Festival extends Action {

    protected function executeGet(): string {
        $r = NRVRepository::getInstance();
        $spectacles = $r->getAllSpectacles();
        $html = '';
        foreach ($spectacles as $spectacle) {
            $render = new SpectacleRender($spectacle);
            $html .= $render->renderCompact();
        }
        return $html;
    }

    protected function executePost(): string{
        return $this->executeGet();
    }
}