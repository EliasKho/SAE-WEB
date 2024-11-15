<?php

namespace iutnc\nrv\action;

use iutnc\nrv\render\SpectacleRender;
use iutnc\nrv\repository\NRVRepository;

/**
 * Classe représentant l'action de gestion des préférences
 * @package iutnc\nrv\action
 */
class Preferences extends Action {

    /**
     * Exécute l'action de la page
     * @return string Le code HTML de la page
     */
    protected function executeGet(): string
    {
        $r = NRVRepository::getInstance();
        // on vérifie si l'utilisateur est connecté
        if (isset($_SESSION['user'])){
            // on récupère l'utilisateur
            $user = unserialize($_SESSION['user']);

            $html = "<h1>Préférences</h1>";
            // on récupère les préférences de l'utilisateur dans la base de données
            $preferences = $r->getPreferencesFromUserId($user->id);

            // si l'utilisateur a cliqué sur un bouton ajouter ou supprimer une préférence
            if (isset($_GET["idSpectacle"])){
                // on récupère l'id du spectacle
                $idSpectacle = $_GET["idSpectacle"];
                // on vérifie si l'utilisateur veut ajouter ou supprimer une préférence
                if ($_GET["action2"] == "ajouter" && !in_array($idSpectacle, $preferences)){
                    // on ajoute la préférence dans la base de données
                    $r->ajouterPreference($user->id, $idSpectacle);
                }
                elseif ($_GET["action2"] == "supprimer" && in_array($idSpectacle, $preferences)){
                    // on supprime la préférence de la base de données
                    $r->supprimerPreference($user->id, $idSpectacle);
                }
            }
            // on récupère les préférences de l'utilisateur dans la base de données après les modifications
            $preferences = $r->getPreferencesFromUserId($user->id);
        }
        // si l'utilisateur n'est pas connecté
        else {
            $html = "<h1>Préférences</h1>";
            // on vérifie si le cookie des préférences existe
            if (!isset($_COOKIE["preferences"])) {
                // si le cookie n'existe pas, on le crée avec un tableau vide
                $preferences = [];
                $cookie = serialize($preferences);
                setcookie('preferences', $cookie, time() + 60 * 60 * 24 * 30);
            } else {
                // si le cookie existe, on récupère les préférences (id des spectacles) de l'utilisateur
                $preferences = unserialize($_COOKIE["preferences"]);

                // si l'utilisateur a cliqué sur un bouton ajouter ou supprimer une préférence
                if (isset($_GET["idSpectacle"])) {
                    // on vérifie si l'utilisateur veut ajouter ou supprimer une préférence
                    if ($_GET["action2"] == "ajouter" && !in_array($_GET["idSpectacle"], $preferences)) {
                        // on ajoute la préférence dans le cookie
                        $preferences[] = $_GET["idSpectacle"];
                        $cookie = serialize($preferences);
                        setcookie('preferences', $cookie, time() + 60 * 60 * 24 * 30);
                    }
                    // si l'utilisateur veut supprimer une préférence
                    elseif ($_GET["action2"] == "supprimer") {
                        // on récupère l'id du spectacle
                        $idSpectacle = $_GET["idSpectacle"];
                        // on supprime la préférence du cookie
                        if (($key = array_search($idSpectacle, $preferences)) !== false) {
                            unset($preferences[$key]);
                            $preferences = array_values($preferences); // Réindexe le tableau pour éviter les trous
                            setcookie('preferences', serialize($preferences), time() + 60 * 60 * 24 * 30);
                        }
                    }
                }
            }
        }
        // après les modifications, on affiche les préférences de l'utilisateur
        $html .= "<ul>";
        foreach ($preferences as $pref) {
            $pref = intval($pref);
            $spectacle = $r->getSpectacleFromId($pref);
            $sr = new SpectacleRender($spectacle);
            $html.=$sr->renderPreferences()."<br>";
        }
        $html .= "</ul>";
        return $html;
    }

    /**
     * Exécute l'action de la page
     * @return string Le code HTML de la page
     */
    protected function executePost(): string
    {
        // on ne fait rien en POST, on redirige en GET
       return $this->executeGet();
    }
}