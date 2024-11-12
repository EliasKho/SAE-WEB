<?php

namespace iutnc\nrv\action;

use iutnc\nrv\repository\NRVRepository;

class AjouterSpectacle extends Action{

    public function executeGet() : string{
        $r = NRVRepository::getInstance();
        $styles = $r->getAllStyles();
        $html = "";
        $html.= <<<FIN
            <h1>Ajouter un spectacle</h1>
            <form method="post" action="?action=add-spectacle" enctype="multipart/form-data">
                <label for="titre">Titre du spectacle:</label>
                <input type="text" id="titre" name="titre" required>             

                <label for="horaire">Horaire du spectacle:</label>
                <input type="time" id="horaire" name="horaire" required>

                <label for="duree">Durée du spectacle:</label>
                <input type="number" id="duree" name="duree" required>

                <label for="description">Description du spectacle:</label>
                <textarea id="description" name="description" required></textarea>
        FIN;

        $html.= "<label for='style'>Style du spectacle:</label>";
        $html.= "<select id='style' name='style' required>";
        for ($i = 1; $i <= count($styles); $i++) {
            $html.= "<option value='$i'>".$styles[$i-1]."</option>";
        }
        $html.= <<<FIN
                </select>
                
                <label for="images">Images du spectacle:</label>
                <input type="file" id="images" name="images" required>
                
                <label for="video">Vidéo du spectacle (lien vers la vidéo):</label>
                <input type="text" id="video" name="video" required>              

                <input type="submit" value="Ajouter">
            </form>
            FIN;

        return $html;

    }

    public function executePost() : string{
        $titre = $_POST['titre'];
        $horaire = $_POST['horaire'];
        $duree = $_POST['duree'];
        $description = $_POST['description'];
        $style = $_POST['style'];
        $images = $_FILES['images'];
        $video = $_POST['video'];

        $r = NRVRepository::getInstance();
        $r->ajouterSpectacle($titre, $horaire, $duree, $description, $style, $images, $video);

        return 'Spectacle ajouté';

    }
}