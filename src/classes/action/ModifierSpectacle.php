<?php

namespace iutnc\nrv\action;

use iutnc\nrv\festival\Spectacle;
use iutnc\nrv\repository\NRVRepository;

class ModifierSpectacle extends Action
{
    public function executeGet(): string
    {
        $idSpectacle = $_GET['idSpectacle'] ?? null;
        if (!$idSpectacle) {
            return "ID du spectacle manquant.";
        }

        $repository = NRVRepository::getInstance();
        $spectacle = $repository->getSpectacleFromId($idSpectacle);

        if (!$spectacle) {
            return "Spectacle introuvable.";
        }

        // Préremplir le formulaire avec les données existantes du spectacle
        $titre = htmlspecialchars($spectacle->titre);
        $description = htmlspecialchars($spectacle->description);
        $horaire = htmlspecialchars($spectacle->horaireSpec);
        $duree = htmlspecialchars($spectacle->dureeSpec);
        $video = htmlspecialchars($spectacle->video);
        $styles = $repository->getAllStyles();

        // Récupérer tous les artistes et ceux associés au spectacle
        $allArtistes = $repository->getAllArtistes(); // Retourne les noms des artistes
        $spectacleArtistes = $repository->getArtistesFromSpectacleId($idSpectacle); // Retourne les noms des artistes associés

        $html = <<<FIN
            <h1>Modifier le spectacle</h1>
            <form method="post" action="?action=ModifSpectacle&idSpectacle={$idSpectacle}" enctype="multipart/form-data">
                <label for="titre">Titre du spectacle:</label>
                <input type="text" id="titre" name="titre" value="{$titre}" required>      
                </br></br>                    
                
                <label for="horaire">Horaire du spectacle:</label>
                <input type="time" id="horaire" name="horaire" value="{$horaire}" required>
                </br></br>
                
                <label for="duree">Durée du spectacle:</label>
                <input type="number" id="duree" name="duree" value="{$duree}" required>
                </br></br>
                
                <label for="description">Description du spectacle:</label>
                <textarea id="description" name="description" required>{$description}</textarea>
                </br></br>
                
                <label for='style'>Style du spectacle:</label>
                <select id='style' name='style' required>
        FIN;

        foreach ($styles as $key => $styleName) {
            $selected = ($key + 1 == $spectacle->idStyle) ? "selected" : "";
            $html .= "<option value='" . ($key + 1) . "' {$selected}>{$styleName}</option>";
        }

        // Ajouter les artistes avec cases à cocher et précocher ceux associés au spectacle
        $html .= "</select></br></br><label>Artistes du spectacle:</label>";
        foreach ($allArtistes as $artiste) {
            $artisteNom = htmlspecialchars($artiste['nomArtiste']);
            $id = $artiste['idArtiste'];
            $checked = in_array($artisteNom, $spectacleArtistes) ? "checked" : "";
            $html .= "<label><input type='checkbox' name='artistes[]' value='{$id}' {$checked}> {$artisteNom}</label> ";
        }

        $html .= <<<FIN
                </br></br>
                <label for="images">Images du spectacle:</label>
                <input type="file" id="images" name="images[]" multiple>
                </br></br>
                
                <label for="video">Vidéo du spectacle (lien vers la vidéo):</label>
                <input type="text" id="video" name="video" value="{$video}" required>       
                </br></br>       

                <input type="submit" value="Modifier">
            </form>
        FIN;

        return $html;
    }

    public function executePost(): string
    {
        $idSpectacle = $_GET['idSpectacle'] ?? null;
        if (!$idSpectacle) {
            return "ID du spectacle manquant.";
        }
        $r = NRVRepository::getInstance();

        $annuler = (bool)$r->getSpectacleFromId($idSpectacle)->estAnnule;
        $titre = $_POST['titre'];
        $horaire = $_POST['horaire'];
        $duree = $_POST['duree'];
        $description = $_POST['description'];
        $style = $_POST['style'];
        $images = $_FILES['images'];
        $video = $_POST['video'];
        $selectedArtistes = $_POST['artistes'] ?? []; // Liste des artistes sélectionnés (index)

        $spectacle = new Spectacle($titre, $description, $video, $horaire, $duree, $style, $annuler, $idSpectacle);

        $r->updateSpectacle($spectacle);
        $r->updateSpectacleImages($idSpectacle, $images);
        $r->updateSpectacleArtistes($idSpectacle, $selectedArtistes);

        return "Spectacle modifié avec succès.";
    }
}
