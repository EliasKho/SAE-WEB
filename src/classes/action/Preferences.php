<?php

namespace iutnc\nrv\action;

use iutnc\nrv\festival\Spectacle;

class Preferences extends Action {

    protected function executeGet(): string
    {
        $res = "<h1>Préférences</h1><br>";
        $res .= "<ul>";
        foreach ($_SESSION['preferences'] as $preference) {
            $res .= "<li>" . $preference . "</li>";
        }
        $res .= "</ul>";
        return $res;
    }

    protected function executePost(): string
    {
//        Créer un nouveau spectacle
        $titre = $_POST['titre'];
        $description = $_POST['description'];
        $video = $_POST['video'];
        $horaireSpec = $_POST['horaireSpec'];
        $dureeSpec = $_POST['dureeSpec'];
        $style = $_POST['style'];
        $spectacle = new Spectacle($titre, $description, $video, $horaireSpec, $dureeSpec, $style);
        $spectacle->setImages($_POST['images']);
        $spectacle->setArtistes($_POST['artistes']);
//        Si l'utilisateur n'est pas connecté, ajouter le spectacle dans les préférences en Session
        if (!isset($_SESSION['user'])) {
            $_SESSION['preferences'][] = $spectacle;
        }
        return $this->executeGet();
    }
}