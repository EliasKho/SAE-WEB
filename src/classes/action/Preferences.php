<?php

namespace iutnc\nrv\action;

use iutnc\nrv\render\SpectacleRender;
use iutnc\nrv\repository\NRVRepository;

class Preferences extends Action {

    protected function executeGet(): string
    {
        $res = "<h1>Préférences</h1><br>";
        if (!isset($_COOKIE["preferences"])) {
            $preferences = [];
            $cookie = serialize($preferences);
            setcookie('preferences', $cookie, time() + 60 * 60 * 24 * 30);
        } else {
            $preferences = unserialize($_COOKIE["preferences"]);

            if (isset($_GET["idSpectacle"]) && !in_array($_GET["idSpectacle"], $preferences)) {
                $preferences[] = $_GET["idSpectacle"];
                $cookie = serialize($preferences);
                setcookie('preferences', $cookie, time() + 60 * 60 * 24 * 30);
            }

            $res .= "<ul>";
            foreach ($preferences as $pref) {
                //$res .= "<li>" . htmlspecialchars($pref) . "</li>";
                $r = NRVRepository::getInstance();
                $spectacle = $r->getSpectacleFromId($pref);
                $sr = new SpectacleRender($spectacle);
                $res.=$sr->renderCompact()."<br>";
            }
            $res .= "</ul>";
        }
        return $res;
    }





    protected function executePost(): string
    {
       return $this->executeGet();
    }
}