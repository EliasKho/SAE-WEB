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

            if (isset($_GET["idSpectacle"])&& $_GET["action2"] == "ajouter"
            && !in_array($_GET["idSpectacle"], $preferences)) {
                $preferences[] = $_GET["idSpectacle"];
                $cookie = serialize($preferences);
                setcookie('preferences', $cookie, time() + 60 * 60 * 24 * 30);
            }

            if (isset($_GET["idSpectacle"]) && $_GET["action2"] == "supprimer") {
                $idSpectacle = $_GET["idSpectacle"];

                if (($key = array_search($idSpectacle, $preferences)) !== false) {
                    unset($preferences[$key]);
                    $preferences = array_values($preferences); // Réindexe le tableau pour éviter les trous
                    setcookie('preferences', serialize($preferences), time() + 60 * 60 * 24 * 30);
                }
            }

            $res .= "<ul>";
            foreach ($preferences as $pref) {
                $r = NRVRepository::getInstance();
                $spectacle = $r->getSpectacleFromId($pref);
                $sr = new SpectacleRender($spectacle);
                $res.=$sr->renderPreferences()."<br>";
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