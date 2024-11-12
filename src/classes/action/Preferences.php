<?php

namespace iutnc\nrv\action;

use iutnc\nrv\festival\Spectacle;
use function Sodium\add;

class Preferences extends Action {

    protected function executeGet(): string
    {
        $res = "<h1>Préférences</h1><br>";
        if (!isset($_COOKIE["preferences"])) {
            $res .= "<h3>Pas encore de préférences</h3>";
        } else {
            $preferences = unserialize($_COOKIE["preferences"]);
            $res .= "<ul>";
            foreach ($preferences as $pref) {
                $res .= "<li>" . $pref . "</li>";
            }
            $res .= "</ul>";
        }
        return $res;
    }




    protected function executePost(): string
    {
        if (!isset($_COOKIE['preferences']) || !is_array(@unserialize($_COOKIE['preferences']))) {
            $array = [];
        } else {
            $array = unserialize($_COOKIE['preferences']);
        }

        $array[] = $_POST["id-spectacle"];
        $serializedArray = serialize($array);

        //creation d un cookie d un mois, pour la duree de tout le festival
        setcookie('preferences', $serializedArray, time() + 60 * 60 * 24 * 30);

        return $this->executeGet();
    }
}