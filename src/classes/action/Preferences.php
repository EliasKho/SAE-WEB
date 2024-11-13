<?php

namespace iutnc\nrv\action;

use iutnc\nrv\render\SpectacleRender;
use iutnc\nrv\repository\NRVRepository;

class Preferences extends Action {

    protected function executeGet(): string
    {
        if (isset($_SESSION['user'])){
            $r = NRVRepository::getInstance();
            $user = unserialize($_SESSION['user']);
            $html = "<h1>Préférences</h1><br>";
            $preferences = $r->getPreferences($user->id);
            if (isset($_GET["idSpectacle"])){
                $idSpectacle = $_GET["idSpectacle"];
                if ($_GET["action2"] == "ajouter" && !in_array($idSpectacle, $preferences)){
                    $r->ajouterPreference($user->id, $idSpectacle);
                }
                elseif ($_GET["action2"] == "supprimer" && in_array($idSpectacle, $preferences)){
                    $r->supprimerPreference($user->id, $idSpectacle);
                }
            }
            $preferences = $r->getPreferences($user->id);
            $html .= "<ul>";
            foreach ($preferences as $pref) {
                $spectacle = $r->getSpectacleFromId($pref);
                $sr = new SpectacleRender($spectacle);
                $html.=$sr->renderPreferences()."<br>";
            }
            $html .= "</ul>";
        }
        else {
            $html = "<h1>Préférences</h1><br>";
            if (!isset($_COOKIE["preferences"])) {
                $preferences = [];
                $cookie = serialize($preferences);
                setcookie('preferences', $cookie, time() + 60 * 60 * 24 * 30);
            } else {
                $preferences = unserialize($_COOKIE["preferences"]);

                if (isset($_GET["idSpectacle"])) {
                    if ($_GET["action2"] == "ajouter" && !in_array($_GET["idSpectacle"], $preferences)) {
                        $preferences[] = $_GET["idSpectacle"];
                        $cookie = serialize($preferences);
                        setcookie('preferences', $cookie, time() + 60 * 60 * 24 * 30);
                    }
                    if ($_GET["action2"] == "supprimer") {
                        $idSpectacle = $_GET["idSpectacle"];

                        if (($key = array_search($idSpectacle, $preferences)) !== false) {
                            unset($preferences[$key]);
                            $preferences = array_values($preferences); // Réindexe le tableau pour éviter les trous
                            setcookie('preferences', serialize($preferences), time() + 60 * 60 * 24 * 30);
                        }
                    }
                }
                $html .= "<ul>";
                foreach ($preferences as $pref) {
                    $r = NRVRepository::getInstance();
                    $spectacle = $r->getSpectacleFromId($pref);
                    $sr = new SpectacleRender($spectacle);
                    $html .= $sr->renderPreferences() . "<br>";
                }
                $html .= "</ul>";
            }
        }
        return $html;
    }





    protected function executePost(): string
    {
       return $this->executeGet();
    }
}