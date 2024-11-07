<?php

namespace iutnc\nrv\action;

use iutnc\nrv\repository\NRVRepository;

class Festival extends Action {

    protected function executeGet(): string{
        $r = NRVRepository::getInstance();
        $festivals = $r->getAllFestivals();
        $html = "<ul>";
        foreach ($festivals as $festival) {
            $render = new FestivalRenderer();
        }

    }

    protected function executePost(): string{
        return $this->executeGet();
    }
}